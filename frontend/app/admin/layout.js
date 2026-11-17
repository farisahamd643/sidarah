'use client';

import AdminNavbar from '@/components/admin/AdminNavbar';
import ProtectedRoute from '@/components/shared/ProtectedRoute';

export default function AdminLayout({ children }) {
  return (
    <ProtectedRoute roleRequired="admin">
      <AdminNavbar />
      <main className="wrap">{children}</main>
    </ProtectedRoute>
  );
}
