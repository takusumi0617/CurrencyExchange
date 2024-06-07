<?php
// セッションスタート
session_start();

// セッションIDがクッキーにあるか確認
if (!isset($_COOKIE['session_id'])) {
    header('Location: auth.php');
    exit();
}

// SQLiteデータベースに接続
$db = new SQLite3(__DIR__ . '/data.db');

// クッキーからセッションIDを取得
$session_id = $_COOKIE['session_id'];

// セッションIDに対応するユーザーIDを取得
$stmt = $db->prepare('SELECT user_id FROM session_data WHERE session_id = :session_id');
$stmt->bindValue(':session_id', $session_id, SQLITE3_TEXT);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);

if (!$user) {
    // セッションIDが存在しない場合、認証ページにリダイレクト
    header('Location: auth.php');
    exit();
}

$user_id = $user['user_id'];

// POSTデータが送信された場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // 現在のパスワードを確認
    $stmt = $db->prepare('SELECT password FROM main_data WHERE id = :user_id');
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $user_data = $result->fetchArray(SQLITE3_ASSOC);

    if (!$user_data || $user_data['password'] !== $current_password) {
        // 現在のパスワードが一致しない場合
        echo '現在のパスワードが正しくありません。';
    } else {
        // パスワードを更新
        $stmt = $db->prepare('UPDATE main_data SET password = :new_password WHERE id = :user_id');
        $stmt->bindValue(':new_password', $new_password, SQLITE3_TEXT);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $result = $stmt->execute();

        if ($result) {
            echo 'パスワードが正常に変更されました。';
        } else {
            echo 'パスワードの変更に失敗しました。';
        }
    }
} else {
    // POSTデータが送信されていない場合、フォームを表示
    ?>
    <!DOCTYPE html>
    <html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>証券取引所 | パスワード変更</title>
    </head>
    <body>
        <h1 style="font-size: 40px; color: black; background-color: #ffffff;">為替・証券取引所</h1>
        <h2>現在のパスワードと新しく設定するパスワードを入力してください。</h2>
        <form action="" method="post">
            <label for="current_password">現在のパスワード:</label>
            <input type="password" id="current_password" name="current_password" required><br>
            <label for="new_password">新しいパスワード:</label>
            <input type="password" id="new_password" name="new_password" required><br>
            <button type="submit">変更</button>
        </form>
    </body>
    </html>
    <?php
}
?>