<?php
include_once("libs/libs-sysconfig.php");
new epaper_register();
class epaper_register {
    //put your code here
    function __construct() {
        global $TPLMSG;
        $result = $this->_isDataValid($_POST);
        if($result[0]){ //註冊epaper資料有效
            $this->_register_data($_POST);
            $this->_show_msg($TPLMSG['EPAPER_REGISTERED']);
        }else{  //註冊epaper資料無效
            $this->_show_msg($result[1]);
        }
        
    }
    
    protected function _isDataValid(&$data){
        global $TPLMSG,$main;
        $main->magic_gpc($data);
        $result = array(false,'');
        if(!empty($data['name']) && !empty($data['email'])){
            if(strtolower($data['name'])!='name' && strtolower($data['email'])!='e-mail'){
                if(!$this->_isMailExists($data['email'])){
                    if(strpos('@', $data['email'])!==FALSE){
                        $result[0] = true;
                    }else{
                        $result[1] = $TPLMSG['EPAPER_EMAIL_INVALID'];
                    }                    
                }else{
                    $result[1] = $TPLMSG['EPAPER_EMAIL_EXISTED'];
                }                
            }else{
                $result[1] = $TPLMSG['EPAPER_MISSING_DATA'];
            }
        }else{
            $result[1] = $TPLMSG['EPAPER_MISSING_DATA'];
        }
        return $result;
    }
    
    protected function _isMailExists($mail){
        global $db,$cms_cfg;
        $result = false;
        $sql = "select * from ".$cms_cfg['tb_prefix']."_member where m_email='".mysql_real_escape_string($mail)."'";
        $res = $db->query($sql);
        if($db->numRows($res))$result = true;
        return $result;
    }
    
    protected function _register_data($data){
        global $db,$cms_cfg;
        $sql = "insert into ".$cms_cfg['tb_prefix']."_member(mc_id,m_name,m_email,m_epaper_status,m_modifydate)values('1','".mysql_real_escape_string($data['name'])."','".mysql_real_escape_string($data['email'])."','1','".date("Y-m-d")."')";
        $db->query($sql);
        if($db->report()){
            echo $db->report();die();
        }
    }
    
    protected function _show_msg($msg){
        global $tpl,$cms_cfg,$main;
        $tpl = new TemplatePower("templates/ws-epaper-msg-tpl.html");
        $tpl->prepare();
        $main->header_footer("epaper");
        $tpl->assign("_ROOT.MSG",$msg);
        $tpl->printToScreen();
    }
    
}

?>
