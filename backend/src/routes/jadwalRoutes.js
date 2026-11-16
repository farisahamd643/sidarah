const router = require('express').Router();
const { auth, adminOnly } = require('../middleware/auth');
const c = require('../controllers/jadwalController');

router.get('/', c.list);
router.get('/:id', c.detail);
router.post('/', auth, adminOnly, c.create);
router.put('/:id', auth, adminOnly, c.update);
router.delete('/:id', auth, adminOnly, c.remove);

module.exports = router;
