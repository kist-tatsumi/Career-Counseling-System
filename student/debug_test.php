<?php
$api_key = 'AIzaSyAd7pBShtmuOeuaImulC3wW51hl2Ixym1c'; // ここを差し替え
// URLの中の「v1beta」を「v1」に変更し、モデルの指定方法をより確実なものに修正します
$url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=" . $api_key;

$data = [
    "contents" => [
        ["parts" => [["text" => "こんにちは、接続テストです。"]]]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
echo "--- Googleからの返答 ---\n";
echo $response; 
echo "\n--- エラー詳細 ---\n";
echo curl_error($ch);
curl_close($ch);
?>