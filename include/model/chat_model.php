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
*
* ログインしていないならログインページに遷移。
*/
function checklogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}
/**
* ログインしているユーザーのアイコンと名前の情報を得る
* 
* @param object $pdo 
* @return array 
*/
function get_login_user_info($pdo) {
    try{
        $sql ="SELECT user_name,user_icon FROM chat_user WHERE user_id = :id;";
        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo -> prepare($sql);
        $stmt -> bindValue(':id', $_SESSION["user_id"]);
        $stmt->execute();
        while($row = $stmt->fetch()) {
            $loginUserInfo = [
                "user_icon" => $row["user_icon"],
                "user_name" => $row["user_name"]
            ];
        }
        return $loginUserInfo;
    } catch (PDOException $e) {
        echo $e->getMessage();
        exit();
    }
}
/**
* postされたメッセージをメッセージテーブルに追加
* 
* @param object $pdo
*/
function post_message($pdo) {
    if(isset($_POST["post-textarea"]) && $_POST["post-textarea"] != "") {
        $postTextarea = h($_POST["post-textarea"]);
        try{
            $sql = "INSERT INTO chat_message (user_id, message, timestamp) VALUES(:id, :message,current_timestamp());";
            $pdo->beginTransaction();
            $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo -> prepare($sql);
            $stmt -> bindValue(':id', $_SESSION["user_id"]);
            $stmt -> bindValue(':message', $postTextarea);
            $stmt->execute();
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo $e->getMessage();
            exit();
        }
    }
}
/**
* postされたメッセージidに従って、メッセージテーブルのメッセージ本文を空文字、削除フラグを1に変更
* 
* @param object $pdo
*/
function post_delete($pdo) {
    if(isset($_POST["delete-message"])) {
        $delete_message = h($_POST["delete-message"]);
        try{
            $sql = "UPDATE chat_message SET message = '',delete_flg = 1 WHERE message_id = :id;";
            $pdo->beginTransaction();
            $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo -> prepare($sql);
            $stmt -> bindValue(':id', $delete_message);
            $stmt->execute();
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo $e->getMessage();
            exit();
        }
    }
}
/**
* postされたsortの値に従って、設定テーブルの降順昇順を変更
* 
* @param object $pdo
*/
function post_change_sort($pdo) {
    if(isset($_POST["sort"])) {
        $sort = h($_POST["sort"]);
        if ($sort == "desc") {
            $flg = 1;
        } else {
            $flg = 0;
        }
        try{
            $sql = "UPDATE chat_setting SET desc_flg = :flg WHERE user_id = :id;";
            $pdo->beginTransaction();
            $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo -> prepare($sql);
            $stmt -> bindValue(':id', $_SESSION["user_id"]);
            $stmt -> bindValue(':flg', $flg);
            $stmt->execute();
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo $e->getMessage();
            exit();
        }
    }
}

/**
* postされた値に従って設定テーブルを変更する関数 をまとめて実行
* 
* @param object $pdo
*/
function post_setting($pdo) {
    post_change_sort($pdo);
}

/**
* 設定テーブルから昇順降順の設定を得る
* 
* @param object $pdo
* @return string "DESC" or "ASC"
*/
function get_sort($pdo) {
    $sql = "SELECT desc_flg FROM chat_setting WHERE user_id = ". $_SESSION['user_id'] . ";";
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $result = $pdo->query($sql);
    while ($row = $result->fetch()) {
        if ($row["desc_flg"] == 1){
            return "DESC";
        } else {
            return "ASC";
        }
    }
}

/**
* ユーザーテーブルとメッセージテーブルからメッセージのデータを得る
* 
* @param object $pdo
* @param string $sort "DESC" or "ASC"
* @return array メッセージのデータ
*/
function get_messageData($pdo,$sort) {
    $messageData=[];
    try{
        $sql = "SELECT chat_user.user_id,chat_user.user_icon, chat_user.user_name,chat_message.message_id,chat_message.message,chat_message.timestamp,chat_message.delete_flg FROM chat_message INNER JOIN chat_user ON chat_message.user_id = chat_user.user_id ORDER BY chat_message.timestamp ". $sort.";";
        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $result = $pdo->query($sql);
        while ($row = $result->fetch()) {
            $data = [
                "user_id" => $row["user_id"],
                "icon" => $row["user_icon"],
                "name" => $row["user_name"],
                "message_id" => $row["message_id"],
                "message" => nl2br($row["message"]),
                "timestamp" => $row["timestamp"],
                "delete_flg" => $row["delete_flg"]
            ];
            array_push($messageData,$data);
        }
        return $messageData;
    } catch (PDOException $e) {
        echo $e->getMessage();
        exit();
    }
}

/**
* メッセージのデータに日付の境界線の情報を追加する
* 
* @param object $pdo
* @param array $messageData メッセージのデータ　改定前
* @return array メッセージのデータ　改定後
*/
function draw_lines_between_dates($pdo,$messageData) {
    $array = $messageData;
    $week = array('日', '月', '火', '水', '木', '金', '土');
    foreach($messageData as $key => &$value) {
        $thisTimestamp = strtotime($value["timestamp"]);
        $thisData = date('Y/m/d', $thisTimestamp);
        if(isset($messageData[$key - 1])) {
            if($thisData != date('Y/m/d', strtotime($messageData[$key - 1]["timestamp"]))) {
                $flg = true;
            } else {
                $flg = false;
            }
        } else {
            $flg = true;
        }
        if($flg) {
            if ($thisData == date('Y/m/d')) {
                $value["line"] = "今日";
            } else if ($thisData == date('Y/m/d', strtotime('-1 day'))) {
                $value["line"] = "昨日";
            } else if (date('Y', $thisTimestamp) == date('Y')){
                $w = $week[date('w', $thisTimestamp)];
                $value["line"] = date('m月d日', $thisTimestamp) . "(" . $w .  ")";
            } else {
                $w = $week[date('w', $thisTimestamp)];
                $value["line"] = date('Y年m月d日', $thisTimestamp) . "(" . $w .  ")";
            }
        } else {
            $value["line"] = null;
        }
    }
    return $messageData;
}