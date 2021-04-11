/*new win*/
var win;var num=0;var name="nw";var d=document;var c;var wide=screen.width>=1024;var maxw=700;var def="100%";
function onw(url){var w=Math.round(screen.width*2/3);h=Math.round(screen.height*2/3); var l=screen.width-w-15;
var props="width="+w+",height="+h+",top=5,left="+l+",menubar=1,resizable=1,scrollbars=1,toolbar=1,location=1,status=1";
if (win==null||win.closed)win=window.open(url,name,props);else{var loc;try{loc=win.location.href;}catch(e){loc="";}if(loc.indexOf("ix.asp")!=-1)win=window.open(url,name,props);else{num++;name=name+num.toString();win=window.open(url,name,props);}}win.focus();}
/*about win*/
var hwin; function oaw(file){var chw=165;var h=screen.availHeight-35;var l=screen.availWidth-chw-5;if(hwin&&hwin!=null&&!hwin.closed){hwin.nav(file);tile(chw);hwin.focus();}else{hwin=open(file,"about","resizable=no,width="+chw+",top=0,height="+h+",left="+l);if(hwin.opener==null)hwin.opener=self;tile(chw);}/*open*/}
window.onunload=function(){if(hwin&&hwin!=null){hwin.close();max();hwin=null;}}
function tile(offset){window.resizeTo(screen.availWidth-offset-5, screen.availHeight);window.moveTo(0,0); return false;}/*tile*/
function max(){window.resizeTo(screen.availWidth, screen.availHeight);window.moveTo(0,0); return false;}/*maximize*/
/*input text*/
function sit(form, input, val){if (d.forms){var frm=d.forms[form];if(frm){var txt=frm.elements[input];if(txt){txt.value=val;if(txt.style)txt.style.fontStyle="italic";}}}}
function cit(txt, df){if(txt){if(txt.value==df){txt.value=txt.onfocus="";}}}
/*edrat*/
function dX(O){if(d.getElementById){var dC=d.getElementById("dratC").style;if(O.innerHTML=='Open'){O.innerHTML='Close';dC.height=300;window.scrollTo(0,dratY.offsetTop);}else{O.innerHTML='Open';dC.height=110;}}}
/*set max width*/
function smxw(){d=document;if(d.getElementById&&wide){if(c==null)c=d.getElementById("c");if(c!=null){var w=c.width;if(d.body.clientWidth>maxw){if(w!=maxw)c.width=maxw;else if(w!=def)c.width=def;}}}}