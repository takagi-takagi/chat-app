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
* ログイン中ならチャット画面に遷移。
*/
function checklogin() {
    if (isset($_SESSION['user_id'])) {
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
* POSTされたuser_iconの画像を返す
*
* @return array
*/
function store_user_icon() {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_FILES['user_icon']) && $_FILES['user_icon']['name'] !== "") {
            return $_FILES['user_icon'];
        } else {
            return null;
        }
    }
    
}

/**
* ユーザー名がかぶっていないか確認
* @param object $pdo
* @param string $userName
* @return boolean
*/
function check_user($pdo, $userName) {
    try{
        $sql = "SELECT user_name FROM chat_user";
        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        if ($result = $pdo->query($sql)) {
            while ($row = $result->fetch()) {
                if ($row["user_name"] === $userName) {
                    return true;
                }
            }
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
        exit();
    }
}
/**
* ユーザーテーブル内でユーザー名が一致する同じユーザーIDを設定テーブルに挿入
*
* @param object $pdo
* @param string $userName
* @param string $passwored
* @param array $usrIcon
* @return string  メッセージ
*/
function insert_setting($pdo, $userName) {
    try{
        $pdo->beginTransaction();
        $sql = "INSERT INTO chat_setting (user_id) SELECT user_id FROM chat_user where user_name = :name;";
        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo -> prepare($sql);
        $stmt -> bindValue(':name', $userName);
        if ($stmt->execute()) {
            $pdo->commit();
            return "登録に成功しました";
        } else {
            $pdo->rollBack();
            return "登録に失敗しました";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo $e->getMessage();
        exit();
    }
}

/**
* 名前とパスワードとユーザーアイコンをユーザーテーブルに追加し、insert_setting関数で設定テーブルにも同じユーザーIDを挿入
*
* @param object $pdo
* @param string $userName
* @param string $passwored
* @param array $usrIcon
* @return string  メッセージ
*/
function insert_user($pdo, $userName, $userPassword,$userIcon) {
    try{
        $pdo->beginTransaction();
        $sql = "INSERT INTO chat_user (user_name, user_password,user_icon) VALUES(:name, :password,:icon);";
        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo -> prepare($sql);
        $stmt -> bindValue(':name', $userName);
        $stmt -> bindValue(':password', $userPassword);
        $stmt -> bindValue(':icon', $userIcon["name"]);
        if ($stmt->execute()) {
            $pdo->commit();
            insert_setting($pdo,$userName);
            return "登録に成功しました";
        } else {
            $pdo->rollBack();
            return "登録に失敗しました";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo $e->getMessage();
        exit();
    }
}

/**
* 画像名がかぶっていないか確認
*
* @param object $pdo
* @param array $userIcon
* @return boolean
*/
function check_userIcon($pdo, $userIcon) {
    try{
        $sql = "SELECT user_icon FROM chat_user";
        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        if ($result = $pdo->query($sql)) {
            while ($row = $result->fetch()) {
                if ($row["user_icon"] === $userIcon['name']) {
                    return true;
                }
            }
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
        exit();
    }
}

/**
* （画像名がかぶっているなら拡張子の前に連番をつけて）画像をアップロード。
*
* @param object $pdo
* @param array $userIcon
* @return array 画像ファイル
*/
function up_userIcon($pdo, $userIcon) {
    $i = 0;
    $extension = "." . pathinfo($userIcon['name'], PATHINFO_EXTENSION);
    $basename = basename($userIcon['name'], $extension);
    while (check_userIcon($pdo, $userIcon)) {
        if ($i != 0) {
            $userIcon['name'] = $basename . "(" . $i . ")" . $extension;
        }
        $i++;
    }
    
    $saveName ='img/' . $userIcon['name'];
    move_uploaded_file($userIcon['tmp_name'], $saveName);
    return $userIcon;
}


/**
* 入力チェックしてからinsert_user関数でユーザー新規登録
*
* @param object $pdo
* @param string $userName
* @param string $passwored
* @param string $userIcon
* @return string  $message 
*/
function registrate_user($pdo, $userName, $userPassword,$userIcon) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $message = "";
        if(!isset($userName) || $userName == "") {
            $message .= "<br>ユーザー名を入力してください";
        } else if (check_user($pdo, $userName)) {
            $message .= "<br>既に登録されている名前です";
        }
        if(!isset($userPassword) || $userPassword == "") {
            $message .= "<br>パスワードを入力してください";
        } else if (!preg_match('/^[a-zA-Z0-9]*$/', $userPassword)) {
            $message .= "<br>パスワードは半角英数字で入力してください";
        }
        if (!isset($userIcon)) {
            $message .= "<br>画像を選択してください";
        } else if (!preg_match('/.(jpeg|png|jpg)$/', $userIcon['name'])) {
            $message .= '<br>商品画像の形式は「JPEG」「PNG」でお願い致します';
        }
        if ($message === "") {
            $userIcon = up_userIcon($pdo, $userIcon);
            return insert_user($pdo,$userName,$userPassword,$userIcon);
        }
        return substr($message, 4);
    }
}