<?php
class autoloader {
    //put your code here
    protected $base_path;
    protected $ext = array("php");
    public function __construct(){
         $this->base_path = dirname(__FILE__) . DIRECTORY_SEPARATOR;
    }
    
    public function load($class_name){
        $class_name = strtolower($class_name);
        $class_path = str_replace("_", DIRECTORY_SEPARATOR, $class_name);
        foreach($this->ext as $ext){
            $class_files = $this->base_path . $class_path . "." .$ext;
            if(file_exists($class_files)){
                require_once $class_files;
                break;
            }
        }
    }
}

?>
