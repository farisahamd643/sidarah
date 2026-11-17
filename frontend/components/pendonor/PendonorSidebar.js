'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { useAuth } from '@/context/AuthContext';
import { Icon } from '@/components/shared/Icons';

export default function PendonorSidebar() {
  const pathname = usePathname();
  const { user } = useAuth();

  const links = [
    { href: '/dashboard', label: 'Dashboard', icon: 'catatan' },
    { href: '/pendaftaran', label: 'Daftar Donor', icon: 'darah' },
    { href: '/status', label: 'Status & Riwayat', icon: 'kalender' },
    { href: '/jadwal', label: 'Jadwal Saya', icon: 'kalender' },
    { href: '/profil', label: 'Profil Saya', icon: 'orang' },
    { href: '/syarat', label: 'Syarat & Ketentuan', icon: 'info' },
  ];

  return (
    <aside className="card" style={{ padding: '1rem', marginBottom: '1.2rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: '0.8rem', marginBottom: '1rem' }}>
        <div className="lingkar" style={{ background: 'var(--rl)', padding: '0.6rem', borderRadius: '50%', lineHeight: 0 }}>
          <Icon name="orang" />
        </div>
        <div>
          <b style={{ display: 'block', fontSize: '1rem' }}>{user?.nama || 'Pendonor'}</b>
          <small className="muted">{user?.email || 'Akun Pendonor'}</small>
        </div>
      </div>
      <div style={{ display: 'flex', flexDirection: 'column', gap: '0.4rem' }}>
        {links.map((link) => {
          const active = pathname === link.href;
          return (
            <Link
              key={link.href}
              href={link.href}
              style={{
                display: 'flex',
                alignItems: 'center',
                gap: '0.6rem',
                padding: '0.5rem 0.8rem',
                borderRadius: '8px',
                textDecoration: 'none',
                color: active ? '#fff' : 'var(--tx)',
                background: active ? 'var(--r)' : 'transparent',
                fontWeight: active ? '600' : 'normal',
                fontSize: '0.95rem',
              }}
            >
              <span>{link.label}</span>
            </Link>
          );
        })}
      </div>
    </aside>
  );
}
