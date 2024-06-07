<?php
// SQLiteデータベースへの接続
$db = new SQLite3(__DIR__ . '/data.db');

// Cookieからsession_idを取得
$session_id = isset($_COOKIE['session_id']) ? $_COOKIE['session_id'] : null;

if ($session_id) {
    // session_dataテーブルからsession_idに一致する行を削除
    $stmt = $db->prepare('DELETE FROM session_data WHERE session_id = :session_id');
    $stmt->bindValue(':session_id', $session_id, SQLITE3_TEXT);
    $stmt->execute();
}

// session_id Cookieを削除
if (isset($_COOKIE['session_id'])) {
    setcookie('session_id', '', time() - 3600, '/'); // 有効期限を過去に設定して削除
}

// is_admin Cookieを削除
if (isset($_COOKIE['is_admin'])) {
    setcookie('is_admin', '', time() - 3600, '/'); // 有効期限を過去に設定して削除
}

// index.phpにリダイレクト
header('Location: index.php');
exit();
?>