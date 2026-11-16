const pool = require('../config/db');

async function list(req, res) {
  const cari = req.query.cari ? `%${req.query.cari}%` : null;
  const gol = req.query.golongan_darah || null;
  try {
    const r = await pool.query(
      `SELECT d.id_pendonor, d.nama_lengkap, d.golongan_darah, d.rhesus, d.jenis_kelamin,
              d.tanggal_lahir, d.no_hp, u.email
       FROM pendonor d LEFT JOIN users u ON u.id_user = d.id_user
       WHERE ($1::text IS NULL OR d.nama_lengkap ILIKE $1 OR u.email ILIKE $1)
         AND ($2::text IS NULL OR d.golongan_darah = $2)
       ORDER BY d.nama_lengkap`,
      [cari, gol]
    );
    res.json(r.rows);
  } catch (err) {
    console.error(err);
    res.status(500).json({ pesan: 'Terjadi kesalahan server' });
  }
}

async function detail(req, res) {
  const id = Number(req.params.id);
  if (!Number.isInteger(id)) return res.status(400).json({ pesan: 'ID tidak valid' });
  try {
    const d = await pool.query(
      `SELECT d.*, u.email FROM pendonor d LEFT JOIN users u ON u.id_user = d.id_user
       WHERE d.id_pendonor = $1`,
      [id]
    );
    if (d.rows.length === 0) return res.status(404).json({ pesan: 'Pendonor tidak ditemukan' });
    const riwayat = await pool.query(
      `SELECT p.id_pendaftaran, p.status, j.judul, j.tanggal, j.lokasi
       FROM pendaftaran_donor p JOIN jadwal_donor j ON j.id_jadwal = p.id_jadwal
       WHERE p.id_pendonor = $1 ORDER BY j.tanggal DESC`,
      [id]
    );
    res.json({ ...d.rows[0], riwayat: riwayat.rows });
  } catch (err) {
    console.error(err);
    res.status(500).json({ pesan: 'Terjadi kesalahan server' });
  }
}

module.exports = { list, detail };
