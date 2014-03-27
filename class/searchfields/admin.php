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
class searchFields_admin extends searchFields_abstract{
    protected $search_fields = array(
        'ai_account' => array("title"=>"帳號","fields"=>array("ai_account")),
        'ai_name' => array("title"=>"姓名","fields"=>array("ai_name")),
    ); 
}
?>
