const router = require('express').Router();
const rateLimit = require('express-rate-limit');
const { register, login } = require('../controllers/authController');

const pembatas = rateLimit({
  windowMs: 15 * 60 * 1000,
  limit: 20,
  standardHeaders: true,
  legacyHeaders: false,
  message: { pesan: 'Terlalu banyak percobaan, coba lagi dalam 15 menit' },
});

router.post('/register', pembatas, register);
router.post('/login', pembatas, login);

module.exports = router;
