<?php
class RealisasiTunggakan {
    public $label = 'Realisasi Piutang';
    
    private $appConfig;
    
    public function __construct ($appConfig) {
        $this->appConfig = $appConfig;
    }
    
    public function printForm(){
        $thn = date("Y");
        $thnTagihan = $this->appConfig['tahun_tagihan'];
        $bulan = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "Nopember", "Desember");
        
        echo '
            <fieldset>
                        <form id="TheForm-2" method="post" action="view/PBB/monitoring/svc-export.php?q='. base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}").'" target="TheWindow">
                            <table width="80%" border="0" cellspacing="0" cellpadding="2">
                                <tr>
                                    <td width="50">Tahun&nbsp;Pajak</td>
                                    <td width="3">:</td>
                                    <td width="61">
                                        <select name="tahun-pajak-realisasi-tunggakan" id="tahun-pajak-realisasi-tunggakan">';
                                            echo "<option value=\"\">Semua</option>";
                                            for ($t = $thn; $t > 1993; $t--) {
                                                if ($t == $thnTagihan) {
                                                    echo "<option value=\"$t\" selected>$t</option>";
                                                } else
                                                    echo "<option value=\"$t\">$t</option>";
                                            }
        echo                            '</select> 
                                    </td>
                                    <td width="1">&nbsp;</td>
                                    <td width="50">Bulan</td>
                                    <td width="3">:</td>
                                    <td width="61">
                                        <select name="periode2-realisasi-tunggakan" id="periode2-realisasi-tunggakan">';
                                            
                                            for ($b = 0; $b < 12; $b++) {
                                                echo "<option value=\"" . ($b + 1) . "\">" . $bulan[$b] . "</option>";
                                            }
                                            
        echo                            '</select> 
                                    </td>
                                    <td width="1">&nbsp;</td>
                                    <td width="69">Kecamatan</td>
                                    <td width="3">:</td>
                                    <td width="138"><select name="kecamatan-realisasi-tunggakan" id="kecamatan-realisasi-tunggakan">
                                        </select></td>
                                    <td width="40">Buku</td>
                                    <td width="3">:</td>
                                    <td width="160"><select id="src-buku-realisasi-tunggakan" name="src-buku-realisasi-tunggakan">
                                            <option value="0" >Pilih Semua</option>
                                            <option value="1" >Buku 1</option>
                                            <option value="12" >Buku 1,2</option>
                                            <option value="123" >Buku 1,2,3</option>
                                            <option value="1234" >Buku 1,2,3,4</option>
                                            <option value="12345" >Buku 1,2,3,4,5</option>
                                            <option value="2" >Buku 2</option>
                                            <option value="23" >Buku 2,3</option>
                                            <option value="234" >Buku 2,3,4</option>
                                            <option value="2345" >Buku 2,3,4,5</option>
                                            <option value="3" >Buku 3</option>
                                            <option value="34" >Buku 3,4</option>
                                            <option value="345" >Buku 3,4,5</option>
                                            <option value="4" >Buku 4</option>
                                            <option value="45" >Buku 4,5</option>
                                            <option value="5" >Buku 5</option>
                                        </select></td>
                                    <td width="380">
                                        <input type="button" name="button3" value="Tampilkan" onClick="showModelRealisasiTunggakan()"/>
                                        <input type="button" name="buttonToExcel" value="Ekspor ke xls" onClick="excelModelRealisasiTunggakan()"/>
                                    </td>    
                            </table>
                            <input type="hidden" id="export_e2"/>
                        </form>
                    </fieldset>
                    <div id="monitoring-content-realisasi-tunggakan" class="monitoring-content"></div>
        ';
    }
}
    
    
?>

<script>

        function showModelRealisasiTunggakan() {
            var buku = $("#src-buku-realisasi-tunggakan").val();
            var tahun = $("#tahun-pajak-realisasi-tunggakan").val();
            var kecamatan = $("#kecamatan-realisasi-tunggakan").val();
            var namakec = $("#kecamatan-realisasi-tunggakan option:selected").text();
            var e_periode = Number($("#periode2-realisasi-tunggakan").val());
            var sts = 1;

            $("#monitoring-content-realisasi-tunggakan").html("loading ...");
            $("#monitoring-content-realisasi-tunggakan").load("view/PBB/monitoring/svc-monitoring-realisasi-tunggakan.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); ?>",
                    {bk: buku, th: tahun, thntagihan: THN_TAGIHAN, st: sts, kc: kecamatan, n: namakec, eperiode: e_periode, target_ketetapan: 'semua'}, function (response, status, xhr) {
                if (status == "error") {
                    var msg = "Sorry but there was an error: ";
                    $("#monitoring-content-realisasi-tunggakan").html(msg + xhr.status + " " + xhr.statusText);
                }
            });
        }



        function excelModelRealisasiTunggakan() {
            var buku = $("#src-buku-realisasi-tunggakan").val();
            var tahun = $("#tahun-pajak-realisasi-tunggakan").val();
            var kecamatan = $("#kecamatan-realisasi-tunggakan").val();

            var namakec = $("#kecamatan-realisasi-tunggakan option:selected").text();
            var e_periode = Number($("#periode2-realisasi-tunggakan").val());
            var sts = 1;
            
            window.open("view/PBB/monitoring/svc-toexcel-realisasi-tunggakan.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); ?>&buku=" + buku + "&n=" + namakec + "&kc=" + kecamatan + "&st=" + sts + "&th=" + tahun + "&eperiode=" + e_periode + "&thntagihan=" + THN_TAGIHAN + "&target_ketetapan=semua");
        }
</script>