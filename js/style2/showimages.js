$(document).ready(function(){
	/* This code is executed after the DOM has been completely loaded */
	
	var totWidth=0;
	var positions = new Array();
	
	$('#p_slides .p_slide').each(function(i){
		
		/* Traverse through all the slides and store their accumulative widths in totWidth */
		
		positions[i]= totWidth;
		totWidth += $(this).width();
		
		/* The positions array contains each slide's commulutative offset from the left part of the container */
		
		if(!$(this).width())
		{
			alert("Please, fill in width & height for all your images!");
			return false;
		}
		
	});
	
	$('#p_slides').width(totWidth);

	/* Change the cotnainer div's width to the exact width of all the slides combined */

	$('#p_menu ul li a').click(function(e){

			/* On a thumbnail click */

			$('li.normal').removeClass('act').addClass('inact');
			$(this).parent().addClass('act');
			
			var pos = $(this).parent().prevAll('.normal').length;
			
			$('#p_slides').stop().animate({marginLeft:-positions[pos]+'px'},450);
			/* Start the sliding animation */
			
			e.preventDefault();
			/* Prevent the default action of the link */
	});
	
	$('#p_menu ul li.normal:first').addClass('act').siblings().addClass('inact');
	/* On page load, mark the first thumbnail as active */
	
});