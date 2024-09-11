<?php
class RekapDHKP
{
    public $label = 'Rekap DHKP';

    private $appConfig;

    public function __construct($appConfig, $DBLink)
    {
        $this->appConfig     = $appConfig;
        $this->DBLink         = $DBLink;
    }

    public function printForm($a = "", $m = "", $uid = "")
    {
        $thn = date("Y");
        $thnTagihan = $this->appConfig['tahun_tagihan'];
        // print_r($getListTahun); 
        echo '
            <div class="row">
                <div class="col-md-12">
                    <form id="TheForm-6" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Tahun Pajak: </label>';
        // <select name="tahun-pajak-rekap-dhkp" id="tahun-pajak-rekap-dhkp">';
        // echo "<option value=\"\">Semua</option>";
        // for ($t = $thn; $t >= 2008; $t--) {
        // if ($t == $thnTagihan) {
        // echo "<option value=\"$t\" selected>$t</option>";
        // } else
        // echo "<option value=\"$t\">$t</option>";
        // }
        echo '<select name="tahun-pajak-rekap-dhkp" class="form-control" id="tahun-pajak-rekap-dhkp">';
        $sql = "SELECT REPLACE(table_name,'cppmod_pbb_sppt_cetak_','') as `table` 
					FROM information_schema.tables 
			        WHERE `table_name` LIKE 'cppmod_pbb_sppt_cetak%' AND table_schema = '" . $this->appConfig['ADMIN_SW_DBNAME'] . "' ORDER BY 1 DESC";
        $result = mysqli_query($this->DBLink, $sql);
        echo '<option value="' . $thnTagihan . '">' . $thnTagihan . '</option>';
        // while ($r = mysqli_fetch_array($result)) {
        //     if ($r[0] == $tahun) $selected70 = 'selected';
        //     else $selected70 = '';

        //     echo '<option value="' . $r[0] . '" $selected70>' . $r[0] . '</option>';
        // }
        for ($t = ($thn - 1); $t > 2008; $t--) {
            if ($t == $thnTagihan) {
                echo "<option value=\"$t\" selected>$t</option>";
            } else
                echo "<option value=\"$t\">$t</option>";
        }
        echo    '</select>         
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Kecamatan: </label>
                                    <select name="kecamatan-rekap-dhkp" class="form-control" id="kecamatan-rekap-dhkp"></select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">' . $this->appConfig['LABEL_KELURAHAN'] . ': </label>
                                    <select id="kelurahan-rekap-dhkp" class="form-control" onchange="cekHalamanDHKP(this)"></select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Status Penetapan: </label>
                                    <select id="sts-penetapan" class="form-control">
                                        <option value="">Semua</option>
                                        <option value="0">Masal</option>
                                        <option value="1">Susulan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Buku: </label>
                                    <select id="buku-rekap-dhkp" class="form-control" name="buku-rekap-dhkp">
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
							<div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Halaman cetak: </label>
                                    <div style="display:flex;align-items:center">
                                        <input class="form-control" type="text" id="dari-halaman-rekap-dhkp">
                                        <span style="margin: 0 5px">s/d</span>
                                        <input class="form-control" type="text" id="sampai-halaman-rekap-dhkp">
                                    </div>
                                </div>
                            </div>
							
							<div class="col-md-2">
								<div class="form-group">
									<label for="">Printer: </label>
									<div style="display:flex;align-items:center">
										<select class="form-control" name="selectedPrinterNew" id="selectedPrinterNew" style="width:150px;display:inline-block"></select>
									</div>
								</div>
							</div>
							
                            <div class="col-md-3" style="margin-top: 25px">

                                <button type="button" name="button6" id="button" class="btn btn-primary btn-orange mb5" onClick="showRekapDHKP()">Tampilkan</button>
								<button type="button" name="newPrintDHKP" id="newPrintDHKPId" class="btn btn-primary btn-blue mb5" onclick="printDHKP()">Cetak DHKP</button>
                                <button type="button" name="buttonToExcel" id="buttonToExcel" class="btn btn-primary btn-blue mb5" onClick="excelRekapDHKP()">Ekspor ke xls</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-content-rekap-dhkp" class="monitoring-content"></div>
                    <script>
                        $("select#kecamatan-rekap-dhkp").change(function () {
                            showKelurahan(\'rekap-dhkp\');
                        })
                    </script>
                </div>
            </div>
        ';
    }

    // private function getListTahun(){
    // $sql = "SELECT REPLACE(table_name,'cppmod_pbb_sppt_cetak_','') as `table` 
    // FROM information_schema.tables 
    // WHERE `table_name` LIKE 'cppmod_pbb_sppt_cetak%' AND table_schema = '".$this->appConfig['ADMIN_SW_DBNAME']."' ORDER BY 1 DESC";

    // $result = mysql_query($sql,$this->DBLink);
    // $data 	= mysqli_fetch_array($result));

    // return $data;
    // }
}


?>

<script>
    function showRekapDHKP() {
        var tahun = $("#tahun-pajak-rekap-dhkp").val();
        var kecamatan = $("#kecamatan-rekap-dhkp").val();
        var kelurahan = $("#kelurahan-rekap-dhkp").val();
        var namakec = $("#kecamatan-rekap-dhkp option:selected").text();
        var stsPenetapan = $("#sts-penetapan").val();
        var buku = $("#buku-rekap-dhkp").val();
        var sts = 1;

        if (kecamatan == "") {
            alert("Silahkan pilih kecamatan!");
        } else {
            $("#monitoring-content-rekap-dhkp").html("loading ...");
            $("#monitoring-content-rekap-dhkp").load("view/PBB/monitoring/svc-monitoring-rekap-dhkp.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid','srch':'$srch'}"); 
                                                                                                            ?>", {
                th: tahun,
                st: sts,
                kc: kecamatan,
                kl: kelurahan,
                n: namakec,
                stsPenetapan: stsPenetapan,
                buku: buku
            }, function(response, status, xhr) {
                if (status == "error") {
                    var msg = "Sorry but there was an error: ";
                    $("#monitoring-content-rekap-dhkp").html(msg + xhr.status + " " + xhr.statusText);
                }
            });
        }
    }
	
	
	function cekHalamanDHKP (kelurahan) {
        var tahun = $("#tahun-pajak-rekap-dhkp").val();
        var kecamatan = $("#kecamatan-rekap-dhkp").val();
        var kelurahan = $(kelurahan).val();
        var namakec = $("#kecamatan-rekap-dhkp option:selected").text();
        var namakel = $("#kelurahan-rekap-dhkp option:selected").text();
        var stsPenetapan = $("#sts-penetapan").val();
        var buku = $("#buku-rekap-dhkp").val();
        var sts = 1;

        var fullParams = "&th=" + tahun + "&st=" + sts + "&kc=" + kecamatan + "&kl=" + kelurahan + "&n=" + namakec + "&nn=" + namakel + "&stsPenetapan=" + stsPenetapan + "&buku=" + buku + "&cetakNew=true&cekHalaman=true";
        var fullUrl = "view/PBB/monitoring/svc-monitoring-rekap-dhkp.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); ?>&" + fullParams;
    
        $.get(fullUrl, function(page) {
            $('#dari-halaman-rekap-dhkp').val(1);
            $('#sampai-halaman-rekap-dhkp').val(page);
        })
    }
	
	
	// ALDES
    function printDHKP() {
        var tahun = $("#tahun-pajak-rekap-dhkp").val();
        var kecamatan = $("#kecamatan-rekap-dhkp").val();
        var kelurahan = $("#kelurahan-rekap-dhkp").val();
        var namakec = $("#kecamatan-rekap-dhkp option:selected").text();
        var namakel = $("#kelurahan-rekap-dhkp option:selected").text();
        var stsPenetapan = $("#sts-penetapan").val();
        var buku = $("#buku-rekap-dhkp").val();
        var sts = 1;
        var printer = $('#selectedPrinterNew').val();
        var dariHalaman = $('#dari-halaman-rekap-dhkp').val() ?? null;
        var sampaiHalaman = $('#sampai-halaman-rekap-dhkp').val() ?? null;

        if (kecamatan == "") {
            alert("Silahkan pilih kecamatan!");
            return;
        }

        if (kelurahan == "") {
            alert('Kelurahan wajib dipilih.');
            return;
        }

        if (confirm('Apakah anda ingin melihat preview ?')) {
            var fullParams = "&th=" + tahun + "&st=" + sts + "&kc=" + kecamatan + "&kl=" + kelurahan + "&n=" + namakec + "&nn=" + namakel + "&stsPenetapan=" + stsPenetapan + "&buku=" + buku + "&cetakNew=true&displayHtml=true&dariHalaman="+ dariHalaman +"&sampaiHalaman=" + sampaiHalaman;
            var fullUrl = "view/PBB/monitoring/svc-monitoring-rekap-dhkp.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); ?>&" + fullParams;

            window.open(fullUrl);
            return false;
        } else {
            if (!confirm('Apakah anda ingin lanjut cetak ?')) {
                return false;
            }
        }

        if (!printer) {
            alert('Pilih printer yang tesedia');
            return;
        }

        var url = "view/PBB/monitoring/svc-monitoring-rekap-dhkp.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); ?>";
        $.post(url, {
            th: tahun,
            st: sts,
            kc: kecamatan,
            kl: kelurahan,
            n: namakec,
            nn: namakel,
            stsPenetapan: stsPenetapan,
            buku: buku,
            cetakNew: true,
            displayHtml: false,
            dariHalaman: dariHalaman,
            sampaiHalaman: sampaiHalaman
        }, function(data) {
            if (!data) {
                alert('Tidak ada data.');
                return;
            }
            if (!qz.websocket.isActive()) {
                alert('QZ Websocket belum aktif');
                return;
            }
            var printer = $('#selectedPrinterNew').val();
            if (!printer) {
                alert('Pilih printer yang tesedia');
                return;
            } else {
                qz.printers.find(printer).then((validPrinter) => {
                    var config = qz.configs.create(validPrinter);
                    var printData = [{
                        type: 'raw',
                        format: 'command',
                        flavor: 'plain',
                        data: data
                    }];
                    qz.print(config, printData).then((e) => {
                        alert('Data sudah dikirim ke printer');
                    }).catch((e) => {
                        console.error(e);
                    });
                }).catch(function(e) {
                    alert('Printer tidak valid');
                    console.error(e);
                });
            }
        }).fail(function() {
            alert('Terjadi kesalahan, silahkan coba lagi.');
            console.error(e);
        });
        // showRekapDHKP(true);
    }

    function excelRekapDHKP() {
        var tahun = $("#tahun-pajak-rekap-dhkp").val();
        var kecamatan = $("#kecamatan-rekap-dhkp").val();
        var kelurahan = $("#kelurahan-rekap-dhkp").val();
        var namakec = $("#kecamatan-rekap-dhkp option:selected").text();
        var namakel = $("#kelurahan-rekap-dhkp option:selected").text();
        var stsPenetapan = $("#sts-penetapan").val();
        var buku = $("#buku-rekap-dhkp").val();
        var sts = 1;

        if (kecamatan == "") {
            alert("Silahkan pilih kecamatan!");
        } else {
            window.open("view/PBB/monitoring/svc-toexcel-rekap-dhkp.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid','srch':'$srch'}"); 
                                                                            ?>&nkel=" + namakel + "&nkec=" + namakec + "&kc=" + kecamatan + "&kl=" + kelurahan + "&st=" + sts + "&th=" + tahun + "&stsPenetapan=" + stsPenetapan + "&buku=" + buku);
        }
    }
	
	// ALDES
    function initQZ() {
        if (qz.websocket.isActive()) {
            findPrinters();
            return;
        }

        return qz.websocket.connect().then(function() {
            findPrinters();
        }).catch((e) => {
            showDialog('Error', 'Software QZ belum aktif atau belum terinstal, <a href="https://qz.io/download/" target="_blank">Download</a>', 'error', false, false);
            console.error(e);
        });
    }

    // ALDES
    function findPrinters() {
        qz.printers.find().then(function(data) {
            var list = '<option value="" disabled selected>Pilih printer</option>';
            for (var i = 0; i < data.length; i++) {
                list += "<option value=\"" + data[i] + "\">" + data[i] + "</option>";
            }
            $('#selectedPrinterNew').html(list);
            console.log(data);
        }).catch(function(e) {
            console.error(e);
        });
    }
</script>