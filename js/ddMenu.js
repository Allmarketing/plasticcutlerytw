/* 主功能項目有rel屬性者才進行下拉式選單的初始化
** rel屬性則是擁有該主功能下拉式選單內容的div id
** 另外，這個div需套用.btn_submenu樣式。選單項目則是<a>標籤        
*/
(function($){
    $.fn.ddMenu = function(settings){
        var _settings = {};
        $.extend(_settings,settings);
        return $(this).each(function(){
            $(this).find("a[rel]").each(function(e){
                var offset = $(this).offset();
                var menuid = $(this).attr('rel');
                var obj_height = $(this).outerHeight(true);
                var parent = this;
                //設定起始位置
                $("#"+menuid).css({ 'top':(offset.top+obj_height)+'px','left':(offset.left)+'px' });
                //設定handler
                $("#"+menuid).mouseleave(function(e){
                    if(e.relatedTarget !== parent){
                        $(this).trigger('close'); 
                    }                      
                });
                $("#"+menuid).bind('close',function(e){
                    $(this).slideUp(); 
                });
                $("#"+menuid).bind('open',function(){
                    $(this).slideDown(); 
                });
                $("#"+menuid).find('a').each(function(){
                   if(/#/.test(this.href)){
                       $(this).bind('click',function(e){
                          e.preventDefault(); 
                       });
                   } 
                });
                $("#"+menuid).find('a').mouseenter(function(){
                    $(this).children('ul').slideDown();
                });
                $("#"+menuid).find('a').mouseleave(function(){
                    $(this).find('ul').hide();
                });            
                $("#"+menuid).find('li').mouseenter(function(){
                    $(this).children('ul').slideDown();
                });
                $("#"+menuid).find('li').mouseleave(function(){
                    $(this).children('ul').hide();
                });
                $("#"+menuid).find('span[rel]').click(function(){
                    var url = $(this).attr('rel');
                    location.href=url;
                });
                $(this).mouseenter(function(){
                    $("#"+menuid).trigger('open');
                });
                $(this).mouseleave(function(e){
                    var sw=true;
                    if($("#"+menuid)[0]===e.relatedTarget){
                        sw=false;
                    }else{
                        $(e.relatedTarget).parents().each(function(){
                            if(this===$("#"+menuid)[0]){
                                sw=false;
                                return false;
                            }
                        });
                    }
                    if(sw)$("#"+menuid).trigger('close');
                })                  
            });
        });
    }
})(jQuery);