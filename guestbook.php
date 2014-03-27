<?php
include_once("libs/libs-sysconfig.php");
$guestbook = new GUESTBOOK;
class GUESTBOOK{
    function GUESTBOOK(){
        global $db,$cms_cfg,$tpl;
        switch($_REQUEST["func"]){
            case "gb_list"://留言版列表
                $this->ws_tpl_file = "templates/ws-guestbook-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->guestbook_list();
                $this->ws_tpl_type=1;
                break;
            case "gb_add"://留言版新增
                $this->ws_tpl_file = "templates/ws-guestbook-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->guestbook_form("add");
                $this->ws_tpl_type=1;
                break;
            case "gbr_add"://留言版回覆新增
                $this->ws_tpl_file = "templates/ws-guestbook-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->guestbook_form("reply");
                $this->ws_tpl_type=1;
                break;
            case "gb_replace"://留言版更新資料(replace)
                $this->ws_tpl_file = "templates/ws-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->guestbook_replace();
                $this->ws_tpl_type=1;
                break;
            default:    //留言版列表
                $this->ws_tpl_file = "templates/ws-guestbook-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->guestbook_list();
                $this->ws_tpl_type=1;
                break;
        }
        if($this->ws_tpl_type){
            $tpl->printToScreen();
        }
    }
    //載入對應的樣板
    function ws_load_tp($ws_tpl_file){
        global $tpl,$cms_cfg,$ws_array,$db,$TPLMSG,$main;
        $tpl = new TemplatePower( $cms_cfg['base_all_tpl'] );
        $tpl->assignInclude( "HEADER", $cms_cfg['base_header_tpl']); //頭檔title,meta,js,css
        //$tpl->assignInclude( "TOP_MENU", $cms_cfg['base_top_menu_tpl']);// 功能列表
        $tpl->assignInclude( "LEFT", $cms_cfg['base_left_normal_tpl']); //左方一般表單
        $tpl->assignInclude( "MAIN", $ws_tpl_file); //主功能顯示區
        //$tpl->assignInclude( "FOOTER", $cms_cfg['base_footer_tpl']); //尾檔功能列表
        $tpl->assignInclude( "AD_H", "templates/ws-fn-ad-h-tpl.html"); //橫式廣告模板
        $tpl->assignInclude( "AD_V", "templates/ws-fn-ad-v-tpl.html"); //直式廣告模板     
        $tpl->prepare();
        $tpl->assignGlobal( "TAG_MAIN_IMG" , $ws_array["main_img"]["guestbook"]); //此頁面對應的flash及圖檔名稱
        $tpl->assignGlobal( "TAG_MAIN_FUNC" , $TPLMSG["GUEST_BOOK"]);
        $tpl->assignGlobal( "TAG_LAYER" , $TPLMSG["GUESTBOOK"]);
        $tpl->assignGlobal( "TAG_CATE_TITLE", $ws_array["left"]["products"]);//左方menu title
        $main->header_footer("");
        $main->left_fix_cate_list();
        $main->google_code(); //google analystics code , google sitemap code
        $main->login_zone();
    }

//留言版--列表================================================================
    function guestbook_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        //留言版列表
        $sql="select * from ".$cms_cfg['tb_prefix']."_guestbook  where gb_reply_type=0 order by gb_modifydate desc";
        //取得總筆數
        $selectrs = $db->query($sql);
        $total_records = $db->numRows($selectrs);
        //取得分頁連結
        $func_str="guestbook.php?func=gb_list";
        //分頁且重新組合包含limit的sql語法
        $sql=$main->pagination($cms_cfg["op_limit"],$cms_cfg["jp_limit"],$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "GUESTBOOK_LIST" );
            $tpl->assign( array("VALUE_GB_ID"  => $row["gb_id"],
                                "VALUE_GB_SUBJECT" => $row["gb_subject"],
                                "VALUE_GB_NAME" => $row["gb_name"],
                                "VALUE_GB_SEX" => ($row["gb_sex"]==0)?"w":"m",
                                "VALUE_GB_EMAIL" => $row["gb_email"],
                                "VALUE_GB_TEXTCOLOR" => $row["gb_textcolor"],
                                "VALUE_GB_IMG" => $row["gb_img"],
                                "VALUE_GB_HIDDEN" => $row["gb_hidden"],
                                "VALUE_GB_URL" => $row["gb_url"],
                                "VALUE_GB_CONTENT" => ($row["gb_hidden"])?$TPLMSG["GB_HIDDEN"]:$row["gb_content"],
                                "VALUE_GB_MODIFYDATE" => $row["gb_modifydate"],
                                "VALUE_GB_SERIAL" => $i,
            ));
            if(trim($row["gb_email"])!=""){
                $tpl->newBlock( "GUESTBOOK_EMAIL" );
                $tpl->assign("VALUE_GB_EMAIL" , $row["gb_email"]);
                $tpl->gotoBlock( "GUESTBOOK_LIST" );
            }
            if(trim($row["gb_url"])!=""){
                $tpl->newBlock( "GUESTBOOK_URL" );
                $tpl->assign("VALUE_GB_URL" , $row["gb_url"]);
                $tpl->gotoBlock( "GUESTBOOK_LIST" );
            }
            $sql2="select * from ".$cms_cfg['tb_prefix']."_guestbook  where gb_reply_type !=0 and gb_parent='".$row["gb_id"]."' order by gb_id";
            $selectrs2 = $db->query($sql2);
            while ( $row2 = $db->fetch_array($selectrs2,1) ) {
                $tpl->newBlock( "GUESTBOOK_REPLY_LIST" );
                if($row2["gb_reply_type"]==1){
                    $gb_reply_type=$TPLMSG["GB_ADMIN"];
                    $color="red";
                }else{
                    $gb_reply_type=$TPLMSG["GB_GUEST"];
                    $color="blue";
                }
                $tpl->assign( array("VALUE_GB_R_NAME" => $row2["gb_name"],
                                    "VALUE_GB_R_SEX" => $row2["gb_sex"],
                                    "VALUE_GB_R_EMAIL" => $row2["gb_email"],
                                    "VALUE_GB_R_TEXTCOLOR" => $row2["gb_textcolor"],
                                    "VALUE_GB_R_IMG" => $row2["gb_img"],
                                    "VALUE_GB_R_URL" => $row2["gb_url"],
                                    "VALUE_GB_R_CONTENT" => ($row2["gb_hidden"])?$TPLMSG["GB_HIDDEN"]:$row2["gb_content"],
                                    "VALUE_GB_R_MODIFYDATE" => $row2["gb_modifydate"],
                                    "VALUE_GB_R_REPLY_TYPE" => $gb_reply_type,
                                    "VALUE_COLOR" => $color,
                ));
            }
            $tpl->gotoBlock( "GUESTBOOK_LIST" );
        }
    }

//留言版--資料更新================================================================
    function guestbook_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        if(!ereg($cms_cfg['base_url'],$_SERVER['HTTP_REFERER'])){
            exit;
        }
        if($cms_cfg["ws_module"]["ws_security"]==1){
            require_once("libs/libs-security-image.php");
            $si = new securityImage();
            $pass=(isset($_POST['callback']) && $si->isValid())?1:0;
        }else{
            $pass=1;
        }
        if($pass){
            $_REQUEST["gb_content"]=strip_tags($_REQUEST["gb_content"]);
            $sql="
                insert into ".$cms_cfg['tb_prefix']."_guestbook (
                    gb_parent,
                    gb_subject,
                    gb_name,
                    gb_sex,
                    gb_textcolor,
                    gb_img,
                    gb_email,
                    gb_reply_type,
                    gb_hidden,
                    gb_content,
                    gb_modifydate,
                    gb_url,
                    gb_ip
                ) values (
                    '".$_REQUEST["gb_parent"]."',
                    '".$_REQUEST["gb_subject"]."',
                    '".$_REQUEST["gb_name"]."',
                    '".$_REQUEST["gb_sex"]."',
                    '".$_REQUEST["gb_textcolor"]."',
                    '".$_REQUEST["gb_img"]."',
                    '".$_REQUEST["gb_email"]."',
                    '".$_REQUEST["gb_reply_type"]."',
                    '".$_REQUEST["gb_hidden"]."',
                    '".$_REQUEST["gb_content"]."',
                    '".date("Y-m-d H:i:s")."',
                    '".$_REQUEST["gb_url"]."',
                    '".$_REQUEST["gb_ip"]."'
                )";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                unset($_SESSION["guestbook"]);
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["base_url"]."guestbook.php?func=gb_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }else{
            $main->magic_gpc($_REQUEST);
            foreach($_REQUEST as $key => $value){
                if(eregi("gb_",$key)){
                    $_SESSION["guestbook"]["$key"]=$value;
                }
            }
            $_SESSION["guestbook"]["security_error"]=1;
            header("location:guestbook.php?func=gb_add");
        }
    }

//留言版回覆--表單================================================================
    function guestbook_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $main->security_zone();
        $tpl -> assignGlobal(array( "VALUE_GB_NAME" => $_SESSION["guestbook"]["gb_name"],
                                    "VALUE_GB_URL" => $_SESSION["guestbook"]["gb_url"],
                                    "VALUE_GB_CONTENT" => $_SESSION["guestbook"]["gb_content"],
                                    "VALUE_GB_EMAIL" => $_SESSION["guestbook"]["gb_email"],
        ));
        //欄位名稱
        $tpl->assignGlobal( array("MSG_NAME"  => $TPLMSG['NAME'],
                                  "MSG_SUBJECT"  => $TPLMSG['SUBJECT'],
                                  "MSG_STATUS" => $TPLMSG['STATUS'],
                                  "MSG_MODE" => $TPLMSG['ADD'],
                                  "STR_GB_STATUS_CK1" => "",
                                  "STR_GB_STATUS_CK0" => "checked",
                                  "VALUE_ACTION_MODE" => $action_mode,
                                  "VALUE_GB_REPLY_TYPE" => 0,
                                  "VALUE_GB_PARENT" => 0,
                                  "REMOTE_ADDR" =>  $_SERVER["REMOTE_ADDR"]
        ));
        //帶入要回覆的留言版資料
        if(!empty($_REQUEST["gb_id"]) && $action_mode=="reply"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_guestbook where gb_id='".$_REQUEST["gb_id"]."' or gb_parent='".$_REQUEST["gb_id"]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                while($row = $db->fetch_array($selectrs,1)){
                    $tpl->assignGlobal( array("VALUE_GB_ID"  => $row["gb_id"],
                                              "VALUE_GB_NAME" => $row["gb_name"],
                                              "VALUE_GB_SEX" => $row["gb_sex"],
                                              "VALUE_GB_EMAIL" => $row["gb_email"],
                                              "VALUE_GB_TEXTCOLOR" => $row["gb_textcolor"],
                                              "VALUE_GB_IMG" => $row["gb_img"],
                                              "VALUE_GB_HIDDEN" => $row["gb_hidden"],
                                              "VALUE_GB_URL" => $row["gb_url"],
                                              "VALUE_GB_CONTENT" => $row["gb_content"],
                                              "MSG_MODE" => $TPLMSG['MODIFY']
                    ));
                }
            }else{
                header("location : guestbook.php?func=gb_list");
            }
            $tpl->assignGlobal( array("VALUE_GB_REPLY_TYPE" => 2,
                                      "VALUE_GB_PARENT" => $_REQUEST["gb_id"],
            ));
        }
        //啟用驗証碼顯示錯誤訊息
        if($cms_cfg["ws_module"]["ws_security"]==1 && $_SESSION["guestbook"]["security_error"]==1){
            $tpl->assignGlobal("MSG_ERROR_MESSAGE",$TPLMSG['SECURITY_ERROR']);
        }
    }
    //顯示訊息並重新導向
    function goto_target_page($url,$sec=2){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }

}
//ob_end_flush();
?>
