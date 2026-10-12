<?php
/**
 * TaskMate - Halaman Login Mahasiswa
 */
require_once __DIR__ . '/includes/auth.php';
require_guest();

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                login_user($user);
                set_flash('success', "Selamat datang kembali, <strong>" . htmlspecialchars($user['nama']) . "</strong>! Siap menyelesaikan tugas hari ini?");
                redirect('dashboard.php');
            } else {
                $error = 'Email atau password yang Anda masukkan salah.';
            }
        } catch (Throwable $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | TaskMate – Pengelolaan Tugas Mahasiswa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">

<div class="auth-card">
    <div class="auth-header">
        <div class="brand-icon-wrapper">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                <path d="M2 17l10 5 10-5"></path>
                <path d="M2 12l10 5 10-5"></path>
            </svg>
        </div>
        <h1>Task<span style="color:var(--pastel-purple-500)">Mate</span></h1>
        <p>Aplikasi Pengelolaan Tugas Mahasiswa Modern</p>
    </div>

    <!-- Kotak Demo Akun Cepat -->
    <div class="demo-account-box">
        <strong>⚡ Akun Demo Siap Pakai:</strong>
        <div>Email: <code style="font-weight:700;">mahasiswa@taskmate.id</code></div>
        <div>Password: <code style="font-weight:700;">password123</code></div>
        <button type="button" onclick="fillDemo()" style="margin-top:8px;background:#ffffff;border:1px solid #bae6fd;padding:4px 10px;border-radius:6px;font-size:0.75rem;cursor:pointer;color:#0284c7;font-weight:700;">
            Gunakan Akun Demo Otomatis
        </button>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?= $flash['message'] ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label class="form-label" for="email">Alamat Email Mahasiswa <span class="required">*</span></label>
            <input type="email" id="email" name="email" class="form-control" placeholder="nama@mahasiswa.ac.id" value="<?= htmlspecialchars($email) ?>" required autofocus>
        </div>

        <div class="form-group">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <label class="form-label" for="password" style="margin-bottom:0;">Password <span class="required">*</span></label>
            </div>
            <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password Anda" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;padding:12px;">
            Masuk ke TaskMate &rarr;
        </button>
    </form>

    <div class="auth-footer">
        Belum punya akun mahasiswa? <a href="register.php">Daftar Sekarang</a>
    </div>
</div>

<script>
function fillDemo() {
    document.getElementById('email').value = 'mahasiswa@taskmate.id';
    document.getElementById('password').value = 'password123';
}
</script>

</body>
</html>
