<?php
require_once '../config.php';

$week_offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$this_monday = strtotime("monday this week");
$target_monday = strtotime("+$week_offset week", $this_monday);

$target_dates = [];
for ($i = 0; $i < 5; $i++) {
    $target_dates[] = date('Y-m-d', strtotime("+$i day", $target_monday));
}
$start_date = $target_dates[0];
$end_date = $target_dates[4];
$weekdays = ['月', '火', '水', '木', '金'];

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['run_matching'])) {
    $teacher_shifts = [];
    $stmt = $dbh->prepare("SELECT * FROM teacher_schedules WHERE schedule_date BETWEEN ? AND ?");
    $stmt->execute([$start_date, $end_date]);
    while ($row = $stmt->fetch()) {
        if ($row['am_available']) $teacher_shifts[$row['schedule_date']]['AM'][] = $row['teacher_name'];
        if ($row['pm_available']) $teacher_shifts[$row['schedule_date']]['PM'][] = $row['teacher_name'];
    }

    $stmt = $dbh->prepare("SELECT * FROM consultations WHERE (appointment_date BETWEEN ? AND ?) OR (appointment_date_2 BETWEEN ? AND ?) OR (appointment_date_3 BETWEEN ? AND ?)");
    $stmt->execute([$start_date, $end_date, $start_date, $end_date, $start_date, $end_date]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    usort($students, function($a, $b) {
        $a_req = ($a['teacher_name'] && $a['teacher_name'] !== '指定なし') ? 1 : 0;
        $b_req = ($b['teacher_name'] && $b['teacher_name'] !== '指定なし') ? 1 : 0;
        return $b_req - $a_req;
    });

    $current_map = [];

    try {
        $dbh->beginTransaction();
        $clear_sql = "UPDATE consultations SET assigned_date=NULL, assigned_time=NULL, assigned_teacher=NULL WHERE (appointment_date BETWEEN ? AND ?) OR (appointment_date_2 BETWEEN ? AND ?) OR (appointment_date_3 BETWEEN ? AND ?)";
        $stmt_clear = $dbh->prepare($clear_sql);
        $stmt_clear->execute([$start_date, $end_date, $start_date, $end_date, $start_date, $end_date]);

        foreach ($students as $student) {
            $best_candidate = null;
            $max_score = -999999;
            $wishes = [1 => $student['appointment_date'], 2 => $student['appointment_date_2'], 3 => $student['appointment_date_3']];

            foreach ($wishes as $priority => $w_date) {
                if (empty($w_date) || !in_array($w_date, $target_dates)) continue;
                foreach (['AM', 'PM'] as $time) {
                    $available_teachers = isset($teacher_shifts[$w_date][$time]) ? $teacher_shifts[$w_date][$time] : [];
                    foreach ($available_teachers as $teacher) {
                        $score = 10000;
                        $current_count = isset($current_map[$w_date][$time][$teacher]) ? $current_map[$w_date][$time][$teacher] : 0;
                        $score -= ($current_count * 5000); 
                        
                        if ($student['teacher_name'] && $student['teacher_name'] !== '指定なし') {
                            $score += ($student['teacher_name'] === $teacher) ? 2000 : -2000;
                        }
                        $score += (4 - $priority) * 100;

                        if ($score > $max_score) {
                            $max_score = $score;
                            $best_candidate = ['date' => $w_date, 'time' => $time, 'teacher' => $teacher];
                        }
                    }
                }
            }
            if ($best_candidate) {
                $d = $best_candidate['date']; $t = $best_candidate['time']; $th = $best_candidate['teacher'];
                if (!isset($current_map[$d][$t][$th])) $current_map[$d][$t][$th] = 0;
                $current_map[$d][$t][$th]++;
                $stmt_up = $dbh->prepare("UPDATE consultations SET assigned_date=?, assigned_time=?, assigned_teacher=? WHERE id=?");
                $stmt_up->execute([$d, $t, $th, $student['id']]);
            }
        }
        $dbh->commit();
        $message = "最適化アルゴリズムによる割り当てを実行し、保存しました。";
    } catch (Exception $e) {
        $dbh->rollBack();
        $message = "エラーが発生しました: " . $e->getMessage();
    }
}

$shifts = [];
$stmt = $dbh->prepare("SELECT * FROM teacher_schedules WHERE schedule_date BETWEEN ? AND ?");
$stmt->execute([$start_date, $end_date]);
while ($row = $stmt->fetch()) {
    if ($row['am_available']) $shifts[$row['schedule_date']]['AM'][] = $row['teacher_name'];
    if ($row['pm_available']) $shifts[$row['schedule_date']]['PM'][] = $row['teacher_name'];
}

$results = [];
$stmt = $dbh->prepare("SELECT * FROM consultations WHERE assigned_date BETWEEN ? AND ?");
$stmt->execute([$start_date, $end_date]);
while ($row = $stmt->fetch()) {
    $results[$row['assigned_date']][$row['assigned_time']][$row['assigned_teacher']][] = $row;
}

$unassigned = [];
$stmt = $dbh->prepare("SELECT * FROM consultations WHERE (assigned_date IS NULL) AND ((appointment_date BETWEEN ? AND ?) OR (appointment_date_2 BETWEEN ? AND ?) OR (appointment_date_3 BETWEEN ? AND ?))");
$stmt->execute([$start_date, $end_date, $start_date, $end_date, $start_date, $end_date]);
$unassigned = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../style.css">
    <title>自動スケジューリング</title>
</head>
<body>
<div class="container">
    <h1>AI自動スケジューリング</h1>

    <div class="box box-info" style="display:flex; justify-content:space-between; align-items:center;">
        <a href="?offset=<?= $week_offset - 1 ?>" class="btn" style="margin:0; padding:5px 15px;">前へ</a>
        
        <div style="text-align:center;">
            <strong style="font-size:1.1rem;"><?= date('Y年n月j日', $target_monday) ?> の週</strong><br>
            <span class="meta-text">期間: <?= date('m/d', strtotime($start_date)) ?> 〜 <?= date('m/d', strtotime($end_date)) ?></span>
        </div>
        
        <a href="?offset=<?= $week_offset + 1 ?>" class="btn" style="margin:0; padding:5px 15px;">次へ</a>
    </div>

    <?php if($message): ?>
        <div class="box box-success">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div style="text-align:center; margin-bottom: 30px;">
        <form method="post" onsubmit="return confirm('この週のスケジュールを再計算して上書き保存しますか？');">
            <button type="submit" name="run_matching" class="run-btn">
                最適化アルゴリズムを実行する
            </button>
            <p class="meta-text" style="margin-top:8px;">
                優先順位ルール: ①重複回避 &nbsp; ②教員指名 &nbsp; ③希望順位
            </p>
        </form>
    </div>

    <?php if (count($unassigned) > 0): ?>
        <div class="box box-alert">
            <strong>マッチング不可（<?= count($unassigned) ?>名）</strong><br>
            <span style="font-size:0.9rem;">以下の生徒は、希望日（第1〜3）に対応可能な教員シフトが見つかりませんでした。</span>
            <ul style="margin:5px 0 0 20px; font-size:0.9rem;">
                <?php foreach($unassigned as $u): ?>
                    <li><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['student_id']) ?>)</li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th style="width:80px; text-align:center;">時間</th>
                <?php foreach ($target_dates as $idx => $date): ?>
                    <th style="text-align:center;"><?= date('n/j', strtotime($date)) ?> (<?= $weekdays[$idx] ?>)</th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach (['AM', 'PM'] as $time): ?>
            <tr>
                <td style="background:#f9f9f9; text-align:center; vertical-align:middle; font-weight:bold;">
                    <?= $time ?>
                </td>
                <?php foreach ($target_dates as $date): ?>
                <td style="background: #fff;">
                    <?php 
                    $teachers = isset($shifts[$date][$time]) ? $shifts[$date][$time] : [];
                    if (empty($teachers)): ?>
                        <div style="color:#ddd; text-align:center;">-</div>
                    <?php else: ?>
                        <?php foreach ($teachers as $t_name): ?>
                            <div style="border:1px solid #eee; border-radius:4px; padding:8px; margin-bottom:5px; background:#fdfdfd;">
                                <div style="font-weight:bold; color:#3498db; border-bottom:1px solid #f0f0f0; margin-bottom:5px; font-size:0.9rem;">
                                    <?= $t_name ?>
                                </div>
                                
                                <?php 
                                if (isset($results[$date][$time][$t_name])):
                                    $assigned_students = $results[$date][$time][$t_name];
                                    foreach ($assigned_students as $idx => $st):
                                        $is_overlap = ($idx > 0); 
                                        $bg_style = $is_overlap ? "background:#fff5f5; color:#c53030;" : "background:#e6fffa; color:#2c7a7b;";
                                ?>
                                    <div style="font-size:0.85rem; padding:4px; border-radius:3px; margin-top:2px; <?= $bg_style ?>">
                                        <?= htmlspecialchars($st['name']) ?>
                                        <?php if($is_overlap) echo "<span style='font-size:0.7rem;'>重複</span>"; ?>
                                    </div>
                                <?php 
                                    endforeach;
                                else: ?>
                                    <span style="color:#ccc; font-size:0.8rem;">(空き)</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="back-link">
        <a href="index.html" class="btn">メニューに戻る</a>
    </div>
</div>
</body>
</html>