<?php
	<div><br>
	<br><!--a href='http://36.92.151.83:8080/portlet/portlet.php' target="_blank"><button type="button" class="button button1"><i class='far fa-address-card'></i> Lihat Daftar Tagihan</button></a> <br/></br>

	
	
	</div>
	<!--<applet name="jZebra" id="jZebra" code="jzebra.PrintApplet.class" alt="jZebra did not load properly" archive="inc/jzebra/jzebra.jar" width="0" height="0">
         <param name="printer" value="zebra">
      </applet>-->
	  <script type="text/javascript">
	  /*_CEKAPPLET=0;
      function getHWMAC() {
         var applet = document.jZebra;
         if (applet != null) {
            applet.findMAC();
			monitorFindingMAC();
         }else{
			 _CEKAPPLET++;
			 if(_CEKAPPLET<=30){
				window.setTimeout('getHWMAC()', 100);
			 }else{
				alert("Java Applet tidak berjalan semestinya\n Coba Pastikan Java Runtime terinstall dan Applet diijinkan untuk berjalan");
			 }
		 }
         
      }
          
      function monitorFindingMAC() {
		var applet = document.jZebra;
		if (applet != null) {
		   if (!applet.isDoneFindingMAC()) {
			  document.getElementById("loader").innerText="Cek Sistem........";
			  window.setTimeout('monitorFindingMAC()', 100);
		   } else {
			   var listing = applet.getHWMAC();
			   //console.log(listing);
			   document.getElementById("login-form").style.display="inherit";
			   document.getElementById("loader").style.display="none";
			   document.getElementById("mac").value=listing;
		   }
		} else {
				alert("Applet not loaded!");
			}
	 }
      
     getHWMAC();*/
	 	document.getElementById("login-form").style.display="inherit";
		document.getElementById("loader").style.display="none";
		document.getElementById("mac").value=listing;
   </script>

   <div class="row">
		<div class="col-md-12 txt-center" style="margin-bottom: 5px;">
			<a href="http://36.92.151.83:2010/portlet/portlet.php" target="_blank">
				<button type="button" class="btn btn-primary btn-orange" style="padding-top: 15px;padding-bottom: 15px;background-color: #df781e !important;box-shadow: none;border-radius: 3px;border: none;color: white;"><i class="fa fa-address-card"></i> Lihat Daftar Tagihan</button>
			</a>
		</div>
	</div>
</body>
