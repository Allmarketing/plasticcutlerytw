<?php
/*
 * 參數：長,寬,高
 * 參數說明：單位為公分
 */
class cuftcbm {
    //put your code here
    const TO_CUFT = 0.0000353;
    const CBM_CUFT = 35.315;
    protected $_l;
    protected $_w;
    protected $_h;
    protected $_cuft;
    protected $_cbm;
    
    public function __construct($l,$w,$h){
        $this->_l = floatval($l);
        $this->_w = floatval($w);
        $this->_h = floatval($h);
        $this->_calculate();
    }
    
    public function  getcuft(){
        return $this->_cuft;
    }
    
    public function  getcbm(){
        return $this->_cbm;
    }    
    
    protected function _calculate(){
        $this->_cuft = ($this->_l * $this->_w * $this->_h) * self::TO_CUFT;
        $this->_cbm = $this->_cuft/self::CBM_CUFT;        
    }
}

?>
