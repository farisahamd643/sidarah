'use client';

import { useAuth } from '@/context/AuthContext';
import { useRouter } from 'next/navigation';
import { useEffect } from 'react';
import Loading from './Loading';

export default function ProtectedRoute({ children, roleRequired }) {
  const { user, loading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!loading) {
      if (!user) {
        router.push('/login');
      } else if (roleRequired && user.role !== roleRequired) {
        if (user.role === 'admin') {
          router.push('/admin/dashboard');
        } else {
          router.push('/dashboard');
        }
      }
    }
  }, [user, loading, roleRequired, router]);

  if (loading) {
    return <Loading fullScreen text="Memeriksa autentikasi..." />;
  }

  if (!user || (roleRequired && user.role !== roleRequired)) {
    return null;
  }

  return children;
}
