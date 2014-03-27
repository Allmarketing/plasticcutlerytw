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
class searchFields_products extends searchFields_abstract{
    protected $search_fields = array(
        'p_name' => array("title"=>"產品名稱","fields"=>array("p_name")),
        'p_desc' => array("title"=>"產品敘述","fields"=>array("p_desc")),
        'p_spec' => array("title"=>"分類規格","fields"=>array("p_spec")),
        'p_character' => array("title"=>"分類特性","fields"=>array("p_character")),
        'p_modifydate' => array("title"=>"編輯月份","fields"=>array("p_modifydate"),'compare'=>"between_date"),
    ); 
}
?>
