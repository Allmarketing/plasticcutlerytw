<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])  || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_ad"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$ad = new AD;
class AD{
    function AD(){
        global $db,$cms_cfg,$tpl;
        $this->current_class="AD";
        switch($_REQUEST["func"]){
            case "ad_list"://廣告列表
                $this->ws_tpl_file = "templates/ws-manage-ad-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->ad_list();
                $this->ws_tpl_type=1;
                break;
            case "ad_add"://廣告新增
                $this->ws_tpl_file = "templates/ws-manage-ad-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_CALENDAR");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_TINYMCE_IMAGE");
                $this->ad_form("add");
                $this->ws_tpl_type=1;
                break;
            case "ad_mod"://廣告修改
                $this->ws_tpl_file = "templates/ws-manage-ad-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_CALENDAR");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_TINYMCE_IMAGE");
                $this->ad_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "ad_replace"://廣告更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->ad_replace();
                $this->ws_tpl_type=1;
                break;
            case "ad_del"://廣告刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->ad_del();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //廣告列表
                $this->ws_tpl_file = "templates/ws-manage-ad-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->ad_list();
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
        $tpl->assignGlobal("CSS_BLOCK_AD","style=\"display:block\"");
         //依權限顯示項目
        $main->mamage_authority();
    }


//廣告--列表================================================================
    function ad_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //聯絡我們分類
        $i=0;
        $tpl->assignGlobal("TAG_NOW_CATE" ,$TPLMSG["NO_CATE"]);
        $tpl->assignGlobal("TAG_".$this->current_class."_CURRENT","class=\"current\"");

        foreach($ws_array["ad_cate"] as $key =>$value){
            $i++;
            $tpl->newBlock( "AD_CATE_LIST" );
            $tpl->assign( array("VALUE_ADC_SUBJECT"  => $value,
                                "VALUE_AD_CATE" => $key,
                                "VALUE_ADC_SERIAL" => $i,
            ));
            if($i%4==0){
                $tpl->assign("TAG_AD_CATE_TRTD","</tr><tr>");
            }
            if($key==$_REQUEST["ad_cate"]){
                $tpl->assignGlobal("TAG_NOW_CATE",$value);
            }
        }
        //廣告列表
        $sql="select * from ".$cms_cfg['tb_prefix']."_ad where ad_id > '0'";
        //附加條件
        $and_str="";
        if(!empty($_REQUEST["ad_cate"])){
            $and_str .= " and ad_cate = '".$_REQUEST["ad_cate"]."'";
        }
        $sql .= $and_str." order by ad_sort ".$cms_cfg['sort_pos'].",ad_modifydate desc ";
        //取得總筆數
        $selectrs = $db->query($sql);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結
        $func_str="ad.php?func=ad_list&ac_cate=".$_REQUEST["ad_cate"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        //分頁並重新組合包含limit的sql語法
        $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("VALUE_TOTAL_BOX" => $rsnum,
                                  "VALUE_AD_CATE"  => $_REQUEST["ad_cate"],
                                  "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                  "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],
        ));
        switch($_REQUEST["st"]){
            case "all" :
                $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK0", "selected");
                break;
            case "ad_subject" :
                $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK1", "selected");
                break;
            case "ad_content" :
                $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK2", "selected");
                break;
        }
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "AD_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_AD_CATE"  => $row["ad_cate"],
                                "VALUE_AD_ID"  => $row["ad_id"],
                                "VALUE_AD_SORT"  => $row["ad_sort"],
                                "VALUE_AD_SUBJECT" => $row["ad_subject"],
                                "VALUE_AD_STARTDATE" => $row["ad_startdate"],
                                "VALUE_AD_SERIAL" => $i,
                                "VALUE_ADC_SUBJECT"  => $ws_array["ad_cate"][$row["ad_cate"]],
                                "VALUE_STATUS_IMG" => ($row["ad_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                "VALUE_STATUS_IMG_ALT" => ($row["ad_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

            ));
            //判斷刊登狀態
            if($row["ad_status"]==0 ||($row["ad_status"]==2 && $row["ad_enddate"] < date("Y-m-d"))){
                $tpl->assign("VALUE_AD_PUBLISH_STATUS","已過期");
            }else{
                $tpl->assign("VALUE_AD_PUBLISH_STATUS","刊登中");
            }

        }
    }
//廣告--表單================================================================
    function ad_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "NOW_AD_ID"  => 0,
                                  "VALUE_AD_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_ad","ad","","",0),
                                  "VALUE_PIC_PREVIEW1" => $cms_cfg['default_preview_pic'],
                                  "STR_AD_STATUS_CK2" => "",
                                  "STR_AD_STATUS_CK1" => "",
                                  "STR_AD_STATUS_CK0" => "checked",
                                  "STR_AD_SORT_TYPE_CK2" => "",
                                  "STR_AD_SORT_TYPE_CK1" => "",
                                  "STR_AD_SORT_TYPE_CK0" => "checked",
                                  "STR_AD_SHOW_TYPE_CK0" => "checked",
                                  "STR_AD_SHOW_TYPE_CK1" => "",
                                  "STR_AD_FILE_TYPE_IMAGE" => "checked",
                                  "STR_AD_FILE_TYPE_FLASH" => "",
                                  "STR_AD_FILE_TYPE_TXT" => "",
                                  "VALUE_ACTION_MODE" => $action_mode
        ));
        $tpl->assignGlobal("TAG_".$this->current_class."_ADD_CURRENT","class=\"current\"");

        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        //如果為修改模式,帶入資料庫資料
        if($action_mode=="mod" && !empty($_REQUEST["ad_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_ad where ad_id='".$_REQUEST["ad_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("VALUE_AD_ID"  => $row["ad_id"],
                                          "VALUE_AD_STATUS"  => $row["ad_status"],
                                          "VALUE_AD_SORT"  => $row["ad_sort"],
                                          "VALUE_AD_SUBJECT" => $row["ad_subject"],
                                          "VALUE_AD_LINK" => $row["ad_link"],
                                          "VALUE_AD_STARTDATE" => $row["ad_startdate"],
                                          "VALUE_AD_ENDDATE" => $row["ad_enddate"],
                                          "VALUE_AD_SHOW_ZONE" => $row["ad_show_zone"],
                                          "STR_AD_STATUS_CK2" => ($row["ad_status"]==2)?"checked":"",
                                          "STR_AD_STATUS_CK1" => ($row["ad_status"]==1)?"checked":"",
                                          "STR_AD_STATUS_CK0" => ($row["ad_status"]==0)?"checked":"",
                                          "STR_AD_SORT_TYPE_CK2" => ($row["ad_sort_type"]==2)?"checked":"",
                                          "STR_AD_SORT_TYPE_CK1" => ($row["ad_sort_type"]==1)?"checked":"",
                                          "STR_AD_SORT_TYPE_CK0" => ($row["ad_sort_type"]==0)?"checked":"",
                                          "STR_AD_SHOW_TYPE_CK1" => ($row["ad_show_type"]==1)?"checked":"",
                                          "STR_AD_SHOW_TYPE_CK0" => ($row["ad_show_type"]==0)?"checked":"",
                                          "STR_AD_FILE_TYPE_IMAGE" => ($row["ad_file_type"]=="image")?"checked":"",
                                          "STR_AD_FILE_TYPE_FLASH" => ($row["ad_file_type"]=="flash")?"checked":"",
                                          "STR_AD_FILE_TYPE_TXT" => ($row["ad_file_type"]=="txt")?"checked":"",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
                $this->get_items_name($row["ad_show_zone"],"pc"); //廣告顯示分類
            }else{
                header("location : ad.php?func=ad_list");
            }
        }
        switch($row["ad_file_type"]){
                case "image":
                    $tpl->assignGlobal(array("FILE_TYPE_DISPLAY_IMAGE"  => "",
                                             "FILE_TYPE_DISPLAY_FLASH"  => "none",
                                             "FILE_TYPE_DISPLAY_TXT"  => "none",
                                             "VALUE_AD_FILE1"  => $row["ad_file"],
                                             "VALUE_PIC_PREVIEW1" => (trim($row["ad_file"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["ad_file"],
                    ));
                    break;
                case "flash":
                    $tpl->assignGlobal(array("FILE_TYPE_DISPLAY_IMAGE"  => "none",
                                             "FILE_TYPE_DISPLAY_FLASH"  => "",
                                             "FILE_TYPE_DISPLAY_TXT"  => "none",
                                             "VALUE_AD_FILE2"  => $row["ad_file"],
                                             "VALUE_PIC_PREVIEW1" => $cms_cfg['default_preview_pic'],
                    ));
                    break;
                case "txt":
                    $tpl->assignGlobal(array("FILE_TYPE_DISPLAY_IMAGE"  => "none",
                                             "FILE_TYPE_DISPLAY_FLASH"  => "none",
                                             "FILE_TYPE_DISPLAY_TXT"  => "",
                                             "VALUE_AD_FILE3"  => $row["ad_file"],
                                             "VALUE_PIC_PREVIEW1" => $cms_cfg['default_preview_pic'],
                    ));
                    break;
                default:
                    $tpl->assignGlobal(array("FILE_TYPE_DISPLAY_IMAGE"  => "",
                                             "FILE_TYPE_DISPLAY_FLASH"  => "none",
                                             "FILE_TYPE_DISPLAY_TXT"  => "none",
                                             "VALUE_AD_FILE1"  => $row["ad_file"],
                                             "VALUE_PIC_PREVIEW1" => (trim($row["ad_file"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["ad_file"],
                    ));
                    break;
            }
        //廣告分類
        foreach($ws_array["ad_cate"] as $key =>$value){
            $i++;
            $tpl->newBlock( "TAG_SELECT_AD_CATE" );
            $tpl->assign( array( "TAG_SELECT_AD_CATE_NAME"  => $value,
                                 "TAG_SELECT_AD_CATE_VALUE" => $key,
                                 "STR_ADC_SEL"       => ($key==$row["ad_cate"])?"selected":""
            ));
        }
    }
//廣告--資料更新================================================================
    function ad_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        switch($_REQUEST["ad_file_type"]){
            case "image":
                $ad_file=$_REQUEST["ad_file1"];
                break;
            case "flash":
                $ad_file=$_REQUEST["ad_file2"];
                break;
            case "txt":
                $ad_file=$_REQUEST["ad_file3"];
                break;
            default:
                $ad_file=$_REQUEST["ad_file1"];
                break;
        }
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_ad (
                        ad_cate,
                        ad_status,
                        ad_sort,
                        ad_subject,
                        ad_file_type,
                        ad_file,
                        ad_link,
                        ad_modifydate,
                        ad_startdate,
                        ad_enddate,
                        ad_show_type,
                        ad_show_zone
                    ) values (
                        '".$_REQUEST["ad_cate"]."',
                        '".$_REQUEST["ad_status"]."',
                        '".$_REQUEST["ad_sort"]."',
                        '".$_REQUEST["ad_subject"]."',
                        '".$_REQUEST["ad_file_type"]."',
                        '".$main->file_str_replace($ad_file)."',
                        '".$_REQUEST["ad_link"]."',
                        '".date("Y-m-d H:i:s")."',
                        '".$_REQUEST["ad_startdate"]."',
                        '".$_REQUEST["ad_enddate"]."',
                        '".$_REQUEST["ad_show_type"]."',
                        '".$_REQUEST["ad_show_zone"]."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_ad set
                        ad_cate='".$_REQUEST["ad_cate"]."',
                        ad_status='".$_REQUEST["ad_status"]."',
                        ad_sort='".$_REQUEST["ad_sort"]."',
                        ad_subject='".$_REQUEST["ad_subject"]."',
                        ad_file_type='".$_REQUEST["ad_file_type"]."',
                        ad_file='".$main->file_str_replace($ad_file)."',
                        ad_link='".$_REQUEST["ad_link"]."',
                        ad_modifydate='".date("Y-m-d H:i:s")."',
                        ad_startdate='".$_REQUEST["ad_startdate"]."',
                        ad_enddate='".$_REQUEST["ad_enddate"]."',
                        ad_show_type='".$_REQUEST["ad_show_type"]."',
                        ad_show_zone='".$_REQUEST["ad_show_zone"]."'
                    where ad_id='".$_REQUEST["ad_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."ad.php?func=ad_list&ad_cate=".$_REQUEST["ad_cate"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//廣告--刪除--資料刪除可多筆處理================================================================
    function ad_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["ad_id"]){
            $ad_id=array(0=>$_REQUEST["ad_id"]);
        }else{
            $ad_id=$_REQUEST["id"];
        }
        if(!empty($ad_id)){
            $ad_id_str = implode(",",$ad_id);
            //刪除勾選的廣告
            $sql="delete from ".$cms_cfg['tb_prefix']."_ad where ad_id in (".$ad_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."ad.php?func=ad_list&ad_cate=".$_REQUEST["ad_cate"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //廣告更改狀態
        if($ws_table=="ad"){
            if($_REQUEST["ad_id"]){
                $ad_id=array(0=>$_REQUEST["ad_id"]);
            }else{
                $ad_id=$_REQUEST["id"];
            }
            if(!empty($ad_id)){
                $ad_id_str = implode(",",$ad_id);
                //刪除勾選的廣告
                $sql="update ".$cms_cfg['tb_prefix']."_ad set ad_status=".$value." where ad_id in (".$ad_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."ad.php?func=ad_list&ad_cate=".$_REQUEST["ad_cate"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //廣告分類更改排序值
        if(!empty($_REQUEST["sort_value"]) && !empty($ws_table)){
            if($ws_table=="ad"){
                $table_name=$cms_cfg['tb_prefix']."_ad";
            }
            foreach($_REQUEST["id"] as $key => $value){
                $sql="update ".$table_name." set ".$ws_table."_sort=".$_REQUEST["sort_value"][$value]." where ".$ws_table."_id='".$value."'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
            }
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."ad.php?func=".$ws_table."_list&ad_cate=".$_REQUEST["ad_cate"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //複製單筆資料
    function copy_data($ws_table){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //廣告複製
        if($ws_table=="ad"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_ad where ad_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_ad (
                        ad_cate,
                        ad_status,
                        ad_sort,
                        ad_subject,
                        ad_file_type,
                        ad_file,
                        ad_link,
                        ad_modifydate,
                        ad_startdate,
                        ad_enddate,
                        ad_show_type,
                        ad_show_zone
                    ) values (
                        '".$row["ad_cate"]."',
                        '".$row["ad_status"]."',
                        '".$row["ad_sort"]."',
                        '".$row["ad_subject"]."',
                        '".$row["ad_file_type"]."',
                        '".$row["ad_file"]."',
                        '".$row["ad_link"]."',
                        '".date("Y-m-d H:i:s")."',
                        '".$row["ad_startdate"]."',
                        '".$row["ad_enddate"]."',
                        '".$row["ad_show_type"]."',
                        '".$row["ad_show_zone"]."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."ad.php?func=ad_list&ad_cate=".$_REQUEST["ad_cate"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
                if($_REQUEST["ws_table"]=="ad"){
                    $this->ad_del();
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
    function get_items_name($id_str,$type){
        global $db,$tpl,$cms_cfg;
        if(trim($id_str)){
            if($type=="pc"){
                $sql="select pc_id,pc_name from ".$cms_cfg['tb_prefix']."_products_cate where pc_id in (".$id_str.")";
                $selectrs = $db->query($sql);
                while ( $row = $db->fetch_array($selectrs,1) ) {
                    $name_str  .= $row["pc_id"]."-".$row["pc_name"]."<br>";
                }
            }
            if($type=="p"){
                $sql="select p_id,p_name from ".$cms_cfg['tb_prefix']."_products where p_id in (".$id_str.")";
                $selectrs = $db->query($sql);
                while ( $row = $db->fetch_array($selectrs,1) ) {
                    $name_str  .= $row["p_id"]."-".$row["p_name"]."<br>";
                }
            }
            $tpl->assignGlobal("VALUE_NAME_STR",$name_str);
        }
    }
}
//ob_end_flush();
?>
