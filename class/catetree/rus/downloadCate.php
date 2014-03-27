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
class catetree_rus_downloadCate extends catetree_abstract {
    //put your code here
    protected $_db;
    protected $_cfg;
    protected $_cate_table = "rus_download_cate";
    protected $_catename_column = "subject";
    protected $_parent_column = "parent";
    protected $_prefix = "dc_";
    protected $_id_tree = array();
    protected $_tree_structure = "";
    protected $_templates = "templates/ws-manage-fn-cate-tree-tpl.html";
    protected $_cate_link_str = "";
    protected $_build = false;
    protected $_varname = "dc_id";
 
}

?>
