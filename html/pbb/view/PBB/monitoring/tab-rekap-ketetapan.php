<?php
class RekapKetetapan
{
    public $label = 'Rekap Ketetapan';

    private $appConfig;

    public function __construct($appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function printForm($a = "", $m = "", $uid = "")
    {
        $thn = date("Y")+1;
        $thnTagihan = $this->appConfig['tahun_tagihan'];

        echo '
            <div class="row">
                <div class="col-md-12">
                    <form id="TheForm-5" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'5','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Tahun Pajak: </label>
                                    <select name="tahun-pajak-rekap-ketetapan" class="form-control" id="tahun-pajak-rekap-ketetapan">';
        echo "<option value=\"\">Semua</option>";
        for ($t = $thn; $t >= 2008; $t--) {
            if ($t == $thnTagihan) {
                echo "<option value=\"$t\" selected>$t</option>";
            } else
                echo "<option value=\"$t\">$t</option>";
        }
        echo                        '</select> 
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select style="display: none;" class="form-control" name="kecamatan-rekap-ketetapan" id="kecamatan-rekap-ketetapan"></select>
                                </div>
                            </div>
                            <div class="col-md-4" style="margin-top:25px">
                                <button type="button" name="button5" class="btn btn-primary btn-orange mb5" onClick="showRekapPokok()">Tampilkan</button>
                                <button type="button" name="buttonToExcel" class="btn btn-primary btn-blue mb5" onClick="excelRekapPokok()">Ekspor ke xls</button>
                            </div>
                        </div>        
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-content-rekap-ketetapan" class="monitoring-content"></div>
                </div>
            </div>
        ';
    }
}


?>

<script>
    function showRekapPokok() {
        var tahun = $("#tahun-pajak-rekap-ketetapan").val();
        var kecamatan = $("#kecamatan-rekap-ketetapan").val();
        var namakec = $("#kecamatan-rekap-ketetapan option:selected").text();
        var e_periode = Number($("#periode-rekap-ketetapan").val());
        var sts = 1;

        $("#monitoring-content-rekap-ketetapan").html("loading ...");
        $("#monitoring-content-rekap-ketetapan").load("view/PBB/monitoring/svc-monitoring-rekappokok.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'5','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'5','uid':'$uid','srch':'$srch'}"); 
                                                                                                            ?>", {
            th: tahun,
            st: sts,
            kc: kecamatan,
            n: namakec,
            eperiode: e_periode
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-rekap-ketetapan").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }

    function excelRekapPokok() {
        var buku = $("#src-buku-5").val();
        var tahun = $("#tahun-pajak-rekap-ketetapan").val();
        var kecamatan = $("#kecamatan-rekap-ketetapan").val();
        var namakec = $("#kecamatan-rekap-ketetapan option:selected").text();
        var e_periode = Number($("#periode-rekap-ketetapan").val());
        var sts = 1;

        window.open("view/PBB/monitoring/svc-toexcel-rekappokok.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'5','uid':'$uid'}"); //base64_encode("{'a':'$a', 'm':'$m', 's':'5','uid':'$uid','srch':'$srch'}"); 
                                                                        ?>&buku=" + buku + "&n=" + namakec + "&kc=" + kecamatan + "&st=" + sts + "&th=" + tahun + "&eperiode=" + e_periode + "&target_ketetapan=semua");
    }
</script>