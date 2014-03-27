(function($){
    $.fn.scrollPanner = function(){
//	$('#products').hover(function(){
//		clearTimeout(TIMER);
//	}, function(){
//		TIMER=setTimeout("timedCount()",5000);
//	});
	
	var SINGLE_WIDTH = 152; //單筆數像素寬度 (包含margin & padding)
	var SHOW_NUM = 4; //顯示多少筆數
	var LEFT_SET = 0;
	var ID_NUM = $(this[0]).find('#move_box .other-prodact-list').length; //計算總筆數
	var PAGE_NUM = Math.ceil(ID_NUM / SHOW_NUM);
	var PAGE_WIDTH = SINGLE_WIDTH * SHOW_NUM; //單頁像素寬度
	var LAST_PX = PAGE_WIDTH - PAGE_WIDTH * PAGE_NUM;
	var container = $(this[0]);
        
	container.find('#move_box .other-prodact-list').each(function(){
		$(this).css({ "left": LEFT_SET });
		LEFT_SET = LEFT_SET + SINGLE_WIDTH;
	});
	
	container.find('#arrow_2').click(function(E){
		E.preventDefault();
		var LI_POT = container.find('#move_box').position().left;
		var CK_POT = Math.ceil(LI_POT / - PAGE_WIDTH);
		var CURT_POT = - (CK_POT * PAGE_WIDTH);
		if(CURT_POT <= LAST_PX){
			var MOVE_R = 0;
		}else{
			var MOVE_R = CURT_POT - PAGE_WIDTH;
		}
		container.find('#move_box').animate({ "left":MOVE_R +"px" });
	});
	container.find('#arrow_1').click(function(E){
		E.preventDefault();
		var LI_POT = container.find('#move_box').position().left;
		var CK_POT = Math.ceil(LI_POT / - PAGE_WIDTH);
		var CURT_POT = - (CK_POT * PAGE_WIDTH);
		if(CURT_POT >= 0){
			var MOVE_L = 0;
		}else{
			var MOVE_L = CURT_POT + PAGE_WIDTH;
		}
		container.find('#move_box').animate({ "left":MOVE_L +"px" });
	});        
    }
})(jQuery);