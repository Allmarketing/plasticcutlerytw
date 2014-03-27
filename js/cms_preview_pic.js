var n = document.layers;
var ie = document.all;
var n6 = document.getElementById&&!document.all;
function onImageSelect(obj,img){
    imgPath = obj.value;        
    if (obj.value == "") {
        return false;
    }
    //if(ie) {
       document.getElementById(img).src= imgPath;
    //}       
}           
function isValidImageExt(imageName) {
    if (imageName.substring(imgPath.length - 3, imageName.length).toLowerCase() != "jpg" && imageName.substring(imgPath.length - 3, imageName.length).toLowerCase() != "gif" && imageName.substring(imgPath.length - 3, imageName.length).toLowerCase() != "png" && imageName.substring(imgPath.length - 4, imageName.length).toLowerCase() != "jpeg") {
            if (imageName.substring(imgPath.length - 3, imageName.length).toLowerCase() == "swf" && 0 ){
                return true;
            }else{
                return false;
            }           
    }else{
        return true;
    }
}
function ChangeAdFileType(type){
    if(type=="image"){
        document.getElementById('ad_file_type_image').style.display='';
        document.getElementById('ad_file_type_flash').style.display='none';
        document.getElementById('ad_file_type_txt').style.display='none';
    }
    if(type=="flash"){
        document.getElementById('ad_file_type_image').style.display='none';
        document.getElementById('ad_file_type_flash').style.display='';
        document.getElementById('ad_file_type_txt').style.display='none';
    }
    if(type=="txt"){
        document.getElementById('ad_file_type_image').style.display='none';
        document.getElementById('ad_file_type_flash').style.display='none';
        document.getElementById('ad_file_type_txt').style.display='';
    }
}
