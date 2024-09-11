<?php
class RekapTunggakan
{
    public $label = 'Rekap Tunggakan';

    private $appConfig;

    public function __construct($appConfig, $NBParam)
    {
        $this->appConfig = $appConfig;
        $this->NBParam = $NBParam;
    }

    public function printForm($a = "", $m = "", $uid = "")
    {
        $thn = date("Y");
        $thnTagihan = $this->appConfig['tahun_tagihan'];
        $bulan = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "Nopember", "Desember");
        echo '
            <div class="row">
                <div class="col-md-12">
                    <form id="TheForm-5" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Tahun Pajak: </label>
                                    <select name="tahun-pajak-rekap-tunggakan" class="form-control" id="tahun-pajak-rekap-tunggakan">
                                        <option value="" selected>Semua</option>';
        for ($t = $thn; $t >= 2008; $t--) {
            if ($t == $thnTagihan) {
                echo "<option value=\"$t\" selected>$t</option>";
            } else
                echo "<option value=\"$t\">$t</option>";
        }
        echo                        '</select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Kecamatan: </td>
                                    <select name="kecamatan-rekap-tunggakan" class="form-control" id="kecamatan-rekap-tunggakan"></select>
                                </div>
                            </div>
                            <div class="col-md-6" style="margin-top: 25px">
                                <div class="form-group">
                                    <button type="button" name="button4" class="btn btn-primary btn-orange" onClick="showTunggakan_()">Tampilkan</button>
                                    <button type="button" name="buttonToExcel" class="btn btn-primary btn-blue" onClick="excelTunggakan_()">Ekspor ke xls</button>
                                    <button type="button" name="button-denda" class="btn btn-primary bg-maka" id="button-denda" onClick="">Proses Hitung Denda</button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="export_e5"/>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-content-rekap-tunggakan" class="monitoring-content"></div>
                    <script>
                            $(document).ready(function () {
                                $("#button-denda").click(function(){
                                    if(confirm("Proses Hitung Denda memerlukan waktu yang cukup lama, apakah Anda akan melanjutkan proses ini?") ===false){
                                        return false;
                                    }
                                    $("#load-mask").css("display","block");
                                    $("#load-content").fadeIn();

                                    loadHD(\'' . $this->NBParam . '\');
                                });
                            });
                    </script>
                </div>
            </div>
        ';
    }
}


?>

<script>
    function showTunggakan_() {

        var tahun = $("#tahun-pajak-rekap-tunggakan").val();
        var kecamatan = $("#kecamatan-rekap-tunggakan").val();
        var namakec = $("#kecamatan-rekap-tunggakan option:selected").text();

        $("#monitoring-content-rekap-tunggakan").html("loading ...");
        $("#monitoring-content-rekap-tunggakan").load("view/PBB/monitoring/svc-tunggakan.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); 
                                                                                                ?>", {
            th: tahun,
            kc: kecamatan,
            n: namakec
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-rekap-tunggakan").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }

    function excelTunggakan_() {
        var tahun = $("#tahun-pajak-rekap-tunggakan").val();
        var kecamatan = $("#kecamatan-rekap-tunggakan").val();
        var namakec = $("#kecamatan-rekap-tunggakan option:selected").text();

        window.open("view/PBB/monitoring/svc-toexcel-tunggakan.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 'uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 'uid':'$uid','srch':'$srch'}"); 
                                                                        ?>" + "&n=" + namakec + "&kc=" + kecamatan + "&th=" + tahun + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT);
    }


    function loadHDSuccess(params) {
        $("#load-content").css("display", "none");
        $("#load-mask").css("display", "none");

        if (params.responseText) {
            var objResult = Ext.decode(params.responseText);

            if (objResult.RC == "0000") {
                alert('Penilaian sukses.');
                document.location.reload(true);
            } else {
                alert('Gagal melakukan penilaian. Terjadi kesalahan server');
            }
        } else {
            alert('Gagal melakukan penilaian. Terjadi kesalahan server');
        }
    }

    function loadHDFailure(params) {
        $("#load-content").css("display", "none");
        $("#load-mask").css("display", "none");
        alert('Gagal melakukan penghitungan denda. Terjadi kesalahan server');
    }

    function loadHD(svr_param) {

        var params = "{\"SVR_PRM\":\"" + svr_param + "\"}";
        params = Base64.encode(params);
        Ext.Ajax.request({
            url: 'view/PBB/monitoring/svc-hitung-denda.php',
            success: loadHDSuccess,
            failure: loadHDFailure,
            params: {
                req: params
            },
            timeout: 30000000
        });

    }
</script>