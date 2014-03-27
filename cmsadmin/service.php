<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])  ){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$service = new SERVICE;
class SERVICE{
    function SERVICE(){
        global $db,$cms_cfg,$tpl;
        switch($_REQUEST["func"]){
            case "st_term":  //各項服務說明設定
                $this->ws_tpl_file = "templates/ws-manage-service-term-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_TINYMCE_EPAPER");
                $this->service_term_form($_REQUEST["term_type"]);
                $this->ws_tpl_type=1;
                break;
            case "st_replace"://更新資料(update)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->service_term_replace();
                $this->ws_tpl_type=1;
                break;
            default:    //各項服務說明設定
                $this->ws_tpl_file = "templates/ws-manage-service-term-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_TINYMCE_EPAPER");
                $this->service_term_form($_REQUEST["term_type"]);
                $this->ws_tpl_type=1;
                break;
        }
        if($this->ws_tpl_type){
            $tpl->printToScreen();
        }
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$db,$main;
        $tpl = new TemplatePower( $cms_cfg['manage_all_tpl'] );
        $tpl->assignInclude( "LEFT", $cms_cfg['manage_left_tpl']);
        $tpl->assignInclude( "TOP_MENU", $cms_cfg['manage_top_menu_tpl']);
        $tpl->assignInclude( "MAIN", $ws_tpl_file);
        $tpl->prepare();
        switch($_REQUEST["term_type"]){
            case "st_epaper_header" :
                $tpl->assignGlobal("TAG_ESH_CURRENT","class=\"current\"");
                $tpl->assignGlobal("CSS_BLOCK_EPAPER","style=\"display:block\"");
                break;
            case "st_epaper_footer" :
                $tpl->assignGlobal("TAG_ESF_CURRENT","class=\"current\"");
                $tpl->assignGlobal("CSS_BLOCK_EPAPER","style=\"display:block\"");
                break;
            case "st_inquiry_mail" :
                $tpl->assignGlobal("TAG_ST_INQUIRY_CURRENT","class=\"current\"");
                $tpl->assignGlobal("CSS_BLOCK_INQUIRY","style=\"display:block\"");
                break;
            case "st_contactus_term" :
                $tpl->assignGlobal("TAG_ST_CONTACTUS_CURRENT","class=\"current\"");
                $tpl->assignGlobal("CSS_BLOCK_CONTACTUS","style=\"display:block\"");
                break;
            default :
                $current_calss=strtoupper(trim($_REQUEST["term_type"]));
                $tpl->assignGlobal("TAG_".$current_calss."_CURRENT","class=\"current\"");
                $tpl->assignGlobal("CSS_BLOCK_ORDER","style=\"display:block\"");
                break;
        }
        //依權限顯示項目
        $main->mamage_authority();
    }

    ////各項服務說明設定--表單
    function service_term_form($term_type){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $msg_term_type=strtoupper(str_replace("st_","",$term_type));
        $sql="select ".$term_type." from ".$cms_cfg['tb_prefix']."_service_term where st_id='1'";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $rsnum    = $db->numRows($selectrs);
        if ($rsnum > 0) {
            if($cms_cfg["ws_module"]["ws_wysiwyg"]=="tinymce"){
                $tpl->newBlock("TINYMCE_JS");
                $tpl->newBlock("WYSIWYG_TINYMCE1");
                $tpl->assign( "VALUE_TERM_CONTENT" , $row[$term_type] );
            }
            $tpl->assignGlobal( "VALUE_ACTION_MODE" , "term");
            $tpl->assignGlobal( "VALUE_TERM_TYPE" , $term_type);
            $tpl->assignGlobal( "TAG_SERVICE_TERM_TYPE" , $TPLMSG[$msg_term_type]);
        }else{
            $goto_url=$cms_cfg["manage_url"]."index.php";
            $this->goto_target_page($goto_url);
        }
    }
    //資料更新
    function service_term_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["action_mode"]=="term"){
            $sql="
                update ".$cms_cfg['tb_prefix']."_service_term set
                    ".$_REQUEST["term_type"]."='".$_REQUEST["term_content"]."'
                where st_id='1'";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            $term_type_str="&term_type=".$_REQUEST["term_type"];
        }
        if ( $db_msg == "" ) {
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$cms_cfg["manage_url"]."service.php?func=st_".$_REQUEST["action_mode"].$term_type_str;
            $this->goto_target_page($goto_url);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
    //顯示訊息並重新導向
    function goto_target_page($url,$sec=0){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }
}
//ob_end_flush();
?>
