<?php
function rebuildAccessTypes(PDO $pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                s.name AS system,
                r.name AS role,
                m.name AS module,
                a.name AS actions
            FROM system_roles sr
            JOIN systems s ON sr.system_id = s.id
            JOIN roles r ON sr.role_id = r.id
            JOIN role_permissions rp ON rp.role_id = r.id AND rp.system_id = s.id
            JOIN modules m ON rp.module_id = m.id
            JOIN actions a ON rp.action_id = a.id
        ");
        $combinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $checkStmt = $pdo->prepare("
            SELECT id FROM access_types 
            WHERE system = ? AND role = ? AND module = ? AND actions = ?
            LIMIT 1
        ");

        $insertStmt = $pdo->prepare("
            INSERT INTO access_types (system, role, module, actions)
            VALUES (?, ?, ?, ?)
        ");

        $inserted = 0;
        foreach ($combinations as $combo) {
            $checkStmt->execute([$combo['system'], $combo['role'], $combo['module'], $combo['actions']]);
            if (!$checkStmt->fetch()) {
                $insertStmt->execute([$combo['system'], $combo['role'], $combo['module'], $combo['actions']]);
                $inserted++;
            }
        }

        error_log("rebuildAccessTypes: inserted $inserted new combinations");
        return true;
    } catch (Exception $e) {
        error_log("rebuildAccessTypes error: " . $e->getMessage());
        return false;
    }
}
?>