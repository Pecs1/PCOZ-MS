<?php
// Include the language file and database configuration
require_once __DIR__ . '/config.php';

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
        
        if (!$email) {
            $msg = $translations[109]['error'] ?? $translations[110]['error'];
        } elseif ($password !== $confirm_password) {
            $msg = $translations[111]['error'] ?? $translations[112]['error'];
        } elseif (strlen($password) < 6 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $msg = $translations[113]['error'] ?? $translations[114]['error'];
        } else {
            // Check if the personal ID or email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users1 WHERE personal_id = :personal_id OR email = :email");
            $stmt->execute(['personal_id' => $personal_id, 'email' => $email]);
            if ($stmt->fetchColumn() > 0) {
                $msg = $translations[115]['error'] ?? $translations[116]['error'];
            } else {
                // Hash the password and insert the user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users1 (personal_id, name, surname, email, preferred_lang, password) 
                                       VALUES (:personal_id, :first_name, :last_name, :email, :lang, :password)");
                $stmt->execute([
                    'personal_id' => $personal_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'lang' => $preferred_lang,
                    'password' => $hashed_password
                ]);
                $success = $translations[117]['error'] ?? $translations[118]['error']; // not really an error
            }
        }
    } catch (PDOException $e) {
        $msg = $translations[119]['error'] ?? $translations[120]['error'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($translations[11]['common'] ?? $translations[12]['common']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="nav_bar">
    <input type="checkbox" id="menu_toggle" class="menu_toggle">
    <label for="menu_toggle" class="menu_icon">
        <img src="assets/menu.png" alt="Menu">
    </label>
    <div class="dropdown_menu">
        <a href="index.php"><?php echo htmlspecialchars($translations[5]['common'] ?? $translations[6]['common']); ?></a>
        <a href="faq.php"><?php echo htmlspecialchars($translations[7]['common'] ?? $translations[8]['common']); ?></a>
        <a href="login.php"><?php echo htmlspecialchars($translations[3]['common'] ?? $translations[4]['common']); ?></a>
    </div>
    <a href="login.php" class="log_in"><?php echo htmlspecialchars($translations[3]['common'] ?? $translations[4]['common']); ?></a>
</div>
<div class="signup_form">
    <h2><?php echo htmlspecialchars($translations[11]['common'] ?? $translations[12]['common']); ?></h2>
    <form method="POST" action="signup.php">

        <label for="first_name" class="label"><?php echo htmlspecialchars($translations[21]['common'] ?? $translations[22]['common']); ?>*:</label>
        <input type="text" id="first_name" name="first_name" class="input" required>

        <label for="last_name" class="label"><?php echo htmlspecialchars($translations[25]['common'] ?? $translations[26]['common']); ?>*:</label>
        <input type="text" id="last_name" name="last_name" class="input" required>

        <label for="email" class="label"><?php echo htmlspecialchars($translations[19]['common'] ?? $translations[20]['common']); ?>*:</label>
        <input type="email" id="email" name="email" class="input" required>

        <label for="personal_id" class="label"><?php echo htmlspecialchars($translations[15]['common'] ?? $translations[16]['common']); ?>*:</label>
        <input type="text" id="personal_id" name="personal_id" class="input" required>

        <label for="password" class="label"><?php echo htmlspecialchars($translations[13]['common'] ?? $translations[14]['common']); ?>*:</label>
        <input type="password" id="password" name="password" class="input" required>

        <label for="confirm_password" class="label"><?php echo htmlspecialchars($translations[17]['common'] ?? $translations[18]['common']); ?>*:</label>
        <input type="password" id="confirm_password" name="confirm_password" class="input" required>

        <button type="submit" class="button"><?php echo htmlspecialchars($translations[11]['common'] ?? $translations[12]['common']); ?></button>
        <button type="reset" class="warn_button"><?php echo htmlspecialchars($translations[27]['common'] ?? $translations[28]['common']); ?></button>
        <a href="login.php" class="links"><?php echo htmlspecialchars($translations[3]['common'] ?? $translations[4]['common']); ?></a>
    </form>
    <?php if ($msg) echo "<p class='error'>$msg</p>"; ?>
    <?php if ($success) echo "<p class='success'>$success</p>"; ?>
</div>
</body>
</html>