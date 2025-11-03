<?php
// index.php
// Minimal PHP app — intentionally vulnerable (SQLi).
// Run with: php -S 127.0.0.1:8080

// Initialize SQLite DB if missing
$dbfile = __DIR__ . '/vuln_php.db';
if (!file_exists($dbfile)) {
    $db = new PDO("sqlite:$dbfile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, password TEXT)");
    $db->exec("INSERT INTO users (username,password) VALUES ('admin','admin123')");
    $db = null;
}

function query_db($q) {
    $db = new PDO("sqlite:" . __DIR__ . "/vuln_php.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $res = $db->query($q);
    return $res ? $res->fetchAll(PDO::FETCH_ASSOC) : [];
}

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    // INTENTIONALLY vulnerable — direct interpolation into SQL
    $sql = "SELECT * FROM users WHERE username = '$user' AND password = '$pass'";
    try {
        $rows = query_db($sql);
        if (count($rows) > 0) {
            $msg = "Welcome " . htmlspecialchars($rows[0]['username']);
        } else {
            $msg = "Invalid credentials";
        }
    } catch (Exception $e) {
        $msg = "DB error: " . $e->getMessage();
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Vulnerable PHP App</title></head>
<body>
<h2>Vulnerable PHP Login</h2>
<form method="post">
  Username: <input name="username"><br>
  Password: <input name="password" type="password"><br>
  <input type="submit" value="Login">
</form>
<p><?php echo $msg; ?></p>
<p>Try SQLi: username = ' OR '1'='1</p>
</body>
</html>
