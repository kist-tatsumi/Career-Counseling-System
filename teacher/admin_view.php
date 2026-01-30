<?php 
require_once '../config.php';

$consultations = $dbh->query("SELECT * FROM consultations ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$interviews = [];
try {
    $interviews = $dbh->query("SELECT * FROM interviews ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../style.css">
    <title>予約管理</title>
</head>
<body>
<div class="container">
    <h1>予約管理パネル</h1>

    <h2>進路相談・面談予約 (AI診断あり)</h2>
    
    <?php if (count($consultations) > 0): ?>
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">希望日時</th>
                <th style="width: 25%;">生徒情報</th>
                <th style="width: 55%;">相談内容・AI診断</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($consultations as $c): ?>
            <tr>
                <td>
                    <div>① <?= htmlspecialchars($c['appointment_date']) ?></div>
                    <?php if(!empty($c['appointment_date_2'])): ?>
                        <div class="meta-text">② <?= htmlspecialchars($c['appointment_date_2']) ?></div>
                    <?php endif; ?>
                    <?php if(!empty($c['appointment_date_3'])): ?>
                        <div class="meta-text">③ <?= htmlspecialchars($c['appointment_date_3']) ?></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($c['assigned_date'])): ?>
                        <div style="margin-top:10px;">
                            <span class="status-badge badge-blue">
                                確定: <?= htmlspecialchars($c['assigned_date']) ?> 
                                (<?= htmlspecialchars($c['assigned_time']) ?>)
                            </span>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?= htmlspecialchars($c['name']) ?></strong><br>
                    <div class="meta-text"><?= htmlspecialchars($c['student_id']) ?></div>
                    <div class="meta-text"><?= htmlspecialchars($c['email']) ?></div>
                </td>
                <td>
                    <div style="margin-bottom:8px;">
                        <?= nl2br(htmlspecialchars(mb_strimwidth($c['content'], 0, 300, "..."))) ?>
                    </div>
                    
                    <?php if(!empty($c['teacher_name']) && $c['teacher_name'] !== '指定なし'): ?>
                        <span class="status-badge">指名: <?= htmlspecialchars($c['teacher_name']) ?></span>
                    <?php endif; ?>

                    <?php if(!empty($c['ai_recommendation'])): ?>
                    <div class="box box-info" style="margin-top:10px; font-size:0.9rem;">
                        <strong>AIアドバイス:</strong><br>
                        <?= nl2br(htmlspecialchars($c['ai_recommendation'])) ?>
                    </div>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="box box-info" style="text-align:center;">現在、相談予約はありません。</div>
    <?php endif; ?>


    <h2 style="border-color: #c0392b;">模擬面接・面接練習</h2>
    
    <?php if (count($interviews) > 0): ?>
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">希望日時</th>
                <th style="width: 25%;">生徒情報</th>
                <th style="width: 50%;">希望担当教員</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($interviews as $i): ?>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($i['appointment_date']) ?></strong>
                </td>
                <td>
                    <strong><?= htmlspecialchars($i['name']) ?></strong><br>
                    <div class="meta-text"><?= htmlspecialchars($i['student_id']) ?></div>
                </td>
                <td>
                    <div>第1希望: <strong><?= htmlspecialchars($i['pref_teacher_1']) ?></strong></div>
                    <?php if(!empty($i['pref_teacher_2'])): ?>
                        <div class="meta-text">第2: <?= htmlspecialchars($i['pref_teacher_2']) ?></div>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="box box-info" style="text-align:center;">現在、面接予約はありません。</div>
    <?php endif; ?>

    <div class="back-link">
        <a href="index.html" class="btn">メニューに戻る</a>
    </div>
</div>
</body>
</html>