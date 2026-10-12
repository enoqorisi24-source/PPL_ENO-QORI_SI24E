<?php
/**
 * TaskMate - Logout Handler
 */
require_once __DIR__ . '/includes/auth.php';

logout_user();
set_flash('info', 'Anda telah berhasil keluar dari akun TaskMate. Sampai jumpa!');
redirect('login.php');
