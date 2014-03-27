<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of contactus
 *
 * @author chunhsin
 */
class searchFields_contactus extends searchFields_abstract{
    protected $search_fields = array(
        'cu_cate'         => array("title"=>"分類","fields"=>array("cu_cate"),'compare'=>'compare_cu_cate'),
        'cu_country'      => array("title"=>"國家","fields"=>array("cu_country")),
        'cu_company_name' => array("title"=>"公司","fields"=>array("cu_company_name")),
        'cu_name'         => array("title"=>"姓名","fields"=>array("cu_fname","cu_lname")),
        'cu_time'         => array("title"=>"時間","fields"=>array("cu_modifydate"),'compare'=>"between_date"),
    ); 
    function compare_cu_cate($fields,$val){
        global $ws_array;//$ws_array["contactus_cate"]
        //取得分類的id
        foreach($ws_array["contactus_cate"] as $k=>$v){
            if($val == $v){
                $cu_cate = $k;
                break;
            }
        }
        $cu_cate = isset($cu_cate)?$cu_cate:99999;
        return $fields[0]."='".$cu_cate."'";
    }
}
?>
