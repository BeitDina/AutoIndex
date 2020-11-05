// COOKIES
	// Switch state
	var colorswitch = $.cookie('mxpcolor');
	
	// Set the user's selection
	if (colorswitch == 'green') {
		$('.headerbar').css("background-color","#094F0E").css("background-image","-webkit-linear-gradient(top, #187E1F 0%, #094F0E 100%)").css("background-image","linear-gradient(to bottom, #187E1F 0%,#094F0E 100%)");
		$('.forabg, .forumbg').css("background-color","#094F0E").css("background-image","-webkit-linear-gradient(top, #529E57 0%, #094F0E 35px, #094F0E 100%)").css("background-image","linear-gradient(to bottom, #529E57 0%,#094F0E 35px,#094F0E 100%)");
		$('.pollbar1, .pollbar2, .pollbar3, .pollbar4').css("background-color","#094F0E");
		$('.pollbar5').css("background-color","#187E1F");
		$('.action-bar .coloredbutton').css("border-color","#094F0E").css("background-color","#094F0E").css("background-image","-webkit-linear-gradient(top, #187E1F 0%, #094F0E 100%)").css("background-image","linear-gradient(to bottom, #187E1F 0%,#094F0E 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#187E1F', endColorstr='#094F0E',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#094F0E").css("background-image","-webkit-linear-gradient(top, #094F0E 0%, #187E1F 100%)").css("background-image","linear-gradient(to bottom, #094F0E 0%,#187E1F 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#094F0E', endColorstr='#187E1F',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#094F0E").css("background-color","#094F0E").css("background-image","-webkit-linear-gradient(top, #187E1F 0%, #094F0E 100%)").css("background-image","linear-gradient(to bottom, #187E1F 0%,#094F0E 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#187E1F', endColorstr='#094F0E',GradientType=0 )");
		});
		$('.pagination li.active span').css("background","#187E1F").css("border-color","#187E1F");
		$('.pagination li a').mouseenter(function() {
			$(this).css("background","#187E1F").css("border-color","#187E1F");
		}).mouseleave(function() {
			$(this).css("background","#ECEDEE").css("border-color","#B4BAC0");
		});
    };
	
	if (colorswitch == 'pink') {
		$('.headerbar').css("background-color","#4F093E").css("background-image","-webkit-linear-gradient(top, #7E1866 0%, #4F093E 100%)").css("background-image","linear-gradient(to bottom, #7E1866 0%,#4F093E 100%)");
		$('.forabg, .forumbg').css("background-color","#4F093E").css("background-image","-webkit-linear-gradient(top, #9E528C 0%, #4F093E 35px, #4F093E 100%)").css("background-image","linear-gradient(to bottom, #9E528C 0%,#4F093E 35px,#4F093E 100%)");
		$('.pollbar1, .pollbar2, .pollbar3, .pollbar4').css("background-color","#4F093E");
		$('.pollbar5').css("background-color","#7E1866");
		$('.action-bar .coloredbutton').css("border-color","#4F093E").css("background-color","#4F093E").css("background-image","-webkit-linear-gradient(top, #7E1866 0%, #4F093E 100%)").css("background-image","linear-gradient(to bottom, #7E1866 0%,#4F093E 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#7E1866', endColorstr='#4F093E',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#4F093E").css("background-image","-webkit-linear-gradient(top, #4F093E 0%, #7E1866 100%)").css("background-image","linear-gradient(to bottom, #4F093E 0%,#7E1866 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#4F093E', endColorstr='#7E1866',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#4F093E").css("background-color","#4F093E").css("background-image","-webkit-linear-gradient(top, #7E1866 0%, #4F093E 100%)").css("background-image","linear-gradient(to bottom, #7E1866 0%,#4F093E 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#7E1866', endColorstr='#4F093E',GradientType=0 )");
		});
		$('.pagination li.active span').css("background","#7E1866").css("border-color","#7E1866");
		$('.pagination li a').mouseenter(function() {
			$(this).css("background","#7E1866").css("border-color","#7E1866");
		}).mouseleave(function() {
			$(this).css("background","#ECEDEE").css("border-color","#B4BAC0");
		});
    };
	
	if (colorswitch == 'purple') {
		$('.headerbar').css("background-color","#3C094F").css("background-image","-webkit-linear-gradient(top, #63187E 0%, #3C094F 100%)").css("background-image","linear-gradient(to bottom, #63187E 0%,#3C094F 100%)");
		$('.forabg, .forumbg').css("background-color","#3C094F").css("background-image","-webkit-linear-gradient(top, #89529E 0%, #3C094F 35px, #3C094F 100%)").css("background-image","linear-gradient(to bottom, #89529E 0%,#3C094F 35px,#3C094F 100%)");
		$('.pollbar1, .pollbar2, .pollbar3, .pollbar4').css("background-color","#3C094F");
		$('.pollbar5').css("background-color","#89529E");
		$('.action-bar .coloredbutton').css("border-color","#3C094F").css("background-color","#3C094F").css("background-image","-webkit-linear-gradient(top, #89529E 0%, #3C094F 100%)").css("background-image","linear-gradient(to bottom, #89529E 0%,#3C094F 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#89529E', endColorstr='#3C094F',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#3C094F").css("background-image","-webkit-linear-gradient(top, #3C094F 0%, #89529E 100%)").css("background-image","linear-gradient(to bottom, #3C094F 0%,#89529E 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#3C094F', endColorstr='#89529E',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#3C094F").css("background-color","#3C094F").css("background-image","-webkit-linear-gradient(top, #89529E 0%, #3C094F 100%)").css("background-image","linear-gradient(to bottom, #89529E 0%,#3C094F 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#89529E', endColorstr='#3C094F',GradientType=0 )");
		});
		$('.pagination li.active span').css("background","#89529E").css("border-color","#89529E");
		$('.pagination li a').mouseenter(function() {
			$(this).css("background","#89529E").css("border-color","#89529E");
		}).mouseleave(function() {
			$(this).css("background","#ECEDEE").css("border-color","#B4BAC0");
		});
    };
	
	if (colorswitch == 'black') {
		$('.headerbar').css("background-color","#3C094F").css("background-image","-webkit-linear-gradient(top, #89529E 0%, #3C094F 100%)").css("background-image","linear-gradient(to bottom, #63187E 0%,#3C094F 100%)");
		$('.forabg, .forumbg').css("background-color","#3C094F").css("background-image","-webkit-linear-gradient(top, #89529E 0%, #3C094F 35px, #3C094F 100%)").css("background-image","linear-gradient(to bottom, #89529E 0%,#3C094F 35px,#3C094F 100%)");
		$('.pollbar1, .pollbar2, .pollbar3, .pollbar4').css("background-color","#3C094F");
		$('.pollbar5').css("background-color","#89529E");
		$('.action-bar .coloredbutton').css("border-color","#3C094F").css("background-color","#3C094F").css("background-image","-webkit-linear-gradient(top, #89529E 0%, #3C094F 100%)").css("background-image","linear-gradient(to bottom, #89529E 0%,#3C094F 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#89529E', endColorstr='#3C094F',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#3C094F").css("background-image","-webkit-linear-gradient(top, #3C094F 0%, #89529E 100%)").css("background-image","linear-gradient(to bottom, #3C094F 0%,#89529E 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#3C094F', endColorstr='#89529E',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#3C094F").css("background-color","#3C094F").css("background-image","-webkit-linear-gradient(top, #89529E 0%, #3C094F 100%)").css("background-image","linear-gradient(to bottom, #89529E 0%,#3C094F 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#89529E', endColorstr='#3C094F',GradientType=0 )");
		});
		$('.pagination li.active span').css("background","#89529E").css("border-color","#89529E");
		$('.pagination li a').mouseenter(function() {
			$(this).css("background","#89529E").css("border-color","#89529E");
		}).mouseleave(function() {
			$(this).css("background","#ECEDEE").css("border-color","#B4BAC0");
		});
    };	
	
	if (colorswitch == 'red') {
		$('.headerbar').css("background-color","#4F0E09").css("background-image","-webkit-linear-gradient(top, #7E1F18 0%, #4F0E09 100%)").css("background-image","linear-gradient(to bottom, #7E1F18 0%,#4F0E09 100%)");
		$('.forabg, .forumbg').css("background-color","#4F0E09").css("background-image","-webkit-linear-gradient(top, #9E5752 0%, #4F0E09 35px, #4F0E09 100%)").css("background-image","linear-gradient(to bottom, #9E5752 0%,#4F0E09 35px,#4F0E09 100%)");
		$('.pollbar1, .pollbar2, .pollbar3, .pollbar4').css("background-color","#4F0E09");
		$('.pollbar5').css("background-color","#7E1F18");
		$('.action-bar .coloredbutton').css("border-color","#4F0E09").css("background-color","#4F0E09").css("background-image","-webkit-linear-gradient(top, #7E1F18 0%, #4F0E09 100%)").css("background-image","linear-gradient(to bottom, #7E1F18 0%,#4F0E09 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#7E1F18', endColorstr='#4F0E09',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#4F0E09").css("background-image","-webkit-linear-gradient(top, #4F0E09 0%, #7E1F18 100%)").css("background-image","linear-gradient(to bottom, #4F0E09 0%,#7E1F18 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#4F0E09', endColorstr='#7E1F18',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#4F0E09").css("background-color","#4F0E09").css("background-image","-webkit-linear-gradient(top, #7E1F18 0%, #4F0E09 100%)").css("background-image","linear-gradient(to bottom, #7E1F18 0%,#4F0E09 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#7E1F18', endColorstr='#4F0E09',GradientType=0 )");
		});
		$('.pagination li.active span').css("background","#7E1F18").css("border-color","#7E1F18");
		$('.pagination li a').mouseenter(function() {
			$(this).css("background","#7E1F18").css("border-color","#7E1F18");
		}).mouseleave(function() {
			$(this).css("background","#ECEDEE").css("border-color","#B4BAC0");
		});
    };

$(document).ready(function() {
// COLORSWITCH:

	// When blue is clicked:
    $('.colorblue').click(function() {
		$('.headerbar').css("background-color","#09334F").css("background-image","-webkit-linear-gradient(top, #18557E 0%, #09334F 100%)").css("background-image","linear-gradient(to bottom, #18557E 0%,#09334F 100%)");
		$('.forabg, .forumbg').css("background-color","#09334F").css("background-image","-webkit-linear-gradient(top, #52809E 0%, #09334F 35px, #09334F 100%)").css("background-image","linear-gradient(to bottom, #52809E 0%,#09334F 35px,#09334F 100%)");
		$('.pollbar1, .pollbar2, .pollbar3, .pollbar4').css("background-color","#09334F");
		$('.pollbar5').css("background-color","#18557E");
		$('.action-bar .coloredbutton').css("border-color","#09334F").css("background-color","#09334F").css("background-image","-webkit-linear-gradient(top, #18557E 0%, #09334F 100%)").css("background-image","linear-gradient(to bottom, #18557E 0%,#09334F 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#18557E', endColorstr='#09334F',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#18557E").css("background-image","-webkit-linear-gradient(top, #09334F 0%, #18557E 100%)").css("background-image","linear-gradient(to bottom, #09334F 0%,#18557E 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#09334F', endColorstr='#18557E',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#09334F").css("background-color","#09334F").css("background-image","-webkit-linear-gradient(top, #18557E 0%, #09334F 100%)").css("background-image","linear-gradient(to bottom, #18557E 0%,#09334F 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#18557E', endColorstr='#09334F',GradientType=0 )");
		});
		$('.pagination li.active span').css("background","#31678B").css("border-color","#31678B");
		$('.pagination li a').mouseenter(function() {
			$(this).css("background","#31678B").css("border-color","#31678B");
		}).mouseleave(function() {
			$(this).css("background","#ECEDEE").css("border-color","#B4BAC0");
		});
		$.cookie('mxpcolor', null, { path: '/' });
    });
	
	// When green is clicked:
    $('.colorgreen').click(function() {
		$('.headerbar').css("background-color","#094F0E").css("background-image","-webkit-linear-gradient(top, #187E1F 0%, #094F0E 100%)").css("background-image","linear-gradient(to bottom, #187E1F 0%,#094F0E 100%)");
		$('.forabg, .forumbg').css("background-color","#094F0E").css("background-image","-webkit-linear-gradient(top, #529E57 0%, #094F0E 35px, #094F0E 100%)").css("background-image","linear-gradient(to bottom, #529E57 0%,#094F0E 35px,#094F0E 100%)");
		$('.pollbar1, .pollbar2, .pollbar3, .pollbar4').css("background-color","#094F0E");
		$('.pollbar5').css("background-color","#187E1F");
		$('.action-bar .coloredbutton').css("border-color","#094F0E").css("background-color","#094F0E").css("background-image","-webkit-linear-gradient(top, #187E1F 0%, #094F0E 100%)").css("background-image","linear-gradient(to bottom, #187E1F 0%,#094F0E 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#187E1F', endColorstr='#094F0E',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#094F0E").css("background-image","-webkit-linear-gradient(top, #094F0E 0%, #187E1F 100%)").css("background-image","linear-gradient(to bottom, #094F0E 0%,#187E1F 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#094F0E', endColorstr='#187E1F',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#094F0E").css("background-color","#094F0E").css("background-image","-webkit-linear-gradient(top, #187E1F 0%, #094F0E 100%)").css("background-image","linear-gradient(to bottom, #187E1F 0%,#094F0E 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#187E1F', endColorstr='#094F0E',GradientType=0 )");
		});
		$('.pagination li.active span').css("background","#187E1F").css("border-color","#187E1F");
		$('.pagination li a').mouseenter(function() {
			$(this).css("background","#187E1F").css("border-color","#187E1F");
		}).mouseleave(function() {
			$(this).css("background","#ECEDEE").css("border-color","#B4BAC0");
		});
		$.cookie('mxpcolor', 'green', { expires: 365, path: '/' });
    });
	
	// When pink is clicked:
    $('.colorpink').click(function() {
		$('.headerbar').css("background-color","#4F093E").css("background-image","-webkit-linear-gradient(top, #7E1866 0%, #4F093E 100%)").css("background-image","linear-gradient(to bottom, #7E1866 0%,#4F093E 100%)");
		$('.forabg, .forumbg').css("background-color","#4F093E").css("background-image","-webkit-linear-gradient(top, #9E528C 0%, #4F093E 35px, #4F093E 100%)").css("background-image","linear-gradient(to bottom, #9E528C 0%,#4F093E 35px,#4F093E 100%)");
		$('.pollbar1, .pollbar2, .pollbar3, .pollbar4').css("background-color","#4F093E");
		$('.pollbar5').css("background-color","#7E1866");
		$('.action-bar .coloredbutton').css("border-color","#4F093E").css("background-color","#4F093E").css("background-image","-webkit-linear-gradient(top, #7E1866 0%, #4F093E 100%)").css("background-image","linear-gradient(to bottom, #7E1866 0%,#4F093E 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#7E1866', endColorstr='#4F093E',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#4F093E").css("background-image","-webkit-linear-gradient(top, #4F093E 0%, #7E1866 100%)").css("background-image","linear-gradient(to bottom, #4F093E 0%,#7E1866 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#4F093E', endColorstr='#7E1866',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#4F093E").css("background-color","#4F093E").css("background-image","-webkit-linear-gradient(top, #7E1866 0%, #4F093E 100%)").css("background-image","linear-gradient(to bottom, #7E1866 0%,#4F093E 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#7E1866', endColorstr='#4F093E',GradientType=0 )");
		});
		$('.pagination li.active span').css("background","#7E1866").css("border-color","#7E1866");
		$('.pagination li a').mouseenter(function() {
			$(this).css("background","#7E1866").css("border-color","#7E1866");
		}).mouseleave(function() {
			$(this).css("background","#ECEDEE").css("border-color","#B4BAC0");
		});
		$.cookie('mxpcolor', 'pink', { expires: 365, path: '/' });
    });
	
	// When purple is clicked:
    $('.colorpurple').click(function() {
		$('.headerbar').css("background-color","#3C094F").css("background-image","-webkit-linear-gradient(top, #63187E 0%, #3C094F 100%)").css("background-image","linear-gradient(to bottom, #63187E 0%,#3C094F 100%)");
		$('.forabg, .forumbg').css("background-color","#3C094F").css("background-image","-webkit-linear-gradient(top, #89529E 0%, #3C094F 35px, #3C094F 100%)").css("background-image","linear-gradient(to bottom, #89529E 0%,#3C094F 35px,#3C094F 100%)");
		$('.pollbar1, .pollbar2, .pollbar3, .pollbar4').css("background-color","#3C094F");
		$('.pollbar5').css("background-color","#89529E");
		$('.action-bar .coloredbutton').css("border-color","#3C094F").css("background-color","#3C094F").css("background-image","-webkit-linear-gradient(top, #89529E 0%, #3C094F 100%)").css("background-image","linear-gradient(to bottom, #89529E 0%,#3C094F 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#89529E', endColorstr='#3C094F',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#3C094F").css("background-image","-webkit-linear-gradient(top, #3C094F 0%, #89529E 100%)").css("background-image","linear-gradient(to bottom, #3C094F 0%,#89529E 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#3C094F', endColorstr='#89529E',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#3C094F").css("background-color","#3C094F").css("background-image","-webkit-linear-gradient(top, #89529E 0%, #3C094F 100%)").css("background-image","linear-gradient(to bottom, #89529E 0%,#3C094F 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#89529E', endColorstr='#3C094F',GradientType=0 )");
		});
		$('.pagination li.active span').css("background","#89529E").css("border-color","#89529E");
		$('.pagination li a').mouseenter(function() {
			$(this).css("background","#89529E").css("border-color","#89529E");
		}).mouseleave(function() {
			$(this).css("background","#ECEDEE").css("border-color","#B4BAC0");
		});
		$.cookie('mxpcolor', 'purple', { expires: 365, path: '/' });
    });
	
	// When red is clicked:
    $('.colorred').click(function() {
		$('.headerbar').css("background-color","#4F0E09").css("background-image","-webkit-linear-gradient(top, #7E1F18 0%, #4F0E09 100%)").css("background-image","linear-gradient(to bottom, #7E1F18 0%,#4F0E09 100%)");
		$('.forabg, .forumbg').css("background-color","#4F0E09").css("background-image","-webkit-linear-gradient(top, #9E5752 0%, #4F0E09 35px, #4F0E09 100%)").css("background-image","linear-gradient(to bottom, #9E5752 0%,#4F0E09 35px,#4F0E09 100%)");
		$('.pollbar1, .pollbar2, .pollbar3, .pollbar4').css("background-color","#4F0E09");
		$('.pollbar5').css("background-color","#7E1F18");
		$('.action-bar .coloredbutton').css("border-color","#4F0E09").css("background-color","#4F0E09").css("background-image","-webkit-linear-gradient(top, #7E1F18 0%, #4F0E09 100%)").css("background-image","linear-gradient(to bottom, #7E1F18 0%,#4F0E09 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#7E1F18', endColorstr='#4F0E09',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#4F0E09").css("background-image","-webkit-linear-gradient(top, #4F0E09 0%, #7E1F18 100%)").css("background-image","linear-gradient(to bottom, #4F0E09 0%,#7E1F18 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#4F0E09', endColorstr='#7E1F18',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#4F0E09").css("background-color","#4F0E09").css("background-image","-webkit-linear-gradient(top, #7E1F18 0%, #4F0E09 100%)").css("background-image","linear-gradient(to bottom, #7E1F18 0%,#4F0E09 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#7E1F18', endColorstr='#4F0E09',GradientType=0 )");
		});
		$('.pagination li.active span').css("background","#7E1F18").css("border-color","#7E1F18");
		$('.pagination li a').mouseenter(function() {
			$(this).css("background","#7E1F18").css("border-color","#7E1F18");
		}).mouseleave(function() {
			$(this).css("background","#ECEDEE").css("border-color","#B4BAC0");
		});
		$.cookie('mxpcolor', 'red', { expires: 365, path: '/' });
    });
});