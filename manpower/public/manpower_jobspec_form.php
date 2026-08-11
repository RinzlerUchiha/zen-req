<?php

/**
 * Job Specification Form (HireFlow)
 *
 * File: manpower/public/manpower_jobspec_form.php
 *
 * Purpose: Standalone create/edit form for tbl_jobspec, independent of
 * any specific manpower request. Full replica of the Zen Admin job spec
 * modal, restyled to HireFlow's design system.
 */

if (!isset($currentUser)) {
    require_once dirname(__DIR__) . '/includes/auth.php';
}

requireRoleIn('Requestor', 'Approver', 'HR Head', 'Admin');

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/manpower_jobspec_config.php';

$editing = false;
$readOnly = false;
$spec = array_fill_keys(array_keys(MP_JOBSPEC_COLUMNS), '');

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $idCol = MP_JOBSPEC_COLUMNS['id'];
    $stmt = $hr_db->prepare("SELECT * FROM " . MP_JOBSPEC_TABLE . " WHERE $idCol = :id LIMIT 1");
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    $found = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($found) {
        $editing = true;
        foreach (MP_JOBSPEC_COLUMNS as $key => $col) {
            $spec[$key] = $found[$col] ?? '';
        }
        // A spec with no recorded owner (created before ownership tracking
        // existed) stays editable by everyone. Otherwise only the creator
        // may edit; everyone else gets a read-only view.
        $readOnly = !empty($spec['created_by']) && $spec['created_by'] !== $empno;
    }
}

// Helper: is this multi-value option currently selected for this field?
function jspec_selected($spec, $field, $value)
{
    $current = explode(MP_JOBSPEC_DELIM, $spec[$field] ?? '');
    return in_array($value, $current);
}

// Dropdown source data — only active records
$jsDepartments = $hr_db->query("SELECT Dept_Code, Dept_Name FROM tbl_department WHERE Dept_Stat = 'active' ORDER BY Dept_Name ASC")->fetchAll(PDO::FETCH_ASSOC);
$jsSections = $hr_db->query("SELECT sec_code, sec_name FROM tbl_section WHERE sec_stat = 'active' ORDER BY sec_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$jsPositions = $hr_db->query("SELECT jd_code, jd_title FROM tbl_jobdescription WHERE jd_stat = 'active' ORDER BY jd_title ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
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
        --mp-radius: 14px;
        --mp-radius-sm: 8px;
        color: var(--mp-text);
        font-size: 14px;
        background: var(--mp-page-bg);
        padding-bottom: 24px;
    }

    #mp-form-app,
    #mp-form-app input,
    #mp-form-app select,
    #mp-form-app textarea,
    #mp-form-app button,
    #mp-form-app label {
        font-size: 13.5px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    #mp-form-app .mp-shell {
        max-width: 1200px;
        margin: 0 auto;
    }

    #mp-form-app .mp-layout {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 24px;
        align-items: start;
    }

    #mp-form-app .mp-toc {
        position: sticky;
        top: 16px;
        background: var(--mp-bg-raised);
        border: 1px solid var(--mp-border-strong);
        border-radius: var(--mp-radius);
        padding: 14px 10px;
        max-height: calc(100vh - 32px);
        overflow-y: auto;
    }

    #mp-form-app .mp-toc a {
        display: block;
        padding: 7px 12px;
        font-size: 11.5px;
        font-weight: 600;
        color: var(--mp-text-muted);
        text-decoration: none;
        border-radius: 7px;
        border-left: 2px solid transparent;
    }

    #mp-form-app .mp-toc a:hover {
        background: var(--mp-bg-input);
        color: var(--mp-text);
    }

    #mp-form-app .mp-toc a.active {
        background: var(--mp-accent-soft);
        color: var(--mp-accent-dark);
        border-left-color: var(--mp-accent);
    }

    #mp-form-app .mp-sticky-footer {
        position: sticky;
        bottom: 0;
        background: var(--mp-bg-raised);
        border-top: 1px solid var(--mp-border-strong);
        padding: 12px 20px;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin: 24px -26px -24px;
        border-radius: 0 0 var(--mp-radius) var(--mp-radius);
        box-shadow: 0 -4px 12px rgba(31, 36, 48, .05);
        z-index: 5;
    }

    @media (max-width: 900px) {
        #mp-form-app .mp-layout {
            grid-template-columns: 1fr;
        }

        #mp-form-app .mp-toc {
            position: static;
            max-height: none;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        #mp-form-app .mp-toc a {
            flex: 1 1 auto;
            text-align: center;
        }
    }

    #mp-form-app .mp-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 0 0 18px;
        flex-wrap: wrap;
    }

    #mp-form-app .mp-header h1 {
        font-size: 16px;
        font-weight: 600;
        margin: 0;
    }

    #mp-form-app .mp-header p {
        font-size: 12px;
        margin: 2px 0 0;
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
        cursor: pointer;
        text-decoration: none;
    }

    #mp-form-app .btn-mp-outline {
        background: var(--mp-bg-raised);
        border: 1px solid var(--mp-border-strong);
        color: var(--mp-text-muted);
    }

    #mp-form-app .btn-mp-outline:hover {
        border-color: var(--mp-accent);
        color: var(--mp-accent);
    }

    #mp-form-app .btn-mp-solid {
        background: linear-gradient(135deg, var(--mp-accent), var(--mp-accent-dark));
        border: none;
        color: #fff;
        box-shadow: 0 6px 16px rgba(47, 111, 228, .28);
    }

    #mp-form-app .mp-card {
        background: var(--mp-bg-raised);
        border: 1px solid var(--mp-border-strong);
        border-radius: var(--mp-radius);
        padding: 24px 26px;
        box-shadow: 0 2px 8px rgba(31, 36, 48, .04);
    }

    #mp-form-app .mp-sec-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3px;
        color: var(--mp-text);
        margin: 26px 0 4px;
        padding-top: 18px;
        border-top: 1px solid var(--mp-border);
    }

    #mp-form-app .mp-sec-title:first-child {
        margin-top: 0;
        padding-top: 0;
        border-top: none;
    }

    #mp-form-app .mp-sec-hint {
        font-size: 11px;
        color: var(--mp-text-muted);
        margin-bottom: 12px;
    }

    #mp-form-app .mp-field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 14px;
    }

    #mp-form-app .mp-field-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--mp-text-muted);
        display: block;
        margin-bottom: 5px;
    }

    #mp-form-app input[type="text"],
    #mp-form-app input[type="number"],
    #mp-form-app select,
    #mp-form-app textarea {
        width: 100%;
        border: 1px solid var(--mp-border-strong);
        border-radius: var(--mp-radius-sm);
        padding: 7px 10px;
        background: var(--mp-bg-raised);
        color: var(--mp-text);
    }

    #mp-form-app input:focus,
    #mp-form-app select:focus,
    #mp-form-app textarea:focus {
        outline: none;
        border-color: var(--mp-accent);
        box-shadow: 0 0 0 2px var(--mp-accent-soft);
    }

    #mp-form-app .mp-searchable-select {
        position: relative;
    }

    #mp-form-app .mp-searchable-select select {
        display: none;
    }

    #mp-form-app .mp-ss-input {
        width: 100%;
        cursor: pointer;
        background: var(--mp-bg-raised) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%238A93A3' stroke-width='1.5' fill='none'/%3E%3C/svg%3E") no-repeat right 12px center;
    }

    #mp-form-app .mp-ss-panel {
        display: none;
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: var(--mp-bg-raised);
        border: 1px solid var(--mp-border-strong);
        border-radius: var(--mp-radius-sm);
        box-shadow: 0 8px 20px rgba(31, 36, 48, .12);
        max-height: 230px;
        overflow-y: auto;
        z-index: 20;
    }

    #mp-form-app .mp-searchable-select.open .mp-ss-panel {
        display: block;
    }

    #mp-form-app .mp-ss-option {
        padding: 8px 12px;
        cursor: pointer;
        font-size: 13px;
    }

    #mp-form-app .mp-ss-option:hover,
    #mp-form-app .mp-ss-option.highlighted {
        background: var(--mp-accent-soft);
        color: var(--mp-accent-dark);
    }

    #mp-form-app .mp-ss-option.mp-ss-empty {
        color: var(--mp-text-muted);
        cursor: default;
        font-style: italic;
    }

    #mp-form-app .mp-ss-option.mp-ss-empty:hover {
        background: none;
        color: var(--mp-text-muted);
    }

    #mp-form-app .mp-check-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 4px;
    }

    #mp-form-app .mp-check-item {
        display: flex;
        align-items: center;
        gap: 7px;
        border: 1px solid var(--mp-border-strong);
        border-radius: 20px;
        padding: 7px 14px 7px 10px;
        cursor: pointer;
        transition: all .12s ease;
        background: var(--mp-bg-raised);
    }

    #mp-form-app .mp-check-item:hover {
        border-color: var(--mp-accent);
    }

    #mp-form-app .mp-check-item:has(input:checked) {
        background: var(--mp-accent-soft);
        border-color: var(--mp-accent);
    }

    #mp-form-app .mp-check-item:has(input:checked) label {
        color: var(--mp-accent-dark);
        font-weight: 600;
    }

    #mp-form-app .mp-check-item input {
        width: auto;
        margin: 0;
        accent-color: var(--mp-accent);
        flex-shrink: 0;
        pointer-events: none;
    }

    #mp-form-app .mp-check-item input.mp-check-detail-inline {
        width: auto;
        margin: 0;
        accent-color: initial;
        flex-shrink: initial;
        pointer-events: auto;
    }

    #mp-form-app .mp-check-detail-inline:disabled {
        background: var(--mp-bg-input);
        color: var(--mp-text-muted);
        cursor: not-allowed;
    }

    #mp-form-app .mp-check-item label {
        cursor: pointer;
        margin: 0;
        flex: 1 1 auto;
    }

    #mp-form-app .mp-radio-block .mp-check-item {
        border-radius: 8px;
        display: flex;
        margin-bottom: 4px;
        width: 100%;
    }

    #mp-form-app .mp-sec-counter {
        font-size: 10.5px;
        font-weight: 700;
        padding: 2px 9px;
        border-radius: 20px;
        background: var(--mp-bg-input);
        color: var(--mp-text-muted);
        margin-left: 8px;
    }

    #mp-form-app .mp-sec-counter.mp-counter-full {
        background: var(--mp-accent-soft);
        color: var(--mp-accent-dark);
    }

    #mp-form-app .mp-check-item-with-detail {
        width: 100%;
        border-radius: 8px;
        display: grid;
        grid-template-columns: 260px 1fr;
        align-items: center;
        column-gap: 10px;
    }

    #mp-form-app .mp-check-item-with-detail input[type="checkbox"] {
        grid-row: 1;
        grid-column: 1;
        justify-self: start;
    }

    #mp-form-app .mp-check-item-with-detail label {
        grid-row: 1;
        grid-column: 1;
        margin-left: 22px;
    }

    #mp-form-app .mp-check-detail-inline {
        grid-column: 2;
        max-width: 320px;
        padding: 5px 10px;
        margin-left: 0;
    }

    #mp-form-app .mp-radio-block {
        border: 1px solid var(--mp-border-strong);
        border-radius: var(--mp-radius-sm);
        padding: 10px 12px;
        margin-bottom: 12px;
    }

    #mp-form-app .mp-radio-block-label {
        font-size: 10.5px;
        font-weight: 700;
        color: var(--mp-text-muted);
        text-transform: uppercase;
        letter-spacing: .3px;
        display: block;
        margin-bottom: 6px;
    }

    #mp-form-app .mp-form-footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding-top: 18px;
        margin-top: 18px;
        border-top: 1px solid var(--mp-border);
    }

    #mp-form-app .mp-field-error {
        color: #E14848;
        font-size: 11px;
        line-height: 1.6;
        margin: 4px 0 10px;
        padding: 8px 10px;
        background: #FDEDED;
        border: 1px solid #F5C2C2;
        border-radius: 8px;
    }

    #mp-form-app .mp-toc a.mp-toc-error {
        color: #E14848;
    }

    #mp-form-app .mp-toc a.mp-toc-error::after {
        content: " •";
    }

    @media (max-width: 768px) {
        #mp-form-app .mp-field-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-wrapper" id="mp-form-app">
    <div class="page-body">
        <div class="container-fluid">
            <div class="mp-shell">

                <div class="mp-header">
                    <div>
                        <h1><?= $readOnly ? 'View Job Specification' : ($editing ? 'Edit Job Specification' : 'New Job Specification') ?></h1>
                        <p><?= $readOnly ? 'You can view this job specification. Only its creator can edit it.' : 'Define the profile and competencies required for this role' ?></p>
                    </div>
                    <a href="dashboard#job-spec" class="btn-mp-outline"><i class="fa fa-arrow-left"></i> Back to dashboard</a>
                </div>

                <div class="mp-layout">
                    <nav class="mp-toc" id="mp-toc">
                        <a href="#sec-basic" class="active">Basic Info</a>
                        <a href="#sec-education">Education</a>
                        <a href="#sec-workexp">Work Experience</a>
                        <a href="#sec-duties">Duties</a>
                        <a href="#sec-competencies">Competencies</a>
                        <a href="#sec-compskill">Computer Skills</a>
                        <a href="#sec-otherskill">Other Skills</a>
                        <a href="#sec-mp">Meta Program</a>
                        <a href="#sec-tapt">TAPT</a>
                        <a href="#sec-enneagram">Enneagram</a>
                        <a href="#sec-learnstyle">Learning Style</a>
                        <a href="#sec-career">Career Anchor</a>
                        <a href="#sec-motivation">Motivation</a>
                        <a href="#sec-personality">Personality</a>
                        <a href="#sec-raven">Raven</a>
                        <a href="#sec-remarks">Remarks</a>
                    </nav>

                    <div class="mp-card">
                        <form id="jobspec-form" novalidate<?= $readOnly ? ' inert' : '' ?>>
                            <input type="hidden" name="id" value="<?= htmlspecialchars($spec['id']) ?>">

                            <div class="mp-sec-title" id="sec-basic">Basic Information</div>
                            <div class="mp-field-row">
                                <div>
                                    <span class="mp-field-label">Department</span>
                                    <div class="mp-searchable-select">
                                        <select name="department" required>
                                            <option value="">Select Department</option>
                                            <?php foreach ($jsDepartments as $d): ?>
                                                <option value="<?= htmlspecialchars($d['Dept_Code']) ?>" <?= $spec['department'] === $d['Dept_Code'] ? 'selected' : '' ?>><?= htmlspecialchars($d['Dept_Name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <span class="mp-field-label">Section</span>
                                    <div class="mp-searchable-select">
                                        <select name="section">
                                            <option value="">Select Section</option>
                                            <?php foreach ($jsSections as $s): ?>
                                                <option value="<?= htmlspecialchars($s['sec_code']) ?>" <?= $spec['section'] === $s['sec_code'] ? 'selected' : '' ?>><?= htmlspecialchars($s['sec_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mp-field-row">
                                <div>
                                    <span class="mp-field-label">Position</span>
                                    <div class="mp-searchable-select">
                                        <select name="position" required>
                                            <option value="">Select Position</option>
                                            <?php foreach ($jsPositions as $p): ?>
                                                <option value="<?= htmlspecialchars($p['jd_code']) ?>" <?= $spec['position'] === $p['jd_code'] ? 'selected' : '' ?>><?= htmlspecialchars($p['jd_title']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <span class="mp-field-label">Employment Status</span>
                                    <select name="emplstat">
                                        <option value="">Select Employment Status</option>
                                        <?php foreach (MP_JOBSPEC_OPTIONS['emplstat'] as $opt): ?>
                                            <option value="<?= htmlspecialchars($opt) ?>" <?= $spec['emplstat'] === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mp-field-row">
                                <div>
                                    <span class="mp-field-label">Sex</span>
                                    <select name="sex">
                                        <option value="">—</option>
                                        <option value="Male" <?= $spec['sex'] === 'Male' ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= $spec['sex'] === 'Female' ? 'selected' : '' ?>>Female</option>
                                        <option value="Either" <?= $spec['sex'] === 'Either' ? 'selected' : '' ?>>Either</option>
                                    </select>
                                </div>
                                <div>
                                    <span class="mp-field-label">Age Range</span>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <input type="number" name="agerange_min" min="0" placeholder="Min" value="<?= htmlspecialchars(explode('-', $spec['agerange'])[0] ?? '') ?>" style="width:100%;">
                                        <span style="color:var(--mp-text-muted); flex:0 0 auto;">to</span>
                                        <input type="number" name="agerange_max" min="0" placeholder="Max" value="<?= htmlspecialchars(explode('-', $spec['agerange'])[1] ?? '') ?>" style="width:100%;">
                                    </div>
                                </div>
                            </div>
                            <div class="mp-sec-title" id="sec-education">Educational Attainment</div>
                            <div class="mp-sec-hint">Check preferred option(s)</div>
                            <?php foreach (MP_JOBSPEC_OPTIONS['education'] as $opt):
                                $eduId = 'edu_' . md5($opt['value']);
                            ?>
                                <div class="mp-check-item<?= $opt['detail'] ? ' mp-check-item-with-detail' : '' ?>">
                                    <input type="checkbox" id="<?= $eduId ?>" name="education[]" value="<?= htmlspecialchars($opt['value']) ?>" <?= jspec_selected($spec, 'education', $opt['value']) ? 'checked' : '' ?>>
                                    <label for="<?= $eduId ?>"><?= htmlspecialchars($opt['value']) ?></label>
                                    <?php if ($opt['detail']): ?>
                                        <input type="text" class="mp-check-detail-inline" name="education_detail_<?= md5($opt['value']) ?>" placeholder="Course/Degree" value="">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>

                            <div class="mp-sec-title" id="sec-workexp">Work Experience Required</div>
                            <div class="mp-check-grid">
                                <?php foreach (MP_JOBSPEC_OPTIONS['workexp'] as $opt): $id = 'workexp_' . md5($opt); ?>
                                    <div class="mp-check-item">
                                        <input type="checkbox" id="<?= $id ?>" name="workexp[]" value="<?= htmlspecialchars($opt) ?>" <?= jspec_selected($spec, 'workexp', $opt) ? 'checked' : '' ?>>
                                        <label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mp-sec-title" id="sec-duties">BRIEF STATEMENT OF DUTIES/RESPONSIBILITIES TO BE PERFORMED:</div>
                            <small>(Please enumerate i.e.IT Dean: Conducts Industry consultation on a quarterly basis)</small>
                            <textarea name="duties" rows="3"><?= htmlspecialchars($spec['duties']) ?></textarea>

                            <div class="mp-sec-title" id="sec-competencies">Technical Competencies</div>
                            <textarea name="techcompetencies" rows="3"><?= htmlspecialchars($spec['techcompetencies']) ?></textarea>

                            <div class="mp-sec-title">Competencies Needed</div>
                            <small>(Ex. Knows how to prepare financial statement, knows Computer Programming). Please enumerate.</small>
                            <textarea name="competencies" rows="3"><?= htmlspecialchars($spec['competencies']) ?></textarea>

                            <div class="mp-sec-title" id="sec-compskill">Computer Skills <small>(Check all that apply)</small></div>

                            <div class="mp-check-grid">
                                <?php foreach (MP_JOBSPEC_OPTIONS['computerskill'] as $opt): $id = 'compskill_' . md5($opt); ?>
                                    <div class="mp-check-item">
                                        <input type="checkbox" id="<?= $id ?>" name="computerskill[]" value="<?= htmlspecialchars($opt) ?>" <?= jspec_selected($spec, 'computerskill', $opt) ? 'checked' : '' ?>>
                                        <label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mp-sec-title" id="sec-otherskill">Other Skills</div>
                            <small>(Ex. Knows how to prepare financial statement, knows Computer Programming). Please enumerate.</small>
                            <textarea name="otherskill" rows="2"><?= htmlspecialchars($spec['otherskill']) ?></textarea>

                            <div class="mp-sec-title" id="sec-mp">Meta Program</div>
                            <div class="mp-field-row">
                                <div class="mp-radio-block">
                                    <span class="mp-radio-block-label">A. Approach to Problem</span>
                                    <?php foreach (MP_JOBSPEC_OPTIONS['mpa'] as $opt): $id = 'mpa_' . md5($opt); ?>
                                        <div class="mp-check-item"><input type="radio" id="<?= $id ?>" name="mpa" value="<?= htmlspecialchars($opt) ?>" <?= $spec['mpa'] === $opt ? 'checked' : '' ?>><label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label></div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mp-radio-block">
                                    <span class="mp-radio-block-label">C. Locus of Control</span>
                                    <?php foreach (MP_JOBSPEC_OPTIONS['mpc'] as $opt): $id = 'mpc_' . md5($opt); ?>
                                        <div class="mp-check-item"><input type="radio" id="<?= $id ?>" name="mpc" value="<?= htmlspecialchars($opt) ?>" <?= $spec['mpc'] === $opt ? 'checked' : '' ?>><label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="mp-field-row">
                                <div class="mp-radio-block">
                                    <span class="mp-radio-block-label">B. Time Frame — Terms</span>
                                    <?php foreach (MP_JOBSPEC_OPTIONS['mpb1'] as $opt): $id = 'mpb1_' . md5($opt); ?>
                                        <div class="mp-check-item"><input type="radio" id="<?= $id ?>" name="mpb1" value="<?= htmlspecialchars($opt) ?>" <?= jspec_selected($spec, 'mpb', $opt) ? 'checked' : '' ?>><label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label></div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mp-radio-block">
                                    <span class="mp-radio-block-label">B. Time Frame — Time</span>
                                    <?php foreach (MP_JOBSPEC_OPTIONS['mpb2'] as $opt): $id = 'mpb2_' . md5($opt); ?>
                                        <div class="mp-check-item"><input type="radio" id="<?= $id ?>" name="mpb2" value="<?= htmlspecialchars($opt) ?>" <?= jspec_selected($spec, 'mpb', $opt) ? 'checked' : '' ?>><label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="mp-field-row">
                                <div class="mp-radio-block">
                                    <span class="mp-radio-block-label">D. Mode of Comparison</span>
                                    <?php foreach (MP_JOBSPEC_OPTIONS['mpd'] as $opt): $id = 'mpd_' . md5($opt); ?>
                                        <div class="mp-check-item"><input type="radio" id="<?= $id ?>" name="mpd" value="<?= htmlspecialchars($opt) ?>" <?= $spec['mpd'] === $opt ? 'checked' : '' ?>><label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label></div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mp-radio-block">
                                    <span class="mp-radio-block-label">E. Chunk Size</span>
                                    <?php foreach (MP_JOBSPEC_OPTIONS['mpe'] as $opt): $id = 'mpe_' . md5($opt); ?>
                                        <div class="mp-check-item"><input type="radio" id="<?= $id ?>" name="mpe" value="<?= htmlspecialchars($opt) ?>" <?= $spec['mpe'] === $opt ? 'checked' : '' ?>><label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="mp-field-row">
                                <div class="mp-radio-block">
                                    <span class="mp-radio-block-label">F. Approach to Solving — Task</span>
                                    <?php foreach (MP_JOBSPEC_OPTIONS['mpf1'] as $opt): $id = 'mpf1_' . md5($opt); ?>
                                        <div class="mp-check-item"><input type="radio" id="<?= $id ?>" name="mpf1" value="<?= htmlspecialchars($opt) ?>" <?= jspec_selected($spec, 'mpf', $opt) ? 'checked' : '' ?>><label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label></div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mp-radio-block">
                                    <span class="mp-radio-block-label">F. Approach to Solving — Relationship</span>
                                    <?php foreach (MP_JOBSPEC_OPTIONS['mpf2'] as $opt): $id = 'mpf2_' . md5($opt); ?>
                                        <div class="mp-check-item"><input type="radio" id="<?= $id ?>" name="mpf2" value="<?= htmlspecialchars($opt) ?>" <?= jspec_selected($spec, 'mpf', $opt) ? 'checked' : '' ?>><label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="mp-radio-block">
                                <span class="mp-radio-block-label">G. Thinking Style</span>
                                <?php foreach (MP_JOBSPEC_OPTIONS['mpg'] as $opt): $id = 'mpg_' . md5($opt); ?>
                                    <div class="mp-check-item"><input type="radio" id="<?= $id ?>" name="mpg" value="<?= htmlspecialchars($opt) ?>" <?= $spec['mpg'] === $opt ? 'checked' : '' ?>><label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label></div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mp-sec-title" id="sec-tapt">TAPT</div>
                            <small>(The TAPT is a personality type assessment. Please check the four preferred personality type combination.)</small>
                            <div class="mp-sec-hint">Check four preferred personality type combination</div>
                            <div class="mp-field-row">
                                <div class="mp-radio-block">
                                    <?php foreach (MP_JOBSPEC_OPTIONS['tapt1'] as $opt): $id = 'tapt1_' . md5($opt); ?>
                                        <div class="mp-check-item"><input type="radio" id="<?= $id ?>" name="tapt1" value="<?= htmlspecialchars($opt) ?>" <?= jspec_selected($spec, 'tapt', $opt) ? 'checked' : '' ?>><label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label></div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mp-radio-block">
                                    <?php foreach (MP_JOBSPEC_OPTIONS['tapt2'] as $opt): $id = 'tapt2_' . md5($opt); ?>
                                        <div class="mp-check-item"><input type="radio" id="<?= $id ?>" name="tapt2" value="<?= htmlspecialchars($opt) ?>" <?= jspec_selected($spec, 'tapt', $opt) ? 'checked' : '' ?>><label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="mp-field-row">
                                <div class="mp-radio-block">
                                    <?php foreach (MP_JOBSPEC_OPTIONS['tapt3'] as $opt): $id = 'tapt3_' . md5($opt); ?>
                                        <div class="mp-check-item"><input type="radio" id="<?= $id ?>" name="tapt3" value="<?= htmlspecialchars($opt) ?>" <?= jspec_selected($spec, 'tapt', $opt) ? 'checked' : '' ?>><label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label></div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mp-radio-block">
                                    <?php foreach (MP_JOBSPEC_OPTIONS['tapt4'] as $opt): $id = 'tapt4_' . md5($opt); ?>
                                        <div class="mp-check-item"><input type="radio" id="<?= $id ?>" name="tapt4" value="<?= htmlspecialchars($opt) ?>" <?= jspec_selected($spec, 'tapt', $opt) ? 'checked' : '' ?>><label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="mp-sec-title" id="sec-enneagram">Enneagram</div>
                            <div class="mp-check-grid">
                                <?php foreach (MP_JOBSPEC_OPTIONS['enneagram'] as $opt): $id = 'enneagram_' . md5($opt); ?>
                                    <div class="mp-check-item">
                                        <input type="checkbox" id="<?= $id ?>" name="enneagram[]" value="<?= htmlspecialchars($opt) ?>" <?= jspec_selected($spec, 'enneagram', $opt) ? 'checked' : '' ?>>
                                        <label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mp-sec-title" id="sec-learnstyle">Learning Style</div>
                            <div class="mp-check-grid">
                                <?php foreach (MP_JOBSPEC_OPTIONS['learnstyle'] as $opt): $id = 'learnstyle_' . md5($opt); ?>
                                    <div class="mp-check-item">
                                        <input type="checkbox" id="<?= $id ?>" name="learnstyle[]" value="<?= htmlspecialchars($opt) ?>" <?= jspec_selected($spec, 'learnstyle', $opt) ? 'checked' : '' ?>>
                                        <label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mp-sec-title" id="sec-career">Career Anchor <span class="mp-sec-counter" data-counter-for="career">0 / 3</span></div>
                            <div class="mp-sec-hint">Check top 3 preferred choices</div>
                            <div class="mp-check-grid" data-max-group="career">
                                <?php foreach (MP_JOBSPEC_OPTIONS['career'] as $opt): $id = 'career_' . md5($opt); ?>
                                    <div class="mp-check-item">
                                        <input type="checkbox" id="<?= $id ?>" name="career[]" value="<?= htmlspecialchars($opt) ?>" <?= jspec_selected($spec, 'career', $opt) ? 'checked' : '' ?>>
                                        <label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mp-sec-title" id="sec-motivation">Motivation to Work <span class="mp-sec-counter" data-counter-for="motivation">0 / 3</span></div>
                            <div class="mp-sec-hint">Check top 3 preferred choices</div>
                            <div class="mp-check-grid" data-max-group="motivation">
                                <?php foreach (MP_JOBSPEC_OPTIONS['motivation'] as $opt): $id = 'motivation_' . md5($opt); ?>
                                    <div class="mp-check-item">
                                        <input type="checkbox" id="<?= $id ?>" name="motivation[]" value="<?= htmlspecialchars($opt) ?>" <?= jspec_selected($spec, 'motivation', $opt) ? 'checked' : '' ?>>
                                        <label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mp-sec-title" id="sec-personality">Personality Type</div>
                            <div class="mp-check-grid">
                                <?php foreach (MP_JOBSPEC_OPTIONS['personality'] as $opt): $id = 'personality_' . md5($opt); ?>
                                    <div class="mp-check-item">
                                        <input type="checkbox" id="<?= $id ?>" name="personality[]" value="<?= htmlspecialchars($opt) ?>" <?= jspec_selected($spec, 'personality', $opt) ? 'checked' : '' ?>>
                                        <label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mp-sec-title" id="sec-raven">Raven</div>
                            <div class="mp-field-row" style="grid-template-columns:1fr 1fr 1fr;">
                                <div class="mp-radio-block">
                                    <span class="mp-radio-block-label">Low</span>
                                    <?php foreach (MP_JOBSPEC_OPTIONS['ravenl'] as $opt): $id = 'ravenl_' . md5($opt); ?>
                                        <div class="mp-check-item"><input type="checkbox" id="<?= $id ?>" name="ravenl[]" value="<?= htmlspecialchars($opt) ?>" <?= jspec_selected($spec, 'ravenl', $opt) ? 'checked' : '' ?>><label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label></div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mp-radio-block">
                                    <span class="mp-radio-block-label">Average</span>
                                    <?php foreach (MP_JOBSPEC_OPTIONS['ravena'] as $opt): $id = 'ravena_' . md5($opt); ?>
                                        <div class="mp-check-item"><input type="checkbox" id="<?= $id ?>" name="ravena[]" value="<?= htmlspecialchars($opt) ?>" <?= jspec_selected($spec, 'ravena', $opt) ? 'checked' : '' ?>><label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label></div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mp-radio-block">
                                    <span class="mp-radio-block-label">High</span>
                                    <?php foreach (MP_JOBSPEC_OPTIONS['ravenh'] as $opt): $id = 'ravenh_' . md5($opt); ?>
                                        <div class="mp-check-item"><input type="checkbox" id="<?= $id ?>" name="ravenh[]" value="<?= htmlspecialchars($opt) ?>" <?= jspec_selected($spec, 'ravenh', $opt) ? 'checked' : '' ?>><label for="<?= $id ?>"><?= htmlspecialchars($opt) ?></label></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="mp-sec-title" id="sec-remarks">Remarks</div>
                            <textarea name="remarks" rows="3"><?= htmlspecialchars($spec['remarks']) ?></textarea>

                            <div id="jobspec-err" style="margin-top:14px;"></div>
                        </form>
                    </div>
                </div>

                <div class="mp-sticky-footer">
                    <a href="dashboard#job-spec" class="btn-mp-outline">Back</a>
                    <?php if (!$readOnly): ?>
                        <button type="submit" form="jobspec-form" class="btn-mp-solid"><i class="fa fa-save"></i> Save Job Specification</button>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    $('#jobspec-form').off('.jobspec');
    $(document).off('.jobspec');

    $(function() {
        // Searchable select widget: converts each hidden <select> inside
        // .mp-searchable-select into a text input + filterable dropdown,
        // while keeping the underlying <select> in sync for form submission.
        $('.mp-searchable-select').each(function() {
            const $wrap = $(this);
            const $select = $wrap.find('select');
            const options = $select.find('option').map(function() {
                return {
                    value: $(this).val(),
                    label: $(this).text()
                };
            }).get();

            const selectedOpt = options.find(o => o.value === $select.val()) || options[0];

            const $input = $('<input type="text" class="mp-ss-input" readonly placeholder="Search...">')
                .val(selectedOpt && selectedOpt.value !== '' ? selectedOpt.label : '');
            const $panel = $('<div class="mp-ss-panel">');

            $wrap.append($input).append($panel);

            function renderOptions(filter) {
                $panel.empty();
                const f = (filter || '').toLowerCase();
                const matches = options.filter(o => o.value !== '' && o.label.toLowerCase().includes(f));
                if (matches.length === 0) {
                    $panel.append('<div class="mp-ss-option mp-ss-empty">No matches found</div>');
                    return;
                }
                matches.forEach(function(o) {
                    const $opt = $('<div class="mp-ss-option">').text(o.label).data('value', o.value);
                    if (o.value === $select.val()) $opt.addClass('highlighted');
                    $panel.append($opt);
                });
            }

            $input.on('click', function() {
                $('.mp-searchable-select.open').not($wrap).removeClass('open');
                $wrap.toggleClass('open');
                if ($wrap.hasClass('open')) {
                    $input.removeAttr('readonly').val('').trigger('focus');
                    renderOptions('');
                } else {
                    $input.attr('readonly', true);
                    const cur = options.find(o => o.value === $select.val());
                    $input.val(cur && cur.value !== '' ? cur.label : '');
                }
            });

            $input.on('input', function() {
                renderOptions($(this).val());
            });

            $panel.on('click', '.mp-ss-option:not(.mp-ss-empty)', function() {
                const val = $(this).data('value');
                const label = $(this).text();
                $select.val(val).trigger('change');
                $input.val(label).attr('readonly', true);
                $wrap.removeClass('open');
            });

            $(document).on('click', function(e) {
                if (!$wrap.is(e.target) && $wrap.has(e.target).length === 0) {
                    if ($wrap.hasClass('open')) {
                        $wrap.removeClass('open');
                        $input.attr('readonly', true);
                        const cur = options.find(o => o.value === $select.val());
                        $input.val(cur && cur.value !== '' ? cur.label : '');
                    }
                }
            });
        });

        // Make the whole chip clickable, not just the tiny checkbox/radio itself
        $('.mp-check-item').on('click', function(e) {
            if ($(e.target).closest('input, label').length) return; // avoid double-toggling: native label click already toggles the input
            const $input = $(this).find('input');
            if ($input.prop('disabled')) return;
            if ($input.attr('type') === 'radio') {
                $input.prop('checked', true).trigger('change');
            } else {
                $input.prop('checked', !$input.prop('checked')).trigger('change');
            }
        });

        // Enable the detail textbox only when its checkbox is checked
        $('.mp-check-item-with-detail').each(function() {
            const $item = $(this);
            const $checkbox = $item.find('input[type="checkbox"]');
            const $detail = $item.find('.mp-check-detail-inline');

            function syncDetail() {
                const checked = $checkbox.prop('checked');
                $detail.prop('disabled', !checked);
                if (!checked) $detail.val('');
            }

            syncDetail();
            $checkbox.on('change', syncDetail);
        });

        // Required-field rules — mirrors the backend's "The <field> field is
        // required." validation block. Each rule points at the section it
        // lives in so we can show the error right under that section's title.
        const REQUIRED_RULES = [{
                section: 'sec-basic',
                label: 'Department',
                check: () => $('select[name="department"]').val()
            },
            {
                section: 'sec-basic',
                label: 'Position',
                check: () => $('select[name="position"]').val()
            },
            {
                section: 'sec-basic',
                label: 'Employment Status',
                check: () => $('select[name="emplstat"]').val()
            },
            {
                section: 'sec-basic',
                label: 'Sex',
                check: () => $('select[name="sex"]').val()
            },
            {
                section: 'sec-basic',
                label: 'Age Range (min and max)',
                check: () => $('input[name="agerange_min"]').val().trim() && $('input[name="agerange_max"]').val().trim()
            },
            {
                section: 'sec-education',
                label: 'Educational Attainment (select at least one)',
                check: () => $('input[name="education[]"]:checked').length > 0
            },
            {
                section: 'sec-workexp',
                label: 'Work Experience Required (select at least one)',
                check: () => $('input[name="workexp[]"]:checked').length > 0
            },
            {
                section: 'sec-duties',
                label: 'Brief Statement of Duties/Responsibilities',
                check: () => $('textarea[name="duties"]').val().trim()
            },
            {
                section: 'sec-mp',
                label: 'Meta Program A — Approach to Problem',
                check: () => $('input[name="mpa"]:checked').length > 0
            },
            {
                section: 'sec-mp',
                label: 'Meta Program B — Time Frame (Terms and Time)',
                check: () => $('input[name="mpb1"]:checked').length > 0 && $('input[name="mpb2"]:checked').length > 0
            },
            {
                section: 'sec-mp',
                label: 'Meta Program C — Locus of Control',
                check: () => $('input[name="mpc"]:checked').length > 0
            },
            {
                section: 'sec-mp',
                label: 'Meta Program D — Mode of Comparison',
                check: () => $('input[name="mpd"]:checked').length > 0
            },
            {
                section: 'sec-mp',
                label: 'Meta Program E — Chunk Size',
                check: () => $('input[name="mpe"]:checked').length > 0
            },
            {
                section: 'sec-mp',
                label: 'Meta Program F — Approach to Solving (Task and Relationship)',
                check: () => $('input[name="mpf1"]:checked').length > 0 && $('input[name="mpf2"]:checked').length > 0
            },
            {
                section: 'sec-mp',
                label: 'Meta Program G — Thinking Style',
                check: () => $('input[name="mpg"]:checked').length > 0
            },
            {
                section: 'sec-tapt',
                label: 'TAPT (all four preferred combinations)',
                check: () => ['tapt1', 'tapt2', 'tapt3', 'tapt4'].every(n => $('input[name="' + n + '"]:checked').length > 0)
            },
            {
                section: 'sec-enneagram',
                label: 'Enneagram (select at least one)',
                check: () => $('input[name="enneagram[]"]:checked').length > 0
            },
            {
                section: 'sec-learnstyle',
                label: 'Learning Style (select at least one)',
                check: () => $('input[name="learnstyle[]"]:checked').length > 0
            },
            {
                section: 'sec-career',
                label: 'Career Anchor (top 3 choices)',
                check: () => $('input[name="career[]"]:checked').length > 0
            },
            {
                section: 'sec-motivation',
                label: 'Motivation to Work (top 3 choices)',
                check: () => $('input[name="motivation[]"]:checked').length > 0
            },
            {
                section: 'sec-personality',
                label: 'Personality Type (select at least one)',
                check: () => $('input[name="personality[]"]:checked').length > 0
            },
            {
                section: 'sec-raven',
                label: 'Raven — Low',
                check: () => $('input[name="ravenl[]"]:checked').length > 0
            },
            {
                section: 'sec-raven',
                label: 'Raven — Average',
                check: () => $('input[name="ravena[]"]:checked').length > 0
            },
            {
                section: 'sec-raven',
                label: 'Raven — High',
                check: () => $('input[name="ravenh[]"]:checked').length > 0
            },
        ];

        function clearFieldErrors() {
            $('#mp-form-app .mp-field-error').remove();
            $('#mp-form-app .mp-toc a').removeClass('mp-toc-error');
        }

        function showSectionError(sectionId, message) {
            const $title = $('#' + sectionId);
            if (!$title.length) return;
            let $err = $title.next('.mp-field-error');
            if (!$err.length) {
                $err = $('<div class="mp-field-error"><ul style="margin:0;padding-left:16px;"></ul></div>');
                $title.after($err);
            }
            $err.find('ul').append('<li>' + message + ' is required.</li>');
            $('.mp-toc a[href="#' + sectionId + '"]').addClass('mp-toc-error');
        }

        function validateJobSpecForm() {
            clearFieldErrors();
            let firstErrorSection = null;
            REQUIRED_RULES.forEach(function(rule) {
                if (!rule.check()) {
                    showSectionError(rule.section, rule.label);
                    if (!firstErrorSection) firstErrorSection = rule.section;
                }
            });
            return firstErrorSection;
        }

        // Re-validate live once errors are showing, so they clear as the
        // user fixes each section instead of only re-checking on submit.
        $('#jobspec-form').on('change.jobspec input.jobspec', function() {
            if ($('#mp-form-app .mp-field-error').length) {
                validateJobSpecForm();
            }
        });

        // TOC scroll-spy
        const $tocLinks = $('.mp-toc a');
        const sections = $tocLinks.map(function() {
            return document.getElementById($(this).attr('href').substring(1));
        }).get().filter(Boolean);

        $(window).on('scroll', function() {
            let current = sections[0];
            sections.forEach(function(sec) {
                if (sec.getBoundingClientRect().top < 120) current = sec;
            });
            $tocLinks.removeClass('active');
            $tocLinks.filter('[href="#' + current.id + '"]').addClass('active');
        });

        $tocLinks.on('click', function(e) {
            e.preventDefault();
            document.getElementById($(this).attr('href').substring(1)).scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });

        // Enforce max-3 selection on Career Anchor / Motivation, with live counter
        $('[data-max-group]').each(function() {
            const groupName = $(this).data('max-group');
            const $checks = $(this).find('input[type="checkbox"]');
            const $counter = $('.mp-sec-counter[data-counter-for="' + groupName + '"]');

            function updateCounter() {
                const checkedCount = $checks.filter(':checked').length;
                $counter.text(checkedCount + ' / 3').toggleClass('mp-counter-full', checkedCount >= 3);
                $checks.not(':checked').prop('disabled', checkedCount >= 3);
            }
            $checks.on('change', updateCounter);
            updateCounter();
        });

        $('#jobspec-form').on('submit.jobspec', function(e) {
            e.preventDefault();

            const firstErrorSection = validateJobSpecForm();
            if (firstErrorSection) {
                $('#jobspec-err').html('<p style="color:#E14848;">Please fill up all required fields highlighted below.</p>');
                document.getElementById(firstErrorSection).scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                return;
            }

            $('#jobspec-err').empty();
            const formData = new FormData(this);
            $.ajax({
                url: 'jobspec_save',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    const data = typeof res === 'string' ? JSON.parse(res) : res;
                    if (data.success) {
                        window.location = 'dashboard#job-spec';
                    } else {
                        $('#jobspec-err').html('<p style="color:#E14848;">' + (data.error || 'Failed to save.') + '</p>');
                    }
                },
                error: function() {
                    $('#jobspec-err').html('<p style="color:#E14848;">An error occurred. Please try again.</p>');
                }
            });
        });
    });
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>