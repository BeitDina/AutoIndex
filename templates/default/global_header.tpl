<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{config:language}">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	
	<meta name="title"       content="Directory Index" />
	<meta name="author"      content="Justin Hagstrom <JustinHagstrom@yahoo.com>" />
	<meta name="copyright"   content="Justin Hagstrom <JustinHagstrom@yahoo.com>" />
	<meta name="keywords"    content="Directory, Index, PHP, Apache" />
	<meta name="description" lang="{config:language}" content="Directory Index. This is the description search engines show when listing your site." />
	<meta name="category"    content="general" />
	<meta name="robots"      content="index,follow" />
	<meta name="revisit-after" content="7 days" >
	
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="blue" />
 	
	<link rel="shortcut icon" href="favicon.ico" />
	<link rel="icon" href="favicon.gif" type="image/gif" />
	
	<link href="{config:template}default.css" rel="stylesheet" title="AutoIndex Default" type="text/css" />
	<link href="{config:template}alternate.css" rel="alternate stylesheet" title="AutoIndex Alternate" type="text/css" />
	
	<link href="{config:assets_path}css/font-awesome.min.css" rel="stylesheet" />
	<link href="{config:assets_path}css/ionicons.min.css" rel="stylesheet" />	
	
	<title>{words:index of} {info:dir}</title>	

	<script language="javascript" type="text/javascript"><!--

function newImage(arg) {
	if (document.images) {
		rslt = new Image();
		rslt.src = arg;
		return rslt;
	}
}

function changeImages() {
	if (document.images ) {
		for (var i=0; i<changeImages.arguments.length; i+=2) {
			document[changeImages.arguments[i]].src = changeImages.arguments[i+1];
		}
	}
}

function checkSearch()
{
	if (document.search_block.search_engine.value == 'google')
	{
		window.open('http://www.google.com/search?q=' + document.search_block.search_keywords.value, '_google', '');
		return false;
	}
	else
	{
		return true;
	}
}

function full_img(url) {
	var url = url;
	window.open(url,'','scrollbars=1,toolbar=0,resizable=1,menubar=0,directories=0,status=0, width=img.width, height=img.height');
	return;
}
// --></script>
<script type="text/javascript" src="{config:template}rollout.js"></script>
<script type="text/javascript" src="{config:template}rollout_main.js"></script>
<script type="text/javascript" src="{config:template}dynifs.js"></script>
</head>

<body class="autoindex_body">
