const pool = require('../config/db');

async function golongan(req, res) {
  try {
    const r = await pool.query(
      `SELECT COALESCE(golongan_darah, 'Belum diisi') AS golongan_darah,
              COALESCE(rhesus, '') AS rhesus,
              COUNT(*)::int AS jumlah
       FROM pendonor
       GROUP BY 1, 2
       ORDER BY 1, 2`
    );
    res.json(r.rows);
  } catch (err) {
    console.error(err);
    res.status(500).json({ pesan: 'Terjadi kesalahan server' });
  }
}

module.exports = { golongan };
