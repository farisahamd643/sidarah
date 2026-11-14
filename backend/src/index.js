require('dotenv').config();
const express = require('express');
const cors = require('cors');
const pool = require('./config/db');

const app = express();
app.set('trust proxy', 1);
app.disable('x-powered-by');
app.use(cors({ origin: process.env.CORS_ORIGIN ? process.env.CORS_ORIGIN.split(',') : true }));
app.use(express.json());

app.get('/health', async (req, res) => {
  try {
    const r = await pool.query('SELECT NOW() AS waktu');
    res.json({ status: 'ok', db: 'terhubung', waktu: r.rows[0].waktu });
  } catch (err) {
    res.status(500).json({ status: 'error', pesan: err.message });
  }
});

app.use('/api/auth', require('./routes/authRoutes'));
app.use('/api/jadwal', require('./routes/jadwalRoutes'));
app.use('/api/pendaftaran', require('./routes/pendaftaranRoutes'));
app.use('/api/profil', require('./routes/profilRoutes'));
app.use('/api/pendonor', require('./routes/pendonorRoutes'));
app.use('/api/dashboard', require('./routes/dashboardRoutes'));

app.use((req, res) => res.status(404).json({ pesan: 'Endpoint tidak ditemukan' }));

app.use((err, req, res, next) => {
  if (err.type === 'entity.parse.failed') {
    return res.status(400).json({ pesan: 'Format JSON tidak valid' });
  }
  console.error(err);
  res.status(500).json({ pesan: 'Terjadi kesalahan server' });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log(`Server jalan di port ${PORT}`));
