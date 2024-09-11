<?php
class StatusBayar
{
    public $sudahBayarLabel = 'Sudah Bayar';
    public $belumBayarLabel = 'Belum Bayar';

    private $appConfig;

    public function __construct($appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function printFromSudahBayar($a, $m, $uid)
    {
        $thn = date("Y");
        $thnTagihan     = $this->appConfig['tahun_tagihan'];
        $lblKelurahan     = $this->appConfig['LABEL_KELURAHAN'];
        echo '
            <div class="row">
                <div class="col-md-12">
                    <form id="TheForm-1" method="post" action="view/PBB/monitoring_pembayaran_kolektif/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">

                            <div class="form-group col-md-3">
                                    <label for="">Tahun Pajak: </label>
                                    <select name="tahun-pajak-1" class="form-control" id="tahun-pajak-1">
                                        <option value="">Semua</option>';
                                        for ($t = $thn; $t > 1993; $t--) {
                                            if ($t == $thnTagihan) {
                                                echo "<option value=\"$t\" selected>$t</option>";
                                            } else
                                                echo "<option value=\"$t\">$t</option>";
                                        }
                                echo   '</select>               
                              
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">NOP </label><br />
                                    <div class="col-md-1" style="padding: 0">
                                        <input type="text" class="form-control nop-input-1" style="padding: 6px;" name="nop-1-1" id="nop-1-1" placeholder="PR">
                                    </div>
                                    <div class="col-md-1" style="padding: 0">
                                        <input type="text" class="form-control nop-input-2" style="padding: 6px;" name="nop-1-2" id="nop-1-2" placeholder="DTII" maxlength="2">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-input-3" style="padding: 6px;" name="nop-1-3" id="nop-1-3" placeholder="KEC" maxlength="3">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-input-4" style="padding: 6px;" name="nop-1-4" id="nop-1-4" placeholder="KEL" maxlength="3">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-input-5" style="padding: 6px;" name="nop-1-5" id="nop-1-5" placeholder="BLOK" maxlength="3">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-input-6" style="padding: 6px;" name="nop-1-6" id="nop-1-6" placeholder="NO.URUT" maxlength="4">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-input-7" style="padding: 6px;" name="nop-1-7" id="nop-1-7" placeholder="KODE" maxlength="1">
                                    </div>
                                    <!--<input type="text" class="form-control" name="nop-1" id="nop-1" />-->
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Nama Wajib Pajak: </label>
                                    <input type="text" name="wp-name" class="form-control" id="wp-name-1" />
                                </div>
                            </div>
                           
                       
                            <div class="form-group col-md-3" >
                            <div class="form-group">
                                <label for="">Tgl Pembayaran: </label>
                                <div class="row">
                                    <div class="col-md-5" style="padding-right:0">
                                    <input type="text" class="form-control" name="jatuh-tempo" id="jatuh-tempo1-1" />
                                    </div>
                                    <div class="col-md-2" style="text-align:center;margin-top:5px;padding-left:0;padding-right:0">
                                        <label>s/d</label>
                                    </div>
                                    <div class="col-md-5" style="padding-left:0">
                                    <input type="text" class="form-control" name="jatuh-tempo2" id="jatuh-tempo2-1" />
                                    </div>
                                </div>
                            </div>
                        </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Kecamatan: </label>
                                    <select id="kecamatan-1" class="form-control"></select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">' . $lblKelurahan . ': </label>
                                    <select id="kelurahan-1" class="form-control"></select>
                                    <!-- <td>Nilai Tagihan <select id="src-tagihan-1" name="src-tagihan-1">
                                            <option value="0" >--semua--</option>
                                            <option value="1" >0 s/d <5jt</option>
                                            <option value="2" >5jt s/d <10jt</option>
                                            <option value="3" >10jt s/d <20jt</option>
                                            <option value="4" >20jt s/d <30jt</option>
                                            <option value="5" >30jt s/d <40jt</option>
                                            <option value="6" >40jt s/d <50jt</option>
                                            <option value="7" >50jt s/d <100jt</option>
                                            <option value="8" >>=100jt</option>
                                            <option value="9" >>100jt</option>
                                        </select></td> -->
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Buku: </label>
                                    <select id="src-buku-1" class="form-control" name="src-buku-1">
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
                        </div>
                        <div class="row">
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Kode Bayar Kolektif: </label>
                                    <input type="text" class="form-control" name="kd_kolektif" id="kd_kolektif"size="10" />
                                </div>
                            </div>
                            <div class="form-group col-md-12" > 
                                <button type="button" name="button2" class="btn btn-primary btn-orange mb5" onClick="onSubmit(1)">Tampilkan</button>
                                <button type="button" name="buttonToExcel" class="btn btn-primary btn-blue mb5" onClick="toExcel(1)">Ekspor ke xls</button>
                                <span id="loadlink1" style="font-size: 10px; display: none;">Loading...</span>
                            </div>
                           
                        </div>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-content-1" class="monitoring-content">
                    </div>
                    <script>
                        $("select#kecamatan-1").change(function () {
                            showKelurahan(\'1\');
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
                                onSubmit(1)
                            }
                        });
                    </script>
                </div>
            </div>
        ';
    }

    public function printFormBelumBayar()
    {
        $thn = date("Y");
        $thnTagihan = $this->appConfig['tahun_tagihan'];
        $lblKelurahan     = $this->appConfig['LABEL_KELURAHAN'];
        echo '
            <fieldset>
                        <form id="TheForm-2" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                            <table width="1260" border="0" cellspacing="0" cellpadding="2">
                                <tr>
                                    <td width="115">Tahun Pajak </td>
                                    <td width="3">:</td>
                                    <td colspan="2">
                                        <select name="tahun-pajak-2" id="tahun-pajak-2">';
        echo "<option value=\"\">Semua</option>";
        for ($t = $thn; $t > 1993; $t--) {
            if ($t == $thnTagihan) {
                echo "<option value=\"$t\" selected>$t</option>";
            } else
                echo "<option value=\"$t\">$t</option>";
        }
        echo                            '</select>               
                                    </td>
                                    <td width="60">&nbsp;</td>
                                    <td width="19">&nbsp;</td>
                                    <td width="73">NOP </td>
                                    <td width="3">:</td>
                                    <td><input type="text" name="nop" id="nop-2" /></td>
                                    <td>&nbsp;</td>
                                    <td>Nama&nbsp;Wajib&nbsp;Pajak</td>
                                    <td>:</td>
                                    <td width="144"><input type="text" name="wp-name-2" id="wp-name-2" /></td>
                                    <td width="180">
                                        <input type="button" name="button2" id="button2" value="Tampilkan" onClick="onSubmit(2)"/>
                                        <input type="button" name="buttonToExcel" id="buttonToExcel" value="Ekspor ke xls" onClick="toExcel(2)"/>
                                        <span id="loadlink2" style="font-size: 10px; display: none;">Loading...</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Tgl&nbspPembayaran</td>
                                    <td>:</td>
                                    <td width="60"><input type="text" name="jatuh-tempo-2" id="jatuh-tempo1-2" size="10" /></td>
                                    <td width="22">s/d </td>
                                    <td><input type="text" name="jatuh-tempo2" id="jatuh-tempo2-2" size="10" /></td>
                                    <td>&nbsp;</td>
                                    <td>Kecamatan </td>
                                    <td>:</td>
                                    <td width="144"><select id="kecamatan-2"></select></td>
                                    <td width="8">&nbsp;</td>
                                    <td width="117">' . $lblKelurahan . '</td>
                                    <td width="3">:</td>
                                    <td><select id="kelurahan-2"></select></td>
                                    <!-- <td>Nilai Tagihan <select id="src-tagihan-2" name="src-tagihan-2">
                                            <option value="0" >--semua--</option>
                                            <option value="1" >0 s/d <5jt</option>
                                            <option value="2" >5jt s/d <10jt</option>
                                            <option value="3" >10jt s/d <20jt</option>
                                            <option value="4" >20jt s/d <30jt</option>
                                            <option value="5" >30jt s/d <40jt</option>
                                            <option value="6" >40jt s/d <50jt</option>
                                            <option value="7" >50jt s/d <100jt</option>
                                            <option value="8" >>=100jt</option>
                                            <option value="9" >>100jt</option>
                                        </select></td> -->
									<td width="40">Buku : 
                                    <select id="src-buku-2" name="src-buku-2">
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
                                        </select></td>
                                </tr>
                            </table> 
                        </form>
                    </fieldset>
                    <div id="monitoring-content-2" class="monitoring-content">
                    </div>
                    <script>
                        $("select#kecamatan-2").change(function () {
                            showKelurahan(\'2\');
                        })
                    </script>
        ';
    }
}


?>

<script>
    function onSubmit(sts) {
        // alert();
        var tempo1 = $("#jatuh-tempo1-" + sts).val();
        var tempo2 = $("#jatuh-tempo2-" + sts).val();
        var tahun = $("#tahun-pajak-" + sts).val();
        //var nop = $("#nop-" + sts).val();
        var nop1 = $("#nop-" + sts + "-1").val();
        var nop2 = $("#nop-" + sts + "-2").val();
        var nop3 = $("#nop-" + sts + "-3").val();
        var nop4 = $("#nop-" + sts + "-4").val();
        var nop5 = $("#nop-" + sts + "-5").val();
        var nop6 = $("#nop-" + sts + "-6").val();
        var nop7 = $("#nop-" + sts + "-7").val();
        var nama = $("#wp-name-" + sts).val();
        var jmlBaris = $("#jml-baris").val();
        var kc = $("#kecamatan-" + sts).val();
        var kl = $("#kelurahan-" + sts).val();
        var tagihan = $("#src-tagihan-" + sts).val();
        var buku = $("#src-buku-" + sts).val();
        var bank = $("#bank-" + sts).val();
        var kolektif = $("#kd_kolektif").val();
        $("#monitoring-content-" + sts).html("loading ...");
        var svc = "";
        $("#monitoring-content-" + sts).load("view/PBB/monitoring_pembayaran_kolektif/svc-monitoring.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>", {
            kolektif: kolektif,
            na: nama,
            t1: tempo1,
            t2: tempo2,
            th: tahun,
            //n: nop,
            n1: nop1,
            n2: nop2,
            n3: nop3,
            n4: nop4,
            n5: nop5,
            n6: nop6,
            n7: nop7,
            st: sts,
            kc: kc,
            kl: kl,
            tagihan: tagihan,
            buku: buku,
            bank: bank,
            GW_DBHOST: GW_DBHOST,
            GW_DBNAME: GW_DBNAME,
            GW_DBUSER: GW_DBUSER,
            GW_DBPWD: GW_DBPWD,
            GW_DBPORT: GW_DBPORT,
            LBL_KEL: LBL_KEL
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
            }
        });

    }

    function toExcel(sts) {
        var nmfileAll = '<?php echo date('yymdhmi'); ?>';
        var nmfile = nmfileAll + '-part-';
        var tempo1 = $("#jatuh-tempo1-" + sts).val();
        var tempo2 = $("#jatuh-tempo2-" + sts).val();
        var tahun = $("#tahun-pajak-" + sts).val();
        //var nop = $("#nop-" + sts).val();
        var nop1 = $("#nop-" + sts + "-1").val();
        var nop2 = $("#nop-" + sts + "-2").val();
        var nop3 = $("#nop-" + sts + "-3").val();
        var nop4 = $("#nop-" + sts + "-4").val();
        var nop5 = $("#nop-" + sts + "-5").val();
        var nop6 = $("#nop-" + sts + "-6").val();
        var nop7 = $("#nop-" + sts + "-7").val();
        var nama = $("#wp-name-" + sts).val();
        var jmlBaris = $("#jml-baris").val();
        var kc = $("#kecamatan-" + sts).val();
        var kl = $("#kelurahan-" + sts).val();
        var tagihan = $("#src-tagihan-" + sts).val();
        var buku = $("#src-buku-" + sts).val()
        var bank = $("#bank-" + sts).val();
        var kolektif = $("#kd_kolektif").val();

        // alert(kolektif)
        if (sts == 1)
            $("#loadlink1").show();
        else
            $("#loadlink2").show();

        $.ajax({
            type: "POST",
            url: "./view/PBB/monitoring_pembayaran_kolektif/svc-countforlink.php",
            data: "q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&buku=" + buku + "&bank=" + bank + "&kd_kolektif=" + kolektif + "&na=" + nama + "&t1=" + tempo1 + "&t2=" + tempo2 + "&th=" + tahun + "&n=" + nop + "&st=" + sts + "&kc=" + kc + "&kl=" + kl + "&tagihan=" + tagihan + "&buku=" + buku + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT + "&LBL_KEL=" + LBL_KEL,
            success: function(msg) {
                // alert(msg);
                var sumOfPage = Math.ceil(msg / 10000);
                // alert(sumOfPage);
                var strOfLink = "";
                if (msg < 10000)
                    strOfLink += '<a target="_blank" href="view/PBB/monitoring_pembayaran_kolektif/svc-toexcel.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&kd_kolektif=' + kolektif + '&na=' + nama + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th=' + tahun + '&n1=' + nop1 + '&n2=' + nop2 + '&n3=' + nop3 + '&n4=' + nop4 + '&n5=' + nop5 + '&n6=' + nop6 + '&n7=' + nop7 + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + '&tagihan=' + tagihan + "&buku=" + buku + '&bank=' + bank + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT + "&LBL_KEL=" + LBL_KEL + '&p=all&total=' + msg + '">' + nmfileAll + '</a><br/>';
                else {
                    for (var page = 1; page <= sumOfPage; page++) {
                        strOfLink += '<a target="_blank" href="view/PBB/monitoring_pembayaran_kolektif/svc-toexcel.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&kd_kolektif=' + kolektif + '&na=' + nama + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th=' + tahun + '&n1=' + nop1 + '&n2=' + nop2 + '&n3=' + nop3 + '&n4=' + nop4 + '&n5=' + nop5 + '&n6=' + nop6 + '&n7=' + nop7 + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + '&tagihan=' + tagihan + "&buku=" + buku + '&bank=' + bank + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT + "&LBL_KEL=" + LBL_KEL + '&p=' + page + '">' + nmfile + page + '</a><br/>';
                    }
                }
                $("#contentLink").html(strOfLink);
                $("#cBox").css("display", "block");

                if (sts == 1)
                    $("#loadlink1").hide();
                else
                    $("#loadlink2").hide();
            }
        });
    }
</script>