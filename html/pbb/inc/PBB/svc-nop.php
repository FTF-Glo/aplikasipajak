<?php
define('DEBUG', true);
define('LOG_DMS_FILENAME', true);

class SvcNOP
{

    private $dbSpec = null;

    public function __construct($dbSpec)
    {
        $this->dbSpec = $dbSpec;
    }

    //DATABASE FUNCTION
    private function getNir($nop, $znt)
    {
        $nop = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nop));
        $znt = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($znt));

        $loc = substr($nop, 0, 10);

        $query = "SELECT * FROM cppmod_pbb_znt WHERE CPM_KODE_LOKASI='$loc' AND CPM_KODE_ZNT='$znt'";

        // echo $query;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    private function getKelasBumi($nilai)
    {
        $nilai = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($nilai));

        $query = "SELECT * FROM cppmod_pbb_kelas_bumi WHERE CPM_NILAI_BAWAH<'$nilai' AND '$nilai'<=CPM_NILAI_ATAS";

        // echo $query;

        if ($this->dbSpec->sqlQueryRow($query, $res)) {
            return $res;
        }
    }

    //GENERAL FUNCTION
    public function getNilai($nop, $znt)
    {
        $res = $this->getNir($nop, $znt);

        return $res[0]['CPM_NIR'];
    }

    public function getKelasNjop($nilai, &$kelas, &$njop)
    {
        $res = $this->getKelasBumi($nilai);

        $kelas = $res[0]['CPM_KELAS'];
        $njop = $res[0]['CPM_NJOP_M2'];
    }

    #ardi : get nomor urut nop

    public function getNoUrut($nop)
    {
        //console.log($nop);
        $sql = "select max(SUBSTRING(CPM_NOP,-5,4)) as CPM_NOP FROM cppmod_pbb_generate_nop where SUBSTRING(CPM_NOP,1,13)='$nop'";
        $this->dbSpec->sqlQueryRow($sql, $res);
        return $res[0]['CPM_NOP'];
    }

    public function checkNOP($nop, $uname)
    {
        $hasil = false;

        $sql = "select CPM_NOP FROM cppmod_pbb_generate_nop where CPM_NOP='$nop'";
        $this->dbSpec->sqlQuery($sql, $res);
        if (mysqli_num_rows($res) == 0) {
            //            $date = date("Y-m-d");
            //            $insert = "insert into cppmod_pbb_generate_nop values ('{$nop}','{$uname}','{$date}')";
            //            $this->dbSpec->sqlQuery($insert, $res);
            $hasil = true;
        }
        return $hasil;
    }

    public function insertNOP($nop, $enop, $nourut, $uname)
    {
        $date = date("Y-m-d");
        $nopComp = $nop . $nourut . $enop;
        $sql = "insert into cppmod_pbb_generate_nop values ('{$nopComp}','{$uname}','{$date}')";
        if ($this->dbSpec->sqlQueryRow($sql, $res)) {
            return $res;
        }
    }
}

require_once("../payment/db-payment.php");
require_once("../payment/inc-payment-db-c.php");
require_once("../central/dbspec-central.php");
require_once("../payment/json.php");

$DBLink = NULL;
$DBConn = NULL;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_DMS_FILENAME);
    exit(1);
}

$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
$svcNOP = new SvcNOP($dbSpec);

//variable for input program: NOP dan ZNT
$getSvcRequest = (@isset($_REQUEST['req']) ? $_REQUEST['req'] : '');
$getSvcRequest = base64_decode($getSvcRequest);
$json = new Services_JSON();
$prm = $json->decode($getSvcRequest);

$nop = $prm->nop;
$enop = isset($prm->enop) ? $prm->enop : '';
$uname = $prm->uname;
$method = $prm->method;

if ($method == 'generate') {
    #$svcNOP->getKelasNjop($nilai, $kelas, $njop);
    $lastNoUrut = (int) $svcNOP->getNoUrut($nop);
    $nourut = str_pad($lastNoUrut + 1, 4, "0", STR_PAD_LEFT);
    // if ($lastNoUrut >= 2000) {
    // $nourut = str_pad($lastNoUrut + 1, 3, "0", STR_PAD_LEFT);
    // //$svcNOP->insertNOP($nop, $enop, $nourut, $uname);
    // } else {
    // $nourut = "2" . str_pad(1, 3, "0", STR_PAD_LEFT);
    // //$svcNOP->insertNOP($nop, $enop, $nourut, $uname);
    // }
    $response = array();
    $response['r'] = true;
    $response['d']['nourut'] = $nourut;


    $val = $json->encode($response);
    echo $val;
} elseif ($method == 'check') {
    $hasil = $svcNOP->checkNOP($nop, $uname);

    $response = array();
    $response['r'] = $hasil;

    $val = $json->encode($response);
    echo $val;
}
