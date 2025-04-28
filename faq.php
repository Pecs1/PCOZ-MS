<?php
// Include the language and shared content logic
require_once __DIR__ . '/language.php';
?>

<!DOCTYPE html>
<html lang="<?php echo $userLanguage; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PČOZ-MS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="nav_bar">
        <input type="checkbox" id="menu_toggle" class="menu_toggle">
        <label for="menu_toggle" class="menu_icon">
            <img src=".github/assets/menu.png" alt="Menu">
        </label>
        <div class="dropdown_menu">
            <a href="index.php"><?php echo htmlspecialchars($translations[5]['common'] ?? $translations[6]['common']); ?></a>
            <a href="faq.php"><?php echo htmlspecialchars($translations[7]['common'] ?? $translations[8]['common']); ?></a>
            <a href="login.php"><?php echo htmlspecialchars($translations[3]['common'] ?? $translations[4]['common']); ?></a>
            </div>
        <div class="search_bar">
            <input type="search" id="search_input" class="search_bar_input" placeholder="<?php echo htmlspecialchars($translations[1]['common'] ?? $translations[2]['common']); ?>">
        </div>
        <a href="login.php" class="log_in"><?php echo htmlspecialchars($translations[3]['common'] ?? $translations[4]['common']); ?></a>
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