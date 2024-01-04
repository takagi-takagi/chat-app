<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>chat画面</title>
</head>
<body>
    <header>
        <?php
        echo "<img class='login-user-icon' src=img/{$loginUserInfo['user_icon']}>";
        echo $loginUserInfo["user_name"]."でログイン中";
        ?>
        <form method="post" action="login.php">
            <button type='submit' name="logout" value="logout">ログアウト</button>
        </form>
        <?php 
            if ($sort == "ASC") {
                echo "
                    <form method='post'>
                        <button type='submit' name='sort' value='desc'>新しい順へ</button>
                    </form>
                ";
            } else if ($sort == "DESC") {
                echo "
                    <form method='post'>
                        <button type='submit' name='sort' value='asc'>古い順へ</button>
                    </form>
                ";
            }
        ?>
        
    </header>
    <div class="container">
        <?php
            if ($sort == "DESC") {
                echo "
                    <form method='post'>
                        <input type='hidden' name='post-username' value='post-username'>
                        <label for='post-textarea'></label>
                        <div class='form-flex'>
                            <textarea id='post-textarea' type='text' name='post-textarea' rows='1'></textarea>
                            <label for='submit-button' class='submit-button' id='submit-label'>
                                <span class='fa fa-paper-plane'></span>
                            </label>
                        </div>
                        <input type='submit' id='submit-button' name='submit-button' disabled>
                    </form>
                ";
            }
            foreach($messageData as $value) {
                if (isset($value["line"])) {
                    echo "<div class='lines-between-dates'>{$value['line']}</div>";
                }
                echo "
                    <div class='message-wrapper'>
                        <img class='user-icon' src='img/{$value["icon"]}' alt='ユーザーの画像'>
                        <div class='message-right'>
                            <div class='info'>
                                <div class='username'>{$value["name"]}</div>
                                <div class='time-stamp'>{$value["timestamp"]}</div>
                ";
                if ($value["user_id"] == $_SESSION["user_id"] && $value["delete_flg"] == 0) {
                    echo "
                                <form method='post'>
                                    <button type='submit' name='delete-message' value={$value['message_id']}}>メッセージを削除</button>
                                    <div class='clear'></div>
                                </form>
                    ";
                                }
                echo "
                            </div>
                ";
                if ($value["delete_flg"] == 0) {
                    echo "<div class='message-text'>{$value["message"]}</div>";
                } else {
                    echo "<div class='message-text deleted'>このメッセージは削除されました。</div>";
                }
                echo "
                        </div>
                    </div>
                ";
            }
            if ($sort == "ASC") {
                echo "
                    <form method='post'>
                        <input type='hidden' name='post-username' value='post-username'>
                        <label for='post-textarea'></label>
                        <div class='form-flex'>
                            <textarea id='post-textarea' type='text' name='post-textarea' rows='1'></textarea>
                            <label for='submit-button' class='submit-button' id='submit-label'>
                                <span class='fa fa-paper-plane'></span>
                            </label>
                        </div>
                        <input type='submit' id='submit-button' name='submit-button' disabled>
                    </form>
                ";
            }
        ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="js/chat.js"></script>
</body>
</html>