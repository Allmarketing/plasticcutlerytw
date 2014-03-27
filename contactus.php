<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$cus = new CONTACTUS;
class CONTACTUS{
    function CONTACTUS(){
        global $db,$cms_cfg,$tpl,$main;
        $this->m_id=$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ID"];
        $this->security_mode = 1;//表單安全驗證模式:0=>math_security,1=>security_zone
        $this->contact_s_style = $cms_cfg['ws_module']['ws_contactus_s_style'];
        $this->contact_inquiry = $cms_cfg['ws_module']['ws_contactus_inquiry'];
        switch($_REQUEST["func"]){
            case "cu_add"://聯絡我們新增
                $this->ws_tpl_file = "templates/ws-contactus-form2-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $this->contactus_form();
                $this->ws_tpl_type=1;
                break;
            case "cu_replace"://聯絡我們更新資料(replace)
                $this->ws_tpl_file = "templates/ws-mail-tpl.html";
                $tpl = new TemplatePower( $this->ws_tpl_file );
                $tpl->prepare();
                $this->contactus_replace();
                $this->ws_tpl_type=0;
                break;
            default:    //聯絡我們列表
                $this->ws_tpl_file = "templates/ws-contactus-form2-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $this->contactus_form();
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
        $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方一般表單
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        $tpl->assignInclude( "AD_H", "templates/ws-fn-ad-h-tpl.html"); //橫式廣告模板
        $tpl->assignInclude( "AD_V", "templates/ws-fn-ad-v-tpl.html"); //直式廣告模板      
        $tpl->assignInclude( "CONTACT_S", "templates/ws-fn-contact-s-style".$this->contact_s_style."-tpl.html"); //稱呼樣版      
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["CONTACT_US"]);
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["products"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["products"]);//左方menu title
        $tpl->assignGlobal( "TAG_CONTACTUS_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["contactus"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-contactus"); //主要顯示區域的css設定
        $main->layer_link( $TPLMSG["CONTACT_US"]);
        $main->header_footer("contactus", $TPLMSG["CONTACT_US"]);
        $main->google_code(); //google analystics code , google sitemap code
        if($this->security_mode){
            $tpl->newBlock("IMAGE_SECURITY_ZONE");
            $main->security_zone();
            
        }else{
            $tpl->newBlock("MATH_SECURITY_ZONE");
            $main->math_security();
        }
        if($cms_cfg["ws_module"]["ws_left_main_pc"]==1){
            $main->left_fix_cate_list();
        }
    }

//聯絡我們--表單================================================================
    function contactus_form(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$ws_array,$main;
        $tpl->assignGlobal(array("VALUE_CU_NAME" => $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_name"],
                                 "VALUE_CU_COMPANY_NAME" => $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_company_name"],
                                 "VALUE_CU_ADDRESS" => $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_address"],
                                 "VALUE_CU_TEL" => $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_tel"],
                                 "VALUE_CU_FAX" => $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_fax"],
                                 "VALUE_CU_CONTENT" => $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_content"],
                                 "STR_M_CS_CK1" => ($_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_contact_s"]=="Mr.")?"selected":"",
                                 "STR_M_CS_CK2" => ($_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_contact_s"]=="Miss.")?"selected":"",
                                 "STR_M_CS_CK3" => ($_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_contact_s"]=="Mrs.")?"selected":"",
                                 "VALUE_CU_EMAIL" => $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_email"]));
        foreach($ws_array["contactus_cate"] as $key =>$value){
            $i++;
            $tpl->newBlock( "TAG_SELECT_CONTACTUS_CATE" );
            $tpl->assign( array("TAG_SELECT_CONTACTUS_CATE_NAME"  => $value,
                                "TAG_SELECT_CONTACTUS_CATE_VALUE" => $key));
        }
        //欄位名稱
        $tpl->assignGlobal( array("MSG_NAME"  => $TPLMSG['MEMBER_NAME'],
                                  "MSG_CONTACT_PERSON" =>$TPLMSG['CONTACT_PERSON'],
                                  "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                                  "MSG_MODE" => $TPLMSG['SEND'],
                                  "MSG_CATE" => $TPLMSG['CATE'],
                                  "MSG_PRODUCT_LIST" => $TPLMSG['CONTACTUS_PRODUCT_LIST'],
                                  "MSG_ADDRESS" => $TPLMSG['ADDRESS'],
                                  "MSG_TEL" => $TPLMSG['TEL'],
                                  "MSG_FAX" => $TPLMSG['FAX'],
                                  "MSG_ATTACH_FILES" => $TPLMSG['CONTACT_US_ATTACH_FILES'],
                                  "MSG_CONTENT" => $TPLMSG['CONTENT']));
        //國家下拉選單
        if($cms_cfg["ws_module"]["ws_country"]==1) {
            $main->country_select($_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_country"]);
        }
        //稱謂下拉選單
        $main->contact_s_select($_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_contact_s"]);
        //產品清單checkbox
        if($this->contact_inquiry){
            $main->contactus_product_list_checkbox($_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_pid"]);
        }
        //可附檔上傳
        if($cms_cfg["ws_module"]["ws_contactus_upfiles"]==1) {
            if($cms_cfg['contactus_upfiles_nums']){
                $tpl->newBlock("ATTACH_FILES");
                for($i=1;$i<=$cms_cfg['contactus_upfiles_nums'];$i++){
                    if($i==1){
                        $tpl->newBlock("AF_FIRST_ROW");
                        $tpl->assign(array(
                            "AF_ROWS" =>  $cms_cfg['contactus_upfiles_nums'],
                            "SERIAL" => $i
                        ));
                    }else{
                        $tpl->newBlock("AF_OTHER_ROW");
                        $tpl->assign("SERIAL" ,$i);
                    }
                }
            }
        }
        //顯示稱職欄位
        if($cms_cfg["ws_module"]["ws_contactus_position"]==1) {
            $tpl->newBlock("POSITION_FIELD");
            $tpl->assign(array(
                "MSG_POSITION"      => $TPLMSG['CONTACTUS_POSITION'],
                "VALUE_CU_POSITION" => $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["cu_position"],
            ));
        }        
        //聯絡我們資料
        $sql="select st_contactus_term from ".$cms_cfg['tb_prefix']."_service_term  where st_id='1'";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $row = $db->fetch_array($selectrs,1);
        $contentus_term=trim($row["st_contactus_term"]);
        if(!empty($contentus_term)){
            $row["st_contactus_term"]=$main->content_file_str_replace($row["st_contactus_term"]);
            $tpl->assignGlobal("MSG_CONTACTUS_TERM",$row["st_contactus_term"]);
        }
        //啟用驗証碼顯示錯誤訊息
        if($cms_cfg["ws_module"]["ws_security"]==1 && $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["security_error"]==1){
            $tpl->assignGlobal("MSG_ERROR_MESSAGE",$TPLMSG['SECURITY_ERROR']);
        }
    }
//聯絡我們--資料更新================================================================
    function contactus_replace(){
        global $db,$tpl,$cms_cfg,$ws_array,$TPLMSG,$main;
            $main->magic_gpc($_REQUEST);
            if($cms_cfg["ws_module"]["ws_security"]==1){
                if($this->security_mode){
                    require_once("./libs/libs-security-image.php");
                    $si = new securityImage();                    
                    $pass=(isset($_POST['callback']) && $si->isValid())?1:0;
                }else{
                    $pass=(isset($_POST['callback']) && $main->math_security_isvalue())?1:0;
                }
            }else{
                $pass=1;
            }
            if($pass){
                $file = $this->file_upload();
                //取得ip對應國家
                $ip_country = array();
                if($cms_cfg['ws_module']['ws_contactus_ipmap']){
                    $ip_country = array_merge($ip_country,$main->get_ip_country($_SERVER['REMOTE_ADDR']));                
                }
                if(is_array($_POST['cu_pid'])){
                    $pid_str = implode(",",$_POST['cu_pid']);
                }
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_contactus (
                        m_id,
                        cu_cate,
                        cu_status,
                        cu_company_name,
                        cu_contact_s,
                        cu_name,
                        cu_position,
                        cu_tel,
                        cu_fax,
                        cu_country,
                        cu_address,
                        cu_email,
                        cu_content,
                        cu_file,
                        cu_ip,
                        cu_ip_country,                        
                        cu_pid_str,                        
                        cu_modifydate
                    ) values (
                        '".$this->m_id."',
                        '".mysql_real_escape_string($_REQUEST["cu_cate"])."',
                        '0',
                        '".mysql_real_escape_string($_REQUEST["cu_company_name"])."',
                        '".$ws_array["contactus_s"][$_REQUEST["cu_contact_s"]]."',
                        '".mysql_real_escape_string($_REQUEST["cu_name"])."',
                        '".mysql_real_escape_string($_REQUEST["cu_position"])."',
                        '".mysql_real_escape_string($_REQUEST["cu_tel"])."',
                        '".mysql_real_escape_string($_REQUEST["cu_fax"])."',
                        '".mysql_real_escape_string($_REQUEST["cu_country"])."',
                        '".mysql_real_escape_string($_REQUEST["cu_address"])."',
                        '".mysql_real_escape_string($_REQUEST["cu_email"])."',
                        '".mysql_real_escape_string($_REQUEST["cu_content"])."',
                        '".$file."',
                        '".$ip_country['address']."',
                        '".$ip_country['country']."',                            
                        '".$pid_str."',                            
                        '".date("Y-m-d H:i:s")."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    unset($_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]);
                    //新增完成寄送確認信
                    //寄送訊息
                    $tpl->newBlock("CONTACTUS_MAIL");
                    $tpl->assign(array(
                            "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                            "MSG_CATE" => $TPLMSG['CATE'],
                            "MSG_ADDRESS" => $TPLMSG['ADDRESS'],
                            "MSG_TEL" => $TPLMSG['TEL'],
                            "MSG_FAX" => $TPLMSG['FAX'],
                            "MSG_CONTENT" => $TPLMSG['CONTENT'],
                            "MSG_ATTACH_FILES" => $TPLMSG['CONTACT_US_ATTACH_FILES'],
                            "VALUE_CUC_SUBJECT"  => $ws_array["contactus_cate"][$_REQUEST["cu_cate"]],
                            "VALUE_CU_COMPANY_NAME" => $_REQUEST["cu_company_name"],
                            "VALUE_CU_FAX" => $_REQUEST["cu_fax"],
                            "VALUE_CU_TEL" => $_REQUEST["cu_tel"],
                            "VALUE_CU_ADDRESS" => $_REQUEST["cu_address"],
                            "VALUE_CU_EMAIL" => $_REQUEST["cu_email"],
                            "VALUE_CU_CONTENT" => (get_magic_quotes_gpc())?stripcslashes($_REQUEST["cu_content"]):$_REQUEST["cu_content"],
                    ));
                    if($cms_cfg['ws_module']['ws_contactus_s_style']==1){//西式稱謂
                        $tpl->newBlock("CONTACT_S_STYLE_1");
                    }elseif($cms_cfg['ws_module']['ws_contactus_s_style']==2){//中式稱謂
                        $tpl->newBlock("CONTACT_S_STYLE_2");
                    }
                    $tpl->assign(array(
                        "MSG_CONTACT_PERSON" =>$TPLMSG['CONTACT_PERSON'],     
                        "VALUE_CU_NAME"      => $_REQUEST["cu_name"],      
                        "VALUE_CU_CONTACT_S" => $ws_array["contactus_s"][$_REQUEST["cu_contact_s"]],                        
                    ));
                    if($file){
                        $tpl->newBlock("UPFILE_ROW");
                        $tpl->assign("VALUE_CU_FILE",$this->cu_file);
                    }
                    //國家欄位
                    if($cms_cfg["ws_module"]["ws_country"]==1) {
                        $tpl->newBlock("CONTACTUS_COUNTRY_ZONE");
                        $tpl->assign(array("MSG_COUNTRY" =>$TPLMSG['COUNTRY'],
                                            "VALUE_CU_COUNTRY" =>$_REQUEST["cu_country"]
                        ));
                    }
                    if($this->contact_inquiry){
                        $prod_arr = $main->contactus_product_list($_POST['cu_pid']);
                        if(!empty($prod_arr)){
                            $tpl->newBlock("PROD_LIST_ROW");
                            $tpl->assign("MSG_PRODUCT_LIST" , $TPLMSG['CONTACTUS_PRODUCT_LIST']);
                            $tpl->assign("VALUE_CU_PRODUCT_LIST",implode(',',$prod_arr));
                        }
                    }
                    //顯示稱職欄位
                    if($cms_cfg["ws_module"]["ws_contactus_position"]==1) {
                        $tpl->newBlock("POSITION_FIELD");
                        $tpl->assign(array(
                            "MSG_POSITION"      => $TPLMSG['CONTACTUS_POSITION'],
                            "VALUE_CU_POSITION" => $_REQUEST["cu_position"],
                        ));
                    }                    
                    //引入聯絡我們自動回覆說明
                    $sql="select st_inquiry_mail from ".$cms_cfg['tb_prefix']."_service_term where st_id='1'";
                    $selectrs = $db->query($sql);
                    $row = $db->fetch_array($selectrs,1);
                    $rsnum = $db->numRows($selectrs);
                    if(!$rsnum){
                        $tpl->assignGlobal( "VALUE_TERM" , $TPLMSG['CONTACT_US_MAIL_NOTICE']);
                    }else{
                        $tpl->assignGlobal( "VALUE_TERM" , $row['st_inquiry_mail']);
                    }
                    $mail_content=$tpl->getOutputContent();
                    $main->ws_mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$_REQUEST["cu_email"],$mail_content,$TPLMSG['CONTACT_US'],"cu","");
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }else{
                $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]=array();
                foreach($_REQUEST as $key => $value){
                    if(eregi("cu_",$key)){
                        $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["$key"]=$value;
                    }
                }
                $_SESSION[$cms_cfg['sess_cookie_name']]["contactus"]["security_error"]=1;
                header("location:contactus.php");
                die();
            }
    }
    //顯示訊息並重新導向
    function goto_target_page($url,$sec=1){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }

    function file_upload(){
        global $db,$tpl,$cms_cfg;
        if($_FILES["cu_file"]){
                $file = $_FILES["cu_file"];
                foreach($file as $key => $V){
                        $text = "file_".$key;
                        $$text = $V;
                }

                $num = 1;
                $dir = $_SERVER['DOCUMENT_ROOT'].$cms_cfg['file_root']."upload_files/cu_file/";
                if(!is_dir($dir)){
                    mkdir($dir, 0777, true) || die("can't create dir of cu_file");
                    chmod($dir, 0777);
                }
                for($i=0;$i<count($file_name);$i++){
                        $file_name_array = explode(".",$file_name[$i]);
                        $sub_name = $file_name_array[count($file_name_array) - 1];

                        if($file_error[$i] == 0 && $file_name[$i] != "" && $sub_name != "exe"){
                                $date_name[$i] = date("Y-m-d-H-i-s")."-file".$num.".".$sub_name;
                                $route = $dir . $date_name[$i];
                                move_uploaded_file($file_tmp_name[$i],$route);
                                chmod($route, 0777);
                                $num++;
                        }
                }

                if(!empty($date_name)){
                        foreach($date_name as $key => $V){
                                $file_str[] = "<a href=\"http://".$cms_cfg['server_name'].$cms_cfg['file_root']."upload_files/cu_file/".$V."\" target=\"_blank\">".$V."</a>";
                        }
                        $this->cu_file = implode(" , ",$file_str);
                        return implode("|",$date_name);
                }
        }
    }       
}
?>