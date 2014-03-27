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
$google = new GOOGLE;
class GOOGLE{
    function GOOGLE(){
        global $db,$cms_cfg,$tpl;
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        switch($_REQUEST["func"]){
            case "ga"://設定 google analytics code
                if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]) || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_google_analytics"]==0){
                    header("location: /");
                    exit;
                }
                $this->current_class="GA";
                $this->ws_tpl_file = "templates/ws-manage-google-setup-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $this->ga_form();
                $this->ws_tpl_type=1;
                break;
            case "gs"://設定 google sitemap code
                if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]) || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_google_sitemap"]==0){
                    header("location: /");
                    exit;
                }
                $this->current_class="GS";
                $this->ws_tpl_file = "templates/ws-manage-google-setup-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->gs_form();
                $this->ws_tpl_type=1;
                break;
            case "gs_make_file"://產生sitemap檔案
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->current_class="GS";
                $this->gs_make_file();
                $this->ws_tpl_type=1;
                break;
            case "g_replace"://更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->google_replace();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
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
//GA表單================================================================
    function ga_form(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $sql="select sc_ga_code from ".$cms_cfg['tb_prefix']."_system_config";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $rsnum    = $db->numRows($selectrs);
        $tpl->newBlock("GA_CODE_ZONE");
        $tpl->assignGlobal( array("VALUE_SC_GA_CODE" => $row["sc_ga_code"],
                                  "VALUE_TYPE" => "ga",
                                  "MSG_MODE" => "修改"
        ));
    }
//GS表單================================================================
    function gs_form(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $sql="select sc_gs_code,sc_gs_datetime,sc_gs_filename from ".$cms_cfg['tb_prefix']."_system_config";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $rsnum    = $db->numRows($selectrs);
        $tpl->newBlock("GS_CODE_ZONE");
        $tpl->assignGlobal( array("VALUE_SC_GS_CODE" => $row["sc_gs_code"],
                                  "VALUE_SC_GS_FILENAME" => (trim($row["sc_gs_filename"])=="")?"":"<a href='".$cms_cfg['base_url'].$row["sc_gs_filename"]."' target='_blank'>".$cms_cfg['base_url'].$row["sc_gs_filename"]."</a> &nbsp;&nbsp;".$row["sc_gs_datetime"],
                                  "VALUE_TYPE" => "gs",
                                  "MSG_MODE" => "修改"
        ));
    }
    function gs_make_file(){
        global $db,$cms_cfg,$tpl,$TPLMSG;
        $ext=($this->ws_seo)?".html":".php";
        $func_array=array ( "aboutus"=>"aboutus".$ext,
                            "download"=>"download".$ext,
                            "faq"=>"faq".$ext,
                            "news"=>"news".$ext,
                            "products"=>"products".$ext,
                            "new_product" =>"new-products".$ext
        );
        $date_str=date("Y-m-d");
        $xml_str="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.google.com/schemas/sitemap/0.84 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">
                <url>
                  <loc>".$cms_cfg["base_url"]."</loc>
                  <lastmod>".$date_str."</lastmod>
                  <changefreq>daily</changefreq>
                  <priority>1.00</priority>
                </url>
                ";
        //主功能連結
        foreach($func_array as $key => $value){
            if($cms_cfg["ws_module"]["ws_".$key]){
                $xml_str .="
                 <url>
                  <loc>".$cms_cfg["base_url"].$value."</loc>
                  <lastmod>".$date_str."</lastmod>
                  <changefreq>daily</changefreq>
                  <priority>1.00</priority>
                 </url>";
            }
        }
        //產品分類連結
        $sql1="select pc_id,pc_parent,pc_name,pc_layer,pc_seo_filename from ".$cms_cfg['tb_prefix']."_products_cate where pc_status='1' order by pc_sort ";
        $selectrs1 = $db->query($sql1);
        while($row1 = $db->fetch_array($selectrs1,1)){
            if($this->ws_seo){
                if(trim($row1["pc_seo_filename"]) !=""){
                    $pc_link=$cms_cfg["base_url"].$row1["pc_seo_filename"].".htm";
                }else{
                    $pc_link=$cms_cfg["base_url"]."category-".$row1["pc_id"].".htm";
                }
            }else{
                $pc_link=$cms_cfg["base_url"]."products.php?func=p_list&pc_parent=".$row1["pc_id"];
            }
            $xml_str .="
             <url>
              <loc>".$pc_link."</loc>
              <lastmod>".$date_str."</lastmod>
              <changefreq>daily</changefreq>
              <priority>0.80</priority>
             </url>";
            //產品連結
            $sql2="select p.pc_id,p.p_id,p.p_name,p.p_seo_filename,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p.pc_id='".$row1["pc_id"]."' and p.p_status='1' ";
            $sql2 .=  " order by p.p_sort ";
            $selectrs2 = $db->query($sql2);
            $k=0;
            while($row2 = $db->fetch_array($selectrs2,1)){
                $k++;
                if($this->ws_seo){
                    $dirname=(trim($row2["pc_seo_filename"])!="")?trim($row2["pc_seo_filename"]):"products";
                    if(trim($row2["p_seo_filename"]) !=""){
                        $p_link=$cms_cfg["base_url"].$dirname."/".$row2["p_seo_filename"].".html";
                    }else{
                        $p_link=$cms_cfg["base_url"].$dirname."/products-".$row2["p_id"]."-".$row2["pc_id"].".html";
                    }
                }else{
                    $p_link=$cms_cfg["base_url"]."products.php?func=p_detail&p_id=".$row2["p_id"]."&pc_parent=".$row2["pc_id"];
                }
                $xml_str .="
                 <url>
                  <loc>".$p_link."</loc>
                  <lastmod>".$date_str."</lastmod>
                  <changefreq>daily</changefreq>
                  <priority>0.50</priority>
                 </url>";
            }
        }
        $xml_str .= "
        </urlset>";
        $xml_file = fopen('../sitemap.xml','w');
        fwrite($xml_file, $xml_str);
        fclose($xml_file);
        if(is_file("../sitemap.xml")){
            $sql="
                update ".$cms_cfg['tb_prefix']."_system_config set
                    sc_gs_datetime='".date("Y-m-d H:i:s")."',
                    sc_gs_filename='sitemap.xml'
                where sc_id='1'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
        }
        if ( $db_msg == "" ) {
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$cms_cfg["manage_url"]."google.php?func=gs";
            $this->goto_target_page($goto_url);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
//--資料更新================================================================
    function google_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        switch ($_REQUEST["type"]){
            case "ga":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_system_config set
                        sc_ga_code='".$_REQUEST["sc_ga_code"]."'
                    where sc_id='1'";
                break;
            case "gs":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_system_config set
                        sc_gs_code='".$_REQUEST["sc_gs_code"]."'
                    where sc_id='1'";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."google.php?func=".$_REQUEST["type"];
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
}
//ob_end_flush();
?>
