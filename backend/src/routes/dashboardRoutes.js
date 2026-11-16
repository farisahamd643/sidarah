const router = require('express').Router();
const { auth, adminOnly } = require('../middleware/auth');
const c = require('../controllers/dashboardController');
const s = require('../controllers/statistikController');

router.get('/', auth, c.ringkasan);
router.get('/golongan', auth, adminOnly, s.golongan);

module.exports = router;
