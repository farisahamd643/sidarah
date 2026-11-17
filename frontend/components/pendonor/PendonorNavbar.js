'use client';

import { useState } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { useAuth } from '@/context/AuthContext';

export default function PendonorNavbar() {
  const [menuOpen, setMenuOpen] = useState(false);
  const pathname = usePathname();
  const { logoutUser } = useAuth();

  const toggleMenu = () => setMenuOpen(!menuOpen);
  const closeMenu = () => setMenuOpen(false);

  const isActive = (path) => pathname === path || (path !== '/' && pathname.startsWith(path));

  return (
    <header className="top">
      <Link className="brand" href="/dashboard" onClick={closeMenu}>
        🩸 SiDarah
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
          className={isActive('/dashboard') ? 'on' : ''}
          href="/dashboard"
          onClick={closeMenu}
        >
          Dashboard
        </Link>
        <Link
          className={isActive('/pendaftaran') ? 'on' : ''}
          href="/pendaftaran"
          onClick={closeMenu}
        >
          Pendaftaran
        </Link>
        <Link
          className={isActive('/status') ? 'on' : ''}
          href="/status"
          onClick={closeMenu}
        >
          Status Pendaftaran
        </Link>
        <Link
          className={isActive('/jadwal') ? 'on' : ''}
          href="/jadwal"
          onClick={closeMenu}
        >
          Jadwal Saya
        </Link>
        <Link
          className={isActive('/profil') ? 'on' : ''}
          href="/profil"
          onClick={closeMenu}
        >
          Profil
        </Link>
        <Link
          className={isActive('/syarat') ? 'on' : ''}
          href="/syarat"
          onClick={closeMenu}
        >
          Syarat
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
