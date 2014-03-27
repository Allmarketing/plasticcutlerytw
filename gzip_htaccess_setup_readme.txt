Header add Cache-Control "max-age=360000"
<FilesMatch "\.(css)">  
    ForceType application/x-httpd-php  
    php_value auto_prepend_file "/home/httpd/vhosts/tanong.com.tw/httpdocs/libs/libs-gzip-css.php"
</FilesMatch>
<FilesMatch "\.(js)">  
    ForceType application/x-httpd-php  
    php_value auto_prepend_file "/home/httpd/vhosts/tanong.com.tw/httpdocs/libs/libs-gzip-js.php"
</FilesMatch>