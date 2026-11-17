'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/context/AuthContext';
import { api } from '@/lib/api';
import { Icon } from '@/components/shared/Icons';
import PasswordInput from '@/components/shared/PasswordInput';
import Alert from '@/components/shared/Alert';

export default function LoginPage() {
  const { user, login, loading: authLoading } = useAuth();
  const router = useRouter();

  const [form, setForm] = useState({
    email: '',
    password: '',
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    if (!authLoading && user) {
      if (user.role === 'admin') {
        router.push('/admin/dashboard');
      } else {
        router.push('/dashboard');
      }
    }
  }, [user, authLoading, router]);

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
    if (error) setError('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const data = await api.post('/api/auth/login', {
        email: form.email.trim(),
        password: form.password,
      });

      login(data.token, data.user);

      if (data.user.role === 'admin') {
        router.push('/admin/dashboard');
      } else {
        router.push('/dashboard');
      }
    } catch (err) {
      setError(err.message || 'Login gagal. Periksa email dan password Anda.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="card form auth">
      <h1>Login SiDarah</h1>
      <p className="muted" style={{ marginBottom: '1.2rem' }}>
        Masuk ke akun pendonor atau administrator.
      </p>

      <Alert type="danger" message={error} onClose={() => setError('')} />

      <form onSubmit={handleSubmit}>
        <label htmlFor="email">Email</label>
        <div className="fld">
          <Icon name="surat" />
          <input
            type="email"
            id="email"
            name="email"
            value={form.email}
            onChange={handleChange}
            required
            autoFocus
            placeholder="Masukkan email"
          />
        </div>

        <label htmlFor="pw-login">Password</label>
        <PasswordInput
          id="pw-login"
          name="password"
          value={form.password}
          onChange={handleChange}
          required
          placeholder="Masukkan password"
        />

        <button className="btn blok" type="submit" disabled={loading}>
          {loading ? 'Memproses...' : 'Masuk'}
        </button>
      </form>

      <p className="tautan muted">
        Belum punya akun? <Link href="/register">Daftar di sini</Link>
      </p>
    </div>
  );
}
