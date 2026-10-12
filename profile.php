<?php
/**
 * TaskMate - Profil Pengguna & Pengaturan Akun Mahasiswa
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/task_functions.php';

require_login();
$user = current_user();

$pageTitle = 'Profil Pengguna';
$pageSubtitle = 'Kelola data identitas mahasiswa dan keamanan akun Anda';
$activeNav = 'profile';

$stats = get_task_statistics($user['id']);
$error = '';
$success = '';

// 1. Penanganan Update Profil Identitas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $nama        = trim($_POST['nama'] ?? '');
    $nim         = trim($_POST['nim'] ?? '');
    $universitas = trim($_POST['universitas'] ?? '');
    $jurusan     = trim($_POST['jurusan'] ?? '');

    if (empty($nama)) {
        $error = 'Nama lengkap wajib diisi.';
    } else {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("UPDATE users SET nama = ?, nim = ?, universitas = ?, jurusan = ?, updated_at = ? WHERE id = ?");
            $stmt->execute([$nama, $nim ?: null, $universitas ?: null, $jurusan ?: null, date('Y-m-d H:i:s'), $user['id']]);

            $_SESSION['user_name'] = $nama;
            $user = current_user(); // Refresh data
            $success = 'Data profil mahasiswa berhasil diperbarui!';
        } catch (Throwable $e) {
            $error = 'Gagal memperbarui profil: ' . $e->getMessage();
        }
    }
}

// 2. Penanganan Ubah Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass     = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if (empty($current_pass) || empty($new_pass)) {
        $error = 'Password lama dan password baru wajib diisi.';
    } elseif (strlen($new_pass) < 6) {
        $error = 'Password baru minimal harus 6 karakter.';
    } elseif ($new_pass !== $confirm_pass) {
        $error = 'Konfirmasi password baru tidak cocok.';
    } else {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($current_pass, $hash)) {
                $error = 'Password lama Anda tidak sesuai.';
            } else {
                $newHash = password_hash($new_pass, PASSWORD_BCRYPT);
                $upStmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = ? WHERE id = ?");
                $upStmt->execute([$newHash, date('Y-m-d H:i:s'), $user['id']]);
                $success = 'Password akun berhasil diubah!';
            }
        } catch (Throwable $e) {
            $error = 'Gagal mengubah password: ' . $e->getMessage();
        }
    }
}

$flash = get_flash();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="main-wrapper">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="content-body">
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <div><?= $flash['message'] ?></div>
                <button type="button" style="background:none;border:none;cursor:pointer;font-size:1.1rem;" onclick="this.parentElement.remove()">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <div class="profile-grid">
            <!-- Kartu Samping: Info Ringkas Mahasiswa -->
            <div class="profile-card-left">
                <div class="profile-avatar-large">
                    <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                </div>
                <h3 style="font-size:1.25rem;font-weight:800;color:var(--text-primary);margin-bottom:4px;">
                    <?= htmlspecialchars($user['nama']) ?>
                </h3>
                <p style="font-size:0.875rem;color:var(--pastel-purple-600);font-weight:700;margin-bottom:2px;">
                    <?= htmlspecialchars($user['nim'] ?: 'NIM Belum Diatur') ?>
                </p>
                <p style="font-size:0.8rem;color:var(--text-muted);">
                    <?= htmlspecialchars($user['email']) ?>
                </p>

                <div style="margin-top:16px;padding:12px;background:#f8fafc;border-radius:var(--radius-md);text-align:left;font-size:0.8rem;">
                    <div style="color:var(--text-muted);margin-bottom:2px;">Perguruan Tinggi:</div>
                    <div style="font-weight:700;color:var(--text-primary);margin-bottom:8px;"><?= htmlspecialchars($user['universitas'] ?: '-') ?></div>
                    <div style="color:var(--text-muted);margin-bottom:2px;">Program Studi:</div>
                    <div style="font-weight:700;color:var(--text-primary);"><?= htmlspecialchars($user['jurusan'] ?: '-') ?></div>
                </div>

                <div class="profile-stats-mini">
                    <div class="stat-box-mini">
                        <div class="val"><?= $stats['total'] ?></div>
                        <div class="lbl">Total Tugas</div>
                    </div>
                    <div class="stat-box-mini">
                        <div class="val" style="color:#10b981;"><?= $stats['selesai'] ?></div>
                        <div class="lbl">Selesai</div>
                    </div>
                </div>

                <div style="margin-top:24px;">
                    <a href="logout.php" class="btn btn-secondary btn-sm" style="width:100%;color:#ef4444;" onclick="return confirm('Apakah Anda yakin ingin logout?');">
                        Keluar dari Akun (Logout)
                    </a>
                </div>
            </div>

            <!-- Bagian Kanan: Form Edit Profil & Keamanan -->
            <div style="display:flex;flex-direction:column;gap:24px;">
                <!-- 1. Form Edit Data Pribadi -->
                <div class="panel-card">
                    <div class="panel-header">
                        <div class="panel-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-600)" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span>Informasi Mahasiswa</span>
                        </div>
                    </div>

                    <form action="profile.php" method="POST">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="nama">Nama Lengkap <span class="required">*</span></label>
                                <input type="text" id="nama" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="email">Alamat Email (Akun)</label>
                                <input type="email" id="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background:#f1f5f9;cursor:not-allowed;">
                                <div class="form-text">Alamat email digunakan sebagai ID login utama.</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="nim">NIM (Nomor Induk Mahasiswa)</label>
                                <input type="text" id="nim" name="nim" class="form-control" value="<?= htmlspecialchars($user['nim'] ?? '') ?>" placeholder="Masukkan NIM Anda">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="universitas">Universitas / Kampus</label>
                                <input type="text" id="universitas" name="universitas" class="form-control" value="<?= htmlspecialchars($user['universitas'] ?? '') ?>" placeholder="Nama kampus">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="jurusan">Program Studi / Jurusan</label>
                            <input type="text" id="jurusan" name="jurusan" class="form-control" value="<?= htmlspecialchars($user['jurusan'] ?? '') ?>" placeholder="Contoh: Teknik Informatika">
                        </div>

                        <div style="display:flex;justify-content:flex-end;">
                            <button type="submit" class="btn btn-primary">
                                Simpan Perubahan Profil
                            </button>
                        </div>
                    </form>
                </div>

                <!-- 2. Form Ganti Password -->
                <div class="panel-card">
                    <div class="panel-header">
                        <div class="panel-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--pastel-purple-500)" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <span>Keamanan & Password Akun</span>
                        </div>
                    </div>

                    <form action="profile.php" method="POST">
                        <input type="hidden" name="action" value="change_password">

                        <div class="form-group">
                            <label class="form-label" for="current_password">Password Saat Ini <span class="required">*</span></label>
                            <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Masukkan password saat ini" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="new_password">Password Baru <span class="required">*</span></label>
                                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Minimal 6 karakter" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="confirm_password">Konfirmasi Password Baru <span class="required">*</span></label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Ulangi password baru" required>
                            </div>
                        </div>

                        <div style="display:flex;justify-content:flex-end;">
                            <button type="submit" class="btn btn-secondary">
                                Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
