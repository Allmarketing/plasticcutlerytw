<?php
include_once("../conf/config.inc.php");
include_once("../libs/libs-mysql.php");
$db = new DB($cms_cfg['db_host'],$cms_cfg['db_user'],$cms_cfg['db_password'],$cms_cfg['db_name']);
/* RECEIVE VALUE */
$validateValue=$_POST['validateValue'];
$validateId=$_POST['validateId'];
$validateError=$_POST['validateError'];
/* RETURN VALUE */
$arrayToJs = array();
$arrayToJs[0] = $validateId;
$arrayToJs[1] = $validateError;
switch($_REQUEST["func"]){
    case "pa":
        $sql="select count(*) as total from ".$cms_cfg['tb_prefix']."_products_application where pa_seo_filename='".$validateValue."' and pa_id!='".$validateId."'";
        break;
    case "pc":
        $sql="select count(*) as total from ".$cms_cfg['tb_prefix']."_products_cate where pc_seo_filename='".$validateValue."' and pc_id!='".$validateId."'";
        break;
    case "p":
        $sql="select count(*) as total from ".$cms_cfg['tb_prefix']."_products where p_seo_filename='".$validateValue."' and p_id!='".$validateId."' ";
        break;
    case "dc":
        $sql="select count(*) as total from ".$cms_cfg['tb_prefix']."_download_cate where dc_seo_filename='".$validateValue."' and dc_id!='".$validateId."' ";
        break;
    case "nc":
        $sql="select count(*) as total from ".$cms_cfg['tb_prefix']."_news_cate where nc_seo_filename='".$validateValue."' and nc_id!='".$validateId."' ";
        break;
    case "n":
        $sql="select count(*) as total from ".$cms_cfg['tb_prefix']."_news where n_seo_filename='".$validateValue."' and n_id!='".$validateId."' ";
        break;
    case "au":
        $sql="select count(*) as total from ".$cms_cfg['tb_prefix']."_aboutus where au_seo_filename='".$validateValue."' and au_id!='".$validateId."' ";
        break;
    case "v":
        $sql="select count(*) as total from ".$cms_cfg['tb_prefix']."_video where v_seo_filename='".$validateValue."' and v_id!='".$validateId."' ";
        break;    
    case "vc":
        $sql="select count(*) as total from ".$cms_cfg['tb_prefix']."_video_cate where vc_seo_filename='".$validateValue."' and vc_id!='".$validateId."' ";
        break;    
    case "fc":
        $sql="select count(*) as total from ".$cms_cfg['tb_prefix']."_faq_cate where fc_seo_filename='".$validateValue."' and fc_id!='".$validateId."' ";
        break;
    case "gc":
        $sql="select count(*) as total from ".$cms_cfg['tb_prefix']."_gallery_cate where gc_seo_filename='".$validateValue."' and gc_id!='".$validateId."' ";
        break;
    case "g":
        $sql="select count(*) as total from ".$cms_cfg['tb_prefix']."_gallery where g_seo_filename='".$validateValue."' and g_id!='".$validateId."' ";
        break;
    case "lc":
        $sql="select count(*) as total from ".$cms_cfg['tb_prefix']."_goodlink_cate where lc_seo_filename='".$validateValue."' and lc_id!='".$validateId."' ";
        break;
    case "sdc":
        $sql="select count(*) as total from ".$cms_cfg['tb_prefix']."_stores_cate where sdc_seo_filename='".$validateValue."' and sdc_id!='".$validateId."' ";
        break;
}
$selectrs = $db->query($sql);
$row = $db->fetch_array($selectrs,1);
if($row["total"]){		// validate??
    $arrayToJs[2] = "false";
	echo '{"jsonValidateReturn":'.json_encode($arrayToJs).'}';		// RETURN ARRAY WITH ERROR
}else{
	$arrayToJs[2] = "true";			// RETURN TRUE
	echo '{"jsonValidateReturn":'.json_encode($arrayToJs).'}';			// RETURN ARRAY WITH success
}
?>