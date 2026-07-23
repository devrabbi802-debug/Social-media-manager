import React, { useState } from 'react';
import { useNavigate, useSearchParams, Link } from 'react-router-dom';
import { Mail, Lock, ArrowLeft, LogIn, UserPlus, Eye, EyeOff } from 'lucide-react';
import { useAuth } from '../../../contexts/AuthContext';

export default function Auth() {
  const { login, register } = useAuth();
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const redirect = searchParams.get('redirect') || '/dashboard';

  const [isLogin, setIsLogin] = useState(searchParams.get('tab') !== 'register');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState('');
  const [fieldErrors, setFieldErrors] = useState({});
  const [loading, setLoading] = useState(false);

  const validate = () => {
    const errs = {};
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) errs.email = 'Valid email required';
    if (password.length < 6) errs.password = 'Min 6 characters';
    if (!isLogin && password !== passwordConfirmation) errs.password_confirmation = 'Passwords do not match';
    setFieldErrors(errs);
    return Object.keys(errs).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    if (!validate()) return;

    setLoading(true);
    try {
      if (isLogin) {
        await login(email, password);
      } else {
        await register({ email, password, password_confirmation: passwordConfirmation });
      }
      navigate(redirect, { replace: true });
    } catch (err) {
      const data = err.response?.data;
      if (data?.errors) {
        const errs = {};
        Object.keys(data.errors).forEach((key) => {
          errs[key] = data.errors[key][0];
        });
        setFieldErrors(errs);
      } else {
        setError(data?.message || 'Something went wrong. Please try again.');
      }
    } finally {
      setLoading(false);
    }
  };

  const toggleMode = () => {
    setIsLogin(!isLogin);
    setError('');
    setFieldErrors({});
  };

  const inputClass = (field) =>
    `w-full border pl-10 pr-10 py-3 text-sm focus:outline-none transition ${
      fieldErrors[field] ? 'border-red-400' : 'border-gray-200 focus:border-gray-900'
    }`;

  return (
    <div className="pt-20 min-h-screen flex items-center justify-center bg-gray-50">
      <div className="w-full max-w-sm mx-auto px-4 py-12">
        <div className="text-center mb-8">
          <h1 className="text-2xl font-bold tracking-tight mb-2">
            {isLogin ? 'Welcome Back' : 'Create Account'}
          </h1>
          <p className="text-sm text-gray-400">
            {isLogin ? 'Sign in to your account' : 'Register a new account'}
          </p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-3 bg-white p-6 border border-gray-100 rounded-lg">
          <div className="relative">
            <Mail className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="Email Address"
              required
              className={inputClass('email')}
            />
            {fieldErrors.email && <p className="text-xs text-red-500 mt-1">{fieldErrors.email}</p>}
          </div>

          <div className="relative">
            <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              type={showPassword ? 'text' : 'password'}
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="Password"
              required
              className={inputClass('password')}
            />
            <button
              type="button"
              onClick={() => setShowPassword(!showPassword)}
              className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-900 transition"
            >
              {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
            </button>
            {fieldErrors.password && <p className="text-xs text-red-500 mt-1">{fieldErrors.password}</p>}
          </div>

          {!isLogin && (
            <div className="relative">
              <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                type={showPassword ? 'text' : 'password'}
                value={passwordConfirmation}
                onChange={(e) => setPasswordConfirmation(e.target.value)}
                placeholder="Confirm Password"
                required
                className={inputClass('password_confirmation')}
              />
              {fieldErrors.password_confirmation && <p className="text-xs text-red-500 mt-1">{fieldErrors.password_confirmation}</p>}
            </div>
          )}

          {error && <p className="text-xs text-red-500 text-center">{error}</p>}

          {isLogin && (
            <div className="text-right">
              <Link to="/forgot-password" className="text-xs text-gray-400 hover:text-gray-900 transition">
                Forgot password?
              </Link>
            </div>
          )}

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-gray-900 text-white py-3 text-sm font-medium hover:bg-gray-800 transition uppercase tracking-wider flex items-center justify-center gap-2 disabled:opacity-50"
          >
            {loading ? (
              <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
            ) : isLogin ? (
              <LogIn className="w-4 h-4" />
            ) : (
              <UserPlus className="w-4 h-4" />
            )}
            {loading ? (isLogin ? 'Signing in...' : 'Creating account...') : isLogin ? 'Login' : 'Register'}
          </button>
        </form>

        <div className="mt-6 text-center">
          <button
            onClick={toggleMode}
            className="text-sm text-gray-400 hover:text-gray-900 transition"
          >
            {isLogin ? "Don't have an account? Register" : 'Already have an account? Sign In'}
          </button>
        </div>

        <div className="mt-4 text-center">
          <Link
            to="/"
            className="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-gray-900 transition"
          >
            <ArrowLeft className="w-3 h-3" />
            Back to Store
          </Link>
        </div>
      </div>
    </div>
  );
}
