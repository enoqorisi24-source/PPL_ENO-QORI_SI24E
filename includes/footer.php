<?php
/**
 * TaskMate - Footer Component
 */
?>
    <!-- Global Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteConfirmModal">
        <div class="modal-card" style="max-width: 440px;">
            <div class="modal-header">
                <h3 class="modal-title" style="color: #ef4444; display:flex; align-items:center; gap:8px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    Konfirmasi Hapus Tugas
                </h3>
                <button type="button" class="modal-close-btn" data-close-modal>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div class="modal-body">
                <p style="font-size:0.95rem;color:var(--text-secondary);margin-bottom:12px;">
                    Apakah Anda yakin ingin menghapus tugas berikut secara permanen?
                </p>
                <div style="background-color:#fef2f2;border:1px solid #fee2e2;border-radius:var(--radius-md);padding:12px 16px;">
                    <strong id="deleteTaskTitle" style="color:#991b1b;font-size:0.95rem;">-</strong>
                </div>
                <p style="font-size:0.8rem;color:var(--text-muted);margin-top:10px;">
                    Tindakan ini tidak dapat dibatalkan. Data tugas akan terhapus dari database.
                </p>
            </div>
            <div class="modal-footer">
                <form action="tasks.php" method="POST" id="deleteTaskForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="task_id" id="deleteTaskIdInput" value="">
                    <button type="button" class="btn btn-secondary btn-sm" data-close-modal>Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm">Ya, Hapus Tugas</button>
                </form>
            </div>
        </div>
    </div>

    </div> <!-- End .main-wrapper -->
</div> <!-- End .app-layout -->

<!-- Core JavaScript -->
<script src="assets/js/app.js"></script>
<?php if (!empty($includeCalendarJs)): ?>
    <script src="assets/js/calendar.js"></script>
<?php endif; ?>
</body>
</html>
