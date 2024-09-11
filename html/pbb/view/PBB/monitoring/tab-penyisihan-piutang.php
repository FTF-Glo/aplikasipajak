<?php
class PenyisihanPiutang
{
    public $label = 'Penyisihan Piutang';

    private $appConfig;

    public function __construct($appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function printForm()
    {
        $thn = date('Y');
        echo '
            <div class="row">
                <div class="col-md-12">
                    <form id="TheForm-2" method="post" action="#" target="TheWindow">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Tahun Pajak: </label>
                                    <select name="tahun-pajak-penyisihan-piutang-awal" class="form-control" id="tahun-pajak-penyisihan-piutang-awal">';
        // echo "<option value=\"\">Semua</option>";
        for ($t = $thn; $t >= 2008; $t--) {
            if ($t == $thnTagihan) {
                echo "<option value=\"$t\" selected>$t</option>";
            } else
                echo "<option value=\"$t\">$t</option>";
        }
        echo                    '</select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Kecamatan: </label>
                                    <select name="kecamatan-penyisihan-piutang" id="kecamatan-penyisihan-piutang" class="form-control"></select>
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label for="">' . $this->appConfig['LABEL_KELURAHAN'] . ': </label>
                                    <select id="kelurahan-penyisihan-piutang" class="form-control"></select>
                                </div>
                            </div>
                            <div class="col-md-3" style="margin-top: 25px">
                                <input type="button" name="button3" class="btn btn-default btn-orange" value="Tampilkan" onClick="showModelPenyisihanPiutang()"/>
                                <input type="button" name="buttonToExcel" class="btn btn-default btn-blue" value="Ekspor ke xls" onClick="excelModelPenyisihanPiutang()"/>
                            </div>
                        </div>
                        <input type="hidden" id="export_e2"/>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-penyisihan-piutang" class="monitoring-content"></div>
					<script>
						$("select#kecamatan-penyisihan-piutang").change(function () {
							showKelurahan(\'penyisihan-piutang\');
						})
                    </script>
                </div>
            </div>
        ';
    }
}


?>

<script>
    function showModelPenyisihanPiutang() {
        var tahunawal = $("#tahun-pajak-penyisihan-piutang-awal").val();
        var kecamatan = $("#kecamatan-penyisihan-piutang").val();
        var kelurahan = $("#kelurahan-penyisihan-piutang").val();
        var namakec = $("#kecamatan-penyisihan-piutang option:selected").text();
        var namakel = $("#kelurahan-penyisihan-piutang option:selected").text();

        $("#monitoring-penyisihan-piutang").html("loading ...");
        $("#monitoring-penyisihan-piutang").load("view/PBB/monitoring/svc-monitoring-penyisihan-piutang.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); 
                                                                                                                ?>", {
            tahunawal: tahunawal,
            kecamatan: kecamatan,
            kelurahan: kelurahan,
            namakec: namakec,
            namakel: namakel
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-3").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }

    function excelModelPenyisihanPiutang() {
        var tahunawal = $("#tahun-pajak-penyisihan-piutang-awal").val();
        var kecamatan = $("#kecamatan-penyisihan-piutang").val();
        var kelurahan = $("#kelurahan-penyisihan-piutang").val();
        var namakec = $("#kecamatan-penyisihan-piutang option:selected").text();
        var namakel = $("#kelurahan-penyisihan-piutang option:selected").text();
        // if(kecamatan!="" || kecamatan==""){
        // window.open("view/PBB/monitoring/svc-toexcel-penyisihan-piutang.php?q=<?php //echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); 
                                                                                    ?>&tahunawal=" + tahunawal + "&kecamatan=" + kecamatan + "&namakec=" + namakec);
        // } else if(kelurahan!=""){
        // window.open("view/PBB/monitoring/svc-toexcel-penyisihan-piutang-perkel.php?q=<?php //echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); 
                                                                                        ?>&tahunawal=" + tahunawal + "&kecamatan=" + kecamatan + "&namakec=" + namakec + "&kelurahan=" + kelurahan + "&namakel=" + namakel);
        // window.open("view/PBB/monitoring/svc-toexcel-penyisihan-piutang-perkel-gol.php?q=<?php //echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); 
                                                                                            ?>&tahunawal=" + tahunawal + "&kecamatan=" + kecamatan + "&namakec=" + namakec + "&kelurahan=" + kelurahan + "&namakel=" + namakel);
        // }
        if (kelurahan) {
            window.open("view/PBB/monitoring/svc-toexcel-penyisihan-piutang-perkel.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); 
                                                                                            ?>&tahunawal=" + tahunawal + "&kecamatan=" + kecamatan + "&namakec=" + namakec + "&kelurahan=" + kelurahan + "&namakel=" + namakel);
            window.open("view/PBB/monitoring/svc-toexcel-penyisihan-piutang-perkel-gol.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); 
                                                                                                ?>&tahunawal=" + tahunawal + "&kecamatan=" + kecamatan + "&namakec=" + namakec + "&kelurahan=" + kelurahan + "&namakel=" + namakel);
        } else {
            window.open("view/PBB/monitoring/svc-toexcel-penyisihan-piutang.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); 
                                                                                    ?>&tahunawal=" + tahunawal + "&kecamatan=" + kecamatan + "&namakec=" + namakec);
        }
    }
</script>