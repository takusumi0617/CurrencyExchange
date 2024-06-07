<?php
// ../api/interest_add.php

function updateDatabase() {
    try {
        // SQLite3データベースに接続
        $db = new SQLite3('/var/www/emse/data.db');

        // トランザクションを開始
        $db->exec('BEGIN TRANSACTION');

        // SQLクエリを定義
        $sql = "
        UPDATE main_data
        SET
            em_amount = CASE
                WHEN t_out < datetime('now')
                THEN em_amount + em_t_amount
                ELSE em_amount + em_t_amount * t_interest
            END,
            di_amount = CASE
                WHEN t_out < datetime('now')
                THEN di_amount + di_t_amount
                ELSE di_amount + di_t_amount * t_interest
            END,
            em_t_amount = CASE
                WHEN t_out < datetime('now')
                THEN 0
                ELSE em_t_amount
            END,
            di_t_amount = CASE
                WHEN t_out < datetime('now')
                THEN 0
                ELSE di_t_amount
            END,
            t_interest = CASE
                WHEN t_out < datetime('now')
                THEN NULL
                ELSE t_interest
            END,
            t_in = CASE
                WHEN t_out < datetime('now')
                THEN NULL
                ELSE t_in
            END,
            t_out = CASE
                WHEN t_out < datetime('now')
                THEN NULL
                ELSE t_out
            END
        WHERE t_out IS NOT NULL;
        ";

        // クエリを実行
        if ($db->exec($sql) === false) {
            throw new Exception("Failed to execute update query");
        }

        // トランザクションをコミット
        $db->exec('COMMIT');

        // 接続を閉じる
        $db->close();

        return true;
    } catch (Exception $e) {
        // エラーが発生した場合、トランザクションをロールバック
        if (isset($db)) {
            $db->exec('ROLLBACK');
        }
        error_log("Error: " . $e->getMessage());
        return false;
    }
}

// CLIまたはHTTPリクエストから実行された場合にデータベースを更新
if (php_sapi_name() === 'cli' || (php_sapi_name() !== 'cli' && isset($_GET['run']))) {
    $success = updateDatabase();
    echo $success ? "Update successful" : "Update failed";
}
?>