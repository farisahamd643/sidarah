const bcrypt = require('bcryptjs');
const pool = require('../config/db');

async function lihat(req, res) {
  try {
    const r = await pool.query('SELECT id_user, nama, email, role FROM users WHERE id_user = $1', [req.user.id]);
    if (r.rows.length === 0) return res.status(404).json({ pesan: 'Akun tidak ditemukan' });
    res.json(r.rows[0]);
  } catch (err) {
    console.error(err);
    res.status(500).json({ pesan: 'Terjadi kesalahan server' });
  }
}

async function ubahNama(req, res) {
  const nama = (req.body.nama || '').trim();
  if (!nama) return res.status(400).json({ pesan: 'Nama wajib diisi' });
  const client = await pool.connect();
  try {
    await client.query('BEGIN');
    const r = await client.query(
      'UPDATE users SET nama = $1 WHERE id_user = $2 RETURNING id_user, nama, email, role',
      [nama, req.user.id]
    );
    if (r.rows.length === 0) {
      await client.query('ROLLBACK');
      return res.status(404).json({ pesan: 'Akun tidak ditemukan' });
    }
    await client.query('UPDATE pendonor SET nama_lengkap = $1 WHERE id_user = $2', [nama, req.user.id]);
    await client.query('COMMIT');
    res.json({ pesan: 'Nama diperbarui', akun: r.rows[0] });
  } catch (err) {
    await client.query('ROLLBACK');
    console.error(err);
    res.status(500).json({ pesan: 'Terjadi kesalahan server' });
  } finally {
    client.release();
  }
}

async function gantiPassword(req, res) {
  const { password_lama, password_baru } = req.body;
  if (!password_lama || !password_baru) {
    return res.status(400).json({ pesan: 'password_lama dan password_baru wajib diisi' });
  }
  if (password_baru.length < 8) {
    return res.status(400).json({ pesan: 'Password baru minimal 8 karakter' });
  }
  try {
    const r = await pool.query('SELECT password FROM users WHERE id_user = $1', [req.user.id]);
    if (r.rows.length === 0) return res.status(404).json({ pesan: 'Akun tidak ditemukan' });
    if (!(await bcrypt.compare(password_lama, r.rows[0].password))) {
      return res.status(401).json({ pesan: 'Password lama salah' });
    }
    const hash = await bcrypt.hash(password_baru, 10);
    await pool.query('UPDATE users SET password = $1 WHERE id_user = $2', [hash, req.user.id]);
    res.json({ pesan: 'Password berhasil diubah' });
  } catch (err) {
    console.error(err);
    res.status(500).json({ pesan: 'Terjadi kesalahan server' });
  }
}

module.exports = { lihat, ubahNama, gantiPassword };
