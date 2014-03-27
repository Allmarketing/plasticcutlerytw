<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$video = new VIDEO;
class VIDEO{
    function VIDEO(){
        global $db,$cms_cfg,$tpl,$main;
        //show page
        $this->ws_tpl_file = "templates/ws-video-tpl.html";
        $this->ws_load_tp($this->ws_tpl_file);
        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        switch($_GET['func']){
            case "ajax_get_video_content":
                $this->ajax_get_video_content($_GET['v_id']);
                break;            
            default:
                $this->ws_tpl_type = 1;
                $defaultAction = "video_list";
                $type = $_GET['type'];
                $type = (!$type && $_GET['v_id'])?"show":$type;
                $type = (!$type && $_GET['vc_id'])?"list":$type;
                $action = ($type)?"video_".$type:$defaultAction;
                $this->$action();
                break;
        }
        if($this->ws_tpl_type){
            //page view record --ph_type,ph_type_id,m_id
            $main->pageview_history("v",$_REQUEST["v_id"],$_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID']);
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
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["VIDEO"]);
//        $tpl->assignGlobal( "TAG_LAYER" , $TPLMSG["VIDEO"]);
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["video"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["video"]);//左方menu title
        $tpl->assignGlobal( "TAG_VIDEO_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["video"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-video"); //主要顯示區域的css設定
        $main->header_footer("video",$TPLMSG["VIDEO"]);
        $main->google_code(); //google analystics code , google sitemap code
        $main->login_zone();
        $tpl->newBlock("HOME_LINK");
    }
    //前台youtube影片--列表================================================================
    function video_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $crow = $this->left_cate_list();
        if($crow){
            $ext = $this->ws_seo?"htm":"php";
            $main->layer_link($TPLMSG['VIDEO'],$cms_cfg['base_root']."video.".$ext)->layer_link($crow['vc_subject']);
            if($this->ws_seo){
                $meta_array=array("meta_title"       =>$crow["vc_seo_title"],
                                  "meta_keyword"     =>$crow["vc_seo_keyword"],
                                  "meta_description" =>$crow["vc_seo_description"],
                                  "seo_h1"           =>(trim($crow["vc_seo_h1"])=="")?$crow["vc_subject"]:$crow["vc_seo_h1"],
                );
                $main->header_footer($meta_array);
            }else{
                $main->header_footer("video",$TPLMSG["VIDEO"]);
            }     
            $vc_id = $crow['vc_id'];
        }
        if($vc_id){
            $sql = "select * from ".$cms_cfg['tb_prefix']."_video where v_status='1' and vc_id='".$vc_id."' order by v_sort ".$cms_cfg['sort_pos'];
            $res = $db->query($sql,1);
            if($db->numRows($res)){
                while($row = $db->fetch_array($res,1)){
                    $row['vc_seo_filename'] = $crow['vc_seo_filename'];
                    $tpl->newBlock("VIDEO_LIST");
                    $tpl->assign(array(
                        "VALUE_V_SUBJECT" => $row['v_subject'],
                        "VALUE_V_LINK"    => $this->get_link($row),
                        "VALUE_V_CODE"    => $main->get_mv_code($row['v_content']),
                    ));
                }
            }else{
                $tpl->assignGlobal("MSG_NO_DATA",$TPLMSG['NO_DATA']);
            }
        }else{
            $main->layer_link($TPLMSG['VIDEO']);            
            $sql = "select * from ".$cms_cfg['tb_prefix']."_video_cate where vc_status='1' order by vc_sort ".$cms_cfg['sort_pos'];
            $res = $db->query($sql,1);
            if($db->numRows($res)){
                while($row = $db->fetch_array($res,1)){
                    $tpl->newBlock("VIDEO_CATE_LIST");
                    $tpl->assign(array(
                        "VALUE_VC_SUBJECT" => $row['vc_subject'],
                        "VALUE_VC_LINK"    => $this->get_link($row,true),
                        "VALUE_VC_IMG"     => $row['vc_img']?$cms_cfg['file_root'].$row['vc_img']:$cms_cfg['default_preview_pic'],
                    ));
                }
            }else{
                $tpl->assignGlobal("MSG_NO_DATA",$TPLMSG['NO_DATA']);                
            }
        }
    }
    //前台youtube影片--內容================================================================
    function video_show(){
        global $db,$cms_cfg,$main,$TPLMSG,$tpl;
        $crow = $this->left_cate_list();    
        $ext = $this->ws_seo?"htm":"php";
        $main->layer_link($TPLMSG['VIDEO'],$cms_cfg['base_root']."video.".$ext)->layer_link($crow['vc_subject'],$this->get_link($crow,true));
        if($_GET['f']){
            $sql = "select * from ".$cms_cfg['tb_prefix']."_video where v_status='1' and v_seo_filename='".$db->quote($_GET['f'])."' limit 1";
        }elseif($_GET['v_id']){
            $sql = "select * from ".$cms_cfg['tb_prefix']."_video where v_status='1' and v_id='".$db->quote($_GET['v_id'])."' limit 1";
        }
        if($sql){
            $row = $db->query_firstrow($sql);
            if($this->ws_seo){
                $meta_array=array("meta_title"       =>$row["v_seo_title"],
                                  "meta_keyword"     =>$row["v_seo_keyword"],
                                  "meta_description" =>$row["v_seo_description"],
                                  "seo_h1"           =>(trim($row["v_seo_h1"])=="")?$row["v_subject"]:$row["v_seo_h1"],
                );
                $main->header_footer($meta_array);
            }else{
                $main->header_footer("video",$row["v_subject"]);
            }            
            $tpl->newBlock("VIDEO_CONTENT");
            $tpl->assign("VALUE_V_CONTENT",$row['v_content']);
            $main->layer_link($row["v_subject"]);
        }else{
            $tpl->assignGlobal("MSG_NO_DATA",$TPLMSG['NO_DATA']);            
        }
    }    
    function left_cate_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //前台左側影片分類
        $sql="select * from ".$cms_cfg['tb_prefix']."_video_cate  where vc_status='1' order by vc_sort ".$cms_cfg['sort_pos'];
        $selectrs = $db->query($sql);
        if(empty($_REQUEST["vc_id"]) && empty($_REQUEST["f"])){
           $sel_top_record=true;
        }
        $current_row = null;
        $i=0;
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            if($this->ws_seo==1 ){
                $cate_link=$cms_cfg["base_root"]."video/".$row["vc_seo_filename"].".htm";
                $ext="htm";
            }else{
                $cate_link=$cms_cfg["base_root"]."video.php?vc_id=".$row["vc_id"];
                $ext="php";
            }
            $tpl->newBlock( "LEFT_CATE_LIST" );
            $tpl->assign( array( "VALUE_CATE_NAME" => $row["vc_subject"],
                                 "VALUE_CATE_LINK"  => $cate_link,
            ));
            if(($_REQUEST["vc_id"]==$row["vc_id"]) || ($this->ws_seo && ($_REQUEST["f"]==$row["vc_seo_filename"] || $_REQUEST["d"]==$row["vc_seo_filename"]))){
                $tpl->assign("TAG_CURRENT_CLASS", "class=\"current\"");
                $current_row = $row;
            }
        }
        return $current_row;
    } 
    function get_link($row,$cate=false){
        global $cms_cfg;
        if($this->ws_seo){
            $base="video/";
            if($cate){
                $ext      = ".htm";
                $filename = $row['vc_seo_filename'];
            }else{
                $base.=$row['vc_seo_filename']."/";
                $ext      = ".html";
                $filename = $row['v_seo_filename'];
            }
            return $cms_cfg['base_root'].$base.$filename.$ext;
        }else{
            $var = "vc_id";
            $id  = $row['vc_id'];     
            $qs = "vc_id=".$row['vc_id'];
            if(!$cate){
                $qs .= "&v_id=".$row['v_id'];                        
            }
            return $cms_cfg['base_root']."video.php?func=v_list&".$qs;
        }
    }
    function ajax_get_video_content($v_id){
        global $db,$cms_cfg;
        $sql = "select v_content,v_subject from ".$cms_cfg['tb_prefix']."_video where v_id='".$v_id."'";
        $row = $db->query_firstrow($sql);
        $res['res'] = ($row['v_content'])?true:false;
        $res['content'] = $row['v_content'];
        $res['subject'] = $row['v_subject'];
        echo json_encode($res);
    }    
}
?>