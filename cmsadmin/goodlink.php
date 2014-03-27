<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]) || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_goodlink"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$goodlink = new GOODLINK;
class GOODLINK{
    function GOODLINK(){
        global $db,$cms_cfg,$tpl;
        switch($_REQUEST["func"]){
            case "lc_list"://相關連結分類列表
                $this->current_class="LC";
                $this->ws_tpl_file = "templates/ws-manage-goodlink-cate-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->goodlink_cate_list();
                $this->ws_tpl_type=1;
                break;
            case "lc_add"://相關連結分類新增
                $this->current_class="LC";
                $this->ws_tpl_file = "templates/ws-manage-goodlink-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->goodlink_cate_form("add");
                $this->ws_tpl_type=1;
                break;
            case "lc_mod"://相關連結分類修改
                $this->current_class="LC";
                $this->ws_tpl_file = "templates/ws-manage-goodlink-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->goodlink_cate_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "lc_replace"://相關連結分類更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->goodlink_cate_replace();
                $this->ws_tpl_type=1;
                break;
            case "lc_del"://相關連結分類刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->goodlink_cate_del();
                $this->ws_tpl_type=1;
                break;
            case "l_list"://相關連結列表
                $this->current_class="L";
                $this->ws_tpl_file = "templates/ws-manage-goodlink-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->goodlink_list();
                $this->ws_tpl_type=1;
                break;
            case "l_add"://相關連結新增
                $this->current_class="L";
                $this->ws_tpl_file = "templates/ws-manage-goodlink-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_CALENDAR");
                $tpl->newBlock("JS_TINYMCE");
                $this->goodlink_form("add");
                $this->ws_tpl_type=1;
                break;
            case "l_mod"://相關連結修改
                $this->current_class="L";
                $this->ws_tpl_file = "templates/ws-manage-goodlink-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_CALENDAR");
                $tpl->newBlock("JS_TINYMCE");
                $this->goodlink_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "l_replace"://相關連結更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->goodlink_replace();
                $this->ws_tpl_type=1;
                break;
            case "l_del"://相關連結刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->goodlink_del();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //相關連結列表
                $this->current_class="L";
                $this->ws_tpl_file = "templates/ws-manage-goodlink-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->goodlink_list();
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
        $tpl->assignGlobal("CSS_BLOCK_GOODLINK","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }

    //相關連結分類--列表
    function goodlink_cate_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_goodlink_cate where lc_id > '0'";
        $and_str = "";
        if(!empty($_REQUEST["sk"])){
            $and_str = " and lc_subject like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by lc_sort ".$cms_cfg['sort_pos']." ";
        //取得總筆數
        $total_records=$main->count_total_records($sql);
        //取得分頁連結
        $func_str="goodlink.php?func=lc_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        //分頁且重新組合包含limit的sql語法
        $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                  "VALUE_TOTAL_BOX" => $rsnum,
                                  "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR']
        ));
        //分類列表
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "GOODLINK_CATE_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_LC_ID"  => $row["lc_id"],
                                "VALUE_LC_STATUS"  => $row["lc_status"],
                                "VALUE_LC_SORT"  => $row["lc_sort"],
                                "VALUE_LC_SUBJECT" => $row["lc_subject"],
                                "VALUE_LC_SERIAL" => $i,
                                "VALUE_STATUS_IMG" => ($row["lc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                "VALUE_STATUS_IMG_ALT" => ($row["lc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
            ));
        }
    }
    //相關連結分類--表單
    function goodlink_cate_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "VALUE_LC_SORT"  => 1,
                                  "STR_LC_STATUS_CK1" => "checked",
                                  "STR_LC_STATUS_CK0" => "",
                                  "VALUE_ACTION_MODE" => $action_mode
        ));
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],
            ));
        }
        //如果為修改模式,帶入資料庫資料
        if($action_mode=="mod" && !empty($_REQUEST["lc_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_goodlink_cate where lc_id='".$_REQUEST["lc_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("VALUE_LC_ID"  => $row["lc_id"],
                                          "VALUE_LC_STATUS"  => $row["lc_status"],
                                          "VALUE_LC_SORT"  => $row["lc_sort"],
                                          "VALUE_LC_SUBJECT" => $row["lc_subject"],
                                          "STR_LC_STATUS_CK1" => ($row["lc_status"])?"checked":"",
                                          "STR_LC_STATUS_CK0" => ($row["lc_status"])?"":"checked",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
            }else{
                header("location : goodlink.php?func=lc_list");
            }
        }
    }
    //相關連結分類--資料更新
    function goodlink_cate_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_goodlink_cate (
                        lc_status,
                        lc_sort,
                        lc_subject
                    ) values (
                        ".$_REQUEST["lc_status"].",
                        '".$_REQUEST["lc_sort"]."',
                        '".htmlspecialchars($_REQUEST["lc_subject"])."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_goodlink_cate set
                        lc_status=".$_REQUEST["lc_status"].",
                        lc_sort='".$_REQUEST["lc_sort"]."',
                        lc_subject='".htmlspecialchars($_REQUEST["lc_subject"])."'
                    where lc_id='".$_REQUEST["lc_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."goodlink.php?func=lc_list&lc_id=".$_REQUEST["lc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //相關連結分類--刪除
    function goodlink_cate_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["lc_id"]){
            $lc_id=array(0=>$_REQUEST["lc_id"]);
        }else{
            $lc_id=$_REQUEST["id"];
        }
        if(!empty($lc_id)){
            $lc_id_str = implode(",",$lc_id);
            //清空分類底下的相關連結
            $sql="delete from ".$cms_cfg['tb_prefix']."_goodlink where lc_id in (".$lc_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                //刪除分類
                $sql="delete from ".$cms_cfg['tb_prefix']."_goodlink_cate where lc_id in (".$lc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."goodlink.php?func=lc_list&lc_id=".$_REQUEST["lc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//相關連結--列表================================================================
    function goodlink_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_goodlink_cate where lc_id > '0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        //沒有分類先建立分類
        if($rsnum < 1){
            $tpl->assignGlobal( "MSG_CREATE_CATE_FIRST" , $TPLMSG["CREATE_CATE_FIRST"]);
            $goto_url=$cms_cfg["manage_url"]."goodlink.php?func=lc_add";
            $this->goto_target_page($goto_url);
        }else{
            //相關連結分類
            $i=0;
            $tpl->assignGlobal("TAG_NOW_CATE" ,$TPLMSG["NO_CATE"]);
            while($row = $db->fetch_array($selectrs,1)){
                $i++;
                $tpl->newBlock( "GOODLINK_CATE_LIST" );
                $tpl->assign( array( "VALUE_LC_SUBJECT"  => $row["lc_subject"],
                                     "VALUE_LC_ID" => $row["lc_id"],
                                     "VALUE_LC_SERIAL" => $i,
                                     "VALUE_STATUS_IMG" => ($row["lc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                     "VALUE_STATUS_IMG_ALT" => ($row["lc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
                ));
                if($i%4==0){
                    $tpl->assign("TAG_GOODLINK_CATE_TRTD","</tr><tr>");
                }
                if($row["lc_id"]==$_REQUEST["lc_id"]){
                    $tpl->assignGlobal("TAG_NOW_CATE",$row["lc_subject"]);
                }
            }
            //相關連結列表
            $sql="select l.*,lc.lc_subject from ".$cms_cfg['tb_prefix']."_goodlink as l left join ".$cms_cfg['tb_prefix']."_goodlink_cate as lc on l.lc_id=lc.lc_id where l.l_id > '0'";
            //附加條件
            $and_str="";
            if(!empty($_REQUEST["lc_id"])){
                $and_str .= " and l.lc_id = '".$_REQUEST["lc_id"]."'";
            }
            if($_REQUEST["st"]=="all"){
                $and_str .= " and (l.l_subject like '%".$_REQUEST["sk"]."%' or l.l_content like '%".$_REQUEST["sk"]."%')";
            }
            if($_REQUEST["st"]=="l_subject"){
                $and_str .= " and l.l_subject like '%".$_REQUEST["sk"]."%'";
            }
            if($_REQUEST["st"]=="l_content"){
                $and_str .= " and l.l_content like '%".$_REQUEST["sk"]."%'";
            }
            $sql .= $and_str." order by lc.lc_sort ".$cms_cfg['sort_pos'].",l.l_sort ".$cms_cfg['sort_pos'].",l.l_modifydate desc ";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="goodlink.php?func=l_list&lc_id=".$_REQUEST["lc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
            //分頁且重新組合包含limit的sql語法
            $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $tpl->assignGlobal( array("VALUE_TOTAL_BOX" => $rsnum,
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                      "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],
            ));
            switch($_REQUEST["st"]){
                case "all" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK0", "selected");
                    break;
                case "l_subject" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK1", "selected");
                    break;
                case "l_content" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK2", "selected");
                    break;
            }
            $i=$page["start_serial"];
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "GOODLINK_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow'");
                }
                $tpl->assign( array("VALUE_LC_ID"  => $row["lc_id"],
                                    "VALUE_L_ID"  => $row["l_id"],
                                    "VALUE_L_SORT"  => $row["l_sort"],
                                    "VALUE_L_SUBJECT" => $row["l_subject"],
                                    "VALUE_L_STARTDATE" => $row["l_startdate"],
                                    "VALUE_L_SERIAL" => $i,
                                    "VALUE_LC_SUBJECT"  => $row["lc_subject"],
                                    "VALUE_STATUS_IMG" => ($row["l_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                    "VALUE_STATUS_IMG_ALT" => ($row["l_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

                ));
                //判斷刊登狀態
                if($row["l_status"]==0 ||($row["l_status"]==2 && $row["l_enddate"] < date("Y-m-d"))){
                    $tpl->assign("VALUE_L_PUBLISH_STATUS","已過期");
                }else{
                    $tpl->assign("VALUE_L_PUBLISH_STATUS","刊登中");
                }

            }
        }
    }
//相關連結--表單================================================================
    function goodlink_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "VALUE_L_SORT"  => 1,
                                  "STR_L_STATUS_CK2" => "",
                                  "STR_L_STATUS_CK1" => "checked",
                                  "STR_L_STATUS_CK0" => "",
                                  "STR_L_CONTENT_TYPE_CK1" => "checked",
                                  "STR_L_CONTENT_TYPE_CK2" => "",
                                  "STR_L_CONTENT_TYPE_DISPLAY" => "",
                                  "STR_L_CONTENT_TYPE_DISPLAY2" => "none",
                                  "STR_L_HOT_CK1" => "",
                                  "STR_L_HOT_CK0" => "checked",
                                  "STR_L_POP_CK1" => "",
                                  "STR_L_POP_CK0" => "checked",
                                  "VALUE_PIC_PREVIEW" => $cms_cfg['default_preview_pic'],
                                  "VALUE_ACTION_MODE" => $action_mode
        ));
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],
            ));
        }
        //如果為修改模式,帶入資料庫資料
        if($action_mode=="mod" && !empty($_REQUEST["l_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_goodlink where l_id='".$_REQUEST["l_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("VALUE_L_ID"  => $row["l_id"],
                                          "VALUE_L_STATUS"  => $row["l_status"],
                                          "VALUE_L_SORT"  => $row["l_sort"],
                                          "VALUE_L_SUBJECT" => $row["l_subject"],
                                          "VALUE_L_URL" => $row["l_url"],
                                          "VALUE_L_STARTDATE" => $row["l_startdate"],
                                          "VALUE_L_ENDDATE" => $row["l_enddate"],
                                          "STR_L_STATUS_CK2" => ($row["l_status"]==2)?"checked":"",
                                          "STR_L_STATUS_CK1" => ($row["l_status"]==1)?"checked":"",
                                          "STR_L_STATUS_CK0" => ($row["l_status"]==0)?"checked":"",
                                          "STR_L_CONTENT_TYPE_CK1" => ($row["l_content_type"]==1)?"checked":"",
                                          "STR_L_CONTENT_TYPE_CK2" => ($row["l_content_type"]==2)?"checked":"",
                                          "STR_L_CONTENT_TYPE_DISPLAY1" => ($row["l_content_type"]==1)?"":"none",
                                          "STR_L_CONTENT_TYPE_DISPLAY2" => ($row["l_content_type"]==2)?"":"none",
                                          "STR_L_HOT_CK1" => ($row["l_hot"]==1)?"checked":"",
                                          "STR_L_HOT_CK0" => ($row["l_hot"]==0)?"checked":"",
                                          "STR_L_POP_CK1" => ($row["l_pop"]==1)?"checked":"",
                                          "STR_L_POP_CK0" => ($row["l_pop"]==0)?"checked":"",
                                          "VALUE_L_S_PIC" => (trim($row["l_s_pic"])=="")?"":$row["l_s_pic"],
                                          "VALUE_PIC_PREVIEW" => (trim($row["l_s_pic"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["base_root"].$row["l_s_pic"],
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
            }else{
                header("location : goodlink.php?func=l_list");
            }
        }
        //相關連結分類
        $sql="select * from ".$cms_cfg['tb_prefix']."_goodlink_cate where lc_id > '0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        while($row1 = $db->fetch_array($selectrs,1)){
            $tpl->newBlock( "SELECT_OPTION_GOODLINK_CATE" );
            $tpl->assign( array( "OPTION_GOODLINK_CATE_NAME"  => $row1["lc_subject"],
                                 "OPTION_GOODLINK_CATE_VALUE" => $row1["lc_id"],
                                 "STR_LC_SEL"       => ($row1["lc_id"]==$row["lc_id"])?"selected":""
            ));
        }
        if($cms_cfg["ws_module"]["ws_wysiwyg"]=="tinymce"){
            $tpl->newBlock("TINYMCE_JS");
            $tpl->newBlock("WYSIWYG_TINYMCE1");
            $tpl->assign( "VALUE_L_CONTENT" , $row["l_content"] );
        }
    }
//相關連結--資料更新================================================================
    function goodlink_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_goodlink (
                        lc_id,
                        l_status,
                        l_sort,
                        l_hot,
                        l_pop,
                        l_subject,
                        l_content_type,
                        l_content,
                        l_url,
                        l_s_pic,
                        l_modifydate
                    ) values (
                        '".$_REQUEST["lc_id"]."',
                        '".$_REQUEST["l_status"]."',
                        '".$_REQUEST["l_sort"]."',
                        '".$_REQUEST["l_hot"]."',
                        '".$_REQUEST["l_pop"]."',
                        '".htmlspecialchars($_REQUEST["l_subject"])."',
                        '".$_REQUEST["l_content_type"]."',
                        '".$_REQUEST["l_content"]."',
                        '".$_REQUEST["l_url"]."',
                        '".$main->file_str_replace($_REQUEST["l_s_pic"])."',
                        '".date("Y-m-d H:i:s")."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_goodlink set
                        lc_id='".$_REQUEST["lc_id"]."',
                        l_status='".$_REQUEST["l_status"]."',
                        l_sort='".$_REQUEST["l_sort"]."',
                        l_hot='".$_REQUEST["l_hot"]."',
                        l_pop='".$_REQUEST["l_pop"]."',
                        l_subject='".htmlspecialchars($_REQUEST["l_subject"])."',
                        l_content_type='".$_REQUEST["l_content_type"]."',
                        l_content='".$_REQUEST["l_content"]."',
                        l_url='".$_REQUEST["l_url"]."',
                        l_s_pic='".$main->file_str_replace($_REQUEST["l_s_pic"])."',
                        l_modifydate='".date("Y-m-d H:i:s")."'
                    where l_id='".$_REQUEST["l_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."goodlink.php?func=l_list&lc_id=".$_REQUEST["lc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//相關連結--刪除--資料刪除可多筆處理================================================================
    function goodlink_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["l_id"]){
            $l_id=array(0=>$_REQUEST["l_id"]);
        }else{
            $l_id=$_REQUEST["id"];
        }
        if(!empty($l_id)){
            $l_id_str = implode(",",$l_id);
            //刪除勾選的相關連結
            $sql="delete from ".$cms_cfg['tb_prefix']."_goodlink where l_id in (".$l_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."goodlink.php?func=l_list&lc_id=".$_REQUEST["lc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //顯示訊息並重新導向
    function goto_target_page($url,$sec=0){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }
    //更改狀態
    function change_status($ws_table,$value){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //相關連結分類更改狀態
        if($ws_table=="lc"){
            if($_REQUEST["lc_id"]){
                $lc_id=array(0=>$_REQUEST["lc_id"]);
            }else{
                $lc_id=$_REQUEST["id"];
            }
            if(!empty($lc_id)){
                $lc_id_str = implode(",",$lc_id);
                //更改分類底下的相關連結狀態
                $sql="update ".$cms_cfg['tb_prefix']."_goodlink set l_status=".$value." where lc_id in (".$lc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    //狀態分類狀態
                    $sql="update ".$cms_cfg['tb_prefix']."_goodlink_cate set lc_status=".$value." where lc_id in (".$lc_id_str.")";
                    $rs = $db->query($sql);
                    $db_msg = $db->report();
                    if ( $db_msg == "" ) {
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                        $goto_url=$cms_cfg["manage_url"]."goodlink.php?func=lc_list&lc_id=".$_REQUEST["lc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                        $this->goto_target_page($goto_url);
                    }else{
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                    }
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //相關連結更改狀態
        if($ws_table=="l"){
            if($_REQUEST["l_id"]){
                $l_id=array(0=>$_REQUEST["l_id"]);
            }else{
                $l_id=$_REQUEST["id"];
            }
            if(!empty($l_id)){
                $l_id_str = implode(",",$l_id);
                //刪除勾選的相關連結
                $sql="update ".$cms_cfg['tb_prefix']."_goodlink set l_status=".$value." where l_id in (".$l_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."goodlink.php?func=l_list&lc_id=".$_REQUEST["lc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
    }
    //更改排序值
    function change_sort($ws_table){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //相關連結分類更改排序值
        if(!empty($_REQUEST["sort_value"]) && !empty($ws_table)){
            if($ws_table=="lc"){
                $table_name=$cms_cfg['tb_prefix']."_goodlink_cate";
            }
            if($ws_table=="l"){
                $table_name=$cms_cfg['tb_prefix']."_goodlink";
            }
            foreach($_REQUEST["id"] as $key => $value){
                $sql="update ".$table_name." set ".$ws_table."_sort=".$_REQUEST["sort_value"][$value]." where ".$ws_table."_id='".$value."'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
            }
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."goodlink.php?func=".$ws_table."_list&lc_id=".$_REQUEST["lc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //複製單筆資料
    function copy_data($ws_table){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //相關連結分類複製
        if($ws_table=="lc"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_goodlink_cate where lc_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_goodlink_cate (
                        lc_status,
                        lc_sort,
                        lc_subject
                    ) values (
                        '".$row["lc_status"]."',
                        '".$row["lc_sort"]."',
                        '".addslashes($row["lc_subject"])."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."goodlink.php?func=lc_list&lc_id=".$_REQUEST["lc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //相關連結複製
        if($ws_table=="l"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_goodlink where l_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_goodlink (
                        lc_id,
                        l_status,
                        l_sort,
                        l_hot,
                        l_pop,
                        l_subject,
                        l_content_type,
                        l_content,
                        l_url,
                        l_s_pic,
                        l_modifydate
                    ) values (
                        '".$row["lc_id"]."',
                        '".$row["l_status"]."',
                        '".$row["l_sort"]."',
                        '".$row["l_hot"]."',
                        '".$row["l_pop"]."',
                        '".addslashes($row["l_subject"])."',
                        '".$row["l_content_type"]."',
                        '".addslashes($row["l_content"])."',
                        '".$row["l_url"]."',
                        '".$main->file_str_replace($_REQUEST["l_s_pic"])."',
                        '".$row["l_modifydate"]."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."goodlink.php?func=l_list&lc_id=".$_REQUEST["lc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
    }
    //資料處理
    function data_processing(){
        switch ($_REQUEST["process_type"]){
            case "del":
                if($_REQUEST["ws_table"]=="lc"){
                    $this->goodlink_cate_del();
                }
                if($_REQUEST["ws_table"]=="l"){
                    $this->goodlink_del();
                }
                break;
            case "copy":
                $this->copy_data($_REQUEST["ws_table"]);
                break;
            case "status":
                $this->change_status($_REQUEST["ws_table"],$_REQUEST["value"]);
                break;
            case "sort":
                $this->change_sort($_REQUEST["ws_table"]);
                break;
        }
    }
}
//ob_end_flush();
?>
