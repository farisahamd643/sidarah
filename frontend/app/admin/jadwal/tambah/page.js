'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { api } from '@/lib/api';
import Alert from '@/components/shared/Alert';

export default function AdminJadwalTambahPage() {
  const router = useRouter();

  const [form, setForm] = useState({
    judul: '',
    tanggal: '',
    jam_mulai: '08:00',
    jam_selesai: '12:00',
    lokasi: '',
    keterangan: '',
    kuota: 50,
  });

  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
    if (error) setError('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');

    if (form.jam_selesai <= form.jam_mulai) {
      setError('Jam selesai harus setelah jam mulai.');
      return;
    }

    setSaving(true);

    try {
      await api.post('/api/jadwal', {
        ...form,
        kuota: Number(form.kuota) || 50,
      });

      router.push('/admin/jadwal');
    } catch (err) {
      setError(err.message || 'Gagal menyimpan jadwal baru.');
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="card form auth" style={{ maxWidth: '640px' }}>
      <h1>Tambah Jadwal Donor</h1>

      <Alert type="danger" message={error} onClose={() => setError('')} />

      <form onSubmit={handleSubmit}>
        <label htmlFor="judul">Nama Kegiatan / Judul</label>
        <input
          id="judul"
          name="judul"
          required
          value={form.judul}
          onChange={handleChange}
          placeholder="Contoh: Donor Darah Sukarela PMI"
        />

        <div className="row">
          <div>
            <label htmlFor="tanggal">Tanggal Kegiatan</label>
            <input
              type="date"
              id="tanggal"
              name="tanggal"
              required
              value={form.tanggal}
              onChange={handleChange}
            />
          </div>
          <div>
            <label htmlFor="kuota">Kuota Pendonor</label>
            <input
              type="number"
              id="kuota"
              name="kuota"
              min="1"
              required
              value={form.kuota}
              onChange={handleChange}
            />
          </div>
        </div>

        <div className="row">
          <div>
            <label htmlFor="jam_mulai">Jam Mulai</label>
            <input
              type="time"
              id="jam_mulai"
              name="jam_mulai"
              required
              value={form.jam_mulai}
              onChange={handleChange}
            />
          </div>
          <div>
            <label htmlFor="jam_selesai">Jam Selesai</label>
            <input
              type="time"
              id="jam_selesai"
              name="jam_selesai"
              required
              value={form.jam_selesai}
              onChange={handleChange}
            />
          </div>
        </div>

        <label htmlFor="lokasi">Lokasi Kegiatan</label>
        <input
          id="lokasi"
          name="lokasi"
          required
          value={form.lokasi}
          onChange={handleChange}
          placeholder="Contoh: Gedung Serbaguna Kota Kediri"
        />

        <label htmlFor="keterangan">Keterangan (opsional)</label>
        <textarea
          id="keterangan"
          name="keterangan"
          rows={3}
          value={form.keterangan}
          onChange={handleChange}
          placeholder="Catatan tambahan untuk calon pendonor..."
        />

        <div style={{ marginTop: '1.2rem' }}>
          <button className="btn" type="submit" disabled={saving}>
            {saving ? 'Menyimpan...' : 'Simpan Jadwal'}
          </button>
          <Link className="btn o" href="/admin/jadwal" style={{ marginLeft: '.5rem' }}>
            Batal
          </Link>
        </div>
      </form>
    </div>
  );
}
