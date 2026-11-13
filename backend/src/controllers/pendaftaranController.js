const pool = require('../config/db');

class Gagal extends Error {
  constructor(status, pesan) {
    super(pesan);
    this.status = status;
  }
}

function tangani(err, res) {
  if (err instanceof Gagal) return res.status(err.status).json({ pesan: err.message });
  if (['22P02', '23514'].includes(err.code)) return res.status(400).json({ pesan: 'Format data tidak valid' });
  console.error(err);
  return res.status(500).json({ pesan: 'Terjadi kesalahan server' });
}

async function daftar(req, res) {
  const idJadwal = Number(req.body.id_jadwal);
  if (!Number.isInteger(idJadwal)) return res.status(400).json({ pesan: 'id_jadwal wajib diisi' });

  const client = await pool.connect();
  try {
    await client.query('BEGIN');

    const p = await client.query(
      `SELECT id_pendonor, DATE_PART('year', AGE(CURRENT_DATE, tanggal_lahir))::int AS umur
       FROM pendonor WHERE id_user = $1`,
      [req.user.id]
    );
    if (p.rows.length === 0) throw new Gagal(404, 'Profil pendonor tidak ditemukan');
    const { id_pendonor, umur } = p.rows[0];
    if (umur < 17) throw new Gagal(400, 'Pendonor minimal berusia 17 tahun');

    const j = await client.query(
      `SELECT kuota, (tanggal >= (NOW() AT TIME ZONE 'Asia/Jakarta')::date) AS masih_buka
       FROM jadwal_donor WHERE id_jadwal = $1 FOR UPDATE`,
      [idJadwal]
    );
    if (j.rows.length === 0) throw new Gagal(404, 'Jadwal tidak ditemukan');
    if (!j.rows[0].masih_buka) throw new Gagal(400, 'Jadwal sudah lewat');

    const ada = await client.query(
      'SELECT id_pendaftaran, status FROM pendaftaran_donor WHERE id_pendonor = $1 AND id_jadwal = $2',
      [id_pendonor, idJadwal]
    );
    if (ada.rows.length > 0 && ada.rows[0].status !== 'dibatalkan') {
      throw new Gagal(409, 'Kamu sudah terdaftar di jadwal ini');
    }

    const n = await client.query(
      `SELECT COUNT(*)::int AS jumlah FROM pendaftaran_donor WHERE id_jadwal = $1 AND status <> 'dibatalkan'`,
      [idJadwal]
    );
    if (n.rows[0].jumlah >= j.rows[0].kuota) throw new Gagal(409, 'Kuota jadwal sudah penuh');

    let hasil;
    if (ada.rows.length > 0) {
      hasil = await client.query(
        `UPDATE pendaftaran_donor SET status = 'menunggu', tanggal_daftar = NOW()
         WHERE id_pendaftaran = $1 RETURNING *`,
        [ada.rows[0].id_pendaftaran]
      );
    } else {
      hasil = await client.query(
        'INSERT INTO pendaftaran_donor (id_pendonor, id_jadwal) VALUES ($1, $2) RETURNING *',
        [id_pendonor, idJadwal]
      );
    }
    await client.query('COMMIT');
    res.status(201).json({ pesan: 'Pendaftaran berhasil', pendaftaran: hasil.rows[0] });
  } catch (err) {
    await client.query('ROLLBACK');
    tangani(err, res);
  } finally {
    client.release();
  }
}

async function saya(req, res) {
  try {
    const r = await pool.query(
      `SELECT p.id_pendaftaran, p.status, p.tanggal_daftar,
              j.id_jadwal, j.judul, j.tanggal, j.jam_mulai, j.jam_selesai, j.lokasi
       FROM pendaftaran_donor p
       JOIN pendonor d ON d.id_pendonor = p.id_pendonor
       JOIN jadwal_donor j ON j.id_jadwal = p.id_jadwal
       WHERE d.id_user = $1
       ORDER BY j.tanggal DESC`,
      [req.user.id]
    );
    res.json(r.rows);
  } catch (err) {
    tangani(err, res);
  }
}

async function batal(req, res) {
  const id = Number(req.params.id);
  if (!Number.isInteger(id)) return res.status(400).json({ pesan: 'ID tidak valid' });
  try {
    const r = await pool.query(
      `UPDATE pendaftaran_donor p SET status = 'dibatalkan'
       FROM pendonor d
       WHERE p.id_pendaftaran = $1 AND p.id_pendonor = d.id_pendonor
         AND d.id_user = $2 AND p.status IN ('menunggu', 'diterima')
       RETURNING p.id_pendaftaran, p.status`,
      [id, req.user.id]
    );
    if (r.rows.length === 0) {
      return res.status(404).json({ pesan: 'Pendaftaran tidak ditemukan atau tidak bisa dibatalkan' });
    }
    res.json({ pesan: 'Pendaftaran dibatalkan', pendaftaran: r.rows[0] });
  } catch (err) {
    tangani(err, res);
  }
}

async function listAdmin(req, res) {
  const idJadwal = req.query.id_jadwal ? Number(req.query.id_jadwal) : null;
  if (req.query.id_jadwal && !Number.isInteger(idJadwal)) {
    return res.status(400).json({ pesan: 'id_jadwal tidak valid' });
  }
  try {
    const r = await pool.query(
      `SELECT p.id_pendaftaran, p.status, p.tanggal_daftar,
              d.id_pendonor, d.nama_lengkap, d.golongan_darah, d.rhesus, d.no_hp,
              j.id_jadwal, j.judul, j.tanggal
       FROM pendaftaran_donor p
       JOIN pendonor d ON d.id_pendonor = p.id_pendonor
       JOIN jadwal_donor j ON j.id_jadwal = p.id_jadwal
       WHERE ($1::int IS NULL OR p.id_jadwal = $1)
       ORDER BY p.tanggal_daftar DESC`,
      [idJadwal]
    );
    res.json(r.rows);
  } catch (err) {
    tangani(err, res);
  }
}

async function ubahStatus(req, res) {
  const id = Number(req.params.id);
  const { status } = req.body;
  if (!Number.isInteger(id)) return res.status(400).json({ pesan: 'ID tidak valid' });
  if (!['menunggu', 'diterima', 'dibatalkan', 'selesai'].includes(status)) {
    return res.status(400).json({ pesan: 'Status harus: menunggu, diterima, dibatalkan, atau selesai' });
  }
  try {
    const r = await pool.query(
      'UPDATE pendaftaran_donor SET status = $1 WHERE id_pendaftaran = $2 RETURNING *',
      [status, id]
    );
    if (r.rows.length === 0) return res.status(404).json({ pesan: 'Pendaftaran tidak ditemukan' });
    res.json(r.rows[0]);
  } catch (err) {
    tangani(err, res);
  }
}

module.exports = { daftar, saya, batal, listAdmin, ubahStatus };
