<?php
require_once '../include/config/const.php';
require_once '../include/model/login_model.php';
session_start();
checklog();
$pdo = get_connection($DSN,$LOGIN_USER,$PASSWORD);
$userName = store_user_name();
$userPassword = store_user_password();
$message = login($pdo, $userName, $userPassword);
include_once '../include/view/login_view.php';