'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useAuth } from '@/context/AuthContext';
import { api } from '@/lib/api';
import { tgl, jam, labelGol } from '@/lib/formatters';

export default function AdminDashboardPage() {
  const { user } = useAuth();
  const [stats, setStats] = useState(null);
  const [jadwal, setJadwal] = useState([]);
  const [sebaran, setSebaran] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    async function loadAdminData() {
      try {
        setLoading(true);
        const [statsData, jadwalData, sebaranData] = await Promise.all([
          api.get('/api/dashboard'),
          api.get('/api/jadwal'),
          api.get('/api/dashboard/golongan'),
        ]);

        setStats(statsData);
        setJadwal(Array.isArray(jadwalData) ? jadwalData.slice(0, 5) : []);
        setSebaran(Array.isArray(sebaranData) ? sebaranData : []);
      } catch (err) {
        setError(err.message || 'Gagal memuat data dashboard admin.');
      } finally {
        setLoading(false);
      }
    }
    loadAdminData();
  }, []);

  return (
    <>
      <h1>Halo, {user?.nama || 'Admin'}</h1>

      {loading ? (
        <p className="muted">Memuat data dashboard admin...</p>
      ) : error ? (
        <div className="alert a-danger">{error}</div>
      ) : (
        <>
          <div className="grid">
            <Link className="stat" href="/admin/pendonor">
              <b>{stats?.total_pendonor ?? 0}</b>
              Total pendonor
            </Link>
            <Link className="stat" href="/admin/jadwal">
              <b>{stats?.jadwal_mendatang ?? 0}</b>
              Jadwal mendatang
            </Link>
            <Link className="stat" href="/admin/pendaftaran?s=menunggu">
              <b>{stats?.menunggu ?? 0}</b>
              Pendaftaran baru (Terdaftar)
            </Link>
          </div>

          <div className="card">
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <h2>Jadwal Donor Mendatang</h2>
              <Link className="btn s" href="/admin/jadwal/tambah">
                + Tambah Jadwal
              </Link>
            </div>
            <div className="tw" style={{ marginTop: '0.8rem' }}>
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
          </div>

          <div className="card">
            <h2>Sebaran Golongan Darah</h2>
            <div className="tw">
              <table>
                <thead>
                  <tr>
                    <th>Golongan</th>
                    <th>Jumlah Pendonor</th>
                  </tr>
                </thead>
                <tbody>
                  {sebaran.length === 0 ? (
                    <tr>
                      <td colSpan="2" className="muted">
                        Belum ada data pendonor.
                      </td>
                    </tr>
                  ) : (
                    sebaran.map((g, idx) => (
                      <tr key={idx}>
                        <td>
                          {g.golongan_darah && g.golongan_darah !== 'Belum diisi' ? (
                            <span className="gol">{labelGol(g.golongan_darah, g.rhesus)}</span>
                          ) : (
                            <span className="muted">Belum diisi</span>
                          )}
                        </td>
                        <td>{g.jumlah}</td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
            <p style={{ marginTop: '1rem' }}>
              <Link className="btn o s" href="/admin/golongan">
                Lihat informasi golongan darah lengkap
              </Link>
            </p>
          </div>
        </>
      )}
    </>
  );
}
