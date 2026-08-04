<?php

/**
 * Manpower Request Form
 *
 * File: manpower/public/manpower_form.php
 *
 * Purpose: Create a new manpower request, or edit/revise an existing
 * Draft/Returned one. A single request can bundle multiple Replacement
 * and/or Additional positions (quality-over-quantity: one request per
 * hiring cycle, not one per position).
 *
 * NOTE: This assumes tbl_manpower_request has been split into a parent
 * request row + a child tbl_manpower_request_position table (id,
 * request_id, type, position, headcount, reason, date_needed). The old
 * single-position columns (position/headcount/justification) on
 * tbl_manpower_request no longer map 1:1 — save_manpower.php needs to be
 * updated to insert the parent + child rows in a transaction.
 *
 * Expects $currentUser, $empno, $department, $company, $hr_db to already
 * be set by manpower/includes/auth.php.
 */

if (!isset($currentUser)) {
    require_once dirname(__DIR__) . '/includes/auth.php';
}

requireRoleIn('Requestor', 'Admin');

require_once dirname(__DIR__) . '/includes/header.php';

// ============================================================================
// Load existing request if editing/revising
// ============================================================================

$editing = false;
$request = [
    'id'             => '',
    'mr_no'          => '',
    'nonnegotiable'  => '',
    'status'         => 'Draft',
];
$replacementRows = [];
$additionalRows  = [];

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $stmt = $hr_db->prepare("SELECT * FROM tbl_manpower_request WHERE id = :id AND requestor_employee_id = :empno LIMIT 1");
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->bindParam(':empno', $empno);
    $stmt->execute();
    $found = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($found) {
        if (in_array($found['status'], ['Draft', 'Returned'])) {
            $editing = true;
            $request = array_merge($request, $found);

            $posStmt = $hr_db->prepare("SELECT * FROM tbl_manpower_request_position WHERE request_id = :id ORDER BY id ASC");
            $posStmt->bindParam(':id', $request['id']);
            $posStmt->execute();
            foreach ($posStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                if ($row['type'] === 'replacement') $replacementRows[] = $row;
                else $additionalRows[] = $row;
            }
        } else {
            echo '<div class="alert alert-warning" style="margin:20px;">This request can no longer be edited (status: ' . htmlspecialchars($found['status']) . ').</div>';
        }
    }
}
?>
<style>
    /* Theme tokens aligned with dashboard.php (blue/white palette, soft shadows) */
    #mp-form-app {
        --mp-page-bg: #F5F6F9;
        --mp-bg-raised: #FFFFFF;
        --mp-bg-input: #F4F5F8;
        --mp-border: #F1F2F5;
        --mp-border-strong: #E7E9EE;
        --mp-text: #1F2430;
        --mp-text-muted: #8A93A3;
        --mp-accent: #2F6FE4;
        --mp-accent-dark: #1B4FB0;
        --mp-accent-soft: #E8F0FE;
        --mp-purple: #6A4FE0;
        --mp-red: #E14848;
        --mp-red-soft: #FCEBEB;
        --mp-red-text: #791F1F;
        --mp-radius: 14px;
        --mp-radius-sm: 8px;
        color: var(--mp-text);
        font-size: 14px;
        background: var(--mp-page-bg);
        padding-bottom: 24px;
    }

    /* Font kept as-is: same stack/sizes/weights the form already used */
    #mp-form-app,
    #mp-form-app input,
    #mp-form-app select,
    #mp-form-app textarea,
    #mp-form-app button {
        font-size: 14px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    #mp-form-app .mp-shell {
        background: transparent;
        border: none;
        border-radius: var(--mp-radius);
        overflow: visible;
        max-width: 1160px;
        margin: 0 auto;
    }

    #mp-form-app .mp-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 0 0 18px;
        margin-bottom: 6px;
        flex-wrap: wrap;
    }

    #mp-form-app .mp-header-titlerow {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 2px;
    }

    #mp-form-app .mp-header-titlerow i {
        font-size: 16px;
        color: var(--mp-text-muted);
    }

    #mp-form-app .mp-header-title h1 {
        font-size: 16px;
        font-weight: 500;
        margin: 0;
        color: var(--mp-text);
    }

    #mp-form-app .mp-header-title p {
        font-size: 12px;
        margin: 0;
        padding-left: 24px;
        color: var(--mp-text-muted);
    }

    #mp-form-app .btn-mp-outline,
    #mp-form-app .btn-mp-solid {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: var(--mp-radius-sm);
        padding: 8px 16px;
        font-weight: 500;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
        transition: transform .15s ease, box-shadow .15s ease, background .15s ease, color .15s ease;
    }

    #mp-form-app .btn-mp-outline {
        background: var(--mp-bg-raised);
        border: 1px solid var(--mp-border-strong);
        color: var(--mp-text-muted);
    }

    #mp-form-app .btn-mp-outline:hover {
        border-color: var(--mp-accent);
        color: var(--mp-accent);
        text-decoration: none;
    }

    #mp-form-app .btn-mp-solid {
        background: linear-gradient(135deg, var(--mp-accent), var(--mp-accent-dark));
        border: 1px solid transparent;
        color: #FFFFFF;
        font-weight: 500;
        box-shadow: 0 6px 16px rgba(47, 111, 228, .28);
    }

    #mp-form-app .btn-mp-solid:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(47, 111, 228, .36);
        color: #FFFFFF;
        text-decoration: none;
    }

    #mp-form-app .mp-content-area {
        padding: 0;
    }

    #mp-form-app .mp-card {
        background: var(--mp-bg-raised);
        border: 1px solid var(--mp-border-strong);
        border-radius: var(--mp-radius);
        overflow: hidden;
        padding: 22px 24px;
        box-shadow: 0 2px 8px rgba(31, 36, 48, .04);
    }

    #mp-form-app .mp-section-divider {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 4px 0 10px;
        font-weight: 500;
        font-size: 12px;
        color: var(--mp-text-muted);
    }

    #mp-form-app .mp-section-divider .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
    }

    #mp-form-app .mp-section-divider.replacement .dot {
        background: var(--mp-purple);
    }

    #mp-form-app .mp-section-divider.additional .dot {
        background: var(--mp-accent);
    }

    #mp-form-app .mp-section-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--mp-border);
        order: 1;
    }

    #mp-form-app .mp-card-table {
        border: 1px solid var(--mp-border-strong);
        border-radius: var(--mp-radius-sm);
        overflow: hidden;
        margin-bottom: 22px;
    }

    #mp-form-app .mp-card-table table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    #mp-form-app .mp-card-table thead th {
        font-size: 11px;
        font-weight: 500;
        color: var(--mp-text-muted);
        padding: 6px 8px;
        border-bottom: 1px solid var(--mp-border);
        background: var(--mp-bg-input);
        text-align: left;
    }

    #mp-form-app .mp-card-table tbody td {
        padding: 6px 8px;
        border-bottom: 1px solid var(--mp-border);
        vertical-align: top;
    }

    #mp-form-app .mp-card-table tbody tr:last-child td {
        border-bottom: none;
    }

    #mp-form-app .mp-card-table input,
    #mp-form-app .mp-card-table select {
        width: 100%;
        border: 1px solid var(--mp-border-strong);
        border-radius: var(--mp-radius-sm);
        padding: 6px 8px;
        font-size: 12.5px;
        color: var(--mp-text);
        background: var(--mp-bg-raised);
    }

    #mp-form-app .mp-card-table input:focus,
    #mp-form-app .mp-card-table select:focus {
        outline: none;
        border-color: var(--mp-accent);
        box-shadow: 0 0 0 2px var(--mp-accent-soft);
    }

    #mp-form-app .mp-card-table .btn-del {
        width: 28px;
        height: 28px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--mp-red-soft);
        border: 1px solid var(--mp-red);
        color: var(--mp-red-text);
        border-radius: var(--mp-radius-sm);
        font-size: 12px;
        transition: background .12s ease, color .12s ease;
    }

    #mp-form-app .mp-card-table .btn-del:hover {
        background: var(--mp-red);
        color: #fff;
    }

    #mp-form-app .btn-add-row-full {
        width: 100%;
        border: none;
        border-top: 1px solid var(--mp-border);
        background: transparent;
        color: var(--mp-text-muted);
        border-radius: 0;
        padding: 8px 10px;
        font-weight: 400;
        font-size: 12px;
        text-align: left;
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }

    #mp-form-app .btn-add-row-full:hover {
        background: var(--mp-bg-input);
        color: var(--mp-accent);
    }

    #mp-form-app .mp-field-block {
        margin-bottom: 18px;
    }

    #mp-form-app .mp-field-label {
        font-size: 11px;
        font-weight: 500;
        color: var(--mp-text-muted);
        display: block;
        margin-bottom: 6px;
    }

    #mp-form-app .mp-field-block textarea {
        width: 100%;
        border: 1px solid var(--mp-border-strong);
        border-radius: var(--mp-radius-sm);
        padding: 8px 11px;
        font-size: 13px;
        color: var(--mp-text);
        background: var(--mp-bg-raised);
    }

    #mp-form-app .mp-field-block textarea:focus {
        outline: none;
        border-color: var(--mp-accent);
        box-shadow: 0 0 0 2px var(--mp-accent-soft);
    }

    #mp-form-app .mp-form-footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding-top: 6px;
        border-top: 1px solid var(--mp-border);
        margin-top: 6px;
    }

    @media (max-width: 768px) {
        #mp-form-app .mp-card {
            padding: 16px;
        }

        #mp-form-app .mp-card-table {
            overflow-x: auto;
        }
    }
</style>

<div class="page-wrapper" id="mp-form-app">
    <div class="page-body">
        <div class="container-fluid">
            <div class="mp-shell">

                <div class="mp-header">
                    <div class="mp-header-title">
                        <div class="mp-header-titlerow">
                            <i class="fa fa-users" aria-hidden="true"></i>
                            <h1><?= $editing ? 'Revise Manpower Request' : 'New Manpower Request' ?></h1>
                        </div>
                        <p>Specify positions needed for your department</p>
                    </div>
                    <a href="dashboard" class="btn-mp-outline"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back to dashboard</a>
                </div>

                <div class="mp-content-area">
                    <div class="mp-card">
                        <form id="manpower-form">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($request['id']) ?>">
                            <input type="hidden" name="requestor_employee_id" value="<?= htmlspecialchars($empno) ?>">
                            <input type="hidden" name="department_id" value="<?= htmlspecialchars($department ?? '') ?>">
                            <input type="hidden" name="company_id" value="<?= htmlspecialchars($company ?? '') ?>">
                            <input type="hidden" id="mp-submit-mode" name="action" value="draft">

                            <?php if ($editing): ?>
                                <div class="mp-field-block">
                                    <span class="mp-field-label">MR Number</span>
                                    <strong><?= htmlspecialchars($request['mr_no']) ?></strong>
                                </div>
                            <?php endif; ?>

                            <!-- REPLACEMENT positions -->
                            <div class="mp-section-divider replacement"><span class="dot"></span> Replacement</div>
                            <div class="mp-card-table">
                                <table id="mp-replacement-table">
                                    <thead>
                                        <tr>
                                            <th width="26%">Position</th>
                                            <th width="12%">Headcount</th>
                                            <th>Reason</th>
                                            <th width="16%">Date Needed</th>
                                            <th width="30px"></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <button type="button" class="btn-add-row-full" data-add="replacement">
                                    <i class="fa fa-plus" aria-hidden="true"></i> Add replacement position
                                </button>
                            </div>

                            <!-- ADDITIONAL positions -->
                            <div class="mp-section-divider additional"><span class="dot"></span> Additional</div>
                            <div class="mp-card-table">
                                <table id="mp-additional-table">
                                    <thead>
                                        <tr>
                                            <th width="26%">Position</th>
                                            <th width="12%">Headcount</th>
                                            <th>Reason</th>
                                            <th width="16%">Date Needed</th>
                                            <th width="30px"></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <button type="button" class="btn-add-row-full" data-add="additional">
                                    <i class="fa fa-plus" aria-hidden="true"></i> Add additional position
                                </button>
                            </div>

                            <div class="mp-field-block">
                                <span class="mp-field-label">NON-NEGOTIABLE</span>
                                <textarea name="nonnegotiable" rows="3"><?= htmlspecialchars($request['nonnegotiable']) ?></textarea>
                            </div>

                            <div id="mp-form-err"></div>

                            <div class="mp-form-footer">
                                <a href="dashboard" class="btn-mp-outline">Cancel</a>
                                <button type="button" class="btn-mp-outline" data-draft="1"><i class="fa fa-save" aria-hidden="true"></i> Save as Draft</button>
                                <button type="submit" class="btn-mp-solid"><i class="fa fa-paper-plane" aria-hidden="true"></i> Submit for approval</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        const replacementSeed = <?= json_encode(array_map(function ($r) {
                                    return [$r['position'], $r['headcount'], $r['reason'], $r['date_needed']];
                                }, $replacementRows)) ?>;
        const additionalSeed = <?= json_encode(array_map(function ($r) {
                                    return [$r['position'], $r['headcount'], $r['reason'], $r['date_needed']];
                                }, $additionalRows)) ?>;

        function addRow(type, values) {
            values = values || ['', 1, '', ''];
            const $tbody = $('#mp-' + type + '-table tbody');
            const $row = $('<tr>');
            $row.append($('<td>').append($('<input type="text" placeholder="Insert Position">').val(values[0]).addClass('mp-pos-' + type)));
            $row.append($('<td>').append($('<input type="number" min="1">').val(values[1] || 1).addClass('mp-count-' + type)));
            $row.append($('<td>').append($('<input type="text" placeholder="Reason">').val(values[2]).addClass('mp-reason-' + type)));
            $row.append($('<td>').append($('<input type="date">').val(values[3]).addClass('mp-date-' + type)));
            $row.append($('<td>').append($('<button type="button" class="btn-del"><i class="fa fa-times"></i></button>').on('click', function() {
                $row.remove();
            })));
            $tbody.append($row);
        }

        $('[data-add]').on('click', function() {
            addRow($(this).data('add'));
        });

        if (replacementSeed.length) {
            replacementSeed.forEach(v => addRow('replacement', v));
        } else {
            addRow('replacement');
        }
        if (additionalSeed.length) {
            additionalSeed.forEach(v => addRow('additional', v));
        } else {
            addRow('additional');
        }

        function collectRows(type) {
            const rows = [];
            $('#mp-' + type + '-table tbody tr').each(function() {
                const position = $(this).find('.mp-pos-' + type).val();
                if (!position) return;
                rows.push({
                    position: position,
                    headcount: $(this).find('.mp-count-' + type).val() || 1,
                    reason: $(this).find('.mp-reason-' + type).val() || '',
                    date_needed: $(this).find('.mp-date-' + type).val() || ''
                });
            });
            return rows;
        }

        function submitForm(action) {
            const replacement = collectRows('replacement');
            const additional = collectRows('additional');

            if (action === 'submit' && replacement.length === 0 && additional.length === 0) {
                $('#mp-form-err').html('<p style="color:#E14848;">Add at least one position before submitting.</p>');
                return;
            }

            const formData = new FormData($('#manpower-form')[0]);
            formData.set('action', action);
            formData.append('replacement', JSON.stringify(replacement));
            formData.append('additional', JSON.stringify(additional));

            $.ajax({
                url: 'save',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    const data = typeof res === 'string' ? JSON.parse(res) : res;
                    if (data.success) {
                        window.location = 'dashboard';
                    } else {
                        $('#mp-form-err').html('<p style="color:#E14848;">' + (data.error || 'Failed to save request.') + '</p>');
                    }
                },
                error: function() {
                    $('#mp-form-err').html('<p style="color:#E14848;">An error occurred while saving. Please try again.</p>');
                }
            });
        }

        $('[data-draft]').on('click', function() {
            submitForm('draft');
        });
        $('#manpower-form').on('submit', function(e) {
            e.preventDefault();
            submitForm('submit');
        });
    });
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>