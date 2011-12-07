$(document).ready(function () {
	//$('#mainMenu').ptMenu();

	$('.menu-active-item').parent().parent().slideDown('slow');
	
	$('a.jsPopupMenuLink').click(function (e) {
		var t = $(this);
		
		if(t.hasClass('activePopupItem')) {
			t.removeClass('activePopupItem');
			$('#popupMenuContainer').hide();
		}
		else {
			$('a.activePopupItem').removeClass('activePopupItem');
			t.addClass('activePopupItem');
			
			var target = t.attr('href').substring(1);
			
			$('#popupMenuContainer').children('ul').hide();
			$('#' + target).show();
			$('#popupMenuContainer').show();
		}
		
		return false;
	});
	
	$('body').bind('click',function (e) {
		$('#popupMenuContainer').hide();
		$('a.activePopupItem').removeClass('activePopupItem');
	});
});