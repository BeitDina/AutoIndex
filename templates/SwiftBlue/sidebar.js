$(document).ready(function() {
// SIDEBARBUTTON:
    $('.handle').click(function() {
		$(".sidebarclosed").css("display","none");
		$('.chatsidebar').css("display","block");
		$('.wrap').css("padding","47px 220px 15px 15px");
		$.cookie('forumbooksidebar', 'open', { expires: 365, path: '/' });
    });

	$('.sidebarhandle').click(function() {
		$(".sidebarclosed").css("display","block");
		$('.chatsidebar').css("display","none");
		$('.wrap').css("padding","47px 15px 15px");
		$.cookie('forumbooksidebar', null, { path: '/' });
    });

// COOKIES
	// Switch state
	var sidebarswitch = $.cookie('forumbooksidebar');
	// Set the user's selection
	if (sidebarswitch == 'open') {
		$(".sidebarclosed").css("display","none");
		$('.chatsidebar').css("display","block");
		$('.wrap').css("padding","47px 220px 15px 15px");
    };
});