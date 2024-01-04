<?php
require_once '../include/config/const.php';
require_once '../include/model/user_registration_model.php';
session_start();
checklogin();
$pdo = get_connection($DSN,$LOGIN_USER,$PASSWORD);
$userName = store_user_name();
$userPassword = store_user_password();
$userIcon = store_user_icon();
$message = registrate_user($pdo, $userName, $userPassword,$userIcon);
include_once '../include/view/user_registration_view.php';