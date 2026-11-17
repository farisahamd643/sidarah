'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import ProtectedRoute from '@/components/shared/ProtectedRoute';
import { useAuth } from '@/context/AuthContext';
import { api } from '@/lib/api';
import { tgl, jam, labelGol } from '@/lib/formatters';

export default function DashboardPage() {
  const { user } = useAuth();
  const [stats, setStats] = useState(null);
  const [profil, setProfil] = useState(null);
  const [jadwal, setJadwal] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    async function loadDashboard() {
      try {
        setLoading(true);
        const [statsData, profilData, jadwalData] = await Promise.all([
          api.get('/api/dashboard'),
          api.get('/api/profil'),
          api.get('/api/jadwal'),
        ]);

        setStats(statsData);
        setProfil(profilData);
        setJadwal(Array.isArray(jadwalData) ? jadwalData.slice(0, 5) : []);
      } catch (err) {
        setError(err.message || 'Gagal memuat data dashboard.');
      } finally {
        setLoading(false);
      }
    }
    loadDashboard();
  }, []);

  return (
    <ProtectedRoute roleRequired="pendonor">
      <h1>Halo, {user?.nama || 'Pendonor'}</h1>

      {loading ? (
        <p className="muted">Memuat data dashboard...</p>
      ) : error ? (
        <div className="alert a-danger">{error}</div>
      ) : (
        <>
          {profil && !profil.golongan_darah && (
            <div className="alert a-warning">
              Golongan darah Anda belum diisi.{' '}
              <Link href="/profil">Lengkapi di Profil</Link>.
            </div>
          )}

          <div className="grid">
            <div className="stat">
              <b>{profil?.golongan_darah ? labelGol(profil.golongan_darah, profil.rhesus) : '-'}</b>
              Golongan darah Anda
            </div>
            <div className="stat">
              <b>{stats?.total_donor ?? 0}</b>
              Donor yang pernah diikuti
            </div>
            <Link className="stat" href="/status">
              <b>{stats?.total_pendaftaran ?? 0}</b>
              Pendaftaran tercatat
            </Link>
          </div>

          {stats?.boleh_donor_lagi && (
            <div className="card" style={{ background: '#fdf8e6', borderColor: '#ffe699' }}>
              <p style={{ margin: 0 }}>
                💡 <b>Catatan Interval Donor:</b> Berdasarkan donor terakhir Anda pada tanggal{' '}
                <b>{tgl(stats.donor_terakhir)}</b>, Anda diperkirakan dapat mendonor kembali setelah tanggal{' '}
                <b>{tgl(stats.boleh_donor_lagi)}</b>.
              </p>
            </div>
          )}

          <div className="card" id="jadwal-mendatang">
            <h2>Jadwal Donor Mendatang</h2>
            <div className="tw">
              <table>
                <thead>
                  <tr>
                    <th>Kegiatan</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Lokasi</th>
                    <th>Kuota</th>
                  </tr>
                </thead>
                <tbody>
                  {jadwal.length === 0 ? (
                    <tr>
                      <td colSpan="5" className="muted">
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
                        <td>
                          {j.terdaftar ?? 0}/{j.kuota}
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
            <p style={{ marginTop: '1.2rem' }}>
              <Link className="btn" href="/pendaftaran">
                Daftar Donor Sekarang
              </Link>
            </p>
          </div>
        </>
      )}
    </ProtectedRoute>
  );
}
