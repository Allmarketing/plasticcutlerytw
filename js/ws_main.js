// JavaScript Document
//只能輸入數字跟刪除鍵
function CheckNum(e){
    var key = window.event ? e.keyCode : e.which;
	var keychar = String.fromCharCode(key);
	reg = /\d/;
	return reg.test(keychar);
}

// 開啟檔案管理視窗
function upload_window(action_file,return_id){   
    var new_width  = 720;
    var new_height = 500;
    //var scr_width  = screen.availWidth;
    //var scr_height = screen.availHeight;
    //var old_width  = scr_width - new_width;
    //var old_height = scr_height;
    //var space = 0 - window.screenLeft ;
    //window.resizeTo(old_width,old_height);
    //window.moveBy(space,0);
    window.open(action_file + '&return_id=' + return_id ,'','width='+new_width+',height='+new_height+',scrollbars=yes,status=yes');
}

function resize_opener(){
    var scr_width  = screen.availWidth;
    var scr_height = screen.availHeight;
    window.opener.resizeTo(scr_width,scr_height);
}

function ConfirmMSG(msg,url){
    var conf = confirm(msg);
    if(conf){
        location.href= url;
        return true;
    }else{
        return false;
    }
}
function MM_openBrWindow(theURL,winName,features) { //v2.0
    window.open(theURL,winName,features);
}
function popinquiry(txt){
    var url="cart.php?func=s_inquiry&p_id=" + txt;
    wo=window.open(url,"Inquiry","toolbar=no,top=200,left=400,width=600,height=420");
    wo.focus();
}
//加入我的最愛
function addBookmarkForBrowser(sTitle, sUrl) {
// Returns the version of Internet Explorer or a -1
// (indicating the use of another browser).
    if(window.sidebar && window.sidebar.addPanel){
        window.sidebar.addPanel(sTitle, sUrl, "");
    }else if(window.external) {
        try{
            window.external.AddFavorite(sUrl, sTitle);
        }catch(e){
            alert('Please help yourself to add this page into bookmarks');         
        }
    }else{
        alert('Please help yourself to add this page into bookmarks');         
    }
}
/* 勾選詢價車時之檢查事項 */
function CheckProducts_eng(form) {  
    onchecked=0;
    if (form.total_box.value ==1 && form['p_id[]'].checked==true) {
        onchecked++;     
    } else if(form.total_box.value > 1) { 

        for (var i=0;i<form.total_box.value;i++) {
            var e = form['p_id[]'][i];
            if (e.checked==true) { 
                onchecked++;
            }   
        }
    }  
    if(onchecked==0) {
        alert("Please check the product!!")
        return false;
    }else{
        form.submit();
        return true;    
    }
}  
function CheckProducts_cht(form) {  
    onchecked=0;
    if (form.total_box.value ==1 && form['p_id[]'].checked==true) {
        onchecked++;     
    } else if(form.total_box.value > 1) { 
        for (var i=0;i<form.total_box.value;i++) {
            var e = form['p_id[]'][i];
            if (e.checked==true) { 
                onchecked++;
            }   
        }
    }  
    if(onchecked==0) {
        alert("請勾選產品!!")
        return false;
    }else{
        form.submit();
        return true;    
    }
}  
/* 詢價車&購物車 CONTINUE更新數量 */  
function cart_continue(obj,url) {
  obj.submit();
  location.href=url;
}
/* 詢價車&購物車 FINISH更新數量 */
function cart_finish(obj) {
  obj.submit();
  location.href='cart.php?func=c_finish';    
}
//設定cookie
function setCookie(c_name,value,exdays){
    var exdate=new Date();
    exdate.setDate(exdate.getDate() + exdays);
    var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
    document.cookie=c_name + "=" + c_value;
}
//讀取cookie
function getCookie(c_name){
    var c_value = document.cookie;
    var c_start = c_value.indexOf(" " + c_name + "=");
    if (c_start == -1){
        c_start = c_value.indexOf(c_name + "=");
    }
    if (c_start == -1) {
        c_value = null;
    }else{
        c_start = c_value.indexOf("=", c_start) + 1;
        var c_end = c_value.indexOf(";", c_start);
        if (c_end == -1){
            c_end = c_value.length;
        }
        c_value = unescape(c_value.substring(c_start,c_end));
    }
    return c_value;
}