# TaskMate – Aplikasi Pengelolaan Tugas Mahasiswa

Aplikasi web modern, responsif, dan interaktif yang dirancang khusus untuk mahasiswa dalam mengelola tugas perkuliahan, mengatur tenggat waktu (deadline), memantau progres pengerjaan, dan menjaga produktivitas belajar.

---

## 🚀 Fitur Unggulan

1. **Autentikasi & Akun Mahasiswa**
   - Registrasi akun dengan data identitas mahasiswa (Nama, Email, NIM, Universitas, Program Studi).
   - Login dengan validasi dan enkripsi kata sandi menggunakan `password_hash()` (Bcrypt).
   - Fitur 1-Click Demo Login untuk kemudahan evaluasi dan pengujian langsung.
   - Manajemen session aman dengan proteksi halaman (*Auth Guard*).

2. **Dashboard Interaktif**
   - 5 Kartu statistik akurat langsung dari database:
     - 📊 Total Seluruh Tugas
     - ⏳ Belum Dikerjakan
     - 🔄 Sedang Dikerjakan
     - ✅ Sudah Selesai
     - ⚠️ Deadline Terdekat & Tugas Terlambat
   - Progress Bar visual persentase penyelesaian tugas semester/kuliah.
   - Panel tugas prioritas mendekati deadline dengan penanda sisa waktu dinamis.
   - Panel daftar tugas terbaru yang baru saja ditambahkan.

3. **Pengelolaan Tugas (Full CRUD)**
   - **Tambah Tugas**: Form lengkap dengan nama tugas, mata kuliah, deskripsi, tanggal & jam deadline, tingkat prioritas (*Rendah, Sedang, Tinggi*), dan status awal.
   - **Tampilan Fleksibel**: Dapat beralih secara instan antara **Tampilan Tabel** dan **Tampilan Kartu (Grid)**.
   - **Pencarian Cepat**: Live search berdasarkan judul tugas, mata kuliah, atau deskripsi.
   - **Multi-Filter & Sorting**:
     - Filter status: *Semua, Belum Dikerjakan, Sedang Dikerjakan, Selesai*.
     - Filter prioritas: *Semua, Rendah, Sedang, Tinggi*.
     - Filter waktu: *Hari Ini, 7 Hari Ke Depan, Terlambat, Belum Selesai*.
     - Urutkan: *Deadline Terdekat, Deadline Terjauh, Prioritas Tertinggi, Terbaru*.
   - **Quick Status Changer**: Ubah status pengerjaan tugas secara langsung melalui dropdown tanpa memuat ulang halaman (*AJAX/Fast API*).
   - **Edit Tugas**: Perbarui detail tugas kapan saja dengan verifikasi kepemilikan data.
   - **Hapus Tugas**: Dialog modal konfirmasi pencegah ketidaksengajaan.

4. **Kalender Deadline Bulanan Interaktif**
   - Tampilan kalender 1 bulan penuh dengan penanda badge warna-warni pada setiap tanggal yang memiliki tugas.
   - Navigasi bulan (*Sebelumnya*, *Berikutnya*, dan tombol pintas *Hari Ini*).
   - Klik tanggal atau event untuk membuka modal detail tugas pada tanggal tersebut, lengkap dengan tombol pintas tambah tugas di tanggal tersebut.

5. **Pengingat Deadline & Notifikasi**
   - Ikon lonceng notifikasi pada navbar dengan indikator badge berkedip (*pulsing badge*).
   - Notifikasi otomatis untuk tugas yang jatuh tempo dalam kurun waktu 48 jam dan tugas yang telah melewati batas waktu (*Overdue*).

6. **Profil & Keamanan Akun**
   - Lihat ringkasan profil, NIM, kampus, serta rasio produktivitas tugas.
   - Formulir perbarui profil mahasiswa.
   - Formulir ganti password akun dengan verifikasi kata sandi lama.

---

## 📁 Struktur Folder Proyek

```text
c:\eno ppl\
├── assets/
│   ├── css/
│   │   └── style.css            # Desain antarmuka modern (Pastel Purple & Sky Blue)
│   └── js/
│       ├── app.js               # Interaktivitas UI, Modal, AJAX Quick Status, Toasts
│       └── calendar.js          # Logika kalender deadline bulanan & navigasi
├── api/
│   ├── update_status.php        # Endpoint AJAX pengubah status tugas
│   └── get_calendar_events.php  # Endpoint AJAX pengambilan data deadline kalender
├── config/
│   ├── config.php               # Konfigurasi aplikasi, helper tanggal Indo, session
│   └── database.php             # Konektor PDO MySQL (XAMPP) & SQLite Fallback
├── database/
│   ├── taskmate.sql             # Skema DDL MySQL & sample data awal
│   └── taskmate.sqlite          # Database SQLite lokal (auto-generated)
├── includes/
│   ├── auth.php                 # Helper autentikasi dan session guard
│   ├── footer.php               # Komponen footer, modal konfirmasi, skrip JS
│   ├── header.php               # Komponen HTML header dan font
│   ├── navbar.php               # Top navbar dengan search dan lonceng notifikasi
│   ├── sidebar.php              # Sidebar navigasi modern responsif
│   └── task_functions.php       # Query database dan logika bisnis tugas
├── tests/
│   ├── run_tests.php            # Test suite otomatis pengujian seluruh fitur
│   └── test_http.php            # Pengujian request HTTP, session, dan halaman
├── calendar.php                 # Halaman Kalender Deadline
├── dashboard.php                # Halaman Dashboard Utama
├── index.php                    # Router utama
├── login.php                    # Halaman Masuk
├── logout.php                   # Handler Logout
├── profile.php                  # Halaman Profil Mahasiswa
├── register.php                 # Halaman Pendaftaran Akun
├── task-create.php              # Halaman Tambah Tugas Baru
├── task-edit.php                # Halaman Edit Tugas
└── tasks.php                    # Halaman Daftar & Manajemen Tugas
```

---

## 🛠️ Petunjuk Instalasi & Menjalankan Aplikasi

Aplikasi TaskMate dirancang sangat fleksibel:
- **Koneksi Utama**: Menggunakan **MySQL** (Standar XAMPP: database `taskmate`, user `root`, password kosong).
- **Auto-Fallback Cerdas**: Jika MySQL belum dijalankan, aplikasi otomatis menggunakan **SQLite** lokal tanpa perlu instalasi tambahan, sehingga aplikasi langsung berjalan tanpa error.

### Cara 1: Menggunakan PHP Built-in Server (Paling Cepat & Praktis)

1. Buka Terminal / PowerShell di folder proyek (`c:\eno ppl`).
2. Jalankan server lokal PHP:
   ```powershell
   php -S 127.0.0.1:8000
   ```
   *(Atau gunakan path XAMPP jika belum ada di environment PATH: `& "C:\xampp\php\php.exe" -S 127.0.0.1:8000`)*
3. Buka browser dan akses:
   [http://127.0.0.1:8000](http://127.0.0.1:8000)

### Cara 2: Menggunakan XAMPP (Apache & MySQL)

1. Pindahkan atau tautkan folder proyek ke dalam direktori `htdocs` XAMPP (misal `C:\xampp\htdocs\taskmate`).
2. Buka **XAMPP Control Panel**, lalu klik **Start** pada modul **Apache** dan **MySQL**.
3. Buka browser dan buka **phpMyAdmin** di [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
4. Buat database baru bernama `taskmate`.
5. Buka tab **Import**, pilih file `database/taskmate.sql`, lalu klik **Import**.
6. Akses aplikasi melalui browser di:
   [http://localhost/taskmate](http://localhost/taskmate)

---

## 🔑 Akun Demo Pengujian

Untuk mempermudah pengujian, sistem telah menyediakan akun demo siap pakai:

- **Email**: `mahasiswa@taskmate.id`
- **Password**: `password123`

*(Pada halaman login tersedia tombol **"Gunakan Akun Demo Otomatis"** untuk mengisi form dalam 1 klik).*

---

## 🧪 Hasil Pengujian Fungsionalitas Otomatis

Aplikasi dilengkapi dengan test suite otomatis yang dapat dijalankan kapan saja dengan perintah:
```powershell
php tests/run_tests.php
```

### Rekap Pengujian Fitur:
- [x] **Koneksi Database & Auto-Migration**: LULUS (Tabel `users` dan `tasks` terbuat secara otomatis)
- [x] **Autentikasi Pengguna**: LULUS (Login, Register, Session Guard, Bcrypt Hashing)
- [x] **Isolasi Data Mahasiswa**: LULUS (Setiap mahasiswa hanya dapat melihat dan mengelola tugas miliknya)
- [x] **CRUD Tugas**: LULUS (Tambah tugas, Tampilkan daftar, Edit tugas, Hapus tugas)
- [x] **Pencarian & Multi-Filter**: LULUS (Pencarian nama tugas/matkul, filter status, filter prioritas, filter deadline)
- [x] **Quick Status Updater**: LULUS (Ubah status secara instan via API)
- [x] **Kalender Deadline**: LULUS (Pengambilan event bulanan dan penanda tanggal)
- [x] **Pengingat Deadline**: LULUS (Kalkulasi akurat tugas mendekati deadline dan overdue)
- [x] **Profil Pengguna**: LULUS (Update informasi mahasiswa dan pergantian password)
- [x] **HTTP End-to-End**: LULUS (Status 200 OK pada seluruh rute aplikasi)
