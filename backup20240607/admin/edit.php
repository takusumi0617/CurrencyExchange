<?php
if (!isset($_COOKIE['is_admin'])) {
    header('Location: ../auth.php');
    exit;
}
if ($_COOKIE['is_admin'] != true) {
    header('Location: ../auth.php');
    exit;
}
$cmd = isset($_GET['cmd']) ? $_GET['cmd'] : null;
if ($cmd === "admin_request")
{
// SQLite3データベースに接続
$db = new SQLite3(dirname(__DIR__) . '/data.db');
// テーブル名を指定
$tableName = 'main_data';
// テーブルの全データを取得するSQLクエリを実行
$query = "SELECT * FROM $tableName";
$result = $db->query($query);
// 結果をHTMLテーブルとして表示
echo '<table border="1">';
echo '<tr>';
// カラム名をヘッダーとして表示
$columns = $db->query("PRAGMA table_info($tableName)");
while ($column = $columns->fetchArray(SQLITE3_ASSOC)) {
    echo '<th>' . htmlspecialchars($column['name']) . '</th>';
}
echo '</tr>';
// データを表示
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    echo '<tr>';
    foreach ($row as $cell) {
        echo '<td>' . htmlspecialchars($cell) . '</td>';
    }
    echo '</tr>';
}
echo '</table>';
// データベース接続を閉じる
$db->close();
}
?>