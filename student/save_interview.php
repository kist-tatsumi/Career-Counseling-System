<?php require_once '../config.php';
try {
    $stmt = $dbh->prepare("INSERT INTO interviews (student_id, name, email, appointment_date, pref_teacher_1, pref_teacher_2, pref_teacher_3) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$_POST['student_id'],$_POST['name'],$_POST['email'],$_POST['appointment_date'],$_POST['pref_teacher_1'],$_POST['pref_teacher_2'],$_POST['pref_teacher_3']]);
    $msg = "予約を受理しました。";
} catch (Exception $e) { $msg = "エラー: " . $e->getMessage(); }
?>
<!DOCTYPE html>
<html lang="ja"><head><meta charset="UTF-8"><link rel="stylesheet" href="../style.css"></head>
<body><div class="container"><h2>結果</h2><p><?php echo $msg; ?></p><a href="index.html" class="btn">戻る</a></div></body>
</html>