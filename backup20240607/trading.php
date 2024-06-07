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
$db1 = new SQLite3(__DIR__ . '/data.db');

// session_dataテーブルに接続し、session_idからuser_idを取得
$session_id = $_COOKIE['session_id'];
$stmt = $db1->prepare('SELECT user_id FROM session_data WHERE session_id = :session_id');
$stmt->bindValue(':session_id', $session_id, SQLITE3_TEXT);
$result = $stmt->execute();
$row = $result->fetchArray();

// user_idが取得できない場合はauth.phpにリダイレクト
if (!$row || !isset($row['user_id'])) {
    header('Location: auth.php');
    exit;
}

$user_id = $row['user_id'];
?>
<html>

<head>
    <meta charset="utf-8">
    <title>証券取引所 | 各種取引</title>
    <link href="style.css" rel="stylesheet" />
    <link href="component/trading.css" rel="stylesheet" />
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
                <li><a href="index.php">マイページ</a></li>
                <li><a href="deposit/index.php">預金</a></li>
                <li><a href="exchange.php">為替</a></li>
                <li><a href="send.php">送金</a></li>
                <li class="current"><a href="trading.php">取引</a></li>
                <li><a href="bonds.php">国債・公債・社債</a></li>
            </ul>
        </nav>
    </header>
    <!-- ヘッダー終了 -->
    <div class="container">
        <div class="column current-deals">
            <h2>現在募集中の取引</h2>
            <?php
            // データベースに接続
            $db = new SQLite3(__DIR__ . '/component/db/trade.db');

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
                $rowIdToDelete = $_POST['delete'];
                $deleteQuery = $db->prepare('DELETE FROM main_data WHERE ROWID = :rowid');
                $deleteQuery->bindValue(':rowid', $rowIdToDelete, SQLITE3_INTEGER);
                $deleteQuery->execute();
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel'])) {
                $rowIdToCancel = $_POST['cancel'];
                $deleteQuery = $db->prepare('UPDATE main_data SET worker_id = null, status = 1 WHERE ROWID = :rowid');
                $deleteQuery->bindValue(':rowid', $rowIdToCancel, SQLITE3_INTEGER);
                $deleteQuery->execute();
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept'])) {
                $rowIdToAccept = $_POST['accept'];
                $deleteQuery = $db->prepare('UPDATE main_data SET worker_id = ' . $user_id . ', status = 2 WHERE ROWID = :rowid');
                $deleteQuery->bindValue(':rowid', $rowIdToAccept, SQLITE3_INTEGER);
                $deleteQuery->execute();
            }

            // データを取得するクエリ
            $query = "SELECT rowid, recruiter_id, worker_id, recruiter_name, deadline, receive, receive_amount, give, give_amount FROM main_data WHERE status = 1 OR worker_id = $user_id OR recruiter_id = $user_id";
            $results = $db->query($query);

            // 結果を表示
            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                echo '<div class="deal">';
                echo htmlspecialchars($row['receive']) . ' × ' . htmlspecialchars($row['receive_amount']) . ' → ' . htmlspecialchars($row['give']) . ' × ' . htmlspecialchars($row['give_amount']) . '<br>';
                echo '募集者名:' . htmlspecialchars($row['recruiter_name']) . ' 期日:' . htmlspecialchars($row['deadline']) . ' ID:' . htmlspecialchars($row['rowid']);
                if ($row['recruiter_id'] === $user_id) { echo '<form method="POST" action=""><button type="submit" name="delete" value="' . htmlspecialchars($row['rowid']) . '" class="btn">削除</button></form>'; }
                elseif ($row['worker_id'] === $user_id) { echo '<form method="POST" action=""><button type="submit" name="cancel" value="' . htmlspecialchars($row['rowid']) . '" class="btn">キャンセル</button></form>'; }
                else { echo '<form method="POST" action=""><button type="submit" name="accept" value="' . htmlspecialchars($row['rowid']) . '" class="btn">受注</button></form>'; }
                echo '</div>';
            }

            // データベース接続を閉じる
            $db->close();
            ?>
        </div>

        <form name="a_form" class="column" action="" method="POST">
            <h2>新規募集</h2>

            <div class="form-group">
                <label>募集物品</label>
                <input type="radio" name="募集物品" value="エメラルド" checked> エメラルド
                <input type="radio" name="募集物品" value="ダイヤ"> ダイヤ
                <input type="radio" name="募集物品" value="カスタム"> カスタム:
                <input class="inline-input" type="text" name="募集物品カスタム" style="width: 10em;">
            </div>

            <div class="form-group">
                <label>数量</label>
                <input type="number" name="募集物品数量"> 個
            </div>

            <hr>

            <div class="form-group">
                <label>報酬設定</label>
                <input type="radio" name="報酬設定" value="エメラルド" checked> エメラルド
                <input type="radio" name="報酬設定" value="ダイヤ"> ダイヤ
                <input type="radio" name="報酬設定" value="カスタム"> カスタム:
                <input class="inline-input" type="text" name="報酬設定カスタム" style="width: 10em;">
            </div>

            <div class="form-group">
                <label>数量</label>
                <input type="number" name="報酬設定数量"> 個
            </div>

            <hr>

            <div class="form-group">
                <label>募集者名</label>
                <input type="text" name="募集者名">
            </div>

            <div class="form-group">
                <label>期日</label>
                <input type="datetime-local" name="期日">
            </div>

            <button name="a_form" class="btn" type="submit">募集開始</button>
        </form>
    </div>
    <?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['a_form'])) {

    if (empty($_POST['募集物品数量']) || empty($_POST['報酬設定数量'])) {
        echo "<script type='text/javascript'>alert('未入力の個所があります。');</script>";
    } else {
    // データベースファイルのパス
    $db_file = __DIR__ . '/component/db/trade.db';

    // フォームからデータを取得
    $deadline = $_POST['期日'];
    $recruiter_name = $_POST['募集者名'];
    $receive = $_POST['募集物品'] === 'カスタム' ? $_POST['募集物品カスタム'] : $_POST['募集物品'];
    $receive_amount = $_POST['募集物品数量'];
    $give = $_POST['報酬設定'] === 'カスタム' ? $_POST['報酬設定カスタム'] : $_POST['報酬設定'];
    $give_amount = $_POST['報酬設定数量'];

    // $id の固定値
    $recruiter_id = $user_id;

    // データベースに接続
    $db = new SQLite3($db_file);

    // テーブルが存在しない場合のためのテーブル作成クエリ
    $createTableQuery = "
    CREATE TABLE IF NOT EXISTS main_data (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        status INTEGER,
        deadline DATETIME,
        recruiter_id INTEGER,
        worker_id INTEGER,
        recruiter_name TEXT,
        receive TEXT,
        receive_amount INTEGER,
        give TEXT,
        give_amount INTEGER
    );
    ";
    $db->exec($createTableQuery);

    // データを挿入するクエリ
    $insertQuery = $db->prepare("
        INSERT INTO main_data (status, deadline, recruiter_id, worker_id, recruiter_name, receive, receive_amount, give, give_amount) 
        VALUES (:status, :deadline, :recruiter_id, :worker_id, :recruiter_name, :receive, :receive_amount, :give, :give_amount)
    ");

    // パラメータをバインド
    $insertQuery->bindValue(':status', 1, SQLITE3_INTEGER);
    $insertQuery->bindValue(':deadline', $deadline, SQLITE3_TEXT);
    $insertQuery->bindValue(':recruiter_id', $recruiter_id, SQLITE3_INTEGER);
    $insertQuery->bindValue(':worker_id', null, SQLITE3_NULL);
    $insertQuery->bindValue(':recruiter_name', $recruiter_name, SQLITE3_TEXT);
    $insertQuery->bindValue(':receive', $receive, SQLITE3_TEXT);
    $insertQuery->bindValue(':receive_amount', $receive_amount, SQLITE3_INTEGER);
    $insertQuery->bindValue(':give', $give, SQLITE3_TEXT);
    $insertQuery->bindValue(':give_amount', $give_amount, SQLITE3_INTEGER);

    // クエリを実行
    if ($insertQuery->execute()) {
        echo "<script type='text/javascript'>alert('募集を開始しました。');</script>";
    } else {
        echo "<script type='text/javascript'>alert('募集の開始に失敗しました。');</script>";
    }

    // データベース接続を閉じる
    $db->close();
    }
}
?>
</body>

</html>
