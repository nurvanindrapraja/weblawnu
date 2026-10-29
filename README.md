# LAWNU JATIM

Website resmi **Lembaga Aset dan Wakaf (LAW) Pengurus Wilayah Nahdlatul Ulama Jawa Timur** — portal informasi digital untuk pengelolaan, pendataan, publikasi, dan pemberdayaan aset wakaf di Jawa Timur.

## Latar Belakang & Tujuan

Lembaga Aset dan Wakaf PWNU Jatim memiliki peran strategis dalam pengelolaan, pendataan, pengamanan, pengembangan, dan pemberdayaan aset wakaf serta pertanahan di lingkungan Nahdlatul Ulama. Website ini dibangun dengan tujuan:

1. **Portal informasi resmi** — pusat berita, profil, regulasi, dan dokumentasi kegiatan lembaga yang terstruktur dan mudah diakses.
2. **Manajemen konten mandiri** — pengurus, reporter, dan redaksi dapat mengelola konten secara mandiri melalui CMS berbasis web (mirip konsep WordPress, namun custom dengan Laravel).
3. **Alur kerja editorial** — berita dan galeri dari reporter melalui proses review redaktur sebelum dipublikasikan, sehingga kualitas konten terjaga.
4. **Interaksi dua arah** — pengunjung dapat berkirim pesan/kontak dengan sistem tiket pelacakan status, memberi rating pada berita, serta mendaftar menjadi kontributor (reporter/redaktur).
5. **Transparansi & akuntabilitas** — seluruh aktivitas pengelolaan terekam dalam activity log, dan data lembaga tersimpan rapi dalam database terpusat.

## Perubahan dari Website Lama (LWP)

Website sebelumnya adalah **Website Lembaga Wakaf dan Pertanahan (LWP)** dengan nama "LWP PWNU Jatim". Versi ini merupakan pembaruan besar:

| Aspek | Website Lama (LWP) | Website Baru (LAWNU JATIM) |
| --- | --- | --- |
| Nama & identitas | LWP PWNU Jatim | **LAWNU JATIM** — Lembaga Aset dan Wakaf PWNU Jatim |
| Struktur menu | Menu datar tanpa submenu | Menu utama ringkas + **submenu** (Wakaf, Pertanahan, Opini, Regulasi, Tokoh, Tanya Jawab, dll. di bawah BERITA) |
| Beranda | Daftar artikel statis | **Slideshow** 5 berita terbaru + hero profil lembaga |
| Galeri | Tidak ada | **Galeri Kegiatan** — multi-gambar per kegiatan, caption per gambar, lightbox, alur review redaktur |
| Registrasi | Tidak ada | **Daftar akun publik** (reporter/redaktur) + verifikasi admin |
| Kontak | Form biasa | **Sistem tiket** (`TKT-XXXXXX`) + halaman cek status publik + balasan dari admin |
| Rating | Tidak ada | **Rating bintang** pengunjung per berita (rata-rata & jumlah tercatat) |
| Peran | Admin saja | 3 peran: **Admin, Reporter, Redaktur** dengan alur kerja submit → review → publish |
| Media | File manager sederhana | **Media library** dengan pencarian live AJAX, unggah multi, dan proteksi hapus (media di berita published tidak bisa dihapus reporter) |
| Monitoring | Tidak ada | **Activity log** lengkap + **badge notifikasi** item menunggu review di sidebar & dashboard |
| Database | MySQL | MySQL (produksi) + SQLite (development) |
| SEO & UX | Dasar | Slug otomatis, meta title/description, breadcrumb, favicon NU, 100% responsive |

## Fitur

### Sisi Publik
- Beranda dengan slideshow berita terbaru dan profil lembaga.
- Berita per kategori (Wakaf, Pertanahan, Opini, Regulasi, Tokoh, Tanya Jawab, Dokumen, dll.) dengan breadcrumb dan pagination.
- Galeri kegiatan multi-gambar dengan caption dan lightbox.
- Halaman statis (Tentang LAWNU, Visi Misi, Sejarah, Struktur Organisasi, Program).
- Form kontak dengan **nomor tiket** dan halaman cek status tiket + balasan resmi.
- Rating bintang pada halaman berita.
- Halaman pendaftaran kontributor (reporter/redaktur).
- Menu navigasi dinamis (kelola dari admin via tabel menu).

### Panel Admin
- Manajemen berita (create/edit/publish/unpublish/reject dengan catatan), filter & pencarian live AJAX, **sortir kolom** (judul, kategori, penulis, tgl publikasi, views), preview tampilan publik.
- Manajemen **Galeri Kegiatan** (upload multi-gambar, caption per gambar, publish langsung atau via review).
- Media library (25 per halaman, pencarian live, proteksi hapus untuk media yang dipakai berita published).
- Manajemen halaman, kategori, tag, dan menu.
- Manajemen pengguna (modal tambah user), role & permission (spatie/laravelesque-style).
- **Pendaftaran** kontributor: verifikasi approve/reject (approve otomatis membuat user + role).
- Pesan kontak: detail, update status, **balas ke pengunjung** (status naik ke `in_progress`).
- Rating & **Activity Log** dengan filter per modul.
- Dashboard statistik + banner notifikasi item menunggu review.

### Portal Reporter
- Membuat/mengedit berita & galeri kegiatan (editor TinyMCE).
- Submit ke redaktur; hanya bisa mengubah item berstatus *draft/rejected*.
- Dashboard statistik pribadi (terkirim, direview, published, ditolak).
- Akses media library (unggah & hapus media milik sendiri yang belum dipakai).

### Panel Redaktur
- Review berita & galeri yang *submitted*: approve (langsung publish) atau reject dengan catatan.
- Banner notifikasi & badge jumlah item menunggu review.
- Akses pesan kontak (tampil, detail, ubah status — tanpa hapus).

## Teknologi

| Komponen | Teknologi |
| --- | --- |
| Backend | Laravel 13 (PHP 8.3+) |
| Frontend | Blade + Tailwind CSS + Alpine.js |
| Editor konten | TinyMCE 7 |
| Database | MySQL/MariaDB (produksi), SQLite (development) |
| Queue & cache | Database driver |
| Testing | PHPUnit (feature & unit) |
| Format kode | Laravel Pint |

## Menjalankan Lokal

```bash
# 1. Clone
git clone https://github.com/nurvanindrapraja/weblawnu.git
cd weblawnu

# 2. Dependensi
composer install
npm install

# 3. Konfigurasi lingkungan
cp .env.example .env
php artisan key:generate
# Sesuaikan DB_CONNECTION/DB_* di .env (default .env.example: SQLite)

# 4. Database
php artisan migrate --seed
php artisan storage:link

# 5. Jalankan
composer run dev          # atau: npm run dev && php artisan serve
```

Untuk development dengan upload gambar besar, naikkan limit PHP:

```bash
php -d upload_max_filesize=20M -d post_max_size=25M artisan serve
```

### User Awal (hasil seeder)

| Role | Username | Password |
| --- | --- | --- |
| Admin | `admin` | `password` |
| Reporter | `reporter` | `password` |
| Redaktur | `redaktur` | `password` |

## Catatan Produksi

- Isi `.env` sesuai hosting: `APP_URL`, `APP_ENV=production`, kredensial MySQL, `SESSION_DOMAIN` bila perlu.
- File upload (isi `storage/app/public/`) **tidak** ikut di repository — upload manual ke hosting lalu `php artisan storage:link`.
- Seeding ulang data awal: `php artisan db:seed --class=DatabaseSeeder`.

## Pengujian

```bash
php artisan test
vendor/bin/pint --test
```
