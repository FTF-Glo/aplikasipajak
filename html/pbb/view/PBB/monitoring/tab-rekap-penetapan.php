<?php
class RekapPenetapan
{
    public $label = 'Rekap Penetapan';

    private $appConfig;

    public function __construct($appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function printForm($a = "", $m = "", $uid = "")
    {
        echo '
            <div class="row">
                <div class="col-md-12">
                    <form method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                        <button type="button" name="button4" class="btn btn-primary btn-orange" style="margin-bottom: 5px" onClick="showTunggakan()">Tampilkan</button>
                        <button type="button" name="buttonToExcel" class="btn btn-primary btn-blue" style="margin-bottom: 5px" onClick="excelTunggakan()"/>Ekspor ke xls</button>
                        <input type="hidden" id="export_e2"/>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-content-rekap-penetapan" class="monitoring-content"></div>
                </div>
            </div>
        ';
    }
}


?>

<script>
    function showTunggakan() {
        $("#monitoring-content-rekap-penetapan").html("loading ...");
        $("#monitoring-content-rekap-penetapan").load("view/PBB/monitoring/svc-monitoring-rekap-penetapan.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'4','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'4','uid':'$uid','srch':'$srch'}"); 
                                                                                                                ?>", {}, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-rekap-penetapan").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }

    function excelTunggakan() {
        window.open("view/PBB/monitoring/svc-toexcel-rekap-penetapan.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'4','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'4','uid':'$uid','srch':'$srch'}"); 
                                                                            ?>");
    }
</script>