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
class searchFields_epaper extends searchFields_abstract{
    protected $search_fields = array(
        'e_subject' => array("title"=>"標題","fields"=>array("e_subject")),
        'e_content' => array("title"=>"內容","fields"=>array("e_content")),
        'e_modifydate' => array("title"=>"修改月份","fields"=>array("e_modifydate")),
    ); 
}
?>
