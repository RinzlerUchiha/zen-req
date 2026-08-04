<?php
/**
 * Manpower / HireFlow — Global Header
 *
 * Expects $currentUser, $empno, $manpower_root, $hr_db to already be set
 * by manpower/includes/auth.php (included before this file, typically via
 * manpower/routes/route.php).
 */

if (!isset($currentUser)) {
    require_once dirname(__DIR__) . '/includes/auth.php';
}

$userRole    = $currentUser['manpower_role'] ?? null;
$userName    = $currentUser['name'] ?? 'User';
$roleDisplay = $userRole;

// ============================================================================
// Notifications (mirrors ReqHub's pattern, scoped to the manpower module)
// ============================================================================
$notifications = [];
$unreadCount   = 0;

if (!empty($currentUser['id'])) {
    try {
        $stmt = $hr_db->prepare("
            SELECT id, type, request_id, message, is_read, created_at
            FROM manpower_notifications
            WHERE user_id = :uid AND is_read = 0
            ORDER BY created_at DESC
            LIMIT 20
        ");
        $stmt->bindParam(':uid', $currentUser['id']);
        $stmt->execute();
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $unreadCount   = count($notifications);
    } catch (Exception $e) {
        error_log("Manpower header: failed to fetch notifications - " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>HireFlow — Manpower Requests</title>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

<style>
:root {
  --mp-blue:#2F6FE4; --mp-blue-dark:#1B4FB0;
  --mp-text:#1F2430; --mp-muted:#8A93A3; --mp-border:#E7E9EE;
  --mp-bg:#F5F6F9;
}
* { font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif; }
body { background:var(--mp-bg); }

.mp-navbar {
  background:#FFFFFF; border-bottom:1px solid var(--mp-border);
  box-shadow:0 2px 8px rgba(31,36,48,.04);
  padding:12px 24px;
}
.mp-navbar .navbar-brand {
  font-weight:800; font-size:17px; color:var(--mp-text) !important;
  letter-spacing:-.3px;
}
.mp-navbar .navbar-brand span { color:var(--mp-blue); }
.mp-return-btn {
  border:1px solid var(--mp-border); color:var(--mp-muted); background:#FFF;
  border-radius:8px; padding:6px 14px; font-size:12px; font-weight:600;
  text-decoration:none; transition:all .15s ease;
}
.mp-return-btn:hover { border-color:var(--mp-blue); color:var(--mp-blue); }

.mp-bell-btn {
  border:1px solid var(--mp-border); background:#FFF; color:var(--mp-text);
  border-radius:9px; width:38px; height:38px; position:relative;
  display:flex; align-items:center; justify-content:center;
}
.mp-bell-btn:hover { border-color:var(--mp-blue); }
.mp-notif-badge {
  position:absolute; top:-5px; right:-5px; background:#E14848; color:#FFF;
  border-radius:50%; font-size:10px; font-weight:700; width:17px; height:17px;
  display:flex; align-items:center; justify-content:center;
}

.mp-notif-menu { width:360px; max-height:480px; overflow-y:auto; border:1px solid var(--mp-border); border-radius:12px; box-shadow:0 12px 28px rgba(31,36,48,.12); }
.mp-notif-header { display:flex; justify-content:space-between; align-items:center; padding:12px 16px; border-bottom:1px solid var(--mp-border); font-weight:700; font-size:13px; }
.mp-notif-item { border-bottom:1px solid #F1F2F5; padding:10px 16px; cursor:pointer; transition:background .12s; }
.mp-notif-item:hover { background:#FAFBFF; }
.mp-notif-item.unread { background:#F5F8FF; border-left:3px solid var(--mp-blue); }
.mp-notif-message { font-size:12.5px; color:var(--mp-text); margin-bottom:2px; }
.mp-notif-time { font-size:10.5px; color:var(--mp-muted); }
.mp-notif-empty { text-align:center; color:var(--mp-muted); font-size:12px; padding:32px 16px; }

.mp-user-block { text-align:right; line-height:1.25; }
.mp-user-name { font-size:13.5px; font-weight:700; color:var(--mp-text); }
.mp-user-role { font-size:11px; color:var(--mp-muted); }
.mp-assign-toggle {
  border:1px solid var(--mp-border); background:#FFF; color:var(--mp-muted);
  border-radius:6px; padding:2px 10px; font-size:10.5px; font-weight:600; margin-top:4px;
}
.mp-assign-toggle:hover { border-color:var(--mp-blue); color:var(--mp-blue); }
.mp-assign-card {
  position:absolute; right:0; margin-top:6px; min-width:220px; z-index:1050;
  background:#FFF; border:1px solid var(--mp-border); border-radius:10px;
  box-shadow:0 12px 28px rgba(31,36,48,.12); display:none;
}
.mp-assign-card .mp-assign-title { padding:8px 12px; border-bottom:1px solid var(--mp-border); font-weight:700; font-size:11.5px; }
.mp-assign-card .mp-assign-row { padding:6px 12px; border-bottom:1px solid #F1F2F5; font-size:11.5px; color:var(--mp-text); }

.mp-dev-strip { background:#3A3F4B; color:#FFF; font-size:11.5px; padding:6px 16px; display:flex; align-items:center; gap:10px; }

/* Page content spacing: .page-wrapper/.page-body are not real Bootstrap
   classes (this stack only loads Bootstrap, not Tabler) so they render
   with zero padding of their own, and .container-fluid only adds 12px
   horizontal. Give <main> real breathing room here so every page that
   goes through this header/footer gets consistent spacing without each
   page having to redeclare it. */
main {
  display: block;
  padding: 28px 24px 40px;
  max-width: 1280px;
  margin: 0 auto;
}
@media (max-width: 768px) {
  main { padding: 18px 16px 32px; }
}
</style>
</head>
<body>

<?php if (!empty($currentUser['is_admin_dev'])): ?>
<div class="mp-dev-strip">
    <span>Dev Role:</span>
    <select onchange="mpSwitchRole(this.value)" class="form-select form-select-sm w-auto">
        <option value="Requestor" <?= $userRole === 'Requestor' ? 'selected' : '' ?>>Requestor</option>
        <option value="Approver"  <?= $userRole === 'Approver'  ? 'selected' : '' ?>>Approver</option>
        <option value="HR Head"   <?= $userRole === 'HR Head'   ? 'selected' : '' ?>>HR Head</option>
        <option value="Admin"     <?= $userRole === 'Admin'     ? 'selected' : '' ?>>Admin</option>
    </select>
    <span style="opacity:.7;">Current: <strong style="opacity:1;"><?= htmlspecialchars($roleDisplay) ?></strong></span>
</div>
<script>
function mpSwitchRole(newRole) {
    fetch('<?= $manpower_root ?>/role_switch', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'role=' + encodeURIComponent(newRole)
    })
    .then(r => r.json())
    .then(data => { if (data.success) window.location.reload(); else alert('Failed: ' + (data.message || 'Unknown error')); });
}
</script>
<?php endif; ?>

<nav class="navbar navbar-expand-lg mp-navbar">
    <a class="navbar-brand" href="<?= $manpower_root ?>/dashboard">Hire<span>Flow</span></a>
    <a class="mp-return-btn ms-3" href="/zen/dashboard">Return to ZenHub</a>

    <div class="ms-auto d-flex align-items-center gap-3">

        <div class="dropdown">
            <button class="mp-bell-btn" id="mpNotifBellBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 00-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                <?php if ($unreadCount > 0): ?>
                    <span class="mp-notif-badge"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
                <?php endif; ?>
            </button>

            <div class="dropdown-menu dropdown-menu-end p-0 mp-notif-menu">
                <div class="mp-notif-header">
                    <span>Notifications</span>
                    <?php if ($unreadCount > 0): ?>
                        <button class="btn btn-link btn-sm p-0" id="mpMarkAllReadBtn" style="font-size:11.5px; color:var(--mp-blue);">Mark all as read</button>
                    <?php endif; ?>
                </div>

                <?php if (empty($notifications)): ?>
                    <div class="mp-notif-empty">No new notifications</div>
                <?php else: ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div class="mp-notif-item unread"
                             data-notif-id="<?= $notif['id'] ?>"
                             data-request-id="<?= $notif['request_id'] ?? '' ?>"
                             data-type="<?= htmlspecialchars($notif['type']) ?>">
                            <div class="mp-notif-message"><?= htmlspecialchars($notif['message']) ?></div>
                            <div class="mp-notif-time"><?= date('M d, Y H:i', strtotime($notif['created_at'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="mp-user-block position-relative">
            <div class="mp-user-name">Hello, <?= htmlspecialchars($userName) ?></div>
            <div class="mp-user-role"><?= htmlspecialchars($roleDisplay) ?></div>

            <?php if (in_array($userRole, ['Approver', 'HR Head']) && !empty($currentUser['assigned_departments'])): ?>
                <button id="mpAssignToggleBtn" class="mp-assign-toggle" type="button">View Assignments</button>
                <div id="mpAssignCard" class="mp-assign-card">
                    <div class="mp-assign-title">Assigned Departments</div>
                    <?php foreach ($currentUser['assigned_departments'] as $dept): ?>
                        <div class="mp-assign-row"><?= htmlspecialchars($dept) ?></div>
                    <?php endforeach; ?>
                </div>
                <script>
                    (function() {
                        var btn = document.getElementById('mpAssignToggleBtn');
                        var card = document.getElementById('mpAssignCard');
                        btn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            card.style.display = (card.style.display === 'block') ? 'none' : 'block';
                        });
                        document.addEventListener('click', function() { card.style.display = 'none'; });
                    })();
                </script>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main>