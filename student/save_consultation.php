<?php
require_once '../config.php';

// --- 設定 ---
$api_key = 'ここにあなたのGEMINI_API_KEYを貼る';
$success = false;
$ai_result = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_content = $_POST['content'];
    
    // 1. Gemini API リクエスト (URLを安定版のv1beta/models形式に)
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $api_key;

    $prompt = "あなたは大学のキャリアセンター助手です。以下の「相談内容」に基づき、リストの中から最適な担当教員を1名選び、理由とアドバイスを回答してください。
    形式：【最適担当者】名前 \n 【理由とアドバイス】内容
    
    教員リスト:
    A先生: IT、エンジニア職、プログラミング、技術面接に強い。
    B先生: 公務員、事務職、履歴書添削、ビジネスマナーに強い。
    C先生: 自己分析、進路迷い、メンタルケアに強い。

    相談内容: $student_content";

    $data = ["contents" => [["parts" => [["text" => $prompt]]]]];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Windows環境でのSSLエラー回避
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch);

    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $ai_result = $result['candidates'][0]['content']['parts'][0]['text'];
        
        // 2. データベース保存 (第1〜第3希望日を保存)
        try {
            $sql = "INSERT INTO consultations (student_id, name, email, appointment_date, appointment_date_2, appointment_date_3, teacher_name, content, ai_recommendation) 
                    VALUES (:sid, :name, :mail, :adate, :adate2, :adate3, :tname, :content, :ai)";
            
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':sid'     => $_POST['student_id'],
                ':name'    => $_POST['name'],
                ':mail'    => $_POST['email'],
                ':adate'   => $_POST['appointment_date'],
                ':adate2'  => $_POST['appointment_date_2'] ?: null,
                ':adate3'  => $_POST['appointment_date_3'] ?: null,
                ':tname'   => $_POST['teacher_name'],
                ':content' => $_POST['content'],
                ':ai'      => $ai_result
            ]);
            $success = true;
        } catch (PDOException $e) {
            $error_msg = "DB保存エラー: " . $e->getMessage();
        }
    } else {
        $error_msg = "AI診断に失敗しました。APIキーまたはURL設定を確認してください。";
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../style.css">
    <title>送信結果</title>
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
            <h2>予約を受け付けました</h2>
            <div class="highlight-box">
                <h3 style="color:#27ae60;">✨ AIマッチング診断結果</h3>
                <p><?= nl2br(htmlspecialchars($ai_result)) ?></p>
            </div>
            <p>ご入力いただいた希望日を調整し、後ほどメールでご連絡します。</p>
        <?php else: ?>
            <h2 style="color:#e74c3c;">エラー</h2>
            <p><?= htmlspecialchars($error_msg) ?></p>
        <?php endif; ?>
        <a href="index.html" class="btn">メニューに戻る</a>
    </div>
</body>
</html>