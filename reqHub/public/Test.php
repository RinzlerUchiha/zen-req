<?php
// require_once __DIR__ . '/../includes/auth.php';
?>
<!DOCTYPE html>
<html>
<head><title>ReqHub Auth Test</title></head>
<body>
    <h1>✅ Authentication Test</h1>
    
    <?php if ($currentUser): ?>
        <h2>You are authenticated!</h2>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($currentUser['name']); ?></p>
        <p><strong>Employee ID:</strong> <?php echo htmlspecialchars($currentUser['emp_no']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($currentUser['email']); ?></p>
        <p><strong>Role:</strong> <strong style="color: green;"><?php echo htmlspecialchars($currentUser['reqhub_role']); ?></strong></p>
        <p><strong>Active:</strong> <?php echo $currentUser['is_active'] ? 'Yes ✓' : 'No ✗'; ?></p>
        
        <hr>
        
        <h2>Helper Functions Test</h2>
        <p>getUserName(): <?php echo getUserName(); ?></p>
        <p>getUserEmpNo(): <?php echo getUserEmpNo(); ?></p>
        <p>getUserRole(): <?php echo getUserRole(); ?></p>
        <p>isAdmin(): <?php echo isAdmin() ? 'Yes' : 'No'; ?></p>
        <p>userHasRole('Approver'): <?php echo userHasRole('Approver') ? 'Yes' : 'No'; ?></p>
        
        <hr>
        <p><a href="logout.php">Logout</a></p>
    <?php else: ?>
        <p style="color: red;">❌ Not authenticated!</p>
    <?php endif; ?>
    
</body>
</html>