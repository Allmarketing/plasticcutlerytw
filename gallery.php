<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$news = new GALLERY;
class GALLERY{
    function GALLERY(){
        global $db,$cms_cfg,$tpl,$main;
        $this->op_limit=$cms_cfg['newsop_limit'];
        $this->jp_limit=$cms_cfg['jp_limit'];
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->ps = $cms_cfg['path_separator'];
        $list_method = ($cms_cfg['ws_module']['ws_gallery_scan_dir'])?"galler_dir_list":"gallery_list";
        switch($_REQUEST["func"]){
            case "g_list"://活動剪影列表
                $this->ws_tpl_file = "templates/ws-gallery-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->{$list_method}();
                //page view record --ph_type,ph_type_id,m_id
                $main->pageview_history("gc",$_REQUEST["gc_id"],$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID']);
                $this->ws_tpl_type=1;
                break;
            case "g_show"://活動剪影顯示
                $this->ws_tpl_file = "templates/ws-gallery-show-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_POP_IMG");
                $this->gallery_show();
                //page view record --ph_type,ph_type_id,m_id
                $main->pageview_history("g",$_REQUEST["g_id"],$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID']);
                $this->ws_tpl_type=1;
                break;
            default:    //活動剪影列表
                $this->ws_tpl_file = "templates/ws-gallery-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->{$list_method}();
                //page view record --ph_type,ph_type_id,m_id
                $main->pageview_history("gc",$_REQUEST["gc_id"],$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID']);
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
        $tpl->assignInclude( "MAIN_MENU", "templates/ws-fn-main-menu-tpl.html"); //主選單
        $tpl->assignInclude( "FOOTER", "templates/ws-fn-footer-tpl.html"); //頁腳        
        $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方一般表單
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        $tpl->assignInclude( "AD_H", "templates/ws-fn-ad-h-tpl.html"); //橫式廣告模板
        $tpl->assignInclude( "AD_V", "templates/ws-fn-ad-v-tpl.html"); //直式廣告模板     
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG['GALLERY']);
        $tpl->assignGlobal( "TAG_GALLERY_CURRENT" , "class='current'");
        $tpl->assignGlobal( "TAG_CATE_TITLE", $TPLMSG['GALLERY']);
        $tpl->assignGlobal( "TAG_CATE_DESC", $TPLMSG['GALLERY_CATE_DESC']);
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "album"); //主要顯示區域的css設定
        $tpl->assignGlobal( "TAG_SUBMENU_TITLE_IMG" , $cms_cfg['default_theme']."left-title-activity.png"); //選單標題圖檔
        $main->header_footer("",$TPLMSG['GALLERY']);
        $this->left_cate_list();
    }

//活動剪影--列表================================================================
    function gallery_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //活動剪影列表
        if(!empty($_REQUEST["gc_id"])){
            $gc_id=$_REQUEST["gc_id"];
            $and_str="and gc_id='".$_REQUEST["gc_id"]."'";
        }else{
            $gc_id=0;
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_gallery where g_status='1'  ".$and_str." order by g_sort ".$cms_cfg['sort_pos'].",g_modifydate desc";
        $selectrs = $db->query($sql,true);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結，且重新組合包含limit的sql語法
        if($this->ws_seo==1 ){
            $func_str="gallery/glist-".$gc_id;
            $sql=$main->pagination_rewrite($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        }else{
            $func_str="gallery.php?func=g_list&gc_id=".$gc_id;
            $sql=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        }
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $i=0;
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "GALLERY_LIST" );
            $simg = $row["g_s_pic"]?$cms_cfg['file_root'].$row["g_s_pic"]:$cms_cfg['default_preview_pic'];
            $dimension = $main->resizeto($simg,$cms_cfg['gallery_img_width'],$cms_cfg['gallery_img_height']);
            $tpl->assign( array("VALUE_GC_ID"  => $row["gc_id"],
                                "VALUE_G_ID"  => $row["g_id"],
                                "VALUE_G_SUBJECT" => $row["g_subject"],
                                "VALUE_G_LINK" => $this->get_link($row,true),
                                "VALUE_G_MODIFYDATE" => substr($row["g_modifydate"],0,10),
                                "VALUE_G_TARGET" => ($row["g_pop"])?"_blank":"_parent",
                                "VALUE_G_SERIAL" => $i,
                                "VALUE_G_S_PIC" => $simg,
                                "VALUE_G_S_PIC_W" => $dimension['width'],
                                "VALUE_G_S_PIC_H" => $dimension['height'],
                                "VALUE_G_STRIP_CONTENT" => str_replace("\r\n","",strip_tags($row["g_content"])),
            ));
        }
    }
//活動剪影--顯示================================================================
    function gallery_show(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //活動剪影內容
        $sql="select * from ".$cms_cfg['tb_prefix']."_gallery where g_id='".$_REQUEST["g_id"]."'";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $main->header_footer("gallery",$row["g_subject"]);
        $main->layer_link($row["g_subject"]);
        $tpl->newBlock( "GALLERY_SHOW" );
        $tpl->assign( array("VALUE_G_ID"  => $row["g_id"],
                            "VALUE_G_SUBJECT" => $row["g_subject"],
                            "VALUE_G_CONTENT" => $row["g_content"],
                            "VALUE_G_MODIFYDATE" => $row["g_modifydate"],
        ));
        for($i=1;$i<=$cms_cfg['gallery_img_limit'];$i++){
            if(trim($row["g_b_pic".$i])!=""){
                $big_img_array[]=$row["g_b_pic".$i];
            }
        }
        if(count($big_img_array) >0){
            $k=1;
            foreach($big_img_array as $key => $value){
               //顯示大圖
               $tpl->newBlock("GALLERY_PIC_LIST");
               $img = $value?$cms_cfg["file_root"].$value:$cms_cfg['default_preview_pic'];
               $dimensions = $main->resizeto($img,$cms_cfg['gallery_img_width'],$cms_cfg['gallery_img_height']);
               $tpl->assign("VALUE_BIG_PIC",$img);
               $tpl->assign("VALUE_BIG_PIC_W",$dimensions['width']);
               $tpl->assign("VALUE_BIG_PIC_H",$dimensions['height']);
            }
        }
        $tpl->assignGlobal("GO_BACK",$TPLMSG['PAGE_BACK']);
    }
    function left_cate_list(){
        global $db,$cms_cfg,$tpl,$main,$TPLMSG;
        //先判斷有沒有gc_id
        if(isset($_REQUEST["gc_id"])){
            $main->layer_link($TPLMSG['GALLERY'],$cms_cfg['base_root']."gallery.htm");
        }else{
            $main->layer_link($TPLMSG['GALLERY']);
        }
        //活動剪影分類
        $sql="select * from ".$cms_cfg['tb_prefix']."_gallery_cate where gc_status='1' order by gc_sort ".$cms_cfg['sort_pos']." ";
        $selectrs = $db->query($sql,true);
        $i=0;
        while($row = $db->fetch_array($selectrs,1)){
            $i++;
            $tpl->newBlock( "LEFT_CATE_LIST" );
            $tpl->assign( array( "VALUE_CATE_NAME"   => $row["gc_subject"],
                                 "VALUE_CATE_LINK"   => $this->get_link($row),
                                 "TAG_CURRENT_CLASS" => "",
            ));
            if($_REQUEST["gc_id"]==$row["gc_id"]){
                $tpl->assign("TAG_CURRENT_CLASS" ,"class='current'");
                if($_GET['g_id']){
                $main->layer_link($row["gc_subject"],$this->get_link($row));
                }else{
                    $main->layer_link($row["gc_subject"]);
                }
                $main->header_footer("",$row["gc_subject"]);
            }
        }        
    }
    function get_link($row,$item=false){
        if($item){
            if($this->ws_seo){
                $link = $cms_cfg["base_root"]."gallery/gdetail-".$row["gc_id"]."-".$row["g_id"].".html";
            }else{
                $link = $cms_cfg['base_root']."gallery.php?func=g_show&gc_id=".$row["gc_id"]."&g_id=".$row["g_id"];
            }
        }else{
            if($this->ws_seo==1 ){
                $link="gallery/glist-".$row["gc_id"].".htm";
            }else{
                $link="gallery.php?func=g_list&gc_id=".$row["gc_id"];
            }        
        }
        return $link;
    }
    function galler_dir_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        if($_GET['gc_id']){
            $and_str = " and gc_id='".$db->quote($_GET['gc_id'])."'";
            $method = "dirshow";
            $tpl->newBlock("JS_POP_IMG");
            $tpl->newBlock("JS_LAZYLOAD");
        }else{
            $method = "dirlist";
}
        $sql = "select * from ".$cms_cfg['tb_prefix']."_gallery_cate where gc_status='1' ".$and_str." order by gc_sort ".$cms_cfg['sort_pos'];
        $res = $db->query($sql,true);
        while($row = $db->fetch_array($res,1)){
            $this->{$method}($row);
        }
    }
    function dirlist($cate){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $dir = $_SERVER['DOCUMENT_ROOT'].$cms_cfg['file_root'].$cate['gc_dir'];
        if(is_dir($dir)){
            $pattern = $dir . "/*.{jpg,jpeg,png}";
            $imgs = glob($pattern,GLOB_BRACE);
            $thumb = $main->file_str_replace($imgs[0]);
            $tpl->newBlock( "GALLERY_CATE_LIST" );
            $simg = $cms_cfg['file_root'].$thumb;
            $dimension = $main->resizeto($simg,$cms_cfg['gallery_img_width'],$cms_cfg['gallery_img_height']);
            $tpl->assign( array(
                "VALUE_GC_SUBJECT" => $cate['gc_subject'],
                "VALUE_GC_LINK" => $this->get_link($cate),
                "VALUE_GC_S_PIC" => $simg,
                "VALUE_GC_S_PIC_W" => $dimension['width'],
                "VALUE_GC_S_PIC_H" => $dimension['height'],
            ));
        }
        
    }
    function dirshow($cate){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $dir = $_SERVER['DOCUMENT_ROOT'].$cms_cfg['file_root'].$cate['gc_dir'];
        if($cate['gc_desc']){
            $tpl->newBlock("GALLERY_CATE_DESC");
            $tpl->assign("VALUE_GC_DESC",$main->content_file_str_replace($cate['gc_desc']));
        }
        if(is_dir($dir)){
            $pattern = $dir . "/*.{jpg,jpeg,png}";
            $imgs = glob($pattern,GLOB_BRACE);
            foreach($imgs  as $full_path_img){
                $thumb = $main->file_str_replace($full_path_img);
                $tpl->newBlock( "GALLERY_BATCH_LIST" );
                $simg = $cms_cfg['file_root'].$thumb;
                $dimension = $main->resizeto($simg,$cms_cfg['gallery_img_width'],$cms_cfg['gallery_img_height']);
                $tpl->assign( array(
                    "VALUE_G_LINK" => $simg,
                    "VALUE_G_S_PIC" => $simg,
                    "VALUE_G_S_PIC_W" => $dimension['width'],
                    "VALUE_G_S_PIC_H" => $dimension['height'],
                ));
            }
        }
    }
}
?>
