'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import ProtectedRoute from '@/components/shared/ProtectedRoute';
import { api } from '@/lib/api';
import { Icon, JamIcon, PanIcon } from '@/components/shared/Icons';
import { jadwalTeks, jamRange, labelStatus } from '@/lib/formatters';

export default function JadwalSayaPage() {
  const [aktif, setAktif] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    async function loadJadwalAktif() {
      try {
        setLoading(true);
        const data = await api.get('/api/pendaftaran/saya');
        if (Array.isArray(data)) {
          const today = new Date().toISOString().split('T')[0];
          // Cari pendaftaran aktif terdekat
          const pendaftaranAktif = data.find(
            (p) => ['menunggu', 'diterima'].includes(p.status) && p.tanggal >= today
          );
          setAktif(pendaftaranAktif || null);
        }
      } catch (err) {
        setError(err.message || 'Gagal memuat jadwal Anda.');
      } finally {
        setLoading(false);
      }
    }
    loadJadwalAktif();
  }, []);

  return (
    <ProtectedRoute roleRequired="pendonor">
      <div className="card kepala-form">
        <div className="lingkar">
          <Icon name="kalender" />
        </div>
        <div>
          <h1>Jadwal Donor Saya</h1>
          <p className="muted">Informasi kegiatan donor darah aktif Anda.</p>
        </div>
      </div>

      {loading ? (
        <p className="muted">Memuat informasi jadwal...</p>
      ) : error ? (
        <div className="alert a-danger">{error}</div>
      ) : !aktif ? (
        <div className="card">
          <p className="muted" style={{ margin: 0 }}>
            Anda belum punya jadwal donor aktif.{' '}
            <Link href="/pendaftaran">Daftar sekarang</Link>.
          </p>
        </div>
      ) : (
        <Link className="jd" href="/status">
          <div className="lingkar">
            <Icon name="kalender" />
          </div>
          <div className="isi">
            <h3>{jadwalTeks(aktif.tanggal)}</h3>
            <span className="chip">{labelStatus(aktif.status)}</span>
            <div className="info">
              <Icon name="pin" />
              <span>{aktif.lokasi}</span>
            </div>
            <div className="info">
              <JamIcon />
              <span>{jamRange(aktif.jam_mulai, aktif.jam_selesai)}</span>
            </div>
          </div>
          <PanIcon />
        </Link>
      )}

      <p style={{ marginTop: '1.2rem' }}>
        <Link className="btn o blok" href="/status">
          Lihat Riwayat &amp; Status Pendaftaran Lengkap
        </Link>
      </p>
    </ProtectedRoute>
  );
}
