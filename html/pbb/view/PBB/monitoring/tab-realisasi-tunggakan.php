    <?php
class RealisasiTunggakan
{
    public $label = 'Realisasi Tunggakan';

    private $appConfig;

    public function __construct($appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function getKecamatan($idKec = '', $idKab = "")
    {
        global $DBLink;

        $qwhere = "";
        if ($idKab) {
            $qwhere = " WHERE CPC_TKC_KKID='$idKab'";
        } else if ($idKec) {
            $qwhere = " WHERE CPC_TKC_ID='$idKec'";
        }

        $qry = "SELECT * FROM cppmod_tax_kecamatan " . $qwhere;
        $res = mysqli_query($DBLink, $qry);
        if (!$res) {
            echo $qry . "<br>";
            echo mysqli_error($DBLink);
        } else {
            $data = array();
            while ($row = mysqli_fetch_assoc($res)) {
                $tmp = array(
                    'id' => $row['CPC_TKC_ID'],
                    'pid' => $row['CPC_TKC_KKID'],
                    'name' => $row['CPC_TKC_KECAMATAN']
                );
                $data[] = $tmp;
            }
            return $data;
        }
    }

    public function printForm($a = "", $m = "", $uid = "")
    {
        $thn = date("Y") - 1;
        $thnTagihan = $this->appConfig['tahun_tagihan'];
        $bulan = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "Nopember", "Desember");
        $cityID = $this->appConfig['KODE_KOTA'];

        $kecOP = $this->getKecamatan('', $cityID);

        $optionKecOP = '<option value="">Semua Kecamatan</option>';
        foreach ($kecOP as $row) {
            $optionKecOP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
        }
        echo '
            <div class="row">
                <div class="col-md-12">
                    <form id="TheForm-2" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Tahun Pajak: </label>
                                        <div class="row">
                                            <div class="col-md-5" style="padding-right:0">
                                                <select class="form-control" id="tahun-pajak-realisasi-tunggakan">';
                                                    for ($t = $thn; $t >= 2000; $t--) {
                                                        echo "<option value=$t ".(($t==($thn-4)) ? 'selected':'').">$t</option>";
                                                    }
                                            echo '</select> 
                                            </div>
                                            <div class="col-md-2" style="padding:7 0 0 0;text-align:center">s/d</div>
                                            <div class="col-md-5" style="padding-left:0">
                                                <select class="form-control" id="tahun-pajak-realisasi-tunggakan2">';
                                                    for ($t = $thn; $t >= 2000; $t--) {
                                                        echo "<option value=$t>$t</option>";
                                                    }
                                            echo '</select> 
                                            </div>
                                        </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tgl Pembayaran: </label>
                                    <div class="row">
                                        <div class="col-md-5" style="padding-right:0">
                                            <input type="text" value="' . date('Y') . '-01-01" id="periode1-realisasi-tunggakan" class="form-control" size="10" />
                                        </div>
                                        <div class="col-md-2" style="padding:7 0 0 0;text-align:center">
                                            <label>s/d</label>
                                        </div>
                                        <div class="col-md-5" style="padding-left:0">
                                            <input type="text" class="form-control" value="' . date("Y-m-d") . '" id="periode2-realisasi-tunggakan" size="10" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2" style="padding:0">
                                <div class="form-group">
                                    <label>Kecamatan: </label>
                                    <select class="form-control" id="kecamatan-realisasi-tunggakan">
                                        '.$optionKecOP.'
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Buku: </label>
                                    <select id="src-buku-realisasi-tunggakan" class="form-control" name="src-buku-realisasi-tunggakan">
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
                                <button type="button" name="button3" class="btn btn-primary btn-orange" onClick="showModelRealisasiTunggakan()" style="margin-bottom: 5px">Tampilkan</button>
                                <button type="button" name="buttonToExcel" class="btn btn-primary btn-blue" onClick="excelModelRealisasiTunggakan()" style="margin-bottom:5px" />xls</button>
                            </div>
                            <input type="hidden" id="export_e2"/>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-content-realisasi-tunggakan" class="monitoring-content"></div>
                </div>
            </div>
        ';
    }
}


?>

<script>
    function showModelRealisasiTunggakan() {
        var buku = $("#src-buku-realisasi-tunggakan").val();
        var thn1 = $("#tahun-pajak-realisasi-tunggakan").val();
        var thn2 = $("#tahun-pajak-realisasi-tunggakan2").val();
        var kecamatan = $("#kecamatan-realisasi-tunggakan").val();
        var namakec = $("#kecamatan-realisasi-tunggakan option:selected").text();
        var eperiode1 = $("#periode1-realisasi-tunggakan").val();
        var eperiode2 = $("#periode2-realisasi-tunggakan").val();
        var sts = 1;

        $("#monitoring-content-realisasi-tunggakan").html("<div style='padding:80px'>Loading... &nbsp;<img src='image/icon/loadinfo.net.gif' width=20 hight=auto></img></div>");
        $("#monitoring-content-realisasi-tunggakan").load("view/PBB/monitoring/svc-monitoring-realisasi-tunggakan.php?q=<?=base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}")?>", {
            bk: buku,
            th1: thn1,
            th2: thn2,
            thntagihan: THN_TAGIHAN,
            st: sts,
            kec: kecamatan,
            namakec: namakec,
            eperiode1: eperiode1,
            eperiode2: eperiode2,
            target_ketetapan: 'semua'
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-realisasi-tunggakan").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }



    function excelModelRealisasiTunggakan() {
        var buku = $("#src-buku-realisasi-tunggakan").val();
        var thn1 = $("#tahun-pajak-realisasi-tunggakan").val();
        var thn2 = $("#tahun-pajak-realisasi-tunggakan2").val();
        var kodekec = $("#kecamatan-realisasi-tunggakan").val();
        var namakec = $("#kecamatan-realisasi-tunggakan option:selected").text();
        var tgl1 = $("#periode1-realisasi-tunggakan").val();
        var tgl2 = $("#periode2-realisasi-tunggakan").val();
        window.open("view/PBB/monitoring/svc-toexcel-realisasi-tunggakan.php?q=<?=base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}")?>&bk=" + buku + "&tgl1=" + tgl1 + "&tgl2=" + tgl2 + "&kec=" + kodekec + "&nmkec=" + namakec + "&thn1="+thn1 + "&thn2="+thn2);
    }
</script>