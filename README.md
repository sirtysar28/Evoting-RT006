# 🗳️ Aplikasi E-Voting Ketua RT 006 / RW 001 Jatiwarna

Aplikasi e-voting modern berbasis website untuk pemilihan calon Ketua RT 006 / RW 001,
Kelurahan Jatiwarna, Kecamatan Pondok Melati, Kota Bekasi, Provinsi Jawa Barat — Periode Masa Bakti **2026–2031**.

aplikasi web full-stack dengan database, autentikasi, captcha, dan dashboard monitoring real-time.

---

## ✨ Fitur Utama

### 🌐 Halaman Pemilih (Publik)
| Fitur | Keterangan |
|---|---|
| **Verifikasi NIK** | Pemilih wajib memasukkan NIK KTP 16 digit. NIK harus terdaftar di **DPT hasil upload Excel panitia** — jika tidak terdaftar, akses ditolak. |
| **Kartu calon interaktif** | Terkunci 🔒 sebelum verifikasi; terbuka dengan animasi setelah NIK sah. Efek tilt 3D saat hover. |
| **Anti pemilih ganda** | NIK yang sudah memilih otomatis ditolak sistem (database transaction + lock). |
| **Bukti memilih digital** | Tiket berkode unik + **QR Code**, bisa dicetak / disimpan PDF. |
| **Desain modern** | Glassmorphism, **parallax mouse & scroll**, animasi reveal on-scroll, counter animasi, gradient bergerak, toast notification. |
| **☀️/🌙 Toggle terang–gelap** | **SEMUA halaman (voting, login admin, dashboard) default mode terang (light)** dengan tombol toggle dark mode — pilihan tersimpan otomatis (localStorage) & sinkron antar halaman. |
| **📱 PWA (Progressive Web App)** | Bisa di-*install* di HP (Android/iOS) seperti aplikasi native — manifest, service worker, ikon 192/512 + maskable, app shortcuts, offline-ready untuk aset statis. |
| **🖼️ Logo & favicon** | Logo resmi `E-Voting-RT006-Jatiwarna.png` dipasang sebagai favicon, apple-touch-icon, ikon PWA, hero halaman voting, sidebar admin & halaman login. |
| **📲 Full responsive mobile** | Layout adaptif di semua halaman: sidebar admin jadi bar horizontal scrollable, grid 2 kolom di mobile, captcha & form nyaman disentuh, tanpa overflow horizontal (teruji di 375–412px). |

### 🛡️ Dashboard Admin (Panitia)
| Fitur | Keterangan |
|---|---|
| **Login + CAPTCHA 5 karakter** | Captcha gambar (GD) dengan noise & rotasi acak, sekali pakai, bisa di-refresh. |
| **Intip password** 👁️ | Semua kolom password (login admin & konfirmasi reset) punya tombol show/hide. |
| **Upload DPT Excel** | Drag & drop file `.xlsx` / `.xls` / `.csv` (kolom **NIK** & **NAMA**), auto-deteksi header. Warga yang sudah memilih datanya terkunci. |
| **Template & Export** | Unduh template Excel DPT, export hasil rekap ke Excel. |
| **Grafik & diagram modern** (Chart.js) | Doughnut distribusi suara, bar chart perolehan, line chart tren suara per jam. |
| **Quick count live** | Auto-refresh tiap 10 detik + progress bar animasi + info calon pemimpin sementara. |
| **Kelola calon** | Edit nama, visi, warna, dan upload foto paslon (tampil di halaman voting). |
| **📊 Leaderboard Publik** | Halaman `/leaderboard` untuk semua warga: **podium peringkat** (medali 🥇🥈🥉 + mahkota 👑 pemenang), klasemen lengkap dengan **persentase & progress bar animasi**, grafik doughnut & tren per jam, **auto-refresh 10 detik**, dan **confetti** otomatis saat ada calon unggul > 50%. |
| **Reset voting** | Hapus semua suara dengan konfirmasi password admin (DPT tetap). |
| **© Footer brand** | Tahun berjalan otomatis © **Digital Imagine Indonesia** (link: [digimagine.web.id](https://digimagine.web.id)) di halaman voting, login, dan dashboard admin. |

---

## 🚀 Cara Menjalankan

```bash
# 1. Install dependency (jika belum)
composer install

# 2. Konfigurasi environment
cp .env.example .env        # sudah tersedia .env siap pakai
php artisan key:generate

# 3a. Database SQLite (default, tanpa instalasi MySQL)
touch database/database.sqlite

# 3b. — ATAU MySQL (shared hosting / cPanel) —
# sesuaikan .env:
#   DB_CONNECTION=mysql
#   DB_HOST=localhost
#   DB_PORT=3306
#   DB_DATABASE=u1658515_evoting
#   DB_USERNAME=<user_db>
#   DB_PASSWORD=<password_db>

# 4. Migrasi + seed (akun admin & calon awal)
php artisan migrate --seed

# 5. Link storage (untuk foto calon)
php artisan storage:link

# 6. Jalankan server
php artisan serve
```

> 💡 Query grafik tren per jam sudah **multi-database** (MySQL/MariaDB, PostgreSQL, SQLite)
> via `App\Services\VoteTrendService` — aman dipakai di hosting MySQL.


---

## 📋 Alur Penggunaan Pemilihan

1. **Panitia** login ke `/admin/login` (username + password + captcha 5 karakter).
2. Buka menu **Kelola DPT Warga** → upload file Excel berisi kolom `NIK` dan `NAMA`
   (contoh file: `docs/Contoh-DPT-Warga-RT006.xlsx`, atau unduh template dari dashboard).
3. **Warga** membuka halaman utama → memasukkan NIK KTP 16 digit → nama muncul otomatis.
4. Kartu calon terbuka → warga klik calon pilihannya → muncul modal konfirmasi.
5. Setelah dikonfirmasi, suara tersimpan & warga menerima **bukti memilih ber-QR**.
6. Panitia memantau hasil real-time di **Dashboard** (doughnut, bar, line chart + quick count).

---

## 🗂️ Struktur Penting

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── HomeController.php          # Halaman voting + verifikasi NIK + simpan suara
│   │   ├── CaptchaController.php       # Generator captcha gambar 5 karakter (GD)
│   │   └── Admin/
│   │       ├── AuthController.php      # Login/logout admin + validasi captcha
│   │       ├── DashboardController.php # Statistik & data grafik (live JSON)
│   │       └── DptController.php       # Import/export Excel, reset voting, kelola calon
│   └── Middleware/AdminAuth.php        # Proteksi sesi admin
├── Models/ (Admin, Candidate, Voter, Vote)
database/
├── migrations/                          # admins, candidates, voters, votes
└── seeders/DatabaseSeeder.php           # Akun admin + 2 calon awal
public/
├── css/app.css                          # Design system (glassmorphism, animasi, parallax)
└── js/ (app.js, voting.js, admin.js, admin-dashboard.js)
resources/views/
├── voting/index.blade.php               # Halaman pemilih (hero parallax)
└── admin/ (login, layout, dashboard, dpt, candidates)
docs/
├── Contoh-DPT-Warga-RT006.xlsx          # Contoh file DPT siap upload
└── screenshots/                         # Tangkapan layar aplikasi
```

## 🔒 Keamanan

- CSRF token di semua form & request AJAX
- Rate limiting: verifikasi NIK (20/menit), voting (10/menit), login admin (10/menit)
- Captcha sekali pakai (terhapus dari session setelah diverifikasi/gagal)
- Password di-hash bcrypt; pemilih ganda ditolak dengan row-level locking
- Throttle & validasi ketat di semua endpoint

## 🧰 Teknologi

- **Backend:** Laravel 12.69, PHP 8.2+, SQLite (default, bisa ganti MySQL/PostgreSQL)
- **Excel:** PhpSpreadsheet 5.x
- **Frontend:** Vanilla JS + Chart.js 4 (CDN, async), QRCode.js (CDN + fallback offline)
- **Font:** Plus Jakarta Sans (Google Fonts, non-blocking)

---

© 2026 - Panitia Pemilihan RT 006 / RW 001 — Kelurahan Jatiwarna, Kec. Pondok Melati, Kota Bekasi.
# Evoting-RT006
