<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])  || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_order"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$order = new ORDER;
class ORDER{
    function ORDER(){
        global $db,$cms_cfg,$tpl;
        switch($_REQUEST["func"]){
            case "o_list"://訂單列表
                $this->current_class="O";
                $this->ws_tpl_file = "templates/ws-manage-order-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                if($cms_cfg["ws_module"]["ws_vaccount"]==1) {
                    $this->check_atm();//檢查新匯款紀錄
                }
                $this->order_list();
                $this->ws_tpl_type=1;
                break;
            case "o_replace"://訂單更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->order_replace();
                $this->ws_tpl_type=1;
                break;
            case "o_reply"://訂單檢視及更新狀態
                $this->current_class="O";
                $this->ws_tpl_file = "templates/ws-manage-order-reply-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->order_reply_form();
                $this->ws_tpl_type=1;
                break;
            case "o_del"://訂單刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->order_del();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //訂單列表
                $this->current_class="O";
                $this->ws_tpl_file = "templates/ws-manage-order-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                if($cms_cfg["ws_module"]["ws_vaccount"]==1) {
                    $this->check_atm();//檢查新匯款紀錄
                }
                $this->order_list();
                $this->ws_tpl_type=1;
                break;
        }
        if($this->ws_tpl_type){
            $tpl->printToScreen();
        }
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
        $tpl->assignGlobal("CSS_BLOCK_ORDER","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }
//訂單--列表================================================================
    function order_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //顯示ATM匯款標題
        if($cms_cfg["ws_module"]["ws_vaccount"]) {
            $tpl->newBlock("TITLE_ATM_TRANSFER");
        }
        $i=0;
        foreach($ws_array["order_status"] as $key =>$value){
            $i++;
            $tpl->newBlock( "ORDER_STATUS_LIST" );
            $tpl->assign( array("VALUE_O_STATUS_SUBJECT"  => $value,
                                "VALUE_O_STATUS" => $key,
                                "VALUE_O_SERIAL" => $i,
            ));
            if($i%4==0){
                $tpl->assign("TAG_ORDER_STATUS_TRTD","</tr><tr>");
            }
            if($key==$_REQUEST["o_status"]){
                $tpl->assignGlobal("TAG_NOW_CATE",$value);
            }
        }
        //訂單列表
        $sql="select * from ".$cms_cfg['tb_prefix']."_order where o_id > '0'";
        //附加條件
        $and_str="";
        if($_REQUEST["o_status"]!=""){
            $and_str .= " and o_status = '".$_REQUEST["o_status"]."'";
        }
        if($_REQUEST["st"]=="o_name"){
            $and_str .= " and o_name like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by o_modifydate desc ";
        //取得總筆數
        $selectrs = $db->query($sql);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結
        $func_str="order.php?func=o_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        //分頁且重新組合包含limit的sql語法
        $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("VALUE_TOTAL_BOX" => $rsnum,
                                  "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                  "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],
        ));
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "ORDER_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_O_ID"  => $row["o_id"],
                                "VALUE_O_NAME" => $row["o_name"],
                                "VALUE_O_CREATEDATE" => $row["o_createdate"],
                                "VALUE_O_MODIFYDATE" => $row["o_modifydate"],
                                "VALUE_O_TOTAL_PRICE" => $row["o_total_price"],
                                "VALUE_O_STATUS" => $ws_array["order_status"][$row["o_status"]],
                                "VALUE_O_SERIAL" => $i ));

            //顯示ATM匯款狀態
            if($cms_cfg["ws_module"]["ws_vaccount"]) {
                $tpl->newBlock("ATM_TRANSFER_STATE");
                $tpl->assign( array("VALUE_O_REMIT_STATUS" => ($row["o_payment_type"] == "ATM") ? $row["o_remit_status"] ? "完成匯款":"<font color=\"#ff0000\">未匯款</font>" :"",
                                    "VALUE_O_CURAMT" => $row["o_curamt"],
                                    "VALUE_O_TRN_TIME" => $row["o_trn_time"] ));
            }
        }
    }

//訂單--刪除--資料刪除可多筆處理================================================================
    function order_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["o_id"]){
            $cu_id=array(0=>$_REQUEST["o_id"]);
        }else{
            $cu_id=$_REQUEST["id"];
        }
        if(!empty($cu_id)){
            $cu_id_str = implode(",",$cu_id);
            //刪除勾選的訂單
            $sql="delete from ".$cms_cfg['tb_prefix']."_order where o_id in (".$cu_id_str.")";
            $rs = $db->query($sql);
            $sql="delete from ".$cms_cfg['tb_prefix']."_order_items where o_id in (".$cu_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."order.php?func=o_list&cuc_id=".$_REQUEST["cuc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //訂單回覆--表單================================================================
    function order_reply_form(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['MODIFY'],
                                  "MSG_PRODUCT_SPECIAL_PRICE" => $TPLMSG['PRODUCT_DISCOUNT_PRICE']
        ));
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        if($cms_cfg["ws_module"]['ws_delivery_timesec']){ //是否顯示配送區間
            $tpl->newBlock("DELIVERY_TIMESEC");
        }
        //帶入要回覆的訂單資料
        if(!empty($_REQUEST["o_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_order where o_id='".$_REQUEST["o_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $dts = strtotime($row['o_deliver_date']);
                $tpl->assignGlobal( array("VALUE_M_ID"  => $row["m_id"],
                                          "VALUE_O_ID"  => $row["o_id"],
                                          "VALUE_O_NAME" => $row["o_name"],
                                          "VALUE_O_TEL" => $row["o_tel"],
                                          "VALUE_O_CELLPHONE" => $row["o_cellphone"],
                                          "VALUE_O_ZIP" => $row["o_zip"],
                                          "VALUE_O_ADDRESS" => $row["o_address"],
                                          "VALUE_O_EMAIL" => $row["o_email"],
                                          "VALUE_O_CONTENT" => $row["o_content"],
                                          "VALUE_O_PLUS_PRICE" => $row["o_plus_price"],
                                          "VALUE_O_SUBTOTAL_PRICE" => $row["o_subtotal_price"],
                                          "VALUE_O_TOTAL_PRICE" => $row["o_total_price"],
                                          "VALUE_O_STATUS" => $ws_array["order_status"][$row["o_status"]],
                                          "VALUE_O_PAYMENT_TYPE"=>$ws_array["payment_type"][$row["o_payment_type"]],
                                          "VALUE_O_ATM_LAST5" => $row["o_atm_last5"],
                                          "VALUE_O_DELIVERY_STR" => sprintf("%s %s",date("Y年m月d日",$dts),$ws_array["deliery_timesec"][$row['o_deliver_time_sec']]),
                ));
                if($cms_cfg["ws_module"]["ws_vaccount"] & $row["o_virtual_account"]) {
                $tpl->newBlock("ATM_DATA");
                $tpl->assignGlobal( array("VALUE_O_VIRTUAL_ACCOUNT" => $row["o_virtual_account"],
                                          "VALUE_O_CURAMT" => $row["o_curamt"]));
                }
                //訂單狀態
                foreach($ws_array["order_status"] as $key =>$value){
                    $i++;
                    $tpl->newBlock( "ORDER_STATUS_LIST" );
                    $tpl->assign( array("VALUE_O_STATUS_SUBJECT"  => $value,
                                        "VALUE_O_STATUS" => $key,
                                        "VALUE_O_SERIAL" => $i,
                    ));
                    if($i%4==0){
                        $tpl->assign("TAG_BR","<br>");
                    }
                    if($key==$row["o_status"]){
                        $tpl->assign("TAG_STATUS_CHECKED","checked");
                    }
                }
                //訂購產品列表
                $sql="select * from ".$cms_cfg['tb_prefix']."_order_items where o_id='".$_REQUEST["o_id"]."'";
                $selectrs = $db->query($sql);
                $total_price=0;
                $i=0;
                while($row = $db->fetch_array($selectrs,1)){
                    $i++;
                    $sub_total_price = $row["p_sell_price"] * $row["oi_amount"];
                    $total_price = $total_price+$sub_total_price;
                    $tpl->newBlock( "ORDER_ITEMS_LIST" );
                    $tpl->assign( array("VALUE_P_ID"  => $row["p_id"],
                                        "VALUE_P_NAME" => $row["p_name"],
                                        "VALUE_P_SELL_PRICE" => $row["p_sell_price"],
                                        "VALUE_P_AMOUNT" => $row["oi_amount"],
                                        "VALUE_P_SUBTOTAL_PRICE"  => $sub_total_price,
                                        "VALUE_P_SERIAL"  => $i,
                    ));
                }
            }else{
                header("location : order.php?func=o_list");
            }
        }
    }
//訂單回覆--資料更新================================================================
    function order_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $sql="update ".$cms_cfg['tb_prefix']."_order set o_status='".$_REQUEST["o_status"]."' , o_modifydate='".date("Y-m-d H:i:s")."' where o_id='".$_REQUEST["o_id"]."'";
        $rs = $db->query($sql);
        $db_msg = $db->report();
        if ( $db_msg == "" ) {
            if($_REQUEST["o_status"] == 2) $this->mail_delivery_notice(); //寄送出貨通知信
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$cms_cfg["manage_url"]."order.php?func=o_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
            $this->goto_target_page($goto_url);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
    //顯示訊息並重新導向
    function goto_target_page($url,$sec=0){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }

    //資料處理
    function data_processing(){
        switch ($_REQUEST["process_type"]){
            case "del":
                $this->order_del();
                break;
        }
    }

    //寄送出貨通知信
    function mail_delivery_notice() {
    	global $db,$TPLMSG,$cms_cfg;
        $this->ws_tpl_file = "templates/ws-manage-mail-tpl.html";
        $tpl = new TemplatePower( $this->ws_tpl_file );
        $tpl->prepare();
        $sql = "select * from ".$cms_cfg['tb_prefix']."_order where o_id='".$_REQUEST["o_id"]."'";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        $tpl->newBlock( "DELIVERY_NOTICE_MAIL" );
        $tpl->assign( array("MSG_DELIVERY_ID" => $TPLMSG['DELIVERY_ID'],
                    "MSG_DELIVERY_TOTAL_PRICE" => $TPLMSG['DELIVERY_TOTAL_PRICE'],
                    "MSG_DELIVERY_DATE" => $TPLMSG['DELIVERY_DATE'],
                    "VALUE_DELIVERY_ID" => $row['o_id'],
                    "VALUE_DELIVERY_TOTAL_PRICE" => $row['o_total_price'],
                    "VALUE_DELIVERY_DATE" => date("Y-m-d H:i:s") ));
        $tpl->assignGlobal( "VALUE_TERM" , $TPLMSG['DELIVERY_NOTICE']);
        $mail_content=$tpl->getOutputContent();
        $this->mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$row['o_email'],$mail_content,$TPLMSG["DELIVERY_NOTICE"]);
        return true;
    }

    function mail_send($from,$to,$mail_content,$mail_subject){
        global $TPLMSG,$cms_cfg;
        $from_email=explode(",",$from);
        $from_name=(trim($_SESSION[$cms_cfg['sess_cookie_name']]["sc_company"]))?$_SESSION[$cms_cfg['sess_cookie_name']]["sc_company"]:$from_email[0];
        //寄給送信者
        $MAIL_HEADER   = "From: ".$from_name." <".$from_email[0].">\n";
        $MAIL_HEADER  .= "Content-Type: text/html; charset=\"utf-8\"\n";
        $MAIL_HEADER  .= "X-Priority: 1\n";

        //$MAIL_HEADER   = "From: ".$from_name."<".$from_email[0].">"."\r\n";
        //$MAIL_HEADER  .= "Reply-To: ".$from_name."<".$from_email[0].">\r\n";
        //$MAIL_HEADER  .= "Return-Path: ".$from_name."<".$from_email[0].">\r\n";    // these two to set reply address
        //$MAIL_HEADER  .= "Content-Type: text/html; charset=\"utf-8\"\n";
        //$MAIL_HEADER  .= "X-Priority: 1\r\n";
        $MAIL_HEADER  .= "Message-ID: <".time()."-".$from_email[0].">\r\n";
        $MAIL_HEADER  .= "X-Mailer: PHP v".phpversion()."\r\n";          // These two to help avoid spam-filters

        $to_email = explode(",",$to);
        for($i=0;$i<count($to_email);$i++){
            mail($to_email[$i], $mail_subject, $mail_content,$MAIL_HEADER);
        }
				return true;
    }

    //檢查匯款
    function check_atm(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $xml_dir = "../atm/";
        $xml_pack = "../atm_pack/";
        if(is_dir($xml_dir)) {
            if($dh = opendir($xml_dir)) {
                $i = 0;
                while(($xml_file = readdir($dh)) !== false) {
                    if(filetype($xml_dir.$xml_file) == "file") {
                        $i++;
                        $xml_str = file_get_contents($xml_dir.$xml_file);
                        $str = str_replace("<?xml version=\"1.0\" encoding=\"BIG5\"?>","",$xml_str);
                        $xml = simplexml_load_string($str);
                        foreach($xml as $text) {
                            foreach($text as $key => $data) {
                                $atm[$key] = $data;
                            }
                        }
                        //比對資料庫
                        $sql="select * from ".$cms_cfg['tb_prefix']."_order where o_virtual_account='".$atm["RCPTId"]."'";
                        $selectrs = $db->query($sql);
                        if($db->numRows($selectrs)) {
                            $row = $db->fetch_array($selectrs,1);
                            $atm["CurAmt"] = sprintf("%d", $atm["CurAmt"]);
                            if($row["o_total_price"] == $atm["CurAmt"]) {
                                $sql="update ".$cms_cfg['tb_prefix']."_order set o_curamt='".$atm["CurAmt"]."' , o_trn_time='".$atm["TrnDt"]." ".$atm["TrnTime"]."' , o_remit_status='1' where o_virtual_account='".$atm["RCPTId"]."'";
                                $rs = $db->query($sql);
                                $db_msg = $db->report();
                                if($db_msg != "") {
                                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                                }elseif(!file_exists($xml_pack)){
                                    mkdir($xml_pack);
                                    copy($xml_dir.$xml_file, $xml_pack.$xml_file);
                                    unlink($xml_dir.$xml_file);
                                }else{
                                    copy($xml_dir.$xml_file, $xml_pack.$xml_file);
                                    unlink($xml_dir.$xml_file);
                                }
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }
    }
}
//ob_end_flush();
?>