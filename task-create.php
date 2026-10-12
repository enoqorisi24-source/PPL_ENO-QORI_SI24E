<?php
/**
 * TaskMate - Halaman Tambah Tugas Baru
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/task_functions.php';

require_login();
$user = current_user();

$pageTitle = 'Tambah Tugas Baru';
$pageSubtitle = 'Catat tugas kuliah baru agar tidak terlewat dari jadwal deadline';
$activeNav = 'task-create';

$error = '';
$judul = '';
$mata_kuliah = '';
$deskripsi = '';
$deadline = $_GET['deadline'] ?? date('Y-m-d\TH:i', strtotime('+2 days 23:59'));
$prioritas = 'Sedang';
$status = 'Belum Dikerjakan';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul       = trim($_POST['judul'] ?? '');
    $mata_kuliah = trim($_POST['mata_kuliah'] ?? '');
    $deskripsi   = trim($_POST['deskripsi'] ?? '');
    $deadline    = trim($_POST['deadline'] ?? '');
    $prioritas   = trim($_POST['prioritas'] ?? 'Sedang');
    $status      = trim($_POST['status'] ?? 'Belum Dikerjakan');

    // Validasi
    if (empty($judul)) {
        $error = 'Judul tugas wajib diisi.';
    } elseif (empty($mata_kuliah)) {
        $error = 'Nama mata kuliah wajib diisi.';
    } elseif (empty($deadline)) {
        $error = 'Tanggal dan waktu deadline wajib ditentukan.';
    } elseif (!in_array($prioritas, ['Rendah', 'Sedang', 'Tinggi'])) {
        $error = 'Tingkat prioritas tidak valid.';
    } elseif (!in_array($status, ['Belum Dikerjakan', 'Sedang Dikerjakan', 'Selesai'])) {
        $error = 'Status tugas tidak valid.';
    } else {
        // Format datetime ke Y-m-d H:i:s
        $formattedDeadline = date('Y-m-d H:i:s', strtotime($deadline));

        try {
            $taskId = create_task($user['id'], [
                'judul' => $judul,
                'mata_kuliah' => $mata_kuliah,
                'deskripsi' => $deskripsi,
                'deadline' => $formattedDeadline,
                'prioritas' => $prioritas,
                'status' => $status
            ]);

            set_flash('success', "Tugas '<strong>" . htmlspecialchars($judul) . "</strong>' berhasil ditambahkan!");
            redirect('tasks.php');
        } catch (Throwable $e) {
            $error = 'Gagal menyimpan tugas ke database: ' . $e->getMessage();
        }
    }
}

$flash = get_flash();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="main-wrapper">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="content-body">
        <div style="max-width: 720px; margin: 0 auto;">
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <div><?= $flash['message'] ?></div>
                    <button type="button" style="background:none;border:none;cursor:pointer;font-size:1.1rem;" onclick="this.parentElement.remove()">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <div class="panel-card">
                <div class="panel-header">
                    <div class="panel-title">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--pastel-purple-500)" stroke-width="2.5">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="16"></line>
                            <line x1="8" y1="12" x2="16" y2="12"></line>
                        </svg>
                        <span>Formulir Tambah Tugas Kuliah</span>
                    </div>
                    <a href="tasks.php" class="btn btn-secondary btn-sm">&larr; Kembali ke Daftar</a>
                </div>

                <form action="task-create.php" method="POST">
                    <div class="form-group">
                        <label class="form-label" for="judul">Judul / Nama Tugas <span class="required">*</span></label>
                        <input type="text" id="judul" name="judul" class="form-control" placeholder="Contoh: Makalah Perkembangan Cloud Computing" value="<?= htmlspecialchars($judul) ?>" required autofocus>
                        <div class="form-text">Berikan nama tugas yang spesifik dan jelas.</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="mata_kuliah">Mata Kuliah <span class="required">*</span></label>
                            <input type="text" id="mata_kuliah" name="mata_kuliah" class="form-control" placeholder="Contoh: Rekayasa Perangkat Lunak" value="<?= htmlspecialchars($mata_kuliah) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="deadline">Tenggat Waktu (Deadline) <span class="required">*</span></label>
                            <input type="datetime-local" id="deadline" name="deadline" class="form-control" value="<?= htmlspecialchars($deadline) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="prioritas">Tingkat Prioritas <span class="required">*</span></label>
                            <select id="prioritas" name="prioritas" class="form-select" required>
                                <option value="Rendah" <?= $prioritas === 'Rendah' ? 'selected' : '' ?>>Rendah (Santai)</option>
                                <option value="Sedang" <?= $prioritas === 'Sedang' ? 'selected' : '' ?>>Sedang (Standar)</option>
                                <option value="Tinggi" <?= $prioritas === 'Tinggi' ? 'selected' : '' ?>>Tinggi (Mendesak / Bobot Besar)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="status">Status Pengerjaan <span class="required">*</span></label>
                            <select id="status" name="status" class="form-select" required>
                                <option value="Belum Dikerjakan" <?= $status === 'Belum Dikerjakan' ? 'selected' : '' ?>>Belum Dikerjakan</option>
                                <option value="Sedang Dikerjakan" <?= $status === 'Sedang Dikerjakan' ? 'selected' : '' ?>>Sedang Dikerjakan</option>
                                <option value="Selesai" <?= $status === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="deskripsi">Deskripsi & Catatan Tambahan (Opsional)</label>
                        <textarea id="deskripsi" name="deskripsi" class="form-control" rows="4" placeholder="Tuliskan petunjuk tugas, format file pengumpulan, anggota kelompok, atau tautan referensi..."><?= htmlspecialchars($deskripsi) ?></textarea>
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:24px;padding-top:16px;border-top:1px solid var(--border-light);">
                        <a href="tasks.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            <span>Simpan Tugas</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
