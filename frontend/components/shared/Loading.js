export default function Loading({ text = 'Memuat data...', fullScreen = false }) {
  if (fullScreen) {
    return (
      <div
        style={{
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          justifyContent: 'center',
          minHeight: '60vh',
          gap: '1rem',
        }}
      >
        <div
          style={{
            width: '40px',
            height: '40px',
            border: '4px solid var(--rl)',
            borderTop: '4px solid var(--r)',
            borderRadius: '50%',
            animation: 'spin 0.8s linear infinite',
          }}
        />
        <p className="muted" style={{ margin: 0, fontSize: '1rem' }}>
          {text}
        </p>
        <style jsx>{`
          @keyframes spin {
            0% {
              transform: rotate(0deg);
            }
            100% {
              transform: rotate(360deg);
            }
          }
        `}</style>
      </div>
    );
  }

  return (
    <p className="muted" style={{ padding: '0.8rem 0' }}>
      ⏳ {text}
    </p>
  );
}
