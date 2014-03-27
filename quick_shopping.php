<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$products = new PRODUCTS;
class PRODUCTS{
    function PRODUCTS(){
        global $db,$cms_cfg,$tpl,$main;
        $this->one_page_limit=($_SESSION[$cms_cfg['sess_cookie_name']]["sc_one_page_limit"])?$_SESSION[$cms_cfg['sess_cookie_name']]["sc_one_page_limit"]:$cms_cfg["one_page_limit"];
        //等級大於10啟動seo
        $this->ws_seo=($cms_cfg["ws_level"]>10)?1:0;
        $this->ws_tpl_file = "templates/ws-quick-shopping-tpl.html";
        $this->ws_load_tp($this->ws_tpl_file);
        $this->products_list();
        //有廣告模組才啟動廣告
        if($cms_cfg["ws_module"]["ws_ad"]==1) $main->ad_list(0);
        $tpl->printToScreen();
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$ws_array,$db,$TPLMSG,$main;
        $tpl = new TemplatePower( $cms_cfg['base_all_tpl'] );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        //$tpl->assignInclude( "TOP_MENU", $cms_cfg['base_top_menu_tpl']);// 功能列表
        $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方一般表單
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        //$tpl->assignInclude( "FOOTER", $cms_cfg['base_footer_tpl']); //尾檔功能列表
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG['CART_QUICK_SHOPPING']);
        $tpl->assignGlobal( "TAG_LAYER" , $TPLMSG['CART_QUICK_SHOPPING']);
        $main->header_footer("products",$TPLMSG['CART_QUICK_SHOPPING']);
        $main->login_zone();
        $main->left_fix_cate_list();
        $main->google_code(); //google analystics code , google sitemap code
    }
    //產品搜尋
    function products_list(){
        global $db,$tpl,$cms_cfg,$ws_array,$TPLMSG,$main;
        //商品列表
        $sql="select p.*,pc.pc_name,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p.p_status='1' order by p.pc_id ";
        //取得總筆數
        $selectrs = $db->query($sql);
        $total_records    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("MSG_NAME"  => $TPLMSG['NAME'],
                                  "MSG_PRODUCTS"  => $TPLMSG['PRODUCTS'],
                                  "MSG_AMOUNT"  => $TPLMSG['AMOUNT'],
                                  "MSG_SERIAL"  => $TPLMSG['SERIAL'],
                                  "MSG_PRODUCT_SPECIAL_PRICE" => $TPLMSG['PRODUCT_SPECIAL_PRICE'],
                                  "CART_ADD" => $TPLMSG['CART_ADD'].$TPLMSG['CART_SHOPPING'],
                                  "VALUE_TOTAL_BOX" => $rsnum,
        ));
        //產品列表
        $i=0;
        $pc_name="";
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "PRODUCTS_LIST" );
            if($pc_name != $row["pc_name"]){
                $tpl->newBlock( "PRODUCTS_CATE_NAME" );
                $tpl->assign("VALUE_PC_NAME"  , $row["pc_name"]);
                $pc_name=$row["pc_name"];
                $tpl->gotoBlock("PRODUCTS_LIST");
            }
            $tpl->assign( array("VALUE_PC_ID"       => $row["pc_id"],
                                "VALUE_P_ID"        => $row["p_id"],
                                "VALUE_P_LINK"      => $cms_cfg['base_root'].$row['pc_seo_filename']."/".$row['p_seo_filename'].".html",
                                "VALUE_P_NAME"      => $row["p_name"],
                                "VALUE_P_SMALL_IMG" => $row["p_small_img"]?$cms_cfg['file_root'].$row["p_small_img"]:$cms_cfg['default_preview_pic'],
                                "VALUE_P_SERIAL"    => $i,
                                "VALUE_PC_NAME"     => ($row["pc_name"])?$row["pc_name"]:$TPLMSG['NO_CATE'],
                                "VALUE_P_SPECIAL_PRICE" => $row["p_list_price"],
            ));
            //購物車
            //會員有登入改為顯示折扣價
            if(!empty($this->discount)){
                $tpl->assign("MSG_SPECIAL_PRICE",$TPLMSG["PRODUCT_DISCOUNT_PRICE"]);
                if($this->discount!=100){ //無折扣也不顯示
                    $discount_price=floor($_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]/100*$row["p_list_price"]);
                    $tpl->assign("VALUE_P_SPECIAL_PRICE",$discount_price);
                }
            }
            $tpl->gotoBlock("_ROOT");
        }
        if($i==0){
            $tpl->assignGlobal("MSG_NO_DATA",$TPLMSG['NO_DATA']);
        }else{
            $tpl->newBlock( "PAGE_DATA_SHOW" );
            $tpl->assign( array("VALUE_TOTAL_RECORDS"  => $page["total_records"],
                                "VALUE_TOTAL_PAGES"  => $page["total_pages"],
                                "VALUE_PAGES_STR"  => $page["pages_str"],
                                "VALUE_PAGES_LIMIT"=>$this->one_page_limit
            ));
            if($page["bj_page"]){
                $tpl->newBlock( "PAGE_BACK_SHOW" );
                $tpl->assign( "VALUE_PAGES_BACK"  , $page["bj_page"]);
                $tpl->gotoBlock("PAGE_DATA_SHOW");
            }
            if($page["nj_page"]){
                $tpl->newBlock( "PAGE_NEXT_SHOW" );
                $tpl->assign( "VALUE_PAGES_NEXT"  , $page["nj_page"]);
                $tpl->gotoBlock("PAGE_DATA_SHOW");
            }
        }
    }
    //顯示訊息並重新導向
    function goto_target_page($url,$sec=2){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }

}
?>