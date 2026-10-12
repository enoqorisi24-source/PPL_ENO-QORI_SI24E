<?php
/**
 * TaskMate - Top Navbar Component
 */
$user = current_user();
$notifications = get_deadline_notifications($user['id']);
$notifCount = count($notifications);
?>
<header class="navbar">
    <div class="navbar-left">
        <button type="button" class="mobile-toggle" id="mobileToggle" aria-label="Buka Menu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>

        <div class="page-title-box">
            <h1><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
            <p><?= htmlspecialchars($pageSubtitle ?? 'Kelola jadwal dan tugas kuliah Anda secara teratur') ?></p>
        </div>
    </div>

    <div class="navbar-right">
        <!-- Pencarian Cepat Global -->
        <form action="tasks.php" method="GET" class="search-box">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" name="search" placeholder="Cari tugas / matkul..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </form>

        <!-- Lonceng Notifikasi Pengingat Deadline -->
        <div class="notif-dropdown-wrapper">
            <button type="button" class="icon-btn" id="notifBtn" title="Pengingat Deadline">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <?php if ($notifCount > 0): ?>
                    <span class="notif-badge-dot"><?= $notifCount ?></span>
                <?php endif; ?>
            </button>

            <!-- Dropdown Menu Notifikasi -->
            <div class="notif-menu" id="notifMenu">
                <div class="notif-header">
                    <span>Pengingat Deadline</span>
                    <span style="font-size:0.75rem;color:var(--pastel-purple-600);"><?= $notifCount ?> Perlu Perhatian</span>
                </div>
                <div class="notif-list">
                    <?php if (empty($notifications)): ?>
                        <div style="padding:24px;text-align:center;font-size:0.85rem;color:var(--text-muted);">
                            🎉 Tidak ada deadline mendesak atau terlambat saat ini.
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $n): ?>
                            <a href="tasks.php?search=<?= urlencode($n['judul']) ?>" class="notif-item <?= $n['is_overdue'] ? 'overdue' : '' ?>">
                                <div class="notif-item-icon" style="background-color: <?= $n['is_overdue'] ? '#fee2e2' : '#fef3c7' ?>; color: <?= $n['is_overdue'] ? '#dc2626' : '#d97706' ?>;">
                                    <?= $n['is_overdue'] ? '⚠️' : '⏰' ?>
                                </div>
                                <div style="flex:1;">
                                    <div class="notif-item-title"><?= htmlspecialchars($n['judul']) ?></div>
                                    <div class="notif-item-sub">
                                        <?= htmlspecialchars($n['mata_kuliah']) ?> • <strong style="color:<?= $n['is_overdue'] ? '#dc2626' : '#d97706' ?>;"><?= $n['info_text'] ?></strong>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div style="padding:10px 16px;background:#f8fafc;border-top:1px solid var(--border-light);text-align:center;">
                    <a href="tasks.php?time_filter=overdue" style="font-size:0.8rem;color:var(--pastel-purple-600);font-weight:700;">Lihat Semua Deadline &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Tombol Tambah Tugas Cepat -->
        <a href="task-create.php" class="btn btn-primary btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>+ Tugas Baru</span>
        </a>
    </div>
</header>
