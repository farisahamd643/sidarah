'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import ProtectedRoute from '@/components/shared/ProtectedRoute';
import { api } from '@/lib/api';
import { Icon, JamIcon } from '@/components/shared/Icons';
import Alert from '@/components/shared/Alert';
import { tgl, jamRange, labelGol } from '@/lib/formatters';

export default function PendaftaranPage() {
  const router = useRouter();

  const [profil, setProfil] = useState(null);
  const [jadwalList, setJadwalList] = useState([]);
  const [selectedJadwal, setSelectedJadwal] = useState('');
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  useEffect(() => {
    async function loadData() {
      try {
        setLoading(true);
        const [profilData, jadwalData] = await Promise.all([
          api.get('/api/profil'),
          api.get('/api/jadwal'),
        ]);

        setProfil(profilData);
        setJadwalList(Array.isArray(jadwalData) ? jadwalData : []);
      } catch (err) {
        setError(err.message || 'Gagal memuat data pendaftaran.');
      } finally {
        setLoading(false);
      }
    }
    loadData();
  }, []);

  const calculateAge = (birthDateString) => {
    if (!birthDateString) return 0;
    const today = new Date();
    const birthDate = new Date(birthDateString);
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
      age--;
    }
    return age;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');

    if (!selectedJadwal) {
      setError('Silakan pilih salah satu jadwal donor darah.');
      return;
    }

    if (profil && calculateAge(profil.tanggal_lahir) < 17) {
      setError('Pendonor minimal berusia 17 tahun untuk dapat mendaftar donor darah.');
      return;
    }

    setSubmitting(true);

    try {
      await api.post('/api/pendaftaran', {
        id_jadwal: Number(selectedJadwal),
      });

      setSuccess('Pendaftaran berhasil! Status pendaftaran Anda: Terdaftar.');
      setTimeout(() => {
        router.push('/status');
      }, 1500);
    } catch (err) {
      setError(err.message || 'Pendaftaran donor gagal. Coba lagi.');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <ProtectedRoute roleRequired="pendonor">
      <div className="card kepala-form">
        <div className="lingkar">
          <Icon name="catatan" />
        </div>
        <div>
          <h1>Daftar Donor Darah</h1>
          <p className="muted">Lengkapi data berikut untuk mendaftar donor darah.</p>
        </div>
      </div>

      <Alert type="danger" message={error} onClose={() => setError('')} />
      <Alert type="success" message={success} />

      {loading ? (
        <p className="muted">Memuat formulir pendaftaran...</p>
      ) : (
        <>
          <h2 className="sub">Data Pribadi</h2>
          <div className="kartu-info">
            <Icon name="orang" />
            <div>
              <b>Nama Lengkap</b>
              <span>{profil?.nama_lengkap || '-'}</span>
            </div>
          </div>

          <div className="kartu-info">
            <Icon name="id" />
            <div>
              <b>NIK</b>
              {profil?.nik ? <span>{profil.nik}</span> : <span className="kosong">Belum diisi</span>}
            </div>
          </div>

          <div className="kartu-info">
            <Icon name="kalender" />
            <div>
              <b>Tanggal Lahir</b>
              <span>{tgl(profil?.tanggal_lahir)}</span>
            </div>
          </div>

          <div className="kartu-info">
            <Icon name="orang" />
            <div>
              <b>Jenis Kelamin</b>
              <span>{profil?.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'}</span>
            </div>
          </div>

          <div className="kartu-info">
            <Icon name="darah" />
            <div>
              <b>Golongan Darah</b>
              {profil?.golongan_darah ? (
                <span>{labelGol(profil.golongan_darah, profil.rhesus)}</span>
              ) : (
                <span className="kosong">
                  Belum diisi (<Link href="/profil">Isi di Profil</Link>)
                </span>
              )}
            </div>
          </div>

          <h2 className="sub" style={{ marginTop: '2rem' }}>
            Pilih Jadwal Kegiatan Donor
          </h2>

          <form onSubmit={handleSubmit}>
            <div style={{ display: 'grid', gap: '0.9rem', marginBottom: '1.5rem' }}>
              {jadwalList.length === 0 ? (
                <div className="card">
                  <p className="muted" style={{ margin: 0 }}>
                    Saat ini belum ada jadwal donor darah yang tersedia. Silakan cek kembali nanti.
                  </p>
                </div>
              ) : (
                jadwalList.map((j) => {
                  const penuh = (j.terdaftar ?? 0) >= j.kuota;
                  const isChecked = String(selectedJadwal) === String(j.id_jadwal);

                  return (
                    <label
                      key={j.id_jadwal}
                      className="card"
                      style={{
                        display: 'flex',
                        alignItems: 'flex-start',
                        gap: '1rem',
                        cursor: penuh ? 'not-allowed' : 'pointer',
                        borderColor: isChecked ? 'var(--r)' : 'var(--bd)',
                        backgroundColor: isChecked ? 'var(--rl)' : penuh ? '#f9f9f9' : '#fff',
                        opacity: penuh ? 0.6 : 1,
                        padding: '1rem',
                        margin: 0,
                      }}
                    >
                      <input
                        type="radio"
                        name="id_jadwal"
                        value={j.id_jadwal}
                        disabled={penuh}
                        checked={isChecked}
                        onChange={(e) => setSelectedJadwal(e.target.value)}
                        style={{ width: 'auto', marginTop: '0.3rem' }}
                      />
                      <div style={{ flex: 1 }}>
                        <b style={{ fontSize: '1.1rem', color: 'var(--rd)' }}>{j.judul}</b>
                        <div style={{ marginTop: '0.4rem', color: '#555', fontSize: '0.95rem' }}>
                          <div>
                            📅 <b>Tanggal:</b> {tgl(j.tanggal)}
                          </div>
                          <div>
                            ⏰ <b>Jam:</b> {jamRange(j.jam_mulai, j.jam_selesai)}
                          </div>
                          <div>
                            📍 <b>Lokasi:</b> {j.lokasi}
                          </div>
                          {j.keterangan && (
                            <div className="muted" style={{ marginTop: '0.3rem', fontSize: '0.9rem' }}>
                              ℹ️ {j.keterangan}
                            </div>
                          )}
                        </div>
                      </div>
                      <div style={{ textAlign: 'right' }}>
                        <span className={`badge ${penuh ? 'b-dibatalkan' : 'b-diterima'}`}>
                          {penuh ? 'Kuota Penuh' : `Sisa ${j.kuota - (j.terdaftar ?? 0)}`}
                        </span>
                      </div>
                    </label>
                  );
                })
              )}
            </div>

            <div className="perhatian">
              <Icon name="info" />
              <div>
                <b>Perhatian Sebelum Mendaftar</b>
                Pastikan Anda dalam keadaan sehat, berusia minimal 17 tahun, berat badan minimal 45 kg,
                cukup tidur minimal 5 jam, dan tidak sedang mengonsumsi obat-obatan keras.
              </div>
            </div>

            <button
              className="btn blok"
              type="submit"
              disabled={submitting || jadwalList.length === 0}
            >
              {submitting ? 'Memproses Pendaftaran...' : 'Konfirmasi & Daftar Sekarang'}
            </button>
          </form>
        </>
      )}
    </ProtectedRoute>
  );
}
