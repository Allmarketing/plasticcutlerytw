<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$ebook = new EBOOK;
class EBOOK{
    function EBOOK(){
        global $db,$cms_cfg,$tpl,$main,$TPLMSG;
        //EBOOK列表分頁限制
        $this->op_limit=($_SESSION[$cms_cfg['sess_cookie_name']]["sc_op_limit"])?$_SESSION[$cms_cfg['sess_cookie_name']]["sc_op_limit"]:9;
        $this->jp_limit=$cms_cfg['jp_limit'];
        //EBOOK詳細頁分頁限制
        $this->op_limit2=($_SESSION[$cms_cfg['sess_cookie_name']]["sc_op_limit"])?$_SESSION[$cms_cfg['sess_cookie_name']]["sc_op_limit"]:1;
        $this->jp_limit2=1;

        $this->ws_seo=($cms_cfg["ws_module"]["ws_seo"])?1:0;
        switch($_REQUEST["func"]){
            case "download":
                $this->download_ebook();
                break;
            case "print":
                $this->ws_tpl_file = "templates/ws-ebook-print-tpl.html";
                $tpl = new TemplatePower($this->ws_tpl_file);
                $tpl->prepare();
                $this->ebook_print();
                $this->ws_tpl_type=1;
                break;
            case "eb_list": //EBOOK列表
                $this->base_all_tpl ="templates/ws-fn-all-tpl.html";
                $this->ws_tpl_file = "templates/ws-ebook-main-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->left_fix_cate_list();
                $this->ebook_list("");
                $this->ws_tpl_type=1;
                break;
             case "eb_detail": //EBOOK詳細頁
                $this->base_all_tpl ="templates/ws-fn-all-tpl.html";
                $this->ws_tpl_file = "templates/ws-ebook-page-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_POP_IMG");
                $this->left_fix_cate_list();
                $this->ebook_show();
                $this->ws_tpl_type=1;
                break;
            default: //EBOOK分類列表
                $this->eb_homepage=1;
                $this->base_all_tpl ="templates/ws-fn-all-tpl.html";
                $this->ws_tpl_file = "templates/ws-ebook-main-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->left_fix_cate_list();
                $this->ebook_list("");
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
        $this->top_layer_link="<a href=\"" . $cms_cfg['base_root'] . "ebook.php\">" . $TPLMSG["EBOOK"] . "</a>";
        $tpl = new TemplatePower($this->base_all_tpl);
        $tpl->assignInclude( "HEADER", "templates/ws-fn-header-tpl.html"); //js,css
        $tpl->assignInclude( "LEFT", "templates/ws-ebook-menu-tpl.html"); //左方一般表單
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        //$tpl->assignGlobal( "MSG_HOME", $TPLMSG['EBOOK']);
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["EBOOK"]);
        $tpl->assignGlobal( "TAG_LAYER" , $this->top_layer_link);
        $tpl->prepare();
        $tpl->assignGlobal("TAG_ROOT_PATH" , $cms_cfg['base_root']);
        $tpl->assignGlobal( "TAG_MAIN_IMG" , $ws_array["main_img"]["ebook"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["ebook"]);//左方menu title
        $tpl->assignGlobal( "TAG_EBOOK_CURRENT" , "class='current'"); //上方menu current
        $main->header_footer("");
    }
    //EBOOK--列表================================================================
    function ebook_list($mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //預設EBOOK列表
        $sql="select ebc_id from ".$cms_cfg['tb_prefix']."_ebook_cate where ebc_status='1' order by ebc_sort ".$cms_cfg['sort_pos'];
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $this->parent=($_REQUEST["ebc_parent"])?$_REQUEST["ebc_parent"]:$row["ebc_id"];
        //分類標題
        $sql = "select ebc_name from ".$cms_cfg['tb_prefix']."_ebook_cate where ebc_status='1' and ebc_id='".$this->parent."'";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $tpl->assignGlobal("TAG_MAIN_FUNC", $row["ebc_name"]);
        
        //顯示資訊
        $show_style_str_ebc="SHOW_STYLE_EBC1";
        $show_style_str_eb="SHOW_STYLE_EB1";
        //一列顯示筆數
        $row_num=$cms_cfg["ws_ebook_row"];
        if($mode==""){
            $sql="select ebc_id,ebc_name,ebc_cate_img from ".$cms_cfg['tb_prefix']."_ebook_cate where ebc_id > '0'";
            $sql .= " and ebc_id='".$this->parent."' and ebc_status='1' order by ebc_sort, ebc_modifyaccount desc ";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            if($rsnum > 0){
                //分類列表
//                $row = $db->fetch_array($selectrs,1);
//                $tpl->newBlock( $show_style_str_ebc );
//                 $tpl->assign( array(
//                    "VALUE_EBC_NAME"  => $row["ebc_name"],
//                    "VALUE_EBC_CATE_IMG" =>(trim($row["ebc_cate_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["base_root"].$row["ebc_cate_img"]
//                ));
            }else{
                if($this->eb_homepage!=1){
                    include_once("404.htm");
                    exit();
                }
            }
        }
        //階層
        $func_str="";
        $ebook_cate_layer=$main->get_layer($cms_cfg['tb_prefix']."_ebook_cate","ebc_name","ebc",$this->parent,$func_str);
        if(!empty($ebook_cate_layer)){
            $tpl->assignGlobal("TAG_LAYER",$this->top_layer_link. $cms_cfg['path_separator'] .implode($cms_cfg['path_separator'],$ebook_cate_layer));
        }

        //EBOOK列表
        $sql="select * from ".$cms_cfg['tb_prefix']."_ebook where eb_status='1' and ebc_id ='".$this->parent."' order by eb_sort ".$cms_cfg['sort_pos'].", eb_modifyaccount desc";
        //取得總筆數
        $selectrs = $db->query($sql);
        $total_records = $db->numRows($selectrs);

        //取得分頁連結
        $func_str="ebook.php?func=eb_list&ebc_parent=".$this->parent;
        //分頁重新組合包含limit的sql語法
        $sql=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        $selectrs = $db->query($sql);

        //EBOOK列表------------------------
        $i  = 0;
        $start_serial = ($nowp=intval($_REQUEST["nowp"]))?($nowp-1)*$this->op_limit:$nowp;
        $k  = $start_serial;
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $n= $k+1;
            $tpl->newBlock( $show_style_str_eb );
            if($row['eb_link']){
                $eb_link=$row['eb_link'];
                $tpl->assign("TAG_TARGET_BLANK","target=\"_blank\"");
            }else{
            $eb_link=$cms_cfg["base_root"]."ebook.php?func=eb_detail&ebc_parent=".$row["ebc_id"]."&eb_id=".$row["eb_id"]."&nowp=".$n."&jp=".$k;
            }
            $tpl->assign( array(
                "VALUE_EB_NAME" => $row["eb_name"],
                "VALUE_EB_LINK" => $eb_link,
                "VALUE_EB_SMALL_IMG" => (trim($row["eb_small_img"])=="")?$cms_cfg['default_ebook_pic']:$cms_cfg["file_root"].$row["eb_small_img"]
            ));
            $i++;
            $k++;
        }
    }
    //EBOOK詳細頁
    function ebook_show() {
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $this->parent=($_REQUEST["ebc_parent"])?$_REQUEST["ebc_parent"]:0;
        $sql="select * from ".$cms_cfg['tb_prefix']."_ebook where eb_status='1' and ebc_id = '".$this->parent."' order by eb_sort asc, eb_modifyaccount desc ";
        //取得總筆數
        $selectrs = $db->query($sql);
        $total_records = $db->numRows($selectrs);
        //取得分頁連結
        $func_str="ebook.php?func=eb_detail&ebc_parent=".$this->parent;
        $page=$this->pagination($this->op_limit2,$this->jp_limit2,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        //EBOOK列表------------------------
        $tpl->assignGlobal("VALUE_PAGES_HOME", "ebook.php?func=eb_list&ebc_parent=" . $this->parent);
        $j=0;
        $k=$page["start_serial"];
        if($rsnum){
            $tpl->newBlock("SHOW_STYLE_EB1");
            while($row = $db->fetch_array($selectrs,1)) {
                $j++;
                $k++;
                    $tpl->newBlock("EB_LIST");
                    $img = (trim($row["eb_big_img"])=="")?$cms_cfg['default_ebook_pic']:$cms_cfg["file_root"].$row["eb_big_img"];
                    $tpl->assign(array(
                        "VALUE_EB_BIG_IMG" => $img,
                        "VALUE_EB_ID"      => $row["eb_id"]
                ));
                if($j==$_GET['nowp']){
                    $tpl->newBlock("SHOW_IMG");
                    $tpl->assign(array(
                        "VALUE_EB_BIG_IMG" => $img
                    ));
                    $tpl->assign("_ROOT.VALUE_EB_ID",$row['eb_id']);
                    $tpl->assignGlobal("TAG_MAIN_FUNC", $row["eb_name"]);
                    $func_str="ebook.php?func=eb_list";
                    $ebook_cate_layer=$main->get_layer($cms_cfg['tb_prefix']."_ebook_cate","ebc_name","ebc",$row["ebc_id"],$func_str,1);
                    if(!empty($ebook_cate_layer)){
                        $tpl->assignGlobal("TAG_LAYER",$this->top_layer_link.$cms_cfg['path_separator'].implode($cms_cfg['path_separator'],$ebook_cate_layer).$cms_cfg['path_separator'].$row["eb_name"]);
                    }
                }
            }
        }
        if($j!=0){
            $tpl->assignGlobal( array("VALUE_TOTAL_RECORDS"  => $page["total_records"],
                    "VALUE_TOTAL_PAGES"  => $page["total_pages"],
                    "VALUE_PAGES_STR"  => $page["pages_str"],
                    "VALUE_PAGES_LIMIT"=>$this->op_limit2
            ));
            if($page["bj_page"]){
                $tpl->newBlock( "PAGE_BACK_SHOW" );
                $tpl->assign( "VALUE_PAGES_BACK"  , $page["bj_page"]);
            }
            if($page["nj_page"]){
                $tpl->newBlock( "PAGE_NEXT_SHOW" );
                $tpl->assign( "VALUE_PAGES_NEXT"  , $page["nj_page"]);
            }
        }
    }    
    //顯示EBOOK分類的左方menu
    function left_fix_cate_list(){
        global $tpl,$db,$main,$cms_cfg;
        $sql="select * from ".$cms_cfg['tb_prefix']."_ebook_cate where ebc_parent='0' and ebc_status='1' order by ebc_sort, ebc_modifyaccount desc";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        if($rsnum > 0 ){
            while($row = $db->fetch_array($selectrs,1)){
                $ebc_link=$cms_cfg["base_root"]."ebook.php?func=eb_list&ebc_parent=".$row["ebc_id"];
                $tpl->newBlock( "LEFT_CATE_LIST" );
                $tpl->assign( array( "VALUE_CATE_NAME" => $row["ebc_name"],
                    "VALUE_CATE_IMG" => (trim($row["ebc_cate_img"])=="")?$cms_cfg['default_ebook_pic']:$cms_cfg["file_root"].$row["ebc_cate_img"],
                    "VALUE_CATE_LINK"  => $ebc_link,
                    "VALUE_CATE_NAME"  => $row['ebc_name'],
                    "TAG_CURRENT_CLASS"  => ($_GET['ebc_parent']==$row['ebc_id'])?"class='current'":"",
                ));
            }
        }
    }
    //EBOOK分頁
    function pagination($op_limit=10,$jp_limit=10,$nowp=1,$jp=0,$func_str,$total){
        $Page["total_records"]=$total;
        //Total Pages
        $Page["total_pages"]=($total%$op_limit)? $total/$op_limit +1 : $total/$op_limit;
        //New Sql
        $start_pages=($nowp>=1)?$nowp-1:0;
        $Page["start_serial"]=$start_pages*$op_limit;
        $ppages=floor($Page["total_pages"]/$jp_limit);
        if($jp<$ppages){
            $page_start=$jp*$jp_limit+1;
            $page_end=$page_start+$jp_limit;
        }else{
            $page_start=$jp*$jp_limit+1;
            $page_end=$page_start+$Page["total_pages"]%$jp_limit;
        }
        //沒有上跳頁也沒有下跳頁
        if($ppages <= 1 && $Page["total_pages"]<$jp_limit+1){
            $Page["bj_page"]="";
            $Page["nj_page"]="";
            $page_start=1;
            $page_end=($total%$op_limit)?$Page["total_pages"] : $Page["total_pages"]+1;
        }else{
            //有上跳頁沒有下跳頁
      if($nowp >= $ppages) {
        if($jp <= $ppages){ //最後下跳頁
          $bp=$jp-1;
          $prev=$page_start-1;
          $Page["bj_page"]=$func_str."&nowp=".$prev."&jp=".$bp;
          $Page["nj_page"]="";
        }
      }else{
        //有上跳頁也有下跳頁
        if($jp < $ppages && $jp!=0){
          $bp=$jp-1;
          $np=$jp+1;
          $prev=$page_start-1;
          $Page["bj_page"]=$func_str."&nowp=".$prev."&jp=".$bp;
          $Page["nj_page"]=$func_str."&nowp=".$page_end."&jp=".$np;
        }
        //沒有上跳頁有下跳頁
        if($jp ==0){//第1頁
          $np=$jp+1;
          $Page["bj_page"]="";
          $Page["nj_page"]=$func_str."&nowp=".$page_end."&jp=".$np;
        }
      }
        }
        //分頁選單PAGE_OPTION
        $nowp_option=array();
        for($i=$page_start;$i<$page_end;$i++){
            //$line1=($i==floor($page_end))?"":" | ";
            $nowp_option[] = ($i==$nowp || ($i==$page_start && $nowp==0))?"<b>".$i."</b>" : "<a href=\"".$func_str."&nowp=".$i."&jp=".$jp."\"> ".$i." </a>";
        }
        $page_option=implode(" ",$nowp_option);
        $Page["pages_str"]=$page_option;
        $Page["total_pages"]=floor($Page["total_pages"]);
        return $Page;
    }
    function download_ebook(){
        global $db,$cms_cfg;
        $sql = "select eb_id,eb_big_img from ".$cms_cfg['tb_prefix']."_ebook where eb_id=".$_REQUEST["fileID"];
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $piece=explode("/",$row["eb_big_img"]);
        $num=count($piece)-1;
        $file_name = $piece[$num];
        //pumo主機
        $file_name_path = $_SERVER['DOCUMENT_ROOT'].$cms_cfg['file_root'].$row["eb_big_img"];
        //amgvh主機
        // $file_name_path = $cms_cfg['file_url'].$row["eb_big_img"];

        header("Content-type:application");
        header("Content-Disposition: attachment; filename=".$file_name);
        //file_name是預設下載時的檔名，可使用變數。
        readfile($file_name_path);
        //file是實際存放在你硬碟中要被下載的檔案，可使用變數。
        exit(0);        
    }
    //列印 EDM
    function ebook_print() {
        global $tpl,$cms_cfg,$ws_array,$db,$TPLMSG,$main;
        $sql ="select eb_big_img from ".$cms_cfg['tb_prefix']."_ebook where eb_id=".$_REQUEST["fileID"];
        $selectrs = $db->query($sql);
        $rsnum = $db->numRows($selectrs);
        if($rsnum > 0) {
            $row = $db->fetch_array($selectrs,1);
            $tpl->assignGlobal(array(
                "VALUE_EB_BIG_IMG" => (trim($row["eb_big_img"])=="")?$cms_cfg['default_ebook_pic']:$cms_cfg["file_root"].$row["eb_big_img"]
    		    ));
        }
    }    
}
?>