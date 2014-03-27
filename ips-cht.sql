-- phpMyAdmin SQL Dump
-- version 2.10.3
-- http://www.phpmyadmin.net
-- 
-- 主機: localhost
-- 建立日期: Feb 22, 2010, 08:45 AM
-- 伺服器版本: 5.0.51
-- PHP 版本: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- 資料庫: `ipsdemo`
-- 

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_aboutus`
-- 

CREATE TABLE `cht_aboutus` (
  `au_id` int(8) NOT NULL auto_increment,
  `au_status` tinyint(1) NOT NULL default '1',
  `au_sort` int(5) NOT NULL default '1',
  `au_subject` varchar(100) NOT NULL,
  `au_content` text NOT NULL,
  `au_modifydate` datetime NOT NULL,
  PRIMARY KEY  (`au_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- 列出以下資料庫的數據： `cht_aboutus`
-- 

INSERT INTO `cht_aboutus` VALUES (1, 1, 1, 'About Us', '', '2009-11-16 18:16:53');

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_ad`
-- 

CREATE TABLE `cht_ad` (
  `ad_cate` tinyint(1) NOT NULL default '1',
  `ad_id` int(8) NOT NULL auto_increment,
  `ad_status` tinyint(1) NOT NULL default '1',
  `ad_sort` int(5) NOT NULL default '1',
  `ad_subject` varchar(100) NOT NULL,
  `ad_file_type` set('image','flash','txt') NOT NULL default 'image',
  `ad_file` varchar(100) NOT NULL,
  `ad_link` varchar(255) NOT NULL,
  `ad_modifydate` datetime NOT NULL,
  `ad_startdate` date NOT NULL,
  `ad_enddate` date NOT NULL,
  `ad_show_type` tinyint(1) NOT NULL default '0',
  `ad_show_zone` text NOT NULL,
  PRIMARY KEY  (`ad_id`),
  KEY `adc_id` (`ad_cate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- 列出以下資料庫的數據： `cht_ad`
-- 


-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_admin_authority`
-- 

CREATE TABLE `cht_admin_authority` (
  `ai_id` int(8) NOT NULL,
  `aa_aboutus` tinyint(1) NOT NULL default '1',
  `aa_ad` tinyint(1) NOT NULL default '1',
  `aa_admin` tinyint(1) NOT NULL default '0',
  `aa_blog` tinyint(1) NOT NULL default '0',
  `aa_bonus` tinyint(1) NOT NULL default '0',
  `aa_contactus` tinyint(1) NOT NULL default '1',
  `aa_download` tinyint(1) NOT NULL default '1',
  `aa_ebook` tinyint(1) NOT NULL default '0',
  `aa_epaper` tinyint(1) NOT NULL default '1',
  `aa_faq` tinyint(1) NOT NULL default '1',
  `aa_forum` tinyint(1) NOT NULL default '0',
  `aa_guestbook` tinyint(1) NOT NULL default '0',
  `aa_inquiry` tinyint(1) NOT NULL default '1',
  `aa_member` tinyint(1) NOT NULL default '1',
  `aa_news` tinyint(1) NOT NULL default '1',
  `aa_order` tinyint(1) NOT NULL default '1',
  `aa_products_cate` tinyint(1) NOT NULL default '1',
  `aa_products` tinyint(1) NOT NULL default '1',
  `aa_sysconfig` tinyint(1) NOT NULL default '0',
  `aa_systool` tinyint(1) NOT NULL default '0',
  `aa_seo` tinyint(1) NOT NULL default '0',
  `aa_google_sitemap` tinyint(1) NOT NULL default '0',
  `aa_google_analytics` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`ai_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- 列出以下資料庫的數據： `cht_admin_authority`
-- 

INSERT INTO `cht_admin_authority` VALUES (1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
INSERT INTO `cht_admin_authority` VALUES (2, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0);
-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_admin_info`
-- 

CREATE TABLE `cht_admin_info` (
  `ai_id` int(8) NOT NULL auto_increment,
  `ai_status` tinyint(1) NOT NULL default '1',
  `ai_sort` int(5) NOT NULL default '1',
  `ai_modifydate` date NOT NULL,
  `ai_account` varchar(100) NOT NULL,
  `ai_password` varchar(10) NOT NULL,
  `ai_name` varchar(100) NOT NULL,
  `ai_address` varchar(255) NOT NULL,
  `ai_tel` varchar(50) NOT NULL,
  `ai_cellphone` varchar(50) NOT NULL,
  `ai_email` varchar(255) NOT NULL,
  PRIMARY KEY  (`ai_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- 
-- 列出以下資料庫的數據： `cht_admin_info`
-- 

INSERT INTO `cht_admin_info` VALUES (1, 1, 1, '2010-01-01', 'root', 'pwamg', '管理員', '', '', '', 'it@allmarketing.com.tw');
INSERT INTO `cht_admin_info` VALUES (2, 1, 2, '2010-03-18', 'eric', 'amg2246', 'Eric', '', '', '', 'eric@allmarketing.com.tw');

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_blog`
-- 

CREATE TABLE `cht_blog` (
  `bc_id` int(8) NOT NULL,
  `b_id` int(8) NOT NULL auto_increment,
  `b_status` tinyint(1) NOT NULL default '1',
  `b_sort` int(5) NOT NULL default '1',
  `b_subject` varchar(100) NOT NULL,
  `b_content` text NOT NULL,
  `b_modifydate` datetime NOT NULL,
  PRIMARY KEY  (`b_id`),
  KEY `fc_id` (`bc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- 列出以下資料庫的數據： `cht_blog`
-- 


-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_blog_cate`
-- 

CREATE TABLE `cht_blog_cate` (
  `bc_id` int(8) NOT NULL auto_increment,
  `bc_status` tinyint(1) default '1',
  `bc_sort` int(5) default '1',
  `bc_subject` varchar(100) NOT NULL,
  PRIMARY KEY  (`bc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- 列出以下資料庫的數據： `cht_blog_cate`
-- 


-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_blog_reply`
-- 

CREATE TABLE `cht_blog_reply` (
  `b_id` int(8) NOT NULL,
  `br_id` int(8) NOT NULL auto_increment,
  `br_name` varchar(100) NOT NULL,
  `br_email` varchar(255) NOT NULL,
  `br_ip` varchar(15) NOT NULL,
  `br_content` text NOT NULL,
  `br_modifydate` datetime NOT NULL,
  PRIMARY KEY  (`br_id`),
  KEY `b_id` (`b_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- 列出以下資料庫的數據： `cht_blog_reply`
-- 


-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_contactus`
-- 

CREATE TABLE `cht_contactus` (
  `m_id` int(8) NOT NULL default '0',
  `cu_cate` tinyint(2) NOT NULL default '1',
  `cu_id` int(8) NOT NULL auto_increment,
  `cu_company_name` varchar(100) NOT NULL,
  `cu_contact_s` varchar(10) NOT NULL default 'Mr.',
  `cu_status` tinyint(1) NOT NULL default '0',
  `cu_name` varchar(100) NOT NULL,
  `cu_tel` varchar(50) NOT NULL,
  `cu_fax` varchar(50) NOT NULL,
  `cu_country` varchar(50) NOT NULL,
  `cu_address` varchar(255) NOT NULL,
  `cu_email` varchar(255) NOT NULL,
  `cu_url` varchar(255) NOT NULL,
  `cu_content` text NOT NULL,
  `cu_modifydate` datetime NOT NULL,
  PRIMARY KEY  (`cu_id`),
  KEY `cuc_id` (`cu_cate`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- 列出以下資料庫的數據： `cht_contactus`
-- 


-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_contactus_reply`
-- 

CREATE TABLE `cht_contactus_reply` (
  `cu_id` int(8) NOT NULL,
  `cur_id` int(8) NOT NULL auto_increment,
  `cur_content` text NOT NULL,
  `cur_modifydate` datetime NOT NULL,
  PRIMARY KEY  (`cur_id`),
  KEY `cu_id` (`cu_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- 列出以下資料庫的數據： `cht_contactus_reply`
-- 


-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_download`
-- 

CREATE TABLE `cht_download` (
  `dc_id` int(8) NOT NULL,
  `d_id` int(8) NOT NULL auto_increment,
  `d_status` tinyint(1) NOT NULL default '1',
  `d_sort` int(5) NOT NULL default '1',
  `d_subject` varchar(100) NOT NULL,
  `d_content` text NOT NULL,
  `d_filepath` text NOT NULL,
  `d_modifydate` datetime NOT NULL,
  PRIMARY KEY  (`d_id`),
  KEY `dc_id` (`dc_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- 列出以下資料庫的數據： `cht_download`
-- 

INSERT INTO `cht_download` VALUES (1, 1, 1, 1, 'File', 'File', 'upload_files/ws-no-image.jpg', '2008-10-24 15:46:44');

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_download_cate`
-- 

CREATE TABLE `cht_download_cate` (
  `dc_id` int(8) NOT NULL auto_increment,
  `dc_status` tinyint(1) default '1',
  `dc_sort` int(5) default '1',
  `dc_subject` varchar(100) NOT NULL,
  PRIMARY KEY  (`dc_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- 列出以下資料庫的數據： `cht_download_cate`
-- 

INSERT INTO `cht_download_cate` VALUES (1, 1, 1, 'DownloadCate');

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_epaper`
-- 

CREATE TABLE `cht_epaper` (
  `ec_id` int(8) NOT NULL,
  `e_id` int(8) NOT NULL auto_increment,
  `e_status` tinyint(1) NOT NULL default '1',
  `e_sort` int(5) NOT NULL default '1',
  `e_subject` varchar(100) NOT NULL,
  `e_content` text NOT NULL,
  `e_modifydate` datetime NOT NULL,
  PRIMARY KEY  (`e_id`),
  KEY `ec_id` (`ec_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- 列出以下資料庫的數據： `cht_epaper`
-- 

INSERT INTO `cht_epaper` VALUES (1, 1, 1, 1, 'test', '<p>test</p>', '2009-12-11 10:35:34');

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_epaper_cate`
-- 

CREATE TABLE `cht_epaper_cate` (
  `ec_id` int(8) NOT NULL auto_increment,
  `ec_status` tinyint(1) default '1',
  `ec_sort` int(5) default '1',
  `ec_subject` varchar(100) NOT NULL,
  PRIMARY KEY  (`ec_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- 列出以下資料庫的數據： `cht_epaper_cate`
-- 

INSERT INTO `cht_epaper_cate` VALUES (1, 1, 1, '123');

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_epaper_send`
-- 

CREATE TABLE `cht_epaper_send` (
  `e_id` int(8) default '0',
  `es_id` int(8) NOT NULL auto_increment,
  `es_group` text NOT NULL,
  `e_subject` varchar(255) NOT NULL,
  `es_modifydate` datetime NOT NULL,
  PRIMARY KEY  (`es_id`),
  KEY `e_id` (`e_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- 列出以下資料庫的數據： `cht_epaper_send`
-- 


-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_faq`
-- 

CREATE TABLE `cht_faq` (
  `fc_id` int(8) NOT NULL,
  `f_id` int(8) NOT NULL auto_increment,
  `f_status` tinyint(1) NOT NULL default '1',
  `f_sort` int(5) NOT NULL default '1',
  `f_subject` varchar(100) NOT NULL,
  `f_content` text NOT NULL,
  `f_modifydate` datetime NOT NULL,
  PRIMARY KEY  (`f_id`),
  KEY `fc_id` (`fc_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- 列出以下資料庫的數據： `cht_faq`
-- 

INSERT INTO `cht_faq` VALUES (1, 1, 1, 1, 'FAQ1', 'FAQ1', '2008-10-24 15:39:12');

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_faq_cate`
-- 

CREATE TABLE `cht_faq_cate` (
  `fc_id` int(8) NOT NULL auto_increment,
  `fc_status` tinyint(1) default '1',
  `fc_sort` int(5) default '1',
  `fc_subject` varchar(100) NOT NULL,
  PRIMARY KEY  (`fc_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- 列出以下資料庫的數據： `cht_faq_cate`
-- 

INSERT INTO `cht_faq_cate` VALUES (1, 1, 1, 'FAQCate1');

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_gallery`
-- 

CREATE TABLE `cht_gallery` (
  `gc_id` int(8) NOT NULL,
  `g_id` int(8) NOT NULL auto_increment,
  `g_status` tinyint(1) NOT NULL default '1',
  `g_sort` int(5) NOT NULL default '1',
  `g_hot` tinyint(1) NOT NULL default '0',
  `g_pop` tinyint(1) NOT NULL default '0',
  `g_subject` varchar(100) NOT NULL,
  `g_content_type` tinyint(1) NOT NULL default '1',
  `g_content` text NOT NULL,
  `g_url` varchar(255) NOT NULL,
  `g_s_pic` varchar(255) NOT NULL,
  `g_b_pic1` varchar(255) NOT NULL,
  `g_b_pic2` varchar(255) NOT NULL,
  `g_b_pic3` varchar(255) NOT NULL,
  `g_b_pic4` varchar(255) NOT NULL,
  `g_b_pic5` varchar(255) NOT NULL,
  `g_b_pic6` varchar(255) NOT NULL,
  `g_b_pic7` varchar(255) NOT NULL,
  `g_b_pic8` varchar(255) NOT NULL,
  `g_b_pic9` varchar(255) NOT NULL,
  `g_b_pic10` varchar(255) NOT NULL,
  `g_modifydate` datetime NOT NULL,
  `g_startdate` date NOT NULL,
  `g_enddate` date NOT NULL,
  PRIMARY KEY  (`g_id`),
  KEY `gc_id` (`gc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

 

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_gallery_cate`
-- 

CREATE TABLE `cht_gallery_cate` (
  `gc_id` int(8) NOT NULL auto_increment,
  `gc_status` tinyint(1) default '1',
  `gc_sort` int(5) default '1',
  `gc_subject` varchar(100) NOT NULL,
  PRIMARY KEY  (`gc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- 
-- 資料表格式： `cht_guestbook`
-- 

CREATE TABLE `cht_guestbook` (
  `gb_id` int(8) NOT NULL auto_increment,
  `gb_parent` int(8) NOT NULL,
  `gb_name` varchar(100) NOT NULL,
  `gb_sex` tinyint(2) NOT NULL,
  `gb_textcolor` varchar(10) NOT NULL,
  `gb_img` varchar(50) NOT NULL,
  `gb_email` varchar(255) NOT NULL,
  `gb_content` text NOT NULL,
  `gb_modifydate` datetime NOT NULL,
  `gb_reply_type` tinyint(2) NOT NULL default '0',
  `gb_hidden` tinyint(2) NOT NULL,
  `gb_url` varchar(50) NOT NULL,
  `gb_ip` varchar(16) NOT NULL,
  PRIMARY KEY  (`gb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- 列出以下資料庫的數據： `cht_guestbook`
-- 


-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_inquiry`
-- 

CREATE TABLE `cht_inquiry` (
  `m_id` int(8) NOT NULL,
  `i_id` int(10) NOT NULL auto_increment,
  `i_status` tinyint(1) NOT NULL default '1',
  `i_createdate` datetime NOT NULL,
  `i_modifydate` datetime NOT NULL,
  `i_account` varchar(100) NOT NULL,
  `i_company_name` varchar(100) NOT NULL,
  `i_contact_s` varchar(10) NOT NULL,
  `i_name` varchar(100) NOT NULL,
  `i_country` varchar(100) NOT NULL,
  `i_zip` int(5) NOT NULL default '0',
  `i_address` varchar(255) NOT NULL,
  `i_tel` varchar(50) NOT NULL,
  `i_fax` varchar(50) NOT NULL,
  `i_cellphone` varchar(50) NOT NULL,
  `i_email` varchar(255) NOT NULL,
  `i_url` varchar(255) NOT NULL,
  `i_content` text NOT NULL,
  `i_reply` text NOT NULL,
  PRIMARY KEY  (`i_id`),
  KEY `mc_id` (`m_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16800001 ;

-- 
-- 列出以下資料庫的數據： `cht_inquiry`
-- 


-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_inquiry_items`
-- 

CREATE TABLE `cht_inquiry_items` (
  `m_id` int(8) NOT NULL,
  `i_id` int(10) NOT NULL,
  `ii_id` int(8) NOT NULL auto_increment,
  `p_id` int(8) NOT NULL,
  `p_name` varchar(100) NOT NULL,
  `ii_amount` int(5) NOT NULL,
  PRIMARY KEY  (`ii_id`),
  KEY `m_id` (`m_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- 列出以下資料庫的數據： `cht_inquiry_items`
-- 


-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_ip_country`
-- 

CREATE TABLE `cht_ip_country` (
  `ip_from` int(8) NOT NULL,
  `ip_to` int(8) NOT NULL,
  `country_code3` char(3)  NOT NULL,
  `country_name` varchar(50) NOT NULL,
  KEY `ip_from` (`ip_from`),
  KEY `ip_to` (`ip_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- 列出以下資料庫的數據： `cht_ip_country`
-- 


-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_login_history`
-- 

CREATE TABLE `cht_login_history` (
  `ai_id` int(8) NOT NULL,
  `m_id` int(8) NOT NULL,
  `lh_id` int(8) NOT NULL auto_increment,
  `lh_success` tinyint(1) NOT NULL default '1',
  `lh_modifydate` datetime NOT NULL,
  PRIMARY KEY  (`lh_id`),
  KEY `ai_id` (`ai_id`),
  KEY `m_id` (`m_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- 列出以下資料庫的數據： `cht_login_history`
-- 


-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_member`
-- 

CREATE TABLE `cht_member` (
  `mc_id` int(8) NOT NULL,
  `m_id` int(8) NOT NULL auto_increment,
  `m_status` tinyint(1) NOT NULL default '1',
  `m_sort` int(5) NOT NULL default '1',
  `m_modifydate` date NOT NULL,
  `m_account` varchar(100) NOT NULL,
  `m_password` varchar(10) NOT NULL,
  `m_company_name` varchar(100) NOT NULL,
  `m_contact_s` varchar(10) NOT NULL default 'Mr.',
  `m_name` varchar(100) NOT NULL,
  `m_birthday` date NOT NULL,
  `m_sex` tinyint(1) NOT NULL default '0',
  `m_country` varchar(255) NOT NULL,
  `m_zip` int(5) NOT NULL default '0',
  `m_address` varchar(255) NOT NULL,
  `m_tel` varchar(50) NOT NULL,
  `m_fax` varchar(50) NOT NULL,
  `m_cellphone` varchar(50) NOT NULL,
  `m_email` varchar(255) NOT NULL,
  `m_url` varchar(255) NOT NULL,
  `m_epaper_status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`m_id`),
  KEY `mc_id` (`mc_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- 列出以下資料庫的數據： `cht_member`
-- 

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_member_cate`
-- 

CREATE TABLE `cht_member_cate` (
  `mc_id` int(8) NOT NULL auto_increment,
  `mc_status` tinyint(1) NOT NULL default '1',
  `mc_sort` int(5) NOT NULL default '1',
  `mc_subject` varchar(100) NOT NULL,
  `mc_discount` int(3) NOT NULL,
  PRIMARY KEY  (`mc_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- 列出以下資料庫的數據： `cht_member_cate`
-- 

INSERT INTO `cht_member_cate` VALUES (1, 1, 1, 'Normal', 100);

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_metatitle`
-- 

CREATE TABLE `cht_metatitle` (
  `mt_name` varchar(50) NOT NULL,
  `mt_seo_title` varchar(255) NOT NULL,
  `mt_seo_keyword` varchar(255) NOT NULL,
  `mt_seo_description` text NOT NULL,
  `mt_seo_short_desc` text NOT NULL,
  `mt_seo_h1` varchar(255) NOT NULL,
  PRIMARY KEY  (`mt_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- 列出以下資料庫的數據： `cht_metatitle`
-- 

INSERT INTO `cht_metatitle` VALUES ('aboutus', '', '', '', '', '');
INSERT INTO `cht_metatitle` VALUES ('contactus', '', '', '', '', '');
INSERT INTO `cht_metatitle` VALUES ('download', '', '', '', '', '');
INSERT INTO `cht_metatitle` VALUES ('faq', '', '', '', '', '');
INSERT INTO `cht_metatitle` VALUES ('news', '', '', '', '', '');
INSERT INTO `cht_metatitle` VALUES ('products', '', '', '', '', '');
INSERT INTO `cht_metatitle` VALUES ('sitemap', '', '', '', '', '');
INSERT INTO `cht_metatitle` VALUES ('default', '', '', '', '', '');

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_news`
-- 

CREATE TABLE `cht_news` (
  `nc_id` int(8) NOT NULL,
  `n_id` int(8) NOT NULL auto_increment,
  `n_status` tinyint(1) NOT NULL default '1',
  `n_sort` int(5) NOT NULL default '1',
  `n_hot` tinyint(1) NOT NULL default '0',
  `n_pop` tinyint(1) NOT NULL default '0',
  `n_subject` varchar(100) NOT NULL,
  `n_short` varchar(500) NOT NULL,
  `n_content_type` tinyint(1) NOT NULL default '1',
  `n_content` text NOT NULL,
  `n_url` varchar(255) NOT NULL,
  `n_s_pic` text NOT NULL,
  `n_modifydate` datetime NOT NULL,
  `n_startdate` date NOT NULL,
  `n_enddate` date NOT NULL,
  PRIMARY KEY  (`n_id`),
  KEY `nc_id` (`nc_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- 列出以下資料庫的數據： `cht_news`
-- 

INSERT INTO `cht_news` VALUES (1, 1, 1, 1, 0, 0, 'news-test','test', 1, 'test', '','upload_files/ws-no-image.jpg', '2008-10-24 15:30:41', '2008-10-01', '0000-00-00');

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_news_cate`
-- 

CREATE TABLE `cht_news_cate` (
  `nc_id` int(8) NOT NULL auto_increment,
  `nc_status` tinyint(1) default '1',
  `nc_sort` int(5) default '1',
  `nc_subject` varchar(100) NOT NULL,
  PRIMARY KEY  (`nc_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- 列出以下資料庫的數據： `cht_news_cate`
-- 

INSERT INTO `cht_news_cate` VALUES (1, 1, 1, 'newsCate');

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_order`
-- 

CREATE TABLE `cht_order` (
  `m_id` int(8) NOT NULL,
  `o_id` int(10) NOT NULL auto_increment,
  `o_status` tinyint(1) NOT NULL default '1',
  `o_createdate` datetime NOT NULL,
  `o_modifydate` datetime NOT NULL,
  `o_account` varchar(100) NOT NULL,
  `o_company_name` varchar(100) NOT NULL,
  `o_contact_s` varchar(10) NOT NULL,
  `o_name` varchar(100) NOT NULL,
  `o_zip` int(5) NOT NULL default '0',
  `o_country` varchar(50) NOT NULL,
  `o_address` varchar(255) NOT NULL,
  `o_tel` varchar(50) NOT NULL,
  `o_fax` varchar(50) NOT NULL,
  `o_cellphone` varchar(50) NOT NULL,
  `o_email` varchar(255) NOT NULL,
  `o_plus_price` int(8) NOT NULL,
  `o_subtotal_price` int(8) NOT NULL,
  `o_total_price` int(8) NOT NULL,
  `o_content` text NOT NULL,
  `o_payment_type` varchar(100) NOT NULL,
  PRIMARY KEY  (`o_id`),
  KEY `mc_id` (`m_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26800001 ;

-- 
-- 列出以下資料庫的數據： `cht_order`
-- 


-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_order_items`
-- 

CREATE TABLE `cht_order_items` (
  `m_id` int(8) NOT NULL,
  `o_id` int(10) NOT NULL,
  `oi_id` int(8) NOT NULL auto_increment,
  `p_id` int(8) NOT NULL,
  `p_name` varchar(100) NOT NULL,
  `p_sell_price` int(8) NOT NULL,
  `oi_amount` int(5) NOT NULL default '0',
  PRIMARY KEY  (`oi_id`),
  KEY `m_id` (`m_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- 列出以下資料庫的數據： `cht_order_items`
-- 


-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_pageview_history`
-- 

CREATE TABLE `cht_pageview_history` (
  `m_id` int(8) NOT NULL default '0',
  `ph_id` int(8) NOT NULL auto_increment,
  `ph_ip_number` int(8) NOT NULL default '0',
  `ph_country` varchar(50) NOT NULL,
  `ph_type` varchar(4) NOT NULL,
  `ph_type_id` int(8) NOT NULL default '0',
  `ph_modifydate` datetime NOT NULL,
  `ph_dateY` smallint(4) NOT NULL,
  `ph_dateM` tinyint(2) NOT NULL,
  `ph_dateD` tinyint(2) NOT NULL,
  `ph_sum_target` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`ph_id`),
  KEY `m_id` (`m_id`),
  KEY `ph_type` (`ph_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- 列出以下資料庫的數據： `cht_pageview_history`
-- 


-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_products`
-- 

CREATE TABLE `cht_products` (
  `pc_id` varchar(50) NOT NULL default '0',
  `pc_layer` varchar(100) NOT NULL default '0',
  `p_id` int(10) NOT NULL auto_increment,
  `p_status` tinyint(1) NOT NULL default '1',
  `p_sort` int(5) NOT NULL default '1',
  `p_new_sort` int(5) NOT NULL default '1',
  `p_name` varchar(100) NOT NULL,
  `p_name_alias` varchar(100) NOT NULL,
  `p_custom_status` tinyint(1) NOT NULL default '0',
  `p_custom` mediumtext NOT NULL,
  `p_show_style` tinyint(2) NOT NULL default '1',
  `p_type` tinyint(2) NOT NULL default '1',
  `p_show_price` tinyint(1) NOT NULL default '1',
  `p_list_price` int(8) NOT NULL default '0',
  `p_special_price` int(8) NOT NULL default '0',
  `p_serial` varchar(100) NOT NULL,
  `p_small_img` text NOT NULL,
  `p_related_products` text NOT NULL,
  `p_spec_title` varchar(255) NOT NULL,
  `p_spec` text NOT NULL,
  `p_character_title` varchar(255) NOT NULL,
  `p_character` text NOT NULL,
  `p_desc_title` varchar(255) NOT NULL,
  `p_desc` text NOT NULL,
  `p_desc_strip` varchar(255) NOT NULL,
  `p_attach_file1` text NOT NULL,
  `p_attach_file2` text NOT NULL,
  `p_modifydate` datetime NOT NULL,
  `p_seo_filename` varchar(255) NOT NULL,
  `p_seo_title` varchar(255) NOT NULL,
  `p_seo_keyword` varchar(255) NOT NULL,
  `p_seo_description` text NOT NULL,
  `p_seo_short_desc` text NOT NULL,
  `p_seo_h1` varchar(255) NOT NULL,
  `p_seo_h2` text NOT NULL,
  `p_cross_cate` text NOT NULL,
  `p_locked` tinyint(1) NOT NULL default '1',
  `p_modifyaccount` varchar(100) NOT NULL default 'root',
  PRIMARY KEY  (`p_id`),
  KEY `pc_id` (`pc_id`),
  KEY `p_status` (`p_status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- 列出以下資料庫的數據： `cht_products`
-- 

INSERT INTO `cht_products` VALUES ('4', '0-1-4', 1, 1, 1, 0, 'product1', '', 0, '', 1, 0, 0, 0, 0, '', '', '', 'Product Specifications', 'Specifications', 'Product  Features', 'Features', 'Product  Description', 'Description', '', '', '', '2010-02-22 16:20:34', 'product1', '', '', '', '', '', '', '', 1, 'root');

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_products_cate`
-- 

CREATE TABLE `cht_products_cate` (
  `pc_id` int(10) NOT NULL auto_increment,
  `pc_parent` int(10) NOT NULL default '0',
  `pc_layer` varchar(100) NOT NULL default '0',
  `pc_status` tinyint(1) NOT NULL default '1',
  `pc_sort` int(5) NOT NULL default '1',
  `pc_name` varchar(100) NOT NULL,
  `pc_name_alias` varchar(100) NOT NULL,
  `pc_level` tinyint(1) NOT NULL default '1',
  `pc_custom_status` tinyint(1) NOT NULL default '0',
  `pc_custom` mediumtext NOT NULL,
  `pc_show_style` tinyint(2) NOT NULL default '1',
  `pc_cate_img` text NOT NULL,
  `pc_related_cate` text NOT NULL,
  `pc_modifydate` datetime NOT NULL,
  `pc_seo_filename` varchar(255) NOT NULL,
  `pc_seo_title` varchar(255) NOT NULL,
  `pc_seo_keyword` varchar(255) NOT NULL,
  `pc_seo_description` text NOT NULL,
  `pc_seo_short_desc` text NOT NULL,
  `pc_seo_h1` varchar(255) NOT NULL,
  `pc_seo_h2` text NOT NULL,
  `pc_cross_cate` text NOT NULL,
  `pc_locked` tinyint(1) NOT NULL default '1',
  `pc_modifyaccount` varchar(100) NOT NULL default 'root',
  PRIMARY KEY  (`pc_id`),
  KEY `pc_parent` (`pc_parent`),
  KEY `pc_status` (`pc_status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- 
-- 列出以下資料庫的數據： `cht_products_cate`
-- 

INSERT INTO `cht_products_cate` VALUES (1, 0, '0-1', 1, 1, 'cate1', '', 2, 0, '', 0, '', '', '2010-02-22 02:49:20', 'cate1', '', '', '', '', '','', '', 1, 'root');
INSERT INTO `cht_products_cate` VALUES (2, 0, '0-2', 1, 2, 'cate2', '', 1, 0, '', 0, '', '', '2010-02-22 02:00:48', 'cate2', '', '', '', '', '','', '', 1, 'root');
INSERT INTO `cht_products_cate` VALUES (3, 0, '0-3', 1, 3, 'cate3', '', 1, 0, '', 0, '', '', '2010-02-22 02:00:27', 'cate3', '', '', '', '', '','', '', 1, 'root');
INSERT INTO `cht_products_cate` VALUES (4, 1, '0-1-4', 1, 1, 'cate1-1', '', 2, 0, '', 0, '', '', '2010-02-22 02:01:24', 'cate1-1', '', '', '', '','', '', '', 1, 'root');

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_products_img`
-- 

CREATE TABLE `cht_products_img` (
  `p_id` int(10) NOT NULL,
  `p_big_img1` text NOT NULL,
  `p_big_img2` text NOT NULL,
  `p_big_img3` text NOT NULL,
  `p_big_img4` text NOT NULL,
  `p_big_img5` text NOT NULL,
  `p_big_img6` text NOT NULL,
  `p_big_img7` text NOT NULL,
  `p_big_img8` text NOT NULL,
  PRIMARY KEY  (`p_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- 列出以下資料庫的數據： `cht_products_img`
-- 

INSERT INTO `cht_products_img` VALUES (1, '', '', '', '', '', '', '', '');

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_service_term`
-- 

CREATE TABLE `cht_service_term` (
  `st_id` int(8) NOT NULL,
  `st_bonus_term` text NOT NULL,
  `st_contactus_term` text NOT NULL,
  `st_join_member_mail` text NOT NULL,
  `st_payment_term` text NOT NULL,
  `st_privacy_policy` text NOT NULL,
  `st_service_term` text NOT NULL,
  `st_shipping_term` text NOT NULL,
  `st_shopping_term` text NOT NULL,
  `st_inquiry_mail` text NOT NULL,
  `st_order_mail` text NOT NULL,
  PRIMARY KEY  (`st_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- 列出以下資料庫的數據： `cht_service_term`
-- 

INSERT INTO `cht_service_term` VALUES (1, '', '', '', '', '', '', '', '', '', '');

-- --------------------------------------------------------

-- 
-- 資料表格式： `cht_system_config`
-- 

CREATE TABLE `cht_system_config` (
  `sc_id` int(8) NOT NULL,
  `sc_status` tinyint(1) NOT NULL default '1',
  `sc_close_msg` varchar(255) NOT NULL,
  `sc_default_front_page` varchar(100) NOT NULL default 'aboutus.php',
  `sc_company` varchar(100) NOT NULL,
  `sc_cart_type` tinyint(1) NOT NULL default '0',
  `sc_shipping_price` int(5) NOT NULL default '0',
  `sc_no_shipping_price` int(5) NOT NULL default '0',
  `sc_ad_sort_type` tinyint(4) NOT NULL default '0',
  `sc_email` varchar(255) NOT NULL,
  `sc_debug` tinyint(1) NOT NULL default '0',
  `sc_template` tinyint(2) NOT NULL default '1',
  `sc_one_page_limit` int(5) NOT NULL default '12',
  `sc_session_duration` int(5) NOT NULL default '0',
  `sc_meta_title` varchar(255) NOT NULL,
  `sc_meta_keyword` varchar(255) NOT NULL,
  `sc_meta_description` text NOT NULL,
  `sc_short_desc` text NOT NULL,
  `sc_seo_h1` varchar(255) NOT NULL,
  `sc_footer` text NOT NULL,
  `sc_im_status` tinyint(1) NOT NULL default '0',
  `sc_im_starttime` time NOT NULL default '08:00:00',
  `sc_im_endtime` time NOT NULL default '18:00:00',
  `sc_im_msn` varchar(100) NOT NULL,
  `sc_im_skype` varchar(100) NOT NULL,
  `sc_seo_rewrite` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`sc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- 列出以下資料庫的數據： `cht_system_config`
-- 
INSERT INTO `cht_system_config` VALUES (1, 1, '', 'main.php', 'cms', 0, 100, 40000, 0, 'it@allmarketing.com.tw', 1, 1, 12, 14400, 'cms', '', '', '', '', '', 0, '08:30:00', '18:50:00', '', '', 0);