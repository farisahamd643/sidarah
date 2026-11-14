require('dotenv').config();
const bcrypt = require('bcryptjs');
const pool = require('../config/db');

(async () => {
  const email = (process.env.ADMIN_EMAIL || '').toLowerCase().trim();
  const nama = process.env.ADMIN_NAMA || 'Administrator';
  const password = process.env.ADMIN_PASSWORD || '';
  if (!email || password.length < 8) {
    console.error('Isi ADMIN_EMAIL dan ADMIN_PASSWORD (minimal 8 karakter)');
    process.exit(1);
  }
  const hash = await bcrypt.hash(password, 10);
  const r = await pool.query(
    `INSERT INTO users (nama, email, password, role) VALUES ($1, $2, $3, 'admin')
     ON CONFLICT (email) DO UPDATE SET password = EXCLUDED.password, role = 'admin', nama = EXCLUDED.nama
     RETURNING id_user, email, role`,
    [nama, email, hash]
  );
  console.log('Admin siap:', r.rows[0]);
  await pool.end();
})().catch((e) => {
  console.error(e.message);
  process.exit(1);
});
