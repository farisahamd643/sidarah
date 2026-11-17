'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { api } from '@/lib/api';
import Alert from '@/components/shared/Alert';
import { tgl, jam } from '@/lib/formatters';

export default function AdminJadwalListPage() {
  const [jadwal, setJadwal] = useState([]);
  const [loading, setLoading] = useState(true);
  const [deletingId, setDeletingId] = useState(null);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const loadJadwal = async () => {
    try {
      setLoading(true);
      const data = await api.get('/api/jadwal');
      setJadwal(Array.isArray(data) ? data : []);
    } catch (err) {
      setError(err.message || 'Gagal memuat daftar jadwal donor.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadJadwal();
  }, []);

  const handleDelete = async (id) => {
    if (!window.confirm('Hapus jadwal ini? Pendaftaran pada jadwal ini ikut terhapus.')) {
      return;
    }

    setDeletingId(id);
    setError('');
    setSuccess('');

    try {
      const res = await api.delete(`/api/jadwal/${id}`);
      setSuccess(res.pesan || 'Jadwal berhasil dihapus.');
      loadJadwal();
    } catch (err) {
      setError(err.message || 'Gagal menghapus jadwal.');
    } finally {
      setDeletingId(null);
    }
  };

  return (
    <>
      <h1>Jadwal Donor</h1>

      <Alert type="danger" message={error} onClose={() => setError('')} />
      <Alert type="success" message={success} onClose={() => setSuccess('')} />

      <div className="card">
        <p>
          <Link className="btn" href="/admin/jadwal/tambah">
            + Tambah Jadwal
          </Link>
        </p>

        {loading ? (
          <p className="muted">Memuat data jadwal...</p>
        ) : (
          <div className="tw">
            <table>
              <thead>
                <tr>
                  <th>Kegiatan</th>
                  <th>Tanggal</th>
                  <th>Jam</th>
                  <th>Lokasi</th>
                  <th>Terisi</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                {jadwal.length === 0 ? (
                  <tr>
                    <td colSpan="6" className="muted">
                      Belum ada jadwal.
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
                      <td>
                        <Link
                          className="btn o s"
                          href={`/admin/jadwal/${j.id_jadwal}`}
                          style={{ marginRight: '0.4rem' }}
                        >
                          Edit
                        </Link>
                        <button
                          type="button"
                          className="btn s"
                          onClick={() => handleDelete(j.id_jadwal)}
                          disabled={deletingId === j.id_jadwal}
                        >
                          {deletingId === j.id_jadwal ? '...' : 'Hapus'}
                        </button>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </>
  );
}
