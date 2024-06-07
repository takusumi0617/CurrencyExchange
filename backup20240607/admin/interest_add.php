<?php
if (!isset($_COOKIE['is_admin'])) {
    header('Location: ../auth.php');
    exit;
}
if ($_COOKIE['is_admin'] != true) {
    header('Location: ../auth.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>証券取引所 | 定期預金操作</title>
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
    <h2>定期預金 通常操作</h2>
    <p>※この操作は毎月1日に行う操作です。それ以外の日には行わないでください。</p>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input class="btn" type="submit" value="実行" style="margin-left: 1em;">
    </form>
    <?php
    // POSTリクエストの処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include dirname(__DIR__) . '/api/interest_add.php';
    $success = updateDatabase();
    if ($success) {
        echo "Database update was successful.";
    } else {
        echo "Database update failed.";
    }
}
    ?>
</body>
</html>