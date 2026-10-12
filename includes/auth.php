<?php
/**
 * TaskMate - Authentication & Session Guard
 */

require_once __DIR__ . '/../config/config.php';

function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        set_flash('warning', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
        redirect('login.php');
    }
}

function require_guest(): void {
    if (is_logged_in()) {
        redirect('dashboard.php');
    }
}

function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }

    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, nama, email, nim, universitas, jurusan, foto_profil, created_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user) {
            // User sudah tidak ada di database
            logout_user();
            redirect('login.php');
        }

        return $user;
    } catch (Throwable $e) {
        return null;
    }
}

function login_user(array $user): void {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['nama'];
    $_SESSION['user_email'] = $user['email'];
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
