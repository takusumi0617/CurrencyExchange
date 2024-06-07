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

$xml = simplexml_load_file(__DIR__ . '/component/data.xml');
$e_d_element = (float)$xml->xpath('/opendata/exchange/e-d')[0];
$d_e_element = (float)$xml->xpath('/opendata/exchange/d-e')[0];
$e_d_highest = (float)$xml->xpath('/opendata/exchange_highest/e-d')[0];
$d_e_highest = (float)$xml->xpath('/opendata/exchange_highest/d-e')[0];
?>
<html>

<head>
    <meta charset="utf-8">
    <title>証券取引所 | 為替</title>
    <link href="style.css" rel="stylesheet" />
</head>

<body>
<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $exchange = $_POST['exchange'];
        $amount = intval($_POST['amount']);

        // データベース接続
        $db = new SQLite3(__DIR__ . '/data.db');
        $result = $db->querySingle("SELECT em_amount, di_amount FROM main_data WHERE id = $id", true);

        $em_amount = $result['em_amount'];
        $di_amount = $result['di_amount'];

        if ($exchange == 'ed') {
            $required_amount = $amount * $e_d_element;

            if ($em_amount < $required_amount) {
                echo "<script type='text/javascript'>alert('残高不足です。');</script>";
            } elseif ($amount > $e_d_highest) {
                echo "<script type='text/javascript'>alert('取引上限を超えています。');</script>";
            } elseif ($required_amount <= 0) {
                echo "<script type='text/javascript'>alert('金額に負の数や0を指定することはできません。');</script>";
            } else {
                $stmt = $db->prepare("UPDATE main_data SET em_amount = em_amount - :required_amount, di_amount = di_amount + $amount WHERE id = :id");
                $stmt->bindValue(':required_amount', $required_amount);
                $stmt->bindValue(':id', $id);
                $stmt->execute();

                $xml = simplexml_load_file(__DIR__ . '/component/data.xml');
                $xml->exchange_highest->{'e-d'} = $e_d_highest - $amount;
                $xml->asXML(__DIR__ . '/component/data.xml');

                echo "<script type='text/javascript'>alert('注文を受け付けました。');</script>";
                header("Refresh:0");
            }
        } elseif ($exchange == 'de') {
            if ($di_amount < $amount) {
                echo "<script type='text/javascript'>alert('残高不足です。');</script>";
            } elseif ($amount > $d_e_highest) {
                echo "<script type='text/javascript'>alert('取引上限を超えています。');</script>";
            } elseif ($amount <= 0) {
                echo "<script type='text/javascript'>alert('金額に負の数や0を指定することはできません。');</script>";
            } else {
                $added_amount = $amount * $d_e_element;
                $stmt = $db->prepare("UPDATE main_data SET di_amount = di_amount - :amount, em_amount = em_amount + :added_amount WHERE id = :id");
                $stmt->bindValue(':amount', $amount);
                $stmt->bindValue(':added_amount', $added_amount);
                $stmt->bindValue(':id', $id);
                $stmt->execute();

                $xml = simplexml_load_file(__DIR__ . '/component/data.xml');
                $xml->exchange_highest->{'d-e'} = $d_e_highest - $amount;
                $xml->asXML(__DIR__ . '/component/data.xml');

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
                <li class="current"><a href="exchange.php">為替</a></li>
                <li><a href="send.php">送金</a></li>
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
            <h2>為替</h2>
            <h3>現在の取引レート<h3>
            <table>
            <tr><th>区分</th><th>レート</th></tr>
            <tr><td>エメラルド→ダイヤ</td><td>1ダイヤ=<?php echo number_format((float)$e_d_element) ?>エメラルド</td></tr>
            <tr><td>ダイヤ→エメラルド</td><td>1ダイヤ=<?php echo number_format((float)$d_e_element) ?>エメラルド</td></tr>
            </table>
            <h3>現在の注文数上限<h3>
            <table>
            <tr><th>区分</th><th>上限数</th></tr>
            <tr><td>エメラルド→ダイヤ</td><td><?php echo number_format((float)$e_d_highest) ?>注文</td></tr>
            <tr><td>ダイヤ→エメラルド</td><td><?php echo number_format((float)$d_e_highest) ?>注文</td></tr>
            </table>
            <form method="POST" name="a_form" action="">
            <p><div><input type="radio" id="ed" name="exchange" value="ed" checked /><label for=ed>エメラルド→ダイヤ</label></div><div><input type="radio" id="de" name="exchange" value="de" /><label for="de">ダイヤ→エメラルド</label></div></p>
            <P>注文数:<input type="number" id="amount"  name="amount" /></p>
            <p>※取引総額=レート×注文数</p>
            <p><a href="#" onclick="document.a_form.submit();" class="btn">実行</a></p>
            </form>
        </div>
    </div>
</body>

</html>