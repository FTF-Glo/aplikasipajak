<?php
class DetailPembayaran
{
    public $label = 'Detail Pembayaran';

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
                    <form id="TheForm-detail-pembayaran" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Periode: </label>
                                    <div class="row">
                                        <div class="col-md-5">
                                            <input type="text" class="form-control text-center" value="' . date('Y') . '-01-01" id="periode1-detail-pembayaran" size="10" />
                                        </div>
                                        <div class="col-md-1" style="padding:5px 0 0 0">
                                            <label>s/d</label>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" class="form-control text-center" value="' . date("Y-m-d") . '" id="periode2-detail-pembayaran" size="10" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Kecamatan: </label>
                                    <select id="kecamatan-detail-pembayaran" class="form-control"></select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">' . $this->appConfig['LABEL_KELURAHAN'] . ': </label>
                                    <select id="kelurahan-detail-pembayaran" class="form-control"></select>
                                </div>
                            </div>
                            <div class="col-md-2" style="margin-top: 25px">
                                <button type="button" name="buttonToExcel6" class="btn btn-primary btn-orange" onClick="excelDetailPembayaran()">Ekspor ke xls</button>
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
                        $("select#kecamatan-detail-pembayaran").change(function () {
                            showKelurahan(\'detail-pembayaran\');
                        })
                    </script>
                </div>
            </div>
        ';
    }
}


?>

<script>
    function excelDetailPembayaran() {
        var kodekec = $("#kecamatan-detail-pembayaran").val();
        var namakec = $("#kecamatan-detail-pembayaran option:selected").text();
        var kodekel = $("#kelurahan-detail-pembayaran").val();
        var namakel = $("#kelurahan-detail-pembayaran option:selected").text();
        var tgl1 = $("#periode1-detail-pembayaran").val();
        var tgl2 = $("#periode2-detail-pembayaran").val();
        if (tgl1=='' || tgl2=='') {
            alert('Silakan Setting tanggal Periode terlebih dahulu');
            return false;
        }
        window.open("view/PBB/monitoring/svc-toexcel-detailpembayaran.php?q=<?=base64_encode("{'a':'$a', 'm':'$m', 'uid':'$uid'}")?>" + "&tgl1=" + tgl1 + "&tgl2=" + tgl2 + "&kel=" + kodekel + "&nmkel=" + namakel + "&kec=" + kodekec + "&nmkec=" + namakec);
    }
</script>