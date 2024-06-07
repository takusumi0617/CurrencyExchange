<?php
if (!isset($_COOKIE['is_admin'])) {
    header('Location: ../auth.php');
    exit;
}
if ($_COOKIE['is_admin'] != true) {
    header('Location: ../auth.php');
    exit;
}
// データベースに接続
$db = new SQLite3(dirname(__DIR__) . '/data.db');

// POSTリクエストの処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // フォームから送信されたデータの取得
    $id = $_POST["id"];
    $emamount = (int)$_POST["em_amount"];
    $diamount = (int)$_POST["di_amount"];

    // SQLインジェクション対策
    $id = SQLite3::escapeString($id);
    $emamount = SQLite3::escapeString($emamount);
    $diamount = SQLite3::escapeString($diamount);

    // SQLクエリの生成
    $sql = "UPDATE main_data SET em_amount = em_amount + $emamount, di_amount = di_amount + $diamount WHERE id = $id";
    // クエリの実行
    $result = $db->exec($sql);
    echo "口座残高を変更しました。";
}
// データベース接続を閉じる
$db->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>証券取引所 | 残高の変更</title>
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
    <h2>口座残高の変更</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="id">id:</label>
        <input type="text" id="id" name="id"><br>
        <label for="em_amount">エメラルド変動量:</label>
        <input type="number" id="em_amount" name="em_amount"><br>
        <label for="di_amount">ダイヤ変動量:</label>
        <input type="number" id="di_amount" name="di_amount"><br>
        <b>※マイナスつけ忘れ注意</b>
        <br>
        <input class="btn" type="submit" value="送信" style="margin-left: 1em;">
    </form>
</body>
</html>