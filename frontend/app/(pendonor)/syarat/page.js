import Link from 'next/link';

export default function SyaratPage() {
  return (
    <div className="card auth" style={{ maxWidth: '760px' }}>
      <h1>Syarat &amp; Ketentuan dan Kebijakan Privasi</h1>
      
      <h2>1. Tujuan</h2>
      <p>
        Akun SiDarah dipakai untuk mendaftar kegiatan donor darah dan untuk membantu admin
        mengelola data pendonor serta jadwal kegiatan.
      </p>

      <h2>2. Data yang disimpan</h2>
      <p>
        Nama lengkap, NIK, tanggal lahir, jenis kelamin, nomor HP, email, alamat, golongan darah
        dan rhesus, serta riwayat pendaftaran donor Anda.
      </p>

      <h2>3. Penggunaan data</h2>
      <p>
        Data hanya digunakan untuk pengelolaan kegiatan donor darah oleh admin SiDarah dan tidak
        dibagikan kepada pihak lain di luar keperluan tersebut.
      </p>

      <h2>4. Keamanan</h2>
      <p>
        Password disimpan dalam bentuk terenkripsi (hash), bukan teks biasa. Jaga kerahasiaan
        password Anda.
      </p>

      <h2>5. Hak pengguna</h2>
      <p>
        Anda dapat melihat dan memperbarui data pribadi di menu Profil. Permintaan penghapusan
        akun dapat disampaikan kepada admin.
      </p>

      <h2>6. Kewajiban pengguna</h2>
      <p>Pengguna wajib mengisi data dengan benar dan tidak menyalahgunakan akun.</p>

      <p className="muted" style={{ marginTop: '1.5rem' }}>
        <em>Catatan: dokumen ini disusun untuk sistem informasi SiDarah.</em>
      </p>

      <p style={{ marginTop: '1.5rem' }}>
        <Link className="btn o" href="/register">
          Kembali ke Pendaftaran
        </Link>
      </p>
    </div>
  );
}
