<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$service = new SERVICE;
class SERVICE{
    function SERVICE(){
        global $db,$cms_cfg,$tpl;
        //show page
        $this->ws_tpl_file = "templates/ws-service-term-tpl.html";
        if($_REQUEST["s"]==1){
             $tpl = new TemplatePower( "templates/ws-service-term-single-tpl.html" );
             $tpl->prepare();
        }else{
             $this->ws_load_tp("templates/ws-service-term-tpl.html");
        }
        $this->service_list($_REQUEST["st"]);
        $tpl->printToScreen();
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$db,$TPLMSG,$main,$ws_array;
        $tpl = new TemplatePower( $cms_cfg['base_all_tpl'] );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方一般表單
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        $tpl->assignInclude( "AD_IMG", "templates/ws-fn-ad-image-tpl.html"); //圖片廣告模板
        $tpl->assignInclude( "AD_TXT", "templates/ws-fn-ad-txt-tpl.html"); //文字廣告模板        
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["service"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["service"]);//左方menu title
        $tpl->assignGlobal( "TAG_SERVICE_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["service"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-service"); //主要顯示區域的css設定
        $main->header_footer("");
        $main->login_zone();
        $main->google_code(); //google analystics code , google sitemap code
    }

    //服務條款--列表================================================================
    function service_list($term_type){
        global $db,$tpl,$cms_cfg,$TPLMSG,$ws_array;
        switch($term_type){
            case "service":
                $field="st_service_term";
                break;
            case "shipping":
                $field="st_shipping_term";
                break;
            case "shopping":
                $field="st_shopping_term";
                break;
            case "payment":
                $field="st_payment_term";
                break;
            case "privacy_policy":
                $field="st_privacy_policy";
                break;
            case "contactus":
                $field="st_contactus_term";
                break;
            case "bonus":
                $field="st_bonus_term";
                break;
            default:
                $field="st_service_term";
                break;
        }
        //服務條款
        $sql="select ".$field." from ".$cms_cfg['tb_prefix']."_service_term ";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $row = $db->fetch_array($selectrs,1);
        foreach($ws_array["service_term_left_cate"] as $key =>$value){
            $i++;
            if($field==$key){
                $tpl->assignGlobal( "TAG_MAIN_FUNC" , $value);
                $tpl->assignGlobal( "TAG_LAYER" , $value);
            }
            $tpl->newBlock( "LEFT_CATE_LIST" );
            $key=str_replace("st_","",$key);
            $key=str_replace("_term","",$key);

            $tpl->assign( array( "VALUE_CATE_NAME" => $value,
                                 "VALUE_CATE_LINK"  => "service.php?st=".$key,
            ));
        }
        if($rsnum > 0){
            $tpl->newBlock( "SERVICE_TERM_SHOW" );
            $tpl->assignGlobal( "VALUE_SERVICE_TERM_CONTENT" , $row[$field]);
        }
    }
}
?>
