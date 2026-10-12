/**
 * TaskMate - Interactive Application Logic & AJAX Handlers
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Sidebar Toggle
    const mobileToggle = document.getElementById('mobileToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            if (sidebarOverlay) sidebarOverlay.classList.toggle('active');
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', () => {
            if (sidebar) sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('active');
        });
    }

    // 2. Notification Dropdown Toggle
    const notifBtn = document.getElementById('notifBtn');
    const notifMenu = document.getElementById('notifMenu');

    if (notifBtn && notifMenu) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notifMenu.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (!notifMenu.contains(e.target) && !notifBtn.contains(e.target)) {
                notifMenu.classList.remove('open');
            }
        });
    }

    // 3. Quick Status Updater
    document.querySelectorAll('.quick-status-select').forEach(select => {
        select.addEventListener('change', async (e) => {
            const taskId = e.target.dataset.taskId;
            const newStatus = e.target.value;
            const originalValue = e.target.dataset.current;

            try {
                const response = await fetch('api/update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ task_id: taskId, status: newStatus })
                });
                const res = await response.json();

                if (res.success) {
                    showToast('Status tugas berhasil diperbarui!', 'success');
                    e.target.dataset.current = newStatus;
                    // Update badge style if present
                    updateBadgeStatus(e.target, newStatus);
                } else {
                    showToast(res.message || 'Gagal mengubah status tugas', 'danger');
                    e.target.value = originalValue;
                }
            } catch (err) {
                showToast('Koneksi bermasalah', 'danger');
                e.target.value = originalValue;
            }
        });
    });

    // 4. Modal Triggers (Open & Close)
    setupModals();
});

// Toast notification helper
function showToast(message, type = 'info') {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.cssText = 'position:fixed;top:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.style.cssText = 'margin:0;pointer-events:auto;min-width:280px;box-shadow:0 8px 24px rgba(0,0,0,0.12);animation:slideDown 0.3s ease;';
    toast.innerHTML = `
        <div style="display:flex;align-items:center;gap:10px;">
            <span>${message}</span>
        </div>
        <button type="button" style="background:none;border:none;cursor:pointer;font-size:1.1rem;margin-left:12px;" onclick="this.parentElement.remove()">&times;</button>
    `;

    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// Setup Generic Modals
function setupModals() {
    // Open modal buttons
    document.querySelectorAll('[data-open-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-open-modal');
            const targetModal = document.getElementById(targetId);
            if (targetModal) targetModal.classList.add('active');
        });
    });

    // Close modal buttons
    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.modal-overlay');
            if (modal) modal.classList.remove('active');
        });
    });

    // Backdrop click close
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
            }
        });
    });
}

// Confirmation helper for deleting tasks
function confirmDeleteTask(id, title) {
    const modal = document.getElementById('deleteConfirmModal');
    const titleSpan = document.getElementById('deleteTaskTitle');
    const formInput = document.getElementById('deleteTaskIdInput');

    if (modal && titleSpan && formInput) {
        titleSpan.textContent = title;
        formInput.value = id;
        modal.classList.add('active');
    } else {
        if (confirm(`Apakah Anda yakin ingin menghapus tugas "${title}"?`)) {
            window.location.href = `tasks.php?action=delete&id=${id}`;
        }
    }
}

// Helper to update badge color in real time
function updateBadgeStatus(selectElement, status) {
    const parent = selectElement.closest('.task-item-card, tr');
    if (!parent) return;

    const badge = parent.querySelector('.task-status-badge');
    if (badge) {
        badge.className = 'badge task-status-badge';
        if (status === 'Selesai') {
            badge.classList.add('badge-done');
            badge.textContent = 'Selesai';
        } else if (status === 'Sedang Dikerjakan') {
            badge.classList.add('badge-progress');
            badge.textContent = 'Sedang Dikerjakan';
        } else {
            badge.classList.add('badge-pending');
            badge.textContent = 'Belum Dikerjakan';
        }
    }
}
