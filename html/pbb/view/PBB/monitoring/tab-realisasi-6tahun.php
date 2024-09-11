<?php
class Realisasi6Tahun
{
    public $label = 'Realisasi 6 Tahun Terakhir';

    private $appConfig;

    public function __construct($appConfig)
    {
        $this->appConfig = $appConfig;
        //$this->label = 'Realisasi ' . ($appConfig['tahun_tagihan'] - 10) . ' - ' . $appConfig['tahun_tagihan'];
		$this->label = 'Realisasi Tahunan';
    }

    public function printForm($a = "", $m = "", $uid = "")
    {
		$thn = date("Y");
        $thnTagihan = $this->appConfig['tahun_tagihan'];
		
        echo '
            <div class="row">
                <div class="col-md-12">
                    <form id="TheForm-realisasi-6tahun" method="post" action="view/PBB/monitoring/svc-export.php?' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Tahun: </td>
                                    <div class="row">
                                        <div class="col-md-5">
											<select name="tgl_start-realisasi6tahun" id="tgl_start-realisasi6tahun" class="form-control">';
													//echo "<option value=\"\">Semua</option>";
													for ($t = $thn; $t >= 2008; $t--) {
														if ($t == $thnTagihan) {
															echo "<option value=\"$t\" selected>$t</option>";
														} else
															echo "<option value=\"$t\">$t</option>";
													}
											echo '</select>
                                        </div>
                                        <div class="col-md-2" style="margin-top: 10px">s/d</div>
                                        <div class="col-md-5">
                                            <select name="tgl_end-realisasi6tahun" id="tgl_end-realisasi6tahun" class="form-control">';
												//echo "<option value=\"\">Semua</option>";
												for ($t = $thn; $t >= 2008; $t--) {
													if ($t == $thnTagihan) {
														echo "<option value=\"$t\" selected>$t</option>";
													} else
														echo "<option value=\"$t\">$t</option>";
												}
											echo '</select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Kecamatan: </td>
                                    <select name="kecamatan-realisasi6tahun" class="form-control" id="kecamatan-realisasi6tahun"></select>
                                </div>
                            </div>
                            <div class="col-md-3" style="margin-top: 25px">
                                <button type="button" name="button4" class="btn btn-primary btn-orange" onClick="showRealisasi6Tahun()" style="margin-bottom: 5px;">Tampilkan</button>
                                <button type="button" name="buttonToExcel" class="btn btn-primary btn-blue" onClick="excelRealisasi6Tahun()" style="margin-bottom: 5px;">Ekspor ke xls</button>
                            </div>
                        </div>
                        <input type="hidden" id="export_realisasi"/>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-content-realisasi6tahun" class="monitoring-content"></div>
                    
                </div>
            </div>
        ';
    }
}


?>

<script>
    function showRealisasi6Tahun() {
        var tgl_start = $("#tgl_start-realisasi6tahun").val();
        var tgl_end = $("#tgl_end-realisasi6tahun").val();
        var kecamatan = $("#kecamatan-realisasi6tahun").val();
        var namakec = $("#kecamatan-realisasi6tahun option:selected").text();

        $("#monitoring-content-realisasi6tahun").html("loading ...");
        $("#monitoring-content-realisasi6tahun").load("view/PBB/monitoring/svc-monitoring-realisasi6tahun.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'4','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'4','uid':'$uid','srch':'$srch'}"); 
                                                                                                                ?>", {
            ds: tgl_start,
            de: tgl_end,
            kc: kecamatan,
            n: namakec
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-realisasi6tahun").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }

    function excelRealisasi6Tahun() {
        var tgl_start = $("#tgl_start-realisasi6tahun").val();
        var tgl_end = $("#tgl_end-realisasi6tahun").val();
        var kecamatan = $("#kecamatan-realisasi6tahun").val();
        var namakec = $("#kecamatan-realisasi6tahun option:selected").text();
        window.open("view/PBB/monitoring/svc-toexcel-realisasi6tahun.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'4','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'4','uid':'$uid','srch':'$srch'}"); 
                                                                            ?>" + "&ds=" + tgl_start + "&de=" + tgl_end + "&kc=" + kecamatan + "&n=" + namakec);
    }
</script>