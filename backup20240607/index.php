<?php
ini_set('display_errors', "On");
// セッションの開始
session_start();

// Cookieが存在しない場合はauth.phpにリダイレクト
if (!isset($_COOKIE['session_id'])) {
    header('Location: auth.php');
    exit;
}

// SQLiteデータベースへの接続
$db = new SQLite3(__DIR__ . '/data.db');

// session_dataテーブルに接続し、session_idからuser_idを取得
$session_id = $_COOKIE['session_id'];
$stmt = $db->prepare('SELECT user_id FROM session_data WHERE session_id = :session_id');
$stmt->bindValue(':session_id', $session_id, SQLITE3_TEXT);
$result = $stmt->execute();
$row = $result->fetchArray();

// user_idが取得できない場合はauth.phpにリダイレクト
if (!$row || !isset($row['user_id'])) {
    header('Location: auth.php');
    exit;
}

$user_id = $row['user_id'];

// main_dataテーブルに接続し、id、em_amount、di_amountを表示
$stmt = $db->prepare('SELECT id, em_amount, di_amount FROM main_data WHERE id = :user_id');
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$row = $result->fetchArray();
$id = $row['id'];
$em_amount = $row['em_amount'];
$di_amount = $row['di_amount'];
?>
<html>

<head>
    <meta charset="utf-8">
    <title>証券取引所 | メインページ</title>
    <link href="style.css" rel="stylesheet" />
</head>

<body>
    <!-- ヘッダー開始 -->
    <header>
        <nav>
            <div>
            <h1 style="font-size: 40px; color: black; background-color: #c3ff8b;">為替・証券取引所</h1>
            <h2 class="logout"><a class="logoutbtn" href="logout.php" style="margin-right: 0px; color: #000000; border-color: blue;">ログアウト</a></h2>
            </div>
            <ul>
                <li class="current"><a href="index.php">マイページ</a></li>
                <li><a href="deposit/index.php">預金</a></li>
                <li><a href="exchange.php">為替</a></li>
                <li><a href="send.php">送金</a></li>
                <li><a href="trading.php">取引</a></li>
                <li><a href="bonds.php">国債・公債・社債</a></li>
            </ul>
        </nav>
    </header>
    <!-- ヘッダー終了 -->
    <h1>ようこそ</h1>
    <div class="contents">
        <div class="item">
            <h2>残高 口座ID:<?php echo $id; ?></h2>
            <p class="money"><img src="component/picture/emerald.png" alt="Emerald" height="40px"/> : <?php echo number_format((float)$em_amount); ?></p>
            <p class="money"><img src="component/picture/diamond.png" alt="Diamond" height="40px"/> : <?php echo number_format((float)$di_amount); ?></p>
        </div>
        <div class="item">
            <h2>お知らせ</h2>
            <p>お知らせはありません。</p>
        </div>
        <div class="item">
            <h2>各種設定</h2>
            <p><a class="btn" href="change_pw.php">パスワード変更はこちら</a></p>
        </div>
</body>

</html>