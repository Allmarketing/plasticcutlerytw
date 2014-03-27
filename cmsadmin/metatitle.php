<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]) || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_seo"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$metatile = new METATITLE;
class METATITLE{
    function METATITLE(){
        global $db,$cms_cfg,$tpl;
        $this->current_class="MT";
        switch($_REQUEST["func"]){
            case "mt_setup":  //各項seo設定
                $this->ws_tpl_file = "templates/ws-manage-meta-title-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_TABTITLE");
                $tpl->newBlock("JS_TINYMCE");
                $this->meta_title_form();
                $this->ws_tpl_type=1;
                break;
            case "mt_replace"://更新資料(update)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->meta_title_replace();
                $this->ws_tpl_type=1;
                break;
            default:    //各項服務說明設定
                $this->ws_tpl_file = "templates/ws-manage-meta-title-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_TABTITLE");
                $this->meta_title_form();
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
        $tpl->assignGlobal("CSS_BLOCK_METATITLE","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }

    ////各項服務說明設定--表單
    function meta_title_form(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $sql="select * from ".$cms_cfg['tb_prefix']."_metatitle ";
        $selectrs = $db->query($sql);
        while($row = $db->fetch_array($selectrs,1)){
            if($cms_cfg["ws_module"]["ws_aboutus"]==1 && $row["mt_name"]=="aboutus"){
                $tpl->newBlock("SEO_ZONE_ABOUTUS_T");
                $tpl->newBlock("SEO_ZONE_ABOUTUS");
                $tpl->assign(array("VALUE_AU_SEO_TITLE" => $row["mt_seo_title"],
                                   "VALUE_AU_SEO_KEYWORD" => $row["mt_seo_keyword"],
                                   "VALUE_AU_SEO_DESCRIPTION" => $row["mt_seo_description"],
                                   "VALUE_AU_SEO_SHORT_DESC" => $row["mt_seo_short_desc"],
                                   "VALUE_AU_SEO_H1" => $row["mt_seo_h1"]
                ));
            }elseif($row["mt_name"]=="video"){
                $tpl->newBlock("SEO_ZONE_VIDEO_T");
                $tpl->newBlock("SEO_ZONE_VIDEO");
                $tpl->assign(array("VALUE_V_SEO_TITLE" => $row["mt_seo_title"],
                                   "VALUE_V_SEO_KEYWORD" => $row["mt_seo_keyword"],
                                   "VALUE_V_SEO_DESCRIPTION" => $row["mt_seo_description"],
                                   "VALUE_V_SEO_SHORT_DESC" => $row["mt_seo_short_desc"],
                                   "VALUE_V_SEO_H1" => $row["mt_seo_h1"]
                ));
            }elseif($cms_cfg["ws_module"]["ws_contactus"]==1 && $row["mt_name"]=="contactus"){
                //聯絡我們seo欄位
                $tpl->newBlock("SEO_ZONE_CONTACTUS_T");
                $tpl->newBlock("SEO_ZONE_CONTACTUS");
                $tpl->assign(array("VALUE_CU_SEO_TITLE" => $row["mt_seo_title"],
                                   "VALUE_CU_SEO_KEYWORD" => $row["mt_seo_keyword"],
                                   "VALUE_CU_SEO_DESCRIPTION" => $row["mt_seo_description"],
                                   "VALUE_CU_SEO_SHORT_DESC" => $row["mt_seo_short_desc"],
                                   "VALUE_CU_SEO_H1" => $row["mt_seo_h1"]
                ));
            }elseif($cms_cfg["ws_module"]["ws_download"]==1 && $row["mt_name"]=="download"){
                //檔案下載seo欄位
                $tpl->newBlock("SEO_ZONE_DOWNLOAD_T");
                $tpl->newBlock("SEO_ZONE_DOWNLOAD");
                $tpl->assign(array("VALUE_D_SEO_TITLE" => $row["mt_seo_title"],
                                   "VALUE_D_SEO_KEYWORD" => $row["mt_seo_keyword"],
                                   "VALUE_D_SEO_DESCRIPTION" => $row["mt_seo_description"],
                                   "VALUE_D_SEO_SHORT_DESC" => $row["mt_seo_short_desc"],
                                   "VALUE_D_SEO_H1" => $row["mt_seo_h1"]
                ));
            }elseif($cms_cfg["ws_module"]["ws_faq"]==1 && $row["mt_name"]=="faq"){
                //FAQ seo欄位
                $tpl->newBlock("SEO_ZONE_FAQ_T");
                $tpl->newBlock("SEO_ZONE_FAQ");
                $tpl->assign(array("VALUE_F_SEO_TITLE" => $row["mt_seo_title"],
                                   "VALUE_F_SEO_KEYWORD" => $row["mt_seo_keyword"],
                                   "VALUE_F_SEO_DESCRIPTION" => $row["mt_seo_description"],
                                   "VALUE_F_SEO_SHORT_DESC" => $row["mt_seo_short_desc"],
                                   "VALUE_F_SEO_H1" => $row["mt_seo_h1"]
                ));
            }elseif($cms_cfg["ws_module"]["ws_news"]==1 && $row["mt_name"]=="news"){
                //最新消息 seo欄位
                $tpl->newBlock("SEO_ZONE_NEWS_T");
                $tpl->newBlock("SEO_ZONE_NEWS");
                $tpl->assign(array("VALUE_N_SEO_TITLE" => $row["mt_seo_title"],
                                   "VALUE_N_SEO_KEYWORD" => $row["mt_seo_keyword"],
                                   "VALUE_N_SEO_DESCRIPTION" => $row["mt_seo_description"],
                                   "VALUE_N_SEO_SHORT_DESC" => $row["mt_seo_short_desc"],
                                   "VALUE_N_SEO_H1" => $row["mt_seo_h1"]
                ));
            }elseif($cms_cfg["ws_module"]["ws_products"]==1 && $row["mt_name"]=="products"){
                //產品主頁 seo欄位
                $tpl->newBlock("SEO_ZONE_PRODUCTS_T");
                $tpl->newBlock("SEO_ZONE_PRODUCTS");
                $tpl->assign(array("VALUE_P_SEO_TITLE" => $row["mt_seo_title"],
                                   "VALUE_P_SEO_KEYWORD" => $row["mt_seo_keyword"],
                                   "VALUE_P_SEO_DESCRIPTION" => $row["mt_seo_description"],
                                   "VALUE_P_SEO_SHORT_DESC" => $row["mt_seo_short_desc"],
                                   "VALUE_P_SEO_H1" => $row["mt_seo_h1"],
                                   "VALUE_P_SEO_CUSTOM" => $row["mt_seo_custom"],
                                   "STR_P_SEO_CUSTOM_STATUS_CK0" => (trim($row["mt_seo_custom"]=="")) ? "checked":"",
                                   "STR_P_SEO_CUSTOM_STATUS_CK1" => (trim($row["mt_seo_custom"]=="")) ? "":"checked",
                                   "STR_P_SEO_CUSTOM_STATUS_DISPLAY" => (trim($row["mt_seo_custom"]=="")) ? "none":"",
                ));
            }elseif($cms_cfg["ws_module"]["ws_products_application"]==1 && $row["mt_name"]=="application"){
                //應用領域 seo欄位
                $tpl->newBlock("SEO_ZONE_APPLICATION_T");
                $tpl->newBlock("SEO_ZONE_APPLICATION");
                $tpl->assign(array("VALUE_PA_SEO_TITLE" => $row["mt_seo_title"],
                                   "VALUE_PA_SEO_KEYWORD" => $row["mt_seo_keyword"],
                                   "VALUE_PA_SEO_DESCRIPTION" => $row["mt_seo_description"],
                                   "VALUE_PA_SEO_SHORT_DESC" => $row["mt_seo_short_desc"],
                                   "VALUE_PA_SEO_H1" => $row["mt_seo_h1"],
                                   "VALUE_PA_SEO_CUSTOM" => $row["mt_seo_custom"],
                                   "STR_PA_SEO_CUSTOM_STATUS_CK0" => (trim($row["mt_seo_custom"]=="")) ? "checked":"",
                                   "STR_PA_SEO_CUSTOM_STATUS_CK1" => (trim($row["mt_seo_custom"]=="")) ? "":"checked",
                                   "STR_PA_SEO_CUSTOM_STATUS_DISPLAY" => (trim($row["mt_seo_custom"]=="")) ? "none":"",
                ));
            }elseif($row["mt_name"]=="sitemap"){
                //網站地圖 seo欄位
                $tpl->newBlock("SEO_ZONE_SITEMAP_T");
                $tpl->newBlock("SEO_ZONE_SITEMAP");
                $tpl->assign(array("VALUE_S_SEO_TITLE" => $row["mt_seo_title"],
                                   "VALUE_S_SEO_KEYWORD" => $row["mt_seo_keyword"],
                                   "VALUE_S_SEO_DESCRIPTION" => $row["mt_seo_description"],
                                   "VALUE_S_SEO_SHORT_DESC" => $row["mt_seo_short_desc"],
                                   "VALUE_S_SEO_H1" => $row["mt_seo_h1"]
                ));
            }
        }
    }
    //資料更新
    function meta_title_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $main->magic_gpc($_REQUEST);
        //關於我們seo欄位更新
        if($cms_cfg["ws_module"]["ws_aboutus"]==1){
            $sql="
                update ".$cms_cfg['tb_prefix']."_metatitle set
                    mt_seo_title='".htmlspecialchars($_REQUEST["au_seo_title"])."',
                    mt_seo_keyword='".htmlspecialchars($_REQUEST["au_seo_keyword"])."',
                    mt_seo_description='".htmlspecialchars($_REQUEST["au_seo_description"])."',
                    mt_seo_short_desc='".$db->quote($main->content_file_str_replace($_REQUEST["au_seo_short_desc"]))."',
                    mt_seo_h1='".htmlspecialchars($_REQUEST["au_seo_h1"])."'
                where mt_name='aboutus'";
            $rs = $db->query($sql);
        }
        //影片seo欄位更新
        if(true){
            $sql="
                update ".$cms_cfg['tb_prefix']."_metatitle set
                    mt_seo_title='".htmlspecialchars($_REQUEST["v_seo_title"])."',
                    mt_seo_keyword='".htmlspecialchars($_REQUEST["v_seo_keyword"])."',
                    mt_seo_description='".htmlspecialchars($_REQUEST["v_seo_description"])."',
                    mt_seo_short_desc='".htmlspecialchars($_REQUEST["v_seo_short_desc"])."',
                    mt_seo_h1='".htmlspecialchars($_REQUEST["v_seo_h1"])."'
                where mt_name='video'";
            $rs = $db->query($sql);
        }        
        //聯絡我們seo欄位更新
        if($cms_cfg["ws_module"]["ws_contactus"]==1){
            $sql="
                update ".$cms_cfg['tb_prefix']."_metatitle set
                    mt_seo_title='".htmlspecialchars($_REQUEST["cu_seo_title"])."',
                    mt_seo_keyword='".htmlspecialchars($_REQUEST["cu_seo_keyword"])."',
                    mt_seo_description='".htmlspecialchars($_REQUEST["cu_seo_description"])."',
                    mt_seo_short_desc='".$db->quote($main->content_file_str_replace($_REQUEST["cu_seo_short_desc"]))."',
                    mt_seo_h1='".htmlspecialchars($_REQUEST["cu_seo_h1"])."'
                where mt_name='contactus'";
            $rs = $db->query($sql);
        }
        //檔案下載seo欄位更新
        if($cms_cfg["ws_module"]["ws_download"]==1){
            $sql="
                update ".$cms_cfg['tb_prefix']."_metatitle set
                    mt_seo_title='".htmlspecialchars($_REQUEST["d_seo_title"])."',
                    mt_seo_keyword='".htmlspecialchars($_REQUEST["d_seo_keyword"])."',
                    mt_seo_description='".htmlspecialchars($_REQUEST["d_seo_description"])."',
                    mt_seo_short_desc='".htmlspecialchars($_REQUEST["d_seo_short_desc"])."',
                    mt_seo_h1='".htmlspecialchars($_REQUEST["d_seo_h1"])."'
                where mt_name='download'";
            $rs = $db->query($sql);
        }
        //FAQ seo欄位更新
        if($cms_cfg["ws_module"]["ws_faq"]==1){
            $sql="
                update ".$cms_cfg['tb_prefix']."_metatitle set
                    mt_seo_title='".htmlspecialchars($_REQUEST["f_seo_title"])."',
                    mt_seo_keyword='".htmlspecialchars($_REQUEST["f_seo_keyword"])."',
                    mt_seo_description='".htmlspecialchars($_REQUEST["f_seo_description"])."',
                    mt_seo_short_desc='".htmlspecialchars($_REQUEST["f_seo_short_desc"])."',
                    mt_seo_h1='".htmlspecialchars($_REQUEST["f_seo_h1"])."'
                where mt_name='faq'";
            $rs = $db->query($sql);
        }
        //最新消息 seo欄位更新
        if($cms_cfg["ws_module"]["ws_news"]==1){
            $sql="
                update ".$cms_cfg['tb_prefix']."_metatitle set
                    mt_seo_title='".htmlspecialchars($_REQUEST["n_seo_title"])."',
                    mt_seo_keyword='".htmlspecialchars($_REQUEST["n_seo_keyword"])."',
                    mt_seo_description='".htmlspecialchars($_REQUEST["n_seo_description"])."',
                    mt_seo_short_desc='".htmlspecialchars($_REQUEST["n_seo_short_desc"])."',
                    mt_seo_h1='".htmlspecialchars($_REQUEST["n_seo_h1"])."'
                where mt_name='news'";
            $rs = $db->query($sql);
        }
        //產品主頁 seo欄位更新
        if($cms_cfg["ws_module"]["ws_products"]==1){
            $sql="
                update ".$cms_cfg['tb_prefix']."_metatitle set
                    mt_seo_title='".htmlspecialchars($_REQUEST["p_seo_title"])."',
                    mt_seo_keyword='".htmlspecialchars($_REQUEST["p_seo_keyword"])."',
                    mt_seo_description='".htmlspecialchars($_REQUEST["p_seo_description"])."',
                    mt_seo_short_desc='".$db->quote($main->content_file_str_replace($_REQUEST["p_seo_short_desc"]))."',
                    mt_seo_h1='".htmlspecialchars($_REQUEST["p_seo_h1"])."',
                    mt_seo_custom='".$db->quote($main->content_file_str_replace($_REQUEST["p_mt_seo_custom"]))."'
                where mt_name='products'";
            $rs = $db->query($sql);
        }
        //產品應用領域 seo欄位更新
        if($cms_cfg["ws_module"]["ws_products_application"]==1){
            $sql="
                update ".$cms_cfg['tb_prefix']."_metatitle set
                    mt_seo_title='".htmlspecialchars($_REQUEST["pa_seo_title"])."',
                    mt_seo_keyword='".htmlspecialchars($_REQUEST["pa_seo_keyword"])."',
                    mt_seo_description='".htmlspecialchars($_REQUEST["pa_seo_description"])."',
                    mt_seo_short_desc='".$_REQUEST["pa_seo_short_desc"]."',
                    mt_seo_h1='".htmlspecialchars($_REQUEST["pa_seo_h1"])."',
                    mt_seo_custom='".$_REQUEST["pa_mt_seo_custom"]."'
                where mt_name='application'";
            $rs = $db->query($sql);
        }
        //網站地圖 seo欄位更新
        $sql="
            update ".$cms_cfg['tb_prefix']."_metatitle set
                mt_seo_title='".htmlspecialchars($_REQUEST["s_seo_title"])."',
                mt_seo_keyword='".htmlspecialchars($_REQUEST["s_seo_keyword"])."',
                mt_seo_description='".htmlspecialchars($_REQUEST["s_seo_description"])."',
                mt_seo_short_desc='".$db->quote($main->content_file_str_replace($_REQUEST["s_seo_short_desc"]))."',
                mt_seo_h1='".htmlspecialchars($_REQUEST["s_seo_h1"])."'
            where mt_name='sitemap'";
        $rs = $db->query($sql);
        $db_msg = $db->report();
        if ( $db_msg == "" ) {
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$cms_cfg["manage_url"]."metatitle.php?func=mt_setup";
            $this->goto_target_page($goto_url);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
    //顯示訊息並重新導向
    function goto_target_page($url,$sec=0){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }
}
//ob_end_flush();
?>
