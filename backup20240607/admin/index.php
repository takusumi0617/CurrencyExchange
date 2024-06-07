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
// クエリを実行して "em_amount" カラムの合計を取得
$em_n_sum = $db->querySingle('SELECT SUM(em_amount) AS total FROM main_data');
$di_n_sum = $db->querySingle('SELECT SUM(di_amount) AS total FROM main_data');
$em_t_sum = $db->querySingle('SELECT SUM(em_t_amount) AS total FROM main_data');
$di_t_sum = $db->querySingle('SELECT SUM(di_t_amount) AS total FROM main_data');
// データベース接続を閉じる
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
                <li class="current"><a href="index.php">ダッシュボード</a></li>
                <li><a href="settings.php">メンテナンス</a></li>
            </ul>
        </nav>
    </header>
    <!-- ヘッダー終了 -->
    <h1>ようこそ</h1>
    <div class="contents">
        <div class="item">
            <h2>全資金残高</h2>
            <p class="money"><img src="../component/picture/emerald.png" alt="Emerald" height="40px"/> : <?php echo number_format((float)($em_n_sum + $em_t_sum)); ?></p>
            <p class="money"><img src="../component/picture/diamond.png" alt="Diamond" height="40px"/> : <?php echo number_format((float)($di_n_sum + $di_t_sum)); ?></p>
        </div>
    </div>
    <div class="contents">
        <div class="item">
            <h2>全当座残高</h2>
            <p class="money"><img src="../component/picture/emerald.png" alt="Emerald" height="40px"/> : <?php echo number_format((float)$em_n_sum); ?></p>
            <p class="money"><img src="../component/picture/diamond.png" alt="Diamond" height="40px"/> : <?php echo number_format((float)$di_n_sum); ?></p>
        </div>
        <div class="item">
            <h2>全定期残高</h2>
            <p class="money"><img src="../component/picture/emerald.png" alt="Emerald" height="40px"/> : <?php echo number_format((float)$em_t_sum); ?></p>
            <p class="money"><img src="../component/picture/diamond.png" alt="Diamond" height="40px"/> : <?php echo number_format((float)$di_t_sum); ?></p>
        </div>
</body>

</html>