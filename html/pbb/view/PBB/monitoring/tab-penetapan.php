<?php
class Penetapan
{
    public $label = 'Penetapan';

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
                    <form method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Tahun Pajak: </label>
                                    <select name="tahun-pajak-penetapan" id="tahun-pajak-penetapan" class="form-control">';
        // echo "<option value=\"\">Semua</option>";
        for ($t = $thn; $t >= 2008; $t--) {
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
                                    <label for="">Kecamatan: </label>
                                    <select name="kecamatan-penetapan" id="kecamatan-penetapan" class="form-control"></select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">' . $this->appConfig['LABEL_KELURAHAN'] . ': </label>
                                    <select id="kelurahan-penetapan" class="form-control"></select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Buku: </label>
                                    <select id="src-buku-penetapan" class="form-control" name="src-buku-penetapan">
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
                                <button type="button" name="button6" class="btn btn-primary btn-orange mb5" onClick="showPenetapan()">Tampilkan</button>
                                <button type="button" name="buttonToExcel" class="btn btn-primary btn-blue mb5" onClick="excelPenetapan()">Ekspor ke xls</button>
                            </div>
                        </div>
                        <input type="hidden" id="export_penetapan"/>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-content-penetapan" class="monitoring-content"></div>
                    <script>
                        $("select#kecamatan-penetapan").change(function () {
                            showKelurahan(\'penetapan\');
                        })
                    </script>
                </div>
            </div>
        ';
    }
}


?>

<script>
    function showPenetapan() {
        var tahun = $("#tahun-pajak-penetapan").val();
        var kecamatan = $("#kecamatan-penetapan").val();
        var kelurahan = $("#kelurahan-penetapan").val();
        var namakec = $("#kecamatan-penetapan option:selected").text();
        var namakel = $("#kelurahan-penetapan option:selected").text();

        var buku = $("#src-buku-penetapan").val();

        // alert(buku);

        $("#monitoring-content-penetapan").html("loading ...");
        $("#monitoring-content-penetapan").load("view/PBB/monitoring/svc-monitoring-penetapan.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid','srch':'$srch'}"); 
                                                                                                    ?>", {
            th: tahun,
            kc: kecamatan,
            n: namakec,
            kl: kelurahan,
            nk: namakel,
            bk: buku
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-penetapan").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }

    function showPenetapanPage(page) {
        var tahun = $("#tahun-pajak-penetapan").val();
        var kecamatan = $("#kecamatan-penetapan").val();
        var kelurahan = $("#kelurahan-penetapan").val();
        var namakec = $("#kecamatan-penetapan option:selected").text();
        var namakel = $("#kelurahan-penetapan option:selected").text();

        var buku = $("#src-buku-penetapan").val();

        $("#monitoring-content-penetapan").html("loading ...");
        $("#monitoring-content-penetapan").load("view/PBB/monitoring/svc-monitoring-penetapan.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid','srch':'$srch'}"); 
                                                                                                    ?>", {
            th: tahun,
            kc: kecamatan,
            n: namakec,
            kl: kelurahan,
            nk: namakel,
            page: page,
            bk: buku
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-penetapan").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }

    function excelPenetapan() {
        var tahun = $("#tahun-pajak-penetapan").val();
        var kecamatan = $("#kecamatan-penetapan").val();
        var kelurahan = $("#kelurahan-penetapan").val();
        var namakec = $("#kecamatan-penetapan option:selected").text();
        var namakel = $("#kelurahan-penetapan option:selected").text();
        var buku = $("#src-buku-penetapan").val();

        window.open("view/PBB/monitoring/svc-toexcel-penetapan.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid','srch':'$srch'}"); 
                                                                        ?>" + "&th=" + tahun + "&kc=" + kecamatan + "&kl=" + kelurahan + "&n=" + namakec + "&nk=" + namakel + "&bk=" + buku);
    }
</script>