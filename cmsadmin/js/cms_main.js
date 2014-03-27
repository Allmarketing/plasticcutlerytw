// JavaScript Document

/* 勾選刪除時之檢查事項 */
function FormCheckDel(form) {  
    delchecked=0;
    if (form.total_box.value ==1 && form['id[]'].checked==true) {
        delchecked++;     
    } else if(form.total_box.value > 1) { 
    
        for (var i=0;i<form.total_box.value;i++) {
            var e = form['id[]'][i];
            if (e.checked==true) { 
                delchecked++;
            }   
        }
    }  
    if (delchecked==0) {
        alert("請勾選欲刪除的項目!!")
        return false;
    } else {
        Scheck = confirm("您確定要刪除嗎?\n\r如果刪除的是分類,則分類底下所屬的資料會全部清除!!") 
   
        if (Scheck==true) {
            form.method = "post";
            form.action = form.action + "?func=data_processing&process_type=del";
            form.submit();
            return true; 
        } 
    }       
}  
  
/* 勾選發送時之檢查事項 */
function FormCheckSend(form) {  
    sendchecked=0;
    if (form.total_box.value ==1 && form['id[]'].checked==true) {
        sendchecked++;     
    } else if(form.total_box.value > 1) { 
        for (var i=0;i<form.total_box.value;i++) {
            var e = form['id[]'][i];
            if (e.checked==true) { 
                sendchecked++; }   
        }
    } 
  
    if (sendchecked==0) {
        alert("請勾選欲發送的項目 !!")
        return false;
    } else if(sendchecked == 1) {
        form.method = "post";
        form.action = form.action + "?func=data_processing&process_type=send";
        form.submit();
        return true;
    } else {
        alert("執行發送時,只能單選 !!")
        return false;
    }  
} 

/* 勾選複製時之檢查事項 */
function FormCheckCopy(form) {  
   
    copychecked=0;
    if (form.total_box.value ==1 && form['id[]'].checked==true) {
        copychecked++;     
    } else if(form.total_box.value > 1) { 
        for (var i=0;i<form.total_box.value;i++) {
            var e = form['id[]'][i];
            if (e.checked==true) { 
                copychecked++; }   
        }
    } 
  
    if (copychecked==0) {
        alert("請勾選欲複製的項目 !!")
        return false;
    } else if(copychecked == 1) {
        form.method = "post";
        form.action = form.action + "?func=data_processing&process_type=copy";
        form.submit();
        return true;
    } else {
        alert("執行複製時，只能單選 !!")
        return false;
    }  
} 

/* 勾選啟用時之檢查事項 */
function FormCheckOn(form) {  

    onchecked=0;
    if (form.total_box.value ==1 && form['id[]'].checked==true) {
        onchecked++;     
    } else if(form.total_box.value > 1) { 
    
        for (var i=0;i<form.total_box.value;i++) {
            var e = form['id[]'][i];
            if (e.checked==true) { 
                onchecked++;
            }   
        }
    }  
  
    if (onchecked==0) {
        alert("請勾選欲啟用的項目!!")
        return false;
    } else {
        Scheck = confirm("您確定要將狀態設為啟用嗎 ?") 
   
        if (Scheck==true) {
            form.method = "post";
            form.action = form.action + "?func=data_processing&process_type=status&value=1";
            form.submit();
            return true; 
        } 
    }       
}  

/* 勾選停用時之檢查事項 */
function FormCheckOff(form) {  
   
    offchecked=0;
    if (form.total_box.value ==1 && form['id[]'].checked==true) {
        offchecked++;     
    } else if(form.total_box.value > 1) { 
    
        for (var i=0;i<form.total_box.value;i++) {
            var e = form['id[]'][i];
            if (e.checked==true) { 
                offchecked++;
            }   
        }
    }  
  
    if (offchecked==0) {
        alert("請勾選欲停用的項目!!")
        return false;
    } else {
        Scheck = confirm("您確定要將狀態設為停用嗎 ?") 
   
        if (Scheck==true) {
            form.method = "post";
            form.action = form.action + "?func=data_processing&process_type=status&value=0";
            form.submit();
            return true; 
        } 
    }       
}  
/* 勾選更改排序值之檢查事項 */
function FormCheckSort(form) {  
   
    sortchecked=0;
    if (form.total_box.value ==1 && form['id[]'].checked==true) {
        sortchecked++;     
    } else if(form.total_box.value > 1) { 
    
        for (var i=0;i<form.total_box.value;i++) {
            var e = form['id[]'][i];
            if (e.checked==true) { 
                sortchecked++;
            }   
        }
    }  
  
    if (sortchecked==0) {
        alert("請勾選欲更改排序值的項目!!")
        return false;
    } else {
        Scheck = confirm("您確定要更改排序值嗎 ?") 
   
        if (Scheck==true) {
            form.method = "post";
            form.action = form.action + "?func=data_processing&process_type=sort";
            form.submit();
            return true; 
        } 
    }       
} 
/* 勾選資料上鎖時之檢查事項 */
function FormCheckLock(form) {  

    onchecked=0;
    if (form.total_box.value ==1 && form['id[]'].checked==true) {
        onchecked++;     
    } else if(form.total_box.value > 1) { 
    
        for (var i=0;i<form.total_box.value;i++) {
            var e = form['id[]'][i];
            if (e.checked==true) { 
                onchecked++;
            }   
        }
    }  
  
    if (onchecked==0) {
        alert("請勾選欲上鎖的項目!!")
        return false;
    } else {
        Scheck = confirm("您確定要將資料上鎖嗎 ?") 
   
        if (Scheck==true) {
            form.method = "post";
            form.action = form.action + "?func=data_processing&process_type=lock&value=1";
            form.submit();
            return true; 
        } 
    }       
}
/* 勾選資料上鎖時之檢查事項 */
function FormCheckUnlock(form) {  

    onchecked=0;
    if (form.total_box.value ==1 && form['id[]'].checked==true) {
        onchecked++;     
    } else if(form.total_box.value > 1) { 
    
        for (var i=0;i<form.total_box.value;i++) {
            var e = form['id[]'][i];
            if (e.checked==true) { 
                onchecked++;
            }   
        }
    }  
  
    if (onchecked==0) {
        alert("請勾選欲解鎖的項目!!")
        return false;
    } else {
        Scheck = confirm("您確定要將資料解鎖嗎 ?") 
   
        if (Scheck==true) {
            form.method = "post";
            form.action = form.action + "?func=data_processing&process_type=lock&value=0";
            form.submit();
            return true; 
        } 
    }       
}  
/* 關鍵字搜尋 */
function FormCheckQuery(form) {  
    if (form.sk.value =="") {
        alert("請輸入查詢的關鍵字!!")
        form.sk.focus();
        return false;
    } else {
        form.method = "post";
        form.action = form.action + "?func="+form.ws_table.value+"_list";
        form.submit();
        return true; 
    }       
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

/* checkbox 全選/取消 */
function click_all(form) {
    checked = form.check_box?true:false;
    $(form).find(":checkbox").each( function() {
        if(checked){
            $(this).removeAttr("checked");
        }else{
            $(this).attr("checked",'true');
        }
    });
    form.check_box = checked?false:true;
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
function LoadDefaultPic(pic_valve,pic_preview){
    document.getElementById(pic_valve).value = "";
    document.getElementById(pic_preview).src = "images/ws-no-image.jpg";
}

//顯示新產品排序
function show_new_sort(form) {
    var new_p = form.p_type1;
    var new_p_sort = document.getElementById('p_new_sort');
    if(new_p.checked == true){
        new_p_sort.style.display = '';
    }else{
        new_p_sort.style.display = 'none';
    }
}
//選擇相關分類、產品、跨分類
function sel_related_items(ReturnID) {
    related_value = '';
	pc_name_str='';
    related_check = document.related_items.elements.length;
    for (i=0;i<related_check;i++){
        if ( document.related_items.elements[i].checked ) {
			pc_name ='pc'+ document.related_items.elements[i].value;
			pc_name_str += document.related_items.elements[i].value + '-' +document.getElementById(pc_name).value + '<br>';
            related_value += document.related_items.elements[i].value + ',';
        }else{
            related_value += '';
        }
    }
	related_value = related_value.substring(0,related_value.length -1 ); //去除最後一個,
	window.opener.document.getElementById("new_cate").innerHTML=pc_name_str;
    window.opener.document.getElementById(ReturnID).value = related_value;
    self.close();
}
function CheckNum(obj){
　var re = /^\d+$/;
　if (obj.value!="" && !re.test(obj.value)) {
　　alert("您必須輸入數字喔");
　　obj.focus();
　　return false;
　} else {
　　return true;
　}
}