<?php
$host = 'localhost';
$dbname = 'career_counseling'; 
$user = 'root';
$pass = 'root';
try {
    $dbh = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit('接続失敗: ' . $e->getMessage());
}
?>