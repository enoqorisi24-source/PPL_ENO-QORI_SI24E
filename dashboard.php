<?php
/**
 * TaskMate - Dashboard Utama Mahasiswa
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/task_functions.php';

require_login();
$user = current_user();

$pageTitle = 'Dashboard Mahasiswa';
$pageSubtitle = 'Ringkasan aktivitas tugas, jadwal deadline, dan statistik produktivitas';
$activeNav = 'dashboard';

// Ambil statistik real-time dari database
$stats = get_task_statistics($user['id']);
$urgentTasks = get_urgent_tasks($user['id'], 5);
$recentTasks = get_recent_tasks($user['id'], 5);

// Driver database yang aktif (MySQL atau SQLite)
$driverName = strtoupper(Database::getDriverUsed());

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

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-text">
                <h2>Halo, <?= htmlspecialchars($user['nama']) ?>! 👋</h2>
                <p>
                    <?= htmlspecialchars($user['jurusan'] ?: 'Mahasiswa') ?> • <?= htmlspecialchars($user['universitas'] ?: 'TaskMate User') ?>
                </p>
                <div class="db-badge-indicator">
                    <span class="db-dot"></span>
                    <span>Database: <?= $driverName ?> (Koneksi Aktif)</span>
                </div>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="task-create.php" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Buat Tugas Baru</span>
                </a>
                <a href="calendar.php" class="btn btn-outline-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span>Lihat Kalender</span>
                </a>
            </div>
        </div>

        <!-- 5 Kartu Statistik Tugas (Sesuai Ketentuan Soal) -->
        <div class="stats-grid">
            <!-- 1. Total Seluruh Tugas -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Total Seluruh Tugas</span>
                    <div class="stat-icon blue">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="stat-value"><?= $stats['total'] ?></div>
                <div class="stat-sub">Semua mata kuliah</div>
            </div>

            <!-- 2. Belum Dikerjakan -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Belum Dikerjakan</span>
                    <div class="stat-icon yellow">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="stat-value"><?= $stats['belum_dikerjakan'] ?></div>
                <div class="stat-sub">Menunggu dikerjakan</div>
            </div>

            <!-- 3. Sedang Dikerjakan -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Sedang Dikerjakan</span>
                    <div class="stat-icon indigo">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path>
                        </svg>
                    </div>
                </div>
                <div class="stat-value"><?= $stats['sedang_dikerjakan'] ?></div>
                <div class="stat-sub">Dalam proses pengerjaan</div>
            </div>

            <!-- 4. Sudah Selesai -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Sudah Selesai</span>
                    <div class="stat-icon green">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="stat-value"><?= $stats['selesai'] ?></div>
                <div class="stat-sub">Tugas tuntas dikerjakan</div>
            </div>

            <!-- 5. Deadline Terdekat & Terlambat -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Deadline Terdekat</span>
                    <div class="stat-icon red">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                    </div>
                </div>
                <div class="stat-value"><?= $stats['mendekati_deadline'] ?></div>
                <div class="stat-sub">
                    <?php if ($stats['terlambat'] > 0): ?>
                        <strong style="color:#ef4444;"><?= $stats['terlambat'] ?> tugas terlambat!</strong>
                    <?php else: ?>
                        Jatuh tempo &le; 3 hari
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Widget Progres Penyelesaian -->
        <div class="progress-card">
            <div class="progress-header">
                <div>
                    <strong style="font-size:0.95rem;color:var(--text-primary);">Tingkat Penyelesaian Tugas Kuliah</strong>
                    <div style="font-size:0.8rem;color:var(--text-muted);"><?= $stats['selesai'] ?> dari <?= $stats['total'] ?> tugas telah diselesaikan</div>
                </div>
                <div style="font-size:1.35rem;font-weight:800;color:var(--pastel-purple-600);">
                    <?= $stats['persentase_selesai'] ?>%
                </div>
            </div>
            <div class="progress-track">
                <div class="progress-fill" style="width: <?= $stats['persentase_selesai'] ?>%;"></div>
            </div>
        </div>

        <!-- Split Grid: Tugas Mendekati Deadline vs Tugas Terbaru -->
        <div class="dash-split-grid">
            <!-- Panel 1: Tugas Mendekati Deadline -->
            <div class="panel-card">
                <div class="panel-header">
                    <div class="panel-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span>Tugas Mendekati Deadline</span>
                    </div>
                    <a href="tasks.php?time_filter=upcoming" style="font-size:0.8rem;color:var(--pastel-purple-600);font-weight:700;">
                        Lihat Semua &rarr;
                    </a>
                </div>

                <?php if (empty($urgentTasks)): ?>
                    <div class="empty-state">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <h3>Semua Tugas Terkendali!</h3>
                        <p>Tidak ada tugas mendesak atau yang belum dikerjakan saat ini.</p>
                    </div>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <?php foreach ($urgentTasks as $task): 
                            $dlInfo = get_deadline_info($task['deadline'], $task['status']);
                        ?>
                            <div class="task-item-card">
                                <div class="task-item-top">
                                    <div style="flex:1;">
                                        <div class="task-item-title"><?= htmlspecialchars($task['judul']) ?></div>
                                        <div class="task-item-course"><?= htmlspecialchars($task['mata_kuliah']) ?></div>
                                    </div>
                                    <span class="badge <?= $dlInfo['class'] ?>"><?= $dlInfo['text'] ?></span>
                                </div>

                                <?php if (!empty($task['deskripsi'])): ?>
                                    <p class="task-item-desc"><?= htmlspecialchars($task['deskripsi']) ?></p>
                                <?php endif; ?>

                                <div class="task-item-footer">
                                    <div class="task-meta-left">
                                        <span class="badge badge-priority-<?= strtolower($task['prioritas']) ?>">
                                            Prioritas <?= $task['prioritas'] ?>
                                        </span>
                                        <span style="font-size:0.75rem;color:var(--text-muted);">
                                            <?= format_indo_date($task['deadline']) ?>
                                        </span>
                                    </div>
                                    <div class="task-actions">
                                        <select class="status-select quick-status-select" data-task-id="<?= $task['id'] ?>" data-current="<?= $task['status'] ?>">
                                            <option value="Belum Dikerjakan" <?= $task['status'] === 'Belum Dikerjakan' ? 'selected' : '' ?>>Belum</option>
                                            <option value="Sedang Dikerjakan" <?= $task['status'] === 'Sedang Dikerjakan' ? 'selected' : '' ?>>Sedang</option>
                                            <option value="Selesai" <?= $task['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                        </select>
                                        <a href="task-edit.php?id=<?= $task['id'] ?>" class="btn btn-secondary btn-sm" title="Edit Tugas">
                                            Edit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Panel 2: Tugas Terbaru Ditambahkan -->
            <div class="panel-card">
                <div class="panel-header">
                    <div class="panel-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-600)" stroke-width="2">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                        </svg>
                        <span>Daftar Tugas Terbaru</span>
                    </div>
                    <a href="tasks.php" style="font-size:0.8rem;color:var(--pastel-purple-600);font-weight:700;">
                        Kelola Tugas &rarr;
                    </a>
                </div>

                <?php if (empty($recentTasks)): ?>
                    <div class="empty-state">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <h3>Belum Ada Tugas</h3>
                        <p>Tambahkan tugas kuliah pertama Anda untuk mulai mengatur jadwal belajar.</p>
                        <a href="task-create.php" class="btn btn-primary btn-sm">+ Tambah Tugas</a>
                    </div>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <?php foreach ($recentTasks as $task): 
                            $badgeStatus = 'badge-pending';
                            if ($task['status'] === 'Sedang Dikerjakan') $badgeStatus = 'badge-progress';
                            if ($task['status'] === 'Selesai') $badgeStatus = 'badge-done';
                        ?>
                            <div class="task-item-card">
                                <div class="task-item-top">
                                    <div style="flex:1;">
                                        <div class="task-item-title"><?= htmlspecialchars($task['judul']) ?></div>
                                        <div class="task-item-course"><?= htmlspecialchars($task['mata_kuliah']) ?></div>
                                    </div>
                                    <span class="badge task-status-badge <?= $badgeStatus ?>"><?= $task['status'] ?></span>
                                </div>

                                <div class="task-item-footer">
                                    <div class="task-meta-left">
                                        <span class="badge badge-priority-<?= strtolower($task['prioritas']) ?>">
                                            <?= $task['prioritas'] ?>
                                        </span>
                                        <span style="font-size:0.75rem;color:var(--text-muted);">
                                            Deadline: <?= format_indo_date($task['deadline'], false) ?>
                                        </span>
                                    </div>
                                    <div class="task-actions">
                                        <select class="status-select quick-status-select" data-task-id="<?= $task['id'] ?>" data-current="<?= $task['status'] ?>">
                                            <option value="Belum Dikerjakan" <?= $task['status'] === 'Belum Dikerjakan' ? 'selected' : '' ?>>Belum</option>
                                            <option value="Sedang Dikerjakan" <?= $task['status'] === 'Sedang Dikerjakan' ? 'selected' : '' ?>>Sedang</option>
                                            <option value="Selesai" <?= $task['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                        </select>
                                        <a href="task-edit.php?id=<?= $task['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
