'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/context/AuthContext';
import { api } from '@/lib/api';
import { tgl, jam } from '@/lib/formatters';
import PublicNavbar from '@/components/shared/PublicNavbar';

export default function HomePage() {
  const { user, loading: authLoading } = useAuth();
  const router = useRouter();
  const [jadwal, setJadwal] = useState([]);
  const [loading, setLoading] = useState(true);
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

  useEffect(() => {
    async function loadJadwal() {
      try {
        setLoading(true);
        const data = await api.get('/api/jadwal');
        setJadwal(Array.isArray(data) ? data.slice(0, 5) : []);
      } catch (err) {
        setError(err.message || 'Gagal memuat jadwal donor');
      } finally {
        setLoading(false);
      }
    }
    loadJadwal();
  }, []);

  return (
    <>
      <PublicNavbar />
      <main className="wrap">
        <div className="hero">
          <h1>Setetes Darah, Sejuta Harapan</h1>
          <p>Selamat datang di website SiDarah – Sistem Informasi Donor Darah</p>
          <div style={{ marginTop: '1.5rem' }}>
            <Link className="btn" href="/register">
              Daftar Jadi Pendonor
            </Link>
            <Link className="btn o" href="/login" style={{ marginLeft: '.5rem' }}>
              Masuk ke Akun
            </Link>
          </div>
        </div>

        <div className="card">
          <h2>Jadwal Donor Darah Mendatang</h2>
          <p className="muted">Berikut kegiatan donor darah terdekat yang dapat Anda ikuti.</p>

          {loading ? (
            <p className="muted">Memuat jadwal donor...</p>
          ) : error ? (
            <p className="muted">{error}</p>
          ) : (
            <div className="tw">
              <table>
                <thead>
                  <tr>
                    <th>Kegiatan</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Lokasi</th>
                  </tr>
                </thead>
                <tbody>
                  {jadwal.length === 0 ? (
                    <tr>
                      <td colSpan="4" className="muted">
                        Belum ada jadwal donor mendatang.
                      </td>
                    </tr>
                  ) : (
                    jadwal.map((j) => (
                      <tr key={j.id_jadwal}>
                        <td>{j.judul}</td>
                        <td>{tgl(j.tanggal)}</td>
                        <td>
                          {jam(j.jam_mulai)}–{jam(j.jam_selesai)}
                        </td>
                        <td>{j.lokasi}</td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </main>
    </>
  );
}
