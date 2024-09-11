<?php
class RangkingRealisasi
{
    public $label = 'Rangking Realisasi';

    private $appConfig;
    private $idRole;
    private $dtUser;

    public function __construct($appConfig, $idRole, $dtUser)
    {
        $this->appConfig     = $appConfig;
        $this->idRole         = $idRole;
        $this->dtUser         = $dtUser;
    }

    public function printForm($a, $m, $uid)
    {
        $thn = date("Y");
        $thnTagihan = $this->appConfig['tahun_tagihan'];
        $lblKelurahan     = $this->appConfig['LABEL_KELURAHAN'];
        $bulan             = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "Nopember", "Desember");
        $filterWilayah  = "";
        if ($this->idRole == "rmKelurahan") {
            $filterWilayah = '<div class="col-md-2"><div class="form-group"><label for="">' . $lblKelurahan . '</label><select id="kelurahan-rangking-realisasi" class="form-control"><option value="' . $this->dtUser['kelurahan'] . '">' . $this->dtUser['CPC_TKL_KELURAHAN'] . '</option></select></div></div>';
        } else if ($this->idRole == "rmKecamatan") {
            $filterWilayah = '<div class="col-md-2"><div class="form-group"><label for="">Kecamatan</label><select id="kecamatan-rangking-realisasi" class="form-control"><option value="' . $this->dtUser['kecamatan'] . '">' . $this->dtUser['CPC_TKC_KECAMATAN'] . '</option></select></div></div><div class="col-md-2"><div class="form-group"><label for="">' . $lblKelurahan . '</label><select id="kelurahan-rangking-realisasi" class="form-control"></select></div></div>';
        } else {
            $filterWilayah = '<div class="col-md-2"><div class="form-group"><label for="">Kecamatan</label><select id="kecamatan-rangking-realisasi" class="form-control"></select></div></div><div class="col-md-2"><div class="form-group"><label for="">' . $lblKelurahan . '</label><select id="kelurahan-rangking-realisasi" class="form-control"></select></div></div>';
        }

        echo '
            <div class="row">
                <div class="col-md-12">
                    <form id="TheForm-5" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'5','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Tahun Pajak: </label>
                                    <select name="tahun-pajak-rangking-realisasi" class="form-control" id="tahun-pajak-rangking-realisasi">';
        echo "<option value=\"\">Semua</option>";
        for ($t = $thn; $t > 1993; $t--) {
            if ($t == $thnTagihan) {
                echo "<option value=\"$t\" selected>$t</option>";
            } else
                echo "<option value=\"$t\">$t</option>";
        }
        echo                         '</select>
                                </div>
                            </div>
                            ' . $filterWilayah . '
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Buku</label>
                                    <select id="src-buku-rangking-realisasi" class="form-control" name="src-buku-2">
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
                                <button type="button" name="button5" class="btn btn-primary btn-orange" onClick="showRangkingRealisasi()">Tampilkan</button>
                                <button type="button" name="buttonToExcel" class="btn btn-primary btn-blue" onClick="excelRangkingRealisasi()">Ekspor ke xls</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-content-rangking-realisasi" class="monitoring-content"></div>
                    <script>
                        showKelurahan(\'rangking-realisasi\');
                        showRW(\'rangking-realisasi\');
                        $("select#kecamatan-rangking-realisasi").change(function () {
                            showKelurahan(\'rangking-realisasi\');
                        })
                    </script>
                </div>
            </div>';
    }
}


?>

<script>
    function showRangkingRealisasi() {
        var tahun = "";
        var kecamatan = $("#kecamatan-rangking-realisasi").val();
        var kelurahan = $("#kelurahan-rangking-realisasi").val();
        var namakec = $("#kecamatan-rangking-realisasi option:selected").text();
        var namakel = $("#kelurahan-rangking-realisasi option:selected").text();
        var e_periode = "";
        var buku = $("#src-buku-rangking-realisasi").val();
        var sts = 1;

        $("#monitoring-content-rangking-realisasi").html("<div style='padding:80px'>Loading... &nbsp;<img src='image/icon/loadinfo.net.gif' width=20 hight=auto></img></div>");
        $("#monitoring-content-rangking-realisasi").load("view/PBB/monitoring_wilayah/svc-monitoring-rangking-realisasi.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'5','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'5','uid':'$uid','srch':'$srch'}"); 
                                                                                                                                ?>", {
            th: tahun,
            st: sts,
            kc: kecamatan,
            kl: kelurahan,
            n: namakec,
            eperiode: e_periode,
            buku: buku
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-rangking-realisasi").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }

    function excelRangkingRealisasi() {
        var tahun = "";
        var buku = $("#src-buku-rangking-realisasi").val();
        var kecamatan = $("#kecamatan-rangking-realisasi").val();
        var kelurahan = $("#kelurahan-rangking-realisasi").val();
        var namakec = $("#kecamatan-rangking-realisasi option:selected").text();
        // alert(namakec);
        var namakel = $("#kelurahan-rangking-realisasi option:selected").text();
        var e_periode = "";
        var sts = 1;

        window.open("view/PBB/monitoring_wilayah/svc-toexcel-rangking-realisasi.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'5','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'5','uid':'$uid','srch':'$srch'}"); 
                                                                                        ?>&n=" + namakec + "&nKel=" + namakel + "&kc=" + kecamatan + "&kl=" + kelurahan + "&st=" + sts + "&th=" + tahun + "&eperiode=" + e_periode + "&target_rangking-realisasi=semua&buku=" + buku);
    }
</script>