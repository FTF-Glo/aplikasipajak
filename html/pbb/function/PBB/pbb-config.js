function getTestPrintException(param){
		alert('Pengambilan data Test Printer gagal !');
	};

	function getTestPrintSucess(param){
		//console.log(param);
		if(param.responseText){
			if(param.responseText.substr(0,3)=='1~~' || param.responseText.substr(0,3)=='0~~')
				printOnpays(param.responseText);
			   else
				print64(param.responseText);
		};
	};
	
	function testPrint(){
		Ext.Ajax.request({
				url: 'svr/pbb/svc-pbb-test-print.php',
				success: getTestPrintSucess,
				failure: getTestPrintException,
		});
	};

	function findPrinter() {
		var applet = document.jZebra;
		if (applet != null) {
			applet.findPrinter("<?php echo $printer?>");
			while (!applet.isDoneFinding()) {
				// Wait
			}
			var ps = applet.getPrintService();
			alert(ps == null ? 'Printer not found' : 'Printer \'' + ps.getName() + '\' found');
		} else {
			alert('Applet not loaded!');
		}
	}


	
	function chr(i) {
		return String.fromCharCode(i);
	}
	
	function testField() {
		//below is trim in javascript
		var printer = document.getElementById("printer").value.replace(new RegExp("^[" + " " + "]+$", "g"), "");
		var copy 	= document.getElementById("copy").value.replace(new RegExp("^[" + " " + "]+$", "g"), "");
		
		if (printer == "") {
			alert("Nama Printer tidak boleh kosong atau spasi");
			document.getElementById("printer").value = "<?php echo $printer?>";
			document.getElementById("copy").value = "<?php echo $copy?>";
			return false;
		} else if (copy == "") {
			alert("Jumlah Copy Rekap tidak boleh kosong atau spasi.");
			document.getElementById("copy").value = "<?php echo $copy?>";
			return false;
		} else {
			return true;
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
   function printOnpays(onpays) {
        var applet = document.jZebra;
        if (applet != null) {
          applet.appendRawOnpays(onpays);
          applet.printOnpays();    
          monitorPrinting();    
        }
        else {
         alert('Applet not loaded!');
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
   function findPrinters(printer) {
        var applet = document.jZebra;
        if (applet != null) {
           applet.findPrinter(printer);
        }
        monitorFinding2();
     }