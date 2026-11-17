'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useParams } from 'next/navigation';
import { api } from '@/lib/api';
import { Icon } from '@/components/shared/Icons';
import { tgl, labelGol, labelStatus, statusBadgeClass } from '@/lib/formatters';

export default function AdminPendonorDetailPage() {
  const params = useParams();
  const id = params?.id;

  const [pendonor, setPendonor] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    async function loadDetail() {
      try {
        setLoading(true);
        const data = await api.get(`/api/pendonor/${id}`);
        setPendonor(data);
      } catch (err) {
        setError(err.message || 'Gagal memuat detail pendonor.');
      } finally {
        setLoading(false);
      }
    }
    if (id) {
      loadDetail();
    }
  }, [id]);

  if (loading) {
    return <p className="muted">Memuat detail pendonor...</p>;
  }

  if (error || !pendonor) {
    return (
      <div className="card">
        <div className="alert a-danger">{error || 'Pendonor tidak ditemukan.'}</div>
        <Link className="btn o" href="/admin/pendonor">
          Kembali ke Data Pendonor
        </Link>
      </div>
    );
  }

  return (
    <>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <h1>Detail Pendonor</h1>
        <div>
          <Link
            className="btn s"
            href={`/admin/pendonor/${pendonor.id_pendonor}`}
            style={{ marginRight: '0.5rem' }}
          >
            Edit Pendonor
          </Link>
          <Link className="btn o s" href="/admin/pendonor">
            Kembali
          </Link>
        </div>
      </div>

      <div className="card">
        <h2 style={{ marginTop: 0 }}>{pendonor.nama_lengkap}</h2>
        <div className="row">
          <div>
            <small className="muted">Golongan Darah</small>
            <p style={{ margin: '0.2rem 0 1rem' }}>
              {pendonor.golongan_darah ? (
                <span className="gol">{labelGol(pendonor.golongan_darah, pendonor.rhesus)}</span>
              ) : (
                <span className="muted">Belum diisi</span>
              )}
            </p>
          </div>
          <div>
            <small className="muted">NIK</small>
            <p style={{ margin: '0.2rem 0 1rem', fontWeight: 600 }}>{pendonor.nik || '-'}</p>
          </div>
          <div>
            <small className="muted">Jenis Kelamin</small>
            <p style={{ margin: '0.2rem 0 1rem', fontWeight: 600 }}>
              {pendonor.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'}
            </p>
          </div>
          <div>
            <small className="muted">Tanggal Lahir</small>
            <p style={{ margin: '0.2rem 0 1rem', fontWeight: 600 }}>{tgl(pendonor.tanggal_lahir)}</p>
          </div>
        </div>

        <div className="row">
          <div>
            <small className="muted">Nomor Handphone</small>
            <p style={{ margin: '0.2rem 0 1rem', fontWeight: 600 }}>{pendonor.no_hp || '-'}</p>
          </div>
          <div>
            <small className="muted">Email Akun</small>
            <p style={{ margin: '0.2rem 0 1rem', fontWeight: 600 }}>{pendonor.email || '-'}</p>
          </div>
          <div style={{ gridColumn: 'span 2' }}>
            <small className="muted">Alamat</small>
            <p style={{ margin: '0.2rem 0 1rem' }}>{pendonor.alamat || '-'}</p>
          </div>
        </div>
      </div>

      <div className="card">
        <h2>Riwayat Pendaftaran &amp; Donor</h2>
        <div className="tw">
          <table>
            <thead>
              <tr>
                <th>Kegiatan</th>
                <th>Tanggal</th>
                <th>Lokasi</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              {!pendonor.riwayat || pendonor.riwayat.length === 0 ? (
                <tr>
                  <td colSpan="4" className="muted">
                    Pendonor ini belum memiliki riwayat pendaftaran.
                  </td>
                </tr>
              ) : (
                pendonor.riwayat.map((r) => (
                  <tr key={r.id_pendaftaran}>
                    <td>{r.judul}</td>
                    <td>{tgl(r.tanggal)}</td>
                    <td>{r.lokasi}</td>
                    <td>
                      <span className={statusBadgeClass(r.status)}>
                        {labelStatus(r.status)}
                      </span>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </>
  );
}
