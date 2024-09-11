var pCode = "";
function hideMask(){
	//hideDialog();
}

function showMask(){
	//showDialog('Load','<img src="image/large-loading.gif" width="32" height="32" style="margin-right:8px;" align="absmiddle"/>Tunggu','prompt',false,true);
}


function sendReprint(refnum,a,ppid,uid){
	hideMask();
	if (refnum != ''){
		
		var params = "{'refnum' : '"+refnum+"','a':'"+a+"','p':'"+ppid+"','u':'"+uid+"'}";
		params = Base64.encode(params);
		  Ext.Ajax.request({
			   url: 'svr/pbb/svc-pbb-reprint.php',
			   success: sendUpdateSuccess,
			   failure: sendUpdateException,
			   params: { q:params}
			});
		showMask();
	} else {
		alert ("Pencetakan laporan tidak bisa dilakukan !");
	}
	
}

function sendUpdateSuccess(param){
	hideMask();
	var objResult=Ext.decode(Base64.decode(param.responseText));
	//var val = document.getElementById('reprintVal-'+objResultPayment.refnum).value;
	print64(objResult.dataprint);
}
function sendUpdateException(param){
	hideMask();
	alert("Terjadi Kesalahan : Update NTRIAL Gagal !");
}

function monitorPrinting() {
	  var applet = document.jZebra;
	  if (applet != null) {
	      while (!applet.isDonePrinting()) {
          	// Wait
          }
	      var e = applet.getException();
          if(e!=null){
					showDialog('Cetak Gagal', "Pastikan Seting Nama Printer anda Benar<br/>Exception occured: " + e.getLocalizedMessage(), 'error',false);
					// alert();
		  }
	  } else {
            alert('Applet not loaded!');
        }
}

function print64(content) {
	 if(content){
		 if(content!=""){
			 var applet = document.jZebra;
			 if (applet != null) {
				 //console.log(content.substr(0,3));
				 if(content.substr(0,3)=='1~~' || content.substr(0,3)=='0~~'){
					applet.appendRawOnpays(content);
					applet.printOnpays();
				 }else{
					applet.append64(content);
					applet.print();					
				 }	
				 monitorPrinting();
			 }
			 else {
				alert("Applet not loaded!");
			 }
		 }
	}
 }
 
 
 function sendReportSuccess(params){
	hideMask();
	if(params.responseText){
		var objResultPayment=Ext.decode(Base64.decode(params.responseText));
		///console.log(objResultPayment);
		if (objResultPayment.result) {
			var strBtn = "<input name='btnSend' type='button' value='Cetak Ke Printer' onclick='printReport(\""+objResultPayment.printCode+"\","+pcopy+");'><br>";
			document.getElementById('pbb-report-result').innerHTML = strBtn+objResultPayment.printHTML;
		} else {
			document.getElementById('pbb-report-result').innerHTML ='<label style="font-weight:bold;color:red">'+objResultPayment.message+'</label>';
		}
	}
}

function sendReportException(param){
	hideMask();
}

function printReport(param,cpy) {
	for (var i=0;i<cpy;i++) {
		print64(param);
	}
}
/*function printReport(param) {
	print64(param);
}*/
function sendReport(a) {
	var report = document.getElementById('jenis-laporan');
	//var rpt = report.options[report.selectedIndex].value;
	var dateTrs = document.getElementById('filtertgl').value;
	var monthTrs = document.getElementById('filterbl').value;
	var yearTrs = document.getElementById('filterth').value;
	if (dateTrs != ''){
		//console.log(a);
		var params = "{'dateTrs' : '"+dateTrs+"','a':'"+a+"'}";
		params = Base64.encode(params);
		  Ext.Ajax.request({
			   url: 'svr/pbb/svc-pbb-daily-report.php',
			   success: sendReportSuccess,
			   failure: sendReportException,
			   params: { q:params}
			});
		showMask();
	} else {
		alert ("Pencetakan laporan tidak bisa dilakukan !");
	}
}

function downloadFile(a){
		ft=document.getElementById('filtertgl').value;
		var params = "{'dateTrs' : '"+ft+"','a':'"+a+"'}";
		
		if(ft.value!=""){			
			urlparam=Base64.encode(params);
			url='http://'+window.location.host+window.location.pathname.replace("main.php","")+'svr/pbb/svc-pbb-download-detail-daily.php?q='+urlparam;
			//console.log(url);
			document.getElementById('download-file').src=url;
		}
}
