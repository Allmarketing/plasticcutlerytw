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
class searchFields_ad extends searchFields_abstract{
    protected $search_fields = array(
        'ad_subject' => array("title"=>"標題","fields"=>array("ad_subject")),
        'ad_modifydate' => array("title"=>"編輯月份","fields"=>array("ad_modifydate"),"compare"=>"between_date"),
    ); 
}
?>
