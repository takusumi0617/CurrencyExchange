<?php
ini_set('display_errors', "On");
// セッションの開始
session_start();

// Cookieが存在しない場合はauth.phpにリダイレクト
if (!isset($_COOKIE['session_id'])) {
    header('Location: ../auth.php');
    exit;
}

// SQLiteデータベースへの接続
$db1 = new SQLite3(dirname(__DIR__) . '/data.db');

// session_dataテーブルに接続し、session_idからuser_idを取得
$session_id = $_COOKIE['session_id'];
$stmt = $db1->prepare('SELECT user_id FROM session_data WHERE session_id = :session_id');
$stmt->bindValue(':session_id', $session_id, SQLITE3_TEXT);
$result = $stmt->execute();
$row = $result->fetchArray();

// user_idが取得できない場合はauth.phpにリダイレクト
if (!$row || !isset($row['user_id'])) {
    header('Location: ../auth.php');
    exit;
}

$user_id = $row['user_id'];

// main_dataテーブルに接続し、id、em_amount、di_amountを表示
$stmt = $db1->prepare('SELECT id, em_amount, di_amount, em_t_amount, di_t_amount, t_out FROM main_data WHERE id = :user_id');
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$row = $result->fetchArray();
$id = $row['id'];
$em_n_amount = $row['em_amount'];
$di_n_amount = $row['di_amount'];
$em_amount = $row['em_t_amount'];
$di_amount = $row['di_t_amount'];
$t_out = $row['t_out'];
$db1->close();
?>
<html>

<head>
    <meta charset="utf-8">
    <title>証券取引所 | 定期預金</title>
    <link href="../style.css" rel="stylesheet" />
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
                <li><a href="../index.php">マイページ</a></li>
                <li class="current"><a href="index.php">預金</a></li>
                <li><a href="../exchange.php">為替</a></li>
                <li><a href="../send.php">送金</a></li>
                <li><a href="../trading.php">取引</a></li>
                <li><a href="../bonds.php">国債・公債・社債</a></li>
            </ul>
            <ul>
                <li><a href="index.php">当座預金</a></li>
                <li class="current"><a href="term.php">定期預金</a></li>
                <li><a href="debt.php">融資</a></li>
            </ul>
        </nav>
    </header>
    <!-- ヘッダー終了 -->
    <h1>ようこそ</h1>
    <div class="contents">
        <div class="item">
            <h2><b>定期預金</b>残高 口座ID:<?php echo $id; ?><br>利子付与終了日:<?php echo $t_out; ?></h2>
            <p class="money"><img src="../component/picture/emerald.png" alt="Emerald" height="40px"/> : <?php echo number_format((float)$em_amount); ?></p>
            <p class="money"><img src="../component/picture/diamond.png" alt="Diamond" height="40px"/> : <?php echo number_format((float)$di_amount); ?></p>
        </div>
        <div class="item">
        <form name="a_form" class="column" action="" method="POST">
            <h2>預入申込</h2>

            <div class="form-group">
                <label>エメラルド数量</label>
                <input type="number" name="エメラルド数量"> 個
            </div>

            <hr>

            <div class="form-group">
                <label>ダイヤ数量</label>
                <input type="number" name="ダイヤ数量"> 個
            </div>

            <hr>

            <div class="form-group">
                <label>預入期間設定(金利)</label>
                <input type="radio" name="金利" value="0.001" checked> 1ヶ月(0.1%)
                <input type="radio" name="金利" value="0.003"> 2ヶ月(0.3%)
                <input type="radio" name="金利" value="0.005"> 3ヶ月(0.5%)
                <input type="radio" name="金利" value="0.01"> 6ヶ月(1%)
                <input type="radio" name="金利" value="0.02"> 1年(2%)
            </div>
            <p style="color: red;">※一度申し込むと利子付与終了日の翌月1日まで定期預金を引き出すことはできません。<br>(1日に自動で当座預金に戻されます。)</p>
            <button name="a_form" class="btn" type="submit">申込送信</button>
        </form>
        </div>
        <?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['a_form'])) {

    $em_in_amount = $_POST['エメラルド数量'];
    $di_in_amount = $_POST['ダイヤ数量'];
    $interest = $_POST['金利'];
    
    // 現在の日時を日本時間で取得
    $now = new DateTime('now', new DateTimeZone('Asia/Tokyo'));

    // 金利に基づいて期間を設定
    switch ($interest) {
        case '0.001':
            $next = (clone $now)->modify('+1 month');
            break;
        case '0.003':
            $next = (clone $now)->modify('+2 months');
            break;
        case '0.005':
            $next = (clone $now)->modify('+3 months');
            break;
        case '0.01':
            $next = (clone $now)->modify('+6 months');
            break;
        case '0.02':
            $next = (clone $now)->modify('+1 year');
            break;
        default:
            echo "<script type='text/javascript'>alert('無効な金利です。');</script>";
            exit;
    }

    $nowFormatted = $now->format('Y-m-d H:i:s');
    $nextFormatted = $next->format('Y-m-d H:i:s');

    if (empty($em_in_amount) && empty($di_in_amount)) {
        echo "<script type='text/javascript'>alert('未入力の個所があります。');</script>";
    } elseif ($em_in_amount <= 0 || $di_in_amount <= 0) {
        echo "<script type='text/javascript'>alert('金額に負の数や0を指定することはできません。');</script>";
    } elseif ($em_n_amount < $em_in_amount || $di_n_amount < $di_in_amount) {
        echo "<script type='text/javascript'>alert('残高不足です。');</script>";
    } elseif (!is_null($t_out)) {
        echo "<script type='text/javascript'>alert('すでに別の定期預金が契約されています。');</script>";
    } else {
        // データベースに接続
        $db = new SQLite3(dirname(__DIR__) . '/data.db');

        // トランザクション開始
        $db->exec('BEGIN');

        try {
            // データを更新するクエリ
            $updateQuery = $db->prepare("
                UPDATE main_data 
                SET 
                    em_amount = em_amount - :em_in_amount, 
                    di_amount = di_amount - :di_in_amount, 
                    em_t_amount = em_t_amount + :em_in_amount, 
                    di_t_amount = di_t_amount + :di_in_amount, 
                    t_out = :nextFormatted, 
                    t_in = :nowFormatted, 
                    t_interest = :interest 
                WHERE id = :user_id
            ");

            // パラメータをバインド
            $updateQuery->bindValue(':em_in_amount', $em_in_amount, SQLITE3_INTEGER);
            $updateQuery->bindValue(':di_in_amount', $di_in_amount, SQLITE3_INTEGER);
            $updateQuery->bindValue(':nextFormatted', $nextFormatted, SQLITE3_TEXT);
            $updateQuery->bindValue(':nowFormatted', $nowFormatted, SQLITE3_TEXT);
            $updateQuery->bindValue(':interest', $interest, SQLITE3_TEXT);  // 金利が数値ならSQLITE3_FLOAT
            $updateQuery->bindValue(':user_id', $user_id, SQLITE3_INTEGER); // $user_idが定義されている必要があります

            // クエリを実行
            if ($updateQuery->execute()) {
                // トランザクションをコミット
                $db->exec('COMMIT');
                echo "<script type='text/javascript'>alert('申込を実行しました。');</script>";
            } else {
                // クエリが失敗した場合はロールバック
                $db->exec('ROLLBACK');
                echo "<script type='text/javascript'>alert('申込に失敗しました。');</script>";
            }
        } catch (Exception $e) {
            // 例外が発生した場合もロールバック
            $db->exec('ROLLBACK');
            echo "<script type='text/javascript'>alert('エラーが発生しました: " . $e->getMessage() . "');</script>";
        }

        // データベース接続を閉じる
        $db->close();
    }
}
?>
        <div class="item">
            <h2>各種設定</h2>
            <p><a class="btn" href="../change_pw.php">パスワード変更はこちら</a></p>
        </div>
    </div>
</body>

</html>