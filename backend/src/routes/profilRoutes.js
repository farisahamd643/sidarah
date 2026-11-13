const router = require('express').Router();
const { auth } = require('../middleware/auth');
const c = require('../controllers/profilController');

router.get('/', auth, c.lihat);
router.put('/', auth, c.ubah);

module.exports = router;
