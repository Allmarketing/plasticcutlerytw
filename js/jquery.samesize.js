/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
(function($){
    $.fn.extend({
        samesize:function(params){
            function sameheight(caller){
                this.height=0;
                var obj = this;
                caller.each(function(){
                     var tmp_height = $(this).height();
                     if(tmp_height>obj.height)obj.height = tmp_height;
                }); 
                caller.each(function(){
                     $(this).height(obj.height); 
                });
            }
            function samewidth(caller){
                this.width=0;
                var obj = this;
                caller.each(function(){
                     var tmp_width = $(this).width();
                     if(tmp_width>obj.width)obj.width = tmp_width;
                }); 
                caller.each(function(){
                     $(this).width(obj.width); 
                });
                
            }
            function _parseOpt(opt){
                var tmp_str = opt.split(',');
                var count_obj = {width:0,height:0}
                var ans = [];
                for(var i=0;i<tmp_str.length;i++){
                    var tmp = $.trim(tmp_str[i]).toLowerCase();
                    switch(tmp){
                        case "width":
                            if(count_obj.width==0){
                                count_obj.width++;
                                ans[ans.length]="width";
                            }
                            break;
                        case "height":
                            if(count_obj.height==0){
                                count_obj.height++;
                                ans[ans.length]="height";
                            }
                            break;
                    }
                }
                return ans;              
            }
            var opt = _parseOpt(params);
            for(var j=0;j<opt.length;j++){
                switch(opt[j]){
                    case "width":
                        new samewidth(this);
                        break;
                    case "height":
                        new sameheight(this);
                        break;
                }
            }
            return this;
        }
    });
})(jQuery);

