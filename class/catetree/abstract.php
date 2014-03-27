<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cate_tree
 *
 * @author chunhsin
 */
class catetree_abstract {
    //put your code here
    protected $_db;
    protected $_cfg;
    protected $_cate_table = "";
    protected $_catename_column = "subject";
    protected $_parent_column = "parent";
    protected $_prefix;
    protected $_id_tree = array();
    protected $_tree_structure = "";
    protected $_templates = "templates/ws-manage-fn-cate-tree-tpl.html";
    protected $_cate_link_str = "";
    protected $_build = false;
    protected $_varname = "";
    
    public function __construct($options){
        $this->setOptions($options);
        $this->_id_tree = $this->get_sub_id();
    }

    public function setOptions(array $options){
        foreach($options as $k => $v){
            $var = "_".$k;
            $this->$var = $v;
        }
    }
    public function get_sub_id($parent=0){
        $sql = "select ".$this->_formated_column("id").",".$this->_formated_catename()." from ".$this->_cate_table. " where ".$this->_formated_column("status")."='1' and ".$this->_formated_parent(). "='".$parent."' order by ".$this->_formated_column("sort");
        $res = $this->_db->query($sql,true);
        if($this->_db->numRows($res)){
            $id_arr = array();
            while($cate_row = $this->_db->fetch_array($res,1)){
                $tmp = array();
                $tmp['id'] = $cate_row[$this->_formated_column("id")];
                $tmp['name'] = $cate_row[$this->_formated_catename()];
                if($_GET[$this->_varname]==$tmp['id']){
                    $tmp['active'] = 1;
                }
                if($sub = $this->get_sub_id($tmp['id'])){
                    $tmp['sub'] = $sub;
                    if($sub['open'])$tmp['active'] = 1;
                }
                if($tmp['active']){
                    $id_arr['open'] = 1;
                }
                $id_arr[] = $tmp;
            }
        }
        return $id_arr; 
    }
    protected function _formated_parent(){
        return $this->_formated_column($this->_parent_column);
    }
    protected function _formated_catename(){
        return $this->_formated_column($this->_catename_column);
    }
    protected function _formated_column($column){
        return $this->_prefix . $column;
    }
    protected function _build_tree(){
        if(!$this->_build){
            $tpl = new TemplatePower($this->_templates);
            $tpl->prepare();
            $tpl->newBlock("MAIN_CATE");
            $tpl->assign("VALUE_CATE_TREE",$this->_build_sub_tree($this->_id_tree));
            $this->_tree_structure = $tpl->getOutputContent();
            $this->_build = true;
        }
    }
    protected function _build_sub_tree($id_tree){
        $tpl = new TemplatePower($this->_templates);
        $tpl->prepare();
        $tpl->newBlock("SUB_CATE");
        if($id_tree){
            foreach($id_tree as $k => $item){
                if(is_numeric($k)){
                    $tpl->newBlock("SUB_CATE_LIST");
                    $tpl->assign(array(
                        "VALUE_CATE_ID"             => $item['id'],
                        "VALUE_CATE_TREE_LINK_NAME" => $item['name'],
                        "VALUE_CATE_TREE_LINK"      => $this->_get_cate_link($item['id']),
                        "OPEN_CLASS"                => ($item['active'] || $item['sub']['open'])?"open":"",
                        "ACTIVE_CLASS"              => ($item['active'] && !$item['sub']['open'])?"active":"text",
                    ));
                    if($item['sub']){
                        $tpl->assign("VALUE_SUB_CATE_TREE",$this->_build_sub_tree($item['sub']));
                    }
                }
            }
            return $tpl->getOutputContent();
        }
    }
    protected function _get_cate_link($id){
        return $this->_cate_link_str."&".$this->_varname."=".$id;
    }
    public function get_tree(){
        $this->_build_tree();
        return $this->_tree_structure;
    }
    public function print_tree(){
        $this->_build_tree();
        echo  $this->_tree_structure;
    }
}

?>
