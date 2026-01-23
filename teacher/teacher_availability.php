<?php require_once '../config.php';
$teacher = "A先生";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach (['月', '火', '水', '木', '金'] as $day) {
        $am = isset($_POST["am_$day"]) ? 1 : 0;
        $pm = isset($_POST["pm_$day"]) ? 1 : 0;
        $stmt = $dbh->prepare("INSERT INTO teacher_schedules (teacher_name, day_of_week, am_available, pm_available) 
                               VALUES (?, ?, ?, ?) 
                               ON DUPLICATE KEY UPDATE am_available=?, pm_available=?");
        $stmt->execute([$teacher, $day, $am, $pm, $am, $pm]);
    }
    $message = "スケジュールを更新しました。";
}

// 現在のデータを取得（チェック状態を復元するため）
$current_data = [];
$res = $dbh->prepare("SELECT * FROM teacher_schedules WHERE teacher_name = ?");
$res->execute([$teacher]);
while($row = $res->fetch()){
    $current_data[$row['day_of_week']] = $row;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../style.css">
    <title>予定設定</title>
</head>
<body>
<div class="container" style="background: #f8f9fa; box-shadow: none;">
    <h2 style="border:none;"><?php echo $teacher; ?> の勤務設定</h2>
    <p style="text-align:center; color:#666; margin-bottom:20px;">面談・面接が可能な時間帯を「空き」にしてください</p>
    
    <?php if(isset($message)) echo "<p style='color:green; text-align:center; font-weight:bold;'>$message</p>"; ?>

    <form method="post">
        <table class="availability-table">
            <thead>
                <tr><th>曜日</th><th>午前 (9:00-12:00)</th><th>午後 (13:00-17:00)</th></tr>
            </thead>
            <tbody>
                <?php foreach (['月', '火', '水', '木', '金'] as $day): 
                    $am_check = (isset($current_data[$day]) && $current_data[$day]['am_available']) ? "checked" : "";
                    $pm_check = (isset($current_data[$day]) && $current_data[$day]['pm_available']) ? "checked" : "";
                ?>
                <tr>
                    <td><?php echo $day; ?>曜</td>
                    <td>
                        <label class="check-container">
                            <input type="checkbox" name="am_<?php echo $day; ?>" <?php echo $am_check; ?>>
                            <span class="checkmark-label"></span>
                        </label>
                    </td>
                    <td>
                        <label class="check-container">
                            <input type="checkbox" name="pm_<?php echo $day; ?>" <?php echo $pm_check; ?>>
                            <span class="checkmark-label"></span>
                        </label>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" style="margin-top: 20px; background-color: #2c3e50;">スケジュールを保存する</button>
    </form>
    <div style="text-align: center;">
        <a href="index.html" class="btn">管理メニューに戻る</a>
    </div>
</div>
</body>
</html>