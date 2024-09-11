<?php
class Realisasi
{
    public $label = 'Realisasi';

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
        $thnTagihan     = $this->appConfig['tahun_tagihan'];
        $lblKelurahan     = $this->appConfig['LABEL_KELURAHAN'];
        $bulan             = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "Nopember", "Desember");
        $filterWilayah  = "";
        if ($this->idRole == "rmKelurahan") {
            $filterWilayah = '<div class="col-md-2"><div class="form-group"><label for="">' . $lblKelurahan . '</label><select id="kelurahan-realisasi" class="form-control"><option value="' . $this->dtUser['kelurahan'] . '">' . $this->dtUser['CPC_TKL_KELURAHAN'] . '</option></select></div></div>';
        } else if ($this->idRole == "rmKecamatan") {
            $filterWilayah = '<div class="col-md-2"><div class="form-group"><label for="">Kecamatan</label><select id="kecamatan-realisasi" class="form-control"><option value="' . $this->dtUser['kecamatan'] . '">' . $this->dtUser['CPC_TKC_KECAMATAN'] . '</option></select></div></div><div class="col-md-2"><div class="form-group"><label for="">' . $lblKelurahan . '</label><select id="kelurahan-realisasi"></select></div></div>';
        } else {
            $filterWilayah = '<div class="col-md-2"><div class="form-group"><label for="">Kecamatan</label><select id="kecamatan-realisasi" class="form-control"></select></div></div><div class="col-md-2"><div class="form-group"><label for="">' . $lblKelurahan . '</label><select id="kelurahan-realisasi" class="form-control"></select></div></div>';
        }
        echo '
            <div class="row">
                <div class="col-md-12">
                    <form id="TheForm-2" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Tahun Pajak </label>
                                    <select name="tahun-pajak-realisasi" class="form-control" id="tahun-pajak-realisasi">';
        echo "<option value=\"\">Semua</option>";
        for ($t = $thn; $t > 1993; $t--) {
            if ($t == $thnTagihan) {
                echo "<option value=\"$t\" selected>$t</option>";
            } else
                echo "<option value=\"$t\">$t</option>";
        }
        echo                        '</select> 
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Bulan</label>
                                    <select name="periode2-realisasi" class="form-control" id="periode2-realisasi">';

        for ($b = 0; $b < 12; $b++) {
            echo "<option value=\"" . ($b + 1) . "\">" . $bulan[$b] . "</option>";
        }

        echo                        '</select> 
                                </div>
                            </div>
                            ' . $filterWilayah . '
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Buku</label>
                                    <select id="src-buku-realisasi" class="form-control" name="src-buku-realisasi">
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
                                <button type="button" name="button3" class="btn btn-primary btn-orange mb5" onClick="showModelRealisasi()">Tampilkan</button>
                                <button type="button" name="buttonToExcel" class="btn btn-primary btn-blue mb5" onClick="excelModelRealisasi()">Ekspor ke xls</button>
                            </div>
                            <input type="hidden" id="export_e2"/>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-realisasi" class="monitoring-content"></div>
                    <script>
                        showKelurahan(\'realisasi\');
                        // showRW(\'realisasi\');
                        $("select#kecamatan-realisasi").change(function () {
                            showKelurahan(\'realisasi\');
                        })
                    </script>
                </div>
            </div>
        ';
    }
}


?>

<script>
    function showModelRealisasi() {
        var buku = $("#src-buku-realisasi").val();
        var tahun = $("#tahun-pajak-realisasi").val();
        var kecamatan = $("#kecamatan-realisasi").val();
        var kelurahan = $("#kelurahan-realisasi").val();
        var namakec = $("#kecamatan-realisasi option:selected").text();
        var namakel = $("#kelurahan-realisasi option:selected").text();
        var e_periode = Number($("#periode2-realisasi").val());
        var sts = 1;

        $("#monitoring-realisasi").html("loading ...");
        $("#monitoring-realisasi").load("view/PBB/monitoring_wilayah/svc-monitoring-realisasi.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); 
                                                                                                    ?>", {
            bk: buku,
            th: tahun,
            st: sts,
            kc: kecamatan,
            kl: kelurahan,
            n: namakec,
            nkel: namakel,
            eperiode: e_periode,
            target_ketetapan: 'semua'
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-3").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }

    function excelModelRealisasi() {
        var buku = $("#src-buku-realisasi").val();
        var tahun = $("#tahun-pajak-realisasi").val();
        var kecamatan = $("#kecamatan-realisasi").val();
        var kelurahan = $("#kelurahan-realisasi").val();
        var namakec = $("#kecamatan-realisasi option:selected").text();
        var namakel = $("#kelurahan-realisasi option:selected").text();
        var e_periode = Number($("#periode2-realisasi").val());
        var sts = 1;

        window.open("view/PBB/monitoring_wilayah/svc-toexcel-realisasi.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); 
                                                                                ?>&bk=" + buku + "&n=" + namakec + "&nkel=" + namakel + "&kc=" + kecamatan + "&kl=" + kelurahan + "&st=" + sts + "&th=" + tahun + "&eperiode=" + e_periode + "&target_ketetapan=semua");
    }
</script>