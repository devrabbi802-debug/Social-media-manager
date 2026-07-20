import React from 'react';
import { Truck, CreditCard, RefreshCw, Headphones } from 'lucide-react';

const features = [
  {
    icon: Truck,
    title: 'Cash on Delivery',
    desc: 'Cash on Delivery available for all Orders',
  },
  {
    icon: CreditCard,
    title: 'Flexible Payment',
    desc: 'Pay with multiple cards or MFS via SSLCOMMERZ.',
  },
  {
    icon: RefreshCw,
    title: '07 Day Returns',
    desc: 'Within 07 days for an exchange',
  },
  {
    icon: Headphones,
    title: 'Premium Support',
    desc: 'Outstanding premium support',
  },
];

export default function Features() {
  return (
    <section className="py-8 md:py-12 border-t border-gray-100">
      <div className="container mx-auto px-4">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
          {features.map((item, i) => {
            const Icon = item.icon;
            return (
              <div key={i} className="text-center group">
                <div className="w-12 h-12 mx-auto mb-3 flex items-center justify-center bg-gray-50 group-hover:bg-gray-900 transition-all duration-300">
                  <Icon className="w-5 h-5 text-gray-900 group-hover:text-white transition-all duration-300" />
                </div>
                <h3 className="text-sm font-bold text-gray-900 mb-1 uppercase tracking-wider">
                  {item.title}
                </h3>
                <p className="text-xs text-gray-500 leading-relaxed max-w-[200px] mx-auto">
                  {item.desc}
                </p>
              </div>
            );
          })}
        </div>
      </div>
    </section>
  );
}
