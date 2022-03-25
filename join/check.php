<?php
require('../library.php');
// セッション開始
session_start();

if(isset($_SESSION['form'])) {
	//セッションから値を取り出す
	$form = $_SESSION['form'];
} else {
	// セッションが無い場合はホームへ移動
	header('Location: index.php');
}

// POSTメソッドを実行した場合
if($_SERVER['REQUEST_METHOD'] === 'POST') {

	// データベースを定義
	$db = dbconnect();

	// 接続したデータベースにデータを挿入する準備
	$stmt = $db->prepare('insert into members (name, email, password, picture) VALUES (?, ?, ?, ?)');
	if(!$stmt) {
		die($db->error);
	};
	// パスワードを入れる場所を作成
	// password_hash = パスワードをランダムに暗号化してデータベースに保存
	$password = password_hash($form['password'], PASSWORD_DEFAULT);
	// セッションの値をデータベースへ
	$stmt->bind_param('ssss', $form['name'], $form['email'], $password, $form['image']);
	// 実行し、失敗したらエラー
	$success = $stmt->execute();
	if(!$success) {
		die($db->error);
	};

	// セッションをリセットし、ページを遷移
	unset($_SESSION['form']);
	header('Location: thanks.php');
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>会員登録</title>

	<link rel="stylesheet" href="../style.css" />
</head>

<body>
	<div id="wrap">
		<div id="head">
			<h1>会員登録</h1>
		</div>

		<div id="content">
			<p>記入した内容を確認して、「登録する」ボタンをクリックしてください</p>
			<form action="" method="post">
				<dl>
					<dt>ニックネーム</dt>
					<dd><?php echo h($form['name']); ?></dd>
					<dt>メールアドレス</dt>
					<dd><?php echo h($form['email']); ?></dd>
					<dt>パスワード</dt>
					<dd>
						【表示されません】
					</dd>
					<dt>写真など</dt>
					<dd>
						<img src="../member_picture/<?php echo h($form['image']); ?>" width="100" alt="" />
					</dd>
				</dl>
				<!-- URLパラメータを付与(action=rewrite) -->
				<div><a href="index.php?action=rewrite">&laquo;&nbsp;書き直す</a> | <input type="submit" value="登録する" /></div>
			</form>
		</div>

	</div>
</body>

</html>