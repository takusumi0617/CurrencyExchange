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
$em_amount = (float)$row['em_amount'];
$di_amount = (float)$row['di_amount'];
$db->close();
?>
<html>

<head>
    <meta charset="utf-8">
    <title>証券取引所 | 送金取引</title>
    <link href="style.css" rel="stylesheet" />
</head>

<body>
<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $send = $_POST['send'];
        $amount = intval($_POST['amount']);
        $sendto = intval($_POST['to']);

        // データベース接続
        $db = new SQLite3(__DIR__ . '/data.db');
        $result = $db->querySingle("SELECT em_amount, di_amount FROM main_data WHERE id = $id", true);

        $em_amount = $result['em_amount'];
        $di_amount = $result['di_amount'];

        $stmt = $db->prepare('SELECT COUNT(*) as count FROM main_data WHERE id = :id');
        $stmt->bindValue(':id', $sendto, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);

        if ($send == 'e') {
            $required_amount = $amount;
            if ($em_amount < $required_amount) {
                echo "<script type='text/javascript'>alert('残高不足です。');</script>";
            } elseif ($row['count'] == 0) {
                echo "<script type='text/javascript'>alert('宛先口座が存在しません。');</script>";
            } elseif ($required_amount <= 0) {
                echo "<script type='text/javascript'>alert('金額に負の数や0を指定することはできません。');</script>";
            } else {
                $stmt = $db->prepare("UPDATE main_data SET em_amount = em_amount - :required_amount WHERE id = :id");
                $stmt->bindValue(':required_amount', $required_amount);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                
                $stmt = $db->prepare("UPDATE main_data SET em_amount = em_amount + :required_amount WHERE id = :sendto");
                $stmt->bindValue(':required_amount', $required_amount);
                $stmt->bindValue(':sendto', $sendto);
                $stmt->execute();
                echo "<script type='text/javascript'>alert('注文を受け付けました。');</script>";
                header("Refresh:0");
            }
        } elseif ($send == 'd') {
            $required_amount = $amount;
            if ($di_amount < $required_amount) {
                echo "<script type='text/javascript'>alert('残高不足です。');</script>";
            } elseif ($row['count'] == 0) {
                echo "<script type='text/javascript'>alert('宛先口座が存在しません。');</script>";
            } elseif ($required_amount <= 0) {
                echo "<script type='text/javascript'>alert('金額に負の数や0を指定することはできません。');</script>";
            } else {
                $stmt = $db->prepare("UPDATE main_data SET di_amount = di_amount - :required_amount WHERE id = :id");
                $stmt->bindValue(':required_amount', $required_amount);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                
                $stmt = $db->prepare("UPDATE main_data SET di_amount = di_amount + :required_amount WHERE id = :sendto");
                $stmt->bindValue(':required_amount', $required_amount);
                $stmt->bindValue(':sendto', $sendto);
                $stmt->execute();
                echo "<script type='text/javascript'>alert('注文を受け付けました。');</script>";
                header("Refresh:0");
            }
        }

        $db->close();
    }
    ?>
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
                <li class="current"><a href="send.php">送金</a></li>
                <li><a href="trading.php">取引</a></li>
                <li><a href="bonds.php">国債・公債・社債</a></li>
            </ul>
        </nav>
    </header>
    <!-- ヘッダー終了 -->
    <div class="contents">
        <div class="item">
            <h2>残高 口座ID:<?php echo $id; ?></h2>
            <p class="money"><img src="component/picture/emerald.png" alt="Emerald" height="40px"/> : <?php echo number_format((float)$em_amount); ?></p>
            <p class="money"><img src="component/picture/diamond.png" alt="Diamond" height="40px"/> : <?php echo number_format((float)$di_amount); ?></p>
        </div>
        <div class="item">
            <h2>送金</h2>
            <form method="POST" name="a_form" action="">
            <br>送金種別:<br><input type="radio" id="e" name="send" value="e" checked />エメラルド <input type="radio" id="d" name="send" value="d" />ダイヤ <br>
            <p>送金先口座:<input type="text" id="to"  name="to" /></p>
            <P>送金額:<input type="number" id="amount"  name="amount" /></p>
            <p><a href="#" onclick="document.a_form.submit();" class="btn">実行</a></p>
            </form>
        </div>
    </div>
</body>

</html>