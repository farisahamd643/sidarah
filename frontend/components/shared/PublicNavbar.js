'use client';

import { useState } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { useAuth } from '@/context/AuthContext';

export default function PublicNavbar() {
  const [menuOpen, setMenuOpen] = useState(false);
  const pathname = usePathname();
  const { user } = useAuth();

  const toggleMenu = () => setMenuOpen(!menuOpen);
  const closeMenu = () => setMenuOpen(false);

  const isAdmin = user && user.role === 'admin';
  const isPendonor = user && user.role === 'pendonor';

  return (
    <header className="top">
      <Link
        className="brand"
        href={isAdmin ? '/admin/dashboard' : isPendonor ? '/dashboard' : '/'}
        onClick={closeMenu}
      >
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
          className={pathname === '/' ? 'on' : ''}
          href="/"
          onClick={closeMenu}
        >
          Beranda
        </Link>
        {!user ? (
          <>
            <Link
              className={pathname === '/login' ? 'on' : ''}
              href="/login"
              onClick={closeMenu}
            >
              Login
            </Link>
            <Link
              className={pathname === '/register' ? 'on' : ''}
              href="/register"
              onClick={closeMenu}
            >
              Daftar
            </Link>
          </>
        ) : isAdmin ? (
          <Link href="/admin/dashboard" onClick={closeMenu}>
            Panel Admin
          </Link>
        ) : (
          <Link href="/dashboard" onClick={closeMenu}>
            Dashboard Pendonor
          </Link>
        )}
      </nav>
    </header>
  );
}
