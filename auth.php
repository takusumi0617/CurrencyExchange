<?php
ini_set('display_errors', "On");
// SQLiteデータベースへの接続
$db = new SQLite3(__DIR__ . '/data.db');

// IDとパスワードが一致するかを確認する関数
function checkCredentials($id, $password, $db) {
    // クエリの作成と実行
    $stmt = $db->prepare('SELECT COUNT(*) FROM main_data WHERE id = :id AND password = :password');
    $stmt->bindValue(':id', $id);
    $stmt->bindValue(':password', $password);
    $result = $stmt->execute();

    // 結果の取得
    $row = $result->fetchArray();
    $count = $row[0];

    // IDとパスワードが一致するかを判定
    if ($count > 0) {
        return true; // 一致する場合はtrueを返す
    } else {
        return false; // 一致しない場合はfalseを返す
    }
}

// ログインフォームの表示
if (!isset($_POST['submit'])) {
    ?>
    <head>
        <title>証券取引所 | ログイン</title>
    </head>
    <body>
    <h1 style="font-size: 40px; color: black; background-color: #ffffff;">為替・証券取引所</h1>
    <h2>ログインが必要です</h2>
    <form action="" method="post">
        <label for="id">ID:</label>
        <input type="text" id="id" name="id" required><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        <input type="submit" name="submit" value="ログイン">
    </form>
    </body>
    <?php
} else {
    // フォームが送信された場合

    $id = $_POST['id'];
    $password = $_POST['password'];

    // 認証チェック
    if ($id === 'admin' && $password === 'takusumi1215')
    {
        setcookie('is_admin', true, time() + (86400 * 30), '/'); // 3 days
        header('Location: admin/index.php');
        exit;
    } else if (checkCredentials($id, $password, $db)) {
        // 認証成功

        // ランダムなsession_id生成
        $session_id = bin2hex(random_bytes(8));

        // session_dataテーブルにsession_idとuser_idを保存
        $stmt2 = $db->prepare('INSERT INTO session_data (user_id, session_id) VALUES (:user_id, :session_id) ON CONFLICT(user_id) DO UPDATE SET session_id = :session_id');
        $stmt2->bindValue(':user_id', $id, SQLITE3_INTEGER);
        $stmt2->bindValue(':session_id', $session_id, SQLITE3_TEXT);
        $stmt2->execute();

        // cookieにsession_idとuser_idを保存
        setcookie('session_id', $session_id, time() + (86400 * 3), '/'); // 3 days

        // index.htmlに遷移
        header('Location: index.php');
        exit;
    } else {
        // 認証失敗
        echo 'ID又はパスワードが違います';
        ?>
        <br><br>
        <form action="" method="post">
            <input type="submit" name="back" value="戻る">
        </form>
        <?php
    }
}
?>