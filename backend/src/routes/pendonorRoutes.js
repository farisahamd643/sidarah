const router = require('express').Router();
const { auth, adminOnly } = require('../middleware/auth');
const c = require('../controllers/pendonorController');
const k = require('../controllers/pendonorKelolaController');

router.get('/', auth, adminOnly, c.list);
router.get('/:id', auth, adminOnly, c.detail);
router.post('/', auth, adminOnly, k.tambah);
router.put('/:id', auth, adminOnly, k.ubah);
router.delete('/:id', auth, adminOnly, k.hapus);

module.exports = router;
