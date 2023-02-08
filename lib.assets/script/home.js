function slideInDetail(selector, direction)
{
	var object = $(selector);
	var objectToSlide = $(selector).find('.shortcut-detail');
	var width = object.width();
	var height = object.height();
	objectToSlide.css({width:width+'px', height:height+'px'}).attr('data-sliding', 'true');
	if(direction == 'DOWN')
	{
		objectToSlide.css({'margin-left':0, 'margin-top':-height+'px', 'display':'block'}).animate({'margin-top':'0px'}, 300, 'swing', function(e){
			$(this).attr('data-sliding', 'false');//.removeClass('after-sliding').addClass('after-sliding').removeAttr('style');
		});
	}
	else if(direction == 'UP')
	{
		objectToSlide.css({'margin-left':0, 'margin-top':height+'px', 'display':'block'}).animate({'margin-top':'0px'}, 300, 'swing', function(e){
			$(this).attr('data-sliding', 'false');//.removeClass('after-sliding').addClass('after-sliding').removeAttr('style');
		});
	}
	else if(direction == 'LEFT')
	{
		objectToSlide.css({'margin-left':width+'px', 'margin-top':0, 'display':'block'}).animate({'margin-left':'0px'}, 300, 'swing', function(e){
			$(this).attr('data-sliding', 'false');//.removeClass('after-sliding').addClass('after-sliding').removeAttr('style');
		});
	}
	else if(direction == 'RIGHT')
	{
		objectToSlide.css({'margin-left':-width+'px', 'margin-top':0, 'display':'block'}).animate({'margin-left':'0px'}, 300, 'swing', function(e){
			$(this).attr('data-sliding', 'false');//.removeClass('after-sliding').addClass('after-sliding').removeAttr('style');
		});
	}
	else
	{
		objectToSlide.css({'margin-left':-width+'px', 'margin-top':0, 'display':'block'}).animate({'margin-left':'0px'}, 300, 'swing', function(e){
			$(this).attr('data-sliding', 'false').removeClass('after-sliding').addClass('after-sliding').removeAttr('style');
		});
	}
	
}
function slideOutDetail(selector, direction)
{
	var object = $(selector);
	var objectToSlide = $(selector).find('.shortcut-detail');
	var width = object.width();
	var height = object.height();
	objectToSlide.css({width:width+'px', height:height+'px'});
	if(direction == 'DOWN')
	{
		objectToSlide.animate({'margin-top':height+'px'}, 300, 'swing', function(e){
			//$(this).removeClass('after-sliding').addClass('after-sliding').css({'margin':'0','display':'block'});
		});
	}
	else if(direction == 'UP')
	{
		objectToSlide.animate({'margin-top':-height+'px'}, 300, 'swing', function(e){
			//$(this).removeClass('after-sliding').addClass('after-sliding').css({'margin':'0','display':'block'});
		});
	}
	else if(direction == 'LEFT')
	{
		objectToSlide.animate({'margin-left':-width+'px'}, 300, 'swing', function(e){
			//$(this).removeClass('after-sliding').addClass('after-sliding').css({'margin':'0','display':'block'});
		});
	}
	else if(direction == 'RIGHT')
	{
		objectToSlide.animate({'margin-left':width+'px'}, 300, 'swing', function(e){
			//$(this).removeClass('after-sliding').addClass('after-sliding').css({'margin':'0','display':'block'});
		});
	}
	else
	{
		objectToSlide.animate({'margin-left':width+'px'}, 300, 'swing', function(e){
			//$(this).removeClass('after-sliding').addClass('after-sliding').css({'margin':'0','display':'block'});
		});
	}
}
$(document).ready(function(e) {
    $(document).on('mouseenter', '.flexbox-container > div', function(e){
		if($(window).width() >= 920)
		{
		var ofssetX = $(this).offset().left;
		var ofssetY = $(this).offset().top;
		var width = $(this).width();
		var height = $(this).height();
		
		var st = $(document).scrollTop();
		var sl = $(document).scrollLeft();
		var startX = e.clientX - ofssetX + sl;
		var startY = e.clientY - ofssetY + st;
		var direction = 'RIGHT';
		if(startY > (height - 20))
		{
			direction = 'UP';
		}
		else if(startX > (width - 20))
		{
			direction = 'LEFT';
		}
		else if(startY < 20)
		{
			direction = 'DOWN';
		}
		else if(startY < 20)
		{
			direction = 'RIGHT';
		}
		else 
		{
			direction = 'RIGHT';
		}
		slideInDetail($(this), direction);
		}
	});
    $(document).on('mouseleave', '.flexbox-container > div > .shortcut-detail', function(e){
		if($(window).width() >= 920)
		{
		var ofssetX = $(this).offset().left;
		var ofssetY = $(this).offset().top;
		var width = $(this).width();
		var height = $(this).height();
		
		var st = $(document).scrollTop();
		var sl = $(document).scrollLeft();
		var startX = e.clientX - ofssetX + sl;
		var startY = e.clientY - ofssetY + st;
		var direction = 'RIGHT';
		if(startY > (height - 20))
		{
			direction = 'DOWN';
		}
		else if(startX > (width - 20))
		{
			direction = 'RIGHT';
		}
		else if(startY < 20)
		{
			direction = 'UP';
		}
		else if(startX < 20)
		{
			direction = 'LEFT';
		}
		else 
		{
			direction = 'LEFT';
		}
		var sliding = $(this).attr('data-sliding') || 'false';
		if(sliding != 'true')
		{
			slideOutDetail($(this).parent(), direction);
		}
		}
	});
	
	$(document).on('click', '.mobile-menu-trigger-left', function(e){
		$('.menu-right').removeClass('show-on-mobile').removeAttr('style');
		$('.menu-left').toggleClass('show-on-mobile');
		if($('.menu-left').hasClass('show-on-mobile'))
		{
			$('.menu-left').slideDown(140, 'swing', function(e){
				$(this).css({'display':'inline-block'});
			});
		}
		else
		{
			$('.menu-left').slideUp(140, 'swing', function(e){
				$('.menu-left').removeClass('show-on-mobile').css({'display':''});
			});
		}
		e.preventDefault();
	});
	$(document).on('click', '.mobile-menu-trigger-right', function(e){
		$('.menu-left').removeClass('show-on-mobile').removeAttr('style');
		$('.menu-right').toggleClass('show-on-mobile');
		if($('.menu-right').hasClass('show-on-mobile'))
		{
			$('.menu-right').slideDown(140, 'swing', function(e){
				$(this).css({'display':'inline-block'});
			});
		}
		else
		{
			$('.menu-right').slideUp(140, 'swing', function(e){
				$('.menu-right').removeClass('show-on-mobile').css({'display':''});
			});
		}
		e.preventDefault();
	});
	$(window).resize(function(e) {
        $('.shortcut-detail').removeAttr('style');
    });
});