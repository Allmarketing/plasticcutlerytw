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
class searchFields_member extends searchFields_abstract{
    protected $search_fields = array(
        'm_country' => array("title"=>"國家","fields"=>array("m_country")),
        'm_company_name' => array("title"=>"公司","fields"=>array("m_company_name")),
        'm_name' => array("title"=>"姓名","fields"=>array("m_fname","m_lname")),
        'm_email' => array("title"=>"電子郵件","fields"=>array("m_email")),
        'm_modifydate' => array("title"=>"編輯月份","fields"=>array("m_modifydate"),"compare"=>"between_date"),
    ); 
}
?>
