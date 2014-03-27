<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])  || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_aboutus"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$aboutus = new ABOUTUS;
class ABOUTUS{
    function ABOUTUS(){
        global $db,$cms_cfg,$tpl;
        $this->seo=($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_seo"] && $cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->current_class="AU";
        switch($_REQUEST["func"]){
            case "au_list"://關於我們列表
                $this->ws_tpl_file = "templates/ws-manage-aboutus-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->aboutus_list();
                $this->ws_tpl_type=1;
                break;
            case "au_add"://關於我們新增
                $this->ws_tpl_file = "templates/ws-manage-aboutus-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_TINYMCE");
                $this->aboutus_form("add");
                $this->ws_tpl_type=1;
                break;
            case "au_mod"://關於我們修改
                $this->ws_tpl_file = "templates/ws-manage-aboutus-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_TINYMCE");
                $this->aboutus_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "au_replace"://關於我們更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->aboutus_replace();
                $this->ws_tpl_type=1;
                break;
            case "au_del"://關於我們刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->aboutus_del();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //關於我們列表
                $this->ws_tpl_file = "templates/ws-manage-aboutus-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->aboutus_list();
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
        $tpl->assignGlobal("CSS_BLOCK_ABOUTUS","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }


    //關於我們--列表================================================================
    function aboutus_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //關於我們列表
        $sql="select * from ".$cms_cfg['tb_prefix']."_aboutus  where au_id > '0'";
        //附加條件
        $and_str="";
        if($_REQUEST["st"]=="all"){
            $and_str .= " and (au_subject like '%".$_REQUEST["sk"]."%' or au_content like '%".$_REQUEST["sk"]."%')";
        }
        if($_REQUEST["st"]=="au_subject"){
            $and_str .= " and au_subject like '%".$_REQUEST["sk"]."%'";
        }
        if($_REQUEST["st"]=="au_content"){
            $and_str .= " and au_content like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by au_cate,au_sort ".$cms_cfg['sort_pos'].",au_modifydate desc ";
        //取得總筆數
        $selectrs = $db->query($sql);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結
        $fumc_str="aboutus.php?func=au_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        //分頁並重新組合包含limit的sql語法
        $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$fumc_str,$total_records,$sql);
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
            case "au_subject" :
                $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK1", "selected");
                break;
            case "au_content" :
                $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK2", "selected");
                break;
        }
        if($cms_cfg['ws_module']['ws_aboutus_au_cate']){
            $tpl->newBlock("AU_CATE_TITLE");
        }
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "ABOUTUS_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_AU_ID"  => $row["au_id"],
                                "VALUE_AU_SORT"  => $row["au_sort"],
                                "VALUE_AU_SUBJECT" => $row["au_subject"],
                                "VALUE_AU_SERIAL" => $i,
                                "VALUE_STATUS_IMG" => ($row["au_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                "VALUE_STATUS_IMG_ALT" => ($row["au_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

            ));
            if($cms_cfg['ws_module']['ws_aboutus_au_cate']){
                $tpl->newBlock("AU_CATE_FIELD");
                $tpl->assign("VALUE_AU_CATE",$ws_array["main"][$row['au_cate']]);
            }
        }
    }
//關於我們--表單================================================================
    function aboutus_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        if($this->seo){
            $tpl->newBlock("SEO_EDIT_ZONE");
        }
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "NOW_AU_ID"  => 0,
                                  "VALUE_AU_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_aboutus","au","","",0),
                                  "STR_AU_STATUS_CK1" => "checked",
                                  "STR_AU_STATUS_CK0" => "",
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
        if($action_mode=="mod" && !empty($_REQUEST["au_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_aboutus where au_id='".$_REQUEST["au_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("NOW_AU_ID"  => $row["au_id"],
                                          "VALUE_AU_ID"  => $row["au_id"],
                                          "VALUE_AU_SORT"  => $row["au_sort"],
                                          "VALUE_AU_SUBJECT" => $row["au_subject"],
                                          "STR_AU_STATUS_CK1" => ($row["au_status"]==1)?"checked":"",
                                          "STR_AU_STATUS_CK0" => ($row["au_status"]==0)?"checked":"",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
                if($this->seo){
                    $tpl->assignGlobal( array("VALUE_AU_SEO_TITLE" => $row["au_seo_title"],
                                              "VALUE_AU_SEO_KEYWORD" => $row["au_seo_keyword"],
                                              "VALUE_AU_SEO_DESCRIPTION" => $row["au_seo_description"],
                                              "VALUE_AU_SEO_FILENAME" => $row["au_seo_filename"],
                                              "VALUE_AU_SEO_H1" => $row["au_seo_h1"],
                    ));
                }
            }else{
                header("location : aboutus.php?func=au_list");
            }
        }
        //有獨立類別
        if($cms_cfg['ws_module']['ws_aboutus_au_cate']){
            $tpl->newBlock("UNIQUE_CATE");
        $this->get_au_cate_option($row);
            if($cms_cfg['ws_module']['ws_aboutus_au_cate_input']){
                $tpl->newblock("AU_CATE_INPUT");
            }
        }        
        if($cms_cfg["ws_module"]["ws_wysiwyg"]=="tinymce"){
            $tpl->newBlock("WYSIWYG_TINYMCE1");
            $tpl->assign( "VALUE_AU_CONTENT" , $row["au_content"] );
        }
    }
//關於我們--資料更新================================================================
    function aboutus_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $main->magic_gpc($_REQUEST);
        if($this->seo){
            $add_field_str="au_seo_title,
                            au_seo_keyword,
                            au_seo_description,
                            au_seo_filename,
                            au_seo_h1,";
            $add_value_str="'".htmlspecialchars($_REQUEST["au_seo_title"])."',
                            '".htmlspecialchars($_REQUEST["au_seo_keyword"])."',
                            '".htmlspecialchars($_REQUEST["au_seo_description"])."',
                            '".htmlspecialchars($_REQUEST["au_seo_filename"])."',
                            '".htmlspecialchars($_REQUEST["au_seo_h1"])."',";
            $update_str="au_seo_title='".htmlspecialchars($_REQUEST["au_seo_title"])."',
                         au_seo_keyword='".htmlspecialchars($_REQUEST["au_seo_keyword"])."',
                         au_seo_description='".htmlspecialchars($_REQUEST["au_seo_description"])."',
                         au_seo_filename='".htmlspecialchars($_REQUEST["au_seo_filename"])."',
                         au_seo_h1='".htmlspecialchars($_REQUEST["au_seo_h1"])."',";
        }
        //設定類別
        $addtion_au_cate = $_REQUEST['au_cate_input']?$_REQUEST['au_cate_input']:$_REQUEST['au_cate_select'];
        $au_cate = $addtion_au_cate?strtolower($addtion_au_cate):'aboutus';
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_aboutus (
                        au_status,
                        au_sort,
                        au_cate,
                        au_subject,
                        au_content,
                        ".$add_field_str."
                        au_modifydate
                    ) values (
                        '".$_REQUEST["au_status"]."',
                        '".$_REQUEST["au_sort"]."',
                        '".$au_cate."',
                        '".$_REQUEST["au_subject"]."',
                        '".$db->quote($main->content_file_str_replace($_REQUEST["au_content"]))."',
                        ".$add_value_str."
                        '".date("Y-m-d H:i:s")."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_aboutus set
                        au_status='".$_REQUEST["au_status"]."',
                        au_sort='".$_REQUEST["au_sort"]."',
                        au_cate='".$au_cate."',
                        au_subject='".$_REQUEST["au_subject"]."',
                        au_content='".$db->quote($main->content_file_str_replace($_REQUEST["au_content"]))."',
                        ".$update_str."
                        au_modifydate='".date("Y-m-d H:i:s")."'
                    where au_id='".$_REQUEST["au_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."aboutus.php?func=au_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//關於我們--刪除--資料刪除可多筆處理================================================================
    function aboutus_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["au_id"]){
            $au_id=array(0=>$_REQUEST["au_id"]);
        }else{
            $au_id=$_REQUEST["id"];
        }
        if(!empty($au_id)){
            $au_id_str = implode(",",$au_id);
            //刪除勾選的最新消息
            $sql="delete from ".$cms_cfg['tb_prefix']."_aboutus where au_id in (".$au_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."aboutus.php?func=au_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //關於我們更改狀態
        if($ws_table=="au"){
            if($_REQUEST["au_id"]){
                $au_id=array(0=>$_REQUEST["au_id"]);
            }else{
                $au_id=$_REQUEST["id"];
            }
            if(!empty($au_id)){
                $au_id_str = implode(",",$au_id);
                //更改勾選的關於我們狀態
                $sql="update ".$cms_cfg['tb_prefix']."_aboutus set au_status='".$value."' where au_id in (".$au_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."aboutus.php?func=au_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //關於我們更改排序值
        if(!empty($_REQUEST["sort_value"]) && !empty($ws_table)){
            if($ws_table=="au"){
                $table_name=$cms_cfg['tb_prefix']."_aboutus";
            }
            foreach($_REQUEST["id"] as $key => $value){
                $sql="update ".$table_name." set ".$ws_table."_sort='".$_REQUEST["sort_value"][$value]."' where ".$ws_table."_id='".$value."'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
            }
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."aboutus.php?func=".$ws_table."_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //複製單筆資料
    function copy_data($ws_table){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //關於我們複製
        if($ws_table=="au"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_aboutus where au_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_aboutus (
                        au_status,
                        au_sort,
                        au_subject,
                        au_content,
                        au_modifydate
                    ) values (
                        ".$row["au_status"].",
                        '".$row["au_sort"]."',
                        '".$row["au_subject"]."',
                        '".$row["au_content"]."',
                        '".date("Y-m-d H:i:s")."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."aboutus.php?func=au_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }

    //資料處理
    function data_processing(){
        switch ($_REQUEST["process_type"]){
            case "del":
                if($_REQUEST["ws_table"]=="au"){
                    $this->aboutus_del();
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
    
    //取得au_cate
    function get_au_cate_option($rdata){
        global $db,$tpl,$cms_cfg;
        $tpl->assignGlobal("CATE_AREA_TYPE",($rdata && $rdata['au_cate']!='aboutus')?"block":"none");
        $tpl->assignGlobal("AU_CATE_CHECKED",($rdata && $rdata['au_cate']!='aboutus')?"checked":"");
        $sql = "select distinct au_cate from ".$cms_cfg['tb_prefix']."_aboutus where au_cate<>'aboutus'";
        $res = $db->query($sql,true);
        while($row = $db->fetch_array($res,1)){
            $tpl->newBlock("AU_CATE_OPTION");
            $tpl->assign("VALUE_AU_CATE",$row['au_cate']);
            $tpl->assign("VALUE_AU_OPTION_SELECTED",($row['au_cate']==$rdata['au_cate'])?"selected":"");
        }
    }
}
//ob_end_flush();
?>
