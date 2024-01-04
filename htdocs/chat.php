<?php
require_once '../include/config/const.php';
require_once '../include/model/chat_model.php';
session_start();
checklogin();
$pdo = get_connection($DSN,$LOGIN_USER,$PASSWORD);
$loginUserInfo = get_login_user_info($pdo);
post_message($pdo);
post_delete($pdo);
post_setting($pdo);
$sort = get_sort($pdo);
$messageData = get_messageData($pdo,$sort);
$messageData = draw_lines_between_dates($pdo,$messageData);
include_once '../include/view/chat_view.php';