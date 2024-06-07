<?php
if (!isset($_COOKIE['is_admin'])) {
    header('Location: ../auth.php');
    exit;
}
if ($_COOKIE['is_admin'] != true) {
    header('Location: ../auth.php');
    exit;
}
// SQLiteデータベースに接続
$db = new SQLite3(dirname(__DIR__) . '/data.db');
$db->close();
?>
<html>

<head>
    <meta charset="utf-8">
    <title>証券取引所 | メインページ</title>
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
                <li class="current"><a href="settings.php">メンテナンス</a></li>
            </ul>
        </nav>
    </header>
    <!-- ヘッダー終了 -->
    <h2>顧客管理</h2>
    <a class="btn" href="add.php" style="margin-left: 1em;">新規口座開設</a>
    <a class="btn" href="change_amount.php" style="margin-left: 1em;">口座残高変更</a>
    <br>
    <h2>定期処理</h2>
    <a class="btn" href="interest_add.php" style="margin-left: 1em;">(手動)利子付与</a>
</body>

</html>