const pool = require('../config/db');

const QUERY_DASAR = `
  SELECT j.*,
         COUNT(p.id_pendaftaran) FILTER (WHERE p.status <> 'dibatalkan')::int AS terdaftar
  FROM jadwal_donor j
  LEFT JOIN pendaftaran_donor p ON p.id_jadwal = j.id_jadwal
`;

function errorDb(err, res) {
  if (['23514', '22007', '22008', '22P02'].includes(err.code)) {
    return res.status(400).json({ pesan: 'Format data tidak valid' });
  }
  console.error(err);
  return res.status(500).json({ pesan: 'Terjadi kesalahan server' });
}

async function list(req, res) {
  try {
    const r = await pool.query(QUERY_DASAR + ' GROUP BY j.id_jadwal ORDER BY j.tanggal, j.jam_mulai');
    res.json(r.rows);
  } catch (err) {
    errorDb(err, res);
  }
}

async function detail(req, res) {
  const id = Number(req.params.id);
  if (!Number.isInteger(id)) return res.status(400).json({ pesan: 'ID tidak valid' });
  try {
    const r = await pool.query(QUERY_DASAR + ' WHERE j.id_jadwal = $1 GROUP BY j.id_jadwal', [id]);
    if (r.rows.length === 0) return res.status(404).json({ pesan: 'Jadwal tidak ditemukan' });
    res.json(r.rows[0]);
  } catch (err) {
    errorDb(err, res);
  }
}

async function create(req, res) {
  const { judul, tanggal, jam_mulai, jam_selesai, lokasi, keterangan, kuota } = req.body;
  if (!judul || !tanggal || !jam_mulai || !jam_selesai || !lokasi) {
    return res.status(400).json({ pesan: 'judul, tanggal, jam_mulai, jam_selesai, lokasi wajib diisi' });
  }
  try {
    const r = await pool.query(
      'INSERT INTO jadwal_donor (judul, tanggal, jam_mulai, jam_selesai, lokasi, keterangan, kuota) VALUES ($1, $2, $3, $4, $5, $6, COALESCE($7, 50)) RETURNING *',
      [judul, tanggal, jam_mulai, jam_selesai, lokasi, keterangan || null, kuota]
    );
    res.status(201).json(r.rows[0]);
  } catch (err) {
    errorDb(err, res);
  }
}

async function update(req, res) {
  const id = Number(req.params.id);
  if (!Number.isInteger(id)) return res.status(400).json({ pesan: 'ID tidak valid' });
  const { judul, tanggal, jam_mulai, jam_selesai, lokasi, keterangan, kuota } = req.body;
  try {
    const r = await pool.query(
      `UPDATE jadwal_donor SET
         judul = COALESCE($1, judul), tanggal = COALESCE($2, tanggal),
         jam_mulai = COALESCE($3, jam_mulai), jam_selesai = COALESCE($4, jam_selesai),
         lokasi = COALESCE($5, lokasi), keterangan = COALESCE($6, keterangan),
         kuota = COALESCE($7, kuota)
       WHERE id_jadwal = $8 RETURNING *`,
      [judul, tanggal, jam_mulai, jam_selesai, lokasi, keterangan, kuota, id]
    );
    if (r.rows.length === 0) return res.status(404).json({ pesan: 'Jadwal tidak ditemukan' });
    res.json(r.rows[0]);
  } catch (err) {
    errorDb(err, res);
  }
}

async function remove(req, res) {
  const id = Number(req.params.id);
  if (!Number.isInteger(id)) return res.status(400).json({ pesan: 'ID tidak valid' });
  try {
    const r = await pool.query('DELETE FROM jadwal_donor WHERE id_jadwal = $1 RETURNING id_jadwal', [id]);
    if (r.rows.length === 0) return res.status(404).json({ pesan: 'Jadwal tidak ditemukan' });
    res.json({ pesan: 'Jadwal dihapus' });
  } catch (err) {
    errorDb(err, res);
  }
}

module.exports = { list, detail, create, update, remove };
