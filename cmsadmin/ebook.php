<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])  || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_ebook"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$ebook = new EBOOK;
class EBOOK{
    function EBOOK(){
        global $db,$cms_cfg,$tpl,$main;
        $this->seo=($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_seo"] && $cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->root_user=($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]=="root")?1:0;
        $this->op_limit=20;
        $this->jp_limit=10;
        switch($_REQUEST["func"]){
            case "ebc_list"://電子型錄管理分類列表
                $this->current_class="EBC";
                $this->ws_tpl_file = "templates/ws-manage-ebook-cate-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_EBC_TREE");
                $this->ebook_cate_list();
                $this->ws_tpl_type=1;
                break;
            case "ebc_add"://電子型錄管理分類新增
                $this->current_class="EBC";
                $this->ws_tpl_file = "templates/ws-manage-ebook-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_TINYMCE");
                $this->ebook_cate_form("add");
                $this->ws_tpl_type=1;
                break;
            case "ebc_mod"://電子型錄管理分類修改
                $num=($this->root_user)?1:$this->check_data_locked("ebc",$_REQUEST["ebc_id"]);
                if($num==0){
                    header("location: /");
                }else{
                    $this->current_class="EBC";
                    $this->ws_tpl_file = "templates/ws-manage-ebook-cate-form-tpl.html";
                    $this->ws_load_tp($this->ws_tpl_file);
                    $tpl->newBlock("JS_FORMVALID");
                    $tpl->newBlock("JS_PREVIEWS_PIC");
                    $tpl->newBlock("JS_MAIN");
                    $tpl->newBlock("JS_TINYMCE");
                    $this->ebook_cate_form("mod");
                    $this->ws_tpl_type=1;
                }
                break;
            case "ebc_replace"://電子型錄管理分類更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->ebook_cate_replace();
                $this->ws_tpl_type=1;
                break;
            case "ebc_del"://電子型錄管理分類刪除
                if($_REQUEST["ebc_id"]!=""){
                    $num=($this->root_user)?1:$this->check_data_locked("ebc",$_REQUEST["ebc_id"]);
                }else{
                    $num=1; //批次處理的直接通過
                }
                if($num==0){
                    header("location: /");
                }else{
                    $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                    $this->ws_load_tp($this->ws_tpl_file);
                    $this->ebook_cate_del();
                    $this->ws_tpl_type=1;
                }
                break;
            case "eb_list"://電子型錄管理列表
                $this->current_class="EB";
                $this->ws_tpl_file = "templates/ws-manage-ebook-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_PC_TREE");
                $this->ebook_list();
                $this->ws_tpl_type=1;
                break;
            case "eb_add"://電子型錄管理新增
                $this->current_class="EB";
                $this->ws_tpl_file = "templates/ws-manage-ebook-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_TINYMCE");
                $tpl->newBlock("JS_TABTITLE");
                $this->ebook_form("add");
                $this->ws_tpl_type=1;
                break;
            case "eb_mod"://電子型錄管理修改
                $num=($this->root_user)?1:$this->check_data_locked("eb",$_REQUEST["eb_id"]);
                if($num==0){
                    header("location: /");
                }else{
                    $this->current_class="EB";
                    $this->ws_tpl_file = "templates/ws-manage-ebook-form-tpl.html";
                    $this->ws_load_tp($this->ws_tpl_file);
                    $tpl->newBlock("JS_FORMVALID");
                    $tpl->newBlock("JS_PREVIEWS_PIC");
                    $tpl->newBlock("JS_MAIN");
                    $tpl->newBlock("JS_TINYMCE");
                    $tpl->newBlock("JS_TABTITLE");
                    $this->ebook_form("mod");
                    $this->ws_tpl_type=1;
                }
                break;
            case "eb_replace"://電子型錄管理更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->ebook_replace();
                $this->ws_tpl_type=1;
                break;
            case "eb_del"://電子型錄管理刪除
                if($_REQUEST["eb_id"]!=""){
                    $num=($this->root_user)?1:$this->check_data_locked("eb",$_REQUEST["eb_id"]);
                }else{
                    $num=1; //批次處理的直接通過
                }
                if($num==0){
                    header("location: /");
                }else{
                    $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                    $this->ws_load_tp($this->ws_tpl_file);
                    $this->ebook_del();
                    $this->ws_tpl_type=1;
                }
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //電子型錄管理列表
                $this->current_class="P";
                $this->ws_tpl_file = "templates/ws-manage-ebook-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->ebook_list();
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
        $tpl->assignGlobal("CSS_BLOCK_EBOOK","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }
    function check_data_locked($table,$id){
        global $db,$cms_cfg;
        if($table=="ebc"){
            $sql="select ebc_id from ".$cms_cfg['tb_prefix']."_ebook_cate where ebc_id='".$id."' and (ebc_locked='0' || (ebc_locked='1' and ebc_modifyaccount='".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'))";
        }
        if($table=="eb"){
            $sql="select eb_id from ".$cms_cfg['tb_prefix']."_ebook where eb_id='".$id."' and (eb_locked='0' || (eb_locked='1' and eb_modifyaccount='".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'))";
        }
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        return $rsnum;
    }
    //電子型錄管理分類--列表
    function ebook_cate_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $this->parent=($_REQUEST["ebc_parent"])?$_REQUEST["ebc_parent"]:0;
        //系統跳回參數
        $tpl->assignGlobal( "VALUE_EBC_PARENT", $this->parent);
        $sql="select * from ".$cms_cfg['tb_prefix']."_ebook_cate where ebc_id > '0'";
        $and_str = "";
        if(!$this->root_user){
            $and_str = " and (ebc_locked='0' || (ebc_locked='1' and ebc_modifyaccount='".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'))";
        }
        if(!empty($_REQUEST["sk"])){
            $and_str = " and ebc_name like '%".$_REQUEST["sk"]."%'";
        }else{
            $and_str = " and ebc_parent='".$this->parent."'";
        }
        $sql .= $and_str." order by ebc_sort ".$cms_cfg['sort_pos'].",ebc_modifydate desc ";
        //取得總筆數
        $total_records=$main->count_total_records($sql);
        //取得分頁連結
        $func_str="ebook.php?func=ebc_list&ebc_parent=".$this->parent."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        //分頁且重新組合包含limit的sql語法
        $sql=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                  "VALUE_TOTAL_BOX" => $rsnum,
                                  "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],
        ));
        //階層
        $tpl->assignGlobal("MSG_NOW_CATE" , $TPLMSG["NOW_CATE"]);
        $ebook_cate_layer=$main->get_layer($cms_cfg['tb_prefix']."_ebook_cate","ebc_name","ebc",$this->parent,$func_str);
        if(!empty($ebook_cate_layer)){
            $tpl->assignGlobal("TAG_EBOOK_CATE_LAYER",implode(" > ",$ebook_cate_layer));
        }else{
            $tpl->assignGlobal("TAG_EBOOK_CATE_LAYER",$TPLMSG["NO_CATE"]);
        }
        //分類列表
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "EBOOK_CATE_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_EBC_ID"  => $row["ebc_id"],
                                "VALUE_EBC_STATUS"  => $row["ebc_status"],
                                "VALUE_EBC_SORT"  => $row["ebc_sort"],
                                "VALUE_EBC_NAME" => $row["ebc_name"],
                                "VALUE_EBC_CATE_IMG" => (trim($row["ebc_cate_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["ebc_cate_img"],
                                "VALUE_EBC_SERIAL" => $i,
                                "VALUE_STATUS_IMG" => ($row["ebc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                "VALUE_STATUS_IMG_ALT" => ($row["ebc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
                                "VALUE_LOCK_IMG" => ($row["ebc_locked"])?$cms_cfg['default_lock']:$cms_cfg['default_key'],
                                "VALUE_EBC_MODIFYDATE" => $row["ebc_modifydate"],
                                "VALUE_EBC_MODIFYACCOUNT" => $row["ebc_modifyaccount"],

            ));
        }
    }
    //電子型錄管理分類--表單
    function ebook_cate_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "VALUE_EBC_SORT" => $main->get_max_sort_value($cms_cfg['tb_prefix'].'_ebook_cate','ebc'),
                                  "NOW_EBC_ID"  => 0,
                                  "STR_EBC_STATUS_CK1" => "checked",
                                  "STR_EBC_STATUS_CK0" => "",
                                  "STR_EBC_LOCK_CK1" => "checked",
                                  "STR_EBC_LOCK_CK0" => "",
                                  "VALUE_PIC_PREVIEW1" => $cms_cfg['default_preview_pic'],
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
        if($action_mode=="mod" && !empty($_REQUEST["ebc_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_ebook_cate where ebc_id='".$_REQUEST["ebc_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("NOW_EBC_ID"  => $row["ebc_id"],
                                          "NOW_EBC_PARENT"  => $row["ebc_parent"],
                                          "VALUE_EBC_SORT"  => $row["ebc_sort"],
                                          "VALUE_EBC_NAME" => $row["ebc_name"],
                                          "VALUE_EBC_NAME_ALIAS" => $row["ebc_name_alias"],
                                          "NOW_EBC_LEVEL" => $row["ebc_level"],
                                          "VALUE_EBC_CATE_IMG" => (trim($row["ebc_cate_img"])=="")?"":$cms_cfg["file_root"].$row["ebc_cate_img"],
                                          "VALUE_PIC_PREVIEW1" => (trim($row["ebc_cate_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["ebc_cate_img"],
                                          "STR_EBC_STATUS_CK1" => ($row["ebc_status"])?"checked":"",
                                          "STR_EBC_STATUS_CK0" => ($row["ebc_status"])?"":"checked",
                                          "STR_EBC_LOCK_CK1" => ($row["ebc_locked"])?"checked":"",
                                          "STR_EBC_LOCK_CK0" => ($row["ebc_locked"])?"":"checked",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
            }else{
                header("location : ebook.php?func=ebc_list");
            }
        }
        //載入分類資料,選擇分類
        $this->ebook_cate_select($this->ebook_cate_select_option, $row["ebc_id"],$row["ebc_parent"], $parent=0, $indent="");
        $tpl->assignGlobal("TAG_SELECT_EBOOK_CATE" ,$this->ebook_cate_select_option);
    }
    //電子型錄管理分類--資料更新
    function ebook_cate_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        switch ($_REQUEST["action_mode"]){
            case "add":
                    $sql="
                    insert into ".$cms_cfg['tb_prefix']."_ebook_cate(
                        ebc_parent,
                        ebc_status,
                        ebc_sort,
                        ebc_name,
                        ebc_name_alias,
                        ebc_cate_img,
                        ebc_modifydate,
                        ebc_locked,
                        ebc_modifyaccount
                    ) values (
                        '".$_REQUEST["ebc_parent"]."',
                        '".$_REQUEST["ebc_status"]."',
                        '".$_REQUEST["ebc_sort"]."',
                        '".htmlspecialchars($_REQUEST["ebc_name"])."',
                        '".htmlspecialchars($_REQUEST["ebc_name_alias"])."',
                        '".$main->file_str_replace($_REQUEST["ebc_cate_img"])."',
                        '".date("Y-m-d m:i:s")."',
                        '".$_REQUEST["ebc_locked"]."',
                        '".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'
                    )";

                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $this->ebc_id=$db->get_insert_id();
                    //取得新的分類階層
                    $ebook_cate_layer=$main->get_layer($cms_cfg['tb_prefix']."_ebook_cate","ebc_id","ebc",$this->ebc_id,"",2);
                    if(!empty($ebook_cate_layer)){
                        $ebc_layer="0-".implode("-",$ebook_cate_layer);
                        $ebc_level=count($ebook_cate_layer)+1;
                    }else{
                        $ebc_layer="0-".$this->ebc_id;
                        $ebc_level=1;
                    }
                    $sql="
                        update ".$cms_cfg['tb_prefix']."_ebook_cate set
                            ebc_layer='".$ebc_layer."',
                            ebc_level='".$ebc_level."'
                        where ebc_id='".$this->ebc_id."'
                    ";
                    $rs = $db->query($sql);
                    $db_msg = $db->report();
                }
                break;
            case "mod":
                //取得新的分類階層
                $ebook_cate_layer=$main->get_layer($cms_cfg['tb_prefix']."_ebook_cate","ebc_id","ebc",$_REQUEST["ebc_parent"],"",2);
                if(!empty($ebook_cate_layer)){
                    $ebc_layer="0-".implode("-",$ebook_cate_layer)."-".$_REQUEST["now_ebc_id"];
                    $ebc_level=count($ebook_cate_layer)+1;
                }else{
                    $ebc_layer="0-".$_REQUEST["now_ebc_id"];
                    $ebc_level=1;
                }
                $sql="
                update ".$cms_cfg['tb_prefix']."_ebook_cate set
                    ebc_parent='".$_REQUEST["ebc_parent"]."',
                    ebc_layer='".$ebc_layer."',
                    ebc_status='".$_REQUEST["ebc_status"]."',
                    ebc_sort='".$_REQUEST["ebc_sort"]."',
                    ebc_name='".htmlspecialchars($_REQUEST["ebc_name"])."',
                    ebc_name_alias='".htmlspecialchars($_REQUEST["ebc_name_alias"])."',
                    ebc_level='".$ebc_level."',
                    ebc_cate_img='".$main->file_str_replace($_REQUEST["ebc_cate_img"])."',
                    ebc_modifydate='".date("Y-m-d m:i:s")."',
                    ebc_locked='".$_REQUEST["ebc_locked"]."',
                    ebc_modifyaccount='".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'
                where ebc_id='".$_REQUEST["now_ebc_id"]."'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                break;
        }
        if ( $db_msg == "" ) {
            //更新電子型錄的products category layer
            $sql="
                update ".$cms_cfg['tb_prefix']."_products set
                    ebc_layer='".$ebc_layer."'
                where ebc_id='".$_REQUEST["now_ebc_id"]."'";
            $rs = $db->query($sql);
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$cms_cfg["manage_url"]."ebook.php?func=ebc_list&ebc_parent=".$_REQUEST["ebc_parent"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
            $this->goto_target_page($goto_url);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
    //電子型錄管理分類--刪除
    function ebook_cate_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["ebc_id"]){
            $ebc_id=array(0=>$_REQUEST["ebc_id"]);
        }else{
            $ebc_id=$_REQUEST["id"];
        }
        if(!empty($ebc_id)){
            $ebc_id_str = implode(",",$ebc_id);
            //清空分類底下的電子型錄管理
            $sql="delete from ".$cms_cfg['tb_prefix']."_ebook where ebc_id in (".$ebc_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                //刪除分類
                $sql="delete from ".$cms_cfg['tb_prefix']."_ebook_cate where ebc_id in (".$ebc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."ebook.php?func=ebc_list&ebc_id=".$_REQUEST["ebc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//電子型錄管理--列表================================================================
    function ebook_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $sql="select count(ebc_id) as ebc_total from ".$cms_cfg['tb_prefix']."_ebook_cate where ebc_id > '0'";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        //沒有分類先建立分類
        if($row["ebc_total"]<1){
            $tpl->assignGlobal( "MSG_CREATE_CATE_FIRST" , $TPLMSG["CREATE_CATE_FIRST"]);
            $goto_url=$cms_cfg["manage_url"]."ebook.php?func=ebc_add";
            $this->goto_target_page($goto_url);
        }else{
            //電子型錄管理分類
            $this->parent=($_REQUEST["ebc_parent"])?$_REQUEST["ebc_parent"]:0;
            //分類樹狀結構--p type
            $this->ebook_cate_tree($this->parent,"eb");
            //系統跳回參數
            $tpl->assignGlobal( "VALUE_EBC_PARENT", $this->parent);
            $sql="select * from ".$cms_cfg['tb_prefix']."_ebook_cate where ebc_id > '0'";
            $and_str = "";
            if(!empty($_REQUEST["sk"])){
                $and_str = " and ebc_name like '%".$_REQUEST["sk"]."%'";
            }else{
                $and_str = " and ebc_parent='".$this->parent."'";
            }
            $sql .= $and_str;
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $i=0;
            $tpl->assignGlobal("TAG_NOW_CATE",$TPLMSG["NO_CATE"]);
            while($row = $db->fetch_array($selectrs,1)){
                $i++;
                $tpl->newBlock( "EBOOK_CATE_LIST" );
                $tpl->assign( array( "VALUE_EBC_NAME"  => $row["ebc_name"],
                                     "VALUE_EBC_ID" => $row["ebc_id"],
                                     "VALUE_EBC_SERIAL" => $i,
                                     "VALUE_STATUS_IMG" => ($row["ebc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                     "VALUE_STATUS_IMG_ALT" => ($row["ebc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
                ));
                if($i%4==0){
                    $tpl->assign("TAG_EBOOK_CATE_TRTD","</tr><tr>");
                }
            }

            //電子型錄管理列表
            $sql="select eb.*,ebc.ebc_name from ".$cms_cfg['tb_prefix']."_ebook as eb left join ".$cms_cfg['tb_prefix']."_ebook_cate as ebc on eb.ebc_id=ebc.ebc_id where eb.eb_id > '0'";
            //附加條件
            $and_str="";
            if(!$this->root_user){
                $and_str = " and (eb.eb_locked='0' || (eb.eb_locked='1' and eb.eb_modifyaccount='".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'))";
            }
            if(!empty($_REQUEST["ebc_parent"])){
                $and_str .= " and eb.ebc_id = '".$_REQUEST["ebc_parent"]."'";
            }
            if($_REQUEST["st"]=="eb_name"){
                $and_str .= " and eb.eb_name like '%".$_REQUEST["sk"]."%'";
            }
            $sql .= $and_str." order by eb.eb_sort ".$cms_cfg['sort_pos'].",eb.eb_modifydate desc ";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records    = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="ebook.php?func=eb_list&ebc_parent=".$this->parent."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
            //分頁且重新組合包含limit的sql語法
            $sql=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $tpl->assignGlobal( array("VALUE_TOTAL_BOX" => $rsnum,
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                      "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp']
            ));
            switch($_REQUEST["st"]){
                case "p_name" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK0", "selected");
                    break;
            }
            //階層
            $tpl->assignGlobal("MSG_NOW_CATE" , $TPLMSG["NOW_CATE"]);
            $ebook_cate_layer=$main->get_layer($cms_cfg['tb_prefix']."_ebook_cate","ebc_name","ebc",$this->parent,$func_str);
            if(!empty($ebook_cate_layer)){
                $tpl->assignGlobal("TAG_EBOOK_CATE_LAYER",implode(" > ",$ebook_cate_layer));
            }else{
                $tpl->assignGlobal("TAG_EBOOK_CATE_LAYER",$TPLMSG["NO_CATE"]);
            }
            //電子型錄列表
            $i=$page["start_serial"];
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "EBOOK_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow'");
                }
                $tpl->assign( array("VALUE_EBC_ID"  => $row["ebc_id"],
                                    "VALUE_EB_ID"  => $row["eb_id"],
                                    "VALUE_EB_SORT"  => $row["eb_sort"],
                                    "VALUE_EB_NAME" => $row["eb_name"],
                                    "VALUE_EB_SMALL_IMG" => (trim($row["eb_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["eb_small_img"],
                                    "VALUE_EB_SERIAL" => $i,
                                    "VALUE_EBC_NAME"  => ($row["ebc_name"])?$row["ebc_name"]:$TPLMSG['NO_CATE'],
                                    "VALUE_STATUS_IMG" => ($row["eb_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                    "VALUE_STATUS_IMG_ALT" => ($row["eb_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
                                    "VALUE_LOCK_IMG" => ($row["eb_locked"])?$cms_cfg['default_lock']:$cms_cfg['default_key'],
                                    "VALUE_EB_MODIFYDATE" => $row["eb_modifydate"],
                                    "VALUE_EB_MODIFYACCOUNT" => $row["eb_modifyaccount"],
                                    "VALUE_NOW_PAGE" => $_REQUEST['nowp']

                ));
            }
        }
    }
//電子型錄管理--表單================================================================
    function ebook_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $this->parent=($_REQUEST["ebc_parent"])?$_REQUEST["ebc_parent"]:0;
        //系統跳回參數
        $tpl->assignGlobal( "VALUE_EBC_PARENT", $this->parent);
        $sql="select sc_cart_type from ".$cms_cfg['tb_prefix']."_system_config where sc_id='1'";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "VALUE_EB_SORT" => $main->get_max_sort_value($cms_cfg['tb_prefix'].'_ebook','eb','ebc_id',$this->parent,true),
                                  "NOW_EB_ID" => 0,
                                  "VALUE_SMALL_PIC_PREVIEW1" => $cms_cfg['default_preview_pic'],
                                  "VALUE_BIG_PIC_PREVIEW1" => $cms_cfg['default_preview_pic'],
                                  "STR_EB_STATUS_CK1" => "checked",
                                  "STR_EB_STATUS_CK0" => "",
                                  "STR_EB_LOCK_CK1" => "checked",
                                  "STR_EB_LOCK_CK0" => "",
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
        if($action_mode=="mod" && !empty($_REQUEST["eb_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_ebook where eb_id='".$_REQUEST["eb_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("NOW_EB_ID"  => $row["eb_id"],
                                          "NOW_EBC_ID"  => $row["ebc_id"],
                                          "VALUE_EB_SORT"  => $row["eb_sort"],
                                          "VALUE_EB_LINK"  => $row["eb_link"],
                                          "VALUE_EB_NAME" => $row["eb_name"],
                                          "VALUE_EB_NAME_ALIAS" => $row["eb_name_alias"],
                                          "VALUE_SMALL_IMG" => (trim($row["eb_small_img"])=="")?"":$cms_cfg["file_root"].$row["eb_small_img"],
                                          "VALUE_SMALL_PIC_PREVIEW1" => (trim($row["eb_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["eb_small_img"],
                                          "VALUE_BIG_PIC_PREVIEW1" => (trim($row["eb_big_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["eb_big_img"],
                                          "VALUE_BIG_PIC1" => (trim($row["eb_big_img"])=="")?"":$cms_cfg["file_root"].$row["eb_big_img"],
                                          "STR_EB_STATUS_CK1" => ($row["eb_status"])?"checked":"",
                                          "STR_EB_STATUS_CK0" => ($row["eb_status"])?"":"checked",
                                          "STR_EB_LOCK_CK1" => ($row["eb_locked"])?"checked":"",
                                          "STR_EB_LOCK_CK0" => ($row["eb_locked"])?"":"checked",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
            }else{
                header("location : ebook.php?func=eb_list");
            }
        }
        $this->ebook_cate_select2($this->ebook_cate_select_option,$row["ebc_id"], $parent=0, $indent="");
        $tpl->assignGlobal("TAG_SELECT_EBOOK_CATE" ,$this->ebook_cate_select_option);
    }
//電子型錄管理--資料更新================================================================
    function ebook_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        switch ($_REQUEST["action_mode"]){
            case "add":
                //取得分類階層
                $ebook_cate_layer=$main->get_layer($cms_cfg['tb_prefix']."_ebook_cate","ebc_id","ebc",$_REQUEST["ebc_id"],"",2);
                if(!empty($ebook_cate_layer)){
                    $ebc_layer="0-".implode("-",$ebook_cate_layer);
                }else{
                    $ebc_layer="0-".$_REQUEST["ebc_id"];
                }
                $sql="
                    INSERT INTO ".$cms_cfg['tb_prefix']."_ebook(
                        ebc_id,
                        ebc_layer,
                        eb_status,
                        eb_sort,
                        eb_name,
                        eb_name_alias,
                        eb_small_img,
                        eb_big_img,
                        eb_link,
                        eb_modifydate,
                        eb_locked,
                        eb_modifyaccount
                    ) VALUES (
                        '".$_REQUEST["ebc_id"]."',
                        '".$_REQUEST["ebc_layer"]."',
                        '".$_REQUEST["eb_status"]."',
                        '".$_REQUEST["eb_sort"]."',
                        '".htmlspecialchars($_REQUEST["eb_name"])."',
                        '".htmlspecialchars($_REQUEST["eb_name_alias"])."',
                        '".$this->file_str_replace($_REQUEST["eb_small_img"])."',
                        '".$this->file_str_replace($_REQUEST["eb_big_img"])."',
                        '".$_REQUEST["eb_link"]."',
                        '".date("Y-m-d H:i:s")."',
                        '".$_REQUEST["eb_locked"]."',
                        '".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                $this->eb_id=$db->get_insert_id();
                break;
            case "mod":
                //取得分類階層
                $ebook_cate_layer=$main->get_layer($cms_cfg['tb_prefix']."_ebook_cate","ebc_id","ebc",$_REQUEST["ebc_id"],"",2);
                if(!empty($ebook_cate_layer)){
                    $ebc_layer="0-".implode("-",$ebook_cate_layer);
                }else{
                    $ebc_layer="0-".$_REQUEST["ebc_id"];
                }
                $sql="
                UPDATE ".$cms_cfg['tb_prefix']."_ebook SET
                    ebc_id = '".$_REQUEST["ebc_id"]."',
                    ebc_layer = '".$ebc_layer."',
                    eb_status = '".$_REQUEST["eb_status"]."',
                    eb_sort = '".$_REQUEST["eb_sort"]."',
                    eb_name = '".htmlspecialchars($_REQUEST["eb_name"])."',
                    eb_name_alias = '".htmlspecialchars($_REQUEST["eb_name_alias"])."',
                    eb_small_img = '".$this->file_str_replace($_REQUEST["eb_small_img"])."',
                    eb_big_img = '".$this->file_str_replace($_REQUEST["eb_big_img"])."',
                    eb_link = '".$_REQUEST["eb_link"]."',
                    eb_modifydate = '".date("Y-m-d H:i:s")."',
                    eb_locked = '".$_REQUEST["eb_locked"]."',
                    eb_modifyaccount = '".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'
                WHERE eb_id ='".$_REQUEST["now_eb_id"]."' ";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                $this->eb_id=$_REQUEST["now_eb_id"];
                break;
        }
        if ( $db_msg == "" ) {
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$cms_cfg["manage_url"]."ebook.php?func=eb_list&ebc_parent=".$_REQUEST["ebc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
            $this->goto_target_page($goto_url);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
//電子型錄管理--刪除--資料刪除可多筆處理================================================================
    function ebook_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["eb_id"]){
            $eb_id=array(0=>$_REQUEST["eb_id"]);
        }else{
            $eb_id=$_REQUEST["id"];
        }
        if(!empty($eb_id)){
            $eb_id_str = implode(",",$eb_id);
            //刪除勾選的電子型錄管理
            $sql="delete from ".$cms_cfg['tb_prefix']."_ebook where eb_id in (".$eb_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."ebook.php?func=eb_list&ebc_parent=".$_REQUEST["ebc_parent"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //電子型錄管理分類更改狀態
        if($ws_table=="ebc"){
            if($_REQUEST["ebc_id"]){
                $ebc_id=array(0=>$_REQUEST["ebc_id"]);
            }else{
                $ebc_id=$_REQUEST["id"];
            }
            if(!empty($ebc_id)){
                $ebc_id_str = implode(",",$ebc_id);
                $sql="update ".$cms_cfg['tb_prefix']."_ebook set eb_status=".$value." where ebc_id in (".$ebc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    //狀態分類狀態
                    $sql="update ".$cms_cfg['tb_prefix']."_ebook_cate set ebc_status=".$value." where ebc_id in (".$ebc_id_str.")";
                    $rs = $db->query($sql);
                    $db_msg = $db->report();
                    if ( $db_msg == "" ) {
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                        $goto_url=$cms_cfg["manage_url"]."ebook.php?func=ebc_list&ebc_parent=".$_REQUEST["ebc_parent"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                        $this->goto_target_page($goto_url);
                    }else{
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                    }
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //電子型錄管理更改狀態
        if($ws_table=="eb"){
            if($_REQUEST["eb_id"]){
                $eb_id=array(0=>$_REQUEST["eb_id"]);
            }else{
                $eb_id=$_REQUEST["id"];
            }
            if(!empty($eb_id)){
                $eb_id_str = implode(",",$eb_id);
                $sql="update ".$cms_cfg['tb_prefix']."_ebook set eb_status=".$value." where eb_id in (".$eb_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."ebook.php?func=eb_list&ebc_parent=".$_REQUEST["ebc_parent"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
    }
    //更改狀態
    function change_lock($ws_table,$value){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //電子型錄管理分類更改資料鎖定
        if($ws_table=="ebc"){
            if($_REQUEST["ebc_id"]){
                $ebc_id=array(0=>$_REQUEST["ebc_id"]);
            }else{
                $ebc_id=$_REQUEST["id"];
            }
            if(!empty($ebc_id)){
                $ebc_id_str = implode(",",$ebc_id);
                $sql="update ".$cms_cfg['tb_prefix']."_ebook_cate set ebc_locked=".$value." where ebc_id in (".$ebc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."ebook.php?func=ebc_list&ebc_parent=".$_REQUEST["ebc_parent"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //電子型錄管理更改資料鎖定
        if($ws_table=="eb"){
            if($_REQUEST["eb_id"]){
                $eb_id=array(0=>$_REQUEST["eb_id"]);
            }else{
                $eb_id=$_REQUEST["id"];
            }
            if(!empty($eb_id)){
                $eb_id_str = implode(",",$eb_id);
                $sql="update ".$cms_cfg['tb_prefix']."_ebook set eb_locked=".$value." where eb_id in (".$eb_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."ebook.php?func=eb_list&ebc_parent=".$_REQUEST["ebc_parent"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
        //電子型錄管理分類更改排序值
        if(!empty($_REQUEST["sort_value"]) && !empty($ws_table)){
            if($ws_table=="ebc"){
                $table_name=$cms_cfg['tb_prefix']."_ebook_cate";
            }
            if($ws_table=="eb"){
                $table_name=$cms_cfg['tb_prefix']."_ebook";
            }
            foreach($_REQUEST["id"] as $key => $value){
                $sql="update ".$table_name." set ".$ws_table."_sort=".$_REQUEST["sort_value"][$value]." where ".$ws_table."_id='".$value."'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
            }
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."ebook.php?func=".$ws_table."_list&ebc_parent=".$_REQUEST["ebc_parent"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //複製單筆資料
    function copy_data($ws_table){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //電子型錄管理分類複製
        if($ws_table=="ebc"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_ebook_cate where ebc_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_ebook_cate(
                        ebc_parent,
                        ebc_status,
                        ebc_sort,
                        ebc_name,
                        ebc_name_alias,
                        ebc_cate_img,
                        ebc_modifydate
                    ) values (
                        '".$row["ebc_parent"]."',
                        '".$row["ebc_status"]."',
                        '".$main->get_max_sort_value($cms_cfg['tb_prefix']."_ebook_cate","ebc","ebc_parent",$row['ebc_parent'],true)."',
                        '".addslashes($row["ebc_name"])."',
                        '".addslashes($row["ebc_name_alias"])."',
                        '".$row["ebc_cate_img"]."',
                        '".date("Y-m-d m:i:s")."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $this->ebc_id=$db->get_insert_id();
                    //取得新的分類階層
                    $ebook_cate_layer=$main->get_layer($cms_cfg['tb_prefix']."_ebook_cate","ebc_id","ebc",$this->ebc_id,"",2);
                    if(!empty($ebook_cate_layer)){
                        $ebc_layer="0-".implode("-",$ebook_cate_layer);
                        $ebc_level=count($ebook_cate_layer)+1;
                    }else{
                        $ebc_layer="0-".$this->ebc_id;
                        $ebc_level=1;
                    }
                    $sql="
                        update ".$cms_cfg['tb_prefix']."_ebook_cate set
                            ebc_layer='".$ebc_layer."',
                            ebc_level='".$ebc_level."'
                        where ebc_id='".$this->ebc_id."'
                    ";
                    $rs = $db->query($sql);
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."ebook.php?func=ebc_list&ebc_parent=".$_REQUEST["ebc_parent"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //電子型錄管理複製
        if($ws_table=="eb"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_ebook where eb_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($rsnum >0){
                $sql="
                    INSERT INTO ".$cms_cfg['tb_prefix']."_ebook(
                        ebc_id,
                        ebc_layer,
                        eb_status,
                        eb_sort,
                        eb_name,
                        eb_name_alias,
                        eb_small_img,
                        eb_big_img,
                        eb_modifydate,
                        eb_locked,
                        eb_modifyaccount
                    ) VALUES (
                        '".$row["ebc_id"]."',
                        '".$row["ebc_layer"]."',
                        '".$row["eb_status"]."',
                        '".$main->get_max_sort_value($cms_cfg['tb_prefix']."_ebook","eb")."',
                        '".addslashes($row["eb_name"])."',
                        '".addslashes($row["eb_name_alias"])."',
                        '".$row["eb_small_img"]."',
                        '".$row["eb_big_img"]."',
                        '".date("Y-m-d H:i:s")."',
                        '".$row["eb_locked"]."',
                        '".$row["eb_modifyaccount"]."'
                    )";
                $rs = $db->query($sql);
                $this->eb_id=$db->get_insert_id();
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."ebook.php?func=eb_list&ebc_parent=".$_REQUEST["ebc_parent"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }

    }
    //組合分類下拉選單
    function ebook_cate_select(&$output, &$ebc_id,$now_ebc_parent, $ebc_parent=0, $indent="") {
        global $db,$cms_cfg;
        $sql = "SELECT ebc_id,ebc_name FROM ".$cms_cfg['tb_prefix']."_ebook_cate WHERE  ebc_parent='".$ebc_parent."' order by ebc_sort";
        $selectrs = $db->query($sql);
        while ($row =  $db->fetch_array($selectrs,1)) {
            $selected = ($row["ebc_id"]==$now_ebc_parent ? "selected" : "");
            //自己分類底下的項目不提供選擇以免進入無窮迴圈
            if($row["ebc_id"]!=$ebc_id){
                $output .= "<option value=\"".$row["ebc_id"]."\" ".$selected.">".$indent."├".$row["ebc_name"]."</option>";
                if($row["ebc_id"]!=$ebc_parent){
                    $this->ebook_cate_select($output, $ebc_id,$now_ebc_parent, $row["ebc_id"],$indent."****");
                }
            }
        }
    }
    //組合分類下拉選單--電子型錄選擇分類專用
    function ebook_cate_select2(&$output,$now_ebc_parent, $ebc_parent=0, $indent="") {
        global $db,$cms_cfg;
        $sql = "SELECT ebc_id,ebc_name FROM ".$cms_cfg['tb_prefix']."_ebook_cate WHERE ebc_parent='".$ebc_parent."' order by ebc_sort";
        $selectrs = $db->query($sql);
        while ($row =  $db->fetch_array($selectrs,1)) {
            $selected = ($row["ebc_id"]==$now_ebc_parent ? "selected" : "");
            $output .= "<option value=\"".$row["ebc_id"]."\" ".$selected.">".$indent."├".$row["ebc_name"]."</option>";
            if($row["ebc_id"]!=$ebc_parent){
                $this->ebook_cate_select2($output,$now_ebc_parent, $row["ebc_id"],$indent."****");
            }
        }
    }
    function ebook_cate_tree($ebc_id,$type){
        global $tpl,$db,$cms_cfg;
        $sql="select ebc_id,ebc_layer from ".$cms_cfg['tb_prefix']."_ebook_cate where ebc_id='".$ebc_id."'";
        $selectrs = $db->query($sql);
        $rsnum = $db->numRows($selectrs);
        if($rsnum >0){
            $row = $db->fetch_array($selectrs,1);
            $ebc_layer_array=explode("-",$row["ebc_layer"]);
        }else{
            $ebc_layer_array=array();
        }
        $ebc_cate_tree=$this->get_tree(0,$ebc_id,$ebc_layer_array,$ebc_cate_tree="",$type);
        $tpl->assignGlobal( "VALUE_EBC_CATE_TREE",$ebc_cate_tree);
    }
    function get_tree($ebc_id,$now_ebc_id,$ebc_layer_array,$ebc_cate_tree,$type){
        global $db,$cms_cfg,$tpl;
        $sql="select ebc_id,ebc_parent,ebc_name,ebc_layer,ebc_level from ".$cms_cfg['tb_prefix']."_ebook_cate where ebc_parent='".$ebc_id."' order by ebc_sort";
        $selectrs = $db->query($sql);
        $rsnum = $db->numRows($selectrs);
        if($rsnum >0){
            $totalwords=strlen($ebc_cate_tree);
            $mi=substr($ebc_cate_tree,$totalwords-6,6);
            if($mi=="</li>\n"){
                $ebc_cate_tree=substr($ebc_cate_tree,0,$totalwords-6)."\n<ul>";
            }else{
                if($ebc_id==0){
                    $ebc_cate_tree .="\n<ul>";
                }else{
                    $ebc_cate_tree .="\n</li>\n<ul>\n";
                }
            }
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $space_str=str_repeat("        ",$row["ebc_level"]);
                $tag_span_class=($now_ebc_id==$row["ebc_id"])?"active":"text";
                $class_str=(in_array($row["ebc_id"],$ebc_layer_array))?"class=\"open\"":"";
                $ebc_cate_tree =$space_str.$ebc_cate_tree ."<li id='".$row["ebc_id"]."' ".$class_str."><a href=\"ebook.php?func=".$type."_list&ebc_parent=".$row["ebc_id"]."\"><span class='".$tag_span_class."'>".$row["ebc_name"]."</span></a></li>\n";
                $ebc_cate_tree = $this->get_tree($row["ebc_id"],$now_ebc_id,$ebc_layer_array,$ebc_cate_tree,$type);
            }
            $ebc_cate_tree =($mi=="</li>\n")?$space_str.$ebc_cate_tree ."</ul>\n</li>\n":$ebc_cate_tree =$space_str.$ebc_cate_tree ."</ul>\n";
        }
        return $ebc_cate_tree;
    }
    //資料處理
    function data_processing(){
        switch ($_REQUEST["process_type"]){
            case "del":
                if($_REQUEST["ws_table"]=="ebc"){
                    $this->ebook_cate_del();
                }
                if($_REQUEST["ws_table"]=="eb"){
                    $this->ebook_del();
                }
                break;
            case "copy":
                $this->copy_data($_REQUEST["ws_table"]);
                break;
            case "status":
                $this->change_status($_REQUEST["ws_table"],$_REQUEST["value"]);
                break;
            case "lock":
                $this->change_lock($_REQUEST["ws_table"],$_REQUEST["value"]);
                break;
            case "sort":
                $this->change_sort($_REQUEST["ws_table"]);
                break;
        }
    }
    //圖檔檔案路徑替換避免破圖
    function file_str_replace($input_path){
        global $cms_cfg;
        $input_path=str_replace($cms_cfg['file_url'],"",$input_path);
        $input_path=str_replace($cms_cfg['file_root']."upload_files/","upload_files/",$input_path);
        return $input_path;
    }
}
//ob_end_flush();
?>