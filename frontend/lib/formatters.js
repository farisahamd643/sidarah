// Helper format tampilan tanggal, jam, status, dan golongan darah khas SiDarah

export function tgl(d) {
  if (!d) return '-';
  const bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  const dateObj = new Date(typeof d === 'string' && !d.includes('T') ? `${d}T00:00:00` : d);
  if (isNaN(dateObj.getTime())) return '-';
  return `${dateObj.getDate()} ${bulan[dateObj.getMonth() + 1]} ${dateObj.getFullYear()}`;
}

export function tglPanjang(d) {
  if (!d) return '-';
  const bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  const dateObj = new Date(typeof d === 'string' && !d.includes('T') ? `${d}T00:00:00` : d);
  if (isNaN(dateObj.getTime())) return '-';
  return `${dateObj.getDate()} ${bulan[dateObj.getMonth() + 1]} ${dateObj.getFullYear()}`;
}

export function hariNama(d) {
  const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
  const dateObj = new Date(typeof d === 'string' && !d.includes('T') ? `${d}T00:00:00` : d);
  if (isNaN(dateObj.getTime())) return '';
  return hari[dateObj.getDay()];
}

export function jadwalTeks(d) {
  if (!d) return '-';
  return `${hariNama(d)}, ${tglPanjang(d)}`;
}

export function jam(j) {
  if (!j) return '-';
  return j.substring(0, 5);
}

export function jamRange(mulai, selesai) {
  if (!mulai || !selesai) return '-';
  const m = jam(mulai).replace(':', '.');
  const s = jam(selesai).replace(':', '.');
  return `${m} – ${s} WIB`;
}

export function labelStatus(s) {
  if (!s) return '-';
  if (s === 'menunggu') return 'Terdaftar';
  return s.charAt(0).toUpperCase() + s.slice(1);
}

export function statusBadgeClass(s) {
  switch (s) {
    case 'menunggu':
      return 'badge b-menunggu';
    case 'diterima':
      return 'badge b-diterima';
    case 'dibatalkan':
      return 'badge b-dibatalkan';
    case 'selesai':
      return 'badge b-selesai';
    default:
      return 'badge';
  }
}

export function labelGol(gol, rh) {
  if (!gol) return '-';
  return `${gol}${rh || ''}`;
}
