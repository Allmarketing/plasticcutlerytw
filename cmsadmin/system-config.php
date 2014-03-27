<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$system_config = new SYSTEMCFG;
class SYSTEMCFG{
    function SYSTEMCFG(){
        global $db,$cms_cfg,$tpl;
        $this->current_class="SC";
        switch($_REQUEST["func"]){
            case "sc_mod":  //系統設定
                $this->ws_tpl_file = "templates/ws-manage-system-config-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->system_config_form();
                $this->ws_tpl_type=1;
                break;
            case "sc_sitemap":  //網站地圖
                $this->ws_tpl_file = "templates/ws-manage-system-config-sitemap-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->system_config_sitemap();
                $this->ws_tpl_type=1;
                break;
            case "sc_db":  //資料庫備份還原
                $this->current_class="SC_DB";
                $this->ws_tpl_file = "templates/ws-manage-system-config-db-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->system_config_db();
                $this->ws_tpl_type=1;
                break;
            case "sc_ip":  //ip國別查詢
                $this->ws_tpl_file = "templates/ws-manage-system-config-ip-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->system_config_ip();
                $this->ws_tpl_type=1;
                break;
            case "sc_replace"://更新資料(update)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->system_config_replace();
                $this->ws_tpl_type=1;
                break;
	    case "sc_banner"://首頁Banner設定
		$this->current_class="IB";
                $this->ws_tpl_file = "templates/ws-manage-banner-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->index_banner();
                $this->ws_tpl_type=1;
	        break;             
            default:    //系統設定
                $this->ws_tpl_file = "templates/ws-manage-system-config-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->system_config_form();
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
        $tpl->assignGlobal("TAG_".$this->current_class."_CURRENT","class=\"current\"");
        $tpl->assignGlobal("CSS_BLOCK_SYSTEM","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }
    //系統設定--表單
    function system_config_form(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$ws_array,$main;
        include_once("../lang/".$cms_cfg['language']."-utf8.php");
        $sql="select * from ".$cms_cfg['tb_prefix']."_system_config where sc_id='1'";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $rsnum    = $db->numRows($selectrs);
        if ($rsnum > 0) {
            //自定產品標題說明
            if($cms_cfg["ws_module"]["ws_products_title"]==1) {
                $tpl->newBlock("TAG_PRODUCTS_TITLE");
            }
            $tpl->assignGlobal( array("VALUE_SC_MSN" => $row["st_im_msn"],
                                      //"VALUE_SC_CLOSE_MSG" => $row["sc_close_msg"],
                                      "VALUE_SC_COMPANY" => $row["sc_company"],
                                      "VALUE_SC_EMAIL" => $row["sc_email"],
                                      "VALUE_SC_SHIPPING_PRICE" => $row["sc_shipping_price"],
                                      "VALUE_SC_NO_SHIPPING_PRICE" => $row["sc_no_shipping_price"],
                                      "VALUE_SC_ONE_PAGE_LIMIT" => $row["sc_one_page_limit"],
                                      //"VALUE_SC_SESSION_DURATION" => $row["sc_session_duration"],
                                      "VALUE_SC_META_TITLE" => $row["sc_meta_title"],
                                      "VALUE_SC_META_KEYWORD" => $row["sc_meta_keyword"],
                                      "VALUE_SC_META_DESCRIPTION" => $row["sc_meta_description"],
                                      "VALUE_SC_SHORT_DESC" => $row["sc_short_desc"],
                                      "VALUE_SC_SEO_H1" => $row["sc_seo_h1"],
                                      //"VALUE_SC_IM_MSN" => $row["sc_im_msn"],
                                      //"VALUE_SC_IM_SKYPE" => $row["sc_im_skype"],
                                      "VALUE_SC_IM_STARTTIME" => $row["sc_im_starttime"],
                                      "VALUE_SC_IM_ENDTIME" => $row["sc_im_endtime"],
                                      "STR_SC_STATUS_CK1" => ($row["sc_status"]==1)?"checked":"",
                                      "STR_SC_STATUS_CK0" => ($row["sc_status"]==0)?"checked":"",
                                      "STR_SC_CART_TYPE_CK2" => ($row["sc_cart_type"]==2)?"checked":"",
                                      "STR_SC_CART_TYPE_CK1" => ($row["sc_cart_type"]==1)?"checked":"",
                                      "STR_SC_CART_TYPE_CK0" => ($row["sc_cart_type"]==0)?"checked":"",
                                      "STR_SC_AD_SORT_TYPE_CK2" => ($row["sc_ad_sort_type"]==2)?"checked":"",
                                      "STR_SC_AD_SORT_TYPE_CK1" => ($row["sc_ad_sort_type"]==1)?"checked":"",
                                      "STR_SC_AD_SORT_TYPE_CK0" => ($row["sc_ad_sort_type"]==0)?"checked":"",
                                      //"STR_SC_DEBUG_CK1" => ($row["sc_debug"]==1)?"checked":"",
                                      //"STR_SC_DEBUG_CK0" => ($row["sc_debug"]==0)?"checked":"",
                                      "STR_SC_IM_STATUS_CK1" => ($row["sc_im_status"]==1)?"checked":"",
                                      "STR_SC_IM_STATUS_CK0" => ($row["sc_im_status"]==0)?"checked":"",
                                      "VALUE_SC_DESC_TITLE_DEFAULT" => (trim($row["sc_desc_title_default"])=="")?$TPLMSG['PRODUCT_DESCRIPTION']:$row["sc_desc_title_default"],
                                      "VALUE_SC_CHARACTER_TITLE_DEFAULT" => (trim($row["sc_character_title_default"])=="")?$TPLMSG['PRODUCT_CHARACTER']:$row["sc_character_title_default"],
                                      "VALUE_SC_SPEC_TITLE_DEFAULT" => (trim($row["sc_spec_title_default"])=="")?$TPLMSG['PRODUCT_SPEC']:$row["sc_spec_title_default"],
                                      "MSG_MODE" => "修改",
            ));
            if($cms_cfg["ws_module"]["ws_im_msn"]==1) {
                $tpl->newBlock("TAG_IM_MSN");
                $tpl->assignGlobal("VALUE_SC_IM_MSN",$row["sc_im_msn"]);
            }
            if($cms_cfg["ws_module"]["ws_im_skype"]==1) {
                $tpl->newBlock("TAG_IM_SKYPE");
                $tpl->assignGlobal("VALUE_SC_IM_SKYPE",$row["sc_im_skype"]);
            }
            if($cms_cfg["ws_module"]["ws_version"]=="ips"){
                $tpl->newBlock("IPC_MODE");
                $tpl->newBlock("IPB_MODE");
                $tpl->newBlock("IPS_SETUP_MODE");
            }
            if($cms_cfg["ws_module"]["ws_version"]=="ipc"){
                $tpl->newBlock("IPC_MODE");
                //如果是IPC,預設設定為詢價車,把設定預設在購物車
                if($row["sc_cart_type"]==0){
                    $tpl->assignGlobal("STR_SC_CART_TYPE_CK1","checked");
                }
            }
            if($cms_cfg["ws_module"]["ws_version"]=="ipb"){
                $tpl->newBlock("IPB_MODE");
                //如果是IPB,預設設定為購物車,把設定預設在詢價車
                if($row["sc_cart_type"]==1){
                    $tpl->assignGlobal("STR_SC_CART_TYPE_CK0","checked");
                }
            }
            if($cms_cfg["ws_module"]["ws_version"]=="ips" || $cms_cfg["ws_module"]["ws_version"]=="ipc"){
                $tpl->newBlock("SHIPPING_PRICE_SETUP");
            }
            if($cms_cfg["ws_module"]["ws_ad"]==1){
                $tpl->newBlock("AD_SETUP");
            }
            foreach($ws_array["front_page"] as $key => $value){
                $tpl->newBlock( "TAG_SELECT_FRONT_PAGE" );
                $tpl->assign( array( "TAG_SELECT_FRONT_PAGE_NAME"  => $value,
                                     "TAG_SELECT_FRONT_PAGE_VALUE" => $key,
                                     "STR_FRONT_PAGE_SEL"       => ($key==$row["sc_default_front_page"])?"selected":""
                ));
            }
            $starttime=explode(":",$row["sc_im_starttime"]);
            $endtime=explode(":",$row["sc_im_endtime"]);
            //時間下拉選單 --時
            for($i=1;$i<24;$i++){
                $tpl->newBlock( "TAG_SELECT_IM_STARTTIME_H" );
                $tpl->assign( array( "TAG_SELECT_IM_STARTTIME_NAME_H"  => sprintf ("%02d", $i),
                                     "TAG_SELECT_IM_STARTTIME_VALUE_H" => sprintf ("%02d", $i),
                                     "STR_IM_STARTTIME_SEL_H"       => ($i==$starttime[0])?"selected":""
                ));
                $tpl->newBlock( "TAG_SELECT_IM_ENDTIME_H" );
                $tpl->assign( array( "TAG_SELECT_IM_ENDTIME_NAME_H"  => sprintf ("%02d", $i),
                                     "TAG_SELECT_IM_ENDTIME_VALUE_H" => sprintf ("%02d", $i),
                                     "STR_IM_ENDTIME_SEL_H"       => ($i==$endtime[0])?"selected":""
                ));
            }
            //時間下拉選單 --分
            for($i=0;$i<60;$i=$i+10){
                $tpl->newBlock( "TAG_SELECT_IM_STARTTIME_I" );
                $tpl->assign( array( "TAG_SELECT_IM_STARTTIME_NAME_I"  => sprintf ("%02d", $i),
                                     "TAG_SELECT_IM_STARTTIME_VALUE_I" => sprintf ("%02d", $i),
                                     "STR_IM_STARTTIME_SEL_I"       => ($i==$starttime[1])?"selected":""
                ));
                $tpl->newBlock( "TAG_SELECT_IM_ENDTIME_I" );
                $tpl->assign( array( "TAG_SELECT_IM_ENDTIME_NAME_I"  => sprintf ("%02d", $i),
                                     "TAG_SELECT_IM_ENDTIME_VALUE_I" => sprintf ("%02d", $i),
                                     "STR_IM_ENDTIME_SEL_I"       => ($i==$endtime[1])?"selected":""
                ));
            }
        }else{
            $goto_url=$cms_cfg["manage_url"]."index.php";
            $this->goto_target_page($goto_url);
        }
    }
    //資料更新
    function system_config_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $sql="
            update ".$cms_cfg['tb_prefix']."_system_config set
                sc_status='".$_REQUEST["sc_status"]."',
                sc_close_msg='".$_REQUEST["sc_close_msg"]."',
                sc_default_front_page ='".$_REQUEST["sc_default_front_page"]."',
                sc_company='".$_REQUEST["sc_company"]."',
                sc_cart_type='".$_REQUEST["sc_cart_type"]."',
                sc_email='".$_REQUEST["sc_email"]."',
                sc_one_page_limit='".$_REQUEST["sc_one_page_limit"]."',
                sc_meta_title='".$_REQUEST["sc_meta_title"]."',
                sc_meta_keyword='".$_REQUEST["sc_meta_keyword"]."',
                sc_meta_description='".$_REQUEST["sc_meta_description"]."',
                sc_short_desc='".$_REQUEST["sc_short_desc"]."',
                sc_seo_h1='".$_REQUEST["sc_seo_h1"]."',
                sc_im_status='".$_REQUEST["sc_im_status"]."',
                sc_im_starttime='".$_REQUEST["sc_im_starttime_h"].":".$_REQUEST["sc_im_starttime_i"].":00',
                sc_im_endtime='".$_REQUEST["sc_im_endtime_h"].":".$_REQUEST["sc_im_endtime_i"].":00',
                sc_im_msn='".$_REQUEST["sc_im_msn"]."',
                sc_desc_title_default='".$_REQUEST["sc_desc_title_default"]."',
                sc_character_title_default='".$_REQUEST["sc_character_title_default"]."',
                sc_spec_title_default='".$_REQUEST["sc_spec_title_default"]."',
                ";
        if($cms_cfg["ws_module"]["ws_version"]=="ips" || $cms_cfg["ws_module"]["ws_version"]=="ipc"){
            $sql .="sc_shipping_price='".$_REQUEST["sc_shipping_price"]."',
                    sc_no_shipping_price='".$_REQUEST["sc_no_shipping_price"]."',";
        }
        if($cms_cfg["ws_module"]["ws_ad"]==1){
            $sql .="sc_ad_sort_type='".$_REQUEST["sc_ad_sort_type"]."',";
        }
        $sql .="sc_im_skype='".$_REQUEST["sc_im_skype"]."' where sc_id='1'";
        $rs = $db->query($sql);
        $db_msg = $db->report();
        if ( $db_msg == "" ) {
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$cms_cfg["manage_url"]."system-config.php?func=sc_mod";
            $this->goto_target_page($goto_url);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
    //資料庫備份還原
    function system_config_db() {
        global $tpl;
        switch($_REQUEST["action_mode"]) {
            case "dump":
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->dump_db();
                break;
            default:
                break;
        }
    }
    //資料庫備份
    function dump_db() {
        global $tpl,$cms_cfg,$TPLMSG;
        $output = array();
        $sql_file_name = $cms_cfg['sql_dir'] . date("Y-m-d") . "_" . $cms_cfg['db_name'] . ".sql";
        $command_str = $cms_cfg['mysql_dump'] . "mysqldump ";
        $command_str .= " -h " . $cms_cfg['db_host'];
        $command_str .= " -u " . $cms_cfg['db_user'];
        $command_str .= " -p" . $cms_cfg['db_password'];
        $command_str .= " --database --skip-opt --no-create-db ";
        $command_str .= $cms_cfg['db_name'] . " > " . $sql_file_name;
        ini_set(max_execution_time, 0);
        system($command_str, $r);
        if(empty($r)) {
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$cms_cfg["manage_url"]."system-config.php?func=sc_db";
            $this->goto_target_page($goto_url,2);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: please contact MIS");
        }
    }
    //顯示訊息並重新導向
    function goto_target_page($url,$sec=0){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }
    //自訂首頁banner
    function index_banner(){
	global $db,$tpl,$cms_cfg,$main,$TPLMSG;
        if($_REQUEST['banner_updata_ck']){
            $tpl->newBlock("MSG_ZONE");
            $sql = "REPLACE INTO `".$cms_cfg['tb_prefix']."_index_banner`VALUES";
            foreach($_REQUEST['ib_img'] as $k=>$img){
                $value_arr[] = sprintf("(%d,'%s','%s')",$k,$img,$_REQUEST['ib_link'][$k]);
            }
            $sql .= implode(',',$value_arr);
            $db->query($sql);
            $db_msg = $db->report();
            if($db_msg == ""){
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."system-config.php?func=sc_banner";
                    $this->goto_target_page($goto_url);
            }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                    $this->error = 1;
            }
            return;
        }
        $tpl->newBlock("DATA_ZONE");
        if($cms_cfg['index_banner_nums']>0){
            $sql="select * from ".$cms_cfg['tb_prefix']."_index_banner order by ib_id ";
            $data_array = $db->get_result($sql);       
            for($i=1;$i<=$cms_cfg['index_banner_nums'];$i++){
                $tpl->newBlock("INDEX_BANNER_ITEM");
                $tpl->assign(array(
                    "SERIAL" => $i,
                    "VALUE_IB_IMG"  => $data_array[$i-1]['ib_img'],
                    "VALUE_IB_LINK" => $data_array[$i-1]['ib_link'],
                ));
            }
        }
    }       
}
//ob_end_flush();
?>