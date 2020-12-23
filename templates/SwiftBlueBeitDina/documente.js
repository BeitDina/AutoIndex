var displaymode=0
var iframecode='<iframe id="external" style="width:512px; height:2400px" src="block_10.php"></iframe>'

if (displaymode==0)
document.write(iframecode)

function gone(){
var selectedurl=document.jumpy.example.options[document.jumpy.example.selectedIndex].value
if (document.getElementById&&displaymode==0)
document.getElementById("external").src=selectedurl
else if (document.all&&displaymode==0)
document.all.external.src=selectedurl
else{
if (!window.win2||win2.closed)
win2=window.open(selectedurl)
//else if win2 already exists
else{
win2.location=selectedurl
win2.focus()
}
}
}