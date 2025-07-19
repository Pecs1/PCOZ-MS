<?php

require_once __DIR__ . '/../vendor/autoload.php';

$changelogFilePath = __DIR__ . '/../CHANGELOG.md';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$parserer = new Parsedown();

$markdownContent = '';
if (file_exists($changelogFilePath)) {
    $markdownContent = file_get_contents($changelogFilePath);
} else {
    $markdownContent = "# Changelog Not Found\n\nThe `CHANGELOG.md` file could not be found at: `{$changelogFilePath}`.";
    // You might want to log this error in a real application
}
$htmlContent = $parserer->text($markdownContent);
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-locale-key="changelog"></title>
    <link rel="stylesheet" href="/global/css/defaultstyle.css">
    <link rel="stylesheet" href="/global/css/changelog.css">
    <link rel="icon" type="image/x-icon" href="/global/pics/favicon.ico">
</head>

<body>
<div class="navbar">
    <div class="menu">
        <input type="checkbox" id="menu_toggle">
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
    <div class="search_bar">
        <input type="search" id="search_input" class="input" data-locale-key="search-input.placeholder">
    </div>
    <div class="user_info">
        <?php if (isset($_SESSION['valid']) && $_SESSION['valid'] === true): ?>
            <span class="log_in"><?php echo htmlspecialchars($_SESSION['name'] . ' ' . $_SESSION['surname']); ?></span>
        <?php else: ?>
            <a data-locale-key="login" href="login.php" class="log_in"></a>
        <?php endif; ?>
    </div>
</div>

<div class="changelog">
    <h1 data-locale-key="changelog"></h1>
    <?php echo $htmlContent; ?>
</div>
<script src="/global/js/locales.js"></script>
</body>
</html>