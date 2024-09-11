<?php
$uid = $data->uid;

// get module
$bOK = $User->GetModuleInArea($uid, $area, $moduleIds);
$idRole = $User->GetUserRole($uid, $application);

// rm1


$appConfig = $User->GetAppConfig($application);
$NBParam = base64_encode('{"ServerAddress":"' . $appConfig['TPB_ADDRESS'] . '","ServerPort":"' . $appConfig['TPB_PORT'] . '","ServerTimeOut":"' . $appConfig['TPB_TIMEOUT'] . '"}');

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR)) error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

function getAreaKecamatan($uid)
{
    global $DBLink, $idRole;
    // rmKelurahan ,rmKecamatan
    $query = " ";
    if ($idRole == "rmKelurahan") {
        $query = "SELECT CPC_TKL_KCID
    FROM tbl_reg_user_pbb  INNER JOIN cppmod_tax_kelurahan KL ON KL.CPC_TKL_ID = tbl_reg_user_pbb.kelurahan 
    WHERE ctr_u_id = '$uid'
     ";
        // echo "$query";
        // exit;
        $data = mysqli_query($DBLink, $query) or die(mysqli_error($DBLink));
        $row = mysqli_fetch_assoc($data);
        $KEC = $row['CPC_TKL_KCID'];
        return $KEC;
    } else if ($idRole == "rmKecamatan") {
        $query = "SELECT CPC_TKC_ID 
    FROM tbl_reg_user_pbb  INNER JOIN cppmod_tax_kecamatan KC ON KC.CPC_TKC_ID = tbl_reg_user_pbb.kecamatan 
    WHERE ctr_u_id = '$uid'
     ";
        $data = mysqli_query($DBLink, $query) or die(mysqli_error($DBLink));
        $row = mysqli_fetch_assoc($data);
        $KEC = $row['CPC_TKC_ID'];
        return $KEC;
    } else { // jika role nya admin
        $query = "SELECT CPC_TKC_ID 
    FROM tbl_reg_user_pbb  INNER JOIN cppmod_tax_kecamatan KC ON KC.CPC_TKC_ID = tbl_reg_user_pbb.kecamatan 
#    WHERE ctr_u_id = '$uid'
     ";
        $data = mysqli_query($DBLink, $query) or die(mysqli_error($DBLink));
        $row = mysqli_fetch_assoc($data);
        $KEC = $row['CPC_TKC_ID'];
        return $KEC;
        //    $query = "SELECT CPC_TKL_ID kode,CPC_TKL_KELURAHAN nama
        //    FROM  cppmod_tax_kelurahan KL
        // #   WHERE CPC_TKL_KCID = '$KEC'
        //     ";

    }
    // echo $query;
    // exit;
    // $data = mysqli_query($DBLink, $query) or die(mysqli_error($DBLink));
    // if ($data){
    //   $array = array();
    //   $i = 0;
    //   while ($row = mysqli_fetch_assoc($data) ){
    //     $array[$i]["kode"] = $row['kode'];
    //     $array[$i]["nama"] = $row['nama'];
    //     $i++;
    //   }
    //   return $array;
    // }else{
    //   return false;
    // }

}

function getAreaPajak($uid)
{
    global $DBLink, $idRole;
    // rmKelurahan ,rmKecamatan
    $query = " ";
    if ($idRole == "rmKelurahan") {
        $query = "SELECT CPC_TKL_ID kode,CPC_TKL_KELURAHAN nama 
    FROM tbl_reg_user_pbb  INNER JOIN cppmod_tax_kelurahan KL ON KL.CPC_TKL_ID = tbl_reg_user_pbb.kelurahan 
    WHERE ctr_u_id = '$uid'
     ";
    } else if ($idRole == "rmKecamatan") {
        $query = "SELECT CPC_TKC_ID 
    FROM tbl_reg_user_pbb  INNER JOIN cppmod_tax_kecamatan KC ON KC.CPC_TKC_ID = tbl_reg_user_pbb.kecamatan 
    WHERE ctr_u_id = '$uid'
     ";
        $data = mysqli_query($DBLink, $query) or die(mysqli_error($DBLink));
        $row = mysqli_fetch_assoc($data);
        $KEC = $row['CPC_TKC_ID'];
        $query = "SELECT CPC_TKL_ID kode,CPC_TKL_KELURAHAN nama 
    FROM  cppmod_tax_kelurahan KL
    WHERE CPC_TKL_KCID = '$KEC'
     ";
    } else { // jika role nya admin
        $query = "SELECT CPC_TKC_ID 
    FROM tbl_reg_user_pbb  INNER JOIN cppmod_tax_kecamatan KC ON KC.CPC_TKC_ID = tbl_reg_user_pbb.kecamatan 
#    WHERE ctr_u_id = '$uid'
     ";
        $data = mysqli_query($DBLink, $query) or die(mysqli_error($DBLink));
        $row = mysqli_fetch_assoc($data);
        $KEC = $row['CPC_TKC_ID'];
        $query = "SELECT CPC_TKL_ID kode,CPC_TKL_KELURAHAN nama 
    FROM  cppmod_tax_kelurahan KL
 #   WHERE CPC_TKL_KCID = '$KEC'
     ";

        // echo "Anda tidak memiliki akses untuk modul ini";
        // exit;

    }
    // echo $query;
    // exit;
    $data = mysqli_query($DBLink, $query) or die(mysqli_error($DBLink));
    if ($data) {
        $array = array();
        $i = 0;
        while ($row = mysqli_fetch_assoc($data)) {
            $array[$i]["kode"] = $row['kode'];
            $array[$i]["nama"] = $row['nama'];
            $i++;
        }
        return $array;
    } else {
        return false;
    }
}
$myKelurahan = getAreaPajak($uid);
$myKecamatan = getAreaKecamatan($uid);

// get status
function get_group_status()
{
    global $DBLink, $appConfig;
    $sql = "SELECT * FROM {$appConfig['GW_DBNAME']}.cppmod_collective_group_status";
    $query = mysqli_query($DBLink, $sql);

    return mysqli_fetch_all($query, MYSQLI_ASSOC);
}

?>
<!-- font awesome -->
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">

<script type="text/javascript" src="view/PBB/pembayaran_va/dataTables/jquery.dataTables.js?v.0.0.1"></script>

<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.1.2/js/dataTables.buttons.min.js"></script>
<style type="text/css">
    .fa {
        cursor: pointer;
    }

    .btn-close,
    .btn-close-independen {
        position: absolute;
        top: 10px;
        right: 10px;
        color: black;
    }

    #form-add-user-group,
    #form-view-failed,
    #form-add-group,
    .modal-base {
        display: none;
        width: 1050px;
        height: 600px;
        border: 2px solid black;
        position: fixed;
        top: 0px;
        left: 0px;
        right: 0px;
        bottom: 0px;
        margin: auto;
        background-color: #f7f7f7;
        z-index: 999;
        padding: 15px;
        overflow-x: auto;

    }

    .list-nop-gagal {
        z-index: 9999;
        width: 100%;
        height: 100%;
        max-width: 499px;
        max-height: 399px;
    }


    /* #form-add-group {
    display: none;
    width: 500px;
    height: 410px;
    border: 2px solid black;
    position: fixed;
    top: 0px;
    left: 0px;
    right: 0px;
    bottom: 0px;
    margin: auto;
    background-color: white;
    z-index: 999999999999999;
    padding: 15px;
    /*overflow-x:auto; 
    }

    */ #full {
        width: 100%;
        height: 100%;
        z-index: 9;
        background-color: rgba(0, 0, 0, 0.6);
        left: 0px;
        top: 0px;
        position: fixed;
        display: none;
    }

    .loader {
        display: none;
    }

    .loader img {
        width: 80px;
        z-index: 99999;
        height: 80px;
        position: fixed;
        top: 0px;
        left: 0px;
        right: 0px;
        bottom: 0px;
        margin: auto;
    }

    .table-success,
    .table-success>td,
    .table-success>th {
        background-color: #c3e6cb;
    }

    .table-danger,
    .table-danger>td,
    .table-danger>th {
        background-color: #f5c6cb;
    }
</style>

<div class="col-md-12">
    <div class="modal-base list-nop-gagal">
        <h1>NOP yang gagal (<span class="list-nop-gagal-total">0</span>)</h1>
        <a href="#" class="btn-close-independen" onclick="event.preventDefault();$('.list-nop-gagal').fadeOut()">
            <i class="fa fa-2x fa-times"></i>
        </a>
        <br>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>NOP</th>
                    <th>Alasan</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
    <div id="form-view-failed">
        <h1>Daftar NOP Gagal Di Finalkan <span id="form-group-name-2" style="font-weight: bolder;"></span></h1>
        <a href="#" class="btn-close">
            <i class="fa fa-2x fa-times"></i>
        </a>
        </br>

        <br>
        <table id="myTableMemberFailed" class="dataTable table table-striped no-footer">
            <thead>

            </thead>
            <tbody id="bodi-nop"></tbody>
        </table>
    </div>
    <!-- end group form -->
    <div id="form-add-user-group">
        <h2 style="width:unset">Tambah NOP Pada Group <span id="form-group-name" style="font-weight: bolder;"></span></h2>
        <a href="#" class="btn-close">
            <i class="fa fa-2x fa-times"></i>
        </a>
        </br>
        <fieldset>
            <legend>Tambah Ke Group</legend>
            <form id="form-cari">
                <table cellpadding="10" border="0" style="width: 100%">
                    <input type="hidden" name="userID" id="userID" value="<?php echo $uid; ?>" />
                    <input type="hidden" name="data-group-id" id="data-group-id" />
                    <input type="hidden" name="data-group-name" id="data-group-name" />
                    <tr class="section-table">
                        <td>NOP</td>
                        <td>
                            <!-- <input class="form-control" type="text" name="data-nop" id="data-nop" style="margin-bottom:10px;width: 100%"> -->
                            <textarea name="data-nop" id="data-nop" cols="20" rows="7" class="form-control" style="width: 100%"></textarea>
                            <small style="margin-bottom:10px;">Pisahkan NOP dengan tanda koma (,) jika ingin memasukkan banyak NOP sekaligus kedalam grup ini.</small>
                        </td>



                        <td>Kelurahan/Desa</td>
                        <td>
                            <div id="td-kel-des"></div>
                            <select class="form-control" name="data-kelurahan" id="data-kelurahan" style="margin-bottom:10px;display: none;">
                                <?php foreach ($myKelurahan as $key => $value) { ?>
                                    <option value="<?php echo $value['kode']; ?>"><?php echo $value['nama'] ?></option>
                                <?php
                                } ?>
                            </select>
                        </td>

                    </tr>
                    <tr class="section-table">
                        <td>Tahun Pajak</td>
                        <td><input class="form-control" type="text" name="data-tahun-pajak" id="data-tahun-pajak" value="<?php echo $appConfig['tahun_tagihan'] ?>"></td>

                        <td>Buku</td>
                        <td>
                            <select class="form-control" id="data-buku" name="data-buku">
                                <?php if ($idRole == "rmKecamatan") { ?>
                                    <option value="1">Buku 1</option>
                                    <option value="12">Buku 1,2</option>
                                    <option value="123">Buku 1,2,3</option>

                                <?php
                                } else { ?>
                                    <option value="1">Buku 1</option>
                                    <option value="12">Buku 1,2</option>
                                    <option value="123">Buku 1,2,3</option>

                                    <option value="1234">Buku 1,2,3,4</option>
                                    <option value="12345">Buku 1,2,3,4,5</option>
                                    <option value="2">Buku 2</option>
                                    <option value="23">Buku 2,3</option>
                                    <option value="234">Buku 2,3,4</option>
                                    <option value="2345">Buku 2,3,4,5</option>
                                    <option value="3">Buku 3</option>
                                    <option value="34">Buku 3,4</option>
                                    <option value="345">Buku 3,4,5</option>
                                    <option value="4">Buku 4</option>
                                    <option value="45">Buku 4,5</option>
                                    <option value="5">Buku 5</option>
                                <?php
                                } ?>
                            </select>
                        </td>

                    </tr>
                    <tr>
                        <td colspan="4">
                            <button type="button" id="btn-tambah-cari-nop" class="btn btn-primary bg-maka" value="Cari & Tambah ke Draft" onclick="cariNOP()">Cari & Tambah ke Draft</button>
                            <button type="button" class="btn-close-group btn btn-primary bg-maka" value="Kembali Ke Group">Kembali Ke Group</button>
                            <button type="button" class="btn btn-primary bg-maka" id="btn-finalkan" value="Finalkan" onclick="finalkan()">Finalkan</button>

                            <!--   <input type="button" onclick="pdfListMember($('#data-group-id').val())" name="cetak-list-member" id="cetak-list-member" value="Cetak PDF "> -->
                            <button type="button" class="btn btn-primary bg-maka" onclick="excelListMember($('#data-group-id').val())" name="cetak-list-member-excel" id="cetak-list-member-excel" value="Cetak Excel">Cetak Excel</button>
                            <button type="button" class="btn btn-primary bg-maka btn-orange" onclick="csvListMember($('#data-group-id').val())" name="cetak-list-member-csv" id="cetak-list-member-csv" value="Cetak CSV">Cetak CSV</button>

                            <div style="width:10em;display:inline-block">
                                <select id="member-status-bayar" class="form-control" onchange="onChangeFilterMemberStatusBayar(this)">
                                    <option value="">Semua</option>
                                    <option value="1">Lunas</option>
                                    <option value="0">Belum Lunas</option>
                                </select>
                            </div>
                        </td>
                        <td></td>
                        <td style="text-align: right;">

                        </td>

                    </tr>

                </table>
            </form>
        </fieldset>
        <br>
        <button id="btn-delete-all" class="btn-delete-place btn btn-primary bg-maka" type="button" value="Hapus Data Terpilih" style="margin-bottom: 10px;">Hapus Data Terpilih</button>
        <button id="btn-check-all" class="btn-delete-place btn btn-primary bg-maka" type="button" value="Pilih Semua Data" style="margin-bottom: 10px;">Pilih Semua Data</button>

        <div class="table-responsive">
            <table id="myTableMember" class="dataTable table table-striped no-footer">
                <thead>

                </thead>
                <tbody id="bodi-nop"></tbody>
                <tfoot>
                    <tr>
                        <th colspan="10" style="text-align:right">Total:</th>
                        <!-- <th></th> -->
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <!-- end group form -->


    <div id="form-add-group" style="height: 500px;">
        <h2 id="form-add-group-fungsi">Tambah Group</h2>
        <a href="#" class="btn-close">
            <i class="fa fa-2x fa-times"></i>
        </a>
        </br>
        <fieldset>
            <legend>Informasi Group</legend>
            <form id="form-tambah-group">
                <input type="hidden" name="userID" id="userID" value="<?php echo $uid; ?>" />
                <input type="hidden" name="data-edit-group-id" id="data-edit-group-id">
                <table cellpadding="10" style="width: 100%">
                    <tr style="display: none;">
                        <td>ID Group</td>
                        <td><input class="form-control" style="margin-bottom:10px;text-transform:uppercase" type="text" placeholder="ID Group" name="data-id-group" id="data-id-group">
                        </td>
                    </tr>
                    <tr>
                        <td>Nama Group</td>
                        <td><input class="form-control" style="margin-bottom:10px;text-transform:uppercase;width: 100%" type="text" placeholder="Nama Group" name="data-nama" id="data-nama-group">
                        </td>
                    </tr>
                    <tr>
                        <td>Keterangan</td>
                        <td><textarea class="form-control" style="margin-bottom:10px;text-transform:uppercase;width: 100%" placeholder="Keterangan" type="text" name="data-keterangan" id="data-keterangan-group"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>Kolektor</td>
                        <td><input class="form-control" style="margin-bottom:10px;text-transform:uppercase;width: 100%" placeholder="Nama Kolektor" type="text" name="data-nama-kolektor" id="data-nama-kolektor">
                        </td>
                    </tr>
                    <tr>
                        <td>No HP Kolektor</td>
                        <td><input class="form-control" style="margin-bottom:10px;text-transform:uppercase;width: 100%" placeholder="Nomor Handphone Kolektor" type="text" name="data-no-kolektor" id="data-no-kolektor">
                        </td>
                    </tr>
                    <!--
       -->

                    <?php
                    // if ($idRole!="rmKelurahan"){

                    ?>
                    <tr>
                        <td>Kecamatan</td>
                        <td>
                            <label id="label-data-kecamatan-group"></label>

                            <select class="form-control" onchange="showKelurahanGroup(this.value)" style="margin-bottom:10px;text-transform:uppercase" name="data-kecamatan-group" id="data-kecamatan-group"></select>
                        </td>
                    </tr>
                    <?php //}

                    ?>
                    <tr>
                        <td>Kelurahan</td>
                        <td>
                            <label id="label-data-kelurahan-group"></label>
                            <select class="form-control" style="margin-bottom:10px;text-transform:uppercase;width: 100%" name="data-kelurahan-group" id="data-kelurahan-group-2">
                                <?php foreach ($myKelurahan as $key => $value) { ?>
                                    <option value="<?php echo $value['kode']; ?>"><?php echo $value['nama'] ?></option>
                                <?php
                                } ?>


                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button id="submit-group-form" fungsi="simpan" type="button" name="submit" value="Simpan" onclick="tambahGroup()" class="btn btn-primary bg-maka">Simpan</button>
                            <button type="button" class="btn-close-group-form btn btn-primary bg-maka" value="Kembali Ke Group">Kembali Ke Group</button>
                        </td>
                    </tr>
                </table>
            </form>
        </fieldset>

        <br>

    </div>
    <!-- end group form -->
    <div id="full">
    </div>
    <div class="loader">
        <img src="view/PBB/pembayaran_va/animated_spinner.gif">
    </div>

    <br>
    <button type="button" name="btn-tambah-group" id="btn-tambah-group" value="Tambah Group" class="btn btn-primary btn-orange">Tambah Group</button>

    <select class="form-control" id="cbKecamatan" style="width:15em;display:inline-block;margin-left:1em" onchange="showKelurahanToEl(this.value, document.getElementById('cbKelurahan'))">
        <option value="">Semua Kecamatan</option>
    </select>
    <select class="form-control" id="cbKelurahan" style="width:15em;display:inline-block;margin-left:1em">
        <option value="">Semua Kelurahan</option>
    </select>
    <select class="form-control" id="cbStatus" style="width:10em;display:inline-block;margin-left:1em">
        <option value="">Semua Status</option>
        <?php foreach (get_group_status() as $k) : ?>
            <option value="<?= $k['ID'] ?>"><?= $k['STATUS_NAME'] ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" class="form-control" id="txSearch" style="width:15em;display:inline-block;margin-left:1em" placeholder="Global Search">
    <button type="button" name="btn-search-group" id="btn-search-group" value="Cari" class="btn btn-primary btn-blue" style="display:inline-block;margin-left:1em">Cari</button>


    <div id="data-collective" class="data-collective">
    </div>
    <br>
    <div class="table-responsive">
        <table cellpadding="0" cellspacing="0" border="0" class="dataTable table table-striped w-100" id="myTable">
        </table>
    </div>
</div>
<script type="text/javascript">
    function resetSearchNOPList() {
        $("#myTableMember").parents(".dataTables_wrapper").find("input[type=search]").val("").trigger("keyup");
    }


    $(document).ready(function() {
        //  $.ajaxSetup({
        //   success: function(result){
        //     $("#full").hide();
        //   }
        // });
        // reloadDataGroup();
        $('#myTable').css('width','100%');

        // $(document).on("submit","#form-cari",function(e){
        //    e.preventDefault();
        //    // alert("1243");
        // });

        $(document).on("click", "#btn-check-all", function(e) {
            resetSearchNOPList();
            $(".nop-member").each(function(e) {
                if ($(this).is(':checked')) {
                    $(this).removeAttr("checked");
                } else {
                    $(this).attr("checked", true);
                }

            });
        });
        $(document).on("click", "#btn-delete-all", function(e) {
            resetSearchNOPList()
            // var ck = "<input type='checkbox' id='btn-check-all' />"
            // $("#myTableMember").find("thead").find("tr").find("th").eq(0).append(ck);
            var data = [];
            $(".nop-member").each(function(e) {
                if ($(this).is(':checked')) {
                    // var arr = [];
                    var nop = $(this).attr("value");
                    var year = $(this).attr("year");
                    // arr.push()
                    data.push({
                        "nop": nop,
                        "tahun": year
                    });
                }
            });
            var jml = data.length;
            if (jml <= 0) {
                alert(" Silahkan pilih data terlebih dahulu");
                return false;
                exit;

            }
            var c = confirm("Yakin " + jml + " Data Terpilih ? ");
            if (!c) {
                return false;
            }

            var request = $.ajax({
                url: "view/PBB/pembayaran_va/setDeleteAll.php",
                type: "POST",
                data: {
                    data: data
                },
                dataType: "json",
                success: function(data) {
                    if (data.success) {
                        var idd = $("#data-group-id").val();
                        getGroupData(idd, 0);
                    }
                    // alert(JSON.stringify(data));

                }
            });
        });
        $(document).on("keypress", "#data-nop", function(e) {
            var code = e.keyCode || e.which;
            var nilai_nop = $(this).val();
            if (code == 13) { //Enter keycode
                if (nilai_nop.length > 18) {
                    var potong = nilai_nop.substr(0, 18);
                    var potong_tahun = nilai_nop.substr(19, 4);
                    // alert(potong_tahun);
                    // exit;
                    $("#data-nop").val(potong);
                    $("#data-tahun-pajak").val(potong_tahun);
                    cariNOP();
                } else {
                    cariNOP();

                }
            }

        });
        $(document).on("click", ".btn-return", function(e) {
            var group_id = $(this).attr("group-id");
            var c = confirm("Yakin mengubah status group ini menjadi draft   ? ");
            if (!c) {
                return false;
            }

            var request = $.ajax({
                url: "view/PBB/pembayaran_va/setActive.php",
                type: "POST",
                data: "id=" + group_id,
                dataType: "json",
                success: function(data) {
                    // alert(data);
                    if (data) {

                        // myTable.destroy();
                        reloadDataGroup();
                    } else {
                        alert(data);
                    }
                    // $(".baris").eq(index).remove();

                }
            });

        });
        $(document).on("click", ".btn-reaktivasi", function(e) {
            var group_id = $(this).attr("group-id");
            var c = confirm("Yakin Reaktivasi Group ini  ? ");
            if (!c) {
                return false;
            }

            var request = $.ajax({
                url: "view/PBB/pembayaran_va/setActive.php",
                type: "POST",
                data: "id=" + group_id,
                dataType: "json",
                success: function(data) {
                    // alert(data);
                    if (data) {

                        // myTable.destroy();
                        reloadDataGroup();
                    } else {
                        alert(data);
                    }
                    // $(".baris").eq(index).remove();

                }
            });

        });
        $(document).on("click", ".btn-view-failed", function(e) {
            $("#full").fadeIn();
            var group_id = $(this).attr("group-id");
            $("#form-view-failed").show();
            if (myTableMember) {
                myTableMember.destroy();
            }
            myTableMember = $('#myTableMemberFailed').DataTable({
                "paging": false,
                "processing": true,
                "serverSide": true,
                "ajax": "view/PBB/pembayaran_va/getDataMemberFailed.php?status=" + status + "&group_id=" + group_id,
                columns: [
                    {
                        title: "Aksi",
                        "searchable": false,
                        "type": "html",
                        "index": 0,
                        'name': ''
                    },
                    {
                        title: "NOP",
                        "searchable": true,
                        "type": "html",
                        "index": 1,
                        'name': 'NOP'
                    },
                    {
                        title: "Tahun Pajak",
                        "searchable": true,
                        "type": "html",
                        "index": 2,
                        'name': 'SPPT_TAHUN_PAJAK'
                    },
                    {
                        title: "Tanggal Jatuh Tempo",
                        "searchable": false,
                        "type": "html",
                        "index": 3,
                        'name': 'SPPT_TANGGAL_JATUH_TEMPO'
                    },
                    {
                        title: "Nama WP",
                        "searchable": true,
                        "type": "html",
                        "index": 4,
                        'name': 'WP_NAMA'
                    },
                    {
                        title: "Kecamatan",
                        "searchable": true,
                        "type": "html",
                        "index": 5,
                        'name': 'OP_KECAMATAN'
                    },
                    {
                        title: "Kelurahan",
                        "searchable": true,
                        "type": "html",
                        "index": 6,
                        'name': 'OP_KELURAHAN'
                    },
                    {
                        title: "Pokok",
                        "searchable": true,
                        "type": "html",
                        "index": 7,
                        'name': 'SPPT_PBB_HARUS_DIBAYAR'
                    },
                    {
                        title: "Denda",
                        "searchable": false,
                        "type": "html",
                        "index": 8,
                        'name': 'test'
                    },
                    {
                        title: "Total",
                        "searchable": false,
                        "type": "html",
                        "index": 9,
                        'name': 'test'
                    },
                    {
                        title: "Status Bayar",
                        "searchable": false,
                        "type": "html",
                        "index": 10,
                        'name': 'test'
                    },
                    {
                        title: "Tanggal Bayar",
                        "searchable": false,
                        "type": "html",
                        "index": 11,
                        'name': 'test'
                    }
                ]



            });
            // alert(group_id);
        });
        $(document).on("click", ".btn-cetak-info-group", function(e) {
            var group_id = $(this).attr("group-id");
            pdfGroupInfo(group_id);
        });
        // $(document).on("click",".btn-delete-temp",function(e){
        //    var index = $(".btn-delete-temp").index(this);
        //   var tahun = $(this).attr("data-tahun");
        //   var nop = $(this).attr("data-nop");
        //    var c = confirm("Yakin Hapus ? ");
        //    if (!c){
        //      return false;
        //    }
        //    var i = $(this);
        //        var request = $.ajax({
        //          url : "view/PBB/pembayaran_va/setDelete.php",
        //          type: "POST",
        //          data: "nop="+nop+"&tahun="+tahun,
        //          dataType: "json",
        //          success: function (data) {
        //            // alert(data);
        //             myTableMember.row( i.parents('tr') ).remove().draw();
        //            // $(".baris").eq(index).remove();
        //              // myTable.destroy();
        //              // reloadDataGroup();

        //          }
        //      });
        // });
        $(document).on("click", ".btn-delete-group", function(e) {
            var index = $(".btn-delete-group").index(this);
            var group_id = $(this).attr("group_id");

            var c = confirm("Yakin Hapus ? ");
            if (!c) {
                return false;
            }
            var i = $(this);
            var request = $.ajax({
                url: "view/PBB/pembayaran_va/setDeleteGroup.php",
                type: "POST",
                data: "id=" + group_id,
                dataType: "json",
                success: function(data) {
                    if (data.success) {
                        myTable.row(i.parents('tr')).remove().draw();

                        // $("#example").find("tbody").find("tr").eq(index).remove();
                        myTable.draw();
                    } else {
                        alert(data);
                    }
                }
            });
        });
        $(document).on("click", "#full,.btn-close,.btn-close-group,.btn-close-group-form", function(e) {
            // removeTable();
            // reloadDataGroup();

            $("#form-add-user-group").fadeOut();
            $("#form-add-group").fadeOut();
            $("#form-view-failed").fadeOut();
            $("#full").hide();

            // $('.modal-base').fadeOut();
            // $('.list-nop-gagal table tbody').html('');

            e.preventDefault();
        })
        $(document).on("click", "#btn-tambah-group", function(e) {
            $("#data-nama-group").focus();
            $("#full").fadeIn();
            $("#form-add-group").fadeIn();
            $("#data-edit-group-id").val("");
            $("#data-id-group").val("");
            $("#data-nama-group").val("");
            $("#data-keterangan-group").val("");
            $("#data-nama-kolektor").val("");
            $("#data-no-kolektor").val("");
            // briva1
            $("#data-kelurahan-group").val("");


            //baru
            $("#data-kelurahan-group-2").show();
            $("#label-data-kelurahan-group").hide();

            // baru 2
            $("#data-kecamatan-group").show();
            $("#label-data-kecamatan-group").hide();


            $("#form-add-group-fungsi").html("Tambah Group");
            $("#submit-group-form").attr("value", "Simpan");

            e.preventDefault();

        });
        $(document).on("click", ".btn-add-nop", function(e) {
            $("#form-add-user-group").fadeIn();
            $("#full").fadeIn();

            var group_id = $(this).closest("a").attr("group-id");
            var status = $(this).closest("a").attr("status");
            // alert(status);
            getGroupData(group_id, status);


            var a = $(this).closest("tr").find("td").eq(2).html();
            var kode = $(this).closest("tr").find("td").eq(9).find(".kd-kel").html();
            var nm = $(this).closest("tr").find("td").eq(9).find(".nm-kel").html();
            // find("nm-kel").find("kd-kel").html();
            // alert(nm);
            // alert(kode);
            $("#td-kel-des").html(nm);
            $("#data-kelurahan").val(kode);
            // alert(a);
            $("#form-group-name").html(a);
            $("#data-group-name").html(a);
            $("#form-group-name-2").html(a);

        });

        $(document).on("click", ".btn-edit-group", function(e) {
            $("#full").fadeIn();
            $("#form-add-group").fadeIn();
            var group_id = $(this).attr("group-id");
            getGroupDataByID(group_id);
            $("#data-edit-group-id").val(group_id);
            $("#form-add-group-fungsi").html("Ubah Group");
            $("#submit-group-form").attr("fungsi", "Ubah");


            // var a = $(this).closest("tr").find("td").eq(1).html();
            // alert(a);
            // $("#data-group-name").html(a);
        });

    });

    var myTable;
    var myTableMember;

    $('body').on('click', 'button[name=btn-search-group]', function() {
        // myTable.ajax.reload();
        reloadDataGroup();
    });

    function reloadDataGroup() {
        if (myTable) {
            myTable.destroy();
        }
        $(".loader").show();
        myTable = $('#myTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "view/PBB/pembayaran_va/getDataGroup.php?userID=<?php echo $uid; ?>",
                "type": "POST",
                "data": function(data) {
                    data.cbKecamatan = $('#cbKecamatan').val();
                    data.cbKelurahan = $('#cbKelurahan').val();
                    data.cbStatus = $('#cbStatus').val();
                    data.txSearch = $('#txSearch').val();
                }
            },
            "lengthChange": true,
            "responsive": true,
            "ordering": true,
            "searching": false,
            "paging": true,
            "fnDrawCallback": function(oSettings) {
                $(".loader").hide();
                //         if (status=="1" || status=="2"){
                //          $("#btn-finalkan").hide();
                //          $("#btn-tambah-cari-nop").hide();
                //          $("#cetak-list-member").show();
                //          // alert("hide");
                //          $(".btn-delete-temp").hide();
                //          $(".section-table").hide();
                //          $(".btn-delete-place").hide();
                //        // alert("masuk");
                //        }else{
                //          $(".btn-delete-place").show();
                //          $(".section-table").show();
                //          $(".btn-delete-temp").show();
                //          // alert("masuk 2");
                //          // $(".member-aksi-delete").show();
                //          $("#btn-tambah-cari-nop").show();
                //          $("#btn-finalkan").show();
                //          $("#cetak-list-member").hide();
                //        }
            },

            "footerCallback": function(row, data, start, end, display) {
                $('#myTable').css('width','100%');
                // var api = this.api(), data;
                // // alert("masuk");

                // // Remove the formatting to get integer data for summation
                // var intVal = function ( i ) {
                //     return typeof i === 'string' ?
                //         i.replace(/[\$,]/g, '')*1 :
                //         typeof i === 'number' ?
                //             i : 0;
                // };

                // // Total over all pages
                // total = api
                //     .column( 9 )
                //     .data()
                //     .reduce( function (a, b) {
                //         return intVal(a) + intVal(b);
                //     }, 0 );

                // // Total over this page
                // pageTotal = api
                //     .column( 9, { page: 'current'} )
                //     .data()
                //     .reduce( function (a, b) {
                //         return intVal(a) + intVal(b);
                //     }, 0 );

                // // Update footer
                // $( api.column( 4 ).footer() ).html(
                //     // 'Total Page :  '+pageTotal +' ( Rp'+ total +' total)'
                //     '( Rp '+ total.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,') +' )'
                // );
            },
            order: [
                [13, "desc"]
            ],
            columns:

                [{
                        title: "No",
                        name: "NOMOR_URUT",
                        className: "text-center",
                        orderable: false,
                        searchable: false
                    },
                    {
                        title: "Aksi",
                        name: 'AKSI',
                        className: "text-center",
                        searchable: false,
                        orderable: false
                    },
                    {
                        title: "Nama Group",
                        name: 'CPM_CG_NAME',
                        searchable: true
                    },

                    {
                        title: "Nama Kolektor",
                        name: 'CPM_CG_COLLECTOR',
                        searchable: true
                    },
                    {
                        title: "Telp Kolektor",
                        name: 'CPM_CG_HP_COLLECTOR',
                        className: "text-center",
                        orderable: false,
                        searchable: true
                    },
                    {
                        title: "Anggota",
                        name: 'JML_ANGGOTA',
                        className: "text-center",
                        orderable: false,
                        searchable: false

                    },
                    {
                        title: "Kode Bayar",
                        name: 'CPM_CG_PAYMENT_CODE',
                        className: "text-center",
                        searchable: true
                    },
                    {
                        title: "Status",
                        name: 'CPM_CG_STATUS',
                        className: "text-center",
                        orderable: false,
                        searchable: false
                    },
                    {
                        title: "Kecamatan",
                        name: 'NAMA_KECAMATAN',
                        searchable: true
                    },
                    {
                        title: "Kelurahan",
                        name: 'NAMA_KELURAHAN',
                        searchable: true
                    },
                    {
                        title: "Keterangan",
                        name: 'CPM_CG_DESC',
                        searchable: false
                    },
                    {
                        visible: false,
                        title: "Tanggal Kadaluarsa ",
                        name: 'CPM_CG_EXPIRED_DATE',
                        className: "text-center",
                        orderable: false,
                        searchable: false
                    },
                    {
                        title: "Dibuat Oleh ",
                        name: 'NAMA_USER',
                        searchable: true
                    },
                    {
                        title: "Dibuat Pada ",
                        name: 'CPM_CG_CREATED_DATE',
                        className: "text-center",
                        searchable: false
                    }
                    //    {  
                    //       title:"Aksi",
                    //       "searchable":false,
                    //       "type":"html",
                    //       "index":0,
                    //       'name':''
                    //    },
                    //    {  
                    //       title:"NOP",
                    //       "searchable":true,
                    //       "type":"html",
                    //       "index":1,
                    //       'name':'NOP'
                    //    },
                    //    {  
                    //       title:"Tahun Pajak",
                    //       "searchable":true,
                    //       "type":"html",
                    //       "index":2,
                    //       'name':'SPPT_TAHUN_PAJAK'
                    //    },
                    //    {  
                    //       title:"Tanggal Jatuh Tempo",
                    //       "searchable":false,
                    //       "type":"html",
                    //       "index":3,
                    //       'name':'SPPT_TANGGAL_JATUH_TEMPO'
                    //    },
                    //    {  
                    //       title:"Nama WP",
                    //       "searchable":true,
                    //       "type":"html",
                    //       "index":4,
                    //       'name':'WP_NAMA'
                    //    },
                    //    {  
                    //       title:"Kecamatan",
                    //       "searchable":true,
                    //       "type":"html",
                    //       "index":5,
                    //       'name':'OP_KECAMATAN'
                    //    },
                    //    {  
                    //       title:"Kelurahan",
                    //       "searchable":true,
                    //       "type":"html",
                    //       "index":6,
                    //       'name':'OP_KELURAHAN'
                    //    },
                    //    {  
                    //       title:"Pokok",
                    //       "searchable":true,
                    //       "type":"html",
                    //       "index":7,
                    //       'name':'SPPT_PBB_HARUS_DIBAYAR'
                    //    },
                    //    {  
                    //       title:"Denda",
                    //       "searchable":false,
                    //       "type":"html",
                    //       "index":8,
                    //       'name':'test'
                    //    },
                    //    {  
                    //       title:"Total",
                    //       "searchable":false,
                    //       "type":"html",
                    //       "index":9,
                    //       'name':'test'
                    //    }
                ]



        });
        $('#myTable').css('width','100%');
        // $.ajax({
        // 	url : "view/PBB/pembayaran_va/getDataGroup.php",
        //   data : "userID=<?php echo $uid; ?>",
        // 	success : function(respon){
        // 		// alert(respon);
        // 		var json = JSON.parse(respon);
        // 		// console.log(json);
        // 		var dataSet = [];
        // 		$.each(json,function(i,v){
        //       var aksi_reaktiv = " ";
        //       var aksi_hapus = " ";
        //       var aksi_pengantar = " ";
        //       var aksi_failed = " ";
        //       var aksi_edit = " ";
        //       // alert(v.is_expired);
        //       if (v.CPM_CG_STATUS=="99"){ 
        //          aksi_reaktiv =   " <a title='Reaktivasi Group Final'><i group-id='"+v.CPM_CG_ID+"'  class=' btn-reaktivasi fa btn-aksi  fa-refresh'></i></a> &nbsp;";
        //       }else{
        //          aksi_reaktiv = "";
        //       }

        //       if (v.CPM_CG_STATUS=="1" || v.CPM_CG_STATUS=="2" ){ 
        //        aksi_pengantar = "<a title='Cetak Surat Pengantar'><i group-id='"+v.CPM_CG_ID+"'  class='fa btn-aksi  fa-book btn-cetak-info-group'></i></a> &nbsp";

        //       }else{
        //          aksi_pengantar = " ";
        //       }

        //       if (v.CPM_CG_STATUS=="1" || v.CPM_CG_STATUS=="0"){ 
        //          if (v.JML_FAILED>0){
        //           aksi_failed = "<a title='Daftar NOP yang Gagal'><i style='color:red' group-id='"+v.CPM_CG_ID+"'  class='fa btn-aksi fa-user-times btn-view-failed'></i></a> &nbsp";
        //          }

        //       }

        //       if (v.CPM_CG_STATUS=="0" || v.CPM_CG_STATUS=="99"){ 
        //         aksi_hapus =  "<a title='Hapus Group'><i style='color:red' group_id='"+v.CPM_CG_ID+"' class=' btn-delete-group fa btn-aksi fa-times'></i></a> &nbsp;"

        //          aksi_edit =  "<a  title='Ubah Data Group'><i style='color:black' group-id='"+v.CPM_CG_ID+"' class='fa btn-edit-group  fa-edit'></i></a> &nbsp;";

        //         }


        //        // if (v.CPM_CG_STATUS!="2" && v.CPM_CG_STATUS!="1"){ 
        //                  // }




        // 			var aksi = "<p>"+
        // 			aksi_edit+
        // 			"<a class='' title='Kelola Member Group'  group-id='"+v.CPM_CG_ID+"'  status='"+v.CPM_CG_STATUS+"' ><i class='btn-add-nop fa btn-aksi fa-user'></i></a> &nbsp;"+aksi_hapus+aksi_pengantar
        // 			"</p>";
        // 			// alert(v.CPM_CG_NAME);
        //       var tgl_bayar = " ";
        //         // alert(v.CPM_CG_PAY_DATE);
        //          // var vType = typeof v.CPM_CG_PAY_DATE;
        //          // alert(vType);
        //         if (isNaN(v.CPM_CG_PAY_DATE)){
        //           tgl_bayar  = "  ";
        //         }
        //         else if (v.CPM_CG_PAY_DATE==null){
        //           tgl_bayar  = " ";
        //         }else{

        //           tgl_bayar  = " "+v.CPM_CG_PAY_DATE ;
        //         }


        //       var status = "";
        //       if (v.is_expired){
        //           status = "<b style='color:red'>Expired</b>";
        //       }else{

        //         if (v.CPM_CG_STATUS=="0"){
        //           status = "Draft";
        //         }else if (v.CPM_CG_STATUS=="1"){
        //           status = "<b style='color:orange'>Final - Siap Dibayar</b>";
        //         }else if (v.CPM_CG_STATUS=="2"){
        //           status = "<b style='color:green'>Sudah Di Bayar <i class='fa fa-check'></i> "+tgl_bayar+"</b>";
        //         }else if (v.CPM_CG_STATUS=="99"){
        //           status = "<b style='color:red'>Expired</b>";
        //           // status = "Expired";
        //         }
        //       }

        //       var kode_bayar = "";
        //       if (v.CPM_CG_PAYMENT_CODE==""){
        //         kode_bayar = "<b style='color:red'>Belum Tersedia</b>";
        //       }else{
        //         kode_bayar = v.CPM_CG_PAYMENT_CODE;
        //       }




        // 				dataSet.push( 
        //          [
        //            aksi,
        //            v.CPM_CG_NAME,
        //            v.CPM_CG_COLLECTOR,
        //            v.CPM_CG_HP_COLLECTOR,
        //            v.JML_ANGGOTA+" "+aksi_failed,
        //            "<p style='text-align:center'>"+kode_bayar+"</p>",
        //            status+aksi_reaktiv,
        //            v.NAMA_KELURAHAN,          
        //            v.CPM_CG_DESC            
        //            ]
        //        );
        // 			});
        // 			// alert(JSON.stringify(dataSet));




        //  var columnDefs = [
        //  	{
        //    title: "Aksi"
        // },
        //  	{
        //    title: "Nama Group"
        // },

        //  {
        //    title: "Nama Kolektor"
        //  }, {
        //    title: "HP Kolektor"
        //  }, {
        //    title: "Anggota"
        //  }, {
        //    title: "Kode Bayar"
        //  }, 
        //  {
        //    title: "Status"
        //  },
        //  {
        //    title: "Kelurahan"
        //  },
        //   {
        //    title: "Ketarangan"
        //  }
        //  ];



        //  myTable = $('#example').DataTable({
        //    "sPaginationType": "full_numbers",
        //    data: dataSet,


        //    columns: columnDefs,
        // 	// dom: 'Bfrtip',        // Needs button container
        //          // select: 'single',
        //          responsive: true,
        //          // altEditor: true,     // Enable altEditor
        //         //  buttons: [{
        //         //    text: 'Add',
        //         //    name: 'add'        // do not change name
        //         //  },
        //         //  {
        //         //    extend: 'selected', // Bind to Selected row
        //         //    text: 'Edit',
        //         //    name: 'edit'        // do not change name
        //         //  },
        //         //  {
        //         //    extend: 'selected', // Bind to Selected row
        //         //    text: 'Delete',
        //         //    name: 'delete'      // do not change name
        //         // }]
        //  });

        // }
        // }); // end ajax




    }
    //   function showKecamatanAll() {
    //     var request = $.ajax({
    //         url: "view/PBB/monitoring/svc-kecamatan.php",
    //         type: "POST",
    //         data: {id: "<?php echo $appConfig['KODE_KOTA'] ?>"},
    //         dataType: "json",
    //         success: function (data) {
    //             var c = data.msg.length;
    //             var options = '';
    //             options += '<option value="">Pilih Semua</option>';
    //             for (var i = 0; i < c; i++) {
    //                 options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
    //             }
    //                 $("select#data-kecamatan").html(options);
    //                 $("select#data-kecamatan-group").html(options);

    //         }
    //     });

    // }
    function showKecamatanAll() {
        var request = $.ajax({
            url: "view/PBB/monitoring/svc-kecamatan.php",
            type: "POST",
            data: {
                id: "<?php echo $appConfig['KODE_KOTA'] ?>"
            },
            dataType: "json",
            success: function(data) {
                var c = data.msg.length;
                var options = '';
                var idRole = '<?php echo $idRole ?>';

                if (idRole != "rmKelurahan" && idRole != "rmKecamatan")
                    options += '<option value="">Pilih Semua</option>';

                var myKecamatan = '<?php echo $myKecamatan ?>';
                // alert(myKecamatan);
                for (var i = 0; i < c; i++) {
                    // alert(data.msg[i].id);
                    if (idRole == "rmKelurahan" || idRole == "rmKecamatan") {
                        if (parseInt(myKecamatan) == parseInt(data.msg[i].id)) {
                            options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
                        }
                    } else {
                        options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
                    }
                }

                $("select#data-kecamatan").html(options);
                $("select#data-kecamatan-group").html(options);

            }
        });

    }

    function showKecamatanToEl(el) {
        $.ajax({
            url: "view/PBB/monitoring/svc-kecamatan.php",
            type: "POST",
            data: {
                id: "<?php echo $appConfig['KODE_KOTA'] ?>"
            },
            dataType: "json",
            success: function(data) {
                var c = data.msg.length;
                var options = '';
                options += '<option value="">Semua Kecamatan</option>';
                for (var i = 0; i < c; i++) {
                    options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
                }
                $(el).html(options);
            }
        });
    }

    function showKelurahanToEl(kec, el) {
        if (kec == "") {
            $(el).html('<option value="">Semua Kelurahan</option>');
            return;
        }

        $.ajax({
            url: "view/PBB/monitoring/svc-kecamatan.php",
            type: "POST",
            data: {
                id: kec,
                kel: 1
            },
            dataType: "json",
            success: function(data) {
                var c = data.msg.length;
                var options = '';
                options += '<option value="">Semua Kelurahan</option>';
                for (var i = 0; i < c; i++) {
                    options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
                }
                $(el).html(options);
            }
        });
    }

    function showKelurahan(sts) {
        var id = $('select#data-kecamatan').val();
        var request = $.ajax({
            url: "view/PBB/monitoring/svc-kecamatan.php",
            ty: "POST",
            data: {
                id: id,
                kel: 1
            },
            dataType: "json",
            success: function(data) {
                var c = data.msg.length;
                var options = '';
                options += '<option value="">Pilih Semua</option>';
                for (var i = 0; i < c; i++) {
                    options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
                    $("select#data-kelurahan").html(options);
                }
            }
        });
    }

    function showKelurahanGroup(sts, datakel = '') {
        var id = $('select#data-kecamatan-group').val();
        var request = $.ajax({
            url: "view/PBB/monitoring/svc-kecamatan.php",
            ty: "POST",
            data: {
                id: id,
                kel: 1
            },
            dataType: "json",
            success: function(data) {
                var c = data.msg.length;

                var options = '';
                options += '<option value="">Pilih Semua</option>';
                for (var i = 0; i < c; i++) {
                    if (datakel != '') {
                        var s = 0;
                        if (datakel == data.msg[i].id) {
                            s = "selected";
                        } else {
                            s = ""
                        }

                        options += '<option ' + s + ' value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';

                    } else {
                        options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';

                    }

                    $("select#data-kelurahan-group").html(options);
                    $("select#data-kelurahan-group-2").html(options);
                }
            }
        });
    }

    function appendToTable(data) {

        var counter = 1;
        let row = d => {
            return [
                "<td class='td-aksi-delete'  style='text-align:center'><a><i data-nop='" + d.NOP + "' data-tahun='" + d.SPPT_TAHUN_PAJAK + "' class='btn-delete-temp fa-times fa '></i></a></td>",
                "<td >" + d.NOP + "</td>",
                "<td >" + d.SPPT_TAHUN_PAJAK + "</td>",
                "<td >" + d.SPPT_TANGGAL_JATUH_TEMPO + "</td>",
                "<td >" + d.WP_NAMA + "</td>",
                "<td >" + d.OP_KECAMATAN + "</td>",
                "<td >" + d.OP_KELURAHAN + "</td>",
                "<td style='text-align:right' >" + d.SPPT_PBB_HARUS_DIBAYAR + "</td>",
                "<td style='text-align:right' >" + d.PBB_DENDA + "</td>",
                "<td style='text-align:right' >" + (parseInt(d.SPPT_PBB_HARUS_DIBAYAR) + parseInt(d.PBB_DENDA)) + "</td>",

            ]
        };

        if (Array.isArray(data)) {
            data.forEach(d => {
                myTableMember.row.add(row(d)).draw(false);
            });
        } else {
            myTableMember.row.add(row(data)).draw(false);
        }


        // $('#bodi-nop').append(
        //      "<tr class='baris' role='row'>" +
        //        "<td class='td-aksi-delete'  style='text-align:center'><a><i data-nop='"+data.NOP+"' data-tahun='"+data.SPPT_TAHUN_PAJAK+"' class='btn-delete-temp fa-times fa '></i></a></td>" +
        //        "<td >"+data.NOP+"</td>" +
        //        "<td >"+data.SPPT_TAHUN_PAJAK+"</td>" +
        //        "<td >"+data.SPPT_TANGGAL_JATUH_TEMPO+"</td>" +
        //        "<td >"+data.WP_NAMA+"</td>" +
        //        "<td >"+data.OP_KECAMATAN+"</td>" +
        //        "<td >"+data.OP_KELURAHAN+"</td>" +
        //        "<td style='text-align:right' >"+data.SPPT_PBB_HARUS_DIBAYAR+"</td>" +
        //        "<td style='text-align:right' >"+data.PBB_DENDA+"</td>" +
        //        "<td style='text-align:right' >"+(parseInt(data.SPPT_PBB_HARUS_DIBAYAR)+parseInt(data.PBB_DENDA)) +"</td>" +

        //      "</tr>"
        //  );
        // if (myTableMember)
        //   myTableMember.destroy();
        // myTableMember =  $('#myTableMember').DataTable();
    }
    // function cariNOP(){
    //   var data = $("#form-cari").serialize();
    //       var request = $.ajax({
    //         url : "view/PBB/pembayaran_va/getDataNop.php",
    //         type: "POST",
    //         data: data,
    //         dataType: "json",
    //         success: function (data) {
    //           if (data.success==true){
    //             appendToTable(data);
    //           }else{
    //             alert(data.message);
    //            // alert(data);
    //           }
    //         }
    //     });


    // }

    function excelListMember(group_id) {
        window.open('view/PBB/pembayaran_va/setExcelListMember.php?id=' + group_id, '_newtab');
    }

    function csvListMember(group_id) {
        window.open('view/PBB/pembayaran_va/setCSVListMember.php?id=' + group_id, '_newtab');
    }


    function pdfListMember(group_id) {
        window.open('view/PBB/pembayaran_va/setPDFListMember.php?id=' + group_id, '_newtab');
    }

    function pdfGroupInfo(group_id) {
        window.open('view/PBB/pembayaran_va/setPDFGroupInfo.php?id=' + group_id, '_newtab');
    }

    function prosesMultiNOP(data) {
        if (data.valid.length) {
            appendToTable(data.valid);
        }

        if (data.invalid.length) {
            let modalInvalidNop = $('.list-nop-gagal');
            let trData = ``;
            (data.invalid).forEach(invalidNop => {
                trData += `<tr><td>${invalidNop.NOP}</td><td>${invalidNop.cause}</td></tr>`;
            });
            modalInvalidNop.find('table tbody').html(trData);
            modalInvalidNop.find('.list-nop-gagal-total').html(data.invalid.length)
            modalInvalidNop.show();
        }

        alert(data.message);
        console.log(data.invalid.length, data.valid.length);
    }

    function cariNOP() {
        // alert('maintanence');return false;
        var nop = $("#data-nop").val();

        var kelurahan = $("#data-kelurahan option:selected ").text();
        var buku = $("#data-buku option:selected ").text();

        var data = $("#form-cari").serialize();
        if (nop.trim() == "") {
            var c = confirm(" Yakin Menambahkan seluruh NOP di Kelurahan " + kelurahan + " , " + buku + " , Tahun Pajak " + $("#data-tahun-pajak").val() + " yang belum bayar ? ");
            if (!c) {
                return false;
            }
            $("#data-nop").val(" ");
        } else {

        }
        var request = $.ajax({
            url: "view/PBB/pembayaran_va/getDataNop.php",
            type: "POST",
            data: data,
            dataType: "json",
            beforeSend: function(data) {
                $(".loader").show();
            },
            success: function(data) {
                $(".loader").hide();
                // alert(JSON.stringify(data));
                // $("#full").hide();

                if (data.multiNop == true) {
                    prosesMultiNOP(data);
                    return;
                }

                if (data.success == true) {


                    if (data.masal == false) { // jika tambahkan per NOP
                        appendToTable(data);
                        $("#data-nop").removeAttr("value");
                        $("#data-nop").focus();
                    } else {
                        if (data.message) {

                            alert(data.message);
                            var idd = $("#data-group-id").val();
                            // alert(idd);
                            getGroupData(idd, 0);
                        } else {
                            alert("Berhasil");
                        }
                    }

                } else {
                    alert(data.message);
                    $("#data-nop").focus();
                    $("#data-nop").select();
                    //   $("#data-tahun-pajak").val("");   
                }
                // }else{
                //   alert(JSON.stringify(data));
                // }
            },
            error: function() {
                // $("#full").hide();
            }
        });


    }

    function tambahGroup() {
        var data = $("#form-tambah-group").serialize();
        var groupNama = $("#data-nama-group").val();
        var groupKeterangan = $("#data-keterangan-group").val();
        var groupKolektor = $("#data-nama-kolektor").val();
        var groupKolektorHP = $("#data-no-kolektor").val();
        var groupKelurahan = $("#data-kelurahan-group-2").val();
        if (groupNama == "") {
            alert("Nama Group Tidak Boleh Kosong");
            exit;
        }
        if (groupKeterangan == "") {
            alert("Keterangan Group Tidak Boleh Kosong");
            exit;
        }
        if (groupKolektor == "") {
            alert("Kolektor Group Tidak Boleh Kosong");
            exit;
        }
        if (groupKolektorHP == "") {
            alert("No HP Kolektor Tidak Boleh Kosong");
            exit;
        }
        if (groupKelurahan == "") {
            alert("Kelurahan Tidak Boleh Kosong");
            exit;
        }


        var request = $.ajax({
            url: "view/PBB/pembayaran_va/setGroupData.php",
            type: "POST",
            data: data,
            dataType: "json",
            beforeSend: function(d) {
                $(".loader").show();
                $("#full").show();
            },
            success: function(data) {
                // alert(data);
                if (data.success == true) {
                    $("#full").hide();
                    $(".loader").hide();
                    // myTable.destroy();
                    // removeTable();
                    reloadDataGroup();
                    $("#form-add-group").hide();
                    // appendToTable(data);
                } else {
                    $(".loader").hide();
                    alert(data.message);
                }
            }
        });
    }

    function finalkan() {
        var c = confirm("Yakin  Finalkan  ? ");
        if (!c) {
            return false;
        }

        var group_id = $("#data-group-id").val();
        // alert(group_id);
        var user_id = $("#userID").val();
        var request = $.ajax({
            url: "view/PBB/pembayaran_va/setFinalGroup.php",
            type: "POST",
            data: "id=" + group_id + "&userID=" + user_id,
            dataType: "json",
            beforeSend: function() {},
            success: function(data) {
                $("#full").hide();
                $("#form-add-user-group").hide();
                if (data.success == true) {
                    // $("#full").hide();
                    // myTable.destroy();
                    reloadDataGroup();
                    $("#form-add-user-group").hide();
                } else {
                    $("#full").hide();
                    alert(data.message);
                    // alert(data);
                }
            }
        });
    }
    //   function tambahGroup(){
    //   var data = $("#form-tambah-group").serialize();
    //       var request = $.ajax({
    //         url : "view/PBB/pembayaran_va/setGroupData.php",
    //         type: "POST",
    //         data: data,
    //         dataType: "json",
    //         success: function (data) {
    //           // alert(data);
    //           if (data.success==true){
    //             myTable.destroy();
    //             reloadDataGroup();
    //             $("#form-add-group").hide();
    //             // appendToTable(data);
    //           }else{
    //             alert(data);
    //           }
    //         }
    //     });


    // }
    function removeMemberTable() {
        if (myTableMember)
            myTableMember.clear().draw();
    }

    function removeTable() {
        if (myTable) {
            myTable.destroy();
            myTable.clear().draw();
        }
    }

    function onChangeFilterMemberStatusBayar(t) {
        let v = $(t);

        let rows     = $('#myTableMember tbody tr');
        let rowLunas = $('#myTableMember [data-is-lunas="1"]').parent().parent();
        let rowBelum = $('#myTableMember [data-is-lunas="0"]').parent().parent();

        rows.hide();
        if (v.val() == '') {
            rows.show();
        } else if (v.val() == '1') {
            rowLunas.show();
        } else {
            rowBelum.show();
        }

    }

    function getGroupData(group_id, status) {
        $("#data-group-id").val(group_id);

        if (myTableMember) {
            myTableMember.destroy();
        }
        $(".loader").show();
        myTableMember = $('#myTableMember').DataTable({
            "paging": false,
            "processing": true,
            "serverSide": true,
            "order": [[5, 'asc'],[6, 'asc']],
            "ajax": "view/PBB/pembayaran_va/getDataMember.php?status=" + status + "&group_id=" + group_id,
            "fnDrawCallback": function(oSettings) {
                $(".loader").hide();

                let filterStatusBayar = $('#member-status-bayar');

                filterStatusBayar.val('').parent().hide();

                if (status == "1" || status == "2") {
                    filterStatusBayar.parent().show();
                    $("#btn-finalkan").hide();
                    $("#btn-tambah-cari-nop").hide();
                    $("#cetak-list-member").show();
                    // alert("hide");
                    $(".btn-delete-temp").hide();
                    $(".section-table").hide();
                    $(".btn-delete-place").hide();
                    // alert("masuk");
                } else {
                    $(".btn-delete-place").show();
                    $(".section-table").show();
                    $(".btn-delete-temp").show();
                    // alert("masuk 2");
                    // $(".member-aksi-delete").show();
                    $("#btn-tambah-cari-nop").show();
                    $("#btn-finalkan").show();
                    $("#cetak-list-member").hide();
                }

                // #f5c6cb : danger
                // #c3e6cb : success


                // aldes
                let rows     = $('#myTableMember tbody tr');
                let rowLunas = $('#myTableMember [data-is-lunas="1"]').parent().parent();
                let rowBelum = $('#myTableMember [data-is-lunas="0"]').parent().parent();

                rows.addClass('table-danger');

                rowLunas.each(function(i, el) {
                    $(el).removeClass('table-danger').addClass('table-success');
                })

                // rowBelum.each(function(i, el) {
                //     $(el).removeClass('table-success').addClass('table-danger');
                // })
            },

            "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                    data;
                // alert("masuk");

                // Remove the formatting to get integer data for summation
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                // Total over all pages
                total = api
                    .column(9)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Total over this page
                pageTotal = api
                    .column(9, {
                        page: 'current'
                    })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                $(api.column(4).footer()).html(
                    // 'Total Page :  '+pageTotal +' ( Rp'+ total +' total)'
                    '( Rp ' + total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + ' )'
                );
            },
            columns: [
                {
                    title: "#",
                    searchable: false,
                    orderable: false,
                    type: "html",
                    index: 0,
                    name: '',
                    visible: status==0 ? true : false
                },
                {
                    title: "NOP",
                    searchable: true,
                    type: "html",
                    index: 1,
                    name: 'NOP'
                },
                {
                    title: "Tahun Pajak",
                    searchable: true,
                    type: "html",
                    index: 2,
                    name: 'SPPT_TAHUN_PAJAK',
                    className: "text-center"
                },
                {
                    title: "Tanggal Jatuh Tempo",
                    searchable: false,
                    orderable: false,
                    type: "html",
                    index: 3,
                    name: 'SPPT_TANGGAL_JATUH_TEMPO'
                },
                {
                    title: "Nama WP",
                    searchable: true,
                    type: "html",
                    index: 4,
                    name: 'WP_NAMA'
                },
                {
                    title: "Kecamatan",
                    searchable: true,
                    type: "html",
                    index: 5,
                    name: 'OP_KECAMATAN'
                },
                {
                    title: "Kelurahan",
                    searchable: true,
                    type: "html",
                    index: 6,
                    name: 'OP_KELURAHAN'
                },
                {
                    title: "Pokok",
                    searchable: false,
                    type: "html",
                    index: 7,
                    name: 'SPPT_PBB_HARUS_DIBAYAR',
                    className: "text-right"
                },
                {
                    title: "Denda",
                    searchable: false,
                    type: "html",
                    index: 8,
                    name: 'DENDA',
                    className: "text-right"
                },
                {
                    title: "Total",
                    searchable: false,
                    type: "html",
                    index: 9,
                    name: 'TOTAL',
                    className: "text-right"
                },
                {
                    title: "Status",
                    searchable: false,
                    type: "html",
                    visible: (status==1 || status==2) ? true : false,
                    orderable: false,
                    index: 10,
                    name: 'STTS_BAYAR',
                    className: "text-center"
                },
                {
                    title: "Tanggal Bayar",
                    searchable: false,
                    visible: (status==1 || status==2) ? true : false,
                    orderable: false,
                    type: "html",
                    index: 11,
                    name: 'TGL_BAYAR',
                    className: "text-center"
                }
            ]



        });
        // $.each(json,function(i,v){
        //   // alert(v.NOP);
        //   appendToTable(v);
        // });
        // alert(status);

        //     // alert(JSON.stringify(json));
        //   }
        // });
    }

    function getGroupDataByID(group_id) {
        $("#data-group-id").val(group_id);
        $.ajax({
            url: "view/PBB/pembayaran_va/getDetailGroup.php",
            data: "group_id=" + group_id,
            success: function(respon) {
                var json = JSON.parse(respon);
                $("#data-nama-group").val(json[0].CPM_CG_NAME);
                $("#data-keterangan-group").val(json[0].CPM_CG_DESC);
                $("#data-nama-kolektor").val(json[0].CPM_CG_COLLECTOR);
                $("#data-no-kolektor").val(json[0].CPM_CG_HP_COLLECTOR);


                $("#data-kecamatan-group").val(json[0].KCID);
                showKelurahanGroup(json[0].KCID, json[0].CPM_CG_AREA_CODE);


                // baru  set kecamatan
                $("#label-data-kecamatan-group").show();
                $("#data-kecamatan-group").hide();
                var selectedKecamatan = $("#data-kecamatan-group option:selected").text();
                $("#label-data-kecamatan-group").html(selectedKecamatan);



                // baru  set kelurahan 
                $("#data-kelurahan-group-2").val(json[0].CPM_CG_AREA_CODE);
                $("#label-data-kelurahan-group").html(json[0].NAMA_KELURAHAN);

                $("#label-data-kelurahan-group").show();
                $("#data-kelurahan-group-2").hide();


                // alert(json[0].CPM_CG_AREA_CODE);
                // var selectedKelurahan = $("#data-kelurahan-group-2 option:selected").text();
                // alert(selectedKelurahan);

                // $("#data-kelurahan-group-2").hide();
                // alert(JSON.stringify(json));
            }
        });
    }

    showKecamatanAll();
    showKecamatanToEl(document.getElementById('cbKecamatan'));
</script>