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
class searchFields_ebookCate extends searchFields_abstract{
    protected $search_fields = array(
        'ebc_name' => array("title"=>"分類名稱","fields"=>array("ebc_name")),
        'ebc_modifydate' => array("title"=>"編輯月份","fields"=>array("ebc_modifydate"),"compare"=>"between_date"),
    ); 
}
?>
