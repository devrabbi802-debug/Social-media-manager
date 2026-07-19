import React from 'react';
import { Facebook, Instagram, Youtube, Phone, Mail, MapPin } from 'lucide-react';

export default function Footer({ config }) {
  const {
    contact = {},
    social = {},
    footer = {},
  } = config || {};

  return (
    <footer className="bg-footerBg text-gray-700">
      <div className="container mx-auto px-4 py-10">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {/* About */}
          <div>
            <h3 className="text-lg font-bold mb-4 text-gray-900">About Us</h3>
            {footer.logo && (
              <img src={footer.logo} alt="Footer Logo" className="h-10 mb-4" />
            )}
            <p className="text-sm text-gray-600 leading-relaxed">
              {footer.about_text || 'Your one-stop shop for quality products.'}
            </p>
          </div>

          {/* Contact */}
          <div>
            <h3 className="text-lg font-bold mb-4 text-gray-900">Contact Info</h3>
            <ul className="space-y-3 text-sm">
              {contact.phone && (
                <li className="flex items-center space-x-2">
                  <Phone className="w-4 h-4 text-primary" />
                  <span>{contact.phone}</span>
                </li>
              )}
              {contact.email && (
                <li className="flex items-center space-x-2">
                  <Mail className="w-4 h-4 text-primary" />
                  <span>{contact.email}</span>
                </li>
              )}
              {contact.address && (
                <li className="flex items-start space-x-2">
                  <MapPin className="w-4 h-4 text-primary mt-0.5" />
                  <span>{contact.address}</span>
                </li>
              )}
            </ul>
          </div>

          {/* Social */}
          <div>
            <h3 className="text-lg font-bold mb-4 text-gray-900">Follow Us</h3>
            <div className="flex space-x-3">
              {social.facebook && (
                <a href={social.facebook} target="_blank" rel="noopener noreferrer" className="w-10 h-10 border border-gray-300 rounded flex items-center justify-center hover:bg-primary hover:text-white hover:border-primary transition">
                  <Facebook className="w-5 h-5" />
                </a>
              )}
              {social.instagram && (
                <a href={social.instagram} target="_blank" rel="noopener noreferrer" className="w-10 h-10 border border-gray-300 rounded flex items-center justify-center hover:bg-primary hover:text-white hover:border-primary transition">
                  <Instagram className="w-5 h-5" />
                </a>
              )}
              {social.youtube && (
                <a href={social.youtube} target="_blank" rel="noopener noreferrer" className="w-10 h-10 border border-gray-300 rounded flex items-center justify-center hover:bg-primary hover:text-white hover:border-primary transition">
                  <Youtube className="w-5 h-5" />
                </a>
              )}
              {social.whatsapp && (
                <a href={`https://wa.me/${social.whatsapp.replace(/[^0-9]/g, '')}`} target="_blank" rel="noopener noreferrer" className="w-10 h-10 border border-gray-300 rounded flex items-center justify-center hover:bg-green-500 hover:text-white hover:border-green-500 transition">
                  <span className="text-lg font-bold">W</span>
                </a>
              )}
            </div>
          </div>
        </div>

        <div className="border-t border-gray-300 mt-8 pt-6 text-center text-sm text-gray-500">
          {footer.copyright || `© ${new Date().getFullYear()} All rights reserved.`}
        </div>
      </div>
    </footer>
  );
}
