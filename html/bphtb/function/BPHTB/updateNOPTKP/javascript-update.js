var cNPOPTKP=0;
// JavaScript Document
function validDelete(){	
		var r=confirm("Anda yakin akan menghapus?");
		if (r==true){
			return true;
		} else {
			return false;
		} 
}

function getAlert(str){
	alert("hallo!: "+str);
}

function hideMask(){
	hideDialog();
}

function showMask(){
	//console.log(showDialog);
	showDialog('Load','<img src="image/large-loading.gif" width="32" height="32" style="margin-right:8px;" align="absmiddle"/>Tunggu','prompt',false,true);
	//alert("");
}
function defaValue(sw,np,npktp,date,a) {
	showMask();
	$.ajax({type: "POST",
        url: "function/BPHTB/updateNOPTKP/svc-default.php",
        data: {swt:sw,nop:np,noktp:npktp,dt:date,ap:a},
        dataType: "json",
        success: function(data){
				 $("#f_noptk").val(data.noptkp);
				 p = $("#f_nilai_pajak").val()-data.noptkp;
				 $("#f_pengurangan").val(p);
				 $("#f_bphtb").val(p*0.05);
				 hideMask();
                },
        error: function(XMLHttpRequest, textStatus, errorThrown){
                alert ("erro: "+textStatus); hideMask();
        }
    });
}

function showForm(str){
	str = Base64.decode(str);
	obj = JSON.parse(str);
	if(obj.msg){
	  alert(obj.msg);
	}else{
	  document.getElementById("f_nilai_pajak").value = obj.nilai_pajak;
	  document.getElementById("f_noptk").value = obj.noptk;
	  document.getElementById("f_pengurangan").value = obj.pengurangan;
	  document.getElementById("f_bphtb").value = obj.bphtb;
	  document.getElementById("up_no_ktp").value = obj.noKTP;
	  document.getElementById("up_nop").value = obj.nop;
	}
}

function hideDiv(v) {

	document.getElementById("show_hide_form_"+v).style.display="none";
}

function showDiv(v) {
	
	document.getElementById("show_hide_form_"+v).style.display="block";
} 

function nextFocus (el,e) {
	if (e.keyCode != 13) {
        return;
    }
	var f = el.form;
	var els = f.elements;
	var x, nextEl;
	for (var i=0, len=els.length; i<len; i++){
		x = els[i];
		if (el == x && (nextEl = els[i+1])){
		if (nextEl.focus) nextEl.focus();
		}
	}
	return false;
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
	nextFocus(myfield, e);
	// control keys
	if ((key==null) || (key==0) || (key==8) || 
		(key==9) ||  (key==27) )
	   return true;
	
	// numbers
	else if ((("0123456789").indexOf(keychar) > -1))
	   return true;
	
	// decimal point jump
	/*else if (dec && (keychar == "."))
	   {
	   myfield.form.elements[dec].focus();
	   return false;
	   }*/
	else
	   return false;
}

function checkTransaction () {
	var lnjop = document.getElementById('land-njop').value;
	var larea = document.getElementById('land-area').value;
	var bnjop = document.getElementById('building-njop').value;
	var barea = document.getElementById('building-area').value
	var tvalue = document.getElementById('trans-value').value;
	var jcp = document.getElementById('jsb-choose-percent').value;
	
	if (isEmpty(document.getElementById('land-njop').value)) lnjop = 0;
	if (isEmpty(document.getElementById('land-area').value)) larea = 0;
	if (isEmpty(document.getElementById('building-area').value)) barea = 0;
	if (isEmpty(document.getElementById('building-njop').value)) bnjop = 0;
	if (isEmpty(document.getElementById('trans-value').value)) tvalue = 0;
//	if (isEmpty(document.getElementById('jsb-choose-percent').value)) jcp =0;
	
	var s1 = parseFloat(larea) * parseFloat(lnjop);
	var s2 = parseFloat(barea) * parseFloat(bnjop);
	var t = parseFloat(tvalue);
	var per = parseFloat(jcp);
		//var NPOPTKP = parseFloat(document.getElementById('NPOPTKP').value);
	var eNPOPTKP = document.getElementById('NPOPTKP');
	var tNJOP = document.getElementById('tNJOP');
	var akumulasi = document.getElementById('akumulasi');
	var tBPHTBT = document.getElementById('tBPHTBT');
	var tWasiat = document.getElementById('tWasiat');
	var tTotal = document.getElementById('tTotal');
	var eNPOPKP = document.getElementById('tNPOPKP');
	var text =  document.createTextNode("0");
	var text2 =  document.createTextNode("0");
	var bphtbt =  document.createTextNode("0");
	var wasiat =  document.createTextNode("0");
	var total = document.createTextNode("0");
	//if (edit) cNPOPTKP = cnpoptkp;
	var noptkp = document.createTextNode(number_format(cNPOPTKP, 2, '.', ','));
	var jsbtotalbefore = document.getElementById('jsb-total-before');
	var jpay = document.getElementById("jmlBayar");
	
	var w = 0;
	var NPOPKP=0;
	//if (edit) eNPOPTKP.value = cnpoptkp;
	
	var ctjpay = document.createTextNode("Jumlah yang dibayarkan :");
	
	if ((s1+s2) > t ) {
		text =  document.createTextNode(number_format(s1+s2, 2, '.', ','));
		text2 = document.createTextNode(number_format(s1+s2, 2, '.', ','));
		jsbtotalbefore.value = s1+s2;
		
	} else {
		text =  document.createTextNode(number_format(t, 2, '.', ','));
		text2 = document.createTextNode(number_format(t, 2, '.', ','));
		jsbtotalbefore.value = t;
		
	}
	var m = jsbtotalbefore.value - cNPOPTKP;
	if (m <= 0) {
		//console.log("1 "+cNPOPTKP); 
		NPOPKP = 0;
		bphtbt = document.createTextNode(number_format(0, 2, '.', ','));
		//if (per!=0) ctjpay = document.createTextNode("Jumlah yang dibayarkan : "+number_format(0*per*0.01, 2, '.', ','));
		//else 
		ctjpay = document.createTextNode("Jumlah yang dibayarkan : "+number_format(0, 2, '.', ','));
	}
	else {
		//console.log("2 "+m); 
		NPOPKP = jsbtotalbefore.value - cNPOPTKP;
		bphtbt = document.createTextNode(number_format(NPOPKP*0.05, 2, '.', ','));
		
		if (!isNaN(per) && (per!=0)) ctjpay = document.createTextNode("Jumlah yang dibayarkan : "+number_format(NPOPKP*0.05-(NPOPKP*0.05*per*0.01), 2, '.', ','));
	    else ctjpay = document.createTextNode("Jumlah yang dibayarkan : "+number_format(NPOPKP*0.05, 2, '.', ','));
	}
	
	var npopkp = document.createTextNode(number_format(NPOPKP, 2, '.', ','));
	
	 
	//text2= text;
	removeFChild (eNPOPTKP);
	removeFChild (eNPOPKP);
	removeFChild (tNJOP);
	removeFChild (akumulasi);
	removeFChild (tBPHTBT);
	removeFChild (tWasiat);
	removeFChild (jpay);
	//removeFChild (tTotal);
	
	//tTotal.appendChild(total);
	//tWasiat.appendChild(wasiat);
	eNPOPKP.appendChild(npopkp)
	eNPOPTKP.appendChild(noptkp)
	tNJOP.appendChild(text);
	akumulasi.appendChild(text2);
	tBPHTBT.appendChild(bphtbt);
	jpay.appendChild(ctjpay);
	
}
function isEmpty(str) {
    return (!str || 0 === str.length);
}
function removeFChild (td) {
	if (td!=null) {
			if ( td.hasChildNodes() )
			{
				while ( td.childNodes.length >= 1 )
				{
					td.removeChild( td.firstChild );       
				} 
			}
	}
}

function number_format(a, b, c, d) {
 a = Math.round(a * Math.pow(10, b)) / Math.pow(10, b);
 e = a + '';
 f = e.split('.');
 if (!f[0]) {
  f[0] = '0';
 }
 if (!f[1]) {
  f[1] = '';
 }
 if (f[1].length < b) {
  g = f[1];
  for (i=f[1].length + 1; i <= b; i++) {
   g += '0';
  }
  f[1] = g;
 }
 if(d != '' && f[0].length > 3) {
  h = f[0];
  f[0] = '';
  for(j = 3; j < h.length; j+=3) {
   i = h.slice(h.length - j, h.length - j + 3);
   f[0] = d + i +  f[0] + '';
  }
  j = h.substr(0, (h.length % 3 == 0) ? 3 : (h.length % 3));
  f[0] = j + f[0];
 }
 c = (b <= 0) ? '' : c;
 return f[0] + c + f[1];
}
function addSN () {
	var lnjop = document.getElementById('land-njop').value;
	var larea = document.getElementById('land-area').value;
	var bnjop = document.getElementById('building-njop').value;
	var barea = document.getElementById('building-area').value
	
	if (isEmpty(document.getElementById('land-njop').value)) lnjop = 0;
	if (isEmpty(document.getElementById('land-area').value)) larea = 0;
	if (isEmpty(document.getElementById('building-area').value)) barea = 0;
	if (isEmpty(document.getElementById('building-njop').value)) bnjop = 0;
	
	var s1 = parseFloat(larea) * parseFloat(lnjop);
	var s2 = parseFloat(barea) * parseFloat(bnjop);
	
	var td1 = document.getElementById('t1');
	var td2 = document.getElementById('t3');
	var text =  document.createTextNode(number_format(s1, 2, '.', ','));
	removeFChild (td1);
	removeFChild (td2);
	if (s==null) text = document.createTextNode("");
	var text2 =  document.createTextNode(number_format(s1+s2, 2, '.', ','));
	td1.appendChild(text);
	td2.appendChild(text2);
	NPOPKP ();
}

function addET () {
	var lnjop = document.getElementById('land-njop').value;
	var larea = document.getElementById('land-area').value;
	var bnjop = document.getElementById('building-njop').value;
	var barea = document.getElementById('building-area').value
	
	if (isEmpty(document.getElementById('land-njop').value)) lnjop = 0;
	if (isEmpty(document.getElementById('land-area').value)) larea = 0;
	if (isEmpty(document.getElementById('building-area').value)) barea = 0;
	if (isEmpty(document.getElementById('building-njop').value)) bnjop = 0;
	
	var s1 = parseFloat(larea) * parseFloat(lnjop);
	var s2 = parseFloat(barea) * parseFloat(bnjop);
	
	var td1 = document.getElementById('t2');
	var td2 = document.getElementById('t3');
	var text =  document.createTextNode(number_format(s2, 2, '.', ','));
	removeFChild (td1);
	removeFChild (td2);
	if (s==null) text = document.createTextNode("");
	var text2 =  document.createTextNode(number_format(s1+s2, 2, '.', ','));
	td1.appendChild(text);
	td2.appendChild(text2);
	NPOPKP ();
}

function NPOPKP () {
	var s1 = parseFloat(document.getElementById('land-area').value) * parseFloat(document.getElementById('land-njop').value);
	var s2 = parseFloat(document.getElementById('building-area').value) * parseFloat(document.getElementById('building-njop').value);
	var t = parseFloat(document.getElementById('trans-value').value);
	var NPOPTKP = parseFloat(document.getElementById('NPOPTKP').value);
	var NPOPKP=0;
	if ((s1+s2) > t ) {
		NPOPKP = (s1+s2) - NPOPTKP;
	} else {
		NPOPKP = t - NPOPTKP;
	}
	
	var ttext =  document.createTextNode(number_format(NPOPKP, 2, '.', ','));
	var tNPOPKP = document.getElementById('tNPOPKP');
	removeFChild (tNPOPKP);
	tNPOPKP.appendChild(ttext);
	//if(edit)document.getElementById('hd-npoptkp').value = NPOPKP;
}

function hitungBPHTB() {
	var a = $("#f_nilai_pajak").val();
	var b = $("#f_noptk").val();
	var c = ((a-b)>0) ?(a-b):0; 
	var d = c * 0.05;
	$("#f_pengurangan").val(c);
	$("#f_bphtb").val(d);
}

$(document).ready(function() {
	// put all your jQuery goodness in here.
	//hideDiv("");
	//defaValue("");
});


