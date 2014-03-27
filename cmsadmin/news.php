<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]) || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_news"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$news = new NEWS;
class NEWS{
    function NEWS(){
        global $db,$cms_cfg,$tpl;
        $this->seo=($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_seo"] && $cms_cfg["ws_module"]["ws_seo"])?1:0;
        switch($_REQUEST["func"]){
            case "ajax":
                if(method_exists($this, "ajax_".$_GET['act'])){
                    $method = "ajax_".$_GET['act'];
                    $this->$method();
                }    
                $this->ws_tpl_type=0;
                break;
            case "nc_list"://最新消息分類列表
                $this->current_class="NC";
                $this->ws_tpl_file = "templates/ws-manage-news-cate-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->news_cate_list();
                $this->ws_tpl_type=1;
                break;
            case "nc_add"://最新消息分類新增
                $this->current_class="NC";
                $this->ws_tpl_file = "templates/ws-manage-news-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->news_cate_form("add");
                $this->ws_tpl_type=1;
                break;
            case "nc_mod"://最新消息分類修改
                $this->current_class="NC";
                $this->ws_tpl_file = "templates/ws-manage-news-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->news_cate_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "nc_replace"://最新消息分類更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->news_cate_replace();
                $this->ws_tpl_type=1;
                break;
            case "nc_del"://最新消息分類刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->news_cate_del();
                $this->ws_tpl_type=1;
                break;
            case "n_file":
                $this->news_files();
                break;             
            case "n_list"://最新消息列表
                $this->current_class="N";
                $this->ws_tpl_file = "templates/ws-manage-news-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->news_list();
                $this->ws_tpl_type=1;
                break;
            case "n_add"://最新消息新增
                $this->current_class="N";
                $this->ws_tpl_file = "templates/ws-manage-news-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_CALENDAR");
                $tpl->newBlock("JS_TINYMCE");
                $this->news_form("add");
                $this->ws_tpl_type=1;
                break;
            case "n_mod"://最新消息修改
                $this->current_class="N";
                $this->ws_tpl_file = "templates/ws-manage-news-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_CALENDAR");
                $tpl->newBlock("JS_TINYMCE");
                $tpl->newBlock("JS_JQ_UI");
                $this->news_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "n_replace"://最新消息更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->news_replace();
                $this->ws_tpl_type=1;
                break;
            case "n_del"://最新消息刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->news_del();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //最新消息列表
                $this->current_class="N";
                $this->ws_tpl_file = "templates/ws-manage-news-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->news_list();
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
        $tpl->assignGlobal("CSS_BLOCK_NEWS","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }

    //最新消息分類--列表
    function news_cate_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_news_cate where nc_id > '0'";
        $and_str = "";
        if(!empty($_REQUEST["sk"])){
            $and_str = " and nc_subject like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by nc_sort ".$cms_cfg['sort_pos']." ";
        //取得總筆數
        $total_records=$main->count_total_records($sql);
        //取得分頁連結
        $func_str="news.php?func=nc_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
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
            $tpl->newBlock( "NEWS_CATE_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_NC_ID"  => $row["nc_id"],
                                "VALUE_NC_STATUS"  => $row["nc_status"],
                                "VALUE_NC_SORT"  => $row["nc_sort"],
                                "VALUE_NC_SUBJECT" => $row["nc_subject"],
                                "VALUE_NC_SERIAL" => $i,
                                "VALUE_STATUS_IMG" => ($row["nc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                "VALUE_STATUS_IMG_ALT" => ($row["nc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

            ));
        }
    }
    //最新消息分類--表單
    function news_cate_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        if($this->seo){
            $tpl->newBlock("SEO_EDIT_ZONE");
        }
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "NOW_NC_ID"  => 0,
                                  "VALUE_NC_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_news_cate","nc","","",0),
                                  "STR_NC_STATUS_CK1" => "checked",
                                  "STR_NC_STATUS_CK0" => "",
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
        if($action_mode=="mod" && !empty($_REQUEST["nc_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_news_cate where nc_id='".$_REQUEST["nc_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("NOW_NC_ID"  => $row["nc_id"],
                                          "VALUE_NC_ID"  => $row["nc_id"],
                                          "VALUE_NC_STATUS"  => $row["nc_status"],
                                          "VALUE_NC_SORT"  => $row["nc_sort"],
                                          "VALUE_NC_SUBJECT" => $row["nc_subject"],
                                          "STR_NC_STATUS_CK1" => ($row["nc_status"])?"checked":"",
                                          "STR_NC_STATUS_CK0" => ($row["nc_status"])?"":"checked",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
                if($this->seo){
                    $tpl->assignGlobal( array("VALUE_NC_SEO_TITLE" => $row["nc_seo_title"],
                                              "VALUE_NC_SEO_KEYWORD" => $row["nc_seo_keyword"],
                                              "VALUE_NC_SEO_DESCRIPTION" => $row["nc_seo_description"],
                                              "VALUE_NC_SEO_FILENAME" => $row["nc_seo_filename"],
                                              "VALUE_NC_SEO_H1" => $row["nc_seo_h1"],
                                              "VALUE_NC_SEO_SHORT_DESC" => $row["nc_seo_short_desc"],
                    ));
                }
            }else{
                header("location : news.php?func=nc_list");
            }
        }
    }
    //最新消息分類--資料更新
    function news_cate_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($this->seo){
            $add_field_str="nc_seo_title,
                            nc_seo_keyword,
                            nc_seo_description,
                            nc_seo_filename,
                            nc_seo_h1,
                            nc_seo_short_desc,";
            $add_value_str="'".htmlspecialchars($_REQUEST["nc_seo_title"])."',
                            '".htmlspecialchars($_REQUEST["nc_seo_keyword"])."',
                            '".htmlspecialchars($_REQUEST["nc_seo_description"])."',
                            '".htmlspecialchars($_REQUEST["nc_seo_filename"])."',
                            '".htmlspecialchars($_REQUEST["nc_seo_h1"])."',
                            '".htmlspecialchars($_REQUEST["nc_seo_short_desc"])."',";
            $update_str="nc_seo_title='".htmlspecialchars($_REQUEST["nc_seo_title"])."',
                         nc_seo_keyword='".htmlspecialchars($_REQUEST["nc_seo_keyword"])."',
                         nc_seo_description='".htmlspecialchars($_REQUEST["nc_seo_description"])."',
                         nc_seo_filename='".htmlspecialchars($_REQUEST["nc_seo_filename"])."',
                         nc_seo_h1='".htmlspecialchars($_REQUEST["nc_seo_h1"])."',
                         nc_seo_short_desc='".htmlspecialchars($_REQUEST["nc_seo_short_desc"])."',";
        }
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_news_cate (
                        nc_status,
                        nc_sort,
                        ".$add_field_str."
                        nc_subject
                    ) values (
                        ".$_REQUEST["nc_status"].",
                        '".$_REQUEST["nc_sort"]."',
                        ".$add_value_str."
                        '".htmlspecialchars($_REQUEST["nc_subject"])."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_news_cate set
                        nc_status=".$_REQUEST["nc_status"].",
                        nc_sort='".$_REQUEST["nc_sort"]."',
                        ".$update_str."
                        nc_subject='".htmlspecialchars($_REQUEST["nc_subject"])."'
                    where nc_id='".$_REQUEST["nc_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."news.php?func=nc_list&nc_id=".$_REQUEST["nc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //最新消息分類--刪除
    function news_cate_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["nc_id"]){
            $nc_id=array(0=>$_REQUEST["nc_id"]);
        }else{
            $nc_id=$_REQUEST["id"];
        }
        if(!empty($nc_id)){
            $nc_id_str = implode(",",$nc_id);
            //清空分類底下的最新消息
            $sql="delete from ".$cms_cfg['tb_prefix']."_news where nc_id in (".$nc_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                //刪除分類
                $sql="delete from ".$cms_cfg['tb_prefix']."_news_cate where nc_id in (".$nc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."news.php?func=nc_list&nc_id=".$_REQUEST["nc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//最新消息--列表================================================================
    function news_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_news_cate where nc_id > '0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        //沒有分類先建立分類
        if($rsnum < 1){
            $tpl->assignGlobal( "MSG_CREATE_CATE_FIRST" , $TPLMSG["CREATE_CATE_FIRST"]);
            $goto_url=$cms_cfg["manage_url"]."news.php?func=nc_add";
            $this->goto_target_page($goto_url);
        }else{
            //最新消息分類
            $i=0;
            $tpl->assignGlobal("TAG_NOW_CATE" ,$TPLMSG["NO_CATE"]);
            while($row = $db->fetch_array($selectrs,1)){
                $i++;
                $tpl->newBlock( "NEWS_CATE_LIST" );
                $tpl->assign( array( "VALUE_NC_SUBJECT"  => $row["nc_subject"],
                                     "VALUE_NC_ID" => $row["nc_id"],
                                     "VALUE_NC_SERIAL" => $i,
                                     "VALUE_STATUS_IMG" => ($row["nc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                     "VALUE_STATUS_IMG_ALT" => ($row["nc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
                ));
                if($i%4==0){
                    $tpl->assign("TAG_NEWS_CATE_TRTD","</tr><tr>");
                }
                if($row["nc_id"]==$_REQUEST["nc_id"]){
                    $tpl->assignGlobal("TAG_NOW_CATE",$row["nc_subject"]);
                }
            }
            //最新消息列表
            $sql="select n.*,nc.nc_subject from ".$cms_cfg['tb_prefix']."_news as n left join ".$cms_cfg['tb_prefix']."_news_cate as nc on n.nc_id=nc.nc_id where n.n_id > '0'";
            //附加條件
            $and_str="";
            if(!empty($_REQUEST["nc_id"])){
                $and_str .= " and n.nc_id = '".$_REQUEST["nc_id"]."'";
            }
            if($_REQUEST["st"]=="all"){
                $and_str .= " and (n.n_subject like '%".$_REQUEST["sk"]."%' or n.n_content like '%".$_REQUEST["sk"]."%')";
            }
            if($_REQUEST["st"]=="n_subject"){
                $and_str .= " and n.n_subject like '%".$_REQUEST["sk"]."%'";
            }
            if($_REQUEST["st"]=="n_content"){
                $and_str .= " and n.n_content like '%".$_REQUEST["sk"]."%'";
            }
            $sql .= $and_str." order by nc.nc_sort ".$cms_cfg['sort_pos'].",n.n_sort ".$cms_cfg['sort_pos'].",n.n_modifydate desc ";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="news.php?func=n_list&nc_id=".$_REQUEST["nc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
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
                case "n_subject" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK1", "selected");
                    break;
                case "n_content" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK2", "selected");
                    break;
            }
            $tpl->assignGlobal( "VALUE_NOW_NC_ID" , $_REQUEST["nc_id"]);
            $i=$page["start_serial"];
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "NEWS_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow'");
                }
                $tpl->assign( array("VALUE_NC_ID"  => $row["nc_id"],
                                    "VALUE_N_ID"  => $row["n_id"],
                                    "VALUE_N_SORT"  => $row["n_sort"],
                                    "VALUE_N_SUBJECT" => $row["n_subject"],
                                    "VALUE_N_SHOWDATE" => $row["n_showdate"],
                                    "VALUE_N_STARTDATE" => $row["n_startdate"],
                                    "VALUE_N_SERIAL" => $i,
                                    "VALUE_NC_SUBJECT"  => $row["nc_subject"],
                                    "VALUE_STATUS_IMG" => ($row["n_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                    "VALUE_STATUS_IMG_ALT" => ($row["n_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

                ));
                //判斷刊登狀態
                if($row["n_status"]==0 ||($row["n_status"]==2 && $row["n_enddate"] < date("Y-m-d"))){
                    $tpl->assign("VALUE_N_PUBLISH_STATUS","已過期");
                }else{
                    $tpl->assign("VALUE_N_PUBLISH_STATUS","刊登中");
                }
            }
        }
    }
//最新消息--表單================================================================
    function news_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        if($this->seo){
            $tpl->newBlock("SEO_EDIT_ZONE");
        }
        //欄位名稱
        $cate=(trim($_REQUEST["nc_id"])!="")?1:0;
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "VALUE_N_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_news","n","nc_id",$_REQUEST["nc_id"],$cate),
                                  "NOW_N_ID" => 0,
                                  "STR_N_STATUS_CK2" => "",
                                  "STR_N_STATUS_CK1" => "checked",
                                  "STR_N_STATUS_CK0" => "",
                                  "STR_N_CONTENT_TYPE_CK1" => "checked",
                                  "STR_N_CONTENT_TYPE_CK2" => "",
                                  "STR_N_CONTENT_TYPE_DISPLAY" => "",
                                  "STR_N_CONTENT_TYPE_DISPLAY2" => "none",
                                  "STR_N_HOT_CK1" => "",
                                  "STR_N_HOT_CK0" => "checked",
                                  "STR_N_POP_CK1" => "",
                                  "STR_N_POP_CK0" => "checked",
                                  "VALUE_PIC_PREVIEW" => $cms_cfg['default_preview_pic'],
                                  "VALUE_ACTION_MODE" => $action_mode,
                                  "VALUE_N_SHOWDATE" => date("Y-m-d"),
        ));
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        //如果有開啟上傳附檔
        if($cms_cfg['ws_module']['ws_news_upfiles']){
            $tpl->newBlock("NEWS_UPFILES_BLOCK");
        }        
        //如果為修改模式,帶入資料庫資料
        if($action_mode=="mod" && !empty($_REQUEST["n_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_news where n_id='".$_REQUEST["n_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("NOW_N_ID"  => $row["n_id"],
                                          "VALUE_N_ID"  => $row["n_id"],
                                          "VALUE_N_STATUS"  => $row["n_status"],
                                          "VALUE_N_SORT"  => $row["n_sort"],
                                          "VALUE_N_SUBJECT" => $row["n_subject"],
                                          "VALUE_N_SHORT"  => $row["n_short"],
                                          "VALUE_N_URL" => $row["n_url"],
                                          "VALUE_N_STARTDATE" => (trim($row["n_startdate"])=="" || trim($row["n_startdate"])=="0000-00-00")?date("Y-m-d"):$row["n_startdate"],
                                          "VALUE_N_ENDDATE" => (trim($row["n_enddate"])=="" || trim($row["n_enddate"])=="0000-00-00")?date("Y-m-d"):$row["n_enddate"],
                                          "VALUE_N_SHOWDATE" => (trim($row["n_showdate"])=="" || trim($row["n_showdate"])=="0000-00-00")?date("Y-m-d"):$row["n_showdate"],
                                          "STR_N_STATUS_CK2" => ($row["n_status"]==2)?"checked":"",
                                          "STR_N_STATUS_CK1" => ($row["n_status"]==1)?"checked":"",
                                          "STR_N_STATUS_CK0" => ($row["n_status"]==0)?"checked":"",
                                          "STR_N_CONTENT_TYPE_CK1" => ($row["n_content_type"]==1)?"checked":"",
                                          "STR_N_CONTENT_TYPE_CK2" => ($row["n_content_type"]==2)?"checked":"",
                                          "STR_N_CONTENT_TYPE_DISPLAY1" => ($row["n_content_type"]==1)?"":"none",
                                          "STR_N_CONTENT_TYPE_DISPLAY2" => ($row["n_content_type"]==2)?"":"none",
                                          "STR_N_HOT_CK1" => ($row["n_hot"]==1)?"checked":"",
                                          "STR_N_HOT_CK0" => ($row["n_hot"]==0)?"checked":"",
                                          "STR_N_POP_CK1" => ($row["n_pop"]==1)?"checked":"",
                                          "STR_N_POP_CK0" => ($row["n_pop"]==0)?"checked":"",
                                          "VALUE_N_S_PIC" => (trim($row["n_s_pic"])=="")?"":$row["n_s_pic"],
                                          "VALUE_PIC_PREVIEW" => (trim($row["n_s_pic"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["base_root"].$row["n_s_pic"],
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
                if($this->seo){
                    $tpl->assignGlobal( array("VALUE_N_SEO_TITLE" => $row["n_seo_title"],
                                              "VALUE_N_SEO_KEYWORD" => $row["n_seo_keyword"],
                                              "VALUE_N_SEO_DESCRIPTION" => $row["n_seo_description"],
                                              "VALUE_N_SEO_FILENAME" => $row["n_seo_filename"],
                                              "VALUE_N_SEO_H1" => $row["n_seo_h1"],
                    ));
                }
                //顯示上傳檔案按鈕
                $tpl->newBlock("UPLOAD_NEWS_FILE");
                $tpl->assignGlobal("TAG_STYLE_HIDDEN","display:none;");
            }else{
                header("location : news.php?func=n_list");
                die();
            }
        }
        //最新消息分類
        $sql="select * from ".$cms_cfg['tb_prefix']."_news_cate where nc_id > '0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        while($row1 = $db->fetch_array($selectrs,1)){
            $tpl->newBlock( "SELECT_OPTION_NEWS_CATE" );
            $tpl->assign( array( "OPTION_NEWS_CATE_NAME"  => $row1["nc_subject"],
                                 "OPTION_NEWS_CATE_VALUE" => $row1["nc_id"],
                                 "STR_NC_SEL"       => ($row1["nc_id"]==$row["nc_id"] || $row1["nc_id"]==$_REQUEST["nc_id"])?"selected":""
            ));
        }
        if($cms_cfg["ws_module"]["ws_wysiwyg"]=="tinymce"){
            $tpl->newBlock("TINYMCE_JS");
            $tpl->newBlock("WYSIWYG_TINYMCE1");
            $tpl->assign( "VALUE_N_CONTENT" , $row["n_content"] );
        }
    }
//最新消息--資料更新================================================================
    function news_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        if($this->seo){
            $add_field_str="n_seo_title,
                            n_seo_keyword,
                            n_seo_description,
                            n_seo_filename,
                            n_seo_h1,";
            $add_value_str="'".htmlspecialchars($_REQUEST["n_seo_title"])."',
                            '".htmlspecialchars($_REQUEST["n_seo_keyword"])."',
                            '".htmlspecialchars($_REQUEST["n_seo_description"])."',
                            '".htmlspecialchars($_REQUEST["n_seo_filename"])."',
                            '".htmlspecialchars($_REQUEST["n_seo_h1"])."',";
            $update_str="n_seo_title='".htmlspecialchars($_REQUEST["n_seo_title"])."',
                         n_seo_keyword='".htmlspecialchars($_REQUEST["n_seo_keyword"])."',
                         n_seo_description='".htmlspecialchars($_REQUEST["n_seo_description"])."',
                         n_seo_filename='".htmlspecialchars($_REQUEST["n_seo_filename"])."',
                         n_seo_h1='".htmlspecialchars($_REQUEST["n_seo_h1"])."',";
        }
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_news (
                        nc_id,
                        n_status,
                        n_sort,
                        n_hot,
                        n_pop,
                        n_subject,
                        n_short,
                        n_content_type,
                        n_content,
                        n_url,
                        n_s_pic,
                        n_modifydate,
                        n_startdate,
                        ".$add_field_str."
                        n_enddate,
                        n_showdate
                    ) values (
                        '".$_REQUEST["nc_id"]."',
                        '".$_REQUEST["n_status"]."',
                        '".$_REQUEST["n_sort"]."',
                        '".$_REQUEST["n_hot"]."',
                        '".$_REQUEST["n_pop"]."',
                        '".htmlspecialchars($_REQUEST["n_subject"])."',
                        '".$_REQUEST["n_short"]."',
                        '".$_REQUEST["n_content_type"]."',
                        '".$main->content_file_str_replace($_REQUEST["n_content"])."',
                        '".$_REQUEST["n_url"]."',
                        '".$main->file_str_replace($_REQUEST["n_s_pic"])."',
                        '".date("Y-m-d H:i:s")."',
                        '".$_REQUEST["n_startdate"]."',
                        ".$add_value_str."
                        '".$_REQUEST["n_enddate"]."',
                        '".$_REQUEST["n_showdate"]."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_news set
                        nc_id='".$_REQUEST["nc_id"]."',
                        n_status='".$_REQUEST["n_status"]."',
                        n_sort='".$_REQUEST["n_sort"]."',
                        n_hot='".$_REQUEST["n_hot"]."',
                        n_pop='".$_REQUEST["n_pop"]."',
                        n_subject='".htmlspecialchars($_REQUEST["n_subject"])."',
                        n_short='".$_REQUEST["n_short"]."',
                        n_content_type='".$_REQUEST["n_content_type"]."',
                        n_content='".$main->content_file_str_replace($_REQUEST["n_content"])."',
                        n_url='".$_REQUEST["n_url"]."',
                        n_s_pic='".$main->file_str_replace($_REQUEST["n_s_pic"])."',
                        n_modifydate='".date("Y-m-d H:i:s")."',
                        n_startdate='".$_REQUEST["n_startdate"]."',
                        ".$update_str."
                        n_enddate='".$_REQUEST["n_enddate"]."',
                        n_showdate='".$_REQUEST["n_showdate"]."'
                    where n_id='".$_REQUEST["n_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."news.php?func=n_list&nc_id=".$_REQUEST["nc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//最新消息--刪除--資料刪除可多筆處理================================================================
    function news_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["n_id"]){
            $n_id=array(0=>$_REQUEST["n_id"]);
        }else{
            $n_id=$_REQUEST["id"];
        }
        if(!empty($n_id)){
            $n_id_str = implode(",",$n_id);
            //刪除勾選的最新消息
            $sql="delete from ".$cms_cfg['tb_prefix']."_news where n_id in (".$n_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."news.php?func=n_list&nc_id=".$_REQUEST["nc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //最新消息分類更改狀態
        if($ws_table=="nc"){
            if($_REQUEST["nc_id"]){
                $nc_id=array(0=>$_REQUEST["nc_id"]);
            }else{
                $nc_id=$_REQUEST["id"];
            }
            if(!empty($nc_id)){
                $nc_id_str = implode(",",$nc_id);
                //更改分類底下的最新消息狀態
                $sql="update ".$cms_cfg['tb_prefix']."_news set n_status=".$value." where nc_id in (".$nc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    //狀態分類狀態
                    $sql="update ".$cms_cfg['tb_prefix']."_news_cate set nc_status=".$value." where nc_id in (".$nc_id_str.")";
                    $rs = $db->query($sql);
                    $db_msg = $db->report();
                    if ( $db_msg == "" ) {
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                        $goto_url=$cms_cfg["manage_url"]."news.php?func=nc_list&nc_id=".$_REQUEST["nc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                        $this->goto_target_page($goto_url);
                    }else{
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                    }
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //最新消息更改狀態
        if($ws_table=="n"){
            if($_REQUEST["n_id"]){
                $n_id=array(0=>$_REQUEST["n_id"]);
            }else{
                $n_id=$_REQUEST["id"];
            }
            if(!empty($n_id)){
                $n_id_str = implode(",",$n_id);
                //刪除勾選的最新消息
                $sql="update ".$cms_cfg['tb_prefix']."_news set n_status=".$value." where n_id in (".$n_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."news.php?func=n_list&nc_id=".$_REQUEST["nc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //最新消息分類更改排序值
        if(!empty($_REQUEST["sort_value"]) && !empty($ws_table)){
            if($ws_table=="nc"){
                $table_name=$cms_cfg['tb_prefix']."_news_cate";
            }
            if($ws_table=="n"){
                $table_name=$cms_cfg['tb_prefix']."_news";
            }
            foreach($_REQUEST["id"] as $key => $value){
                $sql="update ".$table_name." set ".$ws_table."_sort=".$_REQUEST["sort_value"][$value]." where ".$ws_table."_id='".$value."'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
            }
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."news.php?func=".$ws_table."_list&nc_id=".$_REQUEST["nc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //複製單筆資料
    function copy_data($ws_table){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //最新消息分類複製
        if($ws_table=="nc"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_news_cate where nc_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_news_cate (
                        nc_status,
                        nc_sort,
                        nc_subject
                    ) values (
                        '".$row["nc_status"]."',
                        '".$main->get_max_sort_value($cms_cfg['tb_prefix']."_news_cate","nc")."',
                        '".addslashes($row["nc_subject"])." (copy)'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."news.php?func=nc_list&nc_id=".$_REQUEST["nc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //最新消息複製
        if($ws_table=="n"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_news where n_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_news (
                        nc_id,
                        n_status,
                        n_sort,
                        n_hot,
                        n_pop,
                        n_subject,
                        n_short,
                        n_content_type,
                        n_content,
                        n_url,
                        n_s_pic,
                        n_modifydate,
                        n_startdate,
                        n_enddate,
                        n_showdate
                    ) values (
                        '".$row["nc_id"]."',
                        '".$row["n_status"]."',
                        '".$main->get_max_sort_value($cms_cfg['tb_prefix']."_news","n")."',
                        '".$row["n_hot"]."',
                        '".$row["n_pop"]."',
                        '".addslashes($row["n_subject"])." (copy)',
                        '".addslashes($row["n_short"])."',
                        '".$row["n_content_type"]."',
                        '".addslashes($row["n_content"])."',
                        '".$row["n_url"]."',
                        '".$row["n_s_pic"]."',
                        '".$row["n_modifydate"]."',
                        '".$row["n_startdate"]."',
                        '".$row["n_enddate"]."',
                        '".$row["n_showdate"]."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."news.php?func=n_list&nc_id=".$_REQUEST["nc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
                if($_REQUEST["ws_table"]=="nc"){
                    $this->news_cate_del();
                }
                if($_REQUEST["ws_table"]=="n"){
                    $this->news_del();
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
    function news_files(){
        global $tpl,$db,$cms_cfg,$main;
        if($_POST && $_GET['act']=="save"){
            //新檔案
            if($_POST['new_n_file']){
                $sql = "insert into ".$cms_cfg['tb_prefix']."_news_files(n_id,n_file,nf_desc)values('".$db->quote($_POST['n_id'])."','".$db->quote($main->file_str_replace($_POST['new_n_file']))."','".$db->quote($_POST['new_nf_desc'])."')";
                $res0 = $db->query($sql,true);
}
            //修改檔案
            if($_POST['n_file']){
                foreach($_POST['n_file'] as $nfid => $file){
                    $sql = "update ".$cms_cfg['tb_prefix']."_news_files set n_file='".$db->quote($main->file_str_replace($file))."',nf_desc='".$db->quote($_POST['nf_desc'][$nfid])."' where id='".$db->quote($nfid)."'";
                    $db->query($sql,true);
                }
            }
            $this->ws_tpl_type=0;
            header("location: news.php?func=n_file&n_id=".$_POST['n_id']);
            die();
        }else{
            $template = "templates/ws-manage-news-file-tpl.html";
            $tpl = new TemplatePower($template);
            $tpl->prepare();
            $tpl->newBlock("JS_MAIN");
            $tpl->newBlock("JS_PREVIEWS_PIC");
            $tpl->newBlock("JS_FORMVALID");
            $tpl->newBlock("JS_CALENDAR");
            $tpl->newBlock("JS_TINYMCE");        
            $tpl->assignGlobal("TAG_ROOT_PATH" , $cms_cfg['base_root']);
            $tpl->assignGlobal("TAG_FILE_ROOT" , $cms_cfg['file_root']);        
            $tpl->assign("_ROOT.VALUE_N_ID",$_GET['n_id']);
            $this->ws_tpl_type=1;
            //顯示原有檔案列表
            $sql = "select * from ".$cms_cfg['tb_prefix']."_news_files where n_id='".$_GET['n_id']."'";
            $res = $db->query($sql,true);
            while($row = $db->fetch_array($res,1)){
                $tpl->newBlock("NEWS_FILES_LIST");
                $tpl->assign(array(
                    "VALUE_N_FILE" => $row['n_file'],
                    "VALUE_NF_DESC" => $row['nf_desc'],
                    "VALUE_NF_ID"  => $row['id'],
                ));
            }
            //新增檔案
            filefield::setValues(array(
                "TAG_FILE_FIELD_NAME"    => "new_n_file",
                "TAG_FILE_FIELD_ID"      => "new_n_file",
            ));
            $tpl->assign("_ROOT.TAG_FILE_FIELD",filefield::get_html());        
        }
    }
    function ajax_get_news_file(){
        global $db,$cms_cfg;
        if($_GET['nf_id']){
            $sql = "select * from ".$cms_cfg['tb_prefix']."_news_files where id='".$_GET['nf_id']."'";
            $file = $db->query_firstRow($sql);
            if($file){
                //新增檔案
                filefield::setValues(array(
                    "TAG_FILE_FIELD_NAME"    => "n_file[".$file['id']."]",
                    "TAG_FILE_FIELD_ID"      => "n_file_".$file['id'],
                    "TAG_FILE_FIELD_VALUE"   => $file['n_file'],
                ));
                echo filefield::get_html();  
                echo "<div>說明:<input type=\"text\" name=\"nf_desc[".$file['id']."]\" value=\"".$file['nf_desc']."\"/></div>";
            }
        }
    }
    function ajax_del_news_file(){
        global $db,$cms_cfg;
        if($_GET['nf_id']){
            $sql = "delete from ".$cms_cfg['tb_prefix']."_news_files where id='".$_GET['nf_id']."'";
            $res = $db->query($sql,true);
            if($db->report()==""){
                echo 1;
            }
        }
    }
}
//ob_end_flush();
?>
