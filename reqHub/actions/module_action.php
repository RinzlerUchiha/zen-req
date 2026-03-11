<?php
require_once '../includes/auth.php';
requireLogin();

// Only admin can manage modules
if ($_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../includes/db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$module_id = $_POST['module_id'] ?? null;
$name = trim($_POST['name'] ?? '');
$selected_actions = $_POST['selected_actions'] ?? [];

try {
    if ($action === 'deleteModule' && $module_id) {
        $stmt = $pdo->prepare("DELETE FROM module_actions WHERE module_id=?");
        $stmt->execute([$module_id]);
        $stmt = $pdo->prepare("DELETE FROM modules WHERE id=?");
        $stmt->execute([$module_id]);
        echo json_encode(['success'=>true, 'id'=>$module_id]);
        exit;
    }

    if (empty($name)) throw new Exception("Module name required.");

    if ($action === 'editModule' && $module_id) {
        $stmt = $pdo->prepare("UPDATE modules SET name=? WHERE id=?");
        $stmt->execute([$name, $module_id]);

        // Clear old actions
        $stmt = $pdo->prepare("DELETE FROM module_actions WHERE module_id=?");
        $stmt->execute([$module_id]);

        // Insert new actions
        if (!empty($selected_actions)) {
            $stmt = $pdo->prepare("INSERT INTO module_actions (module_id, action_id) VALUES (?,?)");
            foreach($selected_actions as $a){
                $stmt->execute([$module_id, $a]);
            }
        }

        echo json_encode(['success'=>true, 'id'=>$module_id, 'name'=>$name]);
        exit;
    }

    if ($action === 'addModule') {
        $stmt = $pdo->prepare("INSERT INTO modules (name) VALUES (?)");
        $stmt->execute([$name]);
        $module_id = $pdo->lastInsertId();

        // Save selected actions
        if (!empty($selected_actions)) {
            $stmt = $pdo->prepare("INSERT INTO module_actions (module_id,action_id) VALUES (?,?)");
            foreach($selected_actions as $a){
                $stmt->execute([$module_id, $a]);
            }
        }

        echo json_encode(['success'=>true, 'id'=>$module_id, 'name'=>$name]);
        exit;
    }

    echo json_encode(['success'=>false,'message'=>'Invalid action']);
} catch(PDOException $e){
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}