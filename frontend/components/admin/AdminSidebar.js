'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { useAuth } from '@/context/AuthContext';
import { Icon } from '@/components/shared/Icons';

export default function AdminSidebar() {
  const pathname = usePathname();
  const { user } = useAuth();

  const links = [
    { href: '/admin/dashboard', label: 'Dashboard Utama' },
    { href: '/admin/pendonor', label: 'Kelola Pendonor' },
    { href: '/admin/golongan', label: 'Informasi Golongan Darah' },
    { href: '/admin/jadwal', label: 'Kelola Jadwal Donor' },
    { href: '/admin/pendaftaran', label: 'Kelola Pendaftaran' },
    { href: '/admin/profil', label: 'Profil Administrator' },
  ];

  return (
    <aside className="card" style={{ padding: '1rem', marginBottom: '1.2rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: '0.8rem', marginBottom: '1rem' }}>
        <div className="lingkar" style={{ background: 'var(--rl)', padding: '0.6rem', borderRadius: '50%', lineHeight: 0 }}>
          <Icon name="orang" />
        </div>
        <div>
          <b style={{ display: 'block', fontSize: '1rem' }}>{user?.nama || 'Administrator'}</b>
          <small className="muted" style={{ color: 'var(--r)', fontWeight: 600 }}>Peran: Admin</small>
        </div>
      </div>
      <div style={{ display: 'flex', flexDirection: 'column', gap: '0.4rem' }}>
        {links.map((link) => {
          const active = pathname === link.href || (link.href !== '/admin/dashboard' && pathname.startsWith(link.href));
          return (
            <Link
              key={link.href}
              href={link.href}
              style={{
                display: 'flex',
                alignItems: 'center',
                gap: '0.6rem',
                padding: '0.55rem 0.8rem',
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
