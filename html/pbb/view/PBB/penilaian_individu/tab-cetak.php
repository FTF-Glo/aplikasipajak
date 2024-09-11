<?php
class TabCetak {
    public $tabCetakLabel = 'Cetak';
    
    private $appConfig;
    
    public function __construct ($appConfig,$uid,$m) {
        $this->appConfig = $appConfig;
        $this->uid 		 = $uid;
        $this->m 		 = $m;
    }
    
    public function printTabCetak(){
        $thn = date("Y");
        $thnTagihan 	= $this->appConfig['tahun_tagihan'];
		$lblKelurahan 	= $this->appConfig['LABEL_KELURAHAN']; 
        echo '
            <fieldset>
                        <form id="TheForm-1" method="post" action="" target="TheWindow">
                            <table width="1260" border="0" cellspacing="0" cellpadding="2">
                                <tr>
                                    <td width="50" style="background-color:transparent">Tahun </td>
                                    <td width="3" style="background-color:transparent">:</td>
                                    <td width="90" style="background-color:transparent">
                                        <select class="form-control"  style=" margin-left: 10px;" name="tahun-pajak-1" id="tahun-pajak-1">
                                            <option value="">Semua</option>';
                                            for ($t = $thn; $t > 2000; $t--) {
                                                if ($t == $thnTagihan) {
                                                    echo "<option value=\"$t\" selected>$t</option>";
                                                } else
                                                    echo "<option value=\"$t\">$t</option>";
                                            }
                                            
        echo                            '</select>               
                                    </td>
                                    <td width="80" style="padding-left: 20px;">Kecamatan </td>
                                    <td width="3" style="background-color:transparent">:</td>
                                    <td width="150" style="background-color:transparent">
                                    <select class="form-control" style=" margin-left: 10px;" id="kecamatan-1"></select></td>
                                    <td width="100"  style="padding-left: 20px;">'.$lblKelurahan.'</td>
                                    <td width="3">:</td>
                                    <td width="150">
										<select class="form-control"  style=" margin-left: 10px;" id="kelurahan-1">
											<option value="">Semua</option>
										</select>
									</td>
									<td width="" style="padding-left: 20px;">
                                        <input type="button" name="button2" value="Tampilkan" onClick="onSubmit(1)" style="background : linear-gradient(22deg, rgba(30, 120, 150, 1) 0%, rgba(30, 120, 150, 0.5) 100%); height: 30px;"/>
										<input type="button" name="buttonToExcel" value="Ekspor ke xls" onClick="toExcel(1)" style="background :linear-gradient(22deg, rgba(30, 120, 150, 1) 0%, rgba(30, 120, 150, 0.5) 100%); height: 30px;"/>
                                    </td>
									<!-- <td width="350" style="background-color:transparent" align="center">
										<div style="border : 1px solid black; padding : 3px;  margin-left: 10px;">
											Printer <select name="selectedPrinter" id="selectedPrinter"  style="width:200px" onchange="changePrinter($(\'#selectedPrinter\').val(), \''.$this->uid.'\', \''.$this->m.'\');">";</select>
											<input type="button" value="Cetak" name="btn-print" onclick="javascript:printdata()"/>
										</div>
									</td> -->
								</tr>
                            </table>
                        </form>
                    </fieldset>
                    <div id="content-1" class="content">
                    </div>
                    <script>
                        $("select#kecamatan-1").change(function () {
                            showKelurahan(\'1\');
                        })
						// $(document).ready(function() {
							// listPrinter();
						// });
                    </script>
        ';
    }
}
?>

<script>

    function onSubmit(sts) {
		
        var tahun 	= $("#tahun-pajak-" + sts).val();
        var kc 		= $("#kecamatan-" + sts).val();
        var kl 		= $("#kelurahan-" + sts).val();
			
        $("#content-1").html("loading ...");
        $("#content-1").load("view/PBB/penilaian_individu/svc-cetak.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'0','uid':'$uid'}"); ?>",
        {th: tahun, kc: kc, kl: kl}, function (response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
				$("#content" + sts).html(msg + xhr.status + " " + xhr.statusText);
            }
        });
	}
	
	function toExcel(sts) {
        var tahun 	= $("#tahun-pajak-" + sts).val();
        var kc 		= $("#kecamatan-" + sts).val();
        var kl 		= $("#kelurahan-" + sts).val();
		var namakec = $("#kecamatan-1 option:selected").text();
        var namakel = $("#kelurahan-1 option:selected").text();
		
		window.open("view/PBB/penilaian_individu/svc-toexcel.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'0','uid':'$uid'}"); ?>&nkel=" + namakel + "&nkec=" + namakec + "&kc=" + kc + "&kl=" + kl + "&th=" + tahun);
    }
		
</script>