<?php
abstract class Dbtable_Abstract {
    //可修改的欄位
    protected $prefix;
    protected $table;
    protected $db;
    protected $pk;
    protected $post_cols = array(); 
    protected $values = array();
    protected $con = array();
    protected $query_resource;
    protected $order_status = array();
    
    public function __construct(DB $db,$prefix) {
        $this->db = $db;
        $this->prefix = $prefix;
    }
    
    public function __get($name) {
        return $this->values[$name];
    }
    
    public function writeData($post){
        $this->_retrieve_cols($post); 
        if(!empty($this->values[$this->pk])){
            $sql = $this->_mk_update_sql();
        }else{
            $sql = $this->_mk_insert_sql();
        }
        $this->query_resource = $this->db->query($sql,true);
    }
    
    public function getData($pk,$cols="*"){
        if(is_array($cols)){
            $cols = implode(',',$cols);
        }
        $con = sprintf("`%s`='%s'",$this->pk,$pk);
        $sql = $this->_mk_select_sql($con, $cols);
        $this->query_resource = $this->db->query($sql,true);
        $row = $this->db->fetch_array($this->query_resource, 1);
        $this->values = $row;
        return $this;
    }
    
    public function getDataRow(){
        return $this->values;    
    }
    
    public function report(){
        return $this->db->report();
    }
    //取得post資料欄位
    protected function _retrieve_cols($post){
        foreach($post as $k=>$v){
            if(get_magic_quotes_gpc()){
                $v = stripslashes($v);
                $post[$k] = $v;
            }
            if(isset($this->post_cols[$k])){
                if(is_array($v)){
                    $this->values[$k] = implode(',',$v);
                }else{
                    $v = trim($v);
                    if(preg_match("/(seo_title|seo_keyword|seo_description|seo_filename|seo_h1)$/i", $k)){
                        $v = htmlspecialchars($v);
                    }
                    $this->values[$k] = $this->db->quote($v);
                }
            }
        }
        $keys = array_keys($this->post_cols);
        $md = preg_grep('/modifydate$/i',$keys);
        if(!empty($md)){
            foreach($md as $v){
                $this->values[$v] = date("Y-m-d H:i:s");
            }
        }
    }
    //製作新增sql
    protected function _mk_insert_sql(){
        $sql_tpl = "insert into ".$this->tablename()."(%s)values(%s)";
        foreach($this->values as $k=>$v){
            $columns[]=sprintf("`%s`",$k);
            $values[]=sprintf("'%s'",$v);
        }
        return sprintf($sql_tpl,implode(',',$columns),implode(',',$values));
        
    }
    
    //製作新增sql
    protected function _mk_update_sql(){
        $sql_tpl = "update ".$this->tablename()." set %s where %s";
        foreach($this->values as $k=>$v){
            if($k!=$this->pk){
                $updates[] = sprintf("`%s`='%s'",$k,$v);
            }
        }
        $this->con[] = sprintf("`%s`='%s'",$this->pk,$this->values[$this->pk]);
        return sprintf($sql_tpl,implode(',',$updates),implode(' and ',$this->con));
    } 
    
    protected function _mk_select_sql($con,$col="*",$order=null,$limit=null){
        $sql = "select ".$col." from ".$this->tablename()." where ".$con;
        if($order)$sql.=" order by ".$order;
        if($limit)$sql.=" ".$limit;
        return $sql;
    }
    
    protected function tablename(){
        return $this->prefix."_".$this->table;
    }
    
    public function getDataList($con,$order=null,$limit=null){
        $sql = $this->_mk_select_sql($con, $col, $order, $limit);
        $this->query_resource = $db->query($sql);
        $this->values = array();
        if($this->db->numRows($this->query_resource)){
            while($row = $this->db->fetch_array($this->query_resource, 1)){
                $this->values[] = $row;
            }
        }
        if(!empty($this->values)){
            return $this->values;
        }
    }
}

?>
