var inqResp ='';
var COUNT = 0;
var TOTAL_ADM = 0;
var TRAN_AMOUNT = 0;
var valuePrint = "";

function hideMask(){
	hideDialog();
}

function showMask(){
	showDialog('Load','<img src="image/icon/loading.gif" width="32" height="32" style="margin-right:8px;" align="absmiddle"/>Tunggu','prompt',false,true);
}

function de (id) {
	return document.getElementById(id);
}

function printCommand(appID, id) {
    var params = {appID:appID, NOP:id, tahun:$('#tahun').val()};
	console.log("print...");
	params = Base64.encode(Ext.encode(params));
	Ext.Ajax.request({
		   url: 'function/PBB/print/svc-print.php',
		   timeout:100000,
		   success: printCommandSuccess,
		   failure: printException,
		   params: { req:params}
	}); 
//	Ext.Ajax.request({
//		   url: 'function/PBB/sms/svc-sms.php',
//		   timeout:100000,	
//		   success: smsRespon,
//		   failure: smsRespon,
//		   params: { req:params}
//	});
	showMask();
}

function printSttsCommand(appID, id) {
        var params = {appID:appID, NOP:id, tahun:$('#tahun').val()};
        console.log("print...");
        params = Base64.encode(Ext.encode(params));
        Ext.Ajax.request({
                url: 'function/PBB/print/svc-stts-print.php',
                timeout:100000,
                success: printCommandSuccess,
                failure: printException,
                params: { req:params}
        }); 
    //	Ext.Ajax.request({
    //		   url: 'function/PBB/sms/svc-sms.php',
    //		   timeout:100000,	
    //		   success: smsRespon,
    //		   failure: smsRespon,
    //		   params: { req:params}
    //	});
            showMask();
    }

function printCommandSuccess(param){
        hideMask();
        var objResultPayment=Ext.decode(Base64.decode(param.responseText));
        if(param.responseText){
                if (objResultPayment.result) {
                        var nid = "tab-result";
                        valuePrint = objResultPayment.printValue;
                        if (printStruk()) {
                                alert ("Mencetak SPPT");
                        } else {
                                alert ("Pencetakan SPPT gagal !");
                        }
                } else {
                        alert("Terjadi Kesalahan : "+objResultPayment.message+"!");
                }
        }
}

function printException(param){
        hideMask();
        alert("Terjadi Kesalahan : Koneksi Gagal !");
}

function print64(base64Print) {
    var applet = document.jZebra;
    if (applet != null) {
                //console.log(base64Print);
                applet.append64(base64Print);
                // applet.append64("IEJBU0U2NCBNRVNTQUdFIFNFTlQgRlJPTSBKWkVCUkEu");
                applet.print();
                while (!applet.isDonePrinting()) {
                        // Wait\n";
                }
                var e = applet.getException();
                return e == null ? true : false;
                //alert(e == null ? 'Printed Successfully' : 'Exception occured: ' + e.getLocalizedMessage());
                //getSummary();
    }
    else {
                return false;
                alert('Applet not loaded!');
    }
}

function printStruk(){
        //valuePrint decode is base64
        bOK = print64(valuePrint);
        return bOK;
}
        
function smsRespon(param){
	console.log(param.responseText);
}



