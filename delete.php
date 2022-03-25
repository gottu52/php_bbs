<?php
session_start();
require('library.php');

//sessionが無い場合はエラーでトップページに移動する
if(isset($_SESSION['id']) && isset($_SESSION['name'])) {
    $name = $_SESSION['name'];
    $id = $_SESSION['id'];
} else {
    header('Location: /bbs/login.php');
    exit();
}

// 投稿idをURLから特定
$post_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if(!$post_id) {
    header('Location: /bbs/index.php');
    exit();
}

// データベースを定義
$db = dbconnect();

// 削除処理の実行
$stmt = $db->prepare('delete from posts where id=? and member_id=? limit 1');
if(!$stmt) {
    die($db->error);
};
$stmt->bind_param('ii', $post_id, $id);
$success = $stmt->execute();
if(!$success) {
    die($db->error);
};

header('Location: index.php'); 
exit();
?>
