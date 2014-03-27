<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])  || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_contactus"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$contactus = new CONTACTUS;
class CONTACTUS{
    function CONTACTUS(){
        global $db,$cms_cfg,$tpl;
        $this->current_class="CU";
        $this->contact_s_style = $cms_cfg['ws_module']['ws_contactus_s_style'];
        switch($_REQUEST["func"]){
            case "cu_list"://聯絡我們列表
                $this->ws_tpl_file = "templates/ws-manage-contactus-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->contactus_list();
                $this->ws_tpl_type=1;
                break;
            case "cu_replace"://聯絡我們更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->contactus_replace();
                $this->ws_tpl_type=1;
                break;
            case "cu_del"://聯絡我們刪除
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->contactus_del();
                $this->ws_tpl_type=1;
                break;
            //////////////////////////////////////////
            case "cur_add"://聯絡我們回覆新增
                $this->ws_tpl_file = "templates/ws-manage-contactus-reply-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $this->contactus_reply_form("add");
                $this->ws_tpl_type=1;
                break;
            case "cur_replace"://聯絡我們回覆更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->contactus_reply_replace();
                $this->ws_tpl_type=1;
                break;
            //////////////////////////////////////////
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            default:    //聯絡我們列表
                $this->ws_tpl_file = "templates/ws-manage-contactus-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->contactus_list();
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
        $tpl->assignGlobal("CSS_BLOCK_CONTACTUS","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }
//聯絡我們--列表================================================================
    function contactus_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $tpl->assignGlobal(array("MSG_NOW_CATE" =>$TPLMSG["NOW_CATE"],
                                 "TAG_NOW_CATE" =>$TPLMSG["NO_CATE"],
        ));
        //聯絡我們分類
        $i=0;
        foreach($ws_array["contactus_cate"] as $key =>$value){
            $i++;
            $tpl->newBlock( "CONTACTUS_CATE_LIST" );
            $tpl->assign( array("VALUE_CUC_SUBJECT"  => $value,
                                "VALUE_CU_CATE" => $key,
                                "VALUE_CUC_SERIAL" => $i,
            ));
            if($i%4==0){
                $tpl->assign("TAG_CONTACTUS_CATE_TRTD","</tr><tr>");
            }
            if($key==$_REQUEST["cu_cate"]){
                $tpl->assignGlobal("TAG_NOW_CATE",$value);
            }
        }
        //聯絡我們列表
        $sql="select * from ".$cms_cfg['tb_prefix']."_contactus where cu_id > '0'";
        //附加條件
        $and_str="";
        if(!empty($_REQUEST["cu_cate"])){
            $and_str .= " and cu_cate = '".$_REQUEST["cu_cate"]."'";
        }
        if($_REQUEST["st"]=="all"){
            $and_str .= " and (cu_name like '%".$_REQUEST["sk"]."%' or cu_content like '%".$_REQUEST["sk"]."%')";
        }
        if($_REQUEST["st"]=="cu_name"){
            $and_str .= " and cu_name like '%".$_REQUEST["sk"]."%'";
        }
        if($_REQUEST["st"]=="cu_content"){
            $and_str .= " and cu_content like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by cu_modifydate desc ";
        //取得總筆數
        $selectrs = $db->query($sql);
        $total_records    = $db->numRows($selectrs);
        //取得分頁連結
        $func_str="contactus.php?func=cu_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        //分頁並重新組合包含limit的sql語法
        $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("VALUE_TOTAL_BOX" => $rsnum,
                                  "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                  "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],

        ));
        switch($_REQUEST["st"]){
            case "all" :
                $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK0", "selected");
                break;
            case "cu_name" :
                $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK1", "selected");
                break;
            case "cu_content" :
                $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK2", "selected");
                break;
        }
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "CONTACTUS_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_CU_CATE"  => $row["cu_cate"],
                                "VALUE_CU_ID"  => $row["cu_id"],
                                "VALUE_CU_NAME" => $row["cu_name"],
                                "VALUE_CU_CONTENT" => strip_tags($row["cu_content"]),
                                "VALUE_CU_MODIFYDATE" => $row["cu_modifydate"],
                                "VALUE_CU_STATUS" => ($row["cu_status"])?$TPLMSG['REPLY_YES']:$TPLMSG['REPLY_NO'],
                                "VALUE_CU_SERIAL" => $i,
                                "VALUE_CUC_SUBJECT"  => $ws_array["contactus_cate"][$row["cu_cate"]],
            ));
        }
    }

//聯絡我們--資料更新================================================================
    function contactus_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        $sql="
            insert into ".$cms_cfg['tb_prefix']."_contactus (
                cuc_id,
                cu_status,
                cu_sort,
                cu_subject,
                cu_content,
                cu_modifydate
            ) values (
                '".$_REQUEST["cuc_id"]."',
                '".$_REQUEST["cu_status"]."',
                '".$_REQUEST["cu_sort"]."',
                '".$_REQUEST["cu_subject"]."',
                '".$_REQUEST["cu_content"]."',
                '".date("Y-m-d H:i:s")."'
            )";
        $rs = $db->query($sql);
        $db_msg = $db->report();
        if ( $db_msg == "" ) {
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$cms_cfg["manage_url"]."contactus.php?func=cu_list&cuc_id=".$_REQUEST["cuc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
            $this->goto_target_page($goto_url);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
//聯絡我們--刪除--資料刪除可多筆處理================================================================
    function contactus_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["cu_id"]){
            $cu_id=array(0=>$_REQUEST["cu_id"]);
        }else{
            $cu_id=$_REQUEST["id"];
        }
        if(!empty($cu_id)){
            $cu_id_str = implode(",",$cu_id);
            //刪除勾選的聯絡我們
            $this->file_del($cu_id_str);
            $sql="delete from ".$cms_cfg['tb_prefix']."_contactus where cu_id in (".$cu_id_str.")";
            $rs = $db->query($sql);
            $sql="delete from ".$cms_cfg['tb_prefix']."_contactus_reply where cu_id in (".$cu_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."contactus.php?func=cu_list&cuc_id=".$_REQUEST["cuc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//聯絡我們回覆--表單================================================================
    function contactus_reply_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "STR_CU_STATUS_CK1" => "",
                                  "STR_CU_STATUS_CK0" => "checked",
                                  "VALUE_ACTION_MODE" => $action_mode
        ));
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],
            ));
        }
        //是否記錄ip國家
        if($cms_cfg['ws_module']['ws_contactus_ipmap']){
            $tpl->newBlock("IP_COUNTRY_ROW");
        }
        //顯示稱職欄位
        if($cms_cfg["ws_module"]["ws_contactus_position"]==1) {
            $tpl->newBlock("POSITION_FIELD");
        }           
        //國家欄位
        if($cms_cfg["ws_module"]["ws_country"]==1) {
            $tpl->newBlock("CONTACTUS_COUNTRY_ZONE");
        }        
        //帶入要回覆的聯絡我們資料
        if(!empty($_REQUEST["cu_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_contactus where cu_id='".$_REQUEST["cu_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $file = $this->file_load($row["cu_file"]);
                $tpl->assignGlobal( array("VALUE_M_ID"  => $row["m_id"],
                                          "VALUE_CUC_SUBJECT"  => $ws_array["contactus_cate"][$row["cu_cate"]],
                                          "VALUE_CU_ID"  => $row["cu_id"],
                                          "VALUE_CU_COMPANY" => $row["cu_company_name"],
                                          "VALUE_CU_NAME" => $row["cu_name"],
                                          "VALUE_CU_CONTACT_S" => $ws_array['contactus_s'][$row["cu_contact_s"]],
                                          "VALUE_CU_POSITION" => $row["cu_position"],
                                          "VALUE_CU_TEL" => $row["cu_tel"],
                                          "VALUE_CU_FAX" => $row["cu_fax"],
                                          "VALUE_CU_ADDRESS" => $row["cu_address"],
                                          "VALUE_CU_EMAIL" => $row["cu_email"],
                                          "VALUE_CU_CONTENT" => $row["cu_content"],
                                          "VALUE_CU_COUNTRY" => $row["cu_country"],
                                          "VALUE_CU_IP" => $row["cu_ip"],
                                          "VALUE_CU_IP_COUNTRY" => $row["cu_ip_country"],
                                          "VALUE_CU_STATUS" => $ws_array["contactus_status"][$row["cu_status"]],
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
                //稱謂類型
                $tpl->newBlock("CONTACT_S_".$this->contact_s_style);
                if($file){
                    $tpl->newBlock("UPFILE_ROW");
                    $tpl->assign("VALUE_CU_FILE" , $file);
                }
                if($row['cu_pid_str']){
                    $prod_arr = $main->contactus_product_list($row['cu_pid_str']);
                    if($prod_arr){
                        $tpl->newBlock('INQUIRY_PRODUCT_LIST');
                        $tpl->assign("PRODUCT_STR",implode(',',$prod_arr));
                    }
                }
                //回覆資料列表
                $sql="select * from ".$cms_cfg['tb_prefix']."_contactus_reply where cu_id='".$_REQUEST["cu_id"]."' order by cur_modifydate desc limit 1";
                $selectrs = $db->query($sql);
                while($row = $db->fetch_array($selectrs,1)){
                    $tpl->assign("_ROOT.VALUE_CU_DEFAULT_REPLY",$row["cur_content"]);//取出做為預設回覆的內容
                    $tpl->newBlock( "CONTACTUS_REPLY_LIST" );
                    $tpl->assign( array("VALUE_CUR_CONTENT"  => nl2br($row["cur_content"]),
                                        "VALUE_CUR_MODIFYDATE" => $row["cur_modifydate"]
                    ));
                }
            }else{
                header("location : contactus.php?func=cu_list");
                die();
            }
        }
    }
//聯絡我們回覆--資料更新================================================================
    function contactus_reply_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $re_time = date("Y-m-d H:i:s");
        $sql="
            insert into ".$cms_cfg['tb_prefix']."_contactus_reply (
                cu_id,
                cur_content,
                cur_modifydate
            ) values (
                '".$_REQUEST["cu_id"]."',
                '".$_REQUEST["cur_content"]."',
                '".$re_time."'
            )";
        $rs = $db->query($sql);
        $db_msg = $db->report();
        $sql="update ".$cms_cfg['tb_prefix']."_contactus set cu_status='1' where cu_id='".$_REQUEST["cu_id"]."'";
        $rs = $db->query($sql);
        $db_msg = $db->report();
        if ( $db_msg == "" ) {
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $this->mail_cur($re_time); //寄送回覆信
            $goto_url=$cms_cfg["manage_url"]."contactus.php?func=cu_list&cuc_id=".$_REQUEST["cuc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
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
                if($_REQUEST["ws_table"]=="cu"){
                    $this->contactus_del();
                }
                break;
        }
    }
    //回覆完成寄送回覆信
    function mail_cur($re_time="") {
        global $TPLMSG,$cms_cfg;
        $this->ws_tpl_file = "templates/ws-manage-mail-tpl.html";
        $tpl = new TemplatePower( $this->ws_tpl_file );
        $tpl->prepare();
        $tpl->newBlock( "CONTACTUS_MAIL" );
        $tpl->assign( array("MSG_CONTENT" => $TPLMSG['CONTENT'],
                            "MSG_MODIFYDATE" => $TPLMSG['CONTACT_US_REPLY_TIME'],
                            "VALUE_CUR_CONTENT" => $_REQUEST["cur_content"],
                            "VALUE_CUR_MODIFYDATE" => $re_time
        ));
        $tpl->assignGlobal( "VALUE_TERM" , $TPLMSG['CONTACT_US_REPLY']);
        $mail_content=$tpl->getOutputContent();
        $this->mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$_REQUEST["cu_email"],$mail_content,$TPLMSG["CONTACT_US_REPLY"]);
        return true;
    }

    function mail_send($from,$to,$mail_content,$mail_subject){
        global $TPLMSG,$cms_cfg;
        $from_email=explode(",",$from);
        $from_name=(trim($_SESSION[$cms_cfg['sess_cookie_name']]["sc_company"]))?$_SESSION[$cms_cfg['sess_cookie_name']]["sc_company"]:$from_email[0];
        //寄給送信者
        $MAIL_HEADER   = "From: =?UTF-8?B?".base64_encode($from_name)."?= <".$from_email[0].">\n";
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
	function file_load($file = 0){
		global $cms_cfg;
		if($file != ""){
			$file_array = explode("|",$file);
			foreach($file_array as $key => $V){
				$file_link[$key] = "<a href=\"".$cms_cfg['file_root']."upload_files/cu_file/".$V."\" target=\"_blank\">".$V."</a>";
			}
			return implode(" , ",$file_link);
		}
	}
	
	function file_del($cu_id = 0){
		global $db,$cms_cfg;
		if($cu_id != ""){
                    $sql="select * from ".$cms_cfg['tb_prefix']."_contactus where cu_id in (".$cu_id.")";
                    $selectrs = $db->query($sql);
                    $rsnum    = $db->numRows($selectrs);

                    if($rsnum > 0){
                        $dir = $_SERVER['DOCUMENT_ROOT'].$cms_cfg['file_root']."upload_files/cu_file/";
                        while($row = $db->fetch_array($selectrs,1)){
                            if($row["cu_file"]){
                                $file_array = explode("|",$row["cu_file"]);
                                foreach($file_array as $key => $V){
                                    $path = $dir . $V;
                                    if(file_exists($path)){
                                        unlink($path);
                                    }
                                }
                            }
                        }
                    }
		}
	}
}
//ob_end_flush();
?>