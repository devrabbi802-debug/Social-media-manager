export const orders = [
  { id: '#ORD-2026-001', date: '15 Jun 2026', status: 'delivered', total: 5197, items: 3, payment: 'SSLCOMMERZ' },
  { id: '#ORD-2026-002', date: '28 May 2026', status: 'shipped', total: 2599, items: 1, payment: 'COD' },
  { id: '#ORD-2026-003', date: '10 May 2026', status: 'processing', total: 3798, items: 2, payment: 'SSLCOMMERZ' },
  { id: '#ORD-2026-004', date: '22 Apr 2026', status: 'cancelled', total: 1299, items: 1, payment: 'COD' },
  { id: '#ORD-2026-005', date: '05 Apr 2026', status: 'delivered', total: 8396, items: 4, payment: 'SSLCOMMERZ' },
  { id: '#ORD-2026-006', date: '18 Mar 2026', status: 'delivered', total: 1800, items: 1, payment: 'COD' },
];

export const wishlistItems = [
  { id: 3, name: 'Oversized Hoodie', slug: 'oversized-hoodie', base_price: 1800, discount_price: null, effective_price: 1800, image: 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&q=80', inStock: true },
  { id: 10, name: 'Bomber Jacket', slug: 'bomber-jacket', base_price: 4800, discount_price: 3800, effective_price: 3800, image: 'https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?w=600&q=80', inStock: true },
  { id: 16, name: 'Leather Belt', slug: 'leather-belt', base_price: 900, discount_price: 699, effective_price: 699, image: 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&q=80', inStock: false },
];

export const addresses = [
  { id: 1, label: 'Home', name: 'Rafiq Hasan', phone: '01712-345678', address: 'House 12, Road 5, Block C', city: 'Dhaka', district: 'Mirpur', zip: '1216', isDefault: true },
  { id: 2, label: 'Office', name: 'Rafiq Hasan', phone: '01798-765432', address: 'Level 8, BTMA Bhaban', city: 'Dhaka', district: 'Motijheel', zip: '1000', isDefault: false },
];

export const profile = {
  name: 'Rafiq Hasan',
  email: 'rafiq.hasan@gmail.com',
  phone: '01712-345678',
  memberSince: 'January 2026',
  avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&q=80',
};

export const recentReviews = [
  { id: 1, product: 'Classic Cotton T-Shirt', slug: 'classic-cotton-tshirt', rating: 5, date: '12 Jun 2026', text: 'Great quality fabric! Perfect fit.' },
  { id: 2, product: 'Slim Fit Denim Jeans', slug: 'slim-fit-denim-jeans', rating: 4, date: '30 May 2026', text: 'Nice jeans but slightly long for me.' },
];

export const orderTracking = {
  '#ORD-2026-001': { carrier: 'Sundarban Courier', trackingId: 'BD20260615001', estimated: '18 Jun 2026', steps: [
    { label: 'Order Placed', date: '15 Jun 2026, 10:30 AM', completed: true },
    { label: 'Payment Confirmed', date: '15 Jun 2026, 10:35 AM', completed: true },
    { label: 'Processing', date: '16 Jun 2026, 09:00 AM', completed: true },
    { label: 'Shipped', date: '17 Jun 2026, 02:15 PM', completed: true },
    { label: 'Out for Delivery', date: '18 Jun 2026, 08:00 AM', completed: true },
    { label: 'Delivered', date: '18 Jun 2026, 12:45 PM', completed: true },
  ]},
  '#ORD-2026-002': { carrier: 'Pathao Courier', trackingId: 'PAT2026-05821', estimated: '30 May 2026', steps: [
    { label: 'Order Placed', date: '28 May 2026, 03:20 PM', completed: true },
    { label: 'Payment Confirmed', date: '28 May 2026, 03:25 PM', completed: true },
    { label: 'Processing', date: '29 May 2026, 10:00 AM', completed: true },
    { label: 'Shipped', date: '30 May 2026, 09:30 AM', completed: true },
    { label: 'Out for Delivery', date: null, completed: false },
    { label: 'Delivered', date: null, completed: false },
  ]},
  '#ORD-2026-003': { carrier: 'E-Desh Courier', trackingId: 'ED2026061003', estimated: '13 May 2026', steps: [
    { label: 'Order Placed', date: '10 May 2026, 11:45 AM', completed: true },
    { label: 'Payment Confirmed', date: '10 May 2026, 11:50 AM', completed: true },
    { label: 'Processing', date: '12 May 2026, 08:15 AM', completed: true },
    { label: 'Shipped', date: null, completed: false },
    { label: 'Out for Delivery', date: null, completed: false },
    { label: 'Delivered', date: null, completed: false },
  ]},
};

export const statusConfig = {
  delivered: { icon: 'CheckCircle', color: 'text-green-600', bg: 'bg-green-50', label: 'Delivered' },
  shipped: { icon: 'Truck', color: 'text-blue-600', bg: 'bg-blue-50', label: 'Shipped' },
  processing: { icon: 'RefreshCw', color: 'text-amber-600', bg: 'bg-amber-50', label: 'Processing' },
  cancelled: { icon: 'XCircle', color: 'text-red-600', bg: 'bg-red-50', label: 'Cancelled' },
};
