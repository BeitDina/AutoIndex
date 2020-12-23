function rollup_contract(in_buttonSwitch, in_listID, path)
{
    if (document.getElementById) {
        listID = document.getElementById(in_listID);
    }
    else {
        return;
    }

    if (listID.style.display == '') {
        listID.style.display = 'none';
        in_buttonSwitch.innerHTML = '<img src="' + path + 'expand.gif" border="0" />';
        rollup_record_state(in_listID, 0);
    }
    else {
        listID.style.display = '';
        in_buttonSwitch.innerHTML = '<img src="' + path + 'contract.gif" border="0" />';
        rollup_record_state(in_listID, 1);
    }

    if (window.event) {
        window.event.cancelBubble=true;
    }
}

function rollup_record_state(in_listID, status) 
{
    var expDate = new Date();
    // expires in 1 year
    expDate.setTime(expDate.getTime() + 31536000000);
    document.cookie = in_listID + "=" + escape(status) + "; expires=" + expDate.toGMTString();
}
