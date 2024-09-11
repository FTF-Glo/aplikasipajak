<?php
class SaldoPiutang
{
    public $label = 'Saldo Piutang';

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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Tahun Pajak: </label>
                                    <div class="row">
                                        <div class="col-md-5">
                                            <select name="tahun-pajak-saldo-piutang-awal" class="form-control" id="tahun-pajak-saldo-piutang-awal">';
        for ($t = $thn; $t >= 2008; $t--) {
            if ($t == $thnTagihan) {
                echo "<option value=\"$t\" selected>$t</option>";
            } else
                echo "<option value=\"$t\">$t</option>";
        }
        echo                    '</select> 
                                        </div>
                                        <div class="col-md-2" style="margin-top: 10px;">s/d</div>
                                        <div class="col-md-5">
                                            <select name="tahun-pajak-saldo-piutang-akhir" class="form-control" id="tahun-pajak-saldo-piutang-akhir">';
        for ($t = $thn; $t >= 2008; $t--) {
            if ($t == $thnTagihan) {
                echo "<option value=\"$t\" selected>$t</option>";
            } else
                echo "<option value=\"$t\">$t</option>";
        }
        echo '</select> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Kecamatan: </label>
                                    <select name="kecamatan-saldo-piutang" id="kecamatan-saldo-piutang" class="form-control"></select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">' . $this->appConfig['LABEL_KELURAHAN'] . ': </label>
                                    <select id="kelurahan-saldo-piutang" class="form-control"></select>
                                </div>
                            </div>
                        </div>
                        <div class="row mb5">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">NOP: </label><br />
                                    <div class="col-md-1" style="padding: 0">
                                        <input type="text" class="form-control nop-input-1" style="padding: 6px;" name="nops-1" id="nops-1" placeholder="PR">
                                    </div>
                                    <div class="col-md-1" style="padding: 0">
                                        <input type="text" class="form-control nop-input-2" style="padding: 6px;" name="nops-2" id="nops-2" placeholder="DTII" maxlength="2">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-input-3" style="padding: 6px;" name="nops-3" id="nops-3" placeholder="KEC" maxlength="3">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-input-4" style="padding: 6px;" name="nops-4" id="nops-4" placeholder="KEL" maxlength="3">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-input-5" style="padding: 6px;" name="nops-5" id="nops-5" placeholder="BLOK" maxlength="3">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-input-6" style="padding: 6px;" name="nops-6" id="nops-6" placeholder="NO.URUT" maxlength="4">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-input-7" style="padding: 6px;" name="nops-7" id="nops-7" placeholder="KODE" maxlength="1">
                                    </div>
                                    <!--<input type="text" id="nops" class="form-control">-->
                                </div>
                            </div>
                            <div class="col-md-2" style="margin-top: 25px">
                                <button type="button" name="button6" class="btn btn-primary btn-orange mb5" onClick="showSaldoPiutang()">Tampilkan</button>
                                <button type="button" name="buttonToExcel" class="btn btn-primary btn-blue mb5" onClick="excelSaldoPiutang()">Ekspor ke xls</button>
                            </div>
                        </div>
                        <input type="hidden" id="export_saldo-piutang"/>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-content-saldo-piutang" class="monitoring-content"></div>
                    <script>
                        $("select#kecamatan-saldo-piutang").change(function () {
                            showKelurahan(\'saldo-piutang\');
                        })

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
                                showSaldoPiutang();
                            }
                        });
                    </script>
                </div>
            </div>
        ';
    }
}


?>

<script>
    function showSaldoPiutang() {
        var tahun1 = $("#tahun-pajak-saldo-piutang-awal").val();
        var tahun2 = $("#tahun-pajak-saldo-piutang-akhir").val();
        var kecamatan = $("#kecamatan-saldo-piutang").val();
        var kelurahan = $("#kelurahan-saldo-piutang").val();
        var namakec = $("#kecamatan-saldo-piutang option:selected").text();
        var namakel = $("#kelurahan-saldo-piutang option:selected").text();
        //var nop = $("#nops").val();
        var nop1 = $("#nops-1").val();
        var nop2 = $("#nops-2").val();
        var nop3 = $("#nops-3").val();
        var nop4 = $("#nops-4").val();
        var nop5 = $("#nops-5").val();
        var nop6 = $("#nops-6").val();
        var nop7 = $("#nops-7").val();

        //console.log(nop);

        $("#monitoring-content-saldo-piutang").html("loading ...");
        $("#monitoring-content-saldo-piutang").load("view/PBB/monitoring/svc-monitoring-saldo-piutang.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); ?>", {
            th: tahun1,
            th2: tahun2,
            kc: kecamatan,
            n: namakec,
            kl: kelurahan,
            nk: namakel,
            //nop: nop,
            nop1: nop1,
            nop2: nop2,
            nop3: nop3,
            nop4: nop4,
            nop5: nop5,
            nop6: nop6,
            nop7: nop7,
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-saldo-piutang").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }

    function showSaldoPiutangPage(page) {
        var tahun1 = $("#tahun-pajak-saldo-piutang-awal").val();
        var tahun2 = $("#tahun-pajak-saldo-piutang-akhir").val();
        var kecamatan = $("#kecamatan-saldo-piutang").val();
        var kelurahan = $("#kelurahan-saldo-piutang").val();
        var namakec = $("#kecamatan-saldo-piutang option:selected").text();
        var namakel = $("#kelurahan-saldo-piutang option:selected").text();
        //var nop = $("#nops").val();
        var nop1 = $("#nops-1").val();
        var nop2 = $("#nops-2").val();
        var nop3 = $("#nops-3").val();
        var nop4 = $("#nops-4").val();
        var nop5 = $("#nops-5").val();
        var nop6 = $("#nops-6").val();
        var nop7 = $("#nops-7").val();

        $("#monitoring-content-saldo-piutang").html("loading ...");
        $("#monitoring-content-saldo-piutang").load("view/PBB/monitoring/svc-monitoring-saldo-piutang.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid','srch':'$srch'}"); 
                                                                                                            ?>", {
            th: tahun1,
            th2: tahun2,
            kc: kecamatan,
            n: namakec,
            kl: kelurahan,
            nk: namakel,
            page: page,
            //nop: nop,
            nop1: nop1,
            nop2: nop2,
            nop3: nop3,
            nop4: nop4,
            nop5: nop5,
            nop6: nop6,
            nop7: nop7,
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-saldo-piutang").html(msg + xhr.status + " " + xhr.statusText);
            }
        });
    }

    function excelSaldoPiutang() {
        var tahun1 = $("#tahun-pajak-saldo-piutang-awal").val();
        var tahun2 = $("#tahun-pajak-saldo-piutang-akhir").val();
        var kecamatan = $("#kecamatan-saldo-piutang").val();
        var kelurahan = $("#kelurahan-saldo-piutang").val();
        var namakec = $("#kecamatan-saldo-piutang option:selected").text();
        var namakel = $("#kelurahan-saldo-piutang option:selected").text();
        //var nop = $("#nops").val();
        var nop1 = $("#nops-1").val();
        var nop2 = $("#nops-2").val();
        var nop3 = $("#nops-3").val();
        var nop4 = $("#nops-4").val();
        var nop5 = $("#nops-5").val();
        var nop6 = $("#nops-6").val();
        var nop7 = $("#nops-7").val();

        window.open("view/PBB/monitoring/svc-toexcel-saldo-piutang.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid','srch':'$srch'}"); 
                                                                            ?>" + "&th=" + tahun1 + "&th2=" + tahun2 + "&kc=" + kecamatan + "&kl=" + kelurahan + "&n=" + namakec + "&nk=" + namakel + "&nop1=" + nop1 + "&nop2=" + nop2 + "&nop3=" + nop3 + "&nop4=" + nop4 + "&nop5=" + nop5 + "&nop6=" + nop6 + "&nop7=" + nop7);
    }
</script>