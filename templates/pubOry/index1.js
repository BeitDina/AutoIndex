if(!document.getElementById)if(document.all)document.getElementById=document.all;
var orynider = new Object();
orynider.last = "17.01.2004";
orynider.isSlide=true;orynider.nav="";
orynider.oldY=0;
orynider.slidebutton=(document.getElementById&&window.setInterval ? '<input id="slider" type="checkbox" onclick="CheckSlide();" />Slide' : '');
readData();if(orynider.Data[0]!="orynider")orynider.Data=new Array("orynider","","",0);

window.onunload=writeData;

document.write(orynider.Data[1]);

function insertNavigator(l)
{
if(orynider.nav==""){
document.write('<div id="nav"><a href="../">Home</a>'+orynider.slidebutton+
N("l","portal/?page=2","")+
 N("h",0,"SITE")+
N("l","../contact.html#1","Contact")+
N("l","../guestb.html#1&from=0&to=100","GuestBook")+
N("l","../search.html#1","Search")+
 N("h",0,"INFO-NEWS")+
N("l","../download.html#1","Download")+
N("l","../trivory.html","TrivOry")+
N("l","../wangscript.html","WangScript")+
N("l","../news/index.html#1","New!")+
N("l","../news/index.htm#1","Nou!")+
N("l","http://orynider.3x.ro/top10/index.htm#1","Top10")+
 N("h",0,"PUBLIC")+
N("l","../pub/index.html?lang=en","Pub in En")+
N("l","../pub/index.htm?lang=ro","Pub în Ro")+
N("h",0,"INDEX:")+
 N("h",0,"&nbsp;"+document.lastModified)+
 orynider.nav+'</div>');
}
eval(orynider.Data[2]);
if(orynider.isSlide)window.setTimeout("slide()",30);
document.getElementById("slider").checked=orynider.isSlide;
window.setTimeout('document.getElementById("slider").checked=orynider.isSlide;',0);//Mozilla Fix
}

function slide()
{
if(orynider.isSlide)window.setTimeout("slide()",30);
var newY=0;
if(window.pageYOffset>0)newY=window.pageYOffset;
else if(document.body)
 if(document.body.scrollTop>0)newY=document.body.scrollTop;
 else if(document.documentElement)newY=document.documentElement.scrollTop;
if(newY!=orynider.oldY)
 {
 var percent = .1 * (newY - orynider.oldY);
 orynider.oldY += (percent > 0) ? Math.ceil(percent) : Math.floor(percent);
 if(navigator.userAgent.indexOf("Opera")>-1)document.getElementById("nav").style.top=orynider.oldY;
 else if(document.getElementById)document.getElementById("nav").style.top=orynider.oldY+"px";
 else document.nav.top = orynider.oldY;
 }
}

function CheckSlide()
{
orynider.isSlide=orynider.isSlide==false;
if(orynider.isSlide)window.setTimeout("slide()",10);
orynider.Data[2]="orynider.isSlide="+orynider.isSlide;
writeData();
document.getElementById("slider").checked=orynider.isSlide;
}

function N(section,url,text) //NavAdd
{
if(section=="h")return("<hr />"+text);
else return('<br />&nbsp;<a href="'+url+'">'+text+'</a>');
}

function writeData()
{
self.name=orynider.Data.join("]joe[");
}

function readData()
{
orynider.Data=(""+self.name).split("]joe[");
}