<?php
include_once("libs/libs-sysconfig.php");
new epaper_unregister();
class epaper_unregister {
    function __construct() {
        global $db,$cms_cfg,$TPLMSG,$main;
        if($_GET['email']){
            $sql = "select * from ".$cms_cfg['tb_prefix']."_member where m_email='".$db->quote($_GET['email'])."'";
            $res = $db->query($sql);
            if($db->numRows($res)){
                $sql = "update ".$cms_cfg['tb_prefix']."_member set m_epaper_status='0' where m_email='".$db->quote($_GET['email'])."'";
                $db->query($sql);
                if($db->report()==""){
                    $main->js_notice($TPLMSG['EPAPER_UNSUB_SUCCESS'],$cms_cfg['base_root']);
                }
            }
        }
    }
}
?>
