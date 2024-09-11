<?php
class Realisasi
{
    public $label = 'Realisasi';

    private $appConfig;

    public function __construct($appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function printForm($a = "", $m = "", $uid = "")
    {
        $thn = date("Y");
        // $thn = '2024';
        $thnTagihan = $this->appConfig['tahun_tagihan'];
        $bulan = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "Nopember", "Desember");

        echo '
            <div class="row">
                <div class="col-md-12">
                    <form id="TheForm-2" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Tahun Pajak: </label>
                                    <select name="tahun-pajak-realisasi" class="form-control" id="tahun-pajak-realisasi">';
                                        echo '<option value="">Semua</option>';
                                        for ($t = $thn; $t >= 2008; $t--) {
                                            if ($t == $thnTagihan) {
                                                echo "<option value=$t selected>$t</option>";
                                            } else
                                                echo "<option value=$t>$t</option>";
                                        }
                                echo '</select> 
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tgl Pembayaran: </label>
                                    <div class="row">
                                        <div class="col-md-5">
                                            <input type="text" value="' . date('Y') . '-01-01" name="jatuh-tempo" id="periode1-realisasi" class="form-control" size="10" />
                                        </div>
                                        <div class="col-md-1" style="padding:5px 0 0 0">
                                            <label>s/d</label>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" name="jatuh-tempo2" class="form-control" value="' . date("Y-m-d") . '" id="periode2-realisasi" size="10" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Ketetapan: </label>
                                    <select name="ketetapan-realisasi" class="form-control" id="ketetapan-realisasi">
                                        <option value="0">SEMUA</option>
                                        <option value="1" selected>MASAL</option>
                                        <option value="2">SUSULAN</option>
                                    </select> 
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Kecamatan: </label>
                                    <select name="kecamatan-realisasi" class="form-control" id="kecamatan-realisasi"></select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Buku: </label>
                                    <select id="src-buku-realisasi" class="form-control" name="src-buku-realisasi">
                                        <option value="0"       >Pilih Semua</option>
                                        <option value="1"       >Buku 1</option>
                                        <option value="12"      >Buku 1,2</option>
                                        <option value="123"     >Buku 1,2,3</option>
                                        <option value="1234"    >Buku 1,2,3,4</option>
                                        <option value="12345"   >Buku 1,2,3,4,5</option>
                                        <option value="2"       >Buku 2</option>
                                        <option value="23"      >Buku 2,3</option>
                                        <option value="234"     >Buku 2,3,4</option>
                                        <option value="2345"    >Buku 2,3,4,5</option>
                                        <option value="3"       >Buku 3</option>
                                        <option value="34"      >Buku 3,4</option>
                                        <option value="345"     >Buku 3,4,5</option>
                                        <option value="4"       >Buku 4</option>
                                        <option value="45"      >Buku 4,5</option>
                                        <option value="5"       >Buku 5</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12" style="text-align:right">
                                <button type="button" name="button3" class="btn btn-primary btn-orange" onClick="showModelRealisasi()" style="margin-bottom: 5px">Tampilkan</button>
                                <button type="button" name="buttonToExcel" class="btn btn-primary btn-blue" onClick="excelModelRealisasi()" style="margin-bottom: 5px">Ekspor ke xls</button>
								<!--button type="button" name="buttonToExcel" class="btn btn-primary btn-blue" onClick="excelModelRealisasiV2()" style="margin-bottom: 5px">xls V2</button-->
                                <button type="button" name="button3_new" class="btn btn-success btn-green" onClick="showModelRealisasi(1)" style="margin-bottom: 5px">Tampilkan Persentase</button>
                            </div>     
                        </div>
                        <input type="hidden" id="export_e2"/>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-realisasi" class="monitoring-content"></div>
                </div>
            </div>
        ';
    }
}


?>

<script>
    function showModelRealisasi(displaypersen = 0) {
        var buku = $("#src-buku-realisasi").val();
        var tahun = $("#tahun-pajak-realisasi").val();
        var kecamatan = $("#kecamatan-realisasi").val();
        var namakec = $("#kecamatan-realisasi option:selected").text();
        var eperiode = $("#periode1-realisasi").val();
        var eperiode2 = $("#periode2-realisasi").val();
        var ketetapan = $("#ketetapan-realisasi").val();
        // alert(eperiode);
        // alert(eperiode2);
        var sts = 1;
        var q = '<?= base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}") ?>';

        $("#monitoring-realisasi").html("<div style='padding:80px'>Loading... &nbsp;<img src='image/icon/loadinfo.net.gif' width=20 hight=auto></img></div>");
        $("#monitoring-realisasi").load("view/PBB/monitoring/svc-monitoring-realisasi.php?q=" + q, {
            bk: buku,
            th: tahun,
            st: sts,
            kc: kecamatan,
            n: namakec,
            eperiode: eperiode,
            eperiode2: eperiode2,
            target_ketetapan: 'semua',
            ketetapan: ketetapan,
            displaypersen: displaypersen,
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-3").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }



    function excelModelRealisasi(displaypersen = 0) {
        var buku = $("#src-buku-realisasi").val();
        var tahun = $("#tahun-pajak-realisasi").val();
        var kecamatan = $("#kecamatan-realisasi").val();

        var namakec = $("#kecamatan-realisasi option:selected").text();
        // var e_periode = Number($("#periode2-realisasi").val());
        var eperiode = $("#periode1-realisasi").val();
        var eperiode2 = $("#periode2-realisasi").val();
        var sts = 1;
        var q = '<?= base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}") ?>';
        
        if (displaypersen == 1) {
            window.open("view/PBB/monitoring/svc-toexcel-realisasi-persen.php?q="+ q +"&buku=" + buku + "&n=" + namakec + "&kc=" + kecamatan + "&st=" + sts + "&th=" + tahun + "&eperiode=" + eperiode + "&eperiode2=" + eperiode2 + "&target_ketetapan=semua");
            return;
        }

        window.open("view/PBB/monitoring/svc-toexcel-realisasi.php?q="+ q +"&buku=" + buku + "&n=" + namakec + "&kc=" + kecamatan + "&st=" + sts + "&th=" + tahun + "&eperiode=" + eperiode + "&eperiode2=" + eperiode2 + "&target_ketetapan=semua");
    }
	
	function excelModelRealisasiV2(displaypersen = 0) {
        var buku = $("#src-buku-realisasi").val();
        var tahun = $("#tahun-pajak-realisasi").val();
        var kecamatan = $("#kecamatan-realisasi").val();

        var namakec = $("#kecamatan-realisasi option:selected").text();
        // var e_periode = Number($("#periode2-realisasi").val());
        var eperiode = $("#periode1-realisasi").val();
        var eperiode2 = $("#periode2-realisasi").val();
        var sts = 1;
        var q = '<?= base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}") ?>';

        window.open("view/PBB/monitoring/svc-toexcel-realisasiV2.php?q="+ q +"&buku=" + buku + "&n=" + namakec + "&kc=" + kecamatan + "&st=" + sts + "&th=" + tahun + "&eperiode=" + eperiode + "&eperiode2=" + eperiode2 + "&target_ketetapan=semua");
    }

    function pdfModelRealisasiPersen() {
        var buku = $("#src-buku-realisasi").val();
        var tahun = $("#tahun-pajak-realisasi").val();
        var kecamatan = $("#kecamatan-realisasi").val();

        var namakec = $("#kecamatan-realisasi option:selected").text();
        var eperiode = $("#periode1-realisasi").val();
        var eperiode2 = $("#periode2-realisasi").val();
        var sts = 1;
        var q = '<?= base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}") ?>';
        
        
        window.open("view/PBB/monitoring/svc-topdf-realisasi-persen.php?q="+ q +"&buku=" + buku + "&n=" + namakec + "&kc=" + kecamatan + "&st=" + sts + "&th=" + tahun + "&eperiode=" + eperiode + "&eperiode2=" + eperiode2 + "&target_ketetapan=semua");
    }
</script>