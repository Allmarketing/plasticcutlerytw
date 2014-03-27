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
class searchFields_aboutus extends searchFields_abstract{
    protected $search_fields = array(
        'au_subject' => array("title"=>"標題","fields"=>array("au_subject")),
        'au_content' => array("title"=>"內容","fields"=>array("au_content")),
//        'au_modifydate' => array("title"=>"時間","fields"=>array("au_modifydate"),"compare"=>"between_date"),
    ); 
}
?>
