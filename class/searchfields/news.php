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
class searchFields_news extends searchFields_abstract{
    protected $search_fields = array(
        'n_subject' => array("title"=>"標題","fields"=>array("n_subject")),
        'n_content' => array("title"=>"內容","fields"=>array("n_content")),
        'n_modifydate' => array("title"=>"編輯月份","fields"=>array("n_modifydate"),"compare"=>"between_date"),
    ); 
}
?>
