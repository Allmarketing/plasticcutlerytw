(function($){
    $.fn.extend({
       showImages : function(settings){        
            var _settings = {
                img_limit:20,    //最多幾個圖
                img_height:272,  //大圖的高度
                act_mode:"",      //換圖模式;changeMode:直接換；預設為輪動    
                container_id : 'ifocus',
                piclist_id : 'ifocus_piclist',
                btn_id : 'ifocus_btn',
                txt_id : 'ifocus_tx',
                btn_container : "li",
                txt_container : 'li',
                current_className : 'current',
                normal_className : 'normal',
                scrolling: false,
                on:'click'
            };    

            $.extend(_settings,settings?settings:{});

            function moveElement(elementID,final_x,final_y,interval,caller) {
                if (!caller) return false;
                if ($(caller).find("#"+elementID).size()==0) return false;
                var elem = $(caller).find("#"+elementID)[0];
                if (elem.movement) {
                    clearTimeout(elem.movement);
                }
                if (!elem.style.left) {
                    elem.style.left = "0px";
                }
                if (!elem.style.top) {
                    elem.style.top = "0px";
                }
                var xpos = parseInt(elem.style.left);
                var ypos = parseInt(elem.style.top);
                if (xpos == final_x && ypos == final_y) {
                    return true;
                }
                if (xpos < final_x) {
                    var dist = Math.ceil((final_x - xpos)/10);
                    xpos = xpos + dist;
                }
                if (xpos > final_x) {
                    var dist = Math.ceil((xpos - final_x)/10);
                    xpos = xpos - dist;
                }
                if (ypos < final_y) {
                    var dist = Math.ceil((final_y - ypos)/10);
                    ypos = ypos + dist;
                }
                if (ypos > final_y) {
                    var dist = Math.ceil((ypos - final_y)/10);
                    ypos = ypos - dist;
                }
                if(_settings.act_mode==""){
                    elem.style.left = xpos + "px";
                    elem.style.top = ypos + "px";
                }else if(_settings.act_mode=="switch"){
                    elem.style.left = final_x + "px";
                    elem.style.top = final_y + "px";
                }
                elem.movement = setTimeout(function(){
                    moveElement(elementID,final_x,final_y,interval,caller);
                },interval);
            }

            function classNormal(iFocusBtnID,iFocusTxID,caller){
                var iFocusBtns= $(caller).find("#"+iFocusBtnID).find(_settings.btn_container);
                var iFocusTxs = $(caller).find("#"+iFocusTxID).find(_settings.txt_container);
                for(var i=0; i<iFocusBtns.size(); i++) {
                    iFocusBtns[i].className = iFocusBtns[i].className.replace(_settings.current_className,_settings.normal_className);
                    iFocusTxs[i].className = iFocusTxs[i].className.replace(_settings.current_className,_settings.normal_className);
                }
            }

            function classCurrent(iFocusBtnID,iFocusTxID,n,caller){
                var iFocusBtns= $(caller).find("#"+iFocusBtnID).find(_settings.btn_container);
                var iFocusTxs = $(caller).find("#"+iFocusTxID).find(_settings.txt_container);
                if(iFocusBtns[n])iFocusBtns[n].className = iFocusBtns[n].className.replace(_settings.normal_className,_settings.current_className);
                if(iFocusTxs[n])iFocusTxs[n].className = iFocusTxs[n].className.replace(_settings.normal_className,_settings.current_className);
            }

            function iFocusChange(caller) {
                if($(caller).find('#'+_settings.container_id).size()==0) return false;
                $(caller).find('#'+_settings.container_id).mouseover( function(){caller.atuokey = true});
                $(caller).find('#'+_settings.container_id).mouseout(function(){caller.atuokey = false});
                $(caller).find('#'+_settings.btn_id).mouseover(function(){caller.atuokey = true});
                $(caller).find('#'+_settings.btn_id).mouseout( function(){caller.atuokey = false});
                var iFocusBtns = $(caller).find('#'+_settings.btn_id).find(_settings.btn_container);
                var listLength = iFocusBtns.size();
                for(var i=0;i<_settings.img_limit;i++){
                    if(listLength>=(i+1)) {
                         iFocusBtns[i].index = i;
                         $(iFocusBtns[i]).bind(_settings.on, function(e) {
                             if($(this).is('a'))e.preventDefault();
                             var index = this.index;
                             moveElement(_settings.piclist_id,0,-_settings.img_height*index,5,caller);
                             classNormal(_settings.btn_id,_settings.txt_id,caller);
                             classCurrent(_settings.btn_id,_settings.txt_id,index,caller);
                         });
                    }
                }
            }                
            var container = this;
            var autoiFocus = function(index) {
                caller = container[index];
                //沒有按鈕就清除intervalId, 修改於2012.11.02
                if(!$(caller).find("#"+_settings.btn_id).find(_settings.btn_container).size()){
                    clearInterval(caller.intervalId);
                    return false;
                }                
                if(!caller) return false;
                if(caller.atuokey) return false;
                if($(caller).parent().css("display")=="none")return false;
                caller.focusBtnList = $(caller).find('#'+_settings.btn_id).find(_settings.btn_container);
                caller.listLength = caller.focusBtnList.size();
                for(var i=0; i<caller.listLength; i++) {
                    if (caller.focusBtnList[i].className.indexOf(_settings.current_className)!==-1){
                        caller.currentNum = i;
                        break;
                    }
                }
                caller.new_y = 0;
                if(caller.currentNum < caller.listLength-1){
                    caller.new_y = 0-_settings.img_height*(caller.currentNum+1);
                    moveElement(_settings.piclist_id,0,caller.new_y,5,caller);
                    classNormal(_settings.btn_id,_settings.txt_id,caller);
                    classCurrent(_settings.btn_id,_settings.txt_id,caller.currentNum+1,caller);        
                }else{
                    caller.new_y = 0;
                    moveElement(_settings.piclist_id,0,caller.new_y,5,caller);
                    classNormal(_settings.btn_id,_settings.txt_id,caller);
                    classCurrent(_settings.btn_id,_settings.txt_id,0,caller);        
                }            
            }   
            $.extend({
                autoiFocus:autoiFocus
            });

            for(var i=0;i<this.size();i++){
                 var wrapper = this[i];
                 iFocusChange(wrapper);
                 if(_settings.scrolling){
                     wrapper.atuokey = false;
                     wrapper.intervalId = setInterval("jQuery.autoiFocus("+i+")",10000);
                 }
            }
            return this;
       }
    });
})(jQuery);
