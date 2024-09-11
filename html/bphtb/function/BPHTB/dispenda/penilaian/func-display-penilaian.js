function checkform() {
	var rg = document.getElementsByName("RadioGroup1");
	var rb = getCheckedValue(rg);
	var status = "Diterima";
	if (rb == 2) {
		status = "Ditolak";
		var txt = document.getElementById("textarea-info").value;
		if (txt == "") {
			alert("Alasan penolakan harus dicantumkan !");
			return false
		}
	}
	var kurangBayar = "Kurang Bayar ?";
	// document.getElementById('kurangbayar').addEventListener('click', function (event) {
	// 	clickedButton = event.target.value;
	// });
	// console.log(clickedButton);
	// if(){

	// }
	var ok = confirm("Apakah dokumen ini " + status);
	if (!ok) return false;
	else return true;
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

function cekberkas(ssbid, jp) {
	//showMask();
	//var modal = document.getElementById("myModal");
	//	if(role!="rm1")||(role!="rmAdminBPHTB")||(role!="rAllBPHTB"){

	Ext.Ajax.request({
		url: './function/BPHTB/svc-cek-berkas.php',
		method: 'POST',
		params: { ssbid: ssbid, jp: jp },
		success: function (result, request) {
			var jsonData = JSON.parse(result.responseText);
			if (jsonData.success) {



				$("#w3-container").html(jsonData.result);
				document.getElementById('id01').style.display = 'block';





			} else {
				alert("gagal");
			}
		},
		failure: function (result, request) {

		}
	});

	//	}
}
function cekberkasOld(ssbid, jp) {
	//showMask();
	//var modal = document.getElementById("myModal");
	//	if(role!="rm1")||(role!="rmAdminBPHTB")||(role!="rAllBPHTB"){

	Ext.Ajax.request({
		url: './function/BPHTB/svc-cek-berkas_1.php',
		method: 'POST',
		params: { ssbid: ssbid, jp: jp },
		success: function (result, request) {
			var jsonData = JSON.parse(result.responseText);
			if (jsonData.success) {



				$("#w3-container").html(jsonData.result);
				document.getElementById('id01').style.display = 'block';





			} else {
				alert("gagal");
			}
		},
		failure: function (result, request) {

		}
	});

	//	}
}

function enableE(t, p) {
	var rg = document.getElementsByName("RadioGroup1");
	if (t.checked) {
		document.getElementById("textarea-info").setAttribute("disabled", "disabled");
		document.getElementById("RadioGroup99_2").setAttribute("disabled", "disabled");
		document.getElementById("RadioGroup99_1").setAttribute("disabled", "disabled");
		document.getElementById("textarea-info").value = "";
		document.getElementById("textarea-info1").disabled = false;

		if (p == 1) {
			document.getElementById("textarea-info").removeAttribute("disabled");
			document.getElementById("RadioGroup99_2").removeAttribute("disabled");
			document.getElementById("RadioGroup99_1").removeAttribute("disabled");
			document.getElementById("textarea-info1").disabled = true;
		}

	}
}

function printToPDF(json) {
	window.open('./function/BPHTB/notaris/svc-print-notaris-app.php?q=' + encodeBase64(json), '_newtab');
}

function printToPDF_view(json) {
	window.open('./view/BPHTB/notaris/svc-print-notaris-app.php?q=' + encodeBase64(json), '_newtab');
}
function printToPDFKurangBayar(json) {
	window.open('./function/BPHTB/notaris/svc-print-notaris-kb-app.php?q=' + encodeBase64(json), '_newtab');
}
function printToPDFKetetapanKB(json) {
	window.open('./function/BPHTB/notaris/svc-print-ketetapan-KB-app.php?q=' + encodeBase64(json), '_newtab');
}
