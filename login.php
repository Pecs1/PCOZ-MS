<?php
// Include the language and shared content logic
require_once __DIR__ . '/config.php';

// Refresh session timeout on activity
if (isset($_SESSION['timeout']) && (time() - $_SESSION['timeout'] > $config['session']['timeout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
} else {
    $_SESSION['timeout'] = time();
}

if (isset($_SESSION['valid']) && $_SESSION['valid'] === true) {
    header("Location: index.php");
    exit;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Sanitize input
        $personal_id = filter_var($_POST['username'], FILTER_SANITIZE_NUMBER_INT);
        $password = $_POST['password'];

        // Validate CSRF token
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            $msg = htmlspecialchars($translations[101]['error'] ?? $translations[102]['error']);
        } else {
            // Fetch user data
            $stmt = $pdo->prepare("SELECT name, surname, password, failed_attempts, last_attempt_time FROM users WHERE personal_id = :personal_id");
            $stmt->execute(['personal_id' => $personal_id]);
            $user = $stmt->fetch();

            if ($user) {
                $lockout_time = 300; // 5 minutes
                $time_since_last_attempt = time() - strtotime($user['last_attempt_time']);

                if ($user['failed_attempts'] >= 5 && $time_since_last_attempt < $lockout_time) {
                    $msg = htmlspecialchars($translations[103]['error'] ?? $translations[104]['error']);
                } else {
                    // Verify password
                    if (password_verify($password, $user['password'])) {
                        session_regenerate_id(true);
                        $_SESSION['valid'] = true;
                        $_SESSION['timeout'] = time();
                        $_SESSION['personal_id'] = $personal_id;
                        $_SESSION['name'] = $user['name'];
                        $_SESSION['surname'] = $user['surname'];

                        // Reset failed attempts on successful login
                        $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0 WHERE personal_id = :personal_id");
                        $stmt->execute(['personal_id' => $personal_id]);

                        header("Location: index.php");
                        exit;
                    } else {
                        // Increment failed attempts
                        $stmt = $pdo->prepare("UPDATE users SET failed_attempts = failed_attempts + 1, last_attempt_time = NOW() WHERE personal_id = :personal_id");
                        $stmt->execute(['personal_id' => $personal_id]);
                        $msg = htmlspecialchars($translations[105]['error'] ?? $translations[106]['error']);
                    }
                }
            } else {
                $msg = htmlspecialchars($translations[105]['error'] ?? $translations[106]['error']);
            }
        }
    } catch (PDOException $e) {
        $msg = htmlspecialchars($translations[107]['error'] ?? $translations[108]['error']);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($translations[3]['common'] ?? $translations[4]['common']); ?></title>
    <link rel="stylesheet" href="/global/css/defaultstyle.css">
    <link rel="stylesheet" href="/global/css/authentication.css">
</head>
<body>
<div class="nav_bar">
    <input type="checkbox" id="menu_toggle" class="menu_toggle">
    <label for="menu_toggle" class="menu_icon">
        <img src="/global/pics/menu.png" alt="Menu">
    </label>
    <div class="dropdown_menu">
        <a href="/"><?php echo htmlspecialchars($translations[5]['common'] ?? $translations[6]['common']); ?></a>
        <a href="faq.php"><?php echo htmlspecialchars($translations[7]['common'] ?? $translations[8]['common']); ?></a>
        <a href="login.php"><?php echo htmlspecialchars($translations[3]['common'] ?? $translations[4]['common']); ?></a>
    </div>
</div>
<div class="login_form">
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <div>
            <label for="username" class="label"><?php echo htmlspecialchars($translations[31]['common'] ?? $translations[32]['common']); ?></label>
            <input type="text" class="input" name="username" id="username" required>
        </div>
        <div>
            <label for="password" class="label"><?php echo htmlspecialchars($translations[13]['common'] ?? $translations[14]['common']); ?></label>
            <input type="password" class="input" name="password" id="password" required>
        </div>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <button type="submit" class="button" name="login"><?php echo htmlspecialchars($translations[3]['common'] ?? $translations[4]['common']); ?></button>
    </form>
    <p><?php echo htmlspecialchars($msg ?? ''); ?></p>
    <a href="signup.php" class="links"><?php echo htmlspecialchars($translations[11]['common'] ?? $translations[12]['common']); ?></a>
</div>
</body>
</html>