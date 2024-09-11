<?php
class RincianSPPTDHKP
{
    public $label = 'Rincian SPPT & DHKP';

    private $appConfig;

    public function __construct($appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function printForm($a = "", $m = "", $uid = "")
    {
        $thn = date("Y");
        $thnTagihan = $this->appConfig['tahun_tagihan'];

        echo '
            <div class="row">
                <div class="col-md-12">
                    <form id="TheForm-6" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Tahun Pajak: </label>
                                    <select class="form-control" name="tahun-pajak-rincian-sppt-dhkp" id="tahun-pajak-rincian-sppt-dhkp">';
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Kecamatan: </label>
                                    <select class="form-control" name="kecamatan-rincian-sppt-dhkp" id="kecamatan-rincian-sppt-dhkp"></select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Buku: </label>
                                    <select class="form-control" id="src-buku-rekap-dhkp" name="src-buku-rekap-dhkp">
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
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2" style="margin-top: 25px">
                                <button type="button" name="button6" id="button" class="btn btn-primary btn-orange mb5" onClick="showRincianSPPT()">Tampilkan</button>
                                <button type="button" name="buttonToExcel" id="buttonToExcel" class="btn btn-primary btn-blue mb5" onClick="excelRincianSPPT()">Ekspor ke xls</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-content-rincian-sppt-dhkp" class="monitoring-content"></div>
                </div>
            </div>
        ';
    }
}


?>

<script>
    function showRincianSPPT() {
        var buku = $("#src-buku-rekap-dhkp").val();
        var tahun = $("#tahun-pajak-rincian-sppt-dhkp").val();
        var kecamatan = $("#kecamatan-rincian-sppt-dhkp").val();
        var namakec = $("#kecamatan-rincian-sppt-dhkp option:selected").text();
        var e_periode = Number($("#periode6").val());
        var sts = 1;

        $("#monitoring-content-rincian-sppt-dhkp").html("loading ...");
        $("#monitoring-content-rincian-sppt-dhkp").load("view/PBB/monitoring/svc-monitoring-rinciansppt.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid','srch':'$srch'}"); 
                                                                                                                ?>", {
            bk: buku,
            th: tahun,
            st: sts,
            kc: kecamatan,
            n: namakec,
            eperiode: e_periode
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-rincian-sppt-dhkp").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }

    function excelRincianSPPT() {
        var buku = $("#src-buku-rekap-dhkp").val();
        var tahun = $("#tahun-pajak-rincian-sppt-dhkp").val();
        var kecamatan = $("#kecamatan-rincian-sppt-dhkp").val();
        var namakec = $("#kecamatan-rincian-sppt-dhkp option:selected").text();
        var sts = 1;

        window.open("view/PBB/monitoring/svc-toexcel-rinciansppt.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); //base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid','srch':'$srch'}"); 
                                                                        ?>&buku=" + buku + "&n=" + namakec + "&kc=" + kecamatan + "&st=" + sts + "&th=" + tahun + "&target_ketetapan=semua");
    }
</script>