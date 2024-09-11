<?php
class RealisasiDesaKota
{
    public $label = 'Realisasi Pedesaan & Perkotaan';

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
                    <form id="TheForm-7" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'7','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Tahun Pajak: </label>
                                    <select name="tahun-pajak-realisasi-desa-kota" class="form-control" id="tahun-pajak-realisasi-desa-kota">';
        echo "<option value=\"\">Semua</option>";
        for ($t = $thn; $t >= 2008; $t--) {
            if ($t == $thnTagihan) {
                echo "<option value=\"$t\" selected>$t</option>";
            } else
                echo "<option value=\"$t\">$t</option>";
        }
        echo                         '</select> 
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Bulan</label>
                                    <select name="periode-realisasi-desa-kota" id="periode-realisasi-desa-kota" class="form-control">
                                    <option value="0">Semua Bulan</option>';
        for ($b = 0; $b < 12; $b++) {
            echo "<option value=\"" . ($b + 1) . "\">" . $bulan[$b] . "</option>";
        }

        echo                        '</select> 
                                </div>
                            </div>
                            <div class="col-md-4" style="margin-top: 25px">
                                <button type="button" name="button7" id="button" class="btn btn-primary btn-orange mb5" onClick="showRealisasiPedesaanPerkotaan()">Tampilkan</button>
                                <button type="button" name="buttonToExcel" id="buttonToExcel" class="btn btn-primary btn-blue mb5" onClick="excelRealisasiPedesaanPerkotaan()">Ekspor ke xls</button>
                            </div>
                        </div>
                        <input type="hidden" id="export_e2"/>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-content-realisasi-desa-kota" class="monitoring-content"></div>
                </div>
            </div>
        ';
    }
}


?>

<script>
    function showRealisasiPedesaanPerkotaan() {
        var tahun = $("#tahun-pajak-realisasi-desa-kota").val();
        var kecamatan = $("#kecamatan-realisasi-desa-kota").val();
        var namakec = $("#kecamatan-realisasi-desa-kota option:selected").text();
        var e_periode = Number($("#periode-realisasi-desa-kota").val());
        var sts = 1;

        $("#monitoring-content-realisasi-desa-kota").html("loading ...");
        $("#monitoring-content-realisasi-desa-kota").load("view/PBB/monitoring/svc-monitoring-realisasi-pp.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'7','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'7','uid':'$uid','srch':'$srch'}"); 
                                                                                                                    ?>", {
            th: tahun,
            st: sts,
            kc: kecamatan,
            n: namakec,
            eperiode: e_periode
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-6").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }

    function excelRealisasiPedesaanPerkotaan() {
        var buku = $("#src-buku-realisasi-desa-kota").val();
        var tahun = $("#tahun-pajak-realisasi-desa-kota").val();
        var kecamatan = $("#kecamatan-realisasi-desa-kota").val();
        var namakec = $("#kecamatan-realisasi-desa-kota option:selected").text();
        var e_periode = Number($("#periode-realisasi-desa-kota").val());
        var sts = 1;

        window.open("view/PBB/monitoring/svc-toexcel-realisasi-pp.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'7','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'7','uid':'$uid','srch':'$srch'}"); 
                                                                        ?>&n=" + namakec + "&st=" + sts + "&th=" + tahun + "&eperiode=" + e_periode + "&target_ketetapan=semua");
    }
</script>