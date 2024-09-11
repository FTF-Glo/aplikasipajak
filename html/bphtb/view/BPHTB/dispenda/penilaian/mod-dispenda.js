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

function removeFChild (obj) {
	if (obj!=null) {
		if ( obj.hasChildNodes() )
		{
			while ( obj.childNodes.length >= 1 )
			{
				obj.removeChild( obj.firstChild );       
			} 
		}
	}
}

function changeStatusMenu (id,val) {
	var x = document.getElementById(id);
	if (x!=null) {
		removeFChild (x);
		var ttext =  document.createTextNode(val);
		x.appendChild(ttext);
	}
}

function loadData () {
	Ext.Ajax.request({
	url:'./view/BPHTB/dispenda/svc-dispenda.php',
	params :{dispenda:dispenda},
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
				changeStatusMenu ('dil5-menu','Tertunda');
				changeStatusMenu ('pro-menu','Proses');
				var approved = Ext.decode(response.responseText).approved2;
				var reject = Ext.decode(response.responseText).reject;
				var delay = Ext.decode(response.responseText).delay;
				var delay5 = Ext.decode(response.responseText).delay5;
				var proses = Ext.decode(response.responseText).proses;
				
				if (reject != 0) changeStatusMenu ('rej-menu','Ditolak ('+reject+')'); 
				if (approved != 0) changeStatusMenu ('app-menu','Disetujui ('+approved+')');
				if (delay != 0) changeStatusMenu ('dil-menu','Tertunda ('+delay+')');
				if (delay5 != 0) changeStatusMenu ('dil5-menu','Tertunda ('+delay5+')');
				if (proses != 0) changeStatusMenu ('pro-menu','Proses ('+proses+')');
				setTimeout('loadData()', 35000);
			}
			
		}
	});
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
		window.open('./view/BPHTB/notaris/svc-print-notaris-app.php?q='+encodeBase64(json), '_newtab');
	} else {
		alert ("Silahkan pilih data yang akan di print!");
	}
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

function approve(d,a,i) {
	Ext.Ajax.request({
	url:'./view/BPHTB/dispenda/svc-approve.php',
	params :{d:d,a:a},
	method:'POST',
	scope:this,
	callback:function(options, success, response) {
		console.log();
		if (success) {
			if (Ext.decode(response.responseText).success) {
				alert("Persetujuan telah dilakukan!");
				document.getElementById('appr-'+i).innerHTML="Disetujui";
			}
			else alert("Persetujuan gagal dilakukan!");
		}
		else alert("Persetujuan gagal dilakukan!");
	}});
}

Ext.onReady(function(){
 	loadData();
});
