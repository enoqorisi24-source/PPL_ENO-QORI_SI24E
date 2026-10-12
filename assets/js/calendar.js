/**
 * TaskMate - Interactive Deadline Calendar Component
 */

class TaskCalendar {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        if (!this.container) return;

        const today = new Date();
        this.currentYear = options.year || today.getFullYear();
        this.currentMonth = options.month !== undefined ? options.month : today.getMonth(); // 0-indexed
        this.events = options.events || [];

        this.monthNames = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        this.dayNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

        this.init();
    }

    init() {
        this.render();
        this.bindEvents();
    }

    async fetchEvents(year, month) {
        try {
            const resp = await fetch(`api/get_calendar_events.php?year=${year}&month=${month + 1}`);
            const data = await resp.json();
            if (data.success) {
                this.events = data.events || [];
                this.renderGrid();
            }
        } catch (e) {
            console.error('Error fetching calendar events:', e);
        }
    }

    render() {
        this.container.innerHTML = `
            <div class="calendar-card">
                <div class="calendar-header">
                    <div>
                        <h2 class="cal-month-title" id="calMonthTitle">
                            ${this.monthNames[this.currentMonth]} ${this.currentYear}
                        </h2>
                        <span style="font-size:0.85rem;color:var(--text-muted);">
                            Klik tanggal untuk melihat rincian deadline tugas
                        </span>
                    </div>
                    <div class="cal-nav-buttons">
                        <button type="button" class="btn btn-secondary btn-sm" id="calTodayBtn">Hari Ini</button>
                        <button type="button" class="icon-btn" id="calPrevBtn" title="Bulan Sebelumnya">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                        </button>
                        <button type="button" class="icon-btn" id="calNextBtn" title="Bulan Berikutnya">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </button>
                    </div>
                </div>

                <div class="cal-weekdays">
                    ${this.dayNames.map(d => `<div>${d}</div>`).join('')}
                </div>

                <div class="cal-days-grid" id="calDaysGrid">
                    <!-- Days injected here -->
                </div>
            </div>

            <!-- Modal Detail Tugas Tanggal -->
            <div class="modal-overlay" id="calendarDayModal">
                <div class="modal-card">
                    <div class="modal-header">
                        <h3 class="modal-title" id="calDayModalTitle">Deadline Tugas</h3>
                        <button type="button" class="modal-close-btn" data-close-modal>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </button>
                    </div>
                    <div class="modal-body" id="calDayModalBody">
                        <!-- List of tasks -->
                    </div>
                    <div class="modal-footer">
                        <a href="task-create.php" id="calAddNewTaskLink" class="btn btn-primary btn-sm">
                            + Tambah Tugas di Tanggal Ini
                        </a>
                    </div>
                </div>
            </div>
        `;

        this.renderGrid();
    }

    renderGrid() {
        const grid = document.getElementById('calDaysGrid');
        if (!grid) return;

        grid.innerHTML = '';

        const title = document.getElementById('calMonthTitle');
        if (title) {
            title.textContent = `${this.monthNames[this.currentMonth]} ${this.currentYear}`;
        }

        const firstDay = new Date(this.currentYear, this.currentMonth, 1);
        const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0);

        // Menentukan hari pertama dalam minggu (0 = Minggu, konversi agar 0 = Senin)
        let startingDay = firstDay.getDay() - 1;
        if (startingDay === -1) startingDay = 6;

        const totalDays = lastDay.getDate();

        // Hari dari bulan sebelumnya
        const prevMonthLastDay = new Date(this.currentYear, this.currentMonth, 0).getDate();
        for (let i = startingDay - 1; i >= 0; i--) {
            const cell = document.createElement('div');
            cell.className = 'cal-cell other-month';
            cell.innerHTML = `<span class="cal-day-num">${prevMonthLastDay - i}</span>`;
            grid.appendChild(cell);
        }

        const today = new Date();
        const isCurrentMonth = today.getFullYear() === this.currentYear && today.getMonth() === this.currentMonth;
        const todayDate = today.getDate();

        // Hari pada bulan ini
        for (let d = 1; d <= totalDays; d++) {
            const dateStr = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            const cell = document.createElement('div');
            cell.className = 'cal-cell';
            if (isCurrentMonth && d === todayDate) {
                cell.classList.add('today');
            }

            // Temukan tugas di tanggal ini
            const dayEvents = this.events.filter(ev => ev.deadline.startsWith(dateStr));

            let eventsHtml = '';
            dayEvents.slice(0, 3).forEach(ev => {
                let pClass = 'med';
                if (ev.prioritas === 'Tinggi') pClass = 'high';
                if (ev.prioritas === 'Rendah') pClass = 'low';

                eventsHtml += `
                    <div class="cal-event-pill ${pClass}" title="${ev.judul} (${ev.mata_kuliah})">
                        <span>•</span> ${escapeHtml(ev.judul)}
                    </div>
                `;
            });

            if (dayEvents.length > 3) {
                eventsHtml += `
                    <div style="font-size:0.68rem;color:var(--pastel-purple-600);font-weight:700;padding-left:4px;">
                        +${dayEvents.length - 3} tugas lagi
                    </div>
                `;
            }

            cell.innerHTML = `
                <div class="cal-day-num">${d}</div>
                <div style="flex:1;overflow:hidden;">${eventsHtml}</div>
            `;

            cell.addEventListener('click', () => {
                this.showDayDetails(dateStr, d, dayEvents);
            });

            grid.appendChild(cell);
        }

        // Hari dari bulan berikutnya untuk mengisi sisa grid (kelipatan 7)
        const totalRendered = startingDay + totalDays;
        const remainingCells = (7 - (totalRendered % 7)) % 7;
        for (let nextDay = 1; nextDay <= remainingCells; nextDay++) {
            const cell = document.createElement('div');
            cell.className = 'cal-cell other-month';
            cell.innerHTML = `<span class="cal-day-num">${nextDay}</span>`;
            grid.appendChild(cell);
        }
    }

    showDayDetails(dateStr, dayNum, tasks) {
        const modal = document.getElementById('calendarDayModal');
        const modalTitle = document.getElementById('calDayModalTitle');
        const modalBody = document.getElementById('calDayModalBody');
        const addLink = document.getElementById('calAddNewTaskLink');

        if (!modal || !modalBody) return;

        modalTitle.textContent = `Tugas Tanggal ${dayNum} ${this.monthNames[this.currentMonth]} ${this.currentYear}`;
        if (addLink) {
            addLink.href = `task-create.php?deadline=${dateStr}T23:59`;
        }

        if (tasks.length === 0) {
            modalBody.innerHTML = `
                <div class="empty-state" style="padding: 24px 0;">
                    <p style="margin-bottom:0;">Tidak ada deadline tugas pada tanggal ini. Waktu yang tepat untuk bersantai atau mencicil tugas lain!</p>
                </div>
            `;
        } else {
            let html = '<div style="display:flex;flex-direction:column;gap:12px;">';
            tasks.forEach(t => {
                const timeStr = t.deadline.includes(' ') ? t.deadline.split(' ')[1].substring(0, 5) : '23:59';
                let badgeStatusClass = 'badge-pending';
                if (t.status === 'Sedang Dikerjakan') badgeStatusClass = 'badge-progress';
                if (t.status === 'Selesai') badgeStatusClass = 'badge-done';

                html += `
                    <div class="task-item-card" style="margin-bottom:0;">
                        <div class="task-item-top">
                            <div>
                                <h4 class="task-item-title">${escapeHtml(t.judul)}</h4>
                                <span class="task-item-course">${escapeHtml(t.mata_kuliah)}</span>
                            </div>
                            <span class="badge ${badgeStatusClass}">${t.status}</span>
                        </div>
                        <div class="task-item-footer">
                            <div class="task-meta-left">
                                <span class="badge badge-priority-${t.prioritas.toLowerCase()}">Prioritas ${t.prioritas}</span>
                                <span style="font-size:0.75rem;color:var(--text-muted);">Pukul ${timeStr} WIB</span>
                            </div>
                            <div class="task-actions">
                                <a href="task-edit.php?id=${t.id}" class="btn btn-secondary btn-sm">Edit</a>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            modalBody.innerHTML = html;
        }

        modal.classList.add('active');
    }

    bindEvents() {
        const prevBtn = document.getElementById('calPrevBtn');
        const nextBtn = document.getElementById('calNextBtn');
        const todayBtn = document.getElementById('calTodayBtn');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                this.currentMonth--;
                if (this.currentMonth < 0) {
                    this.currentMonth = 11;
                    this.currentYear--;
                }
                this.fetchEvents(this.currentYear, this.currentMonth);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                this.currentMonth++;
                if (this.currentMonth > 11) {
                    this.currentMonth = 0;
                    this.currentYear++;
                }
                this.fetchEvents(this.currentYear, this.currentMonth);
            });
        }

        if (todayBtn) {
            todayBtn.addEventListener('click', () => {
                const now = new Date();
                this.currentYear = now.getFullYear();
                this.currentMonth = now.getMonth();
                this.fetchEvents(this.currentYear, this.currentMonth);
            });
        }
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
