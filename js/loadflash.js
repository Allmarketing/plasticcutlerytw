function LoadFlash(url,wmode,width,Height)
{ 
document.write(
  '<embed src="' + url + '" wmode=' + wmode +' quality="high"  width="' + width + '" height="' + Height + '" pluginspage=http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash type="application/x-shockwave-flash"></embed>');   
}

/* example: <script type="text/javascript">LoadFlash('swf/index.swf','transparent','720','301')</script> */

