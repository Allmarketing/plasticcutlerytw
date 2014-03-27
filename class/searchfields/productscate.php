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
class searchFields_productsCate extends searchFields_abstract{
    protected $search_fields = array(
        'pc_name' => array("title"=>"分類名稱","fields"=>array("pc_name")),
        'pc_desc' => array("title"=>"敘述內容","fields"=>array("pc_seo_short_desc","pc_seo_description","pc_seo_down_short_desc")),
    ); 
}
?>
