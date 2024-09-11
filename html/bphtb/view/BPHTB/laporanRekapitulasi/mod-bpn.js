function printToPDF(json) {
	window.open('./view/BPHTB/laporanRekapitulasi/print-to-pdf.php?q='+json, '_newtab');
}
function printToXLS(json) {
	window.open('./view/BPHTB/laporanRekapitulasi/print-to-execl.php?q='+json, '_newtab'); 
}
