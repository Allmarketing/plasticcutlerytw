<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])  || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_admin"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$admin = new ADMIN;
class ADMIN{
    function ADMIN(){
        global $db,$cms_cfg,$tpl;
        $this->current_class="AI";
        switch($_REQUEST["func"]){
            case "ai_list"://管理員列表
                $this->ws_tpl_file = "templates/ws-manage-admin-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->admin_list();
                $this->ws_tpl_type=1;
                break;
            case "ai_add"://管理員新增
                $this->ws_tpl_file = "templates/ws-manage-admin-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_TABTITLE");
                $this->admin_form("add");
                $this->ws_tpl_type=1;
                break;
            case "ai_mod"://管理員修改
                $this->ws_tpl_file = "templates/ws-manage-admin-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_TABTITLE");
                $this->admin_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "ai_replace"://管理員更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->admin_replace();
                $this->ws_tpl_type=1;
                break;
            case "ai_del"://管理員刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->admin_del();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //管理員列表
                $this->ws_tpl_file = "templates/ws-manage-admin-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->admin_list();
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
        $tpl->assignGlobal("CSS_BLOCK_SYSTEM","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }


    //管理員--列表================================================================
    function admin_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //沒有簽權限程式的不顯示管理員功能,只保留修改root密碼權限
        ($cms_cfg["ws_module"]["ws_admin"])?$tpl->newBlock( "IMG_ITEM_ADMIN" ):"";
        //管理員列表
        $sql="select * from ".$cms_cfg['tb_prefix']."_admin_info  where ai_id > '0'";
        //附加條件
        $and_str="";
        if($_REQUEST["st"]=="ai_name"){
            $and_str .= " and ai_name like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by ai_sort ".$cms_cfg['sort_pos'].",ai_modifydate desc ";
        //取得總筆數
        $selectrs = $db->query($sql);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結
        $fumc_str="admin.php?func=ai_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        //分頁並重新組合包含limit的sql語法
        $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$fumc_str,$total_records,$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("VALUE_TOTAL_BOX" => $rsnum,
                                  "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                  "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],

        ));
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "ADMIN_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_AI_ID"  => $row["ai_id"],
                                "VALUE_AI_SORT"  => $row["ai_sort"],
                                "VALUE_AI_ACCOUNT" => $row["ai_account"],
                                "VALUE_AI_NAME" => $row["ai_name"],
                                "VALUE_AI_SERIAL" => $i,
                                "VALUE_STATUS_IMG" => ($row["ai_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                "VALUE_STATUS_IMG_ALT" => ($row["ai_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],

            ));

            //root帳號不顯示核選方塊及刪除連結
            if($row["ai_account"]=="root"){
                $tpl->assign( "TAG_MARK_START","&nbsp;<!--" );
                $tpl->assign( "TAG_MARK_END","-->" );
                $tpl->assign( "STR_DISABLE","disabled" );
            }
        }
    }
//管理員--表單================================================================
    function admin_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "STR_AI_STATUS_CK1" => "checked",
                                  "STR_AI_STATUS_CK0" => "",
                                  "NOW_AI_ID"  => 0,
                                  "VALUE_AI_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_admin_info","ai","","",0),
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
        if($action_mode=="mod" && !empty($_REQUEST["ai_id"])){
            $tpl->newBlock( "MOD_ADMIN" );
            $sql="select * from ".$cms_cfg['tb_prefix']."_admin_info where ai_id='".$_REQUEST["ai_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("VALUE_AI_ID"  => $row["ai_id"],
                                          "VALUE_AI_SORT"  => $row["ai_sort"],
                                          "TAG_AI_ACCOUNT" => $row["ai_account"],
                                          "VALUE_AI_ACCOUNT" => $row["ai_account"],
                                          "VALUE_AI_PASSWORD" => $row["ai_password"],
                                          "VALUE_AI_NAME" => $row["ai_name"],
                                          "VALUE_AI_ADDRESS" => $row["ai_address"],
                                          "VALUE_AI_TEL" => $row["ai_tel"],
                                          "VALUE_AI_EMAIL" => $row["ai_email"],
                                          "VALUE_AI_CELLPHONE" => $row["ai_cellphone"],
                                          "STR_AI_STATUS_CK1" => ($row["ai_status"]==1)?"checked":"",
                                          "STR_AI_STATUS_CK0" => ($row["ai_status"]==0)?"checked":"",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
                if($row["ai_account"]=="root"){
                    $tpl->newBlock( "ROOT_ADMIN" );
                }else{
                    //$tpl->newBlock( "JS_AUTHORITY");
                    $tpl->newBlock( "NORMAL_ADMIN" );
                    $tpl->newBlock( "NORMAL_ADMIN_AUTHORITY" );
                }
            }else{
                header("location : admin.php?func=ai_list");
            }
        }else{
            //$tpl->newBlock( "JS_AUTHORITY");
            $tpl->newBlock( "ADD_ADMIN" );
            $tpl->newBlock( "NORMAL_ADMIN" );
            $tpl->newBlock( "NORMAL_ADMIN_AUTHORITY" );
        }
        if($cms_cfg["ws_module"]["ws_aboutus"] == 1 ) $tpl->newBlock("ABOUTUS_AUTH");
        if($cms_cfg["ws_module"]["ws_ad"] == 1 ) $tpl->newBlock("AD_AUTH");
        if($cms_cfg["ws_module"]["ws_admin"] == 1 ) $tpl->newBlock("ADMIN_AUTH");
        if($cms_cfg["ws_module"]["ws_blog"] == 1 ) $tpl->newBlock("BLOG_AUTH");
        if($cms_cfg["ws_module"]["ws_bonus"] == 1 ) $tpl->newBlock("BONUS_AUTH");
        if($cms_cfg["ws_module"]["ws_contactus"] == 1 ) $tpl->newBlock("CONTACTUS_AUTH");
        if($cms_cfg["ws_module"]["ws_download"] == 1 ) $tpl->newBlock("DOWNLOAD_AUTH");
        if($cms_cfg["ws_module"]["ws_ebook"] == 1 ) $tpl->newBlock("EBOOK_AUTH");
        if($cms_cfg["ws_module"]["ws_epaper"] == 1 ) $tpl->newBlock("EPAPER_AUTH");
        if($cms_cfg["ws_module"]["ws_faq"] == 1 ) $tpl->newBlock("FAQ_AUTH");
        if($cms_cfg["ws_module"]["ws_forum"] == 1 ) $tpl->newBlock("FORUM_AUTH");
        if($cms_cfg["ws_module"]["ws_gallery"] == 1 ) $tpl->newBlock("GALLERY_AUTH");
        if($cms_cfg["ws_module"]["ws_goodlink"] == 1 ) $tpl->newBlock("GOODLINK_AUTH");
        if($cms_cfg["ws_module"]["ws_guestbook"] == 1 ) $tpl->newBlock("GUESTBOOK_AUTH");
        if($cms_cfg["ws_module"]["ws_inquiry"] == 1 ) $tpl->newBlock("INQUIRY_AUTH");
        if($cms_cfg["ws_module"]["ws_member"] == 1 ) $tpl->newBlock("MEMBER_AUTH");
        if($cms_cfg["ws_module"]["ws_news"] == 1 ) $tpl->newBlock("NEWS_AUTH");
        if($cms_cfg["ws_module"]["ws_order"] == 1 ) $tpl->newBlock("ORDER_AUTH");
        if($cms_cfg["ws_module"]["ws_products"] == 1 ){
            $tpl->newBlock("PRODUCTS_CATE_AUTH");
            $tpl->newBlock("PRODUCTS_AUTH");
        }
        if($cms_cfg["ws_module"]["ws_sysconfig"] == 1 ) $tpl->newBlock("SYSCONFIG_AUTH");
        if($cms_cfg["ws_module"]["ws_sysconfig"] == 1 ) $tpl->newBlock("SYSTOOL_AUTH");
        if($cms_cfg["ws_module"]["ws_seo"] == 1){
            $tpl->newBlock("SEO_AUTH");
            $tpl->newBlock("GOOGLE_SITEMAP_AUTH");
            $tpl->newBlock("GOOGLE_ANALYTICS_AUTH");
        }
        //管理員權限
        $sql="select * from ".$cms_cfg['tb_prefix']."_admin_authority where ai_id='".$row["ai_id"]."'";
        $selectrs = $db->query($sql);
        $rsnum = $db->numRows($selectrs);
        while($row1 = $db->fetch_array($selectrs,1)){
            $tpl->assignGlobal( array( "STR_AA_ABOUTUS_CK"  => ($row1["aa_aboutus"]==1)?"checked":"",
                                       "STR_AA_AD_CK"  => ($row1["aa_ad"]==1)?"checked":"",
                                       "STR_AA_ADMIN_CK"  => ($row1["aa_admin"]==1)?"checked":"",
                                       "STR_AA_BLOG_CK"  => ($row1["aa_blog"]==1)?"checked":"",
                                       "STR_AA_BONUS_CK"  => ($row1["aa_bonus"]==1)?"checked":"",
                                       "STR_AA_CONTACTUS_CK"  => ($row1["aa_contactus"]==1)?"checked":"",
                                       "STR_AA_DOWNLOAD_CK"  => ($row1["aa_download"]==1)?"checked":"",
                                       "STR_AA_EBOOK_CK"  => ($row1["aa_ebook"]==1)?"checked":"",
                                       "STR_AA_EPAPER_CK"  => ($row1["aa_epaper"]==1)?"checked":"",
                                       "STR_AA_FAQ_CK"  => ($row1["aa_faq"]==1)?"checked":"",
                                       "STR_AA_FORUM_CK"  => ($row1["aa_forum"]==1)?"checked":"",
                                       "STR_AA_GALLERY_CK"  => ($row1["aa_gallery"]==1)?"checked":"",
                                       "STR_AA_GOODLINK_CK"  => ($row1["aa_goodlink"]==1)?"checked":"",
                                       "STR_AA_GUESTBOOK_CK"  => ($row1["aa_guestbook"]==1)?"checked":"",
                                       "STR_AA_INQUIRY_CK"  => ($row1["aa_inquiry"]==1)?"checked":"",
                                       "STR_AA_MEMBER_CK"  => ($row1["aa_member"]==1)?"checked":"",
                                       "STR_AA_NEWS_CK"  => ($row1["aa_news"]==1)?"checked":"",
                                       "STR_AA_ORDER_CK"  => ($row1["aa_order"]==1)?"checked":"",
                                       "STR_AA_PRODUCTS_CATE_CK"  => ($row1["aa_products_cate"]==1)?"checked":"",
                                       "STR_AA_PRODUCTS_CK"  => ($row1["aa_products"]==1)?"checked":"",
                                       "STR_AA_SYSCONFIG_CK"  => ($row1["aa_sysconfig"]==1)?"checked":"",
                                       "STR_AA_SYSTOOL_CK"  => ($row1["aa_systool"]==1)?"checked":"",
                                       "STR_AA_SEO_CK"  => ($row1["aa_seo"]==1)?"checked":"",
                                       "STR_AA_GOOGLE_SITEMAP_CK"  => ($row1["aa_google_sitemap"]==1)?"checked":"",
                                       "STR_AA_GOOGLE_ANALYTICS_CK"  => ($row1["aa_google_analytics"]==1)?"checked":"",
            ));
        }
    }
//管理員--資料更新================================================================
    function admin_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $sql="select count(*) as exist_account from ".$cms_cfg['tb_prefix']."_admin_info where ai_account='".$_REQUEST["ai_account"]."' and ai_account!='".$_REQUEST["old_ai_account"]."'";
        $rs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        if($row["exist_account"]>0){
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $_REQUEST["ai_account"].$TPLMSG["ACCOUNT_EXIST"]);
            $goto_url=$cms_cfg["manage_url"]."admin.php?func=ai_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
            $this->goto_target_page($goto_url);
        }else{
            switch ($_REQUEST["action_mode"]){
                case "add":
                        $sql="
                        insert into ".$cms_cfg['tb_prefix']."_admin_info (
                            ai_status,
                            ai_sort,
                            ai_modifydate,
                            ai_account,
                            ai_password,
                            ai_name,
                            ai_address,
                            ai_tel,
                            ai_cellphone,
                            ai_email
                        ) values (
                            ".$_REQUEST["ai_status"].",
                            '".$_REQUEST["ai_sort"]."',
                            '".date("Y-m-d H:i:s")."',
                            '".$_REQUEST["ai_account"]."',
                            '".$_REQUEST["ai_password"]."',
                            '".$_REQUEST["ai_name"]."',
                            '".$_REQUEST["ai_address"]."',
                            '".$_REQUEST["ai_tel"]."',
                            '".$_REQUEST["ai_cellphone"]."',
                            '".$_REQUEST["ai_email"]."'
                        )";
                        $rs = $db->query($sql);
                        $db_msg = $db->report();
                        $this->ai_id=$db->get_insert_id();
                    break;
                case "mod":
                    $sql="
                    update ".$cms_cfg['tb_prefix']."_admin_info set
                        ai_status='".$_REQUEST["ai_status"]."',
                        ai_sort='".$_REQUEST["ai_sort"]."',
                        ai_modifydate='".date("Y-m-d H:i:s")."',
                        ai_password='".$_REQUEST["ai_password"]."',
                        ai_name='".$_REQUEST["ai_name"]."',
                        ai_address='".$_REQUEST["ai_address"]."',
                        ai_tel='".$_REQUEST["ai_tel"]."',
                        ai_cellphone='".$_REQUEST["ai_cellphone"]."',
                        ai_email='".$_REQUEST["ai_email"]."'
                    where ai_id='".$_REQUEST["ai_id"]."'";
                    $rs = $db->query($sql);
                    $db_msg = $db->report();
                    $this->ai_id=$_REQUEST["ai_id"];
                    break;
            }
            if ( $db_msg == "" ) {
                $sql="
                    REPLACE INTO ".$cms_cfg['tb_prefix']."_admin_authority SET
                        ai_id = '".$this->ai_id."',
                        aa_aboutus = '".$_REQUEST["aa_aboutus"]."',
                        aa_ad = '".$_REQUEST["aa_ad"]."',
                        aa_admin = '".$_REQUEST["aa_admin"]."',
                        aa_blog = '".$_REQUEST["aa_blog"]."',
                        aa_bonus = '".$_REQUEST["aa_bonus"]."',
                        aa_contactus = '".$_REQUEST["aa_contactus"]."',
                        aa_download = '".$_REQUEST["aa_download"]."',
                        aa_ebook = '".$_REQUEST["aa_ebook"]."',
                        aa_epaper = '".$_REQUEST["aa_epaper"]."',
                        aa_faq = '".$_REQUEST["aa_faq"]."',
                        aa_forum = '".$_REQUEST["aa_forum"]."',
                        aa_gallery = '".$_REQUEST["aa_gallery"]."',
                        aa_goodlink = '".$_REQUEST["aa_goodlink"]."',
                        aa_guestbook = '".$_REQUEST["aa_guestbook"]."',
                        aa_inquiry = '".$_REQUEST["aa_inquiry"]."',
                        aa_member = '".$_REQUEST["aa_member"]."',
                        aa_news = '".$_REQUEST["aa_news"]."',
                        aa_order = '".$_REQUEST["aa_order"]."',
                        aa_products_cate = '".$_REQUEST["aa_products_cate"]."',
                        aa_products = '".$_REQUEST["aa_products"]."',
                        aa_sysconfig = '".$_REQUEST["aa_sysconfig"]."',
                        aa_systool = '".$_REQUEST["aa_systool"]."',
                        aa_seo = '".$_REQUEST["aa_seo"]."',
                        aa_google_sitemap = '".$_REQUEST["aa_google_sitemap"]."',
                        aa_google_analytics = '".$_REQUEST["aa_google_analytics"]."'
                    ";
                if($_REQUEST["ai_account"]!="root"){
                    $rs = $db->query($sql);
                    $db_msg = $db->report();
                }
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."admin.php?func=ai_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//管理員--刪除--資料刪除可多筆處理================================================================
    function admin_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["ai_id"]){
            $ai_id=array(0=>$_REQUEST["ai_id"]);
        }else{
            $ai_id=$_REQUEST["id"];
        }
        if(!empty($ai_id)){
            $ai_id_str = implode(",",$ai_id);
            //刪除勾選的管理員
            $sql="delete from ".$cms_cfg['tb_prefix']."_admin_info where ai_id in (".$ai_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                //刪除勾選的管理員權限
                $sql="delete from ".$cms_cfg['tb_prefix']."_admin_authority where ai_id in (".$ai_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."admin.php?func=ai_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
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
    function change_status($table_name,$value){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //管理員更改狀態
        if($table_name=="ai"){
            if($_REQUEST["ai_id"]){
                $ai_id=array(0=>$_REQUEST["ai_id"]);
            }else{
                $ai_id=$_REQUEST["id"];
            }
            if(!empty($ai_id)){
                $ai_id_str = implode(",",$ai_id);
                //更改勾選的管理員狀態
                $sql="update ".$cms_cfg['tb_prefix']."_admin_info set ai_status='".$value."' where ai_id in (".$ai_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."admin.php?func=ai_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
    }
    //更改排序值
    function change_sort($table_name){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //管理員更改排序值
        if(!empty($_REQUEST["sort_value"]) && !empty($table_name)){
            if($table_name=="ai"){
                $table=$cms_cfg['tb_prefix']."_admin_info";
            }
            foreach($_REQUEST["id"] as $key => $value){
                $sql="update ".$table." set ".$table_name."_sort='".$_REQUEST["sort_value"][$value]."' where ".$table_name."_id='".$value."'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
            }
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."admin.php?func=".$table_name."_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //資料處理
    function data_processing(){
        switch ($_REQUEST["process_type"]){
            case "del":
                if($_REQUEST["table_name"]=="ai"){
                    $this->admin_del();
                }
                break;
            case "status":
                $this->change_status($_REQUEST["table_name"],$_REQUEST["value"]);
                break;
            case "sort":
                $this->change_sort($_REQUEST["table_name"]);
                break;
        }
    }
}
//ob_end_flush();
?>
