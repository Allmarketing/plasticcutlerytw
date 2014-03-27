<?php
session_start();
include_once("../conf/config.inc.php");
if(trim($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])==""){
    header("location: login.php");
}else{
    include_once("../libs/libs-manage-sysconfig.php");
    $tpl = new TemplatePower( $cms_cfg['manage_all_tpl'] );
    $tpl->assignInclude( "LEFT", $cms_cfg['manage_left_tpl']);
    $tpl->assignInclude( "TOP_MENU", $cms_cfg['manage_top_menu_tpl']);
    $tpl->assignInclude( "MAIN", "templates/ws-manage-index-tpl.html");
    $tpl->prepare();
    $main->mamage_authority();
    $tpl->printToScreen();
}
?>