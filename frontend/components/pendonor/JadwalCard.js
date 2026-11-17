import { Icon, JamIcon, PanIcon } from '@/components/shared/Icons';
import { jadwalTeks, jamRange, labelStatus, statusBadgeClass } from '@/lib/formatters';
import Link from 'next/link';

export default function JadwalCard({ jadwal, href = '/status' }) {
  if (!jadwal) return null;

  return (
    <Link className="jd" href={href}>
      <div className="lingkar">
        <Icon name="kalender" />
      </div>
      <div className="isi">
        <h3>{jadwalTeks(jadwal.tanggal)}</h3>
        {jadwal.status && (
          <span className={statusBadgeClass(jadwal.status)} style={{ marginBottom: '0.4rem', display: 'inline-block' }}>
            {labelStatus(jadwal.status)}
          </span>
        )}
        <div className="info">
          <Icon name="pin" />
          <span>{jadwal.lokasi}</span>
        </div>
        <div className="info">
          <JamIcon />
          <span>{jamRange(jadwal.jam_mulai, jadwal.jam_selesai)}</span>
        </div>
      </div>
      <PanIcon />
    </Link>
  );
}
