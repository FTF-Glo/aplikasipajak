
var inqResp ='';
var COUNT = 0;
var TOTAL_ADM = 0;
var TRAN_AMOUNT = 0;
var valuePrint = "";

function clearAll () {
	de('nop_npwp').value = "";
	de('wp-name').innerHTML = "";
	de('info-oriBil').innerHTML = rupiah(0);
	de('wp-address').innerHTML = "";
	de('info-miscBill').innerHTML =  rupiah(0);
	de('wp-kelurahan').innerHTML = "";
	de('info-penaltyBill').innerHTML = rupiah(0);
	de('wp-rtRw').innerHTML = ""
	de('info-adminFee').innerHTML = rupiah(0);
	de('wp-kecamatan').innerHTML = "";
	de('info-totalAmount').innerHTML =rupiah(0);
	de('jml-bayar').value = parseFloat(0);
	de('wp-kabupaten').innerHTML ="";
	de('wp-kdPos').innerHTML = "";
	de('infoObj-address').innerHTML = "";
	de('infoObj-kelurahan').innerHTML = "";
	de('infoObj-kecamatan').innerHTML = "";
	de('infoObj-kabupaten').innerHTML = "";
	de('jml-uang').value = "";
	de('jml-bayar').value = "";
	de('jml-kembali').value = "";
	document.getElementById('payment').disabled=false;
	document.getElementById('jml-uang').disabled=false;
}

function hideMask(){
	hideDialog();
}

function showMask(){
	showDialog('Load','<img src="image/large-loading.gif" width="32" height="32" style="margin-right:8px;" align="absmiddle"/>Tunggu','prompt',false,true);
}

function sendInquiryException(param){
	hideMask();
}

function inquiryBPHTB(areaCode,flag,nopNpwp,appID) {
	if ((areaCode != '') && (flag != '') && (nopNpwp != '')) {
		var params = {area_code : areaCode,flag:flag,nop_npwp:nopNpwp,appID:appID};
		params = Base64.encode(Ext.encode(params));
		  Ext.Ajax.request({
			   url: 'svr/bphtb/svc-bphtb-inquiry.php',
			   timeout:80000,
			   success: getInquirySuccess,
			   failure: getInquiryException,
			   params: { req:params}
			});
		showMask();
	} else {
		alert ("Inquiry tidak bisa dilakukan !, pilih dan silahkan isi NOP/NPWP ! .");
	}
}

function getCheckedValue(radioObj) {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
	
	
}

function sendInquiry(appID) {
	var flag = getCheckedValue(document.forms['inqform'].elements['radiogroup']);
	var selObj = document.getElementById("area");
	var areaCode = selObj.options[selObj.selectedIndex].value; 
	var nop_npwp = document.getElementById("nop_npwp").value;
	inquiryBPHTB(areaCode,flag,nop_npwp,appID)
}

function de (id) {
	return document.getElementById(id);
}

function getInquirySuccess(param){
	if(param.responseText){
		var objResult=Ext.decode(Base64.decode(param.responseText));
		//console.log(objResult);
		if(objResult.result){
				de('wp-name').innerHTML = objResult.data.subject_name;
				de('info-oriBil').innerHTML = rupiah(parseFloat(objResult.data.ori_bill));
				de('wp-address').innerHTML = objResult.data.subject_address;
				de('info-miscBill').innerHTML =  rupiah(parseFloat(objResult.data.misc_bill));
				de('wp-kelurahan').innerHTML = objResult.data.subject_kelurahan;
				de('info-penaltyBill').innerHTML = rupiah(parseFloat(objResult.data.penalty_fee));
				de('wp-rtRw').innerHTML =objResult.data.subject_rt_rw;
				de('info-adminFee').innerHTML = rupiah(parseFloat(objResult.data.admin_fee));
				de('wp-kecamatan').innerHTML = objResult.data.subject_kecamatan;
				de('info-totalAmount').innerHTML =rupiah(parseFloat(objResult.data.total_amount));
				de('jml-bayar').value = formatNumber(parseFloat(objResult.data.total_amount),2,'.','');
				de('wp-kabupaten').innerHTML =objResult.data.subject_kabupaten;
				de('wp-kdPos').innerHTML =objResult.data.subject_zip_post;
				de('infoObj-address').innerHTML =objResult.data.object_address;
				de('infoObj-kelurahan').innerHTML =objResult.data.object_kelurahan;
				de('infoObj-kecamatan').innerHTML =objResult.data.object_kecamatan;
				de('infoObj-kabupaten').innerHTML =objResult.data.object_kabupaten;
				document.getElementById('payment').disabled=false;
				document.getElementById('jml-uang').disabled=false;
				var x  = objResult.data.rincian_tagihan_amount.length;
				for (var i = 0; i < x ; i++) {
					document.getElementById('type_'+i).innerHTML = objResult.data.rincian_tagihan_type[i];
					document.getElementById('amount_'+i).innerHTML = rupiah(parseFloat(objResult.data.rincian_tagihan_amount[i]));
				}
				//console.log(objResult.data.sw_refnum);
				inqResp = objResult.data.sw_refnum;
		} else {
			var objResult=Ext.decode(Base64.decode(param.responseText));
			alert("Terjadi Kesalahan ! \r\n"+objResult.message);
		}
	}else{
		alert("Terjadi Kesalahan");
	}
	hideMask();
}

function sendPayment(appID) {
	document.getElementById('payment').disabled=true;
	document.getElementById('jml-uang').disabled=true;
	
	var byr = document.getElementById('jml-bayar').value;
	var juang = document.getElementById('jml-uang').value;
	
	if (byr < juang) {
		alert ("Error : Jumlah pembayaran lebih kecil dari jumlah yang harus dibayarkan !");
	} else {
		/*var bill = parseFloat(document.getElementById('jml-bayar').value);
		var params = {inqResp : inqResp,rp:bill,ab:aBon,appID:appID};
		params = Base64.encode(Ext.encode(params));
		  Ext.Ajax.request({
			   url: 'svr/bphtb/svc-bphtb-payment.php',
			   timeout:100000,
			   success: sendPaymentSuccess,
			   failure: sendException,
			   params: { req:params}
			});*/
		showMask();
	}
}

function sendPaymentSuccess(param){
	hideMask();
	
 	if(param.responseText){
		var objResultPayment=Ext.decode(Base64.decode(param.responseText));
		if (objResultPayment.result) {
			//console.log(objResultPayment.data);
			var nid = "tab-result";
			//setValue('txt-keterangan',objResultPayment.message);
			valuePrint = objResultPayment.printValue;
			if (printStruk()) {
				alert ("Pembayaran berhasil, mencetak struk");
				clearAll ();
			}
			COUNT += parseFloat(objResultPayment.COUNT);
			TOTAL_ADM += parseFloat(objResultPayment.TOTAL_ADM);
			TRAN_AMOUNT ++;
			displaySummary();
			
		} else {
			//setValue('txt-keterangan',objResultPayment.message);
			alert("Terjadi Kesalahan : "+objResultPayment.message+"!");
		}
     }
}
function sendException(param){
	hideMask();
	alert("Terjadi Kesalahan : Koneksi Gagal !");
}
function getInquiryException () {
	hideMask();
	alert("Terjadi Kesalahan : Koneksi Gagal !");
}

function print64(base64Print) {
    var applet = document.jZebra;
    if (applet != null) {
		applet.append64(base64Print);
		// applet.append64(\"". "IEJBU0U2NCBNRVNTQUdFIFNFTlQgRlJPTSBKWkVCUkEu" ."\");\n";
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
	else
	   return false;
}

function rupiah(value) 
{ 

	value += ''; 
	x = value.split('.'); 
	x1 = x[0]; 
	x2 = x.length > 1 ? '.' + x[1] : ''; 
	var rgx = /(\d+)(\d{3})/; 
	while (rgx.test(x1)) { 
	x1 = x1.replace(rgx, '$1' + '.' + '$2'); 
	} 
	
	return 'Rp ' + x1 + x2 + ",00"; 
};

function printStruk(){
	//valuePrint decode is base64
	bOK = print64(valuePrint);
	return bOK;
}

function getSummary(){
	var now = new Date();
	var dt = dateFormat("yyyy-mm-dd");
	var summaryParams = "{'dt':'"+dt+"','ab':'"+aBon+"'}";
	summaryParams = Base64.encode(summaryParams);
    Ext.Ajax.request({
       url: 'svr/bphtb/svc-bphtb-summary.php',
       success: getSummarySucess,
       failure: getSummaryException,
	   disableCaching:true,
       params: { q: summaryParams }
    });
    showMask();
}

function getSummaryException(param){
	showDialog('Informasi','Koneksi Gagal','prompt',true,true);
	hideMask();
}

function displaySummary() {
	document.getElementById('sum-curTrs').innerHTML = rupiah(COUNT);
	document.getElementById('sum-admTrs').innerHTML = rupiah(TOTAL_ADM);
	document.getElementById('sum-totTrs').innerHTML = TRAN_AMOUNT;
}

function getSummarySucess(param){
	try {
		var oResult = Ext.decode(Base64.decode(param.responseText));
		if (oResult.result) {
			COUNT = parseFloat(oResult.bill[0].sumta);
			TOTAL_ADM = parseFloat(oResult.bill[0].sumadm);
			TRAN_AMOUNT = parseFloat(oResult.bill[0].sumtrans);
			displaySummary();
		} 
	} catch (e) {
		alert("parsing error");
	}
	hideMask();
}

function jml() {
	var v = document.getElementById('jml-bayar');  
	var x = clearFormat(v.value,v);
	var b = document.getElementById('jml-uang');
	var c = clearFormat(b.value,b);
	document.getElementById('jml-kembali').value = formatNumber(parseInt(c) - parseInt(x),2,'.','');
}
Ext.onReady(function() {
	getSummary();
	document.getElementById('payment').disabled=true;
	document.getElementById('jml-uang').disabled=true;
	document.getElementById('jml-uang').value="";
	document.getElementById('jml-bayar').value="";
	document.getElementById('jml-kembali').value="";
});