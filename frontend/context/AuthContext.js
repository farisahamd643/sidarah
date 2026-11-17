'use client';

import { createContext, useContext, useState, useEffect } from 'react';
import { getToken, getUser, setToken, setUser, logout as clearAuth } from '@/lib/auth';
import { api } from '@/lib/api';
import { useRouter } from 'next/navigation';

const AuthContext = createContext({
  user: null,
  token: null,
  loading: true,
  login: () => {},
  logoutUser: () => {},
  refreshUser: async () => {},
});

export function AuthProvider({ children }) {
  const [user, setUserState] = useState(null);
  const [token, setTokenState] = useState(null);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  useEffect(() => {
    const savedToken = getToken();
    const savedUser = getUser();

    if (savedToken && savedUser) {
      setTokenState(savedToken);
      setUserState(savedUser);
    }
    setLoading(false);
  }, []);

  const login = (newToken, newUser) => {
    setToken(newToken);
    setUser(newUser);
    setTokenState(newToken);
    setUserState(newUser);
  };

  const logoutUser = () => {
    clearAuth();
    setTokenState(null);
    setUserState(null);
    router.push('/login');
  };

  const refreshUser = async () => {
    try {
      const data = await api.get('/api/akun');
      if (data) {
        setUser(data);
        setUserState(data);
      }
    } catch {
      // Abaikan jika gagal refresh akun
    }
  };

  return (
    <AuthContext.Provider value={{ user, token, loading, login, logoutUser, refreshUser }}>
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => useContext(AuthContext);
