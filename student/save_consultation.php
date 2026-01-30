<?php
require_once '../config.php';

// ■■■ テスト用設定 ■■■
// true にすると、Google APIを呼ばずにダミーの回答で動作確認できます
$debug_mode = true; 
// ■■■■■■■■■■■■■■■■■

$success = false;
$ai_result = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_content = $_POST['content'];
    
    // config.phpで定義した定数がなければ空文字扱い
    $api_key = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';

    // 1. デバッグモード または APIキー未設定時は「ダミー回答」を使用
    if ($debug_mode || empty($api_key) || $api_key === 'ここにあなたのAPIキーを入力してください') {
        // APIを呼ばずに、成功したことにしてダミーテキストをセット
        $ai_result = "【テストモード動作】\nこれはAPIを使用しないテスト用の回答です。\n\n" .
                     "【最適担当者】A先生 (テスト判定)\n" .
                     "【理由とアドバイス】\n" .
                     "現在はテストモードで動作しています。相談内容「" . htmlspecialchars(mb_strimwidth($student_content, 0, 30, "...")) . "」を受け付けました。\n" .
                     "本番環境ではここにGemini AIからの分析結果が表示されます。";
    } else {
        // 2. 通常の Gemini API リクエスト
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $api_key;

        $prompt = "あなたは大学のキャリアセンター助手です。以下の「相談内容」に基づき、リストの中から最適な担当教員を1名選び、理由とアドバイスを回答してください。
        
        教員リスト:
        A先生: IT、エンジニア職、プログラミング、技術面接に強い。
        B先生: 公務員、事務職、履歴書添削、ビジネスマナーに強い。
        C先生: 自己分析、進路迷い、メンタルケアに強い。
        
        回答形式：
        【最適担当者】名前
        【理由とアドバイス】内容

        相談内容: $student_content";

        $data = ["contents" => [["parts" => [["text" => $prompt]]]]];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 開発環境用

        $response = curl_exec($ch);
        
        if(curl_errno($ch)){
            $error_msg = 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);

        if (!$error_msg) {
            $result = json_decode($response, true);
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $ai_result = $result['candidates'][0]['content']['parts'][0]['text'];
            } else {
                $error_msg = "AI診断失敗(API Error): <pre>" . htmlspecialchars($response) . "</pre>";
            }
        }
    }

    // 3. データベース保存 (AI回答が得られていれば実行)
    if ($ai_result && !$error_msg) {
        try {
            $sql = "INSERT INTO consultations (student_id, name, email, appointment_date, appointment_date_2, appointment_date_3, teacher_name, content, ai_recommendation) 
                    VALUES (:sid, :name, :mail, :adate, :adate2, :adate3, :tname, :content, :ai)";
            
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':sid'     => $_POST['student_id'],
                ':name'    => $_POST['name'],
                ':mail'    => $_POST['email'],
                ':adate'   => $_POST['appointment_date'],
                ':adate2'  => !empty($_POST['appointment_date_2']) ? $_POST['appointment_date_2'] : null,
                ':adate3'  => !empty($_POST['appointment_date_3']) ? $_POST['appointment_date_3'] : null,
                ':tname'   => $_POST['teacher_name'],
                ':content' => $_POST['content'],
                ':ai'      => $ai_result
            ]);
            $success = true;
        } catch (PDOException $e) {
            $error_msg = "DB保存エラー: " . $e->getMessage();
        }
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
            
            <?php if(isset($debug_mode) && $debug_mode): ?>
                <div style="background:#fff3cd; color:#856404; padding:10px; border-radius:4px; margin-bottom:15px; border:1px solid #ffeeba;">
                    <strong>⚠ テストモードで動作中:</strong> API通信は行われていません。
                </div>
            <?php endif; ?>

            <div class="highlight-box">
                <h3 style="color:#27ae60;">✨ AIマッチング診断結果</h3>
                <p><?= nl2br(htmlspecialchars($ai_result)) ?></p>
            </div>
            <p>ご入力いただいた希望日を調整し、後ほどメールでご連絡します。</p>
        <?php else: ?>
            <h2 style="color:#e74c3c;">エラーが発生しました</h2>
            <p><?= $error_msg ?></p>
        <?php endif; ?>
        <a href="index.html" class="btn">メニューに戻る</a>
    </div>
</body>
</html>