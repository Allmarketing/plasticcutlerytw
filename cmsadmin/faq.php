<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])  || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_faq"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$faq = new FAQ;
class FAQ{
    function FAQ(){
        global $db,$cms_cfg,$tpl;
        $this->seo=($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_seo"] && $cms_cfg["ws_module"]["ws_seo"])?1:0;
        switch($_REQUEST["func"]){
            case "fc_list"://問與答分類列表
                $this->current_class="FC";
                $this->ws_tpl_file = "templates/ws-manage-faq-cate-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->faq_cate_list();
                $this->ws_tpl_type=1;
                break;
            case "fc_add"://問與答分類新增
                $this->current_class="FC";
                $this->ws_tpl_file = "templates/ws-manage-faq-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->faq_cate_form("add");
                $this->ws_tpl_type=1;
                break;
            case "fc_mod"://問與答分類修改
                $this->current_class="FC";
                $this->ws_tpl_file = "templates/ws-manage-faq-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->faq_cate_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "fc_replace"://問與答分類更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->faq_cate_replace();
                $this->ws_tpl_type=1;
                break;
            case "fc_del"://問與答分類刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->faq_cate_del();
                $this->ws_tpl_type=1;
                break;
            case "f_list"://問與答列表
                $this->current_class="F";
                $this->ws_tpl_file = "templates/ws-manage-faq-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->faq_list();
                $this->ws_tpl_type=1;
                break;
            case "f_add"://問與答新增
                $this->current_class="F";
                $this->ws_tpl_file = "templates/ws-manage-faq-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_TINYMCE");
                $this->faq_form("add");
                $this->ws_tpl_type=1;
                break;
            case "f_mod"://問與答修改
                $this->current_class="F";
                $this->ws_tpl_file = "templates/ws-manage-faq-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_TINYMCE");
                $this->faq_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "f_replace"://問與答更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->faq_replace();
                $this->ws_tpl_type=1;
                break;
            case "f_del"://問與答刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->faq_del();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //問與答列表
                $this->current_class="F";
                $this->ws_tpl_file = "templates/ws-manage-faq-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->faq_list();
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
        $tpl->assignGlobal("CSS_BLOCK_FAQ","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }

    //問與答分類--列表
    function faq_cate_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $sql="select * from ".$cms_cfg['tb_prefix']."_faq_cate where fc_id > '0'";
        $and_str = "";
        if(!empty($_REQUEST["sk"])){
            $and_str = " and fc_subject like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by fc_sort ".$cms_cfg['sort_pos']." ";
        //取得總筆數
        $total_records=$main->count_total_records($sql);
        //取得分頁連結
        $func_str="faq.php?func=fc_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        //分頁且重新組合包含limit的sql語法
        $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("MSG_SUBJECT"  => $TPLMSG['CATE'].$TPLMSG['NAME'],
                                  "MSG_STATUS" => $TPLMSG['STATUS'],
                                  "MSG_MODE" => $TPLMSG['MANAGE_CATE'],
                                  "MSG_ADD" => $TPLMSG['ADD'],
                                  "MSG_MODIFY" => $TPLMSG['MODIFY'],
                                  "MSG_DEL" => $TPLMSG['DEL'],
                                  "MSG_COPY" => $TPLMSG['COPY'],
                                  "MSG_SORT" => $TPLMSG['SORT'],
                                  "MSG_ON" => $TPLMSG['ON'],
                                  "MSG_OFF" => $TPLMSG['OFF'],
                                  "MSG_KEYWORD" => $TPLMSG['KEYWORD'],
                                  "MSG_LOGIN_LANGUAGE" => $TPLMSG['LOGIN_LANGUAGE'],
                                  "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                  "VALUE_TOTAL_BOX" => $rsnum,
                                  "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],
        ));
        //分類列表
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "FAQ_CATE_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_FC_ID"  => $row["fc_id"],
                                "VALUE_FC_SORT"  => $row["fc_sort"],
                                "VALUE_FC_SUBJECT" => $row["fc_subject"],
                                "VALUE_FC_SERIAL" => $i,
                                "VALUE_STATUS_IMG" => ($row["fc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                "VALUE_STATUS_IMG_ALT" => ($row["fc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

            ));
        }
    }
    //問與答分類--表單
    function faq_cate_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        if($this->seo){
            $tpl->newBlock("SEO_EDIT_ZONE");
        }
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'], //表單預設模式為『新增』
                                  "NOW_FC_ID"  => 0,
                                  "VALUE_FC_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_faq_cate","fc","","",0),
                                  "STR_FC_STATUS_CK1" => "checked",//啟用狀態預設為『啟用』
                                  "STR_FC_STATUS_CK0" => "",
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
        if($action_mode=="mod" && !empty($_REQUEST["fc_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_faq_cate where fc_id='".$_REQUEST["fc_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("NOW_FC_ID"  => $row["fc_id"],
                                          "VALUE_FC_ID"  => $row["fc_id"],
                                          "VALUE_FC_SORT"  => $row["fc_sort"],
                                          "VALUE_FC_SUBJECT" => $row["fc_subject"],
                                          "STR_FC_STATUS_CK1" => ($row["fc_status"])?"checked":"",
                                          "STR_FC_STATUS_CK0" => ($row["fc_status"])?"":"checked",
                                          "MSG_MODE" => $TPLMSG['MODIFY'] //表單模式變更為『修改』
                ));
                if($this->seo){
                    $tpl->assignGlobal( array("VALUE_FC_SEO_TITLE" => $row["fc_seo_title"],
                                              "VALUE_FC_SEO_KEYWORD" => $row["fc_seo_keyword"],
                                              "VALUE_FC_SEO_DESCRIPTION" => $row["fc_seo_description"],
                                              "VALUE_FC_SEO_FILENAME" => $row["fc_seo_filename"],
                                              "VALUE_FC_SEO_H1" => $row["fc_seo_h1"],
                    ));
                }
            }else{
                header("location : faq.php?func=fc_list");
            }
        }
    }
    //問與答分類--資料更新
    function faq_cate_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($this->seo){
            $add_field_str="fc_seo_title,
                            fc_seo_keyword,
                            fc_seo_description,
                            fc_seo_filename,
                            fc_seo_h1,";
            $add_value_str="'".htmlspecialchars($_REQUEST["fc_seo_title"])."',
                            '".htmlspecialchars($_REQUEST["fc_seo_keyword"])."',
                            '".htmlspecialchars($_REQUEST["fc_seo_description"])."',
                            '".htmlspecialchars($_REQUEST["fc_seo_filename"])."',
                            '".htmlspecialchars($_REQUEST["fc_seo_h1"])."',";
            $update_str="fc_seo_title='".htmlspecialchars($_REQUEST["fc_seo_title"])."',
                         fc_seo_keyword='".htmlspecialchars($_REQUEST["fc_seo_keyword"])."',
                         fc_seo_description='".htmlspecialchars($_REQUEST["fc_seo_description"])."',
                         fc_seo_filename='".htmlspecialchars($_REQUEST["fc_seo_filename"])."',
                         fc_seo_h1='".htmlspecialchars($_REQUEST["fc_seo_h1"])."',";
        }
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_faq_cate (
                        fc_status,
                        fc_sort,
                        ".$add_field_str."
                        fc_subject
                    ) values (
                        '".$_REQUEST["fc_status"]."',
                        '".$_REQUEST["fc_sort"]."',
                        ".$add_value_str."
                        '".$_REQUEST["fc_subject"]."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_faq_cate set
                        fc_status='".$_REQUEST["fc_status"]."',
                        fc_sort='".$_REQUEST["fc_sort"]."',
                        ".$update_str."
                        fc_subject='".$_REQUEST["fc_subject"]."'
                    where fc_id='".$_REQUEST["fc_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."faq.php?func=fc_list&fc_id=".$_REQUEST["fc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //問與答分類--刪除
    function faq_cate_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["fc_id"]){
            $fc_id=array(0=>$_REQUEST["fc_id"]);
        }else{
            $fc_id=$_REQUEST["id"];
        }
        if(!empty($fc_id)){
            $fc_id_str = implode(",",$fc_id);
            //清空分類底下的問與答
            $sql="delete from ".$cms_cfg['tb_prefix']."_faq where fc_id in (".$fc_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                //刪除分類
                $sql="delete from ".$cms_cfg['tb_prefix']."_faq_cate where fc_id in (".$fc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."faq.php?func=fc_list&fc_id=".$_REQUEST["fc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//問與答--列表================================================================
    function faq_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $sql="select * from ".$cms_cfg['tb_prefix']."_faq_cate where fc_id > '0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        //沒有分類先建立分類
        if($rsnum<1){
            $tpl->assignGlobal( "MSG_CREATE_CATE_FIRST" , $TPLMSG["CREATE_CATE_FIRST"]);
            $goto_url=$cms_cfg["manage_url"]."faq.php?func=fc_add";
            $this->goto_target_page($goto_url);
        }else{
            //問與答分類
            $i=0;
            $tpl->assignGlobal("TAG_NOW_CATE" ,$TPLMSG["NO_CATE"]);
            while($row = $db->fetch_array($selectrs,1)){
                $i++;
                $tpl->newBlock( "FAQ_CATE_LIST" );
                $tpl->assign( array( "VALUE_FC_SUBJECT"  => $row["fc_subject"],
                                     "VALUE_FC_ID" => $row["fc_id"],
                                     "VALUE_FC_SERIAL" => $i,
                                     "VALUE_STATUS_IMG" => ($row["fc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                     "VALUE_STATUS_IMG_ALT" => ($row["fc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
                ));
                if($i%4==0){
                    $tpl->assign("TAG_FAQ_CATE_TRTD","</tr><tr>");
                }
                if($row["fc_id"]==$_REQUEST["fc_id"]){
                    $tpl->assignGlobal("TAG_NOW_CATE",$row["fc_subject"]);
                }
            }
            //問與答列表
            $sql="select f.*,fc.fc_subject from ".$cms_cfg['tb_prefix']."_faq as f left join ".$cms_cfg['tb_prefix']."_faq_cate as fc on f.fc_id=fc.fc_id where f.f_id > '0'";
            //附加條件
            $and_str="";
            if(!empty($_REQUEST["fc_id"])){
                $and_str .= " and f.fc_id = '".$_REQUEST["fc_id"]."'";
            }
            if($_REQUEST["st"]=="all"){
                $and_str .= " and (f.f_subject like '%".$_REQUEST["sk"]."%' or f.f_content like '%".$_REQUEST["sk"]."%')";
            }
            if($_REQUEST["st"]=="f_subject"){
                $and_str .= " and f.f_subject like '%".$_REQUEST["sk"]."%'";
            }
            if($_REQUEST["st"]=="f_content"){
                $and_str .= " and f.f_content like '%".$_REQUEST["sk"]."%'";
            }
            $sql .= $and_str." order by f.f_sort ".$cms_cfg['sort_pos'].",f.f_modifydate desc ";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records    = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="faq.php?func=f_list&fc_id=".$_REQUEST["fc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
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
                case "f_subject" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK1", "selected");
                    break;
                case "f_content" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK2", "selected");
                    break;
            }
            $tpl->assignGlobal( "VALUE_NOW_FC_ID" , $_REQUEST["fc_id"]);
            $i=$page["start_serial"];
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "FAQ_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow'");
                }
                $tpl->assign( array("VALUE_FC_ID"  => $row["fc_id"],
                                    "VALUE_F_ID"  => $row["f_id"],
                                    "VALUE_F_SORT"  => $row["f_sort"],
                                    "VALUE_F_SUBJECT" => $row["f_subject"],
                                    "VALUE_F_SERIAL" => $i,
                                    "VALUE_FC_SUBJECT"  => $row["fc_subject"],
                                    "VALUE_STATUS_IMG" => ($row["f_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                    "VALUE_STATUS_IMG_ALT" => ($row["f_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

                ));

            }
        }
    }
//問與答--表單================================================================
    function faq_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //欄位名稱
        $cate=(trim($_REQUEST["fc_id"])!="")?1:0;
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "VALUE_F_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_faq","f","fc_id",$_REQUEST["fc_id"],$cate),
                                  "STR_F_STATUS_CK1" => "checked",
                                  "STR_F_STATUS_CK0" => "",
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
        if($action_mode=="mod" && !empty($_REQUEST["f_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_faq where f_id='".$_REQUEST["f_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("VALUE_F_ID"  => $row["f_id"],
                                          "VALUE_F_SORT"  => $row["f_sort"],
                                          "VALUE_F_SUBJECT" => $row["f_subject"],
                                          "STR_F_STATUS_CK1" => ($row["f_status"]==1)?"checked":"",
                                          "STR_F_STATUS_CK0" => ($row["f_status"]==0)?"checked":"",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
            }else{
                header("location : faq.php?func=f_list");
            }
        }
        //問與答分類
        $sql="select * from ".$cms_cfg['tb_prefix']."_faq_cate where fc_id > '0'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        while($row1 = $db->fetch_array($selectrs,1)){
            $tpl->newBlock( "TAG_SELECT_FAQ_CATE" );
            $tpl->assign( array( "TAG_SELECT_FAQ_CATE_NAME"  => $row1["fc_subject"],
                                 "TAG_SELECT_FAQ_CATE_VALUE" => $row1["fc_id"],
                                 "STR_FC_SEL"       => ($row1["fc_id"]==$row["fc_id"])?"selected":""
            ));
        }
        if($cms_cfg["ws_module"]["ws_wysiwyg"]=="tinymce"){
            $tpl->newBlock("WYSIWYG_TINYMCE1");
            $tpl->assign( "VALUE_F_CONTENT" , $row["f_content"] );
        }
    }
//問與答--資料更新================================================================
    function faq_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_faq (
                        fc_id,
                        f_status,
                        f_sort,
                        f_subject,
                        f_content,
                        f_modifydate
                    ) values (
                        '".$_REQUEST["fc_id"]."',
                        '".$_REQUEST["f_status"]."',
                        '".$_REQUEST["f_sort"]."',
                        '".$_REQUEST["f_subject"]."',
                        '".$_REQUEST["f_content"]."',
                        '".date("Y-m-d H:i:s")."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_faq set
                        fc_id='".$_REQUEST["fc_id"]."',
                        f_status='".$_REQUEST["f_status"]."',
                        f_sort='".$_REQUEST["f_sort"]."',
                        f_subject='".$_REQUEST["f_subject"]."',
                        f_content='".$_REQUEST["f_content"]."',
                        f_modifydate='".date("Y-m-d H:i:s")."'
                    where f_id='".$_REQUEST["f_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."faq.php?func=f_list&fc_id=".$_REQUEST["fc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//問與答--刪除--資料刪除可多筆處理================================================================
    function faq_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["f_id"]){
            $f_id=array(0=>$_REQUEST["f_id"]);
        }else{
            $f_id=$_REQUEST["id"];
        }
        if(!empty($f_id)){
            $f_id_str = implode(",",$f_id);
            //刪除勾選的問與答
            $sql="delete from ".$cms_cfg['tb_prefix']."_faq where f_id in (".$f_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."faq.php?func=f_list&fc_id=".$_REQUEST["fc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //問與答分類更改狀態
        if($ws_table=="fc"){
            if($_REQUEST["fc_id"]){
                $fc_id=array(0=>$_REQUEST["fc_id"]);
            }else{
                $fc_id=$_REQUEST["id"];
            }
            if(!empty($fc_id)){
                $fc_id_str = implode(",",$fc_id);
                //更改分類底下的問與答狀態
                $sql="update ".$cms_cfg['tb_prefix']."_faq set f_status='".$value."' where fc_id in (".$fc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    //狀態分類狀態
                    $sql="update ".$cms_cfg['tb_prefix']."_faq_cate set fc_status='".$value."' where fc_id in (".$fc_id_str.")";
                    $rs = $db->query($sql);
                    $db_msg = $db->report();
                    if ( $db_msg == "" ) {
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                        $goto_url=$cms_cfg["manage_url"]."faq.php?func=fc_list&fc_id=".$_REQUEST["fc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                        $this->goto_target_page($goto_url);
                    }else{
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                    }
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //問與答更改狀態
        if($ws_table=="f"){
            if($_REQUEST["f_id"]){
                $f_id=array(0=>$_REQUEST["f_id"]);
            }else{
                $f_id=$_REQUEST["id"];
            }
            if(!empty($f_id)){
                $f_id_str = implode(",",$f_id);
                //刪除勾選的問與答
                $sql="update ".$cms_cfg['tb_prefix']."_faq set f_status='".$value."' where f_id in (".$f_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."faq.php?func=f_list&fc_id=".$_REQUEST["fc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //問與答分類更改排序值
        if(!empty($_REQUEST["sort_value"]) && !empty($ws_table)){
            if($ws_table=="fc"){
                $table_name=$cms_cfg['tb_prefix']."_faq_cate";
            }
            if($ws_table=="f"){
                $table_name=$cms_cfg['tb_prefix']."_faq";
            }
            foreach($_REQUEST["id"] as $key => $value){
                $sql="update ".$table_name." set ".$ws_table."_sort='".$_REQUEST["sort_value"][$value]."' where ".$ws_table."_id='".$value."'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
            }
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."faq.php?func=".$ws_table."_list&fc_id=".$_REQUEST["fc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //複製單筆資料
    function copy_data($ws_table){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //問與答分類複製
        if($ws_table=="fc"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_faq_cate where fc_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_faq_cate (
                        fc_status,
                        fc_sort,
                        fc_subject
                    ) values (
                        '".$row["fc_status"]."',
                        '".$row["fc_sort"]."',
                        '".$row["fc_subject"]."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."faq.php?func=fc_list&fc_id=".$_REQUEST["fc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //問與答複製
        if($ws_table=="f"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_faq where f_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_faq (
                        fc_id,
                        f_status,
                        f_sort,
                        f_subject,
                        f_content,
                        f_modifydate
                    ) values (
                        '".$row["fc_id"]."',
                        '".$row["f_status"]."',
                        '".$row["f_sort"]."',
                        '".$row["f_subject"]."',
                        '".$row["f_content"]."',
                        '".$row["f_modifydate"]."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."faq.php?func=f_list&fc_id=".$_REQUEST["fc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
                if($_REQUEST["ws_table"]=="fc"){
                    $this->faq_cate_del();
                }
                if($_REQUEST["ws_table"]=="f"){
                    $this->faq_del();
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
