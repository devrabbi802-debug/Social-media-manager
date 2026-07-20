import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Phone, Lock, ArrowLeft, LogIn, UserPlus, Globe } from 'lucide-react';

export default function Auth() {
  const [isLogin, setIsLogin] = useState(true);
  const [phone, setPhone] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const handleSubmit = (e) => {
    e.preventDefault();
    setError('');

    if (phone.length < 11) {
      setError('Please enter a valid phone number');
      return;
    }
    if (password.length < 6) {
      setError('Password must be at least 6 characters');
      return;
    }

    if (isLogin) {
      navigate('/');
    } else {
      setIsLogin(true);
      setPassword('');
      setPhone('');
    }
  };

  return (
    <div className="pt-20 min-h-screen flex items-center justify-center">
      <div className="w-full max-w-sm mx-auto px-4 py-12">
        <div className="text-center mb-8">
          <h1 className="text-2xl font-bold tracking-tight mb-2">
            {isLogin ? 'Welcome Back' : 'Create Account'}
          </h1>
          <p className="text-sm text-gray-400">
            {isLogin ? 'Sign in with your phone number' : 'Register with your phone number'}
          </p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="relative">
            <Phone className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              type="tel"
              value={phone}
              onChange={(e) => setPhone(e.target.value)}
              placeholder="Phone Number"
              required
              className="w-full border border-gray-200 pl-10 pr-4 py-3 text-sm focus:outline-none focus:border-gray-900 transition"
            />
          </div>

          <div className="relative">
            <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="Password"
              required
              className="w-full border border-gray-200 pl-10 pr-4 py-3 text-sm focus:outline-none focus:border-gray-900 transition"
            />
          </div>

          {error && (
            <p className="text-xs text-red-500">{error}</p>
          )}

          {isLogin && (
            <div className="text-right">
              <button type="button" className="text-xs text-gray-400 hover:text-gray-900 transition">
                Forgot password?
              </button>
            </div>
          )}

          <button
            type="submit"
            className="w-full bg-gray-900 text-white py-3 text-sm font-medium hover:bg-gray-800 transition uppercase tracking-wider flex items-center justify-center gap-2"
          >
            {isLogin ? <LogIn className="w-4 h-4" /> : <UserPlus className="w-4 h-4" />}
            {isLogin ? 'Login' : 'Register'}
          </button>
        </form>

        {isLogin && (
          <div className="mt-4">
            <div className="relative">
              <div className="absolute inset-0 flex items-center">
                <div className="w-full border-t border-gray-100" />
              </div>
              <div className="relative flex justify-center text-xs">
                <span className="bg-white px-3 text-gray-400">or</span>
              </div>
            </div>
            <button
              type="button"
              onClick={() => navigate('/')}
              className="w-full mt-4 border border-gray-200 text-gray-600 py-3 text-sm font-medium hover:border-gray-900 hover:text-gray-900 transition flex items-center justify-center gap-2"
            >
              <Globe className="w-4 h-4" />
              Continue with Google
            </button>
          </div>
        )}

        <div className="mt-6 text-center">
          <button
            onClick={() => { setIsLogin(!isLogin); setError(''); }}
            className="text-sm text-gray-400 hover:text-gray-900 transition"
          >
            {isLogin ? "Don't have an account? Register" : 'Already have an account? Sign In'}
          </button>
        </div>

        <div className="mt-6 text-center">
          <button
            onClick={() => navigate('/')}
            className="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-gray-900 transition"
          >
            <ArrowLeft className="w-3 h-3" />
            Back to Store
          </button>
        </div>
      </div>
    </div>
  );
}
