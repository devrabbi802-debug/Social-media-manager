import React from 'react';

export default function Skeleton({ className }) {
  return (
    <div
      className={`bg-gradient-to-r from-gray-100 via-gray-200 to-gray-100 animate-pulse ${className}`}
    />
  );
}
