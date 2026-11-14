process.env.JWT_SECRET = 'rahasia-khusus-tes';
process.env.DATABASE_URL = process.env.DATABASE_URL || 'postgresql://tes:tes@localhost:5432/tes';

const test = require('node:test');
const assert = require('node:assert/strict');
const jwt = require('jsonwebtoken');
const app = require('../src/index.js');

let server;
let base;
const tokenPendonor = jwt.sign({ id: 1, role: 'pendonor' }, process.env.JWT_SECRET);
const tokenAdmin = jwt.sign({ id: 2, role: 'admin' }, process.env.JWT_SECRET);

test.before(async () => {
  await new Promise((r) => { server = app.listen(0, r); });
  base = `http://127.0.0.1:${server.address().port}`;
});
test.after(() => server.close());

async function kirim(method, path, { token, body, raw } = {}) {
  const adaBody = raw !== undefined || body !== undefined;
  const res = await fetch(base + path, {
    method,
    headers: {
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...(adaBody ? { 'Content-Type': 'application/json' } : {}),
    },
    body: raw !== undefined ? raw : body !== undefined ? JSON.stringify(body) : undefined,
  });
  return { status: res.status, data: await res.json() };
}

test('endpoint tidak dikenal mengembalikan 404', async () => {
  const r = await kirim('GET', '/api/tidakada');
  assert.equal(r.status, 404);
});

test('JSON rusak mengembalikan 400', async () => {
  const r = await kirim('POST', '/api/auth/login', { raw: '{rusak' });
  assert.equal(r.status, 400);
  assert.equal(r.data.pesan, 'Format JSON tidak valid');
});

test('profil tanpa token ditolak 401', async () => {
  const r = await kirim('GET', '/api/profil');
  assert.equal(r.status, 401);
});

test('token palsu ditolak 401', async () => {
  const r = await kirim('GET', '/api/profil', { token: 'abc.def.ghi' });
  assert.equal(r.status, 401);
});

test('pendonor biasa tidak boleh melihat daftar pendonor (403)', async () => {
  const r = await kirim('GET', '/api/pendonor', { token: tokenPendonor });
  assert.equal(r.status, 403);
});

test('pendonor biasa tidak boleh menambah jadwal (403)', async () => {
  const r = await kirim('POST', '/api/jadwal', { token: tokenPendonor, body: {} });
  assert.equal(r.status, 403);
});

test('register dengan data kurang ditolak 400', async () => {
  const r = await kirim('POST', '/api/auth/register', { body: { nama: 'A' } });
  assert.equal(r.status, 400);
});

test('register dengan password pendek ditolak 400', async () => {
  const r = await kirim('POST', '/api/auth/register', {
    body: { nama: 'A', email: 'a@a.com', password: '123', jenis_kelamin: 'L', tanggal_lahir: '2000-01-01' },
  });
  assert.equal(r.status, 400);
});

test('login tanpa password ditolak 400', async () => {
  const r = await kirim('POST', '/api/auth/login', { body: { email: 'a@a.com' } });
  assert.equal(r.status, 400);
});

test('pendaftaran tanpa id_jadwal valid ditolak 400', async () => {
  const r = await kirim('POST', '/api/pendaftaran', { token: tokenPendonor, body: {} });
  assert.equal(r.status, 400);
});

test('admin menambah jadwal tanpa lokasi ditolak 400', async () => {
  const r = await kirim('POST', '/api/jadwal', { token: tokenAdmin, body: { judul: 'X' } });
  assert.equal(r.status, 400);
});

test('id jadwal bukan angka ditolak 400', async () => {
  const r = await kirim('GET', '/api/jadwal/abc');
  assert.equal(r.status, 400);
});

test('admin mengubah status pendaftaran dengan nilai ngawur ditolak 400', async () => {
  const r = await kirim('PATCH', '/api/pendaftaran/1/status', { token: tokenAdmin, body: { status: 'ngawur' } });
  assert.equal(r.status, 400);
});
