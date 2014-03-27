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
class searchFields_inquiry extends searchFields_abstract{
    protected $search_fields = array(
        'i_id' => array("title"=>"編號","fields"=>array("i_id")),
        'i_country' => array("title"=>"國家","fields"=>array("i_country")),
        'i_company_name' => array("title"=>"公司","fields"=>array("i_company_name")),
        'i_name' => array("title"=>"姓名","fields"=>array("i_name")),
        'i_createdate' => array("title"=>"詢問月份","fields"=>array("i_createdate"),"compare"=>"between_date"),
    ); 
}
?>
