import '@/styles/globals.css';
import { AuthProvider } from '@/context/AuthContext';
import Footer from '@/components/shared/Footer';

export const metadata = {
  title: 'SiDarah - Sistem Informasi Donor Darah',
  description: 'Website Sistem Informasi Donor Darah SiDarah',
};

export const viewport = {
  width: 'device-width',
  initialScale: 1,
};

export default function RootLayout({ children }) {
  return (
    <html lang="id">
      <body>
        <AuthProvider>
          {children}
          <Footer />
        </AuthProvider>
      </body>
    </html>
  );
}
