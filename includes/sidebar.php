<?php
/**
 * TaskMate - Sidebar Component
 */
$user = current_user();
$activeNav = $activeNav ?? 'dashboard';

// Total pending tasks badge for sidebar
$sidebarStats = get_task_statistics($user['id']);
$pendingCount = $sidebarStats['belum_dikerjakan'] + $sidebarStats['sedang_dikerjakan'];
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="dashboard.php" class="brand-logo">
            <div class="brand-icon-wrapper">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                    <path d="M2 17l10 5 10-5"></path>
                    <path d="M2 12l10 5 10-5"></path>
                </svg>
            </div>
            <span>Task<span style="color:var(--pastel-purple-500)">Mate</span></span>
            <span class="brand-badge">PRO</span>
        </a>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Menu Utama</div>

        <a href="dashboard.php" class="nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="9"></rect>
                <rect x="14" y="3" width="7" height="5"></rect>
                <rect x="14" y="12" width="7" height="9"></rect>
                <rect x="3" y="16" width="7" height="5"></rect>
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="tasks.php" class="nav-item <?= $activeNav === 'tasks' ? 'active' : '' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            <span>Semua Tugas</span>
            <?php if ($pendingCount > 0): ?>
                <span class="badge-count"><?= $pendingCount ?></span>
            <?php endif; ?>
        </a>

        <a href="task-create.php" class="nav-item <?= $activeNav === 'task-create' ? 'active' : '' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
            <span>Tambah Tugas</span>
        </a>

        <a href="calendar.php" class="nav-item <?= $activeNav === 'calendar' ? 'active' : '' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span>Kalender</span>
        </a>

        <div class="nav-section-title" style="margin-top:12px;">Akun & Pengaturan</div>

        <a href="profile.php" class="nav-item <?= $activeNav === 'profile' ? 'active' : '' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span>Profil Pengguna</span>
        </a>

        <a href="logout.php" class="nav-item" style="color:#ef4444;" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?');">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Keluar (Logout)</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="profile.php" class="user-mini-card">
            <div class="user-avatar">
                <?= strtoupper(substr($user['nama'], 0, 1)) ?>
            </div>
            <div class="user-mini-info">
                <div class="user-mini-name"><?= htmlspecialchars($user['nama']) ?></div>
                <div class="user-mini-sub"><?= htmlspecialchars($user['nim'] ?: 'Mahasiswa') ?></div>
            </div>
        </a>
    </div>
</aside>
