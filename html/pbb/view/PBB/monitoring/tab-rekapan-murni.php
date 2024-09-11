<?php
class RekapanMurni
{
    public $label = 'Rekapan Murni';

    private $appConfig;

    public function __construct($appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function printForm($a = "", $m = "", $uid = "")
    {
        $thn = date("Y");
        $thnTagihan = $this->appConfig['tahun_tagihan'];
        $bulan = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "Nopember", "Desember");

        echo '
        <div class="row">
            <div class="col-md-12">
                <form id="TheForm-2" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="">Tahun Pajak: </label>
                                <select name="tahun-pajak-rekapan-murni" class="form-control" id="tahun-pajak-rekapan-murni">';
        echo "<option value=\"\">Semua</option>";
        for ($t = $thn; $t >= 2008; $t--) {
            if ($t == $thnTagihan) {
                echo "<option value=\"$t\" selected>$t</option>";
            } else
                echo "<option value=\"$t\">$t</option>";
        }
        echo                     '</select> 
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="">Bulan: </label>
                                <select name="periode-rekapan-murni" class="form-control" id="periode-rekapan-murni">';

        for ($b = 0; $b < 12; $b++) {
            echo "<option value=\"" . ($b + 1) . "\">" . $bulan[$b] . "</option>";
        }

        echo                    '</select> 
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="">Kecamatan: </label>
                                <select name="kecamatan-rekapan-murni" class="form-control" id="kecamatan-rekapan-murni">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="">' . $this->appConfig['LABEL_KELURAHAN'] . ': </label>
                                <select id="kelurahan-rekapan-murni" class="form-control"></select>
                            </div>
                        </div>
                        <div class="col-md-2" style="margin-top: 25px">
                            <button type="button" name="button3" id="button" class="btn btn-primary btn-orange" onClick="showMurni()" style="margin-bottom: 5px;">Tampilkan</button>
                            <button type="button" name="buttonToExcel" id="buttonToExcel" class="btn btn-primary btn-blue" onClick="excelMurni()" style="margin-bottom: 5px;">Ekspor ke xls</button>
                        </div>
                    </div>
                    <input type="hidden" id="export_e2"/>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div id="monitoring-content-rekapan-murni" class="monitoring-content"></div>
                <script>
                    $("select#kecamatan-rekapan-murni").change(function () {
                        showKelurahan(\'rekapan-murni\');
                    })
                </script>
            </div>
        </div>
        ';
    }
}


?>

<script>
    function showMurni() {
        var kelurahan = $("#kelurahan-rekapan-murni").val();
        var tahun = $("#tahun-pajak-rekapan-murni").val();
        var kecamatan = $("#kecamatan-rekapan-murni").val();
        var namakec = $("#kecamatan-rekapan-murni option:selected").text();
        var e_periode = Number($("#periode-rekapan-murni").val());
        var sts = 1;

        $("#monitoring-content-rekapan-murni").html("loading ...");
        $("#monitoring-content-rekapan-murni").load("view/PBB/monitoring/svc-monitoring-murni.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); 
                                                                                                    ?>", {
            kl: kelurahan,
            th: tahun,
            st: sts,
            kc: kecamatan,
            n: namakec,
            eperiode: e_periode,
            target_ketetapan: 'semua'
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-rekapan-murni").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }

    function excelMurni() {
        var tahun = $("#tahun-pajak-rekapan-murni").val();
        var kecamatan = $("#kecamatan-rekapan-murni").val();
        var kelurahan = $("#kelurahan-rekapan-murni").val();
        var namakec = $("#kecamatan-rekapan-murni option:selected").text();
        var e_periode = Number($("#periode-rekapan-murni").val());
        var sts = 1;

        window.open("view/PBB/monitoring/svc-toexcel-murni.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); 
                                                                    ?>&n=" + namakec + "&kc=" + kecamatan + "&kl=" + kelurahan + "&st=" + sts + "&th=" + tahun + "&eperiode=" + e_periode + "&target_ketetapan=semua");
    }
</script>