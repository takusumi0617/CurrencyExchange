<?php
// データベースに接続
$db = new SQLite3(dirname(__DIR__) . '/data.db');

// POSTリクエストの処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // フォームから送信されたデータの取得
    $id = $_POST["id"];
    $password = $_POST["password"];

    // SQLインジェクション対策
    $id = SQLite3::escapeString($id);
    $password = SQLite3::escapeString($password);

    // SQLクエリの生成
    $sql = "INSERT INTO main_data (id, password) VALUES ('$id', '$password')";
    // クエリの実行
    $result = $db->exec($sql);
    echo "新しい口座を作成しました。";
}
// データベース接続を閉じる
$db->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>証券取引所 | 口座の追加</title>
    <link href="../style.css" rel="stylesheet" />
</head>
<body>
    <!-- ヘッダー開始 -->
    <header>
        <nav>
            <div>
            <h1 style="font-size: 40px; color: black; background-color: #c3ff8b;">為替・証券取引所 管理者画面</h1>
            </div>
            <ul>
                <li><a href="index.php">ダッシュボード</a></li>
                <li><a href="settings.php">メンテナンス</a></li>
            </ul>
        </nav>
    </header>
    <!-- ヘッダー終了 -->
    <h2>銀行口座の追加</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="id">id:</label>
        <input type="text" id="id" name="id"><br>
        <label for="password">パスワード:</label>
        <input type="text" id="password" name="password"><br><br>
        <input class="btn" type="submit" value="送信" style="margin-left: 1em;">
    </form>
</body>
</html>