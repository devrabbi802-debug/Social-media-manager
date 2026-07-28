"""
CLIP Image Embedding Server + Text Semantic Search
Local, Offline, Free - No API Keys Required!

Endpoints:
- POST /embed: Generate embedding for a single image
- POST /match: Match customer image against catalog embeddings
- POST /text-embed: Generate text embedding for product/customer message
- POST /text-search: Search products by text query (semantic search)
- GET /health: Health check
"""

import io
import base64
import logging
from typing import Optional

import numpy as np
import torch
import clip
from PIL import Image
from fastapi import FastAPI, HTTPException, UploadFile, File
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import requests

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(
    title="CLIP Image Embedding Server",
    description="Local image embedding generation using CLIP model",
    version="1.0.0",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Global model loading
DEVICE = "cuda" if torch.cuda.is_available() else "cpu"
logger.info(f"Loading CLIP model on {DEVICE}...")
MODEL, PREPROCESS = clip.load("ViT-B/32", device=DEVICE)
EMBEDDING_DIM = 512
logger.info(f"CLIP model loaded successfully. Embedding dimension: {EMBEDDING_DIM}")

# Load text embedding model (multilingual, supports Bangla + 50 languages)
logger.info("Loading text embedding model (paraphrase-multilingual-MiniLM-L12-v2)...")
from sentence_transformers import SentenceTransformer
TEXT_MODEL = SentenceTransformer('paraphrase-multilingual-MiniLM-L12-v2')
TEXT_EMBEDDING_DIM = 384
logger.info(f"Text embedding model loaded. Dimension: {TEXT_EMBEDDING_DIM}")


class EmbedRequest(BaseModel):
    """Request for embedding generation."""
    image_base64: Optional[str] = None
    image_url: Optional[str] = None


class EmbedResponse(BaseModel):
    """Response containing image embedding."""
    embedding: list[float]
    dimension: int
    device: str


class MatchRequest(BaseModel):
    """Request for matching customer image against catalog."""
    customer_image_base64: Optional[str] = None
    customer_image_url: Optional[str] = None
    catalog_embeddings: list[dict]  # [{"id": 1, "embedding": [...], "product_name": "..."}, ...]
    top_k: int = 5
    threshold: float = 0.7


class MatchResult(BaseModel):
    """Single match result."""
    id: int
    product_name: str
    score: float
    metadata: Optional[dict] = None


class MatchResponse(BaseModel):
    """Response containing match results."""
    matches: list[MatchResult]
    best_match: Optional[MatchResult]
    customer_embedding: list[float]
    total_catalog_items: int


def load_image_from_base64(base64_string: str) -> Image.Image:
    """Load PIL Image from base64 string."""
    try:
        if "," in base64_string:
            base64_string = base64_string.split(",")[1]
        
        image_data = base64.b64decode(base64_string)
        return Image.open(io.BytesIO(image_data)).convert("RGB")
    except Exception as e:
        raise HTTPException(status_code=400, detail=f"Invalid base64 image: {str(e)}")


def load_image_from_url(url: str) -> Image.Image:
    """Load PIL Image from URL."""
    try:
        response = requests.get(url, timeout=30)
        response.raise_for_status()
        return Image.open(io.BytesIO(response.content)).convert("RGB")
    except Exception as e:
        raise HTTPException(status_code=400, detail=f"Failed to load image from URL: {str(e)}")


def get_embedding(image: Image.Image) -> np.ndarray:
    """Generate CLIP embedding for an image."""
    image_input = PREPROCESS(image).unsqueeze(0).to(DEVICE)
    
    with torch.no_grad():
        image_features = MODEL.encode_image(image_input)
        image_features = image_features / image_features.norm(dim=-1, keepdim=True)
    
    return image_features.cpu().numpy().flatten()


def cosine_similarity(a: np.ndarray, b: np.ndarray) -> float:
    """Calculate cosine similarity between two vectors."""
    return float(np.dot(a, b) / (np.linalg.norm(a) * np.linalg.norm(b)))


@app.get("/health")
async def health_check():
    """Health check endpoint."""
    return {
        "status": "healthy",
        "image_model": "ViT-B/32",
        "text_model": "paraphrase-multilingual-MiniLM-L12-v2",
        "device": DEVICE,
        "image_embedding_dimension": EMBEDDING_DIM,
        "text_embedding_dimension": TEXT_EMBEDDING_DIM,
    }


@app.post("/embed", response_model=EmbedResponse)
async def embed_image(request: EmbedRequest):
    """
    Generate CLIP embedding for an image.
    
    Accepts either base64 image or URL.
    Returns 512-dimensional embedding vector.
    """
    if not request.image_base64 and not request.image_url:
        raise HTTPException(
            status_code=400,
            detail="Either image_base64 or image_url is required"
        )
    
    try:
        if request.image_base64:
            image = load_image_from_base64(request.image_base64)
        else:
            image = load_image_from_url(request.image_url)
        
        embedding = get_embedding(image)
        
        return EmbedResponse(
            embedding=embedding.tolist(),
            dimension=EMBEDDING_DIM,
            device=DEVICE,
        )
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Embedding generation failed: {str(e)}")
        raise HTTPException(status_code=500, detail=f"Embedding failed: {str(e)}")


@app.post("/embed/upload", response_model=EmbedResponse)
async def embed_image_upload(file: UploadFile = File(...)):
    """
    Generate CLIP embedding for an uploaded image file.
    """
    try:
        contents = await file.read()
        image = Image.open(io.BytesIO(contents)).convert("RGB")
        
        embedding = get_embedding(image)
        
        return EmbedResponse(
            embedding=embedding.tolist(),
            dimension=EMBEDDING_DIM,
            device=DEVICE,
        )
    except Exception as e:
        logger.error(f"Embedding generation failed: {str(e)}")
        raise HTTPException(status_code=500, detail=f"Embedding failed: {str(e)}")


@app.post("/match", response_model=MatchResponse)
async def match_image(request: MatchRequest):
    """
    Match customer image against catalog embeddings.
    
    Returns top-k matches with similarity scores.
    """
    if not request.customer_image_base64 and not request.customer_image_url:
        raise HTTPException(
            status_code=400,
            detail="Either customer_image_base64 or customer_image_url is required"
        )
    
    if not request.catalog_embeddings:
        raise HTTPException(
            status_code=400,
            detail="catalog_embeddings cannot be empty"
        )
    
    try:
        # Load customer image
        if request.customer_image_base64:
            customer_image = load_image_from_base64(request.customer_image_base64)
        else:
            customer_image = load_image_from_url(request.customer_image_url)
        
        # Get customer embedding
        customer_embedding = get_embedding(customer_image)
        
        # Calculate similarities
        results = []
        for item in request.catalog_embeddings:
            catalog_embedding = np.array(item["embedding"])
            score = cosine_similarity(customer_embedding, catalog_embedding)
            
            results.append(MatchResult(
                id=item["id"],
                product_name=item.get("product_name", ""),
                score=score,
                metadata=item.get("metadata"),
            ))
        
        # Sort by score descending
        results.sort(key=lambda x: x.score, reverse=True)
        
        # Filter by threshold and get top-k
        filtered = [r for r in results if r.score >= request.threshold]
        top_matches = filtered[:request.top_k]
        
        best_match = top_matches[0] if top_matches else None
        
        return MatchResponse(
            matches=top_matches,
            best_match=best_match,
            customer_embedding=customer_embedding.tolist(),
            total_catalog_items=len(request.catalog_embeddings),
        )
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Image matching failed: {str(e)}")
        raise HTTPException(status_code=500, detail=f"Matching failed: {str(e)}")


@app.post("/batch/embed")
async def batch_embed(requests: list[EmbedRequest]):
    """
    Batch generate embeddings for multiple images.
    More efficient than calling /embed multiple times.
    """
    results = []
    
    for req in requests:
        try:
            if req.image_base64:
                image = load_image_from_base64(req.image_base64)
            elif req.image_url:
                image = load_image_from_url(req.image_url)
            else:
                results.append({"error": "No image provided"})
                continue
            
            embedding = get_embedding(image)
            results.append({
                "embedding": embedding.tolist(),
                "dimension": EMBEDDING_DIM,
            })
        except Exception as e:
            results.append({"error": str(e)})
    
    return {"results": results, "count": len(results)}


# ============================================================
# TEXT SEARCH MODELS
# ============================================================

class TextEmbedRequest(BaseModel):
    """Request for text embedding generation."""
    text: str


class TextEmbedResponse(BaseModel):
    """Response containing text embedding."""
    embedding: list[float]
    dimension: int
    model: str


class TextSearchRequest(BaseModel):
    """Request for text-based product search."""
    query: str
    catalog_embeddings: list[dict]  # [{"id": 1, "embedding": [...], "product_name": "...", ...}]
    top_k: int = 5
    threshold: float = 0.3


class TextSearchResult(BaseModel):
    """Single text search result."""
    id: str
    product_name: str
    score: float
    metadata: Optional[dict] = None


class TextSearchResponse(BaseModel):
    """Response containing text search results."""
    matches: list[TextSearchResult]
    best_match: Optional[TextSearchResult]
    query: str
    total_catalog_items: int


# ============================================================
# TEXT SEARCH ENDPOINTS
# ============================================================

@app.post("/text-embed", response_model=TextEmbedResponse)
async def embed_text(request: TextEmbedRequest):
    """
    Generate text embedding using multilingual model.
    Supports Bangla + 50 other languages.
    """
    if not request.text or not request.text.strip():
        raise HTTPException(status_code=400, detail="Text cannot be empty")

    try:
        embedding = TEXT_MODEL.encode([request.text.strip()], normalize_embeddings=True)
        return TextEmbedResponse(
            embedding=embedding[0].tolist(),
            dimension=TEXT_EMBEDDING_DIM,
            model="paraphrase-multilingual-MiniLM-L12-v2",
        )
    except Exception as e:
        logger.error(f"Text embedding failed: {str(e)}")
        raise HTTPException(status_code=500, detail=f"Text embedding failed: {str(e)}")


@app.post("/text-search", response_model=TextSearchResponse)
async def text_search(request: TextSearchRequest):
    """
    Search products by text query using semantic similarity.
    Works with Bangla + English mixed queries.
    """
    if not request.query or not request.query.strip():
        raise HTTPException(status_code=400, detail="Query cannot be empty")

    if not request.catalog_embeddings:
        raise HTTPException(status_code=400, detail="catalog_embeddings cannot be empty")

    try:
        # Generate query embedding
        query_embedding = TEXT_MODEL.encode([request.query.strip()], normalize_embeddings=True)[0]

        # Calculate similarities
        results = []
        for item in request.catalog_embeddings:
            catalog_embedding = np.array(item["embedding"])
            score = float(np.dot(query_embedding, catalog_embedding) / (
                np.linalg.norm(query_embedding) * np.linalg.norm(catalog_embedding)
            ))

            results.append(TextSearchResult(
                id=item["id"],
                product_name=item.get("product_name", ""),
                score=score,
                metadata=item.get("metadata"),
            ))

        # Sort by score descending
        results.sort(key=lambda x: x.score, reverse=True)

        # Filter by threshold and get top-k
        filtered = [r for r in results if r.score >= request.threshold]
        top_matches = filtered[:request.top_k]

        best_match = top_matches[0] if top_matches else None

        return TextSearchResponse(
            matches=top_matches,
            best_match=best_match,
            query=request.query,
            total_catalog_items=len(request.catalog_embeddings),
        )
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Text search failed: {str(e)}")
        raise HTTPException(status_code=500, detail=f"Text search failed: {str(e)}")


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8089)
