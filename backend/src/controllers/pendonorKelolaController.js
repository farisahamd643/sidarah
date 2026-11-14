const pool = require('../config/db');

const kosong = (x) => (x === undefined || x === '' ? null : x);

const KOLOM = ['nama_lengkap', 'nik', 'golongan_darah', 'rhesus', 'jenis_kelamin', 'tanggal_lahir', 'no_hp', 'alamat'];

function bersihkan(body) {
  const b = {};
  for (const k of KOLOM) b[k] = kosong(body[k]);
  return b;
}

function periksa(b, wajib) {
  if (wajib && (!b.nama_lengkap || !b.jenis_kelamin || !b.tanggal_lahir)) {
    return 'nama_lengkap, jenis_kelamin, tanggal_lahir wajib diisi';
  }
  if (b.nik !== null && !/^\d{16}$/.test(b.nik)) return 'NIK harus 16 digit angka';
  if (b.golongan_darah !== null && !['A', 'B', 'AB', 'O'].includes(b.golongan_darah)) return 'Golongan darah harus A, B, AB, atau O';
  if (b.rhesus !== null && !['+', '-'].includes(b.rhesus)) return 'Rhesus harus + atau -';
  if (b.jenis_kelamin !== null && !['L', 'P'].includes(b.jenis_kelamin)) return 'Jenis kelamin harus L atau P';
  return null;
}

function errorDb(err, res) {
  if (err.code === '23505') return res.status(409).json({ pesan: 'NIK sudah dipakai pendonor lain' });
  if (['22007', '22008', '22P02', '23514'].includes(err.code)) {
    return res.status(400).json({ pesan: 'Format data tidak valid' });
  }
  console.error(err);
  return res.status(500).json({ pesan: 'Terjadi kesalahan server' });
}

async function tambah(req, res) {
  const b = bersihkan(req.body);
  const salah = periksa(b, true);
  if (salah) return res.status(400).json({ pesan: salah });
  try {
    const r = await pool.query(
      `INSERT INTO pendonor (nama_lengkap, nik, golongan_darah, rhesus, jenis_kelamin, tanggal_lahir, no_hp, alamat)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8) RETURNING *`,
      [b.nama_lengkap, b.nik, b.golongan_darah, b.rhesus, b.jenis_kelamin, b.tanggal_lahir, b.no_hp, b.alamat]
    );
    res.status(201).json(r.rows[0]);
  } catch (err) {
    errorDb(err, res);
  }
}

async function ubah(req, res) {
  const id = Number(req.params.id);
  if (!Number.isInteger(id)) return res.status(400).json({ pesan: 'ID tidak valid' });
  const b = bersihkan(req.body);
  const salah = periksa(b, false);
  if (salah) return res.status(400).json({ pesan: salah });

  const client = await pool.connect();
  try {
    await client.query('BEGIN');
    const r = await client.query(
      `UPDATE pendonor SET
         nama_lengkap = COALESCE($1, nama_lengkap), nik = COALESCE($2, nik),
         golongan_darah = COALESCE($3, golongan_darah), rhesus = COALESCE($4, rhesus),
         jenis_kelamin = COALESCE($5, jenis_kelamin), tanggal_lahir = COALESCE($6, tanggal_lahir),
         no_hp = COALESCE($7, no_hp), alamat = COALESCE($8, alamat)
       WHERE id_pendonor = $9 RETURNING *`,
      [b.nama_lengkap, b.nik, b.golongan_darah, b.rhesus, b.jenis_kelamin, b.tanggal_lahir, b.no_hp, b.alamat, id]
    );
    if (r.rows.length === 0) {
      await client.query('ROLLBACK');
      return res.status(404).json({ pesan: 'Pendonor tidak ditemukan' });
    }
    if (b.nama_lengkap && r.rows[0].id_user) {
      await client.query('UPDATE users SET nama = $1 WHERE id_user = $2', [b.nama_lengkap, r.rows[0].id_user]);
    }
    await client.query('COMMIT');
    res.json(r.rows[0]);
  } catch (err) {
    await client.query('ROLLBACK');
    errorDb(err, res);
  } finally {
    client.release();
  }
}

async function hapus(req, res) {
  const id = Number(req.params.id);
  if (!Number.isInteger(id)) return res.status(400).json({ pesan: 'ID tidak valid' });
  const client = await pool.connect();
  try {
    await client.query('BEGIN');
    const r = await client.query('DELETE FROM pendonor WHERE id_pendonor = $1 RETURNING id_user', [id]);
    if (r.rows.length === 0) {
      await client.query('ROLLBACK');
      return res.status(404).json({ pesan: 'Pendonor tidak ditemukan' });
    }
    if (r.rows[0].id_user) {
      await client.query("DELETE FROM users WHERE id_user = $1 AND role = 'pendonor'", [r.rows[0].id_user]);
    }
    await client.query('COMMIT');
    res.json({ pesan: 'Pendonor dihapus' });
  } catch (err) {
    await client.query('ROLLBACK');
    errorDb(err, res);
  } finally {
    client.release();
  }
}

module.exports = { tambah, ubah, hapus };
