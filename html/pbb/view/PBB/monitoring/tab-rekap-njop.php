<?php
class RekapNJOP
{
    public $label = 'Rekap NJOP';

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
                    <form id="TheForm-7" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'7','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-3">
                                <button type="button" name="button7" id="button" class="btn btn-primary btn-orange mb5" onClick="showRekapNJOP2()">Tampilkan</button>
                                <button type="button" name="buttonToExcel" id="buttonToExcel" class="btn btn-primary btn-blue mb5" onClick="excelRekapNJOP()">Ekspor ke xls</button>
                            </div>
                        </div>
                        <input type="hidden" id="export_e2"/>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-content-rekap-njop" class="monitoring-content"></div>
                </div>
            </div>
        ';
    }
}


?>

<script>
    function showRekapNJOP2() {
        // alert("123");
        $("#monitoring-content-rekap-njop").html("loading ...");
        $("#monitoring-content-rekap-njop").load("view/PBB/monitoring/svc-monitoring-rekap-njop.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'7','uid':'$uid'}"); ?>", {}, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-rekap-njop").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }

    function excelRekapNJOP() {

        window.open("view/PBB/monitoring/svc-toexcel-rekap-njop.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'7','uid':'$uid'}"); ?>");
    }
</script>