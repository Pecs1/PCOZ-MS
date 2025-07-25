<?php

// Include the language and shared content logic
require_once __DIR__ . '/../config.php';

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

// --- PHP LOGIC FOR LOCALES AND TRANSLATIONS ---
$default_locale = 'sk';

// 1. Determine the current locale for PHP.
//    Priority: POST (if form submitted) > SESSION (if previously set) > DEFAULT
$current_locale = $_POST['current_locale'] ?? $_SESSION['preferred_locale'] ?? $default_locale;

// Basic validation to prevent arbitrary file access
if (!in_array($current_locale, ['en', 'sk'])) {
    $current_locale = $default_locale; // Fallback to default if invalid locale is provided
}

// 2. Store the determined locale in session for future requests
$_SESSION['preferred_locale'] = $current_locale;

error_log("Determined current_locale: " . $current_locale); // Log the determined locale

// Path to your language files
$lang_file_path = __DIR__ . "/global/lang/{$current_locale}.json";

$php_translations = [];
if (file_exists($lang_file_path)) {
    $json_content = file_get_contents($lang_file_path);
    $php_translations = json_decode($json_content, true); // true for associative array
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error decoding {$current_locale}.json: " . json_last_error_msg());
        $php_translations = []; // Fallback to empty translations
    }
} else {
    error_log("Language file not found: {$lang_file_path}");
}
// --- END LOCALE PHP LOGIC ---


$msg = ''; // Initialize $msg for the current request

// Retrieve error message from session if it exists from a previous POST request
if (isset($_SESSION['error_message'])) {
    $msg = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear it after retrieving to display only once
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    try {


    // Sanitize input
    $personal_id = filter_var($_POST['username'], FILTER_SANITIZE_NUMBER_INT);
    $password = $_POST['password'];

    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        // Use translation key 'invalid-csrf-token' from loaded JSON
        $msg = htmlspecialchars($php_translations['errors']['invalid-csrf-token'] ?? 'Invalid CSRF token.');

    } else {
        // Fetch user data
        $stmt = $pdo->prepare("SELECT name, surname, password, failed_attempts, last_attempt_time FROM users WHERE personal_id = :personal_id");
        $stmt->execute(['personal_id' => $personal_id]);
        $user = $stmt->fetch();

        if ($user) {
            $lockout_time = 300; // 5 minutes
            $time_since_last_attempt = time() - strtotime($user['last_attempt_time']);

            if ($user['failed_attempts'] >= 5 && $time_since_last_attempt < $lockout_time) {
                // Use translation key 'too-many-attempts' from loaded JSON
                $msg = htmlspecialchars($php_translations['errors']['too-many-attempts'] ?? 'Too many failed attempts. Please try again later.');

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
                    // Use translation key 'invalid-id-password' from loaded JSON
                    $msg = htmlspecialchars($php_translations['errors']['invalid-id-password'] ?? 'Invalid Personal ID or Password.');
                    }
                }
            } else {
                // User not found
                // Use translation key 'invalid-id-password' from loaded JSON
                $msg = htmlspecialchars($php_translations['errors']['invalid-id-password'] ?? 'Invalid Personal ID or Password.');
            }
        }
    } catch (PDOException $e) {
        // Use translation key 'database-error' from loaded JSON
        $msg = htmlspecialchars($php_translations['errors']['database-error'] ?? 'Database error :(');
        // In a real application, you might log $e->getMessage() for debugging
        error_log("Database error in login.php: " . $e->getMessage());
    }

    // Store the error message in the session if it's not empty,
    // so it persists across the redirect
    if (!empty($msg)) {
        $_SESSION['error_message'] = $msg;
    }

    // IMPORTANT: Redirect to prevent form resubmission on browser refresh.
    // This also ensures the page is reloaded as a GET request, picking up the latest session locale.
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-locale-key="login"></title>
    <link rel="stylesheet" href="/global/css/defaultstyle.css">
    <link rel="stylesheet" href="/global/css/auth.css">
    <link rel="icon" type="image/x-icon" href="/global/pics/favicon.ico">
</head>
<body>
<div class="navbar">
    <div class="menu">
        <input type="checkbox" id="menu_toggle" class="menu_toggle">
        <label for="menu_toggle" class="menu_icon">
            <img src="/global/pics/menu.png" alt="Menu">
        </label>
            <div class="dropdown_menu">
                <a data-locale-key="home" href="/"></a>
                <a data-locale-key="faq-short" href="faq.php"></a>
                <?php if (isset($_SESSION['valid']) && $_SESSION['valid'] === true): ?>
                    <a data-locale-key="logout" href="logout.php"></a>
                <?php else: ?>
                    <a data-locale-key="login" href="login.php"></a>
                <?php endif; ?>
                <a data-locale-key="changelog" href="changes.php"></a>
                <select data-locale-switcher class="locale_switcher">
                    <option value="sk">Slovenčina</option>
                    <option value="en">English</option>
                </select>
            </div>
    </div>
</div>

<div class="login_form">
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <div>
            <label for="username" class="label" data-locale-key="login-credentials"></label>
            <input type="text" class="input" name="username" id="username" required>
        </div>
        <div>
            <label for="password" class="label" data-locale-key="password"></label>
            <input type="password" class="input" name="password" id="password" required>
        </div>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="current_locale" id="current_locale_input" value="<?php echo htmlspecialchars($current_locale); ?>">
        <button type="submit" class="button" name="login" data-locale-key="login"></button>
    </form>
    <p class="error-message"><?php echo htmlspecialchars($msg ?? ''); ?></p>
    <a data-locale-key="register" href="signup.php" class="links"></a>
</div>
<script src="/global/js/locales.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const localeInput = document.getElementById('current_locale_input');
        const savedLocale = localStorage.getItem('preferredLocale');
        if (localeInput && savedLocale) {
            localeInput.value = savedLocale;
        }
    });
</script>
</body>
</html>