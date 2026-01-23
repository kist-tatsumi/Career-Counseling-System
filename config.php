<?php
// データベース接続設定
$host = 'localhost';
$dbname = 'career_counseling'; 
$user = 'root';
$pass = 'root';

// Google Gemini APIキー設定
define('GEMINI_API_KEY', 'ここにあなたのAPIキーを入力してください');

try {
    $dbh = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    exit('データベース接続失敗: ' . $e->getMessage());
}
?>