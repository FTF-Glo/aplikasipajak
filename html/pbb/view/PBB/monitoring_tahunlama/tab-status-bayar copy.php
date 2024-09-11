<?php
// <option value="0" >Pilih Semua</option>
// <option value="1" >Buku 1</option>
// <option value="12" >Buku 1,2</option>
// <option value="123" >Buku 1,2,3</option>
// <option value="1234" >Buku 1,2,3,4</option>
// <option value="12345" >Buku 1,2,3,4,5</option>
// <option value="2" >Buku 2</option>
// <option value="23" >Buku 2,3</option>
// <option value="234" >Buku 2,3,4</option>
// <option value="2345" >Buku 2,3,4,5</option>
// <option value="3" >Buku 3</option>
// <option value="34" >Buku 3,4</option>
// <option value="345" >Buku 3,4,5</option>
// <option value="4" >Buku 4</option>
// <option value="45" >Buku 4,5</option>
// <option value="5" >Buku 5</option>
class StatusBayar
{
    public $sudahBayarLabel = 'Sudah Bayar';
	public $belumBayarLabel = 'Belum Bayar 2009 - 2015';
	public $pembatalanLabel = 'Pembatalan';
    //public $belumBayarLabel = 'Belum Bayar';

    private $appConfig;
    private $idRole;
    private $dtUser;

    public function __construct($appConfig, $idRole, $dtUser)
    {
        $this->appConfig     = $appConfig;
        $this->idRole         = $idRole;
        $this->dtUser         = $dtUser;
    }

    public function printFromSudahBayar($a, $m, $uid)
    {
        $thn = date("Y");
        $thnTagihan     = $this->appConfig['tahun_tagihan'];
        $lblKelurahan     = $this->appConfig['LABEL_KELURAHAN'];
        $filterWilayah  = "";
        // echo $this->idRole;exit;
        // var_dump($this->dtUser);exit;
        if ($this->idRole == "rmKelurahan") {
            // $filterWilayah = '<td>'.$lblKelurahan.'</td><td><select id="kelurahan-1"></select></td><td>RW</td><td><select id="rw-1"></select></td>';
            // $filterWilayah = '<td>'.$lblKelurahan.'</td><td><select id="kelurahan-1"></select></td>';
            $filterWilayah = '<div class="col-md-2"><div class="form-group"><label for="">Kecamatan</label><select id="kecamatan-1" class="form-control"><option value="' . $this->dtUser['kecamatan'] . '">' . $this->dtUser['CPC_TKC_KECAMATAN'] . '</option></select></div></div><div class="col-md-2"><div class="form-group"><label for="">' . $lblKelurahan . '</label><select id="kelurahan-1" class="form-control"></select></div></div>';
        } else if ($this->idRole == "rmKecamatan") {
            $filterWilayah = '<div class="col-md-2"><div class="form-group"><label for="">Kecamatan</label><select id="kecamatan-1" class="form-control"><option value="' . $this->dtUser['kecamatan'] . '">' . $this->dtUser['CPC_TKC_KECAMATAN'] . '</option></select></div></div><div class="col-md-2"><div class="form-group"><label for="">' . $lblKelurahan . '</label><select id="kelurahan-1" class="form-control"></select></div></div>';
        } else {
            $filterWilayah = '<div class="col-md-2"><div class="form-group"><label for="">Kecamatan</label><select id="kecamatan-1" class="form-control"></select></div></div><div class="col-md-2"><div class="form-group"><label for="">' . $lblKelurahan . '</label><select id="kelurahan-1" class="form-control"></select></div></div>';
        }

        echo '
            <div class="row">
                <div class="col-md-12">
                    <form id="TheForm-1" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="">Tahun Pajak: </label>
                                    <select name="tahun-pajak-1" id="tahun-pajak-1" class="form-control">
                                        <option value="">Semua</option>';
        for ($t = $thn; $t > 1993; $t--) {
            if ($t == $thnTagihan) {
                echo "<option value=\"$t\" selected>$t</option>";
            } else
                echo "<option value=\"$t\">$t</option>";
        }
        echo '                      
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="">NOP: </label><br />
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
                                    <!--<input type="text" size="30" class="form-control" name="nop-1" id="nop-1" />-->
                                </div>
                            </div>
							<div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Nama WP: </label>
                                    <input type="text" name="wp-name-1" id="wp-name-1" class="form-control" />
                                </div>
                            </div>
							<div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Alamat OP</label>
                                    <input type="text" name="alamatOP-1" id="alamatOP-1" class="form-control" />
                                </div>
                            </div> 
						</div>
                        <div class="row">
                            ' . $filterWilayah . '
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Tgl Pembayaran </label>
                                    <div class="row">
                                        <div class="col-md-5">
                                            <input type="text" name="jatuh-tempo" class="form-control" id="jatuh-tempo1-1" size="10" />
                                        </div>
                                        <div class="col-md-2" style="margin-top: 10px;">s/d</div>
                                        <div class="col-md-5">
                                            <input type="text" name="jatuh-tempo2" class="form-control" id="jatuh-tempo2-1" size="10" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Buku</label>
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
                            <div class="col-md-2" style="margin-top: 25px">
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
						showKelurahan(\'1\');
						showRW(\'1\');
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

    public function printFormBelumBayar($a, $m, $uid)
    {
        $thn = date("Y");
        $thnTagihan = $this->appConfig['tahun_tagihan'];
        $lblKelurahan     = $this->appConfig['LABEL_KELURAHAN'];
        $filterWilayah  = "";
        if ($this->idRole == "rmKelurahan") {
            // $filterWilayah = '<td>'.$lblKelurahan.'</td><td><select id="kelurahan-2"></select></td><td>RW</td><td><select id="rw-2"></select></td>';
            $filterWilayah = '<div class="col-md-3"><div class="form-group">' . $lblKelurahan . '<select id="kelurahan-2" class="form-control"></select></div></div>';
        } else if ($this->idRole == "rmKecamatan") {
            $filterWilayah = '<div class="col-md-3"><div class="form-group"><label for="">Kecamatan</label><select id="kecamatan-2" class="form-control"><option value="' . $this->dtUser['kecamatan'] . '">' . $this->dtUser['CPC_TKC_KECAMATAN'] . '</option></select></div></div><div class="col-md-3"><div class="form-group">' . $lblKelurahan . '<select id="kelurahan-2" class="form-control"></select></div></div>';
        } else {
            $filterWilayah = '<div class="col-md-3"><div class="form-group"><label for="">Kecamatan</label><select id="kecamatan-2" class="form-control"></select></div></div><div class="col-md-3"><div class="form-group"><label for="">' . $lblKelurahan . '</label><select id="kelurahan-2" class="form-control"></select></div></div>';
        }
        echo '
            <div class="row">
                <div class="col-md-12">
                    <form id="TheForm-2" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">

                        <p style="margin-bottom:20px; display:flex; align-items:center;justify-content:end;">
                            <button class="btn btn-primary" style="border-radius:8px;" type="button" data-toggle="collapse" data-target="#collapsepenghapusan" aria-expanded="false" aria-controls="collapsepenghapusan">
                            Filter Data
                            </button>
                        </p>

                        <div class="collapse" id="collapsepenghapusan">
                            <div class="card card-body">
                                <div class="row" style="margin-left:5px; margin-top:10px">
                            
                                    <div class="form-group col-md-3">
                                    <label for="">Tahun Pajak: </label>
                                    <select name="tahun-pajak-2" class="form-control" id="tahun-pajak-2">
                                        <option value="">Semua</option>';
                                        for ($t = $thn; $t > 2008; $t--) {
                                            if ($t == $thnTagihan) {
                                                echo "<option value=\"$t\" selected>$t</option>";
                                            } else
                                                echo "<option value=\"$t\">$t</option>";
                                        }
                                        echo '                                    
                                    </select>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>

                                <div class="col-md-1">
                                <div class="form-group">
                                    <label for="">Tahun Pajak: </label>
                                    <select name="tahun-pajak-2" class="form-control" id="tahun-pajak-2">
                                        <option value="">Semua</option>';
                                        for ($t = $thn; $t > 2008; $t--) {
                                            if ($t == $thnTagihan) {
                                                echo "<option value=\"$t\" selected>$t</option>";
                                            } else
                                                echo "<option value=\"$t\">$t</option>";
                                        }
                                        echo '                                    
                                    </select>
                                </div>
                            </div>
							<div class="col-md-5">
                                <div class="form-group">
                                    <label for="">NOP: </label><br />
                                    <div class="col-md-1" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-1" style="padding: 6px;" name="nop-2-1" id="nop-2-1" placeholder="PR">
                                    </div>
                                    <div class="col-md-1" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-2" style="padding: 6px;" name="nop-2-2" id="nop-2-2" placeholder="DTII">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-3" style="padding: 6px;" name="nop-2-3" id="nop-2-3" placeholder="KEC">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-4" style="padding: 6px;" name="nop-2-4" id="nop-2-4" placeholder="KEL">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-5" style="padding: 6px;" name="nop-2-5" id="nop-2-5" placeholder="BLOK">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-6" style="padding: 6px;" name="nop-2-6" id="nop-2-6" placeholder="NO.URUT">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-7" style="padding: 6px;" name="nop-2-7" id="nop-2-7" placeholder="KODE">
                                    </div>
                                    <!--<input type="text" class="form-control" size="30" name="nop-2" id="nop-2" />-->
                                </div>
                            </div> 
							<div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Nama WP: </label>
                                    <input type="text" class="form-control" name="wp-name" id="wp-name-2" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Alamat OP</label>
                                    <input type="text" class="form-control" name="alamatOP-2" id="alamatOP-2" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            ' . $filterWilayah . '
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Buku </label>
                                    <select id="src-buku-2" class="form-control" name="src-buku-2">
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
                                <button type="button" name="button2" class="btn btn-primary btn-orange mb5" onClick="onSubmit(2)">Tampilkan</button>
                                <button type="button" name="buttonToExcel" class="btn btn-primary btn-blue mb5" onClick="toExcel(2)">Ekspor ke xls</button>
                                <span id="loadlink1" style="font-size: 10px; display: none;">Loading...</span>
                            </div>
						</div>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="monitoring-content-2" class="monitoring-content">
                    </div>
                    <script>
						showKelurahan(\'2\');
						showRW(\'2\');
                        $("select#kecamatan-2").change(function () {
                            showKelurahan(\'2\');
                        })

                        $(".nop-inputs-1").on("keyup", function(){
                            var len = $(this).val().length;

                            if(len == 2) {
                                $(".nop-inputs-2").focus();
                            }
                        });

                        $(".nop-inputs-2").on("keyup", function(){
                            var len = $(this).val().length;

                            if(len == 2) {
                                $(".nop-inputs-3").focus();
                            }
                        });

                        $(".nop-inputs-3").on("keyup", function(){
                            var len = $(this).val().length;

                            if(len == 3) {
                                $(".nop-inputs-4").focus();
                            }
                        });

                        $(".nop-inputs-4").on("keyup", function(){
                            var len = $(this).val().length;

                            if(len == 3) {
                                $(".nop-inputs-5").focus();
                            }
                        });

                        $(".nop-inputs-5").on("keyup", function(){
                            var len = $(this).val().length;

                            if(len == 3) {
                                $(".nop-inputs-6").focus();
                            }
                        });

                        $(".nop-inputs-6").on("keyup", function(){
                            var len = $(this).val().length;

                            if(len == 4) {
                                $(".nop-inputs-7").focus();
                            }
                        });

                        $(".nop-inputs-7").on("keyup", function(){
                            var len = $(this).val().length;

                            if(len == 1) {
                                onSubmit(2)
                            }
                        });
                    </script>
                </div>
            </div>
        ';
    }
	
	
	 public function printFormPembatalan($a, $m, $uid)
    {
		$thn = date("Y");
        $thnTagihan = $this->appConfig['tahun_tagihan'];
        $lblKelurahan     = $this->appConfig['LABEL_KELURAHAN'];
        $filterWilayah  = "";
        if ($this->idRole == "rmKelurahan") {
            // $filterWilayah = '<td>'.$lblKelurahan.'</td><td><select id="kelurahan-1"></select></td><td>RW</td><td><select id="rw-1"></select></td>';
            $filterWilayah = '<div class="col-md-3"><div class="form-group">' . $lblKelurahan . '<select id="kelurahan-1" class="form-control"></select></div></div>';
        } else if ($this->idRole == "rmKecamatan") {
            $filterWilayah = '<div class="col-md-3"><div class="form-group"><label for="">Kecamatan</label><select id="kecamatan-1" class="form-control"><option value="' . $this->dtUser['kecamatan'] . '">' . $this->dtUser['CPC_TKC_KECAMATAN'] . '</option></select></div></div><div class="col-md-3"><div class="form-group">' . $lblKelurahan . '<select id="kelurahan-1" class="form-control"></select></div></div>';
        } else {
            $filterWilayah = '<div class="col-md-3"><div class="form-group"><label for="">Kecamatan</label><select id="kecamatan-1" class="form-control"></select></div></div><div class="col-md-3"><div class="form-group"><label for="">' . $lblKelurahan . '</label><select id="kelurahan-1" class="form-control"></select></div></div>';
        }
        echo '
            <div class="row">
                <div class="col-md-12">
                    <form id="TheForm-1" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                        <div class="row">
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="">Tahun Pajak: </label>
                                    <select name="tahun-pajak-1" class="form-control" id="tahun-pajak-1">
                                        <option value="">Semua</option>';
        for ($t = $thn; $t > 2008; $t--) {
            if ($t == $thnTagihan) {
                echo "<option value=\"$t\" selected>$t</option>";
            } else
                echo "<option value=\"$t\">$t</option>";
        }
        echo '                                    
                                    </select>
                                </div>
                            </div>
							<div class="col-md-5">
                                <div class="form-group">
                                    <label for="">NOP: </label><br />
                                    <div class="col-md-1" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-1" style="padding: 6px;" name="nop-1-1" id="nop-1-1" placeholder="PR">
                                    </div>
                                    <div class="col-md-1" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-2" style="padding: 6px;" name="nop-1-2" id="nop-1-2" placeholder="DTII">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-3" style="padding: 6px;" name="nop-1-3" id="nop-1-3" placeholder="KEC">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-4" style="padding: 6px;" name="nop-1-4" id="nop-1-4" placeholder="KEL">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-5" style="padding: 6px;" name="nop-1-5" id="nop-1-5" placeholder="BLOK">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-6" style="padding: 6px;" name="nop-1-6" id="nop-1-6" placeholder="NO.URUT">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-7" style="padding: 6px;" name="nop-1-7" id="nop-1-7" placeholder="KODE">
                                    </div>
                                    <!--<input type="text" class="form-control" size="30" name="nop-1" id="nop-1" />-->
                                </div>
                            </div> 
							<div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Nama WP: </label>
                                    <input type="text" class="form-control" name="wp-name" id="wp-name-1" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Alamat OP</label>
                                    <input type="text" class="form-control" name="alamatOP-1" id="alamatOP-1" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            ' . $filterWilayah . '
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Buku </label>
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
							<div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Nomor SK</label>
                                    <input type="text" class="form-control" name="nosk-1" id="nosk-1" />
                                </div>
                            </div>
                            <div class="col-md-1" style="margin-top: 25px">
                                <button type="button" name="button2" class="btn btn-primary btn-orange mb5" onClick="onSubmit(1)">Tampilkan</button>
                                <!-- <button type="button" name="buttonToExcel" class="btn btn-primary btn-blue mb5" onClick="toExcel(1)">Ekspor ke xls</button>-->
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
						showKelurahan(\'1\');
						showRW(\'1\');
                        $("select#kecamatan-1").change(function () {
                            showKelurahan(\'1\');
                        })

                        $(".nop-inputs-1").on("keyup", function(){
                            var len = $(this).val().length;

                            if(len == 2) {
                                $(".nop-inputs-1").focus();
                            }
                        });

                        $(".nop-inputs-1").on("keyup", function(){
                            var len = $(this).val().length;

                            if(len == 2) {
                                $(".nop-inputs-3").focus();
                            }
                        });

                        $(".nop-inputs-3").on("keyup", function(){
                            var len = $(this).val().length;

                            if(len == 3) {
                                $(".nop-inputs-4").focus();
                            }
                        });

                        $(".nop-inputs-4").on("keyup", function(){
                            var len = $(this).val().length;

                            if(len == 3) {
                                $(".nop-inputs-5").focus();
                            }
                        });

                        $(".nop-inputs-5").on("keyup", function(){
                            var len = $(this).val().length;

                            if(len == 3) {
                                $(".nop-inputs-6").focus();
                            }
                        });

                        $(".nop-inputs-6").on("keyup", function(){
                            var len = $(this).val().length;

                            if(len == 4) {
                                $(".nop-inputs-7").focus();
                            }
                        });

                        $(".nop-inputs-7").on("keyup", function(){
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
}

?>

<script>
    function onSubmit(sts) {
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
        var alamat = $("#alamatOP-" + sts).val();
        var kc = $("#kecamatan-" + sts).val();
        var kl = $("#kelurahan-" + sts).val();
        var rw = $("#rw-" + sts).val();
        var buku = $("#src-buku-" + sts).val();
		
		var nosk = $("#nosk-" + sts).val();

        if (kc == undefined) kc = "";
        if (rw == undefined) rw = "";
        if (tempo1 == undefined) tempo1 = "";
        if (tempo2 == undefined) tempo2 = "";

        $("#monitoring-content-" + sts).html("loading ...");
        $("#monitoring-content-" + sts).load("view/PBB/monitoring_tahunlama/svc-monitoring.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>", {
            na: nama,
            almt: alamat,
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
            rw: rw,
            buku: buku,
            LBL_KEL: LBL_KEL,
			nosk:nosk
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
        var alamat = $("#alamatOP-" + sts).val();
        var jmlBaris = $("#jml-baris").val();
        var kc = $("#kecamatan-" + sts).val();
        var kl = $("#kelurahan-" + sts).val() || '';
        var rw = $("#rw-" + sts).val();
        var buku = $("#src-buku-" + sts).val()
        // alert(nop);
        if (kc == undefined) kc = "";
        if (rw == undefined) rw = "";
        if (tempo1 == undefined) tempo1 = "";
        if (tempo2 == undefined) tempo2 = "";

        if (sts == 1)
            $("#loadlink1").show();
        else
            $("#loadlink2").show();

        $.ajax({
            type: "POST",
            url: "./view/PBB/monitoring_tahunlama/svc-countforlink.php",
            data: "q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&na=" + nama + '&almt=' + alamat + "&t1=" + tempo1 + "&t2=" + tempo2 + "&th=" + tahun + "&n1=" + nop1 + "&n2=" + nop2 + "&n3=" + nop3 + "&n4=" + nop4 + "&n5=" + nop5 + "&n6=" + nop6 + "&n7=" + nop7 + "&st=" + sts + "&kc=" + kc + "&kl=" + kl + "&rw=" + rw + "&buku=" + buku + "&LBL_KEL=" + LBL_KEL,
            success: function(msg) {
                var sumOfPage = Math.ceil(msg / 20000);
                var strOfLink = "";
                if (msg < 20000 && msg != 0)
                    strOfLink += '<a href="view/PBB/monitoring_tahunlama/svc-toexcel.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&na=' + nama + '&almt=' + alamat + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th=' + tahun + '&n1=' + nop1 + '&n2=' + nop2 + '&n3=' + nop3 + '&n4=' + nop4 + '&n5=' + nop5 + '&n6=' + nop6 + '&n7=' + nop7 + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + "&rw=" + rw + "&buku=" + buku + "&LBL_KEL=" + LBL_KEL + '&p=all&total=' + msg + '">' + nmfileAll + '</a><br/>';
                    // if (msg==0) strOfLink='';
                else {
                    for (var page = 1; page <= sumOfPage; page++) {
                        strOfLink += '<a href="view/PBB/monitoring_tahunlama/svc-toexcel.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&na=' + nama + '&almt=' + alamat + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th=' + tahun + '&n1=' + nop1 + '&n2=' + nop2 + '&n3=' + nop3 + '&n4=' + nop4 + '&n5=' + nop5 + '&n6=' + nop6 + '&n7=' + nop7 + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + "&rw=" + rw + "&buku=" + buku + "&LBL_KEL=" + LBL_KEL + '&p=' + page + '">' + nmfile + page + '</a><br/>';
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