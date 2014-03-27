<?php
include_once("../conf/config.inc.php");
include_once("../libs/libs-manage-sysconfig.php");
include_once("../lang/".$cms_cfg['language']."-utf8.php");
set_time_limit(0);
//取得佇列項目
$sql = "select * from ".$cms_cfg['tb_prefix']."_epaper_queue where eq_send_time <= now()";
$res = $db->query($sql);
if($db->numRows($res)){
    //取得寄件資訊
    $from_sql="select sc_company,sc_email from ".$cms_cfg['tb_prefix']."_system_config where sc_id = '1'";
    $from_res = $db->query($from_sql);
    $fromRow = $db->fetch_array($from_res,1);
    $from_mail=$fromRow["sc_email"]; 
    $from_name=$fromRow["sc_company"];
    while($qRow = $db->fetch_array($res,1)){
        //取得寄送名單
        $mgArr = explode(',',$qRow['eq_group']);
        if(!count($mgArr))continue;
        foreach($mgArr as $k=>$v){
            $mgArr[$k] = "'".$v."'";
        }
        $sql="select m.m_email,group_concat(mc.mc_subject) as mc_subject from ".$cms_cfg['tb_prefix']."_member as m left join ".$cms_cfg['tb_prefix']."_member_cate as mc on find_in_set(mc.mc_id,m.mc_id) where m.m_epaper_status='1' and mc.mc_subject in(".implode(',',$mgArr).") group by m_id";
        $selectrs = $db->query($sql);
        $rsnum    = $db->numRows($selectrs);
        if($rsnum > 0){
            $mail_array=array();
            $mx_arr=array();
            while($row = $db->fetch_array($selectrs,1)){
                $piece=explode(",",$row["m_email"]);
                foreach($piece as $key => $value){
                    if(trim($value) && strpos($value, '@')!==false){
                        $mail_array[$value] += 1;
                        if($mail_array[$value]==1){
                            $tmp = explode('@',$value);
                            $mx_arr[$tmp[1]][] = $value;
                        }
                    }
                }
                unset($piece);
            }           
            if(!empty($mx_arr)){
                //取得電子報內容
                $mail_subject=$qRow["e_subject"];
                $mail_content=str_replace("=\"../upload_files/","=\"".$cms_cfg['file_url']."upload_files/",$qRow["eq_content"]);
                //初始化電子報樣版
                $mtpl = new TemplatePower('./templates/ws-manage-epaper-template-tpl.html');
                $mtpl->prepare();
                //取得電子報頁首、頁尾
                $sql = "select st_epaper_header,st_epaper_footer from ".$cms_cfg['tb_prefix']."_service_term where st_id='1'";
                list($e_header,$e_footer) = $db->query_firstrow($sql,0);
                $mtpl->assignGlobal("MSG_EPAPER_HEADER",$e_header);
                $mtpl->assignGlobal("MSG_EPAPER_FOOTER",$e_footer);                
                $mtpl->assignGlobal("MSG_COMPANY",$_SESSION[$cms_cfg['sess_cookie_name']]['sc_company']);
                $mtpl->assignGlobal("MSG_HOME",$TPLMSG['HOME']);
                $mtpl->assignGlobal("MSG_CONTACTUS",$TPLMSG['CONTACT_US']);
                $mtpl->assignGlobal("TAG_THEME_PATH" , $cms_cfg['default_theme']);
                $mtpl->assignGlobal("TAG_ROOT_PATH" , $cms_cfg['base_root']);
                $mtpl->assignGlobal("TAG_FILE_ROOT" , $cms_cfg['file_root']);
                $mtpl->assignGlobal("TAG_BASE_URL" ,$cms_cfg["base_url"]);
                $mtpl->assignGlobal("TAG_LANG",$cms_cfg['language']);                
                $mtpl->assign("_ROOT.EPAPER_PAGE_TITLE",$qRow["e_subject"]);
                $mtpl->assign("_ROOT.EPAPER_TITLE",$qRow["e_subject"]);
                $mtpl->assign("_ROOT.EPAPER_CONTENT",$mail_content);
                if(trim($qRow['eq_attach_products'])){
                    $sql = "select p.*,pc.pc_seo_filename from ".$cms_cfg['tb_prefix']."_products as p left join ".$cms_cfg['tb_prefix']."_products_cate as pc on p.pc_id=pc.pc_id where p_status='1' and p_id in(".$qRow['eq_attach_products'].")";
                    $p_rs = $db->query($sql);
                    while($p_row = $db->fetch_array($p_rs,1)){
                        $mtpl->newBlock("ATTACH_PRODUCT_LIST");
                        if($cms_cfg['ws_module']['ws_seo']){
                            $dirname = ($p_row['pc_seo_filename']?$p_row['pc_seo_filename']:"products")."/";
                            $p_link = $cms_cfg['base_url'].$dirname. $p_row['p_seo_filename'].".html";
                        }else{
                            $p_link = $cms_cfg['base_url']."products.php?func=p_detail&p_id=".$p_row['p_id'];
                        }
                        $simg = $p_row['p_small_img']?$cms_cfg['file_root'].$p_row['p_small_img']:$cms_cfg['default_preview_pic'];
                        $dimension = $main->resizeto($simg,219,171);
                        $mtpl->assign(array(
                           "VALUE_P_LINK"      => $p_link, 
                           "VALUE_P_SMALL_IMG" => $p_row['p_small_img']?$cms_cfg['file_url'].$p_row['p_small_img']:$cms_cfg['server_url'].$cms_cfg['default_preview_pic'], 
                           "VALUE_P_SMALL_IMG_W" => $dimension['width'], 
                           "VALUE_P_SMALL_IMG_H" => $dimension['height'], 
                           "VALUE_P_NAME"      => $p_row['p_name'], 
                           "VALUE_P_DESC"      => $p_row['p_desc'], 
                        ));
                    }
                }
                //寫入發送記錄
                $sql="
                    insert into ".$cms_cfg['tb_prefix']."_epaper_send (
                        e_id,
                        es_modifydate,
                        es_group,e_subject
                    ) values (
                        '".$qRow["e_id"]."',
                        '".date("Y-m-d H:i:s")."',
                        '".$qRow['eq_group']."',
                        '".$qRow["e_subject"]."'
                    )";
                $rs = $db->query($sql);
                while(!empty($mx_arr)){
                    foreach($mx_arr as $mx => $email_list){
                        $i=0;
                        $nums = count($email_list);
                        while(($mail_str = array_shift($email_list))!==null){
                            $i++;
                            $mtpl->assignGlobal("CURRENT_RECEIVER",$mail_str);
                            $mail_content = $mtpl->getOutputContent();
                            $main->ws_mail_send_simple($from_mail,$mail_str,$mail_content,$mail_subject,$from_name);
                            if($i==50 && $i<$nums){
                                $mx_arr[$mx] = $email_list;
                                sleep(60);
                                continue 2;
                            }
                            if($i==$nums)break;
                        }
                        unset($mx_arr[$mx]);
                        sleep(3);
                    }
                }                
            }
        }
    }
    //刪除過期的佇列
    $sql = "delete from ".$cms_cfg['tb_prefix']."_epaper_queue where eq_send_time <= now()";
    $db->query($sql);
    $sql = "OPTIMIZE TABLE  `".$cms_cfg['tb_prefix']."_epaper_queue`";
    $db->query($sql);
}
?>
