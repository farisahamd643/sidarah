'use client';

import { useState } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { useAuth } from '@/context/AuthContext';

export default function AdminNavbar() {
  const [menuOpen, setMenuOpen] = useState(false);
  const pathname = usePathname();
  const { user, logoutUser } = useAuth();

  const toggleMenu = () => setMenuOpen(!menuOpen);
  const closeMenu = () => setMenuOpen(false);

  const isActive = (path) => pathname === path || (path !== '/admin' && pathname.startsWith(path));

  return (
    <header className="top">
      <Link className="brand" href="/admin/dashboard" onClick={closeMenu}>
        🩸 SiDarah Admin
      </Link>
      <button
        id="menuBtn"
        className="burger"
        aria-label="Menu"
        onClick={toggleMenu}
      >
        ☰
      </button>
      <nav id="nav" className={menuOpen ? 'open' : ''}>
        <Link
          className={isActive('/admin/dashboard') || pathname === '/admin' ? 'on' : ''}
          href="/admin/dashboard"
          onClick={closeMenu}
        >
          Dashboard
        </Link>
        <Link
          className={isActive('/admin/pendonor') ? 'on' : ''}
          href="/admin/pendonor"
          onClick={closeMenu}
        >
          Data Pendonor
        </Link>
        <Link
          className={isActive('/admin/golongan') ? 'on' : ''}
          href="/admin/golongan"
          onClick={closeMenu}
        >
          Golongan Darah
        </Link>
        <Link
          className={isActive('/admin/jadwal') ? 'on' : ''}
          href="/admin/jadwal"
          onClick={closeMenu}
        >
          Jadwal Donor
        </Link>
        <Link
          className={isActive('/admin/pendaftaran') ? 'on' : ''}
          href="/admin/pendaftaran"
          onClick={closeMenu}
        >
          Pendaftaran
        </Link>
        <Link
          className={isActive('/admin/profil') ? 'on' : ''}
          href="/admin/profil"
          onClick={closeMenu}
        >
          Profil
        </Link>
        <a
          href="#logout"
          onClick={(e) => {
            e.preventDefault();
            closeMenu();
            logoutUser();
          }}
        >
          Keluar
        </a>
      </nav>
    </header>
  );
}
