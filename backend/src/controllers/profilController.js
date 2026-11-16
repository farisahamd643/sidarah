const pool = require('../config/db');

const kosongJadiNull = (x) => (x === undefined || x === '' ? null : x);

async function lihat(req, res) {
  try {
    const r = await pool.query(
      `SELECT d.id_pendonor, d.nama_lengkap, d.nik, d.golongan_darah, d.rhesus,
              d.jenis_kelamin, d.tanggal_lahir, d.no_hp, d.alamat,
              u.email, u.role
       FROM pendonor d JOIN users u ON u.id_user = d.id_user
       WHERE d.id_user = $1`,
      [req.user.id]
    );
    if (r.rows.length === 0) return res.status(404).json({ pesan: 'Profil tidak ditemukan' });
    res.json(r.rows[0]);
  } catch (err) {
    console.error(err);
    res.status(500).json({ pesan: 'Terjadi kesalahan server' });
  }
}

async function ubah(req, res) {
  const nama = kosongJadiNull(req.body.nama_lengkap);
  const nik = kosongJadiNull(req.body.nik);
  const gol = kosongJadiNull(req.body.golongan_darah);
  const rhesus = kosongJadiNull(req.body.rhesus);
  const hp = kosongJadiNull(req.body.no_hp);
  const alamat = kosongJadiNull(req.body.alamat);

  if (nik !== null && !/^\d{16}$/.test(nik)) {
    return res.status(400).json({ pesan: 'NIK harus 16 digit angka' });
  }
  if (gol !== null && !['A', 'B', 'AB', 'O'].includes(gol)) {
    return res.status(400).json({ pesan: 'Golongan darah harus A, B, AB, atau O' });
  }
  if (rhesus !== null && !['+', '-'].includes(rhesus)) {
    return res.status(400).json({ pesan: 'Rhesus harus + atau -' });
  }

  const client = await pool.connect();
  try {
    await client.query('BEGIN');
    const r = await client.query(
      `UPDATE pendonor SET
         nama_lengkap = COALESCE($1, nama_lengkap), nik = COALESCE($2, nik),
         golongan_darah = COALESCE($3, golongan_darah), rhesus = COALESCE($4, rhesus),
         no_hp = COALESCE($5, no_hp), alamat = COALESCE($6, alamat)
       WHERE id_user = $7
       RETURNING id_pendonor, nama_lengkap, nik, golongan_darah, rhesus, jenis_kelamin, tanggal_lahir, no_hp, alamat`,
      [nama, nik, gol, rhesus, hp, alamat, req.user.id]
    );
    if (r.rows.length === 0) {
      await client.query('ROLLBACK');
      return res.status(404).json({ pesan: 'Profil tidak ditemukan' });
    }
    if (nama !== null) {
      await client.query('UPDATE users SET nama = $1 WHERE id_user = $2', [nama, req.user.id]);
    }
    await client.query('COMMIT');
    res.json({ pesan: 'Profil diperbarui', profil: r.rows[0] });
  } catch (err) {
    await client.query('ROLLBACK');
    if (err.code === '23505') return res.status(409).json({ pesan: 'NIK sudah dipakai akun lain' });
    console.error(err);
    res.status(500).json({ pesan: 'Terjadi kesalahan server' });
  } finally {
    client.release();
  }
}

module.exports = { lihat, ubah };
