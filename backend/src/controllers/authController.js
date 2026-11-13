const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const pool = require('../config/db');

async function register(req, res) {
  const { nama, email, password, jenis_kelamin, tanggal_lahir, nik, no_hp, alamat } = req.body;
  if (!nama || !email || !password || !jenis_kelamin || !tanggal_lahir) {
    return res.status(400).json({ pesan: 'nama, email, password, jenis_kelamin, tanggal_lahir wajib diisi' });
  }
  if (password.length < 8) {
    return res.status(400).json({ pesan: 'Password minimal 8 karakter' });
  }

  const client = await pool.connect();
  try {
    await client.query('BEGIN');
    const hash = await bcrypt.hash(password, 10);
    const u = await client.query(
      "INSERT INTO users (nama, email, password, role) VALUES ($1, $2, $3, 'pendonor') RETURNING id_user, nama, email, role",
      [nama, email.toLowerCase().trim(), hash]
    );
    await client.query(
      'INSERT INTO pendonor (id_user, nama_lengkap, nik, jenis_kelamin, tanggal_lahir, no_hp, alamat) VALUES ($1, $2, $3, $4, $5, $6, $7)',
      [u.rows[0].id_user, nama, nik || null, jenis_kelamin, tanggal_lahir, no_hp || null, alamat || null]
    );
    await client.query('COMMIT');
    res.status(201).json({ pesan: 'Registrasi berhasil', user: u.rows[0] });
  } catch (err) {
    await client.query('ROLLBACK');
    if (err.code === '23505') return res.status(409).json({ pesan: 'Email atau NIK sudah terdaftar' });
    if (err.code === '23514' || err.code === '22007') return res.status(400).json({ pesan: 'Format data tidak valid' });
    console.error(err);
    res.status(500).json({ pesan: 'Terjadi kesalahan server' });
  } finally {
    client.release();
  }
}

async function login(req, res) {
  const { email, password } = req.body;
  if (!email || !password) {
    return res.status(400).json({ pesan: 'Email dan password wajib diisi' });
  }
  try {
    const r = await pool.query('SELECT * FROM users WHERE email = $1', [email.toLowerCase().trim()]);
    const user = r.rows[0];
    if (!user || !(await bcrypt.compare(password, user.password))) {
      return res.status(401).json({ pesan: 'Email atau password salah' });
    }
    const token = jwt.sign({ id: user.id_user, role: user.role }, process.env.JWT_SECRET, { expiresIn: '1d' });
    res.json({
      token,
      user: { id_user: user.id_user, nama: user.nama, email: user.email, role: user.role },
    });
  } catch (err) {
    console.error(err);
    res.status(500).json({ pesan: 'Terjadi kesalahan server' });
  }
}

module.exports = { register, login };
