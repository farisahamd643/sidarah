require('dotenv').config();
const express = require('express');
const cors = require('cors');
const pool = require('./config/db');

const app = express();
app.use(cors());
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

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log(`Server jalan di port ${PORT}`));
