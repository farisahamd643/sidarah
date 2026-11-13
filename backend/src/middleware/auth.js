const jwt = require('jsonwebtoken');

function auth(req, res, next) {
  const header = req.headers.authorization || '';
  const token = header.startsWith('Bearer ') ? header.slice(7) : null;
  if (!token) return res.status(401).json({ pesan: 'Token tidak ditemukan' });
  try {
    req.user = jwt.verify(token, process.env.JWT_SECRET);
    next();
  } catch {
    res.status(401).json({ pesan: 'Token tidak valid atau kedaluwarsa' });
  }
}

function adminOnly(req, res, next) {
  if (!req.user || req.user.role !== 'admin') {
    return res.status(403).json({ pesan: 'Hanya admin yang boleh mengakses' });
  }
  next();
}

module.exports = { auth, adminOnly };
