<?php
// URLからパラメータを取得
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$em_amount = isset($_GET['em_amount']) ? floatval($_GET['em_amount']) : 0;
$di_amount = isset($_GET['di_amount']) ? floatval($_GET['di_amount']) : 0;

if ($id === 0) {
    die("Invalid ID");
}

// データベースに接続
try {
    $db = new SQLite3(dirname(__DIR__) . '/data.db');
} catch (Exception $e) {
    die("データベースとの接続に失敗しました。: " . $e->getMessage());
}

// トランザクションを開始
$db->exec('BEGIN TRANSACTION');

try {
    // 現在の値を取得
    $stmt = $db->prepare('SELECT em_amount, di_amount FROM main_data WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $new_em_amount = $row['em_amount'] + $em_amount;
        $new_di_amount = $row['di_amount'] + $di_amount;

        // 新しい値を更新
        $update_stmt = $db->prepare('UPDATE main_data SET em_amount = :new_em_amount, di_amount = :new_di_amount WHERE id = :id');
        $update_stmt->bindValue(':new_em_amount', $new_em_amount, SQLITE3_FLOAT);
        $update_stmt->bindValue(':new_di_amount', $new_di_amount, SQLITE3_FLOAT);
        $update_stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $update_stmt->execute();
    } else {
        echo "次のIDに一致するデータがありません。: $id";
    }

    // コミット
    $db->exec('COMMIT');
    echo "em_amount=" . $new_em_amount . ",di_amount=" . $new_di_amount;
} catch (Exception $e) {
    // エラーが発生した場合はロールバック
    $db->exec('ROLLBACK');
    die("エラー: " . $e->getMessage());
}

// データベース接続を閉じる
$db->close();
?>