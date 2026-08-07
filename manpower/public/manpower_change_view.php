<?php
/**
 * Manpower Change Request View (modal fragment)
 *
 * File: manpower/public/manpower_change_view.php
 *
 * Purpose: Returns a change-request's detail (original request summary +
 * requestor's reason) plus Approve/Decline actions, loaded via AJAX into
 * a modal from dashboard.php.
 */

if (!isset($currentUser)) {
    require_once dirname(__DIR__) . '/includes/auth.php';
}

requireRoleIn('Approver', 'HR Head', 'Admin');

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : null;

if (!$id) {
    echo '<div class="alert alert-danger">No change request specified.</div>';
    return;
}

$stmt = $hr_db->prepare("SELECT c.*, r.department_id, r.mr_no, r.requestor_employee_id,
        CONCAT(a.bi_empfname, ' ', a.bi_emplname) AS requestor_name,
        d.Dept_Name,
        (SELECT GROUP_CONCAT(p.position SEPARATOR ', ') FROM tbl_manpower_request_position p WHERE p.request_id = r.id) AS position_list
    FROM tbl_manpower_change_request c
    JOIN tbl_manpower_request r ON r.id = c.request_id
    LEFT JOIN tbl201_basicinfo a ON a.bi_empno = r.requestor_employee_id AND a.datastat = 'current'
    LEFT JOIN tbl_department d ON d.Dept_Code = r.department_id
    WHERE c.id = :id LIMIT 1");
$stmt->bindParam(':id', $id);
$stmt->execute();
$cr = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cr) {
    echo '<div class="alert alert-danger">Change request not found.</div>';
    return;
}

$canAct = false;
if ($cr['status'] === 'Pending') {
    $userRole = $currentUser['manpower_role'];
    if ($userRole === 'Admin') {
        $canAct = true;
    } elseif ($userRole === 'Approver') {
        $canAct = ($currentUser['manpower_department_id'] === $cr['department_id']);
    }
}
?>
<style>
    #mpv-fragment {
        --mpv-blue: #2F6FE4;
        --mpv-blue-dark: #1B4FB0;
        --mpv-purple: #6A4FE0;
        --mpv-text: #1F2430;
        --mpv-muted: #8A93A3;
        --mpv-border: #E7E9EE;
        --mpv-bg-input: #F4F5F8;
        font-size: 14px;
    }

    #mpv-fragment,
    #mpv-fragment * {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    #mpv-fragment .mpv-chip {
        display: inline-block;
        border-radius: 6px;
        padding: 3px 11px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .2px;
    }

    #mpv-fragment .mpv-field-label {
        font-size: 11.5px;
        font-weight: 600;
        color: var(--mpv-muted) !important;
        background: none !important;
        letter-spacing: .3px;
        display: block;
        margin: 26px 0 8px;
    }

    #mpv-fragment .mpv-field-value {
        font-size: 13.5px;
        color: var(--mpv-text);
        white-space: pre-line;
    }

    #mpv-fragment .mpv-empty-muted {
        font-size: 12px;
        color: var(--mpv-muted);
    }

    #mpv-fragment .mpv-actions {
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid var(--mpv-border);
    }

    #mpv-fragment .mpv-actions textarea {
        width: 100%;
        border: 1px solid var(--mpv-border);
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 13px;
        margin-bottom: 10px;
    }

    #mpv-fragment .mpv-actions textarea:focus {
        outline: none;
        border-color: var(--mpv-blue);
        box-shadow: 0 0 0 2px #E8F0FE;
    }

    #mpv-fragment .mpv-btn-row {
        display: flex;
        gap: 8px;
    }

    #mpv-fragment .mpv-btn {
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
    }

    #mpv-fragment .mpv-btn-approve {
        background: #1E7A34;
        color: #fff;
    }

    #mpv-fragment .mpv-btn-reject {
        background: #E14848;
        color: #fff;
    }

    #mpv-fragment .mpv-btn-edit {
        background: #fff;
        color: var(--mpv-blue);
        border: 1px solid var(--mpv-blue);
        display: inline-block;
        text-decoration: none;
    }

    #mpv-fragment .mpv-btn-edit:hover {
        text-decoration: none;
        background: #F5F8FF;
    }
</style>
<div id="mpv-fragment">
    <div class="row" style="margin-left:0; margin-right:0;">
        <div class="col-sm-12">
            <table style="width:100%; margin-bottom:6px;">
            <tr>
                    <td style="width:140px; font-size:12.5px; color:var(--mpv-muted); padding:3px 0;">Request</td>
                    <td style="font-size:13px; color:var(--mpv-text); font-weight:600;">
                        <?= htmlspecialchars($cr['position_list'] ?: 'No positions') ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-size:12.5px; color:var(--mpv-muted); padding:3px 0;">Requestor</td>
                    <td style="font-size:13px; color:var(--mpv-text); font-weight:600;"><?= htmlspecialchars($cr['requestor_name'] ?? $cr['requestor_employee_id']) ?></td>
                </tr>
                <tr>
                    <td style="font-size:12.5px; color:var(--mpv-muted); padding:3px 0;">Department</td>
                    <td style="font-size:13px; color:var(--mpv-text); font-weight:600;"><?= htmlspecialchars($cr['Dept_Name'] ?? $cr['department_id']) ?></td>
                </tr>
                <tr>
                    <td style="font-size:12.5px; color:var(--mpv-muted); padding:3px 0;">Requested action</td>
                    <td style="font-size:13px; color:var(--mpv-text); font-weight:600;">
                        <?= $cr['change_type'] === 'edit' ? 'Edit this request' : 'Cancel this request' ?>
                        <span style="margin-left:10px;"><?php
                            $crStatusMap = [
                                'Pending'  => ['#2F6FE4', '#FFFFFF'],
                                'Approved' => ['#E7F6EC', '#1E7A34'],
                                'Declined' => ['#FCEBEB', '#791F1F'],
                            ];
                            [$crBg, $crFg] = $crStatusMap[$cr['status']] ?? ['#EEF0F3', '#5B6474'];
                            echo '<span class="mpv-chip" style="background:' . $crBg . ';color:' . $crFg . ';">' . htmlspecialchars($cr['status']) . '</span>';
                        ?></span>
                    </td>
                </tr>
            </table>

            <span class="mpv-field-label">REASON</span>
            <div class="mpv-field-value"><?= $cr['reason'] !== null && $cr['reason'] !== '' ? nl2br(htmlspecialchars($cr['reason'])) : '<span class="mpv-empty-muted">No reason provided.</span>' ?></div>

            <div style="margin-top:18px;">
                <a href="view?id=<?= (int) $cr['request_id'] ?>" class="mpv-btn mpv-btn-edit" onclick="mpOpenRequestModal(<?= (int) $cr['request_id'] ?>, <?= (int) $cr['id'] ?>); return false;">View Original Request</a>
            </div>

            <?php if ($canAct) { ?>
                <div class="mpv-actions">
                    <span class="mpv-field-label" style="margin-top:0;">YOUR DECISION</span>
                    <textarea id="mp-cr-remarks" rows="3" placeholder="Remarks (required for Decline)"></textarea>
                    <div class="mpv-btn-row">
                        <button type="button" class="mpv-btn mpv-btn-approve" onclick="mpDecideChangeRequest('approve', <?= (int) $cr['id'] ?>)">Approve</button>
                        <button type="button" class="mpv-btn mpv-btn-reject" onclick="mpDecideChangeRequest('decline', <?= (int) $cr['id'] ?>)">Decline</button>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>