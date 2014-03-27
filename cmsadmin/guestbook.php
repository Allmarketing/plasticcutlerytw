<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])  || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_guestbook"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$guestbook = new GUESTBOOK;
class GUESTBOOK{
    function GUESTBOOK(){
        global $db,$cms_cfg,$tpl;
        $this->current_class="GB";
        switch($_REQUEST["func"]){
            case "gb_list"://留言版列表
                $this->ws_tpl_file = "templates/ws-manage-guestbook-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->guestbook_list();
                $this->ws_tpl_type=1;
                break;
            case "gb_replace"://留言版更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->guestbook_replace();
                $this->ws_tpl_type=1;
                break;
            case "gb_del"://留言版刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->guestbook_del();
                $this->ws_tpl_type=1;
                break;
            //////////////////////////////////////////
            case "gbr_add"://留言版回覆新增
                $this->ws_tpl_file = "templates/ws-manage-guestbook-reply-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->guestbook_form("reply");
                $this->ws_tpl_type=1;
                break;
            //////////////////////////////////////////
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //留言版列表
                $this->ws_tpl_file = "templates/ws-manage-guestbook-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->guestbook_list();
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
        $tpl->assignGlobal("CSS_BLOCK_GUESTBOOK","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }

//留言版--列表================================================================
    function guestbook_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //留言版列表
        $sql="select * from ".$cms_cfg['tb_prefix']."_guestbook where gb_reply_type=0 ";
        //取得總筆數
        $selectrs = $db->query($sql);
        //附加條件
        $and_str="";
        if($_REQUEST["st"]=="all"){
            $and_str .= " and (gb_name like '%".$_REQUEST["sk"]."%' or gb_content like '%".$_REQUEST["sk"]."%')";
        }
        if($_REQUEST["st"]=="gb_name"){
            $and_str .= " and cu.cu_subject like '%".$_REQUEST["sk"]."%'";
        }
        if($_REQUEST["st"]=="gb_content"){
            $and_str .= " and gb_content like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by gb_modifydate desc";
        //取得總筆數
        $selectrs = $db->query($sql);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結
        $func_str="guestbook.php?func=gb_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        //分頁，且重新組合包含limit的sql語法
        $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                  "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR']
        ));
        switch($_REQUEST["st"]){
            case "all" :
                $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK0", "selected");
                break;
            case "gb_name" :
                $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK1", "selected");
                break;
            case "gb_content" :
                $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK2", "selected");
                break;
        }
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "GUESTBOOK_LIST" );
            $tpl->assign( array("VALUE_GB_ID"  => $row["gb_id"],
                                "VALUE_GB_NAME" => $row["gb_name"],
                                "VALUE_GB_SEX" => ($row["gb_sex"]==0)?"w":"m",
                                "VALUE_GB_EMAIL" => $row["gb_email"],
                                "VALUE_GB_TEXTCOLOR" => $row["gb_textcolor"],
                                "VALUE_GB_IMG" => $row["gb_img"],
                                "VALUE_GB_HIDDEN" => $row["gb_hidden"],
                                "VALUE_GB_URL" => $row["gb_url"],
                                "VALUE_GB_CONTENT" => $row["gb_content"],
                                "VALUE_GB_MODIFYDATE" => $row["gb_modifydate"],
                                "VALUE_GB_SERIAL" => $i,
            ));
            if(trim($row["gb_email"])!=""){
                $tpl->newBlock( "GUESTBOOK_EMAIL" );
                $tpl->gotoBlock( "GUESTBOOK_LIST" );
            }
            if(trim($row["gb_url"])!=""){
                $tpl->newBlock( "GUESTBOOK_URL" );
                $tpl->gotoBlock( "GUESTBOOK_LIST" );
            }
            $sql2="select * from ".$cms_cfg['tb_prefix']."_guestbook  where gb_reply_type !=0 and gb_parent='".$row["gb_id"]."' order by gb_id";
            $selectrs2 = $db->query($sql2);
            $rsnum2    = $db->numRows($selectrs2);
            $rsnum += $rsnum2;
            while ( $row2 = $db->fetch_array($selectrs2,1) ) {
                $tpl->newBlock( "GUESTBOOK_REPLY_LIST" );
                if($row2["gb_reply_type"]==1){
                    $gb_reply_type=$TPLMSG["GB_ADMIN"];
                    $color="red";
                }else{
                    $gb_reply_type=$TPLMSG["GB_GUEST"];
                    $color="blue";
                }
                $tpl->assign( array("VALUE_GB_R_ID" => $row2["gb_id"],
                                    "VALUE_GB_R_NAME" => $row2["gb_name"],
                                    "VALUE_GB_R_SEX" => $row2["gb_sex"],
                                    "VALUE_GB_R_EMAIL" => $row2["gb_email"],
                                    "VALUE_GB_R_TEXTCOLOR" => $row2["gb_textcolor"],
                                    "VALUE_GB_R_IMG" => $row2["gb_img"],
                                    "VALUE_GB_R_URL" => $row2["gb_url"],
                                    "VALUE_GB_R_CONTENT" => ($row2["gb_hidden"])?$TPLMSG["GB_HIDDEN"]:$row2["gb_content"],
                                    "VALUE_GB_R_MODIFYDATE" => $row2["gb_modifydate"],
                                    "VALUE_GB_R_REPLY_TYPE" => $gb_reply_type,
                                    "VALUE_COLOR" => $color,
                ));
            }
            $tpl->gotoBlock( "GUESTBOOK_LIST" );
        }
        $tpl->assignGlobal("VALUE_TOTAL_BOX" , $rsnum);
    }

//留言版--資料更新================================================================
    function guestbook_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $sql="
            insert into ".$cms_cfg['tb_prefix']."_guestbook (
                gb_parent,gb_name,gb_sex,gb_textcolor,gb_img,gb_email,gb_reply_type,gb_hidden,gb_content,gb_modifydate,gb_url,gb_ip
            ) values (
                '".$_REQUEST["gb_parent"]."','','".$_REQUEST["gb_sex"]."','red',
                '','','1','0',
                '".$_REQUEST["gb_content"]."','".date("Y-m-d H:i:s")."','',''
            )";
        $rs = $db->query($sql);
        $db_msg = $db->report();
        if ( $db_msg == "" ) {
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$cms_cfg["manage_url"]."guestbook.php?func=gb_list";
            $this->goto_target_page($goto_url);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
//留言版--刪除--資料刪除可多筆處理================================================================
    function guestbook_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["gb_id"]){
            $gb_id=array(0=>$_REQUEST["gb_id"]);
        }else{
            $gb_id=$_REQUEST["id"];
        }
        if(!empty($gb_id)){
            $gb_id_str = implode(",",$gb_id);
            //刪除勾選的留言版
            $sql="delete from ".$cms_cfg['tb_prefix']."_guestbook where gb_id in (".$gb_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."guestbook.php?func=gb_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//留言版回覆--表單================================================================
    function guestbook_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //帶入要回覆的留言版資料
        if(!empty($_REQUEST["gb_id"]) && $action_mode=="reply"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_guestbook where gb_id='".$_REQUEST["gb_id"]."' or gb_parent='".$_REQUEST["gb_id"]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                while($row = $db->fetch_array($selectrs,1)){
                    $tpl->assignGlobal( array("VALUE_GB_ID"  => $row["gb_id"],
                                              "VALUE_GB_NAME" => $row["gb_name"],
                                              "VALUE_GB_SEX" => $row["gb_sex"],
                                              "VALUE_GB_EMAIL" => $row["gb_email"],
                                              "VALUE_GB_TEXTCOLOR" => $row["gb_textcolor"],
                                              "VALUE_GB_IMG" => $row["gb_img"],
                                              "VALUE_GB_HIDDEN" => $row["gb_hidden"],
                                              "VALUE_GB_URL" => $row["gb_url"],
                                              "VALUE_GB_CONTENT" => $row["gb_content"],
                                              "VALUE_GB_REPLY_TYPE" => $row["gb_reply_type"],
                                              "VALUE_GB_PARENT" => $row["gb_id"]
                    ));
                }
            }else{
                header("location : guestbook.php?func=gb_list");
            }
        }
    }
//留言版回覆--資料更新================================================================
    function guestbook_reply_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $sql="
            insert into ".$cms_cfg['tb_prefix']."_guestbook (
                gb_parent,gb_reply_type,gb_hidden,gb_content,gb_modifydate
            ) values (
                '".$_REQUEST["gb_id"]."','1','".$_REQUEST["gb_hidden"]."',
                '".$_REQUEST["gb_content"]."','".date("Y-m-d H:i:s")."'
            )";
        $rs = $db->query($sql);
        $db_msg = $db->report();
        if ( $db_msg == "" ) {
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$cms_cfg["manage_url"]."guestbook.php?func=gb_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
            $this->goto_target_page($goto_url);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
    //顯示訊息並重新導向
    function goto_target_page($url,$sec=1){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }

    //資料處理
    function data_processing(){
        $this->guestbook_del();
    }
}
//ob_end_flush();
?>
