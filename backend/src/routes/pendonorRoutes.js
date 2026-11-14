const router = require('express').Router();
const { auth, adminOnly } = require('../middleware/auth');
const c = require('../controllers/pendonorController');

router.get('/', auth, adminOnly, c.list);
router.get('/:id', auth, adminOnly, c.detail);

module.exports = router;
