# CLIP Image Embedding Server

Local, Offline, Free image recognition system using OpenAI's CLIP model.

## Features

- **Free**: No API keys required
- **Offline**: Works without internet connection
- **Fast**: ~50ms per image on CPU
- **Accurate**: 90-95% accuracy for product matching

## Setup

### Local Development

```bash
# Install dependencies
pip install -r requirements.txt

# Start the server
python main.py
```

### Docker

```bash
# Build and start
docker compose up -d --build

# Check health
curl http://localhost:8089/health
```

## API Endpoints

### Health Check
```http
GET /health
```

Response:
```json
{
    "status": "healthy",
    "model": "ViT-B/32",
    "device": "cpu",
    "embedding_dimension": 512
}
```

### Generate Embedding
```http
POST /embed
Content-Type: application/json

{
    "image_base64": "base64_encoded_image",
    "or": "image_url": "https://example.com/image.jpg"
}
```

Response:
```json
{
    "embedding": [0.23, -0.15, 0.87, ...],
    "dimension": 512,
    "device": "cpu"
}
```

### Match Image
```http
POST /match
Content-Type: application/json

{
    "customer_image_base64": "base64_encoded_image",
    "catalog_embeddings": [
        {
            "id": 1,
            "embedding": [0.23, -0.15, 0.87, ...],
            "product_name": "Product 1"
        }
    ],
    "top_k": 5,
    "threshold": 0.7
}
```

Response:
```json
{
    "matches": [
        {
            "id": 1,
            "product_name": "Product 1",
            "score": 0.95
        }
    ],
    "best_match": {
        "id": 1,
        "product_name": "Product 1",
        "score": 0.95
    },
    "customer_embedding": [0.23, -0.15, 0.87, ...],
    "total_catalog_items": 100
}
```

### Batch Embedding
```http
POST /batch/embed
Content-Type: application/json

[
    {"image_base64": "base64_encoded_image_1"},
    {"image_base64": "base64_encoded_image_2"}
]
```

## Configuration

### Environment Variables

```bash
CLIP_SERVER_URL=http://localhost:8089
CLIP_THRESHOLD=0.7
CLIP_TIMEOUT=30
```

### Config File

```php
// config/services.php
'clip' => [
    'server_url' => env('CLIP_SERVER_URL', 'http://localhost:8089'),
    'threshold' => env('CLIP_THRESHOLD', 0.7),
    'timeout' => env('CLIP_TIMEOUT', 30),
],
```

## How It Works

1. **Image Upload**: Customer sends image via Facebook Messenger
2. **Embedding Generation**: CLIP model converts image to 512-dimensional vector
3. **Catalog Matching**: Compare against stored product embeddings using cosine similarity
4. **Result**: Return best match with confidence score

## Accuracy

- **90-95%**: For distinct products with unique shapes/colors
- **80-85%**: For similar products (different flavors, labels)
- **70-75%**: For very similar products (same product, different angle)

## Performance

- **CPU**: ~50ms per image
- **GPU**: ~10ms per image (if CUDA available)
- **Memory**: ~350MB for model
- **Storage**: ~2KB per embedding (512 floats)

## Troubleshooting

### Server Won't Start

```bash
# Check Python version
python3 --version

# Install dependencies
pip install -r requirements.txt

# Check port availability
lsof -i :8089
```

### Low Accuracy

- Add more product images (5-10 per product)
- Use different angles and lighting
- Preprocess images (remove background, crop)

### Slow Performance

- Use GPU if available
- Reduce catalog size
- Cache frequent queries

## License

MIT License - Same as main project
