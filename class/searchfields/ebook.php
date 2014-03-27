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
class searchFields_ebook extends searchFields_abstract{
    protected $search_fields = array(
        'eb_name' => array("title"=>"名稱","fields"=>array("eb_name")),
        'eb_modifydate' => array("title"=>"修改月份","fields"=>array("eb_modifydate")),
    ); 
}
?>
