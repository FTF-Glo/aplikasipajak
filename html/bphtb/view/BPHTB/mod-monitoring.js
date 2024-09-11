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
  window.open('./view/BPHTB/monitoring/print-to-excel.php?q='+json, '_newtab'); 
}
function printToXLSDaily(json) {
  window.open('./view/BPHTB/monitoring/print-to-excel-Lharian.php?q='+json, '_newtab'); 
}
function printToXLS2(json) {
	window.open('./view/BPHTB/monitoring/print-to-excel-dhb.php?q='+json, '_newtab'); 
}

function getTotalPenerimaan(){
	$.ajax({
		type : 'post',
		data : 'ajax=1&a='+ap,
		url : 'view/BPHTB/monitoring/svc-get-total.php',
		success : function(res){
			var ajx = jQuery.parseJSON(res);
			if(ajx.success==true){
				$('#tot-trans').html(ajx.data.jml_transaksi);
				$('#tot-trims').html(ajx.data.total_transaksi);	
			}
		}	
	});
}

$(document).ready(function(e) {
    setInterval("getTotalPenerimaan()",10000);
});















