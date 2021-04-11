<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html dir="ltr xmlns="http://www.w3.org/1999/xhtml" xml:lang="{config:language}">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta name="title"       content="Your site's title" />
<meta name="author"      content="Your name or your company name" />
<meta name="copyright"   content="Add some copyright notice here" />
<meta name="keywords"    content="Add all your keywords in here (separate each keyword by a comma and space)" />
<meta name="description" lang="{config:language}" content="This is the description search engines show when listing your site." />
<meta name="category"    content="general" />
<meta name="robots"      content="index,follow" />
<meta name="revisit-after" content="7 days" >

<link rel="top" href="http://bibliuta.lx.ro/forum/index.php" title="Pagina de start a forumului Bibliuta.Lx.Ro" />
<link rel="search" href="http://bibliuta.lx.ro/forum/search.php" title="Cãutare" />
<link rel="help" href="http://bibliuta.lx.ro/forum/faq.php" title="FAQ" />
<link rel="author" href="http://bibliuta.lx.ro/forum/memberlist.php" title="Membri" />

<title>{words:index of} {info:dir} @ #Bibliuta</title>
<!-- First load standard template *.css definition, located in the the phpbb template folder -->
<link rel="stylesheet" href="{config:template}SwiftBlue.css" type="text/css" >
<!-- Then load addon template *.css definition for mx, located in the the portal template folder -->
<link rel="stylesheet" href="{config:template}mx_addon.css" type="text/css" >

<!-- Optionally, redefine some defintions for gecko browsers -->
<style type="text/css">
<!--
/*
  SwiftBlue Theme for phpBB
  Created by BitByBit, using subSilver Theme as a base.
  http://www.bitbybit.f2s.com

*/

/* General page style. The scroll bar colours only visible in IE5.5+ */
body {
	background-color: #7EB5E8;
	padding:0px; scrollbar-face-color: #BADBF5;
	scrollbar-highlight-color: #E3F0FB;
	scrollbar-shadow-color: #BADBF5;
	scrollbar-3dlight-color: #80BBEC;
	scrollbar-arrow-color:  #072978;
	scrollbar-track-color: #DAECFA;
	scrollbar-darkshadow-color: #4B8DF1;
	BACKGROUND: url('{config:template}images/images/backgroundbluelight.gif');
	COLOR: #000000;
	font-style:normal; font-variant:normal; font-weight:normal; font-size:10pt; font-family:Fixedsys, geneva, lucida, lucida grande, arial, helvetica, sans-serif; margin-left:10px; margin-right:10px; margin-top:5px; margin-bottom:10px
}

/* General font families for common tags */
font, th, td, p { font-family: Fixedsys, Arial, Helvetica, sans-serif }
a:link, a:active, a:visited { color : #072978; }
a:hover		{ text-decoration: underline; color : #041642; }
hr	{ height: 0px; border: solid #80BBEC 0px; border-top-width: 1px;}

/* This is the border line & background colour round the entire page */
.bodyline	{ background-color: #E3F0FB; border: 1px #4B8DF1 solid; }

/* This is the outline round the main forum tables */
.forumline	{ background-color: #E3F0FB; border: 2px #006699 solid; }

/* Main table cell colours and backgrounds */
td.row1	{ background-color: #DAECFA; }
td.row2	{ background-color: #BADBF5; }
td.row3	{ background-color: #80BBEC; }

/*
  This is for the table cell above the Topics, Post & Last posts on the http://bibliuta.lx.ro/forum/index.php page
  By default this is the fading out gradiated silver background.
  However, you could replace this with a bitmap specific for each forum
*/
td.rowpic {
		background-color: #E3F0FB;
		background-image: url('{config:template}images/cellpic2.jpg');
		background-repeat: repeat-y
}

/* Header cells - the blue and silver gradient backgrounds */
th	{
	color: #051D41; font-size: 11px; font-weight : bold;
	background-color: #072978; height: 25px;
	background-image: url('{config:template}images/cellpic3.gif');
}

td.cat, td.catHead, td.catSides, td.catLeft, td.catRight, td.catBottom {
			background-image: url('{config:template}images/cellpic1.gif');
			background-color:#80BBEC; border: medium solid #FFFFFF; height: 28px
}

/*
  Setting additional nice inner borders for the main table cells.
  The names indicate which sides the border will be on.
  Don't worry if you don't understand this, just ignore it :-)
*/
td.cat, td.catHead, td.catBottom {
	height: 29px;
	border-width: 0px 0px 0px 0px;
}
th.thHead, th.thSides, th.thTop, th.thLeft, th.thRight, th.thBottom, th.thCornerL, th.thCornerR {
	font-weight: bold; border: #E3F0FB; border-style: solid; height: 28px;
}
td.row3Right, td.spaceRow {
	background-color: #80BBEC; border: #FFFFFF; border-style: solid;
}

th.thHead, td.catHead { font-size: 12px; border-width: 1px 1px 0px 1px; }
th.thSides, td.catSides, td.spaceRow	 { border-width: 0px 1px 0px 1px; }
th.thRight, td.catRight, td.row3Right	 { border-width: 0px 1px 0px 0px; }
th.thLeft, td.catLeft	  { border-width: 0px 0px 0px 1px; }
th.thBottom, td.catBottom  { border-width: 0px 1px 1px 1px; }
th.thTop	 { border-width: 1px 0px 0px 0px; }
th.thCornerL { border-width: 1px 0px 0px 1px; }
th.thCornerR { border-width: 1px 1px 0px 0px; }

/* The largest text used in the index page title and toptic title etc. */
.maintitle	{
	font-weight: bold; font-size: 22px; font-family: "Trebuchet MS",Fixedsys, Arial, Helvetica, sans-serif;
	text-decoration: none; line-height : 120%; color : #000000;
}

/* General text */
.gen { font-size : 12px; }
.genmed { font-size : 11px; }
.gensmall { font-size : 10px; }
.gen, .genmed, .gensmall { color : #000000; }
a.gen, a.genmed, a.gensmall { color: #072978; text-decoration: none; }
a.gen:hover, a.genmed:hover, a.gensmall:hover	{ color: #041642; text-decoration: underline; }

/* The register, login, search etc links at the top of the page */
.mainmenu		{ font-size : 11px; color : #000000; font-family: "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif; }
a.mainmenu		{ text-decoration: none; color : #072978; font-family: "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif; }
a.mainmenu:hover{ text-decoration: underline; color : #041642; font-family: "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif; }

/* Forum category titles */
.cattitle		{ font-weight: bold; font-size: 12px ; letter-spacing: 1px; color : #072978}
a.cattitle		{ text-decoration: none; color : #072978; }
a.cattitle:hover{ text-decoration: underline; }

/* Forum title: Text and link to the forums used in: http://bibliuta.lx.ro/forum/index.php */
.forumlink		{ font-weight: bold; font-size: 12px; color : #072978; }
a.forumlink 	{ text-decoration: none; color : #072978; }
a.forumlink:hover{ text-decoration: underline; color : #041642; }

/* Used for the navigation text, (Page 1,2,3 etc) and the navigation bar when in a forum */
.nav			{ font-weight: bold; font-size: 11px; color : #000000;}
a.nav			{ text-decoration: none; color : #072978; }
a.nav:hover		{ text-decoration: underline; }

/* titles for the topics: could specify viewed link colour too */
.topictitle, h1, h2	{ font-weight: bold; font-size: 11px; color : #000000; }
a.topictitle:link   { text-decoration: none; color : #072978; }
a.topictitle:visited { text-decoration: none; color : #072978; }
a.topictitle:hover	{ text-decoration: underline; color : #041642; }

/* Name of poster in viewmsg.php and viewtopic.php and other places */
.name			{ font-size : 11px; color : #000000;}

/* Location, number of posts, post date etc */
.postdetails		{ font-size : 10px; color : #000000; }

/* The content of the posts (body of text) */
.postbody { font-size : 12px; line-height: 18px}
a.postlink:link	{ text-decoration: none; color : #072978 }
a.postlink:visited { text-decoration: none; color : #072978; }
a.postlink:hover { text-decoration: underline; color : #041642}

/* Quote & Code blocks */
.code {
	font-family: Courier, 'Courier New', sans-serif; font-size: 11px; color: #006600;
	background-color: #FAFAFA; border: #80BBEC; border-style: solid;
	border-left-width: 1px; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px
}

.quote {
	font-family: Fixedsys, Arial, Helvetica, sans-serif; font-size: 11px; color: #444444; line-height: 125%;
	background-color: #FAFAFA; border: #80BBEC; border-style: solid;
	border-left-width: 1px; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px
}

/* Copyright and bottom info */
.copyright		{ font-size: 10px; font-family: Fixedsys, Arial, Helvetica, sans-serif; color: #444444; letter-spacing: -1px;}
a.copyright		{ color: #444444; text-decoration: none;}
a.copyright:hover { color: #000000; text-decoration: underline;}

/* Form elements */
input, textarea, select {
	color : #000000;
	font: normal 11px Fixedsys, Arial, Helvetica, sans-serif;
	border-color : #000000;
}

/* The text input fields background colour */
input.post, textarea.post, select {
	background-color : #E3F0FB;
}

input { text-indent : 2px; }

/* The buttons used for bbCode styling in message post */
input.button {
	background-color : #DAECFA;
	color : #000000;
	font-size: 11px; font-family: Fixedsys, Arial, Helvetica, sans-serif;
}

/* The main submit button option */
input.mainoption {
	background-color : #FAFAFA;
	font-weight : bold;
}

/* None-bold submit button */
input.liteoption {
	background-color : #FAFAFA;
	font-weight : normal;
}

/* This is the line in the posting page which shows the rollover
  help line. This is actually a text box, but if set to be the same
  colour as the background no one will know ;)
*/
.helpline { background-color: #BADBF5; border-style: none; }

/* Import the fancy styles for IE only (NS4.x doesn't use the @import function) */
@import url("{config:template}formIE.css");
-->
</style>

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

function set_mx_cookie(in_listID, status)
{
    var expDate = new Date();
    // expires in 1 year
    expDate.setTime(expDate.getTime() + 31536000000);
    document.cookie = in_listID + "=" + escape(status) + "; expires=" + expDate.toGMTString();
}

function set_phpbb_cookie(cookieName, cookieValue, lifeTime, path, domain, isSecure)
{
    var expDate = new Date();
    // expires in 1 year
    expDate.setTime(expDate.getTime() + 31536000000);

	document.cookie = escape( cookieName ) + "=" + escape( cookieValue ) +
		";expires=" + expDate.toGMTString() +
		( path ? ";path=" + path : "") + ( domain ? ";domain=" + domain : "") +
		( isSecure == 1 ? ";secure" : "");
}

function checkSearch()
{
	if (document.search_block.search_engine.value == 'google')
	{
		window.open('http://www.google.com/search?q=' + document.search_block.search_keywords.value, '_google', '');
		return false;
	}
	else if (document.search_block.search_engine.value == 'site')
	{
		window.open('http://bibliuta.lx.ro/forum/index.php?page=5&mode=results&search_terms=all&search_keywords=' + document.search_block.search_keywords.value, '_self', '');
		return false;
	}
	else if (document.search_block.search_engine.value == 'kb')
	{
		window.open('http://bibliuta.lx.ro/forum/index.php?page=&mode=search&search_terms=all&search_keywords=' + document.search_block.search_keywords.value, '_self', '');
		return false;
	}
	else if (document.search_block.search_engine.value == 'pafiledb')
	{
		window.open('http://bibliuta.lx.ro/forum/index.php?page=&action=search&search_terms=all&search_keywords=' + document.search_block.search_keywords.value, '_self', '');
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
<script language="javascript" type="text/javascript" src="{config:template}rollout.js"></script>
<script language="javascript" type="text/javascript" src="{config:template}rollout_main.js"></script>
<script language="javascript" type="text/javascript" src="{config:template}dynifs.js"></script>
</head>
<body class="autoindex_body" bgcolor="#7EB5E8" text="#000000" link="#072978" vlink="#072978">
