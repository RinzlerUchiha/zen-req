<?php
/**
 * Manpower Render Helpers
 *
 * File: manpower/includes/manpower_render_helpers.php
 *
 * Purpose: Shared rendering functions used by both dashboard.php (full
 * page load) and manpower_status_rows.php (AJAX sub-tab loads), so both
 * files use the exact same row/badge markup instead of duplicating it.
 */

if (!function_exists('mp_status_badge')) {
    function mp_status_badge($status)
    {
        $map = [
            'Draft'     => ['#EEF0F3', '#5B6474'],
            'Pending'   => ['#2F6FE4', '#FFFFFF'],
            'Returned'  => ['#E8F0FE', '#1B4FB0'],
            'Rejected'  => ['#FCEBEB', '#791F1F'],
            'Approved'  => ['#E7F6EC', '#1E7A34'],
            'Cancelled' => ['#EEF0F3', '#5B6474'],
        ];
        [$bg, $fg] = $map[$status] ?? ['#EEF0F3', '#5B6474'];
        return '<span class="mp-chip" style="background:' . $bg . ';color:' . $fg . ';">'
            . htmlspecialchars($status) . '</span>';
    }
}

if (!function_exists('mp_change_type_badge')) {
    function mp_change_type_badge($type)
    {
        $map = [
            'edit'   => ['#E8F0FE', '#1B4FB0'],
            'cancel' => ['#FCEBEB', '#791F1F'],
        ];
        [$bg, $fg] = $map[$type] ?? ['#EEF0F3', '#5B6474'];
        $label = $type === 'edit' ? 'Edit Requested' : 'Cancel Requested';
        return '<span class="mp-chip" style="background:' . $bg . ';color:' . $fg . ';">'
            . htmlspecialchars($label) . '</span>';
    }
}

if (!function_exists('mp_render_change_rows')) {
    function mp_render_change_rows($rows, $emptyMsg)
    {
        if (empty($rows)) {
            echo '<div class="mp-empty"><span>' . htmlspecialchars($emptyMsg) . '</span></div>';
            return;
        }
        $i = 1;
        foreach ($rows as $r) {
            echo '<div class="mp-row" onclick="mpOpenChangeRequestModal(' . (int) $r['id'] . ')">';
            echo   '<div class="mp-row-num">' . $i++ . '</div>';
            echo   '<div class="mp-row-body">';
            echo     '<div class="mp-row-top"><span class="mp-row-title">' . htmlspecialchars($r['position_list'] ?: $r['mr_no']) . '</span></div>';
            echo     '<div class="mp-row-cols">';
            echo       '<div class="mp-col"><span class="mp-col-label">DEPARTMENT</span><span class="mp-col-value">' . htmlspecialchars($r['department_id']) . '</span></div>';
            echo       '<div class="mp-col"><span class="mp-col-label">REQUESTED</span><span class="mp-col-value">' . date("M d, Y", strtotime($r['created_at'])) . '</span></div>';
            echo       '<div class="mp-col mp-col-status">' . mp_change_type_badge($r['change_type']) . '</div>';
            echo     '</div>';
            echo   '</div>';
            echo '</div>';
        }
    }
}

if (!function_exists('mp_render_rows')) {
    function mp_render_rows($rows, $emptyMsg)
    {
        if (empty($rows)) {
            echo '<div class="mp-empty">';
            echo '<svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11H5a2 2 0 00-2 2v7a1 1 0 001 1h16a1 1 0 001-1v-7a2 2 0 00-2-2h-4M9 11V4a1 1 0 011-1h4a1 1 0 011 1v7M9 11h6"/></svg>';
            echo '<span>' . htmlspecialchars($emptyMsg) . '</span>';
            echo '</div>';
            return;
        }
        $i = 1;
        foreach ($rows as $r) {
            $mpRowClick = ($r['status'] === 'Returned' && !userHasRoleIn('Approver', 'HR Head', 'Admin'))
                ? "window.location='request?id=" . (int) $r['id'] . "'"
                : "mpOpenRequestModal(" . (int) $r['id'] . ")";
                $searchBlob = strtolower(($r['position_list'] ?? '') . ' ' . $r['department_id'] . ' ' . date("M d, Y", strtotime($r['created_at'])) . ' ' . $r['status']);
            echo '<div class="mp-row" data-status="' . htmlspecialchars($r['status']) . '" data-search="' . htmlspecialchars($searchBlob) . '" onclick="' . $mpRowClick . '">';
            echo   '<div class="mp-row-num">' . $i++ . '</div>';
            $positionDisplay = $r['position_list'] !== null && $r['position_list'] !== ''
                ? $r['position_list']
                : 'No positions added yet';
            echo   '<div class="mp-row-body">';
            echo     '<div class="mp-row-top">';
            echo       '<span class="mp-row-title">' . htmlspecialchars($positionDisplay) . '</span>';
            echo     '</div>';
            echo     '<div class="mp-row-cols">';
            echo       '<div class="mp-col"><span class="mp-col-label">DEPARTMENT</span><span class="mp-col-value">' . htmlspecialchars($r['department_id']) . '</span></div>';
            echo       '<div class="mp-col"><span class="mp-col-label">HEADCOUNT</span><span class="mp-col-value">' . htmlspecialchars($r['total_headcount']) . '</span></div>';
            echo       '<div class="mp-col"><span class="mp-col-label">POSITIONS</span><span class="mp-col-value">' . htmlspecialchars($r['position_count']) . '</span></div>';
            echo       '<div class="mp-col"><span class="mp-col-label">SUBMITTED</span><span class="mp-col-value">' . date("M d, Y", strtotime($r['created_at'])) . '</span></div>';
            echo       '<div class="mp-col mp-col-status">';
            if ($r['status'] === 'Returned' && !empty($r['update_pending_review'])) {
                echo mp_status_badge('Update') . ' <span class="mp-chip" style="background:#E8F0FE;color:#1B4FB0;">Revised</span>';
            } else {
                echo mp_status_badge($r['status']);
            }
            echo       '</div>';
            echo     '</div>';
            echo   '</div>';
            echo '</div>';
        }
    }
}