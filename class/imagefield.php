<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of abstract
 *
 * @author chunhsin
 */
class imagefield {
    //put your code here
    static protected $_fields = array(
        "TAG_FORM_NAME"         => "myform",
        "TAG_IMG_TITLE"         => "",
        "TAG_IMG_FIELD_NAME"    => "",
        "TAG_IMG_FIELD_ID"      => "",
        "TAG_IMG_FIELD_VALUE"   => "",
        "TAG_PREVIEW_IMG_ID"    => "",
        "TAG_PREVIEW_IMG_VALUE" => "",
    );
    static protected $_templates = "./templates/ws-manage-fn-image-field-tpl.html";
    static protected $_tpl = null;
    
    static function setValues(array $val){
        foreach($val as $k => $v){
            if(isset(self::$_fields[$k])){
                self::$_fields[$k] = $v;
            }
        }
    }
    
    static function get_html(){
        foreach(self::$_fields as $fieldName => $fieldValue){
            self::getTpl()->assignGlobal($fieldName,$fieldValue);
        }
        return self::getTpl()->getOutputContent();
    }
    
    static public function getTpl(){
        if(!self::$_tpl){
            $tpl = new TemplatePower(self::$_templates);
            $tpl->prepare();
            self::$_tpl = $tpl;
        }
        return self::$_tpl;
    }
}
?>
