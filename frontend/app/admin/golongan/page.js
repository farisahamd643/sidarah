'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { api } from '@/lib/api';

const GOLONGAN = ['A', 'B', 'AB', 'O'];
const RHESUS = ['+', '-'];

export default function AdminGolonganPage() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    async function loadGolongan() {
      try {
        setLoading(true);
        const res = await api.get('/api/dashboard/golongan');
        setData(Array.isArray(res) ? res : []);
      } catch (err) {
        setError(err.message || 'Gagal memuat rekap golongan darah.');
      } finally {
        setLoading(false);
      }
    }
    loadGolongan();
  }, []);

  const getJumlah = (g, r) => {
    const item = data.find(
      (x) => x.golongan_darah === g && (x.rhesus === r || (!x.rhesus && !r))
    );
    return item ? item.jumlah : 0;
  };

  const kosong = data.find(
    (x) => x.golongan_darah === 'Belum diisi' || !x.golongan_darah
  )?.jumlah || 0;

  return (
    <>
      <h1>Informasi Golongan Darah</h1>
      <p className="muted">Klik salah satu golongan untuk melihat daftar pendonornya.</p>

      {loading ? (
        <p className="muted">Memuat data golongan darah...</p>
      ) : error ? (
        <div className="alert a-danger">{error}</div>
      ) : (
        <div className="grid">
          {GOLONGAN.flatMap((g) =>
            RHESUS.map((r) => {
              const count = getJumlah(g, r);
              return (
                <Link
                  key={`${g}${r}`}
                  className="stat"
                  href={`/admin/pendonor?gol=${g}&rh=${encodeURIComponent(r)}`}
                >
                  <b>{g}{r}</b>
                  {count} pendonor
                </Link>
              );
            })
          )}
          <Link className="stat" href="/admin/pendonor?gol=kosong">
            <b>-</b>
            {kosong} belum mengisi golongan darah
          </Link>
        </div>
      )}
    </>
  );
}
