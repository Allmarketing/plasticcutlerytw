<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
include_once("../libs/libs-graphic.php");
$PG = new PowerGraphic;
$statistic = new STATISTIC;
class STATISTIC{
    function STATISTIC(){
        global $db,$cms_cfg,$tpl,$TPLMSG;
        $this->ws_tpl_file = "templates/ws-manage-statistic-show-tpl.html";
        $this->ws_load_tp($this->ws_tpl_file);
        $tpl->newBlock("JS_FORMVALID");
        if(empty($_REQUEST["date_query"]) ){
            $this->date_year=date("Y");
            $this->date_month=date("m");
        }else{
            $this->date_year=$_REQUEST["start_year"];
            $this->date_month=$_REQUEST["start_month"];
        }
        switch($_REQUEST["mode"]){
            case "visitors"://訪客統計
                $this->select_date();
                $this->statistic_visitors();
                $tpl->assignGlobal("TAG_STATISTIC_MODE",$TPLMSG["STATISTIC_VISITORS"]);
                $tpl->assignGlobal("VALUE_STATISTIC_MODE", "visitors");
                $this->current_class="SR_VISITORS";
                break;
            case "country"://國別統計
                $this->select_date();
                $this->statistic_country();
                $tpl->assignGlobal("TAG_STATISTIC_MODE",$TPLMSG["STATISTIC_COUNTRY"]);
                $tpl->assignGlobal("VALUE_STATISTIC_MODE", "country");
                $this->current_class="SR_COUNTRY";
                break;
            case "views"://流量統計
                $this->select_date();
                $this->statistic_views();
                $tpl->assignGlobal("TAG_STATISTIC_MODE",$TPLMSG["STATISTIC_VIEWS"]);
                $tpl->assignGlobal("VALUE_STATISTIC_MODE", "views");
                $this->current_class="SR_VIEWS";
                break;
            case "sale"://銷售統計
                $this->statistic_sale();
                $tpl->assignGlobal("TAG_STATISTIC_MODE",$TPLMSG["STATISTIC_SALE"]);
                break;
            case "stock"://庫存統計
                $this->statistic_stock();
                $tpl->assignGlobal("TAG_STATISTIC_MODE",$TPLMSG["STATISTIC_STOCK"]);
                break;
            default:    //訪客統計
                $this->select_date();
                $this->statistic_visitors();
                break;
        }
        $tpl->printToScreen();
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
        $tpl->assignGlobal("CSS_BLOCK_STATISTIC","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }

    function select_date(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //輸出年下拉選單
        $sql = "select ph_dateY from ".$cms_cfg['tb_prefix']."_pageview_history group by ph_dateY";
        $selectrs_year = $db->query($sql);
        while($row_year = $db->fetch_array($selectrs_year,1)) {
            $tpl->newBlock("TAG_SELECT_STATISTIC_YEAR");
            $tpl->assign( array("TAG_SELECT_STATISTIC_YEAR_NAME" => $row_year['ph_dateY'],
                                "TAG_SELECT_STATISTIC_YEAR_VALUE" => $row_year['ph_dateY'],
                                "STR_SEL_Y" => ($this->date_year == $row_year['ph_dateY']) ? "selected" : ""
            ));
        }
        //輸出月下拉選單
        $sql = "select ph_dateM from ".$cms_cfg['tb_prefix']."_pageview_history group by ph_dateM";
        $selectrs_month = $db->query($sql);
        while($row_month = $db->fetch_array($selectrs_month,1)) {
            $tpl->newBlock("TAG_SELECT_STATISTIC_MONTH");
            $tpl->assign( array("TAG_SELECT_STATISTIC_MONTH_NAME" => $row_month['ph_dateM'],
                                "TAG_SELECT_STATISTIC_MONTH_VALUE" => $row_month['ph_dateM'],
                                "STR_SEL_M" => ($this->date_month == $row_month['ph_dateM']) ? "selected" : ""
            ));
        }
    }

    //訪客統計================================================================
    function statistic_visitors(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $sql="select distinct ph_ip_number, ph_dateD,count(ph_sum_target) as total from ".$cms_cfg['tb_prefix']."_pageview_history
                  where ph_dateY='".$this->date_year."' and ph_dateM='".$this->date_month."' group by ph_dateD,ph_ip_number";
        $selectrs = $db->query($sql);
        if($db->numRows($selectrs)) {
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $x[]=$row["ph_dateD"];
                $y[]=$row["total"];
            }
            $statistic_img=$this->pg_zone($x,$y,"Day","VIEWS","IP",2,1);
            $tpl->newBlock("STATISTIC_SHOW");
            $tpl->assign("VALUE_STATISTIC_SHOW",$statistic_img);
        }else{
            $tpl->newBlock("NO_DATA");
            $tpl->assign("MSG_NO_DATA", "NO DATA!!");
        }
    }
    //國別統計================================================================
    function statistic_country(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $sql="select ph_country,count(ph_sum_target) as total from ".$cms_cfg['tb_prefix']."_pageview_history
                  where ph_dateY='".$this->date_year."' and ph_dateM='".$this->date_month."' group by ph_country";
        $selectrs = $db->query($sql);
        if($db->numRows($selectrs)) {
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $x[]=$row["ph_country"];
                $y[]=$row["total"];
            }
            $statistic_img=$this->pg_zone($x,$y,"CONTRY","VIEWS","COUNTRY",5,1);
            $tpl->newBlock("STATISTIC_SHOW");
            $tpl->assign("VALUE_STATISTIC_SHOW",$statistic_img);
        }else{
            $tpl->newBlock("NO_DATA");
            $tpl->assign("MSG_NO_DATA", "NO DATA!!");
        }
    }
    //流量統計================================================================
    function statistic_views(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $sql="select ph_dateD,count(ph_sum_target) as total from ".$cms_cfg['tb_prefix']."_pageview_history
                  where ph_dateY='".$this->date_year."' and ph_dateM='".$this->date_month."' group by ph_dateD";
        $selectrs = $db->query($sql);
        if($db->numRows($selectrs)) {
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $x[]=$row["ph_dateD"];
                $y[]=$row["total"];
            }
            $statistic_img=$this->pg_zone($x,$y,"Day","VIEWS","VISITORS",1,3);
            $tpl->newBlock("STATISTIC_SHOW");
            $tpl->assign("VALUE_STATISTIC_SHOW",$statistic_img);
        }else{
            $tpl->newBlock("NO_DATA");
            $tpl->assign("MSG_NO_DATA", "NO DATA!!");
        }
    }
    //銷售統計================================================================
    function statistic_sale(){
        global $db,$tpl,$cms_cfg,$TPLMSG;

    }
    //銷售統計================================================================
    function statistic_stock(){
        global $db,$tpl,$cms_cfg,$TPLMSG;

    }
    function pg_zone($x,$y,$axis_x="Month",$axis_y="views",$title,$type=1,$skin=1){
        global $PG;
        $PG->title     = $title;
        $PG->x         = $x;
        $PG->y         = $y;
        $PG->axis_x    = $axis_x;
        $PG->axis_y    = $axis_y;
        $PG->graphic_1 = 'Year 2004';
        $PG->graphic_2 = 'Year 2003';
        $PG->type      = $type;
        $PG->skin      = $skin;
        $PG->credits   = 0;
        $img='<img src="../libs/libs-graphic.php?' . $PG->create_query_string() . '" border="1" alt="" /> <br /><br />';
        // Clear parameters
        $PG->reset_values();
        return $img;
    }
}
//ob_end_flush();
?>
