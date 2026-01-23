<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>面接予約 - キャリア支援</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="container">
    <h2>模擬面接・面接予約</h2>
    <form action="save_interview.php" method="post">
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
            <label>予約日付</label>
            <input type="date" name="appointment_date" required>
        </div>

        <div class="highlight-box">
            <label>希望面接担当者</label>
            <p><label>第1希望</label>
            <select name="pref_teacher_1" required>
                <option value="">-- 選択してください --</option>
                <option value="A先生">A先生</option>
                <option value="B先生">B先生</option>
                <option value="C先生">C先生</option>
            </select></p>
            <p><label>第2希望</label>
            <select name="pref_teacher_2">
                <option value="">-- 選択してください --</option>
                <option value="A先生">A先生</option>
                <option value="B先生">B先生</option>
                <option value="C先生">C先生</option>
            </select></p>
            <p><label>第3希望</label>
            <select name="pref_teacher_3">
                <option value="">-- 選択してください --</option>
                <option value="A先生">A先生</option>
                <option value="B先生">B先生</option>
                <option value="C先生">C先生</option>
            </select></p>
        </div>
        <button type="submit">予約を確定する</button>
    </form>
    <div style="text-align: center; margin-top: 20px;">
        <a href="index.html" class="btn">トップメニューに戻る</a>
    </div>
</div>
</body>
</html>