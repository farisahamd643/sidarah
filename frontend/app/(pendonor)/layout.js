'use client';

import PendonorNavbar from '@/components/pendonor/PendonorNavbar';
import ProtectedRoute from '@/components/shared/ProtectedRoute';

export default function PendonorLayout({ children }) {
  return (
    <ProtectedRoute roleRequired="pendonor">
      <PendonorNavbar />
      <main className="wrap">{children}</main>
    </ProtectedRoute>
  );
}
