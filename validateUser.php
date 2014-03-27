<?php
include_once("conf/config.inc.php");
include_once("libs/libs-mysql.php");
$db = new DB($cms_cfg['db_host'],$cms_cfg['db_user'],$cms_cfg['db_password'],$cms_cfg['db_name']);
/* RECEIVE VALUE */
$validateValue=$_POST['validateValue'];
$validateId=$_POST['validateId'];
$validateError=$_POST['validateError'];
//$validateError2=$_POST['validateError2'];
/* RETURN VALUE */
$arrayToJs = array();
$arrayToJs[0] = $validateId;
$arrayToJs[1] = $validateError;
//非windows版本檢查mx
$ck_dns=1;
if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN'){
    $domain=explode("@",$validateValue);
    $ck_dns=checkdnsrr($domain[1], 'MX');
}
if($ck_dns){
    $sql="select count(*) as total from ".$cms_cfg['tb_prefix']."_member where m_account='".$validateValue."'";
    $selectrs = $db->query($sql);
    $row = $db->fetch_array($selectrs,1);
}else{
    $row["total"]=1;
}
if($row["total"]){		// validate??
    $arrayToJs[2] = "false";
	echo '{"jsonValidateReturn":'.json_encode($arrayToJs).'}';		// RETURN ARRAY WITH ERROR
}else{
	$arrayToJs[2] = "true";			// RETURN TRUE
	echo '{"jsonValidateReturn":'.json_encode($arrayToJs).'}';			// RETURN ARRAY WITH success
}
?>