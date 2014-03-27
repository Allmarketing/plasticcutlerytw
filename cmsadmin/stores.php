<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]) ){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$stores = new STORES;
class STORES{
    function STORES(){
        global $db,$cms_cfg,$tpl;
        $this->seo=($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_seo"] && $cms_cfg["ws_module"]["ws_seo"])?1:0;
        switch($_REQUEST["func"]){
            case "sdc_list"://門市管理分類列表
                $this->current_class="NC";
                $this->ws_tpl_file = "templates/ws-manage-stores-cate-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->stores_cate_list();
                $this->ws_tpl_type=1;
                break;
            case "sdc_add"://門市管理分類新增
                $this->current_class="NC";
                $this->ws_tpl_file = "templates/ws-manage-stores-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->stores_cate_form("add");
                $this->ws_tpl_type=1;
                break;
            case "sdc_mod"://門市管理分類修改
                $this->current_class="NC";
                $this->ws_tpl_file = "templates/ws-manage-stores-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->stores_cate_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "sdc_replace"://門市管理分類更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->stores_cate_replace();
                $this->ws_tpl_type=1;
                break;
            case "sdc_del"://門市管理分類刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->stores_cate_del();
                $this->ws_tpl_type=1;
                break;
            case "sd_list"://門市管理列表
                $this->current_class="N";
                $this->ws_tpl_file = "templates/ws-manage-stores-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->stores_list();
                $this->ws_tpl_type=1;
                break;
            case "sd_add"://門市管理新增
                $this->current_class="N";
                $this->ws_tpl_file = "templates/ws-manage-stores-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_CALENDAR");
                $tpl->newBlock("JS_TINYMCE");
                $this->stores_form("add");
                $this->ws_tpl_type=1;
                break;
            case "sd_mod"://門市管理修改
                $this->current_class="N";
                $this->ws_tpl_file = "templates/ws-manage-stores-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_CALENDAR");
                $tpl->newBlock("JS_TINYMCE");
                $this->stores_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "sd_replace"://門市管理更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->stores_replace();
                $this->ws_tpl_type=1;
                break;
            case "sd_del"://門市管理刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->stores_del();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //門市管理列表
                $this->current_class="N";
                $this->ws_tpl_file = "templates/ws-manage-stores-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->stores_list();
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
        $tpl->assignGlobal("CSS_BLOCK_STORES","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }

    //門市管理分類--列表
    function stores_cate_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_stores_cate where sdc_id > '0'";
        $and_str = "";
        if(!empty($_REQUEST["sk"])){
            $and_str = " and sdc_subject like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by sdc_sort ".$cms_cfg['sort_pos']." ";
        //取得總筆數
        $total_records=$main->count_total_records($sql);
        //取得分頁連結
        $func_str="stores.php?func=sdc_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
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
            $tpl->newBlock( "STORES_CATE_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_SDC_ID"  => $row["sdc_id"],
                                "VALUE_SDC_STATUS"  => $row["sdc_status"],
                                "VALUE_SDC_SORT"  => $row["sdc_sort"],
                                "VALUE_SDC_SUBJECT" => $row["sdc_subject"],
                                "VALUE_SDC_SERIAL" => $i,
                                "VALUE_STATUS_IMG" => ($row["sdc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                "VALUE_STATUS_IMG_ALT" => ($row["sdc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

            ));
        }
    }
    //門市管理分類--表單
    function stores_cate_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        if($this->seo){
            $tpl->newBlock("SEO_EDIT_ZONE");
        }
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "NOW_SDC_ID"  => 0,
                                  "VALUE_SDC_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_stores_cate","sdc","","",0),
                                  "STR_SDC_STATUS_CK1" => "checked",
                                  "STR_SDC_STATUS_CK0" => "",
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
        if($action_mode=="mod" && !empty($_REQUEST["sdc_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_stores_cate where sdc_id='".$_REQUEST["sdc_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("NOW_SDC_ID"  => $row["sdc_id"],
                                          "VALUE_SDC_ID"  => $row["sdc_id"],
                                          "VALUE_SDC_STATUS"  => $row["sdc_status"],
                                          "VALUE_SDC_SORT"  => $row["sdc_sort"],
                                          "VALUE_SDC_SUBJECT" => $row["sdc_subject"],
                                          "STR_SDC_STATUS_CK1" => ($row["sdc_status"])?"checked":"",
                                          "STR_SDC_STATUS_CK0" => ($row["sdc_status"])?"":"checked",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
                if($this->seo){
                    $tpl->assignGlobal( array("VALUE_SDC_SEO_TITLE" => $row["sdc_seo_title"],
                                              "VALUE_SDC_SEO_KEYWORD" => $row["sdc_seo_keyword"],
                                              "VALUE_SDC_SEO_DESCRIPTION" => $row["sdc_seo_description"],
                                              "VALUE_SDC_SEO_FILENAME" => $row["sdc_seo_filename"],
                                              "VALUE_SDC_SEO_H1" => $row["sdc_seo_h1"],
                                              "VALUE_SDC_SEO_SHORT_DESC" => $row["sdc_seo_short_desc"],
                    ));
                }
            }else{
                header("location : stores.php?func=sdc_list");
            }
        }
    }
    //門市管理分類--資料更新
    function stores_cate_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($this->seo){
            $add_field_str="sdc_seo_title,
                            sdc_seo_keyword,
                            sdc_seo_description,
                            sdc_seo_filename,
                            sdc_seo_h1,
                            sdc_seo_short_desc,";
            $add_value_str="'".htmlspecialchars($_REQUEST["sdc_seo_title"])."',
                            '".htmlspecialchars($_REQUEST["sdc_seo_keyword"])."',
                            '".htmlspecialchars($_REQUEST["sdc_seo_description"])."',
                            '".htmlspecialchars($_REQUEST["sdc_seo_filename"])."',
                            '".htmlspecialchars($_REQUEST["sdc_seo_h1"])."',
                            '".htmlspecialchars($_REQUEST["sdc_seo_short_desc"])."',";
            $update_str="sdc_seo_title='".htmlspecialchars($_REQUEST["sdc_seo_title"])."',
                         sdc_seo_keyword='".htmlspecialchars($_REQUEST["sdc_seo_keyword"])."',
                         sdc_seo_description='".htmlspecialchars($_REQUEST["sdc_seo_description"])."',
                         sdc_seo_filename='".htmlspecialchars($_REQUEST["sdc_seo_filename"])."',
                         sdc_seo_h1='".htmlspecialchars($_REQUEST["sdc_seo_h1"])."',
                         sdc_seo_short_desc='".htmlspecialchars($_REQUEST["sdc_seo_short_desc"])."',";
        }
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_stores_cate (
                        sdc_status,
                        sdc_sort,
                        ".$add_field_str."
                        sdc_subject
                    ) values (
                        ".$_REQUEST["sdc_status"].",
                        '".$_REQUEST["sdc_sort"]."',
                        ".$add_value_str."
                        '".htmlspecialchars($_REQUEST["sdc_subject"])."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_stores_cate set
                        sdc_status=".$_REQUEST["sdc_status"].",
                        sdc_sort='".$_REQUEST["sdc_sort"]."',
                        ".$update_str."
                        sdc_subject='".htmlspecialchars($_REQUEST["sdc_subject"])."'
                    where sdc_id='".$_REQUEST["sdc_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."stores.php?func=sdc_list&sdc_id=".$_REQUEST["sdc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //門市管理分類--刪除
    function stores_cate_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["sdc_id"]){
            $sdc_id=array(0=>$_REQUEST["sdc_id"]);
        }else{
            $sdc_id=$_REQUEST["id"];
        }
        if(!empty($sdc_id)){
            $sdc_id_str = implode(",",$sdc_id);
            //清空分類底下的門市管理
            $sql="delete from ".$cms_cfg['tb_prefix']."_stores where sdc_id in (".$sdc_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                //刪除分類
                $sql="delete from ".$cms_cfg['tb_prefix']."_stores_cate where sdc_id in (".$sdc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."stores.php?func=sdc_list&sdc_id=".$_REQUEST["sdc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//門市管理--列表================================================================
    function stores_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        //門市管理類別
        $sd_type_arr = array(1=>"實體門市",2=>"網路門市");
        $i=0;
        $tpl->assignGlobal("TAG_NOW_CATE" ,$TPLMSG["NO_CATE"]);
        foreach($sd_type_arr as $sd_type=>$sd_type_name){
            $i++;
            $tpl->newBlock( "STORES_CATE_LIST" );
            $tpl->assign( array( "VALUE_SD_TYPE_NAME"  => $sd_type_name,
                                 "VALUE_SD_TYPE" => $sd_type,
            ));
            if($i%4==0){
                $tpl->assign("TAG_STORES_CATE_TRTD","</tr><tr>");
            }
            if($row["sdc_id"]==$_REQUEST["sdc_id"]){
                $tpl->assignGlobal("TAG_NOW_CATE",$row["sdc_subject"]);
            }
        }
        //門市管理列表
        $sql="select sd.*,sdc.sdc_subject from ".$cms_cfg['tb_prefix']."_stores as sd left join ".$cms_cfg['tb_prefix']."_stores_cate as sdc on sd.sdc_id=sdc.sdc_id where sd.sd_id > '0'";
        //附加條件
        $and_str="";
        if(!empty($_REQUEST["sd_type"])){
            $and_str .= " and sd.sd_type = '".$_REQUEST["sd_type"]."'";
        }
        if($_REQUEST["st"]=="all"){
            $and_str .= " and (sd.sd_subject like '%".$_REQUEST["sk"]."%' or sd.sd_content like '%".$_REQUEST["sk"]."%')";
        }
        if($_REQUEST["st"]=="sd_subject"){
            $and_str .= " and sd.sd_subject like '%".$_REQUEST["sk"]."%'";
        }
        if($_REQUEST["st"]=="sd_content"){
            $and_str .= " and sd.sd_content like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by sdc.sdc_sort ".$cms_cfg['sort_pos'].",sd.sd_sort ".$cms_cfg['sort_pos'].",sd.sd_modifydate desc ";
        //取得總筆數
        $selectrs = $db->query($sql);
        $total_records = $db->numRows($selectrs);
        //取得分頁連結
        $func_str="stores.php?func=sd_list&sd_type=".$_REQUEST["sd_type"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
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
            case "sd_subject" :
                $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK1", "selected");
                break;
            case "sd_content" :
                $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK2", "selected");
                break;
        }
        $tpl->assignGlobal( "VALUE_NOW_SD_TYPE" , $_GET["sd_type"]);
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "STORES_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_SDC_ID"  => $row["sdc_id"],
                                "VALUE_SD_ID"  => $row["sd_id"],
                                "VALUE_SD_SORT"  => $row["sd_sort"],
                                "VALUE_SD_NAME" => $row["sd_name"],
                                "VALUE_SD_SERIAL" => $i,
                                "VALUE_SD_TYPE"  => ($row["sd_type"]==1)?"實體門市":"網路門市",
                                "VALUE_STATUS_IMG" => ($row["sd_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                "VALUE_STATUS_IMG_ALT" => ($row["sd_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

            ));

        }
    }
//門市管理--表單================================================================
    function stores_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        if($this->seo){
            $tpl->newBlock("SEO_EDIT_ZONE");
        }
        //欄位名稱
        $cate=(trim($_REQUEST["sd_type"])!="")?1:0;
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "VALUE_SD_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_stores","sd","sd_type",$_REQUEST["sd_type"],$cate),
                                  "NOW_SD_ID" => 0,
                                  "STR_SD_STATUS_CK1" => "checked",
                                  "STR_SD_STATUS_CK0" => "",
                                  "STR_SD_TYPE_CK1" => (empty($_GET['sd_type']) || $_GET['sd_type']=='1')?"checked":"",
                                  "STR_SD_TYPE_CK2" => ($_GET['sd_type']=='2')?"checked":"",
                                  "STR_SD_TYPE_DISPLAY1" => (empty($_GET['sd_type']) || $_GET['sd_type']=='1')?"":"none",
                                  "STR_SD_TYPE_DISPLAY2" => ($_GET['sd_type']=='2')?"":"none",
                                  "VALUE_PIC_PREVIEW" => $cms_cfg['default_preview_pic'],
                                  "VALUE_ACTION_MODE" => $action_mode,
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
        if($action_mode=="mod" && !empty($_REQUEST["sd_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_stores where sd_id='".$_REQUEST["sd_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("NOW_SD_ID"  => $row["sd_id"],
                                          "VALUE_SD_ID"  => $row["sd_id"],
                                          "VALUE_SD_STATUS"  => $row["sd_status"],
                                          "VALUE_SD_SORT"  => $row["sd_sort"],
                                          "VALUE_SD_NAME" => $row["sd_name"],
                                          "VALUE_SD_URL" => $row["sd_url"],
                                          "VALUE_SD_GMURL" => $row["sd_gmurl"],
                                          "VALUE_SD_TYPE" => $row["sd_type"],
                                          "STR_SD_STATUS_CK1" => ($row["sd_status"]==1)?"checked":"",
                                          "STR_SD_STATUS_CK0" => ($row["sd_status"]==0)?"checked":"",
                                          "STR_SD_TYPE_CK1" => ($row["sd_type"]==1)?"checked":"",
                                          "STR_SD_TYPE_CK2" => ($row["sd_type"]==2)?"checked":"",
                                          "STR_SD_TYPE_DISPLAY1" => ($row["sd_type"]==1)?"":"none",
                                          "STR_SD_TYPE_DISPLAY2" => ($row["sd_type"]==2)?"":"none",
                                          "VALUE_SD_IMG" => (trim($row["sd_img"])=="")?"":$row["sd_img"],
                                          "VALUE_PIC_PREVIEW" => (trim($row["sd_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["base_root"].$row["sd_img"],
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
            }else{
                header("location : stores.php?func=sd_list");
            }
        }
        //門市管理分類
        $sql="select * from ".$cms_cfg['tb_prefix']."_stores_cate where sdc_id > '0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        while($row1 = $db->fetch_array($selectrs,1)){
            $tpl->newBlock( "SELECT_OPTION_STORES_CATE" );
            $tpl->assign( array( "OPTION_STORES_CATE_NAME"  => $row1["sdc_subject"],
                                 "OPTION_STORES_CATE_VALUE" => $row1["sdc_id"],
                                 "STR_SDC_SEL"       => ($row1["sdc_id"]==$row["sdc_id"] || $row1["sdc_id"]==$_REQUEST["sdc_id"])?"selected":""
            ));
        }
        if($cms_cfg["ws_module"]["ws_wysiwyg"]=="tinymce"){
            $tpl->newBlock("TINYMCE_JS");
            $tpl->newBlock("WYSIWYG_TINYMCE1");
            $tpl->assign( "VALUE_SD_DESC" , $row["sd_desc"] );
        }
    }
//門市管理--資料更新================================================================
    function stores_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_stores (
                        sdc_id,
                        sd_status,
                        sd_sort,
                        sd_name,
                        sd_type,
                        sd_desc,
                        sd_url,
                        sd_gmurl,
                        sd_img,
                        sd_modifydate
                    ) values (
                        '".$_REQUEST["sdc_id"]."',
                        '".$_REQUEST["sd_status"]."',
                        '".$_REQUEST["sd_sort"]."',
                        '".htmlspecialchars($_REQUEST["sd_name"])."',
                        '".$_REQUEST["sd_type"]."',
                        '".$_REQUEST["sd_desc"]."',
                        '".$_REQUEST["sd_url"]."',
                        '".$_REQUEST["sd_gmurl"]."',
                        '".$main->file_str_replace($_REQUEST["sd_img"])."',
                        '".date("Y-m-d H:i:s")."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_stores set
                        sdc_id='".$_REQUEST["sdc_id"]."',
                        sd_status='".$_REQUEST["sd_status"]."',
                        sd_sort='".$_REQUEST["sd_sort"]."',
                        sd_name='".htmlspecialchars($_REQUEST["sd_name"])."',
                        sd_type='".$_REQUEST["sd_type"]."',
                        sd_desc='".$_REQUEST["sd_desc"]."',
                        sd_url='".$_REQUEST["sd_url"]."',
                        sd_gmurl='".$_REQUEST["sd_gmurl"]."',
                        sd_img='".$main->file_str_replace($_REQUEST["sd_img"])."',
                        sd_modifydate='".date("Y-m-d H:i:s")."'
                    where sd_id='".$_REQUEST["sd_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql,true);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."stores.php?func=sd_list&sdc_id=".$_REQUEST["sdc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//門市管理--刪除--資料刪除可多筆處理================================================================
    function stores_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["sd_id"]){
            $sd_id=array(0=>$_REQUEST["sd_id"]);
        }else{
            $sd_id=$_REQUEST["id"];
        }
        if(!empty($sd_id)){
            $sd_id_str = implode(",",$sd_id);
            //刪除勾選的門市管理
            $sql="delete from ".$cms_cfg['tb_prefix']."_stores where sd_id in (".$sd_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."stores.php?func=sd_list&sdc_id=".$_REQUEST["sdc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //門市管理分類更改狀態
        if($ws_table=="sdc"){
            if($_REQUEST["sdc_id"]){
                $sdc_id=array(0=>$_REQUEST["sdc_id"]);
            }else{
                $sdc_id=$_REQUEST["id"];
            }
            if(!empty($sdc_id)){
                $sdc_id_str = implode(",",$sdc_id);
                //更改分類底下的門市管理狀態
                $sql="update ".$cms_cfg['tb_prefix']."_stores set sd_status=".$value." where sdc_id in (".$sdc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    //狀態分類狀態
                    $sql="update ".$cms_cfg['tb_prefix']."_stores_cate set sdc_status=".$value." where sdc_id in (".$sdc_id_str.")";
                    $rs = $db->query($sql);
                    $db_msg = $db->report();
                    if ( $db_msg == "" ) {
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                        $goto_url=$cms_cfg["manage_url"]."stores.php?func=sdc_list&sdc_id=".$_REQUEST["sdc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                        $this->goto_target_page($goto_url);
                    }else{
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                    }
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //門市管理更改狀態
        if($ws_table=="sd"){
            if($_REQUEST["sd_id"]){
                $sd_id=array(0=>$_REQUEST["sd_id"]);
            }else{
                $sd_id=$_REQUEST["id"];
            }
            if(!empty($sd_id)){
                $sd_id_str = implode(",",$sd_id);
                //刪除勾選的門市管理
                $sql="update ".$cms_cfg['tb_prefix']."_stores set sd_status=".$value." where sd_id in (".$sd_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."stores.php?func=sd_list&sdc_id=".$_REQUEST["sdc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //門市管理分類更改排序值
        if(!empty($_REQUEST["sort_value"]) && !empty($ws_table)){
            if($ws_table=="sdc"){
                $table_name=$cms_cfg['tb_prefix']."_stores_cate";
            }
            if($ws_table=="sd"){
                $table_name=$cms_cfg['tb_prefix']."_stores";
            }
            foreach($_REQUEST["id"] as $key => $value){
                $sql="update ".$table_name." set ".$ws_table."_sort=".$_REQUEST["sort_value"][$value]." where ".$ws_table."_id='".$value."'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
            }
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."stores.php?func=".$ws_table."_list&sdc_id=".$_REQUEST["sdc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //複製單筆資料
    function copy_data($ws_table){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //門市管理分類複製
        if($ws_table=="sdc"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_stores_cate where sdc_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_stores_cate (
                        sdc_status,
                        sdc_sort,
                        sdc_subject
                    ) values (
                        '".$row["sdc_status"]."',
                        '".$row["sdc_sort"]."',
                        '".addslashes($row["sdc_subject"])." (copy)'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."stores.php?func=sdc_list&sdc_id=".$_REQUEST["sdc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //門市管理複製
        if($ws_table=="sd"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_stores where sd_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_stores (
                        sdc_id,
                        sd_status,
                        sd_sort,
                        sd_hot,
                        sd_pop,
                        sd_subject,
                        sd_short,
                        sd_content_type,
                        sd_content,
                        sd_url,
                        sd_s_pic,
                        sd_modifydate,
                        sd_startdate,
                        sd_enddate,
                        sd_showdate
                    ) values (
                        '".$row["sdc_id"]."',
                        '".$row["sd_status"]."',
                        '".$row["sd_sort"]."',
                        '".$row["sd_hot"]."',
                        '".$row["sd_pop"]."',
                        '".addslashes($row["sd_subject"])." (copy)',
                        '".addslashes($row["sd_short"])."',
                        '".$row["sd_content_type"]."',
                        '".addslashes($row["sd_content"])."',
                        '".$row["sd_url"]."',
                        '".$row["sd_s_pic"]."',
                        '".$row["sd_modifydate"]."',
                        '".$row["sd_startdate"]."',
                        '".$row["sd_enddate"]."',
                        '".$row["sd_showdate"]."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."stores.php?func=sd_list&sdc_id=".$_REQUEST["sdc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
                if($_REQUEST["ws_table"]=="sdc"){
                    $this->stores_cate_del();
                }
                if($_REQUEST["ws_table"]=="sd"){
                    $this->stores_del();
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
