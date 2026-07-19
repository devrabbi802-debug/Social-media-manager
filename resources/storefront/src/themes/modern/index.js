import React from 'react';
import Layout from './Layout';

const Home = React.lazy(() => import('./pages/Home'));
const Products = React.lazy(() => import('./pages/Products'));
const ProductDetail = React.lazy(() => import('./pages/ProductDetail'));
const Category = React.lazy(() => import('./pages/Category'));
const Brand = React.lazy(() => import('./pages/Brand'));
const NotFound = React.lazy(() => import('./pages/NotFound'));

export { Layout, Home, Products, ProductDetail, Category, Brand, NotFound };
