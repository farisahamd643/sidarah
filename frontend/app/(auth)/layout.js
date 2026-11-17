import PublicNavbar from '@/components/shared/PublicNavbar';

export default function AuthLayout({ children }) {
  return (
    <>
      <PublicNavbar />
      <main className="wrap">{children}</main>
    </>
  );
}
