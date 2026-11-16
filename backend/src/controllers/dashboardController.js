const pool = require('../config/db');

const JEDA_HARI = 60; // jeda minimal antar donor, sesuaikan dengan halaman syarat

async function ringkasan(req, res) {
  try {
    if (req.user.role === 'admin') {
      const r = await pool.query(
        `SELECT
           (SELECT COUNT(*) FROM pendonor)::int AS total_pendonor,
           (SELECT COUNT(*) FROM jadwal_donor
              WHERE tanggal >= (NOW() AT TIME ZONE 'Asia/Jakarta')::date)::int AS jadwal_mendatang,
           (SELECT COUNT(*) FROM pendaftaran_donor WHERE status = 'menunggu')::int AS menunggu,
           (SELECT COUNT(*) FROM pendaftaran_donor WHERE status = 'diterima')::int AS diterima,
           (SELECT COUNT(*) FROM pendaftaran_donor WHERE status = 'selesai')::int AS selesai`
      );
      return res.json({ role: 'admin', ...r.rows[0] });
    }

    const r = await pool.query(
      `SELECT COUNT(p.id_pendaftaran) FILTER (WHERE p.status <> 'dibatalkan')::int AS total_pendaftaran,
              COUNT(p.id_pendaftaran) FILTER (WHERE p.status = 'selesai')::int AS total_donor,
              MAX(j.tanggal) FILTER (WHERE p.status = 'selesai') AS donor_terakhir
       FROM pendonor d
       LEFT JOIN pendaftaran_donor p ON p.id_pendonor = d.id_pendonor
       LEFT JOIN jadwal_donor j ON j.id_jadwal = p.id_jadwal
       WHERE d.id_user = $1
       GROUP BY d.id_pendonor`,
      [req.user.id]
    );
    if (r.rows.length === 0) return res.status(404).json({ pesan: 'Profil pendonor tidak ditemukan' });

    const data = r.rows[0];
    let bolehDonorLagi = null;
    if (data.donor_terakhir) {
      const t = new Date(data.donor_terakhir + 'T00:00:00Z');
      t.setUTCDate(t.getUTCDate() + JEDA_HARI);
      bolehDonorLagi = t.toISOString().slice(0, 10);
    }
    res.json({ role: 'pendonor', ...data, boleh_donor_lagi: bolehDonorLagi });
  } catch (err) {
    console.error(err);
    res.status(500).json({ pesan: 'Terjadi kesalahan server' });
  }
}

module.exports = { ringkasan };
