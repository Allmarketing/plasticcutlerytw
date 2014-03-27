<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$application = new APPLICATON;
class APPLICATON{
    function APPLICATON(){
        global $db,$cms_cfg,$tpl,$main,$TPLMSG;
        $this->op_limit=($_SESSION[$cms_cfg['sess_cookie_name']]["sc_one_page_limit"])?$_SESSION[$cms_cfg['sess_cookie_name']]["sc_one_page_limit"]:$cms_cfg["op_limit"];
        $this->jp_limit=$cms_cfg["jp_limit"];
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->ps = $cms_cfg['path_separator'];
        $this->ws_tpl_file = "templates/ws-products-tpl.html";
        $this->ws_load_tp($this->ws_tpl_file);
        $this->products_list();
        $this->ws_tpl_type=1;
        if($this->ws_tpl_type){
            $main->layer_link();
            $tpl->printToScreen();
        }
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$ws_array,$db,$TPLMSG,$main;
        $ext=($this->ws_seo)?"htm":"php";
        $this->top_layer_link="<a href='".$cms_cfg['base_root']."application.".$ext."'>".$TPLMSG["APPLICATION"]."</a>";
        $tpl = new TemplatePower( $cms_cfg['base_all_tpl'] );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方一般表單
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        $tpl->assignInclude( "AD_H", "templates/ws-fn-ad-h-tpl.html"); //橫式廣告模板
        $tpl->assignInclude( "AD_V", "templates/ws-fn-ad-v-tpl.html"); //直式廣告模板        
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["APPLICATION"]);
        $tpl->assignGlobal( "TAG_LAYER" , $this->top_layer_link);
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["application"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["application"]);//左方menu title
        $tpl->assignGlobal( "TAG_APPLICATION_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["application"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-products"); //主要顯示區域的css設定
        $main->google_code(); //google analystics code , google sitemap code
        $this->left_fix_cate_list();
    }

//產品--列表================================================================
    function products_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //顯示資訊
        $show_style_str_pc="SHOW_STYLE_PC1";
        $show_style_str_pc_desc="SHOW_STYLE_PC1_DESC";
        $show_style_str_p="SHOW_STYLE_P1";
        $show_style_str_p_desc="SHOW_STYLE_P1_DESC";
 
        if(!$_GET['f'] && !$_GET['pa_id']){
            $main->layer_link($TPLMSG['APPLICATION']);
            $dirname="application";
            //顯示產品主頁 SEO H1 標題
            $sql = "select * from ".$cms_cfg['tb_prefix']."_metatitle where mt_name ='application' ";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $meta_array=array("meta_title"=>$row["mt_seo_title"],
                              "meta_keyword"=>$row["mt_seo_keyword"],
                              "meta_description"=>$row["mt_seo_description"],
            );
            $seo_H1=(trim($row["mt_seo_h1"]))?$row["mt_seo_h1"]:$TPLMSG["APPLICATION"];
            $main->header_footer($meta_array,$seo_H1);
            //顯示產品主頁自訂頁
            if(trim($row["mt_seo_custom"])) {
                    $row["mt_seo_custom"]=preg_replace("/src=\"([^>]+)upload_files/","src=\"".$cms_cfg["file_root"]."upload_files",$row["mt_seo_custom"]);
                    $tpl->newBlock("PRODUCTS_CATE_CUSTOM");
                    $tpl->assign("VALUE_PC_CUSTOM",$row["mt_seo_custom"]);
                    $custom=1;
            }else{
                //顯示產品主頁SEO簡述
                if(trim($row["mt_seo_short_desc"])){
                    $row["mt_seo_short_desc"]=preg_replace("/src=\"([^>]+)upload_files/","src=\"".$cms_cfg["file_root"]."upload_files",$row["mt_seo_short_desc"]);
                    $tpl->newBlock("PRODUCTS_CATE_SHORT_DESC");
                    $tpl->assign("VALUE_PC_SHORT_DESC",$row["mt_seo_short_desc"]);
                }
            }           
            //應用領域列表
            $sql="select * from ".$cms_cfg['tb_prefix']."_products_application where pa_status='1' order by pa_sort ".$cms_cfg['sort_pos']." ";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $i=0;
            while($row = $db->fetch_array($selectrs,1)){
                $pa_link = $this->get_link($row);
                //收集第二頁以後pc_name 做為 meta title
                if(!empty($_REQUEST["nowp"]) && $i<3){
                    $meta_title .=$row["pa_name"];
                }
                if(!empty($_REQUEST["nowp"]) && $i<6){
                    $meta_description .=$row["pa_name"];
                }
                $pa_img=(trim($row["pa_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["pa_small_img"];
                $i++;
                $tpl->newBlock( $show_style_str_pc."_APP" );
                $tpl->assign( array( "VALUE_PC_NAME"  => $row["pa_name"],
                                     "VALUE_PC_NAME_ALIAS" =>$row["pa_name_alias"],
                                     "VALUE_PC_LINK"  => $pa_link,
                                     "VALUE_PC_ID" => $row["pa_id"],
                                     "VALUE_PC_CATE_IMG" => $pa_img,
                                     "VALUE_PC_SERIAL" => $i,
                ));
                if(is_file($_SERVER['DOCUMENT_ROOT'].$pa_img)){
                    $dimensions = $main->resizeto($pa_img,$cms_cfg['small_img_width'],$cms_cfg['small_img_height']);
                }
                $tpl->assign("VALUE_PC_SMALL_IMG_W",$dimensions["width"]);
                $tpl->assign("VALUE_PC_SMALL_IMG_H",$dimensions["height"]);
                if($row_num){
                    if($i%$row_num==0){
                        $tpl->assign("TAG_PRODUCTS_CATE_TRTD","</tr><tr>");
                    }
                }
                if($row["pa_id"]==$_GET["pa_id"]){
                    $tpl->assignGlobal("TAG_NOW_CATE",$row["pa_name"]);
                }
            }            
        }else{
           //應用領域內頁
           //先取得應用領域的info
            $sql = "select * from ".$cms_cfg['tb_prefix']."_products_application where ";
            if($_GET['f']){
                $sql .= " pa_seo_filename='".$_GET['f']."'";
            }else{
                $sql .= " pa_id = '".$_GET['pa_id']."'";
            }
            $res = $db->query($sql);
            if($db->numRows($res)){
                $app_row = $db->fetch_array($res,1);
                //設定seo
                $meta_array=array("meta_title"=>$app_row["pa_seo_title"],
                                  "meta_keyword"=>$app_row["pa_seo_keyword"],
                                  "meta_description"=>$app_row["pa_seo_description"],
                );
                $seo_H1=(trim($app_row["pa_seo_h1"]))?$app_row["pa_seo_h1"]:$app_row["pa_name"];
                $main->header_footer($meta_array,$seo_H1);
                //顯示產品主頁SEO簡述
                if(trim($app_row["pa_seo_short_desc"])){
                    $app_row["pa_seo_short_desc"]=preg_replace("/src=\"([^>]+)upload_files/","src=\"".$cms_cfg["file_root"]."upload_files",$app_row["pa_seo_short_desc"]);
                    $tpl->newBlock("PRODUCTS_CATE_SHORT_DESC");
                    $tpl->assign("VALUE_PC_SHORT_DESC",$app_row["pa_seo_short_desc"]);
                }                 
                //layer link
                $main->layer_link($TPLMSG['APPLICATION'],$cms_cfg['base_root']."application.htm")->layer_link($app_row['pa_name']);
                if($cms_cfg['ws_module']['ws_application_cates']){  //應用領域產品分類
                    //分類列表
                    $sql="select * from ".$cms_cfg['tb_prefix']."_products_cate where pc_id in (select pc_id from ".$cms_cfg['tb_prefix']."_products_cate_application_map where pa_id='".$app_row['pa_id']."' and checked='1')";
                }elseif($cms_cfg['ws_module']['ws_application_products']){ //應用領域產品
                    //產品列表
                    $sql="select p.pc_id,p.p_id,p.p_name,p.p_name_alias,p.p_serial,p.p_small_img,p.p_seo_filename,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p.p_status='1' ";

                    if(empty($_SESSION[$cms_cfg["sess_cookie_name"]]["MEMBER_ID"]) && $cms_cfg["ws_module"]["ws_new_product_login"]==1){
                        $sql .=  " and p.p_type not in ('1','3','5','7') ";
                    }
                    //附加條件
                    $and_str = " and p.p_id in (select p_id from ".$cms_cfg['tb_prefix']."_products_application_map where pa_id='".$app_row['pa_id']."' and checked='1') order by p.p_up_sort desc,p.p_sort ".$cms_cfg['sort_pos'].",p.p_modifydate desc";

                    $sql .= $and_str;       
                }

                //取得總筆數
                $selectrs = $db->query($sql);
                $total_records    = $db->numRows($selectrs);
                //取得分頁連結且重新組合包含limit的sql語法
                if($this->ws_seo==1 && trim($_GET["f"])!=""){
                    $func_str=$_GET["f"];
                    $sql=$main->pagination_rewrite($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql,!$custom);
                }else{
                    $func_str="application.php?pa_id=".$app_row['pa_id'];
                    $sql=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql,!$custom);
                }
                $selectrs = $db->query($sql);
                $rsnum    = $db->numRows($selectrs);
                $tpl->assignGlobal( array("MSG_NAME"  => $TPLMSG['NAME'],
                                          "MSG_SUBJECT"  => $TPLMSG['SUBJECT'],
                                          "MSG_MODE" => $TPLMSG['MANAGE_CATE'],
                                          "MSG_MODIFY" => $TPLMSG['MODIFY'],
                                          "MSG_CATE" => $TPLMSG['CATE'],
                                          "MSG_SPECIAL_PRICE" => $TPLMSG['PRODUCT_SPECIAL_PRICE']
                ));

                //產品列表------------------------
                $j=0;
                $k=$page["start_serial"];
                while ( $row = $db->fetch_array($selectrs,1) ) {
                    $j++;
                    $k++;
                    if($cms_cfg['ws_module']['ws_application_cates']){
                        $tpl->newBlock( $show_style_str_pc."_APP" );
                        $pc_link = $this->get_link($row);                        
                        $pc_img=(trim($row["pc_cate_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["pc_cate_img"];
                        $tpl->assign( array( "VALUE_PC_NAME"  => $row["pc_name"],
                                             "VALUE_PC_NAME_ALIAS" =>$row["pc_name_alias"],
                                             "VALUE_PC_LINK"  => $pc_link,
                                             "VALUE_PC_ID" => $row["pc_id"],
                                             "VALUE_PC_SHOW_STYLE" => $row["pc_show_style"],
                                             "VALUE_PC_CATE_IMG" => $pc_img,
                        ));
                        if(is_file($_SERVER['DOCUMENT_ROOT'].$pc_img)){
                            $dimensions = $main->resizeto($pc_img,$cms_cfg['small_img_width'],$cms_cfg['small_img_height']);
                        }
                        $tpl->assign("VALUE_PC_SMALL_IMG_W",$dimensions["width"]);
                        $tpl->assign("VALUE_PC_SMALL_IMG_H",$dimensions["height"]);                        
                    }elseif($cms_cfg['ws_module']['ws_application_products']){
                        $p_link = $this->get_link($row,true);                        
                        $p_img=(trim($row["p_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["p_small_img"];
                        $tpl->newBlock( $show_style_str_p."_APP" );
                        $tpl->assign( array("VALUE_P_NAME" =>$row["p_name"],
                                            "VALUE_P_ID" =>$row["p_id"],
                                            "VALUE_P_NAME_ALIAS" =>$row["p_name_alias"],
                                            "VALUE_P_LINK"  => $p_link,
                                            "VALUE_P_SMALL_IMG" => $p_img,
                                            "VALUE_P_SPECIAL_PRICE" => $row["p_special_price"],
                                            "VALUE_P_SERIAL" => $row["p_serial"],
                                            "VALUE_P_NO" => $k,
                        ));
                        if(is_file($_SERVER['DOCUMENT_ROOT'].$p_img)){
                            $dimensions = $main->resizeto($p_img,$cms_cfg['small_img_width'],$cms_cfg['small_img_height']);
                        }
                        $tpl->assign("VALUE_P_SMALL_IMG_W",$dimensions["width"]);
                        $tpl->assign("VALUE_P_SMALL_IMG_H",$dimensions["height"]);
                    }
                }
            }else{//找不到項目
                include_once("404.htm");
                exit();                
            }
        }
    }
    
    function left_fix_cate_list(){
        global $tpl,$db,$main,$cms_cfg;
        $sql="select * from ".$cms_cfg['tb_prefix']."_products_application where pa_status='1' order by pa_sort ".$cms_cfg['sort_pos'];
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        if($rsnum > 0 ){
            //顯示左方分類
            while($row = $db->fetch_array($selectrs,1)){
                $pa_link = $this->get_link($row);
                $tpl->newBlock( "LEFT_CATE_LIST" );
                $tpl->assign( array( "VALUE_CATE_NAME" => $row["pa_name"],
                                     "VALUE_CATE_LINK"  => $pa_link,
                                     "TAG_CURRENT_CLASS" => (($_GET['f'] && $_GET['f']==$row['pa_seo_filename'])||($_GET['pa_id']==$row['pa_id']))?"class='current'":"",
                ));
            }
        }
    }
    /*由$row取得該筆記錄的url
     */
    function get_link(&$row,$is_product=false){
        global $cms_cfg;
        $link = "";
        if($is_product){
            if($this->ws_seo){
                $dirname=(trim($row["pc_seo_filename"]))?$row["pc_seo_filename"]:"products";
                if(trim($row["p_seo_filename"]) !=""){
                    $link=$cms_cfg["base_root"].$dirname."/".$row["p_seo_filename"].".html";
                }else{
                    $link=$cms_cfg["base_root"].$dirname."/"."products-".$row["p_id"]."-".$row["pc_id"].".html";
                }
            }else{
                $link=$cms_cfg["base_root"]."products.php?func=p_detail&p_id=".$row["p_id"]."&pc_parent=".$row["pc_id"];
            }      
        }else{
            if($this->ws_seo){
                if($row['pc_id']){
                    if(trim($row["pc_seo_filename"]) !=""){
                        $link=$cms_cfg["base_root"].$row["pc_seo_filename"].".htm";
                    }else{
                        $link=$cms_cfg["base_root"]."category-".$row["pc_id"].".htm";
                    }
                }else{
                    if(trim($row["pa_seo_filename"]) !=""){
                        $link=$cms_cfg["base_root"].'application/'.$row["pa_seo_filename"].".htm";
                    }else{
                        $link=$cms_cfg["base_root"]."application-".$row["pa_id"].".htm";
                    }
                }
            }else{
                if($row['pc_id']){
                    $link=$cms_cfg["base_root"]."products.php?func=p_list&pc_parent=".$row["pc_id"];
                }else{
                    $link=$cms_cfg["base_root"]."application.php?pa_id=".$row["pa_id"];
                }
            }
        }
        return $link;                  
    }    	
}
?>