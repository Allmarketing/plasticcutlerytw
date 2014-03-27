<?php
class Dbtable_Aboutus extends Dbtable_Abstract{
    //可修改的欄位
    protected $table = "aboutus";
    protected $pk = "au_id";
    protected $post_cols = array(
        'au_id'              => "",
        'au_status'          => "",
        'au_sort'            => "",
        'au_cate'            => "",
        'au_subject'         => "",
        'au_content'         => "",
        'au_modifydate'      => "", 
        'au_seo_title'       => "", 
        'au_seo_keyword'     => "", 
        'au_seo_description' => "",
        'au_seo_filename'    => "",
        'au_seo_h1'          => "",        
    );     
    protected function _retrieve_cols($post){
        parent::_retrieve_cols($post);
        $au_cate = $post['au_cate_input']?$post['au_cate_input']:$post['au_cate_select'];
        $au_cate = $au_cate?$au_cate:"aboutus";
        $this->values['au_cate'] = $this->db->quote($au_cate);
    }    
}
?>
