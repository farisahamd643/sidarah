const router = require('express').Router();
const { auth, adminOnly } = require('../middleware/auth');
const c = require('../controllers/pendaftaranController');

router.post('/', auth, c.daftar);
router.get('/saya', auth, c.saya);
router.patch('/:id/batal', auth, c.batal);
router.get('/', auth, adminOnly, c.listAdmin);
router.patch('/:id/status', auth, adminOnly, c.ubahStatus);

module.exports = router;
