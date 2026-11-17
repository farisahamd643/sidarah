'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter, useParams } from 'next/navigation';
import { api } from '@/lib/api';
import Alert from '@/components/shared/Alert';
import { Icon } from '@/components/shared/Icons';

const GOLONGAN = ['A', 'B', 'AB', 'O'];
const RHESUS = ['+', '-'];

export default function AdminPendonorEditPage() {
  const router = useRouter();
  const params = useParams();
  const id = params?.id;

  const [form, setForm] = useState({
    nama_lengkap: '',
    nik: '',
    tanggal_lahir: '',
    jenis_kelamin: '',
    no_hp: '',
    alamat: '',
    golongan_darah: '',
    rhesus: '',
  });

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    async function loadPendonor() {
      try {
        setLoading(true);
        const data = await api.get(`/api/pendonor/${id}`);
        setForm({
          nama_lengkap: data.nama_lengkap || '',
          nik: data.nik || '',
          tanggal_lahir: data.tanggal_lahir ? data.tanggal_lahir.split('T')[0] : '',
          jenis_kelamin: data.jenis_kelamin || '',
          no_hp: data.no_hp || '',
          alamat: data.alamat || '',
          golongan_darah: data.golongan_darah || '',
          rhesus: data.rhesus || '',
        });
      } catch (err) {
        setError(err.message || 'Pendonor tidak ditemukan.');
      } finally {
        setLoading(false);
      }
    }
    if (id) {
      loadPendonor();
    }
  }, [id]);

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
    if (error) setError('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');

    if (form.nik && !/^\d{16}$/.test(form.nik.trim())) {
      setError('NIK harus berupa 16 digit angka.');
      return;
    }

    setSaving(true);

    try {
      await api.put(`/api/pendonor/${id}`, {
        nama_lengkap: form.nama_lengkap.trim(),
        nik: form.nik.trim() || null,
        tanggal_lahir: form.tanggal_lahir,
        jenis_kelamin: form.jenis_kelamin,
        no_hp: form.no_hp.trim() || null,
        alamat: form.alamat.trim() || null,
        golongan_darah: form.golongan_darah || null,
        rhesus: form.rhesus || null,
      });

      router.push('/admin/pendonor');
    } catch (err) {
      setError(err.message || 'Gagal memperbarui pendonor.');
    } finally {
      setSaving(false);
    }
  };

  const today = new Date().toISOString().split('T')[0];

  return (
    <div className="card form auth" style={{ maxWidth: '640px' }}>
      <h1>Edit Data Pendonor</h1>

      <Alert type="danger" message={error} onClose={() => setError('')} />

      {loading ? (
        <p className="muted">Memuat data pendonor...</p>
      ) : (
        <form onSubmit={handleSubmit}>
          <label htmlFor="nama_lengkap">Nama Lengkap</label>
          <div className="fld">
            <Icon name="orang" />
            <input
              id="nama_lengkap"
              name="nama_lengkap"
              required
              maxLength={100}
              value={form.nama_lengkap}
              onChange={handleChange}
            />
          </div>

          <label htmlFor="nik">NIK (16 digit angka)</label>
          <div className="fld">
            <Icon name="id" />
            <input
              id="nik"
              name="nik"
              inputMode="numeric"
              maxLength={16}
              pattern="[0-9]{16}"
              value={form.nik}
              onChange={handleChange}
            />
          </div>

          <label htmlFor="tanggal_lahir">Tanggal Lahir</label>
          <div className="fld">
            <Icon name="kalender" />
            <input
              type="date"
              id="tanggal_lahir"
              name="tanggal_lahir"
              required
              max={today}
              value={form.tanggal_lahir}
              onChange={handleChange}
            />
          </div>

          <label htmlFor="jenis_kelamin">Jenis Kelamin</label>
          <div className="fld">
            <Icon name="orang" />
            <select
              id="jenis_kelamin"
              name="jenis_kelamin"
              required
              value={form.jenis_kelamin}
              onChange={handleChange}
            >
              <option value="">Pilih jenis kelamin</option>
              <option value="L">Laki-laki</option>
              <option value="P">Perempuan</option>
            </select>
          </div>

          <label htmlFor="no_hp">Nomor HP</label>
          <div className="fld">
            <Icon name="telp" />
            <input
              type="tel"
              id="no_hp"
              name="no_hp"
              maxLength={20}
              value={form.no_hp}
              onChange={handleChange}
            />
          </div>

          <label htmlFor="alamat">Alamat</label>
          <div className="fld ta">
            <Icon name="pin" />
            <textarea
              id="alamat"
              name="alamat"
              rows={3}
              value={form.alamat}
              onChange={handleChange}
            />
          </div>

          <h2 className="sub">Data Donor</h2>
          <div className="row">
            <div>
              <label htmlFor="golongan_darah">Golongan Darah</label>
              <div className="fld">
                <Icon name="darah" />
                <select
                  id="golongan_darah"
                  name="golongan_darah"
                  value={form.golongan_darah}
                  onChange={handleChange}
                >
                  <option value="">Belum diisi</option>
                  {GOLONGAN.map((g) => (
                    <option key={g} value={g}>
                      {g}
                    </option>
                  ))}
                </select>
              </div>
            </div>
            <div>
              <label htmlFor="rhesus">Rhesus</label>
              <div className="fld">
                <Icon name="darah" />
                <select
                  id="rhesus"
                  name="rhesus"
                  value={form.rhesus}
                  onChange={handleChange}
                >
                  <option value="">Belum diisi</option>
                  {RHESUS.map((r) => (
                    <option key={r} value={r}>
                      {r}
                    </option>
                  ))}
                </select>
              </div>
            </div>
          </div>

          <div style={{ marginTop: '1.2rem' }}>
            <button className="btn" type="submit" disabled={saving}>
              {saving ? 'Menyimpan...' : 'Simpan Perubahan'}
            </button>
            <Link className="btn o" href="/admin/pendonor" style={{ marginLeft: '.5rem' }}>
              Batal
            </Link>
          </div>
        </form>
      )}
    </div>
  );
}
