<?php
//error_reporting(15);
//ob_start();
session_start();
include_once("../conf/config.inc.php");
if(empty($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"])  || $_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_products"]==0){
    header("location: /");
    exit;
}
include_once("../libs/libs-manage-sysconfig.php");
$products = new PRODUCTS;
class PRODUCTS{
    function PRODUCTS(){
        global $db,$cms_cfg,$tpl,$main;
        $this->seo=($_SESSION[$cms_cfg['sess_cookie_name']]["AUTHORITY"]["aa_seo"] && $cms_cfg["ws_module"]["ws_seo"])?1:0;
        $this->root_user=($_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]=="root")?1:0;
        $this->op_limit=10;
        $this->jp_limit=10;
        switch($_REQUEST["func"]){
            case "ca_list"://認證標章列表
                $this->current_class="CA";
                $this->ws_tpl_file = "templates/ws-manage-products-ca-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $this->products_ca_list();
                $this->ws_tpl_type=1;
                break;
            case "ca_add":
                $this->current_class="CA";
                $this->ws_tpl_file = "templates/ws-manage-products-ca-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_MAIN");
                $this->products_ca_form("add");
                $this->ws_tpl_type=1;     
                break;
            case "ca_mod":
                $this->current_class="CA";
                $this->ws_tpl_file = "templates/ws-manage-products-ca-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_MAIN");
                $this->products_ca_form("mod");
                $this->ws_tpl_type=1;                    
                break;
            case "ca_replace"://認證標章(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->products_ca_replace();
                $this->ws_tpl_type=1;
                break;            
            case "ca_del":             
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->products_ca_del();
                $this->ws_tpl_type=1;
                break;               
            case "pa_list"://應用領域分類列表
                $this->current_class="PA";
                $this->ws_tpl_file = "templates/ws-manage-products-application-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->products_application_list();
                $this->ws_tpl_type=1;
                break;
            case "pa_add":
                $this->current_class="PA";
                $this->ws_tpl_file = "templates/ws-manage-products-application-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_TINYMCE");
                $this->products_application_form("add");
                $this->ws_tpl_type=1;                
                break;
            case "pa_mod":
                $this->current_class="PA";
                $this->ws_tpl_file = "templates/ws-manage-products-application-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_TINYMCE");
                $this->products_application_form("mod");
                $this->ws_tpl_type=1;             
                break;
            case "pa_replace":
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->products_application_replace();
                $this->ws_tpl_type=1;                
                break;
            case "pa_del":
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->products_application_del();
                $this->ws_tpl_type=1;            
                break;
            case "pc_list"://產品管理分類列表
                $this->current_class="PC";
                $this->ws_tpl_file = "templates/ws-manage-products-cate-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_PC_TREE");
                $this->products_cate_list();
                $this->ws_tpl_type=1;
                break;
            case "pc_add"://產品管理分類新增
                $this->current_class="PC";
                $this->ws_tpl_file = "templates/ws-manage-products-cate-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_TINYMCE");
                $this->products_cate_form("add");
                $this->ws_tpl_type=1;
                break;
            case "pc_mod"://產品管理分類修改
                $num=($this->root_user)?1:$this->check_data_locked("pc",$_REQUEST["pc_id"]);
                if($num==0){
                    header("location: /");
                }else{
                    $this->current_class="PC";
                    $this->ws_tpl_file = "templates/ws-manage-products-cate-form-tpl.html";
                    $this->ws_load_tp($this->ws_tpl_file);
                    $tpl->newBlock("JS_FORMVALID");
                    $tpl->newBlock("JS_PREVIEWS_PIC");
                    $tpl->newBlock("JS_MAIN");
                    $tpl->newBlock("JS_TINYMCE");
                    $this->products_cate_form("mod");
                    $this->ws_tpl_type=1;
                }
                break;
            case "pc_replace"://產品管理分類更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->products_cate_replace();
                $this->ws_tpl_type=1;
                break;
            case "pc_del"://產品管理分類刪除
                if($_REQUEST["pc_id"]!=""){
                    $num=($this->root_user)?1:$this->check_data_locked("pc",$_REQUEST["pc_id"]);
                }else{
                    $num=1; //批次處理的直接通過
                }
                if($num==0){
                    header("location: /");
                }else{
                    $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                    $this->ws_load_tp($this->ws_tpl_file);
                    $this->products_cate_del();
                    $this->ws_tpl_type=1;
                }
                break;
            case "p_list"://產品管理列表
                $this->current_class="P";
                $this->ws_tpl_file = "templates/ws-manage-products-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_PC_TREE");
                $this->products_list();
                $this->ws_tpl_type=1;
                break;
            case "p_add"://產品管理新增
                $this->current_class="P";
                $this->ws_tpl_file = "templates/ws-manage-products-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_FORMVALID");
                $tpl->newBlock("JS_PREVIEWS_PIC");
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_TINYMCE");
                $tpl->newBlock("JS_TABTITLE");
                $this->products_form("add");
                $this->ws_tpl_type=1;
                break;
            case "p_mod"://產品管理修改
                $num=($this->root_user)?1:$this->check_data_locked("p",$_REQUEST["p_id"]);
                if($num==0){
                    header("location: /");
                }else{
                    $this->current_class="P";
                    $this->ws_tpl_file = "templates/ws-manage-products-form-tpl.html";
                    $this->ws_load_tp($this->ws_tpl_file);
                    $tpl->newBlock("JS_FORMVALID");
                    $tpl->newBlock("JS_PREVIEWS_PIC");
                    $tpl->newBlock("JS_MAIN");
                    $tpl->newBlock("JS_TINYMCE");
                    $tpl->newBlock("JS_TABTITLE");
                    $this->products_form("mod");
                    $this->ws_tpl_type=1;
                }
                break;
            case "p_replace"://產品管理更新資料(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->products_replace();
                $this->ws_tpl_type=1;
                break;
            case "p_del"://產品管理刪除
                if($_REQUEST["p_id"]!=""){
                    $num=($this->root_user)?1:$this->check_data_locked("p",$_REQUEST["p_id"]);
                }else{
                    $num=1; //批次處理的直接通過
                }
                if($num==0){
                    header("location: /");
                }else{
                    $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                    $this->ws_load_tp($this->ws_tpl_file);
                    $this->products_del();
                    $this->ws_tpl_type=1;
                }
                break;
            case "new_p_list"://新產品管理列表
                $this->current_class="PN";
                $this->ws_tpl_file = "templates/ws-manage-newproducts-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->newproducts_list();
                $this->ws_tpl_type=1;
                break;
            case "data_processing"://多筆刪除,複製,啟用,停用 處理
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->data_processing();
                $this->ws_tpl_type=1;
                break;
            case "pc_cate_tree"://分類樹狀結構
                $this->ws_tpl_file = "templates/ws-manage-products-cate-tree-tpl.html";
                $tpl = new TemplatePower( $this->ws_tpl_file );
                $tpl->prepare();
                $this->products_cate_tree($_REQUEST["pc_id"]);
                $tpl->printToScreen();
                break;
            case "p_select_cate"://選擇分類
                $this->ws_tpl_file = "templates/ws-manage-related-cate-list-tpl.html";
                $tpl = new TemplatePower( $this->ws_tpl_file );
                $tpl->prepare();
                $this->select_cate($_REQUEST["pc_id_str"],$_REQUEST["id"]);
                $tpl->printToScreen();
                break;
            case "p_select_products"://選擇相關產品
                $this->ws_tpl_file = "templates/ws-manage-related-products-list-tpl.html";
                $tpl = new TemplatePower( $this->ws_tpl_file );
                $tpl->prepare();
                $this->select_products($_REQUEST["p_id_str"],$_REQUEST["id"]);
                $tpl->printToScreen();
                break;
            case "related_items"://相關項目
                $this->ws_tpl_file = "templates/ws-manage-related-items-list-tpl.html";
                $tpl = new TemplatePower( $this->ws_tpl_file );
                $tpl->assignGlobal( "TAG_ROOT_PATH" , $cms_cfg['base_root']);
                $tpl->prepare();
                $this->related_items($_REQUEST["related_type"],$_REQUEST["id"]);
                $tpl->printToScreen();
                break;
            case "p_clone"://產品分身新增
                $this->current_class="P";
                $this->ws_tpl_file = "templates/ws-manage-products-clone-form-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->products_clone_form();
                $this->ws_tpl_type=1;
                break;
            case "clone_replace"://clone(replace)
                $this->ws_tpl_file = "templates/ws-manage-msg-action-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $this->clone_replace();
                $this->ws_tpl_type=1;
                break;       
            default:    //產品管理列表
                $this->current_class="P";
                $this->ws_tpl_file = "templates/ws-manage-products-list-tpl.html";
                $this->ws_load_tp($this->ws_tpl_file);
                $tpl->newBlock("JS_MAIN");
                $tpl->newBlock("JS_FORMVALID");
                $this->products_list();
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
        $tpl->assignGlobal("CSS_BLOCK_PRODUCTS","style=\"display:block\"");
        //依權限顯示項目
        $main->mamage_authority();
    }
    function check_data_locked($table,$id){
        global $db,$cms_cfg;
        if($table=="pc"){
            $sql="select pc_id from ".$cms_cfg['tb_prefix']."_products_cate where pc_id='".$id."' and (pc_locked='0' || (pc_locked='1' and pc_modifyaccount='".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'))";
        }
        if($table=="p"){
            $sql="select p_id from ".$cms_cfg['tb_prefix']."_products where p_id='".$id."' and (p_locked='0' || (p_locked='1' and p_modifyaccount='".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'))";
        }
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        return $rsnum;
    }
    //產品管理分類--列表
    function products_cate_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        if(trim($_SESSION[$cms_cfg['sess_cookie_name']]["BACK_EDIT_ZONE"])!=""){
            $tpl->assignGlobal("TAG_BACK_PRE_EDIT_ZONE","<a href='".$_SESSION[$cms_cfg['sess_cookie_name']]["BACK_EDIT_ZONE"]."'><font color='blue'>回到上次分類列表</font></a>");
            unset($_SESSION[$cms_cfg['sess_cookie_name']]["BACK_EDIT_ZONE"]);
        }
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        $this->parent=($_REQUEST["pc_parent"])?$_REQUEST["pc_parent"]:0;
        //分類樹狀結構
//            $this->products_cate_tree($this->parent,"p");
        require "../class/catetree/".$cms_cfg['tb_prefix']."/productsCate.php";
        $catetreeClass = "catetree_".$cms_cfg['tb_prefix']."_productsCate";
        $cateTree = new $catetreeClass(array(
            "db"            => $db,
            "cfg"           => $cms_cfg,
            "cate_link_str" => "products.php?func=pc_list",
        ));
        $tpl->assign("_ROOT.PRODUCTS_CATE_TREE",$cateTree->get_tree());
        //系統跳回參數
        $tpl->assignGlobal( "VALUE_PC_PARENT", $this->parent);
        $sql="select * from ".$cms_cfg['tb_prefix']."_products_cate where pc_id > '0'";
        $and_str = "";
        if(!$this->root_user){
            $and_str = " and (pc_locked='0' || (pc_locked='1' and pc_modifyaccount='".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'))";
        }
        if(!empty($_REQUEST["sk"])){
            $and_str .= " and pc_name like '%".$_REQUEST["sk"]."%'";
        }else{
            $and_str .= " and pc_parent='".$this->parent."'";
        }
        $sql .= $and_str." order by pc_sort ".$cms_cfg['sort_pos'].",pc_modifydate desc ";
        //取得總筆數
        $total_records=$main->count_total_records($sql);
        //取得分頁連結
        $func_str="products.php?func=pc_list&pc_parent=".$this->parent."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        //分頁且重新組合包含limit的sql語法
        $sql=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                  "VALUE_TOTAL_BOX" => $rsnum,
                                  "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],
                                  "VALUE_NOW_PAGE" => $_REQUEST['nowp']
        ));
        //階層
        $tpl->assignGlobal("MSG_NOW_CATE" , $TPLMSG["NOW_CATE"]);
        $products_cate_layer=$main->get_layer($cms_cfg['tb_prefix']."_products_cate","pc_name","pc",$this->parent,$func_str);
        if(!empty($products_cate_layer)){
            $tpl->assignGlobal("TAG_PRODUCTS_CATE_LAYER",implode(" > ",$products_cate_layer));
        }else{
            $tpl->assignGlobal("TAG_PRODUCTS_CATE_LAYER",$TPLMSG["NO_CATE"]);
        }
        //分類列表
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "PRODUCTS_CATE_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_PC_ID"  => $row["pc_id"],
                                "VALUE_PC_STATUS"  => $row["pc_status"],
                                "VALUE_PC_UP_SORT"  => ($row["pc_up_sort"])?"<font color='red' size='2'>[置頂]</font>":"",
                                "VALUE_PC_SORT"  => $row["pc_sort"],
                                "VALUE_PC_NAME" => $row["pc_name"],
                                "VALUE_PC_CATE_IMG" => (trim($row["pc_cate_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["pc_cate_img"],
                                "VALUE_PC_SERIAL" => $i,
                                "VALUE_STATUS_IMG" => ($row["pc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                "VALUE_STATUS_IMG_ALT" => ($row["pc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
                                "VALUE_LOCK_IMG" => ($row["pc_locked"])?$cms_cfg['default_lock']:$cms_cfg['default_key'],
                                "VALUE_PC_MODIFYDATE" => $row["pc_modifydate"],
                                "VALUE_PC_MODIFYACCOUNT" => $row["pc_modifyaccount"],

            ));
        }
    }
    //產品管理分類--表單
    function products_cate_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        if($this->seo){
            $tpl->newBlock("SEO_EDIT_ZONE");
            $tpl->assignGlobal( array("STR_PC_UP_SORT_CK1" => "",
                                      "STR_PC_UP_SORT_CK0" => "checked",
            ));
        }
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "VALUE_PC_SORT" => 1,
                                  "NOW_PC_ID"  => 0,
                                  "VALUE_PC_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_products_cate","pc","pc_parent",$_REQUEST["pc_parent"],1),
                                  "STR_PC_STATUS_CK1" => "checked",
                                  "STR_PC_STATUS_CK0" => "",
                                  "STR_PC_LOCK_CK1" => "checked",
                                  "STR_PC_LOCK_CK0" => "",
                                  "STR_PC_SHOW_STYLE_CK3" =>"",
                                  "STR_PC_SHOW_STYLE_CK2" =>"",
                                  "STR_PC_SHOW_STYLE_CK1" =>"checked",
                                  "STR_PC_CUSTOM_STATUS_CK1" => "",
                                  "STR_PC_CUSTOM_STATUS_CK0" => "checked",
                                  "STR_PC_CUSTOM_STATUS_DISPLAY" => "none",
                                  "VALUE_PIC_PREVIEW1" => $cms_cfg['default_preview_pic'],
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
        $pc_parent=$_REQUEST["pc_parent"];
        //如果為修改模式,帶入資料庫資料
        if($action_mode=="mod" && !empty($_REQUEST["pc_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_products_cate where pc_id='".$_REQUEST["pc_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("NOW_PC_ID"  => $row["pc_id"],
                                          "NOW_PC_PARENT"  => $row["pc_parent"],
                                          "VALUE_PC_SORT"  => $row["pc_sort"],
                                          "VALUE_PC_NAME" => $row["pc_name"],
                                          "VALUE_PC_NAME_ALIAS" => $row["pc_name_alias"],
                                          "VALUE_PC_CUSTOM" => $row["pc_custom"],
                                          "NOW_PC_LEVEL" => $row["pc_level"],
                                          "VALUE_PC_CATE_IMG" => (trim($row["pc_cate_img"])=="")?"":$cms_cfg["file_root"].$row["pc_cate_img"],
                                          "VALUE_PIC_PREVIEW1" => (trim($row["pc_cate_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["pc_cate_img"],
                                          "STR_PC_STATUS_CK1" => ($row["pc_status"])?"checked":"",
                                          "STR_PC_STATUS_CK0" => ($row["pc_status"])?"":"checked",
                                          "STR_PC_LOCK_CK1" => ($row["pc_locked"])?"checked":"",
                                          "STR_PC_LOCK_CK0" => ($row["pc_locked"])?"":"checked",
                                          "STR_PC_SHOW_STYLE_CK3" =>($row["pc_show_style"]==3)?"checked":"",
                                          "STR_PC_SHOW_STYLE_CK2" =>($row["pc_show_style"]==2)?"checked":"",
                                          "STR_PC_SHOW_STYLE_CK1" =>($row["pc_show_style"]==1)?"checked":"",
                                          "STR_PC_CUSTOM_STATUS_CK1" => ($row["pc_custom_status"]==1)?"checked":"",
                                          "STR_PC_CUSTOM_STATUS_CK0" => ($row["pc_custom_status"]==0)?"checked":"",
                                          "STR_PC_CUSTOM_STATUS_DISPLAY" => ($row["pc_custom_status"]==1)?" ":"none",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
                if($this->seo){
                    $tpl->assignGlobal( array("VALUE_PC_RELATED_CATE" => $row["pc_related_cate"],
                                              "VALUE_PC_SEO_FILENAME" => $row["pc_seo_filename"],
                                              "VALUE_PC_SEO_TITLE" => $row["pc_seo_title"],
                                              "VALUE_PC_SEO_KEYWORD" => $row["pc_seo_keyword"],
                                              "VALUE_PC_SEO_DESCRIPTION" => $row["pc_seo_description"],
                                              "VALUE_PC_SEO_H1" => $row["pc_seo_h1"],
                                              "VALUE_PC_SEO_SHORT_DESC" => $row["pc_seo_short_desc"],
                                              "VALUE_PC_SEO_DOWN_SHORT_DESC" => $row["pc_seo_down_short_desc"],
                                              "STR_PC_UP_SORT_CK1" => ($row["pc_up_sort"])?"checked":"",
                                              "STR_PC_UP_SORT_CK0" => ($row["pc_up_sort"])?"":"checked",
                                              "VALUE_PC_CROSS_CATE" => $row["pc_cross_cate"],
                    ));
                    $this->get_items_name($row["pc_related_cate"],"pc"); //相關分類
                }
                $_SESSION[$cms_cfg['sess_cookie_name']]["BACK_EDIT_ZONE"]=$_SERVER["HTTP_REFERER"];
                $pc_parent=$row["pc_parent"];
            }else{
                header("location : products.php?func=pc_list");
            }
        }
        //載入分類資料,選擇分類
        $this->products_cate_select($this->products_cate_select_option, $row["pc_id"],$pc_parent, $parent=0, $indent="");
        $tpl->assignGlobal("TAG_SELECT_PRODUCTS_CATE" ,$this->products_cate_select_option);
        if($cms_cfg["ws_module"]["ws_wysiwyg"]=="tinymce"){
            $tpl->newBlock("WYSIWYG_TINYMCE1");
            $tpl->assign( "VALUE_PC_CUSTOM" , $row["pc_custom"] );
        }
        if($cms_cfg["ws_module"]['ws_products_application'] && $cms_cfg["ws_module"]['ws_application_cates']){
            $this->application_checkbox($row["pc_id"],true);
        }
    }
    //產品管理分類--資料更新
    function products_cate_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $main->magic_gpc($_REQUEST);
        if($this->seo){
            $add_field_str="pc_name_alias,
                            pc_seo_filename,
                            pc_seo_title,
                            pc_seo_keyword,
                            pc_seo_description,
                            pc_seo_short_desc,
                            pc_seo_down_short_desc,
                            pc_seo_h1,
                            pc_up_sort,";
            $add_value_str="'".htmlspecialchars($_REQUEST["pc_name_alias"])."',
                            '".trim($_REQUEST["pc_seo_filename"])."',
                            '".htmlspecialchars($_REQUEST["pc_seo_title"])."',
                            '".htmlspecialchars($_REQUEST["pc_seo_keyword"])."',
                            '".htmlspecialchars($_REQUEST["pc_seo_description"])."',
                            '".$db->quote($main->content_file_str_replace($_REQUEST["pc_seo_short_desc"]))."',
                            '".$_REQUEST["pc_seo_down_short_desc"]."',
                            '".htmlspecialchars($_REQUEST["pc_seo_h1"])."',
                            '".$_REQUEST["pc_up_sort"]."',";
            $update_str="pc_name_alias='".htmlspecialchars($_REQUEST["pc_name_alias"])."',
                         pc_seo_filename='".trim($_REQUEST["pc_seo_filename"])."',
                         pc_seo_title='".htmlspecialchars($_REQUEST["pc_seo_title"])."',
                         pc_seo_keyword='".htmlspecialchars($_REQUEST["pc_seo_keyword"])."',
                         pc_seo_description='".htmlspecialchars($_REQUEST["pc_seo_description"])."',
                         pc_seo_short_desc='".$db->quote($main->content_file_str_replace($_REQUEST["pc_seo_short_desc"]))."',
                         pc_seo_down_short_desc='".$_REQUEST["pc_seo_down_short_desc"]."',
                         pc_seo_h1='".htmlspecialchars($_REQUEST["pc_seo_h1"])."',
                         pc_up_sort='".$_REQUEST["pc_up_sort"]."',";
        }
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                insert into ".$cms_cfg['tb_prefix']."_products_cate(
                    pc_parent,
                    pc_status,
                    pc_sort,
                    pc_name,
                    pc_custom_status,
                    pc_custom,
                    pc_show_style,
                    pc_cate_img,
                    pc_related_cate,
                    pc_modifydate,
                    ".$add_field_str."
                    pc_cross_cate,
                    pc_locked,
                    pc_modifyaccount
                ) values (
                    '".$_REQUEST["pc_parent"]."',
                    '".$_REQUEST["pc_status"]."',
                    '".$_REQUEST["pc_sort"]."',
                    '".htmlspecialchars($_REQUEST["pc_name"])."',
                    '".$_REQUEST["pc_custom_status"]."',
                    '".$db->quote($main->content_file_str_replace($_REQUEST["pc_custom"]))."',
                    '".$_REQUEST["pc_show_style"]."',
                    '".$main->file_str_replace($_REQUEST["pc_cate_img"])."',
                    '".$_REQUEST["pc_related_cate"]."',
                    '".date("Y-m-d H:i:s")."',
                    ".$add_value_str."
                    '".$_REQUEST["pc_cross_cate"]."',
                    '".$_REQUEST["pc_locked"]."',
                    '".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'
                )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $this->pc_id=$db->get_insert_id();
                    //取得新的分類階層
                    $products_cate_layer=$main->get_layer($cms_cfg['tb_prefix']."_products_cate","pc_id","pc",$this->pc_id,"",2);
                    if(!empty($products_cate_layer)){
                        $pc_layer="0-".implode("-",$products_cate_layer);
                        $pc_level=count($products_cate_layer)+1;
                    }else{
                        $pc_layer="0-".$this->pc_id;
                        $pc_level=1;
                    }
                    $sql="
                        update ".$cms_cfg['tb_prefix']."_products_cate set
                            pc_layer='".$pc_layer."',
                            pc_level='".$pc_level."'
                        where pc_id='".$this->pc_id."'
                    ";
                    $rs = $db->query($sql);
                    $db_msg = $db->report();
                }
                break;
            case "mod":
                //取得新的分類階層
                $products_cate_layer=$main->get_layer($cms_cfg['tb_prefix']."_products_cate","pc_id","pc",$_REQUEST["pc_parent"],"",2);
                if(!empty($products_cate_layer)){
                    $pc_layer="0-".implode("-",$products_cate_layer)."-".$_REQUEST["now_pc_id"];
                    $pc_level=count($products_cate_layer)+1;
                }else{
                    $pc_layer="0-".$_REQUEST["now_pc_id"];
                    $pc_level=1;
                }
                $sql="
                update ".$cms_cfg['tb_prefix']."_products_cate set
                    pc_parent='".$_REQUEST["pc_parent"]."',
                    pc_layer='".$pc_layer."',
                    pc_status='".$_REQUEST["pc_status"]."',
                    pc_sort='".$_REQUEST["pc_sort"]."',
                    pc_name='".htmlspecialchars($_REQUEST["pc_name"])."',
                    pc_level='".$pc_level."',
                    pc_custom_status='".$_REQUEST["pc_custom_status"]."',
                    pc_custom='".$db->quote($main->content_file_str_replace($_REQUEST["pc_custom"]))."',
                    pc_show_style='".$_REQUEST["pc_show_style"]."',
                    pc_cate_img='".$main->file_str_replace($_REQUEST["pc_cate_img"])."',
                    pc_related_cate='".$_REQUEST["pc_related_cate"]."',
                    pc_modifydate='".date("Y-m-d H:i:s")."',
                    ".$update_str."
                    pc_cross_cate='".$_REQUEST["pc_cross_cate"]."',
                    pc_locked='".$_REQUEST["pc_locked"]."',
                    pc_modifyaccount='".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'
                where pc_id='".$_REQUEST["now_pc_id"]."'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                break;
        }
        if ( $db_msg == "" ) {
            //更新產品的products category layer
            $sql="
                update ".$cms_cfg['tb_prefix']."_products set
                    pc_layer='".$pc_layer."'
                where pc_id='".$_REQUEST["now_pc_id"]."'";
            $rs = $db->query($sql);
            if($cms_cfg["ws_module"]['ws_products_application'] && $cms_cfg["ws_module"]['ws_application_cates']){//有應用領域
                if($_POST['pa_id_str']){
                    $pc_id = $_REQUEST["now_pc_id"]?$_REQUEST["now_pc_id"]:$this->pc_id;
                    $db_msg .= $this->write_application($pc_id,$_POST['pa_id_str'],true);
                }
            }            
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$cms_cfg["manage_url"]."products.php?func=pc_list&pc_parent=".$_REQUEST["pc_parent"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
            $this->goto_target_page($goto_url);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
    //產品管理分類--刪除
    function products_cate_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["pc_id"]){
            $pc_id=array(0=>$_REQUEST["pc_id"]);
        }else{
            $pc_id=$_REQUEST["id"];
        }
        if(!empty($pc_id)){
            $pc_id_str = implode(",",$pc_id);
            //清空分類底下的產品管理
            $sql="delete from ".$cms_cfg['tb_prefix']."_products where pc_id in (".$pc_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                //刪除分類
                $sql="delete from ".$cms_cfg['tb_prefix']."_products_cate where pc_id in (".$pc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."products.php?func=pc_list&pc_id=".$_REQUEST["pc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
//產品管理--列表================================================================
    function products_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        if(trim($_SESSION[$cms_cfg['sess_cookie_name']]["BACK_EDIT_ZONE"])!=""){
            $tpl->assignGlobal("TAG_BACK_PRE_EDIT_ZONE","<a href='".$_SESSION[$cms_cfg['sess_cookie_name']]["BACK_EDIT_ZONE"]."'><font color='blue'>回到上次產品列表</font></a>");
            unset($_SESSION[$cms_cfg['sess_cookie_name']]["BACK_EDIT_ZONE"]);
        }
        $sql="select count(pc_id)  as pc_total from ".$cms_cfg['tb_prefix']."_products_cate where pc_id > '0'";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        //沒有分類先建立分類
        if($row["pc_total"]<1){
            $tpl->assignGlobal( "MSG_CREATE_CATE_FIRST" , $TPLMSG["CREATE_CATE_FIRST"]);
            $goto_url=$cms_cfg["manage_url"]."products.php?func=pc_add";
            $this->goto_target_page($goto_url);
        }else{
            //產品管理分類
            $this->parent=($_REQUEST["pc_parent"])?$_REQUEST["pc_parent"]:0;
            //分類樹狀結構
//            $this->products_cate_tree($this->parent,"p");
            require "../class/catetree/".$cms_cfg['tb_prefix']."/productsCate.php";
            $catetreeClass = "catetree_".$cms_cfg['tb_prefix']."_productsCate";
            $cateTree = new $catetreeClass(array(
                "db"            => $db,
                "cfg"           => $cms_cfg,
                "cate_link_str" => "products.php?func=p_list",
            ));
            $tpl->assign("_ROOT.PRODUCTS_CATE_TREE",$cateTree->get_tree());
            //系統跳回參數
            $tpl->assignGlobal( "VALUE_PC_PARENT", $this->parent);
            $sql="select * from ".$cms_cfg['tb_prefix']."_products_cate where pc_id > '0'";
            $and_str = "";
            if(!empty($_REQUEST["sk"])){
                $and_str = " and pc_name like '%".$_REQUEST["sk"]."%'";
            }else{
                $and_str = " and pc_parent='".$this->parent."'";
            }
            $sql .= $and_str;
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $i=0;
            $tpl->assignGlobal("TAG_NOW_CATE",$TPLMSG["NO_CATE"]);
            while($row = $db->fetch_array($selectrs,1)){
                $i++;
                $tpl->newBlock( "PRODUCTS_CATE_LIST" );
                $tpl->assign( array( "VALUE_PC_NAME"  => $row["pc_name"],
                                     "VALUE_PC_ID" => $row["pc_id"],
                                     "VALUE_PC_SERIAL" => $i,
                                     "VALUE_STATUS_IMG" => ($row["pc_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                     "VALUE_STATUS_IMG_ALT" => ($row["pc_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
                ));
                if($i%4==0){
                    $tpl->assign("TAG_PRODUCTS_CATE_TRTD","</tr><tr>");
                }
                if($row["pc_id"]==$_REQUEST["pc_id"]){
                    $tpl->assignGlobal("TAG_NOW_CATE",$row["pc_subject"]);
                }
            }

            //產品管理列表
            $sql="select p.*,pc.pc_name from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p.p_id > '0'";
            //附加條件
            $and_str="";
            if(!$this->root_user){
                $and_str = " and (p.p_locked='0' || (p.p_locked='1' and p.p_modifyaccount='".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'))";
            }
            if(!empty($_REQUEST["pc_parent"])){
                $and_str .= " and p.pc_id = '".$_REQUEST["pc_parent"]."'";
            }
            if($_REQUEST["st"]=="all"){
                $and_str .= " and (p.p_name like '%".$_REQUEST["sk"]."%' or p.p_spec like '%".$_REQUEST["sk"]."%' or p.p_character like '%".$_REQUEST["sk"]."%' or p.p_desc like '%".$_REQUEST["sk"]."%')";
            }
            if($_REQUEST["st"]=="p_name"){
                $and_str .= " and p.p_name like '%".$_REQUEST["sk"]."%'";
            }
            if($_REQUEST["st"]=="p_spec"){
                $and_str .= " and p.p_spec like '%".$_REQUEST["sk"]."%'";
            }
            if($_REQUEST["st"]=="p_character"){
                $and_str .= " and p.p_character like '%".$_REQUEST["sk"]."%'";
            }
            if($_REQUEST["st"]=="p_desc"){
                $and_str .= " and p.p_desc like '%".$_REQUEST["sk"]."%'";
            }
            $sql .= $and_str." order by pc.pc_sort ".$cms_cfg['sort_pos'].",p.p_sort ".$cms_cfg['sort_pos'].",p.p_modifydate desc ";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records    = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="products.php?func=p_list&pc_parent=".$this->parent."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
            //分頁且重新組合包含limit的sql語法
            $sql=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $tpl->assignGlobal( array("VALUE_TOTAL_BOX" => $rsnum,
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                      "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp']
            ));
            switch($_REQUEST["st"]){
                case "all" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK0", "selected");
                    break;
                case "p_name" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK1", "selected");
                    break;
                case "p_spec" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK2", "selected");
                    break;
                case "p_character" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK3", "selected");
                    break;
                case "p_desc" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK4", "selected");
                    break;
            }
            //階層
            $tpl->assignGlobal("MSG_NOW_CATE" , $TPLMSG["NOW_CATE"]);
            $products_cate_layer=$main->get_layer($cms_cfg['tb_prefix']."_products_cate","pc_name","pc",$this->parent,$func_str);
            if(!empty($products_cate_layer)){
                $tpl->assignGlobal("TAG_PRODUCTS_CATE_LAYER",implode(" > ",$products_cate_layer));
            }else{
                $tpl->assignGlobal("TAG_PRODUCTS_CATE_LAYER",$TPLMSG["NO_CATE"]);
            }
            //產品列表
            $i=$page["start_serial"];
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
				if($row["p_clone"]){
					$sql_clone="select p.*,pc.pc_name from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p.p_id > '0' and p_id='".$row["p_clone"]."'";
					//附加條件
					if(!$this->root_user){
						$sql_clone .= " and (p.p_locked='0' || (p.p_locked='1' and p.p_modifyaccount='".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'))";
					}
					$selectrs_clone = $db->query($sql_clone);
					$row_clone = $db->fetch_array($selectrs_clone,1);
					$tpl->newBlock( "CLONE_LIST" );
					$tpl->assign("TAG_TR_CLASS","style='background:#FFCDFE'");
					$tpl->assign( array("VALUE_PC_ID"  => $row["p_id"],
										"VALUE_P_ID"  => $row["p_id"],
										"VALUE_P_SORT"  => $row["p_sort"],
										"VALUE_P_UP_SORT"  => ($row["p_up_sort"])?"<font color='red' size='2'>[置頂]</font>":"",
										"VALUE_P_NAME" => $row_clone["p_name"]."<font color='red' size='2'>[分身]</font>",
										"VALUE_P_SMALL_IMG" => (trim($row_clone["p_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row_clone["p_small_img"],
										"VALUE_P_SERIAL" => $i,
										"VALUE_PC_NAME"  => ($row["pc_name"])?$row["pc_name"]."<br /><font color='red' size='2'>[原類別]</font><br />".$row_clone["pc_name"]:$TPLMSG['NO_CATE'],
										"VALUE_STATUS_IMG" => ($row["p_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
										"VALUE_STATUS_IMG_ALT" => ($row["p_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
										"VALUE_LOCK_IMG" => ($row["p_locked"])?$cms_cfg['default_lock']:$cms_cfg['default_key'],
										"VALUE_P_MODIFYDATE" => $row["p_modifydate"],
										"VALUE_P_MODIFYACCOUNT" => $row["p_modifyaccount"],
										"VALUE_NOW_PAGE" => $_REQUEST['nowp']

					));
				}else{
					$tpl->newBlock( "PRODUCTS_LIST" );
					if($i%2){
						$tpl->assign("TAG_TR_CLASS","class='altrow'");
					}
					$tpl->assign( array("VALUE_PC_ID"  => $row["p_id"],
										"VALUE_P_ID"  => $row["p_id"],
										"VALUE_P_SORT"  => $row["p_sort"],
										"VALUE_P_UP_SORT"  => ($row["p_up_sort"])?"<font color='red' size='2'>[置頂]</font>":"",
										"VALUE_P_NAME" => $row["p_name"],
										"VALUE_P_SMALL_IMG" => (trim($row["p_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["p_small_img"],
										"VALUE_P_SERIAL" => $i,
										"VALUE_PC_NAME"  => ($row["pc_name"])?$row["pc_name"]:$TPLMSG['NO_CATE'],
										"VALUE_STATUS_IMG" => ($row["p_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
										"VALUE_STATUS_IMG_ALT" => ($row["p_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
										"VALUE_LOCK_IMG" => ($row["p_locked"])?$cms_cfg['default_lock']:$cms_cfg['default_key'],
										"VALUE_P_MODIFYDATE" => $row["p_modifydate"],
										"VALUE_P_MODIFYACCOUNT" => $row["p_modifyaccount"],
										"VALUE_NOW_PAGE" => $_REQUEST['nowp']

					));
				}
            }
        }
    }
//產品管理--表單================================================================
    function products_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //取得系統設定裡面的產品說明欄位
        $this->parent=($_REQUEST["pc_parent"])?$_REQUEST["pc_parent"]:0;
        //系統跳回參數
        $tpl->assignGlobal( "VALUE_PC_PARENT", $this->parent);
        if($this->seo){
            $tpl->newBlock("SEO_EDIT_ZONE");
            $tpl->assignGlobal( array("STR_P_UP_SORT_CK1" => "",
                                      "STR_P_UP_SORT_CK0" => "checked",
            ));
        }
        $sql="select sc_cart_type,sc_desc_title_default,sc_character_title_default,sc_spec_title_default from ".$cms_cfg['tb_prefix']."_system_config where sc_id='1'";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        //自定產品標題說明
        if($cms_cfg["ws_module"]["ws_products_title"]==1) {
            include_once("../lang/".$cms_cfg['language']."-utf8.php");
            $tpl->newBlock("PRODUCTS_TITLE1");
            $tpl->assignGlobal("VALUE_DESC_TITLE",(trim($row["sc_desc_title_default"])=="")?$TPLMSG['PRODUCT_DESCRIPTION']:$row["sc_desc_title_default"]);
            $tpl->newBlock("PRODUCTS_TITLE2");
            $tpl->assignGlobal("VALUE_CHARACTER_TITLE",(trim($row["sc_character_title_default"])=="")?$TPLMSG['PRODUCT_CHARACTER']:$row["sc_character_title_default"]);
            $tpl->newBlock("PRODUCTS_TITLE3");
            $tpl->assignGlobal("VALUE_SPEC_TITLE",(trim($row["sc_spec_title_default"])=="")?$TPLMSG['PRODUCT_SPEC']:$row["sc_spec_title_default"]);
            $tpl->newBlock("PRODUCTS_TITLE4");
			$tpl->assignGlobal("VALUE_TXT1_TITLE","PRODUCTS TITLE4");
			$tpl->newBlock("PRODUCTS_TITLE5");
			$tpl->assignGlobal("VALUE_TXT2_TITLE","PRODUCTS TITLE5");
			$tpl->newBlock("PRODUCTS_TITLE6");
			$tpl->assignGlobal("VALUE_TXT3_TITLE","PRODUCTS TITLE6");
			$tpl->newBlock("PRODUCTS_TITLE7");
			$tpl->assignGlobal("VALUE_TXT4_TITLE","PRODUCTS TITLE7");
		}else{
            $tpl->assignGlobal( array("TAG_PRODUCTS_DESC" => $TPLMSG['PRODUCT_DESCRIPTION'].":",
                                      "TAG_PRODUCTS_CHARACTER" => $TPLMSG['PRODUCT_CHARACTER'].":",
                                      "TAG_PRODUCTS_SPEC" => $TPLMSG['PRODUCT_SPEC'].":",
            ));
        }
        //欄位名稱
        $cate=(trim($_REQUEST["pc_parent"])==0)?0:1;
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "VALUE_P_SORT" => 1,
                                  "VALUE_P_LIST_PRICE" => 0,
                                  "VALUE_P_SPECIAL_PRICE" => 0,
                                  "NOW_P_ID" => 0,
                                  "VALUE_P_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_products","p","pc_id",$_REQUEST["pc_parent"],$cate),
                                  "VALUE_SMALL_PIC_PREVIEW1" => $cms_cfg['default_preview_pic'],
                                  "VALUE_BIG_PIC_PREVIEW1" => $cms_cfg['default_preview_pic'],
                                  "VALUE_BIG_PIC_PREVIEW2" => $cms_cfg['default_preview_pic'],
                                  "VALUE_BIG_PIC_PREVIEW3" => $cms_cfg['default_preview_pic'],
                                  "VALUE_BIG_PIC_PREVIEW4" => $cms_cfg['default_preview_pic'],
                                  "STR_P_STATUS_CK1" => "checked",
                                  "STR_P_STATUS_CK0" => "",
                                  "STR_P_LOCK_CK1" => "checked",
                                  "STR_P_LOCK_CK0" => "",
                                  "STR_P_SHOW_PRICE_CK1" => "checked",
                                  "STR_P_SHOW_PRICE_CK0" => "",
                                  "STR_P_SHOW_STYLE_CK4" =>"",
                                  "STR_P_SHOW_STYLE_CK3" =>"",
                                  "STR_P_SHOW_STYLE_CK2" =>"",
                                  "STR_P_SHOW_STYLE_CK1" =>"checked",
                                  "STR_P_CUSTOM_STATUS_CK1" => "",
                                  "STR_P_CUSTOM_STATUS_CK0" => "checked",
                                  "STR_P_CUSTOM_STATUS_DISPLAY" => "none",
                                  "STR_NEW_SORT_DISPLAY" => "none",
                                  "VALUE_ACTION_MODE" => $action_mode
        ));		
        // 無新產品不顯示產品類型欄位
        ($cms_cfg["ws_module"]["ws_new_product"])?$tpl->newBlock( "PRODUCTS_TYPE_FIELD" ):"";
        switch($row["sc_cart_type"]){
            case 0 :
                $PRODUCT_SHOW_PRICE_NOTICE2=$TPLMSG['PRODUCT_SP_INQUIRY_NOTICE'];
                break;
            case 1 :
                $PRODUCT_SHOW_PRICE_NOTICE2=$TPLMSG['PRODUCT_SP_SHOPPING_NOTICE'];
                // IPS版本選擇購物車則顯示價格欄位
                $tpl->newBlock( "PRODUCTS_PRICE_FIELD" );
                break;
            case 2 :
                $PRODUCT_SHOW_PRICE_NOTICE2=$TPLMSG['PRODUCT_SP_DISPLAY_NOTICE'];
                // IPC套裝均顯示價格欄位提供編輯
                ($cms_cfg["ws_module"]["ws_version"]=="ipc")?$tpl->newBlock( "PRODUCTS_PRICE_FIELD" ):"";
                break;
        }
        $tpl->assignGlobal("MSG_PRODUCT_SHOW_PRICE_NOTICE2" , $PRODUCT_SHOW_PRICE_NOTICE2 );

        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        $pc_id=$_REQUEST["pc_parent"];//預設帶入的分類id
        //如果為修改模式,帶入資料庫資料
        if($action_mode=="mod" && !empty($_REQUEST["p_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_products where p_id='".$_REQUEST["p_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                if($cms_cfg["ws_module"]["ws_new_product"]){
                    //product type: 1-新產品 2-熱門產品 4-促銷產品
                    switch($row["p_type"]){
                        case 1 :
                            $tpl->assignGlobal("STR_P_TYPE_CK1","checked");
                            $tpl->assignGlobal("STR_NEW_SORT_DISPLAY","");
                            break;
                        case 2 :
                            $tpl->assignGlobal("STR_P_TYPE_CK2","checked");
                            $tpl->assignGlobal("STR_NEW_SORT_DISPLAY","none");
                            break;
                        case 3 :
                            $tpl->assignGlobal("STR_P_TYPE_CK1","checked");
                            $tpl->assignGlobal("STR_P_TYPE_CK2","checked");
                            $tpl->assignGlobal("STR_NEW_SORT_DISPLAY","");
                            break;
                        case 4 :
                            $tpl->assignGlobal("STR_P_TYPE_CK3","checked");
                            $tpl->assignGlobal("STR_NEW_SORT_DISPLAY","none");
                            break;
                        case 5 :
                            $tpl->assignGlobal("STR_P_TYPE_CK1","checked");
                            $tpl->assignGlobal("STR_P_TYPE_CK3","checked");
                            $tpl->assignGlobal("STR_NEW_SORT_DISPLAY","");
                            break;
                        case 6 :
                            $tpl->assignGlobal("STR_P_TYPE_CK2","checked");
                            $tpl->assignGlobal("STR_P_TYPE_CK3","checked");
                            $tpl->assignGlobal("STR_NEW_SORT_DISPLAY","none");
                            break;
                        case 7 :
                            $tpl->assignGlobal("STR_P_TYPE_CK1","checked");
                            $tpl->assignGlobal("STR_P_TYPE_CK2","checked");
                            $tpl->assignGlobal("STR_P_TYPE_CK3","checked");
                            $tpl->assignGlobal("STR_NEW_SORT_DISPLAY","");
                            break;
                    }
                }
                $tpl->assignGlobal( array("NOW_P_ID"  => $row["p_id"],
                                          "NOW_PC_ID"  => $row["pc_id"],
                                          "VALUE_P_SORT"  => $row["p_sort"],
                                          "VALUE_NEW_P_SORT" => $row["p_new_sort"],
                                          "VALUE_P_NAME" => $row["p_name"],
                                          "VALUE_P_NAME_ALIAS" => $row["p_name_alias"],
                                          "VALUE_P_CUSTOM" => $row["p_custom"],
                                          "VALUE_P_SERIAL" => $row["p_serial"],
                                          "VALUE_P_LIST_PRICE" => $row["p_list_price"],
                                          "VALUE_P_SPECIAL_PRICE" => $row["p_special_price"],
                                          "VALUE_SMALL_IMG" => (trim($row["p_small_img"])=="")?"":$cms_cfg["file_root"].$row["p_small_img"],
                                          "VALUE_SMALL_PIC_PREVIEW1" => (trim($row["p_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["p_small_img"],
                                          "STR_P_STATUS_CK1" => ($row["p_status"])?"checked":"",
                                          "STR_P_STATUS_CK0" => ($row["p_status"])?"":"checked",
                                          "STR_P_LOCK_CK1" => ($row["p_locked"])?"checked":"",
                                          "STR_P_LOCK_CK0" => ($row["p_locked"])?"":"checked",
                                          "STR_P_SHOW_PRICE1" => ($row["p_show_price"])?"checked":"",
                                          "STR_P_SHOW_PRICE0" => ($row["p_show_price"])?"":"checked",
                                          "STR_P_SHOW_STYLE_CK4" =>($row["p_show_style"]==4)?"checked":"",
                                          "STR_P_SHOW_STYLE_CK3" =>($row["p_show_style"]==3)?"checked":"",
                                          "STR_P_SHOW_STYLE_CK2" =>($row["p_show_style"]==2)?"checked":"",
                                          "STR_P_SHOW_STYLE_CK1" =>($row["p_show_style"]==1)?"checked":"",
                                          "STR_P_CUSTOM_STATUS_CK1" => ($row["p_custom_status"]==1)?"checked":"",
                                          "STR_P_CUSTOM_STATUS_CK0" => ($row["p_custom_status"]==0)?"checked":"",
                                          "STR_P_CUSTOM_STATUS_DISPLAY" => ($row["p_custom_status"]==1)?" ":"none",
                                          "MSG_MODE" => "修改",
                                          "VALUE_P_RELATED_PRODUCTS" => $row["p_related_products"],
                                          "VALUE_DESC_TITLE" =>$row["p_desc_title"],
                                          "VALUE_CHARACTER_TITLE" =>$row["p_character_title"],
                                          "VALUE_SPEC_TITLE" =>$row["p_spec_title"],
                                          "VALUE_P_CROSS_CATE" => $row["p_cross_cate"],
										  "VALUE_P_SHORT_SPEC" =>$row["p_short_spec"],
                ));
                //附加檔案
                if($cms_cfg['ws_module']['ws_products_upfiles']){
                    $tpl->newBlock("PRODUCTS_ATTACH_FILES");
                    $tpl->assign(array(
                        "VALUE_P_ATTACH_FILE1" => $row["p_attach_file1"],
                        "VALUE_P_ATTACH_FILE2" => $row["p_attach_file2"],                        
                    ));
                }
                //影片
                if($cms_cfg['ws_module']['ws_products_mv']){
                    $tpl->newBlock("MV_COLUMN");
                    $tpl->assign("VALUE_P_MV",$row['p_mv']);
                }
                if($this->seo){
                    $tpl->assignGlobal( array("VALUE_P_SEO_FILENAME" => $row["p_seo_filename"],
                                              "VALUE_P_SEO_TITLE" => $row["p_seo_title"],
                                              "VALUE_P_SEO_KEYWORD" => $row["p_seo_keyword"],
                                              "VALUE_P_SEO_DESCRIPTION" => $row["p_seo_description"],
                                              "VALUE_P_SEO_SHORT_DESC" => $row["p_seo_short_desc"],
                                              "STR_P_UP_SORT_CK1" => ($row["p_up_sort"])?"checked":"",
                                              "STR_P_UP_SORT_CK0" => ($row["p_up_sort"])?"":"checked",
                                              "VALUE_P_SEO_H1" => $row["p_seo_h1"]));
                    $this->get_items_name($row["p_related_products"],"p"); //相關產品
                }
                //取得大圖資料
                $sql="select * from ".$cms_cfg['tb_prefix']."_products_img where p_id='".$_REQUEST["p_id"]."'";
                $selectrs = $db->query($sql);
                $row2 = $db->fetch_array($selectrs,1);				
                $_SESSION[$cms_cfg['sess_cookie_name']]["BACK_EDIT_ZONE"]=$_SERVER["HTTP_REFERER"];
                $pc_id=$row["pc_id"];
            }else{
                header("location : products.php?func=p_list");
                die();
            }
        }
        for($j=1;$j<=$cms_cfg['big_img_limit'];$j++){	//新增時載入大圖區域及預設值
            //大圖區域TAB
            $tpl->newBlock("PRODUCTS_BIG_IMG_TAB");
            $tpl->assign("BIG_IMG_NO",$j);
            $tpl->newBlock("PRODUCTS_BIG_IMG");
            $tpl->assign( array(
                "VALUE_BIG_PIC_PREVIEW" => (trim($row2["p_big_img".$j])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row2["p_big_img".$j],
                "VALUE_BIG_PIC" => (trim($row2["p_big_img".$j])=="")?"":$cms_cfg["file_root"].$row2["p_big_img".$j],
                "BIG_IMG_NO" =>$j,
            ));
        }
        $this->products_cate_select2($this->products_cate_select_option,$pc_id, $parent=0, $indent="");
        $tpl->assignGlobal("TAG_SELECT_PRODUCTS_CATE" ,$this->products_cate_select_option);
        $tpl->assignGlobal( array ("VALUE_P_CUSTOM" => $row["p_custom"],
                                   "VALUE_P_SPEC" => $row["p_spec"],
                                   "TAG_SPEC_SHOW" => (trim($row["p_spec"]))?"":"none" ,
                                   "VALUE_P_CHARACTER" => $row["p_character"],
                                   "TAG_CHARACTER_SHOW" => (trim($row["p_character"]))?"":"none" ,
                                   "VALUE_P_DESC" => $row["p_desc"],
                                   "TAG_DESC_SHOW" => (trim($row["p_desc"]))?"":"none",
                                   "VALUE_P_CERT" => $row["p_certificate"],
                                   "TAG_CERT_SHOW" => (trim($row["p_certificate"]))?"":"none",
                                   "TAG_SHORT_DESC_SHOW" => (trim($row["p_seo_short_desc"]))?"":"none",
								   "VALUE_P_TXT1" => $row["p_txt1"],
								   "VALUE_TXT1_TITLE" => $row["p_txt1_title"],
                                   "TAG_TXT1_SHOW" => (trim($row["p_txt1"]))?"":"none",
				                   "VALUE_P_TXT2" => $row["p_txt2"],
								   "VALUE_TXT2_TITLE" => $row["p_txt2_title"],
                                   "TAG_TXT2_SHOW" => (trim($row["p_txt2"]))?"":"none",
				                   "VALUE_P_TXT3" => $row["p_txt3"],
								   "VALUE_TXT3_TITLE" => $row["p_txt3_title"],
                                   "TAG_TXT3_SHOW" => (trim($row["p_txt3"]))?"":"none",
				                   "VALUE_P_TXT4" => $row["p_txt4"],
								   "VALUE_TXT4_TITLE" => $row["p_txt4_title"],
                                   "TAG_TXT4_SHOW" => (trim($row["p_txt4"]))?"":"none",
        ));
        if($cms_cfg['ws_module']['ws_products_info_fields']){
            for($j=1;$j<=$cms_cfg['ws_module']['ws_products_info_fields'];$j++){
                $tpl->newBlock("INFO_FIELD_LIST");
                $tpl->assign(array(
                   "SERIAL"           => $j,
                   "TITLE_SERIAL"     => $j+3,
                   "ELM_SERIAL"       => $j+5,
                   "INFO_FIELD_TITLE" => $row["p_info_field".$j."_title"],
                   "INFO_FIELD_VALUE" => $row["p_info_field".$j],
                   "INFO_FIELD_SHOW"  => (trim($row["p_info_field".$j]))?"":"none",
                ));
            }
        }
        if($cms_cfg["ws_module"]['ws_products_application'] && $cms_cfg["ws_module"]['ws_application_products']){
            $this->application_checkbox($row["p_id"]);
        }
        if($cms_cfg['ws_module']['ws_products_ca']){
            $this->ca_checkbox($row['p_ca']);        
        }
    }
//產品管理--資料更新================================================================
    function products_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $main->magic_gpc($_REQUEST);
        if($this->seo){
            $add_field_str="p_name_alias,
                            p_seo_filename,
                            p_seo_title,
                            p_seo_keyword,
                            p_seo_description,
                            p_seo_short_desc,
                            p_seo_h1,
                            p_up_sort,";
            $add_value_str="'".htmlspecialchars($_REQUEST["p_name_alias"])."',
                            '".trim($_REQUEST["p_seo_filename"])."',
                            '".htmlspecialchars($_REQUEST["p_seo_title"])."',
                            '".htmlspecialchars($_REQUEST["p_seo_keyword"])."',
                            '".htmlspecialchars($_REQUEST["p_seo_description"])."',
                            '".$db->quote($main->content_file_str_replace($_REQUEST["p_seo_short_desc"]))."',
                            '".htmlspecialchars($_REQUEST["p_seo_h1"])."',
                            '".$_REQUEST["p_up_sort"]."',";
            $update_str="p_name_alias = '".htmlspecialchars($_REQUEST["p_name_alias"])."',
                         p_seo_filename='".trim($_REQUEST["p_seo_filename"])."',
                         p_seo_title='".htmlspecialchars($_REQUEST["p_seo_title"])."',
                         p_seo_keyword='".htmlspecialchars($_REQUEST["p_seo_keyword"])."',
                         p_seo_description='".htmlspecialchars($_REQUEST["p_seo_description"])."',
                         p_seo_short_desc='".$db->quote($main->content_file_str_replace($_REQUEST["p_seo_short_desc"]))."',
                         p_seo_h1='".htmlspecialchars($_REQUEST["p_seo_h1"])."',
                         p_up_sort='".$_REQUEST["p_up_sort"]."',";
        }
        if($cms_cfg['ws_module']['ws_products_info_fields']){
            for($j=1;$j<=$cms_cfg['ws_module']['ws_products_info_fields'];$j++){
                switch($_REQUEST["action_mode"]){
                    case "add":
                        $add_extra_fields .= "p_info_field".$j."_title,";
                        $add_extra_fields .= "p_info_field".$j.",";
                        $add_extra_values .= "'".$db->quote($_REQUEST['p_info_field'.$j.'_title'])."',";
                        $add_extra_values .= "'".$db->quote($_REQUEST['p_info_field'.$j])."',";
                        break;
                    case "mod":
                        $update_extra_fields .= "p_info_field".$j."_title ='".$db->quote($_REQUEST['p_info_field'.$j.'_title'])."',";
                        $update_extra_fields .= "p_info_field".$j."='".$db->quote($_REQUEST['p_info_field'.$j])."',";
                        break;
                }
            }
        }
        switch ($_REQUEST["action_mode"]){
            case "add":
                $this->p_type=$_REQUEST["p_type1"]+$_REQUEST["p_type2"]+$_REQUEST["p_type3"];
                //取得分類階層
                $products_cate_layer=$main->get_layer($cms_cfg['tb_prefix']."_products_cate","pc_id","pc",$_REQUEST["pc_id"],"",2);
                if(!empty($products_cate_layer)){
                    $pc_layer="0-".implode("-",$products_cate_layer);
                }else{
                    $pc_layer="0-".$_REQUEST["pc_id"];
                }
                $p_desc_strip_str=$main->replace_html_tags($_REQUEST["p_desc"]);
                $sql="
                    INSERT INTO ".$cms_cfg['tb_prefix']."_products(
                        pc_id,
                        pc_layer,
                        p_status,
                        p_sort,
                        p_new_sort,
                        p_name,
                        p_custom_status,
                        p_custom,
                        p_show_style,
                        p_type,
                        p_show_price,
                        p_list_price,
                        p_special_price,
                        p_serial,
                        p_small_img,
                        p_related_products,
                        p_spec_title,
                        p_spec,
                        p_character_title,
                        p_character,
                        p_desc_title,
                        p_desc,
                        p_desc_strip,
						p_txt1_title,
						p_txt1,
						p_txt2_title,
						p_txt2,
						p_txt3_title,
						p_txt3,
						p_txt4_title,
						p_txt4,
                        p_attach_file1,
                        p_attach_file2,
                        p_mv,
                        p_ca,
                        ".$add_extra_fields."
                        p_modifydate,
                        ".$add_field_str."
                        p_cross_cate,
                        p_locked,
						p_short_spec,
                        p_modifyaccount
                    ) VALUES (
                        '".$_REQUEST["pc_id"]."',
                        '".$pc_layer."',
                        '".$_REQUEST["p_status"]."',
                        '".$_REQUEST["p_sort"]."',
                        '".$_REQUEST["p_new_sort"]."',
                        '".htmlspecialchars($_REQUEST["p_name"])."',
                        '".$_REQUEST["p_custom_status"]."',
                        '".$db->quote($main->content_file_str_replace($_REQUEST["p_custom"]))."',
                        '".$_REQUEST["p_show_style"]."',
                        '".$this->p_type."',
                        '".$_REQUEST["p_show_price"]."',
                        '".$_REQUEST["p_list_price"]."',
                        '".$_REQUEST["p_special_price"]."',
                        '".htmlspecialchars($_REQUEST["p_serial"])."',
                        '".$main->file_str_replace($_REQUEST["p_small_img"])."',
                        '".$_REQUEST["p_related_products"]."',
                        '".$db->quote($_REQUEST["p_spec_title"])."',
                        '".$db->quote($main->content_file_str_replace($_REQUEST["p_spec"]))."',
                        '".$db->quote($_REQUEST["p_character_title"])."',
                        '".$db->quote($main->content_file_str_replace($_REQUEST["p_character"]))."',
                        '".$db->quote($_REQUEST["p_desc_title"])."',
                        '".$db->quote($main->content_file_str_replace($_REQUEST["p_desc"]))."',
                        '".$db->quote(htmlspecialchars($p_desc_strip_str))."',
                        '".$db->quote($_REQUEST["p_txt1_title"])."',
                        '".$db->quote($main->content_file_str_replace($_REQUEST["p_txt1"]))."',
                        '".$db->quote($_REQUEST["p_txt2_title"])."',
                        '".$db->quote($main->content_file_str_replace($_REQUEST["p_txt2"]))."',
                        '".$db->quote($_REQUEST["p_txt3_title"])."',
                        '".$db->quote($main->content_file_str_replace($_REQUEST["p_txt3"]))."',
                        '".$db->quote($_REQUEST["p_txt4_title"])."',
                        '".$db->quote($main->content_file_str_replace($_REQUEST["p_txt4"]))."',
                        '".$main->file_str_replace($_REQUEST["p_attach_file1"])."',
                        '".$main->file_str_replace($_REQUEST["p_attach_file2"])."',
                        '".$db->quote($_REQUEST["p_mv"])."',
                        '".implode(',',(array)$_REQUEST["p_ca"])."',
                        ".$add_extra_values."    
                        '".date("Y-m-d H:i:s")."',
                        ".$add_value_str."
                        '".$_REQUEST["p_cross_cate"]."',
                        '".$_REQUEST["p_locked"]."',
						'".$_REQUEST["p_short_spec"]."',
                        '".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'
                    )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                $this->p_id=$db->get_insert_id();
                break;
            case "mod":
                $this->p_type=$_REQUEST["p_type1"]+$_REQUEST["p_type2"]+$_REQUEST["p_type3"];
                //取得分類階層
                $products_cate_layer=$main->get_layer($cms_cfg['tb_prefix']."_products_cate","pc_id","pc",$_REQUEST["pc_id"],"",2);
                if(!empty($products_cate_layer)){
                    $pc_layer="0-".implode("-",$products_cate_layer);
                }else{
                    $pc_layer="0-".$_REQUEST["pc_id"];
                }
                $p_desc_strip_str=$main->replace_html_tags($_REQUEST["p_desc"]);
                $sql="
                UPDATE ".$cms_cfg['tb_prefix']."_products SET
                    pc_id = '".$_REQUEST["pc_id"]."',
                    pc_layer = '".$pc_layer."',
                    p_status = '".$_REQUEST["p_status"]."',
                    p_sort = '".$_REQUEST["p_sort"]."',
                    p_new_sort = '".$_REQUEST["p_new_sort"]."',
                    p_name = '".htmlspecialchars($_REQUEST["p_name"])."',
                    p_custom_status = '".$_REQUEST["p_custom_status"]."',
                    p_custom = '".$db->quote($main->content_file_str_replace($_REQUEST["p_custom"]))."',
                    p_show_style = '".$_REQUEST["p_show_style"]."',
                    p_type = '".$this->p_type."',
                    p_show_price = '".$_REQUEST["p_show_price"]."',
                    p_list_price = '".$_REQUEST["p_list_price"]."',
                    p_special_price = '".$_REQUEST["p_special_price"]."',
                    p_serial = '".htmlspecialchars($_REQUEST["p_serial"])."',
                    p_small_img = '".$main->file_str_replace($_REQUEST["p_small_img"])."',
                    p_related_products = '".$_REQUEST["p_related_products"]."',
                    p_spec_title = '".$db->quote($_REQUEST["p_spec_title"])."',
                    p_spec = '".$db->quote($main->content_file_str_replace($_REQUEST["p_spec"]))."',
                    p_character_title = '".$db->quote($_REQUEST["p_character_title"])."',
                    p_character = '".$db->quote($main->content_file_str_replace($_REQUEST["p_character"]))."',
                    p_desc_title = '".$db->quote($_REQUEST["p_desc_title"])."',
                    p_desc = '".$db->quote($main->content_file_str_replace($_REQUEST["p_desc"]))."',
                    p_desc_strip = '".$db->quote(htmlspecialchars($p_desc_strip_str))."',
					p_txt1_title = '".$db->quote($_REQUEST["p_txt1_title"])."',
					p_txt1 = '".$db->quote($main->content_file_str_replace($_REQUEST["p_txt1"]))."',
					p_txt2_title  = '".$db->quote($_REQUEST["p_txt2_title"])."',
					p_txt2 = '".$db->quote($main->content_file_str_replace($_REQUEST["p_txt2"]))."',
					p_txt3_title  = '".$db->quote($_REQUEST["p_txt3_title"])."',
					p_txt3 = '".$db->quote($main->content_file_str_replace($_REQUEST["p_txt3"]))."',
					p_txt4_title  = '".$db->quote($_REQUEST["p_txt4_title"])."',
					p_txt4 = '".$db->quote($main->content_file_str_replace($_REQUEST["p_txt4"]))."',
                    p_attach_file1 = '".$main->file_str_replace($_REQUEST["p_attach_file1"])."',
                    p_attach_file2 = '".$main->file_str_replace($_REQUEST["p_attach_file2"])."',
                    p_mv = '".$db->quote($_REQUEST["p_mv"])."',
                    p_ca = '".implode(',',(array)$_REQUEST["p_ca"])."',
                    ".$update_extra_fields."    
                    p_modifydate = '".date("Y-m-d H:i:s")."',
                    ".$update_str."
                    p_cross_cate = '".$_REQUEST["p_cross_cate"]."',
                    p_locked = '".$_REQUEST["p_locked"]."',
					p_short_spec = '".$_REQUEST["p_short_spec"]."',
                    p_modifyaccount = '".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'
                WHERE p_id ='".$_REQUEST["now_p_id"]."' ";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                $this->p_id=$_REQUEST["now_p_id"];
                break;
        }
        if ( $db_msg == "" ) {
            $p_big_img_replace_str="";
            for($j=1;$j<=$cms_cfg['big_img_limit'];$j++){
                    $p_img_target="p_big_img".$j;
                    $$p_img_target=$main->file_str_replace($_REQUEST["p_big_img".$j]);//會變成這樣$p_big_img1=$main->file_str_replace($_REQUEST["p_big_img1"])			
                    $p_big_img_replace_str.="p_big_img".$j." = '".$$p_img_target."',";
            }			
            $sql="
                REPLACE INTO ".$cms_cfg['tb_prefix']."_products_img SET
					".$p_big_img_replace_str."				
                    p_id = '".$this->p_id."'
            ";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if($cms_cfg["ws_module"]['ws_products_application'] && $cms_cfg["ws_module"]['ws_application_products']){//有應用領域
                if($_POST['pa_id_str']){
                    $db_msg .= $this->write_application($this->p_id,$_POST['pa_id_str']);
                }
            }
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."products.php?func=p_list&pc_parent=".$_REQUEST["pc_id"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
//產品管理--刪除--資料刪除可多筆處理================================================================
    function products_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["p_id"]){
            $p_id=array(0=>$_REQUEST["p_id"]);
        }else{
            $p_id=$_REQUEST["id"];
        }
        if(!empty($p_id)){
            $p_id_str = implode(",",$p_id);
            //刪除勾選的產品管理
            $sql="delete from ".$cms_cfg['tb_prefix']."_products where p_id in (".$p_id_str.")";
            $rs = $db->query($sql);
            //刪除勾選的產品管理圖檔
            $sql="delete from ".$cms_cfg['tb_prefix']."_products_img where p_id in (".$p_id_str.")";
            $rs = $db->query($sql);
            //刪除勾選的clone產品管理
            $sql="delete from ".$cms_cfg['tb_prefix']."_products where p_clone in (".$p_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."products.php?func=p_list&pc_parent=".$_REQUEST["pc_parent"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //顯示訊息並重新導向
    function goto_target_page($url,$sec=0){
        global $tpl;
        if(!empty($url)){
            $tpl->assignGlobal( "TAG_META_REFRESH" , "<meta http-equiv=\"refresh\" content=\"$sec;URL=$url\">");
        }
    }
    //更改狀態
    function change_status($ws_table,$value){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //產品管理分類更改狀態
        if($ws_table=="pc"){
            if($_REQUEST["pc_id"]){
                $pc_id=array(0=>$_REQUEST["pc_id"]);
            }else{
                $pc_id=$_REQUEST["id"];
            }
            if(!empty($pc_id)){
                $pc_id_str = implode(",",$pc_id);
                $sql="update ".$cms_cfg['tb_prefix']."_products set p_status=".$value." where pc_id in (".$pc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    //狀態分類狀態
                    $sql="update ".$cms_cfg['tb_prefix']."_products_cate set pc_status=".$value." where pc_id in (".$pc_id_str.")";
                    $rs = $db->query($sql);
                    $db_msg = $db->report();
                    if ( $db_msg == "" ) {
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                        $goto_url=$cms_cfg["manage_url"]."products.php?func=pc_list&pc_parent=".$_REQUEST["pc_parent"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                        $this->goto_target_page($goto_url);
                    }else{
                        $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                    }
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }elseif($ws_table=="p"){//產品管理更改狀態
            if($_REQUEST["p_id"]){
                $p_id=array(0=>$_REQUEST["p_id"]);
            }else{
                $p_id=$_REQUEST["id"];
            }
            if(!empty($p_id)){
                $p_id_str = implode(",",$p_id);
                $sql="update ".$cms_cfg['tb_prefix']."_products set p_status=".$value." where p_clone in (".$p_id_str.")";
                $rs = $db->query($sql);
                $sql="update ".$cms_cfg['tb_prefix']."_products set p_status=".$value." where p_id in (".$p_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."products.php?func=p_list&pc_parent=".$_REQUEST["pc_parent"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }elseif($ws_table=="pa"){//應用領域更改狀態
            if($_REQUEST["pa_id"]){
                $pa_id=(array)$_REQUEST["pa_id"];
            }else{
                $pa_id=$_REQUEST["id"];
            }
            if(!empty($pa_id)){
                $pa_id_str = implode(",",$pa_id);
                $sql="update ".$cms_cfg['tb_prefix']."_products_application set pa_status=".$value." where pa_id in (".$pa_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."products.php?func=pa_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }  
            }
        }
    }
    //更改狀態
    function change_lock($ws_table,$value){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //產品管理分類更改資料鎖定
        if($ws_table=="pc"){
            if($_REQUEST["pc_id"]){
                $pc_id=array(0=>$_REQUEST["pc_id"]);
            }else{
                $pc_id=$_REQUEST["id"];
            }
            if(!empty($pc_id)){
                $pc_id_str = implode(",",$pc_id);
                $sql="update ".$cms_cfg['tb_prefix']."_products_cate set pc_locked=".$value." where pc_id in (".$pc_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."products.php?func=pc_list&pc_parent=".$_REQUEST["pc_parent"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
        //產品管理更改資料鎖定
        if($ws_table=="p"){
            if($_REQUEST["p_id"]){
                $p_id=array(0=>$_REQUEST["p_id"]);
            }else{
                $p_id=$_REQUEST["id"];
            }
            if(!empty($p_id)){
                $p_id_str = implode(",",$p_id);
                $sql="update ".$cms_cfg['tb_prefix']."_products set p_locked=".$value." where p_clone in (".$p_id_str.")";
                $rs = $db->query($sql);
                $sql="update ".$cms_cfg['tb_prefix']."_products set p_locked=".$value." where p_id in (".$p_id_str.")";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."products.php?func=p_list&pc_parent=".$_REQUEST["pc_parent"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
    }
    //更改排序值
    function change_sort($ws_table){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        //產品管理分類更改排序值
        if(!empty($_REQUEST["sort_value"]) && !empty($ws_table)){
            if($ws_table=="pc"){
                $table_name=$cms_cfg['tb_prefix']."_products_cate";
            }elseif($ws_table=="p" || $ws_table=="p_new"){
                $table_name=$cms_cfg['tb_prefix']."_products";
            }elseif($ws_table=="pa"){
                $table_name=$cms_cfg['tb_prefix']."_products_application";
            }elseif($ws_table=="ca"){
                $table_name=$cms_cfg['tb_prefix']."_products_ca";
            }
            foreach($_REQUEST["id"] as $key => $value){
                if($ws_table == "p_new") {
                    $sql="update ".$table_name." set ".$ws_table."_sort=".$_REQUEST["sort_value"][$value]." where  p_id='".$value."'";
                }else{
                    $sql="update ".$table_name." set ".$ws_table."_sort=".$_REQUEST["sort_value"][$value]." where ".$ws_table."_id='".$value."'";
                }
                $rs = $db->query($sql);
                $db_msg = $db->report();
            }
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."products.php?func=".$ws_table."_list&pc_parent=".$_REQUEST["pc_parent"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }
    //複製單筆資料
    function copy_data($ws_table){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //產品管理分類複製
        if($ws_table=="pc"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_products_cate where pc_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($this->seo){
                $add_field_str="pc_name_alias,
                                pc_seo_title,
                                pc_seo_keyword,
                                pc_seo_description,
                                pc_seo_short_desc,
                                pc_seo_down_short_desc,
                                pc_seo_h1,
                                pc_up_sort,";
                $add_value_str="'".addslashes($row["pc_name_alias"])."',
                                '".addslashes($row["pc_seo_title"])."',
                                '".addslashes($row["pc_seo_keyword"])."',
                                '".addslashes($row["pc_seo_description"])."',
                                '".addslashes($row["pc_seo_short_desc"])."',
                                '".addslashes($row["pc_seo_down_short_desc"])."',
                                '".addslashes($row["pc_seo_h1"])."',
                                '".$row["pc_up_sort"]."',";
            }
            if($rsnum >0){
                $sql="
                insert into ".$cms_cfg['tb_prefix']."_products_cate(
                    pc_parent,
                    pc_status,
                    pc_sort,
                    pc_name,
                    pc_custom_status,
                    pc_custom,
                    pc_show_style,
                    pc_cate_img,
                    pc_related_cate,
                    pc_modifydate,
                    ".$add_field_str."
                    pc_cross_cate,
                    pc_locked,
                    pc_modifyaccount
                ) values (
                    '".$row["pc_parent"]."',
                    '".$row["pc_status"]."',
                    '".$main->get_max_sort_value($cms_cfg['tb_prefix']."_products_cate","pc","pc_parent",$row['pc_parent'],true)."',
                    '".addslashes($row["pc_name"])." (copy)',
                    '".$row["pc_custom_status"]."',
                    '".addslashes($row["pc_custom"])."',
                    '".$row["pc_show_style"]."',
                    '".$row["pc_cate_img"]."',
                    '".$row["pc_related_cate"]."',
                    '".$row["pc_modifydate"]."',
                    ".$add_value_str."
                    '".$row["pc_cross_cate"]."',
                    '".$row["pc_locked"]."',
                    '".$row["pc_modifyaccount"]."'
                )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $this->pc_id=$db->get_insert_id();
                    //取得新的分類階層
                    $products_cate_layer=$main->get_layer($cms_cfg['tb_prefix']."_products_cate","pc_id","pc",$this->pc_id,"",2);
                    if(!empty($products_cate_layer)){
                        $pc_layer="0-".implode("-",$products_cate_layer);
                        $pc_level=count($products_cate_layer)+1;
                    }else{
                        $pc_layer="0-".$this->pc_id;
                        $pc_level=1;
                    }
                    $sql="
                        update ".$cms_cfg['tb_prefix']."_products_cate set
                            pc_layer='".$pc_layer."',
                            pc_level='".$pc_level."'
                        where pc_id='".$this->pc_id."'
                    ";
                    $rs = $db->query($sql);
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."products.php?func=pc_list&pc_parent=".$_REQUEST["pc_parent"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }elseif($ws_table=="p"){//產品管理複製
            $sql="select p.*,pi.*  from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_img as pi on p.p_id=pi.p_id where p.p_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($this->seo){
                $add_field_str="p_name_alias,
                                p_seo_title,
                                p_seo_keyword,
                                p_seo_description,
                                p_seo_short_desc,
                                p_seo_h1,
                                p_up_sort,";
                $add_value_str="'".addslashes($row["p_name_alias"])."',
                                '".addslashes($row["p_seo_title"])."',
                                '".addslashes($row["p_seo_keyword"])."',
                                '".addslashes($row["p_seo_description"])."',
                                '".addslashes($row["p_seo_short_desc"])."',
                                '".addslashes($row["p_seo_h1"])."',
                                '".$row["p_up_sort"]."',";
            }
            if($rsnum >0){
                $p_desc_strip_str=$main->replace_html_tags($row["p_desc"]);
                $sql="
                INSERT INTO ".$cms_cfg['tb_prefix']."_products(
                    pc_id,
                    pc_layer,
                    p_status,
                    p_sort,
                    p_new_sort,
                    p_name,
                    p_custom_status,
                    p_custom,
                    p_show_style,
                    p_type,
                    p_show_price,
                    p_list_price,
                    p_special_price,
                    p_serial,
                    p_small_img,
                    p_related_products,
                    p_spec_title,
                    p_spec,
                    p_character_title,
                    p_character,
                    p_desc_title,
                    p_desc,
                    p_desc_strip,
                    p_attach_file1,
                    p_attach_file2,
                    p_modifydate,
                    ".$add_field_str."
                    p_cross_cate,
                    p_locked,
                    p_modifyaccount
                ) VALUES (
                    '".$row["pc_id"]."',
                    '".$row["pc_layer"]."',
                    '".$row["p_status"]."',
                    '".$main->get_max_sort_value($cms_cfg['tb_prefix']."_products","p","pc_id",$row['pc_id'],true)."',
                    '".$row["p_new_sort"]."',
                    '".addslashes($row["p_name"])." (copy)',
                    '".$row["p_custom_status"]."',
                    '".addslashes($row["p_custom"])."',
                    '".$row["p_show_style"]."',
                    '".$row["p_type"]."',
                    '".$row["p_show_price"]."',
                    '".$row["p_list_price"]."',
                    '".$row["p_special_price"]."',
                    '".$row["p_serial"]."',
                    '".$row["p_small_img"]."',
                    '".$row["p_related_products"]."',
                    '".$row["p_spec_title"]."',
                    '".addslashes($row["p_spec"])."',
                    '".$row["p_character_title"]."',
                    '".addslashes($row["p_character"])."',
                    '".$row["p_desc_title"]."',
                    '".addslashes($row["p_desc"])."',
                    '".addslashes($p_desc_strip_str)."',
                    '".$row["p_attach_file1"]."',
                    '".$row["p_attach_file2"]."',
                    '".$row["p_modifydate"]."',
                    ".$add_value_str."
                    '".$row["p_cross_cate"]."',
                    '".$row["p_locked"]."',
                    '".$row["p_modifyaccount"]."'
                )";
                $rs = $db->query($sql);
                $this->p_id=$db->get_insert_id();
				$p_big_img_replace_str="";
				for($j=1;$j<=$cms_cfg['big_img_limit'];$j++){
					$p_img_target="p_big_img".$j;
					$$p_img_target=$main->file_str_replace($row["p_big_img".$j]);//會變成這樣$p_big_img1=$main->file_str_replace($row["p_big_img1"])			
					$p_big_img_replace_str.="p_big_img".$j." = '".$$p_img_target."',";
				}			
				$sql="
					REPLACE INTO ".$cms_cfg['tb_prefix']."_products_img SET
						".$p_big_img_replace_str."				
						p_id = '".$this->p_id."'
				";
				$rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."products.php?func=p_list&pc_parent=".$_REQUEST["pc_parent"]."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }elseif($ws_table=="pa"){
            $sql="select * from ".$cms_cfg['tb_prefix']."_products_application where pa_id='".$_REQUEST["id"][0]."'";
            $selectrs = $db->query($sql);
            $rsnum    = $db->numRows($selectrs);
            $row = $db->fetch_array($selectrs,1);
            if($this->seo){
                $add_field_str="pa_name_alias,
                                pa_seo_title,
                                pa_seo_keyword,
                                pa_seo_description,
                                pa_seo_short_desc,
                                pa_seo_down_short_desc,
                                pa_seo_h1,";
                $add_value_str="'".addslashes($row["pa_name_alias"])."',
                                '".addslashes($row["pa_seo_title"])."',
                                '".addslashes($row["pa_seo_keyword"])."',
                                '".addslashes($row["pa_seo_description"])."',
                                '".addslashes($row["pa_seo_short_desc"])."',
                                '".addslashes($row["pa_seo_down_short_desc"])."',
                                '".addslashes($row["pa_seo_h1"])."',";
            }
            if($rsnum >0){
                $sql="
                insert into ".$cms_cfg['tb_prefix']."_products_application(
                    pa_status,
                    pa_sort,
                    pa_name,
                    pa_custom_status,
                    pa_custom,
                    pa_small_img,
                    pa_modifydate,
                    ".$add_field_str."
                    pa_modifyaccount
                ) values (
                    '".$row["pa_status"]."',
                    '".$main->get_max_sort_value($cms_cfg['tb_prefix']."_products_application","pa","","",0)."',
                    '".addslashes($row["pa_name"])." (copy)',
                    '".$row["pa_custom_status"]."',
                    '".addslashes($row["pa_custom"])."',
                    '".$row["pa_small_img"]."',
                    '".date("Y-m-d H:i:s")."',
                    ".$add_value_str."
                    '".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'
                )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $this->pc_id=$db->get_insert_id();
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                    $goto_url=$cms_cfg["manage_url"]."products.php?func=pa_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                    $this->goto_target_page($goto_url);
                }else{
                    $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
                }
            }
        }
    }
    //組合分類下拉選單
    function products_cate_select(&$output, &$pc_id,$now_pc_parent, $pc_parent=0, $indent="") {
        global $db,$cms_cfg;
        $sql = "SELECT pc_id,pc_name FROM ".$cms_cfg['tb_prefix']."_products_cate WHERE  pc_parent='".$pc_parent."' order by pc_sort ".$cms_cfg['sort_pos'].",pc_modifydate desc";
        $selectrs = $db->query($sql);
        while ($row =  $db->fetch_array($selectrs,1)) {
            $selected = ($row["pc_id"]==$now_pc_parent ? "selected" : "");
            //自己分類底下的項目不提供選擇以免進入無窮迴圈
            if($row["pc_id"]!=$pc_id){
                $output .= "<option value=\"".$row["pc_id"]."\" ".$selected.">".$indent."├".$row["pc_name"]."</option>";
                if($row["pc_id"]!=$pc_parent){
                    $this->products_cate_select($output, $pc_id,$now_pc_parent, $row["pc_id"],$indent."****");
                }
            }
        }
    }
    //組合分類下拉選單--產品選擇分類專用
    function products_cate_select2(&$output,$now_pc_parent, $pc_parent=0, $indent="") {
        global $db,$cms_cfg;
        $sql = "SELECT pc_id,pc_name FROM ".$cms_cfg['tb_prefix']."_products_cate WHERE pc_parent='".$pc_parent."' order by pc_sort ".$cms_cfg['sort_pos'].",pc_modifydate desc";
        $selectrs = $db->query($sql);
        while ($row =  $db->fetch_array($selectrs,1)) {
            $selected = ($row["pc_id"]==$now_pc_parent) ? "selected" : "";
            $output .= "<option value=\"".$row["pc_id"]."\" ".$selected.">".$indent."├".$row["pc_name"]."</option>";
            if($row["pc_id"]!=$pc_parent){
                $this->products_cate_select2($output,$now_pc_parent, $row["pc_id"],$indent."****");
            }
        }
    }
    function products_cate_tree($pc_id,$type){
        global $tpl,$db,$cms_cfg;
        $sql="select pc_id,pc_layer from ".$cms_cfg['tb_prefix']."_products_cate where pc_id='".$pc_id."'";
        $selectrs = $db->query($sql);
        $rsnum = $db->numRows($selectrs);
        if($rsnum >0){
            $row = $db->fetch_array($selectrs,1);
            $pc_layer_array=explode("-",$row["pc_layer"]);
        }else{
            $pc_layer_array=array();
        }
        $pc_cate_tree=$this->get_tree(0,$pc_id,$pc_layer_array,$pc_cate_tree="",$type);
        $tpl->assignGlobal( "VALUE_PC_CATE_TREE",$pc_cate_tree);
    }
    function get_tree($pc_id,$now_pc_id,$pc_layer_array,$pc_cate_tree,$type){
        global $db,$cms_cfg,$tpl;
        $sql="select pc_id,pc_parent,pc_name,pc_layer,pc_level from ".$cms_cfg['tb_prefix']."_products_cate where pc_parent='".$pc_id."' order by pc_sort ".$cms_cfg['sort_pos'].",pc_modifydate desc";
        $selectrs = $db->query($sql);
        $rsnum = $db->numRows($selectrs);
        if($rsnum >0){
            $totalwords=strlen($pc_cate_tree);
            $mi=substr($pc_cate_tree,$totalwords-6,6);
            if($mi=="</li>\n"){
                $pc_cate_tree=substr($pc_cate_tree,0,$totalwords-6)."\n<ul>";
            }else{
                if($pc_id==0){
                    $pc_cate_tree .="\n<ul>";
                }else{
                    $pc_cate_tree .="\n</li>\n<ul>\n";
                }
            }
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $space_str=str_repeat("        ",$row["pc_level"]);
                $tag_span_class=($now_pc_id==$row["pc_id"])?"active":"text";
                $class_str=(in_array($row["pc_id"],$pc_layer_array))?"class=\"open\"":"";
                $pc_cate_tree =$space_str.$pc_cate_tree ."<li id='".$row["pc_id"]."' ".$class_str."><span class='".$tag_span_class."'>&nbsp;</span><a href=\"products.php?func=".$type."_list&pc_parent=".$row["pc_id"]."\">".$row["pc_name"]."</a></li>\n";
                $pc_cate_tree = $this->get_tree($row["pc_id"],$now_pc_id,$pc_layer_array,$pc_cate_tree,$type);
            }
            $pc_cate_tree =($mi=="</li>\n")?$space_str.$pc_cate_tree ."</ul>\n</li>\n":$pc_cate_tree =$space_str.$pc_cate_tree ."</ul>\n";
        }
        return $pc_cate_tree;
    }
    function related_items($related_type,$id){
        global $tpl,$db,$main,$cms_cfg;
        switch ($related_type){
            case "pc_related_cate":
                $sql="select pc_related_cate as items from ".$cms_cfg['tb_prefix']."_products_cate where pc_id='".$id."'";
                break;
            case "p_related_products":
                $sql="select p_related_products as items from ".$cms_cfg['tb_prefix']."_products where p_id='".$id."'";
                break;
            case "pc_cross_cate":
                $sql="select pc_related_cate as items from ".$cms_cfg['tb_prefix']."_products_cate where pc_id='".$id."'";
                break;
            case "p_cross_cate":
                $sql="select p_related_cate as items from ".$cms_cfg['tb_prefix']."_products where p_id='".$id."'";
                break;
            case "ad_show_zone":
                $sql="select ad_show_zone as items from ".$cms_cfg['tb_prefix']."_ad where ad_id='".$id."'";
                break;
        }
        if(!empty($sql)){
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $id_array=array();
            if(!empty($row)){
                $id_array=explode(",",trim($row["items"]));
            }
            $tpl->assignGlobal( "CATE_TREE_PREFACE", $main->Preface());

            $tpl->assignGlobal( "VALUE_RETURN_ID", $_REQUEST["return_id"]);
            // 顯示more文字連結
            switch($related_type) {
                case "pc_related_cate":
                    $tpl->assignGlobal( "CATE_TREE_LIST", $this->products_cate_tree(0,0,"products.php?func=p_list&",0,"checkbox",$id_array));
                    $sql = "select * from ".$cms_cfg['tb_prefix']."_products_cate where pc_parent=0";
                    break;
                case "p_related_products":
                    $tpl->assignGlobal( "CATE_TREE_LIST", $this->products_tree("products.php?func=p_list","checkbox",$id_array));
                    $sql = "select * from ".$cms_cfg['tb_prefix']."_products";
                    break;
            }
            $selectrs = $db->query($sql);
            $rsnum = $db->numRows($selectrs);
            if($rsnum > $cms_cfg['related_limit'] && !$_REQUEST["show_all_items"]) {
                $tpl->newBlock("SHOW_ALL_ITEMS");
                $tpl->assign("VALUE_RELATED_TYPE" , $related_type);
                $tpl->assign("VALUE_PC_PARENT_ID" , $_REQUEST['id']);
                $tpl->assign("VALUE_RETURN_AGIN_ID" , $_REQUEST["return_id"]);
            }
        }
    }
    function select_cate($pc_id_str,$id){
        global $tpl,$db,$main,$cms_cfg;
        $tpl->assignGlobal("TAG_ROOT_PATH" , $cms_cfg['base_root']);
        $id_array=explode(",",$pc_id_str);
        $sql="select pc_id,pc_name,pc_layer from ".$cms_cfg['tb_prefix']."_products_cate where pc_status='1' order by pc_layer,pc_sort ".$cms_cfg['sort_pos'];
        $selectrs = $db->query($sql);
        $rsnum = $db->numRows($selectrs);
        $pc_name_tabs="";
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $k=0;
            if(substr_count($row["pc_layer"],"-")==1){
                $i=0;
                $pc_name_tabs .= "<li><a href=\"#".$row["pc_name"]."\">".$row["pc_name"]."</a></li>\n";
                $tpl->newBlock("PRODUCT_CATE_MAIN");
                $tpl->assign("VALUE_PC_MAIN_NAME",$row["pc_name"]);
                $tpl->assign("VALUE_PC_ID",$row["pc_id"]);
                $tpl->assign("TAG_DIV_DISPLAY","none");//預設為隱藏
                if(in_array($row["pc_id"],$id_array)){
                    $main_pc_id=$row["pc_id"];
                    $tpl->assign("TAG_CHECKED","checked");
                    $tpl->assign("TAG_DIV_DISPLAY","");//有勾選的話即展開
                }
            }else{
                $i++;
                $tpl->newBlock("PRODUCT_CATE_SUB");
                $tpl->assign("VALUE_PC_SUB_NAME",$row["pc_name"]);
                $tpl->assign("VALUE_PC_ID",$row["pc_id"]);
                if(in_array($row["pc_id"],$id_array)){
                    $k=1;
                    $tpl->assign("TAG_FONT_COLOR","blue");
                    $tpl->assign("TAG_CHECKED","checked");
                    $tpl->assign("TAG_DIV_DISPLAY","");//有勾選的話即展開
                }
                if($i%6==0){
                    $tpl->assign("TAG_TR","</tr><tr>");
                }
                $tpl->gotoBlock("PRODUCT_CATE_MAIN");
                if($k==1){
                    $tpl->assign("TAG_DIV_DISPLAY","");//有勾選的話即展開
                }
            }
        }
        $tpl->assignGlobal( "VALUE_PC_MAIN_TABS",$pc_name_tabs);
        $tpl->assignGlobal( "VALUE_RETURN_ID", $_REQUEST["return_id"]);
    }
    function select_products($p_id_str,$id){
        global $tpl,$db,$main,$cms_cfg;
        $tpl->assignGlobal("TAG_ROOT_PATH" , $cms_cfg['base_root']);
        $id_array=explode(",",$p_id_str);
        $sql="select pc_id,pc_name,pc_layer from ".$cms_cfg['tb_prefix']."_products_cate where pc_parent='0' order by pc_sort ".$cms_cfg['sort_pos'];
        $selectrs = $db->query($sql);
        $rsnum = $db->numRows($selectrs);
        if($rsnum > 0){
            $pc_name_tabs="";
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $k=0;
                $i=1;
                $pc_name_tabs .= "<li><a href=\"#".$row["pc_name"]."\">".$row["pc_name"]."</a></li>\n";
                $tpl->newBlock("PRODUCT_CATE_MAIN");
                $tpl->assign("VALUE_PC_MAIN_NAME",$row["pc_name"]);
                $tpl->assign("VALUE_PC_ID",$row["pc_id"]);
                $tpl->assign("TAG_DIV_DISPLAY","none");//預設為隱藏
                $sql1="select p_id,p_serial,p_name from ".$cms_cfg['tb_prefix']."_products where p_status='1' and pc_layer regexp '".$row["pc_layer"]."(-[0-9]+)*$' order by p_sort ".$cms_cfg['sort_pos']." ";
                $selectrs1 = $db->query($sql1,true);
                while ( $row1 = $db->fetch_array($selectrs1,1) ) {
                    $tpl->newBlock("PRODUCT_CATE_SUB");
                    $tpl->assign("VALUE_PC_SUB_NAME",$row1["p_name"]);
                    $tpl->assign("VALUE_PC_ID",$row1["p_id"]);
                    if(in_array($row1["p_id"],$id_array)){
                        $k=1;
                        $tpl->assign("TAG_FONT_COLOR","blue");
                        $tpl->assign("TAG_CHECKED","checked");
                        $tpl->assign("TAG_DIV_DISPLAY","");//有勾選的話即展開
                    }
                    if($i%6==0 && $i!=0){
                        $tpl->assign("TAG_TR","</tr><tr>");
                    }
                    $tpl->gotoBlock("PRODUCT_CATE_MAIN");
                    if($k==1){
                        $tpl->assign("TAG_DIV_DISPLAY","");//有勾選的話即展開
                    }
                    $i++;
                }
            }
            $tpl->assignGlobal( "VALUE_PC_MAIN_TABS",$pc_name_tabs);
        }
        $tpl->assignGlobal( "VALUE_RETURN_ID", $_REQUEST["return_id"]);
    }
    //資料處理
    function data_processing(){
        switch ($_REQUEST["process_type"]){
            case "del":
                if($_REQUEST["ws_table"]=="pc"){
                    $this->products_cate_del();
                }elseif($_REQUEST["ws_table"]=="p"){
                    $this->products_del();
                }elseif($_REQUEST["ws_table"]=="pa"){
                    $this->products_application_del();
                }elseif($_REQUEST["ws_table"]=="ca"){
                    $this->products_ca_del();
                }
                break;
            case "copy":
                $this->copy_data($_REQUEST["ws_table"]);
                break;
            case "status":
                $this->change_status($_REQUEST["ws_table"],$_REQUEST["value"]);
                break;
            case "lock":
                $this->change_lock($_REQUEST["ws_table"],$_REQUEST["value"]);
                break;
            case "sort":
                $this->change_sort($_REQUEST["ws_table"]);
                break;
        }
    }
    function get_items_name($id_str,$type){
        global $db,$tpl,$cms_cfg;
        if(trim($id_str)){
            if($type=="pc"){
                $sql="select pc_id,pc_name from ".$cms_cfg['tb_prefix']."_products_cate where pc_id in (".$id_str.")";
                $selectrs = $db->query($sql);
                while ( $row = $db->fetch_array($selectrs,1) ) {
                    $name_str  .= $row["pc_id"]."-".$row["pc_name"]."<br>";
                }
            }
            if($type=="p"){
                $sql="select p_id,p_name from ".$cms_cfg['tb_prefix']."_products where p_id in (".$id_str.")";
                $selectrs = $db->query($sql);
                while ( $row = $db->fetch_array($selectrs,1) ) {
                    $name_str  .= $row["p_id"]."-".$row["p_name"]."<br>";
                }
            }
            $tpl->assignGlobal("VALUE_NAME_STR",$name_str);
        }
    }
    //新產品管理--列表================================================================
    function newproducts_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main,$ws_array;
        $sql="select count(pc_id) as pc_total from ".$cms_cfg['tb_prefix']."_products_cate where pc_id > '0'";
        $selectrs = $db->query($sql);
        $row = $db->fetch_array($selectrs,1);
        //沒有分類先建立分類
        if($row["pc_total"]<1){
            $tpl->assignGlobal( "MSG_CREATE_CATE_FIRST" , $TPLMSG["CREATE_CATE_FIRST"]);
            $goto_url=$cms_cfg["manage_url"]."products.php?func=pc_add";
            $this->goto_target_page($goto_url);
        }else{
            //產品管理列表
            $sql="select p.*,pc.pc_name from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where  p_type in ('1','3','5','7')";
            //附加條件
            $and_str="";
            if(!empty($_REQUEST["pc_parent"])){
                $and_str .= " and p.pc_id = '".$_REQUEST["pc_parent"]."'";
            }
            if($_REQUEST["st"]=="all"){
                $and_str .= " and (p.p_name like '%".$_REQUEST["sk"]."%' or p.p_spec like '%".$_REQUEST["sk"]."%' or p.p_character like '%".$_REQUEST["sk"]."%' or p.p_desc like '%".$_REQUEST["sk"]."%')";
            }
            if($_REQUEST["st"]=="p_name"){
                $and_str .= " and p.p_name like '%".$_REQUEST["sk"]."%'";
            }
            if($_REQUEST["st"]=="p_spec"){
                $and_str .= " and p.p_spec like '%".$_REQUEST["sk"]."%'";
            }
            if($_REQUEST["st"]=="p_character"){
                $and_str .= " and p.p_character like '%".$_REQUEST["sk"]."%'";
            }
            if($_REQUEST["st"]=="p_desc"){
                $and_str .= " and p.p_desc like '%".$_REQUEST["sk"]."%'";
            }
            $sql .= $and_str." order by p.p_new_sort ".$cms_cfg['sort_pos'].",p.p_modifydate desc ";
            //取得總筆數
            $selectrs = $db->query($sql);
            $total_records    = $db->numRows($selectrs);
            //取得分頁連結
            $func_str="products.php?func=new_p_list&pc_parent=".$this->parent."&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
            //分頁且重新組合包含limit的sql語法
            $sql=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
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
                case "p_name" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK1", "selected");
                    break;
                case "p_spec" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK2", "selected");
                    break;
                case "p_character" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK3", "selected");
                    break;
                case "p_desc" :
                    $tpl->assignGlobal("STR_SELECT_SEARCH_TARGET_CK4", "selected");
                    break;
            }
            //產品列表
            $i=$page["start_serial"];
            while ( $row = $db->fetch_array($selectrs,1) ) {
                $i++;
                $tpl->newBlock( "PRODUCTS_LIST" );
                if($i%2){
                    $tpl->assign("TAG_TR_CLASS","class='altrow'");
                }
                $tpl->assign( array("VALUE_PC_ID"  => $row["p_id"],
                                    "VALUE_P_ID"  => $row["p_id"],
                                    "VALUE_P_SORT"  => $row["p_new_sort"],
                                    "VALUE_P_NAME" => $row["p_name"],
                                    "VALUE_P_SERIAL" => $i,
                                    "VALUE_PC_NAME"  => ($row["pc_name"])?$row["pc_name"]:$TPLMSG['NO_CATE'],
                                    "VALUE_STATUS_IMG" => ($row["p_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                    "VALUE_STATUS_IMG_ALT" => ($row["p_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
                ));
            }
        }
    }
    //應用領域列表
    function products_application_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_products_application where pa_id > '0'";
        $and_str = "";
        if(!empty($_REQUEST["sk"])){
            $and_str .= " and pa_name like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by pa_sort ".$cms_cfg['sort_pos'].",pa_modifydate desc ";
        //取得總筆數
        $total_records=$main->count_total_records($sql);
        //取得分頁連結
        $func_str="products.php?func=pa_list&&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        //分頁且重新組合包含limit的sql語法
        $sql=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                  "VALUE_TOTAL_BOX" => $rsnum,
                                  "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],
                                  "VALUE_NOW_PAGE" => $_REQUEST['nowp']
        ));
        //分類列表
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "PRODUCTS_APPLICATION_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $tpl->assign( array("VALUE_PA_ID"  => $row["pa_id"],
                                "VALUE_PA_STATUS"  => $row["pa_status"],
                                "VALUE_PA_SORT"  => $row["pa_sort"],
                                "VALUE_PA_NAME" => $row["pa_name"],
                                "VALUE_PA_SMALL_IMG" => (trim($row["pa_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["pa_small_img"],
                                "VALUE_PA_SERIAL" => $i,
                                "VALUE_STATUS_IMG" => ($row["pa_status"])?$cms_cfg['default_status_on']:$cms_cfg['default_status_off'],
                                "VALUE_STATUS_IMG_ALT" => ($row["pa_status"])?$TPLMSG['ON']:$TPLMSG['OFF'],
                                "VALUE_PA_MODIFYDATE" => $row["pa_modifydate"],
                                "VALUE_PA_MODIFYACCOUNT" => $row["pa_modifyaccount"],
            ));
        }
    }
    //應用領域表單
    function products_application_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        if($this->seo){
            $tpl->newBlock("SEO_EDIT_ZONE");
        }
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "VALUE_PA_SORT" => 1,
                                  "NOW_PA_ID"  => 0,
                                  "VALUE_PA_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_products_application","pa","","",0),
                                  "STR_PA_STATUS_CK1" => "checked",
                                  "STR_PA_STATUS_CK0" => "",
                                  "STR_PA_CUSTOM_STATUS_CK1" => "",
                                  "STR_PA_CUSTOM_STATUS_CK0" => "checked",
                                  "STR_PA_CUSTOM_STATUS_DISPLAY" => "none",
                                  "VALUE_PIC_PREVIEW1" => $cms_cfg['default_preview_pic'],
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
        //如果為修改模式,帶入資料庫資料
        if($action_mode=="mod" && !empty($_REQUEST["pa_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_products_application where pa_id='".$_REQUEST["pa_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("NOW_PA_ID"  => $row["pa_id"],
                                          "VALUE_PA_SORT"  => $row["pa_sort"],
                                          "VALUE_PA_NAME" => $row["pa_name"],
                                          "VALUE_PA_NAME_ALIAS" => $row["pa_name_alias"],
                                          "VALUE_PA_CUSTOM" => $row["pa_custom"],
                                          "VALUE_PA_SMALL_IMG" => (trim($row["pa_small_img"])=="")?"":$cms_cfg["file_root"].$row["pa_small_img"],
                                          "VALUE_PIC_PREVIEW1" => (trim($row["pa_small_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["pa_small_img"],
                                          "STR_PA_STATUS_CK1" => ($row["pa_status"])?"checked":"",
                                          "STR_PA_STATUS_CK0" => ($row["pa_status"])?"":"checked",
                                          "STR_PA_CUSTOM_STATUS_CK1" => ($row["pa_custom_status"]==1)?"checked":"",
                                          "STR_PA_CUSTOM_STATUS_CK0" => ($row["pa_custom_status"]==0)?"checked":"",
                                          "STR_PA_CUSTOM_STATUS_DISPLAY" => ($row["pa_custom_status"]==1)?" ":"none",
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
                if($this->seo){
                    $tpl->assignGlobal( array(
                        "VALUE_PA_SEO_FILENAME" => $row["pa_seo_filename"],
                        "VALUE_PA_SEO_TITLE" => $row["pa_seo_title"],
                        "VALUE_PA_SEO_KEYWORD" => $row["pa_seo_keyword"],
                        "VALUE_PA_SEO_DESCRIPTION" => $row["pa_seo_description"],
                        "VALUE_PA_SEO_H1" => $row["pa_seo_h1"],
                        "VALUE_PA_SEO_SHORT_DESC" => $row["pa_seo_short_desc"],
                        "VALUE_PA_SEO_DOWN_SHORT_DESC" => $row["pa_seo_down_short_desc"],
                    ));
                }
                $_SESSION[$cms_cfg['sess_cookie_name']]["BACK_EDIT_ZONE"]=$_SERVER["HTTP_REFERER"];
            }else{
                header("location : products.php?func=pa_list");
            }
        }
        if($cms_cfg["ws_module"]["ws_wysiwyg"]=="tinymce"){
            $tpl->newBlock("WYSIWYG_TINYMCE1");
            $tpl->assign( "VALUE_PA_CUSTOM" , $row["pa_custom"] );
        }
    }
    //應用領域儲存
    function products_application_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $main->magic_gpc($_REQUEST);
        if($this->seo){
            $seo_fields = array(
                "pa_name_alias"=>array(
                    'filter'=>array('htmlspecialchars')
                ),
                "pa_seo_filename"=>array(
                    //'filter'=>array('htmlspecialchars')
                ),
                "pa_seo_title"=>array(
                    'filter'=>array('htmlspecialchars')
                ),
                "pa_seo_keyword"=>array(
                    'filter'=>array('htmlspecialchars')              
                ),
                "pa_seo_description"=>array(
                    'filter'=>array('htmlspecialchars')  
                ),
                "pa_seo_short_desc"=>array(
                    //'filter'=>array('htmlspecialchars')   
                ),
                "pa_seo_down_short_desc"=>array(
                    //'filter'=>array('htmlspecialchars')   
                ),
                "pa_seo_h1"=>array(
                    'filter'=>array('htmlspecialchars')   
                ));
            $add_field_str='';
            $add_value_str='';
            $update_str="";                
            foreach($seo_fields as $field => $info){
                $val = trim($_REQUEST[$field]);
                if(is_array($info['filter'])){
                    foreach($info['filter'] as $func){
                        $val = $func($val);   
                    }
                }
                $add_field_str .= "".$field.",";
                $add_value_str .= "'".$val."',";
                $update_str    .= "".$field."='".$val."',";                
            }
        }
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                insert into ".$cms_cfg['tb_prefix']."_products_application(
                    pa_status,
                    pa_sort,
                    pa_name,
                    pa_custom_status,
                    pa_custom,
                    pa_small_img,
                    pa_modifydate,
                    ".$add_field_str."
                    pa_modifyaccount
                ) values (
                    '".$_REQUEST["pa_status"]."',
                    '".$_REQUEST["pa_sort"]."',
                    '".htmlspecialchars($_REQUEST["pa_name"])."',
                    '".$_REQUEST["pa_custom_status"]."',
                    '".$db->quote($main->content_file_str_replace($_REQUEST["pa_custom"]))."',
                    '".$main->file_str_replace($_REQUEST["pa_small_img"])."',
                    '".date("Y-m-d H:i:s")."',
                    ".$add_value_str."
                    '".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'
                )";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $this->pa_id=$db->get_insert_id();
                }
                break;
            case "mod":
                $sql="
                update ".$cms_cfg['tb_prefix']."_products_application set
                    pa_status='".$_REQUEST["pa_status"]."',
                    pa_sort='".$_REQUEST["pa_sort"]."',
                    pa_name='".htmlspecialchars($_REQUEST["pa_name"])."',
                    pa_custom_status='".$_REQUEST["pa_custom_status"]."',
                    pa_custom='".$db->quote($main->content_file_str_replace($_REQUEST["pa_custom"]))."',
                    pa_small_img='".$main->file_str_replace($_REQUEST["pa_small_img"])."',
                    pa_modifydate='".date("Y-m-d H:i:s")."',
                    ".$update_str."
                    pa_modifyaccount='".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'
                where pa_id='".$_REQUEST["now_pa_id"]."'";
                $rs = $db->query($sql);
                $db_msg = $db->report();
                break;
        }
        if ( $db_msg == "" ) {
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$cms_cfg["manage_url"]."products.php?func=pa_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
            $this->goto_target_page($goto_url);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }    
    //應用領域刪除
    function products_application_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["pa_id"]){
            $pa_id=(array)$_REQUEST["pa_id"];
        }else{
            $pa_id=$_REQUEST["id"];
        }
        if(!empty($pa_id)){
            $pa_id_str = implode(",",$pa_id);
            //刪除勾選的產品管理
            $sql="delete from ".$cms_cfg['tb_prefix']."_products_application where pa_id in (".$pa_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."products.php?func=pa_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }
    }    
    //應用領域核取方塊
    function application_checkbox($id,$is_cate=false){
        global $db,$cms_cfg,$tpl;
        $tpl->newBlock("PRODUCTS_APPLICATION_ZONE");
        if(!$is_cate){
            $sql = "select pa.*,pam.checked as `checked` from ".$cms_cfg['tb_prefix']."_products_application as pa left join (select * from ".$cms_cfg['tb_prefix']."_products_application_map where p_id='".$id."') as pam on pa.pa_id=pam.pa_id where pa.pa_status='1' order by pa.pa_sort ".$cms_cfg['sort_pos'];
        }else{
            $sql = "select pa.*,pam.checked as `checked` from ".$cms_cfg['tb_prefix']."_products_application as pa left join (select * from ".$cms_cfg['tb_prefix']."_products_cate_application_map where pc_id='".$id."') as pam on pa.pa_id=pam.pa_id where pa.pa_status='1' order by pa.pa_sort ".$cms_cfg['sort_pos'];
        }
        $res = $db->query($sql);
        $s=1;
        $pa_id_arr = array();
        while($row=$db->fetch_array($res,1)){
            $pa_id_arr[] = $row['pa_id'];
            $tpl->newBlock("APPLICATION_ITEM");
            $tpl->assign(array(
                "SERIAL" => $s,
                "VALUE_PA_ID" => $row['pa_id'],
                "VALUE_PA_NAME" => $row['pa_name'],
                "TAG_PA_ID_CHK" => $row['checked']?"checked":"",
            ));
            $s++;
        }
        if(count($pa_id_arr)){
            $tpl->gotoBlock("PRODUCTS_APPLICATION_ZONE");
            $tpl->assign("VALUE_PA_ID_STR",implode(',',$pa_id_arr));
        }
    }
    function write_application($id,$paids,$is_cate=false){
        global $db,$cms_cfg;
        $pa_id_arr = explode(',',$paids);
        if(!$is_cate){
            $sql = "replace into ".$cms_cfg['tb_prefix']."_products_application_map(p_id,pa_id,checked)values";
        }else{
            $sql = "replace into ".$cms_cfg['tb_prefix']."_products_cate_application_map(pc_id,pa_id,checked)values";
}
        $values = array();
        for($i=0;$i<count($pa_id_arr);$i++){
            $pa_id = $pa_id_arr[$i];
            $checked = $_POST['pa_id'][$pa_id]?1:0;
            $values[] = "('".$id."','".$pa_id."','".$checked."')";
        }
        if(count($values)){
            $sql .= implode(",",$values);
            $db->query($sql,true);
            return $db->report();
        }        
    }
    //產品認證標章列表
    function products_ca_list(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        if(trim($_SESSION[$cms_cfg['sess_cookie_name']]["BACK_EDIT_ZONE"])!=""){
            $tpl->assignGlobal("TAG_BACK_PRE_EDIT_ZONE","<a href='".$_SESSION[$cms_cfg['sess_cookie_name']]["BACK_EDIT_ZONE"]."'><font color='blue'>回到上次分類列表</font></a>");
            unset($_SESSION[$cms_cfg['sess_cookie_name']]["BACK_EDIT_ZONE"]);
        }
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        $sql="select * from ".$cms_cfg['tb_prefix']."_products_ca ";
        $and_str = "";
        if(!empty($_REQUEST["sk"])){
            $and_str .= " where  ca_name like '%".$_REQUEST["sk"]."%'";
        }
        $sql .= $and_str." order by ca_sort ".$cms_cfg['sort_pos'].",ca_modifydate desc ";
        //取得總筆數
        $total_records=$main->count_total_records($sql);
        //取得分頁連結
        $func_str="products.php?func=ca_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"];
        //分頁且重新組合包含limit的sql語法
        $sql=$main->pagination($this->op_limit,$this->jp_limit,$_REQUEST["nowp"],$_REQUEST["jp"],$func_str,$total_records,$sql);
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        $tpl->assignGlobal( array("VALUE_SEARCH_KEYWORD" => $_REQUEST["sk"],
                                  "VALUE_TOTAL_BOX" => $rsnum,
                                  "TAG_DELETE_CHECK_STR" => $TPLMSG['DELETE_CHECK_STR'],
                                  "VALUE_NOW_PAGE" => $_REQUEST['nowp']
        ));
        //認證標章列表
        $i=$page["start_serial"];
        while ( $row = $db->fetch_array($selectrs,1) ) {
            $i++;
            $tpl->newBlock( "PRODUCTS_CA_LIST" );
            if($i%2){
                $tpl->assign("TAG_TR_CLASS","class='altrow'");
            }
            $ca_img = $row['ca_img']?$cms_cfg['file_root'].$row['ca_img']:$cms_cfg['default_preview_pic'];
            $tpl->assign( array("VALUE_CA_ID"  => $row["ca_id"],
                                "VALUE_CA_SORT"  => $row["ca_sort"],
                                "VALUE_CA_NAME" => $row["ca_name"],
                                "VALUE_CA_IMG" => $ca_img,
                                "VALUE_CA_MODIFYDATE" => $row["ca_modifydate"],
                                "VALUE_CA_SERIAL" => $i,
            ));
        }
    }
    //認證標章表單
    function products_ca_form($action_mode){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //欄位名稱
        $tpl->assignGlobal( array("MSG_MODE" => $TPLMSG['ADD'],
                                  "VALUE_CA_SORT" => 1,
                                  "NOW_CA_ID"  => 0,
                                  "VALUE_CA_SORT"  => $main->get_max_sort_value($cms_cfg['tb_prefix']."_products_ca","ca","","",0),
                                  "VALUE_PIC_PREVIEW1" => $cms_cfg['default_preview_pic'],
                                  "STR_CA_STATUS_CK1" => "checked",
                                  "STR_CA_STATUS_CK0" => "",
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
        //如果為修改模式,帶入資料庫資料
        if($action_mode=="mod" && !empty($_REQUEST["ca_id"])){
            $sql="select * from ".$cms_cfg['tb_prefix']."_products_ca where ca_id='".$_REQUEST["ca_id"]."'";
            $selectrs = $db->query($sql);
            $row = $db->fetch_array($selectrs,1);
            $rsnum    = $db->numRows($selectrs);
            if ($rsnum > 0) {
                $tpl->assignGlobal( array("NOW_CA_ID"  => $row["ca_id"],
                                          "VALUE_CA_SORT"  => $row["ca_sort"],
                                          "VALUE_CA_NAME" => $row["ca_name"],
                                          "VALUE_CA_IMG" => (trim($row["ca_img"])=="")?"":$cms_cfg["file_root"].$row["ca_img"],
                                          "VALUE_PIC_PREVIEW1" => (trim($row["ca_img"])=="")?$cms_cfg['default_preview_pic']:$cms_cfg["file_root"].$row["ca_img"],
                                          "MSG_MODE" => $TPLMSG['MODIFY']
                ));
                $_SESSION[$cms_cfg['sess_cookie_name']]["BACK_EDIT_ZONE"]=$_SERVER["HTTP_REFERER"];
            }else{
                header("location : products.php?func=ca_list");
            }
        }
    }        
    //認證標章儲存
    function products_ca_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $main->magic_gpc($_REQUEST);
        switch ($_REQUEST["action_mode"]){
            case "add":
                $sql="
                insert into ".$cms_cfg['tb_prefix']."_products_ca(
                    ca_sort,
                    ca_name,
                    ca_img,
                    ca_modifydate
                ) values (
                    '".$_REQUEST["ca_sort"]."',
                    '".htmlspecialchars($_REQUEST["ca_name"])."',
                    '".$main->file_str_replace($_REQUEST["ca_img"])."',
                    '".date("Y-m-d H:i:s")."'
                )";
                $rs = $db->query($sql,true);
                $db_msg = $db->report();
                if ( $db_msg == "" ) {
                    $this->ca_id=$db->get_insert_id();
                }
                break;
            case "mod":
                $sql="
                update ".$cms_cfg['tb_prefix']."_products_ca set
                    ca_sort='".$_REQUEST["ca_sort"]."',
                    ca_name='".htmlspecialchars($_REQUEST["ca_name"])."',
                    ca_img='".$main->file_str_replace($_REQUEST["ca_img"])."',
                    ca_modifydate='".date("Y-m-d H:i:s")."'
                where ca_id='".$_REQUEST["now_ca_id"]."'";
                $rs = $db->query($sql,true);
                $db_msg = $db->report();
                break;
        }
        if ( $db_msg == "" ) {
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$cms_cfg["manage_url"]."products.php?func=ca_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
            $this->goto_target_page($goto_url);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
    function products_ca_del(){
        global $db,$tpl,$cms_cfg,$TPLMSG;
        if($_REQUEST["ca_id"]){
            $ca_id=array(0=>$_REQUEST["ca_id"]);
        }else{
            $ca_id=$_REQUEST["id"];
        }
        if(!empty($ca_id)){
            $ca_id_str = implode(",",$ca_id);
            //刪除勾選的產品管理
            $sql="delete from ".$cms_cfg['tb_prefix']."_products_ca where ca_id in (".$ca_id_str.")";
            $rs = $db->query($sql);
            $db_msg = $db->report();
            if ( $db_msg == "" ) {
                $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
                $goto_url=$cms_cfg["manage_url"]."products.php?func=ca_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
                $this->goto_target_page($goto_url);
            }else{
                $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
            }
        }        
    }    
    function ca_checkbox($ca_str){
        global $db,$tpl,$cms_cfg;
        $tpl->newBlock("CA_ROW");
        $ca_arr = explode(',',$ca_str);
        $sql = "select * from ".$cms_cfg['tb_prefix']."_products_ca order by ca_sort ".$cms_cfg['sort_pos'];
        $res = $db->query($sql,true);
        while($row = $db->fetch_array($res,1)){
            $tpl->newBlock("CA_CHECKBOX_LIST");
            $ca_img = ($row['ca_img'])?$cms_cfg['file_root'].$row['ca_img']:$cms_cfg['default_preview_pic'];
            $tpl->assign(array(
                "VALUE_CA_ID"   => $row['ca_id'],
                "VALUE_CA_NAME" => $row['ca_name'],
                "VALUE_CA_IMG" => $ca_img,
                "TAG_CHECKED"    => (in_array($row['ca_id'],$ca_arr))?"checked":"", 
            ));
        }
    }
	
    //產品管理分類--表單
    function products_clone_form(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        //欄位名稱
        $tpl->assignGlobal( array("VALUE_CLONE_ID" => $_REQUEST["p_id"],
								  "MSG_MODE" => $TPLMSG['ADD'],

        ));
        //相關參數
        if(!empty($_REQUEST['nowp'])){
            $tpl->assignGlobal( array("VALUE_SEARCH_TARGET" => $_REQUEST['st'],
                                      "VALUE_SEARCH_KEYWORD" => $_REQUEST['sk'],
                                      "VALUE_NOW_PAGE" => $_REQUEST['nowp'],
                                      "VALUE_JUMP_PAGE" => $_REQUEST['jp'],

            ));
        }
        //載入分類資料,選擇分類
        $this->products_cate_select($this->products_cate_select_option, $row["pc_id"],$pc_parent, $parent=0, $indent="");
        $tpl->assignGlobal("TAG_SELECT_PRODUCTS_CATE" ,$this->products_cate_select_option);
    }

    //clone儲存
    function clone_replace(){
        global $db,$tpl,$cms_cfg,$TPLMSG,$main;
        $main->magic_gpc($_REQUEST);
                $sql="
                insert into ".$cms_cfg['tb_prefix']."_products(
                    p_clone,
					pc_id,
                    p_modifydate,
					p_modifyaccount
                ) values (
                    '".$_REQUEST["p_clone"]."',
					'".$_REQUEST["pc_id"]."',
                    '".date("Y-m-d H:i:s")."',
					'".$_SESSION[$cms_cfg['sess_cookie_name']]["USER_ACCOUNT"]."'
                )";
                $rs = $db->query($sql,true);
                $db_msg = $db->report();

        if ( $db_msg == "" ) {
            $tpl->assignGlobal( "MSG_ACTION_TERM" , $TPLMSG["ACTION_TERM"]);
            $goto_url=$cms_cfg["manage_url"]."products.php?func=p_list&st=".$_REQUEST["st"]."&sk=".$_REQUEST["sk"]."&nowp=".$_REQUEST["nowp"]."&jp=".$_REQUEST["jp"];
            $this->goto_target_page($goto_url);
        }else{
            $tpl->assignGlobal( "MSG_ACTION_TERM" , "DB Error: $db_msg, please contact MIS");
        }
    }
	
}
//ob_end_flush();
?>