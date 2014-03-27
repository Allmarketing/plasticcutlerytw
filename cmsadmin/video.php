<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$video = new VIDEO;
class VIDEO{
    function VIDEO(){
        global $db,$cms_cfg,$tpl;
        $this->seo=($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_seo"] && $cms_cfg["ws_module"]["ws_seo"])?1:0;
        switch($_REQUEST["func"]){
            case "vc_list":
                $this->ws_tpl_file = "templates/ws-manage-video-cate-list-tpl.html";
                $this->current_class="VC";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->video_cate_list();
                $this->ws_tpl_type=1;
                break;
            case "vc_add"://影片分類新增
                $this->current_class="VC";
                $this->ws_tpl_file = "templates/ws-manage-video-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->video_cate_form("add");
                $this->ws_tpl_type=1;
                break;
            case "vc_mod"://影片分類修改
                $this->current_class="VC";
                $this->ws_tpl_file = "templates/ws-manage-video-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->video_cate_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "vc_replace"://影片分類更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->video_cate_replace();
                $this->ws_tpl_type=1;
                break;
            case "vc_del"://影片分類刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->video_cate_del();
                $this->ws_tpl_type=1;
                break;            
            case "v_list"://Youtube影片列表
                $this->ws_tpl_file = "templates/ws-manage-video-list-tpl.html";
                $this->current_class="V";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->video_list();
                $this->ws_tpl_type=1;
                break;
            case "v_add"://Youtube影片新增
                $this->ws_tpl_file = "templates/ws-manage-video-form-tpl.html";
                $this->current_class="V";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->video_form("add");
                $this->ws_tpl_type=1;
                break;
            case "v_mod"://Youtube影片修改
                $this->ws_tpl_file = "templates/ws-manage-video-form-tpl.html";
                $this->current_class="V";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->video_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "v_replace"://Youtube影片更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->video_replace();
                $this->ws_tpl_type=1;
                break;
            case "v_del"://Youtube影片刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->video_del();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //Youtube影片列表
                $this->ws_tpl_file = "templates/ws-manage-video-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->video_list();
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
        $tpl->assignGlobal("CSS_BLOCK_VIDEO","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }
    //影片分類--列表
    function video_cate_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_video_cate where vc_id > '0'";
        $and_str = "";
        if(!empty($_REQUEST["sk"])){
            $and_str = " and vc_subject like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by vc_sort ".$cms_cfg['sort_pos']." ";
        //取得總筆數
        $total_records=$main->count_total_records($sql);
        //取得分頁連結
        $func_str="video.php?func=vc_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
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
            $tpl->newBlock( "VIDEO_CATE_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_VC_ID"  => $row["vc_id"],
                                "VALUE_VC_STATUS"  => $row["vc_status"],
                                "VALUE_VC_SORT"  => $row["vc_sort"],
                                "VALUE_VC_SUBJECT" => $row["vc_subject"],
                                "VALUE_VC_SERIAL" => $i,
                                "VALUE_STATUS_IMG" => ($row["vc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                "VALUE_STATUS_IMG_ALT" => ($row["vc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

            ));
        }
    }
    //影片分類--表單
    function video_cate_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        if($this->seo){
            $tpl->newBlock("SEO_EDIT_ZONE");
        }
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "NOW_VC_ID"  => 0,
                                  "VALUE_VC_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_video_cate","vc","","",0),
                                  "STR_VC_STATUS_CK1" => "checked",
                                  "STR_VC_STATUS_CK0" => "",
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
        if($action_mode=="mod" && !empty($_REQUEST["vc_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_video_cate where vc_id='".$_REQUEST["vc_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("NOW_VC_ID"  => $row["vc_id"],
                                          "VALUE_VC_ID"  => $row["vc_id"],
                                          "VALUE_VC_STATUS"  => $row["vc_status"],
                                          "VALUE_VC_SORT"  => $row["vc_sort"],
                                          "VALUE_VC_SUBJECT" => $row["vc_subject"],
                                          "STR_VC_STATUS_CK1" => ($row["vc_status"])?"checked":"",
                                          "STR_VC_STATUS_CK0" => ($row["vc_status"])?"":"checked",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
                if($this->seo){
                    $tpl->assignGlobal( array("VALUE_VC_SEO_TITLE" => $row["vc_seo_title"],
                                              "VALUE_VC_SEO_KEYWORD" => $row["vc_seo_keyword"],
                                              "VALUE_VC_SEO_DESCRIPTION" => $row["vc_seo_description"],
                                              "VALUE_VC_SEO_FILENAME" => $row["vc_seo_filename"],
                                              "VALUE_VC_SEO_H1" => $row["vc_seo_h1"],
                                              "VALUE_VC_SEO_SHORT_DESC" => $row["vc_seo_short_desc"],
                    ));
                }
            }else{
                header("location : video.php?func=vc_list");
            }
        }
        //分類影片
        require_once "../class/imagefield.php";
        imagefield::setValues(array(
            "TAG_IMG_TITLE"         => "影片分類小圖",
            "TAG_IMG_FIELD_NAME"    => "vc_img",
            "TAG_IMG_FIELD_ID"      => "vc_img",
            "TAG_IMG_FIELD_VALUE"   => $row['vc_img'],
            "TAG_PREVIEW_IMG_ID"    => "vc_preview_img",
            "TAG_PREVIEW_IMG_VALUE" => $row['vc_img']?$cms_cfg['file_root'].$row['vc_img']:$cms_cfg['default_preview_pic'],            
        ));
        $tpl->assignGlobal("TAG_VIDEO_IMG_FIELD",  imagefield::get_html());
    }
    //影片分類--資料更新
    function video_cate_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        if($this->seo){
            $add_field_str="vc_seo_title,
                            vc_seo_keyword,
                            vc_seo_description,
                            vc_seo_filename,
                            vc_seo_h1,
                            vc_seo_short_desc,";
            $add_value_str="'".htmlspecialchars($_REQUEST["vc_seo_title"])."',
                            '".htmlspecialchars($_REQUEST["vc_seo_keyword"])."',
                            '".htmlspecialchars($_REQUEST["vc_seo_description"])."',
                            '".htmlspecialchars($_REQUEST["vc_seo_filename"])."',
                            '".htmlspecialchars($_REQUEST["vc_seo_h1"])."',
                            '".htmlspecialchars($_REQUEST["vc_seo_short_desc"])."',";
            $update_str="vc_seo_title='".htmlspecialchars($_REQUEST["vc_seo_title"])."',
                         vc_seo_keyword='".htmlspecialchars($_REQUEST["vc_seo_keyword"])."',
                         vc_seo_description='".htmlspecialchars($_REQUEST["vc_seo_description"])."',
                         vc_seo_filename='".htmlspecialchars($_REQUEST["vc_seo_filename"])."',
                         vc_seo_h1='".htmlspecialchars($_REQUEST["vc_seo_h1"])."',
                         vc_seo_short_desc='".htmlspecialchars($_REQUEST["vc_seo_short_desc"])."',";
        }
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_video_cate (
                        vc_img,
                        vc_status,
                        vc_sort,
                        ".$add_field_str."
                        vc_subject
                    ) values (
                        '".$main->file_str_replace($_REQUEST["vc_img"])."',
                        ".$_REQUEST["vc_status"].",
                        '".$_REQUEST["vc_sort"]."',
                        ".$add_value_str."
                        '".htmlspecialchars($_REQUEST["vc_subject"])."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_video_cate set
                        vc_img='".$main->file_str_replace($_REQUEST["vc_img"])."',
                        vc_status=".$_REQUEST["vc_status"].",
                        vc_sort='".$_REQUEST["vc_sort"]."',
                        ".$update_str."
                        vc_subject='".htmlspecialchars($_REQUEST["vc_subject"])."'
                    where vc_id='".$_REQUEST["vc_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."video.php?func=vc_list&vc_id=".$_REQUEST["vc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //影片分類--刪除
    function video_cate_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["vc_id"]){
            $vc_id=array(0=>$_REQUEST["vc_id"]);
        }else{
            $vc_id=$_REQUEST["id"];
        }
        if(!empty($vc_id)){
            $vc_id_str = implode(",",$vc_id);
            //清空分類底下的影片
            $sql="delete from ".$cms_cfg['tb_prefix']."_video where vc_id in (".$vc_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                //刪除分類
                $sql="delete from ".$cms_cfg['tb_prefix']."_video_cate where vc_id in (".$vc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."video.php?func=vc_list&vc_id=".$_REQUEST["vc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }

    //Youtube影片--列表================================================================
    function video_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //影片分類
        $sql="select * from ".$cms_cfg['tb_prefix']."_video_cate where vc_status='1' order by vc_sort ".$cms_cfg['sort_pos'];
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);        
        $i=0;
        $tpl->assignGlobal("TAG_NOW_CATE" ,$TPLMSG["NO_CATE"]);
        while($row = $db->fetch_array($selectrs,1)){
            $i++;
            $tpl->newBlock( "VIDEO_CATE_LIST" );
            $tpl->assign( array( "VALUE_VC_SUBJECT"  => $row["vc_subject"],
                                 "VALUE_VC_ID" => $row["vc_id"],
                                 "VALUE_VC_SERIAL" => $i,
                                 "VALUE_STATUS_IMG" => ($row["vc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                 "VALUE_STATUS_IMG_ALT" => ($row["vc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
            ));
            if($i%4==0){
                $tpl->assign("TAG_VIDEO_CATE_TRTD","</tr><tr>");
            }
            if($row["vc_id"]==$_REQUEST["vc_id"]){
                $tpl->assignGlobal("TAG_NOW_CATE",$row["vc_subject"]);
            }
        }        
        //Youtube影片列表
        $sql="select v.*,vc.vc_subject,vc.vc_sort from ".$cms_cfg['tb_prefix']."_video as v left join ".$cms_cfg['tb_prefix']."_video_cate as vc on v.vc_id=vc.vc_id where v_id > '0'";
        //附加條件
        $and_str="";
        if($_GET['vc_id']){
            $and_str=" and v.vc_id='".$_GET['vc_id']."'";
        }
        if($_REQUEST["st"]=="all"){
            $and_str .= " and (v_subject like '%".$_REQUEST["sk"]."%' or v_content like '%".$_REQUEST["sk"]."%')";
        }
        if($_REQUEST["st"]=="v_subject"){
            $and_str .= " and v_subject like '%".$_REQUEST["sk"]."%'";
        }
        if($_REQUEST["st"]=="v_content"){
            $and_str .= " and v_content like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by vc_sort ".$cms_cfg['sort_pos'].",v_sort ".$cms_cfg['sort_pos'].",v_modifydate desc ";
        //取得總筆數
        $selectrs = $db->query($sql);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結
        $fumc_str="video.php?func=v_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        //分頁且重新組合包含limit的sql語法
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
            case "v_subject" :
                $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK1", "selected");
                break;
            case "v_content" :
                $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK2", "selected");
                break;
        }
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "VIDEO_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_V_ID"  => $row["v_id"],
                                "VALUE_V_SORT"  => $row["v_sort"],
                                "VALUE_V_SUBJECT" => $row["v_subject"],
                                "VALUE_VC_SUBJECT" => $row["vc_subject"],
                                "VALUE_V_SERIAL" => $i,
                                "VALUE_STATUS_IMG" => ($row["v_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                "VALUE_STATUS_IMG_ALT" => ($row["v_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

            ));
        }
    }
//Youtube影片--表單================================================================
    function video_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        if($this->seo){
            $tpl->newBlock("SEO_EDIT_ZONE");
        }
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "NOW_V_ID"  => 0,
                                  "VALUE_V_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_video","v","","",0),
                                  "STR_V_STATUS_CK1" => "checked",
                                  "STR_V_STATUS_CK0" => "",
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
        if($action_mode=="mod" && !empty($_REQUEST["v_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_video where v_id='".$_REQUEST["v_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("NOW_V_ID"  => $row["v_id"],
                                          "VALUE_V_ID"  => $row["v_id"],
                                          "VALUE_V_SORT"  => $row["v_sort"],
                                          "VALUE_V_SUBJECT" => $row["v_subject"],
                                          "VALUE_V_CONTENT" => $row["v_content"],
                                          "STR_V_STATUS_CK1" => ($row["v_status"]==1)?"checked":"",
                                          "STR_V_STATUS_CK0" => ($row["v_status"]==0)?"checked":"",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
                if($this->seo){
                    $tpl->assignGlobal( array("VALUE_V_SEO_TITLE" => $row["v_seo_title"],
                                              "VALUE_V_SEO_KEYWORD" => $row["v_seo_keyword"],
                                              "VALUE_V_SEO_DESCRIPTION" => $row["v_seo_description"],
                                              "VALUE_V_SEO_FILENAME" => $row["v_seo_filename"],
                                              "VALUE_V_SEO_H1" => $row["v_seo_h1"],
                    ));
                }
            }else{
                header("location : video.php?func=v_list");
            }
        }
        $this->video_cate_select($row['vc_id']);
    }
//Youtube影片--資料更新================================================================
    function video_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($this->seo){
            $add_field_str="v_seo_title,
                            v_seo_keyword,
                            v_seo_description,
                            v_seo_filename,
                            v_seo_h1,";
            $add_value_str="'".htmlspecialchars($_REQUEST["v_seo_title"])."',
                            '".htmlspecialchars($_REQUEST["v_seo_keyword"])."',
                            '".htmlspecialchars($_REQUEST["v_seo_description"])."',
                            '".htmlspecialchars($_REQUEST["v_seo_filename"])."',
                            '".htmlspecialchars($_REQUEST["v_seo_h1"])."',";
            $update_str="v_seo_title='".htmlspecialchars($_REQUEST["v_seo_title"])."',
                         v_seo_keyword='".htmlspecialchars($_REQUEST["v_seo_keyword"])."',
                         v_seo_description='".htmlspecialchars($_REQUEST["v_seo_description"])."',
                         v_seo_filename='".htmlspecialchars($_REQUEST["v_seo_filename"])."',
                         v_seo_h1='".htmlspecialchars($_REQUEST["v_seo_h1"])."',";
        }
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_video (
                        vc_id,
                        v_status,
                        v_sort,
                        v_subject,
                        v_content,
                        ".$add_field_str."
                        v_modifydate
                    ) values (
                        '".$_REQUEST["vc_id"]."',
                        '".$_REQUEST["v_status"]."',
                        '".$_REQUEST["v_sort"]."',
                        '".$_REQUEST["v_subject"]."',
                        '".$_REQUEST["v_content"]."',
                        ".$add_value_str."
                        '".date("Y-m-d H:i:s")."'
                    )";
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_video set
                        vc_id='".$_REQUEST["vc_id"]."',
                        v_status='".$_REQUEST["v_status"]."',
                        v_sort='".$_REQUEST["v_sort"]."',
                        v_subject='".$_REQUEST["v_subject"]."',
                        v_content='".$_REQUEST["v_content"]."',
                        ".$update_str."
                        v_modifydate='".date("Y-m-d H:i:s")."'
                    where v_id='".$_REQUEST["v_id"]."'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."video.php?func=v_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//Youtube影片--刪除--資料刪除可多筆處理================================================================
    function video_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["v_id"]){
            $v_id=array(0=>$_REQUEST["v_id"]);
        }else{
            $v_id=$_REQUEST["id"];
        }
        if(!empty($v_id)){
            $v_id_str = implode(",",$v_id);
            //刪除勾選的影片
            $sql="delete from ".$cms_cfg['tb_prefix']."_video where v_id in (".$v_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."video.php?func=v_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //Youtube影片更改狀態
        if($ws_table=="v"){
            if($_REQUEST["v_id"]){
                $v_id=array(0=>$_REQUEST["v_id"]);
            }else{
                $v_id=$_REQUEST["id"];
            }
            if(!empty($v_id)){
                $v_id_str = implode(",",$v_id);
                //更改勾選的Youtube影片狀態
                $sql="update ".$cms_cfg['tb_prefix']."_video set v_status='".$value."' where v_id in (".$v_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."video.php?func=v_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }elseif($ws_table=="vc"){
            if($_REQUEST["vc_id"]){
                $vc_id=array(0=>$_REQUEST["vc_id"]);
            }else{
                $vc_id=$_REQUEST["id"];
            }
            if(!empty($vc_id)){
                $vc_id_str = implode(",",$vc_id);
                //更改勾選的Youtube影片狀態
                $sql="update ".$cms_cfg['tb_prefix']."_video_cate set vc_status='".$value."' where vc_id in (".$vc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."video.php?func=vc_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //Youtube影片更改排序值
        if(!empty($_REQUEST["sort_value"]) && !empty($ws_table)){
            if($ws_table=="v"){
                $table_name=$cms_cfg['tb_prefix']."_video";
            }elseif($ws_table=="vc"){
                $table_name=$cms_cfg['tb_prefix']."_video_cate";
            }
            foreach($_REQUEST["id"] as $key => $value){
                $sql="update ".$table_name." set ".$ws_table."_sort='".$_REQUEST["sort_value"][$value]."' where ".$ws_table."_id='".$value."'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
            }
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."video.php?func=".$ws_table."_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //複製單筆資料
    function copy_data($ws_table){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //Youtube影片複製
        if($ws_table=="v"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_video where v_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_video (
                        vc_id,
                        v_status,
                        v_sort,
                        v_subject,
                        v_content,
                        v_modifydate
                    ) values (
                        ".$row["vc_id"].",
                        ".$row["v_status"].",
                        '".$main->get_max_sort_value($cms_cfg['tb_prefix']."_video","v")."',
                        '".$row["v_subject"]."',
                        '".$row["v_content"]."',
                        '".date("Y-m-d H:i:s")."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."video.php?func=v_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
                switch($_REQUEST["ws_table"]){
                    case "v":
                    $this->video_del();
                        break;
                    case "vc":
                        $this->video_cate_del();
                        break;
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
    function video_cate_select($vc_id){
        global $db,$cms_cfg,$tpl;
        $sql = "select * from ".$cms_cfg['tb_prefix']."_video_cate where vc_status='1' order by vc_sort ".$cms_cfg['sort_pos'];
        $res = $db->query($sql,true);
        if($db->numRows($res)){
            while($row = $db->fetch_array($res,1)){
                $tpl->newBlock("VIDEO_CATE_LIST");
                $tpl->assign(array(
                   "VALUE_VC_ID"      => $row['vc_id'], 
                   "VALUE_VC_SUBJECT" => $row['vc_subject'], 
                   "TAG_SELECTED"     => ($vc_id && $row['vc_id']==$vc_id)?"selected":"", 
                ));
            }
        }
    }
}
//ob_end_flush();
?>
