/* 
 *  Print SSPD - BPHTB
 *  Author By ardi@vsi.co.id
 *  06-12-2016
*/
 
 function printToPrinter(par) {    
    var params = {q:par};
	console.log("print...");
	$.ajax({
		url: 'function/BPHTB/notaris/print/svc-print.php',
		data: params,
		type: 'post',
		success: printSuccess
	}); 
}

function printSuccess(msg){
	var res=Ext.decode(Base64.decode(msg));
	
	if(res.code == '00'){
		var applet = document.jZebra;
		
		if (applet != null) {
			
			console.log(res.data);
			applet.append64(res.data);
			applet.print();
			while (!applet.isDonePrinting()) {
				console.log('printing..')
			}
			var e = applet.getException();
			return e == null ? true : false;
		}else {
			alert('Applet not loaded!');
			return false;
		}
    }else{
		alert('Respon error!');
	}
}

