<?php
//error_reporting(15);
//ob_start();
session_start();
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]) ){
    header("location: /");
    exit;
}
//include_once("products.php");
class RELATED extends PRODUCTS{
    function RELATED(){
        global $db,$cms_cfg,$tpl;
        //等級大於10啟動seo
        $this->ws_seo=($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["ws_level"]>10)?1:0;
        switch($_REQUEST["func"]){
            case "pc_related"://相關分類
                $this->ws_tpl_file = "templates/ws-manage-related-items-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->products_cate_tree(0,$level=0,$func_str,0);
                $this->ws_tpl_type=1;
                break;
            case "p_related"://相關產品
                $this->ws_tpl_file = "templates/ws-manage-related-items-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->products_cate_form("add");
                $this->ws_tpl_type=1;
                break;
            case "pc_cross"://跨分類
                $this->ws_tpl_file = "templates/ws-manage-related-items-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->products_cate_list();
                $this->ws_tpl_type=1;
                break;
            default:    //相關分類
                $this->ws_tpl_file = "templates/ws-manage-related-items-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->products_cate_tree(0,$level=0,$func_str,0);
                $this->ws_tpl_type=1;
                break;
        }
        if($this->ws_tpl_type){
            $tpl->printToScreen();
        }
    }
}
$ri = new RELATED;
//ob_end_flush();
?>
