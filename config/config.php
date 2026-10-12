<?php
/**
 * TaskMate - Application Configuration & Helpers
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Zona waktu default Indonesia
date_default_timezone_set('Asia/Jakarta');

// Autoload database
require_once __DIR__ . '/database.php';

// Flash Messages
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Redirect Helper
function redirect(string $url): void {
    header("Location: {$url}");
    exit;
}

// Sanitasi Input
function sanitize(?string $data): string {
    if ($data === null) return '';
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Format Tanggal Bahasa Indonesia
function format_indo_date(?string $datetime, bool $withTime = true): string {
    if (!$datetime) return '-';
    $timestamp = strtotime($datetime);
    if (!$timestamp) return '-';

    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    $day = date('d', $timestamp);
    $month = $months[(int)date('m', $timestamp)];
    $year = date('Y', $timestamp);
    
    if ($withTime) {
        $time = date('H:i', $timestamp);
        return "{$day} {$month} {$year}, {$time} WIB";
    }

    return "{$day} {$month} {$year}";
}

// Status Deadline Human-Readable
function get_deadline_info(?string $deadline, string $status): array {
    if (!$deadline) {
        return ['text' => 'Tanpa Deadline', 'class' => 'badge-neutral', 'is_urgent' => false, 'is_overdue' => false];
    }

    if ($status === 'Selesai') {
        return ['text' => 'Selesai', 'class' => 'badge-success', 'is_urgent' => false, 'is_overdue' => false];
    }

    $now = time();
    $target = strtotime($deadline);
    $diff = $target - $now;

    // Terlambat
    if ($diff < 0) {
        $daysLate = ceil(abs($diff) / 86400);
        return [
            'text' => "Terlambat {$daysLate} hari",
            'class' => 'badge-danger',
            'is_urgent' => true,
            'is_overdue' => true
        ];
    }

    // Hari Ini
    if ($diff < 86400 && date('Y-m-d') === date('Y-m-d', $target)) {
        $hours = ceil($diff / 3600);
        return [
            'text' => "Hari Ini ({$hours} jam lagi)",
            'class' => 'badge-warning',
            'is_urgent' => true,
            'is_overdue' => false
        ];
    }

    // Besok
    if ($diff <= 86400 * 2) {
        return [
            'text' => 'Besok',
            'class' => 'badge-warning',
            'is_urgent' => true,
            'is_overdue' => false
        ];
    }

    // Mendekati (<= 3 hari)
    if ($diff <= 86400 * 3) {
        $days = ceil($diff / 86400);
        return [
            'text' => "{$days} hari lagi",
            'class' => 'badge-info',
            'is_urgent' => true,
            'is_overdue' => false
        ];
    }

    // Cukup lama
    $days = ceil($diff / 86400);
    return [
        'text' => "{$days} hari lagi",
        'class' => 'badge-secondary',
        'is_urgent' => false,
        'is_overdue' => false
    ];
}
