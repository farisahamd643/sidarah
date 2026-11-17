'use client';

import { useState, useEffect } from 'react';
import { useSearchParams } from 'next/navigation';
import { api } from '@/lib/api';
import Alert from '@/components/shared/Alert';
import { tgl, labelStatus, statusBadgeClass, labelGol } from '@/lib/formatters';

const STATUS_DONOR = ['menunggu', 'diterima', 'dibatalkan', 'selesai'];

export default function AdminPendaftaranPage() {
  const searchParams = useSearchParams();
  const initialStatus = searchParams?.get('s') || '';

  const [pendaftaran, setPendaftaran] = useState([]);
  const [filterStatus, setFilterStatus] = useState(initialStatus);
  const [loading, setLoading] = useState(true);
  const [updatingId, setUpdatingId] = useState(null);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const loadData = async () => {
    try {
      setLoading(true);
      const data = await api.get('/api/pendaftaran');
      setPendaftaran(Array.isArray(data) ? data : []);
    } catch (err) {
      setError(err.message || 'Gagal memuat daftar pendaftaran donor.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const handleStatusChange = async (id, newStatus) => {
    setUpdatingId(id);
    setError('');
    setSuccess('');

    try {
      await api.patch(`/api/pendaftaran/${id}/status`, {
        status: newStatus,
      });

      setSuccess('Status pendaftaran berhasil diperbarui.');
      // Update local state directly
      setPendaftaran((prev) =>
        prev.map((item) =>
          item.id_pendaftaran === id ? { ...item, status: newStatus } : item
        )
      );
    } catch (err) {
      setError(err.message || 'Gagal mengubah status pendaftaran.');
    } finally {
      setUpdatingId(null);
    }
  };

  const filteredList = filterStatus
    ? pendaftaran.filter((p) => p.status === filterStatus)
    : pendaftaran;

  return (
    <>
      <h1>Pendaftaran Donor</h1>

      <Alert type="danger" message={error} onClose={() => setError('')} />
      <Alert type="success" message={success} onClose={() => setSuccess('')} />

      <div className="card">
        <div className="bar">
          <div>
            <label htmlFor="filterStatus">Filter status</label>
            <select
              id="filterStatus"
              value={filterStatus}
              onChange={(e) => setFilterStatus(e.target.value)}
            >
              <option value="">Semua Status</option>
              {STATUS_DONOR.map((s) => (
                <option key={s} value={s}>
                  {labelStatus(s)}
                </option>
              ))}
            </select>
          </div>
        </div>

        {loading ? (
          <p className="muted">Memuat data pendaftaran...</p>
        ) : (
          <div className="tw">
            <table>
              <thead>
                <tr>
                  <th>Pendonor</th>
                  <th>Gol.</th>
                  <th>No. HP</th>
                  <th>Kegiatan</th>
                  <th>Tanggal</th>
                  <th>Status</th>
                  <th>Ubah Status</th>
                </tr>
              </thead>
              <tbody>
                {filteredList.length === 0 ? (
                  <tr>
                    <td colSpan="7" className="muted">
                      Tidak ada data pendaftaran.
                    </td>
                  </tr>
                ) : (
                  filteredList.map((d) => (
                    <tr key={d.id_pendaftaran}>
                      <td>
                        <b>{d.nama_lengkap}</b>
                      </td>
                      <td>
                        {d.golongan_darah ? (
                          <span className="gol">{labelGol(d.golongan_darah, d.rhesus)}</span>
                        ) : (
                          <span className="muted">-</span>
                        )}
                      </td>
                      <td>{d.no_hp || '-'}</td>
                      <td>{d.judul}</td>
                      <td>{tgl(d.tanggal)}</td>
                      <td>
                        <span className={statusBadgeClass(d.status)}>
                          {labelStatus(d.status)}
                        </span>
                      </td>
                      <td>
                        <select
                          value={d.status}
                          disabled={updatingId === d.id_pendaftaran}
                          onChange={(e) =>
                            handleStatusChange(d.id_pendaftaran, e.target.value)
                          }
                          style={{
                            padding: '0.3rem 0.5rem',
                            fontSize: '0.85rem',
                            borderRadius: '6px',
                          }}
                        >
                          {STATUS_DONOR.map((opt) => (
                            <option key={opt} value={opt}>
                              {labelStatus(opt)}
                            </option>
                          ))}
                        </select>
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
