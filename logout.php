<?php
session_start();

// セッションをリセット(ログアウト)
unset($_SESSION['id']);
unset($_SESSION['name']);

header('Location: login.php'); exit();
?>
