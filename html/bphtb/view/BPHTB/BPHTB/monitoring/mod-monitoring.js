
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

function printToPDF(json) {
	window.open('./view/BPHTB/monitoring/print-to-pdf.php?q='+json, '_newtab');
}
function printToXLS(json) {
	window.open('./view/BPHTB/monitoring/print-to-execl.php?q='+json, '_newtab'); 
}

function removeNode(obj) {
	if (obj.hasChildNodes()) {
		while ( obj.childNodes.length >= 1 )
		{
			obj.removeChild( obj.firstChild );       
		} 
	}
}
function changeSummary(ttran,ttrim) {
	var tot = document.getElementById('tot-trans');
	var tottr = document.getElementById('tot-trims');
	var t1 = document.createTextNode(number_format(ttrim, 0, '.', ','));
	var t2 = document.createTextNode(number_format(ttran, 2, '.', ','));
	removeNode(tot);
	removeNode(tottr);
	tot.appendChild(t1);
	tottr.appendChild(t2);
}

function getSummary() {
	$.ajax({
	  url: "./view/BPHTB/monitoring/svc-get-total.php",
	  datatype:"json",
	  type: "POST",
	  data: {'a':ap},
	  success: function(data){
		var obj = JSON.parse(data);
		if(obj.success){
			changeSummary(obj.data.total_transaksi,obj.data.jml_transaksi) 
		}
	  }
	});
}

$(document).ready(function() {
getSummary();
setInterval(function() {
   getSummary();
}, 60000*3); //5 seconds
});