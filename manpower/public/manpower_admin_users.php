<?php
/**
 * Manpower Admin — User Role Management
 *
 * File: manpower/public/manpower_admin_users.php
 *
 * Purpose: Lets Admins grant/change manpower_role, department scope, and
 * active status for any employee who has an entry in tbl_manpower_users
 * (rows are auto-created on first visit via auth.php's provisioning step).
 */

if (!isset($currentUser)) {
    require_once dirname(__DIR__) . '/includes/auth.php';
}

requireRoleIn('Admin');

require_once dirname(__DIR__) . '/includes/header.php';

// All manpower users, joined to HR basic info for display name + current dept
$stmt = $hr_db->query("
    SELECT
        mu.id,
        mu.employee_id,
        mu.manpower_role,
        mu.department_id,
        mu.is_active,
        mu.is_admin_dev,
        CONCAT(bi.bi_empfname, ' ', bi.bi_emplname) AS fullname,
        jr.jrec_department AS hr_department
    FROM tbl_manpower_users mu
    LEFT JOIN tbl201_basicinfo bi ON bi.bi_empno = mu.employee_id AND bi.datastat = 'current'
    LEFT JOIN tbl201_jobrec jr ON jr.jrec_empno = mu.employee_id AND jr.jrec_status = 'Primary'
    ORDER BY mu.manpower_role = 'No Access' DESC, fullname ASC
");
$manpowerUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

const MP_ROLE_OPTIONS = ['No Access', 'Requestor', 'Approver', 'HR Head', 'Admin'];

// Mirror dashboard.php's sidebar sections so nav stays consistent here too.
$isHrHeadOnly = userHasRoleIn('HR Head') && !userHasRoleIn('Approver', 'Admin');
$mpSections = [];
if ($isHrHeadOnly) {
    $mpSections['contract-offers'] = ['label' => 'Contract offers', 'icon' => 'handshake-o'];
} else {
    if (!userHasRoleIn('Approver', 'Admin')) {
        $mpSections['my-requests'] = ['label' => 'My requests', 'icon' => 'file-text'];
    }
    if (userHasRoleIn('Approver', 'HR Head')) {
        $mpSections['all-requests'] = ['label' => 'All requests', 'icon' => 'list'];
        $mpSections['for-approval'] = ['label' => 'For my approval', 'icon' => 'check-circle'];
        $mpSections['change-requests'] = ['label' => 'Requests to edit/cancel', 'icon' => 'pencil-square-o'];
    } elseif (userHasRoleIn('Admin')) {
        $mpSections['all-requests'] = ['label' => 'All requests', 'icon' => 'list'];
    }
    $mpSections['job-spec'] = ['label' => 'Job specification', 'icon' => 'briefcase'];
}
if (userHasRoleIn('Admin')) {
    $mpSections['admin-users'] = ['label' => 'User access', 'icon' => 'users'];
}
?>
<style>
    .mp-wrap {
        background: #F5F6F9;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        display: flex;
        align-items: stretch;
        min-height: calc(100vh - 60px);
        margin: -28px -24px -40px;
    }
    @media (max-width: 768px) {
        .mp-wrap { margin: -18px -16px -32px; }
    }
    main { max-width: none !important; margin: 0 !important; }
    .mp-wrap * { font-family: inherit; }
    .mp-sidebar {
        width: 220px;
        flex: 0 0 auto;
        background: #FFFFFF;
        border-right: 1px solid #E7E9EE;
        padding: 20px 12px;
    }
    .mp-nav-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #5B6474;
        cursor: pointer;
        margin-bottom: 2px;
        text-decoration: none;
    }
    .mp-nav-item:hover { background: #F5F6F9; }
    .mp-nav-item.active { background: #E8F0FE; color: #1B4FB0; }
    .mp-nav-icon { width: 18px; text-align: center; flex: 0 0 auto; }
    .mp-main { flex: 1; min-width: 0; padding: 32px 40px; }
    @media (max-width: 900px) {
        .mp-wrap { flex-direction: column; }
        .mp-sidebar {
            width: 100%;
            display: flex;
            overflow-x: auto;
            border-right: none;
            border-bottom: 1px solid #E7E9EE;
        }
        .mp-nav-item { white-space: nowrap; }
    }
    #mp-admin-app {
        --mp-page-bg: #F5F6F9;
        --mp-border: #E7E9EE;
        --mp-text: #1F2430;
        --mp-text-muted: #8A93A3;
        --mp-accent: #2F6FE4;
        color: var(--mp-text);
        font-size: 13.5px;
    }
    #mp-admin-app, #mp-admin-app input, #mp-admin-app select, #mp-admin-app button {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        font-size: 13px;
    }
    #mp-admin-app .mp-admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
    }
    #mp-admin-app .mp-admin-header h4 {
        margin: 0;
        font-weight: 800;
        letter-spacing: -.3px;
    }
    #mp-admin-app table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border: 1px solid var(--mp-border);
        border-radius: 12px;
        overflow: hidden;
    }
    #mp-admin-app th {
        text-align: left;
        font-size: 10.5px;
        text-transform: uppercase;
        letter-spacing: .3px;
        color: var(--mp-text-muted);
        background: #FAFBFC;
        padding: 10px 14px;
        border-bottom: 1px solid var(--mp-border);
    }
    #mp-admin-app td {
        padding: 8px 14px;
        border-bottom: 1px solid #F1F2F5;
        vertical-align: middle;
    }
    #mp-admin-app tr:last-child td { border-bottom: none; }
    #mp-admin-app select {
        border: 1px solid var(--mp-border);
        border-radius: 6px;
        padding: 5px 8px;
        font-size: 12.5px;
        min-width: 110px;
    }
    #mp-admin-app .mp-admin-save-btn {
        background: var(--mp-accent);
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 5px 12px;
        font-size: 11.5px;
        font-weight: 600;
        cursor: pointer;
    }
    #mp-admin-app .mp-admin-save-btn:disabled { opacity: .5; cursor: default; }
    #mp-admin-app .mp-admin-status {
        font-size: 11px;
        margin-left: 8px;
        color: #1E9E4C;
    }
    #mp-admin-app .mp-admin-status.error { color: #E14848; }
    #mp-admin-app .mp-role-badge {
        display: inline-block;
        font-size: 10.5px;
        font-weight: 700;
        padding: 2px 9px;
        border-radius: 20px;
        background: #F1F2F5;
        color: var(--mp-text-muted);
    }
    #mp-admin-app .mp-role-badge.role-noaccess { background: #FCEBEB; color: #E14848; }
    #mp-admin-app .mp-role-badge.role-admin { background: #F1EEFE; color: #6A4FE0; }
    #mp-admin-app input[type="checkbox"] { width: 16px; height: 16px; }
</style>

<div class="mp-wrap">
<div class="mp-sidebar">
    <?php foreach ($mpSections as $key => $section): ?>
        <?php if ($key === 'admin-users'): ?>
            <div class="mp-nav-item active">
                <span class="mp-nav-icon"><i class="fa fa-<?= $section['icon'] ?>"></i></span>
                <span><?= htmlspecialchars($section['label']) ?></span>
            </div>
        <?php else: ?>
            <a class="mp-nav-item" href="dashboard#<?= $key ?>">
                <span class="mp-nav-icon"><i class="fa fa-<?= $section['icon'] ?>"></i></span>
                <span><?= htmlspecialchars($section['label']) ?></span>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<div class="mp-main">
<div id="mp-admin-app">
    <div class="mp-admin-header">
        <h4>Manpower — User Access</h4>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Emp No</th>
                <th>HR Department</th>
                <th>Manpower Role</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($manpowerUsers as $u): ?>
                <tr data-id="<?= (int) $u['id'] ?>" data-empno="<?= htmlspecialchars($u['employee_id']) ?>">
                    <td><?= htmlspecialchars($u['fullname'] ?: '(name not found)') ?></td>
                    <td><?= htmlspecialchars($u['employee_id']) ?></td>
                    <td><?= htmlspecialchars($u['hr_department'] ?: '—') ?></td>
                    <td>
                        <select class="mp-role-select">
                            <?php foreach (MP_ROLE_OPTIONS as $roleOpt): ?>
                                <option value="<?= htmlspecialchars($roleOpt) ?>" <?= $u['manpower_role'] === $roleOpt ? 'selected' : '' ?>><?= htmlspecialchars($roleOpt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="mp-admin-save-btn">Save</button>
                        <span class="mp-admin-status"></span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
    </table>
    </div>
    </div>
</div>

<script>
$(function() {
    $('.mp-admin-save-btn').on('click', function() {
        const $btn    = $(this);
        const $row    = $btn.closest('tr');
        const $status = $row.find('.mp-admin-status');

        $btn.prop('disabled', true);
        $status.text('').removeClass('error');

        $.post('manpower_admin_user_save', {
            id: $row.data('id'),
            manpower_role: $row.find('.mp-role-select').val()
        }, function(res) {
            const data = typeof res === 'string' ? JSON.parse(res) : res;
            if (data.success) {
                $status.text('Saved').removeClass('error');
                setTimeout(() => $status.text(''), 2000);
            } else {
                $status.text(data.error || 'Failed').addClass('error');
            }
        }).fail(function() {
            $status.text('Error saving').addClass('error');
        }).always(function() {
            $btn.prop('disabled', false);
        });
    });
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>