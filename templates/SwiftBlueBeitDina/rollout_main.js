function rollup_contract_main(in_buttonSwitch, in_listID, path)
{
	var in_listID;
	
    if (document.getElementsByName) {
		var listID = document.getElementsByName(in_listID);
    }
    else {
        return;
    }

	for (var i=0;i<listID.length;i++)
	{
    	if (listID[i].style.display == '') {
        	listID[i].style.display = 'none';
       	 	in_buttonSwitch.innerHTML = '<img src="' + path + 'expand.gif" border="0" />';
			var send_cookie = 0;
    	}
    	else {
        	listID[i].style.display = '';
        	in_buttonSwitch.innerHTML = '<img src="' + path + 'contract.gif" border="0" />';
			var send_cookie = 1;
    	}

    	if (window.event) {
        	window.event.cancelBubble=true;
    	}
	}
    
	if (send_cookie == 1) {
     	rollup_record_state_main(in_listID, 1);
    }
    else {
       	rollup_record_state_main(in_listID, 0);
    }
	
	
}

function rollup_record_state_main(in_listID, status) 
{
    var expDate = new Date();
    // expires in 1 year
    expDate.setTime(expDate.getTime() + 31536000000);
    document.cookie = in_listID + "=" + escape(status) + "; expires=" + expDate.toGMTString();
}
