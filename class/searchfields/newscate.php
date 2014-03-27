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
class searchFields_newsCate extends searchFields_abstract{
    protected $search_fields = array(
        'nc_subject' => array("title"=>"分類名稱","fields"=>array("nc_subject")),
        'nc_desc' => array("title"=>"敘述內容","fields"=>array("nc_seo_description","nc_seo_short_desc")),
        'nc_modifydate' => array("title"=>"編輯月份","fields"=>array("nc_modifydate"),"compare"=>"between_date"),
    ); 
}
?>
