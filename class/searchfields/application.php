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
class searchFields_application extends searchFields_abstract{
    protected $search_fields = array(
        'pa_name' => array("title"=>"名稱","fields"=>array("pa_name")),
        'pa_desc' => array("title"=>"敘述","fields"=>array("pa_seo_short_desc","pa_seo_description","pa_seo_down_short_desc")),
        'pa_modifydate' => array("title"=>"編輯月份","fields"=>array("pa_modifydate"),"compare"=>"between_date"),
    ); 
}
?>
