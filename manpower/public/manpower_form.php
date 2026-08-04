<?php
/**
 * Manpower Request Form
 *
 * File: manpower/public/manpower_form.php
 *
 * Purpose: Create a new manpower request, or edit/revise an existing
 * Draft/Returned one. Submits via AJAX to actions/save_manpower.php,
 * following the same pattern as main/pages/add_memo.php.
 *
 * Expects $currentUser, $empno, $department, $company, $hr_db to already
 * be set by manpower/includes/auth.php.
 */

if (!isset($currentUser)) {
    require_once dirname(__DIR__) . '/includes/auth.php';
}

// Only Requestors (and Admin, for testing/support) can create/edit requests
requireRoleIn('Requestor', 'Admin');

// ============================================================================
// Load existing request if editing/revising
// ============================================================================

$editing = false;
$request = [
    'id'              => '',
    'mr_no'           => '',
    'position'        => '',
    'headcount'       => 1,
    'employment_type' => 'Regular',
    'justification'   => '',
    'urgency'         => 'Medium',
    'requested_date'  => date('Y-m-d'),
    'status'          => 'Draft',
];

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $stmt = $hr_db->prepare("SELECT * FROM tbl_manpower_request WHERE id = :id AND requestor_employee_id = :empno LIMIT 1");
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->bindParam(':empno', $empno);
    $stmt->execute();
    $found = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($found) {
        // Only Draft or Returned requests can be edited/revised
        if (in_array($found['status'], ['Draft', 'Returned'])) {
            $editing = true;
            $request = $found;
        } else {
            echo '<div class="alert alert-warning" style="margin:20px;">This request can no longer be edited (status: ' . htmlspecialchars($found['status']) . ').</div>';
        }
    }
}
?>
<div class="page-wrapper">
    <div class="page-body">
        <div class="row" style="margin-left:0; margin-right:0;">
            <div class="col-sm-8">
                <div class="card" id="mp-form-card">
                    <div class="card-block" style="padding:1.25rem;">

                        <h5 style="margin-bottom:15px;">
                            <?= $editing ? 'Revise Manpower Request' : 'New Manpower Request' ?>
                        </h5>

                        <form id="manpower-form">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($request['id']) ?>">
                            <input type="hidden" name="requestor_employee_id" value="<?= htmlspecialchars($empno) ?>">
                            <input type="hidden" name="department_id" value="<?= htmlspecialchars($department ?? '') ?>">
                            <input type="hidden" name="company_id" value="<?= htmlspecialchars($company ?? '') ?>">

                            <?php if ($editing) { ?>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">MR Number:</label>
                                <div class="col-sm-8" style="padding-top:7px;">
                                    <strong><?= htmlspecialchars($request['mr_no']) ?></strong>
                                </div>
                            </div>
                            <?php } ?>

                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Requesting Department:</label>
                                <div class="col-sm-8" style="padding-top:7px;">
                                    <?= htmlspecialchars($department ?? 'N/A') ?>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Position <span style="color:red;">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" name="position" class="form-control" required
                                        value="<?= htmlspecialchars($request['position']) ?>"
                                        placeholder="e.g. Sales Associate">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Headcount <span style="color:red;">*</span></label>
                                <div class="col-sm-8">
                                    <input type="number" name="headcount" class="form-control" min="1" required
                                        value="<?= htmlspecialchars($request['headcount']) ?>">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Employment Type <span style="color:red;">*</span></label>
                                <div class="col-sm-8">
                                    <select name="employment_type" class="form-control" required>
                                        <?php foreach (['Regular', 'Probationary', 'Contractual', 'Project-based', 'Seasonal'] as $type) { ?>
                                            <option value="<?= $type ?>" <?= $request['employment_type'] === $type ? 'selected' : '' ?>><?= $type ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Urgency <span style="color:red;">*</span></label>
                                <div class="col-sm-8">
                                    <select name="urgency" class="form-control" required>
                                        <?php foreach (['Low', 'Medium', 'High', 'Critical'] as $u) { ?>
                                            <option value="<?= $u ?>" <?= $request['urgency'] === $u ? 'selected' : '' ?>><?= $u ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Needed By <span style="color:red;">*</span></label>
                                <div class="col-sm-8">
                                    <input type="date" name="requested_date" class="form-control" required
                                        value="<?= htmlspecialchars($request['requested_date']) ?>">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Justification <span style="color:red;">*</span></label>
                                <div class="col-sm-8">
                                    <textarea name="justification" class="form-control" rows="5" required
                                        placeholder="Explain why this position is needed..."><?= htmlspecialchars($request['justification']) ?></textarea>
                                </div>
                            </div>

                            <div class="form-group row" style="margin-top:20px;">
                                <div class="col-sm-12" style="display:flex; justify-content:flex-end; gap:8px;">
                                    <a href="dashboard" class="btn btn-default btn-mini">Cancel</a>
                                    <button type="submit" name="action" value="draft" class="btn btn-outline-primary btn-mini">
                                        Save as Draft
                                    </button>
                                    <button type="submit" name="action" value="submit" class="btn btn-primary btn-mini">
                                        Submit for Approval
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="card" id="mp-form-card">
                    <div class="card-header">
                        <div class="card-header-left">
                            <h5>Guide</h5>
                        </div>
                    </div>
                    <div class="card-block">
                        <p><strong>Draft</strong> — save your progress without sending it for approval yet.</p>
                        <p><strong>Submit for Approval</strong> — sends the request to your department approver.</p>
                        <p>A returned request can be revised and resubmitted using this same form.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    let submitAction = 'submit';

    $('#manpower-form button[type="submit"]').on('click', function() {
        submitAction = $(this).val();
    });

    $('#manpower-form').on('submit', function(e) {
        e.preventDefault();

        let formData = $(this).serialize() + '&action=' + encodeURIComponent(submitAction);

        $.post('save', formData, function(res) {
            let data = typeof res === 'string' ? JSON.parse(res) : res;
            if (data.success) {
                window.location = 'dashboard';
            } else {
                alert(data.error || 'Failed to save request. Please try again.');
            }
        }).fail(function() {
            alert('An error occurred while saving. Please try again.');
        });
    });
});
</script>
