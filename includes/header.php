<?php
/**
 * TaskMate - Header Component
 */
if (!isset($pageTitle)) {
    $pageTitle = 'TaskMate – Pengelolaan Tugas Mahasiswa';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TaskMate – Aplikasi Pengelolaan Tugas Mahasiswa Modern untuk mengatur deadline, prioritas, dan produktivitas kuliah.">
    <title><?= htmlspecialchars($pageTitle) ?> | TaskMate</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Main Style -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <!-- Backdrop for Mobile Sidebar -->
    <div class="modal-overlay" id="sidebarOverlay" style="background-color:rgba(15,23,42,0.4);z-index:35;"></div>
