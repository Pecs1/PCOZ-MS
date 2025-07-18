<?php
require_once '/../config.php'; // Include your database configuration file

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user_role = get_user_role();

$allowed_roles = ['student', 'teacher', 'admin'];

if (!in_array($user_role, $allowed_roles)) {
    die("You do not have permission to upload files.");
}

$upload_message = "";
$private_upload_dir= 'uploads/'; // Make sure this is set to a secure directory outside of the web root

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
  $user_provided_name = trim($_POST['user_provided_name'] ?? '');
  $description = trim($_POST['description'] ?? '');

  if (empty($user_provided_name) {
    $upload_message = 
  })
}
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$supportedFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

// Check if file is a actual file or fake file
if(isset($_POST["submit"])) {
  $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
  if($check !== false) {
    echo "File is - " . $check["mime"] . ".";
    $uploadOk = 1;
  } else {
    echo "File is not real.";
    $uploadOk = 0;
  }
}

// Check if file already exists
if (file_exists($target_file)) {
  echo "Sorry, file already exists.";
  $uploadOk = 0;
}

// Check file size
if ($_FILES["fileToUpload"]["size"] > 500000) {
  echo "Sorry, your file is too large.";
  $uploadOk = 0;
}

// Allow certain file formats
if($supportedFileType != "pdf" && $supportedFileType != "zip" && $supportedFileType != "rar"
&& $supportedFileType != "7zip" ) {
  echo "Sorry, only PDF, ZIP, RAR & 7ZIP files are allowed.";
  $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
  echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
  if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
  } else {
    echo "Sorry, there was an error uploading your file.";
  }
}
?>
