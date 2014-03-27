<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$sitemap = new SITEMAP;
class SITEMAP{
    function SITEMAP(){
        global $db,$cms_cfg,$tpl,$main;
        //等級大於10啟動seo
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->ws_load_tp("templates/ws-sitemap-tpl.html");
        $this->sitemap_list();
        $main->layer_link();
        $tpl->printToScreen();
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$db,$TPLMSG,$ws_array,$main;
        $tpl = new TemplatePower( $cms_cfg['base_all_tpl'] );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方一般表單
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        $tpl->assignInclude( "AD_H", "templates/ws-fn-ad-h-tpl.html"); //橫式廣告模板
        $tpl->assignInclude( "AD_V", "templates/ws-fn-ad-v-tpl.html"); //直式廣告模板   
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["SITEMAP"]);
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["sitemap"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["sitemap"]);//左方menu title
        $tpl->assignGlobal( "TAG_SITEMAP_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["sitemap"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_IMG" , $ws_array["main_img"]["sitemap"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-sitemap"); //主要顯示區域的css設定
        $main->layer_link($TPLMSG["SITEMAP"]);
        $main->header_footer("sitemap", $TPLMSG["SITEMAP"]);
        $main->left_fix_cate_list(); //顯示產品分類
        $main->google_code(); //google analystics code , google sitemap code
    }

//網站地圖================================================================
    function sitemap_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$ws_array;
        $ext=($this->ws_seo)?".htm":".php";
        ($cms_cfg["ws_module"]["ws_aboutus"])?$tpl->newBlock( "SITEMAP_ABOUTUS" ):"";
        ($cms_cfg["ws_module"]["ws_download"])?$tpl->newBlock( "SITEMAP_DOWNLOAD" ):"";
        ($cms_cfg["ws_module"]["ws_faq"])?$tpl->newBlock( "SITEMAP_FAQ" ):"";
        ($cms_cfg["ws_module"]["ws_news"])?$tpl->newBlock( "SITEMAP_NEWS" ):"";
        ($cms_cfg["ws_module"]["ws_products"])?$tpl->newBlock( "SITEMAP_PRODUCTS" ):"";
        ($cms_cfg["ws_module"]["ws_products_application"])?$tpl->newBlock( "SITEMAP_APPLICATION" ):"";
        ($cms_cfg["ws_module"]["ws_new_product"])?$tpl->newBlock( "SITEMAP_NEW_PRODUCT" ):"";
        ($cms_cfg["ws_module"]["ws_video"])?$tpl->newBlock( "SITEMAP_VIDEO" ):"";
        ($cms_cfg["ws_module"]["ws_ebook"])?$tpl->newBlock( "SITEMAP_EBOOK" ):"";
        ($cms_cfg["ws_module"]["ws_guestbook"])?$tpl->newBlock( "SITEMAP_GUESTBOOK" ):"";
        ($cms_cfg["ws_module"]["ws_stores"])?$tpl->newBlock( "SITEMAP_STORES" ):"";
        ($cms_cfg["ws_module"]["ws_factory"])?$tpl->newBlock( "SITEMAP_FACTORY" ):"";
		//轉換大寫
		foreach($TPLMSG as $key => $value) {
			$TPLMSG[$key] = strtoupper($value);
		}
        $tpl->assignGlobal(array("VALUE_STR_ABOUTUS" =>$TPLMSG["ABOUT_US"],
                                 "VALUE_STR_DOWNLOAD" =>$TPLMSG["DOWNLOAD"],
                                 "VALUE_STR_FAQ" =>$TPLMSG["FAQ"],
                                 "VALUE_STR_VIDEO" =>$TPLMSG["VIDEO"],
                                 "VALUE_STR_EBOOK" =>$TPLMSG["EBOOK"],
                                 "VALUE_STR_GUESTBOOK" =>$TPLMSG["GUESTBOOK"],
                                 "VALUE_STR_STORES" =>$TPLMSG["STORES"],
                                 "VALUE_STR_FACTORY" =>$TPLMSG["FACTORY"],
                                 "VALUE_STR_NEWS" =>$TPLMSG["NEWS"],
                                 "VALUE_STR_PRODUCTS" =>$TPLMSG["PRODUCTS"],
                                 "VALUE_STR_APPLICATION" =>$TPLMSG["APPLICATION"],
                                 "VALUE_STR_NEW_PRODUCT" =>$TPLMSG["PRODUCT_NEW"],
                                 "VALUE_STR_SITEMAP" =>$TPLMSG["SITEMAP"],
                                 "VALUE_STR_CONTACTUS" =>$TPLMSG["CONTACT_US"],
                                 "VALUE_STR_HOME" =>$TPLMSG["HOME"],
                                 "VALUE_ABOUTUS_LINK" =>$cms_cfg["base_root"]."aboutus".$ext,
                                 "VALUE_DOWNLOAD_LINK" =>$cms_cfg["base_root"]."download".$ext,
                                 "VALUE_FAQ_LINK" =>$cms_cfg["base_root"]."faq".$ext,
                                 "VALUE_VIDEO_LINK" =>$cms_cfg["base_root"]."video".$ext,
                                 "VALUE_EBOOK_LINK" =>$cms_cfg["base_root"]."ebook".$ext,
                                 "VALUE_GUESTBOOK_LINK" =>$cms_cfg["base_root"]."guestbook".$ext,
                                 "VALUE_STORES_LINK" =>$cms_cfg["base_root"]."stores".$ext,
//                                 "VALUE_FACTORY_LINK" =>$cms_cfg["base_root"]."factory".$ext,
                                 "VALUE_FACTORY_LINK" =>"#",
                                 "VALUE_NEWS_LINK" =>$cms_cfg["base_root"]."news".$ext,
                                 "VALUE_PRODUCTS_LINK" =>$cms_cfg["base_root"]."products".$ext,
                                 "VALUE_APPLICATION_LINK" =>$cms_cfg["base_root"]."application".$ext,
                                 "VALUE_NEW_PRODUCT_LINK" =>$cms_cfg["base_root"]."new-products.html",
                                 "VALUE_SITEMAP_LINK" =>$cms_cfg["base_root"]."sitemap".$ext,
                                 "VALUE_CONTACTUS_LINK" =>$cms_cfg["base_root"]."contactus".$ext,
        ));

        //主分類
        $sql="select pc_id,pc_parent,pc_name,pc_layer,pc_seo_filename from ".$cms_cfg['tb_prefix']."_products_cate where pc_parent=0 and pc_status='1' order by pc_sort ".$cms_cfg['sort_pos']." ";
        $selectrs = $db->query($sql);
        while($row = $db->fetch_array($selectrs,1)){
            if($this->ws_seo){
                if(trim($row["pc_seo_filename"]) !=""){
                    $pc_link=$cms_cfg["base_root"].$row["pc_seo_filename"].".htm";
                }else{
                    $pc_link=$cms_cfg["base_root"]."category-".$row["pc_id"].".htm";
                }
            }else{
                $pc_link=$cms_cfg["base_root"]."products.php?func=p_list&pc_parent=".$row["pc_id"];
            }
            $tpl->newBlock("PRODUCTS_CATE_MAIN");
            $tpl->assign( array( "VALUE_PC_NAME"  => $row["pc_name"],
                                 "VALUE_PC_LINK"  => $pc_link,
            ));
            //搜尋次分類
            $sql1="select pc_id,pc_name,pc_seo_filename from ".$cms_cfg['tb_prefix']."_products_cate where pc_layer like '".$row["pc_layer"]."-%' and pc_status='1' order by pc_sort ".$cms_cfg['sort_pos']." ";
            $selectrs1 = $db->query($sql1);
            $rsnum1     = $db->numRows($selectrs1);
            if($rsnum1 > 0) {
                $tpl->assign(array(
                        "TAG_UL1"  => "<ul>",
                        "TAG_UL2"  => "</ul>"
                ));
            }
            while($row1 = $db->fetch_array($selectrs1,1)){
                if($this->ws_seo){
                    if(trim($row1["pc_seo_filename"]) !=""){
                        $pc_link1=$cms_cfg["base_root"].$row1["pc_seo_filename"].".htm";
                    }else{
                        $pc_link1=$cms_cfg["base_root"]."category-".$row1["pc_id"].".htm";
                    }
                }else{
                    $pc_link1=$cms_cfg["base_root"]."products.php?func=p_list&pc_parent=".$row1["pc_id"];
                }
                $tpl->newBlock("PRODUCTS_CATE_SUB");
                $tpl->assign( array( "VALUE_PC_NAME"  => $row1["pc_name"],
                                     "VALUE_PC_LINK"  => $pc_link1,
                ));
                //搜尋產品(設定要顯示才執行)
                if($cms_cfg["ws_module"]["ws_sitemap_product"]) {
                    $sql2="select p.p_clone,p.pc_id,p.p_id,p.p_name,p.p_seo_filename,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p.pc_id='".$row1["pc_id"]."' and p.p_status='1' ";
                    if(empty($_SESSION[$cms_cfg["sess_cookie_name"]]["MEMBER_ID"]) && $cms_cfg["ws_module"]["ws_new_product_login"]==1){
                        $sql2 .=  " and p.p_type not in ('1','3','5','7') ";
                    }
                    $sql2 .=  " order by p.p_sort ".$cms_cfg['sort_pos']." ";
                    $selectrs2 = $db->query($sql2);
                    $rsnum2    = $db->numRows($selectrs2);
                    if($rsnum2 > 0) {
                        $tpl->assign(array(
                                "TAG_PRODUCT_UL1"  => "<ul>",
                                "TAG_PRODUCT_UL2"  => "</ul>"
                        ));
                    }
                    $k=0;
                    while($row2 = $db->fetch_array($selectrs2,1)){
						if($row2["p_clone"]){
							$sql_clone="select p.*,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p.p_id > '0' and p_id='".$row2["p_clone"]."'";
							$selectrs_clone = $db->query($sql_clone);
							$row_clone = $db->fetch_array($selectrs_clone,1);
							$row2=$row_clone;
						}
                        $k++;
                        if($this->ws_seo){
                            $dirname=(trim($row2["pc_seo_filename"])!="")?trim($row2["pc_seo_filename"]):"products";
                            if(trim($row2["p_seo_filename"]) !=""){
                                $p_link2=$cms_cfg["base_root"].$dirname."/".$row2["p_seo_filename"].".html";
                            }else{
                                $p_link2=$cms_cfg["base_root"].$dirname."/products-".$row2["p_id"]."-".$row2["pc_id"].".html";
                            }
                        }else{
                            $p_link2=$cms_cfg["base_root"]."products.php?func=p_detail&p_id=".$row2["p_id"]."&pc_parent=".$row2["pc_id"];
                        }
                        $tpl->newBlock("PRODUCTS_LIST");
                        $tpl->assign( array( "VALUE_P_NAME"  => $row2["p_name"],
                                             "VALUE_P_LINK"  => $p_link2,
                        ));
                        $tpl->gotoBlock("PRODUCTS_CATE_SUB");
                    }
                }
                $tpl->gotoBlock("PRODUCTS_CATE_MAIN");
            }
            //搜尋主分類產品
            if($cms_cfg["ws_module"]["ws_sitemap_product"]) {
                $sql3="select p.p_clone,p.pc_id,p.p_id,p.p_name,p.p_seo_filename,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p.pc_id='".$row["pc_id"]."' and p.p_status='1' ";
                if(empty($_SESSION[$cms_cfg["sess_cookie_name"]]["MEMBER_ID"]) && $cms_cfg["ws_module"]["ws_new_product_login"]==1){
                    $sql3 .=  " and p.p_type not in ('1','3','5','7') ";
                }
                $sql3 .=  " order by p.p_sort ".$cms_cfg['sort_pos']." ";
                $selectrs3 = $db->query($sql3);
                $rsnum3    = $db->numRows($selectrs3);
                if($rsnum3 > 0) {
                    $tpl->assign(array(
                            "TAG_UL1"  => "<ul>",
                            "TAG_UL2"  => "</ul>"
                    ));
                }
                while($row3 = $db->fetch_array($selectrs3,1)){
					if($row3["p_clone"]){
						$sql_clone="select p.*,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p.p_id > '0' and p_id='".$row3["p_clone"]."'";
						$selectrs_clone = $db->query($sql_clone);
						$row_clone = $db->fetch_array($selectrs_clone,1);
						$row3=$row_clone;
					}
                    if($this->ws_seo){
                        $dirname=(trim($row3["pc_seo_filename"])!="")?trim($row3["pc_seo_filename"]):"products";
                        if(trim($row3["p_seo_filename"]) !=""){
                            $p_link3=$cms_cfg["base_root"].$dirname."/".$row3["p_seo_filename"].".html";
                        }else{
                            $p_link3=$cms_cfg["base_root"].$dirname."/products-".$row3["p_id"]."-".$row3["pc_id"].".html";
                        }
                    }else{
                        $p_link3=$cms_cfg["base_root"]."products.php?func=p_detail&p_id=".$row3["p_id"]."&pc_parent=".$row3["pc_id"];
                    }
                    $tpl->newBlock("PRODUCTS_MAIN");
                    $tpl->assign( array( "VALUE_P_NAME"  => $row3["p_name"],
                                         "VALUE_P_LINK"  => $p_link3,
                                         "TAG_BR" => ($k==1)?"<br>":"",
                    ));
                }
                $tpl->gotoBlock("PRODUCTS_CATE_MAIN");
            }
        }
        //顯示未分類產品
        if($cms_cfg["ws_module"]["ws_sitemap_product"]) {
            $sql4 = "SELECT p_clone,pc_id,p_id,p_name,p_seo_filename FROM ".$cms_cfg['tb_prefix']."_products WHERE pc_id='0' AND p_status='1' ";
            if(empty($_SESSION[$cms_cfg["sess_cookie_name"]]["MEMBER_ID"]) && $cms_cfg["ws_module"]["ws_new_product_login"]==1){
                $sql4 .=  " and p_type not in ('1','3','5','7') ";
            }
            $sql4 .=  " order by p_sort ".$cms_cfg['sort_pos']." ";
            $selectrs4 = $db->query($sql4);
            while($row4 = $db->fetch_array($selectrs4,1)){
				if($row4["p_clone"]){
					$sql_clone="select p.*,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p.p_id > '0' and p_id='".$row4["p_clone"]."'";
					$selectrs_clone = $db->query($sql_clone);
					$row_clone = $db->fetch_array($selectrs_clone,1);
					$row4=$row_clone;
				}
                if($this->ws_seo){
                    if(trim($row4["p_seo_filename"]) !=""){
                        $p_link4=$cms_cfg["base_root"]."products/".$row4["p_seo_filename"].".html";
                    }else{
                        $p_link4=$cms_cfg["base_root"]."products/products-".$row4["p_id"]."-".$row4["pc_id"].".html";
                    }
                }else{
                    $p_link4=$cms_cfg["base_root"]."products.php?func=p_detail&p_id=".$row4["p_id"]."&pc_parent=".$row4["pc_id"];
                }
                $tpl->newBlock("PRODUCTS_NONE");
                $tpl->assign( array("VALUE_P_NAME"  => $row4["p_name"],
                                    "VALUE_P_LINK"  => $p_link4,
                ));
            }
        }
    }

}
?>