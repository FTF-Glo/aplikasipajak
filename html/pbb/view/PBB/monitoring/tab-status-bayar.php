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

    public function printFromSudahBayar($a = "", $m = "", $uid = "")
    {
        $thn = date("Y");
        $thnTagihan     = $this->appConfig['tahun_tagihan'];
        $lblKelurahan     = $this->appConfig['LABEL_KELURAHAN'];
        echo '
        <style>
            .form-filtering-penetapan {
                background-color: #fff;
                margin:  20px;
                padding: 20px 20px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);

            }
        </style>
            <div class="row">
                <div class="col-md-12">
                    <form id="TheForm-1" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
             
                        <div class="row">
                            <div class="col-12 pb-1" style="display:flex; align-items:center;justify-content:end;">
                                <button class="btn btn-primary" type="button" style="border-radius:8px; margin-right:30px" data-toggle="collapse" data-target="#collapsFilter-'.$selected.'" aria-expanded="false" aria-controls="collapsFilter-'.$selected.'">
                                    Filter Data
                                </button>
                            </div>

                            <div class="col-12" style="margin-bottom:20px"> 
                                <div class="collapse" id="collapsFilter-'.$selected.'">
                                    <div class="form-filtering-penetapan">
                                        <div class="row ">

                                            <div class="form-group col-md-3">
                                                <div class="form-group">
                                                    <label for="">Tahun Pajak</label>
                                                    <div class="row">
                                                        <div class="col-md-5" style="padding-right:0">
                                                            <select name="tahun-pajak-1-awal" class="form-control" id="tahun-pajak-1-awal"><option value="">Semua Tahun</option>';
                                                                for ($t = $thn; $t >= 1994; $t--) {
                                                                    $selected = ($t == $thnTagihan) ? ' selected' : '';
                                                                    echo '<option value="'.$t.'"'.$selected.'>'.$t.'</option>';
                                                                }
                                                            echo '</select>
                                                        </div>
                                                        <div class="col-md-2" style="padding:7px 0 0 0;text-align:center">s/d</div>
                                                        <div class="col-md-5" style="padding-left:0">
                                                            <select name="tahun-pajak-1-akhir" class="form-control" id="tahun-pajak-1-akhir"><option value="">Semua Tahun</option>';
                                                            for ($t = $thn; $t >= 1994; $t--) {
                                                                $selected = ($t == $thnTagihan) ? ' selected' : '';
                                                                echo '<option value="'.$t.'"'.$selected.'>'.$t.'</option>';
                                                            }
                                                            echo '</select> 
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>  
                                            

                                            <div class="form-group col-md-6" >
                                                <div class="form-group">
                                                    <label for="">NOP:</label><br />
                                                    <div class="col-md-1" style="padding: 0">
                                                        <input type="text" class="form-control tcenter nop-input-1" name="nopsub-1-1" id="nopsub-1-1" placeholder="PR">
                                                    </div>
                                                    <div class="col-md-1" style="padding: 0">
                                                        <input type="text" class="form-control tcenter nop-input-2" name="nopsub-1-2" id="nopsub-1-2" placeholder="DTII" maxlength="2">
                                                    </div>
                                                    <div class="col-md-2" style="padding: 0">
                                                        <input type="text" class="form-control tcenter nop-input-3" name="nopsub-1-3" id="nopsub-1-3" placeholder="KEC" maxlength="3">
                                                    </div>
                                                    <div class="col-md-2" style="padding: 0">
                                                        <input type="text" class="form-control tcenter nop-input-4" name="nopsub-1-4" id="nopsub-1-4" placeholder="KEL" maxlength="3">
                                                    </div>
                                                    <div class="col-md-2" style="padding: 0">
                                                        <input type="text" class="form-control tcenter nop-input-5" name="nopsub-1-5" id="nopsub-1-5" placeholder="BLOK" maxlength="3">
                                                    </div>
                                                    <div class="col-md-2" style="padding: 0">
                                                        <input type="text" class="form-control tcenter nop-input-6" name="nopsub-1-6" id="nopsub-1-6" placeholder="NO.URUT" maxlength="4">
                                                    </div>
                                                    <div class="col-md-2" style="padding: 0">
                                                        <input type="text" class="form-control tcenter nop-input-7" name="nopsub-1-7" id="nopsub-1-7" placeholder="KODE" maxlength="1">
                                                    </div>
                                                    <!--<input type="text" name="nop-1" id="nop-1" />-->
                                                </div>
                                            </div>

                                            <div class="form-group col-md-3" >
                                                <label>Nama WP</label>
                                                <input type="text" class="form-control" name="wp-name" id="wp-name-1" />
                                            </div>

                                        </div>

                                        <div class="row ">

                                            <div class="form-group col-md-3" >
                                                <div class="form-group">
                                                    <label for="">Tgl Pembayaran: </label>
                                                    <div class="row">
                                                        <div class="col-md-5" style="padding-right:0">
                                                            <input type="text" name="jatuh-tempo" class="form-control" id="jatuh-tempo1-1" size="10" />
                                                        </div>
                                                        <div class="col-md-2" style="text-align:center;margin-top:5px;padding-left:0;padding-right:0">
                                                            <label>s/d</label>
                                                        </div>
                                                        <div class="col-md-5" style="padding-left:0">
                                                            <input type="text" name="jatuh-tempo2" class="form-control" id="jatuh-tempo2-1" size="10" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                  
                                            <div class="form-group col-md-3">
                                                <label for="">Kecamatan: </label>
                                                <select id="kecamatan-1" class="form-control"></select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="">Desa/Kelurahan: </label>
                                                <select id="kelurahan-1" class="form-control"></select>
                                            </div>

                                            <div class="form-group col-md-3" >
                                                <label>Bank: </label>
                                                <select id="bank-1" name="bank-1" class="form-control">
                                                    <option value="0000001">Bank BJB</option> -->
                                                </select>
                                            </div>
                                        </div>
                                            
                                        <div class="row ">
                                            <div class="form-group col-md-3" >
                                                <label>Operator Payment: </label>
                                                <input type="text" class="form-control" name="operator-1" id="operator-1" />
                                            </div>                                      

                                            <div class="form-group col-md-3" >
                                                <label>Nilai Tagihan: </label>
                                                <select id="src-tagihan-1" name="src-tagihan-1" class="form-control">
                                                    <option value="0" >--semua--</option>
                                                    <option value="1" >0 s/d <=100rb</option>
                                                    <option value="2" >100rb s/d <=200rb</option>
                                                    <option value="3" >200rb s/d <=500rb</option>
                                                    <option value="4" >500rb s/d <=2jt</option>
                                                    <option value="5" >2jt s/d <=5jt</option>
                                                    <option value="6" >>5jt</option>
                                                    <!--<option value="7" >50jt s/d <100jt</option>
                                                    <option value="8" >>=100jt</option>
                                                    <option value="9" >>100jt</option>-->
                                                </select>
                                            </div>
                                      
                                            <div class="form-group col-md-3" >
                                                <label>Buku: </label>
                                                <select id="buku-1" name="buku-1" class="form-control">
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

                                            <div class="form-group col-md-12" > 
                                                <button type="button" name="button2" class="btn btn-info" onClick="onSubmit(1)">Tampilkan</button>
                                                <button type="button" name="buttonToExcel" class="btn btn-primary btn-blue" onClick="toExcel(1)">Ekspor ke xls</button>
                                                <span id="loadlink1" style="font-size: 10px; display: none;">Loading...</span>
                                            </div>

                                        </div>
                                        
                            
                                        
                                        </div>
                                    </div>
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
                            
                            if(len > 2) $(".nop-input-2").val(nopLengkap.substr(2, 2));
                            if(len > 4) $(".nop-input-3").val(nopLengkap.substr(4, 3));
                            if(len > 7) $(".nop-input-4").val(nopLengkap.substr(7, 3));
                            if(len > 10) $(".nop-input-5").val(nopLengkap.substr(10, 3));
                            if(len > 13) $(".nop-input-6").val(nopLengkap.substr(13, 4));
                            if(len > 17) $(".nop-input-7").val(nopLengkap.substr(17, 1));

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
                                onSubmit(1);
                            }
                        });
                    </script>
                </div>
            </div>
        ';
    }

    public function printFormBelumBayar($a = "", $m = "", $uid = "")
    {
        $thn = date("Y");
        $thnTagihan = $this->appConfig['tahun_tagihan'];
        $lblKelurahan     = $this->appConfig['LABEL_KELURAHAN'];
        echo '
            <div class="row">
                <div class="col-md-12">
                    <form id="TheForm-2" method="post" action="view/PBB/monitoring/svc-export.php?q=' . base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}") . '" target="TheWindow">
                        <input type="hidden" name="jatuh-tempo" id="jatuh-tempo1-2" size="10" />
                        <input type="hidden" name="jatuh-tempo" id="jatuh-tempo2-2" size="10" />
                        <div class="row">
                            <div class="form-group col-md-3">
                                
                                    <label>Tahun Pajak</label>
                                    <div class="row">
                                        <div class="col-md-5" style="padding-right:0">
                                            <select name="tahun-pajak-2-awal" class="form-control" id="tahun-pajak-2-awal"><option value="">Semua Tahun</option>';
                                                for ($t = $thn; $t >= 1994; $t--) {
                                                    $selected = ($t == $thnTagihan) ? ' selected' : '';
                                                    echo '<option value="'.$t.'"'.$selected.'>'.$t.'</option>';
                                                }
                                            echo '</select>
                                        </div>
                                        <div class="col-md-2" style="padding:7px 0 0 0;text-align:center">s/d</div>
                                        <div class="col-md-5" style="padding-left:0">
                                            <select name="tahun-pajak-2-akhir" class="form-control" id="tahun-pajak-2-akhir"><option value="">Semua Tahun</option>';
                                            for ($t = $thn; $t >= 1994; $t--) {
                                                $selected = ($t == $thnTagihan) ? ' selected' : '';
                                                echo '<option value="'.$t.'"'.$selected.'>'.$t.'</option>';
                                            }
                                            echo '</select> 
                                        </div>
                                    </div>              
                              
                            </div>
                            <div class="col-md-6">
                               
                                    <label>NOP:</label><br />
                                    <div class="col-md-1" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-1" style="padding: 6px;" name="nopsub-2-1" id="nopsub-2-1" placeholder="PR">
                                    </div>
                                    <div class="col-md-1" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-2" style="padding: 6px;" name="nopsub-2-2" id="nopsub-2-2" placeholder="DTII" maxlength="2">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-3" style="padding: 6px;" name="nopsub-2-3" id="nopsub-2-3" placeholder="KEC" maxlength="3">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-4" style="padding: 6px;" name="nopsub-2-4" id="nopsub-2-4" placeholder="KEL" maxlength="3">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-5" style="padding: 6px;" name="nopsub-2-5" id="nopsub-2-5" placeholder="BLOK" maxlength="3">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-6" style="padding: 6px;" name="nopsub-2-6" id="nopsub-2-6" placeholder="NO.URUT" maxlength="4">
                                    </div>
                                    <div class="col-md-2" style="padding: 0">
                                        <input type="text" class="form-control nop-inputs-7" style="padding: 6px;" name="nopsub-2-7" id="nopsub-2-7" placeholder="KODE" maxlength="1">
                                    </div>
                                    <!--<input type="text" class="form-control" name="nop-1" id="nop-1" />-->
                            </div>
                            <div class="form-group col-md-3">
                                
                                    <label for="">Nama Wajib Pajak</label>
                                    <input type="text" class="form-control" name="wp-name-2" id="wp-name-2" />
                            </div>
                           
                 
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Kecamatan: </label>
                                    <select id="kecamatan-2" class="form-control"></select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">' . $lblKelurahan . '</label>
                                    <select id="kelurahan-2" class="form-control"></select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Nilai Tagihan</label>
                                    <select id="src-tagihan-2" class="form-control" name="src-tagihan-2">
                                        <option value="0" >--semua--</option>
                                        <option value="1" >0 s/d <=100rb</option>
                                        <option value="2" >100rb s/d <=200rb</option>
                                        <option value="3" >200rb s/d <=500rb</option>
                                        <option value="4" >500rb s/d <=2jt</option>
                                        <option value="5" >2jt s/d <=5jt</option>
                                        <option value="6" >>5jt</option>
                                        <!--<option value="7" >50jt s/d <100jt</option>
                                        <option value="8" >>=100jt</option>
                                        <option value="9" >>100jt</option>-->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Buku: </label>
                                    <select id="buku-2" class="form-control" name="buku-2">
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
                                    <label for="">NJOP Bumi Permeter</label>
                                    <div class="row">
                                        <div class="col-md-4" style="padding-right:0">
                                            <input type="text" class="form-control" name="njop1-2" id="njop1-2" value=""/>
                                        </div>
                                        <div class="col-md-2" style="text-align:center;margin-top:5px;padding-left:0;padding-right:0">s/d</div>
                                        <div class="col-md-6" style="padding-left:0">
                                            <input type="text" class="form-control" name="njop2-2" id="njop2-2" value=""/>
                                        </div>
                                    </div>
                                </div>
                            </div>
							
							<div class="col-md-3">
                                <div class="form-group">
                                    <label for="">NJOP Bangunan Permeter</label>
                                    <div class="row">
                                        <div class="col-md-4" style="padding-right:0">
                                            <input type="text" class="form-control" name="njop3-2" id="njop3-2" value=""/>
                                        </div>
                                        <div class="col-md-2" style="text-align:center;margin-top:5px;padding-left:0;padding-right:0">s/d</div>
                                        <div class="col-md-6" style="padding-left:0">
                                            <input type="text" class="form-control" name="njop4-2" id="njop4-2" value=""/>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-3 style="margin-bottom:20px">
                            <label for="">Ceklis</label>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="show-all-2"> Include Sudah Bayar
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-12" > 
                                <button type="button" name="button2" class="btn btn-primary btn-orange" id="button2" onClick="onSubmit(2)" style="margin-bottom: 5px">Tampilkan</button>
                                <button type="button" name="buttonToExcel" class="btn btn-primary btn-blue" id="buttonToExcel" onClick="toExcel(2)" style="margin-bottom: 5px">Ekspor ke xls</button>
                                <button type="button" name="buttonToExcelV2" class="btn btn-primary btn-blue" id="buttonToExcelV2" onClick="toExcelV2(2)" style="margin-bottom: 5px">xls V2</button>
                                <button type="button" name="buttonPdf" class="btn btn-primary btn-blue" id="buttonPdf" onClick="toPdf(2)" style="margin-bottom: 5px">pdf</button>
                                <button type="button" class="btn btn-dark" onClick="toExcelV3(2)" style="margin-bottom: 5px">csv</button>
                                <span id="loadlink2" style="font-size: 10px; display: none;">Loading...</span>
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
                        $("select#kecamatan-2").change(function () {
                            showKelurahan(\'2\');
                        })

                        $(".nop-inputs-1").on("keyup", function(){
                            var len = $(this).val().length;

                            let nopLengkap = $(this).val();
                            
                            if(len > 2) $(".nop-inputs-2").val(nopLengkap.substr(2, 2));
                            if(len > 4) $(".nop-inputs-3").val(nopLengkap.substr(4, 3));
                            if(len > 7) $(".nop-inputs-4").val(nopLengkap.substr(7, 3));
                            if(len > 10) $(".nop-inputs-5").val(nopLengkap.substr(10, 3));
                            if(len > 13) $(".nop-inputs-6").val(nopLengkap.substr(13, 4));
                            if(len > 17) $(".nop-inputs-7").val(nopLengkap.substr(17, 1));

                            if(len > 2) $(this).val(nopLengkap.substr(0, 2));

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
                                onSubmit(2);
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
        // alert();
        var tempo1 = $("#jatuh-tempo1-" + sts).val();
        var tempo2 = $("#jatuh-tempo2-" + sts).val();
        var tahun1 = $("#tahun-pajak-" + sts + "-awal").val();
        var tahun2 = $("#tahun-pajak-" + sts + "-akhir").val();
        //var nop = $("#nop-" + sts).val();
        var nop1 = $("#nopsub-" + sts + "-1").val();
        var nop2 = $("#nopsub-" + sts + "-2").val();
        var nop3 = $("#nopsub-" + sts + "-3").val();
        var nop4 = $("#nopsub-" + sts + "-4").val();
        var nop5 = $("#nopsub-" + sts + "-5").val();
        var nop6 = $("#nopsub-" + sts + "-6").val();
        var nop7 = $("#nopsub-" + sts + "-7").val();
        var nama = $("#wp-name-" + sts).val();
        var jmlBaris = $("#jml-baris").val();
        var kc = $("#kecamatan-" + sts).val();
        var kl = $("#kelurahan-" + sts).val();
        var tagihan = $("#src-tagihan-" + sts).val();
        var bank = $("#bank-" + sts).val();
        var buku = $("#buku-" + sts).val();
		var nj1 = $("#njop1-" + sts).val();
		var nj2 = $("#njop2-" + sts).val();
		var nj3 = $("#njop3-" + sts).val();
		var nj4 = $("#njop4-" + sts).val();
		var operator = $("#operator-" + sts).val();
		
		let showAll = $('#show-all-' + sts).length ? ($('#show-all-' + sts).is(':checked') ? true : false) : false;
		
        $("#monitoring-content-" + sts).html("<div style='padding:80px'>Loading... &nbsp;<img src='image/icon/loadinfo.net.gif' width=20 hight=auto></img></div>");
        var svc = "";
        $("#monitoring-content-" + sts).load("view/PBB/monitoring/svc-monitoring.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>", {
            na: nama,
            t1: tempo1,
            t2: tempo2,
            th1: tahun1,
            th2: tahun2,
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
            bank: bank,
            LBL_KEL: LBL_KEL,
            buku: buku,
			nj1: nj1,
			nj2: nj2,
			nj3: nj3,
			nj4: nj4,
			operator: operator,
			showAll: showAll
        }, function(response, status, xhr) {
            if (status == "error") {
                var msg = "Sorry but there was an error: ";
                $("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
            }
        });

    }

    function toExcel(sts) {
        var nmfileAll = (sts=='1') ? 'SUDAH_BAYAR' : 'Belum_Bayar';
        var nmfile = nmfileAll + '_Part_';
        var tempo1 = $("#jatuh-tempo1-" + sts).val();
        var tempo2 = $("#jatuh-tempo2-" + sts).val();
        var tahun1 = $("#tahun-pajak-" + sts + "-awal").val();
        var tahun2 = $("#tahun-pajak-" + sts + "-akhir").val();
        //var nop = $("#nop-" + sts).val();
        var nop1 = $("#nopsub-" + sts + "-1").val();
        var nop2 = $("#nopsub-" + sts + "-2").val();
        var nop3 = $("#nopsub-" + sts + "-3").val();
        var nop4 = $("#nopsub-" + sts + "-4").val();
        var nop5 = $("#nopsub-" + sts + "-5").val();
        var nop6 = $("#nopsub-" + sts + "-6").val();
        var nop7 = $("#nopsub-" + sts + "-7").val();
        var nama = $("#wp-name-" + sts).val();
        var jmlBaris = $("#jml-baris").val();
        var kc = $("#kecamatan-" + sts).val();
        var kl = $("#kelurahan-" + sts).val();
        var nmkc = $("#kecamatan-" + sts + " option:selected").text();
        var nmkl = $("#kelurahan-" + sts + "  option:selected").text();
        var tagihan = $("#src-tagihan-" + sts).val();
        var bank = $("#bank-" + sts).val();
        var buku = $("#buku-" + sts).val();
		var nj1 = $("#njop1-" + sts).val();
		var nj2 = $("#njop2-" + sts).val();
		var nj3 = $("#njop3-" + sts).val();
		var nj4 = $("#njop4-" + sts).val();
		var operator = $("#operator-" + sts).val();
		
		let showAll = $('#show-all-' + sts).length ? ($('#show-all-' + sts).is(':checked') ? true : false) : false;

        if (sts == 1)
            $("#loadlink1").show();
        else
            $("#loadlink2").show();

        $.ajax({
            type: "POST",
            url: "./view/PBB/monitoring/svc-countforlink.php",
            data: "q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&na=" + nama + "&t1=" + tempo1 + "&t2=" + tempo2 + "&th1=" + tahun1 + "&th2=" + tahun2 + "&n1=" + nop1 + "&n2=" + nop2 + "&n3=" + nop3 + "&n4=" + nop4 + "&n5=" + nop5 + "&n6=" + nop6 + "&n7=" + nop7 + "&st=" + sts + "&kc=" + kc + "&kl=" + kl + "&tagihan=" + tagihan + '&bank=' + bank + "&LBL_KEL=" + LBL_KEL + "&buku=" + buku + "&nmkc=" + nmkc + "&nmkl=" + nmkl + "&nj1=" + nj1 + "&nj2=" + nj2 + "&nj3=" + nj3 + "&nj4=" + nj4 + "&operator=" + operator + "&showAll=" + showAll,
            success: function(msg) {
                var sumOfPage = Math.ceil(msg / 10000);
                var strOfLink = "";
                if (msg < 10000)
                    strOfLink += '<a href="view/PBB/monitoring/svc-toexcel.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&na=' + nama + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th1=' + tahun1 + "&th2=" + tahun2 + '&n1=' + nop1 + '&n2=' + nop2 + '&n3=' + nop3 + '&n4=' + nop4 + '&n5=' + nop5 + '&n6=' + nop6 + '&n7=' + nop7 + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + '&tagihan=' + tagihan + '&bank=' + bank + "&LBL_KEL=" + LBL_KEL + "&buku=" + buku + "&LBL_KEL=" + LBL_KEL + +"&nmkc=" + nmkc + "&nmkl=" + nmkl + "&nj1=" + nj1 + "&nj2=" + nj2 + "&nj3=" + nj3 + "&nj4=" + nj4 + "&operator=" + operator + "&showAll=" + showAll + '&p=all&total=' + msg + '">' + nmfileAll + '</a><br/>';
                else {
                    for (var page = 1; page <= sumOfPage; page++) {
                        strOfLink += '<a href="view/PBB/monitoring/svc-toexcel.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&na=' + nama + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th1=' + tahun1 + "&th2=" + tahun2 + '&n1=' + nop1 + '&n2=' + nop2 + '&n3=' + nop3 + '&n4=' + nop4 + '&n5=' + nop5 + '&n6=' + nop6 + '&n7=' + nop7 + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + '&tagihan=' + tagihan + '&bank=' + bank + "&LBL_KEL=" + LBL_KEL + "&buku=" + buku + "&nmkc=" + nmkc + "&nmkl=" + nmkl + "&nj1=" + nj1 + "&nj2=" + nj2 + "&nj3=" + nj3 + "&nj4=" + nj4 + "&operator=" + operator + "&showAll=" + showAll + '&p=' + page + '">' + nmfile + page + '</a><br/>';
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
	
	function toExcelV2(sts) {
        var nmfileAll = (sts=='1') ? 'SUDAH_BAYAR_VERSION_2' : 'Belum_Bayar_Version_2';
        var nmfile = nmfileAll + '_Part_';
        var tempo1 = $("#jatuh-tempo1-" + sts).val();
        var tempo2 = $("#jatuh-tempo2-" + sts).val();
        var tahun1 = $("#tahun-pajak-" + sts + "-awal").val();
        var tahun2 = $("#tahun-pajak-" + sts + "-akhir").val();
        //var nop = $("#nop-" + sts).val();
        var nop1 = $("#nopsub-" + sts + "-1").val();
        var nop2 = $("#nopsub-" + sts + "-2").val();
        var nop3 = $("#nopsub-" + sts + "-3").val();
        var nop4 = $("#nopsub-" + sts + "-4").val();
        var nop5 = $("#nopsub-" + sts + "-5").val();
        var nop6 = $("#nopsub-" + sts + "-6").val();
        var nop7 = $("#nopsub-" + sts + "-7").val();
        var nama = $("#wp-name-" + sts).val();
        var jmlBaris = $("#jml-baris").val();
        var kc = $("#kecamatan-" + sts).val();
        var kl = $("#kelurahan-" + sts).val();
        var nmkc = $("#kecamatan-" + sts + " option:selected").text();
        var nmkl = $("#kelurahan-" + sts + "  option:selected").text();
        var tagihan = $("#src-tagihan-" + sts).val();
        var bank = $("#bank-" + sts).val();
        var buku = $("#buku-" + sts).val();
		var nj1 = $("#njop1-" + sts).val();
		var nj2 = $("#njop2-" + sts).val();
		var nj3 = $("#njop3-" + sts).val();
		var nj4 = $("#njop4-" + sts).val();
		var operator = $("#operator-" + sts).val();
		
		let showAll = $('#show-all-' + sts).length ? ($('#show-all-' + sts).is(':checked') ? true : false) : false;

        if (sts == 1)
            $("#loadlink1").show();
        else
            $("#loadlink2").show();

        $.ajax({
            type: "POST",
            url: "./view/PBB/monitoring/svc-countforlink.php",
            data: "q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&na=" + nama + "&t1=" + tempo1 + "&t2=" + tempo2 + "&th1=" + tahun1 + "&th2=" + tahun2 + "&n1=" + nop1 + "&n2=" + nop2 + "&n3=" + nop3 + "&n4=" + nop4 + "&n5=" + nop5 + "&n6=" + nop6 + "&n7=" + nop7 + "&st=" + sts + "&kc=" + kc + "&kl=" + kl + "&tagihan=" + tagihan + '&bank=' + bank + "&LBL_KEL=" + LBL_KEL + "&buku=" + buku + "&nmkc=" + nmkc + "&nmkl=" + nmkl + "&nj1=" + nj1 + "&nj2=" + nj2 + "&nj3=" + nj3 + "&nj4=" + nj4 + "&operator=" + operator + "&showAll=" + showAll,
            success: function(msg) {
                var sumOfPage = Math.ceil(msg / 10000);
                var strOfLink = "";
                if (msg < 10000)
                    strOfLink += '<a href="view/PBB/monitoring/svc-toexcelV2.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&na=' + nama + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th1=' + tahun1 + "&th2=" + tahun2  + '&n1=' + nop1 + '&n2=' + nop2 + '&n3=' + nop3 + '&n4=' + nop4 + '&n5=' + nop5 + '&n6=' + nop6 + '&n7=' + nop7 + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + '&tagihan=' + tagihan + '&bank=' + bank + "&LBL_KEL=" + LBL_KEL + "&buku=" + buku +"&nmkc=" + nmkc + "&nmkl=" + nmkl + "&nj1=" + nj1 + "&nj2=" + nj2 + "&nj3=" + nj3 + "&nj4=" + nj4 + "&operator=" + operator + "&showAll=" + showAll + '&p=all&total=' + msg + '">' + nmfileAll + '</a><br/>';
                else {
                    for (var page = 1; page <= sumOfPage; page++) {
                        strOfLink += '<a href="view/PBB/monitoring/svc-toexcelV2.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&na=' + nama + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th1=' + tahun1 + "&th2=" + tahun2 + '&n1=' + nop1 + '&n2=' + nop2 + '&n3=' + nop3 + '&n4=' + nop4 + '&n5=' + nop5 + '&n6=' + nop6 + '&n7=' + nop7 + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + '&tagihan=' + tagihan + '&bank=' + bank + "&LBL_KEL=" + LBL_KEL + "&buku=" + buku + "&nmkc=" + nmkc + "&nmkl=" + nmkl + "&nj1=" + nj1 + "&nj2=" + nj2 + "&nj3=" + nj3 + "&nj4=" + nj4 + "&operator=" + operator + "&showAll=" + showAll + '&p=' + page + '">' + nmfile + page + '</a><br/>';
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

	function toExcelV3(sts) {
        var nmfileAll = (sts=='1') ? 'SUDAH_BAYAR' : 'Belum_Bayar';
        var nmfile = nmfileAll + '_Part_';
        var tempo1 = $("#jatuh-tempo1-" + sts).val();
        var tempo2 = $("#jatuh-tempo2-" + sts).val();
        var tahun1 = $("#tahun-pajak-" + sts + "-awal").val();
        var tahun2 = $("#tahun-pajak-" + sts + "-akhir").val();
        //var nop = $("#nop-" + sts).val();
        var nop1 = $("#nopsub-" + sts + "-1").val();
        var nop2 = $("#nopsub-" + sts + "-2").val();
        var nop3 = $("#nopsub-" + sts + "-3").val();
        var nop4 = $("#nopsub-" + sts + "-4").val();
        var nop5 = $("#nopsub-" + sts + "-5").val();
        var nop6 = $("#nopsub-" + sts + "-6").val();
        var nop7 = $("#nopsub-" + sts + "-7").val();
        var nama = $("#wp-name-" + sts).val();
        var jmlBaris = $("#jml-baris").val();
        var kc = $("#kecamatan-" + sts).val();
        var kl = $("#kelurahan-" + sts).val();
        var nmkc = $("#kecamatan-" + sts + " option:selected").text();
        var nmkl = $("#kelurahan-" + sts + "  option:selected").text();
        var tagihan = $("#src-tagihan-" + sts).val();
        var bank = $("#bank-" + sts).val();
        var buku = $("#buku-" + sts).val();
		var nj1 = $("#njop1-" + sts).val();
		var nj2 = $("#njop2-" + sts).val();
		var nj3 = $("#njop3-" + sts).val();
		var nj4 = $("#njop4-" + sts).val();
		var operator = $("#operator-" + sts).val();
		
		let showAll = $('#show-all-' + sts).length ? ($('#show-all-' + sts).is(':checked') ? true : false) : false;

        if (sts == 1)
            $("#loadlink1").show();
        else
            $("#loadlink2").show();

        $.ajax({
            type: "POST",
            url: "./view/PBB/monitoring/svc-countforlink.php",
            data: "q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&na=" + nama + "&t1=" + tempo1 + "&t2=" + tempo2 + "&th1=" + tahun1 + "&th2=" + tahun2 + "&n1=" + nop1 + "&n2=" + nop2 + "&n3=" + nop3 + "&n4=" + nop4 + "&n5=" + nop5 + "&n6=" + nop6 + "&n7=" + nop7 + "&st=" + sts + "&kc=" + kc + "&kl=" + kl + "&tagihan=" + tagihan + '&bank=' + bank + "&LBL_KEL=" + LBL_KEL + "&buku=" + buku + "&nmkc=" + nmkc + "&nmkl=" + nmkl + "&nj1=" + nj1 + "&nj2=" + nj2 + "&nj3=" + nj3 + "&nj4=" + nj4 + "&operator=" + operator + "&showAll=" + showAll,
            success: function(msg) {
                var sumOfPage = Math.ceil(msg / 10000);
                var strOfLink = "";
                if (msg < 10000)
                    strOfLink += '<a href="view/PBB/monitoring/svc-toexcelV3_csv.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&na=' + nama + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th1=' + tahun1 + "&th2=" + tahun2 + '&n1=' + nop1 + '&n2=' + nop2 + '&n3=' + nop3 + '&n4=' + nop4 + '&n5=' + nop5 + '&n6=' + nop6 + '&n7=' + nop7 + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + '&tagihan=' + tagihan + '&bank=' + bank + "&LBL_KEL=" + LBL_KEL + "&buku=" + buku + "&nmkc=" + nmkc + "&nmkl=" + nmkl + "&nj1=" + nj1 + "&nj2=" + nj2 + "&nj3=" + nj3 + "&nj4=" + nj4 + "&operator=" + operator + "&showAll=" + showAll + '&p=all&total=' + msg + '">' + nmfileAll + '</a><br/>';
                else {
                    for (var page = 1; page <= sumOfPage; page++) {
                        strOfLink += '<a href="view/PBB/monitoring/svc-toexcelV3_csv.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&na=' + nama + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th1=' + tahun1 + "&th2=" + tahun2 + '&n1=' + nop1 + '&n2=' + nop2 + '&n3=' + nop3 + '&n4=' + nop4 + '&n5=' + nop5 + '&n6=' + nop6 + '&n7=' + nop7 + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + '&tagihan=' + tagihan + '&bank=' + bank + "&LBL_KEL=" + LBL_KEL + "&buku=" + buku + "&nmkc=" + nmkc + "&nmkl=" + nmkl + "&nj1=" + nj1 + "&nj2=" + nj2 + "&nj3=" + nj3 + "&nj4=" + nj4 + "&operator=" + operator + "&showAll=" + showAll + '&p=' + page + '">' + nmfile + page + '</a><br/>';
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
	
	function toPdf(sts) {
        var nmfileAll = (sts=='1') ? 'SUDAH_BAYAR' : 'Belum_Bayar';
        var nmfile = nmfileAll + '_Part_';
        var tempo1 = $("#jatuh-tempo1-" + sts).val();
        var tempo2 = $("#jatuh-tempo2-" + sts).val();
        var tahun1 = $("#tahun-pajak-" + sts + "-awal").val();
        var tahun2 = $("#tahun-pajak-" + sts + "-akhir").val();
        //var nop = $("#nop-" + sts).val();
        var nop1 = $("#nopsub-" + sts + "-1").val();
        var nop2 = $("#nopsub-" + sts + "-2").val();
        var nop3 = $("#nopsub-" + sts + "-3").val();
        var nop4 = $("#nopsub-" + sts + "-4").val();
        var nop5 = $("#nopsub-" + sts + "-5").val();
        var nop6 = $("#nopsub-" + sts + "-6").val();
        var nop7 = $("#nopsub-" + sts + "-7").val();
        var nama = $("#wp-name-" + sts).val();
        var jmlBaris = $("#jml-baris").val();
        var kc = $("#kecamatan-" + sts).val();
        var kl = $("#kelurahan-" + sts).val();
        var nmkc = $("#kecamatan-" + sts + " option:selected").text();
        var nmkl = $("#kelurahan-" + sts + "  option:selected").text();
        var tagihan = $("#src-tagihan-" + sts).val();
        var bank = $("#bank-" + sts).val();
        var buku = $("#buku-" + sts).val();
		var nj1 = $("#njop1-" + sts).val();
		var nj2 = $("#njop2-" + sts).val();
		var nj3 = $("#njop3-" + sts).val();
		var nj4 = $("#njop4-" + sts).val();
		var operator = $("#operator-" + sts).val();
		
		let showAll = $('#show-all-' + sts).length ? ($('#show-all-' + sts).is(':checked') ? true : false) : false;

        if (sts == 1)
            $("#loadlink1").show();
        else
            $("#loadlink2").show();
        console.log(nmkc);
        $.ajax({
            type: "POST",
            url: "./view/PBB/monitoring/svc-countforlink.php",
            data: "q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&na=" + nama + "&t1=" + tempo1 + "&t2=" + tempo2 + "&th1=" + tahun1 + "&th2=" + tahun2 + "&n1=" + nop1 + "&n2=" + nop2 + "&n3=" + nop3 + "&n4=" + nop4 + "&n5=" + nop5 + "&n6=" + nop6 + "&n7=" + nop7 + "&st=" + sts + "&kc=" + kc + "&kl=" + kl + "&tagihan=" + tagihan + '&bank=' + bank + "&LBL_KEL=" + LBL_KEL + "&buku=" + buku + "&nmkc=" + nmkc + "&nmkl=" + nmkl + "&nj1=" + nj1 + "&nj2=" + nj2 + "&nj3=" + nj3 + "&nj4=" + nj4 + "&operator=" + operator + "&showAll=" + showAll,
            success: function(msg) {
                var sumOfPage = Math.ceil(msg / 500);
                // console.log(sumOfPage)
                var labelLastCount = "&LastPart=1";
                var strOfLink = "";
                if (msg < 500){
                    strOfLink += '<a href="view/PBB/monitoring/svc-topdf-statusbayar.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&na=' + nama + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th1=' + tahun1 + "&th2=" + tahun2 + '&n1=' + nop1 + '&n2=' + nop2 + '&n3=' + nop3 + '&n4=' + nop4 + '&n5=' + nop5 + '&n6=' + nop6 + '&n7=' + nop7 + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + '&tagihan=' + tagihan + '&bank=' + bank + "&LBL_KEL=" + LBL_KEL + "&buku=" + buku  +"&nmkc=" + nmkc + "&nmkl=" + nmkl + "&nj1=" + nj1 + "&nj2=" + nj2 + "&nj3=" + nj3 + "&nj4=" + nj4 + "&operator=" + operator + "&showAll=" + showAll + '&p=all&total=' + msg +labelLastCount+'" >' + nmfileAll + '</a><br/>';
                }
                else {
                    for (var page = 1; page <= sumOfPage; page++) {
                        labelLastCount = (page!=sumOfPage) ? "&LastPart=0" : "&LastPart=1";
                        strOfLink += '<a href="view/PBB/monitoring/svc-topdf-statusbayar.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&na=' + nama + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th1=' + tahun1 + "&th2=" + tahun2 + '&n1=' + nop1 + '&n2=' + nop2 + '&n3=' + nop3 + '&n4=' + nop4 + '&n5=' + nop5 + '&n6=' + nop6 + '&n7=' + nop7 + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + '&tagihan=' + tagihan + '&bank=' + bank + "&LBL_KEL=" + LBL_KEL + "&buku=" + buku + "&nmkc=" + nmkc + "&nmkl=" + nmkl + "&nj1=" + nj1 + "&nj2=" + nj2 + "&nj3=" + nj3 + "&nj4=" + nj4 + "&operator=" + operator + "&showAll=" + showAll + '&p=' + page + labelLastCount+'" >' + nmfile + page + '</a><br/>';
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