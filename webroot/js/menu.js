$(document).ready(function () {
	$('#mainMenu').ptMenu();
	
	$('.jsConfigMenuLink').bind('click',function (e) {
		e.preventDefault();
		$('#configMenuContainer').toggle();
		return false;
	});
	
	$('body').bind('click',function (e) {
		$('#configMenuContainer').hide();
	});
});