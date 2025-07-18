<?php

// Include the database configuration (assuming it defines DB_HOST, DB_USER, etc.)
require_once __DIR__ . '/../config.php';

// Check for existing user session (e.g., if already logged in, redirect)
if (isset($_SESSION['valid']) && $_SESSION['valid'] === true) {
    header("Location: index.php"); // Redirect to index if already logged in
    exit;
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

error_log("Determined current_locale for signup: " . $current_locale);

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
// --- END PHP LOCALES LOGIC ---

$msg = '';     // Initialize $msg for current request
$success = ''; // Initialize $success for current request

// Retrieve messages from session if they exist from a previous POST request
if (isset($_SESSION['error_message_signup'])) {
    $msg = $_SESSION['error_message_signup'];
    unset($_SESSION['error_message_signup']); // Clear it after retrieving to display only once
}
if (isset($_SESSION['success_message_signup'])) {
    $success = $_SESSION['success_message_signup'];
    unset($_SESSION['success_message_signup']); // Clear it after retrieving to display only once
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Sanitize and validate input
        $first_name = filter_var($_POST['first_name'], FILTER_SANITIZE_STRING);
        $last_name = filter_var($_POST['last_name'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $personal_id = filter_var($_POST['personal_id'], FILTER_SANITIZE_NUMBER_INT);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate CSRF token
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $msg = htmlspecialchars($php_translations['errors']['invalid-csrf-token'] ?? 'Invalid CSRF token.');
        } else if (!$email) {
            $msg = htmlspecialchars($php_translations['errors']['invalid-email'] ?? 'Invalid email address.');
        } elseif ($password !== $confirm_password) {
            $msg = htmlspecialchars($php_translations['errors']['passwords-do-not-match'] ?? 'Passwords do not match.');
        } elseif (strlen($password) < 6 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $msg = htmlspecialchars($php_translations['errors']['password-requirements'] ?? 'Password must have 6 characters and contain at least one uppercase letter and a number.');
        } else {
            // Check if the personal ID or email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE personal_id = :personal_id OR email = :email");
            $stmt->execute(['personal_id' => $personal_id, 'email' => $email]);
            if ($stmt->fetchColumn() > 0) {
                $msg = htmlspecialchars($php_translations['errors']['id-email-exists'] ?? 'Personal ID or email already exists.');
            } else {
                // Hash the password and insert the user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (personal_id, name, surname, email, preferred_lang, password) 
                                       VALUES (:personal_id, :first_name, :last_name, :email, :lang, :password)");
                $stmt->execute([
                    'personal_id' => $personal_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'lang' => $current_locale, // Use the dynamically determined locale
                    'password' => $hashed_password
                ]);
                $success = htmlspecialchars($php_translations['errors']['account-created-success'] ?? 'Account created successfully.');
            }
        }
    } catch (PDOException $e) {
        $msg = htmlspecialchars($php_translations['errors']['database-error'] ?? 'Database error :(');
        error_log("Database error in signup.php: " . $e->getMessage()); // Log database errors
    }

    // Store messages in session before redirecting (PRG pattern)
    if (!empty($msg)) {
        $_SESSION['error_message_signup'] = $msg;
    }
    if (!empty($success)) {
        $_SESSION['success_message_signup'] = $success;
    }

    // Redirect to prevent form resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-locale-key="register"></title>
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
    <div class="user_info">
        <a data-locale-key="login" href="login.php" class="log_in"></a>
    </div>
</div>

<div class="signup_form">
    <h2 data-locale-key="register"></h2>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <label for="first_name" class="label" data-locale-key="first-name"></label>
        <input type="text" id="first_name" name="first_name" class="input" required>

        <label for="last_name" class="label" data-locale-key="surname"></label>
        <input type="text" id="last_name" name="last_name" class="input" required>

        <label for="email" class="label" data-locale-key="email"></label>
        <input type="email" id="email" name="email" class="input" required>

        <label for="personal_id" class="label" data-locale-key="personal-id"></label>
        <input type="text" id="personal_id" name="personal_id" class="input" required>

        <label for="password" class="label" data-locale-key="password"></label>
        <input type="password" id="password" name="password" class="input" required>

        <label for="confirm_password" class="label" data-locale-key="confirm-password"></label>
        <input type="password" id="confirm_password" name="confirm_password" class="input" required>

        <button type="submit" class="button" data-locale-key="register"></button>
        <button type="reset" class="warn_button" data-locale-key="reset-info"></button>
        <a href="login.php" class="links" data-locale-key="login"></a>
    </form>
    <?php if ($msg) echo "<p class='error'>$msg</p>"; ?>
    <?php if ($success) echo "<p class='success'>$success</p>"; ?>
</div>
<script src="/global/js/locales.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const localeInput = document.getElementById('current_locale_input');
        const savedLocale = localStorage.getItem('preferredLocale');
        if (localeInput && savedLocale) {
            localeInput.value = savedLocale;
        }
        // Also update the hidden input when the locale switcher changes (redundant if location.reload() is used, but good for completeness)
        const localeSwitcher = document.querySelector('[data-locale-switcher]');
        if (localeInput && localeSwitcher) {
            localeSwitcher.addEventListener('change', (event) => {
                localeInput.value = event.target.value;
            });
        }
    });
</script>
</body>
</html>