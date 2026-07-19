import React, { useState, useEffect } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

export default function HeroBanner({ banners = [] }) {
  const [current, setCurrent] = useState(0);

  useEffect(() => {
    if (banners.length <= 1) return;
    const timer = setInterval(() => {
      setCurrent((prev) => (prev + 1) % banners.length);
    }, 5000);
    return () => clearInterval(timer);
  }, [banners.length]);

  if (banners.length === 0) {
    return (
      <div className="bg-gradient-to-r from-primary to-secondary h-96 flex items-center justify-center">
        <div className="text-center text-white">
          <h1 className="text-4xl font-bold mb-4">Welcome to Our Store</h1>
          <p className="text-xl">Discover amazing products</p>
        </div>
      </div>
    );
  }

  const goToPrev = () => setCurrent((prev) => (prev - 1 + banners.length) % banners.length);
  const goToNext = () => setCurrent((prev) => (prev + 1) % banners.length);

  return (
    <div className="relative h-96 overflow-hidden">
      {banners.map((banner, index) => (
        <div
          key={banner.id}
          className={`absolute inset-0 transition-opacity duration-500 ${index === current ? 'opacity-100' : 'opacity-0'}`}
        >
          {banner.image && (
            <img src={`/storage/${banner.image}`} alt={banner.title} className="w-full h-full object-cover" />
          )}
          <div className="absolute inset-0 bg-black/40" />
          <div className="absolute inset-0 flex items-center justify-center">
            <div className="text-center text-white px-4">
              {banner.title && <h2 className="text-3xl md:text-5xl font-bold mb-4">{banner.title}</h2>}
              {banner.subtitle && <p className="text-lg md:text-xl mb-6">{banner.subtitle}</p>}
              {banner.btn_text && banner.link && (
                <a href={banner.link} className="inline-block bg-primary text-white px-8 py-3 rounded-lg font-medium hover:bg-primary/90 transition">
                  {banner.btn_text}
                </a>
              )}
            </div>
          </div>
        </div>
      ))}

      {banners.length > 1 && (
        <>
          <button onClick={goToPrev} className="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/40 transition">
            <ChevronLeft className="w-6 h-6" />
          </button>
          <button onClick={goToNext} className="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/40 transition">
            <ChevronRight className="w-6 h-6" />
          </button>
        </>
      )}

      {banners.length > 1 && (
        <div className="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2">
          {banners.map((_, index) => (
            <button
              key={index}
              onClick={() => setCurrent(index)}
              className={`w-3 h-3 rounded-full transition ${index === current ? 'bg-white' : 'bg-white/50'}`}
            />
          ))}
        </div>
      )}
    </div>
  );
}
