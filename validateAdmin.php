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
$sql="select count(*) as total from ".$cms_cfg['tb_prefix']."_admin_info where ai_account='".$validateValue."'";
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