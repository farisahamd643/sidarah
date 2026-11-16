const router = require('express').Router();
const { auth } = require('../middleware/auth');
const c = require('../controllers/akunController');

router.get('/', auth, c.lihat);
router.put('/', auth, c.ubahNama);
router.put('/password', auth, c.gantiPassword);

module.exports = router;
