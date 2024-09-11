function sendInquiry(){
	if($('#nop_npwp').val() == '') alert('Silahkan Isi Nomor NOP !');
	else{
		Ext.Ajax.request({
			url: 'function/PBB/pencatatan_pembayaran/loadData.php',
			params:{nop1:$('#nop_npwp-1').val(),nop2:$('#nop_npwp-2').val(),nop3:$('#nop_npwp-3').val(),nop4:$('#nop_npwp-4').val(),nop5:$('#nop_npwp-5').val(),nop6:$('#nop_npwp-6').val(),nop7:$('#nop_npwp-7').val(),year:$('#year').val(),mode:$('#mode').val(),tgl:$('#tgl-bayar').val()},
			timeout: 80000,
			success: function(res){setData(res.responseText)},
			failure: function(){alert('Connection Error !!'); clearData();}
		});
	}
}

function setData(json){
   if(json=='[]'){
		alert('Data tagihan tidak ada !'); 
		clearData();
	}
	else{
		$data = jQuery.parseJSON(json);
      if($data[0].PAYMENT_FLAG==1 && $('#mode').val()!='cetak_ulang'){
			alert('Nop Sudah Dibayar !!!'); 
			clearData();
		}
		else if($data[0].PAYMENT_FLAG==0 && $('#mode').val()=='cetak_ulang'){
			alert('Nop Belum Dibayar !!!'); 
			clearData();		
		}
		else if($('#mode').val()=='cetak_ulang'){
			var totalTagihan = $data[0].SPPT_PBB_HARUS_DIBAYAR+$data[0].SPPT_DENDA;
			
			$('#wp-name').html($data[0].WP_NAMA);
			$('#wp-duedate').html($data[0].SPPT_TANGGAL_JATUH_TEMPO);
			$('#wp-address').html($data[0].WP_ALAMAT);
			$('#wp-kelurahan').html($data[0].WP_KELURAHAN);
			$('#wp-rtRw').html($data[0].WP_RT+'/'+$data[0].WP_RW);
			$('#wp-kecamatan').html($data[0].WP_KECAMATAN);
			$('#wp-kabupaten').html($data[0].WP_KOTAKAB);
			$('#wp-kdPos').html($data[0].WP_KODEPOS);

			$('#wp-amount').html($data[0].SPPT_PBB_HARUS_DIBAYAR);	
			$('#wp-penalty').html($data[0].SPPT_DENDA);
			$('#wp-admin').html(0);
			$('#wp-totalamount').html($data[0].TOTAL_TAGIHAN_VIEW);
			
			$('#jml-bayar').val($data[0].TOTAL_TAGIHAN_VIEW);
			$('#jml-uang').val($data[0].TOTAL_TAGIHAN_VIEW);
			$('#jml-kembali').val(0);
                        if($data[0].PAYMENT_PAID.length > 9){
									//2015-04-10
									//alert($data[0].PAYMENT_PAID.substring(8, 10)+'-'+$data[0].PAYMENT_PAID.substring(5, 7)+'-'+$data[0].PAYMENT_PAID.substring(0, 4));
									$('#tgl-bayar').val($data[0].PAYMENT_PAID.substring(8, 10)+'-'+$data[0].PAYMENT_PAID.substring(5, 7)+'-'+$data[0].PAYMENT_PAID.substring(0, 4));
                        }else $('#tgl-bayar').val($data[0].PAYMENT_PAID);
			
			document.getElementById("payment").disabled = false; 
		}
		else {
			var totalTagihan = $data[0].SPPT_PBB_HARUS_DIBAYAR+$data[0].SPPT_DENDA;
			
			$('#wp-name').html($data[0].WP_NAMA);
			$('#wp-duedate').html($data[0].SPPT_TANGGAL_JATUH_TEMPO);
			$('#wp-address').html($data[0].WP_ALAMAT);
			$('#wp-kelurahan').html($data[0].WP_KELURAHAN);
			$('#wp-rtRw').html($data[0].WP_RT+'/'+$data[0].WP_RW);
			$('#wp-kecamatan').html($data[0].WP_KECAMATAN);
			$('#wp-kabupaten').html($data[0].WP_KOTAKAB);
			$('#wp-kdPos').html($data[0].WP_KODEPOS);

			$('#wp-amount').html($data[0].SPPT_PBB_HARUS_DIBAYAR);	
			$('#wp-penalty').html($data[0].SPPT_DENDA);
			$('#wp-admin').html(0);
			$('#wp-totalamount').html($data[0].TOTAL_TAGIHAN_VIEW);
			
			$('#jml-bayar').val($data[0].TOTAL_TAGIHAN_VIEW);
			$('#jml-uang').val(0);
			$('#jml-kembali').val(0);
			
			document.getElementById("payment").disabled = false; 
		}
	}
}


function clearData(){
	$('#wp-name').html('-');
	$('#wp-duedate').html('-');
	$('#wp-address').html('-');
	$('#wp-kelurahan').html('-');
	$('#wp-rtRw').html('-');
	$('#wp-kecamatan').html('-');
	$('#wp-kabupaten').html('-');
	$('#wp-kdPos').html('-');

	$('#wp-amount').html(0);	
	$('#wp-penalty').html(0);
	$('#wp-admin').html(0);
	$('#wp-totalamount').html(0);
	
	$('#jml-bayar').val(0);
	$('#jml-uang').val(0);
	$('#jml-kembali').val(0);
	
	document.getElementById("payment").disabled = true; 
}

function sendBayar(){
	var v = document.getElementById('jml-kembali').value;
	var n = v.search("-"); 
	var y = document.getElementById('jml-uang');
	var z = clearFormat(y.value,y);
	var nop1 	= $('#nop_npwp-1').val();
	var nop2 	= $('#nop_npwp-2').val();
	var nop3 	= $('#nop_npwp-3').val();
	var nop4 	= $('#nop_npwp-4').val();
	var nop5 	= $('#nop_npwp-5').val();
	var nop6 	= $('#nop_npwp-6').val();
	var nop7 	= $('#nop_npwp-7').val();
	var year 	= $('#year').val();
	var driver 	= $('#driver').val();
	var mode 	= $('#mode').val();
	var uname 	= $('#uname').val();
	var tgl 	= $('#tgl-bayar').val();
	if (parseInt(z) > 0 && n == '-1') {
		Ext.Ajax.request({
		url: 'function/PBB/pencatatan_pembayaran/printDataPDF.php',
		params:{
			nop1: $('#nop_npwp-1').val(),
			nop2: $('#nop_npwp-2').val(),
			nop3: $('#nop_npwp-3').val(),
			nop4: $('#nop_npwp-4').val(),
			nop5: $('#nop_npwp-5').val(),
			nop6: $('#nop_npwp-6').val(),
			nop7: $('#nop_npwp-7').val(),
			year: $('#year').val(),
			driver: $('#driver').val(),
			mode: $('#mode').val(),
			uname: $('#uname').val(),
            tgl:$('#tgl-bayar').val()
		},
		timeout: 80000,
		success: function(r){
			if(r.responseText==0000){
				printToPDF(nop,year,mode,uname,tgl);
			} else {
				alert('Terjadi kesalahan');
			}
			clearData();	
		},
		failure: function(){alert('Connection Error !!'); clearData();}
	});
	}
	else alert('Tidak bisa melakukan pembayaran.\nJumlah uang yang dibayar kurang dari jumlah tagihan yang harus dibayar.');
}

function printToPDF(nop,year,mode,uname,tgl) {
	var params = {nop:nop,year:year,mode:mode,uname:uname,tgl:tgl};
	params = Base64.encode(Ext.encode(params));
	window.open('function/PBB/pencatatan_pembayaran/stts-pdf.php?req='+params, '_newtab');
}
function jml() {
	var v = document.getElementById('jml-bayar');
	var x = clearFormat(v.value,v);
	var b = document.getElementById('jml-uang');
	var c = clearFormat(b.value,b);
	
	document.getElementById('jml-kembali').value = formatNumber(parseInt(c) - parseInt(x),0,'.','');
} 
function clearFormat (input, milSep){
	var strCheck = '0123456789';
	var clstr = "";
	if (milSep.value != undefined)
		len = milSep.value.length;
	else len = 0;
	for(i=0; i < len; i++)
		if (strCheck.indexOf(milSep.value.charAt(i))!=-1) clstr += milSep.value.charAt(i);
	return clstr;
} 
function filterInput(input, e){
	if(e != undefined){
		var strCheck = '0123456789';
		var whichCode = (window.Event) ? e.which : e.keyCode;
		if (whichCode == 8) return true; // Delete
		//VALIDATION
		key = String.fromCharCode(whichCode); // Get key value from key code
	if (strCheck.indexOf(key) == -1) return false; // Not a valid key
	}
	if (input.value.charAt(0) == '0') return false; // block 0 input
	}
	function applySeparator(str, milSep){
		st = "";
		length = str.length;
		j = 0;
		for (i = length - 1; i >= 0; i--)
		{
			j++;
		if (j > 3)
		{
			st = milSep + st;
			j = 1;
		}
		ch = str.charAt(i);
		st = ch + st;
	}
	return st;
}
// When using this function at onChange event, use filterInput in onKeyPress
// Default using at onBlur
function currencyFormatIC (input, milSep) {
	if (input.value.charAt(0) == '0') return false; // block 0 input
	aux = clearFormat(input,milSep);
	input.value = applySeparator(aux, milSep);
} 
// OnKeyPress, execute before character code displayed
function currencyFormatI(input, milSep, e) {
var sep = 0;
var key = '';
var i = j = 0;
var len = len2 = 0;
var strCheck = '0123456789';
var aux = aux2 = '';
var whichCode = (window.Event) ? e.which : e.keyCode;
len = input.value.length;
//VALIDATION
key = String.fromCharCode(whichCode); // Get key value from key code
if (whichCode == 8) { //Backspace
if(input.value.length != 0){ // Fix all delimiters place before backspace
for(i=0; i < len; i++) // Trim character other than number
if (strCheck.indexOf(input.value.charAt(i))!=-1) aux += input.value.charAt(i);
len = aux.length;
aux2 = '';
j = 0;
first=true;
for (i = len; i >= 0; i--) {
if ((j == 5) && (first==true)) {
first = false;
aux2 += milSep;
j = 0;
}
if ((j == 3) && (first==false)) {
aux2 += milSep;
j = 0;
}
aux2 += aux.charAt(i);
j++;
}
//console.log("aux2:"+ aux2);
input.value = '';
len2 = aux2.length;
for (i = len2; i >= 0; i--)
input.value += aux2.charAt(i);
}
return true;
}
if (input.value.charAt(0) == '0') return false; // block 0 input
if (strCheck.indexOf(key) == -1) return false; // not a valid key
len = input.value.length; // check maxLength
maxlen = input.maxLength;
if (maxlen == len) return false;
if (maxlen % 4 == 0){
if(len == maxlen-1)
return false;
}
aux = '';
for(i=0; i < len; i++)
if (strCheck.indexOf(input.value.charAt(i))!=-1) aux += input.value.charAt(i);
len = aux.length;
aux2 = '';
j = 0; k = 0;
for (i = len; i >= 0; i--) {
if (j == 3) {
aux2 += milSep;
j = 0;
}
aux2 += aux.charAt(i);
j++;
k++;
}
input.value = '';
len2 = aux2.length;
for (i = len2 - 1; i >= 0; i--)
input.value += aux2.charAt(i);
}
function onSelectClearFormat(input, milSep, e){
input.value = clearFormat(input, milSep);
return filterInput(input, e);
}

function openListTag(url){
	window.open(url,'_blank');
}