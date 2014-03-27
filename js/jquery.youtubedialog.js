jQuery.fn.extend({
    youtubedialog:function(options){
        var $ = jQuery;
        //預設影片相關選項
        var default_options = {
            autoImage:false,
            container_id: '#youtubedialog',
            container_width:520,
            container_height:420,
            container_title:'youtubedialog title',
            video_width:480,
            video_height:360,
        };
        //dialog的預設選項
        var default_dialog_options = {
           autoOpen:false, 
           modal:true,
           width:options.container_width,
           height:options.container_height,
           beforeClose:function(evt,ui){
               $(this).empty();
           }        
        };
        //合併預設選項
        var options = $.extend(default_options,options);
        //從合併後的options.dialog_options繼續設定dialog
        //預設選項沒有設定即為dialog本身的預設選項
        var dialog_options = $.extend(default_dialog_options,options.dialog_options);
        //將dialog容器先隱藏
        $(options.container_id).hide();
        //初始化dialog
        $(options.container_id).dialog(dialog_options);
        return this.each(function(k,obj){
            var videoId = $(obj).attr('href').substring(1);
            var title = $(obj).attr('title');
            if(options.autoImage){
                //自動加入圖片
                var img = $("<img/>");
                img.attr("src","http://i3.ytimg.com/vi/"+videoId+"/default.jpg");
                $(obj).append(img);                
            }
            $(obj).click(function(evt){
                evt.preventDefault(); 
                var embedVideo = $("<iframe></iframe>");
                embedVideo.attr("src","http://www.youtube.com/embed/"+videoId+"/?autoplay=1");
                embedVideo.width(options.video_width);
                embedVideo.height(options.video_height);
                embedVideo.css({border:"none"});
                //開啟dialog
                if(title){
                    $(options.container_id).dialog("option","title",title);
                }else{
                    $(options.container_id).dialog("option","title",options.container_title);
                }
                $(options.container_id).dialog("open").append(embedVideo);
            });
        });
    }
});


