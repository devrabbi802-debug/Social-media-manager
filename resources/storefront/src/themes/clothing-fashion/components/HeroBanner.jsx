import React, { useState, useEffect, useCallback } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

const staticBanners = [
  {
    id: 1,
    title: 'Summer Collection 2026',
    subtitle: 'Get up to 40% off on trending styles',
    btn_text: 'Shop Now',
    link: '/products',
    image: 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1920&q=80',
    align: 'left',
  },
  {
    id: 2,
    title: 'New Arrivals',
    subtitle: 'Streetwear & Casual Fashion',
    btn_text: 'Explore',
    link: '/products',
    image: 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1920&q=80',
    align: 'center',
  },
  {
    id: 3,
    title: 'Premium Denim',
    subtitle: 'Limited edition collection',
    btn_text: 'Discover',
    link: '/products',
    image: 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=1920&q=80',
    align: 'right',
  },
];

export default function HeroBanner({ banners }) {
  const slides = banners?.length > 0 ? banners : staticBanners;
  const [current, setCurrent] = useState(0);
  const [touchStart, setTouchStart] = useState(0);

  const goTo = useCallback((index) => {
    setCurrent((index + slides.length) % slides.length);
  }, [slides.length]);

  const goToPrev = useCallback(() => goTo(current - 1), [goTo, current]);
  const goToNext = useCallback(() => goTo(current + 1), [goTo, current]);

  useEffect(() => {
    if (slides.length <= 1) return;
    const timer = setInterval(goToNext, 5000);
    return () => clearInterval(timer);
  }, [goToNext, slides.length]);

  const handleTouchStart = (e) => setTouchStart(e.touches[0].clientX);
  const handleTouchEnd = (e) => {
    const diff = touchStart - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 50) {
      diff > 0 ? goToNext() : goToPrev();
    }
  };

  return (
    <div
      className="relative w-full h-[80vh] min-h-[500px] max-h-[800px] overflow-hidden bg-gray-900"
      onTouchStart={handleTouchStart}
      onTouchEnd={handleTouchEnd}
    >
      {slides.map((slide, index) => (
        <div
          key={slide.id || index}
          className={`absolute inset-0 transition-all duration-700 ease-in-out ${
            index === current ? 'opacity-100 scale-100' : 'opacity-0 scale-105'
          }`}
        >
          <div
            className="w-full h-full bg-cover bg-center bg-gray-900"
            style={{ backgroundImage: `url(${slide.image})` }}
          />
          <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent" />

          <div
            className={`absolute inset-0 flex items-center ${
              slide.align === 'left'
                ? 'justify-start'
                : slide.align === 'right'
                ? 'justify-end'
                : 'justify-center'
            }`}
          >
            <div
              className={`text-white px-6 md:px-12 max-w-2xl pt-20 ${
                slide.align === 'center' ? 'text-center' : ''
              } ${index === current ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'}
              transition-all duration-700 delay-200`}
            >
              <p className="text-sm md:text-base uppercase tracking-[0.2em] mb-3 font-light text-white/80">
                {slide.align === 'left' ? 'New Season' : slide.align === 'right' ? 'Trending Now' : 'Featured'}
              </p>
              <h2 className="text-3xl md:text-5xl lg:text-6xl font-bold mb-4 leading-tight">
                {slide.title}
              </h2>
              <p className="text-base md:text-lg mb-8 text-white/80 max-w-lg mx-auto">
                {slide.subtitle}
              </p>
              {slide.btn_text && (
                <a
                  href={slide.link || '/products'}
                  className="inline-flex items-center gap-2 bg-white text-gray-900 px-8 py-3 rounded-full font-medium hover:bg-gray-100 transition-all hover:shadow-xl hover:scale-105"
                >
                  {slide.btn_text}
                  <ChevronRight className="w-4 h-4" />
                </a>
              )}
            </div>
          </div>
        </div>
      ))}

      {slides.length > 1 && (
        <>
          <button
            onClick={goToPrev}
            className="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white/30 transition border border-white/20 group"
          >
            <ChevronLeft className="w-5 h-5 text-white group-hover:scale-110 transition" />
          </button>
          <button
            onClick={goToNext}
            className="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white/30 transition border border-white/20 group"
          >
            <ChevronRight className="w-5 h-5 text-white group-hover:scale-110 transition" />
          </button>
        </>
      )}

      <div className="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center gap-2">
        {slides.map((_, index) => (
          <button
            key={index}
            onClick={() => goTo(index)}
            className={`transition-all duration-300 ${
              index === current
                ? 'w-8 h-2 bg-white'
                : 'w-2 h-2 bg-white/40 hover:bg-white/70'
            } rounded-full`}
          />
        ))}
      </div>
    </div>
  );
}
