var cNPOPTKP = 0;
var xOk = false;
function checkform()
{
    var f = document.getElementById('form-notaris');
    var els = f.elements;
    var err = '';
    var rg = document.getElementsByName("RadioGroup1");
    var rb = getCheckedValue(rg);
    var imgEx = Array();

    for (var i = 0, len = els.length; i < len; i++) {
        x = els[i];


        if (x.type == 'text') {
            var ge = document.getElementById('err_' + i);
            if (ge) {
                x.parentNode.removeChild(ge);
            }
            if (els.item(i).id == 'noktp') {
                if (els.item(i).value.length < 16) {
                    var ge = document.getElementById('err_' + i);
                    if (!ge) {
                        imgEx[i] = document.createElement("img");
                        imgEx[i].setAttribute("src", "./image/icon/exclamation.png");
                        imgEx[i].setAttribute("title", x.title + ' belum diisi atau kurang dari 16 digit (tanpa tanda pemisah)!');
                        imgEx[i].setAttribute("id", 'err_' + i);
                        x.parentNode.appendChild(imgEx[i]);
                    }
                    err += x.title + ' belum diisi atau kurang dari 16 digit (tanpa tanda pemisah)!\n'
                }
            }
            if (els.item(i).id == 'name2') {
                if (els.item(i).value.length < 18) {
                    var ge = document.getElementById('err_' + i);
                    if (!ge) {
                        imgEx[i] = document.createElement("img");
                        imgEx[i].setAttribute("src", "./image/icon/exclamation.png");
                        imgEx[i].setAttribute("title", x.title + ' belum diisi atau kurang dari 18 digit (tanpa tanda pemisah)!');
                        imgEx[i].setAttribute("id", 'err_' + i);
                        x.parentNode.appendChild(imgEx[i]);
                    }
                    err += x.title + ' belum diisi atau kurang dari 18 digit (tanpa tanda pemisah)!\n'
                }
            }
            if (x.value == '') {
                //35,36,37
                if ((i != 30) && (i != 31) && (i != 33) && (i != 34)) {
                    var ge = document.getElementById('err_' + i);
                    if (!ge) {
                        imgEx[i] = document.createElement("img");
                        imgEx[i].setAttribute("src", "./image/icon/exclamation.png");
                        imgEx[i].setAttribute("title", x.title + ' belum diisi!');
                        imgEx[i].setAttribute("id", 'err_' + i);
                        x.parentNode.appendChild(imgEx[i]);
                    }
                    err += x.title + ' belum diisi!\n'
                }
                ;

                if (rb == 2) {
                    if ((i == 30) || (i == 31)) {
                        var ge = document.getElementById('err_' + i);
                        if (!ge) {
                            imgEx[i] = document.createElement("img");
                            imgEx[i].setAttribute("src", "./image/icon/exclamation.png");
                            imgEx[i].setAttribute("title", x.title + ' belum diisi!');
                            imgEx[i].setAttribute("id", 'err_' + i);
                            x.parentNode.appendChild(imgEx[i]);
                        }
                        err += x.title + ' belum diisi!\n';
                    }
                }
                if (rb == 3) {
                    if ((i == 33) || (i == 34)) {
                        var ge = document.getElementById('err_' + i);
                        if (!ge) {
                            imgEx[i] = document.createElement("img");
                            imgEx[i].setAttribute("src", "./image/icon/exclamation.png");
                            imgEx[i].setAttribute("title", x.title + ' belum diisi!');
                            imgEx[i].setAttribute("id", 'err_' + i);
                            x.parentNode.appendChild(imgEx[i]);
                        }
                        err += x.title + ' belum diisi!\n';
                    }
                }
            } else {
                /*var ge = document.getElementById('err_'+i);
                 if (ge) {
                 x.parentNode.removeChild(ge);
                 }*/
            }
        }
		if (x.name == 'address') {
                if (x.value.trim() == "") {
                    var ge = document.getElementById('err_' + i);
                    if (!ge) {
                        imgEx[i] = document.createElement("img");
                        imgEx[i].setAttribute("src", "./image/icon/exclamation.png");
                        imgEx[i].setAttribute("title", x.title + ' belum diisi!');
                        imgEx[i].setAttribute("id", 'err_' + i);
                        x.parentNode.appendChild(imgEx[i]);
                    }
                    err += x.title + ' belum diisi!\n';
                }
            }
        if (rb == 4) {
            if (x.name == 'jsb-etc') {
                if (x.value == "") {
                    var ge = document.getElementById('err_' + i);
                    if (!ge) {
                        imgEx[i] = document.createElement("img");
                        imgEx[i].setAttribute("src", "./image/icon/exclamation.png");
                        imgEx[i].setAttribute("title", x.title + ' belum diisi!' + i);
                        imgEx[i].setAttribute("id", 'err_' + i);
                        x.parentNode.appendChild(imgEx[i]);
                    }
                    err += x.title + ' belum diisi!\n';
                }
            }
            ;
        }
    }

    //if (rb=='') err += 'Jumlah Setoran Berdasarkan belum dipilih!';
    if (err != '') {
        alert(err);
        checkTransLast();
        return false;
    }
    hideMask();
    return true;
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
    if ((key == null) || (key == 0) || (key == 8) ||
            (key == 9) || (key == 27))
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
        for (i = f[1].length + 1; i <= b; i++) {
            g += '0';
        }
        f[1] = g;
    }
    if (d != '' && f[0].length > 3) {
        h = f[0];
        f[0] = '';
        for (j = 3; j < h.length; j += 3) {
            i = h.slice(h.length - j, h.length - j + 3);
            f[0] = d + i + f[0] + '';
        }
        j = h.substr(0, (h.length % 3 == 0) ? 3 : (h.length % 3));
        f[0] = j + f[0];
    }
    c = (b <= 0) ? '' : c;
    return f[0] + c + f[1];
}

function removeFChild(td) {
    if (td != null) {
        if (td.hasChildNodes())
        {
            while (td.childNodes.length >= 1)
            {
                td.removeChild(td.firstChild);
            }
        }
    }
}

/*function NPOPKP () {
 var s1 = parseFloat(document.getElementById('land-area').value) * parseFloat(document.getElementById('land-njop').value);
 var s2 = parseFloat(document.getElementById('building-area').value) * parseFloat(document.getElementById('building-njop').value);
 var t = parseFloat(document.getElementById('trans-value').value);
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
 
 }*/

function warisNonWaris() {
    var sel = document.getElementById("right-land-build");
    var d = sel.options[sel.selectedIndex].value;
    //console.log(d);
    if (d == 5) {
        return true;
    }
    return false;
}

function getCheckedValue(radioObj) {
    if (!radioObj)
        return "";
    var radioLength = radioObj.length;
    if (radioLength == undefined)
        if (radioObj.checked)
            return radioObj.value;
        else
            return "";
    for (var i = 0; i < radioLength; i++) {
        if (radioObj[i].checked) {
            return radioObj[i].value;
        }
    }
    return "";
}

function enableE(t, p) {
    var rg = document.getElementsByName("RadioGroup1");

    if (t.checked) {
        document.getElementById("jsb-choose").setAttribute("disabled", "disabled");
        document.getElementById("jsb-choose-number").setAttribute("disabled", "disabled");
        document.getElementById("jsb-choose-role-number").setAttribute("disabled", "disabled");
        document.getElementById("jsb-choose-date").setAttribute("disabled", "disabled");
        document.getElementById("jsb-choose-percent").setAttribute("disabled", "disabled");
        document.getElementById("jsb-etc").setAttribute("disabled", "disabled");
        document.getElementById("jsb-choose-percent").value = 0;
		document.getElementById("jsb-choose-fraction1").value = "";
		document.getElementById("jsb-choose-fraction2").value = "";
        if (p == 1) {
            document.getElementById("jsb-choose").removeAttribute("disabled");
            document.getElementById("jsb-choose-number").removeAttribute("disabled");
            document.getElementById("jsb-choose-date").removeAttribute("disabled");
        }

        if (p == 2) {
            document.getElementById("jsb-choose-percent").removeAttribute("disabled");
            document.getElementById("jsb-choose-role-number").removeAttribute("disabled");
        }
        if (p == 3) {
            document.getElementById("jsb-etc").removeAttribute("disabled");
        }
        checkTransaction();
    }
}

function isEmpty(str) {
    return (!str || 0 === str.length);
}
function hitungpersen(){
   var persen= document.getElementById('tBPHTB_BAYAR_PERSEN').value;
//    var harga= document.getElementById('harusbayar').value;
   var harusbayarText = document.getElementById('harusbayar').textContent;
   var harga = parseFloat(harusbayarText.replace(/,/g, '')); // Remove commas and convert to a number
   
//    console.log(harga);
   
   hitung = harga * persen/100
   document.getElementById('tBPHTB_BAYAR').value=hitung;
   checkTransaction();
   
}
function numbersonly1(myfield, e, dec) {
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
    if ((key == null) || (key == 0) || (key == 8) ||
            (key == 9) || (key == 27))
        return true;

    // numbers and decimal point
    else if ((("0123456789.").indexOf(keychar) > -1)) {
        // Get the current input value and combine it with the new key character
        var inputValue = myfield.value + keychar;
        
        // Check if the resulting value is greater than 100
        if (parseFloat(inputValue) <= 100){

            return true;
        }else{
            return false;
        }
    }
   

    // decimal point jump
    else if (dec && (keychar == ".")) {
        myfield.form.elements[dec].focus();
        return false;
    } else
        return false;
        // console.log(inputValue);
}



function hitungpersen1() {
    var persenInput = document.getElementById('tBPHTB_BAYAR_PERSEN');
    var persenValue = persenInput.value;

    // Remove any non-numeric characters except for the decimal point
    persenValue = persenValue.replace(/[^0-9.]/g, '');

    // Ensure persen value is within the range of 0 to 100
    var persen = parseFloat(persenValue);
    persen = Math.min(persen, 100); // Limit to maximum 100

    persenInput.value = persen.toFixed(2); // Set value with 2 decimal places

    var harusbayarText = document.getElementById('harusbayar').textContent;
    var harusbayar = parseFloat(harusbayarText.replace(/,/g, ''));

    var hitung = (harusbayar * persen) / 100;
    document.getElementById('tBPHTB_BAYAR').value = hitung.toFixed(2);
}


function checkTransaction() {
    var lnjop = document.getElementById('land-njop').value;
    var larea = document.getElementById('land-area').value;
    var bnjop = document.getElementById('building-njop').value;
    var barea = document.getElementById('building-area').value
    var tvalue = document.getElementById('trans-value').value;
	var tdenda = document.getElementById('denda-value').value;
	var pdenda = document.getElementById('denda-percent').value;
    //var jcp = document.getElementById('jsb-choose-percent').value;
// console.log(bnjop);
    if (isEmpty(document.getElementById('land-njop').value))
        lnjop = 0;
    if (isEmpty(document.getElementById('land-area').value))
        larea = 0;
    if (isEmpty(document.getElementById('building-area').value))
        barea = 0;
    if (isEmpty(document.getElementById('building-njop').value))
        bnjop = 0;
    if (isEmpty(document.getElementById('trans-value').value))
        tvalue = 0;
	if (isEmpty(document.getElementById('denda-value').value)) tdenda = 0;
	if (isEmpty(document.getElementById('denda-percent').value)) pdenda = 0;
    //if (isEmpty(document.getElementById('jsb-choose-percent').value)) jcp =0;


    var s1 = parseFloat(larea) * parseFloat(lnjop);
    var s2 = parseFloat(barea) * parseFloat(bnjop);
    var t = parseFloat(tvalue);
	var denda = parseFloat(tdenda);
	var percentdenda = parseFloat(pdenda);
    //var per = parseFloat(jcp);tNPOPTKP
    var eNPOPTKP = document.getElementById('NPOPTKP');
    var tNPOPTKP = document.getElementById('tNPOPTKP').value;
    var tNJOP = document.getElementById('tNJOP');
    var akumulasi = document.getElementById('akumulasi');
    var tBPHTBT = document.getElementById('harusbayar');
	var tBPHTBTS = document.getElementById('tBPHTBTS');
	var Phibahwaris = document.getElementById('Phibahwaris').value;
	var bphtbsb = document.getElementById('tBPHTB_BAYAR').value;
	if (isEmpty(document.getElementById('tBPHTB_BAYAR').value))
        bphtbsb = 0;
	var tBPHTBTU = document.getElementById('tBPHTBTU').value;
	var tPengenaan = document.getElementById('tPengenaan');
	var tAPHB = document.getElementById('tAPHB');
    var tWasiat = document.getElementById('tWasiat');
    var tTotal = document.getElementById('tTotal');
    var eNPOPKP = document.getElementById('tNPOPKP');
	var harusbayar = document.getElementById('harusbayar');
    var text = document.createTextNode("0");
    var text2 = document.createTextNode("0");
    var bphtbt = document.createTextNode("0");
	var bphtbt1 = document.createTextNode("0");
	var bphtbts = document.createTextNode("0");
	var pengenaan = document.createTextNode("0");
	var APHB = document.createTextNode("0");
    var wasiat = document.createTextNode("0");
    var total = document.createTextNode("0");
    var noptkp = document.createTextNode(number_format(cNPOPTKP, 0, '.', ','));
    //var jsbtotalbefore = document.getElementById('jsb-total-before');
	var jsbtotalbefore = document.getElementById('tNPOP');
    var jpay = document.getElementById("jmlBayar");
	var jnsperolehan = $("#right-land-build").val();
	var aphb = $("#pengurangan-aphb").val();
    if (aphb === '') {
        aphb = '0/0';
    }
	var pec=aphb.split('/');
// console.log(pec);
    var w = 0;
    var NPOPKP = 0;

    var ctjpay = document.createTextNode("Jumlah yang dibayarkan :");
	
	if (jnsperolehan === "8") {
        jsbtotalbefore.value = t;
    }else{
		if ((s1 + s2) > t) {
			text = document.createTextNode(number_format(s1 + s2, 0, '.', ','));
			text2 = document.createTextNode(number_format(s1 + s2, 0, '.', ','));
			jsbtotalbefore.value = s1 + s2;

		} else {
			text = document.createTextNode(number_format(t, 0, '.', ','));
			text2 = document.createTextNode(number_format(t, 0, '.', ','));
			jsbtotalbefore.value = t;

		}
	}
// console.log(jsbtotalbefore.value);
    
    var m = jsbtotalbefore.value - cNPOPTKP;
    if (m <= 0) {
        // console.log("1 "+cNPOPTKP); 
        NPOPKP = 0;
        bphtbt = document.createTextNode(number_format(0, 0, '.', ','));
        //if (per!=0) ctjpay = document.createTextNode("Jumlah yang dibayarkan : "+number_format(0*per*0.01, 2, '.', ','));
        //else 
        ctjpay = document.createTextNode("Jumlah yang dibayarkan : " + number_format(0, 0, '.', ','));
    }
    else {
        // console.log("2 "+m); 
		denda = NPOPKP*percentdenda*0.05/100;
        NPOPKP = jsbtotalbefore.value - tNPOPTKP;
        //  console.log(jsbtotalbefore.value+'  '+ tNPOPTKP); 
        bphtbts = document.createTextNode(number_format(NPOPKP * 0.05, 0, '.', ','));
		bphtbt = document.createTextNode(number_format((NPOPKP*0.05)+denda, 0, '.', ','));
       
//  console.log(bphtbt); 
//  console.log(jnsperolehan); 
        
		if((jnsperolehan=="4")||(jnsperolehan=="5")||(jnsperolehan=="31")){
			if(configpengenaan=='1'){
				pengenaan = document.createTextNode(number_format(NPOPKP * 0.05 * Phibahwaris * 0.01, 0, '.', ','));
				APHB=document.createTextNode(number_format(0, 0, '.', ','));
				hpengenaan = NPOPKP * 0.05 * Phibahwaris * 0.01;
				 //alert(Phibahwaris);
				hbphtbt = ((NPOPKP * 0.05)-hpengenaan);
				denda = hbphtbt*percentdenda/100;
				bphtbt1= hbphtbt+denda;
				bphtbt = document.createTextNode(number_format((NPOPKP * 0.05)-(NPOPKP * 0.05 * Phibahwaris * 0.01)+denda, 0, '.', ','));
			}else{
				pengenaan = document.createTextNode(number_format(0, 0, '.', ','));
				APHB=document.createTextNode(number_format(0, 0, '.', ','));
				denda = (NPOPKP*0.05)*percentdenda/100;
				bphtbt1= (NPOPKP * 0.05)+denda;
				bphtbt = document.createTextNode(number_format((NPOPKP * 0.05)+denda, 0, '.', ','));
                
			}
				ctjpay = document.createTextNode("Jumlah yang dibayarkan : " + number_format((NPOPKP * 0.05)-(NPOPKP * 0.05* Phibahwaris * 0.01)+denda, 0, '.', ','));
		}else if(jnsperolehan=="33" || jnsperolehan=="7"){
			pengenaan = document.createTextNode(number_format(0, 0, '.', ','));
       

			if(configaphb=='1'){
				if(hitungaphb=='1'){
					APHB = document.createTextNode(number_format((NPOPKP * 0.05 * pec[0]/pec[1]), 0, '.', ','));
                    if (isNaN(APHB)) {
                        APHB =document.createTextNode( number_format(0, 0, '.', ','));
                    }
					denda = (NPOPKP * 0.05 *0.5)*percentdenda/100;
					// bphtbt = document.createTextNode(number_format((NPOPKP * 0.05 * pec[0]/pec[1])+denda, 0, '.', ','));
					bphtbt = document.createTextNode(number_format((NPOPKP * 0.05 )*0.5, 0, '.', ','));
					hitbphtb = (NPOPKP * 0.05)*0.5;
                //  console.log(bphtbt ); 
				}else if(hitungaphb=='2'){
					APHB = document.createTextNode(number_format((NPOPKP * 0.05)-(NPOPKP * 0.05 * pec[0]/pec[1]), 0, '.', ','));
					denda = ((NPOPKP * 0.05)-(NPOPKP * 0.05 * pec[0]/pec[1]))*percentdenda/100;
					bphtbt = document.createTextNode(number_format((NPOPKP * 0.05)-(NPOPKP * 0.05 * pec[0]/pec[1])+denda, 0, '.', ','));
					hitbphtb = (NPOPKP * 0.05)-(NPOPKP * 0.05 * pec[0]/pec[1])+denda
				}else if(hitungaphb=='3'){
					text=document.createTextNode(number_format((jsbtotalbefore.value * pec[0]/pec[1]), 0, '.', ','));
					NPOP_APHB = jsbtotalbefore.value * pec[0]/pec[1];
					denda = ((NPOP_APHB-cNPOPTKP) * 0.05)*percentdenda/100;
					APHB = document.createTextNode(number_format(((NPOP_APHB-cNPOPTKP) * 0.05)+denda, 0, '.', ','));
					bphtbt = document.createTextNode(number_format(((NPOP_APHB-cNPOPTKP) * 0.05)+denda, 0, '.', ','));
					hitbphtb = (NPOP_APHB-cNPOPTKP) * 0.05;
					NPOPKP = NPOP_APHB-cNPOPTKP;
				}else if(hitungaphb=='0'){
					APHB=document.createTextNode(number_format(0, 0, '.', ','));
					denda = (NPOPKP*0.05)*percentdenda/100;
					bphtbt = document.createTextNode(number_format((NPOPKP*0.05)+denda, 0, '.', ','));
					hitbphtb = (NPOPKP*0.05)+denda;
				}
			}else{
					APHB=document.createTextNode(number_format(0, 0, '.', ','));
					denda = (NPOPKP*0.05)*percentdenda/100;
					bphtbt = document.createTextNode(number_format((NPOPKP*0.05)+denda, 0, '.', ','));
					hitbphtb = (NPOPKP*0.05)+denda;
			}
			
            // hitbphtb=  tBPHTBTU;
			bphtbt1= hitbphtb;
			bphtbt = document.createTextNode(number_format(hitbphtb, 0, '.', ','));
            // console.log(tBPHTBTU);
            // console.log(s1 + s2);
           var cek=$("#tBPHTB_BAYAR").val();
           console.log(cek);


			
				ctjpay = document.createTextNode("Jumlah yang dibayarkan  : " + number_format(hitbphtb-cek , 0, '.', ','));
		}else{
			pengenaan = document.createTextNode(number_format(0, 0, '.', ','));
			APHB=document.createTextNode(number_format(0, 0, '.', ','));
			bphtbt1= (NPOPKP * 0.05)+denda;
			bphtbt = document.createTextNode(number_format((NPOPKP * 0.05)+denda, 0, '.', ','));
            var cek=$("#tBPHTB_BAYAR").val();
            // console.log(cek);
 
				ctjpay = document.createTextNode("Jumlah yang dibayarkan : " + number_format((NPOPKP * 0.05)-cek, 0, '.', ','));
		}
		
        
    }

    var npopkp = document.createTextNode(number_format(NPOPKP, 0, '.', ','));

    //text2= text;
    removeFChild(eNPOPTKP);
    removeFChild(eNPOPKP);
    removeFChild(tNJOP);
    removeFChild(akumulasi);
    removeFChild(tBPHTBT);
	removeFChild(tPengenaan);
	removeFChild(tAPHB);
    removeFChild(tWasiat);
    removeFChild(jpay);
	removeFChild(harusbayar);
	removeFChild(tBPHTBTS);
    //removeFChild (tTotal);
	var bphtbkb = bphtbt1 - bphtbsb;
	if (isNaN(bphtbkb)) {
		document.getElementById('tBPHTBTU').value=0;
	}else{
		document.getElementById('tBPHTBTU').value=bphtbkb;
	}
	
	document.getElementById('bphtbtu').value=bphtbt1;
    //tTotal.appendChild(total);
    //tWasiat.appendChild(wasiat);
    eNPOPKP.appendChild(npopkp)
    //eNPOPTKP.appendChild(noptkp)
    /*tNJOP.appendChild(text);
     akumulasi.appendChild(text2);
     tBPHTBT.appendChild(bphtbt);*/
     jpay.appendChild(ctjpay);
	tBPHTBT.appendChild(bphtbt);
	tBPHTBTS.appendChild(bphtbts);
	tPengenaan.appendChild(pengenaan);
	tAPHB.appendChild(APHB);

}
function cekAPHB(){
	var sel = document.getElementById("right-land-build");
    var d = sel.options[sel.selectedIndex].value;
	if(configaphb=='1'){
		if(d==33 || d==7){
			$("#pengurangan-aphb").removeAttr("disabled", "disabled");
			document.getElementById("pengurangan-aphb").selectedIndex = "1"; 
		}else{
			$("#pengurangan-aphb").attr("disabled", "disabled");
			document.getElementById("pengurangan-aphb").selectedIndex = "0";
		}
	}else{
		$("#pengurangan-aphb").attr("disabled", "disabled");
		document.getElementById("pengurangan-aphb").selectedIndex = "0";
	}
}
function addSN() {
    var lnjop = document.getElementById('land-njop').value;
    var larea = document.getElementById('land-area').value;
    var bnjop = document.getElementById('building-njop').value;
    var barea = document.getElementById('building-area').value


    if (isEmpty(document.getElementById('land-njop').value))
        lnjop = 0;
    if (isEmpty(document.getElementById('land-area').value))
        larea = 0;
    if (isEmpty(document.getElementById('building-area').value))
        barea = 0;
    if (isEmpty(document.getElementById('building-njop').value))
        bnjop = 0;

    var s1 = parseFloat(larea) * parseFloat(lnjop);
    var s2 = parseFloat(barea) * parseFloat(bnjop);

    var td1 = document.getElementById('t1');
    var td2 = document.getElementById('t3');

    var text = document.createTextNode(number_format(s1, 0, '.', ','));
    removeFChild(td1);
    removeFChild(td2);
    var text2 = document.createTextNode(number_format(s1 + s2, 0, '.', ','));
    td1.appendChild(text);
    td2.appendChild(text2);
}

function addET() {
    var lnjop = document.getElementById('land-njop').value;
    var larea = document.getElementById('land-area').value;
    var bnjop = document.getElementById('building-njop').value;
    var barea = document.getElementById('building-area').value

    if (isEmpty(document.getElementById('land-njop').value))
        lnjop = 0;
    if (isEmpty(document.getElementById('land-area').value))
        larea = 0;
    if (isEmpty(document.getElementById('building-area').value))
        barea = 0;
    if (isEmpty(document.getElementById('building-njop').value))
        bnjop = 0;

    var s1 = parseFloat(larea) * parseFloat(lnjop);
    var s2 = parseFloat(barea) * parseFloat(bnjop);

    var td1 = document.getElementById('t2');
    var td2 = document.getElementById('t3');
    var text = document.createTextNode(number_format(s2, 0, '.', ','));
    removeFChild(td1);
    removeFChild(td2);
    var text2 = document.createTextNode(number_format(s1 + s2, 0, '.', ','));
    td1.appendChild(text);
    td2.appendChild(text2);
    //NPOPKP ();
}

function copyPasteAddress() {
    var address1 = document.getElementById('address').value;
    var kelurahan1 = document.getElementById("kelurahan").value;
    var rt1 = document.getElementById("rt").value;
    var rw1 = document.getElementById("rw").value;
    var kecamatan1 = document.getElementById("kecamatan").value;
    var kabupaten1 = document.getElementById("kabupaten").value;
    var zipcode1 = document.getElementById("zip-code").value;
    document.getElementById("address2").value = address1;
    document.getElementById("kelurahan2").value = kelurahan1;
    document.getElementById("rt2").value = rt1;
    document.getElementById("rw2").value = rw1;
    document.getElementById("kecamatan2").value = kecamatan1;
    document.getElementById("kabupaten2").value = kabupaten1;
    document.getElementById("zip-code2").value = zipcode1;
}

function nextFocus(el, e) {
    if (e.keyCode != 13) {
        return;
    }
    var f = el.form;
    var els = f.elements;
    var x, nextEl;
    for (var i = 0, len = els.length; i < len; i++) {
        x = els[i];
        if (el == x && (nextEl = els[i + 1])) {
            if (nextEl.focus)
                nextEl.focus();
        }
    }
    return false;
}


function loadNPOPTKP() {
    //showMask();

    var sel = document.getElementById("right-land-build");
    var d = sel.options[sel.selectedIndex].value;
    var noktp = document.getElementById("noktp").value;
    var trsid = document.getElementById("trsid").value;
    var nop = document.getElementById("name2").value;
    var verdoc = document.getElementById("ver-doc").value;
    Ext.Ajax.request({
        url: './function/BPHTB/pengurangan/svc-npoptkp.php',
        method: 'POST',
        params: {id: d, axx: axx},
        params: {id: d, axx: axx, noktp: noktp,verdoc:verdoc,trsid:trsid,nop:nop},
        success: function(result, request) {
            var jsonData = $.parseJSON(result.responseText);
            if (jsonData.success) {

                if (!xOk)
                    cNPOPTKP = jsonData.result;
                else
                    cNPOPTKP = 0;
                if (warisNonWaris())
                    cNPOPTKP = jsonData.result;
                //console.log(warisNonWaris());
                checkTransaction();
                hideMask();
                document.getElementById('tNPOPTKP').value = cNPOPTKP;
                //console.log(cNPOPTKP);

            }
        },
        failure: function(result, request) {

        }
    });
}

function hideMask() {
    hideDialog();
}

function showMask() {
    //console.log(showDialog);
    showDialog('Load', '<img src="image/large-loading.gif" width="32" height="32" style="margin-right:8px;" align="absmiddle"/>Tunggu', 'prompt', false, true);
    //alert("");
}

function setCheckedValue(radioObj, newValue) {
    if (!radioObj)
        return;
    var radioLength = radioObj.length;
    if (radioLength == undefined) {
        radioObj.checked = (radioObj.value == newValue.toString());
        return;
    }
    for (var i = 0; i < radioLength; i++) {
        radioObj[i].checked = false;
        if (radioObj[i].value == newValue.toString()) {
            radioObj[i].checked = true;
        }
    }
}

function getCheckedObject(radioObj) {
    if (!radioObj)
        return "";
    var radioLength = radioObj.length;
    if (radioLength == undefined)
        if (radioObj.checked)
            return radioObj;
        else
            return "";
    for (var i = 0; i < radioLength; i++) {
        if (radioObj[i].checked) {
            return radioObj[i];
        }
    }
    return "";
}

var loop = 0;
var loop2 = 0;
var cloop = 3
function sendNames(i) {
    loop--;
    var n = $("#name2").val();
    var sel = $("#noktp").val();
    var prm = encodeBase64("{'noktp':'" + sel + "','axx':'" + axx + "','n':'" + n + "'}");
    $.ajax({
        url: './function/BPHTB/pengurangan/svc-check-noktp.php',
        data: {req: prm},
        dataType: 'json',
        success: function(result) {
            //console.log(result);
            // var jsonData = Ext.util.JSON.decode(result.responseText);
            if (result.success) {
                if (result.found)
                    xOk = true;
                else
                    xOk = false;
                loadNPOPTKP();
                hideMask();
                loop = 0;
                $("input[type=submit]").removeAttr("disabled");
            } else {
                $("input[type=submit]").attr("disabled", "disabled");
                alert("Error : Respon salah!");
            }
        },
        error: function() {
            if (loop2 == cloop) {
                loop2 = 0;
                //$("input[type=submit]").attr("disabled", "disabled");
                //alert ("Error : Terjadi gangguan koneksi ke server pusat, perhitungan tidak bisa dilakukan, periksa koneksi modem anda atau segera hubungi call center kami !");
            }
        }
    });
}

// function jumlah() {
    // var jml = $("#tNPOP").val() - $("#tNPOPTKP").val();
    // jml = (jml < 0) ? 0 : jml;
    // var td = document.getElementById("tNPOPKP");
    // removeFChild(td);
    // var t = document.createTextNode(number_format(jml, 0, '.', ','));
    // td.appendChild(t);
// }

function jumlah() {
    var jml = $("#tNPOP").val() - $("#tNPOPTKP").val();
    jml = (jml < 0) ? 0 : jml;
	jml2=jml*0.05;
    var td = document.getElementById("tNPOPKP");
	var td2 = document.getElementById("harusbayar");
	var td3 = document.getElementById("tBPHTB_BAYAR").value;
    removeFChild(td);
	removeFChild(td2);
    var t = document.createTextNode(number_format(jml, 0, '.', ','));
	var t2 = document.createTextNode(number_format(jml2, 0, '.', ','));
	//var t3 = document.createTextNode(number_format(jml2, 0, '.', ','));
	
    td.appendChild(t);
	td2.appendChild(t2);
	document.getElementById('tBPHTBTU').value=jml2-td3;
    console.log(td);
}

function checkTransLast() {
    showMask();
    loop = cloop;
    sendNames();
}

Ext.onReady(function() {
	if (isEmpty(document.getElementById('tBPHTBTU').value))
        document.getElementById('tBPHTBTU').value = 0;
    $('.currencyFormat').blur(function() {
        $('.currencyFormat').blur(function() {
            $(this).formatCurrency({
                decimalSymbol: '.',
                digitGroupSymbol: '',
                dropDecimals: false,
                groupDigits: true,
                symbol: ''
            });

            var nilai = $(this).asNumber();
            if (nilai == 0)
                $(this).val('0.00')
        });
    });
    checkTransLast();

});

function checkNOP(obj) {
    var val = $.trim($(obj).val().replace(/\_/g, ''));
    $('#errnop').remove();
    if (val.length < 18) {
        var errnop = $("<span id='errnop'><br> NOP anda : " + val + ", harus 18 digit</span>").css({'color': '#FF0000'})
        $(obj).val('').after(errnop);
        return false;
    }
    $(obj).blur();
    showMask();
    $.ajax({
        type: "post",
        data: "nop=" + val,
        url: "./function/BPHTB/pengurangan/svc-check-nop.php",
        dataType: "json",
        success: function(res) {

            if (res.message)
                // alert(res.message);

            $('#errnop').remove();
            if (res.denied)
                if (res.denied == 1) {
                    var errnop = $("<span id='errnop'><br>" + res.message + "</span>").css({'color': '#FF0000'})
                    $(obj).val('').after(errnop);
                } else {
                    $(obj).css({'color': '#000'});
                }
            else {
                $(obj).css({'color': '#000'});
            }
            hideMask();
        },
        error: hideMask(), // function(res){ hideMask();console.log(res) },
        failure: hideMask()// function(res){ hideMask();console.log(res) }

    });
}
function autoNOP(obj) { // get data berdasarkan nop, autofill
    var val = $.trim($(obj).val().replace(/\_/g, ''));

    $.ajax({
        type: "post",
        data: "nop=" + val,
        url: "./function/BPHTB/pengurangan/svc-auto-nop.php",
        dataType: "json",
        success: function(res) {
//            alert(res.data.address2);
            if (res.message)
                // alert(res.message);

            if (res.result == true) {
                $('#name').val(res.data.name).attr('readonly', 'true');
                $('#npwp').val(res.data.npwp).attr('readonly', 'true');
                $('#noktp').val(res.data.noktp).attr('readonly', 'true');
                $('#address').val(res.data.address).attr('readonly', 'true');
                $('#kelurahan').val(res.data.kelurahan).attr('readonly', 'true');
                $('#kecamatan').val(res.data.kecamatan).attr('readonly', 'true');
                $('#rt').val(res.data.rt2).attr('readonly', 'true');
                $('#rw').val(res.data.rw2).attr('readonly', 'true');
                $('#kabupaten').val(res.data.kabupaten2).attr('readonly', 'true');
                $('#zip-code').val(res.data.zipcode).attr('readonly', 'true');
                
                $('#nama-wp-lama').val(res.data.nama_wp_lama).attr('readonly', 'true');
                $('#nama-wp-cert').val(res.data.namawpcert).attr('readonly', 'true');
                $('#address2').val(res.data.address2).attr('readonly', 'true');
                $('#kelurahan2').val(res.data.kelurahan2).attr('readonly', 'true');
                $('#kecamatan2').val(res.data.kecamatan2).attr('readonly', 'true');
                $('#rt2').val(res.data.rt2).attr('readonly', 'true');
                $('#rw2').val(res.data.rw2).attr('readonly', 'true');
                $('#zip-code2').val(res.data.zipcode2).attr('readonly', 'true');
                $('#kabupaten2').val(res.data.kabupaten2).attr('readonly', 'true');
                
                $('#right-year').val(res.data.right_year);
                $('#land-njop').val(res.data.land_njop).attr('readonly', 'true');
                $('#building-njop').val(res.data.building_njop).attr('readonly', 'true');
                $('#land-area').val(res.data.land_area);
                $('#building-area').val(res.data.building_area); 
                $('#trans-value').val(res.data.trans_value);
                $('#right-land-build').val(res.data.right_land_build);
                $('#certificate-number').val(res.data.certificate_number);
                $('#akumulasi').val(res.data.akumulasi);
                $('#tNPOP').val(res.data.tNPOP);
                $('#tNPOPTKP').val(res.data.tNPOPTKP);
                $('#tBPHTBTU').val(res.data.tBPHTBTU);
				$('#pengurangan-aphb').val(res.data.penguranganaphb);
				if($('#right-land-build').val()==7 || $('#right-land-build').val()==33){
					$('#pengurangan-aphb').removeAttr('disabled');
				}else{
					$('#pengurangan-aphb').attr("disabled", "disabled");
				}
				$('#znt').val(res.data.znt);
				$('#tBPHTB_BAYAR').val(res.data.bphtb_dibayar);
				$('#id_ssb_sebelum').val(res.data.id_ssb_sebelum);
            }
//            }else{
//                
//                
//                $('#address2').val('').removeAttr('readonly');
//                $('#kelurahan2').val('').removeAttr('readonly');
//                $('#kecamatan2').val('').removeAttr('readonly');
//                $('#nama-wp-lama').val('').removeAttr('readonly');
//                $('#rt2').val('').removeAttr('readonly');
//                $('#rw2').val('').removeAttr('readonly');
//                $('#kabupaten2').val('').removeAttr('readonly');
//                $('#land-njop').val('').removeAttr('readonly');
//                $('#building-njop').val('').removeAttr('readonly');
//                $('#land-area').val('');
//                $('#building-area').val('');
//            }
            addSN();addET();checkTransaction();
        },
        error: /*hideMask(), */ function(res) {
            hideMask();
            console.log(res)
        },
        failure: /*hideMask(), */ function(res) {
            hideMask();
            console.log(res)
        }
    });
}