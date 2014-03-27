<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]) || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_gallery"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$news = new GALLERY;
class GALLERY{
    function GALLERY(){
        global $db,$cms_cfg,$tpl;
        switch($_REQUEST["func"]){
            case "gc_list"://Gallery分類列表
                $this->current_class="GC";
                $this->ws_tpl_file = "templates/ws-manage-gallery-cate-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->gallery_cate_list();
                $this->ws_tpl_type=1;
                break;
            case "gc_add"://Gallery分類新增
                $this->current_class="GC";
                $this->ws_tpl_file = "templates/ws-manage-gallery-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_TINYMCE");
                $this->gallery_cate_form("add");
                $this->ws_tpl_type=1;
                break;
            case "gc_mod"://Gallery分類修改
                $this->current_class="GC";
                $this->ws_tpl_file = "templates/ws-manage-gallery-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_TINYMCE");
                $this->gallery_cate_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "gc_replace"://Gallery分類更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->gallery_cate_replace();
                $this->ws_tpl_type=1;
                break;
            case "gc_del"://Gallery分類刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->gallery_cate_del();
                $this->ws_tpl_type=1;
                break;
            case "g_list"://Gallery列表
                $this->current_class="G";
                $this->ws_tpl_file = "templates/ws-manage-gallery-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->gallery_list();
                $this->ws_tpl_type=1;
                break;
            case "g_add"://Gallery新增
                $this->current_class="G";
                $this->ws_tpl_file = "templates/ws-manage-gallery-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_TABTITLE");
                $tpl->newBlock("JS_TINYMCE");
                $this->gallery_form("add");
                $this->ws_tpl_type=1;
                break;
            case "g_mod"://Gallery修改
                $this->current_class="G";
                $this->ws_tpl_file = "templates/ws-manage-gallery-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_TABTITLE");
                $tpl->newBlock("JS_TINYMCE");
                $this->gallery_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "g_replace"://Gallery更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->gallery_replace();
                $this->ws_tpl_type=1;
                break;
            case "g_del"://Gallery刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->gallery_del();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //Gallery列表
                $this->current_class="G";
                $this->ws_tpl_file = "templates/ws-manage-gallery-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->gallery_list();
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
        $tpl->assignGlobal("CSS_BLOCK_GALLERY","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }

    //Gallery分類--列表
    function gallery_cate_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $sql="select * from ".$cms_cfg['tb_prefix']."_gallery_cate where gc_id > '0'";
        $and_str = "";
        if(!empty($_REQUEST["sk"])){
            $and_str = " and gc_subject like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by gc_sort ".$cms_cfg['sort_pos']." ";
        //取得總筆數
        $total_records=$main->count_total_records($sql);
        //取得分頁連結
        $func_str="gallery.php?func=gc_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
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
            $tpl->newBlock( "GALLERY_CATE_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_GC_ID"  => $row["gc_id"],
                                "VALUE_GC_STATUS"  => $row["gc_status"],
                                "VALUE_GC_SORT"  => $row["gc_sort"],
                                "VALUE_GC_SUBJECT" => $row["gc_subject"],
                                "VALUE_GC_SERIAL" => $i,
                                "VALUE_STATUS_IMG" => ($row["gc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                "VALUE_STATUS_IMG_ALT" => ($row["gc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

            ));
        }
    }
    //Gallery分類--表單
    function gallery_cate_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "VALUE_GC_SORT"  => 1,
                                  "STR_GC_STATUS_CK1" => "checked",
                                  "STR_GC_STATUS_CK0" => "",
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
        if($action_mode=="mod" && !empty($_REQUEST["gc_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_gallery_cate where gc_id='".$_REQUEST["gc_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("VALUE_GC_ID"  => $row["gc_id"],
                                          "VALUE_GC_STATUS"  => $row["gc_status"],
                                          "VALUE_GC_SORT"  => $row["gc_sort"],
                                          "VALUE_GC_SUBJECT" => $row["gc_subject"],
                                          "VALUE_GC_DESC" => $row["gc_desc"],
                                          "VALUE_GC_DIR" => $row["gc_dir"],
                                          "STR_GC_STATUS_CK1" => ($row["gc_status"])?"checked":"",
                                          "STR_GC_STATUS_CK0" => ($row["gc_status"])?"":"checked",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
            }else{
                header("location : gallery.php?func=gc_list");
                die();
            }
        }
        if($cms_cfg['ws_module']['ws_gallery_scan_dir']){
            $tpl->newBlock("SETTING_DIR_ZONE");
    }
    }
    //Gallery分類--資料更新
    function gallery_cate_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_gallery_cate (
                        gc_status,
                        gc_sort,
                        gc_subject,
                        gc_desc,
                        gc_dir
                    ) values (
                        ".$_REQUEST["gc_status"].",
                        '".$_REQUEST["gc_sort"]."',
                        '".htmlspecialchars($_REQUEST["gc_subject"])."',
                        '".$db->quote($main->content_file_str_replace($_REQUEST["gc_desc"]))."',
                        '".$main->file_str_replace($_REQUEST["gc_dir"])."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_gallery_cate set
                        gc_status=".$_REQUEST["gc_status"].",
                        gc_sort='".$_REQUEST["gc_sort"]."',
                        gc_subject='".htmlspecialchars($_REQUEST["gc_subject"])."',
                        gc_desc='".$db->quote($main->content_file_str_replace($_REQUEST["gc_desc"]))."',
                        gc_dir='".$main->file_str_replace($_REQUEST["gc_dir"])."'
                    where gc_id='".$_REQUEST["gc_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."gallery.php?func=gc_list&gc_id=".$_REQUEST["gc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //Gallery分類--刪除
    function gallery_cate_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["gc_id"]){
            $gc_id=array(0=>$_REQUEST["gc_id"]);
        }else{
            $gc_id=$_REQUEST["id"];
        }
        if(!empty($gc_id)){
            $gc_id_str = implode(",",$gc_id);
            //清空分類底下的Gallery
            $sql="delete from ".$cms_cfg['tb_prefix']."_gallery where gc_id in (".$gc_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                //刪除分類
                $sql="delete from ".$cms_cfg['tb_prefix']."_gallery_cate where gc_id in (".$gc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."gallery.php?func=gc_list&gc_id=".$_REQUEST["gc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//Gallery--列表================================================================
    function gallery_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $sql="select * from ".$cms_cfg['tb_prefix']."_gallery_cate where gc_id > '0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        //沒有分類先建立分類
        if($rsnum < 1){
            $tpl->assignGlobal( "MSG_CREATE_CATE_FIRST" , $TPLMSG["CREATE_CATE_FIRST"]);
            $goto_url=$cms_cfg["manage_url"]."gallery.php?func=gc_add";
            $this->goto_target_page($goto_url);
        }else{
            //Gallery分類
            $i=0;
            $tpl->assignGlobal("TAG_NOW_CATE" ,$TPLMSG["NO_CATE"]);
            while($row = $db->fetch_array($selectrs,1)){
                $i++;
                $tpl->newBlock( "GALLERY_CATE_LIST" );
                $tpl->assign( array( "VALUE_GC_SUBJECT"  => $row["gc_subject"],
                                     "VALUE_GC_ID" => $row["gc_id"],
                                     "VALUE_GC_SERIAL" => $i,
                                     "VALUE_STATUS_IMG" => ($row["gc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                     "VALUE_STATUS_IMG_ALT" => ($row["gc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
                ));
                if($i%4==0){
                    $tpl->assign("TAG_GALLERY_CATE_TRTD","</tr><tr>");
                }
                if($row["gc_id"]==$_REQUEST["gc_id"]){
                    $tpl->assignGlobal("TAG_NOW_CATE",$row["gc_subject"]);
                }
            }
            //Gallery列表
            $sql="select g.*,gc.gc_subject from ".$cms_cfg['tb_prefix']."_gallery as g left join ".$cms_cfg['tb_prefix']."_gallery_cate as gc on g.gc_id=gc.gc_id where g.g_id > '0'";
            //附加條件
            $and_str="";
            if(!empty($_REQUEST["gc_id"])){
                $and_str .= " and g.gc_id = '".$_REQUEST["gc_id"]."'";
            }
            if($_REQUEST["st"]=="all"){
                $and_str .= " and (g.g_subject like '%".$_REQUEST["sk"]."%' or g.g_content like '%".$_REQUEST["sk"]."%')";
            }
            if($_REQUEST["st"]=="g_subject"){
                $and_str .= " and n.n_subject like '%".$_REQUEST["sk"]."%'";
            }
            if($_REQUEST["st"]=="g_content"){
                $and_str .= " and g.g_content like '%".$_REQUEST["sk"]."%'";
            }
            $sql .= $and_str." order by gc.gc_sort ".$cms_cfg['sort_pos'].",g.g_sort ".$cms_cfg['sort_pos'].",g.g_modifydate desc ";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="gallery.php?func=g_list&gc_id=".$_REQUEST["gc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
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
                case "g_subject" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK1", "selected");
                    break;
                case "g_content" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK2", "selected");
                    break;
            }
            $i=$page["start_serial"];
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "GALLERY_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow'");
                }
                $tpl->assign( array("VALUE_GC_ID"  => $row["gc_id"],
                                    "VALUE_G_ID"  => $row["g_id"],
                                    "VALUE_G_SORT"  => $row["g_sort"],
                                    "VALUE_G_SUBJECT" => $row["g_subject"],
                                    "VALUE_G_STARTDATE" => $row["g_startdate"],
                                    "VALUE_G_SERIAL" => $i,
                                    "VALUE_GC_SUBJECT"  => $row["gc_subject"],
                                    "VALUE_STATUS_IMG" => ($row["g_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                    "VALUE_STATUS_IMG_ALT" => ($row["g_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

                ));
                //判斷刊登狀態
                if($row["g_status"]==0 ||($row["g_status"]==2 && $row["g_enddate"] < date("Y-m-d"))){
                    $tpl->assign("VALUE_G_PUBLISH_STATUS","已過期");
                }else{
                    $tpl->assign("VALUE_G_PUBLISH_STATUS","刊登中");
                }

            }
        }
    }
//Gallery--表單================================================================
    function gallery_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "VALUE_G_SORT"  => 1,
                                  "STR_G_STATUS_CK2" => "",
                                  "STR_G_STATUS_CK1" => "checked",
                                  "STR_G_STATUS_CK0" => "",
                                  "STR_G_CONTENT_TYPE_CK1" => "checked",
                                  "STR_G_CONTENT_TYPE_CK2" => "",
                                  "STR_G_CONTENT_TYPE_DISPLAY" => "",
                                  "STR_G_CONTENT_TYPE_DISPLAY2" => "none",
                                  "STR_G_HOT_CK1" => "",
                                  "STR_G_HOT_CK0" => "checked",
                                  "STR_G_POP_CK1" => "",
                                  "STR_G_POP_CK0" => "checked",
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
        if($action_mode=="mod" && !empty($_REQUEST["g_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_gallery where g_id='".$_REQUEST["g_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("VALUE_G_ID"  => $row["g_id"],
                                          "VALUE_G_STATUS"  => $row["g_status"],
                                          "VALUE_G_SORT"  => $row["g_sort"],
                                          "VALUE_G_SUBJECT" => $row["g_subject"],
                                          "VALUE_G_URL" => $row["g_url"],
                                          "VALUE_G_STARTDATE" => $row["g_startdate"],
                                          "VALUE_G_ENDDATE" => $row["g_enddate"],
                                          "STR_G_STATUS_CK2" => ($row["g_status"]==2)?"checked":"",
                                          "STR_G_STATUS_CK1" => ($row["g_status"]==1)?"checked":"",
                                          "STR_G_STATUS_CK0" => ($row["g_status"]==0)?"checked":"",
                                          "STR_G_CONTENT_TYPE_CK1" => ($row["g_content_type"]==1)?"checked":"",
                                          "STR_G_CONTENT_TYPE_CK2" => ($row["g_content_type"]==2)?"checked":"",
                                          "STR_G_CONTENT_TYPE_DISPLAY1" => ($row["g_content_type"]==1)?"":"none",
                                          "STR_G_CONTENT_TYPE_DISPLAY2" => ($row["g_content_type"]==2)?"":"none",
                                          "STR_G_HOT_CK1" => ($row["g_hot"]==1)?"checked":"",
                                          "STR_G_HOT_CK0" => ($row["g_hot"]==0)?"checked":"",
                                          "STR_G_POP_CK1" => ($row["g_pop"]==1)?"checked":"",
                                          "STR_G_POP_CK0" => ($row["g_pop"]==0)?"checked":"",
                                          "VALUE_G_S_PIC" => (trim($row["g_s_pic"])=="")?"":$row["g_s_pic"],
                                          "VALUE_PIC_PREVIEW" => (trim($row["g_s_pic"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["base_root"].$row["g_s_pic"],
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
            }else{
                header("location : gallery.php?func=g_list");
                die();
            }
        }
        for($j=1;$j<=$cms_cfg['gallery_img_limit'];$j++){	//新增時載入大圖區域及預設值
            //大圖區域TAB
            $tpl->newBlock("GALLERY_BIG_IMG_TAB");
            $tpl->assign("BIG_IMG_NO",$j);
            $tpl->newBlock("GALLERY_BIG_IMG");
            $tpl->assign( array(
                "VALUE_BIG_PIC_PREVIEW" => (trim($row["g_b_pic".$j])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["g_b_pic".$j],
                "VALUE_BIG_PIC" => (trim($row["g_b_pic".$j])=="")?"":$cms_cfg["file_root"].$row["g_b_pic".$j],
                "BIG_IMG_NO" =>$j,
            ));
        }                
        //Gallery分類
        $sql="select * from ".$cms_cfg['tb_prefix']."_gallery_cate where gc_id > '0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        while($row1 = $db->fetch_array($selectrs,1)){
            $tpl->newBlock( "SELECT_OPTION_GALLERY_CATE" );
            $tpl->assign( array( "OPTION_GALLERY_CATE_NAME"  => $row1["gc_subject"],
                                 "OPTION_GALLERY_CATE_VALUE" => $row1["gc_id"],
                                 "STR_GC_SEL"       => ($row1["gc_id"]==$row["gc_id"])?"selected":""
            ));
        }
        if($cms_cfg["ws_module"]["ws_wysiwyg"]=="tinymce"){
            $tpl->newBlock("WYSIWYG_TINYMCE1");
            $tpl->assign( "VALUE_G_CONTENT" , $row["g_content"] );
        }
    }
//Gallery--資料更新================================================================
    function gallery_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_gallery (
                        gc_id,
                        g_status,
                        g_sort,
                        g_hot,
                        g_pop,
                        g_subject,
                        g_content_type,
                        g_content,
                        g_url,
                        g_s_pic,
                        g_b_pic1,
                        g_b_pic2,
                        g_b_pic3,
                        g_b_pic4,
                        g_b_pic5,
                        g_b_pic6,
                        g_b_pic7,
                        g_b_pic8,
                        g_b_pic9,
                        g_b_pic10,
                        g_modifydate,
                        g_startdate,
                        g_enddate
                    ) values (
                        '".$_REQUEST["gc_id"]."',
                        '".$_REQUEST["g_status"]."',
                        '".$_REQUEST["g_sort"]."',
                        '".$_REQUEST["g_hot"]."',
                        '".$_REQUEST["g_pop"]."',
                        '".htmlspecialchars($_REQUEST["g_subject"])."',
                        '".$_REQUEST["g_content_type"]."',
                        '".$_REQUEST["g_content"]."',
                        '".$_REQUEST["g_url"]."',
                        '".$main->file_str_replace($_REQUEST["g_s_pic"])."',
                        '".$main->file_str_replace($_REQUEST["g_b_pic1"])."',
                        '".$main->file_str_replace($_REQUEST["g_b_pic2"])."',
                        '".$main->file_str_replace($_REQUEST["g_b_pic3"])."',
                        '".$main->file_str_replace($_REQUEST["g_b_pic4"])."',
                        '".$main->file_str_replace($_REQUEST["g_b_pic5"])."',
                        '".$main->file_str_replace($_REQUEST["g_b_pic6"])."',
                        '".$main->file_str_replace($_REQUEST["g_b_pic7"])."',
                        '".$main->file_str_replace($_REQUEST["g_b_pic8"])."',
                        '".$main->file_str_replace($_REQUEST["g_b_pic9"])."',
                        '".$main->file_str_replace($_REQUEST["g_b_pic10"])."',
                        '".date("Y-m-d H:i:s")."',
                        '".$_REQUEST["g_startdate"]."',
                        '".$_REQUEST["g_enddate"]."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_gallery set
                        gc_id='".$_REQUEST["gc_id"]."',
                        g_status='".$_REQUEST["g_status"]."',
                        g_sort='".$_REQUEST["g_sort"]."',
                        g_hot='".$_REQUEST["g_hot"]."',
                        g_pop='".$_REQUEST["g_pop"]."',
                        g_subject='".htmlspecialchars($_REQUEST["g_subject"])."',
                        g_content_type='".$_REQUEST["g_content_type"]."',
                        g_content='".$_REQUEST["g_content"]."',
                        g_url='".$_REQUEST["g_url"]."',
                        g_s_pic='".$main->file_str_replace($_REQUEST["g_s_pic"])."',
                        g_b_pic1='".$main->file_str_replace($_REQUEST["g_b_pic1"])."',
                        g_b_pic2='".$main->file_str_replace($_REQUEST["g_b_pic2"])."',
                        g_b_pic3='".$main->file_str_replace($_REQUEST["g_b_pic3"])."',
                        g_b_pic4='".$main->file_str_replace($_REQUEST["g_b_pic4"])."',
                        g_b_pic5='".$main->file_str_replace($_REQUEST["g_b_pic5"])."',
                        g_b_pic6='".$main->file_str_replace($_REQUEST["g_b_pic6"])."',
                        g_b_pic7='".$main->file_str_replace($_REQUEST["g_b_pic7"])."',
                        g_b_pic8='".$main->file_str_replace($_REQUEST["g_b_pic8"])."',
                        g_b_pic9='".$main->file_str_replace($_REQUEST["g_b_pic9"])."',
                        g_b_pic10='".$main->file_str_replace($_REQUEST["g_b_pic10"])."',
                        g_modifydate='".date("Y-m-d H:i:s")."',
                        g_startdate='".$_REQUEST["g_startdate"]."',
                        g_enddate='".$_REQUEST["g_enddate"]."'
                    where g_id='".$_REQUEST["g_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."gallery.php?func=g_list&gc_id=".$_REQUEST["gc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//Gallery--刪除--資料刪除可多筆處理================================================================
    function gallery_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["g_id"]){
            $g_id=array(0=>$_REQUEST["g_id"]);
        }else{
            $g_id=$_REQUEST["id"];
        }
        if(!empty($g_id)){
            $g_id_str = implode(",",$g_id);
            //刪除勾選的Gallery
            $sql="delete from ".$cms_cfg['tb_prefix']."_gallery where g_id in (".$g_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."gallery.php?func=g_list&gc_id=".$_REQUEST["gc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //Gallery分類更改狀態
        if($ws_table=="gc"){
            if($_REQUEST["gc_id"]){
                $gc_id=array(0=>$_REQUEST["gc_id"]);
            }else{
                $gc_id=$_REQUEST["id"];
            }
            if(!empty($gc_id)){
                $gc_id_str = implode(",",$gc_id);
                //更改分類底下的Gallery狀態
                $sql="update ".$cms_cfg['tb_prefix']."_gallery set g_status=".$value." where gc_id in (".$gc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    //狀態分類狀態
                    $sql="update ".$cms_cfg['tb_prefix']."_gallery_cate set gc_status=".$value." where gc_id in (".$gc_id_str.")";
                    $rs = $db->query($sql);
                    $db_msg = $db->report();
                    if ( $db_msg == "" ) {
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                        $goto_url=$cms_cfg["manage_url"]."gallery.php?func=gc_list&gc_id=".$_REQUEST["gc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                        $this->goto_target_page($goto_url);
                    }else{
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                    }
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //Gallery更改狀態
        if($ws_table=="g"){
            if($_REQUEST["g_id"]){
                $g_id=array(0=>$_REQUEST["g_id"]);
            }else{
                $g_id=$_REQUEST["id"];
            }
            if(!empty($g_id)){
                $g_id_str = implode(",",$g_id);
                //刪除勾選的Gallery
                $sql="update ".$cms_cfg['tb_prefix']."_gallery set g_status=".$value." where g_id in (".$n_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."gallery.php?func=g_list&gc_id=".$_REQUEST["gc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //Gallery分類更改排序值
        if(!empty($_REQUEST["sort_value"]) && !empty($ws_table)){
            if($ws_table=="gc"){
                $table_name=$cms_cfg['tb_prefix']."_gallery_cate";
            }
            if($ws_table=="g"){
                $table_name=$cms_cfg['tb_prefix']."_gallery";
            }
            foreach($_REQUEST["id"] as $key => $value){
                $sql="update ".$table_name." set ".$ws_table."_sort=".$_REQUEST["sort_value"][$value]." where ".$ws_table."_id='".$value."'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
            }
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."gallery.php?func=".$ws_table."_list&gc_id=".$_REQUEST["gc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //複製單筆資料
    function copy_data($ws_table){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //Gallery分類複製
        if($ws_table=="gc"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_gallery_cate where gc_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_gallery_cate (
                        gc_status,
                        gc_sort,
                        gc_subject
                    ) values (
                        '".$row["gc_status"]."',
                        '".$main->get_max_sort_value($cms_cfg['tb_prefix']."_gallery_cate","gc")."',
                        '".addslashes($row["gc_subject"])."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."gallery.php?func=gc_list&gc_id=".$_REQUEST["gc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //Gallery複製
        if($ws_table=="g"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_gallery where g_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_gallery (
                        gc_id,
                        g_status,
                        g_sort,
                        g_hot,
                        g_pop,
                        g_subject,
                        g_content_type,
                        g_content,
                        g_url,
                        g_s_pic,
                        g_b_pic1,
                        g_b_pic2,
                        g_b_pic3,
                        g_b_pic4,
                        g_b_pic5,
                        g_b_pic6,
                        g_b_pic7,
                        g_b_pic8,
                        g_b_pic9,
                        g_b_pic10,
                        g_modifydate,
                        g_startdate,
                        g_enddate
                    ) values (
                        '".$row["gc_id"]."',
                        '".$row["g_status"]."',
                        '".$main->get_max_sort_value($cms_cfg['tb_prefix']."_gallery","g")."',
                        '".$row["g_hot"]."',
                        '".$row["g_pop"]."',
                        '".addslashes($row["g_subject"])."',
                        '".$row["g_content_type"]."',
                        '".addslashes($row["g_content"])."',
                        '".$row["g_url"]."',
                        '".$row["g_s_pic"]."',
                        '".$row["g_b_pic1"]."',
                        '".$row["g_b_pic2"]."',
                        '".$row["g_b_pic3"]."',
                        '".$row["g_b_pic4"]."',
                        '".$row["g_b_pic5"]."',
                        '".$row["g_b_pic6"]."',
                        '".$row["g_b_pic7"]."',
                        '".$row["g_b_pic8"]."',
                        '".$row["g_b_pic9"]."',
                        '".$row["g_b_pic10"]."',
                        '".$row["g_modifydate"]."',
                        '".$row["g_startdate"]."',
                        '".$row["g_enddate"]."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."gallery.php?func=g_list&gc_id=".$_REQUEST["gc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
                if($_REQUEST["ws_table"]=="gc"){
                    $this->gallery_cate_del();
                }
                if($_REQUEST["ws_table"]=="g"){
                    $this->gallery_del();
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
