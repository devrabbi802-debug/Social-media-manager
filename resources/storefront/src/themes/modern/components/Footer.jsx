import React from 'react';
import { Facebook, Instagram, Youtube, Phone, Mail, MapPin } from 'lucide-react';

export default function Footer({ config }) {
  const {
    contact = {},
    social = {},
    footer = {},
  } = config || {};

  return (
    <footer className="bg-footerBg text-white">
      <div className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div className="col-span-1 md:col-span-2">
            <h3 className="text-lg font-semibold mb-4">{footer.logo ? '' : 'About Us'}</h3>
            {footer.logo && (
              <img src={footer.logo} alt="Footer Logo" className="h-10 mb-4" />
            )}
            <p className="text-gray-300 text-sm">
              {footer.about_text || 'Your one-stop shop for quality products.'}
            </p>
          </div>

          <div>
            <h3 className="text-lg font-semibold mb-4">Contact</h3>
            <ul className="space-y-2 text-sm text-gray-300">
              {contact.phone && (
                <li className="flex items-center space-x-2">
                  <Phone className="w-4 h-4" />
                  <span>{contact.phone}</span>
                </li>
              )}
              {contact.email && (
                <li className="flex items-center space-x-2">
                  <Mail className="w-4 h-4" />
                  <span>{contact.email}</span>
                </li>
              )}
              {contact.address && (
                <li className="flex items-start space-x-2">
                  <MapPin className="w-4 h-4 mt-0.5" />
                  <span>{contact.address}</span>
                </li>
              )}
            </ul>
          </div>

          <div>
            <h3 className="text-lg font-semibold mb-4">Follow Us</h3>
            <div className="flex space-x-4">
              {social.facebook && (
                <a href={social.facebook} target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center hover:bg-primary transition">
                  <Facebook className="w-5 h-5" />
                </a>
              )}
              {social.instagram && (
                <a href={social.instagram} target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center hover:bg-primary transition">
                  <Instagram className="w-5 h-5" />
                </a>
              )}
              {social.youtube && (
                <a href={social.youtube} target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center hover:bg-primary transition">
                  <Youtube className="w-5 h-5" />
                </a>
              )}
              {social.whatsapp && (
                <a href={`https://wa.me/${social.whatsapp.replace(/[^0-9]/g, '')}`} target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center hover:bg-green-500 transition">
                  <span className="text-lg">W</span>
                </a>
              )}
            </div>
          </div>
        </div>

        <div className="border-t border-white/20 mt-8 pt-8 text-center text-sm text-gray-400">
          {footer.copyright || `© ${new Date().getFullYear()} All rights reserved.`}
        </div>
      </div>
    </footer>
  );
}
