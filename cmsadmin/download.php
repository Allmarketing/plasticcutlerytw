<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])  || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_download"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$download = new DOWNLOAD;
class DOWNLOAD{
    function DOWNLOAD(){
        global $db,$cms_cfg,$tpl;
        $this->seo=($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_seo"] && $cms_cfg["ws_module"]["ws_seo"])?1:0;
        switch($_REQUEST["func"]){
            case "dc_list"://檔案下載分類列表
                $this->current_class="DC";
                $this->ws_tpl_file = "templates/ws-manage-download-cate-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->download_cate_list();
                $this->ws_tpl_type=1;
                break;
            case "dc_add"://檔案下載分類新增
                $this->current_class="DC";
                $this->ws_tpl_file = "templates/ws-manage-download-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->download_cate_form("add");
                $this->ws_tpl_type=1;
                break;
            case "dc_mod"://檔案下載分類修改
                $this->current_class="DC";
                $this->ws_tpl_file = "templates/ws-manage-download-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->download_cate_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "dc_replace"://檔案下載分類更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->download_cate_replace();
                $this->ws_tpl_type=1;
                break;
            case "dc_del"://檔案下載分類刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->download_cate_del();
                $this->ws_tpl_type=1;
                break;
            case "d_list"://檔案下載列表
                $this->current_class="D";
                $this->ws_tpl_file = "templates/ws-manage-download-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->download_list();
                $this->ws_tpl_type=1;
                break;
            case "d_add"://檔案下載新增
                $this->current_class="D";
                $this->ws_tpl_file = "templates/ws-manage-download-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_TINYMCE_FILE");
                $this->download_form("add");
                $this->ws_tpl_type=1;
                break;
            case "d_mod"://檔案下載修改
                $this->current_class="D";
                $this->ws_tpl_file = "templates/ws-manage-download-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_TINYMCE_FILE");
                $this->download_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "d_replace"://檔案下載更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->download_replace();
                $this->ws_tpl_type=1;
                break;
            case "d_del"://檔案下載刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->download_del();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //檔案下載列表
                $this->current_class="D";
                $this->ws_tpl_file = "templates/ws-manage-download-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->download_list();
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
        $tpl->assignGlobal("CSS_BLOCK_DOWNLOAD","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }

    //檔案下載分類--列表
    function download_cate_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $sql="select * from ".$cms_cfg['tb_prefix']."_download_cate where dc_id > '0'";
        $and_str = "";
        if(!empty($_REQUEST["sk"])){
            $and_str = " and dc_subject like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by dc_sort ".$cms_cfg['sort_pos']." ";
        //取得總筆數
        $total_records=$main->count_total_records($sql);
        //取得分頁連結
        $func_str="download.php?func=dc_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
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
            $tpl->newBlock( "DOWNLOAD_CATE_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_DC_ID"  => $row["dc_id"],
                                "VALUE_DC_SORT"  => $row["dc_sort"],
                                "VALUE_DC_SUBJECT" => $row["dc_subject"],
                                "VALUE_DC_SERIAL" => $i,
                                "VALUE_STATUS_IMG" => ($row["dc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                "VALUE_STATUS_IMG_ALT" => ($row["dc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

            ));
        }
    }
    //檔案下載分類--表單
    function download_cate_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        if($this->seo){
            $tpl->newBlock("SEO_EDIT_ZONE");
        }
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "NOW_DC_ID"  => 0,
                                  "VALUE_DC_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_download_cate","dc","","",0),
                                  "STR_DC_STATUS_CK1" => "checked",
                                  "STR_DC_STATUS_CK0" => "",
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
        if($action_mode=="mod" && !empty($_REQUEST["dc_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_download_cate where dc_id='".$_REQUEST["dc_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("NOW_DC_ID"  => $row["dc_id"],
                                          "VALUE_DC_ID"  => $row["dc_id"],
                                          "VALUE_DC_SORT"  => $row["dc_sort"],
                                          "VALUE_DC_SUBJECT" => $row["dc_subject"],
                                          "STR_DC_STATUS_CK1" => ($row["dc_status"])?"checked":"",
                                          "STR_DC_STATUS_CK0" => ($row["dc_status"])?"":"checked",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
                if($this->seo){
                    $tpl->assignGlobal( array("VALUE_DC_SEO_TITLE" => $row["dc_seo_title"],
                                              "VALUE_DC_SEO_KEYWORD" => $row["dc_seo_keyword"],
                                              "VALUE_DC_SEO_DESCRIPTION" => $row["dc_seo_description"],
                                              "VALUE_DC_SEO_FILENAME" => $row["dc_seo_filename"],
                                              "VALUE_DC_SEO_H1" => $row["dc_seo_h1"],
                                              "VALUE_DC_SEO_SHORT_DESC" => $row["dc_seo_short_desc"],
                    ));
                }
            }else{
                header("location : download.php?func=dc_list");
                die();
            }
        }
    }
    //檔案下載分類--資料更新
    function download_cate_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($this->seo){
            $add_field_str="dc_seo_title,
                            dc_seo_keyword,
                            dc_seo_description,
                            dc_seo_filename,
                            dc_seo_h1,
                            dc_seo_short_desc,";
            $add_value_str="'".htmlspecialchars($_REQUEST["dc_seo_title"])."',
                            '".htmlspecialchars($_REQUEST["dc_seo_keyword"])."',
                            '".htmlspecialchars($_REQUEST["dc_seo_description"])."',
                            '".htmlspecialchars($_REQUEST["dc_seo_filename"])."',
                            '".htmlspecialchars($_REQUEST["dc_seo_h1"])."',
                            '".htmlspecialchars($_REQUEST["dc_seo_short_desc"])."',";
            $update_str="dc_seo_title='".htmlspecialchars($_REQUEST["dc_seo_title"])."',
                         dc_seo_keyword='".htmlspecialchars($_REQUEST["dc_seo_keyword"])."',
                         dc_seo_description='".htmlspecialchars($_REQUEST["dc_seo_description"])."',
                         dc_seo_filename='".htmlspecialchars($_REQUEST["dc_seo_filename"])."',
                         dc_seo_h1='".htmlspecialchars($_REQUEST["dc_seo_h1"])."',
                         dc_seo_short_desc='".htmlspecialchars($_REQUEST["dc_seo_short_desc"])."',";
        }
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_download_cate (
                        dc_status,
                        dc_sort,
                        ".$add_field_str."
                        dc_subject
                    ) values (
                        '".$_REQUEST["dc_status"]."',
                        '".$_REQUEST["dc_sort"]."',
                        ".$add_value_str."
                        '".$_REQUEST["dc_subject"]."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_download_cate set
                        dc_status='".$_REQUEST["dc_status"]."',
                        dc_sort='".$_REQUEST["dc_sort"]."',
                        ".$update_str."
                        dc_subject='".$_REQUEST["dc_subject"]."'
                    where  dc_id='".$_REQUEST["dc_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."download.php?func=dc_list&dc_id=".$_REQUEST["dc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //檔案下載分類--刪除
    function download_cate_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["dc_id"]){
            $dc_id=array(0=>$_REQUEST["dc_id"]);
        }else{
            $dc_id=$_REQUEST["id"];
        }
        if(!empty($dc_id)){
            $dc_id_str = implode(",",$dc_id);
            //清空分類底下的檔案下載
            $sql="delete from ".$cms_cfg['tb_prefix']."_download where  dc_id in (".$dc_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                //刪除分類
                $sql="delete from ".$cms_cfg['tb_prefix']."_download_cate where dc_id in (".$dc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."download.php?func=dc_list&dc_id=".$_REQUEST["dc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//檔案下載--列表================================================================
    function download_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $sql="select * from ".$cms_cfg['tb_prefix']."_download_cate where dc_id > '0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        //沒有分類先建立分類
        if($rsnum < 1){
            $tpl->assignGlobal( "MSG_CREATE_CATE_FIRST" , $TPLMSG["CREATE_CATE_FIRST"]);
            $goto_url=$cms_cfg["manage_url"]."download.php?func=dc_add";
            $this->goto_target_page($goto_url);
        }else{
            $i=0;
            $tpl->assignGlobal(array("MSG_NOW_CATE" =>$TPLMSG["NOW_CATE"],
                                     "TAG_NOW_CATE" =>$TPLMSG["NO_CATE"],
            ));
            while($row = $db->fetch_array($selectrs,1)){
                $i++;
                $tpl->newBlock( "DOWNLOAD_CATE_LIST" );
                $tpl->assign( array( "VALUE_DC_SUBJECT"  => $row["dc_subject"],
                                     "VALUE_DC_ID" => $row["dc_id"],
                                     "VALUE_DC_SERIAL" => $i,
                                     "VALUE_STATUS_IMG" => ($row["dc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                     "VALUE_STATUS_IMG_ALT" => ($row["dc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
                ));
                if($i%4==0){
                    $tpl->assign("TAG_DOWNLOAD_CATE_TRTD","</tr><tr>");
                }
                if($row["dc_id"]==$_REQUEST["dc_id"]){
                    $tpl->assignGlobal("TAG_NOW_CATE",$row["dc_subject"]);
                }
            }
            //檔案下載列表
            $sql="select d.*,dc.dc_subject from ".$cms_cfg['tb_prefix']."_download as d left join ".$cms_cfg['tb_prefix']."_download_cate as dc on d.dc_id=dc.dc_id where d.d_id > '0'";
            //附加條件
            $and_str="";
            if(!empty($_REQUEST["dc_id"])){
                $and_str .= " and d.dc_id = '".$_REQUEST["dc_id"]."'";
            }
            if($_REQUEST["st"]=="all"){
                $and_str .= " and (d.d_subject like '%".$_REQUEST["sk"]."%' or d.d_content like '%".$_REQUEST["sk"]."%')";
            }
            if($_REQUEST["st"]=="d_subject"){
                $and_str .= " and d.d_subject like '%".$_REQUEST["sk"]."%'";
            }
            if($_REQUEST["st"]=="d_content"){
                $and_str .= " and d.d_content like '%".$_REQUEST["sk"]."%'";
            }
            $sql .= $and_str." order by dc.dc_sort ".$cms_cfg['sort_pos'].",d.d_sort ".$cms_cfg['sort_pos'].",d.d_modifydate desc ";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records    = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="download.php?func=d_list&dc_id=".$_REQUEST["dc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
            //分頁且重新組合包含limit的sql語法
            $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $tpl->assignGlobal( array("VALUE_TOTAL_BOX" => $rsnum,
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                      "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR']

            ));
            switch($_REQUEST["st"]){
                case "all" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK0", "selected");
                    break;
                case "d_subject" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK1", "selected");
                    break;
                case "d_content" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK2", "selected");
                    break;
            }
            $tpl->assignGlobal( "VALUE_NOW_DC_ID" , $_REQUEST["dc_id"]);
            $i=$page["start_serial"];
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "DOWNLOAD_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow'");
                }
                $tpl->assign( array("VALUE_DC_ID"  => $row["dc_id"],
                                    "VALUE_D_ID"  => $row["d_id"],
                                    "VALUE_D_SORT"  => $row["d_sort"],
                                    "VALUE_D_SUBJECT" => $row["d_subject"],
                                    "VALUE_D_SERIAL" => $i,
                                    "VALUE_DC_SUBJECT"  => $row["dc_subject"],
                                    "VALUE_STATUS_IMG" => ($row["d_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                    "VALUE_STATUS_IMG_ALT" => ($row["d_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

                ));
            }
        }
    }
//檔案下載--表單================================================================
    function download_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //欄位名稱
        $cate=(trim($_REQUEST["dc_id"])!="")?1:0;
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "VALUE_D_SORT"           => $main->get_max_sort_value($cms_cfg['tb_prefix']."_download","d","dc_id",$_REQUEST["dc_id"],$cate),
                                  "STR_D_STATUS_CK1"       => "checked",
                                  "STR_D_STATUS_CK0"       => "",
                                  "VALUE_D_THUMB_PREVIEW1" => $cms_cfg['default_preview_pic'],
                                  "VALUE_ACTION_MODE"      => $action_mode,
                                  "PUBLIC_RAD_0_CHK"       => "checked"
        ));
        //是否顯示公開下載欄位
        if($cms_cfg['ws_module']['ws_member_download']){
            $tpl->newBlock("DOWNLOAD_PUBLIC_OR_NOT");
        }
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        //如果為修改模式,帶入資料庫資料
        if($action_mode=="mod" && !empty($_REQUEST["d_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_download where d_id='".$_REQUEST["d_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("VALUE_D_ID"  => $row["d_id"],
                                          "VALUE_D_SORT"  => $row["d_sort"],
                                          "VALUE_D_SUBJECT" => $row["d_subject"],
                                          "VALUE_D_CONTENT" => $row["d_content"],
                                          "VALUE_D_FILEPATH" => $row["d_filepath"],
                                          "STR_D_STATUS_CK1" => ($row["d_status"]==1)?"checked":"",
                                          "STR_D_STATUS_CK0" => ($row["d_status"]==0)?"checked":"",
                                          "PUBLIC_RAD_1_CHK" => ($row['d_public']==1)?"checked":"", 
                                          "PUBLIC_RAD_0_CHK" => ($row['d_public']==0)?"checked":"", 
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
            }else{
                header("location : download.php?func=d_list");
                die();
            }
        }
        //檔案下載分類
        $sql="select * from ".$cms_cfg['tb_prefix']."_download_cate where dc_id > '0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        while($row1 = $db->fetch_array($selectrs,1)){
            $tpl->newBlock( "TAG_SELECT_DOWNLOAD_CATE" );
            $tpl->assign( array( "TAG_SELECT_DOWNLOAD_CATE_NAME"  => $row1["dc_subject"],
                                 "TAG_SELECT_DOWNLOAD_CATE_VALUE" => $row1["dc_id"],
                                 "STR_DC_SEL"       => ($row1["dc_id"]==$_GET["dc_id"])?"selected":""
            ));
        }
        if($cms_cfg['ws_module']['ws_download_thumb']){
            $tpl->newBlock("THUMB_ROW");
            $tpl->assign(array(
                "VALUE_D_THUMB"          => $row["d_thumb"],
                "VALUE_D_THUMB_PREVIEW1" => trim($row["d_thumb"])?$cms_cfg['file_root'].$row["d_thumb"]:$cms_cfg['default_preview_pic'],                
            ));
        }
    }
//檔案下載--資料更新================================================================
    function download_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //設定d_public
        $d_public = ($cms_cfg['ws_module']['ws_member_download'])?$_REQUEST["d_public"]:1;
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_download (
                        dc_id,
                        d_status,
                        d_sort,
                        d_thumb,
                        d_public,
                        d_subject,
                        d_content,
                        d_filepath,
                        d_modifydate
                    ) values (
                        '".$_REQUEST["dc_id"]."',
                        '".$_REQUEST["d_status"]."',
                        '".$_REQUEST["d_sort"]."',
                        '".$main->file_str_replace($_REQUEST["d_thumb"])."',
                        '".$d_public."',
                        '".$_REQUEST["d_subject"]."',
                        '".$_REQUEST["d_content"]."',
                        '".$main->file_str_replace($_REQUEST["d_filepath"])."',
                        '".date("Y-m-d H:i:s")."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_download set
                        dc_id='".$_REQUEST["dc_id"]."',
                        d_status='".$_REQUEST["d_status"]."',
                        d_sort='".$_REQUEST["d_sort"]."',
                        d_thumb='".$main->file_str_replace($_REQUEST["d_thumb"])."',
                        d_public='".$d_public."',
                        d_subject='".$_REQUEST["d_subject"]."',
                        d_content='".$_REQUEST["d_content"]."',
                        d_filepath='".$main->file_str_replace($_REQUEST["d_filepath"])."',
                        d_modifydate='".date("Y-m-d H:i:s")."'
                    where d_id='".$_REQUEST["d_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."download.php?func=d_list&dc_id=".$_REQUEST["dc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//檔案下載--刪除--資料刪除可多筆處理================================================================
    function download_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["d_id"]){
            $d_id=array(0=>$_REQUEST["d_id"]);
        }else{
            $d_id=$_REQUEST["id"];
        }
        if(!empty($d_id)){
            $d_id_str = implode(",",$d_id);
            //刪除勾選的檔案下載
            $sql="delete from ".$cms_cfg['tb_prefix']."_download where d_id in (".$d_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."download.php?func=d_list&dc_id=".$_REQUEST["dc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //檔案下載分類更改狀態
        if($ws_table=="dc"){
            if($_REQUEST["dc_id"]){
                $dc_id=array(0=>$_REQUEST["dc_id"]);
            }else{
                $dc_id=$_REQUEST["id"];
            }
            if(!empty($dc_id)){
                $dc_id_str = implode(",",$dc_id);
                //更改分類底下的檔案下載狀態
                $sql="update ".$cms_cfg['tb_prefix']."_download set d_status='".$value."' where dc_id in (".$dc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    //狀態分類狀態
                    $sql="update ".$cms_cfg['tb_prefix']."_download_cate set dc_status='".$value."' where dc_id in (".$dc_id_str.")";
                    $rs = $db->query($sql);
                    $db_msg = $db->report();
                    if ( $db_msg == "" ) {
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                        $goto_url=$cms_cfg["manage_url"]."download.php?func=dc_list&dc_id=".$_REQUEST["dc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                        $this->goto_target_page($goto_url);
                    }else{
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                    }
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //檔案下載更改狀態
        if($ws_table=="d"){
            if($_REQUEST["d_id"]){
                $d_id=array(0=>$_REQUEST["d_id"]);
            }else{
                $d_id=$_REQUEST["id"];
            }
            if(!empty($d_id)){
                $d_id_str = implode(",",$d_id);
                //刪除勾選的檔案下載
                $sql="update ".$cms_cfg['tb_prefix']."_download set d_status='".$value."' where d_id in (".$d_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."download.php?func=d_list&dc_id=".$_REQUEST["dc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //檔案下載分類更改排序值
        if(!empty($_REQUEST["sort_value"]) && !empty($ws_table)){
            if($ws_table=="dc"){
                $table_name=$cms_cfg['tb_prefix']."_download_cate";
            }
            if($ws_table=="d"){
                $table_name=$cms_cfg['tb_prefix']."_download";
            }
            foreach($_REQUEST["id"] as $key => $value){
                $sql="update ".$table_name." set ".$ws_table."_sort='".$_REQUEST["sort_value"][$value]."' where ".$ws_table."_id='".$value."'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
            }
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."download.php?func=".$ws_table."_list&dc_id=".$_REQUEST["dc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //複製單筆資料
    function copy_data($ws_table){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //檔案下載分類複製
        if($ws_table=="dc"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_download_cate where dc_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_download_cate (
                        dc_status,
                        dc_sort,
                        dc_subject
                    ) values (
                        '".$row["dc_status"]."',
                        '".$row["dc_sort"]."',
                        '".$row["dc_subject"]."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."download.php?func=dc_list&dc_id=".$_REQUEST["dc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //檔案下載複製
        if($ws_table=="d"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_download where d_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_download (
                        dc_id,
                        d_status,
                        d_sort,
                        d_subject,
                        d_content,
                        d_filepath,
                        d_modifydate
                    ) values (
                        '".$row["dc_id"]."',
                        '".$row["d_status"]."',
                        '".$row["d_sort"]."',
                        '".$row["d_subject"]."',
                        '".$row["d_content"]."',
                        '".$row["d_filepath"]."',
                        '".$row["d_modifydate"]."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."download.php?func=d_list&dc_id=".$_REQUEST["dc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
                if($_REQUEST["ws_table"]=="dc"){
                    $this->download_cate_del();
                }
                if($_REQUEST["ws_table"]=="d"){
                    $this->download_del();
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
