<?php
class PenerimaanHarian {
    public $sudahBayarLabel = '';
    public $label = 'Daftar Penerimaan Harian';
    
    private $appConfig;
    private $idRole;
    private $dtUser;
    
    public function __construct ($appConfig,$idRole,$dtUser) {
        $this->appConfig   = $appConfig;
        $this->idRole       = $idRole;
        $this->dtUser       = $dtUser;
    }
        
    public function printForm(){
        $thn = date("Y");
        $thnTagihan = $this->appConfig['tahun_tagihan'];
        $lblKelurahan   = $this->appConfig['LABEL_KELURAHAN']; 
        $filterWilayah  = "";

        if($this->idRole=="rmKelurahan") {
            // $filterWilayah = '<td>'.$lblKelurahan.'</td><td><sele ct id="kelurahan-1"></select></td><td>RW</td><td><select id="rw-1"></select></td>';
            $filterWilayah = ' 
                                    <td width="117">'.$lblKelurahan.'</td>
                                    <td width="3">:</td>
                                    <td><select id="kelurahan-7"></select></td>
                                     <td width="8">&nbsp;</td>';

        } else if($this->idRole=="rmKecamatan"){
            $filterWilayah = 
            '<td width="100">Kecamatan </td>
                                    <td width="3">:</td>
                                    <td width="144"><select id="kecamatan-7">
            <option value="'.$this->dtUser['kecamatan'].'">'.$this->dtUser['CPC_TKC_KECAMATAN'].'</option>
            </select></td>
                                    <td width="8">&nbsp;</td>
                                    <td width="117">'.$lblKelurahan.'</td>
                                    <td width="3">:</td>
                                    <td><select id="kelurahan-7"></select></td>' ;
        } else {
            $filterWilayah = '<td width="100">Kecamatan </td>
                                    <td width="3">:</td>
                                    <td width="144"><select id="kecamatan-7">
            <option value="'.$this->dtUser['kecamatan'].'">'.$this->dtUser['CPC_TKC_KECAMATAN'].'</option>
            </select></td>
                                    <td width="8">&nbsp;</td>
                                    <td width="117">'.$lblKelurahan.'</td>
                                    <td width="3">:</td>
                                    <td><select id="kelurahan-7"></select></td>';
        }
        echo '
            <fieldset id="formDetail" style="display: none;">
                        <form id="TheForm-7" method="post" action="view/PBB/monitoring/svc-export.php?q='.base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}").'" target="TheWindow">
                            <input type="hidden" name="jatuh-tempo" id="jatuh-tempo1-7" size="10" />
                            <input type="hidden" name="jatuh-tempo" id="jatuh-tempo2-7" size="10" />
                            <table width="100%" border="0" cellspacing="0" cellpadding="2">
                                <tr>
                                    <td width="100">Tahun Pajak </td>
                                    <td width="3">:</td>
                                    <td colspan="2" align="left">
                                        <select name="tahun-pajak-7" id="tahun-pajak-7">';
                                            echo "<option value=\"\">Semua</option>";
                                            for ($t = $thn; $t > 1993; $t--) {
                                                if ($t == $thnTagihan) {
                                                    echo "<option value=\"$t\" selected>$t</option>";
                                                } else
                                                    echo "<option value=\"$t\">$t</option>";
                                            }
        echo                            '</select>  
         s/d  
         <select name="tahun-pajak2-7" id="tahun-pajak2-7">';
                                            echo "<option value=\"\">Semua</option>";
                                            for ($t = $thn; $t > 1993; $t--) {
                                                if ($t == $thnTagihan) {
                                                    echo "<option value=\"$t\" selected>$t</option>";
                                                } else
                                                    echo "<option value=\"$t\">$t</option>";
                                            }
        echo                            '</select>                
                                    </td>
                                    <td width="100">NOP </td>
                                    <td width="3">:</td>
                                    <td><input type="text" name="nop" id="nop-7" /></td>
                                    <td width="100">Nama&nbsp;Wajib&nbsp;Pajak</td>
                                    <td width="3">:</td>
                                    <td><input type="text" name="wp-name-7" id="wp-name-7" /></td>
                                    <td>
                                        <input type="button" name="button7" id="button7" value="Tambahkan" onClick="onSubmitHarian(7)"/>
                                        <input type="button" name="buttonToExcel7" id="buttonToExcel7" value="To XLS" onClick="toExcelHarian(7)"/>
                                        
                                        <input type="button" name="btn-cetak-pdf" id="btn-cetak-pdf7" onClick="toPdfHarian(7)" value="To PDF" />
               
                                        <span id="loadlink7" style="font-size: 10px; display: none;">Loading...</span>
                                    </td>
                                </tr>
                                <tr>
                                    '.$filterWilayah.'
                                    <td width="73">Buku</td>
                                    <td width="3">:</td>
                                    <td><select id="src-tagihan-7" name="src-tagihan-7">
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

                                        </td>
                                        <td colspan="2">
                                            <input type="button" name="button7" id="button7" value="KEMBALI" onClick="back()"/>
                                           
                                        </td>
                                        <td colspan="2">
                                            <input type="button" name="button7" id="button7" value="SIMPAN" onClick="simpanDaftar_(7)"/>
                                        </td>
                                </tr>
                            </table> 
                        </form>
                    </fieldset>
                    <div id="monitoring-content-7" class="monitoring-content" style="display: none;">
                    </div>
                    <script>
                        showKelurahan(\'7\');
                        showRW(\'7\');
                        $("select#kecamatan-7").change(function () {
                            showKelurahan(\'7\');
                        })
                    </script>
        ';

        echo '
        <fieldset id="listDaftar" style="display:block"> &nbsp&nbsp
        <input type="button" name="button7" id="button7" value="BUAT DAFTAR BARU" onClick="simpanGroup(7)" style="margin:10px 10px 10px 0px;"/>
            <table cellpadding="1" cellspacing="1" id="users" class="display" width="100%">
             <thead>
                <tr>
                    <th>NO DPH</th>
                    <th>NAMA FILE</th>
                    <th>TANGGAL</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            </table>
        </fieldset>';
    }
        // <input type="button" name="buttonToExcel7" id="buttonToExcel7" value="To CSV" onClick="toExcelHarian(7)"/>
          // <td colspan="2">
          //                                   <input type="button" name="button7" id="button7" value="BUKA DAFTAR" onClick="lihat(7)"/>
          //                               </td>
     public function printDaftar(){
        $thn = date("Y");
        $thnTagihan = $this->appConfig['tahun_tagihan'];
        $lblKelurahan   = $this->appConfig['LABEL_KELURAHAN']; 
        $filterWilayah  = "";

        if($this->idRole=="rmKelurahan") {
            // $filterWilayah = '<td>'.$lblKelurahan.'</td><td><select id="kelurahan-1"></select></td><td>RW</td><td><select id="rw-1"></select></td>';
            $filterWilayah = ' 
                                    <td width="117">'.$lblKelurahan.'</td>
                                    <td width="3">:</td>
                                    <td><select id="kelurahan-7"></select></td>
                                     <td width="8">&nbsp;</td>';

        } else if($this->idRole=="rmKecamatan"){
            $filterWilayah = 
            '<td width="100">Kecamatan </td>
                                    <td width="3">:</td>
                                    <td width="144"><select id="kecamatan-7">
            <option value="'.$this->dtUser['kecamatan'].'">'.$this->dtUser['CPC_TKC_KECAMATAN'].'</option>
            </select></td>
                                    <td width="8">&nbsp;</td>
                                    <td width="117">'.$lblKelurahan.'</td>
                                    <td width="3">:</td>
                                    <td><select id="kelurahan-7"></select></td>' ;
        } else {
            $filterWilayah = '<td width="100">Kecamatan </td>
                                    <td width="3">:</td>
                                    <td width="144"><select id="kecamatan-7">
            <option value="'.$this->dtUser['kecamatan'].'">'.$this->dtUser['CPC_TKC_KECAMATAN'].'</option>
            </select></td>
                                    <td width="8">&nbsp;</td>
                                    <td width="117">'.$lblKelurahan.'</td>
                                    <td width="3">:</td>
                                    <td><select id="kelurahan-7"></select></td>';
        }
        echo '
        <fieldset id="listDaftar">
            <table cellpadding="1" cellspacing="1" id="users" class="display" width="100%">
    <thead>
    <tr>
        <th>NO DPH</th>
        <th>NAMA FILE</th>
        <th>TANGGAL</th>
        <th>AKSI</th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <th>NO DPH</th>
        <th>NAMA FILE</th>
        <th>TANGGAL</th>
        <th>AKSI</th>
    </tr>
    </tfoot>
</table>
</fieldset>
 
';
    }
}
    
?>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>    
        
        var x ;
        var y ; 
        var content7;
        var tabelGroup;
        var NO_DPH_AKTIF = 0;
        var NAMA_FILE_AKTIF ="";

        var arrTempSPPT=[];
         $(document).ready(function () {
           // init(7);
           x        = document.getElementById("formDetail");
           content7 = document.getElementById("monitoring-content-7");
           y        = document.getElementById("listDaftar");

           if (x) x.style.display = "none";
           if (content7) content7.style.display = "none";

            var stss = 7 ;
            var tempo1      = $("#jatuh-tempo1-" + stss).val();
            var tempo2      = $("#jatuh-tempo2-" + stss).val();
            var tahun       = $("#tahun-pajak-" + stss).val();
            var tahun2      = $("#tahun-pajak2-" + stss).val();
            // alert(tahun);
            // alert(tahun2);
            var nop         = $("#nop-" + stss).val();
            var nama        = $("#wp-name-" + stss).val();
            var jmlBaris    = $("#jml-baris").val();
            var kc          = $("#kecamatan-" + stss).val();
            var kl          = $("#kelurahan-" + stss).val();
            var tagihan     = $("#src-tagihan-" + stss).val();
            var bank        = $("#bank-"+stss).val();
            var base        =String("<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>");
            
            setTimeout(function(){
               tabelGroup = $('#users').DataTable({
            "columns": [
                {"data": "NO_DPH"},
                {"data": "NAMA_FILE",},
                {"data": "CREATED_AT","width":"50px"},
                {
                    "data"      : null,
                    "sortable"  : false,
                    "render"    : function (data,type,full){
                       // console.log(data);
                        return "<button type='button' onClick=\"openDaftar(\'"+data.NO_DPH+"\',\'"+data.NAMA_FILE+"\')\">Lihat</button>"+
                                "<button type='button' onClick='hapusDaftar("+data.NO_DPH+")'style='background-color:red;'>hapus</button>";
                    }
        
                // {
                //     "data": null,
                //     "targets":-1,
                //     "defaultContent": "<button onClick='openDaftar(NO_DPH)'>Lihat</button><button style='background-color:red;'>hapus</button>"

                }
            ],
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: "./view/PBB/monitoring_wilayah/svc-daftar-dph.php",
                type: 'POST', 
                data: {
                    q: base,
                    na      : nama ,
                    th1     : tempo1 ,
                    th2     : tempo2 ,
                    th      : tahun, 
                    th2     : tahun2,
                    n       : nop,
                    st      : 7,
                    kc      : kc ,
                    kl      : kl,
                    tagihan : tagihan, 
                    GW_DBHOST : GW_DBHOST,
                    GW_DBNAME   : GW_DBNAME,
                    GW_DBUSER   : GW_DBUSER,
                    GW_DBPWD    : GW_DBPWD,
                    GW_DBPORT   : GW_DBPORT,
                    LBL_KEL     : LBL_KEL

                }
            },

            }); 

            } , 4000 );
            

         });
         function back(){
           x.style.display = "none";
           content7.style.display ="none";
           y.style.display = "block";
           NO_DPH_AKTIF =0;
         }
         function lihat(sts){
            var tempo1      = $("#jatuh-tempo1-" + sts).val();
            var tempo2      = $("#jatuh-tempo2-" + sts).val();
            var tahun       = $("#tahun-pajak-" + sts).val();
            var tahun2      = $("#tahun-pajak2-" + sts).val();
            // alert(tahun);
            // alert(tahun2);
            var nop         = $("#nop-" + sts).val();
            var nama        = $("#wp-name-" + sts).val();
            var jmlBaris    = $("#jml-baris").val();
            var kc          = $("#kecamatan-" + sts).val();
            var kl          = $("#kelurahan-" + sts).val();
            var tagihan     = $("#src-tagihan-" + sts).val();
            var bank        = $("#bank-"+sts).val();

            $.ajax({ 
                type: "POST",
                url: "./view/PBB/monitoring_wilayah/svc-monitoring-dph.php",
                data: "q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&na=" + nama +"&lihat=1"+ "&t1=" + tempo1 + "&t2=" + tempo2 + "&th=" + tahun +"&th2=" + tahun2 + "&n=" + nop + "&st=" + sts + "&kc=" + kc + "&kl=" + kl + "&tagihan=" + tagihan + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT + "&LBL_KEL=" + LBL_KEL,
                success: function (msg) {
                    $("#contentDph1").find("tr:gt(0)").remove();
                    var table = document.getElementById("contentDph1");
                    if (msg!="false"){
                        var daftarDph = JSON.parse(msg);
                        var i =1;
                         daftarDph.forEach(function(entry) {
                           // console.log(entry);
                            var row   = table.insertRow(i);
                            var cell1 = row.insertCell(0);
                            var cell2 = row.insertCell(1);
                            var cell3 = row.insertCell(2);
                            var cell4 = row.insertCell(3);
                            cell1.innerHTML = i;
                            cell2.innerHTML = entry.NAMA_FILE;
                            cell3.innerHTML = entry.CREATED_AT;
                            cell4.innerHTML = "<button type='button' onClick='openDaftar("+entry.NO_DPH+")'>Lihat</button><button type='button' style='background-color:red;'>hapus</button>";
                            i++;
                         });
                    }else{
                        alert("pengambilan data gagal");
                    }
                }
            });
            
             var strOfLink = '<a href="view/PBB/monitoring_wilayah/svc-toexcel-dph.php?>' + "nmfileAll" + '</a><br/>';
                
                    $("#contentDph").html(strOfLink);
                    $("#dBox").css("display", "block");

         }
         function simpanGroup(sts){
            var tempo1      = $("#jatuh-tempo1-" + sts).val();
            var tempo2      = $("#jatuh-tempo2-" + sts).val();
            var tahun       = $("#tahun-pajak-" + sts).val();
            var tahun2      = $("#tahun-pajak2-" + sts).val();
            // alert(tahun);
            // alert(tahun2);
            var nop         = $("#nop-" + sts).val();
            var nama        = $("#wp-name-" + sts).val();
            var jmlBaris    = $("#jml-baris").val();
            var kc          = $("#kecamatan-" + sts).val();
            var kl          = $("#kelurahan-" + sts).val();
            var tagihan     = $("#src-tagihan-" + sts).val();
            var bank        = $("#bank-"+sts).val();

            var nameFile = prompt("Masukan nama file yang akan disimpan  *maksimal 15 karakter  :");

            if (nameFile == null || nameFile == "") {
               
                console.log ("cancel");
            } else {
               // console.log(nameFile.trim());
                daftar2 =JSON.stringify(arrTempSPPT);

                $.ajax({ 
                type: "POST",
                url: "./view/PBB/monitoring_wilayah/svc-monitoring-dph.php",
                data: "q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&na=" + nama +"&jsonDaftar="+daftar2+"&simpan=2"+"&nameFile="+nameFile+ "&t1=" + tempo1 + "&t2=" + tempo2 + "&th=" + tahun +"&th2=" + tahun2 + "&n=" + nop + "&st=" + sts + "&kc=" + kc + "&kl=" + kl + "&tagihan=" + tagihan + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT + "&LBL_KEL=" + LBL_KEL,
                success: function (msg) {
                    if (msg=="true"){
                        alert("Data Berhasil Disimpan dengan nama : "+nameFile);
                        tabelGroup.draw();
                    }else{
                        alert("gagal menyimpan : "+msg);
                    } 
                }
            });

            }
         }
         function simpanDaftar(sts){

            var tempo1      = $("#jatuh-tempo1-" + sts).val();
            var tempo2      = $("#jatuh-tempo2-" + sts).val();
            var tahun       = $("#tahun-pajak-" + sts).val();
            var tahun2      = $("#tahun-pajak2-" + sts).val();
            // alert(tahun);
            // alert(tahun2);
            var nop         = $("#nop-" + sts).val();
            var nama        = $("#wp-name-" + sts).val();
            var jmlBaris    = $("#jml-baris").val();
            var kc          = $("#kecamatan-" + sts).val();
            var kl          = $("#kelurahan-" + sts).val();
            var tagihan     = $("#src-tagihan-" + sts).val();
            var bank        = $("#bank-"+sts).val();

            var nameFile = prompt("Masukan nama file yang akan disimpan  *maksimal 15 karakter  :");

            if (nameFile == null || nameFile == "") {
               
                console.log ("cancel");
            } else {
               // console.log(nameFile.trim());
                daftar2 =JSON.stringify(arrTempSPPT);

                $.ajax({ 
                type: "POST",
                url: "./view/PBB/monitoring_wilayah/svc-monitoring-dph.php",
                data: "q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&na=" + nama +"&jsonDaftar="+daftar2+"&simpan=1"+"&nameFile="+nameFile+ "&t1=" + tempo1 + "&t2=" + tempo2 + "&th=" + tahun +"&th2=" + tahun2 + "&n=" + nop + "&st=" + sts + "&kc=" + kc + "&kl=" + kl + "&tagihan=" + tagihan + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT + "&LBL_KEL=" + LBL_KEL,
                success: function (msg) {
                    if (msg=="true"){
                        alert("Data Berhasil Disimpan dengan nama : "+nameFile);
                    }else{
                        alert("gagal menyimpan : "+msg);
                    }
                   
                }
            });

            }
         }
         function simpanDaftar_(sts){ // sementara
            
            var tempo1      = $("#jatuh-tempo1-" + sts).val();
            var tempo2      = $("#jatuh-tempo2-" + sts).val();
            var tahun       = $("#tahun-pajak-" + sts).val();
            var tahun2      = $("#tahun-pajak2-" + sts).val();
            // alert(tahun);
            // alert(tahun2);
            var nop         = $("#nop-" + sts).val();
            var nama        = $("#wp-name-" + sts).val();
            var jmlBaris    = $("#jml-baris").val();
            var kc          = $("#kecamatan-" + sts).val();
            var kl          = $("#kelurahan-" + sts).val();
            var tagihan     = $("#src-tagihan-" + sts).val();
            var bank        = $("#bank-"+sts).val();


               // console.log(nameFile.trim());
                daftar2 =JSON.stringify(arrTempSPPT);
                $.ajax({ 
                type: "POST",
                url: "./view/PBB/monitoring_wilayah/svc-monitoring-dph.php",
                data: "q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&noDph="+NO_DPH_AKTIF+"&na=" + nama +"&jsonDaftar="+daftar2+"&simpan=1"+"&nameFile="+NAMA_FILE_AKTIF+ "&t1=" + tempo1 + "&t2=" + tempo2 + "&th=" + tahun +"&th2=" + tahun2 + "&n=" + nop + "&st=" + sts + "&kc=" + kc + "&kl=" + kl + "&tagihan=" + tagihan + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT + "&LBL_KEL=" + LBL_KEL,
                success: function (msg) {
                    if (msg=="true"){
                        alert("Data Berhasil Disimpan dengan nama : "+NAMA_FILE_AKTIF);
                    }else{
                        alert("gagal menyimpan : "+msg);
                    }
                   
                }
            });

        
         }
          function tambahDataDariFilter(sts) {
             $.ajax({ 
                type: "POST",
                url: "./view/PBB/monitoring_wilayah/svc-dph.php",
                data: "q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" +"&METHOD=" +"TAMBAH_TEMP" +"&NO_DPH='" + no1 + "'&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT ,
                success: function (msg) {
                   // console.log(msg);
                        if (msg==true || msg =="true"){
                             alert("Daftar berhasil ditambahkan");
                             tabelGroup.draw();
                        }else if (msg==2){
                            alert("Tidak ada data yang ditambahkan");
                        }else {
                            alert ("Terjadi Kesalahan pada sistem.");
                        }
                    }
                });
        }

        function onSubmitHarian(sts) {
            var tempo1      = $("#jatuh-tempo1-" + sts).val();
            var tempo2      = $("#jatuh-tempo2-" + sts).val();
            var tahun       = $("#tahun-pajak-" + sts).val();
            var tahun2      = $("#tahun-pajak2-" + sts).val();
            // alert(tahun);
            // alert(tahun2);
            var nop         = $("#nop-" + sts).val();
            var nama        = $("#wp-name-" + sts).val();
            var jmlBaris    = $("#jml-baris").val();
            var kc          = $("#kecamatan-" + sts).val();
            var kl          = $("#kelurahan-" + sts).val();
            var tagihan     = $("#src-tagihan-" + sts).val();
            var bank        = $("#bank-"+sts).val();
            $("#monitoring-content-" + sts).html("loading ...");
            var svc = "";
            //console.log("persiapan load data...");
            $("#monitoring-content-" + sts).load("view/PBB/monitoring_wilayah/svc-monitoring-dph.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>",
                    {noDph:NO_DPH_AKTIF,na: nama, t1: tempo1, t2: tempo2, th: tahun, th2: tahun2, n: nop, st: sts, kc: kc, kl: kl, tagihan: tagihan,bank:bank, GW_DBHOST: GW_DBHOST, GW_DBNAME: GW_DBNAME, GW_DBUSER: GW_DBUSER, GW_DBPWD: GW_DBPWD, GW_DBPORT: GW_DBPORT, LBL_KEL: LBL_KEL},
             function (response, status, xhr) {

                if (status == "error") {
                    var msg = "Sorry but there was an error: ";
                    $("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
                }else{
                     document.getElementById("carinop-7").addEventListener("change", myFunction, true);
                     document.getElementById("carinop-7").addEventListener("click", myClick, true);
                     document.getElementById("carinop-7").addEventListener('keyup', function(e) {
                      // console.log(e.keyCode);
                        if ((e.keyCode || e.which) == 13) {
                            //masukan ke tabel
                           // console.log(e.keyCode);
                            //alert("data berhasih ditambahkan");
                            // document.getElementById('carinop-7').value="";
                            // document.getElementById('cari-tahun-7').value=""
                            // document.getElementById('cari-nama-7').value="";
                            // document.getElementById('cari-desa-7').value="";
                            // document.getElementById('cari-kecamatan-7').value="";
                            // document.getElementById('cari-pbb-7').value="";
                            // document.getElementById('cari-denda-7').value="";
                            // document.getElementById('cari-total-7').value="";
			   //document.getElementById('carinop-7').value="";
                         }else if ((e.keyCode || e.which) == 32){
                            document.getElementById('carinop-7').value="";
                            document.getElementById('cari-tahun-7').value=""
                            document.getElementById('cari-nama-7').value="";
                            document.getElementById('cari-desa-7').value="";
                            document.getElementById('cari-kecamatan-7').value="";
                            document.getElementById('cari-pbb-7').value="";
                            document.getElementById('cari-denda-7').value="";
                            document.getElementById('cari-total-7').value="";
                         }
                    }, true);
                }
            });
        }

        function openDaftar(noDph,namaFile) {
            //console.log("ini open : "+noDph);
            NO_DPH_AKTIF    = noDph;
            NAMA_FILE_AKTIF = namaFile;
            x.style.display         = "block";
            content7.style.display  = "block";
            y.style.display         = "none";
            arrTempSPPT= [];
           // init(7);

            var sts         = 7;
            var tempo1      = $("#jatuh-tempo1-" + sts).val();
            var tempo2      = $("#jatuh-tempo2-" + sts).val();
            var tahun       = $("#tahun-pajak-" + sts).val();
            var tahun2      = $("#tahun-pajak2-" + sts).val();
            // alert(tahun);
            // alert(tahun2);
            var nop         = $("#nop-" + sts).val();
            var nama        = $("#wp-name-" + sts).val();
            var jmlBaris    = $("#jml-baris").val();
            var kc          = $("#kecamatan-" + sts).val();
            var kl          = $("#kelurahan-" + sts).val();
            var tagihan     = $("#src-tagihan-" + sts).val();
            var bank        = $("#bank-"+sts).val();
            $("#monitoring-content-" + sts).html("loading ...");
            var svc = "";
            //console.log("persiapan load data...");
            $("#monitoring-content-" + sts).load("view/PBB/monitoring_wilayah/svc-monitoring-dph.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>",
                    {edit:1,na: nama, t1: tempo1, t2: tempo2,noDph:noDph, th: tahun, th2: tahun2, n: nop, st: sts, kc: kc, kl: kl, tagihan: tagihan,bank:bank, GW_DBHOST: GW_DBHOST, GW_DBNAME: GW_DBNAME, GW_DBUSER: GW_DBUSER, GW_DBPWD: GW_DBPWD, GW_DBPORT: GW_DBPORT, LBL_KEL: LBL_KEL},
             function (response, status, xhr) {
                if (status == "error") {
                    var msg = "Sorry but there was an error: ";
                    $("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
                }else{
                     document.getElementById("carinop-7").addEventListener("change", myFunction, true);
                     document.getElementById("carinop-7").addEventListener("click", myClick, true);
                     document.getElementById("carinop-7").addEventListener('keyup', function(e) {
                        if ((e.keyCode || e.which) == 13) {
			    document.getElementById('carinop-7').value="";
                         }else if ((e.keyCode || e.which) == 32){
                            document.getElementById('carinop-7').value="";
                            document.getElementById('cari-tahun-7').value=""
                            document.getElementById('cari-nama-7').value="";
                            document.getElementById('cari-desa-7').value="";
                            document.getElementById('cari-kecamatan-7').value="";
                            document.getElementById('cari-pbb-7').value="";
                            document.getElementById('cari-denda-7').value="";
                            document.getElementById('cari-total-7').value="";
                         }
                    }, true);
                }
            });
        }

        function hapusDaftar(no1){
           // console.log(no1);
           var cnf = confirm("Apakah Anda Yakin ?");

            if (cnf == false) {
                console.log ("cancel");
            } else {
                $.ajax({ 
                type: "POST",
                url: "./view/PBB/monitoring_wilayah/svc-dph.php",
                data: "q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" +"&METHOD=" +"HAPUS" +"&NO_DPH='" + no1 + "'&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT ,
                success: function (msg) {
                   // console.log(msg);
                        if (msg==true || msg =="true"){
                             alert("Daftar berhasil dihapus ");
                             tabelGroup.draw();
                        }else{
                            alert("Hapus data gagal.");
                        }
                    }
                });
            }
        }

        function init(sts) {
            var tempo1      = $("#jatuh-tempo1-" + sts).val();
            var tempo2      = $("#jatuh-tempo2-" + sts).val();
            var tahun       = $("#tahun-pajak-" + sts).val();
            var tahun2      = $("#tahun-pajak2-" + sts).val();
            var nop         = 0;
            var nama        = $("#wp-name-" + sts).val();
            var jmlBaris    = $("#jml-baris").val();
            var kc          = $("#kecamatan-" + sts).val();
            var kl          = $("#kelurahan-" + sts).val();
            var tagihan     = $("#src-tagihan-" + sts).val();
            var bank        = $("#bank-"+sts).val();
            $("#monitoring-content-" + sts).html("loading ...");
            var svc = "";
            //console.log("persiapan load data...");
            $("#monitoring-content-" + sts).load("view/PBB/monitoring_wilayah/svc-monitoring-dph.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>",
                    {na: nama, t1: tempo1, t2: tempo2, th: tahun, th2: tahun2, n: nop, st: sts, kc: kc, kl: kl, tagihan: tagihan,bank:bank, GW_DBHOST: GW_DBHOST, GW_DBNAME: GW_DBNAME, GW_DBUSER: GW_DBUSER, GW_DBPWD: GW_DBPWD, GW_DBPORT: GW_DBPORT, LBL_KEL: LBL_KEL},
             function (response, status, xhr) {
                // console.log(response);
                if (status == "error") {
                    var msg = "Sorry but there was an error: ";
                    $("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
                }else{
                     document.getElementById("carinop-7").addEventListener("change", myFunction, true);
                     document.getElementById("carinop-7").addEventListener("click", myClick, true);
                     document.getElementById("carinop-7").addEventListener('keyup', function(e) {
                       //console.log(e.keyCode);
                        if ((e.keyCode || e.which) == 13) {
                            //masukan ke tabel
                            //console.log(e.keyCode);
			    document.getElementById('carinop-7').value="";

                         }else if ((e.keyCode || e.which) == 32){
                            document.getElementById('carinop-7').value="";
                            document.getElementById('cari-tahun-7').value=""
                            document.getElementById('cari-nama-7').value="";
                            document.getElementById('cari-desa-7').value="";
                            document.getElementById('cari-kecamatan-7').value="";
                            document.getElementById('cari-pbb-7').value="";
                            document.getElementById('cari-denda-7').value="";
                            document.getElementById('cari-total-7').value="";
                         }
                    }, true);

                }
               
            });

        }
        function myClick(){
                document.getElementById('carinop-7').value="";
                document.getElementById('cari-tahun-7').value=""
                document.getElementById('cari-nama-7').value="";
                document.getElementById('cari-desa-7').value="";
                document.getElementById('cari-kecamatan-7').value="";
                document.getElementById('cari-pbb-7').value="";
                document.getElementById('cari-denda-7').value=""; 
                document.getElementById('cari-total-7').value="";
        }
        function cekArray(nop){
             var status= true;

            arrTempSPPT.forEach(function(entry) {
                    if(entry['0']==nop){
                         status= false;
                }
            });
           return status;
        }

        function checkNop(nopCari,tahun) {
            var status= true;
           $.ajax({ 
                type: "POST",
                url: "./view/PBB/monitoring_wilayah/svc-dph.php",
                data: "q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" +"&METHOD=" +"CEK" +"&NO_DPH='" + NO_DPH_AKTIF +"'&NOP='"+nopCari+"'&TAHUN='"+tahun+ "'&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT ,
                success: function (msg) {
                        if (msg !="true"){ // jika true berarti nop tersebut tidak ada di dalam daftar
                            status= false; // false berarti nop terbut tidak bisa di masukan kedalam daftar
                            // console.log(msg);
                            // console.log("999999999999999999999");
                        }else{
                            status = true;
                            arrTempSPPT.forEach(function(entry) {
                                if(entry['0']==nopCari){
                                status= false;
                                }
                            });
                            // console.log(msg);
                            // console.log(status);
                            return status;
                        }
                    }
                });

        }


       function myFunction(){
            var sts = 7;
            var barcode = $("#carinop-7").val().trim().split("\\");
            var nop     = barcode[0];
            var tahun   = barcode[1];

            var nmfileAll       = '<?php echo date('yymdhmi'); ?>';
            var nmfile          = nmfileAll + '-part-';
            var tempo1          = $("#jatuh-tempo1-" + sts).val();
            var tempo2          = $("#jatuh-tempo2-" + sts).val();
           // var tahun           = $("#tahun-pajak-" + sts).val();
            var tahun2          =tahun;    // $("#tahun-pajak2-" + sts).val();
           // var nop             = $("#nop-" + sts).val();
            var nama            = $("#wp-name-" + sts).val();
            var jmlBaris        = $("#jml-baris").val();
            var kc              = $("#kecamatan-" + sts).val();
            var kl              = $("#kelurahan-" + sts).val();
            var tagihan         = $("#src-tagihan-" + sts).val();
            var bank            = $("#bank-" + sts).val();

            var totPokok        = parseInt($("#totpokok").text());
            var totDenda        = parseInt($("#totdenda").text());
            var totBayar        = parseInt($("#totbayar").text());

            // console.log(totPokok+"=="+totDenda+"=="+totBayar);
      
          // if(checkNop(nop,tahun)){
             if(cekArray(nop)){
                //cek apakah sudah ada di pbb detail ?
                $.ajax({ 
                type: "POST",
                url: "./view/PBB/monitoring_wilayah/svc-dph.php",
                data: "q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" +"&METHOD=" +"CEK" +"&NO_DPH='" + NO_DPH_AKTIF +"'&NOP='"+nop+"'&TAHUN='"+tahun+ "'&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT ,
                success: function (msg) {

                if (msg =="true"){ // jika true berarti nop tersebut tidak ada di dalam daftar

                $.ajax({
                type: "GET",
                url: "./view/PBB/monitoring_wilayah/svc-countforlink-dph.php",
                data: "q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&na=" + nama +"&barcode=1"+ "&t1=" + tempo1 + "&t2=" + tempo2 + "&th=" + tahun +"&th2=" + tahun2 + "&n=" + nop + "&st=" + sts + "&kc=" + kc + "&kl=" + kl + "&tagihan=" + tagihan + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT + "&LBL_KEL=" + LBL_KEL,
                success: function (response, status, xhr) {
                    if(response != 'false'){    
                        var x= JSON.parse(response);
                        arrTempSPPT.push(
                            x
                        );
                       // console.log(arrTempSPPT);
                        var table = document.getElementById("monitoring_table");
                        var row = table.insertRow(2);
                        var cell1 = row.insertCell(0);
                        var cell2 = row.insertCell(1);
                        var cell3 = row.insertCell(2);
                        var cell4 = row.insertCell(3);
                        var cell5 = row.insertCell(4);
                        var cell6 = row.insertCell(5);
                        var cell7 = row.insertCell(6);
                        var cell8 = row.insertCell(7);
                        cell1.innerHTML = x['0'];
                        cell2.innerHTML = x['1'];
                        cell3.innerHTML = x['2'];
                        cell4.innerHTML = x['3'];
                        cell5.innerHTML = x['4'];
                        cell6.innerHTML = x['5'];
                        cell7.innerHTML = x['6'];
                        cell8.innerHTML = x['7'];

                        document.getElementById('carinop-7').value=x['0'];
                        document.getElementById('cari-tahun-7').value=x['1'];
                        document.getElementById('cari-nama-7').value=x['2'];
                        document.getElementById('cari-desa-7').value=x['3'];
                        document.getElementById('cari-kecamatan-7').value=x['4'];
                        document.getElementById('cari-pbb-7').value=x['5'];
                        document.getElementById('cari-denda-7').value=x['6'];
                        document.getElementById('cari-total-7').value=x['7'];

                        document.getElementById('totpokok').innerHTML="<b>"+(totPokok+parseInt(x['5']))+"</b>";
                        document.getElementById('totdenda').innerHTML="<b>"+(totDenda+parseInt(x['6']))+"</b>";
                        document.getElementById('totbayar').innerHTML="<b>"+(totBayar+parseInt(x['7']))+"</b>"; 
                        myClick();
                    }else{
                         document.getElementById('carinop-7').value="";
                         document.getElementById('cari-tahun-7').value=""
                         document.getElementById('cari-nama-7').value="";
                         document.getElementById('cari-desa-7').value="";
                         document.getElementById('cari-kecamatan-7').value="";
                         document.getElementById('cari-pbb-7').value="";
                         document.getElementById('cari-denda-7').value="";
                         document.getElementById('cari-total-7').value="";
                        alert("Maaf Data Tidak Ditemukan");
                    }
          
                }
            });
                        }else{
                           alert("Data sudah ada dalam daftar");
                        }
                    }
                });

                
                }else{
                    alert("Data sudah ada dalam daftar");
                }
            
        }

        function toExcelHarian(sts) {
           // console.log(arrTempSPPT);

            var nmfileAll       = '<?php echo date('yymdhmi'); ?>';
            var nmfile          = nmfileAll + '-part-';
            var tempo1          = $("#jatuh-tempo1-" + sts).val();
            var tempo2          = $("#jatuh-tempo2-" + sts).val();
            var tahun           = $("#tahun-pajak-" + sts).val();
            var tahun2          = $("#tahun-pajak2-" + sts).val();
            var nop             = $("#nop-" + sts).val();
            var nama            = $("#wp-name-" + sts).val();
            var jmlBaris        = $("#jml-baris").val();
            var kc              = $("#kecamatan-" + sts).val();
            var kl              = $("#kelurahan-" + sts).val();
            var tagihan         = $("#src-tagihan-" + sts).val();
            var bank            = $("#bank-" + sts).val();

            if (sts == 1)
                $("#loadlink7").show();
            else
                $("#loadlink2").show();

            $.ajax({
                type: "POST",
                url: "./view/PBB/monitoring_wilayah/svc-countforlink-dph.php",
                data: "q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&na=" + nama + "&t1=" + tempo1 + "&t2=" + tempo2 + "&th=" + tahun +"&th2=" + tahun2 + "&n=" + nop + "&st=" + sts + "&kc=" + kc + "&kl=" + kl + "&tagihan=" + tagihan + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT + "&LBL_KEL=" + LBL_KEL,
                success: function (msg) {
                    // console.log("ini ----------------------------------------");
                    // console.log(msg);
                    var sumOfPage = Math.ceil(msg / 10000);
                    var strOfLink = "";
                    if (msg < 10000)
                        strOfLink += '<a href="view/PBB/monitoring_wilayah/svc-toexcel-dph.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' + '&noDph=' + NO_DPH_AKTIF +'&na=' + nama + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th=' + tahun +"&th2=" + tahun2 + '&n=' + nop + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + '&tagihan=' + tagihan + '&bank=' + bank + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT + "&LBL_KEL=" + LBL_KEL + '&p=all&total=' + msg + '">' + nmfileAll + '</a><br/>';
                    else {
                        for (var page = 1; page <= sumOfPage; page++) {
                            strOfLink += '<a href="view/PBB/monitoring_wilayah/svc-toexcel-dph.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' +'&noDph=' + NO_DPH_AKTIF + '&na=' + nama + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th=' + tahun +"&th2=" + tahun2 + '&n=' + nop + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + '&tagihan=' + tagihan + '&bank=' + bank + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT + "&LBL_KEL=" + LBL_KEL + '&p=' + page + '">' + nmfile + page + '</a><br/>';
                        }
                    }
                    $("#contentLink").html(strOfLink);
                    $("#cBox").css("display", "block");

                    if (sts == 1)
                        $("#loadlink7").hide();
                    else
                        $("#loadlink2").hide();
                }
            });
        }

         function toPdfHarian(sts) {
           // console.log(arrTempSPPT);

            var nmfileAll       = '<?php echo date('yymdhmi'); ?>';
            var nmfile          = nmfileAll + '-part-';
            var tempo1          = $("#jatuh-tempo1-" + sts).val();
            var tempo2          = $("#jatuh-tempo2-" + sts).val();
            var tahun           = $("#tahun-pajak-" + sts).val();
            var tahun2          = $("#tahun-pajak2-" + sts).val();
            var nop             = $("#nop-" + sts).val();
            var nama            = $("#wp-name-" + sts).val();
            var jmlBaris        = $("#jml-baris").val();
            var kc              = $("#kecamatan-" + sts).val();
            var kl              = $("#kelurahan-" + sts).val();
            var tagihan         = $("#src-tagihan-" + sts).val();
            var bank            = $("#bank-" + sts).val();

            if (sts == 1)
                $("#loadlink7").show();
            else
                $("#loadlink2").show();

            $.ajax({
                type: "POST",
                url: "./view/PBB/monitoring_wilayah/svc-countforlink-dph.php",
                data: "q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>" + "&na=" + nama + "&t1=" + tempo1 + "&t2=" + tempo2 + "&th=" + tahun +"&th2=" + tahun2 + "&n=" + nop + "&st=" + sts + "&kc=" + kc + "&kl=" + kl + "&tagihan=" + tagihan + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT + "&LBL_KEL=" + LBL_KEL,
                success: function (msg) {
                    // console.log("ini ----------------------------------------");
                    // console.log(msg);
                    var sumOfPage = Math.ceil(msg / 10000);
                    var strOfLink = "";
                    if (msg < 10000)
                        strOfLink += '<a href="view/PBB/monitoring_wilayah/svc-topdf1-dph.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' +'&noDph=' + NO_DPH_AKTIF + '&na=' + nama + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th=' + tahun +"&th2=" + tahun2 + '&n=' + nop + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + '&tagihan=' + tagihan + '&bank=' + bank + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT + "&LBL_KEL=" + LBL_KEL + '&p=all&total=' + msg + '">' + nmfileAll + '</a><br/>';
                    else {
                        for (var page = 1; page <= sumOfPage; page++) {
                            strOfLink += '<a href="view/PBB/monitoring_wilayah/svc-topdf1-dph.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid'}"); ?>' +'&noDph=' + NO_DPH_AKTIF + '&na=' + nama + '&t1=' + tempo1 + '&t2=' + tempo2 + '&th=' + tahun +"&th2=" + tahun2 + '&n=' + nop + '&st=' + sts + '&kc=' + kc + '&kl=' + kl + '&tagihan=' + tagihan + '&bank=' + bank + "&GW_DBHOST=" + GW_DBHOST + "&GW_DBNAME=" + GW_DBNAME + "&GW_DBUSER=" + GW_DBUSER + "&GW_DBPWD=" + GW_DBPWD + "&GW_DBPORT=" + GW_DBPORT + "&LBL_KEL=" + LBL_KEL + '&p=' + page + '">' + nmfile + page + '</a><br/>';
                        }
                    }
                    $("#contentLink").html(strOfLink);
                    $("#cBox").css("display", "block");

                    if (sts == 1)
                        $("#loadlink7").hide();
                    else
                        $("#loadlink2").hide();
                }
            });
        }

        function toPdf(sts){

            var nmfileAll       = '<?php echo date('yymdhmi'); ?>';
            var nmfile          = nmfileAll + '-part-';
            var tempo1          = $("#jatuh-tempo1-" + sts).val();
            var tempo2          = $("#jatuh-tempo2-" + sts).val();
            var tahun           = $("#tahun-pajak-" + sts).val();
            var tahun2          = $("#tahun-pajak2-" + sts).val();
            var nop             = $("#nop-" + sts).val();
            var nama            = $("#wp-name-" + sts).val();
            var jmlBaris        = $("#jml-baris").val();
            var kc              = $("#kecamatan-" + sts).val();
            var kl              = $("#kelurahan-" + sts).val();
            var tagihan         = $("#src-tagihan-" + sts).val();
            var bank            = $("#bank-" + sts).val();   

            var el              = document.getElementById('kelurahan-7');
            var kl_text         = el.options[el.selectedIndex].innerHTML;
            var buku            = document.getElementById('src-tagihan-7');
            var buku_text       = buku.options[buku.selectedIndex].innerHTML;
                 
  var postData = { 
            q           : '<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'23','uid':'$uid','u':'$data->uname'}"); ?>',
            na          : nama ,
            t1          : tempo1,
            t2          : tempo2,
            th          : tahun,
            th2         : tahun2,
            n           : nop,
            st          : sts,
            kc          : kc ,
            kl          : kl ,
            tagihan     : tagihan ,
            bank        : bank,
            GW_DBHOST   : GW_DBHOST,
            GW_DBNAME   : GW_DBNAME,
            GW_DBUSER   : GW_DBUSER,
            GW_DBPWD    : GW_DBPWD,
            GW_DBPORT   : GW_DBPORT,
            LBL_KEL     : LBL_KEL,
            p           : 'all',
            kl_text     : kl_text,
            buku_text   : buku_text
           // dataTable   : arrTempSPPT
           // total       : msg
        };

        post('view/PBB/monitoring_wilayah/svc-topdf-dph.php', postData);
    
    }
    

    function post(path, params, method) {
        method = method || "post";
        var target = '_blank';
        var form = document.createElement("form");
        form.setAttribute("method", method);
        form.setAttribute("action", path);
        form.setAttribute("target", target);

        for(var key in params) {
            if(params.hasOwnProperty(key)) {
                var hiddenField = document.createElement("input");
                hiddenField.setAttribute("type", "hidden");
                hiddenField.setAttribute("name", key);
                hiddenField.setAttribute("value", params[key]);
                form.appendChild(hiddenField);
            }
        }
        document.body.appendChild(form);
        form.submit();
    }
</script>
