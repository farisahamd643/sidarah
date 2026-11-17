export default function AdminTable({ headers = [], children, emptyMessage = 'Tidak ada data ditemukan.', isEmpty = false }) {
  return (
    <div className="tw">
      <table>
        {headers.length > 0 && (
          <thead>
            <tr>
              {headers.map((h, i) => (
                <th key={i}>{h}</th>
              ))}
            </tr>
          </thead>
        )}
        <tbody>
          {isEmpty ? (
            <tr>
              <td colSpan={headers.length || 1} className="muted" style={{ textAlign: 'center', padding: '1.5rem' }}>
                {emptyMessage}
              </td>
            </tr>
          ) : (
            children
          )}
        </tbody>
      </table>
    </div>
  );
}
