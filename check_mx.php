<?
$email = 'andychang1125@gmail.com';
list(, $mailDomain) = split('@', $email); // 取出 DOMAIN_NAME
var_dump(checkdnsrr($mailDomain, 'MX'));
?>