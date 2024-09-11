<?php
class Penetapan
{
    public $label = 'Penetapan';

    private $appConfig;

    public function __construct($appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function printForm($a, $m, $uid)
    {
        $thn = date("Y");
        $thnTagihan = $this->appConfig['tahun_tagihan'];

        echo '
            <div class="row">
                <div class="col-md-12">
                    <form method="post" action="view/PBB/monitoring_pembayaran_kolektif/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Kode Bayar: </label>
                                    <input type="text" class="form-control" name="kode" id="kode"size="10" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Tgl Pembayaran: </label>
                                    <div class="row">
                                        <div class="col-md-5">
                                            <input type="text" name="jatuh-tempo" class="form-control" id="jatuh-tempo1-col-1" size="10" />
                                        </div>
                                        <div class="col-md-2" style="margin-top: 10px;">s/d</div>
                                        <div class="col-md-5">
                                            <input type="text" name="jatuh-tempo2" class="form-control" id="jatuh-tempo2-col-1" size="10" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2" style="margin-top: 25px">
                                <button type="button" name="button6" class="btn btn-primary btn-orange mb5" onClick="showPenetapan()">Tampilkan</button>
                                <button type="button" name="buttonToExcel" class="btn btn-primary btn-blue mb5" onClick="excelPenetapan()">Ekspor ke xls</button>
                            </div>
                        </div>
                        <input type="hidden" id="export_penetapan"/>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-content-penetapan" class="monitoring-content"></div>
                    <script>
                        $("select#kecamatan-penetapan").change(function () {
                            showKelurahan(\'penetapan\');
                        })
                    </script>
                </div>
            </div>
        ';
    }
}


?>

<script>
    function showPenetapan() {
        // var tahun 		= $("#tahun-pajak-penetapan").val();
        // var kecamatan 	= $("#kecamatan-penetapan").val();
        // var kelurahan 	= $("#kelurahan-penetapan").val();
        // var namakec		= $("#kecamatan-penetapan option:selected").text();
        // var namakel		= $("#kelurahan-penetapan option:selected").text();
        var kode = $("#kode").val();
        var tempo1 = $("#jatuh-tempo1-col-1").val();
        var tempo2 = $("#jatuh-tempo2-col-1").val();

        $("#monitoring-content-penetapan").html("loading ...");
        $("#monitoring-content-penetapan").load("view/PBB/monitoring_pembayaran_kolektif/svc-rekap-kolektif.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid','srch':'$srch'}"); 
                                                                                                                    ?>", {
            kode: kode,
            tempo1: tempo1,
            tempo2: tempo2
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-penetapan").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }

    function showPenetapanPage(page) {
        var kode = $("#kode").val();
        var tempo1 = $("#jatuh-tempo1-col-1").val();
        var tempo2 = $("#jatuh-tempo2-col-1").val();
        $("#monitoring-content-penetapan").html("loading ...");
        $("#monitoring-content-penetapan").load("view/PBB/monitoring_pembayaran_kolektif/svc-rekap-kolektif.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid','srch':'$srch'}"); 
                                                                                                                    ?>", {
            kode: kode,
            tempo1: tempo1,
            tempo2: tempo2,
            page: page
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-penetapan").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }

    function excelPenetapan() {
        var kode = $("#kode").val();
        // alert(kode);                
        var tempo1 = $("#jatuh-tempo1-col-1").val();
        var tempo2 = $("#jatuh-tempo2-col-1").val();
        window.open("view/PBB/monitoring_pembayaran_kolektif/svc-toexcel-rekap-kolektif.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid','srch':'$srch'}"); 
                                                                                                ?>" + "&kode=" + kode + "&tempo1=" + tempo1 + "&tempo2=" + tempo2);
    }
</script>