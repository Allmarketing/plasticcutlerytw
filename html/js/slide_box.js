// JavaScript Document

/* 使用方法

$("#id").slide_box({
	SHOW_NUM : 3, //一次顯示數量
	OUTER_WIDTH : 20, //額外間距
	ACT_TIMER : 1000, //動作間隔時間
	POSITION : 0, // 起始位置
	AUTO : true, // true => 自動動作 , false => 手動動作
	WIDTH : 200, // 圖片大小
	HOVER : false, // 滑鼠hover停止動作 , true => 停止 , false => 不停止
	CYCLE : true, // 循環 / 回放切換 , true => 循環 , false => 回放
	VERTICAL : false, //移動方向 , true => 垂直 , false => 水平
	AFTER :  function() { function_name() } // 動作後執行擴充
});

*/


(function($){
	$.fn.slide_box = function(OPTION){
		var SLIDE = jQuery.extend({
			SHOW_NUM : 3, //一次顯示數量
			OUTER_WIDTH : 20, //額外間距
			ACT_TIMER : 3000, //動作間隔時間
			POSITION : 0, //起始位置
			AUTO : true, // true => 自動動作 , false => 手動動作
			WIDTH : 200, //圖片大小
			HOVER : false, // 滑鼠hover停止動作 , true => 停止 , false => 不停止
			CYCLE : false, // 循環 / 回放切換 , true => 循環 , false => 回放
			VERTICAL : false, //移動方向 , true => 垂直 , false => 水平
			AFTER :  function() {  }, // 動作後執行擴充
			
			//----
			NUM : "",
			TIMER : 0,
		}, OPTION);
		
		var THIS = this;
		SLIDE.NUM = THIS.find(".slide_pic").length;
		
		function DELAY(){
			SLIDE.TIMER = setTimeout(MOVE,SLIDE.ACT_TIMER);
		}
		
		function MOVE(STEP){
			var IMG_W = SLIDE.WIDTH - -SLIDE.OUTER_WIDTH;
			
			var SLIDER_INDEX = THIS.find(".slide_pic.current").removeClass("current").index();
			
			if(SLIDE.CYCLE == true){
				var LIMIT_NUM = SLIDE.NUM;
			}else{
				var LIMIT_NUM = SLIDE.NUM - SLIDE.SHOW_NUM;
			}
			
			switch(STEP){
				case 1:
					var SLIDER_MOVE = SLIDER_INDEX - 1;
				break;
				default:
					var SLIDER_MOVE = SLIDER_INDEX - -1;
				break;
			}
			
			if(SLIDE.VERTICAL == true){
				if(SLIDER_MOVE <= LIMIT_NUM && SLIDER_MOVE >= 0){
					THIS.find(".slide_move").stop().animate({ "top":"-"+ SLIDER_MOVE * IMG_W +"px" },function(){
						SLIDE.AFTER();
					});
				}else{
					if(SLIDE.CYCLE == true && SLIDER_MOVE > 0){
						clearTimeout(SLIDE.TIMER);
						THIS.find(".slide_pic:eq(0)").addClass("current");
						THIS.find(".slide_move").stop().animate({ "top":"0px" },0);
						MOVE();
						return false;
					}else{
						THIS.find(".slide_move").stop().animate({ "top":"0px" });
						SLIDER_MOVE = 0;
					}
				}
			}else{
				if(SLIDER_MOVE <= LIMIT_NUM && SLIDER_MOVE >= 0){
					THIS.find(".slide_move").stop().animate({ "left":"-"+ SLIDER_MOVE * IMG_W +"px" },function(){
						SLIDE.AFTER();
					});
				}else{
					if(SLIDE.CYCLE == true && SLIDER_MOVE > 0){
						clearTimeout(SLIDE.TIMER);
						THIS.find(".slide_pic:eq(0)").addClass("current");
						THIS.find(".slide_move").stop().animate({ "left":"0px" },0);
						MOVE();
						return false;
					}else{
						THIS.find(".slide_move").stop().animate({ "left":"0px" });
						SLIDER_MOVE = 0;
					}
				}
			}
			
			THIS.find(".slide_pic:eq("+ SLIDER_MOVE +")").addClass("current");
			
			SLIDE.POSITION = 0;
			
			if(SLIDE.AUTO == true){
				DELAY();
			}
		}
		
		function KEY_MOVE(KEY){
			var IMG_W = SLIDE.WIDTH - -SLIDE.OUTER_WIDTH;
			var HOVER_CK = THIS.find(".slide_pic.key").length;
			THIS.find(".slide_pic.current").removeClass("current");
			
			if(SLIDE.VERTICAL == true){
				THIS.find(".slide_move").stop().animate({ "top":"-"+ KEY * IMG_W +"px" },function(){
					SLIDE.AFTER();
				});
			}else{
				THIS.find(".slide_move").stop().animate({ "left":"-"+ KEY * IMG_W +"px" },function(){
					SLIDE.AFTER();
				});
			}
			
			THIS.find(".slide_pic:eq("+ KEY +")").addClass("current");
			
			SLIDE.POSITION = 0;
			
			if(SLIDE.AUTO == true && (HOVER_CK <= 0 && SLIDE.HOVER || !SLIDE.HOVER)){
				DELAY();
			}
		}
		
		function CURRENT_MOVE(INDEX,FUNC_SWITCH){
			var SLIDER_INDEX = THIS.find(".slide_pic.current").removeClass("current").index();
			
			if(FUNC_SWITCH > 0){
				THIS.find(".slide_pic:eq("+ INDEX +")").addClass("current");
			}else{
				switch(INDEX){
					case 1:
						var SLIDER_MOVE = SLIDER_INDEX - 1;
					break;
					default:
						var SLIDER_MOVE = SLIDER_INDEX - -1;
					break;
				}
				
				if(SLIDER_MOVE == SLIDE.NUM){
					SLIDER_MOVE = 0;
				}
				
				THIS.find(".slide_pic:eq("+ SLIDER_MOVE +")").addClass("current");
			}
			
			SLIDE.AFTER();
		}
		
		return this.each(function(){
			var IMG_W = SLIDE.WIDTH - -SLIDE.OUTER_WIDTH;
			
			if(SLIDE.CYCLE == true && SLIDE.NUM > SLIDE.SHOW_NUM){
				//var COPY_PIC = THIS.find(".slide_move").html(); //before
				var COPY_PIC = THIS.find(".slide_move").children().clone(true); //mod by Xin 2013-10-11
				
				THIS.find(".slide_move").append(COPY_PIC);
			}
			
			THIS.find(".slide_pic").each(function(KEY){
				if(SLIDE.POSITION > 0 && (SLIDE.POSITION - 1) == KEY && SLIDE.NUM > SLIDE.SHOW_NUM || SLIDE.POSITION == 0 && KEY == 0 || SLIDE.NUM < SLIDE.SHOW_NUM && KEY == 0){
					$(this).addClass("current");
				}
				
				if(SLIDE.VERTICAL == true){
					if(SLIDE.POSITION > 0 && (SLIDE.POSITION - 1) == KEY && SLIDE.NUM > SLIDE.SHOW_NUM){
						THIS.find(".slide_move").css({ "top":"-"+ IMG_W * KEY +"px"});
					}
					
					$(this).css({ "top":IMG_W * KEY +"px" });
				}else{
					if(SLIDE.POSITION > 0 && (SLIDE.POSITION - 1) == KEY && SLIDE.NUM > SLIDE.SHOW_NUM){
						THIS.find(".slide_move").css({ "left":"-"+ IMG_W * KEY +"px"});
					}
					
					$(this).css({ "left":IMG_W * KEY +"px" });
				}
			});
						
			if(SLIDE.AUTO == true && SLIDE.NUM > SLIDE.SHOW_NUM){
				DELAY();
			}
			
			THIS.find(".arrow").click(function(E){
				E.preventDefault();
				
				var ARROW_INDEX = THIS.find(".arrow").index(this) - 1;
				
				if(SLIDE.AUTO == true){
					clearTimeout(SLIDE.TIMER);
				}
				
				if(SLIDE.NUM > SLIDE.SHOW_NUM){
					MOVE(-ARROW_INDEX);
				}else{
					//?
					CURRENT_MOVE(-ARROW_INDEX,0);
				}
			});
			
			THIS.find(".key").click(function(E){
				E.preventDefault();
				
				var REL_VAL = $(this).attr("rel");				
				
				if(typeof(REL_VAL) != "undefined"){
					var KEY_INDEX = REL_VAL - 1;
				}else{
					var KEY_INDEX = THIS.find(".key").index(this);
				}
				
				if(KEY_INDEX > (SLIDE.NUM - 1)){
					KEY_INDEX = 0;
				}
				
				if(SLIDE.AUTO == true){
					clearTimeout(SLIDE.TIMER);
				}
				
				if(SLIDE.NUM > SLIDE.SHOW_NUM){
					KEY_MOVE(KEY_INDEX);
				}else{
					//?
					CURRENT_MOVE(KEY_INDEX,1);
				}
			});
			
			if(SLIDE.HOVER == true && SLIDE.AUTO == true){
				THIS.find(".slide_pic").hover(function(){
					clearTimeout(SLIDE.TIMER);
				},function(){
					DELAY();
				});
			}
		});
	};
})(jQuery);
