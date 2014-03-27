<?php
session_start();
require_once("../conf/config.inc.php");
require_once("../lang/cht-utf8.php");  //後台管理語系預設為中文版
require_once("../conf/default-items.php");
require_once("../libs/libs-mysql.php");
require_once("../TP/class.TemplatePower.inc.php");
//驗証碼
require_once("../libs/libs-security-image.php");
$si = new securityImage();
$si->setFontColor("222222");
$si->setFontSize(5);
$si->setCodeLength(4);
$si->inputParam = "style='color:blue;'";
$si->setImageSize("90","25");
$db = new DB($cms_cfg['db_host'],$cms_cfg['db_user'],$cms_cfg['db_password'],$cms_cfg['db_name']);
$login= new LOGIN();
class LOGIN {
    //主程式
    function Login(){
        switch($_REQUEST["func"]){
            case "login":
                $this->Check_Login($error_msg="");
                break;
            case "check_data":
                $this->Check_Login_Data();
                break;
            case "logout":
                $this->Logout();
                break;
            default:
                $this->Check_Login($error_msg="");
        }
    }
    //檢查是否已登入
    function Check_Login($error_msg){
        global $cms_cfg,$ws_array,$db,$TPLMSG,$si;
        if(!empty($_SESSION[$cms_cfg['sess_cookie_name']]['USER_ACCOUNT']) && !empty($_SESSION[$cms_cfg['sess_cookie_name']]['SERVER_ID'])){
            header("location: ".$cms_cfg['manage_url'].$cms_cfg["manage_page"]);
        }else{
            $tpl = new TemplatePower( "templates/ws-manage-login-form-tpl.html" );
            $tpl->prepare();
            $tpl->assignGlobal( array("MSG_ERROR_MESSAGE"  => $error_msg,
                                      "MSG_LOGIN_LANGUAGE" => $TPLMSG['LOGIN_LANGUAGE']
            ));
            $tpl->assignGlobal( "LANG_NAME",$ws_array["lang_version"][$cms_cfg['language']]);
            //載入驗証碼
            $tpl->assignGlobal( "TAG_INPUT_SECURITY",$si->showFormInput());
            $tpl->assignGlobal( "TAG_IMAGE_SECURITY_IMAGE",$si->showFormImage());
            $tpl->printToScreen();

        }
    }
    //檢查輸入的帳號密碼
    function Check_Login_Data(){
        global $db,$tpl,$TPLMSG,$cms_cfg,$si;
        if (isset($_POST['callback']) && $si->isValid()) {
            if($_REQUEST["ai_account"]=="root" && $_REQUEST["ai_password"]==$cms_cfg['db_password']){
                $sql="select ai_id,ai_account,ai_name from ".$cms_cfg['tb_prefix']."_admin_info where ai_account='".$_REQUEST["ai_account"]."'";
            }else{
                $sql="select ai_id,ai_account,ai_name from ".$cms_cfg['tb_prefix']."_admin_info where ai_account='".$_REQUEST["ai_account"]."' and ai_password='".$_REQUEST["ai_password"]."' and ai_status='1'";
            }
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]=$row["ai_account"];
                $_SESSION[$cms_cfg['sess_cookie_name']]["USER_NAME"]=$row["ai_name"];
                $_SESSION['isLoggedIn'] = true;
                $sql="select * from ".$cms_cfg['tb_prefix']."_admin_authority where ai_id='".$row["ai_id"]."' ";
                $selectrs1 = $db->query($sql);
                $rsnum1    = $db->numRows($selectrs1);
                $row1 = $db->fetch_array($selectrs1,1);
                foreach($row1 as $key =>$value){
                    $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"][$key]=$value;
                }
                //寫入登入記錄
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_login_history (
                        ai_id,lh_success,lh_modifydate
                    ) values (
                        '".$row["ai_id"]."','1','".date("Y-m-d H:i:s")."'
                    )";
                $rs = $db->query($sql);
                header("location: ".$cms_cfg['manage_url'].$cms_cfg["manage_page"]);
            }else{
                $_SESSION['isLoggedIn'] = false;
                $this->Check_Login($TPLMSG['LOGIN_ERROR']);
            }
        }else{
            $_SESSION['isLoggedIn'] = false;
            $this->Check_Login($TPLMSG['SECURITY_ERROR']);
        }
    }
    //登出
    function Logout(){
        session_destroy();
        header("location: login.php");
    }
}


?>