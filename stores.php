<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$stores = new STORES;
class STORES{
    function STORES(){
        global $db,$cms_cfg,$tpl,$main;
        $this->op_limit=$cms_cfg['storesop_limit'];
        $this->jp_limit=$cms_cfg['jp_limit'];
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->ps = $cms_cfg['path_separator'];
        if(!empty($_REQUEST["type"])){
            $_REQUEST["func"]="sd_".$_REQUEST["type"];
            $this->func_str=$cms_cfg['base_root']."stores/".$_REQUEST["f"];
        }else{
            $_REQUEST["f"]="stores";
            $this->func_str=$cms_cfg['base_root'].$_REQUEST["f"];
        }
        switch($_REQUEST["func"]){
            case "sd_get_map":
                $this->get_map();
                break;
            case "sd_list"://門市管理列表
                $this->ws_tpl_file = "templates/ws-stores-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->stores_list();
                $tpl->newBlock("JS_JQ_UI");
                //page view record --ph_type,ph_type_id,m_id
                $this->ws_tpl_type=1;
                break;
            default:    //門市管理列表
                $this->ws_tpl_file = "templates/ws-stores-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->stores_list();
                $tpl->newBlock("JS_JQ_UI");
                //page view record --ph_type,ph_type_id,m_id
                $this->ws_tpl_type=1;
                break;
        }
        if($this->ws_tpl_type){
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
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["STORES"]);
        $tpl->assignGlobal( "TAG_LAYER" , $TPLMSG["STORES"]);
        $tpl->assignGlobal("TAG_PAGE_BACK", $TPLMSG['PAGE_BACK']);
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["stores"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["stores"]);//左方menu title
        $tpl->assignGlobal( "TAG_STORES_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["stores"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_IMG" , $ws_array["main_img"]["stores"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-stores"); //主要顯示區域的css設定
        $main->google_code(); //google analystics code , google sitemap code
    }

//門市管理--列表================================================================
    function stores_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $ext=($this->ws_seo)?".htm":".php";
        $stores_link="<a href=\"".$cms_cfg["base_root"]."stores".$ext."\">".$TPLMSG["STORES"]."</a>";
        //門市管理分類
        $sdc_id = $this->left_cate_list();      
        $tpl->assignGlobal("TAG_LAYER",$stores_link);
        //門市管理列表
        ////網路門市
        if($_GET['f']=='webstores' || $_GET['sd_type']=='2'){
            $and_str = " and sd_type='2' ";
            $blocklist="WEB_STORES_LIST";
        }else{
            ////實體門市
            $and_str = " and sd_type='1'";
            if($sdc_id){
                $and_str .= " and sdc_id='".$sdc_id."'";
            }
            $blocklist="REAL_STORES_LIST";
        }
        
        $sql="select * from ".$cms_cfg['tb_prefix']."_stores where sd_status='1' ".$and_str." order by sd_sort ".$cms_cfg['sort_pos'];
        $selectrs = $db->query($sql);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結，且重新組合包含limit的sql語法
        if($this->ws_seo==1 && trim($_REQUEST["f"])!=""){
            $func_str=$cms_cfg['base_root']."stores/".$_REQUEST["f"];
            $sql=$main->pagination_rewrite($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        }else{
            $func_str=$cms_cfg["base_root"]."stores.php?func=sd_list&sd_type=".$_GET['sd_type']."&sdc_id=".$sdc_id;
            $sql=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        }
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $i=0;
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( $blocklist );
            $sd_img=(trim($row["sd_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["sd_img"];
            $tpl->assign( array("VALUE_SDC_ID"  => $row["sdc_id"],
                                "VALUE_SD_ID"  => $row["sd_id"],
                                "VALUE_SD_NAME" => $row["sd_name"],
                                "VALUE_SD_DESC" => $row["sd_desc"],
                                "VALUE_SD_SERIAL" => $i,
                                "VALUE_SD_IMG" => $sd_img,
                                "VALUE_SD_URL" => $row['sd_url'],
            ));
            if($row['sd_gmurl']){
                $tpl->newBlock("GMURL_BLOCK");
                $tpl->assign("VALUE_SD_ID",$row["sd_id"]);
            }
        }
    }
//門市管理--顯示================================================================
    function stores_show(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //門市管理分類
        $ext=($this->ws_seo)?".htm":".php";
        $stores_link="<a href=\"".$cms_cfg["base_root"]."stores".$ext."\">".$TPLMSG["STORES"]."</a>";
        $sql="select * from ".$cms_cfg['tb_prefix']."_stores_cate where sdc_status='1' order by sdc_sort ".$cms_cfg['sort_pos']." ";
        $selectrs = $db->query($sql);
        $i=0;
        while($row = $db->fetch_array($selectrs,1)){
            $i++;
            $cate_link = $this->get_link($row);
            $cate_link_array[$row["sdc_id"]]=$cate_link;
            $cate_subject_array[$row["sdc_id"]]=$row["sdc_subject"];
            $tpl->newBlock( "LEFT_CATE_LIST" );
            $tpl->assign( array( "VALUE_CATE_NAME" => $row["sdc_subject"],
                                 "VALUE_CATE_LINK"  => $cate_link,
            ));
        }
        //門市管理內容
        if(!empty($_REQUEST["sd_id"])){
            $and_str="sd_id='".$_REQUEST["sd_id"]."'";
        }
        if(!empty($_REQUEST["f"]) && $_REQUEST["type"]=="show"){
            $and_str="sd_seo_filename='".$_REQUEST["f"]."'";
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_stores where ".$and_str;
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        if($this->ws_seo){
            $meta_array=array("meta_title"=>$row["sd_seo_title"],
                              "meta_keyword"=>$row["sd_seo_keyword"],
                              "meta_description"=>$row["sd_seo_description"],
                              "seo_h1"=>(trim($row["sd_seo_h1"])=="")?$row["sd_subject"]:$row["sd_seo_h1"],
            );
            $main->header_footer($meta_array);
        }else{
            $main->header_footer("stores",$TPLMSG["STORES"]);
        }
        $stores_link .= $this->ps."<a href=\"".$cate_link_array[$row["sdc_id"]]."\">".$cate_subject_array[$row["sdc_id"]]."</a>".$this->ps.$row["sd_subject"];
        $tpl->newBlock( "STORES_SHOW" );
        $row["sd_content"]=preg_replace("/src=\"([^>]+)upload_files/","src=\"".$cms_cfg["file_root"]."upload_files",$row["sd_content"]);
        $tpl->assign( array("VALUE_SD_ID"  => $row["sd_id"],
                            "VALUE_SD_SUBJECT" => $row["sd_subject"],
                            "VALUE_SD_CONTENT" => $row["sd_content"],
                            "VALUE_SD_MODIFYDATE" => $row["sd_modifydate"],
        ));
        $tpl->assignGlobal("TAG_LAYER",$stores_link);
    }
    
    /*由$row取得該筆記錄的url
     */
    function get_link(&$row,$is_stores=false){
        global $cms_cfg;
        $link = "";
        if($is_stores){
            if($this->ws_seo==1 ){
                if(trim($row["sd_seo_filename"])==""){
                    $link = $cms_cfg["base_root"]."stores/ndetail-".$row["sdc_id"]."-".$row["sd_id"].".html";
                }else{
                    $link = $cms_cfg["base_root"]."stores/".$row["sd_seo_filename"].".html";
                }
            }else{
                $link = "stores.php?func=sd_show&sdc_id=".$row["sdc_id"]."&sd_id=".$row["sd_id"];
            }   
        }else{
            if($this->ws_seo==1 ){
                if(trim($row["sdc_seo_filename"])==""){
                    $link = $cms_cfg["base_root"]."stores/nlist-".$row["sdc_id"].".htm";
                }else{
                    $link = $cms_cfg["base_root"]."stores/".$row["sdc_seo_filename"].".htm";
                }
            }else{
                $link = $cms_cfg["base_root"]."stores.php?func=sd_list&sdc_id=".$row["sdc_id"];
            }
        }
        return $link;                  
    }     
    
    //左側選單
    function left_cate_list(){
        global $db,$cms_cfg,$tpl,$main,$TPLMSG;
        $sd_type_arr = array(1=>'各地門市',2=>'網路商城');
        foreach($sd_type_arr  as $sd_type=>$info){
            $tpl->newBlock( "LEFT_CATE_LIST" );
            if($sd_type==1){
                $cate_link = "#";
            }elseif($sd_type=2){
                if($this->ws_seo){
                    $cate_link = $cms_cfg['base_root']."stores/webstores.htm";
                }else{
                    $cate_link = $cms_cfg['base_root']."stores.php?func=sd_list&sd_type=2";
                }
            }
            $tpl->assign( array( "VALUE_CATE_NAME" => $info,
                                 "VALUE_CATE_LINK"  => $cate_link,
                                 "TAG_CURRENT_CLASS"  => ($_GET['f']=='webstores' || $_GET['sd_type']==2)?"class='current'":"",
            ));            
            if($sd_type==1){
                $sql = "select * from ".$cms_cfg['tb_prefix']."_stores_cate where sdc_status='1' order by sdc_sort ".$cms_cfg['sort_pos'];
                $res = $db->query($sql,true);
                if($db->numRows($res)){
                    $tpl->assign(array(
                       "TAG_SUB_UL1"=>"<div class=\"menu_body\"><ul>", 
                       "TAG_SUB_UL2"=>"</ul></div>", 
                    ));
                    while($row = $db->fetch_array($res,1)){
                        $tpl->newBlock("LEFT_SUBCATE_LIST");
                        if($this->ws_seo){
                            $sub_cate_link = $cms_cfg['base_root']."stores/".$row['sdc_seo_filename'].".htm";  
                            
                        }else{
                            $sub_cate_link = $cms_cfg['base_root']."stores.php?func=sd_list&sdc_id=".$row['sdc_id'];
                        }
                        if($_GET['f']==$row['sdc_seo_filename'] || $_GET['sdc_id']==$row['sdc_id']){
                            $current_class="class='current'";
                            $sdc_id = $row['sdc_id'];        
                            $meta_row = $row;
                        }else{
                            $current_class="";
                        }
                        $tpl->assign(array(
                           "VALUE_SUBCATE_LINK"=>$sub_cate_link, 
                           "TAG_CURRENT_CLASS"=>$current_class,
                           "VALUE_SUBCATE_NAME"=>$row['sdc_subject'],
                        ));     
                    }
                }
            }
        }
        if($meta_row){
            if($this->ws_seo){
                $meta_array=array("meta_title"=>$meta_row["sdc_seo_title"],
                                  "meta_keyword"=>$meta_row["sdc_seo_keyword"],
                                  "meta_description"=>$meta_row["sdc_seo_description"],
                                  "seo_short_desc" => $meta_row["sdc_seo_short_desc"],
                                  "seo_h1"=>(trim($meta_row["sdc_seo_h1"])=="")?$meta_row["sdc_subject"]:$meta_row["sdc_seo_h1"],
                );
                $main->header_footer($meta_array);
            }else{
                $main->header_footer("stores",$TPLMSG["STORES"]);
            }                              
        }else{
            $main->header_footer("stores",$TPLMSG["STORES"]);
        }        
        return $sdc_id;
    }
    //取得google map
    function get_map(){
        global $cms_cfg,$db;
        if($_POST['sd_id']){
            $sql = "select sd_gmurl from ".$cms_cfg['tb_prefix']."_stores where sd_id='".$_POST['sd_id']."'";
            $res = $db->query($sql);
            list($gmurl) = $db->fetch_array($res);
            if($gmurl){
                echo $gmurl;
            }
        }        
    }
}
?>