<?php
require_once 'config.php'; // データベース接続を読み込み

$success = false;
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $sql = "INSERT INTO career_surveys (student_id, name, email, first_choice, consultation) 
                VALUES (:sid, :name, :mail, :choice, :consult)";
        
        $stmt = $dbh->prepare($sql);
        $stmt->execute([
            ':sid'    => $_POST['student_id'],
            ':name'   => $_POST['name'],
            ':mail'   => $_POST['email'],
            ':choice' => $_POST['first_choice'],
            ':consult'=> $_POST['consultation']
        ]);
        $success = true;
    } catch (PDOException $e) {
        $error_msg = "保存失敗: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>送信結果 - 進路希望調査</title>
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
            <h2>調査票を受理しました</h2>
            <p>ご協力ありがとうございました。入力内容は正常に保存されました。</p>
        <?php else: ?>
            <h2>エラーが発生しました</h2>
            <p><?php echo htmlspecialchars($error_msg); ?></p>
        <?php endif; ?>
        <a href="index.html" class="back-link">トップメニューへ戻る</a>
    </div>
</body>
</html>