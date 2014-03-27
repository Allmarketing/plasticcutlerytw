<?php
session_start();
if($_SESSION['isLoggedIn']){
    $_SESSION['user'] = "login_account";
    header("location: " . $_REQUEST['return_url']);
	die;
}else{
    header("location: /");
    exit;
}
?>