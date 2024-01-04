<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/style.css">
    <title>チャット機能ログイン</title>
</head>
<body>
    <h2>チャット機能・ログイン</h2>
    <?php
    if (isset($message)) {
        echo "<p class='error-message'>".$message."</p>";
    }    
    ?>
    <form method="post">
        <label for="user_name">ユーザー名</label><input type="text" id="user_name" name="user_name"><br>
        <label for="password">パスワード</label><input type="text" id="password" name="password"><br>
        <input type="submit" value="ログイン" name="submit">
   </form>
   <p><a href="user_registration.php">新規登録ページへ</a></p>
</body>
</html>