function hideMask(){
	hideDialog();
}

function showMask(){
	showDialog('Load','<img src="image/large-loading.gif" width="32" height="32" style="margin-right:8px;" align="absmiddle"/>Tunggu','prompt',false,true);
}

function sendFindSuccess(params){
	hideMask();
	if(params.responseText){
		var objResultPayment=Ext.decode(Base64.decode(params.responseText));
		if (objResultPayment.result) {
			//alert(objResultPayment.message);
			//print64(objResultPayment.printStruk);
			document.getElementById('elpost-reprint-main-result').innerHTML = objResultPayment.message;
		} else {
			document.getElementById('elpost-reprint-main-result').innerHTML ='<label style="font-weight:bold;color:red">Data Tidak ditemukan !</label>';
		}
	}
}

function sendFindException(param){
	hideMask();
}

function findVoucher() {
	var msisdn = document.getElementById('filtermsisdn').value;
	var dateTrs = document.getElementById('filtertgl').value;
	var prodid = document.getElementById('filterprodid').value;
	if ((msisdn != '') && (dateTrs != '')) {
		var params = "{'req':'getList','msisdn' : '"+msisdn+"','dateTrs' :'"+dateTrs+"','prodid':'"+prodid+"'}";
		params = Base64.encode(params);
		  Ext.Ajax.request({
			   url: 'svr/voucher/svc-voucher-reprint.php',
			   success: sendFindSuccess,
			   failure: sendFindException,
			   params: { req:params}
			});
		showMask();
	} else {
		alert ("Pencarian tidak bisa dilakukan !, silahkan isi no hp, kode voucher, dan harga jual.");
	}
}

function sendReprintSuccess(params){
	hideMask();
	if(params.responseText){
		var objResultPayment=Ext.decode(Base64.decode(params.responseText));
		if (objResultPayment.result) {
			//alert(objResultPayment.message);
			print64(objResultPayment.printStruk);
			//document.getElementById('elpost-reprint-main-result').innerHTML = objResultPayment.message;
		} else {
			document.getElementById('elpost-reprint-main-result').innerHTML ='<label style="font-weight:bold;color:red">'+objResultPayment.message+'</label>';
		}
	}
}

function sendReprint(refnum) {
if (refnum != '') {
		var params = "{'req':'getDataToPrint','refnum' : '"+refnum+"'}";
		params = Base64.encode(params);
		  Ext.Ajax.request({
			   url: 'svr/voucher/svc-voucher-reprint.php',
			   success: sendReprintSuccess,
			   failure: sendFindException,
			   params: { req:params}
			});
		showMask();
	} else {
		alert ("Pencetakan tidak bisa dilakukan !");
	}
}

function sendReportSuccess(params){
	hideMask();
	if(params.responseText){

	var objResultPayment=Ext.decode(Base64.decode(params.responseText));
		if (objResultPayment.result) {
			var strBtn = "<input name='btnSend' type='button' value='Cetak Ke Printer' onclick='printReport(\""+objResultPayment.printcode+"\","+objResultPayment.printcopy+");'><br>";
			document.getElementById('voucher-report-result').innerHTML = strBtn+objResultPayment.message;
		} else {
			document.getElementById('voucher-report-result').innerHTML ='<label style="font-weight:bold;color:red">'+objResultPayment.message+'</label>';
		}

	}
}

function monitorPrinting() {
     var applet = document.jZebra;
     if (applet != null) {
         while (!applet.isDonePrinting()) {
         	// Wait
         }
         var e = applet.getException();
         if(e!=null){
					alert("Pastikan Seting Nama Printer anda Betul!\r\nException occured: "+e.getLocalizedMessage());
		 }
     } else {
           alert('Applet not loaded!');
       }
}

function updateFlag(str){
var xmlhttp;    
if (str=="")
  {
  document.getElementById("txtHint").innerHTML="";
  return;
  }
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("txtHint").innerHTML=xmlhttp.responseText;
    }
  }
xmlhttp.open("GET","function/DHKP/tampil.php?qId="+str,true);
xmlhttp.send();
}

function printReport(param,cpy) {
	//var cpy=1;
	//alert(" printReport23: " + param );
	updateFlag(param);
	for (var i=0;i<cpy;i++) {
		print64(param);
	}
}

function print64(base64Print) {
    var applet = document.jZebra;
    if (applet != null) {
		console.log(base64Print);
		if(base64Print.substr(0,3)=="1~~"||base64Print.substr(0,3)=="0~~"){
			applet.appendRawOnpays(base64Print);
			applet.printOnpays();
		}else{
			applet.append64(base64Print);
			applet.print();
		}
		monitorPrinting();
    }
    else {
   		alert('Applet not loaded!');
    }
}


function sendReport() {
		//var kec = document.getElementById('kecamatan');
		//var kecSel = kec.options[report.selectedIndex].value;
		//var kel = document.getElementById('kelurahan');
		//var kelSel = kel.options[report.selectedIndex].value;
		//var params = "{'req':'getReport','kec' : '"+kecSel+"','kel' : '"+kelSel+"'}";
		var params = "{'req':'getReport'}";
		params = Base64.encode(params);
		  Ext.Ajax.request({
			   url: 'svr/DHKP/svr-report-DHKP.php',
			   success: sendReportSuccess,
			   failure: sendFindException,
			   params: {req:params}
			});
		showMask();
}

function downloadFile(){
		ft=document.getElementById('filtertgl');
		if(ft.value!=""){			
			urlparam=Base64.encode(ft.value);
			url='http://'+window.location.host+window.location.pathname.replace("main.php","")+'svr/voucher/svc-voucher-download-detail-daily.php?q='+urlparam;
			document.getElementById('download-file').src=url;
			// console.log(url);
		}
}

function saveItemSuccess(response, params) {
	var objResult = Ext.decode(Base64.decode(response.responseText))
	// alert('refnum: '+objResult.result.priv.refnum);
	// console.log(objResult);
	if (objResult.success) {
	//tambah isi tabel result() 
		document.getElementById('lbl-'+objResult.prodid).innerHTML = objResult.profit;
	} else {
		alert('Error Update dari server: ');
	}
	hideMask();
}

//fungsi yang dijalankan jika inquiry gagal
function saveItemFailure(response, params) {
	alert('Inquiry Gagal Karena Koneksi: '+response.responseText);
	hideMask();
}

function saveItem(prodId){
	  var price = document.getElementById('profit-'+prodId).value;
	  var ppid = document.getElementById('ppid').value;
	  var params = "{'ppid' :'"+ppid+"','prodid' :'"+prodId+"','price':'"+price+"'}";
	  //console.log(params);
	  var params64 = Base64.encode(params);
	  Ext.Ajax.request({
		   url: 'svr/voucher/svc-voucher-save-item.php',
		   success: saveItemSuccess,
		   failure: saveItemFailure,
		   params: { req: params64 }
		});
	showMask();
}

function numbersonly(myfield, e, dec)
{
	var key;
	var keychar;
	
	if (window.event)
	   key = window.event.keyCode;
	else if (e)
	   key = e.which;
	else
	   return true;
	keychar = String.fromCharCode(key);
	
	// control keys
	if ((key==null) || (key==0) || (key==8) || 
		(key==9) || (key==13) || (key==27) )
	   return true;
	
	// numbers
	else if ((("0123456789").indexOf(keychar) > -1))
	   return true;
	
	// decimal point jump
	else if (dec && (keychar == "."))
	   {
	   myfield.form.elements[dec].focus();
	   return false;
	   }
	else return false;
}

function HandlerFilterjenisChange(){
	var f = document.getElementById("jenis-laporan");
	var h = document.getElementById("input_harian");
	var b = document.getElementById("input_bulanan");
	var bu = document.getElementById("btnUnduh");
	if(f){
		if(f.value == "1"){
			if(h) h.style.display="none";
			if(b) b.style.display="inline";
			if(bu) bu.style.display="none";
		}else{
			if(h) h.style.display="inline";
			if(bu) bu.style.display="inline";
			if(b) b.style.display="none";
		}
	}
}
