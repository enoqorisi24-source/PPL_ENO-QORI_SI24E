<?php
/**
 * TaskMate - Kalender Deadline Tugas Mahasiswa
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/task_functions.php';

require_login();
$user = current_user();

$pageTitle = 'Kalender Deadline';
$pageSubtitle = 'Visualisasi tenggat waktu tugas kuliah dalam kalender bulanan interaktif';
$activeNav = 'calendar';
$includeCalendarJs = true;

// Ambil event bulan saat ini untuk initial render
$curYear = (int)date('Y');
$curMonth = (int)date('m');
$initialEvents = get_tasks_for_calendar($user['id'], $curYear, $curMonth);

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

        <!-- Legend / Petunjuk Warna Prioritas -->
        <div style="background:#ffffff;border:1px solid var(--border-light);border-radius:var(--radius-lg);padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                <span style="font-size:0.8rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Keterangan Prioritas:</span>
                <div style="display:flex;align-items:center;gap:6px;font-size:0.8rem;">
                    <span style="width:10px;height:10px;border-radius:50%;background:#ef4444;display:inline-block;"></span>
                    <span>Tinggi</span>
                </div>
                <div style="display:flex;align-items:center;gap:6px;font-size:0.8rem;">
                    <span style="width:10px;height:10px;border-radius:50%;background:#f59e0b;display:inline-block;"></span>
                    <span>Sedang</span>
                </div>
                <div style="display:flex;align-items:center;gap:6px;font-size:0.8rem;">
                    <span style="width:10px;height:10px;border-radius:50%;background:#10b981;display:inline-block;"></span>
                    <span>Rendah</span>
                </div>
            </div>
            <div>
                <a href="task-create.php" class="btn btn-primary btn-sm">+ Tambah Tugas Baru</a>
            </div>
        </div>

        <!-- Wadah Kalender Interaktif -->
        <div id="taskCalendarApp"></div>
    </main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Inisialisasi kalender dengan initial events dari database
    const initialTasks = <?= json_encode($initialEvents) ?>;
    window.taskMateCalendar = new TaskCalendar('taskCalendarApp', {
        year: <?= $curYear ?>,
        month: <?= $curMonth - 1 ?>,
        events: initialTasks
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
