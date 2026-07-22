import React, { useState, useRef, useCallback, useEffect } from 'react';
import { X, ZoomIn, Move, ChevronLeft, ChevronRight, Maximize2 } from 'lucide-react';

const ZOOM = 2.5;

export default function ImageZoom({ src, alt, images = [], onImageChange, currentIndex = 0 }) {
  const [isHovering, setIsHovering] = useState(false);
  const [lensPos, setLensPos] = useState({ x: 50, y: 50 });
  const [lightboxOpen, setLightboxOpen] = useState(false);
  const [lightboxZoom, setLightboxZoom] = useState(1);
  const [lightboxPos, setLightboxPos] = useState({ x: 0, y: 0 });
  const [isDragging, setIsDragging] = useState(false);
  const [dragStart, setDragStart] = useState({ x: 0, y: 0 });
  const [loadError, setLoadError] = useState(false);

  const containerRef = useRef(null);
  const lightboxRef = useRef(null);
  const imgRef = useRef(null);

  // Reset zoom/scroll when image changes
  useEffect(() => {
    setLoadError(false);
    setLightboxZoom(1);
    setLightboxPos({ x: 0, y: 0 });
  }, [src]);

  const handleMouseMove = useCallback((e) => {
    if (!containerRef.current || !imgRef.current) return;
    const rect = containerRef.current.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    setLensPos({
      x: Math.min(100, Math.max(0, x)),
      y: Math.min(100, Math.max(0, y)),
    });
  }, []);

  const handleLightboxMouseDown = (e) => {
    if (lightboxZoom === 1) return;
    e.preventDefault();
    setIsDragging(true);
    setDragStart({ x: e.clientX - lightboxPos.x, y: e.clientY - lightboxPos.y });
  };

  const handleLightboxMouseMove = (e) => {
    if (!isDragging) return;
    setLightboxPos({
      x: e.clientX - dragStart.x,
      y: e.clientY - dragStart.y,
    });
  };

  const handleLightboxMouseUp = () => setIsDragging(false);

  const handleWheel = (e) => {
    if (!lightboxOpen) return;
    e.preventDefault();
    const delta = e.deltaY > 0 ? -0.25 : 0.25;
    setLightboxZoom((prev) => {
      const next = Math.min(4, Math.max(1, prev + delta));
      return next;
    });
  };

  const openLightbox = () => {
    setLightboxOpen(true);
    setLightboxZoom(1);
    setLightboxPos({ x: 0, y: 0 });
  };

  const closeLightbox = () => {
    setLightboxOpen(false);
    setLightboxZoom(1);
    setLightboxPos({ x: 0, y: 0 });
    setIsDragging(false);
  };

  // Keyboard navigation in lightbox
  useEffect(() => {
    if (!lightboxOpen) return;
    const handleKey = (e) => {
      if (e.key === 'Escape') closeLightbox();
      if (images.length > 1 && onImageChange) {
        if (e.key === 'ArrowLeft' && currentIndex > 0) onImageChange(currentIndex - 1);
        if (e.key === 'ArrowRight' && currentIndex < images.length - 1) onImageChange(currentIndex + 1);
      }
    };
    window.addEventListener('keydown', handleKey);
    return () => window.removeEventListener('keydown', handleKey);
  }, [lightboxOpen, currentIndex, images.length]);

  // Prevent body scroll when lightbox is open
  useEffect(() => {
    if (lightboxOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
    return () => { document.body.style.overflow = ''; };
  }, [lightboxOpen]);

  if (!src) return null;

  return (
    <>
      {/* Main Image with Zoom */}
      <div
        ref={containerRef}
        className="relative aspect-square bg-gray-50 rounded-xl overflow-hidden cursor-crosshair group"
        onMouseEnter={() => setIsHovering(true)}
        onMouseLeave={() => setIsHovering(false)}
        onMouseMove={handleMouseMove}
        onClick={openLightbox}
      >
        {loadError ? (
          <div className="w-full h-full flex items-center justify-center text-gray-200 text-6xl">?</div>
        ) : (
          <img
            ref={imgRef}
            src={src}
            alt={alt}
            className="w-full h-full object-cover select-none"
            draggable={false}
            onError={() => setLoadError(true)}
          />
        )}

        {/* Zoom lens overlay */}
        {isHovering && !loadError && (
          <>
            {/* Lens indicator */}
            <div
              className="absolute w-24 h-24 rounded-full border-2 border-white shadow-xl pointer-events-none bg-white/10 backdrop-blur-[1px]"
              style={{
                left: `calc(${lensPos.x}% - 48px)`,
                top: `calc(${lensPos.y}% - 48px)`,
              }}
            />

            {/* Zoomed view */}
            <div
              className="absolute inset-0 pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity duration-150"
              style={{
                backgroundImage: `url(${src})`,
                backgroundSize: `${ZOOM * 100}%`,
                backgroundPosition: `${lensPos.x}% ${lensPos.y}%`,
                backgroundRepeat: 'no-repeat',
              }}
            />
          </>
        )}

        {/* Zoom icon on hover */}
        <div className="absolute bottom-3 right-3 bg-white/80 backdrop-blur-sm rounded-lg px-2.5 py-1.5 flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 shadow-sm">
          <ZoomIn className="w-3.5 h-3.5 text-gray-700" />
          <span className="text-[10px] font-medium text-gray-700 uppercase tracking-wider">Zoom</span>
        </div>
      </div>

      {/* Lightbox Modal */}
      {lightboxOpen && (
        <div
          ref={lightboxRef}
          className="fixed inset-0 z-[9999] bg-black/95 backdrop-blur-md flex items-center justify-center"
          onWheel={handleWheel}
          onMouseMove={handleLightboxMouseMove}
          onMouseUp={handleLightboxMouseUp}
          onMouseLeave={handleLightboxMouseUp}
        >
          {/* Close button */}
          <button
            onClick={closeLightbox}
            className="absolute top-4 right-4 z-10 w-11 h-11 flex items-center justify-center bg-white/10 hover:bg-white/20 backdrop-blur-sm rounded-full transition-all duration-200 group"
          >
            <X className="w-5 h-5 text-white group-hover:scale-110 transition-transform" />
          </button>

          {/* Zoom controls */}
          <div className="absolute bottom-6 left-1/2 -translate-x-1/2 z-10 flex items-center gap-3 bg-black/40 backdrop-blur-md rounded-full px-4 py-2.5">
            <button
              onClick={() => setLightboxZoom((p) => Math.max(1, p - 0.5))}
              className="w-8 h-8 flex items-center justify-center text-white/70 hover:text-white hover:bg-white/10 rounded-full transition"
            >
              <MinusIcon />
            </button>
            <span className="text-xs text-white/80 font-medium min-w-[36px] text-center tabular-nums">
              {Math.round(lightboxZoom * 100)}%
            </span>
            <button
              onClick={() => setLightboxZoom((p) => Math.min(4, p + 0.5))}
              className="w-8 h-8 flex items-center justify-center text-white/70 hover:text-white hover:bg-white/10 rounded-full transition"
            >
              <PlusIcon />
            </button>
            <span className="w-px h-5 bg-white/20 mx-1" />
            <span className="text-[10px] text-white/50 flex items-center gap-1">
              <Move className="w-3 h-3" /> Drag to pan
            </span>
            <span className="text-[10px] text-white/50 flex items-center gap-1">
              Scroll to zoom
            </span>
          </div>

          {/* Image count */}
          {images.length > 1 && (
            <div className="absolute top-6 left-1/2 -translate-x-1/2 z-10 bg-black/40 backdrop-blur-md rounded-full px-3.5 py-1.5">
              <span className="text-xs text-white/70 font-medium">
                {currentIndex + 1} / {images.length}
              </span>
            </div>
          )}

          {/* Navigation arrows */}
          {images.length > 1 && onImageChange && (
            <>
              <button
                onClick={() => onImageChange(currentIndex - 1)}
                disabled={currentIndex <= 0}
                className="absolute left-4 top-1/2 -translate-y-1/2 z-10 w-11 h-11 flex items-center justify-center bg-white/10 hover:bg-white/20 backdrop-blur-sm rounded-full transition-all duration-200 disabled:opacity-20 disabled:cursor-not-allowed"
              >
                <ChevronLeft className="w-5 h-5 text-white" />
              </button>
              <button
                onClick={() => onImageChange(currentIndex + 1)}
                disabled={currentIndex >= images.length - 1}
                className="absolute right-4 top-1/2 -translate-y-1/2 z-10 w-11 h-11 flex items-center justify-center bg-white/10 hover:bg-white/20 backdrop-blur-sm rounded-full transition-all duration-200 disabled:opacity-20 disabled:cursor-not-allowed"
              >
                <ChevronRight className="w-5 h-5 text-white" />
              </button>
            </>
          )}

          {/* Image container */}
          <div
            className="relative max-w-[90vw] max-h-[90vh] overflow-hidden select-none"
            onMouseDown={handleLightboxMouseDown}
            style={{ cursor: lightboxZoom > 1 ? 'grab' : 'default' }}
          >
            <img
              src={src}
              alt={alt}
              className={`max-w-full max-h-[90vh] object-contain ${
                isDragging ? 'transition-none' : 'transition-transform duration-75 ease-out'
              }`}
              style={{
                transform: `scale(${lightboxZoom}) translate(${lightboxPos.x / lightboxZoom}px, ${lightboxPos.y / lightboxZoom}px)`,
              }}
              draggable={false}
            />
          </div>
        </div>
      )}
    </>
  );
}

function MinusIcon() {
  return (
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
      <line x1="5" y1="12" x2="19" y2="12" />
    </svg>
  );
}

function PlusIcon() {
  return (
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
      <line x1="12" y1="5" x2="12" y2="19" />
      <line x1="5" y1="12" x2="19" y2="12" />
    </svg>
  );
}
