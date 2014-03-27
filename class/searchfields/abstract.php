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
class searchFields_abstract {
    /*
     * $search_fields 
     * 型態：陣列
     * 說明：作用的關聯索引及其值用意說明(型態)
     *      title：搜尋標題(字串)
     *      fields：該搜尋標題要比對的資料庫欄位(陣列)
     *      compare：搜尋值與資料庫欄位比對的方式，是物件內的方法名稱(字串)
     */
    protected $search_fields = array(); 
    protected $field_template = "./templates/ws-manage-fn-search-field-tpl.html";
    protected $default_compare = "default_compare";
    
    function list_search_fields($st,$sk){
        $tpl = new TemplatePower($this->field_template);
        $tpl->prepare();
        if(is_array($this->search_fields) && !empty($this->search_fields)){
            $tpl->newBlock("SEARCH_FIELD_OPTION");
            $tpl->assign(array(
               "TAG_FIELD_TITLE"   => "全部", 
               "TAG_FIELD_VALUE"   => "all", 
            ));            
            foreach($this->search_fields as $key => $field){
                $tpl->newBlock("SEARCH_FIELD_OPTION");
                $tpl->assign(array(
                   "TAG_FIELD_TITLE"   => $field['title'], 
                   "TAG_FIELD_VALUE"   => $key, 
                   "TAG_FIELD_SELECTED" => (!empty($st) && $key==$st)?"selected":"", 
                ));
                $tpl->assignGlobal(array("VALUE_SEARCH_KEYWORD" => trim($sk), ));
            }
        }
        return $tpl->getOutputContent();
    }
    
    function find_search_value_sql($and_str,$st,$sk){
        $sk = trim($sk);
        if($sk){
            if($st=="all"){
                $tmp = "";
                foreach($this->search_fields as $key=>$field){
                    if($key!="all"){
                        if(is_array($field['fields'])){
                            if($field['compare']){
                                $callback = array($this,$field['compare']);
                            }else{
                                $callback = array($this,$this->default_compare);
                            }
                            $tmp .= (($tmp)?" or ":""). call_user_func($callback,$field['fields'], $sk);
                        }
                    }
                }            
            }else{
                if(is_array($this->search_fields[$st]['fields'])){
                    if($this->search_fields[$st]['compare']){
                        $callback = array($this,$this->search_fields[$st]['compare']);
                    }else{
                        $callback = array($this,$this->default_compare);
                    }
                    $tmp = call_user_func($callback,$this->search_fields[$st]['fields'],$sk);
                }            
            }    
        }
        if($tmp){
            $and_str .=  ((trim($and_str))?" and ":"")." (".$tmp.")";
        }
        return $and_str;
    }    
    function default_compare($fields,$val){
        global $db;
        $tmp = "";
        foreach($fields as $f){
            $tmp .= (($tmp)?" or ":"").$f." like '%".$db->quote($val)."%'";
        }
        $tmp = "(".$tmp.")";
        return $tmp;
    }
    function between_date($fields,$val){
        $dateinfo = getdate(strtotime($val));
        $field = $fields[0]; //cu_modifydate
        $ts1 = mktime(0,0,0,$dateinfo['mon'],$dateinfo['mday'],$dateinfo['year']);
        $ts2 = mktime(0,0,0,$dateinfo['mon']+1,$dateinfo['mday'],$dateinfo['year']);      
        $tmp = "(".$field . " between '".date("Y-m",$ts1)."' and '".date("Y-m",$ts2)."') ";
        return $tmp;
    }
}
?>
