<?php
class DetailTunggakan
{
    public $label = 'Detail Tunggakan';

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
                    <form id="TheForm-detail-tunggakan" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Kecamatan: </label>
                                    <select id="kecamatan-detail-tunggakan" class="form-control"></select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Desa: </label>
                                    <select id="kelurahan-detail-tunggakan" class="form-control"></select>
                                </div>
                            </div>
                            <div class="col-md-4" style="margin-top: 25px">
                                <button type="button" name="buttonToExcel6" class="btn btn-primary btn-orange" onClick="excelDetailTunggakan()">Ekspor ke xls</button>
                            </div>
                        </div>
                        <input type="hidden" id="export_e6"/>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-content-6" class="monitoring-content"></div>
                    <script>
                        $("select#kecamatan-detail-tunggakan").change(function () {
                            showKelurahan(\'detail-tunggakan\');
                        })
                    </script>
                </div>
            </div>
        ';
    }
}


?>

<script>
    function excelDetailTunggakan() {
        var namakec = $("#kecamatan-detail-tunggakan option:selected").text();
        var kelurahan = $("#kelurahan-detail-tunggakan").val();
        var namakel = $("#kelurahan-detail-tunggakan option:selected").text();

        if (kelurahan == '' || kelurahan == null) alert('Kelurahan harus dipilih!!!');
        else window.open("view/PBB/monitoring/svc-toexcel-detailtunggakan.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 'uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 'uid':'$uid','srch':'$srch'}"); 
                                                                                ?>" + "&kl=" + kelurahan + "&nkl=" + namakel + "&nkc=" + namakec + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT);
    }
</script>