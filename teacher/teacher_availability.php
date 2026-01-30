<?php 
require_once '../config.php';

// --- 設定値の取得 ---
$teacher = isset($_GET['teacher']) ? $_GET['teacher'] : "A先生";
$teacher_list = ["A先生", "B先生", "C先生"];
// 週のオフセット（0=今週, 1=来週...）
$week_offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// --- 日付計算ロジック ---
// 今週の月曜日を基準にする
$this_monday = strtotime("monday this week");
// 選択された週の月曜日
$target_monday = strtotime("+$week_offset week", $this_monday);
// 表示対象の5日間（月〜金）の日付配列を作成
$target_dates = [];
for ($i = 0; $i < 5; $i++) {
    $target_dates[] = date('Y-m-d', strtotime("+$i day", $target_monday));
}

$message = "";

// --- 保存処理 ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $posted_teacher = $_POST['teacher_name'];
    
    // セキュリティチェック
    if (in_array($posted_teacher, $teacher_list)) {
        $teacher = $posted_teacher;
        
        // 「向こう4週間分まとめて登録」チェックボックス
        $bulk_update = isset($_POST['bulk_update']) ? true : false;
        $weeks_to_process = $bulk_update ? 4 : 1; // 1週間分か、4週間分か

        // 週間ループ
        for ($w = 0; $w < $weeks_to_process; $w++) {
            // このループで処理する週の月曜日
            $current_base_monday = strtotime("+$w week", $target_monday);
            
            // 月〜金の5日間を処理
            for ($d = 0; $d < 5; $d++) {
                // 処理対象の日付 (YYYY-MM-DD)
                $date_str = date('Y-m-d', strtotime("+$d day", $current_base_monday));
                // フォームのキー名 (例: am_0, am_1...) 
                // ※一括登録時は「元のフォームの入力値(0〜4)」をそのまま全週に適用する
                $am = isset($_POST["am_$d"]) ? 1 : 0;
                $pm = isset($_POST["pm_$d"]) ? 1 : 0;

                $stmt = $dbh->prepare("INSERT INTO teacher_schedules (teacher_name, schedule_date, am_available, pm_available) 
                                       VALUES (?, ?, ?, ?) 
                                       ON DUPLICATE KEY UPDATE am_available=?, pm_available=?");
                $stmt->execute([$teacher, $date_str, $am, $pm, $am, $pm]);
            }
        }
        
        $message = $bulk_update 
            ? "{$teacher}のスケジュールを4週間分まとめて更新しました。" 
            : "スケジュールを更新しました。";
    }
}

// --- 現在のデータを取得 ---
// 表示対象の週のデータをDBから引く
$current_data = [];
$placeholders = implode(',', array_fill(0, count($target_dates), '?'));
$sql = "SELECT * FROM teacher_schedules WHERE teacher_name = ? AND schedule_date IN ($placeholders)";
$stmt = $dbh->prepare($sql);
$stmt->execute(array_merge([$teacher], $target_dates));

while($row = $stmt->fetch()){
    $current_data[$row['schedule_date']] = $row;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../style.css">
    <title>スケジュール設定</title>
    <style>
        .week-nav { display: flex; justify-content: center; align-items: center; gap: 20px; margin: 20px 0; }
        .week-nav a { text-decoration: none; font-weight: bold; color: #3498db; font-size: 1.2rem; }
        .week-nav span { font-size: 1.1rem; font-weight: bold; }
        .bulk-option { background: #fff3cd; padding: 10px; border-radius: 6px; text-align: center; margin-top: 15px; border: 1px solid #ffeeba; }
    </style>
</head>
<body>
<div class="container" style="background: #f8f9fa;">
    <h2>教員スケジュール設定</h2>
    
    <div style="text-align:center;">
        <select onchange="location.href='?teacher='+this.value+'&offset=<?= $week_offset ?>'" style="width:auto; display:inline-block;">
            <?php foreach($teacher_list as $t): ?>
                <option value="<?= $t ?>" <?= $teacher === $t ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if($message) echo "<p style='color:green; text-align:center; font-weight:bold; margin-top:10px;'>$message</p>"; ?>

    <div class="week-nav">
        <a href="?teacher=<?= $teacher ?>&offset=<?= $week_offset - 1 ?>">« 前の週</a>
        <span><?= date('Y年n月j日', $target_monday) ?> の週</span>
        <a href="?teacher=<?= $teacher ?>&offset=<?= $week_offset + 1 ?>">次の週 »</a>
    </div>

    <form method="post">
        <input type="hidden" name="teacher_name" value="<?= htmlspecialchars($teacher) ?>">
        
        <table class="availability-table">
            <thead>
                <tr><th>日付</th><th>午前 (9:00-12:00)</th><th>午後 (13:00-17:00)</th></tr>
            </thead>
            <tbody>
                <?php 
                $weekdays = ['月', '火', '水', '木', '金'];
                foreach ($target_dates as $index => $date): 
                    $day_label = $weekdays[$index];
                    // DBにデータがあればチェック状態にする
                    $am_check = (isset($current_data[$date]) && $current_data[$date]['am_available']) ? "checked" : "";
                    $pm_check = (isset($current_data[$date]) && $current_data[$date]['pm_available']) ? "checked" : "";
                ?>
                <tr>
                    <td>
                        <?= date('n/j', strtotime($date)) ?> (<?= $day_label ?>)
                    </td>
                    <td>
                        <label class="check-container">
                            <input type="checkbox" name="am_<?= $index ?>" <?= $am_check ?>>
                            <span class="checkmark-label"></span>
                        </label>
                    </td>
                    <td>
                        <label class="check-container">
                            <input type="checkbox" name="pm_<?= $index ?>" <?= $pm_check ?>>
                            <span class="checkmark-label"></span>
                        </label>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="bulk-option">
            <label>
                <input type="checkbox" name="bulk_update" value="1">
                <strong>このパターンを「向こう4週間分」まとめて登録する</strong>
            </label>
            <p style="font-size:0.85rem; margin:5px 0 0; color:#666;">※チェックを入れると、表示中の週と同じ設定が4週間後までコピーされます。</p>
        </div>

        <button type="submit" style="margin-top: 20px; background-color: #2c3e50;">保存する</button>
    </form>
    
    <div style="text-align: center;">
        <a href="index.html" class="btn">管理メニューに戻る</a>
    </div>
</div>
</body>
</html>