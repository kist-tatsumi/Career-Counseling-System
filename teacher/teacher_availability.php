<?php 
require_once '../config.php';

// 教員の選択（GETパラメータで切り替え、デフォルトはA先生）
$teacher = isset($_GET['teacher']) ? $_GET['teacher'] : "A先生";
$teacher_list = ["A先生", "B先生", "C先生"];

// POST送信時の処理（スケジュール保存）
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // フォームから送信された教員名を使用（セキュリティのため検証リストと比較）
    $posted_teacher = $_POST['teacher_name'];
    if (in_array($posted_teacher, $teacher_list)) {
        $teacher = $posted_teacher; // 表示用の変数も更新
        
        foreach (['月', '火', '水', '木', '金'] as $day) {
            $am = isset($_POST["am_$day"]) ? 1 : 0;
            $pm = isset($_POST["pm_$day"]) ? 1 : 0;
            
            // setup.sql で定義した teacher_schedules テーブルを使用
            $stmt = $dbh->prepare("INSERT INTO teacher_schedules (teacher_name, day_of_week, am_available, pm_available) 
                                   VALUES (?, ?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE am_available=?, pm_available=?");
            $stmt->execute([$teacher, $day, $am, $pm, $am, $pm]);
        }
        $message = "{$teacher} のスケジュールを更新しました。";
    } else {
        $error = "不正な教員名です。";
    }
}

// 現在のデータを取得（チェック状態を復元）
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
<div class="container" style="background: #f8f9fa;">
    <h2>教員スケジュール設定</h2>
    
    <div style="text-align:center; margin-bottom:20px;">
        <label>設定する教員を選択：</label>
        <select onchange="location.href='?teacher='+this.value" style="width:auto; display:inline-block;">
            <?php foreach($teacher_list as $t): ?>
                <option value="<?= $t ?>" <?= $teacher === $t ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <h3 style="text-align:center; border:none;"><?= htmlspecialchars($teacher) ?> の勤務設定</h3>
    
    <?php if(isset($message)) echo "<p style='color:green; text-align:center; font-weight:bold;'>$message</p>"; ?>
    <?php if(isset($error)) echo "<p style='color:red; text-align:center;'>$error</p>"; ?>

    <form method="post">
        <input type="hidden" name="teacher_name" value="<?= htmlspecialchars($teacher) ?>">
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
                    <td><?= $day ?>曜</td>
                    <td>
                        <label class="check-container">
                            <input type="checkbox" name="am_<?= $day ?>" <?= $am_check ?>>
                            <span class="checkmark-label"></span>
                        </label>
                    </td>
                    <td>
                        <label class="check-container">
                            <input type="checkbox" name="pm_<?= $day ?>" <?= $pm_check ?>>
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