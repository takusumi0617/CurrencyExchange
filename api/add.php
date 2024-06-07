<?php
// エラーレポートを設定
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// URLからidとpasswordを取得
$id = isset($_GET['id']) ? $_GET['id'] : null;
$password = isset($_GET['password']) ? $_GET['password'] : null;

if ($id && $password) {
    try {
        // SQLite3データベースに接続
        $db = new SQLite3(dirname(__DIR__) . '/data.db');
        // データをインサートするための準備
        $stmt = $db->prepare('INSERT INTO main_data (id, password) VALUES (:id, :password)');
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':password', $password);

        // インサート実行
        $result = $stmt->execute();

        // 成功メッセージ
        if ($result) {
            echo "データの保存に成功しました。";
        } else {
            echo "データの保存に失敗しました。";
        }

        // クローズ
        $stmt->close();
        $db->close();
    } catch (Exception $e) {
        echo "エラー: " . $e->getMessage();
    }
} else {
    echo "idまたはpasswordが指定されていません。";
}
?>