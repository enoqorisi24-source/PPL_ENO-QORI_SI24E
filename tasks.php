<?php
/**
 * TaskMate - Pengelolaan Semua Tugas (CRUD, Search, Filter, Sort)
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/task_functions.php';

require_login();
$user = current_user();

$pageTitle = 'Semua Tugas Mahasiswa';
$pageSubtitle = 'Kelola daftar tugas kuliah, atur prioritas, dan pantau tenggat waktu';
$activeNav = 'tasks';

// Penanganan Aksi Hapus Tugas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $taskId = (int)($_POST['task_id'] ?? 0);
    if ($taskId > 0 && delete_task($taskId, $user['id'])) {
        set_flash('success', 'Tugas berhasil dihapus dari daftar.');
    } else {
        set_flash('danger', 'Gagal menghapus tugas atau tugas tidak ditemukan.');
    }
    redirect('tasks.php');
}

// Fallback Hapus via GET jika dipanggil dari konfirmasi sederhana
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $taskId = (int)$_GET['id'];
    if ($taskId > 0 && delete_task($taskId, $user['id'])) {
        set_flash('success', 'Tugas berhasil dihapus dari daftar.');
    } else {
        set_flash('danger', 'Gagal menghapus tugas.');
    }
    redirect('tasks.php');
}

// Parameter Filter & Pencarian
$filters = [
    'search'      => trim($_GET['search'] ?? ''),
    'status'      => trim($_GET['status'] ?? ''),
    'prioritas'   => trim($_GET['prioritas'] ?? ''),
    'time_filter' => trim($_GET['time_filter'] ?? ''),
    'sort'        => trim($_GET['sort'] ?? 'deadline_asc'),
];

$viewMode = $_GET['view'] ?? 'table'; // 'table' atau 'grid'

// Query Tugas
$tasks = get_user_tasks($user['id'], $filters);
$totalFound = count($tasks);

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

        <!-- Bar Pencarian & Multi-Filter -->
        <div class="filter-card">
            <form action="tasks.php" method="GET" class="filter-grid" id="filterForm">
                <!-- 1. Input Pencarian -->
                <div>
                    <label class="form-label" style="font-size:0.75rem;text-transform:uppercase;color:var(--text-muted);">Pencarian Kata Kunci</label>
                    <input type="text" name="search" class="form-control" placeholder="Nama tugas atau mata kuliah..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>

                <!-- 2. Filter Status -->
                <div>
                    <label class="form-label" style="font-size:0.75rem;text-transform:uppercase;color:var(--text-muted);">Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="Belum Dikerjakan" <?= $filters['status'] === 'Belum Dikerjakan' ? 'selected' : '' ?>>Belum Dikerjakan</option>
                        <option value="Sedang Dikerjakan" <?= $filters['status'] === 'Sedang Dikerjakan' ? 'selected' : '' ?>>Sedang Dikerjakan</option>
                        <option value="Selesai" <?= $filters['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                    </select>
                </div>

                <!-- 3. Filter Prioritas -->
                <div>
                    <label class="form-label" style="font-size:0.75rem;text-transform:uppercase;color:var(--text-muted);">Prioritas</label>
                    <select name="prioritas" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Prioritas</option>
                        <option value="Tinggi" <?= $filters['prioritas'] === 'Tinggi' ? 'selected' : '' ?>>Tinggi (Urgent)</option>
                        <option value="Sedang" <?= $filters['prioritas'] === 'Sedang' ? 'selected' : '' ?>>Sedang</option>
                        <option value="Rendah" <?= $filters['prioritas'] === 'Rendah' ? 'selected' : '' ?>>Rendah</option>
                    </select>
                </div>

                <!-- 4. Filter Waktu Deadline -->
                <div>
                    <label class="form-label" style="font-size:0.75rem;text-transform:uppercase;color:var(--text-muted);">Filter Waktu</label>
                    <select name="time_filter" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Waktu</option>
                        <option value="today" <?= $filters['time_filter'] === 'today' ? 'selected' : '' ?>>Deadline Hari Ini</option>
                        <option value="week" <?= $filters['time_filter'] === 'week' ? 'selected' : '' ?>>7 Hari Ke Depan</option>
                        <option value="overdue" <?= $filters['time_filter'] === 'overdue' ? 'selected' : '' ?>>Terlambat</option>
                        <option value="upcoming" <?= $filters['time_filter'] === 'upcoming' ? 'selected' : '' ?>>Belum Selesai</option>
                    </select>
                </div>

                <!-- 5. Pengurutan (Sorting) -->
                <div>
                    <label class="form-label" style="font-size:0.75rem;text-transform:uppercase;color:var(--text-muted);">Urutkan Berdasarkan</label>
                    <select name="sort" class="form-select" onchange="this.form.submit()">
                        <option value="deadline_asc" <?= $filters['sort'] === 'deadline_asc' ? 'selected' : '' ?>>Deadline Terdekat</option>
                        <option value="deadline_desc" <?= $filters['sort'] === 'deadline_desc' ? 'selected' : '' ?>>Deadline Terjauh</option>
                        <option value="priority_desc" <?= $filters['sort'] === 'priority_desc' ? 'selected' : '' ?>>Prioritas Tertinggi</option>
                        <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Tugas Terbaru</option>
                    </select>
                </div>

                <!-- Hidden view mode -->
                <input type="hidden" name="view" value="<?= htmlspecialchars($viewMode) ?>">
            </form>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:12px;border-top:1px solid #f1f5f9;flex-wrap:wrap;gap:10px;">
                <div style="font-size:0.85rem;color:var(--text-secondary);">
                    Ditemukan <strong><?= $totalFound ?></strong> tugas
                    <?php if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['prioritas']) || !empty($filters['time_filter'])): ?>
                        <a href="tasks.php" style="color:var(--pastel-purple-600);margin-left:8px;font-weight:600;">(Reset Filter)</a>
                    <?php endif; ?>
                </div>

                <div style="display:flex;align-items:center;gap:12px;">
                    <!-- View Mode Toggle -->
                    <div style="display:inline-flex;background:#f1f5f9;padding:3px;border-radius:var(--radius-sm);">
                        <a href="tasks.php?<?= http_build_query(array_merge($filters, ['view' => 'table'])) ?>" 
                           class="btn btn-sm" 
                           style="padding:4px 10px;border-radius:6px;background:<?= $viewMode === 'table' ? '#ffffff' : 'transparent' ?>;color:<?= $viewMode === 'table' ? 'var(--text-primary)' : 'var(--text-muted)' ?>;box-shadow:<?= $viewMode === 'table' ? 'var(--shadow-sm)' : 'none' ?>;"
                           title="Tampilan Tabel">
                            Tabel
                        </a>
                        <a href="tasks.php?<?= http_build_query(array_merge($filters, ['view' => 'grid'])) ?>" 
                           class="btn btn-sm" 
                           style="padding:4px 10px;border-radius:6px;background:<?= $viewMode === 'grid' ? '#ffffff' : 'transparent' ?>;color:<?= $viewMode === 'grid' ? 'var(--text-primary)' : 'var(--text-muted)' ?>;box-shadow:<?= $viewMode === 'grid' ? 'var(--shadow-sm)' : 'none' ?>;"
                           title="Tampilan Kartu">
                            Kartu
                        </a>
                    </div>

                    <a href="task-create.php" class="btn btn-primary btn-sm">
                        + Tambah Tugas Baru
                    </a>
                </div>
            </div>
        </div>

        <!-- Daftar Tugas -->
        <?php if (empty($tasks)): ?>
            <div class="panel-card empty-state">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <h3>Tidak Ada Tugas Ditemukan</h3>
                <p>Tidak ada data tugas yang sesuai dengan kriteria filter atau pencarian Anda.</p>
                <a href="task-create.php" class="btn btn-primary btn-sm">+ Buat Tugas Sekarang</a>
            </div>

        <?php elseif ($viewMode === 'grid'): ?>
            <!-- TAMPILAN KARTU / GRID -->
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:20px;">
                <?php foreach ($tasks as $task): 
                    $dlInfo = get_deadline_info($task['deadline'], $task['status']);
                    $badgeStatus = 'badge-pending';
                    if ($task['status'] === 'Sedang Dikerjakan') $badgeStatus = 'badge-progress';
                    if ($task['status'] === 'Selesai') $badgeStatus = 'badge-done';
                ?>
                    <div class="task-item-card" style="margin-bottom:0;">
                        <div class="task-item-top">
                            <div style="flex:1;">
                                <div class="task-item-title"><?= htmlspecialchars($task['judul']) ?></div>
                                <div class="task-item-course"><?= htmlspecialchars($task['mata_kuliah']) ?></div>
                            </div>
                            <span class="badge task-status-badge <?= $badgeStatus ?>"><?= $task['status'] ?></span>
                        </div>

                        <?php if (!empty($task['deskripsi'])): ?>
                            <p class="task-item-desc"><?= htmlspecialchars($task['deskripsi']) ?></p>
                        <?php endif; ?>

                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:6px;">
                            <span class="badge badge-priority-<?= strtolower($task['prioritas']) ?>">
                                Prioritas <?= $task['prioritas'] ?>
                            </span>
                            <span class="badge <?= $dlInfo['class'] ?>"><?= $dlInfo['text'] ?></span>
                        </div>

                        <div class="task-item-footer">
                            <div style="font-size:0.75rem;color:var(--text-muted);">
                                ⏰ <?= format_indo_date($task['deadline']) ?>
                            </div>
                            <div class="task-actions">
                                <select class="status-select quick-status-select" data-task-id="<?= $task['id'] ?>" data-current="<?= $task['status'] ?>">
                                    <option value="Belum Dikerjakan" <?= $task['status'] === 'Belum Dikerjakan' ? 'selected' : '' ?>>Belum</option>
                                    <option value="Sedang Dikerjakan" <?= $task['status'] === 'Sedang Dikerjakan' ? 'selected' : '' ?>>Sedang</option>
                                    <option value="Selesai" <?= $task['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                </select>
                                <a href="task-edit.php?id=<?= $task['id'] ?>" class="btn btn-secondary btn-sm" title="Edit">
                                    Edit
                                </a>
                                <button type="button" class="btn btn-secondary btn-sm" style="color:#ef4444;" onclick="confirmDeleteTask(<?= $task['id'] ?>, '<?= addslashes(htmlspecialchars($task['judul'])) ?>')" title="Hapus">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <!-- TAMPILAN TABEL -->
            <div class="table-responsive">
                <table class="task-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tugas & Mata Kuliah</th>
                            <th>Prioritas</th>
                            <th>Deadline</th>
                            <th>Status Pengerjaan</th>
                            <th style="text-align:right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $idx => $task): 
                            $dlInfo = get_deadline_info($task['deadline'], $task['status']);
                            $badgeStatus = 'badge-pending';
                            if ($task['status'] === 'Sedang Dikerjakan') $badgeStatus = 'badge-progress';
                            if ($task['status'] === 'Selesai') $badgeStatus = 'badge-done';
                        ?>
                            <tr>
                                <td style="color:var(--text-muted);font-weight:600;"><?= $idx + 1 ?></td>
                                <td>
                                    <div style="font-weight:700;color:var(--text-primary);font-size:0.925rem;">
                                        <?= htmlspecialchars($task['judul']) ?>
                                    </div>
                                    <div style="font-size:0.8rem;color:var(--primary-700);font-weight:600;margin-top:2px;">
                                        <?= htmlspecialchars($task['mata_kuliah']) ?>
                                    </div>
                                    <?php if (!empty($task['deskripsi'])): ?>
                                        <div style="font-size:0.78rem;color:var(--text-muted);margin-top:4px;max-width:420px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            <?= htmlspecialchars($task['deskripsi']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-priority-<?= strtolower($task['prioritas']) ?>">
                                        <?= $task['prioritas'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight:600;color:var(--text-primary);font-size:0.85rem;">
                                        <?= format_indo_date($task['deadline'], false) ?>
                                    </div>
                                    <div style="margin-top:3px;">
                                        <span class="badge <?= $dlInfo['class'] ?>" style="font-size:0.7rem;padding:2px 8px;">
                                            <?= $dlInfo['text'] ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <span class="badge task-status-badge <?= $badgeStatus ?>">
                                            <?= $task['status'] ?>
                                        </span>
                                        <select class="status-select quick-status-select" data-task-id="<?= $task['id'] ?>" data-current="<?= $task['status'] ?>" title="Ubah Status Cepat">
                                            <option value="Belum Dikerjakan" <?= $task['status'] === 'Belum Dikerjakan' ? 'selected' : '' ?>>Belum</option>
                                            <option value="Sedang Dikerjakan" <?= $task['status'] === 'Sedang Dikerjakan' ? 'selected' : '' ?>>Sedang</option>
                                            <option value="Selesai" <?= $task['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                        </select>
                                    </div>
                                </td>
                                <td style="text-align:right;">
                                    <div style="display:inline-flex;gap:6px;">
                                        <a href="task-edit.php?id=<?= $task['id'] ?>" class="btn btn-secondary btn-sm" title="Edit Tugas">
                                            Edit
                                        </a>
                                        <button type="button" class="btn btn-secondary btn-sm" style="color:#ef4444;" onclick="confirmDeleteTask(<?= $task['id'] ?>, '<?= addslashes(htmlspecialchars($task['judul'])) ?>')" title="Hapus Tugas">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
