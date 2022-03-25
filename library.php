<?php

// データベースを定義
function dbconnect() {
   $db = new mysqli('localhost:8889', 'root', 'root', 'min_bbs'); 
   // 接続できなかったらエラー
	if(!$db) {
		die($db->error);
	};
    return $db;
}

// htmlspecialcharsを関数化
// htmlspecialchars=文字列をhtmlエンティティに変換
// ENT_QUOTES=シングルクォートとかも変換
function h($value) {
    return htmlspecialchars($value, ENT_QUOTES);
};

?>