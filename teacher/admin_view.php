<?php 
require_once '../config.php';
$consultations = $dbh->query("SELECT * FROM consultations ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../style.css">
    <title>管理画面 - 面談予約一覧</title>
    <style>
        .container { max-width: 1000px; }
        .ai-box { background: #f0fdf4; padding: 10px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #dcfce7; }
        .date-list { font-size: 0.8rem; line-height: 1.4; color: #555; }
        .primary-date { color: #e67e22; font-weight: bold; font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="container">
    <h1>予約管理パネル</h1>
    <table>
        <thead>
            <tr>
                <th>希望日時（第1〜3）</th>
                <th>生徒情報</th>
                <th>相談内容</th>
                <th>AI推奨アドバイス</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($consultations as $c): ?>
            <tr>
                <td class="date-list">
                    <span class="primary-date">① <?= htmlspecialchars($c['appointment_date']) ?></span><br>
                    ② <?= htmlspecialchars($c['appointment_date_2'] ?: '未入力') ?><br>
                    ③ <?= htmlspecialchars($c['appointment_date_3'] ?: '未入力') ?>
                </td>
                <td>
                    <strong><?= htmlspecialchars($c['name']) ?></strong><br>
                    <small><?= htmlspecialchars($c['student_id']) ?></small>
                </td>
                <td><?= nl2br(htmlspecialchars($c['content'])) ?></td>
                <td>
                    <div class="ai-box">
                        <?= nl2br(htmlspecialchars($c['ai_recommendation'])) ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div style="text-align:center;"><a href="index.html" class="btn">戻る</a></div>
</div>
</body>
</html>