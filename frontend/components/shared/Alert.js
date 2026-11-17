export default function Alert({ type = 'danger', message, onClose }) {
  if (!message) return null;

  const typeClass =
    type === 'success' ? 'a-success' : type === 'warning' ? 'a-warning' : 'a-danger';

  return (
    <div
      className={`alert ${typeClass}`}
      style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}
    >
      <div>{message}</div>
      {onClose && (
        <button
          type="button"
          onClick={onClose}
          style={{
            background: 'none',
            border: 'none',
            color: 'inherit',
            fontWeight: 'bold',
            cursor: 'pointer',
            marginLeft: '1rem',
            fontSize: '1.2rem',
            lineHeight: 1,
          }}
          aria-label="Tutup"
        >
          ×
        </button>
      )}
    </div>
  );
}
