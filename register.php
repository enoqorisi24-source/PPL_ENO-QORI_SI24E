<?php
/**
 * TaskMate - Halaman Pendaftaran Akun Mahasiswa
 */
require_once __DIR__ . '/includes/auth.php';
require_guest();

$error = '';
$nama = '';
$email = '';
$nim = '';
$universitas = '';
$jurusan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama        = trim($_POST['nama'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $nim         = trim($_POST['nim'] ?? '');
    $universitas = trim($_POST['universitas'] ?? '');
    $jurusan     = trim($_POST['jurusan'] ?? '');
    $password    = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validasi
    if (empty($nama) || empty($email) || empty($password)) {
        $error = 'Nama lengkap, email, dan password wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format alamat email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal harus 6 karakter.';
    } elseif ($password !== $password_confirm) {
        $error = 'Konfirmasi password tidak cocok dengan password yang dimasukkan.';
    } else {
        try {
            $pdo = Database::getConnection();

            // Cek apakah email sudah terdaftar
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email tersebut sudah terdaftar di TaskMate. Silakan gunakan email lain atau login.';
            } else {
                // Hash password secara aman
                $hashed = password_hash($password, PASSWORD_BCRYPT);

                $insertStmt = $pdo->prepare("
                    INSERT INTO users (nama, email, password, nim, universitas, jurusan)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $insertStmt->execute([
                    $nama,
                    $email,
                    $hashed,
                    $nim ?: null,
                    $universitas ?: 'Perguruan Tinggi',
                    $jurusan ?: 'Mahasiswa'
                ]);

                $newUserId = (int)$pdo->lastInsertId();

                // Berikan 2 tugas selamat datang awal agar akun baru tidak kosong
                $d1 = date('Y-m-d 23:59:00', strtotime('+2 days'));
                $d2 = date('Y-m-d 17:00:00', strtotime('+5 days'));
                $tStmt = $pdo->prepare("INSERT INTO tasks (user_id, judul, mata_kuliah, deskripsi, deadline, prioritas, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $tStmt->execute([$newUserId, 'Menjelajahi Fitur TaskMate', 'Orientasi Perkuliahan', 'Coba tandai tugas ini selesai atau tambahkan tugas kuliah pertama Anda!', $d1, 'Rendah', 'Belum Dikerjakan']);
                $tStmt->execute([$newUserId, 'Mempersiapkan Rencana Semester Ini', 'Manajemen Waktu', 'Susun target IPK dan daftar mata kuliah semester ini.', $d2, 'Sedang', 'Belum Dikerjakan']);

                // Auto login
                login_user([
                    'id' => $newUserId,
                    'nama' => $nama,
                    'email' => $email
                ]);

                set_flash('success', "Akun Anda berhasil dibuat! Selamat datang di TaskMate, {$nama} 🎓");
                redirect('dashboard.php');
            }
        } catch (Throwable $e) {
            $error = 'Terjadi kesalahan database: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Mahasiswa | TaskMate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">

<div class="auth-card" style="max-width: 520px;">
    <div class="auth-header">
        <div class="brand-icon-wrapper">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                <path d="M2 17l10 5 10-5"></path>
                <path d="M2 12l10 5 10-5"></path>
            </svg>
        </div>
        <h1>Daftar Akun Mahasiswa</h1>
        <p>Mulai atur tugas kuliah dan pantau deadline dengan mudah</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <div class="form-group">
            <label class="form-label" for="nama">Nama Lengkap Mahasiswa <span class="required">*</span></label>
            <input type="text" id="nama" name="nama" class="form-control" placeholder="Contoh: Ahmad Fauzi" value="<?= htmlspecialchars($nama) ?>" required autofocus>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="email">Alamat Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" class="form-control" placeholder="nama@kampus.ac.id" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="nim">NIM (Nomor Induk Mahasiswa)</label>
                <input type="text" id="nim" name="nim" class="form-control" placeholder="Contoh: 2201011054" value="<?= htmlspecialchars($nim) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="universitas">Universitas / Kampus</label>
                <input type="text" id="universitas" name="universitas" class="form-control" placeholder="Contoh: Universitas Indonesia" value="<?= htmlspecialchars($universitas) ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="jurusan">Jurusan / Program Studi</label>
                <input type="text" id="jurusan" name="jurusan" class="form-control" placeholder="Contoh: Teknik Informatika" value="<?= htmlspecialchars($jurusan) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="password">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Min. 6 karakter" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirm">Konfirmasi Password <span class="required">*</span></label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="Ulangi password" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;padding:12px;">
            Daftar Akun Baru &rarr;
        </button>
    </form>

    <div class="auth-footer">
        Sudah memiliki akun? <a href="login.php">Masuk di sini</a>
    </div>
</div>

</body>
</html>
