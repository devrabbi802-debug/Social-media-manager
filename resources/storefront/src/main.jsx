import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App';
import './styles/globals.css';

const root = document.getElementById('root');
const container = document.createElement('div');
container.id = 'react-root';
root.appendChild(container);

ReactDOM.createRoot(container).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);
