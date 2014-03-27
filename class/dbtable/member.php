<?php
class Dbtable_Member extends Dbtable_Abstract{
    //可修改的欄位
    protected $table = "member";
    protected $pk = "m_id";
    protected $post_cols = array(
        'mc_id'            => array(),
        'm_id'             => array(),
        'm_status'         => array(),
        'm_sort'           => array(),
        'm_account'        => array(),
        'm_name'           => array(),
        'm_ename'          => array(), 
        'm_nickname'       => array(), 
        'm_idnumber'       => array(), 
        'm_idname'         => array(),
        'm_sex'            => array(),
        'm_zip'            => array(),
        'm_city'           => array(),
        'm_canton'         => array(),
        'm_address'        => array(),
        'm_tel'            => array(),
        'm_cellphone'      => array(),
        'm_email'          => array(),   
        'm_info'           => array(),  
        'm_bonus'          => array(),  
        'm_totalbonus'     => array(),  
        'm_epaper_status'  => array(),          
    ); 
    
    //取得post資料欄位
    protected function _retrieve_cols($post){
        parent::_retrieve_cols($post);
        $this->values['m_modifydate'] = date("Y-m-d H:i:s");
        $password = $this->_get_password($post);
        if($password)$this->values['m_password'] = $password;
    }
    
    protected function _get_password($post){
        if($post['m_password'] && $post['v_password']){
            if($post['m_password']==$post['v_password']){
                return md5(trim($post['m_password']));
            }
        }
    }    
}
?>
