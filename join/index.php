<?php
require('../library.php');
// セッション開始
session_start();

// $_GETでURLパラメータを取得
if(isset($_GET['action']) && $_GET['action'] === 'rewrite' && isset($_SESSION['form'])) {
    $form = $_SESSION['form'];
} else {
    // 配列の初期化
    $form = [
        'name' => '',
        'email' => '',
        'password' => '',
    ];
}
$error=[];

// POSTメソッドを実行した時のみ、処理を行う
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    //postメソッドで送られてきたnameの値をフィルタリングして取得し、配列に入れる
    //FILTER_SANITIZE_STRINGでタグを取り除く
    $form['name'] = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    // $formが空の場合、エラー
    if($form['name'] === '') {
        // エラーの情報を記録
        $error['name'] = 'blank';
    }    
    // emailも
    $form['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING);
    if($form['email'] === '') {
        $error['email'] = 'blank';
    } else {
        // データベースを定義
        $db = dbconnect();
        // データを取得(SQL構文)
        $stmt = $db->prepare('select count(*) from members where email=?');
        // エラー
        if(!$stmt) {
            die($db->error);
        };
        // ?の中身を指定
        $stmt->bind_param('s', $form['email']);
        // 実行し、失敗したらエラー
        $success = $stmt->execute();
        if (!$success) {
            die($db->error);
        }

        // bind_paramで取ってきたデータを取得
        $stmt->bind_result($cnt);
        $stmt->fetch();
        var_dump($cnt);

        if($cnt > 0) {
            $error['email'] = 'duplicate';
        }
    }

    // パスワード
    $form['password'] = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    if($form['password'] === '') {
        $error['password'] = 'blank';
    // 文字列の長さが４文字未満なら
    } else if (strlen($form['password']) < 4) {
        $error['password'] = 'length';
    }

    // 画像のチェック
    // filter_inputではない
    $image = $_FILES['image'];
    // ファイルにはいろいろな属性が付与されており、nameはファイルの名前を表す
    // error=0は、エラーが起こっていないことを表す
    if($image['name'] !== '' && $image['error'] === 0) {
        // ファイルのタイプ(拡張子)を判断
        // tmp_name=一時的に付けられる、仮の名前(ファイルの特定に使用する？)
        $type = mime_content_type($image['tmp_name']);
        if($type !== 'image/png' && $type !== 'image/jpeg') {
            $error['image'] = 'type';
        }
    }

    // エラーが何もなかった場合
    if(empty($error)) {
        // formの中身を全てセッションに渡す
        $_SESSION['form'] = $form;
        //画像があるなら、実行
        if($image['name'] !== '') {
            //画像のアップロード
            //日付＋ファイル名をfilenameに入れる
            $filename = date('YmdHis') . '_' . $image['name'];
            // アップロードしたファイルをテンポラリーの場所から正式な場所へと移動する
            //第一引数にファイル、第二引数に移動先を指定(移動先は自分で作成)
            if (!move_uploaded_file($image['tmp_name'], '../member_picture/' . $filename)) {
                // エラー
                die('ファイルのアップロードに失敗しました');
            };
            $_SESSION['form']['image'] = $filename;    
        } else {
            // セッションをリセット
            $_SESSION['form']['image'] = '';
        }
    }
    header('Location: check.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>会員登録</title>

    <link rel="stylesheet" href="../style.css"/>
</head>

<body>
<div id="wrap">
    <div id="head">
        <h1>会員登録</h1>
    </div>

    <div id="content">
        <p>次のフォームに必要事項をご記入ください。</p>
        <!-- ページ内でエラーのチェックをしたいので、action属性は空に -->
        <form action="" method="post" enctype="multipart/form-data">
            <dl>

                <dt>ニックネーム<span class="required">必須</span></dt>
                <dd>
                    <!-- エラー時、既に入力されている値は保持する -->
                    <input type="text" name="name" size="35" maxlength="255" value="<?php echo h($form['name']); ?>"/>
                    <!-- エラーの時のみメッセージを表示 -->
                    <!-- isset=値が入っているかどうか確認 -->
                    <?php if(isset($error['name']) && $error['name'] === 'blank'): ?>
                        <p class="error">* ニックネームを入力してください</p>
                    <?php endif; ?>
                </dd>

                <dt>メールアドレス<span class="required">必須</span></dt>
                <dd>
                    <!-- 入力されている値は保持 -->
                    <input type="text" name="email" size="35" maxlength="255" value="<?php echo h($form['email']); ?>"/>
                    <!-- 入力していないならエラー -->
                    <?php if(isset($error['email']) && $error['email'] === 'blank'): ?>
                        <p class="error">* メールアドレスを入力してください</p>
                    <?php endif; ?>
                    <!-- 重複しているならエラー -->
                    <?php if(isset($error['email']) && $error['email'] === 'duplicate'): ?>
                        <p class="error">* 指定されたメールアドレスはすでに登録されています</p>
                    <?php endif; ?>
                <dt>パスワード<span class="required">必須</span></dt>

                <dd>
                    <!-- 値保持 -->
                    <input type="password" name="password" size="10" maxlength="20" value="<?php echo h($form['password']); ?>"/>
                    <!-- 空ならエラー -->
                    <?php if(isset($error['password']) && $error['password'] === 'blank'): ?>
                        <p class="error">* パスワードを入力してください</p>
                    <?php endif; ?>
                    <!-- 短すぎるとエラー -->
                    <?php if(isset($error['password']) && $error['password'] === 'length'): ?>
                        <p class="error">* パスワードは4文字以上で入力してください</p>
                    <?php endif; ?>
                </dd>

                <dt>写真など</dt>
                <dd>
                    <input type="file" name="image" size="35" value=""/>
                    <?php if(isset($error['image']) && $error['image'] === 'type'): ?>
                        <p class="error">* 写真などは「.png」または「.jpg」の画像を指定してください</p>
                    <?php endif; ?>
                    <p class="error">* 恐れ入りますが、画像を改めて指定してください</p>
                </dd>
            </dl>
            <!-- 送信 -->
            <div><input type="submit" value="入力内容を確認する"/></div>
        </form>
    </div>
</body>

</html>