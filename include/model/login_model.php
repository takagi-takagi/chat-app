<?php
/**
* htmlspecialchars（特殊文字の変換）のラッパー関数
*
* @param string 
* @return string 
*/
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
* DB接続を行いPDOインスタンスを返す
* 
* @param $DSN
* @param $LOGIN_USER
* @param $PASSWORD
* @return object $pdo 
*/
function get_connection($DSN,$LOGIN_USER,$PASSWORD) {
    try{
        $pdo=new PDO($DSN,$LOGIN_USER,$PASSWORD);
    } catch (PDOException $e) {
        echo $e->getMessage();
        exit();
    }
    return $pdo;
}

/**
* ログアウトするならセッションとCookieに保存されているセッションIDを削除。しないならログインしているときチャット画面に遷移。
*/
function checklog() {
    if (isset($_POST["logout"])) {
        $session = session_name();
        $_SESSION = [];
        if (isset($_COOKIE[$session])) {
            setcookie($session, '', time() - 30, '/');
        }
    } else if (isset($_SESSION['user_id'])) {
        header('Location: chat.php');
        exit();
    }
}

/**
* POSTされたuser_nameの値を返す
*
* @return string  
*/
function store_user_name() {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['user_name'])) {
            return h($_POST['user_name']);
        } else {
            return '';
        }
    }
}

/**
* POSTされたpasswordの値を返す
*
* @return string  
*/
function store_user_password() {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['password'])) {
            return h($_POST['password']);
        } else {
            return '';
        }
    }
}

/**
* ユーザーテーブルから引数のユーザー名がテーブルのユーザー名と一致するレコードのIDとパスワードを取得し、セッションに保存し、遷移。失敗時はメッセージを返す。
*
* @param object $pdo
* @param string $userName
* @param string $passwored
* @return string  $message 失敗時のメッセージ
*/
function login($pdo, $userName, $userPassword) {
    if (isset($_POST["submit"])) {
        try{
            $sql = "SELECT user_id, user_password FROM chat_user WHERE user_name = :name;";
            $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo -> prepare($sql);
            $stmt -> bindValue(':name', $userName);
            $stmt->execute();
            while ($row = $stmt->fetch()) {
                if ($row["user_password"] === $userPassword) {
                    $_SESSION['user_id'] =  $row["user_id"];
                    $_SESSION['user_password'] =  $userPassword;
                    header('Location: chat.php');
                    exit();
                } 
            } 
            return "パスワードが一致しません。";
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit();
        }
    }
}