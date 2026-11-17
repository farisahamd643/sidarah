'use client';

import { useState, useEffect } from 'react';
import ProtectedRoute from '@/components/shared/ProtectedRoute';
import { useAuth } from '@/context/AuthContext';
import { api } from '@/lib/api';
import { Icon } from '@/components/shared/Icons';
import PasswordInput from '@/components/shared/PasswordInput';
import Alert from '@/components/shared/Alert';

const GOLONGAN = ['A', 'B', 'AB', 'O'];
const RHESUS = ['+', '-'];

export default function ProfilPage() {
  const { refreshUser } = useAuth();

  const [form, setForm] = useState({
    nama_lengkap: '',
    nik: '',
    tanggal_lahir: '',
    jenis_kelamin: '',
    no_hp: '',
    alamat: '',
    email: '',
    golongan_darah: '',
    rhesus: '',
  });

  const [passwordLama, setPasswordLama] = useState('');
  const [passwordBaru, setPasswordBaru] = useState('');

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  useEffect(() => {
    async function loadProfil() {
      try {
        setLoading(true);
        const data = await api.get('/api/profil');
        setForm({
          nama_lengkap: data.nama_lengkap || '',
          nik: data.nik || '',
          tanggal_lahir: data.tanggal_lahir ? data.tanggal_lahir.split('T')[0] : '',
          jenis_kelamin: data.jenis_kelamin || '',
          no_hp: data.no_hp || '',
          alamat: data.alamat || '',
          email: data.email || '',
          golongan_darah: data.golongan_darah || '',
          rhesus: data.rhesus || '',
        });
      } catch (err) {
        setError(err.message || 'Gagal memuat data profil.');
      } finally {
        setLoading(false);
      }
    }
    loadProfil();
  }, []);

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
    if (error) setError('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');

    if (form.nik && !/^\d{16}$/.test(form.nik.trim())) {
      setError('NIK harus berupa 16 digit angka.');
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
      // 1. Simpan perubahan profil
      await api.put('/api/profil', {
        nama_lengkap: form.nama_lengkap.trim(),
        nik: form.nik.trim() || null,
        golongan_darah: form.golongan_darah || null,
        rhesus: form.rhesus || null,
        no_hp: form.no_hp.trim() || null,
        alamat: form.alamat.trim() || null,
      });

      // 2. Jika ada pergantian password, panggil endpoint password
      if (passwordBaru && passwordLama) {
        await api.put('/api/akun/password', {
          password_lama: passwordLama,
          password_baru: passwordBaru,
        });
        setPasswordLama('');
        setPasswordBaru('');
      }

      await refreshUser();
      setSuccess('Profil berhasil diperbarui.');
    } catch (err) {
      setError(err.message || 'Gagal memperbarui profil.');
    } finally {
      setSaving(false);
    }
  };

  return (
    <ProtectedRoute roleRequired="pendonor">
      <div className="card form auth">
        <h1>Profil Saya</h1>
        <p className="muted">
          Peran: pendonor
          {!form.golongan_darah && (
            <>
              {' '}· <b style={{ color: 'var(--r)' }}>Golongan darah belum diisi, lengkapi di bawah.</b>
            </>
          )}
        </p>

        <Alert type="danger" message={error} onClose={() => setError('')} />
        <Alert type="success" message={success} onClose={() => setSuccess('')} />

        {loading ? (
          <p className="muted">Memuat data profil...</p>
        ) : (
          <form onSubmit={handleSubmit}>
            <label htmlFor="nama_lengkap">Nama Lengkap</label>
            <div className="fld">
              <Icon name="orang" />
              <input
                id="nama_lengkap"
                name="nama_lengkap"
                required
                maxLength={100}
                value={form.nama_lengkap}
                onChange={handleChange}
                placeholder="Masukkan nama lengkap"
              />
            </div>

            <label htmlFor="nik">NIK (16 digit angka)</label>
            <div className="fld">
              <Icon name="id" />
              <input
                id="nik"
                name="nik"
                inputMode="numeric"
                maxLength={16}
                pattern="[0-9]{16}"
                title="NIK terdiri dari 16 digit angka"
                value={form.nik}
                onChange={handleChange}
                placeholder="Masukkan NIK"
              />
            </div>

            <label htmlFor="tanggal_lahir">Tanggal Lahir</label>
            <div className="fld">
              <Icon name="kalender" />
              <input
                type="date"
                id="tanggal_lahir"
                name="tanggal_lahir"
                value={form.tanggal_lahir}
                disabled
                style={{ backgroundColor: '#f7f2f2' }}
              />
            </div>

            <label htmlFor="jenis_kelamin">Jenis Kelamin</label>
            <div className="fld">
              <Icon name="orang" />
              <input
                id="jenis_kelamin"
                value={form.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'}
                disabled
                style={{ backgroundColor: '#f7f2f2' }}
              />
            </div>

            <label htmlFor="no_hp">Nomor HP</label>
            <div className="fld">
              <Icon name="telp" />
              <input
                type="tel"
                id="no_hp"
                name="no_hp"
                maxLength={20}
                value={form.no_hp}
                onChange={handleChange}
                placeholder="Masukkan nomor handphone"
              />
            </div>

            <label htmlFor="email">Email</label>
            <div className="fld">
              <Icon name="surat" />
              <input id="email" value={form.email} readOnly />
            </div>

            <label htmlFor="alamat">Alamat</label>
            <div className="fld ta">
              <Icon name="pin" />
              <textarea
                id="alamat"
                name="alamat"
                rows={3}
                value={form.alamat}
                onChange={handleChange}
                placeholder="Masukkan alamat lengkap"
              />
            </div>

            <h2 className="sub">Data Donor</h2>
            <div className="row">
              <div>
                <label htmlFor="golongan_darah">Golongan Darah</label>
                <div className="fld">
                  <Icon name="darah" />
                  <select
                    id="golongan_darah"
                    name="golongan_darah"
                    value={form.golongan_darah}
                    onChange={handleChange}
                  >
                    <option value="">Belum diisi</option>
                    {GOLONGAN.map((g) => (
                      <option key={g} value={g}>
                        {g}
                      </option>
                    ))}
                  </select>
                </div>
              </div>
              <div>
                <label htmlFor="rhesus">Rhesus</label>
                <div className="fld">
                  <Icon name="darah" />
                  <select
                    id="rhesus"
                    name="rhesus"
                    value={form.rhesus}
                    onChange={handleChange}
                  >
                    <option value="">Belum diisi</option>
                    {RHESUS.map((r) => (
                      <option key={r} value={r}>
                        {r}
                      </option>
                    ))}
                  </select>
                </div>
              </div>
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
              {saving ? 'Menyimpan Perubahan...' : 'Simpan Perubahan'}
            </button>
          </form>
        )}
      </div>
    </ProtectedRoute>
  );
}
