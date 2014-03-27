/*
Function : Floating block (using class)
Author : Audi
http://audi.tw
Date:March 2008
歡迎應用於無償用途散播，並請勿移除本版權宣告
*/
(function($){
    window.floatMark=function(layerName,scrollSpeed) {
            this.id=layerName;
            this.obj=document.getElementById(this.id);
            this.scrollSpeed=scrollSpeed;	//捲動速度
            var fm = this;
            //初始化位置
            this.initPosition = function(){
                fm.lastScrollY=document.documentElement.scrollTop;
                fm.lastScrollX=document.documentElement.scrollLeft;            
                var x=$(window).width()-$(fm.obj).outerWidth()+fm.lastScrollX;
                var y=parseInt(($(window).height()-$(fm.obj).outerHeight())/2)+fm.lastScrollY;
                fm.obj.style.left = x+'px';
                fm.obj.style.top = y+'px';
            }      
            this.initPosition();
            $(window).resize(this.initPosition);

    }

     window.floatMark.prototype.setScroll=function(time){
            var obj=this.obj;
            var lastScrollY=this.lastScrollY;
            var scrollSpeed=this.scrollSpeed;

            setInterval(function(){

                    diffY = (document.all)?document.documentElement.scrollTop:self.pageYOffset;
                    diffX = $(window).width()-$(obj).outerWidth();


                    //if (obj.style.visibility!='hidden'){
                            if(diffY != lastScrollY){        
                                    percent = 1 * (diffY - lastScrollY) / scrollSpeed;
                                    if(percent > 0) percent = Math.ceil(percent);
                                    else percent = Math.floor(percent);

                                    var offset = $(obj).position();
                                    //newY=getPosTop(obj);	
                                    newY = offset.top;				
                                    newY+=percent;

                                    newY=newY+'px';
                                    newX=diffX +'px';
                                    obj.style.top = newY;
                                    obj.style.left = newX;
                                    /*if (document.all){
                                            newY=parseInt(obj.style.pixelTop);
                                            newY+=percent;
                                            newY=newY;
                                            obj.style.pixelTop = newY;
                                    }else{
                                            newY=parseInt(obj.style.top);
                                            newY+=percent;
                                            newY=newY+'px';
                                            obj.style.top = newY;
                                    }*/

                                    lastScrollY += percent;
                            }
                    //}
            },time);
    }

    //Not Use
     window.floatMark.prototype.slide=function(){
            diffY = (document.all)?document.documentElement.scrollTop:self.pageYOffset;
            diffX = 0;

            window.status=diffY+','+this.obj.style.top;

            if (this.obj.style.visibility!='hidden'){
                    if(diffY != this.lastScrollY){

                            percent = 1 * (diffY - this.lastScrollY) / this.scrollSpeed;
                            if(percent > 0) percent = Math.ceil(percent);
                            else percent = Math.floor(percent);

                            if (document.all){
                                    newY=parseInt(this.obj.style.pixelTop);
                                    newY+=percent;
                                    newY=newY;
                                    this.obj.style.pixelTop = newY;
                            }else{
                                    newY=parseInt(this.obj.style.top);
                                    newY+=percent;
                                    newY=newY+'px';
                                    this.obj.style.top = newY;
                            }

                            this.lastScrollY += percent;
                    }
            }
    }    
})(jQuery);