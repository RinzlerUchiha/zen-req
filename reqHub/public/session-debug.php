<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head><title>Session Debug</title></head>
<body>
<h1>ZenHub Session Variables</h1>
<pre><?php var_dump($_SESSION); ?></pre>

<h2>Instructions:</h2>
<ol>
<li>Log out first: <a href="/zen/logout.php">Logout</a></li>
<li>Log in to ZenHub: <a href="/zen/login.php">Login</a></li>
<li>Then come back to this page</li>
<li>Take a screenshot of what you see above</li>
<li>Note the exact key names in the session (e.g., 'user_id', 'HR_UID', etc.)</li>
</ol>
</body>
</html>