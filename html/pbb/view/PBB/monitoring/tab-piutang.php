<?php
class Piutang
{
    public $label = 'Piutang';

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
                    <form id="TheForm-2" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tahun Pajak: </label>
                                    <div class="row">
                                        <div class="col-md-5" style="padding-right:0">
                                            <select name="tahun-pajak-piutang-awal" id="tahun-pajak-piutang-awal" class="form-control">';
                                            echo '<option value="">Semua</option>';
                                            for ($t = $thn; $t >= 2008; $t--) {
                                                $selected = ($t == $thnTagihan) ? 'selected':'';
                                                echo "<option value=$t $selected>$t</option>";
                                            }
                                    echo '</select>
                                        </div>
                                        <div class="col-md-2" style="margin-top:5px;padding:0px;text-align:center">s/d</div>
                                        <div class="col-md-5" style="padding-left:0">
                                            <select name="tahun-pajak-piutang-akhir" id="tahun-pajak-piutang-akhir" class="form-control">';
                                            echo '<option value="">Semua</option>';
                                            for ($t = $thn; $t >= 2008; $t--) {
                                                $selected = ($t == $thnTagihan) ? 'selected':'';
                                                echo "<option value=$t $selected>$t</option>";
                                            }
                                    echo '</select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Kecamatan: </label>
                                    <select name="kecamatan-piutang" id="kecamatan-piutang" class="form-control"></select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Kelurahan: </label>
                                    <select name="kelurahan-piutang" id="kelurahan-piutang" class="form-control"></select>
                                </div>
                            </div>
                            <!-- div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Bank: </label>
                                    <select id="bank" name="bank" class="form-control">
                                        <option value="">--semua--</option>
                                        <option value="9991036">Pemda Kab. Kupang</option>
                                        <option value="5303008">Bank Mandiri</option>
                                        <option value="1300000">Bank NTT</option>
                                        <option value="5303200,2000000,1200000">Bank BTN</option>
                                    </select>
                                </div>
                            </div -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Tanggal Bayar: </label>
                                    <div class="row">
                                        <div class="col-md-5" style="padding-right:0">
                                            <input type="text" class="form-control" name="piutang-tgl-bayar-awal" id="piutang-tgl-bayar-awal" size="10" />
                                        </div>
                                        <div class="col-md-2" style="margin-top:5px;padding:0px;text-align:center">s/d</div>
                                        <div class="col-md-5" style="padding-left:0">
                                            <input type="text" class="form-control" name="piutang-tgl-bayar-akhir" id="piutang-tgl-bayar-akhir" size="10" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-bottom:10px">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">NOP: </label><br />
                                    <div class="col-md-1" style="padding: 0">
                                        <input type="text" class="form-control nop-input-1" style="padding: 6px;" name="nop-1" id="nop-1" placeholder="PR">
                                    </div>
                                    <div class="col-md-1" style="padding: 0">
                                        <input type="text" class="form-control nop-input-2" style="padding: 6px;" name="nop-2" id="nop-2" placeholder="DTII" maxlength="2">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-input-3" style="padding: 6px;" name="nop-3" id="nop-3" placeholder="KEC" maxlength="3">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-input-4" style="padding: 6px;" name="nop-4" id="nop-4" placeholder="KEL" maxlength="3">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-input-5" style="padding: 6px;" name="nop-5" id="nop-5" placeholder="BLOK" maxlength="3">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-input-6" style="padding: 6px;" name="nop-6" id="nop-6" placeholder="NO.URUT" maxlength="4">
                                    </div>
                                    <div class="col-md-1" style="padding: 0">
                                        <input type="text" class="form-control nop-input-7" style="padding: 6px;" name="nop-7" id="nop-7" placeholder="KODE" maxlength="1">
                                    </div>
                                    <!--<input type="text" name="nop" id="nop" class="form-control">-->
                                </div>
                            </div>
                            <div class="col-md-6" style="padding-top:27px;text-align:right">
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
                </div>
            </div>
            <script>
                $(".nop-input-1").on("keyup", function(){
                    var len = $(this).val().length;
                    let nopLengkap = $(this).val();
                    
                    if(!$(".nop-input-2").val()) $(".nop-input-2").val(nopLengkap.substr(2, 2));
					if(!$(".nop-input-3").val()) $(".nop-input-3").val(nopLengkap.substr(4, 3));
					if(!$(".nop-input-4").val()) $(".nop-input-4").val(nopLengkap.substr(7, 3));
					if(!$(".nop-input-5").val()) $(".nop-input-5").val(nopLengkap.substr(10, 3));
					if(!$(".nop-input-6").val()) $(".nop-input-6").val(nopLengkap.substr(13, 4));
					if(!$(".nop-input-7").val()) $(".nop-input-7").val(nopLengkap.substr(17, 1));
                    if(len > 2) $(this).val(nopLengkap.substr(0, 2));
                    if(len == 2) {
                        $(".nop-input-2").focus();
                    }
                });

                $(".nop-input-2").on("keyup", function(){
                    var len = $(this).val().length;

                    if(len == 2) {
                        $(".nop-input-3").focus();
                    }
                });

                $(".nop-input-3").on("keyup", function(){
                    var len = $(this).val().length;

                    if(len == 3) {
                        $(".nop-input-4").focus();
                    }
                });

                $(".nop-input-4").on("keyup", function(){
                    var len = $(this).val().length;

                    if(len == 3) {
                        $(".nop-input-5").focus();
                    }
                });

                $(".nop-input-5").on("keyup", function(){
                    var len = $(this).val().length;

                    if(len == 3) {
                        $(".nop-input-6").focus();
                    }
                });

                $(".nop-input-6").on("keyup", function(){
                    var len = $(this).val().length;

                    if(len == 4) {
                        $(".nop-input-7").focus();
                    }
                });

                $(".nop-input-7").on("keyup", function(){
                    var len = $(this).val().length;

                    if(len == 1) {
                        showModelPiutang();
                    }
                });
            </script>
        ';
    }
}


?>

<script>
    function showModelPiutang() {
        var tahunawal = $("#tahun-pajak-piutang-awal").val();
        var tahunakhir = $("#tahun-pajak-piutang-akhir").val();
        var kecamatan = $("#kecamatan-piutang").val();
        var kelurahan = $("#kelurahan-piutang").val();
        var namakec = $("#kecamatan-piutang option:selected").text();
        var namakel = $("#kelurahan-piutang option:selected").text();
        var tglawal = $("#piutang-tgl-bayar-awal").val();
        var tglakhir = $("#piutang-tgl-bayar-akhir").val();
        //var nop = $("#nop").val();
        var nop1 = $("#nop-1").val();
        var nop2 = $("#nop-2").val();
        var nop3 = $("#nop-3").val();
        var nop4 = $("#nop-4").val();
        var nop5 = $("#nop-5").val();
        var nop6 = $("#nop-6").val();
        var nop7 = $("#nop-7").val();
        var bank = $("#bank").val();
        var sts = 1;

        $("#monitoring-piutang").html("<div style='padding:80px'>Loading... &nbsp;<img src='image/icon/loadinfo.net.gif' width=20 hight=auto></img></div>");
        $("#monitoring-piutang").load("view/PBB/monitoring/svc-monitoring-piutang.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); 
                                                                                        ?>", {
            tahunawal: tahunawal,
            tahunakhir: tahunakhir,
            st: sts,
            kecamatan: kecamatan,
            namakec: namakec,
            kelurahan: kelurahan,
            namakel: namakel,
            //nop: nop,
            nop1: nop1,
            nop2: nop2,
            nop3: nop3,
            nop4: nop4,
            nop5: nop5,
            nop6: nop6,
            nop7: nop7,
            tglawal: tglawal,
            tglakhir: tglakhir,
            bank: bank
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-3").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }

    function excelModelPiutang() {
        var tahunawal = $("#tahun-pajak-piutang-awal").val();
        var tahunakhir = $("#tahun-pajak-piutang-akhir").val();
        var kecamatan = $("#kecamatan-piutang").val();
        var namakec = $("#kecamatan-piutang option:selected").text();
        var kelurahan = $("#kelurahan-piutang").val();
        var namakel = $("#kelurahan-piutang option:selected").text();
        var tglawal = $("#piutang-tgl-bayar-awal").val();
        var tglakhir = $("#piutang-tgl-bayar-akhir").val();
        //var nop = $("#nop").val();
        var nop1 = $("#nop-1").val();
        var nop2 = $("#nop-2").val();
        var nop3 = $("#nop-3").val();
        var nop4 = $("#nop-4").val();
        var nop5 = $("#nop-5").val();
        var nop6 = $("#nop-6").val();
        var nop7 = $("#nop-7").val();
        var bank = $("#bank").val();
        var nmbank = $("#bank  option:selected").text();
        var sts = 1;

        window.open("view/PBB/monitoring/svc-toexcel-piutang.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); 
                                                                    ?>&tahunawal=" + tahunawal + "&tahunakhir=" + tahunakhir + "&kecamatan=" + kecamatan + "&namakec=" + namakec + "&tglawal=" + tglawal + "&tglakhir=" + tglakhir + "&bank=" + bank + "&nmbank=" + nmbank + "&kelurahan=" + kelurahan + "&namakel=" + namakel + "&nop1=" + nop1 + "&nop2=" + nop2 + "&nop3=" + nop3 + "&nop4=" + nop4 + "&nop5=" + nop5 + "&nop6=" + nop6 + "&nop7=" + nop7);
    }
</script>