// JavaScript Document

/* 使用方法

$(".class or #id").menu_box({
	TOP : 30, //選單上方位置
	LEFT : 0, //選單左方位置
	Z_INDEX : 10, //選單Z軸位置
	ALIGN : "center", //文字對齊 (left,center,right)
	BG : "#333", //背景顏色
	COLOR : "#FFF", //文字顏色
	CH_BG : "#EEE", //反白背景顏色
	CH_COLOR : "#333", //反白文字顏色
});

*/

(function($){
	$.fn.menu_box = function(OPTION){
		var MENU = jQuery.extend({
			TOP : 30, //選單上方位置
			LEFT : 0, //選單左方位置
			Z_INDEX : 10, //選單Z軸位置
			ALIGN : "left", //文字對齊 (left,center,right)
			BG : "#333", //背景顏色
			COLOR : "#FFF", //文字顏色
			CH_BG : "#EEE", //反白背景顏色
			CH_COLOR : "#333", //反白文字顏色
			
			//----
			TIMER : 0,
		}, OPTION);
		
		var THIS = this;
		
		//初始化
		THIS.parent("*").css({
			"position":"relative",
		});
		
		THIS.css({
			"position":"absolute",
			"top":MENU.TOP +"px",
			"left":MENU.LEFT +"px",
			"z-index":MENU.Z_INDEX,
			"list-style":"none",
			"padding":"0",
			"margin":"0",
			"display":"none",
		});
		
		THIS.find("li").css({
			"white-space":"nowrap",
			"text-align":MENU.ALIGN,
			"padding":"5px 15px",
			"background":MENU.BG,
			"position":"relative",
		});
		
		THIS.find("li > a").css({
			"color":MENU.COLOR,
			"text-decoration":"none",
		});
		
		THIS.find("ul").each(function(){
			$(this).css({
				"position":"absolute",
				"top":"0",
				"left":"100%",
				"z-index":MENU.Z_INDEX,
				"list-style":"none",
				"padding":"0",
				"margin":"0",
				"display":"none",
			});
		});
		
		THIS.find("li").hover(function(){
			$(this).children("a").css({
				"color":MENU.CH_COLOR, //可選
			});
			
			$(this).css({
				"background":MENU.CH_BG, //可選
			});
		},function(){
			$(this).children("a").css({
				"color":MENU.COLOR,
			});
			
			$(this).css({
				"background":MENU.BG,
			});
		});
		
		function menu_delay(){
			MENU.TIMER = setTimeout(menu_hide,1000);
		}
		
		function menu_hide(){
			THIS.hide();
		}	
		
		//執行
		THIS.parent("*").mousemove(function(){
			if(typeof(MENU.TIMER) != "undefined"){
				clearTimeout(MENU.TIMER);
			}
			
			THIS.hide();
			$(this).find(THIS).show();
		});
		
		THIS.parent("*").mouseout(function(){
			menu_delay();
		});
		
		THIS.find("li").mousemove(function(){
			clearTimeout(MENU.TIMER);
		});
		
		THIS.mouseleave(function(){
			$(this).hide();
		});
		
		
		THIS.find("ul").parent("li").mousemove(function(){
			$(this).children("ul").show();
		});
		
		THIS.find("ul").parent("li").mouseleave(function(){
			$(this).children("ul").hide();
		});
	};
})(jQuery);