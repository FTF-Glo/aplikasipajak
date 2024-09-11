<?php

date_default_timezone_set('Asia/Jakarta');

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'portlet-new', '', dirname(__FILE__))) . '/';

require_once $sRootPath . 'inc/payment/inc-payment-db-c.php';
require_once $sRootPath . 'inc/PBB/dbUtils.php';
require_once $sRootPath . 'inc/phpexcel/Classes/PHPExcel.php';
require_once 'TraitExportExcel.php';

class Portlet
{
    public $appConfig;
    public $bulan = array(
		"01" => "Januari",
		"02" => "Februari",
		"03" => "Maret",
		"04" => "April",
		"05" => "Mei",
		"06" => "Juni",
		"07" => "Juli",
		"08" => "Agustus",
		"09" => "September",
		"10" => "Oktober",
		"11" => "November",
		"12" => "Desember"
	);

    protected $dbUtils;

    private $link = null;
    private $result;
    private $query;
    private $debug;

    // Variables
    protected $idwp;
    protected $nop;
    protected $tahun1;
    protected $tahun2;

    // Const
    const PBB_MAXPENALTY_MONTH = 24;
    const PBB_ONE_MONTH        = 0;
    const PBB_PENALTY_PERCENT  = 2;
    const PBB_PENALTY_PERCENT_1= 1;
    const DEFAULT_MYSQL_PORT   = '3306';
    const APP_ID               = 'aPBB';
    const MIN_TAHUN            = 2010;
    const BELUM_LUNAS_TEXT     = 'BELUM LUNAS';
    const NAMA_BAPENDA         = 'Lampung Selatan';

    use ExportExcel;

    public function __construct($dbUtils)
    {
        $this->dbUtils = $dbUtils;
        $this->appConfig = $this->getConfig();
    }

    public function set($varname, $value)
    {
        $this->$varname = $value;
        return $this;
    }

    public function get($varname)
    {
        return $this->$varname;
    }

    protected function getConfig()
    {
        $dbhosts = explode(':', ONPAYS_DBHOST);

        $this->link = mysqli_connect(
            $dbhosts[0],
            ONPAYS_DBUSER,
            ONPAYS_DBPWD,
            ONPAYS_DBNAME,
            (isset($dbhosts[1]) ? $dbhosts[1] : self::DEFAULT_MYSQL_PORT)
        );

        if (mysqli_connect_errno() !== 0 && $this->debug) {
            echo 'Error: ' . mysqli_connect_error() . PHP_EOL;
            exit;
        }

        $query = "SELECT * FROM central_app_config WHERE CTR_AC_AID = '" . self::APP_ID . "'";
        $rows = $this->dbQuery($query)->fetchAll();

        $config = array();
        foreach ($rows as $row) {
            $config[$row['CTR_AC_KEY']] = $row['CTR_AC_VALUE'];
        }

        return $config;
    }

    protected function openLink()
    {
        $link = mysqli_connect(
            $this->appConfig['GW_DBHOST'],
            $this->appConfig['GW_DBUSER'],
            $this->appConfig['GW_DBPWD'],
            $this->appConfig['GW_DBNAME'],
            $this->appConfig['GW_DBPORT']
        );

        if (mysqli_connect_errno() !== 0 && $this->debug) {
            echo 'Error: ' . mysqli_connect_error() . PHP_EOL;
            exit;
        }

        $this->link = $link;
        return $this;
    }

    protected function dbQuery($query)
    {
        $result = mysqli_query($this->link, $query);
        if ($result === false && $this->debug) {
            echo 'Error: ' . mysqli_error($this->link) . PHP_EOL;
            echo 'Query: ' . $query . PHP_EOL;
            exit;
        }
        $this->query = $query;
        $this->result = $result;
        return $this;
    }

    public function dbEscape($value)
    {
        return mysqli_real_escape_string($this->link, $value);
    } 

    protected function dbGetNumRows($sql, $select = '*')
    {
        $sql    = str_replace($select, 'COUNT(*) AS counts', $sql);
        $row    = $this->dbQuery($sql)->fetchRow();
        return isset($row['counts']) ? $row['counts'] : 0;
    }

    protected function fetchAll()
    {
        return mysqli_fetch_all($this->result, MYSQLI_ASSOC);
    }

    protected function fetchRow()
    {
        return mysqli_fetch_assoc($this->result);
    }

    public function getData()
    {
        $joinPengurangan = "LEFT JOIN (SELECT MAX(ID) AS MAX_ID_PENGURANGAN, NOP, TAHUN FROM pengurangan_denda WHERE DELETED_AT IS NULL GROUP BY NOP, TAHUN) C ON C.NOP = A.NOP AND C.TAHUN = A.SPPT_TAHUN_PAJAK LEFT JOIN pengurangan_denda B ON B.ID = C.MAX_ID_PENGURANGAN";

        $select = "IFNULL(B.NILAI, 0) AS NILAI_PENGURANGAN, IFNULL(B.PERSENTASE, 0) AS PERSENTASE_PENGURANGAN, B.ID AS ID_PENGURANGAN, A.*";

        $query = "SELECT {$select} FROM pbb_sppt A {$joinPengurangan} WHERE 1=1 ";
        
        if ($this->nop != "") {
            $query .= "AND A.NOP = '". $this->dbEscape($this->nop) ."' ";
        } else {
            if ($this->idwp == "") {
                return array();
            }
        }

        if ($this->idwp != "") {
            $query .= "AND A.ID_WP = '". $this->dbEscape($this->idwp) ."' ";
        }

        if ($this->tahun1 != "") {
            $query .= "AND A.SPPT_TAHUN_PAJAK >= '". $this->dbEscape($this->tahun1) ."' ";
        }

        if ($this->tahun2 != "") {
            $query .= "AND A.SPPT_TAHUN_PAJAK <= '". $this->dbEscape($this->tahun2) ."' ";
        }

        if ($this->tahun1 == "" && $this->tahun2 == "") {
            $query .= "AND A.SPPT_TAHUN_PAJAK >= '" . self::MIN_TAHUN . "'";
        }

        $queryCount = $query;
        $query .= " ORDER BY A.NOP,A.SPPT_TAHUN_PAJAK";

        if($_SERVER ['HTTP_USER_AGENT']=='Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/118.0'){
            // print_r($query);exit;
        }
        $rows = $this->openLink()->dbQuery($query)->fetchAll();
        $numRows = $this->dbGetNumRows($queryCount, $select);

        if (empty($rows)) {
            return array();
        }

        $total = array(
            'tagihan'             => 0,
            'tagihan_belum_bayar' => 0,
            'denda'               => 0,
            'tagihan_plus_denda'  => 0,
            'luas_bumi'           => 0,
            'luas_bangunan'       => 0,
            'njop_bumi'           => 0,
            'njop_bangunan'       => 0,
        );

        $rows = array_map(function ($row) use (&$total) {
            $row['IS_LUNAS'] = $row['PAYMENT_FLAG'] === "1";
            
            if (!$row['IS_LUNAS']) {
                // if($row['SPPT_TAHUN_PAJAK']>='2024'){
                    $denda = $this->dbUtils->getDenda(date('Y-m-d', strtotime($row["SPPT_TANGGAL_JATUH_TEMPO"])), $row["SPPT_PBB_HARUS_DIBAYAR"], self::PBB_ONE_MONTH, self::PBB_MAXPENALTY_MONTH, self::PBB_PENALTY_PERCENT_1);
                // }else{
                //     $denda = $this->dbUtils->getDenda(date('Y-m-d', strtotime($row["SPPT_TANGGAL_JATUH_TEMPO"])), $row["SPPT_PBB_HARUS_DIBAYAR"], self::PBB_ONE_MONTH, self::PBB_MAXPENALTY_MONTH, self::PBB_PENALTY_PERCENT);
                // }
                $row['PBB_DENDA'] = $denda - $row['NILAI_PENGURANGAN'];
                $row['TAGIHAN_PLUS_DENDA'] = $row['SPPT_PBB_HARUS_DIBAYAR'] + $row['PBB_DENDA'];
                $row['TAGIHAN_BELUM_BAYAR'] = $row['SPPT_PBB_HARUS_DIBAYAR'];
            } else {
                $row['PBB_DENDA'] = 0;
                $row['TAGIHAN_PLUS_DENDA'] = 0;
                $row['TAGIHAN_BELUM_BAYAR'] = 0;
            }

            $row['NJOP_BUMI_M2'] = $row['OP_NJOP_BUMI'] / $row['OP_LUAS_BUMI'];
            $row['NJOP_BANGUNAN_M2'] = $row['OP_NJOP_BANGUNAN'] / $row['OP_LUAS_BANGUNAN'];

            $total['tagihan']               += $row['SPPT_PBB_HARUS_DIBAYAR'];
            $total['tagihan_belum_bayar']   += $row['TAGIHAN_BELUM_BAYAR'];
            $total['denda']                 += $row['PBB_DENDA'];
            $total['tagihan_plus_denda']    += $row['TAGIHAN_PLUS_DENDA'];
            $total['luas_bumi']             += $row['OP_LUAS_BUMI'];
            $total['luas_bangunan']         += $row['OP_LUAS_BANGUNAN'];
            $total['njop_bumi']             += $row['OP_NJOP_BUMI'];
            $total['njop_bangunan']         += $row['OP_NJOP_BANGUNAN'];

            return $row;
        }, $rows);

        return array(
            'rows' => $rows,
            'total' => $total,
            'numRows' => $numRows
        );
    }

    public function formatRupiah($value)
    {
        return 'Rp.' . number_format($value, 2, ',', '.');
    }

    public function formatDate($date, $separator = '/')
    {
        return date("d{$separator}m{$separator}Y", strtotime($date));
    }

    public function formatHumanDate($date)
    {
        $timestamp = strtotime($date);
        return date('d', $timestamp) . ' ' . $this->bulan[date('m')] . ' ' . date('Y', $timestamp);
    }

    public function formatNop($nop) 
    {
        return substr($nop,0,2).'.'.substr($nop,2,2).'.'.substr($nop,4,3).'.'.substr($nop,7,3).'.'.substr($nop,10,3).'-'.substr($nop,13,4).'.'.substr($nop,17,1);
    }

    public function getTahunFilter($selected = '')
    {
        $min = self::MIN_TAHUN;
        $max = $this->appConfig['tahun_tagihan'];

        $html = '';
        for ($i = $max; $i >= $min; $i--) {
            $html .= '<option value="' . $i . '" ' . ($selected == $i ? 'selected' : '') . '>' . $i . '</option>';
        }

        return $html;
    }

    public function getRequest($key, $default = '')
    {
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
    }

}

/** ALDES */

$portlet = new Portlet((new DbUtils(null)));
