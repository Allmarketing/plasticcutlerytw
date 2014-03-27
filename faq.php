<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$faq = new FAQ;
class FAQ{
    function FAQ(){
        global $db,$cms_cfg,$tpl,$main;
        $this->op_limit=$cms_cfg['faqsop_limit'];
        $this->jp_limit=$cms_cfg['jp_limit'];
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->ps = $cms_cfg['path_separator'];
        if(!empty($_REQUEST["type"])){
            $_REQUEST["func"]="f_".$_REQUEST["type"];
            $this->func_str=$cms_cfg['base_root']."faq/".$_REQUEST["f"];
        }else{
            $_REQUEST["f"]="faq";
            $this->func_str=$cms_cfg['base_root'].$_REQUEST["f"];
        }
        switch($_REQUEST["func"]){
            case "f_list"://問與答列表
                $this->ws_tpl_file = "templates/ws-faq-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->faq_list();
                //page view record --ph_type,ph_type_id,m_id
                $main->pageview_history("fc",$_REQUEST["fc_id"],$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID']);
                $this->ws_tpl_type=1;
                break;
            default:    //問與答列表
                $this->ws_tpl_file = "templates/ws-faq-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->faq_list();
                //page view record --ph_type,ph_type_id,m_id
                $main->pageview_history("fc",$_REQUEST["fc_id"],$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID']);
                $this->ws_tpl_type=1;
                break;
        }
        if($this->ws_tpl_type){
            $main->layer_link();
            $tpl->printToScreen();
        }
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$db,$ws_array,$TPLMSG,$main;
        $tpl = new TemplatePower( $cms_cfg['base_all_tpl'] );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方一般表單
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        $tpl->assignInclude( "AD_H", "templates/ws-fn-ad-h-tpl.html"); //橫式廣告模板
        $tpl->assignInclude( "AD_V", "templates/ws-fn-ad-v-tpl.html"); //直式廣告模板       
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["FAQ"]);
        $tpl->assignGlobal( "TAG_LAYER" , $TPLMSG["FAQ"]);
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["faq"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["faq"]);//左方menu title
        $tpl->assignGlobal( "TAG_FAQ_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["faq"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-faq"); //主要顯示區域的css設定
        if(empty($_GET['fc_id'])){
            $main->layer_link($TPLMSG["FAQ"]);
        }else{
            $main->layer_link($TPLMSG["FAQ"],$cms_cfg["base_root"]."faq.htm");
        }
        $main->header_footer("faq", $TPLMSG["FAQ"]);
        $main->google_code(); //google analystics code , google sitemap code
    }

//問與答--列表================================================================
    function faq_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $cate = $this->left_cate_list();
        //問與答列表
        if($cate){
            $and_str="and fc_id='".$cate['fc_id']."'";
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_faq where f_status='1' ".$and_str." order by f_sort ".$cms_cfg['sort_pos']." ";
        //取得總筆數
        $selectrs = $db->query($sql);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結，且重新組合包含limit的sql語法
        if($this->ws_seo==1  && trim($_REQUEST["f"])!=""){
            //$func_str="faq-flist-".$fc_id;
            $func_str=$this->func_str;
            $sql=$main->pagination_rewrite($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        }else{
            $func_str=$cms_cfg["base_root"]."faq.php?func=f_list&fc_id=".$cate['fc_id'];
            $sql=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        }
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $i=0;
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "FAQ_LIST" );
            $row["f_content"]=$main->content_file_str_replace($row["f_content"]);
            $tpl->assign( array("VALUE_FC_ID"        => $row["fc_id"],
                                "VALUE_F_ID"         => $row["f_id"],
                                "VALUE_F_SUBJECT"    => $row["f_subject"],
                                "VALUE_F_CONTENT"    => $row["f_content"],
                                "VALUE_F_MODIFYDATE" => $row["f_modifydate"],
                                "VALUE_F_SERIAL"     => $i
            ));
        }
    }
    function get_link($row){
        global $cms_cfg;
        if($this->ws_seo==1 ){
            if(trim($row["fc_seo_filename"])==""){
                $cate_link=$cms_cfg["base_root"]."faq/flist-".$row["fc_id"].".htm";
            }else{
                $cate_link=$cms_cfg["base_root"]."faq/".$row["fc_seo_filename"].".htm";
            }
        }else{
            $cate_link=$cms_cfg["base_root"]."faq.php?func=f_list&fc_id=".$row["fc_id"];
        }     
        return $cate_link;
    }
    function left_cate_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //問與答分類
        $sql="select * from ".$cms_cfg['tb_prefix']."_faq_cate where fc_status='1' order by fc_sort ".$cms_cfg['sort_pos']." ";
        $selectrs = $db->query($sql);
        $i=0;
        while($row = $db->fetch_array($selectrs,1)){
            $i++;
            //顯示左方分類
            $tpl->newBlock( "LEFT_CATE_LIST" );
            $tpl->assign( array( "VALUE_CATE_NAME" => $row["fc_subject"],
                                 "VALUE_CATE_LINK"  => $this->get_link($row),
            ));
            if($_REQUEST["fc_id"]==$row["fc_id"] || ($_REQUEST["f"] && $_REQUEST["f"]==$row["fc_seo_filename"])){
                $tpl->assign( "TAG_CURRENT_CLASS"  , "class='current'");
                $main->layer_link($row["fc_subject"]);
                if($this->ws_seo){
                    $meta_array=array("meta_title"       => $row["fc_seo_title"],
                                      "meta_keyword"     => $row["fc_seo_keyword"],
                                      "meta_description" => $row["fc_seo_description"],
                                      "seo_h1"           => (trim($row["fc_seo_h1"])=="")?$row["fc_subject"]:$row["fc_seo_h1"],
                    );
                    if(trim($row["fc_seo_short_desc"])!=""){
                        $tpl->newBlock("FAQ_CATE_SHORT_DESC");
                        $tpl->assign("VALUE_FC_SEO_SHORT_DESC",$main->content_file_str_replace($row["fc_seo_short_desc"]));
                    }
                    $main->header_footer($meta_array);
                }else{
                    $main->header_footer("faq",$TPLMSG["FAQ"]);
                }
                $current_row = $row;
            }
        }     
        return $row;
    }
}
?>
