<?php
//error_reporting(15);
include_once("libs/libs-sysconfig.php");
$member = new MEMBER;
class MEMBER{
    function MEMBER(){
        global $db,$cms_cfg,$tpl,$main;
        $this->m_id=$_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ID"];
        $this->contact_s_style = $cms_cfg['ws_module']['ws_contactus_s_style'];
        switch($_REQUEST["func"]){
            case "activate":
                $this->ws_tpl_file = "templates/ws-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->account_activate();
                $this->ws_tpl_type=1;
                break;
            case "m_zone"://會員專區
                if(empty($this->m_id)){
                    header("Location: member.php?func=m_add");
                    die();
                }
                $this->ws_tpl_file = "templates/ws-member-zone-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->member_zone();
                $this->ws_tpl_type=1;
                break;
            case "m_add"://會員管理新增
                $this->ws_tpl_file = "templates/ws-member-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JQUERY_UI_SCRIPT");
                $tpl->newBlock("DATEPICKER_SCRIPT");
                $this->member_form("add");
                $this->ws_tpl_type=1;
                break;
            case "m_mod"://會員管理修改
                if(empty($this->m_id)){
                    header("Location: member.php?func=m_add");
                }
                $this->ws_tpl_file = "templates/ws-member-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JQUERY_UI_SCRIPT");
                $tpl->newBlock("DATEPICKER_SCRIPT");
                $this->member_form("mod");
                $this->ws_tpl_type=1;
                break;
            case "m_forget"://忘記密碼
                $this->member_forget_password();
                $this->ws_tpl_type=1;
                break;
            case "m_sps"://密碼寄送完成顯示訊息
                $this->ws_tpl_file = "templates/ws-member-forget-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->member_send_password_success_str();
                $this->ws_tpl_type=1;
                break;
            case "m_replace"://會員管理更新資料(replace)
                $this->ws_tpl_file = "templates/ws-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->member_replace();
                $this->ws_tpl_type=1;
                break;
            default:    //會員專區
                if(!empty($this->m_id)){
                    header("Location: member.php?func=m_zone");
                }
                $this->ws_tpl_file = "templates/ws-login-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
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
        global $tpl,$cms_cfg,$db,$TPLMSG,$ws_array,$main;
        $tpl = new TemplatePower( $cms_cfg['base_all_tpl'] );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        if(!empty($this->m_id)){
            $tpl->assignInclude( "LEFT", $cms_cfg['base_left_member_tpl']); //左方會員專區表單
        }
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        $tpl->assignInclude( "CONTACT_S", "templates/ws-fn-contact-s-style".$this->contact_s_style."-tpl.html"); //稱呼樣版      
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["member"]);//左方menu title
        $tpl->assignGlobal( "TAG_CATE_DESC", $ws_array["left_desc"]["member"]);//左方menu title
        $tpl->assignGlobal( "TAG_MEMBER_CURRENT" , "class='current'"); //上方menu current
        $tpl->assignGlobal( "TAG_MAIN" , $ws_array["main"]["member"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_CLASS" , "main-member"); //主要顯示區域的css設定
        $main->header_footer("");
        $main->google_code(); //google analystics code , google sitemap code
        //定義目前語系的表單檢查JS
        $tpl->assignGlobal("TAG_LANG",$cms_cfg['language']);
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["MEMBER_ZONE"]);
        $tpl->assignGlobal( "MSG_MEMBER_LOGIN",$TPLMSG["MEMBER_LOGIN"]);
        $tpl->assignGlobal( "TAG_MEMBER_LOGIN" , $TPLMSG["MEMBER_LOGIN"]);
        $tpl->assignGlobal( "TAG_MEMBER_JOIN" , $TPLMSG["MEMBER_JOIN"]);
        $tpl->assignGlobal( "TAG_MEMBER_LOGOUT" , $TPLMSG["MEMBER_LOGOUT"]);
        if($_GET){
            $main->layer_link($TPLMSG["MEMBER_ZONE"],$cms_cfg['base_root']."member.php");
        }else{
            $main->layer_link($TPLMSG["MEMBER_ZONE"]);
        }
        //頁首會員登入區
        if(empty($_SESSION[$cms_cfg['sess_cookie_name']]['MEMBER_ID'])){
            $tpl->newBlock("INDEX_LOGIN_ZONE");
        }else{
            $tpl->newBlock("INDEX_LOGOUT_ZONE");
        }
    }

    //會員專區================================================================
    function member_zone(){
        global $db,$tpl;
        $_REQUEST["type"]=(empty($_REQUEST["type"]))?"list":$_REQUEST["type"];
        switch($_REQUEST["mzt"]){
            case "data"://基本資料修改
                $this->member_form("mod");
                break;
            case "order"://訂單查詢
                $this->member_order($_REQUEST["type"],$_REQUEST["o_id"]);
                break;
            case "inquiry"://詢問信查詢
                $this->member_inquiry($_REQUEST["type"],$_REQUEST["i_id"]);
                break;
            case "contactus"://聯絡我們
                $this->member_contactus($_REQUEST["type"],$_REQUEST["cu_id"]);
                break;
            case "bonus"://紅利點數
                $this->member_bonus();
                break;
            default:
                $this->member_form("mod");
                break;
        }
    }

    //會員管理--表單================================================================
    function member_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$ws_array,$main;
        $main->load_js_msg();
        //欄位名稱
        $tpl->assignGlobal( array("TAG_MAIN_FUNC"  => $TPLMSG['MEMBER_JOIN'],
                                  "MSG_MEMBER_FNAME"  => $TPLMSG['MEMBER_FNAME'],
                                  "MSG_MEMBER_LNAME"  => $TPLMSG['MEMBER_LNAME'],
                                  "MSG_CHECK_ACCOUNT" => $TPLMSG['MEMBER_CHECK_ACCOUNT'],
                                  "MSG_MODE" => $TPLMSG['SEND'],
                                  "MSG_ACCOUNT" => $TPLMSG["LOGIN_ACCOUNT"],
                                  "MSG_PASSWORD" => $TPLMSG["LOGIN_PASSWORD"],
                                  "MSG_CONTACT_PERSON" =>$TPLMSG['CONTACT_PERSON'],
                                  "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                                  "MSG_VALID_PASSWORD" => $TPLMSG['MEMBER_CHECK_PASSWORD'],
                                  "MSG_BIRTHDAY" => $TPLMSG["BIRTHDAY"],
                                  "MSG_ZIP" => $TPLMSG["ZIP"],
                                  "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                                  "MSG_TEL" => $TPLMSG["TEL"],
                                  "MSG_MUTI_TEL_NOTICE" => $TPLMSG["MEMBER_MUTI_TEL_NOTICE"],
                                  "MSG_FAX" => $TPLMSG["FAX"],
                                  "MSG_MUTI_FAX_NOTICE" => $TPLMSG["MEMBER_MUTI_FAX_NOTICE"],
                                  "MSG_EMAIL" => $TPLMSG["EMAIL"],
                                  "MSG_MUTI_EMAIL_NOTICE" => $TPLMSG["MEMBER_MUTI_EMAIL_NOTICE"],
                                  "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
                                  "MSG_MUTI_CELLPHONE_NOTICE" => $TPLMSG["MEMBER_MUTI_CELLPHONE_NOTICE"],
                                  "MSG_YES" => $TPLMSG["YES"],
                                  "MSG_NO" => $TPLMSG["NO"],
                                  "MSG_SUBSCRIBE" => $TPLMSG["SUBSCRIBE"],
                                  "MSG_ACCEPT_SERVICE_TERM" => $TPLMSG["MEMBER_ACCEPT_SERVICE_TERM"],
                                  "STR_M_CS_CK1" =>"selected",
                                  "STR_M_CS_CK2" => "",
                                  "STR_M_CS_CK3" => "",
                                  "STR_M_EPAPER_STATUS_CK1" => "checked",
                                  "STR_M_EPAPER_STATUS_CK0" => "",
                                  "TAG_SEND_SHOW" => "",
                                  "VALUE_ACTION_MODE" => $action_mode
        ));
        //如果有詢問信管理，顯示公司及傳真欄位
        if($cms_cfg["ws_module"]["ws_inquiry"]){
            $tpl->newBlock( "COMPANY_ZONE" );
        }
        //如果為修改模式,帶入資料庫資料
        if($action_mode=="mod" && !empty($this->m_id)){
            $tpl->newBlock( "MEMBER_MOD_MODE" );
            $sql="select * from ".$cms_cfg['tb_prefix']."_member where m_id='".$this->m_id."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                //修改會員，顯示e-mail欄位
                $main->layer_link( $TPLMSG['MEMBER_DATA_MOD']);
                $tpl->newBlock( "MOD_EMAIL" );
                $tpl->assignGlobal( array("TAG_MAIN_FUNC"  => $TPLMSG['MEMBER_DATA_MOD'],
                                          "VALUE_M_ID"  => $row["m_id"],
                                          "VALUE_M_ACCOUNT" => $row["m_account"],
                                          "VALUE_M_PASSWORD" => $row["m_password"],
                                          "VALUE_M_COMPANY_NAME" => $row["m_company_name"],
                                          "VALUE_M_FNAME" => $row["m_fname"],
                                          "VALUE_M_LNAME" => $row["m_lname"],
                                          "VALUE_M_BIRTHDAY" => $row["m_birthday"],
                                          "VALUE_M_ZIP" => $row["m_zip"],
                                          "VALUE_M_ADDRESS" => $row["m_address"],
                                          "VALUE_M_TEL" => $row["m_tel"],
                                          "VALUE_M_FAX" => $row["m_fax"],
                                          "VALUE_M_EMAIL" => $row["m_email"],
                                          "VALUE_M_CELLPHONE" => $row["m_cellphone"],
                                          "STR_M_CS_CK1" => ($row["m_contact_s"]=="Mr.")?"selected":"",
                                          "STR_M_CS_CK2" => ($row["m_contact_s"]=="Miss.")?"selected":"",
                                          "STR_M_CS_CK3" => ($row["m_contact_s"]=="Mrs.")?"selected":"",
                                          "STR_M_EPAPER_STATUS_CK1" => ($row["m_epaper_status"]==1)?"checked":"",
                                          "STR_M_EPAPER_STATUS_CK0" => ($row["m_epaper_status"]==0)?"checked":"",
                                          "MSG_MODE" => $TPLMSG['MODIFY'],
                                          "TAG_SEND_SHOW" => "",
                ));
            }else{
                header("location : member.php?func=m_add");
                die();
            }
        }else{
            //$tpl->newBlock( "MEMBER_ADD_PIC" );
            //新增模式顯示服務條款
            $tpl->newBlock( "MEMBER_ADD_MODE" );
            $main->layer_link($TPLMSG['MEMBER_JOIN']);
            //$tpl->newBlock( "SERVICE_TERM_SHOW" );
        }
        //國家下拉選單
        if($cms_cfg["ws_module"]["ws_country"]==1) {
            $main->country_select($row["m_country"]);
        }
        $main->contact_s_select($row['m_contact_s'],$zone="MEMBER");
    }
    //會員管理--資料更新================================================================
    function member_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        switch ($_REQUEST["action_mode"]){
            case "add":
                $max_sort = $main->get_max_sort_value($cms_cfg['tb_prefix']."_member",'m');
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_member (
                        mc_id,
                        m_sort,
                        m_status,
                        m_modifydate,
                        m_account,
                        m_password,
                        m_company_name,
                        m_contact_s,
                        m_fname,
                        m_lname,
                        m_birthday,
                        m_sex,
                        m_country,
                        m_zip,
                        m_address,
                        m_tel,
                        m_fax,
                        m_cellphone,
                        m_email,
                        m_epaper_status
                    ) values (
                        '1',
                        '".$max_sort."',
                        '1',
                        '".date("Y-m-d H:i:s")."',
                        '".$_REQUEST["m_account"]."',
                        '".$_REQUEST["m_password"]."',
                        '".$_REQUEST["m_company_name"]."',
                        '".$_REQUEST["m_contact_s"]."',
                        '".$_REQUEST["m_fname"]."',
                        '".$_REQUEST["m_lname"]."',
                        '".$_REQUEST["m_birthday"]."',
                        '".$_REQUEST["m_sex"]."',
                        '".$_REQUEST["m_country"]."',
                        '".$_REQUEST["m_zip"]."',
                        '".$_REQUEST["m_address"]."',
                        '".$_REQUEST["m_tel"]."',
                        '".$_REQUEST["m_fax"]."',
                        '".$_REQUEST["m_cellphone"]."',
                        '".$_REQUEST["m_account"]."',
                        '".$_REQUEST["m_epaper_status"]."'
                    )";//新增時e-mail等於account
                $goto_url=$cms_cfg["base_url"];
                break;
            case "mod":
                $sql="
                    update ".$cms_cfg['tb_prefix']."_member set
                        m_modifydate='".date("Y-m-d H:i:s")."',
                        m_password='".$_REQUEST["m_password"]."',
                        m_company_name='".$_REQUEST["m_company_name"]."',
                        m_contact_s='".$_REQUEST["m_contact_s"]."',
                        m_fname='".$_REQUEST["m_fname"]."',
                        m_lname='".$_REQUEST["m_lname"]."',
                        m_birthday='".$_REQUEST["m_birthday"]."',
                        m_sex='".$_REQUEST["m_sex"]."',
                        m_country='".$_REQUEST["m_country"]."',
                        m_zip='".$_REQUEST["m_zip"]."',
                        m_address='".$_REQUEST["m_address"]."',
                        m_tel='".$_REQUEST["m_tel"]."',
                        m_fax='".$_REQUEST["m_fax"]."',
                        m_cellphone='".$_REQUEST["m_cellphone"]."',
                        m_email='".$_REQUEST["m_email"]."',
                        m_epaper_status='".$_REQUEST["m_epaper_status"]."'
                    where m_id='".$this->m_id."'";
                $goto_url=$cms_cfg["base_url"]."member.php?func=m_mod";
                break;
        }
        if(!empty($sql)){
            $rs = $db->query($sql);
            $db_msg = $db->report();
            $this->ws_tpl_file = "templates/ws-msg-action-tpl.html";
            $this->ws_load_tp($this->ws_tpl_file);            
            if ( $db_msg == "" ) {
                if($_REQUEST["action_mode"]=="add"){
                    //已有購物或詢價時直接登入
                    if(!empty($_SESSION[$cms_cfg['sess_cookie_name']]["CART_PID"])){
                        $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ID"]=$db->get_insert_id();
                        $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_ACCOUNT"]=$_REQUEST["m_account"];
                        $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_NAME"]=$_REQUEST["m_name"];
                        $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_CATE"]="";
                        $_SESSION[$cms_cfg['sess_cookie_name']]["MEMBER_DISCOUNT"]=100;
                        $goto_url=$cms_cfg["base_url"]."cart.php?func=c_finish";
                    }
                    $this->ws_tpl_file = "templates/ws-mail-tpl.html";
                    $mtpl = new TemplatePower( $this->ws_tpl_file );
                    $mtpl->prepare();
                    //寄送訊息
                    $sql="select st_join_member_mail from ".$cms_cfg['tb_prefix']."_service_term where st_id='1'";
                    $selectrs = $db->query($sql);
                    $row = $db->fetch_array($selectrs,1);
                    //如果有詢問信管理，顯示公司及傳真欄位
                    if($cms_cfg["ws_module"]["ws_inquiry"]){
                        $mtpl->newBlock( "COMPANY_ZONE" );
                    }
                    $mtpl->newBlock( "MEMBER_MAIL" );
                    $mtpl->assignGlobal( array("MSG_MEMBER_NAME"  => $TPLMSG['MEMBER_NAME'],
                                              "MSG_ACCOUNT" => $TPLMSG["LOGIN_ACCOUNT"],
                                              "MSG_PASSWORD" => $TPLMSG["LOGIN_PASSWORD"],
                                              "MSG_CONTACT_PERSON" =>$TPLMSG['CONTACT_PERSON'],
                                              "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                                              "MSG_BIRTHDAY" => $TPLMSG["BIRTHDAY"],
                                              "MSG_SEX" => $TPLMSG["SEX"],
                                              "MSG_ZIP" => $TPLMSG["ZIP"],
                                              "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                                              "MSG_TEL" => $TPLMSG["TEL"],
                                              "MSG_FAX" => $TPLMSG["FAX"],
                                              "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
                                              "MSG_SUBSCRIBE" => $TPLMSG["SUBSCRIBE"],
                                              "VALUE_M_ACCOUNT" => $_REQUEST["m_account"],
                                              "VALUE_M_PASSWORD" => $_REQUEST["m_password"],
                                              "VALUE_M_CONTACT_S" => $ws_array["contactus_s"][$_REQUEST["m_contact_s"]],
                                              "VALUE_M_COMPANY_NAME" => $_REQUEST["m_company_name"],
                                              "VALUE_M_FNAME" => $_REQUEST["m_fname"],
                                              "VALUE_M_LNAME" => $_REQUEST["m_lname"],
                                              "VALUE_M_BIRTHDAY" => $_REQUEST["m_birthday"],
                                              "VALUE_M_SEX" => ($_REQUEST["m_sex"]==1)?$TPLMSG["MALE"]:$TPLMSG["FEMALE"],
                                              "VALUE_M_ZIP" => $_REQUEST["m_zip"],
                                              "VALUE_M_ADDRESS" => $_REQUEST["m_address"],
                                              "VALUE_M_TEL" => $_REQUEST["m_tel"],
                                              "VALUE_M_FAX" => $_REQUEST["m_fax"],
                                              "VALUE_M_EMAIL" => $_REQUEST["m_account"],
                                              "VALUE_M_CELLPHONE" => $_REQUEST["m_cellphone"],
                                              "VALUE_M_EPAPER" => ($_REQUEST["m_epaper"]==1)?$TPLMSG["YES"]:$TPLMSG["NO"],
                    ));
                    //國家欄位
                    if($cms_cfg["ws_module"]["ws_country"]==1) {
                        $mtpl->newBlock("MEMBER_COUNTRY_ZONE");
                        $mtpl->assign(array("MSG_COUNTRY" =>$TPLMSG['COUNTRY'],
                                           "VALUE_M_COUNTRY" =>$_REQUEST["m_country"]
                        ));
                    }
                    //稱謂
                    $mtpl->newBlock("MEMBER_S_STYLE_".$this->contact_s_style);
                    $mtpl->assignGlobal( "VALUE_TERM" , $row['st_join_member_mail']);
//                    //帳號啟用連結
//                    $m_id = $db->get_insert_id();
//                    $act_link = $this->get_activate_link($_POST['m_account'],$m_id);      
//                    $tpl->assignGlobal("ACTIVATE_LINK",$act_link);
                    $mail_content=$mtpl->getOutputContent();
                    $main->ws_mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$_REQUEST["m_account"],$mail_content,$TPLMSG['MEMBER_CONFIRM_MAIL'],"m",$goto_url);
                }else{
                  $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                  $this->goto_target_page($goto_url);
                }
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
            $tpl->printToScreen();
        }
    }
    //會員訂單查詢
    function member_order($type="list",$o_id=""){
        global $db,$tpl,$main,$cms_cfg,$TPLMSG,$ws_array;
        if($type=="list"){
            $main->layer_link($TPLMSG['MEMBER_ZONE_ORDER']);
            $tpl->assignGlobal( array("TAG_MAIN_FUNC"  => $TPLMSG['MEMBER_ZONE_ORDER']  ));
            $tpl->newBlock( "ORDER_LIST_ZONE" );
            $tpl->assign( array("MSG_NAME"  => $TPLMSG['MEMBER_NAME'],
                                "MSG_STATUS" => $TPLMSG['STATUS'],
                                "MSG_ORDER_ID" => $TPLMSG['ORDER_ID'],
                                "MSG_CREATEDATE" => $TPLMSG['CREATEDATE'],
                                "MSG_MODIFYDATE" => $TPLMSG['MODIFYDATE'],
                                "MSG_TOTAL_MONEY" => $TPLMSG['ORDER_TOTAL_MONEY'],
                                "MSG_VIEWS" => $TPLMSG['VIEWS'],
            ));
            $sql="select * from ".$cms_cfg['tb_prefix']."_order where m_id='".$this->m_id."'";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records    = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="member.php?func=m_zone&mzt=order&type=list";
            //重新組合包含limit的sql語法
            $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
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
                                    "VALUE_O_SERIAL" => $i,
                                    "VALUE_O_DETAIL" => $TPLMSG['DETAIL'],
                ));
            }
        }
        if($type=="detail"){
            $tpl->newBlock( "ORDER_DETAIL_ZONE" );
            //欄位名稱
            $tpl->assignGlobal( array("MSG_ORDER_DETAIL"  => $TPLMSG['ORDER_DETAIL'],
                                      "MSG_ORDER_CONTENT"  => $TPLMSG['ORDER_CONTENT'],
                                      "MSG_CONTACT_PERSON" =>$TPLMSG['CONTACT_PERSON'],
                                      "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                                      "MSG_NAME"  => $TPLMSG['NAME'],
                                      "MSG_ZIP" => $TPLMSG["ZIP"],
                                      "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                                      "MSG_TEL" => $TPLMSG["TEL"],
                                      "MSG_FAX" => $TPLMSG["FAX"],
                                      "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
                                      "MSG_STATUS" => $TPLMSG['STATUS'],
                                      "MSG_ORDER_ID" => $TPLMSG['ORDER_ID'],
                                      "MSG_MODE" => $TPLMSG['MODIFY'],
                                      "MSG_CONTENT" => $TPLMSG['CONTENT'],
                                      "MSG_PAYMENT_TYPE" => $TPLMSG['PAYMENT_TYPE'],
                                      "MSG_TOTAL" => $TPLMSG['CART_TOTAL'],
                                      "MSG_SUBTOTAL" => $TPLMSG['CART_SUBTOTAL'],
                                      "MSG_AMOUNT" => $TPLMSG['CART_AMOUNT'],
                                      "MSG_PRODUCT" => $TPLMSG['PRODUCT'],
                                      "MSG_PRODUCT_SPECIAL_PRICE" => $TPLMSG['PRODUCT_DISCOUNT_PRICE'],
                                      "MSG_SHIPPING_PRICE"  => $TPLMSG['SHIPPING_PRICE'],
            ));
            //相關參數
            if(!empty($_REQUEST['nowp'])){
                $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                          "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                          "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                          "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

                ));
            }
            //帶入要回覆的訂單資料
            if(!empty($_REQUEST["o_id"])){
                $sql="select * from ".$cms_cfg['tb_prefix']."_order where m_id='".$this->m_id."' and o_id='".$_REQUEST["o_id"]."'";
                $selectrs = $db->query($sql);
                $row = $db->fetch_array($selectrs,1);
                $rsnum    = $db->numRows($selectrs);
                if ($rsnum > 0) {
                    $tpl->assignGlobal( array("VALUE_M_ID"  => $row["m_id"],
                                              "VALUE_O_ID"  => $row["o_id"],
                                              "VALUE_O_COMPANY_NAME" => $row["o_company_name"],
                                              "VALUE_O_CONTACT_S" => $row["o_contact_s"],
                                              "VALUE_O_NAME" => $row["o_name"],
                                              "VALUE_O_TEL" => $row["o_tel"],
                                              "VALUE_O_FAX" => $row["o_fax"],
                                              "VALUE_O_CELLPHONE" => $row["o_cellphone"],
                                              "VALUE_O_ZIP" => $row["o_zip"],
                                              "VALUE_O_ADDRESS" => $row["o_address"],
                                              "VALUE_O_EMAIL" => $row["o_email"],
                                              "VALUE_O_PLUS_PRICE" => $row["o_plus_price"],
                                              "VALUE_O_SUBTOTAL_PRICE" => $row["o_subtotal_price"],
                                              "VALUE_O_TOTAL_PRICE" => $row["o_total_price"],
                                              "VALUE_O_STATUS_SUBJECT" => $ws_array["order_status"][$row["o_status"]],
                                              "VALUE_O_CONTENT" => $row["o_content"],
                                              "VALUE_O_PAYMENT_TYPE" => $row["o_payment_type"],
                    ));
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
                    header("location : member.php");
                }
            }
        }
    }
    //會員詢問信
    function member_inquiry($type="list",$i_id=""){
       global $db,$tpl,$main,$cms_cfg,$TPLMSG,$ws_array;
        if($type=="list"){
            $main->layer_link($TPLMSG['MEMBER_ZONE_INQUIRY']);
            $tpl->assignGlobal( array("TAG_MAIN_FUNC"  => $TPLMSG['MEMBER_ZONE_INQUIRY'] ));
            $tpl->newBlock( "INQUIRY_LIST_ZONE" );
            $tpl->assign( array("MSG_NAME"  => $TPLMSG['MEMBER_NAME'],
                                "MSG_STATUS" => $TPLMSG['STATUS'],
                                "MSG_INQUIRY_ID" => $TPLMSG['INQUIRY_ID'],
                                "MSG_CREATEDATE" => $TPLMSG['CREATEDATE'],
                                "MSG_MODIFYDATE" => $TPLMSG['MODIFYDATE'],
                                "MSG_VIEWS" => $TPLMSG['VIEWS'],
            ));
            $sql="select * from ".$cms_cfg['tb_prefix']."_inquiry where m_id='".$this->m_id."'";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="member.php?func=m_zone&mzt=inquiry&type=list";
            //重新組合包含limit的sql語法
            $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "INQUIRY_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow'");
                }
                $tpl->assign( array("VALUE_I_ID"  => $row["i_id"],
                                    "VALUE_I_NAME" => $row["i_name"],
                                    "VALUE_I_CREATEDATE" => $row["i_createdate"],
                                    "VALUE_I_MODIFYDATE" => $row["i_modifydate"],
                                    "VALUE_I_STATUS" => ($row["i_status"])?$TPLMSG["REPLY_YES"]:$TPLMSG["REPLY_NO"],
                                    "VALUE_I_SERIAL" => $i,
                                    "VALUE_I_DETAIL" => $TPLMSG['DETAIL'],
                ));
            }
        }
        if($type=="detail"){
            $tpl->newBlock( "INQUIRY_DETAIL_ZONE" );
            //欄位名稱
            $tpl->assignGlobal( array("MSG_INQUIRY_DETAIL"  => $TPLMSG['INQUIRY_DETAIL'],
                                      "MSG_INQUIRY_CONTENT"  => $TPLMSG['INQUIRY_CONTENT'],
                                      "MSG_CONTACT_PERSON" =>$TPLMSG['CONTACT_PERSON'],
                                      "MSG_COMPANY_NAME" =>$TPLMSG['COMPANY_NAME'],
                                      "MSG_NAME"  => $TPLMSG['NAME'],
                                      "MSG_ZIP" => $TPLMSG["ZIP"],
                                      "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                                      "MSG_TEL" => $TPLMSG["TEL"],
                                      "MSG_FAX" => $TPLMSG["FAX"],
                                      "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
                                      "MSG_STATUS" => $TPLMSG['STATUS'],
                                      "MSG_INQUIRY_ID" => $TPLMSG['INQUIRY_ID'],
                                      "MSG_MODE" => $TPLMSG['MODIFY'],
                                      "MSG_CONTENT" => $TPLMSG['CONTENT'],
                                      "MSG_REPLY" => $TPLMSG['REPLY'],
                                      "MSG_AMOUNT" => $TPLMSG['CART_AMOUNT'],
                                      "MSG_PRODUCT" => $TPLMSG['PRODUCT'],
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
            //帶入要回覆的訂單資料
            if(!empty($_REQUEST["i_id"])){
                $sql="select * from ".$cms_cfg['tb_prefix']."_inquiry where m_id='".$this->m_id."' and i_id='".$_REQUEST["i_id"]."'";
                $selectrs = $db->query($sql);
                $row = $db->fetch_array($selectrs,1);
                $rsnum    = $db->numRows($selectrs);
                if ($rsnum > 0) {
                    $tpl->assignGlobal( array("VALUE_M_ID"  => $row["m_id"],
                                              "VALUE_I_ID"  => $row["i_id"],
                                              "VALUE_I_COMPANY_NAME" => $row["i_company_name"],
                                              "VALUE_I_TEL" => $row["i_tel"],
                                              "VALUE_I_CELLPHONE" => $row["i_cellphone"],
                                              "VALUE_I_ZIP" => $row["i_zip"],
                                              "VALUE_I_ADDRESS" => $row["i_address"],
                                              "VALUE_I_EMAIL" => $row["i_email"],
                                              "VALUE_I_CONTENT" => $row["i_content"],
                                              "VALUE_I_REPLY" => $row["i_reply"],
                                              "VALUE_I_STATUS_SUBJECT" => $ws_array["inquiry_status"][$row["i_status"]],
//                                              "VALUE_I_STATUS_SUBJECT" => ($row["i_status"])?$TPLMSG["INQUIRY_REPLY"]:$TPLMSG["INQUIRY_NO_REPLY"],
                    ));
//                    $main->contact_s_select($row["i_contact_s"],"CART");
                    $tpl->newBlock("INQUIRY_S_".$this->contact_s_style);
                    $tpl->assign(array(
                          "VALUE_I_NAME"      => $row["i_name"],
                          "VALUE_I_CONTACT_S" => $ws_array['contactus_s'][$row["i_contact_s"]],
                    ));
                    //訂購產品列表
                    $sql="select * from ".$cms_cfg['tb_prefix']."_inquiry_items where i_id='".$_REQUEST["i_id"]."'";
                    $selectrs = $db->query($sql);
                    $total_price=0;
                    $i=0;
                    while($row = $db->fetch_array($selectrs,1)){
                        $i++;
                        $sub_total_price = $row["p_sell_price"] * $row["ii_amount"];
                        $total_price = $total_price+$sub_total_price;
                        $tpl->newBlock( "INQUIRY_ITEMS_LIST" );
                        $tpl->assign( array("VALUE_P_ID"  => $row["p_id"],
                                            "VALUE_P_NAME" => $row["p_name"],
                                            "VALUE_P_AMOUNT" => $row["ii_amount"],
                                            "VALUE_P_SERIAL"  => $i,
                        ));
                    }
                }else{
                    header("location : member.php");
                    die();
                }
            }
        }
    }
    //會員聯絡我們
    function member_contactus($type="list",$cu_id=""){
        global $db,$tpl,$main,$cms_cfg,$TPLMSG,$ws_array;
        if($type=="list"){
            $tpl->newBlock( "CONTACTUS_LIST_ZONE" );
            //聯絡我們分類
            $i=0;
            foreach($ws_array["contactus_cate"] as $key =>$value){
                $i++;
                $tpl->newBlock( "CONTACTUS_CATE_LIST" );
                $tpl->assign( array("VALUE_CUC_SUBJECT"  => $value,
                                    "VALUE_CU_CATE" => $key,
                                    "VALUE_CUC_SERIAL" => $i,
                ));
            }
            //聯絡我們列表
            $sql="select * from ".$cms_cfg['tb_prefix']."_contactus where m_id='".$this->m_id."'";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records    = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="contactus.php?func=cu_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
            //重新組合包含limit的sql語法
            $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $tpl->assignGlobal( array("MSG_NAME"  => $TPLMSG['MEMBER_NAME'],
                                      "MSG_CONTENT"  => $TPLMSG['CONTENT'],
                                      "MSG_STATUS" => $TPLMSG['STATUS'],
                                      "MSG_MODE" => $TPLMSG['MANAGE_CATE'],
                                      "MSG_REPLY" => $TPLMSG['REPLY'],
                                      "MSG_CATE" => $TPLMSG['CATE'],
                                      "MSG_CREATEDATE" => $TPLMSG['CREATEDATE'],
                                      "MSG_VIEWS" => $TPLMSG['VIEWS'],
                                      "MSG_LOGIN_LANGUAGE" => $TPLMSG['LOGIN_LANGUAGE'],
                                      "VALUE_TOTAL_BOX" => $rsnum,
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],

            ));

            $i=$page["start_serial"];
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "CONTACTUS_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow'");
                }
                $tpl->assign( array("VALUE_CU_ID"  => $row["cu_id"],
                                    "VALUE_CU_NAME" => $row["cu_name"],
                                    "VALUE_CU_CONTENT" => strip_tags($row["cu_content"]),
                                    "VALUE_CU_MODIFYDATE" => $row["cu_modifydate"],
                                    "VALUE_CU_STATUS" => ($row["cu_status"])?$TPLMSG['REPLY_YES']:$TPLMSG['REPLY_NO'],
                                    "VALUE_CU_SERIAL" => $i,
                                    "VALUE_CUC_SUBJECT"  => $ws_array["contactus_cate"][$row["cu_cate"]],
                                    "VALUE_CU_DETAIL" => $TPLMSG['DETAIL'],
                ));
            }
            if($i==0){
                $tpl->assignGlobal("MSG_NO_DATA",$TPLMSG['NO_DATA']);
            }else{
                $tpl->newBlock( "PAGE_DATA_SHOW" );
                $tpl->assign( array("VALUE_TOTAL_RECORDS"  => $page["total_records"],
                                    "VALUE_TOTAL_PAGES"  => $page["total_pages"],
                                    "VALUE_PAGES_STR"  => $page["pages_str"],
                                    "VALUE_PAGES_LIMIT"=>$cms_cfg["op_limit"]
                ));
            }
        }
        if($type=="detail"){
            $tpl->newBlock( "CONTACTUS_DETAIL_ZONE" );
            //欄位名稱
            $tpl->assignGlobal( array("MSG_CONTACT_US_DETAIL"  => $TPLMSG['CONTACT_US_DETAIL'],
                                      "MSG_CONTACT_US_REPLY"  => $TPLMSG['CONTACT_US_REPLY'],
                                      "MSG_CONTACT_US_REPLY_CONTENT"  => $TPLMSG['CONTACT_US_REPLY_CONTENT'],
                                      "MSG_CONTACT_US_REPLY_TIME"  => $TPLMSG['CONTACT_US_REPLY_TIME'],
                                      "MSG_CATE"  => $TPLMSG['CATE'],
                                      "MSG_NAME"  => $TPLMSG['NAME'],
                                      "MSG_ADDRESS" => $TPLMSG["ADDRESS"],
                                      "MSG_TEL" => $TPLMSG["TEL"],
                                      "MSG_FAX" => $TPLMSG["FAX"],
                                      "MSG_CELLPHONE" => $TPLMSG["CELLPHONE"],
                                      "MSG_STATUS" => $TPLMSG['STATUS'],
            ));
            //相關參數
            if(!empty($_REQUEST['nowp'])){
                $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                          "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                          "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                          "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

                ));
            }
            //帶入聯絡我們資料
            if(!empty($_REQUEST["cu_id"])){
                $sql="select * from ".$cms_cfg['tb_prefix']."_contactus where m_id='".$this->m_id."' and cu_id='".$_REQUEST["cu_id"]."'";
                $selectrs = $db->query($sql);
                $row = $db->fetch_array($selectrs,1);
                $rsnum    = $db->numRows($selectrs);
                if ($rsnum > 0) {
                    $tpl->assignGlobal( array("VALUE_M_ID"  => $row["m_id"],
                                              "VALUE_CUC_SUBJECT"  => $ws_array["contactus_cate"][$row["cu_cate"]],
                                              "VALUE_CU_ID"  => $row["cu_id"],
                                              "VALUE_CU_NAME" => $row["cu_name"],
                                              "VALUE_CU_TEL" => $row["cu_tel"],
                                              "VALUE_CU_FAX" => $row["cu_fax"],
                                              "VALUE_CU_ADDRESS" => $row["cu_address"],
                                              "VALUE_CU_EMAIL" => $row["cu_email"],
                                              "VALUE_CU_STATUS_SUBJECT" => ($row["cu_status"])?$TPLMSG['REPLY_YES']:$TPLMSG["REPLY_NO"],
                    ));

                    //聯絡我們回覆
                    $sql="select * from ".$cms_cfg['tb_prefix']."_contactus_reply where cu_id='".$_REQUEST["cu_id"]."'";
                    $selectrs = $db->query($sql);
                    $total_price=0;
                    $i=0;
                    while($row = $db->fetch_array($selectrs,1)){
                        $i++;
                        $tpl->newBlock( "CONTACTUS_REPLY_LIST" );
                        $tpl->assign( array(
                                            "VALUE_CUR_CONTENT" => $row["cur_content"],
                                            "VALUE_CUR_MODIFYDATE" => $row["cur_modifydate"],
                                            "VALUE_CUR_SERIAL"  => $i,
                        ));
                    }
                }else{
                    header("location : member.php");
                    die();
                }
            }
        }
    }
    //會員忘記密碼
    function member_forget_password(){
        global $db,$tpl,$main,$cms_cfg,$TPLMSG,$ws_array;
        if(empty($_REQUEST["m_email"])){
            $this->ws_tpl_file = "templates/ws-member-forget-tpl.html";
            $this->ws_load_tp($this->ws_tpl_file);
            $main->layer_link($TPLMSG["FORGOT_PASSWORD"] );
            $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["FORGOT_PASSWORD"] );
            $tpl->newBlock(strtoupper( $cms_cfg['language'])."_REGISTER_FORGET_PASSWORD" );
            $main->security_zone();
        }else{
            require_once("./libs/libs-security-image.php");
            $si = new securityImage();  
            if($si->isValid()){
                $sql="select m_name,m_account,m_password,m_email from ".$cms_cfg["tb_prefix"]."_member where m_account='".$_REQUEST["m_email"]."'";
                $selectrs = $db->query($sql);
                $row = $db->fetch_array($selectrs,1);
                $rsnum    = $db->numRows($selectrs);
                if ($rsnum > 0) {
                    //重設密碼
                    $row['m_password'] = $main->rand_str(17);
                    $sql = "update ".$cms_cfg['tb_prefix']."_member set m_password=md5('".$db->quote($row['m_password'])."') where m_account='".$db->quote($row['m_email'])."'";
                    $db->query($sql,true);
                    //寄出通知信
                    $tpl = new TemplatePower( "templates/ws-mail-tpl.html" );
                    $tpl->prepare();
                    $tpl->newBlock( "MEMBER_FORGET_PASSWORD" );
                    $tpl->assignGlobal( array("MSG_TITLE"  => $TPLMSG['MEMBER_FORGET_TITLE'],
                                              "MSG_M_ACCOUNT" => $TPLMSG['MEMBER_FORGET_ACCOUNT'],
                                              "MSG_M_PASSWORD" => $TPLMSG['MEMBER_FORGET_PASSWORD'],
                                              "VALUE_M_NAME"  => $row["m_name"],
                                              "VALUE_M_ACCOUNT"  => $row["m_account"],
                                              "VALUE_M_PASSWORD"  => $row["m_password"],
                                              "TAG_BASE_URL"=>$cms_cfg["base_url"]
                    ));
                    $mail_content=$tpl->getOutputContent();
                    $goto_url=$cms_cfg["base_root"]."member.php?func=m_sps";
                    $main->ws_mail_send($_SESSION[$cms_cfg['sess_cookie_name']]['sc_email'],$row["m_email"],$mail_content,"Member's Password","pw",$goto_url);
                }else{
                    $goto_url=$cms_cfg["base_root"]."member.php?func=m_forget";
                    $main->js_notice("Account not exist!!",$goto_url);
                }
            }else{
                $goto_url=$cms_cfg["base_root"]."member.php?func=m_forget";
                $main->js_notice("Security Error!!",$goto_url);
            }
        }
    }    
     //寄送密碼完成訊息
    function member_send_password_success_str(){
        global $db,$tpl,$main,$cms_cfg,$TPLMSG,$ws_array;
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["FORGOT_PASSWORD"] );
        $tpl->newBlock( strtoupper($cms_cfg['language'])."_REGISTER_SEND_PASSWORD_SUCCESSFULLY" );
    }
    //會員紅利
    function member_bonus(){}
    //顯示訊息並重新導向
    function goto_target_page($url,$sec=2){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }
    //啟用帳號
    function account_activate(){
        global $db,$cms_cfg,$main,$TPLMSG,$tpl;
        if($_GET['account'] && $_GET['code']){
            $sql = "select m_id,m_account from ".$cms_cfg['tb_prefix']."_member where m_account='".$db->quote($_GET['account'])."'";
            $mRow = $db->query_firstRow($sql);
            if($mRow){
                $code = $this->_generate_activate_code($mRow['m_account'],$mRow['m_id']);
                if($code == $_GET['code']){
                    $sql = "update ".$cms_cfg['tb_prefix']."_member set m_status='1' where m_id='".$mRow['m_id']."'";
                    $res = $db->query($sql,1);
                    if(!$db->report()){
                        $main->meta_refresh($cms_cfg['base_root']."member.php",3);
                        $tpl->assignGlobal("MSG_ACTION_TERM",$TPLMSG['ACCOUNT_ACTIVATED']);
                        return;                        
                    }
                }
            }
        }
        $tpl->assignGlobal("MSG_ACTION_TERM",$TPLMSG['ACCOUNT_ACTIVATE_FAILED']);
    }
    function get_activate_link($acc,$mid){
        global $cms_cfg;
        $code = $this->_generate_activate_code($acc, $mid);
        return $cms_cfg['base_url']."member.php?func=activate&account=".urlencode($acc)."&code=".urlencode($code);
    }
    function _generate_activate_code($acc,$mid){
        $accPart = substr(md5($acc),0,10);
        $midPart = substr(md5($mid),0,10);
        return $accPart.$midPart;
    }
}

?>