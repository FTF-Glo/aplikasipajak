<?php
class ReportNjop
{
    public $label = 'Statistik NJOP';

    private $appConfig;

    public function __construct($appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function printForm($a = "", $m = "", $uid = "")
    {
        $thn = date("Y");
        $thnTagihan = $this->appConfig['tahun_tagihan'];
        $count = ($thn > $thnTagihan) ? ($thn - $thnTagihan) + $thnTagihan : $thnTagihan;
        $opt = "<select name=\"tahun\" class=\"form-control\" id=\"tahun-njop\">";
        for ($a = $count; $a >= ($count - 5); $a--) {
            $opt .= "<option value=\"{$a}\" " . (($thnTagihan == $a) ? "selected='selected'" : "") . ">{$a}</option>";
        }
        $opt .= "</select>";
        echo '
            <div class="row">
                <div class="col-md-12">
                    <form id="TheForm-7" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'7','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Tahun: </label>
                                    ' . $opt . '
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Buku: </label>
                                    <select id="src-buku-report-njop" class="form-control" name="src-buku-report-njop">
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
                            <div class="col-md-3" style="margin-top: 25px">
                                <button type="button" name="button7" class="btn btn-primary btn-orange" id="button" onClick="showRekapNJOP()">Tampilkan</button>
                                <button type="button" name="buttonToExcel" class="btn btn-primary btn-blue" id="buttonToExcel" onClick="excelReportNJOP()">Ekspor ke xls</button>
								<button type="button" name="buttonToExcelV2" class="btn btn-primary btn-blue" id="buttonToExcelV2" onClick="excelReportNJOPV2()">xls V2</button>
							</div>
                            <input type="hidden" id="export_e2"/>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-content-report-njop" class="monitoring-content"></div>
                </div>
            </div>
        ';
    }
}


?>

<script>
    function showRekapNJOP() {
        $("#monitoring-content-report-njop").html("loading ...");
        $("#monitoring-content-report-njop").load("view/PBB/monitoring/svc-monitoring-report-njop.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'7','uid':'$uid'}"); ?>", {
            tahun: $('#tahun-njop').val(),
			buku : $("#src-buku-report-njop").val()
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-report-njop").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
		
    }

    function excelReportNJOP() {
        window.open("view/PBB/monitoring/svc-toexcel-report-njop.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'7','uid':'$uid'}"); ?>&tahun=" + $('#tahun-njop').val() + '&buku=' + $('#src-buku-report-njop').val());
    }
	
	function excelReportNJOPV2() {
        window.open("view/PBB/monitoring/svc-toexcel-report-njopV2.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'7','uid':'$uid'}"); ?>&tahun=" + $('#tahun-njop').val() + '&buku=' + $('#src-buku-report-njop').val());
    }
</script>