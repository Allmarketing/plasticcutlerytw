<?php
class opbutton_abstract {
    protected $template = "templates/ws-manage-fn-operation-button-tpl.html";
    protected $switch = "11111111";
    protected $pattern = array("10000000","01000000","00100000","00010000","00001000","00000100","00000010","00000001");
    protected $add_link = "";
    protected $tpl;
    public function __construct(){
        $tpl = new TemplatePower($this->template);
        $tpl->prepare();
        $this->tpl = $tpl;
        $this->_run_switch();
    }
    protected function _run_switch(){
        foreach($this->pattern as $pattern){
            if(($this->switch & $pattern) == $pattern){
                $this->tpl->newBlock($pattern);
                if($pattern=="10000000"){
                    $this->tpl->assign("ADD_LINK",$this->add_link);
                }
            }
        }
    }
    public function get_result(){
        return $this->tpl->getOutputContent();
    }
}
?>
