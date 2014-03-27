(function($){
    $.fn.bgiSlide = function(settings) {
        
        var bgiSlider = function(elm,options){
            this.slideId = null;
            this.options = {width:'100%', height:'100%'};
            this.currentId = 0;
            this.preId = -1;   
            this.elm  = elm;
            this.imgLength = $(elm).find('div[rel]').size();
            $.extend(this.options,options);
            //mouse事件設定
           $(elm).mouseover(function(e){                    
                var slideObj = $(this).data("bgiSlide");
                slideObj.stopSlide();
            });
            $(elm).mouseout(function(e){
                var slideObj = $(this).data("bgiSlide");
                slideObj.startSlide();
            });   
            //特殊事件設定
            if(this.options['AfterBgiSlide']){
                $(this.elm).bind("AfterBgiSlide",this.options['AfterBgiSlide']);
            }
            if(this.options['bigSlideLoaded']){
                $(this.elm).bind("bigSlideLoaded",this.options['bigSlideLoaded']);
            }
            
            //容器初始設定
            $(elm).css({position:'relative'});     
            var container = this;
            $(elm).find("div[rel]").each(function(){
                var img_src = $(this).attr("rel");
                $(this).css({
                    "background":"url("+img_src+") top center no-repeat ",
                     position: 'absolute',
                     top:'0',
                     left:'0',
                     width:'100%',
                     height:container.options['height']
                });                
            });                 
            
            this.stopSlide = function(){
                clearInterval(this.slideId);
            }
            
            this.startSlide = function(){
                var slideObj = this;
                this.slideId = setInterval(function(){
                    var id = slideObj.currentId + 1;
                    slideObj.slideTo(id);
                },5000);
            }
            
            this.slideTo = function(id){
                var obj = this;
                obj.preId = obj.currentId;
                obj.currentId = (id>(obj.imgLength-1) || id<0 )?0:id;  
                $(obj.elm).find('div[rel]:eq('+obj.currentId+')').css('z-index',10).fadeIn(3000,function(){
                    if(obj.preId>-1 && obj.preId!=obj.currentId){
                        $(obj.elm).find('div[rel]:eq('+obj.preId+')').css("z-index",1).hide();
                    } 
                });
                $(obj.elm).trigger("AfterBgiSlide");
            }
        }
        
        return this.each(function(){
            if($(this).data("bgiSlide")){
                return $(this).data("bgiSlide");
            }else{
                var slideObj = new bgiSlider(this,settings);
                slideObj.startSlide();
                $(this).data("bgiSlide",slideObj);    
                $(this).trigger("bigSlideLoaded");
            }
            
        });
    };
})(jQuery);

