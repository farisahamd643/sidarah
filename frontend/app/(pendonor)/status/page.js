'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import ProtectedRoute from '@/components/shared/ProtectedRoute';
import { api } from '@/lib/api';
import { Icon, JamIcon } from '@/components/shared/Icons';
import Alert from '@/components/shared/Alert';
import { jadwalTeks, jamRange, labelStatus, statusBadgeClass, tgl } from '@/lib/formatters';

const CATATAN = {
  menunggu: 'Harap hadir 15 menit sebelum jadwal donor dan membawa KTP asli.',
  diterima: 'Pendaftaran disetujui admin. Harap hadir 15 menit sebelum jadwal donor dan membawa KTP asli.',
  selesai: 'Anda sudah mengikuti kegiatan donor ini. Terima kasih!',
  dibatalkan: 'Pendaftaran ini sudah dibatalkan.',
};

export default function StatusPage() {
  const [riwayat, setRiwayat] = useState([]);
  const [loading, setLoading] = useState(true);
  const [cancellingId, setCancellingId] = useState(null);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const loadRiwayat = async () => {
    try {
      setLoading(true);
      const data = await api.get('/api/pendaftaran/saya');
      setRiwayat(Array.isArray(data) ? data : []);
    } catch (err) {
      setError(err.message || 'Gagal memuat riwayat pendaftaran.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadRiwayat();
  }, []);

  const handleBatal = async (id_pendaftaran) => {
    if (!window.confirm('Apakah Anda yakin ingin membatalkan pendaftaran donor darah ini?')) {
      return;
    }

    setCancellingId(id_pendaftaran);
    setError('');
    setSuccess('');

    try {
      const res = await api.patch(`/api/pendaftaran/${id_pendaftaran}/batal`);
      setSuccess(res.pesan || 'Pendaftaran berhasil dibatalkan.');
      // Refresh list
      loadRiwayat();
    } catch (err) {
      setError(err.message || 'Gagal membatalkan pendaftaran.');
    } finally {
      setCancellingId(null);
    }
  };

  return (
    <ProtectedRoute roleRequired="pendonor">
      <div className="card kepala-form">
        <div className="lingkar">
          <Icon name="kalender" />
        </div>
        <div>
          <h1>Status Pendaftaran</h1>
          <p className="muted">Cek detail status dan riwayat pendaftaran donor darah Anda.</p>
        </div>
      </div>

      <Alert type="danger" message={error} onClose={() => setError('')} />
      <Alert type="success" message={success} onClose={() => setSuccess('')} />

      <h2 className="sub">Detail Pendaftaran</h2>

      {loading ? (
        <p className="muted">Memuat data riwayat...</p>
      ) : riwayat.length === 0 ? (
        <div className="card">
          <p className="muted" style={{ margin: 0 }}>
            Anda belum mendaftar kegiatan donor darah apa pun.{' '}
            <Link href="/pendaftaran">Daftar sekarang</Link>.
          </p>
        </div>
      ) : (
        riwayat.map((s) => (
          <div key={s.id_pendaftaran} className="card" style={{ marginBottom: '1.2rem' }}>
            <div
              style={{
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                borderBottom: '1px solid var(--bd)',
                paddingBottom: '0.6rem',
                marginBottom: '0.8rem',
              }}
            >
              <span className={statusBadgeClass(s.status)}>{labelStatus(s.status)}</span>
              <small className="muted">
                Daftar: {tgl(s.tanggal_daftar)}
              </small>
            </div>

            <div className="baris">
              <div className="lingkar">
                <Icon name="kalender" />
              </div>
              <div>
                <small>Tanggal</small>
                <b>{jadwalTeks(s.tanggal)}</b>
              </div>
            </div>

            <div className="baris">
              <div className="lingkar">
                <JamIcon />
              </div>
              <div>
                <small>Jam Kegiatan</small>
                <b>{jamRange(s.jam_mulai, s.jam_selesai)}</b>
              </div>
            </div>

            <div className="baris">
              <div className="lingkar">
                <Icon name="pin" />
              </div>
              <div>
                <small>Lokasi Donor</small>
                <b>{s.lokasi}</b>
              </div>
            </div>

            <div
              style={{
                background: '#fafafa',
                borderRadius: '8px',
                padding: '0.75rem 1rem',
                marginTop: '0.8rem',
                fontSize: '0.9rem',
                color: '#555',
              }}
            >
              ℹ️ {CATATAN[s.status] || ''}
            </div>

            {['menunggu', 'diterima'].includes(s.status) && (
              <div style={{ marginTop: '1rem', textAlign: 'right' }}>
                <button
                  type="button"
                  className="btn o s"
                  onClick={() => handleBatal(s.id_pendaftaran)}
                  disabled={cancellingId === s.id_pendaftaran}
                >
                  {cancellingId === s.id_pendaftaran ? 'Membatalkan...' : 'Batalkan Pendaftaran'}
                </button>
              </div>
            )}
          </div>
        ))
      )}
    </ProtectedRoute>
  );
}
