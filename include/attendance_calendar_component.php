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
    gap: 9px;
}

.att-summary-item-line {
    display: flex;
    align-items: center;
    padding: 11px 16px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13.5px;
    line-height: 1.5;
    gap: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
    transition: all 0.15s ease;
}

.att-summary-item-line:hover {
    background: #fbfcfe;
    border-color: #cbd5e1;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
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
    background: #fafafa;
}

.att-line-head {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    min-width: 175px;
    flex-shrink: 0;
}

.att-row-icon-wrap {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
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
    font-size: 13.5px;
}

.att-count-badge {
    font-size: 12px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 12px;
    display: inline-block;
    line-height: 1.4;
}
.badge-late { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.badge-halfday { background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }
.badge-leave { background: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
.badge-present { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.badge-working { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }

.att-line-details {
    color: #334155;
    flex: 1;
    min-width: 0;
}

.att-empty-hint {
    color: #94a3b8;
    font-style: italic;
    font-size: 13px;
}

.att-date-tag {
    display: inline-flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    margin-right: 14px;
    margin-bottom: 2px;
}

.att-date-pill {
    background: #f1f5f9;
    color: #0f172a;
    font-weight: 700;
    font-size: 12.5px;
    padding: 2px 8px;
    border-radius: 5px;
    border: 1px solid #e2e8f0;
}

.att-date-tag .tag-note {
    color: #475569;
    font-size: 13px;
}

.att-date-tag .tag-note strong {
    color: #0f172a;
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

    <!-- 4. Monthly Attendance Summary (Text-based, Clean, Eye-catching) -->
    <div class="att-text-summary-section">
        <div class="att-summary-header-row">
            <h4 class="att-summary-title-text">
                <i class="fa fa-file-text-o" style="color: #2563eb;"></i>
                Monthly Attendance Summary &mdash; <span id="attSummaryMonthTitle" style="color: #2563eb;">October 2026</span>
            </h4>
            <span class="att-summary-subtext">
                <i class="fa fa-info-circle" style="color: #6366f1;"></i> Click any date on the calendar above to mark status
            </span>
        </div>

        <div class="att-summary-content">
            <!-- Late Login Line -->
            <div class="att-summary-item-line line-late">
                <div class="att-line-head">
                    <span class="att-row-icon-wrap icon-late"><i class="fa fa-clock-o"></i></span>
                    <span class="att-line-label">Late Login</span>
                    <span class="att-count-badge badge-late" id="sumLateCount">0</span>
                </div>
                <div class="att-line-details" id="sumLateDetails">
                    <span class="att-empty-hint">None marked</span>
                </div>
            </div>

            <!-- Half Day Line -->
            <div class="att-summary-item-line line-halfday">
                <div class="att-line-head">
                    <span class="att-row-icon-wrap icon-halfday"><i class="fa fa-hourglass-half"></i></span>
                    <span class="att-line-label">Half Day</span>
                    <span class="att-count-badge badge-halfday" id="sumHalfDayCount">0</span>
                </div>
                <div class="att-line-details" id="sumHalfDayDetails">
                    <span class="att-empty-hint">None marked</span>
                </div>
            </div>

            <!-- Leave Line -->
            <div class="att-summary-item-line line-leave">
                <div class="att-line-head">
                    <span class="att-row-icon-wrap icon-leave"><i class="fa fa-calendar-minus-o"></i></span>
                    <span class="att-line-label">Leave</span>
                    <span class="att-count-badge badge-leave" id="sumLeaveCount">0</span>
                </div>
                <div class="att-line-details" id="sumLeaveDetails">
                    <span class="att-empty-hint">None marked</span>
                </div>
            </div>

            <!-- Present Line -->
            <div class="att-summary-item-line line-present">
                <div class="att-line-head">
                    <span class="att-row-icon-wrap icon-present"><i class="fa fa-check-circle"></i></span>
                    <span class="att-line-label">Present</span>
                    <span class="att-count-badge badge-present" id="sumPresentCount">0 Days</span>
                </div>
                <div class="att-line-details" id="sumPresentDetails">
                    <span class="att-empty-hint">0 Days recorded</span>
                </div>
            </div>

            <!-- Total Working Days Line -->
            <div class="att-summary-item-line line-working">
                <div class="att-line-head">
                    <span class="att-row-icon-wrap icon-working"><i class="fa fa-briefcase"></i></span>
                    <span class="att-line-label">Working Days</span>
                    <span class="att-count-badge badge-working" id="sumWorkingDays">27</span>
                </div>
                <div class="att-line-details">
                    <span style="font-size: 13px; color: #475569;">
                        Sundays (Off): <strong style="color: #0f172a;" id="sumSundayCount">4</strong> days &bull; Total Month Days: <strong style="color: #0f172a;" id="sumTotalMonthDays">31</strong> days
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- 5. Day Details & Interactive Marking Modal -->
<div class="modal fade" id="attDayDetailModal" tabindex="-1" role="dialog" aria-labelledby="attDayDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width: 480px; margin-top: 60px;">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: 1px solid #cbd5e1; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
            <div class="modal-header" style="background: linear-gradient(135deg, #0d4d8b 0%, #2563eb 100%); color: #ffffff; padding: 16px 20px;">
                <h5 class="modal-title" id="attDayDetailModalLabel" style="font-weight: 700; font-size: 16px; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-calendar-check-o"></i> Attendance Details & Action
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
                            <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Punch In</div>
                            <div id="modalPunchInVal" style="font-size: 14.5px; font-weight: 700; color: #0f172a; margin-top: 2px;">--:--</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Punch Out</div>
                            <div id="modalPunchOutVal" style="font-size: 14.5px; font-weight: 700; color: #0f172a; margin-top: 2px;">--:--</div>
                        </div>
                    </div>
                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e2e8f0; display: flex; justify-content: space-between; font-size: 12px;">
                        <span style="color: #64748b;">Note / Shift Status:</span>
                        <strong id="modalPunchNoteVal" style="color: #0f172a;">--</strong>
                    </div>
                </div>

                <!-- Interactive Mark Attendance Controls -->
                <div class="att-mark-actions-box" id="modalMarkActionsBox" style="margin-bottom: 16px;">
                    <label style="font-size: 11.5px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 8px; display: block; letter-spacing: 0.3px;">
                        <i class="fa fa-pencil-square-o" style="color: #2563eb;"></i> Mark / Change Status for this Date:
                    </label>
                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                        <button type="button" class="btn btn-sm btn-mark-att" data-mark="present" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-weight: 600; border-radius: 6px; padding: 5px 11px; font-size: 12px;">
                            <i class="fa fa-check"></i> Present
                        </button>
                        <button type="button" class="btn btn-sm btn-mark-att" data-mark="late" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-weight: 600; border-radius: 6px; padding: 5px 11px; font-size: 12px;">
                            <i class="fa fa-clock-o"></i> Late Login
                        </button>
                        <button type="button" class="btn btn-sm btn-mark-att" data-mark="halfday" style="background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; font-weight: 600; border-radius: 6px; padding: 5px 11px; font-size: 12px;">
                            <i class="fa fa-hourglass-half"></i> Half Day
                        </button>
                        <button type="button" class="btn btn-sm btn-mark-att" data-mark="leave" style="background: #faf5ff; color: #6b21a8; border: 1px solid #e9d5ff; font-weight: 600; border-radius: 6px; padding: 5px 11px; font-size: 12px;">
                            <i class="fa fa-calendar-minus-o"></i> Leave
                        </button>
                        <button type="button" class="btn btn-sm btn-mark-att" data-mark="clear" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; font-weight: 600; border-radius: 6px; padding: 5px 11px; font-size: 12px;">
                            <i class="fa fa-eraser"></i> Clear
                        </button>
                    </div>
                    <div id="markFeedbackMsg" style="display: none; margin-top: 8px; font-size: 12px; font-weight: 600; color: #16a34a;">
                        <i class="fa fa-check-circle"></i> Status marked successfully! Summary updated.
                    </div>
                </div>

                <!-- Info Notice -->
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px; font-size: 12px; color: #1e40af; line-height: 1.5; display: flex; gap: 10px;">
                    <i class="fa fa-info-circle" style="font-size: 15px; margin-top: 2px; color: #2563eb; flex-shrink: 0;"></i>
                    <div>
                        <strong>Real-time Sync Ready:</strong> Status marks will automatically reflect in the calendar days and in the monthly summary below.
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
                SELECT attendance_date, status, in_time, out_time, remarks, punch_in_selfie_url 
                FROM aitf_attendance 
                WHERE user_id = :uid 
                  AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL 120 DAY)
                ORDER BY attendance_date ASC
            ");
            $attStmt->execute([':uid' => $targetAttendanceUid]);
            $rawRecords = $attStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rawRecords as $r) {
                $d = $r['attendance_date'];
                $dbAttendanceMap[$d] = [
                    'status' => $r['status'],
                    'label' => ($r['status'] === 'late') ? 'Late Login' : (($r['status'] === 'halfday') ? 'Half Day' : ucfirst($r['status'])),
                    'inTime' => !empty($r['in_time']) ? date('h:i A', strtotime($r['in_time'])) : '--',
                    'outTime' => !empty($r['out_time']) ? date('h:i A', strtotime($r['out_time'])) : '--',
                    'note' => $r['remarks'] ?? '',
                    'selfieUrl' => $r['punch_in_selfie_url'] ?? ''
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

    // Update the Text-Based Summary Section below Calendar
    function updateMonthlySummary(year, month) {
        var daysInCurrentMonth = new Date(year, month + 1, 0).getDate();
        var lateList = [];
        var halfDayList = [];
        var leaveList = [];
        var presentList = [];
        var totalSundays = 0;

        for (var d = 1; d <= daysInCurrentMonth; d++) {
            var dateObj = new Date(year, month, d);
            var isSun = (dateObj.getDay() === 0);
            if (isSun) totalSundays++;

            var dateStr = year + '-' + padZero(month + 1) + '-' + padZero(d);
            var rec = window.AITF_ATTENDANCE_CACHE[dateStr];
            if (rec) {
                var dayLabel = dayNames[dateObj.getDay()].substring(0, 3) + ', ' + d + ' ' + monthNames[month].substring(0, 3);
                if (rec.status === 'late') {
                    lateList.push({ dayLabel: dayLabel, inTime: rec.inTime, outTime: rec.outTime, note: rec.note || 'Late login' });
                } else if (rec.status === 'halfday') {
                    halfDayList.push({ dayLabel: dayLabel, inTime: rec.inTime, outTime: rec.outTime, note: rec.note || 'Half day shift' });
                } else if (rec.status === 'leave') {
                    leaveList.push({ dayLabel: dayLabel, note: rec.note || 'Casual Leave' });
                } else if (rec.status === 'present') {
                    presentList.push({ dayLabel: dayLabel, inTime: rec.inTime, outTime: rec.outTime });
                }
            }
        }

        var workingDays = daysInCurrentMonth - totalSundays;

        $('#attSummaryMonthTitle').text(monthNames[month] + ' ' + year);
        $('#sumTotalMonthDays').text(daysInCurrentMonth);
        $('#sumWorkingDays').text(workingDays);
        $('#sumSundayCount').text(totalSundays);

        // 1. Late Login Text Line
        $('#sumLateCount').text(lateList.length);
        if (lateList.length === 0) {
            $('#sumLateDetails').html('<span class="att-empty-hint">None marked this month</span>');
        } else {
            var lateHtml = '';
            for (var i = 0; i < lateList.length; i++) {
                var item = lateList[i];
                lateHtml += '<span class="att-date-tag"><span class="att-date-pill">' + item.dayLabel + '</span><span class="tag-note">(In: <strong>' + item.inTime + '</strong> | Out: <strong>' + item.outTime + '</strong> &mdash; ' + item.note + ')</span></span>';
            }
            $('#sumLateDetails').html(lateHtml);
        }

        // 2. Half Day Text Line
        $('#sumHalfDayCount').text(halfDayList.length);
        if (halfDayList.length === 0) {
            $('#sumHalfDayDetails').html('<span class="att-empty-hint">None marked this month</span>');
        } else {
            var hdHtml = '';
            for (var i = 0; i < halfDayList.length; i++) {
                var item = halfDayList[i];
                hdHtml += '<span class="att-date-tag"><span class="att-date-pill">' + item.dayLabel + '</span><span class="tag-note">(In: <strong>' + item.inTime + '</strong> | Out: <strong>' + item.outTime + '</strong> &mdash; ' + item.note + ')</span></span>';
            }
            $('#sumHalfDayDetails').html(hdHtml);
        }

        // 3. Leave Text Line
        $('#sumLeaveCount').text(leaveList.length);
        if (leaveList.length === 0) {
            $('#sumLeaveDetails').html('<span class="att-empty-hint">None marked this month</span>');
        } else {
            var leaveHtml = '';
            for (var i = 0; i < leaveList.length; i++) {
                var item = leaveList[i];
                leaveHtml += '<span class="att-date-tag"><span class="att-date-pill">' + item.dayLabel + '</span><span class="tag-note">(' + item.note + ')</span></span>';
            }
            $('#sumLeaveDetails').html(leaveHtml);
        }

        // 4. Present Text Line
        $('#sumPresentCount').text(presentList.length + ' Days');
        if (presentList.length === 0) {
            $('#sumPresentDetails').html('<span class="att-empty-hint">No present records marked yet</span>');
        } else {
            var presHtml = '';
            for (var i = 0; i < presentList.length; i++) {
                presHtml += '<span class="att-date-tag"><span class="att-date-pill">' + presentList[i].dayLabel + '</span></span>';
            }
            $('#sumPresentDetails').html(presHtml);
        }
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
            }
            $('#modalPunchInVal').text(rec.inTime || '--:--');
            $('#modalPunchOutVal').text(rec.outTime || '--:--');
            $('#modalPunchNoteVal').text(rec.note || rec.label);
        } else {
            $('#modalAttStatusBadge').html('<span class="att-pill-status att-pill-empty" style="font-size: 12px; padding: 4px 10px;"><i class="fa fa-circle-o"></i> Not Marked</span>');
            $('#modalPunchInVal').text('--:--');
            $('#modalPunchOutVal').text('--:--');
            $('#modalPunchNoteVal').text('--');
        }
    }

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
            var dayNum = $(this).data('day');
            var dayName = $(this).data('day-name');
            var isSun = $(this).data('is-sun') === 1 || $(this).data('is-sun') === '1';

            if (!dateStr) return;

            $('#attDayDetailModal').data('active-date', dateStr);

            var dParts = dateStr.split('-');
            var y = parseInt(dParts[0], 10);
            var m = parseInt(dParts[1], 10) - 1;
            var d = parseInt(dParts[2], 10);

            var formattedDate = dayName + ', ' + d + ' ' + monthNames[m] + ' ' + y;
            $('#modalAttFullDate').text(formattedDate);

            if (isSun) {
                $('#modalAttDayType').text('Weekend (Sunday - Official Off)');
                $('#modalAttStatusBadge').html('<span class="att-pill-status att-pill-off" style="font-size: 12px; padding: 4px 10px;"><i class="fa fa-coffee"></i> Weekend / Off</span>');
                $('#modalPunchInVal').text('--');
                $('#modalPunchOutVal').text('--');
                $('#modalPunchNoteVal').text('Weekly Off (Sunday)');
                $('#modalMarkActionsBox').hide();
            } else {
                $('#modalAttDayType').text('Standard Working Day');
                $('#modalMarkActionsBox').show();
                updateModalStatusBadge(dateStr);
            }

            $('#markFeedbackMsg').hide();
            $('#attDayDetailModal').modal('show');
        });

        // Mark Attendance Action (Late Login, Half Day, Leave, Present, Clear)
        $(document).on('click', '.btn-mark-att', function(e) {
            e.preventDefault();
            var markType = $(this).data('mark');
            var activeDateStr = $('#attDayDetailModal').data('active-date');
            if (!activeDateStr) return;

            if (markType === 'clear') {
                delete window.AITF_ATTENDANCE_CACHE[activeDateStr];
            } else {
                var label = 'Present';
                var inTime = '09:30 AM';
                var outTime = '06:30 PM';
                var note = 'Full day attendance';

                if (markType === 'late') {
                    label = 'Late Login';
                    inTime = '10:15 AM';
                    outTime = '06:30 PM';
                    note = '15 mins late login';
                } else if (markType === 'halfday') {
                    label = 'Half Day';
                    inTime = '09:30 AM';
                    outTime = '02:00 PM';
                    note = 'First half shift';
                } else if (markType === 'leave') {
                    label = 'Leave';
                    inTime = '--';
                    outTime = '--';
                    note = 'Casual / Sick Leave';
                }

                window.AITF_ATTENDANCE_CACHE[activeDateStr] = {
                    status: markType,
                    label: label,
                    inTime: inTime,
                    outTime: outTime,
                    note: note
                };
            }

            // Re-render calendar and update summary
            renderCalendar(viewedYear, viewedMonth);

            // Update modal state
            updateModalStatusBadge(activeDateStr);

            $('#markFeedbackMsg').stop(true, true).fadeIn().delay(1400).fadeOut();
        });
    });
})();
</script>
