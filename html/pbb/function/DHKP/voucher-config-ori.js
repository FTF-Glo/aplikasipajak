  function getTestPrintException(param){
    alert('Pengambilan data Test Printer gagal !');
  }

  function getTestPrintSucess(param){
    if(param.responseText){
         if(param.responseText.substr(0,3)=='1~~' || param.responseText.substr(0,3)=='0~~')
     		printOnpays(param.responseText);
     	   else
     		print64(param.responseText);
  	}
  }
  function testPrint(){
    Ext.Ajax.request({
    		url: 'svr/voucher/svc-voucher-test-print.php',
     		success: getTestPrintSucess,
     		failure: getTestPrintException,
  	});
  }
   function printOnpays(onpays) {
        var applet = document.jZebra;
        if (applet != null) {
           applet.appendRawOnpays(onpays);
		   applet.printOnpays();
   		   monitorPrinting();
        }         
     }

   function monitorPrinting() {
     var applet = document.jZebra;
     if (applet != null) {
         while (!applet.isDonePrinting()) {
         	// Wait
         }
         var e = applet.getException();
         alert(e == null ? 'Printed Successfully' : 'Exception occured: ' + e.getLocalizedMessage());
     } else {
           alert('Applet not loaded!');
       }
     }

   function print64(base64TestPrint) {
      var applet = document.jZebra;
       if (applet != null) {
         applet.append64(base64TestPrint);
         applet.print();
         monitorPrinting();
      }
      else {
         alert('Applet not loaded!');
       }
   }


    function chr(i) {
       return String.fromCharCode(i);
    }
   function findPrinters(printer) {
        var applet = document.jZebra;
        if (applet != null) {
           applet.findPrinter(printer);
        }
        monitorFinding2();
     }