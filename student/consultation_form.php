<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../style.css">
    <title>進路相談・面談予約</title>
</head>
<body>
    <div class="container">
        <h1>進路相談・面談予約</h1>
        <form action="save_consultation.php" method="POST">
            <div class="form-group">
                <label>学籍番号</label>
                <input type="text" name="student_id" required placeholder="例: 2024CS001">
            </div>
            <div class="form-group">
                <label>お名前</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>メールアドレス</label>
                <input type="email" name="email" required>
            </div>

            <div class="highlight-box">
                <div class="form-group">
                    <label>面談希望日（第1希望）</label>
                    <input type="date" name="appointment_date" required>
                </div>
                <div class="form-group">
                    <label>面談希望日（第2希望）</label>
                    <input type="date" name="appointment_date_2">
                </div>
                <div class="form-group">
                    <label>面談希望日（第3希望）</label>
                    <input type="date" name="appointment_date_3">
                </div>
            </div>

            <div class="form-group">
                <label>相談したい先生（任意）</label>
                <select name="teacher_name">
                    <option value="指定なし">指定なし（AIに任せる）</option>
                    <option value="A先生">A先生（IT・エンジニア系）</option>
                    <option value="B先生">B先生（公務員・事務系）</option>
                    <option value="C先生">C先生（自己分析・メンタル）</option>
                </select>
            </div>
            <div class="form-group">
                <label>相談内容（具体的に記入してください）</label>
                <textarea name="content" rows="5" required placeholder="どのようなことで悩んでいますか？"></textarea>
            </div>
            <button type="submit">AIマッチングを開始して予約する</button>
        </form>
        <a href="index.html" class="back-link">メニューに戻る</a>
    </div>
</body>
</html>