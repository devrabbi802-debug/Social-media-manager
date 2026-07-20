import React from 'react';
import Layout from './Layout';

const Home = React.lazy(() => import('./pages/Home'));
const Products = React.lazy(() => import('./pages/Products'));
const ProductDetail = React.lazy(() => import('./pages/ProductDetail'));
const Category = React.lazy(() => import('./pages/Category'));
const Brand = React.lazy(() => import('./pages/Brand'));
const Cart = React.lazy(() => import('./pages/Cart'));
const Checkout = React.lazy(() => import('./pages/Checkout'));
const Auth = React.lazy(() => import('./pages/Auth'));
const NotFound = React.lazy(() => import('./pages/NotFound'));

const DashboardLayout = React.lazy(() => import('./pages/dashboard/DashboardLayout'));
const DashboardHome = React.lazy(() => import('./pages/dashboard/Overview'));
const DashboardOrders = React.lazy(() => import('./pages/dashboard/Orders'));
const DashboardTracking = React.lazy(() => import('./pages/dashboard/Tracking'));
const DashboardWishlist = React.lazy(() => import('./pages/dashboard/Wishlist'));
const DashboardAddresses = React.lazy(() => import('./pages/dashboard/Addresses'));
const DashboardSettings = React.lazy(() => import('./pages/dashboard/Settings'));

export {
  Layout, Home, Products, ProductDetail, Category, Brand, Cart, Checkout, Auth,
  DashboardLayout, DashboardHome, DashboardOrders, DashboardTracking,
  DashboardWishlist, DashboardAddresses, DashboardSettings,
  NotFound,
};
