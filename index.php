<?php
session_start();
include_once("conf/config.inc.php");
include_once("libs/libs-mysql.php");
include_once("libs/libs-main.php");
include_once("lang/".$cms_cfg['language']."-utf8.php");
$db = new DB($cms_cfg['db_host'],$cms_cfg['db_user'],$cms_cfg['db_password'],$cms_cfg['db_name']);
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]['sc_default_front_page'])){
    $sql="select * from ".$cms_cfg['tb_prefix']."_system_config where sc_id='1'";
    $selectrs = $db->query($sql);
    $row = $db->fetch_array($selectrs,1);
    $rsnum = $db->numRows($selectrs);
    if($rsnum >0 ){
        foreach($row as $key => $value){
            $_SESSION[$cms_cfg['sess_cookie_name']][$key]=$value;
        }
        include_once($_SESSION[$cms_cfg['sess_cookie_name']]['sc_default_front_page']);
    }else{
        include_once($cms_cfg['index_page']);
    }
}else{
    include_once($_SESSION[$cms_cfg['sess_cookie_name']]['sc_default_front_page']);
}
//page view record --ph_type,ph_type_id,m_id
//$main->pageview_history("home",0,$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID']);
?>

