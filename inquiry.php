<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])  || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_inquiry"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$inquiry = new INQUIRY;
class INQUIRY{
    function INQUIRY(){
        global $db,$cms_cfg,$tpl;
        $this->current_class="I";
        $this->contact_s_style = $cms_cfg['ws_module']['ws_contactus_s_style'];
        switch($_REQUEST["func"]){
            case "i_list"://詢問單列表
                $this->ws_tpl_file = "templates/ws-manage-inquiry-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->inquiry_list();
                $this->ws_tpl_type=1;
                break;
            case "i_replace"://詢問單更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->inquiry_replace();
                $this->ws_tpl_type=1;
                break;
            case "i_reply"://詢問單檢視及更新狀態
                $this->ws_tpl_file = "templates/ws-manage-inquiry-reply-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->inquiry_reply_form();
                $this->ws_tpl_type=1;
                break;
            case "i_del"://詢問單刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->inquiry_del();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //詢問單列表
                $this->ws_tpl_file = "templates/ws-manage-inquiry-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->inquiry_list();
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
        $tpl->assignGlobal("CSS_BLOCK_INQUIRY","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }
//詢問單--列表================================================================
    function inquiry_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $tpl->assignGlobal("TAG_NOW_CATE",$TPLMSG["NO_CATE"]); //初始值為未分類
        $i=0;
        foreach($ws_array["inquiry_status"] as $key =>$value){
            $i++;
            $tpl->newBlock( "INQUIRY_STATUS_LIST" );
            $tpl->assign( array("VALUE_I_STATUS_SUBJECT"  => $value,
                                "VALUE_I_STATUS" => $key,
                                "VALUE_I_SERIAL" => $i,
            ));
            if($i%4==0){
                $tpl->assign("TAG_INQUIRY_STATUS_TRTD","</tr><tr>");
            }
            if($key==$_REQUEST["i_status"]){
                $tpl->assignGlobal("TAG_NOW_CATE",$value);
            }
        }
        //詢問單列表
        $sql="select * from ".$cms_cfg['tb_prefix']."_inquiry where i_id > '0'";
        //附加條件
        $and_str="";
        if($_REQUEST["i_status"]!=""){
            $and_str .= " and i_status = '".$_REQUEST["i_status"]."'";
        }
        if($_REQUEST["st"]=="i_name"){
            $and_str .= " and i_name like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by i_modifydate desc ";
        //取得總筆數
        $selectrs = $db->query($sql);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結
        $func_str="inquiry.php?func=i_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        //分頁且重新組合包含limit的sql語法
        $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("VALUE_TOTAL_BOX" => $rsnum,
                                  "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                  "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],
        ));
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "INQUIRY_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_I_ID"  => $row["i_id"],
                                "VALUE_I_NAME" => $row["i_name"],
                                "VALUE_I_CREATEDATE" => $row["i_createdate"],
                                "VALUE_I_MODIFYDATE" => $row["i_modifydate"],
                                "VALUE_I_STATUS" => $ws_array["inquiry_status"][$row["i_status"]],
                                "VALUE_I_SERIAL" => $i,
            ));
        }
    }

//詢問單--刪除--資料刪除可多筆處理================================================================
    function inquiry_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["i_id"]){
            $cu_id=array(0=>$_REQUEST["i_id"]);
        }else{
            $cu_id=$_REQUEST["id"];
        }
        if(!empty($cu_id)){
            $cu_id_str = implode(",",$cu_id);
            //刪除勾選的詢問單
            $sql="delete from ".$cms_cfg['tb_prefix']."_inquiry where i_id in (".$cu_id_str.")";
            $rs = $db->query($sql);
            $sql="delete from ".$cms_cfg['tb_prefix']."_inquiry_items where i_id in (".$cu_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."inquiry.php?func=o_list&cuc_id=".$_REQUEST["cuc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //詢問單回覆--表單================================================================
    function inquiry_reply_form(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //欄位名稱
        $tpl->assignGlobal("MSG_MODE" , $TPLMSG['REPLY']);
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        //帶入要回覆的詢問單資料
        if(!empty($_REQUEST["i_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_inquiry where i_id='".$_REQUEST["i_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("VALUE_M_ID"  => $row["m_id"],
                                          "VALUE_I_ID"  => $row["i_id"],
                                          "VALUE_I_NAME" => $row["i_name"],
                                          "VALUE_I_CONTACT_S" => $ws_array['contactus_s'][$row["i_contact_s"]],
                                          "VALUE_I_TEL" => $row["i_tel"],
                                          "VALUE_I_CELLPHONE" => $row["i_cellphone"],
                                          "VALUE_I_ZIP" => $row["i_zip"],
                                          "VALUE_I_ADDRESS" => $row["i_address"],
                                          "VALUE_I_EMAIL" => $row["i_email"],
                                          "VALUE_I_CONTENT" => $row["i_content"],
                                          "VALUE_I_REPLY" => $row["i_reply"],
                                          "VALUE_I_STATUS" => $ws_array["inquiry_status"][$row["i_status"]],
                ));
                //稱謂類型
                $tpl->newBlock("I_CONTACT_S_".$this->contact_s_style);
                //訂購產品列表
                $sql="select * from ".$cms_cfg['tb_prefix']."_inquiry_items where i_id='".$_REQUEST["i_id"]."'";
                $selectrs = $db->query($sql);
                $total_price=0;
                $i=0;
                while($row = $db->fetch_array($selectrs,1)){
                    $i++;
                    $sub_total_price = $row["p_sell_price"] * $row["oi_amount"];
                    $total_price = $total_price+$sub_total_price;
                    $tpl->newBlock( "INQUIRY_ITEMS_LIST" );
                    $tpl->assign( array("VALUE_P_ID"  => $row["p_id"],
                                        "VALUE_P_NAME" => $row["p_name"],
                                        "VALUE_P_AMOUNT" => $row["ii_amount"],
                                        "VALUE_P_SERIAL"  => $i,
                    ));
                }
            }else{
                header("location : inquiry.php?func=i_list");
            }
        }
    }
//詢問單回覆--資料更新================================================================
    function inquiry_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $sql="update ".$cms_cfg['tb_prefix']."_inquiry set  i_reply='".$_REQUEST["i_reply"]."' , i_status='1' , i_modifydate='".date("Y-m-d H:i:s")."' where  i_id='".$_REQUEST["i_id"]."'";
        $rs = $db->query($sql);
        $db_msg = $db->report();
        if ( $db_msg == "" ) {
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$cms_cfg["manage_url"]."inquiry.php?func=o_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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

    //資料處理
    function data_processing(){
        switch ($_REQUEST["process_type"]){
            case "del":
                $this->inquiry_del();
                break;
        }
    }
}
//ob_end_flush();
?>
