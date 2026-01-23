<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>進路希望調査</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="container">
    <h2>進路希望調査フォーム</h2>
    <form action="save_survey.php" method="POST">
        <div class="form-group">
            <label>学籍番号</label>
            <input type="text" name="student_id" required>
        </div>
        <div class="form-group">
            <label>お名前</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>メールアドレス</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>卒業後の進路（第一希望）</label>
            <select name="first_choice" required>
                <option value="">-- 選択してください --</option>
                <option value="民間企業">民間企業</option>
                <option value="公務員">公務員</option>
                <option value="進学">進学</option>
                <option value="その他">その他</option>
            </select>
        </div>
        <div class="form-group">
            <label>進路に関して相談したいこと</label>
            <textarea name="consultation" rows="4"></textarea>
        </div>
        <button type="submit">送信する</button>
    </form>
    <div style="text-align: center; margin-top: 20px;">
        <a href="index.html" class="btn">トップメニューに戻る</a>
    </div>
</div>
</body>
</html>