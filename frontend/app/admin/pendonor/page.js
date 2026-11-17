'use client';

import { useState, useEffect, useCallback } from 'react';
import Link from 'next/link';
import { useSearchParams } from 'next/navigation';
import { api } from '@/lib/api';
import Alert from '@/components/shared/Alert';
import { tgl, labelGol } from '@/lib/formatters';

const GOLONGAN = ['A', 'B', 'AB', 'O'];
const RHESUS = ['+', '-'];

export default function AdminPendonorPage() {
  const searchParams = useSearchParams();
  const initialGol = searchParams?.get('gol') || '';
  const initialRh = searchParams?.get('rh') || '';

  const [list, setList] = useState([]);
  const [cari, setCari] = useState('');
  const [gol, setGol] = useState(initialGol);
  const [rh, setRh] = useState(initialRh);
  const [loading, setLoading] = useState(true);
  const [deletingId, setDeletingId] = useState(null);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const loadPendonor = useCallback(async () => {
    try {
      setLoading(true);
      const params = new URLSearchParams();
      if (cari.trim()) params.append('cari', cari.trim());
      if (gol && gol !== 'kosong') params.append('golongan_darah', gol);

      const qs = params.toString();
      const data = await api.get(`/api/pendonor${qs ? `?${qs}` : ''}`);

      let filtered = Array.isArray(data) ? data : [];
      if (gol === 'kosong') {
        filtered = filtered.filter((p) => !p.golongan_darah);
      }
      if (rh) {
        filtered = filtered.filter((p) => p.rhesus === rh);
      }

      setList(filtered);
    } catch (err) {
      setError(err.message || 'Gagal memuat data pendonor.');
    } finally {
      setLoading(false);
    }
  }, [cari, gol, rh]);

  useEffect(() => {
    loadPendonor();
  }, [loadPendonor]);

  const handleDelete = async (id, nama) => {
    if (!window.confirm(`Hapus pendonor "${nama}"? Akun login yang terhubung juga akan dihapus.`)) {
      return;
    }

    setDeletingId(id);
    setError('');
    setSuccess('');

    try {
      const res = await api.delete(`/api/pendonor/${id}`);
      setSuccess(res.pesan || 'Pendonor berhasil dihapus.');
      loadPendonor();
    } catch (err) {
      setError(err.message || 'Gagal menghapus data pendonor.');
    } finally {
      setDeletingId(null);
    }
  };

  return (
    <>
      <h1>Data Pendonor</h1>

      <Alert type="danger" message={error} onClose={() => setError('')} />
      <Alert type="success" message={success} onClose={() => setSuccess('')} />

      <div className="card">
        <form
          className="bar"
          onSubmit={(e) => {
            e.preventDefault();
            loadPendonor();
          }}
        >
          <div>
            <label htmlFor="cari">Cari nama / email</label>
            <input
              id="cari"
              value={cari}
              onChange={(e) => setCari(e.target.value)}
              placeholder="Ketik nama atau email..."
            />
          </div>
          <div>
            <label htmlFor="gol">Golongan darah</label>
            <select id="gol" value={gol} onChange={(e) => setGol(e.target.value)}>
              <option value="">Semua</option>
              {GOLONGAN.map((x) => (
                <option key={x} value={x}>
                  {x}
                </option>
              ))}
              <option value="kosong">Belum diisi</option>
            </select>
          </div>
          <div>
            <label htmlFor="rh">Rhesus</label>
            <select id="rh" value={rh} onChange={(e) => setRh(e.target.value)}>
              <option value="">Semua</option>
              {RHESUS.map((x) => (
                <option key={x} value={x}>
                  {x}
                </option>
              ))}
            </select>
          </div>
          <button className="btn" type="submit">
            Cari
          </button>
          <Link className="btn o" href="/admin/pendonor/tambah">
            + Tambah Pendonor
          </Link>
        </form>

        <p className="muted" style={{ marginTop: '0.8rem' }}>
          {list.length} pendonor ditemukan.
        </p>

        {loading ? (
          <p className="muted">Memuat data...</p>
        ) : (
          <div className="tw">
            <table>
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Gol.</th>
                  <th>JK</th>
                  <th>Tgl Lahir</th>
                  <th>No. HP</th>
                  <th>Email</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                {list.length === 0 ? (
                  <tr>
                    <td colSpan="7" className="muted">
                      Tidak ada data pendonor ditemukan.
                    </td>
                  </tr>
                ) : (
                  list.map((p) => (
                    <tr key={p.id_pendonor}>
                      <td>
                        <Link
                          href={`/admin/pendonor/detail/${p.id_pendonor}`}
                          style={{ color: 'var(--rd)', fontWeight: 'bold' }}
                        >
                          {p.nama_lengkap}
                        </Link>
                      </td>
                      <td>
                        {p.golongan_darah ? (
                          <span className="gol">{labelGol(p.golongan_darah, p.rhesus)}</span>
                        ) : (
                          <span className="muted">-</span>
                        )}
                      </td>
                      <td>{p.jenis_kelamin === 'L' ? 'L' : 'P'}</td>
                      <td>{tgl(p.tanggal_lahir)}</td>
                      <td>{p.no_hp || '-'}</td>
                      <td>{p.email || '-'}</td>
                      <td>
                        <Link
                          className="btn o s"
                          href={`/admin/pendonor/detail/${p.id_pendonor}`}
                          style={{ marginRight: '0.3rem' }}
                        >
                          Detail
                        </Link>
                        <Link
                          className="btn o s"
                          href={`/admin/pendonor/${p.id_pendonor}`}
                          style={{ marginRight: '0.3rem' }}
                        >
                          Edit
                        </Link>
                        <button
                          type="button"
                          className="btn s"
                          onClick={() => handleDelete(p.id_pendonor, p.nama_lengkap)}
                          disabled={deletingId === p.id_pendonor}
                        >
                          {deletingId === p.id_pendonor ? '...' : 'Hapus'}
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
