'use client';

import { useState, useEffect } from 'react';
import { useAuth } from '@/context/AuthContext';
import { api } from '@/lib/api';
import { Icon } from '@/components/shared/Icons';
import PasswordInput from '@/components/shared/PasswordInput';
import Alert from '@/components/shared/Alert';

export default function AdminProfilPage() {
  const { user, refreshUser } = useAuth();

  const [nama, setNama] = useState('');
  const [email, setEmail] = useState('');
  const [passwordLama, setPasswordLama] = useState('');
  const [passwordBaru, setPasswordBaru] = useState('');

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  useEffect(() => {
    async function loadAkun() {
      try {
        setLoading(true);
        const data = await api.get('/api/akun');
        setNama(data.nama || '');
        setEmail(data.email || '');
      } catch (err) {
        setError(err.message || 'Gagal memuat profil akun admin.');
      } finally {
        setLoading(false);
      }
    }
    loadAkun();
  }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');

    if (!nama.trim()) {
      setError('Nama lengkap wajib diisi.');
      return;
    }

    if (passwordBaru && !passwordLama) {
      setError('Masukkan password lama untuk mengubah password.');
      return;
    }

    if (passwordBaru && passwordBaru.length < 8) {
      setError('Password baru minimal 8 karakter.');
      return;
    }

    setSaving(true);

    try {
      // 1. Update nama admin
      await api.put('/api/akun', { nama: nama.trim() });

      // 2. Update password jika diisi
      if (passwordBaru && passwordLama) {
        await api.put('/api/akun/password', {
          password_lama: passwordLama,
          password_baru: passwordBaru,
        });
        setPasswordLama('');
        setPasswordBaru('');
      }

      await refreshUser();
      setSuccess('Profil admin berhasil diperbarui.');
    } catch (err) {
      setError(err.message || 'Gagal memperbarui profil admin.');
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="card form auth" style={{ maxWidth: '580px' }}>
      <h1>Profil Saya</h1>
      <p className="muted">Peran: Administrator</p>

      <Alert type="danger" message={error} onClose={() => setError('')} />
      <Alert type="success" message={success} onClose={() => setSuccess('')} />

      {loading ? (
        <p className="muted">Memuat data...</p>
      ) : (
        <form onSubmit={handleSubmit}>
          <label htmlFor="nama">Nama Lengkap</label>
          <div className="fld">
            <Icon name="orang" />
            <input
              id="nama"
              name="nama"
              required
              maxLength={100}
              value={nama}
              onChange={(e) => setNama(e.target.value)}
              placeholder="Masukkan nama lengkap"
            />
          </div>

          <label htmlFor="email">Email</label>
          <div className="fld">
            <Icon name="surat" />
            <input id="email" value={email} readOnly style={{ backgroundColor: '#f7f2f2' }} />
          </div>

          <h2 className="sub">Ganti Password (opsional)</h2>
          <label htmlFor="pw-lama">Password Lama</label>
          <PasswordInput
            id="pw-lama"
            name="password_lama"
            required={false}
            value={passwordLama}
            onChange={(e) => setPasswordLama(e.target.value)}
            placeholder="Masukkan password lama"
          />

          <label htmlFor="pw-baru">Password Baru</label>
          <PasswordInput
            id="pw-baru"
            name="password_baru"
            required={false}
            minLength={8}
            value={passwordBaru}
            onChange={(e) => setPasswordBaru(e.target.value)}
            placeholder="Masukkan password baru (min. 8 karakter)"
          />

          <button className="btn blok" type="submit" disabled={saving}>
            {saving ? 'Menyimpan...' : 'Simpan Perubahan'}
          </button>
        </form>
      )}
    </div>
  );
}
