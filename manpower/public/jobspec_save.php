<?php
/**
 * Job Specification — Save Handler (HireFlow)
 *
 * File: manpower/public/jobspec_save.php
 *
 * Purpose: AJAX endpoint for manpower_jobspec_form.php. Inserts or updates
 * a single tbl_jobspec row. Reads column names, option lists, and the
 * multi-value delimiter from manpower_jobspec_config.php — never hardcodes
 * table/column names here, so retargeting the table only touches the config.
 */

header('Content-Type: application/json');

require_once dirname(__DIR__) . '/includes/auth.php';
requireRoleIn('Requestor', 'Approver', 'HR Head', 'Admin');

require_once dirname(__DIR__) . '/includes/manpower_jobspec_config.php';

function jspec_fail($msg, $code = 400)
{
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jspec_fail('Invalid request method.', 405);
}

// Fields stored as single scalar DB columns, taken directly from POST.
$scalarFields = [
    'department', 'section', 'position', 'headcount', 'sex',
    'emplstat', 'duties', 'techcompetencies', 'competencies', 'otherskill',
    'mpa', 'mpc', 'mpd', 'mpe', 'mpg', 'reason', 'remarks',
];

// Fields that come from a single checkbox/radio group but are split across
// two sub-groups in the form (e.g. mpb1+mpb2 both feed the 'mpb' column).
$mergedRadioFields = [
    'mpb' => ['mpb1', 'mpb2'],
    'mpf' => ['mpf1', 'mpf2'],
    'tapt' => ['tapt1', 'tapt2', 'tapt3', 'tapt4'],
];

// Fields submitted as arrays (checkbox groups), joined with MP_JOBSPEC_DELIM.
$multiFields = [
    'education', 'workexp', 'computerskill', 'enneagram', 'learnstyle',
    'career', 'motivation', 'personality', 'ravenl', 'ravena', 'ravenh',
];

if (empty($_POST['department']) || empty($_POST['position'])) {
    jspec_fail('Department and Position are required.');
}

$data = [];

foreach ($scalarFields as $field) {
    $data[$field] = trim($_POST[$field] ?? '');
}

// Age Range is entered as two separate fields but stored in one column
$ageMin = trim($_POST['agerange_min'] ?? '');
$ageMax = trim($_POST['agerange_max'] ?? '');
$data['agerange'] = ($ageMin !== '' || $ageMax !== '') ? "$ageMin-$ageMax" : '';

foreach ($mergedRadioFields as $column => $groups) {
    $parts = [];
    foreach ($groups as $g) {
        if (!empty($_POST[$g])) {
            $parts[] = trim($_POST[$g]);
        }
    }
    $data[$column] = implode(MP_JOBSPEC_DELIM, $parts);
}

foreach ($multiFields as $field) {
    $values = $_POST[$field] ?? [];
    if (!is_array($values)) {
        $values = [];
    }
    $values = array_map('trim', array_filter($values, 'strlen'));
    $data[$field] = implode(MP_JOBSPEC_DELIM, $values);
}

// Headcount must be numeric or empty (column is standalone, no FK per prior confirmation)
if ($data['headcount'] !== '' && !ctype_digit((string) $data['headcount'])) {
    jspec_fail('Headcount must be a whole number.');
}

$id = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int) $_POST['id'] : null;

try {
    $idCol = MP_JOBSPEC_COLUMNS['id'];
    $ownerCol = MP_JOBSPEC_COLUMNS['created_by'];

    if ($id) {
        // Ownership check — a spec with no recorded owner (created before
        // ownership tracking existed) stays editable by everyone. Otherwise
        // only the creator may update it. This mirrors the read-only check
        // in manpower_jobspec_form.php, but is enforced here server-side
        // since the form's "inert" state is only a UI convenience.
        $ownerStmt = $hr_db->prepare("SELECT $ownerCol FROM " . MP_JOBSPEC_TABLE . " WHERE $idCol = :id LIMIT 1");
        $ownerStmt->bindValue(':id', $id, PDO::PARAM_INT);
        $ownerStmt->execute();
        $existing = $ownerStmt->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            jspec_fail('Job specification not found.', 404);
        }
        if (!empty($existing[$ownerCol]) && $existing[$ownerCol] !== $empno) {
            jspec_fail('You do not have permission to edit this job specification.', 403);
        }

        // UPDATE — build SET clause dynamically from config column map
        $setParts = [];
        foreach ($data as $key => $val) {
            $col = MP_JOBSPEC_COLUMNS[$key];
            $setParts[] = "$col = :$key";
        }
        $sql = "UPDATE " . MP_JOBSPEC_TABLE . " SET " . implode(', ', $setParts) . " WHERE $idCol = :id";
        $stmt = $hr_db->prepare($sql);
        foreach ($data as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['success' => true, 'id' => $id, 'mode' => 'update']);
    } else {
        // INSERT — record the creator so future edits can be restricted to them
        $data['created_by'] = $empno;

        $cols = [];
        $placeholders = [];
        foreach ($data as $key => $val) {
            $cols[] = MP_JOBSPEC_COLUMNS[$key];
            $placeholders[] = ":$key";
        }
        $sql = "INSERT INTO " . MP_JOBSPEC_TABLE . " (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $hr_db->prepare($sql);
        foreach ($data as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }
        $stmt->execute();

        echo json_encode(['success' => true, 'id' => (int) $hr_db->lastInsertId(), 'mode' => 'insert']);
    }
} catch (PDOException $e) {
    error_log('[jobspec_save] ' . $e->getMessage());
    jspec_fail('A database error occurred while saving.', 500);
}