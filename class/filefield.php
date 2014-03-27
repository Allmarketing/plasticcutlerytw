<?php
class filefield {
    //put your code here
    static protected $_fields = array(
        "TAG_FORM_NAME"         => "myform",
        "TAG_FILE_FIELD_NAME"    => "",
        "TAG_FILE_FIELD_ID"      => "",
        "TAG_FILE_FIELD_VALUE"   => "",
    );
    static protected $_templates = "./templates/ws-manage-fn-select-file-tpl.html";
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
