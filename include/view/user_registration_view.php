<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/style.css">
    <title>ユーザー登録</title>
</head>
<body>
    <h2>ユーザー登録</h2>
    <?php
    if (isset($message)) {
        if ($message === "登録に成功しました") {
            echo "<p class='success-message'>".$message."</p>";
        } else {
            echo "<p class='error-message'>".$message."</p>";
        }
    }    
    ?>
    <form method="post" enctype="multipart/form-data">
        <label for="user_name">ユーザー名</label><input type="text" class="validate[required]" id="user_name" name="user_name"><br>
        <label for="password">パスワード</label><input type="text" id="password" name="password" class="validate[required]"><br>
        <label for="user_icon">アイコン　</label><input type="file" id="user_icon" name="user_icon" class="validate[required]"><br>
        <input type="submit" value="登録">
    </form>
    <p><a href="login.php">ログインページへ</a></p>
</body>
</html>