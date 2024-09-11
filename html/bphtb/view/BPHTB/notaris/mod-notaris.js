function addEvent(obj, evType, fn){ 
	if (obj.addEventListener){ 
   		obj.addEventListener(evType, fn, false); 
   		return true; 
 	} else if (obj.attachEvent){ 
   		var r = obj.attachEvent("on"+evType, fn); 
   		return r; 
 	} else { 
   		return false; 
 	} 
}

addEvent(window, 'load', initCheckboxes);

function initCheckboxes() {
	if (document.getElementById('all')!=null) addEvent(document.getElementById('all'), 'click', setCheckboxes);
}

function setCheckboxes() {
	var cb = document.getElementById('container').getElementsByTagName('input');
		
	for (var i = 0; i < cb.length; i++) {
		cb[i].checked = document.getElementById('all').checked;
	}
}

function getCheckedValue(buttonGroup,draf) {
   // Go through all the check boxes. return an array of all the ones
   // that are selected (their position numbers). if no boxes were checked,
   // returned array will be empty (length will be zero)
   var retArr = new Array();
   
   var lastElement = 0;
   if (buttonGroup[0]) { // if the button group is an array (one check box is not an array)
      for (var i=0; i<buttonGroup.length; i++) {
         if (buttonGroup[i].checked) {
            retArr.length = lastElement;
			var arrObj = new Object ();
			arrObj.id = buttonGroup[i].value;
			arrObj.draf = draf;
			arrObj.axx = axx;
			arrObj.uname = "";
            retArr[lastElement] = arrObj;
            lastElement++;
         }
      }
   } else { // There is only one check box (it's not an array)
      if (buttonGroup.checked) { // if the one check box is checked
         retArr.length = lastElement;
		 var arrObj = new Object ();
         arrObj.id = buttonGroup[i].value;
			arrObj.draf = draf;
			arrObj.axx = axx;
            retArr[lastElement] = arrObj; // return zero as the only array value
      }
   }
   return retArr;
}

function printToPDF(json) {
	if (json){
		window.open('./view/BPHTB/notaris/svc-print-notaris-app.php?q='+encode64(json), '_newtab');
	} else {
		alert ("Silahkan pilih data yang akan di print!");
	}
}
function printToPDFDraf(json) {
	//window.open('./function/BPHTB/notaris/svc-print-notaris-app.php?q='+encodeBase64(json), '_newtab');
	window.open('./view/BPHTB/notaris/svc-print-notaris-app.php?q='+encode64(json), '_newtab');
}

function printNFPDF() {
	window.open('./view/BPHTB/notaris/fsspd.pdf', '_newtab');
}

function printDataToPDF (d) {
	var dt = getCheckedValue(document.getElementsByName('check-all'),d);
	var s = "";
	if (dt!="") {
		s = Ext.util.JSON.encode(dt);
	}
	//console.log(s);
	printToPDF(s)
}

function printDataToPDFDraf () {
	var dt = getCheckedValue(document.getElementsByName('check-all'));
	var s = "";
	if (dt!="") {
		s = Ext.util.JSON.encode(dt);
	}
	printToPDFDraf(s)
}

function removeFChild (obj) {
	//if (td!=null) {
		if ( obj.hasChildNodes() )
		{
			while ( obj.childNodes.length >= 1 )
			{
				obj.removeChild( obj.firstChild );       
			} 
		}
	//}
}

function changeStatusMenu (id,val) {
	var x = document.getElementById(id);
	removeFChild (x);
	var ttext =  document.createTextNode(val);
	x.appendChild(ttext);
}

function loadData () {
	Ext.Ajax.request({
	url:'./view/BPHTB/notaris/svc-notaris.php',
	params :{uname:uname},
	method:'POST',
	scope:this,
	callback:function(options, success, response) {
			if (Ext.decode(response.responseText).success == false) {
				var er = Ext.decode(response.responseText).error;
				Ext.Msg.alert('Error!', "Salah");
			} else {
				changeStatusMenu ('rej-menu','Ditolak'); 
				changeStatusMenu ('app-menu','Disetujui');
				changeStatusMenu ('dil-menu','Tertunda');
				changeStatusMenu ('tmp-menu','Sementara');
				var approved = Ext.decode(response.responseText).approved;
				var reject = Ext.decode(response.responseText).reject;
				var delay = Ext.decode(response.responseText).delay;
				var temp = Ext.decode(response.responseText).temporary;
				if (reject != 0) changeStatusMenu ('rej-menu','Ditolak ('+reject+')'); 
				if (approved != 0) changeStatusMenu ('app-menu','Disetujui ('+approved+')');
				if (delay != 0) changeStatusMenu ('dil-menu','Tertunda ('+delay+')');
				if (temp != 0) changeStatusMenu ('tmp-menu','Sementara ('+temp+')');
				setTimeout('loadData()', 350000);
			}
			
		}
	});
}

function post_to_url(path, params, method) {
    method = method || "post"; // Set method to post by default, if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.getElementById("form-notaris");
    //form.setAttribute("method", method);
    //form.setAttribute("action", path);

    for(var key in params) {
        if(params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
         }
    }

    document.body.appendChild(form);
    form.submit();
}

function deleteSelected() {
	var dt = getCheckedValue(document.getElementsByName('check-all'));
	var s = "";
	if (dt!="") {
		s = Ext.util.JSON.encode(dt);
		post_to_url("",{"del":s},"post");
	}
}

Ext.onReady(function(){
 	loadData();
});


