require('dotenv').config();
const { Pool, types } = require('pg');

// Kolom DATE dikembalikan sebagai teks "YYYY-MM-DD" (bukan objek Date)
types.setTypeParser(1082, (v) => v);

const pool = new Pool({
  connectionString: process.env.DATABASE_URL,
  ssl: { rejectUnauthorized: false },
  max: 5,
  idleTimeoutMillis: 10000,
});

// Mencegah server mati kalau koneksi idle diputus oleh database
pool.on('error', (err) => {
  console.error('Koneksi idle database terputus:', err.message);
});

module.exports = pool;
