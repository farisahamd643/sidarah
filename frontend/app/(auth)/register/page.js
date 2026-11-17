'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { api } from '@/lib/api';
import { Icon } from '@/components/shared/Icons';
import PasswordInput from '@/components/shared/PasswordInput';
import Alert from '@/components/shared/Alert';

export default function RegisterPage() {
  const router = useRouter();

  const [form, setForm] = useState({
    nama_lengkap: '',
    nik: '',
    tanggal_lahir: '',
    jenis_kelamin: '',
    no_hp: '',
    email: '',
    alamat: '',
    password: '',
    password2: '',
    setuju: false,
  });

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setForm((prev) => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value,
    }));
    if (error) setError('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');

    // Validasi frontend
    if (form.password.length < 8) {
      setError('Password minimal 8 karakter.');
      return;
    }
    if (form.password !== form.password2) {
      setError('Konfirmasi password tidak sama.');
      return;
    }
    if (form.nik && !/^\d{16}$/.test(form.nik.trim())) {
      setError('NIK harus berupa 16 digit angka.');
      return;
    }
    if (!form.setuju) {
      setError('Anda harus menyetujui Syarat & Ketentuan dan Kebijakan Privasi.');
      return;
    }

    setLoading(true);

    try {
      await api.post('/api/auth/register', {
        nama: form.nama_lengkap.trim(),
        email: form.email.toLowerCase().trim(),
        password: form.password,
        jenis_kelamin: form.jenis_kelamin,
        tanggal_lahir: form.tanggal_lahir,
        nik: form.nik.trim() || undefined,
        no_hp: form.no_hp.trim() || undefined,
        alamat: form.alamat.trim() || undefined,
      });

      setSuccess('Registrasi berhasil! Silakan login untuk melanjutkan.');
      setTimeout(() => {
        router.push('/login');
      }, 1500);
    } catch (err) {
      setError(err.message || 'Registrasi gagal. Coba lagi.');
    } finally {
      setLoading(false);
    }
  };

  const today = new Date().toISOString().split('T')[0];

  return (
    <div className="card form auth">
      <h1>Buat Akun Pendonor</h1>
      <p className="muted">Isi data diri Anda. Golongan darah bisa dilengkapi nanti di menu Profil.</p>

      <Alert type="danger" message={error} onClose={() => setError('')} />
      <Alert type="success" message={success} />

      <form onSubmit={handleSubmit}>
        <label htmlFor="nama_lengkap">Nama Lengkap</label>
        <div className="fld">
          <Icon name="orang" />
          <input
            id="nama_lengkap"
            name="nama_lengkap"
            required
            maxLength={100}
            placeholder="Masukkan nama lengkap"
            value={form.nama_lengkap}
            onChange={handleChange}
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
            placeholder="Masukkan NIK (16 digit)"
            value={form.nik}
            onChange={handleChange}
          />
        </div>

        <label htmlFor="tanggal_lahir">Tanggal Lahir</label>
        <div className="fld">
          <Icon name="kalender" />
          <input
            type="date"
            id="tanggal_lahir"
            name="tanggal_lahir"
            required
            max={today}
            value={form.tanggal_lahir}
            onChange={handleChange}
          />
        </div>

        <label htmlFor="jenis_kelamin">Jenis Kelamin</label>
        <div className="fld">
          <Icon name="orang" />
          <select
            id="jenis_kelamin"
            name="jenis_kelamin"
            required
            value={form.jenis_kelamin}
            onChange={handleChange}
          >
            <option value="">Pilih jenis kelamin</option>
            <option value="L">Laki-laki</option>
            <option value="P">Perempuan</option>
          </select>
        </div>

        <label htmlFor="no_hp">Nomor HP</label>
        <div className="fld">
          <Icon name="telp" />
          <input
            type="tel"
            id="no_hp"
            name="no_hp"
            maxLength={20}
            placeholder="Masukkan nomor handphone"
            value={form.no_hp}
            onChange={handleChange}
          />
        </div>

        <label htmlFor="email">Email</label>
        <div className="fld">
          <Icon name="surat" />
          <input
            type="email"
            id="email"
            name="email"
            required
            maxLength={100}
            placeholder="Masukkan email"
            value={form.email}
            onChange={handleChange}
          />
        </div>

        <label htmlFor="alamat">Alamat</label>
        <div className="fld ta">
          <Icon name="pin" />
          <textarea
            id="alamat"
            name="alamat"
            rows={3}
            placeholder="Masukkan alamat lengkap"
            value={form.alamat}
            onChange={handleChange}
          />
        </div>

        <label htmlFor="pw1">Password (min. 8 karakter)</label>
        <PasswordInput
          id="pw1"
          name="password"
          required
          minLength={8}
          placeholder="Masukkan password"
          value={form.password}
          onChange={handleChange}
        />

        <label htmlFor="pw2">Konfirmasi Password</label>
        <PasswordInput
          id="pw2"
          name="password2"
          required
          minLength={8}
          placeholder="Masukkan konfirmasi password"
          value={form.password2}
          onChange={handleChange}
        />

        <label className="cek">
          <input
            type="checkbox"
            name="setuju"
            checked={form.setuju}
            onChange={handleChange}
            required
          />
          <span>
            Saya menyetujui{' '}
            <Link href="/syarat" target="_blank" rel="noopener">
              Syarat &amp; Ketentuan dan Kebijakan Privasi
            </Link>
          </span>
        </label>

        <button className="btn blok" type="submit" disabled={loading}>
          {loading ? 'Mendaftarkan...' : 'Daftar Akun'}
        </button>
      </form>

      <p className="tautan muted">
        Sudah punya akun? <Link href="/login">Masuk di sini</Link>
      </p>
    </div>
  );
}
