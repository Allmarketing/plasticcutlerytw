<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of resizeimg
 *
 * @author chunhsin
 */
require_once "./class/wideimage/WideImage.php";

define('BASE_PATH',dirname(__FILE__));
define("DS", DIRECTORY_SEPARATOR);
define("BASE_IMG_PATH",BASE_PATH . DS . "upload_files" . DS);

new resizeimg($_GET['src'],$_GET['w'],$_GET['h']);

class resizeimg {
    //put your code here
    protected $_basePath = BASE_PATH;
    protected $_imgBasePath = BASE_IMG_PATH; 
    
    function __construct($src="",$w="",$h=""){
        if(!empty($src) && file_exists($this->_imgBasePath . $src)){
            $base = WideImage::load($this->_imgBasePath . $src);
            $resize = $base->resize($w,$h);
            $resize->output("jpg");
        }  
    }
}

?>
