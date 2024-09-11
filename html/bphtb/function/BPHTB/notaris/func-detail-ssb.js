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

        var ge = document.getElementById('err_' + i);
        if (ge) {
            x.parentNode.removeChild(ge);
        }

        if (x.type == 'text') {
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
            
            if (els.item(i).id == 'certificate-number') {
                if (els.item(i).value.length < 5) {
                    var ge = document.getElementById('err_' + i);
                    if (!ge) {
                        imgEx[i] = document.createElement("img");
                        imgEx[i].setAttribute("src", "./image/icon/exclamation.png");
                        imgEx[i].setAttribute("title", x.title + ' belum diisi atau kurang dari 5 digit!');
                        imgEx[i].setAttribute("id", 'err_' + i);
                        x.parentNode.appendChild(imgEx[i]);
                    }
                    err += x.title + ' belum diisi atau kurang dari 5 digit!\n'
                }
            }
            if (x.value == '') {
                //35,36,37

                //if ((i != 30) && (i != 31) && (i != 32) && (i != 34) && (i != 35)) {
//                if ((i != 54) && (i != 55) && (i != 56) && (i != 58) && (i != 59)) {
				  if ((i != 30) && (i != 32)  && (i != 37) && (i != 38) && (i != 39) && (i != 3)&&(i != 11)&&(i != 14)&&(i != 47)&&(i != 48)&&(i != 49)) {
                    var ge = document.getElementById('err_' + i);
                    if (!ge) {
                        imgEx[i] = document.createElement("img");
                        imgEx[i].setAttribute("src", "./image/icon/exclamation.png");
                        imgEx[i].setAttribute("title", x.title + ' belum diisi!');
                        imgEx[i].setAttribute("id", 'err_' + i);
                        x.parentNode.appendChild(imgEx[i]);
                    }
                    err += x.title + ' belum diisi!'+i+'\n'
                }
                ;

                if (rb == 2) {
                    if ((i == 37) || (i == 38)) {
//                    if ((i == 55) || (i == 56)) {
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
				// else if (rb == 3) {
                    // if (i == 37) {
// //                    if ((i == 58) || (i == 59)) {
                        // var ge = document.getElementById('err_' + i);
                        // if (!ge) {
                            // imgEx[i] = document.createElement("img");
                            // imgEx[i].setAttribute("src", "./image/icon/exclamation.png");
                            // imgEx[i].setAttribute("title", x.title + ' belum diisi!');
                            // imgEx[i].setAttribute("id", 'err_' + i);
                            // x.parentNode.appendChild(imgEx[i]);
                        // }
                        // err += x.title + ' belum diisi!\n';
                    // }
                // }
				else if (rb == 5) {
//                    if ((i == 63) || (i == 64)) {
                    if ((i == 47) || (i == 48)) {
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
                        imgEx[i].setAttribute("title", x.title + ' belum diisi!');
                        imgEx[i].setAttribute("id", 'err_' + i);
                        x.parentNode.appendChild(imgEx[i]);
                    }
                    err += x.title + ' belum diisi!\n';
                }
            }
            ;
        }
    }

    if (rb == '')
        err += 'Jumlah Setoran Berdasarkan belum dipilih!';
    if (err != '') {
        alert(err);
        checkTransLast();
        return false;
    }
    hideMask();
    return true;
}
function loadLaikPasar() {
    //showMask();
	var sel = document.getElementById("right-land-build");
    var d = sel.options[sel.selectedIndex].value;
	var harga = document.getElementById("trans-value").value;
    var nop = document.getElementById("name2").value;
	var znt = document.getElementById("op-znt").value;
	var luas_tnh= document.getElementById("land-area").value;
	var njop_bgn= document.getElementById("building-njop").value;
	var luas_bgn= document.getElementById("building-area").value;
	var role= document.getElementById("role").value;
	var eNPOPTKP = document.getElementById("NPOPTKP");
	//	if(role!="rm1")||(role!="rmAdminBPHTB")||(role!="rAllBPHTB"){

			Ext.Ajax.request({
				url: './function/BPHTB/notaris/svc-cek-nilai-pasar.php',
				method: 'POST',
				params: {id: d,nop: nop, axx: axx, harga: harga, znt: znt, luas_tnh:luas_tnh, luas_bgn:luas_bgn, njop_bgn:njop_bgn, role:role},
				success: function(result, request) {
					var jsonData = JSON.parse(result.responseText);
					if (jsonData.success) {

						
							if(jsonData.flag==1){
							alert("Tidak Sesuai Dengan Harga Pasar");	
							//$("#w3-container").html(jsonData.result);
							$("#nilai-pasar").html("");
							$("#nilai-pasar").html("Loading...");
							$("#nilai-pasar").html(jsonData.result);
							//document.getElementById('id01').style.display='block';
							$("input[type=submit]").attr("disabled", "disabled");
							}else if(jsonData.flag==0){
								alert(jsonData.hasil);
								$("#nilai-pasar").html(jsonData.result);
								$("#nilai-pasar").html("");
								$("input[type=submit]").removeAttr("disabled");
							}else if(jsonData.flag==2){
								alert(jsonData.hasil);
								$("#nilai-pasar").html("Loading...");
								$("#nilai-pasar").html(jsonData.result);
								$("input[type=submit]").attr("disabled", "disabled");
							}else {
								
								$("input[type=submit]").removeAttr("disabled");
							}
						
                        loadNPOPTKP();
						checkTransaction();
						hideMask();

					}
				},
				failure: function(result, request) {

				}
			});
			
	//	}
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

function NPOPKP() {
    var s1 = parseFloat(document.getElementById('land-area').value) * parseFloat(document.getElementById('land-njop').value);
    var s2 = parseFloat(document.getElementById('building-area').value) * parseFloat(document.getElementById('building-njop').value);
    var t = parseFloat(document.getElementById('trans-value').value);
    var NPOPTKP = parseFloat(document.getElementById('NPOPTKP').innerHTML);
    var NPOPKP = 0;
    if ((s1 + s2) > t) {    
        NPOPKP = (s1 + s2) - NPOPTKP;

    } else {
        NPOPKP = t - NPOPTKP;
    }
    console.log("t:"+t+",NPOPTKP:"+NPOPTKP)
    var ttext = document.createTextNode(number_format(NPOPKP, 0, '.', ','));
    var tNPOPKP = document.getElementById('tNPOPKP');
    removeFChild(tNPOPKP);
    tNPOPKP.appendChild(ttext);
    //if(edit)document.getElementById('hd-npoptkp').value = NPOPKP;

}

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
function checkNOP() {
 var val = $.trim($("#name2").val());
 var msg=0;
    $('#errnop').remove();
    if (val.length < 18) {
        var errnop = $("<span id='errnop'><br> NOP anda : " + val + ", harus 18 digit</span>").css({'color': '#FF0000'}); 
        //$(obj).val('').after(errnop);
        return false;
    }
    $("#name2").blur();
    showMask();
    $.ajax({
        type: "post",
        data: "nop=" + val,
        url: "./function/BPHTB/notaris/svc-check-nop.php",
        dataType: "json",
        success: function(res) {
            
            if (res.message)
                alert(res.message);
                  
            $('#errnop').remove();
            if (res.denied)
                if (res.denied == 1) {
                   $(".draf").attr('disabled','disabled');
                    $(".final").attr('disabled','disabled');
                    
                    var errnop = $("<span id='errnop'><br>" + res.message + "</span>").css({'color': '#FF0000'});
                    
                    //$(obj).val('').after(errnop);
                //} else {                   
                  //  $(obj).css({'color': '#000'});
            //}else {
                //$(obj).css({'color': '#000'});
               
            }
           if (!res.denied){
              $(".draf").removeAttr('disabled');
              $(".final").removeAttr('disabled');
           }   
            
            hideMask();            
        },
        error: hideMask(), //function(res){ hideMask();console.log(res) },
        failure: hideMask() //function(res){ hideMask();console.log(res) }
    });
  
  // return msg;
}
function enableE(t, p) {
    var rg = document.getElementsByName("RadioGroup1");

    if (t.checked) {
        document.getElementById("jsb-choose").setAttribute("disabled", "disabled");
        document.getElementById("jsb-choose-number").setAttribute("disabled", "disabled");
        document.getElementById("jsb-choose-role-number").setAttribute("disabled", "disabled");
        document.getElementById("jsb-choose-date").setAttribute("disabled", "disabled");
        document.getElementById("jsb-choose-percent").setAttribute("disabled", "disabled");
		document.getElementById("jsb-choose-fraction1").setAttribute("disabled", "disabled");
		document.getElementById("jsb-choose-fraction2").setAttribute("disabled", "disabled");
        document.getElementById("jsb-etc").setAttribute("disabled", "disabled");
        document.getElementById("jsb-choose-percent").selectedIndex = "0"; 
		document.getElementById("jsb-choose-date").setAttribute("disabled", "disabled");
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
		if (p == 4) {
            document.getElementById("jsb-choose-fraction1").removeAttribute("disabled");
            document.getElementById("jsb-choose-fraction2").removeAttribute("disabled");
        }
        checkTransaction();
    }
}

function isEmpty(str) {
    return (!str || 0 === str.length);
}

function checkTransaction() {
    var lnjop = document.getElementById('land-njop').value;
    var larea = document.getElementById('land-area').value;
    var bnjop = document.getElementById('building-njop').value;
    var barea = document.getElementById('building-area').value
    var tvalue = document.getElementById('trans-value').value;
	var tdenda = document.getElementById('denda-value').value;
	var pdenda = document.getElementById('denda-percent').value;

	var jcf1 = document.getElementById('jsb-choose-fraction1').value;
	var jcf2 = document.getElementById('jsb-choose-fraction2').value;

    var jcp = document.getElementById('jsb-choose-percent').value;
   
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
    if (isEmpty(document.getElementById('jsb-choose-percent').value))
        jcp = 0;
	if (isEmpty(document.getElementById('jsb-choose-fraction1').value))
        jcf1 = 0;
	if (isEmpty(document.getElementById('jsb-choose-fraction2').value))
        jcf2 = 0;
	if (isEmpty(document.getElementById('denda-value').value)) tdenda = 0;
	if (isEmpty(document.getElementById('denda-percent').value)) pdenda = 0;
	
    var s1 = parseFloat(larea) * parseFloat(lnjop);
    var s2 = parseFloat(barea) * parseFloat(bnjop);
    var t = parseFloat(tvalue);
    var per = parseFloat(jcp);
	var frac1 = parseFloat(jcf1);
	var frac2 = parseFloat(jcf2);
	var denda = parseFloat(tdenda);
	var percentdenda = parseFloat(pdenda);
    //var NPOPTKP = parseFloat(document.getElementById('NPOPTKP').value);
    var eNPOPTKP = document.getElementById('NPOPTKP');
    var tNJOP = document.getElementById('tNJOP');
    var akumulasi = document.getElementById('akumulasi');
    var tBPHTBT = document.getElementById('tBPHTBT');
	var tBPHTBTS = document.getElementById('tBPHTBTS');
	var Phibahwaris = document.getElementById('Phibahwaris').value;
	var tPengenaan = document.getElementById('tPengenaan');
	var tAPHB = document.getElementById('tAPHB');
    var tWasiat = document.getElementById('tWasiat');
    var tTotal = document.getElementById('tTotal');
    var eNPOPKP = document.getElementById('tNPOPKP');
    var text = document.createTextNode("0");
    var text2 = document.createTextNode("0");
	var bphtbts = document.createTextNode("0");
	var pengenaan = document.createTextNode("0");
	var APHB = document.createTextNode("0");
    var bphtbt = document.createTextNode("0");
    var wasiat = document.createTextNode("0");
    var total = document.createTextNode("0");
    var noptkp = document.createTextNode(number_format(cNPOPTKP, 0, '.', ','));
    var jsbtotalbefore = document.getElementById('jsb-total-before');
    var jpay = document.getElementById("jmlBayar");
	var jnsperolehan = $("#right-land-build").val();
	var aphb = $("#pengurangan-aphb").val();
	var pec=aphb.split('/');
    var w = 0;
    var NPOPKP = 0;
    //if (edit) eNPOPTKP.value = cnpoptkp;
//    if(tvalue <= tNJOP)
//    {
//       alert('Nilai transkasi sama atau lebih kecil dari NPOP');    
//    }
    var ctjpay = document.createTextNode("Jumlah yang dibayarkan :");

    if (jnsperolehan != "8"){
		if ((s1 + s2) > t) {
			text = document.createTextNode(number_format(s1 + s2, 0, '.', ','));
			text2 = document.createTextNode(number_format(s1 + s2, 0, '.', ','));
			jsbtotalbefore.value = s1 + s2;

		} else {
			text = document.createTextNode(number_format(t, 0, '.', ','));
			text2 = document.createTextNode(number_format(t, 0, '.', ','));
			jsbtotalbefore.value = t;
		}
    
    }else{
		text2 = document.createTextNode(number_format(tvalue, 0, '.', ','));
        text = document.createTextNode(number_format(tvalue, 0, '.', ','));
		jsbtotalbefore.value = tvalue;
	}
    
    var new_njop = jsbtotalbefore.value;
    if (jnsperolehan==7) {
        new_njop = jsbtotalbefore.value * (pec[0]/pec[1]);
    }
    // var m = jsbtotalbefore.value - cNPOPTKP;
    var m = new_njop - cNPOPTKP;
    if (m <= 0) {
        NPOPKP = 0;
        bphtbt = document.createTextNode(number_format(0, 0, '.', ','));        
        ctjpay = document.createTextNode("Jumlah yang dibayarkan : " + number_format(0, 0, '.', ','));
    }
    else {
        //console.log("2 "+m); 
        denda = NPOPKP*percentdenda*0.05/100;
        NPOPKP = jsbtotalbefore.value - cNPOPTKP;
        // NPOPKP = (jsbtotalbefore.value*0.75) - cNPOPTKP;
        bphtbt = document.createTextNode(number_format((NPOPKP*0.05)+denda, 0, '.', ','));
        bphtbts = document.createTextNode(number_format(NPOPKP * 0.05, 0, '.', ','));
        if((jnsperolehan=="4")||(jnsperolehan=="5")||(jnsperolehan=="3")||(jnsperolehan=="31")){
            if(configpengenaan=='1'){
                pengenaan = document.createTextNode(number_format(NPOPKP * 0.05 * Phibahwaris * 0.01, 0, '.', ','));
                hpengenaan = NPOPKP * 0.05 * Phibahwaris * 0.01;
                APHB=document.createTextNode(number_format(0, 0, '.', ','));
                 //alert(Phibahwaris);
                hbphtbt = ((NPOPKP * 0.05)-hpengenaan);
                denda = hbphtbt*percentdenda/100;
                bphtbt = document.createTextNode(number_format(hbphtbt+denda, 0, '.', ','));
            }else{
                pengenaan = document.createTextNode(number_format(0, 0, '.', ','));
                denda = (NPOPKP*0.05)*percentdenda/100;
                APHB=document.createTextNode(number_format(0, 0, '.', ','));
                hbphtbt = (NPOPKP * 0.05);
                bphtbt = document.createTextNode(number_format(hbphtbt+denda, 0, '.', ','));
            }
            if (!isNaN(per) && (per != 0))
                ctjpay = document.createTextNode("Jumlah yang dibayarkan : " + number_format((hbphtbt - (hbphtbt * per * 0.01)+denda, 0, '.', ',')));
            else if (!isNaN(frac1) && (frac1 != 0) && !isNaN(frac2) && (frac2 != 0))
                ctjpay = document.createTextNode("Jumlah yang dibayarkan : " + number_format((hbphtbt * (frac1/frac2))+denda, 0, '.', ','));
            else
                ctjpay = document.createTextNode("Jumlah yang dibayarkan : " + number_format(hbphtbt+denda, 0, '.', ','));
        }else if(jnsperolehan=="33" || jnsperolehan=="7"){
            if(configaphb=='1'){
                if(hitungaphb=='1'){
                    // APHB = document.createTextNode(number_format((NPOPKP * 0.05 * pec[0]/pec[1]), 0, '.', ','));
                    // denda = (NPOPKP * 0.05 * pec[0]/pec[1])*percentdenda/100;
                    // bphtbt = document.createTextNode(number_format((NPOPKP * 0.05 * pec[0]/pec[1])+denda, 0, '.', ','));
                    // hitbphtb = (NPOPKP * 0.05 * pec[0]/pec[1]);
                    var new_aphb = ((jsbtotalbefore.value * (pec[0]/pec[1])) - cNPOPTKP) * 0.05;
                    APHB = document.createTextNode(number_format(new_aphb, 0, '.', ','));
                    denda = (NPOPKP * 0.05 * (pec[0]/pec[1]))*percentdenda/100;
                    bphtbt = document.createTextNode(number_format(new_aphb+denda, 0, '.', ','));
                    hitbphtb = new_aphb+denda;
                }else if(hitungaphb=='2'){
                    APHB = document.createTextNode(number_format((NPOPKP * 0.05)-(NPOPKP * 0.05 * pec[0]/pec[1]), 0, '.', ','));
                    denda = ((NPOPKP * 0.05)-(NPOPKP * 0.05 * pec[0]/pec[1]))*percentdenda/100;
                    bphtbt = document.createTextNode(number_format((NPOPKP * 0.05)-(NPOPKP * 0.05 * pec[0]/pec[1])+denda, 0, '.', ','));
                    
                    hitbphtb = (NPOPKP * 0.05)-(NPOPKP * 0.05 * pec[0]/pec[1]);
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
                    
                    hitbphtb = (NPOPKP*0.05);
                }
            }else{
                APHB=document.createTextNode(number_format(0, 0, '.', ','));
                denda = (NPOPKP*0.05)*percentdenda/100;
                bphtbt = document.createTextNode(number_format((NPOPKP*0.05)+denda, 0, '.', ','));
                hitbphtb = (NPOPKP*0.05);
            }
            pengenaan = document.createTextNode(number_format(0, 0, '.', ','));
            if (!isNaN(per) && (per != 0))
                ctjpay = document.createTextNode("Jumlah yang dibayarkan : " + number_format(hitbphtb - (hitbphtb * per * 0.01)+denda, 0, '.', ','));
            else if (!isNaN(frac1) && (frac1 != 0) && !isNaN(frac2) && (frac2 != 0))
                ctjpay = document.createTextNode("Jumlah yang dibayarkan : " + number_format((hitbphtb * (frac1/frac2))+denda, 0, '.', ','));
            else
                ctjpay = document.createTextNode("Jumlah yang dibayarkan : " + number_format(hitbphtb, 0, '.', ','));
        }else{
            denda = (NPOPKP*0.05)*percentdenda/100;
            pengenaan = document.createTextNode(number_format(0, 0, '.', ','));
            APHB=document.createTextNode(number_format(0, 0, '.', ','));
            bphtbt = document.createTextNode(number_format((NPOPKP * 0.05), 0, '.', ','));
            if (!isNaN(per) && (per != 0))
            ctjpay = document.createTextNode("Jumlah yang dibayarkan : " + number_format(NPOPKP * 0.05 - (NPOPKP * 0.05 * per * 0.01)+denda, 0, '.', ','));
            else if (!isNaN(frac1) && (frac1 != 0) && !isNaN(frac2) && (frac2 != 0))
                ctjpay = document.createTextNode("Jumlah yang dibayarkan : " + number_format((NPOPKP * 0.05) * (frac1/frac2)+denda, 0, '.', ','));
            else
                ctjpay = document.createTextNode("Jumlah yang dibayarkan : " + number_format((NPOPKP * 0.05)+denda, 0, '.', ','));
        }
        
        
    }
    var npopkp = document.createTextNode(number_format(NPOPKP, 0, '.', ','));

    removeFChild(eNPOPTKP);
    removeFChild(eNPOPKP);
    removeFChild(tNJOP);
    removeFChild(akumulasi);
    removeFChild(tBPHTBT);
	removeFChild(tBPHTBTS);
	removeFChild(tPengenaan);
	removeFChild(tAPHB);
    removeFChild(tWasiat);
    removeFChild(jpay);
    //removeFChild (tTotal);

    //tTotal.appendChild(total);
    //tWasiat.appendChild(wasiat);
    eNPOPKP.appendChild(npopkp);
    eNPOPTKP.appendChild(noptkp);
    console.log(noptkp);
    tNJOP.appendChild(text);
    akumulasi.appendChild(text2);
    tBPHTBT.appendChild(bphtbt);
	tBPHTBTS.appendChild(bphtbts);
	tPengenaan.appendChild(pengenaan);
	tAPHB.appendChild(APHB);
    jpay.appendChild(ctjpay);
    document.getElementById('denda-value').value = denda;

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
    NPOPKP();
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
    NPOPKP();
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

function printToPDF(json) {
    window.open('./function/BPHTB/notaris/svc-print-notaris-app.php?q=' + encodeBase64(json), '_newtab');
}

function loadNPOPTKP() {
    //showMask();

    var sel = document.getElementById("right-land-build");
    var d = sel.options[sel.selectedIndex].value;
	var noktp = document.getElementById("noktp").value;
    var eNPOPTKP = document.getElementById("NPOPTKP");
    var verdoc = document.getElementById("ver-doc").value;
    var trsid = document.getElementById("trsid").value;
	var nop = document.getElementById("name2").value;
    Ext.Ajax.request({
        url: './function/BPHTB/notaris/svc-npoptkp.php',
        method: 'POST',
        params: {id: d, axx: axx, noktp: noktp,verdoc:verdoc,trsid:trsid,nop:nop},
        success: function(result, request) {
            var jsonData = $.parseJSON(result.responseText);
            if (jsonData.success) {

                if (jsonData.result!='')
                // if (!xOk)
                    cNPOPTKP = jsonData.result;
                else
                    cNPOPTKP = 0;
                // if (warisNonWaris())
                    // cNPOPTKP = jsonData.result;
                console.log(cNPOPTKP);
                checkTransaction();
                hideMask();
                document.getElementById("hd-npoptkp").value = cNPOPTKP;
				removeFChild(eNPOPTKP);
				var noptkp = document.createTextNode(number_format(cNPOPTKP, 0, '.', ','));
				eNPOPTKP.appendChild(noptkp);

                ket_pelapora_ke();
                ket_noppelapora_ke();
                // console.log(cNPOPTKP);

            }
        },
        failure: function(result, request) {

        }
    });
}

function ket_pelapora_ke() {
    var noktp = document.getElementById("noktp").value;
    var nop = document.getElementById("name2").value;
    Ext.Ajax.request({
        url : './function/BPHTB/notaris/svc-cek-pelaporan.php',
        method: 'POST',
        params: {axx: axx, noktp: noktp,nop:nop},
        success: function(result, request) {
            var jsonData = JSON.parse(result.responseText);
            if (jsonData.success) {
                console.log(jsonData.feedback)
                $("#ketlaporan").html(jsonData.feedback)
            }
        },
        failure: function(result, request) {

        }
    });
}
function ket_noppelapora_ke() {
    var noktp = document.getElementById("noktp").value;
    var nop = document.getElementById("name2").value;
    Ext.Ajax.request({
        url : './function/BPHTB/notaris/svc-cek-noppelaporan.php',
        method: 'POST',
        params: {axx: axx, noktp: noktp,nop:nop},
        success: function(result, request) {
            var jsonData = JSON.parse(result.responseText);
            if (jsonData.success) {
                console.log(jsonData.feedback)
                $("#ketlaporan2").html(jsonData.feedback)
            }
        },
        failure: function(result, request) {

        }
    });
}

function checkTransaksi(){
    var val = $.trim($("#noktp").val());
    
    $.ajax({
        type: "post",
        data: "noktp=" + val,
        url: "./function/BPHTB/notaris/svc-check-transaksi.php",
        dataType: "json",
        success: function(res) {
            $("#load-pbb").html("");
            if (res.message)
                alert(res.message);

            $('#errnop').remove();
            if (res.denied)
                if (res.denied == 1) {
                    var errnop = $("<span id='errnop'><br>" + res.message + "</span>").css({'color': '#FF0000'})
                    $(obj).val('').after(errnop);
                } else {
                    $(obj).css({'color': '#000'});
                }
            //else {
                //$(obj).css({'color': '#000'});
            //}
            //hideMask();
        },
        //error: hideMask(), //function(res){ hideMask();console.log(res) },
        //failure: hideMask(), //function(res){ hideMask();console.log(res) }
    });
}
function hideMask() {
    hideDialog();
}

function showMask() {
    //console.log(showDialog);
    showDialog('Load', '<img src="image/icon/loading.gif" width="32" height="32" style="margin-right:8px;" align="absmiddle"/>Tunggu', 'prompt', false, true);
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
    var n = document.getElementById("name2").value;
    var sel = document.getElementById("noktp").value;
    var prm = encodeBase64("{'noktp':'" + sel + "','axx':'" + axx + "','n':'" + n + "'}");
    $.ajax({
        url: './function/BPHTB/notaris/svc-check-noktp.php',
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
function cekAPHB(){
	var sel = document.getElementById("right-land-build");
    var d = sel.options[sel.selectedIndex].value;
	if(configaphb=='1'){
		if(d==33 || d==7){
            $(".aphb_show").show()
			$("#pengurangan-aphb").removeAttr("disabled", "disabled");
			document.getElementById("pengurangan-aphb").selectedIndex = "1"; 
		}else{
            $(".aphb_show").hide()
			$("#pengurangan-aphb").attr("disabled", "disabled");
			document.getElementById("pengurangan-aphb").selectedIndex = "0";
		}
	}else{
		$("#pengurangan-aphb").attr("disabled", "disabled");
		document.getElementById("pengurangan-aphb").selectedIndex = "0";
	}
}
function checkTransLast() {
    showMask();
	loop = cloop;
	var n = document.getElementById("name2").value;
	var sel = document.getElementById("noktp").value;
	var prm = encodeBase64("{'noktp':'"+sel+"','axx':'"+axx+"','n':'"+n+"'}");
	//console.log(document.getElementById('hd-npoptkp').value);
	Ext.Ajax.request({
	 url : './function/BPHTB/notaris/svc-check-noktp.php',
			  method: 'POST',
			  params :{req:prm},
			  success: function ( result, request ) {
				 var jsonData = Ext.util.JSON.decode(result.responseText);
				 if (jsonData.success) {
				 	if(jsonData.found) xOk = true;
					else xOk = false;
					loadNPOPTKP();
					hideMask();
				 }
		   	  },
			  failure: function ( result, request ) {
			   
		      }
   });
   sendNames();
   loadNPOPTKP();
}

$(document).ready(function() {
    // loadNPOPTKP();
    checkTransLast();
    loadLaikPasar();
    var rb = getCheckedValue(document.forms['form-notaris'].elements['RadioGroup1']);
    if (rb) {
        var objChecked = getCheckedObject(document.forms['form-notaris'].elements['RadioGroup1']);
        if (edit)
            enableE(objChecked, 1);
    }
    if (edit) {
        setCheckedValue(document.forms['form-notaris'].elements['RadioGroup1'], '1');
        document.getElementById("jsb-choose").setAttribute("disabled", "disabled");
        document.getElementById("jsb-choose-number").setAttribute("disabled", "disabled");
        document.getElementById("jsb-choose-role-number").setAttribute("disabled", "disabled");
        document.getElementById("jsb-choose-date").setAttribute("disabled", "disabled");
        document.getElementById("jsb-choose-percent").setAttribute("disabled", "disabled");
		document.getElementById("jsb-choose-fraction1").setAttribute("disabled", "disabled");
		document.getElementById("jsb-choose-fraction2").setAttribute("disabled", "disabled");
        document.getElementById("jsb-etc").setAttribute("disabled", "disabled");
    }
    $('.currencyFormat').blur(function()
    {
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
    
    var sel = document.getElementById("right-land-build");
    var d = sel.options[sel.selectedIndex].value;
    if(d==33 || d==7){
        $(".aphb_show").show()
    }else{
        $(".aphb_show").hide()
    }

});