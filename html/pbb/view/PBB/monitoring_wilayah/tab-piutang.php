<!-- 123 -->
<?php
class Piutang
{
    public $label = 'Realisasi Piutang';

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
        $thn             = date("Y");
        $thnTagihan     = $this->appConfig['tahun_tagihan'];
        $lblKelurahan     = $this->appConfig['LABEL_KELURAHAN'];
        $bulan = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "Nopember", "Desember");
        $filterWilayah  = "";
        if ($this->idRole == "rmKelurahan") {
            $filterWilayah = '<div class="col-md-3"><div class="form-group"><label for="">' . $lblKelurahan . '</label><select id="kelurahan-piutang" class="form-control"><option value="' . $this->dtUser['kelurahan'] . '">' . $this->dtUser['CPC_TKL_KELURAHAN'] . '</option></select></div></div>';
        } else if ($this->idRole == "rmKecamatan") {
            $filterWilayah = '<div class="col-md-3"><div class="form-group"><label for="">Kecamatan</label><select id="kecamatan-piutang" class="form-control"><option value="' . $this->dtUser['kecamatan'] . '">' . $this->dtUser['CPC_TKC_KECAMATAN'] . '</option></select></div></div><div class="col-md-3"><div class="form-group"><label for="">' . $lblKelurahan . '</label><select id="kelurahan-piutang" class="form-control"></select></div></div>';
        } else {
            $filterWilayah = '<div class="col-md-3"><div class="form-group"><label for="">Kecamatan</label><select id="kecamatan-piutang" class="form-control"></select></div></div><div class="col-md-3"><div class="form-group"><label for="">' . $lblKelurahan . '</label><select id="kelurahan-piutang" class="form-control"></select></div></div>';
        }
        echo '
            <div class="row">
                <div class="col-md-12">
                    <form id="TheForm-2" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Tahun Pajak</label>
                                    <div class="row">
                                        <div class="col-md-5">
                                            <select name="tahun-pajak-piutang-awal" class="form-control" id="tahun-pajak-piutang-awal">';
        echo "<option value=\"\">Semua</option>";
        for ($t = $thn; $t > 1993; $t--) {
            if ($t == $thnTagihan) {
                echo "<option value=\"$t\" selected>$t</option>";
            } else
                echo "<option value=\"$t\">$t</option>";
        }
        echo                                '</select>
                                        </div>
                                        <div class="col-md-2" style="margin-top:10px;">s/d</div>
                                        <div class="col-md-5">
                                            <select name="tahun-pajak-piutang-akhir" class="form-control" id="tahun-pajak-piutang-akhir">';
        echo "<option value=\"\">Semua</option>";
        for ($t = $thn; $t > 1993; $t--) {
            if ($t == $thnTagihan) {
                echo "<option value=\"$t\" selected>$t</option>";
            } else
                echo "<option value=\"$t\">$t</option>";
        }
        echo                                '</select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            ' . $filterWilayah . '
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Buku: </label>
                                    <select id="src-buku-piutang-akhir" class="form-control" name="src-buku-2">
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Tanggal Bayar: </label>
                                    <div class="row">
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" name="piutang-tgl-bayar-awal" id="piutang-tgl-bayar-awal" size="10" />
                                        </div>
                                        <div class="col-md-2" style="margin-top: 10px;">s/d</div>
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" name="piutang-tgl-bayar-akhir" id="piutang-tgl-bayar-akhir" size="10" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Bank</label>
                                    <select id="bank-piutang" class="form-control" name="bank-piutang"></select>
                                </div>
                            </div>
                            <div class="col-md-5" style="margin-top:25px">
                                <button type="button" name="button3" class="btn btn-primary btn-orange mb5" onClick="showModelPiutang()">Tampilkan</button>
                                <button type="button" name="buttonToExcel" class="btn btn-primary btn-blue mb5" onClick="excelModelPiutang()">Ekspor ke xls</button>
                            </div>
                        </div>
                        <input type="hidden" id="export_e2"/>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-piutang" class="monitoring-content"></div>
					<script>
						showBank(\'piutang\');
						showKelurahan(\'piutang\');
						$("select#kecamatan-piutang").change(function () {
							showKelurahan(\'piutang\');
						})
                    </script>
                </div>
            </div>
        ';
    }
}


?>

<script>
    function showModelPiutang() {
        var buku = $("#src-buku-piutang-akhir").val();
        var tahunawal = $("#tahun-pajak-piutang-awal").val();
        var tahunakhir = $("#tahun-pajak-piutang-akhir").val();
        var kecamatan = $("#kecamatan-piutang").val();
        var kelurahan = $("#kelurahan-piutang").val();
        var namakec = $("#kecamatan-piutang option:selected").text();
        var namakel = $("#kelurahan-piutang option:selected").text();
        var tglawal = $("#piutang-tgl-bayar-awal").val();
        var tglakhir = $("#piutang-tgl-bayar-akhir").val();
        var bank = $("#bank-piutang").val();
        var sts = 1;
        if ($("#piutang-tgl-bayar-awal").val() == "") {
            alert("isi tanggal awal terlebih dahulu")
        } else {
            $("#monitoring-piutang").html("<div style='padding:80px'>Loading... &nbsp;<img src='image/icon/loadinfo.net.gif' width=20 hight=auto></img></div>");
            $("#monitoring-piutang").load("view/PBB/monitoring_wilayah/svc-monitoring-piutang.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); 
                                                                                                    ?>", {
                tahunawal: tahunawal,
                tahunakhir: tahunakhir,
                st: sts,
                kecamatan: kecamatan,
                kelurahan: kelurahan,
                namakel: namakel,
                namakec: namakec,
                tglawal: tglawal,
                tglakhir: tglakhir,
                bank: bank,
                buku: buku
            }, function(response, status, xhr) {
                if (status == "error") {
                    var msg = "Sorry but there was an error: ";
                    $("#monitoring-content-3").html(msg + xhr.status + " " + xhr.statusText);
                }
            });
        }
    }

    function excelModelPiutang() {
        var buku = $("#src-buku-piutang-akhir").val();
        var tahunawal = $("#tahun-pajak-piutang-awal").val();
        var tahunakhir = $("#tahun-pajak-piutang-akhir").val();
        var kecamatan = $("#kecamatan-piutang").val();
        var kelurahan = $("#kelurahan-piutang").val();
        var namakec = $("#kecamatan-piutang option:selected").text();
        var namakel = $("#kelurahan-piutang option:selected").text();
        var tglawal = $("#piutang-tgl-bayar-awal").val();
        var tglakhir = $("#piutang-tgl-bayar-akhir").val();
        var bank = $("#bank-piutang").val();
        var nmbank = $("#bank-piutang option:selected").text();
        var sts = 1;
        if ($("#piutang-tgl-bayar-awal").val() == "") {
            alert("isi tanggal awal terlebih dahulu")
        } else {
            window.open("view/PBB/monitoring_wilayah/svc-toexcel-piutang.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); 
                                                                                ?>&tahunawal=" + tahunawal + "&tahunakhir=" + tahunakhir + "&kecamatan=" + kecamatan + "&kelurahan=" + kelurahan + "&namakec=" + namakec + "&namakel=" + namakel + "&tglawal=" + tglawal + "&tglakhir=" + tglakhir + "&bank=" + bank + "&nmbank=" + nmbank + "&buku=" + buku);
        }
    }
</script>