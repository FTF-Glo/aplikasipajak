<?php
session_start();

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'loket', '', dirname(__FILE__))) . '/';
//require_once($sRootPath."inc/payment/c8583.php");
//require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-dms-c.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/central/user-central.php");

echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
//echo "<script language=\"javascript\" src=\"view/PBB/loket/mod-tax-service-print.js\" type=\"text/javascript\"></script>\n";

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}


class TaxService
{
    private $arrayStatus = array('1' => 'Staf', '2' => 'Verifikasi', '3' => 'Persetujuan', '5' => 'Ditolak Verifikasi', '6' => 'Ditolak Persetujuan');
    private $arrayStatusOPBaru = array('0' => 'Staf', '1' => 'Verifikasi 1', '2' => 'Verifikasi 2', '3' => 'Verifikasi 3', '10' => 'Penetapan', '5' => 'Ditolak Verifikasi', '6' => 'Ditolak Persetujuan');

    function __construct($userGroup, $user)
    {
        $this->userGroup = $userGroup;
        $this->user = $user;
    }

    public function encode_for_slug($string)
    {
        $replace  = str_replace(' ', '-', $string);
        $replace  = str_replace(".", "-",$replace);
        $replace  = str_replace("&", "-",$replace);
        $replace  = str_replace(" ", "-",$replace);
        $replace  = str_replace("  ", "-",$replace);
        $replace  = str_replace("   ", "-",$replace);
        $replace  = str_replace("$", "-",$replace);
        $replace  = str_replace("+", "-",$replace);
        $replace  = str_replace("! ", "-",$replace);
        $replace  = str_replace("@", "-",$replace);
        $replace  = str_replace("#", "-",$replace);
        $replace  = str_replace("$", "-",$replace);
        $replace  = str_replace("%", "-",$replace);
        $replace  = str_replace("^", "-",$replace);
        $replace  = str_replace("&", "-",$replace);
        $replace  = str_replace("*", "-",$replace);
        $replace  = str_replace("(", "-",$replace);
        $replace  = str_replace(")", "-",$replace);
        $replace  = str_replace("/", "-",$replace);
        $replace  = str_replace("+", "-",$replace);
        $replace  = preg_replace('/[^A-Za-z0-9\-]/', '', $replace);
        $replace  = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $replace);
        $replace = preg_replace('/-+/', '-', $replace);

        if(substr($replace, -1) == '-')
        {
            $replace = substr_replace($replace,'',-1);
        }

        return strtolower($replace);
    }

    function mysql2json($mysql_result, $name)
    {
        $json = "{\n'$name': [\n";
        $field_names = array();
        $fields = mysqli_num_fields($mysql_result);
        for ($x = 0; $x < $fields; $x++) {
            $field_name = mysqli_fetch_field($mysql_result);
            if ($field_name) {
                $field_names[$x] = $field_name->name;
            }
        }
        $rows = mysqli_num_rows($mysql_result);
        for ($x = 0; $x < $rows; $x++) {
            $row = mysqli_fetch_array($mysql_result);
            $json .= "{\n";
            for ($y = 0; $y < count($field_names); $y++) {
                $json .= "'$field_names[$y]' :	'" . addslashes($row[$y]) . "'";
                if ($y == count($field_names) - 1) {
                    $json .= "\n";
                } else {
                    $json .= ",\n";
                }
            }
            if ($x == $rows - 1) {
                $json .= "\n}\n";
            } else {
                $json .= "\n},\n";
            }
        }
        $json .= "]\n}";
        return ($json);
    }

    function getDocRecap(&$dat)
    {
        global $DBLink, $json, $srcTglAwal, $srcTglAkhir, $srcNama;

        $srcNama = trim($srcNama);
        $wherenama = '';
        if ($srcNama && $srcNama!="") $wherenama = " AND CPM_RECEIVER LIKE '$srcNama%' ";
        $TglAwal = convertDate($srcTglAwal);
        $TglAkhir = convertDate($srcTglAkhir);

        $query="SELECT 
                    CPM_RECEIVER AS PENERIMA,
                    CONCAT('$srcTglAwal',' ','$srcTglAkhir') AS TANGGAL,
                    CPM_TYPE AS TIPE,
                    IF(CPM_APPROVER IS NULL, 0, 1) AS APPROV
				FROM sw_pbb.cppmod_pbb_services 
                WHERE
                    DATE(CPM_DATE_RECEIVE) >= '$TglAwal' AND
                    DATE(CPM_DATE_RECEIVE) <= '$TglAkhir' 
                    $wherenama 
                ORDER BY CPM_RECEIVER, CPM_TYPE";

        // print_r($query); exit;

        $res = mysqli_query($DBLink, $query);
        
        if ($res === false) return false;

        $data = $json->decode($this->mysql2json($res, "src"));
        $HTML = "<tbody>";

        /// pengelompokan dulu
        $tempData = [];
        foreach ($data->src as $row) {
            $penerima = $this->encode_for_slug($row->PENERIMA);
            $tipe = $row->TIPE;
            $stts = $row->APPROV;
            // unset($row->TIPE);
            // unset($row->APPROV);
            $tempData[$penerima][$tipe][$stts][] = $row;
        }
        ksort($tempData);
        ///==========================================

        /// format data dan hitung status
        $dataUrut = [];
        foreach ($tempData as $rows) {
            $penerima = '';
            $tanggal = '';
            $jmltotal = 0;

            $op_baru_0 = 0;
            $op_baru_1 = 0;

            $pecah_0 = 0;
            $pecah_1 = 0;

            $gabung_0 = 0;
            $gabung_1 = 0;

            $mutasi_0 = 0;
            $mutasi_1 = 0;

            $ubah_0 = 0;
            $ubah_1 = 0;

            $batal_0 = 0;
            $batal_1 = 0;

            $duplikat_0 = 0;
            $duplikat_1 = 0;

            $hapus_0 = 0;
            $hapus_1 = 0;

            $pengurangan_0 = 0;
            $pengurangan_1 = 0;

            $keberatan_0 = 0;
            $keberatan_1 = 0;

            $cetak_0 = 0;
            $cetak_1 = 0;

            foreach ($rows as $tipe) {
                foreach ($tipe as $stts) {
                    foreach ($stts as $r) {
                        $penerima = $r->PENERIMA;
                        $tanggal = $r->TANGGAL;
                        $jmltotal++;

                        if($r->TIPE=='1' && $r->APPROV=='0') $op_baru_0++;
                        if($r->TIPE=='1' && $r->APPROV=='1') $op_baru_1++;
                        
                        if($r->TIPE=='2' && $r->APPROV=='0') $pecah_0++;
                        if($r->TIPE=='2' && $r->APPROV=='1') $pecah_1++;

                        if($r->TIPE=='3' && $r->APPROV=='0') $gabung_0++;
                        if($r->TIPE=='3' && $r->APPROV=='1') $gabung_1++;
                        
                        if($r->TIPE=='4' && $r->APPROV=='0') $mutasi_0++;
                        if($r->TIPE=='4' && $r->APPROV=='1') $mutasi_1++;
                        
                        if($r->TIPE=='5' && $r->APPROV=='0') $ubah_0++;
                        if($r->TIPE=='5' && $r->APPROV=='1') $ubah_1++;
                        
                        if($r->TIPE=='6' && $r->APPROV=='0') $batal_0++;
                        if($r->TIPE=='6' && $r->APPROV=='1') $batal_1++;
                        
                        if($r->TIPE=='7' && $r->APPROV=='0') $duplikat_0++;
                        if($r->TIPE=='7' && $r->APPROV=='1') $duplikat_1++;
                        
                        if($r->TIPE=='8' && $r->APPROV=='0') $hapus_0++;
                        if($r->TIPE=='8' && $r->APPROV=='1') $hapus_1++;
                        
                        if($r->TIPE=='9' && $r->APPROV=='0') $pengurangan_0++;
                        if($r->TIPE=='9' && $r->APPROV=='1') $pengurangan_1++;
                        
                        if($r->TIPE=='10' && $r->APPROV=='0') $keberatan_0++;
                        if($r->TIPE=='10' && $r->APPROV=='1') $keberatan_1++;
                        
                        if($r->TIPE=='11' && $r->APPROV=='0') $cetak_0++;
                        if($r->TIPE=='11' && $r->APPROV=='1') $cetak_1++;
                    }
                }
            }

            $obj                = (object)[];
            $obj->PENERIMA      = $penerima;
            $obj->TANGGAL       = $tanggal;
            $obj->JUMLAH        = $jmltotal;
            $obj->OP_BARU_0     = $op_baru_0;
            $obj->OP_BARU_1     = $op_baru_1;
            $obj->PECAH_0       = $pecah_0;
            $obj->PECAH_1       = $pecah_1;
            $obj->GABUNG_0      = $gabung_0;
            $obj->GABUNG_1      = $gabung_1;
            $obj->MUTASI_0      = $mutasi_0;
            $obj->MUTASI_1      = $mutasi_1;
            $obj->UBAH_0        = $ubah_0;
            $obj->UBAH_1        = $ubah_1;
            $obj->BATAL_0       = $batal_0;
            $obj->BATAL_1       = $batal_1;
            $obj->DUPLIKAT_0    = $duplikat_0;
            $obj->DUPLIKAT_1    = $duplikat_1;
            $obj->HAPUS_0       = $hapus_0;
            $obj->HAPUS_1       = $hapus_1;
            $obj->PENGURANGAN_0 = $pengurangan_0;
            $obj->PENGURANGAN_1 = $pengurangan_1;
            $obj->KEBERATAN_0   = $keberatan_0;
            $obj->KEBERATAN_1   = $keberatan_1;
            $obj->CETAK_0       = $cetak_0;
            $obj->CETAK_1       = $cetak_1;
            $dataUrut[] = $obj;
        }
        
        // echo '<pre style="background:#17182b;color:#5cff2f">';
        // // print_r(json_encode($tempData));
        // print_r( json_encode($dataUrut, JSON_PRETTY_PRINT) );
        // echo '</pre>';
        // exit;

        if(count($dataUrut)==0) return false;

        foreach ($dataUrut as $r) {
            $HTML .= "<tr>";
            $HTML .= "<td>" . $r->PENERIMA  . "</td>";
            $HTML .= "<td>" . $r->TANGGAL   . "</td>";
            $HTML .= "<td align=center>" . $r->JUMLAH . "</td>";
            $HTML .= "<td class=\"ylw ylw{$r->OP_BARU_0}\"      >" . number_format($r->OP_BARU_0, 0, ',', '.')      . "</td>";
            $HTML .= "<td class=\"grn grn{$r->OP_BARU_1}\"      >" . number_format($r->OP_BARU_1, 0, ',', '.')      . "</td>";
            $HTML .= "<td class=\"ylw ylw{$r->PECAH_0}\"        >" . number_format($r->PECAH_0, 0, ',', '.')        . "</td>";
            $HTML .= "<td class=\"grn grn{$r->PECAH_1}\"        >" . number_format($r->PECAH_1, 0, ',', '.')        . "</td>";
            $HTML .= "<td class=\"ylw ylw{$r->GABUNG_0}\"       >" . number_format($r->GABUNG_0, 0, ',', '.')       . "</td>";
            $HTML .= "<td class=\"grn grn{$r->GABUNG_1}\"       >" . number_format($r->GABUNG_1, 0, ',', '.')       . "</td>";
            $HTML .= "<td class=\"ylw ylw{$r->MUTASI_0}\"       >" . number_format($r->MUTASI_0, 0, ',', '.')       . "</td>";
            $HTML .= "<td class=\"grn grn{$r->MUTASI_1}\"       >" . number_format($r->MUTASI_1, 0, ',', '.')       . "</td>";
            $HTML .= "<td class=\"ylw ylw{$r->UBAH_0}\"         >" . number_format($r->UBAH_0, 0, ',', '.')         . "</td>";
            $HTML .= "<td class=\"grn grn{$r->UBAH_1}\"         >" . number_format($r->UBAH_1, 0, ',', '.')         . "</td>";
            $HTML .= "<td class=\"ylw ylw{$r->BATAL_0}\"        >" . number_format($r->BATAL_0, 0, ',', '.')        . "</td>";
            $HTML .= "<td class=\"grn grn{$r->BATAL_1}\"        >" . number_format($r->BATAL_1, 0, ',', '.')        . "</td>";
            $HTML .= "<td class=\"ylw ylw{$r->DUPLIKAT_0}\"     >" . number_format($r->DUPLIKAT_0, 0, ',', '.')     . "</td>";
            $HTML .= "<td class=\"grn grn{$r->DUPLIKAT_1}\"     >" . number_format($r->DUPLIKAT_1, 0, ',', '.')     . "</td>";
            $HTML .= "<td class=\"ylw ylw{$r->HAPUS_0}\"        >" . number_format($r->HAPUS_0, 0, ',', '.')        . "</td>";
            $HTML .= "<td class=\"grn grn{$r->HAPUS_1}\"        >" . number_format($r->HAPUS_1, 0, ',', '.')        . "</td>";
            $HTML .= "<td class=\"ylw ylw{$r->PENGURANGAN_0}\"  >" . number_format($r->PENGURANGAN_0, 0, ',', '.')  . "</td>";
            $HTML .= "<td class=\"grn grn{$r->PENGURANGAN_1}\"  >" . number_format($r->PENGURANGAN_1, 0, ',', '.')  . "</td>";
            $HTML .= "<td class=\"ylw ylw{$r->KEBERATAN_0}\"    >" . number_format($r->KEBERATAN_0, 0, ',', '.')    . "</td>";
            $HTML .= "<td class=\"grn grn{$r->KEBERATAN_1}\"    >" . number_format($r->KEBERATAN_1, 0, ',', '.')    . "</td>";
            $HTML .= "<td class=\"ylw ylw{$r->CETAK_0}\"        >" . number_format($r->CETAK_0, 0, ',', '.')        . "</td>";
            $HTML .= "<td class=\"grn grn{$r->CETAK_1}\"        >" . number_format($r->CETAK_1, 0, ',', '.')        . "</td>";
            $HTML .= "</tr>";
        }
        $dat = $HTML . "</tbody>";
        return true;
    }

    public function docRekap()
    {
        global $tab;
          
        $HTML = '<div class=row><div class="col-md-12">';
        if ($tab == 0) $HTML = $this->headerContentRecap();

        if ($tab == 0) $this->getDocRecap($dt);

        if ($dt) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=13 align=center>Data Kosong !</td></tr> ";
        }
        $HTML .= "</table>";
        $HTML .= '</div></div>';
        return $HTML;
    }


    public function headerContentRecap()
    {
        global $tab, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcNama;

        $srcTglAwal = ($srcTglAwal!="") ? $srcTglAwal : date("01-m-Y");
        $srcTglAkhir = ($srcTglAkhir!="") ? $srcTglAkhir : ( (date('d')=='01') ? date("t-m-Y") : date("d-m-Y") );

        $HTML = '
        <style>
            .ylw { text-align:center;background:#ffff6b }
            .ylw0 { background:#feffb169 !important }
            .grn { text-align:center;background:#b1ffd3 }
            .grn0 { background:#b1ffd336 !important }
            .tbl-custome thead tr th {
                color: #fff;
                vertical-align: middle;
                text-align: center;
                font-weight: bold;
                border: solid 1px #CCCCCC;
                background-color: #107138;
                background-image:linear-gradient(to bottom right, #107138, #209550);
            }
            .tbl-custome tbody tr td {
                vertical-align: middle;
            }
            .prss { background:#feffb1 !important }
            .slsi { background:#b1ffd3 !important }
        </style>
        <form id="form-laporan" name="form-notaris" method="post" action="">
            <div class=row>
                <div class="col-md-1" style="text-align:right;padding-top:5px">Tanggal</div>
                <div class="col-md-4">
                    <div class=row>
                        <div class="col-md-5">
                            <input type=text class="srcTgl form-control" id="srcTglAwal-'.$tab.'" size=10 maxlength=10 value="'.$srcTglAwal.'" placeholder="Tgl Awal"/>
                        </div>
                        <div class="col-md-1" style="margin-top:5px;padding:0px;text-align:center">s/d</div>
                        <div class="col-md-5">
                            <input type=text class="srcTgl form-control" id="srcTglAkhir-'.$tab.'" size=10 maxlength=10 value="'.$srcTglAkhir.'" placeholder="Tgl Akhir"/>
                        </div>
                        
                    </div>
                </div>
                <div class="col-md-1" style="text-align:right;padding-top:5px">Nama&nbsp;User</div>
                <div class="col-md-3">
                    <input type=text class="form-control" id="srcNama-'.$tab.'" size=30 value="'.$srcNama.'" placeholder=Nama />
                </div>
                <div class="col-md-3" style="text-align:right;margin:4px 0 20px 0">
                    <button type=button class="btn btn-primary btn-orange" style="width:90px" onclick="setTabs('. $tab .')">Cari</button> 
                    <button type=button class="btn btn-primary btn-blue" onClick="exportToExcel('. $tab .')">Ekspor ke xls</button>
                </div>
            </div>
        </form>';

        $HTML .= '<div class=row>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered tbl-custome">
                                <thead>
                                    <tr>';
                            $HTML .= "<th rowspan=2>User Penerima</th>";
                            $HTML .= "<th rowspan=2>Tanggal</th>";
                            $HTML .= "<th rowspan=2>Jml Dok. Masuk</th>";
                            $HTML .= "<th colspan=2 style='background:#bde6bb'>OP Baru</th>";
                            $HTML .= "<th colspan=2 style='background:#e3bbe6'>Pemecahan</th>";
                            $HTML .= "<th colspan=2 style='background:#bbd2e6'>Penggabungan</th>";
                            $HTML .= "<th colspan=2 style='background:#f9bc88'>Mutasi</th>";
                            $HTML .= "<th colspan=2 style='background:#e6cc9a'>Perubahan</th>";
                            $HTML .= "<th colspan=2 style='background:#ffe065'>Pembatalan</th>";
                            $HTML .= "<th colspan=2 style='background:#bbe6cc'>Duplikat</th>";
                            $HTML .= "<th colspan=2 style='background:#fb8f8f'>Penghapusan</th>";
                            $HTML .= "<th colspan=2 style='background:#b990f4'>Pengurangan</th>";
                            $HTML .= "<th colspan=2 style='background:#acaba9'>Keberatan</th>";
                            $HTML .= "<th colspan=2 style='background:#d7d7d7'>Cetak SKNJOP</th>";
                            $HTML .= "</tr><tr>";
                            $HTML .= '<th class="prss">Proses</th>';
                            $HTML .= '<th class="slsi">Selesai</th>';
                            $HTML .= '<th class="prss">Proses</th>';
                            $HTML .= '<th class="slsi">Selesai</th>';
                            $HTML .= '<th class="prss">Proses</th>';
                            $HTML .= '<th class="slsi">Selesai</th>';
                            $HTML .= '<th class="prss">Proses</th>';
                            $HTML .= '<th class="slsi">Selesai</th>';
                            $HTML .= '<th class="prss">Proses</th>';
                            $HTML .= '<th class="slsi">Selesai</th>';
                            $HTML .= '<th class="prss">Proses</th>';
                            $HTML .= '<th class="slsi">Selesai</th>';
                            $HTML .= '<th class="prss">Proses</th>';
                            $HTML .= '<th class="slsi">Selesai</th>';
                            $HTML .= '<th class="prss">Proses</th>';
                            $HTML .= '<th class="slsi">Selesai</th>';
                            $HTML .= '<th class="prss">Proses</th>';
                            $HTML .= '<th class="slsi">Selesai</th>';
                            $HTML .= '<th class="prss">Proses</th>';
                            $HTML .= '<th class="slsi">Selesai</th>';
                            $HTML .= '<th class="prss">Proses</th>';
                            $HTML .= '<th class="slsi">Selesai</th>';
                            $HTML .= "</tr></thead>";

        return $HTML;
    }
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$n = $q->n;
$tab    = $q->tab;
$uname  = $q->u;
$uid    = isset($q->uid) ? $q->uid : '';

$srcTglAwal     = @isset($_REQUEST['srcTglAwal'])   ? $_REQUEST['srcTglAwal']   : date("01-m-Y");
$srcTglAkhir    = @isset($_REQUEST['srcTglAkhir'])  ? $_REQUEST['srcTglAkhir']  : ( (date('d')=='01') ? date("t-m-Y") : date("d-m-Y") ); 
$srcNama        = @isset($_REQUEST['srcNama'])      ? $_REQUEST['srcNama']      : false;

$User       = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$modNotaris = new TaxService(1, $uname);

print_r( $modNotaris->docRekap() );

function convertDate($date, $delimiter = '-')
{
    if ($date == null || $date == '') return '';

    $tmp = explode($delimiter, $date);
    return $tmp[2] . $delimiter . $tmp[1] . $delimiter . $tmp[0];
}

?>
<script type="text/javascript">
    $(".srcTgl").datepicker({
        dateFormat: 'dd-mm-yy'
    });
</script>