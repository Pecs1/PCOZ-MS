<?php
// Include the language and shared content logic
require_once __DIR__ . '/config.php';
?>

<!DOCTYPE html>
<html lang="<?php echo $userLanguage; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PČOZ-MS</title>
    <link rel="stylesheet" href="/global/css/defaultstyle.css">
    <link rel="stylesheet" href="/global/css/faq.css">
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
        <?php if (isset($_SESSION['valid']) && $_SESSION['valid'] === true): ?>
            <a href="logout.php"><?php echo htmlspecialchars($translations[33]['common'] ?? $translations[34]['common']); ?></a>
        <?php else: ?>
            <a href="login.php"><?php echo htmlspecialchars($translations[3]['common'] ?? $translations[4]['common']); ?></a>
        <?php endif; ?>
    </div>
        <div class="search_bar">
            <input type="search" id="search_input" class="input" placeholder="<?php echo htmlspecialchars($translations[1]['common'] ?? $translations[2]['common']); ?>">
        </div>
        <?php if (isset($_SESSION['valid']) && $_SESSION['valid'] === true): ?>
            <span class="log_in"><?php echo htmlspecialchars($_SESSION['name'] . ' ' . $_SESSION['surname']); ?></span>
        <?php else: ?>
            <a href ="login.php" class="log_in"><?php echo htmlspecialchars($translations[3]['common'] ?? $translations[4]['common']); ?></a>
        <?php endif; ?>
    </div>

    <div class="faq">
        <h1><?php echo htmlspecialchars($translations[9]['common'] ?? $translations[10]['common']); ?></h1>
        <?php
        foreach ($translations as $id => $translation) {
            if (!empty($translation['faq_question']) && !empty($translation['faq_answer'])) {
                echo "<details>";
                echo "<summary>" . htmlspecialchars($translation['faq_question']) . "</summary>";
                echo "<p>" . htmlspecialchars($translation['faq_answer']) . "</p>";
                echo "</details>";
            }
        }
        ?>
    </div>
</body>
</html>