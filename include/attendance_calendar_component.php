<?php
/**
 * Attendance Calendar Component
 * Clean, interactive monthly attendance calendar for User Dashboard.
 * Includes Text-Based Monthly Summary below the calendar for Late Login, Half Day, Leaves & Working Days.
 * Clean typography without box widgets or nested card clutter.
 */

if (!isset($targetAttendanceUid)) {
    $targetAttendanceUid = (int)($userRow['user_id'] ?? ($_SESSION['id'] ?? 0));
}
if (!isset($targetAttendanceName)) {
    $targetAttendanceName = $userName ?? ($_SESSION['username'] ?? 'User');
}
if (!isset($targetAttendanceRole)) {
    $targetAttendanceRole = $userRole ?? ($_SESSION['role'] ?? 'Staff');
}
?>

<!-- Clean White Theme - Attendance Calendar Styles -->
<style>
.attendance-app-wrapper {
    background: #ffffff;
    color: #1e293b;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    margin-bottom: 24px;
    user-select: none;
}

/* 1. Header Bar */
.att-header-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    padding-bottom: 18px;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 16px;
}

.att-title-group h3 {
    margin: 0;
    font-size: 21px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
    letter-spacing: -0.3px;
}

.att-title-group .att-subtitle {
    margin: 6px 0 0 0;
    font-size: 13px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.att-user-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #cbd5e1;
    padding: 2px 9px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 600;
}

/* Navigation Controls */
.att-nav-controls {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.att-nav-btn {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    color: #334155;
    font-size: 13px;
    font-weight: 600;
    padding: 7px 14px;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}

.att-nav-btn:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
}

.att-nav-btn.btn-today {
    background: #eff6ff;
    border-color: #93c5fd;
    color: #1d4ed8;
}

.att-nav-btn.btn-today:hover {
    background: #dbeafe;
    border-color: #60a5fa;
}

.att-select {
    height: 36px;
    border-radius: 8px;
    border: 1.5px solid #cbd5e1;
    background: #ffffff;
    color: #0f172a;
    font-size: 13px;
    font-weight: 600;
    padding: 4px 10px;
    cursor: pointer;
    outline: none;
    transition: border-color 0.15s;
}

.att-select:focus {
    border-color: #2563eb;
}

/* 2. Clean Indicators Row */
.att-legend-row {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 16px;
    margin-bottom: 18px;
    font-size: 12.5px;
    color: #475569;
}

.att-legend-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
}

.att-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    display: inline-block;
}

.att-dot-present { background: #10b981; }
.att-dot-late { background: #d97706; }
.att-dot-halfday { background: #ea580c; }
.att-dot-leave { background: #7c3aed; }
.att-dot-weekend { background: #94a3b8; }
.att-dot-today { background: #2563eb; }

/* 3. Calendar Grid */
.att-calendar-grid-wrap {
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    overflow: hidden;
    background: #ffffff;
}

.att-weekdays-row {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #f1f5f9;
    border-bottom: 1.5px solid #cbd5e1;
}

.att-weekday-cell {
    padding: 11px 8px;
    text-align: center;
    font-weight: 700;
    font-size: 12.5px;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-right: 1px solid #e2e8f0;
}

.att-weekday-cell:last-child {
    border-right: none;
}

.att-weekday-cell.is-sun {
    color: #dc2626;
    background: #fee2e2;
}

.att-days-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #cbd5e1;
    gap: 1px;
}

.att-day-cell {
    background: #ffffff;
    min-height: 95px;
    padding: 8px 10px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    cursor: pointer;
    transition: all 0.15s ease;
}

.att-day-cell:hover {
    background: #f8fafc;
    box-shadow: inset 0 0 0 1.5px #3b82f6;
    z-index: 2;
}

.att-day-cell.is-other-month {
    background: #f8fafc;
    color: #94a3b8;
    opacity: 0.65;
    cursor: default;
}

.att-day-cell.is-other-month:hover {
    background: #f8fafc;
    box-shadow: none;
}

.att-day-cell.is-weekend {
    background: #fdfdfd;
}

.att-day-cell.is-today {
    background: #eff6ff;
    box-shadow: inset 0 0 0 2px #2563eb;
    z-index: 3;
}

.att-day-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 4px;
}

.att-day-number {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    width: 26px;
    height: 26px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.att-day-cell.is-today .att-day-number {
    background: #2563eb;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(37,99,235,0.3);
}

.att-day-cell.is-sun .att-day-number {
    color: #dc2626;
}

.att-day-cell.is-other-month .att-day-number {
    color: #94a3b8;
}

.att-day-badge-tag {
    font-size: 10px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.att-tag-today {
    background: #dbeafe;
    color: #1e40af;
}

.att-tag-weekend {
    background: #fee2e2;
    color: #b91c1c;
}

/* Day status pills on calendar */
.att-day-body {
    margin-top: 6px;
    min-height: 26px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}

.att-pill-status {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    width: 100%;
    box-sizing: border-box;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.att-pill-empty {
    background: #f1f5f9;
    color: #64748b;
    border: 1px dashed #cbd5e1;
}

.att-pill-off {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

.att-pill-present {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
}

.att-pill-late {
    background: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
}

.att-pill-halfday {
    background: #fff7ed;
    color: #c2410c;
    border: 1px solid #fed7aa;
}

.att-pill-leave {
    background: #faf5ff;
    color: #6b21a8;
    border: 1px solid #e9d5ff;
}

/* 4. Text-Based Attendance Summary (Refined, Highly Readable & Elegant) */
.att-text-summary-section {
    margin-top: 26px;
    padding-top: 20px;
    border-top: 2px solid #f1f5f9;
}

.att-summary-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 16px;
}

.att-summary-title-text {
    margin: 0;
    font-size: 16.5px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
    letter-spacing: -0.2px;
}

.att-summary-subtext {
    font-size: 12.5px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 6px;
}

.att-summary-content {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.att-summary-item-line {
    display: flex;
    flex-direction: column;
    padding: 0;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #cbd5e1;
    border-radius: 10px;
    font-size: 13.5px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.02);
    transition: all 0.2s ease;
    overflow: hidden;
}

.att-summary-item-line:hover {
    border-color: #cbd5e1;
    box-shadow: 0 3px 10px rgba(15, 23, 42, 0.05);
}

.att-summary-item-line.is-open {
    border-color: #cbd5e1;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.07);
}

.att-summary-item-line.line-late {
    border-left-color: #d97706;
}
.att-summary-item-line.line-halfday {
    border-left-color: #ea580c;
}
.att-summary-item-line.line-leave {
    border-left-color: #7c3aed;
}
.att-summary-item-line.line-present {
    border-left-color: #10b981;
}
.att-summary-item-line.line-working {
    border-left-color: #2563eb;
}

.att-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 18px;
    cursor: pointer;
    user-select: none;
    background: #ffffff;
    transition: background 0.15s ease;
    gap: 12px;
}

.att-card-header:hover {
    background: #fbfcfe;
}

.att-summary-item-line.is-open .att-card-header {
    background: #f8fafc;
    border-bottom: 1px solid #edf2f7;
}

.att-line-head {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}

.att-row-icon-wrap {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}
.icon-late { background: #fef3c7; color: #b45309; }
.icon-halfday { background: #ffedd5; color: #c2410c; }
.icon-leave { background: #f3e8ff; color: #7c3aed; }
.icon-present { background: #dcfce7; color: #15803d; }
.icon-working { background: #eff6ff; color: #2563eb; }

.att-line-label {
    color: #0f172a;
    font-weight: 700;
    font-size: 14px;
}

.att-count-badge {
    font-size: 12px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 12px;
    display: inline-block;
    line-height: 1.4;
}
.badge-late { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.badge-halfday { background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }
.badge-leave { background: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
.badge-present { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.badge-working { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }

.att-card-action {
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.att-action-hint {
    font-size: 12.5px;
    color: #64748b;
    font-weight: 500;
}
.att-action-hint .hint-muted {
    color: #94a3b8;
    font-style: italic;
}
.att-action-hint .hint-active {
    color: #334155;
    font-weight: 600;
}

.att-chevron {
    color: #94a3b8;
    font-size: 12px;
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), color 0.15s ease, background 0.15s ease;
    width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #f1f5f9;
}
.att-card-header:hover .att-chevron {
    color: #1d4ed8;
    background: #e0e7ff;
}
.att-summary-item-line.is-open .att-chevron {
    transform: rotate(180deg);
    color: #2563eb;
    background: #dbeafe;
}

.att-card-body {
    padding: 14px 18px 16px;
    background: #f8fafc;
}

.att-details-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.att-detail-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.15s ease;
    gap: 12px;
}
.att-detail-item:hover {
    border-color: #cbd5e1;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
}
.att-detail-item.item-late {
    border-left: 3px solid #d97706;
}
.att-detail-item.item-halfday {
    border-left: 3px solid #ea580c;
}
.att-detail-item.item-leave {
    border-left: 3px solid #7c3aed;
}

.att-detail-item-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
    flex-wrap: wrap;
}

.att-detail-date-badge {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #f1f5f9;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    white-space: nowrap;
}

.att-detail-note {
    font-size: 12.5px;
    color: #475569;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 320px;
}

.att-detail-item-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
    flex-wrap: wrap;
}

.att-time-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    line-height: 1.3;
}
.att-time-pill.in-time {
    background: #eff6ff;
    color: #1e40af;
    border: 1px solid #bfdbfe;
}
.att-time-pill.in-time-late {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}
.att-time-pill.out-time {
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
}
.att-time-pill .time-label {
    font-weight: 600;
    font-size: 10.5px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    opacity: 0.8;
}
.att-time-pill .time-val {
    font-weight: 700;
    font-size: 12.5px;
}

.att-btn-view-day {
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 6px;
    color: #2563eb;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    transition: all 0.15s ease;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.att-btn-view-day:hover {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
}

.att-empty-state-box {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    background: #ffffff;
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    color: #475569;
    font-size: 13px;
}

.att-present-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
    gap: 8px;
}
.att-present-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-left: 3px solid #10b981;
    border-radius: 8px;
    padding: 9px 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    transition: all 0.15s ease;
}
.att-present-card:hover {
    border-color: #86efac;
    border-left-color: #10b981;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.08);
}
.pres-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.pres-date {
    font-size: 12.5px;
    font-weight: 700;
    color: #0f172a;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.pres-card-times {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11.5px;
    color: #475569;
    border-top: 1px solid #f1f5f9;
    padding-top: 5px;
}
.pres-card-times strong {
    color: #0f172a;
}

.att-working-breakdown-card {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.att-wb-stats-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.att-wb-stat-box {
    flex: 1;
    min-width: 140px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 14px;
    text-align: center;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
}
.att-wb-stat-box .wb-num {
    display: block;
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}
.att-wb-stat-box .wb-lbl {
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
}
.att-wb-stat-box.off-box .wb-num {
    color: #d97706;
}
.att-wb-stat-box.net-box {
    background: #eff6ff;
    border-color: #bfdbfe;
}
.att-wb-stat-box.net-box .wb-num {
    color: #1d4ed8;
}
.att-sundays-list-wrap {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 14px;
}
.sundays-title {
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.sundays-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.sunday-chip {
    background: #fff7ed;
    color: #c2410c;
    border: 1px solid #fed7aa;
    font-size: 12px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.15s ease;
}
.sunday-chip:hover {
    background: #ea580c;
    color: #ffffff;
    border-color: #ea580c;
}
.att-toggle-all-btn {
    font-size: 12px;
    border-radius: 6px;
    padding: 5px 12px;
    font-weight: 600;
    color: #475569;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    transition: all 0.15s ease;
}
.att-toggle-all-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #94a3b8;
}

/* 5. Responsive Styles */
@media (max-width: 768px) {
    .attendance-app-wrapper {
        padding: 16px 12px;
    }
    .att-header-bar {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .att-nav-controls {
        width: 100%;
        justify-content: space-between;
    }
    .att-day-cell {
        min-height: 70px;
        padding: 5px 4px;
    }
    .att-day-number {
        font-size: 12px;
        width: 22px;
        height: 22px;
    }
    .att-day-badge-tag {
        display: none;
    }
    .att-pill-status {
        font-size: 9.5px;
        padding: 2px 4px;
    }
    .att-summary-item-line {
        flex-direction: column;
        gap: 4px;
        padding: 8px 10px;
    }
    .att-line-head {
        min-width: auto;
    }
}
</style>

<div class="attendance-app-wrapper">

    <!-- 1. Header Bar with Title and Month/Year Navigators -->
    <div class="att-header-bar">
        <div class="att-title-group">
            <h3>
                <i class="fa fa-calendar-check-o" style="color: #6366f1;"></i>
                <span id="attMonthYearDisplay">October 2026</span>
            </h3>
            <p class="att-subtitle">
                <span>Attendance of <strong><?php echo htmlspecialchars($targetAttendanceName); ?></strong></span>
            </p>
        </div>

        <div class="att-nav-controls">
            <button type="button" class="att-nav-btn" id="attBtnPrev" title="Previous Month">
                <i class="fa fa-chevron-left"></i> Prev
            </button>
            <button type="button" class="att-nav-btn btn-today" id="attBtnToday" title="Jump to Current Month">
                <i class="fa fa-crosshairs"></i> Today
            </button>
            <button type="button" class="att-nav-btn" id="attBtnNext" title="Next Month">
                Next <i class="fa fa-chevron-right"></i>
            </button>

            <!-- Jump to Month & Year Dropdowns -->
            <select id="attSelectMonth" class="att-select" title="Jump to Month">
                <option value="0">January</option>
                <option value="1">February</option>
                <option value="2">March</option>
                <option value="3">April</option>
                <option value="4">May</option>
                <option value="5">June</option>
                <option value="6">July</option>
                <option value="7">August</option>
                <option value="8">September</option>
                <option value="9">October</option>
                <option value="10">November</option>
                <option value="11">December</option>
            </select>

            <select id="attSelectYear" class="att-select" title="Jump to Year">
                <?php
                $curY = (int)date('Y');
                for ($y = $curY - 3; $y <= $curY + 3; $y++): ?>
                    <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </div>

    <!-- 2. Clean Indicators Row -->
    <div class="att-legend-row">
        <div class="att-legend-item">
            <span class="att-dot att-dot-present"></span> Present
        </div>
        <div class="att-legend-item">
            <span class="att-dot att-dot-late"></span> Late Login
        </div>
        <div class="att-legend-item">
            <span class="att-dot att-dot-halfday"></span> Half Day
        </div>
        <div class="att-legend-item">
            <span class="att-dot att-dot-leave"></span> Leave
        </div>
        <div class="att-legend-item">
            <span class="att-dot att-dot-weekend"></span> Weekend (Sunday)
        </div>
        <div class="att-legend-item">
            <span class="att-dot att-dot-today"></span> Today
        </div>
    </div>

    <!-- 3. Calendar Main Grid -->
    <div class="att-calendar-grid-wrap">
        <!-- Weekday Headers (Sun to Sat) -->
        <div class="att-weekdays-row">
            <div class="att-weekday-cell is-sun">Sun</div>
            <div class="att-weekday-cell">Mon</div>
            <div class="att-weekday-cell">Tue</div>
            <div class="att-weekday-cell">Wed</div>
            <div class="att-weekday-cell">Thu</div>
            <div class="att-weekday-cell">Fri</div>
            <div class="att-weekday-cell">Sat</div>
        </div>

        <!-- Dynamic Days Grid (Rendered by JS) -->
        <div class="att-days-grid" id="attDaysGrid">
            <!-- Populated via JavaScript -->
        </div>
    </div>

    <!-- 4. Monthly Attendance Summary (Collapsible Cards, Clean & Detailed on Click) -->
    <div class="att-text-summary-section">
        <div class="att-summary-header-row">
            <div>
                <h4 class="att-summary-title-text">
                    <i class="fa fa-file-text-o" style="color: #2563eb;"></i>
                    Monthly Attendance Summary &mdash; <span id="attSummaryMonthTitle" style="color: #2563eb;">October 2026</span>
                </h4>
                <div class="att-summary-subtext" style="margin-top: 4px;">
                    <i class="fa fa-info-circle" style="color: #6366f1;"></i> Click any section card below to view detailed breakdown (Sign In / Sign Out)
                </div>
            </div>
            <div class="att-summary-header-actions">
                <button type="button" class="btn btn-xs btn-default att-toggle-all-btn" id="attToggleAllCards" title="Expand or collapse all summary sections">
                    <i class="fa fa-angle-double-down"></i> Expand All
                </button>
            </div>
        </div>

        <div class="att-summary-content">
            <!-- 1. Late Login Line / Card -->
            <div class="att-summary-item-line line-late" id="attCardLate">
                <div class="att-card-header" role="button" tabindex="0" title="Click to view/hide Late Login breakdown">
                    <div class="att-line-head">
                        <span class="att-row-icon-wrap icon-late"><i class="fa fa-clock-o"></i></span>
                        <span class="att-line-label">Late Login</span>
                        <span class="att-count-badge badge-late" id="sumLateCount">0</span>
                    </div>
                    <div class="att-card-action">
                        <span class="att-action-hint" id="sumLateActionHint">Click to view</span>
                        <i class="fa fa-chevron-down att-chevron"></i>
                    </div>
                </div>
                <div class="att-card-body" id="sumLateDetails" style="display: none;">
                    <!-- Expanded breakdown rendered by JS -->
                </div>
            </div>

            <!-- 2. Half Day Line / Card -->
            <div class="att-summary-item-line line-halfday" id="attCardHalfDay">
                <div class="att-card-header" role="button" tabindex="0" title="Click to view/hide Half Day breakdown">
                    <div class="att-line-head">
                        <span class="att-row-icon-wrap icon-halfday"><i class="fa fa-hourglass-half"></i></span>
                        <span class="att-line-label">Half Day</span>
                        <span class="att-count-badge badge-halfday" id="sumHalfDayCount">0</span>
                    </div>
                    <div class="att-card-action">
                        <span class="att-action-hint" id="sumHalfDayActionHint">Click to view</span>
                        <i class="fa fa-chevron-down att-chevron"></i>
                    </div>
                </div>
                <div class="att-card-body" id="sumHalfDayDetails" style="display: none;">
                    <!-- Expanded breakdown rendered by JS -->
                </div>
            </div>

            <!-- 3. Leave Line / Card -->
            <div class="att-summary-item-line line-leave" id="attCardLeave">
                <div class="att-card-header" role="button" tabindex="0" title="Click to view/hide Leave breakdown">
                    <div class="att-line-head">
                        <span class="att-row-icon-wrap icon-leave"><i class="fa fa-calendar-minus-o"></i></span>
                        <span class="att-line-label">Leave</span>
                        <span class="att-count-badge badge-leave" id="sumLeaveCount">0</span>
                    </div>
                    <div class="att-card-action">
                        <span class="att-action-hint" id="sumLeaveActionHint">Click to view</span>
                        <i class="fa fa-chevron-down att-chevron"></i>
                    </div>
                </div>
                <div class="att-card-body" id="sumLeaveDetails" style="display: none;">
                    <!-- Expanded breakdown rendered by JS -->
                </div>
            </div>

            <!-- 4. Present Line / Card -->
            <div class="att-summary-item-line line-present" id="attCardPresent">
                <div class="att-card-header" role="button" tabindex="0" title="Click to view/hide Present breakdown">
                    <div class="att-line-head">
                        <span class="att-row-icon-wrap icon-present"><i class="fa fa-check-circle"></i></span>
                        <span class="att-line-label">Present</span>
                        <span class="att-count-badge badge-present" id="sumPresentCount">0 Days</span>
                    </div>
                    <div class="att-card-action">
                        <span class="att-action-hint" id="sumPresentActionHint">Click to view</span>
                        <i class="fa fa-chevron-down att-chevron"></i>
                    </div>
                </div>
                <div class="att-card-body" id="sumPresentDetails" style="display: none;">
                    <!-- Expanded breakdown rendered by JS -->
                </div>
            </div>

            <!-- 5. Total Working Days Line / Card -->
            <div class="att-summary-item-line line-working" id="attCardWorking">
                <div class="att-card-header" role="button" tabindex="0" title="Click to view/hide Working Days breakdown">
                    <div class="att-line-head">
                        <span class="att-row-icon-wrap icon-working"><i class="fa fa-briefcase"></i></span>
                        <span class="att-line-label">Working Days</span>
                        <span class="att-count-badge badge-working"><span id="sumWorkingDays">27</span> Days</span>
                    </div>
                    <div class="att-card-action">
                        <span class="att-action-hint" id="sumWorkingActionHint">Sundays: <strong id="sumSundayCount">4</strong> &bull; Total: <strong id="sumTotalMonthDays">31</strong></span>
                        <i class="fa fa-chevron-down att-chevron"></i>
                    </div>
                </div>
                <div class="att-card-body" id="sumWorkingDetails" style="display: none;">
                    <!-- Expanded breakdown rendered by JS -->
                </div>
            </div>
        </div>
    </div>

</div>

<!-- 5. Day Details Modal -->
<div class="modal fade" id="attDayDetailModal" tabindex="-1" role="dialog" aria-labelledby="attDayDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width: 480px; margin-top: 60px;">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: 1px solid #cbd5e1; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
            <div class="modal-header" style="background: linear-gradient(135deg, #0d4d8b 0%, #2563eb 100%); color: #ffffff; padding: 16px 20px;">
                <h5 class="modal-title" id="attDayDetailModalLabel" style="font-weight: 700; font-size: 16px; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-calendar-check-o"></i> Attendance Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #ffffff; opacity: 0.85; text-shadow: none; font-size: 20px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 22px 20px; background: #ffffff;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px;">
                    <div>
                        <div id="modalAttFullDate" style="font-size: 16px; font-weight: 700; color: #0f172a;">Monday, 19 October 2026</div>
                        <div id="modalAttDayType" style="font-size: 12.5px; color: #64748b; margin-top: 3px;">Standard Working Day</div>
                    </div>
                    <div id="modalAttStatusBadge">
                        <span class="att-pill-status att-pill-empty" style="font-size: 12px; padding: 4px 10px;">Not Marked</span>
                    </div>
                </div>

                <!-- Punch Log & Shift Details -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Sign In</div>
                            <div id="modalPunchInVal" style="font-size: 14.5px; font-weight: 700; color: #0f172a; margin-top: 2px;">--:--</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Sign Out</div>
                            <div id="modalPunchOutVal" style="font-size: 14.5px; font-weight: 700; color: #0f172a; margin-top: 2px;">--:--</div>
                        </div>
                    </div>
                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e2e8f0; display: flex; justify-content: space-between; font-size: 12px;">
                        <span style="color: #64748b;">Note / Shift Status:</span>
                        <strong id="modalPunchNoteVal" style="color: #0f172a;">--</strong>
                    </div>
                    <div id="modalAttHoursRow" style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e2e8f0; display: none; justify-content: space-between; font-size: 12px;">
                        <span style="color: #64748b;">Total Working Hours:</span>
                        <strong id="modalPunchHoursVal" style="color: #0f172a;">--</strong>
                    </div>
                </div>

                <!-- Both Locations: Sign In & Sign Out Locations -->
                <div id="modalLocationsSection">
                    <!-- 1. Sign In Location -->
                    <div id="modalInLocationBox" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px; margin-bottom: 12px; display: none;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                            <span style="font-size: 11.5px; font-weight: 700; color: #15803d; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
                                <i class="fa fa-map-marker" style="color: #16a34a; font-size: 14px;"></i> Sign In Location
                            </span>
                            <span id="modalInTimeSmall" style="font-size: 11px; font-weight: 600; color: #166534; background: #dcfce7; padding: 2px 7px; border-radius: 4px;">--:--</span>
                        </div>
                        <div id="modalPunchInLocationVal" style="font-size: 12.5px; color: #0f172a; font-weight: 600; line-height: 1.45;">--</div>
                        <div id="modalInMapLinkWrap" style="margin-top: 6px; display: none;">
                            <a id="modalInMapLink" href="#" target="_blank" rel="noopener noreferrer" style="font-size: 11px; color: #15803d; font-weight: 600; text-decoration: underline; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fa fa-external-link"></i> View on Google Maps
                            </a>
                        </div>
                    </div>

                    <!-- 2. Sign Out Location -->
                    <div id="modalOutLocationBox" style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 12px; margin-bottom: 12px; display: none;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                            <span style="font-size: 11.5px; font-weight: 700; color: #c2410c; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
                                <i class="fa fa-map-marker" style="color: #ea580c; font-size: 14px;"></i> Sign Out Location
                            </span>
                            <span id="modalOutTimeSmall" style="font-size: 11px; font-weight: 600; color: #9a3412; background: #ffedd5; padding: 2px 7px; border-radius: 4px;">--:--</span>
                        </div>
                        <div id="modalPunchOutLocationVal" style="font-size: 12.5px; color: #0f172a; font-weight: 600; line-height: 1.45;">--</div>
                        <div id="modalOutMapLinkWrap" style="margin-top: 6px; display: none;">
                            <a id="modalOutMapLink" href="#" target="_blank" rel="noopener noreferrer" style="font-size: 11px; color: #c2410c; font-weight: 600; text-decoration: underline; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fa fa-external-link"></i> View on Google Maps
                            </a>
                        </div>
                    </div>

                    <!-- Sign Out Pending Notice -->
                    <div id="modalOutPendingBox" style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 10px 12px; margin-bottom: 12px; display: none; align-items: center; gap: 8px; font-size: 12px; color: #64748b;">
                        <i class="fa fa-hourglass-half" style="color: #d97706; font-size: 14px;"></i>
                        <span><strong>Sign Out Pending:</strong> Not signed out yet for this shift.</span>
                    </div>

                    <!-- Empty Day Notice -->
                    <div id="modalNoPunchBox" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; text-align: center; color: #64748b; font-size: 12.5px; display: none;">
                        <i class="fa fa-info-circle" style="color: #94a3b8; font-size: 18px; margin-bottom: 4px; display: block;"></i>
                        <span id="modalNoPunchText">No sign-in activity recorded for this date.</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 6px; font-size: 13px; font-weight: 600; padding: 6px 16px;">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- 6. Calendar & Summary Dynamic Logic (Future-proof Javascript) -->
<script>
(function() {
    var today = new Date();
    var currentYear = today.getFullYear();
    var currentMonth = today.getMonth(); // 0-indexed (0 = Jan, 9 = Oct)

    var viewedYear = currentYear;
    var viewedMonth = currentMonth;

    var monthNames = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    var dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

    function padZero(num) {
        return num < 10 ? '0' + num : num;
    }

    // Attendance data cache (Key: 'YYYY-MM-DD' => Object)
<?php
    $dbAttendanceMap = [];
    if (isset($db) && $db instanceof PDO && !empty($targetAttendanceUid)) {
        try {
            $attStmt = $db->prepare("
                SELECT attendance_date, status, in_time, out_time, total_working_hours, remarks, 
                       punch_in_selfie_url, punch_out_selfie_url, 
                       in_address, in_latitude, in_longitude, 
                       out_address, out_latitude, out_longitude 
                FROM aitf_attendance 
                WHERE user_id = :uid 
                  AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL 120 DAY)
                ORDER BY attendance_date ASC, id ASC
            ");
            $attStmt->execute([':uid' => $targetAttendanceUid]);
            $rawRecords = $attStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rawRecords as $r) {
                $d = $r['attendance_date'];
                // Robust location address fallbacks
                $inAddr = trim($r['in_address'] ?? '');
                if ($inAddr === '' && !empty($r['in_latitude']) && !empty($r['in_longitude'])) {
                    $inAddr = $r['in_latitude'] . ', ' . $r['in_longitude'];
                }
                $outAddr = trim($r['out_address'] ?? '');
                if ($outAddr === '' && !empty($r['out_latitude']) && !empty($r['out_longitude'])) {
                    $outAddr = $r['out_latitude'] . ', ' . $r['out_longitude'];
                }
                $dbAttendanceMap[$d] = [
                    'status' => $r['status'],
                    'label' => ($r['status'] === 'late') ? 'Late Login' : (($r['status'] === 'halfday') ? 'Half Day' : ucfirst($r['status'])),
                    'inTime' => !empty($r['in_time']) ? date('h:i A', strtotime($r['in_time'])) : '--:--',
                    'outTime' => !empty($r['out_time']) ? date('h:i A', strtotime($r['out_time'])) : '--:--',
                    'workingHours' => !empty($r['total_working_hours']) ? (float)$r['total_working_hours'] : 0,
                    'note' => $r['remarks'] ?? '',
                    'selfieUrl' => $r['punch_in_selfie_url'] ?? '',
                    'punchInSelfieUrl' => $r['punch_in_selfie_url'] ?? '',
                    'punchOutSelfieUrl' => $r['punch_out_selfie_url'] ?? '',
                    'inAddress' => $inAddr,
                    'inLat' => $r['in_latitude'] !== null ? (float)$r['in_latitude'] : null,
                    'inLng' => $r['in_longitude'] !== null ? (float)$r['in_longitude'] : null,
                    'outAddress' => $outAddr,
                    'outLat' => $r['out_latitude'] !== null ? (float)$r['out_latitude'] : null,
                    'outLng' => $r['out_longitude'] !== null ? (float)$r['out_longitude'] : null
                ];
            }
        } catch (Exception $e) {
            error_log("Error loading attendance map: " . $e->getMessage());
        }
    }
?>
    window.AITF_ATTENDANCE_CACHE = <?php echo json_encode($dbAttendanceMap, JSON_UNESCAPED_SLASHES); ?> || {};

    // Render Calendar Grid
    function renderCalendar(year, month) {
        var firstDayIndex = new Date(year, month, 1).getDay(); // 0 (Sun) - 6 (Sat)
        var daysInCurrentMonth = new Date(year, month + 1, 0).getDate();
        var daysInPrevMonth = new Date(year, month, 0).getDate();

        // Update header & selectors
        $('#attMonthYearDisplay').text(monthNames[month] + ' ' + year);
        $('#attSelectMonth').val(month);
        $('#attSelectYear').val(year);

        var gridHtml = '';

        // 1. Trailing days from previous month
        var prevMonthYear = month === 0 ? year - 1 : year;
        var prevMonthNum = month === 0 ? 11 : month - 1;
        for (var p = firstDayIndex - 1; p >= 0; p--) {
            var dayNum = daysInPrevMonth - p;
            var dateStr = prevMonthYear + '-' + padZero(prevMonthNum + 1) + '-' + padZero(dayNum);
            gridHtml += '<div class="att-day-cell is-other-month" data-date="' + dateStr + '">' +
                '<div class="att-day-top">' +
                    '<span class="att-day-number">' + dayNum + '</span>' +
                '</div>' +
                '<div class="att-day-body">' +
                    '<span class="att-pill-status att-pill-empty" style="opacity: 0.5;"><i class="fa fa-minus"></i></span>' +
                '</div>' +
            '</div>';
        }

        // 2. Days of current month
        for (var day = 1; day <= daysInCurrentMonth; day++) {
            var dateObj = new Date(year, month, day);
            var dayOfWeek = dateObj.getDay();
            var isSunday = (dayOfWeek === 0);
            var isToday = (year === today.getFullYear() && month === today.getMonth() && day === today.getDate());

            var dateStr = year + '-' + padZero(month + 1) + '-' + padZero(day);

            var cellClasses = 'att-day-cell';
            if (isSunday) cellClasses += ' is-weekend is-sun';
            if (isToday) cellClasses += ' is-today';

            var badgeTag = '';
            if (isToday) {
                badgeTag = '<span class="att-day-badge-tag att-tag-today">Today</span>';
            } else if (isSunday) {
                badgeTag = '<span class="att-day-badge-tag att-tag-weekend">Weekend</span>';
            }

            // Attendance pill status
            var statusHtml = '';
            var record = window.AITF_ATTENDANCE_CACHE[dateStr] || null;

            if (record) {
                if (record.status === 'present') {
                    statusHtml = '<span class="att-pill-status att-pill-present"><i class="fa fa-check"></i> Present</span>';
                } else if (record.status === 'late') {
                    statusHtml = '<span class="att-pill-status att-pill-late"><i class="fa fa-clock-o"></i> Late Login</span>';
                } else if (record.status === 'halfday') {
                    statusHtml = '<span class="att-pill-status att-pill-halfday"><i class="fa fa-hourglass-half"></i> Half Day</span>';
                } else if (record.status === 'leave') {
                    statusHtml = '<span class="att-pill-status att-pill-leave"><i class="fa fa-calendar-minus-o"></i> Leave</span>';
                }
            } else if (isSunday) {
                statusHtml = '<span class="att-pill-status att-pill-off"><i class="fa fa-coffee"></i> Off</span>';
            } else {
                statusHtml = '<span class="att-pill-status att-pill-empty"><i class="fa fa-circle-o"></i> Not Marked</span>';
            }

            gridHtml += '<div class="' + cellClasses + '" data-date="' + dateStr + '" data-day="' + day + '" data-day-name="' + dayNames[dayOfWeek] + '" data-is-sun="' + (isSunday ? '1' : '0') + '">' +
                '<div class="att-day-top">' +
                    '<span class="att-day-number">' + day + '</span>' +
                    badgeTag +
                '</div>' +
                '<div class="att-day-body">' +
                    statusHtml +
                '</div>' +
            '</div>';
        }

        // 3. Leading days for next month to complete the grid
        var totalCellsSoFar = firstDayIndex + daysInCurrentMonth;
        var totalGridSlots = totalCellsSoFar > 35 ? 42 : 35;
        var remainingCells = totalGridSlots - totalCellsSoFar;
        var nextMonthYear = month === 11 ? year + 1 : year;
        var nextMonthNum = month === 11 ? 0 : month + 1;

        for (var n = 1; n <= remainingCells; n++) {
            var dateStr = nextMonthYear + '-' + padZero(nextMonthNum + 1) + '-' + padZero(n);
            gridHtml += '<div class="att-day-cell is-other-month" data-date="' + dateStr + '">' +
                '<div class="att-day-top">' +
                    '<span class="att-day-number">' + n + '</span>' +
                '</div>' +
                '<div class="att-day-body">' +
                    '<span class="att-pill-status att-pill-empty" style="opacity: 0.5;"><i class="fa fa-minus"></i></span>' +
                '</div>' +
            '</div>';
        }

        $('#attDaysGrid').html(gridHtml);

        // Update Text-Based Monthly Summary section below calendar
        updateMonthlySummary(year, month);
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function updateExpandAllBtnState() {
        var totalCards = $('.att-summary-item-line').length;
        var openCards = $('.att-summary-item-line.is-open').length;
        if (openCards === totalCards && totalCards > 0) {
            $('#attToggleAllCards').html('<i class="fa fa-angle-double-up"></i> Collapse All');
        } else {
            $('#attToggleAllCards').html('<i class="fa fa-angle-double-down"></i> Expand All');
        }
    }

    // Open Day Details Modal for a given date string (YYYY-MM-DD)
    function openDayModalByDate(dateStr) {
        if (!dateStr) return;
        var dParts = dateStr.split('-');
        var y = parseInt(dParts[0], 10);
        var m = parseInt(dParts[1], 10) - 1;
        var d = parseInt(dParts[2], 10);
        var dObj = new Date(y, m, d);
        var dayName = dayNames[dObj.getDay()];
        var isSun = (dObj.getDay() === 0);

        $('#attDayDetailModal').data('active-date', dateStr);
        var formattedDate = dayName + ', ' + d + ' ' + monthNames[m] + ' ' + y;
        $('#modalAttFullDate').text(formattedDate);

        if (isSun) {
            $('#modalAttDayType').text('Weekend (Sunday - Official Off)');
            var rec = window.AITF_ATTENDANCE_CACHE[dateStr] || null;
            if (rec) {
                updateModalStatusBadge(dateStr);
            } else {
                $('#modalAttStatusBadge').html('<span class="att-pill-status att-pill-off" style="font-size: 12px; padding: 4px 10px;"><i class="fa fa-coffee"></i> Weekend / Off</span>');
                $('#modalPunchInVal').text('--:--');
                $('#modalPunchOutVal').text('--:--');
                $('#modalPunchNoteVal').text('Weekly Off (Sunday)');
                $('#modalAttHoursRow').hide();
                $('#modalInLocationBox').hide();
                $('#modalOutLocationBox').hide();
                $('#modalOutPendingBox').hide();
                $('#modalNoPunchText').text('Weekly Off (Sunday) - Official holiday.');
                $('#modalNoPunchBox').show();
            }
        } else {
            $('#modalAttDayType').text('Standard Working Day');
            updateModalStatusBadge(dateStr);
        }

        $('#attDayDetailModal').modal('show');
    }

    // Update the Collapsible Monthly Summary Section below Calendar
    function updateMonthlySummary(year, month) {
        var daysInCurrentMonth = new Date(year, month + 1, 0).getDate();
        var lateList = [];
        var halfDayList = [];
        var leaveList = [];
        var presentList = [];
        var totalSundays = 0;
        var sundayList = [];

        for (var d = 1; d <= daysInCurrentMonth; d++) {
            var dateObj = new Date(year, month, d);
            var isSun = (dateObj.getDay() === 0);
            var dateStr = year + '-' + padZero(month + 1) + '-' + padZero(d);

            if (isSun) {
                totalSundays++;
                sundayList.push({
                    dateStr: dateStr,
                    dayNum: d,
                    dayLabel: 'Sun, ' + d + ' ' + monthNames[month].substring(0, 3)
                });
            }

            var rec = window.AITF_ATTENDANCE_CACHE[dateStr];
            if (rec) {
                var dayLabel = dayNames[dateObj.getDay()].substring(0, 3) + ', ' + d + ' ' + monthNames[month].substring(0, 3) + ' ' + year;
                var shortLabel = dayNames[dateObj.getDay()].substring(0, 3) + ', ' + d + ' ' + monthNames[month].substring(0, 3);
                if (rec.status === 'late') {
                    lateList.push({
                        dateStr: dateStr,
                        dayLabel: dayLabel,
                        shortLabel: shortLabel,
                        inTime: rec.inTime || '--:--',
                        outTime: rec.outTime || '--:--',
                        note: rec.note || 'Late login recorded',
                        inAddress: rec.inAddress || '',
                        outAddress: rec.outAddress || '',
                        selfieUrl: rec.selfieUrl || ''
                    });
                } else if (rec.status === 'halfday') {
                    halfDayList.push({
                        dateStr: dateStr,
                        dayLabel: dayLabel,
                        shortLabel: shortLabel,
                        inTime: rec.inTime || '--:--',
                        outTime: rec.outTime || '--:--',
                        note: rec.note || 'Half day shift',
                        inAddress: rec.inAddress || '',
                        outAddress: rec.outAddress || '',
                        selfieUrl: rec.selfieUrl || ''
                    });
                } else if (rec.status === 'leave') {
                    leaveList.push({
                        dateStr: dateStr,
                        dayLabel: dayLabel,
                        shortLabel: shortLabel,
                        note: rec.note || 'Leave'
                    });
                } else if (rec.status === 'present') {
                    presentList.push({
                        dateStr: dateStr,
                        dayLabel: dayLabel,
                        shortLabel: shortLabel,
                        inTime: rec.inTime || '--:--',
                        outTime: rec.outTime || '--:--',
                        note: rec.note || 'Full day attendance',
                        inAddress: rec.inAddress || '',
                        outAddress: rec.outAddress || ''
                    });
                }
            }
        }

        var workingDays = daysInCurrentMonth - totalSundays;

        $('#attSummaryMonthTitle').text(monthNames[month] + ' ' + year);
        $('#sumTotalMonthDays').text(daysInCurrentMonth);
        $('#sumWorkingDays').text(workingDays);
        $('#sumSundayCount').text(totalSundays);

        // 1. Late Login Line / Card
        $('#sumLateCount').text(lateList.length);
        if (lateList.length === 0) {
            $('#sumLateActionHint').html('<span class="hint-muted">0 recorded</span>');
            $('#sumLateDetails').html(
                '<div class="att-empty-state-box">' +
                    '<i class="fa fa-check-circle" style="color: #16a34a; font-size: 16px;"></i> ' +
                    '<span>No late logins recorded for <strong>' + monthNames[month] + ' ' + year + '</strong>. Excellent punctuality!</span>' +
                '</div>'
            );
        } else {
            $('#sumLateActionHint').html('<span class="hint-active"><i class="fa fa-clock-o" style="color: #d97706;"></i> ' + lateList.length + ' ' + (lateList.length === 1 ? 'late day' : 'late days') + ' &bull; Click to view</span>');
            var lateHtml = '<div class="att-details-list">';
            for (var i = 0; i < lateList.length; i++) {
                var item = lateList[i];
                lateHtml += '<div class="att-detail-item item-late">' +
                    '<div class="att-detail-item-left">' +
                        '<span class="att-detail-date-badge">' +
                            '<i class="fa fa-calendar-check-o" style="color: #d97706;"></i> ' + item.dayLabel +
                        '</span>' +
                        (item.note ? '<span class="att-detail-note" title="' + escapeHtml(item.note) + '"><i class="fa fa-info-circle"></i> ' + escapeHtml(item.note) + '</span>' : '') +
                    '</div>' +
                    '<div class="att-detail-item-right">' +
                        '<div class="att-time-pill in-time-late">' +
                            '<span class="time-label"><i class="fa fa-sign-in"></i> Sign In</span>' +
                            '<span class="time-val">' + item.inTime + '</span>' +
                        '</div>' +
                        '<div class="att-time-pill out-time">' +
                            '<span class="time-label"><i class="fa fa-sign-out"></i> Sign Out</span>' +
                            '<span class="time-val">' + item.outTime + '</span>' +
                        '</div>' +
                        '<button type="button" class="btn att-btn-view-day" data-date="' + item.dateStr + '" title="View on calendar">' +
                            '<i class="fa fa-calendar"></i> View Day' +
                        '</button>' +
                    '</div>' +
                '</div>';
            }
            lateHtml += '</div>';
            $('#sumLateDetails').html(lateHtml);
        }

        // 2. Half Day Line / Card
        $('#sumHalfDayCount').text(halfDayList.length);
        if (halfDayList.length === 0) {
            $('#sumHalfDayActionHint').html('<span class="hint-muted">0 recorded</span>');
            $('#sumHalfDayDetails').html(
                '<div class="att-empty-state-box">' +
                    '<i class="fa fa-check-circle" style="color: #16a34a; font-size: 16px;"></i> ' +
                    '<span>No half day shifts marked for <strong>' + monthNames[month] + ' ' + year + '</strong>.</span>' +
                '</div>'
            );
        } else {
            $('#sumHalfDayActionHint').html('<span class="hint-active"><i class="fa fa-hourglass-half" style="color: #ea580c;"></i> ' + halfDayList.length + ' ' + (halfDayList.length === 1 ? 'half day' : 'half days') + ' &bull; Click to view</span>');
            var hdHtml = '<div class="att-details-list">';
            for (var i = 0; i < halfDayList.length; i++) {
                var item = halfDayList[i];
                hdHtml += '<div class="att-detail-item item-halfday">' +
                    '<div class="att-detail-item-left">' +
                        '<span class="att-detail-date-badge">' +
                            '<i class="fa fa-hourglass-half" style="color: #ea580c;"></i> ' + item.dayLabel +
                        '</span>' +
                        (item.note ? '<span class="att-detail-note" title="' + escapeHtml(item.note) + '"><i class="fa fa-info-circle"></i> ' + escapeHtml(item.note) + '</span>' : '') +
                    '</div>' +
                    '<div class="att-detail-item-right">' +
                        '<div class="att-time-pill in-time">' +
                            '<span class="time-label"><i class="fa fa-sign-in"></i> Sign In</span>' +
                            '<span class="time-val">' + item.inTime + '</span>' +
                        '</div>' +
                        '<div class="att-time-pill out-time">' +
                            '<span class="time-label"><i class="fa fa-sign-out"></i> Sign Out</span>' +
                            '<span class="time-val">' + item.outTime + '</span>' +
                        '</div>' +
                        '<button type="button" class="btn att-btn-view-day" data-date="' + item.dateStr + '" title="View on calendar">' +
                            '<i class="fa fa-calendar"></i> View Day' +
                        '</button>' +
                    '</div>' +
                '</div>';
            }
            hdHtml += '</div>';
            $('#sumHalfDayDetails').html(hdHtml);
        }

        // 3. Leave Line / Card
        $('#sumLeaveCount').text(leaveList.length);
        if (leaveList.length === 0) {
            $('#sumLeaveActionHint').html('<span class="hint-muted">0 recorded</span>');
            $('#sumLeaveDetails').html(
                '<div class="att-empty-state-box">' +
                    '<i class="fa fa-check-circle" style="color: #16a34a; font-size: 16px;"></i> ' +
                    '<span>No leaves taken in <strong>' + monthNames[month] + ' ' + year + '</strong>.</span>' +
                '</div>'
            );
        } else {
            $('#sumLeaveActionHint').html('<span class="hint-active"><i class="fa fa-calendar-minus-o" style="color: #7c3aed;"></i> ' + leaveList.length + ' ' + (leaveList.length === 1 ? 'leave' : 'leaves') + ' &bull; Click to view</span>');
            var leaveHtml = '<div class="att-details-list">';
            for (var i = 0; i < leaveList.length; i++) {
                var item = leaveList[i];
                leaveHtml += '<div class="att-detail-item item-leave">' +
                    '<div class="att-detail-item-left">' +
                        '<span class="att-detail-date-badge">' +
                            '<i class="fa fa-calendar-minus-o" style="color: #7c3aed;"></i> ' + item.dayLabel +
                        '</span>' +
                        (item.note ? '<span class="att-detail-note" title="' + escapeHtml(item.note) + '"><i class="fa fa-tag"></i> ' + escapeHtml(item.note) + '</span>' : '') +
                    '</div>' +
                    '<div class="att-detail-item-right">' +
                        '<button type="button" class="btn att-btn-view-day" data-date="' + item.dateStr + '" title="View on calendar">' +
                            '<i class="fa fa-calendar"></i> View Day' +
                        '</button>' +
                    '</div>' +
                '</div>';
            }
            leaveHtml += '</div>';
            $('#sumLeaveDetails').html(leaveHtml);
        }

        // 4. Present Line / Card
        $('#sumPresentCount').text(presentList.length + ' Days');
        if (presentList.length === 0) {
            $('#sumPresentActionHint').html('<span class="hint-muted">0 Days recorded</span>');
            $('#sumPresentDetails').html(
                '<div class="att-empty-state-box">' +
                    '<i class="fa fa-info-circle" style="color: #64748b; font-size: 16px;"></i> ' +
                    '<span>No present records marked yet for <strong>' + monthNames[month] + ' ' + year + '</strong>.</span>' +
                '</div>'
            );
        } else {
            $('#sumPresentActionHint').html('<span class="hint-active"><i class="fa fa-check-circle" style="color: #10b981;"></i> ' + presentList.length + ' Days marked &bull; Click to view</span>');
            var presHtml = '<div class="att-present-grid">';
            for (var i = 0; i < presentList.length; i++) {
                var item = presentList[i];
                presHtml += '<div class="att-present-card">' +
                    '<div class="pres-card-head">' +
                        '<span class="pres-date"><i class="fa fa-calendar-check-o" style="color: #10b981;"></i> ' + item.shortLabel + '</span>' +
                        '<button type="button" class="btn att-btn-view-day" data-date="' + item.dateStr + '" title="View on calendar"><i class="fa fa-calendar"></i></button>' +
                    '</div>' +
                    '<div class="pres-card-times">' +
                        '<span class="pres-time-in"><i class="fa fa-sign-in"></i> In: <strong>' + item.inTime + '</strong></span>' +
                        '<span class="pres-time-out"><i class="fa fa-sign-out"></i> Out: <strong>' + item.outTime + '</strong></span>' +
                    '</div>' +
                '</div>';
            }
            presHtml += '</div>';
            $('#sumPresentDetails').html(presHtml);
        }

        // 5. Working Days Line / Card
        $('#sumWorkingActionHint').html('Sundays (Off): <strong>' + totalSundays + '</strong> &bull; Total: <strong>' + daysInCurrentMonth + '</strong> days &bull; Click to view');
        var workingHtml = '<div class="att-working-breakdown-card">' +
            '<div class="att-wb-stats-row">' +
                '<div class="att-wb-stat-box">' +
                    '<span class="wb-num">' + daysInCurrentMonth + '</span>' +
                    '<span class="wb-lbl">Total Month Days</span>' +
                '</div>' +
                '<div class="att-wb-stat-box off-box">' +
                    '<span class="wb-num">' + totalSundays + '</span>' +
                    '<span class="wb-lbl">Sundays (Off)</span>' +
                '</div>' +
                '<div class="att-wb-stat-box net-box">' +
                    '<span class="wb-num">' + workingDays + '</span>' +
                    '<span class="wb-lbl">Net Working Days</span>' +
                '</div>' +
            '</div>';
        if (sundayList.length > 0) {
            workingHtml += '<div class="att-sundays-list-wrap">' +
                '<div class="sundays-title"><i class="fa fa-coffee" style="color: #c2410c;"></i> Weekly Off Days (Sundays) this month:</div>' +
                '<div class="sundays-chips">';
            for (var s = 0; s < sundayList.length; s++) {
                workingHtml += '<span class="sunday-chip att-btn-view-day" data-date="' + sundayList[s].dateStr + '" role="button" title="View in calendar modal"><i class="fa fa-calendar-o"></i> ' + sundayList[s].dayLabel + '</span>';
            }
            workingHtml += '</div></div>';
        }
        workingHtml += '</div>';
        $('#sumWorkingDetails').html(workingHtml);

        updateExpandAllBtnState();
    }

    function updateModalStatusBadge(dateStr) {
        var rec = window.AITF_ATTENDANCE_CACHE[dateStr] || null;
        if (rec) {
            if (rec.status === 'present') {
                $('#modalAttStatusBadge').html('<span class="att-pill-status att-pill-present" style="font-size: 12px; padding: 4px 10px;"><i class="fa fa-check"></i> Present</span>');
            } else if (rec.status === 'late') {
                $('#modalAttStatusBadge').html('<span class="att-pill-status att-pill-late" style="font-size: 12px; padding: 4px 10px;"><i class="fa fa-clock-o"></i> Late Login</span>');
            } else if (rec.status === 'halfday') {
                $('#modalAttStatusBadge').html('<span class="att-pill-status att-pill-halfday" style="font-size: 12px; padding: 4px 10px;"><i class="fa fa-hourglass-half"></i> Half Day</span>');
            } else if (rec.status === 'leave') {
                $('#modalAttStatusBadge').html('<span class="att-pill-status att-pill-leave" style="font-size: 12px; padding: 4px 10px;"><i class="fa fa-calendar-minus-o"></i> Leave</span>');
            } else if (rec.status === 'holiday') {
                $('#modalAttStatusBadge').html('<span class="att-pill-status att-pill-off" style="font-size: 12px; padding: 4px 10px;"><i class="fa fa-star"></i> Holiday</span>');
            } else {
                $('#modalAttStatusBadge').html('<span class="att-pill-status att-pill-empty" style="font-size: 12px; padding: 4px 10px;">' + escapeHtml(rec.label || 'Marked') + '</span>');
            }

            var inTimeVal = (rec.inTime && rec.inTime !== '--') ? rec.inTime : '--:--';
            var outTimeVal = (rec.outTime && rec.outTime !== '--') ? rec.outTime : '--:--';

            $('#modalPunchInVal').text(inTimeVal);
            $('#modalPunchOutVal').text(outTimeVal);
            $('#modalPunchNoteVal').text(rec.note || rec.label || '--');

            if (rec.workingHours && parseFloat(rec.workingHours) > 0) {
                $('#modalPunchHoursVal').text(rec.workingHours + ' hrs');
                $('#modalAttHoursRow').css('display', 'flex');
            } else {
                $('#modalAttHoursRow').hide();
            }

            $('#modalNoPunchBox').hide();

            // 1. Clock In Location (Punch In)
            if (rec.inAddress) {
                $('#modalInLocationBox').show();
                $('#modalPunchInLocationVal').text(rec.inAddress);
                $('#modalInTimeSmall').text(inTimeVal);
                if (rec.inLat && rec.inLng) {
                    $('#modalInMapLink').attr('href', 'https://www.google.com/maps?q=' + encodeURIComponent(rec.inLat + ',' + rec.inLng));
                    $('#modalInMapLinkWrap').show();
                } else {
                    $('#modalInMapLinkWrap').hide();
                }
            } else {
                $('#modalInLocationBox').hide();
            }

            // 2. Clock Out Location (Punch Out)
            if (rec.outAddress) {
                $('#modalOutLocationBox').show();
                $('#modalPunchOutLocationVal').text(rec.outAddress);
                $('#modalOutTimeSmall').text(outTimeVal);
                if (rec.outLat && rec.outLng) {
                    $('#modalOutMapLink').attr('href', 'https://www.google.com/maps?q=' + encodeURIComponent(rec.outLat + ',' + rec.outLng));
                    $('#modalOutMapLinkWrap').show();
                } else {
                    $('#modalOutMapLinkWrap').hide();
                }
                $('#modalOutPendingBox').hide();
            } else {
                $('#modalOutLocationBox').hide();
                // If user clocked in but punch out not done yet
                if (inTimeVal !== '--:--' && outTimeVal === '--:--') {
                    $('#modalOutPendingBox').css('display', 'flex');
                } else {
                    $('#modalOutPendingBox').hide();
                }
            }
        } else {
            $('#modalAttStatusBadge').html('<span class="att-pill-status att-pill-empty" style="font-size: 12px; padding: 4px 10px;"><i class="fa fa-circle-o"></i> Not Marked</span>');
            $('#modalPunchInVal').text('--:--');
            $('#modalPunchOutVal').text('--:--');
            $('#modalPunchNoteVal').text('--');
            $('#modalAttHoursRow').hide();
            $('#modalInLocationBox').hide();
            $('#modalOutLocationBox').hide();
            $('#modalOutPendingBox').hide();
            $('#modalNoPunchText').text('No punch activity recorded for this date.');
            $('#modalNoPunchBox').show();
        }
    }

    window.refreshAttendanceCalendar = function() {
        renderCalendar(viewedYear, viewedMonth);
    };

    // Initialize calendar on page load
    $(document).ready(function() {
        renderCalendar(viewedYear, viewedMonth);

        // Previous Month
        $('#attBtnPrev').on('click', function(e) {
            e.preventDefault();
            viewedMonth--;
            if (viewedMonth < 0) {
                viewedMonth = 11;
                viewedYear--;
            }
            renderCalendar(viewedYear, viewedMonth);
        });

        // Next Month
        $('#attBtnNext').on('click', function(e) {
            e.preventDefault();
            viewedMonth++;
            if (viewedMonth > 11) {
                viewedMonth = 0;
                viewedYear++;
            }
            renderCalendar(viewedYear, viewedMonth);
        });

        // Today
        $('#attBtnToday').on('click', function(e) {
            e.preventDefault();
            viewedYear = today.getFullYear();
            viewedMonth = today.getMonth();
            renderCalendar(viewedYear, viewedMonth);
        });

        // Jump selectors
        $('#attSelectMonth').on('change', function() {
            viewedMonth = parseInt($(this).val(), 10);
            renderCalendar(viewedYear, viewedMonth);
        });

        $('#attSelectYear').on('change', function() {
            viewedYear = parseInt($(this).val(), 10);
            renderCalendar(viewedYear, viewedMonth);
        });

        // Day cell click: Open Modal
        $(document).on('click', '.att-day-cell:not(.is-other-month)', function() {
            var dateStr = $(this).data('date');
            if (dateStr) {
                openDayModalByDate(dateStr);
            }
        });

        // Collapsible summary card header click
        $(document).on('click', '.att-card-header', function(e) {
            e.preventDefault();
            var $card = $(this).closest('.att-summary-item-line');
            var $body = $card.find('.att-card-body');
            if ($card.hasClass('is-open')) {
                $card.removeClass('is-open');
                $body.stop(true, true).slideUp(180, function() {
                    updateExpandAllBtnState();
                });
            } else {
                $card.addClass('is-open');
                $body.stop(true, true).slideDown(180, function() {
                    updateExpandAllBtnState();
                });
            }
        });

        // View Day button inside details click
        $(document).on('click', '.att-btn-view-day', function(e) {
            e.stopPropagation();
            var dateStr = $(this).data('date');
            if (dateStr) {
                openDayModalByDate(dateStr);
            }
        });

        // Toggle Expand/Collapse All summary cards
        $(document).on('click', '#attToggleAllCards', function(e) {
            e.preventDefault();
            var totalCards = $('.att-summary-item-line').length;
            var openCards = $('.att-summary-item-line.is-open').length;
            if (openCards === totalCards && totalCards > 0) {
                // Collapse all
                $('.att-summary-item-line').removeClass('is-open');
                $('.att-card-body').slideUp(180);
                $(this).html('<i class="fa fa-angle-double-down"></i> Expand All');
            } else {
                // Expand all
                $('.att-summary-item-line').addClass('is-open');
                $('.att-card-body').slideDown(180);
                $(this).html('<i class="fa fa-angle-double-up"></i> Collapse All');
            }
        });
    });
})();
</script>
