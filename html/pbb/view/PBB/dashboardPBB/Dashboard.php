<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'dashboardPBB', '', dirname(__FILE__))) . '/';

require_once $sRootPath . 'inc/PBB/SimpleDB.php';
require_once $sRootPath . 'inc/PBB/dbUtils.php';
require_once $sRootPath . 'inc/PBB/HitungDendaMassal.php';

class Dashboard extends SimpleDB
{
    protected $table = 'pbb_sppt';
    protected $today;
    protected $thisMonth;
    protected $thisYear;
    protected $time;

    const MIN_TAHUN = 2000;

    public function __construct()
    {
        parent::__construct();

        $this->dbOpen('gw');
        $this->today     = date('Y-m-d');
        $this->thisMonth = date('m');
        $this->thisYear  = date('Y');
        $this->time      = date('Y-m-d H:i:s');
    }

    public function getData() // 45.58 detik
    {
        $sql = "SELECT 
                    SUM(IFNULL(A.SPPT_PBB_HARUS_DIBAYAR, 0)) AS SUM_KETETAPAN,
                    SUM(IF(A.PAYMENT_FLAG = 1, IFNULL(A.PBB_TOTAL_BAYAR, 0), 0)) AS SUM_REALISASI,
                    SUM(IF(A.PAYMENT_FLAG = 1, IFNULL(A.PBB_TOTAL_BAYAR, 0) - IFNULL(A.SPPT_PBB_HARUS_DIBAYAR, 0), IFNULL(D.PBB_DENDA, 0))) AS SUM_DENDA,
                    A.SPPT_TAHUN_PAJAK AS TAHUN_PAJAK
                FROM 
                    {$this->table} A
                    LEFT JOIN pbb_denda D ON D.NOP = A.NOP AND D.SPPT_TAHUN_PAJAK = A.SPPT_TAHUN_PAJAK
                    -- LEFT JOIN (SELECT * FROM {$this->table} WHERE PAYMENT_FLAG = 1) E ON E.NOP = A.NOP AND E.SPPT_TAHUN_PAJAK = A.SPPT_TAHUN_PAJAK
                WHERE " . $this->getFilter() . " AND (A.SPPT_TAHUN_PAJAK <> '' AND A.SPPT_TAHUN_PAJAK IS NOT NULL AND A.SPPT_TAHUN_PAJAK >= 2013) 
                GROUP BY 
                    A.SPPT_TAHUN_PAJAK
                ORDER BY 
                    A.SPPT_TAHUN_PAJAK ASC";

        $rows = $this->dbQuery($sql)->fetchAll();

        if (empty($rows)) {
            return null;
        }

        $result = array();
        foreach ($rows as $row) {
            $result[] = array(
                'SUM_DENDA'       => $row['SUM_DENDA'] + 0,
                'SUM_REALISASI'   => $row['SUM_REALISASI'] + 0,
                'SUM_KETETAPAN'   => $row['SUM_KETETAPAN'] + 0,
                'TAHUN_PAJAK'     => $row['TAHUN_PAJAK'],
            );
        }

        return $result;
    }

    public function getTappingRealisasi($tahun)
    {
        $link = $this->dbOpen('gw', true);
        $sql = "SELECT
                    A.OP_KECAMATAN_KODE                                 AS KODE,
                    IFNULL(KEC.CPC_TKC_KECAMATAN, A.OP_KECAMATAN)       AS KECAMATAN,
                    SUM(A.SPPT_PBB_HARUS_DIBAYAR)                       AS KETETAPAN,
                    SUM(IF(A.PAYMENT_FLAG = 1, A.PBB_TOTAL_BAYAR, 0))   AS REALISASI,
                    SUM(IFNULL(B.PBB_DENDA, 0))                         AS DENDA
                FROM pbb_sppt A
                LEFT JOIN pbb_denda B ON A.NOP = B.NOP AND A.SPPT_TAHUN_PAJAK = B.SPPT_TAHUN_PAJAK
                LEFT JOIN cppmod_tax_kecamatan KEC ON A.OP_KECAMATAN_KODE = KEC.CPC_TKC_ID
                WHERE
                    A.SPPT_TAHUN_PAJAK = '$tahun' AND 
                    A.OP_KECAMATAN<>'' AND 
                    A.OP_KECAMATAN<>'-' AND 
                    A.OP_KECAMATAN IS NOT NULL 
                GROUP BY A.OP_KECAMATAN_KODE
                ORDER BY IFNULL(KEC.CPC_TKC_URUTAN, A.OP_KECAMATAN_KODE) ASC";

        $sql_kec = "SELECT CPC_TKC_ID AS KODE, CPC_TKC_KECAMATAN AS KECAMATAN FROM cppmod_tax_kecamatan ORDER BY CPC_TKC_ID ASC";
        $query = $this->dbQuery($sql, $link)->fetchAll(); //print_r($query);exit;
        $query_kec = $this->dbQuery($sql_kec, $link)->fetchAll();// print_r($query_kec);exit;
        $kodekecada = [];
        $result = [];
        foreach ($query as $r) {
            $obj = (object)[];
            $obj->KECAMATAN = $r['KECAMATAN'];
            $target         = $r['KETETAPAN']+$r['DENDA'];
            $obj->TARGET    = number_format($target,0,',','.');
            $realisasi      = $r['REALISASI'];
            $obj->REALISASI = number_format($realisasi,0,',','.');
            $persen         = ($realisasi > 0 && $target > 0) ? ($realisasi / $target * 100) : 0;
            $persenx        = (float)number_format($persen,2,'.',',');
            // $obj->PERSEN    = str_replace('.',',',$persenx);
            $obj->PERSEN    = ($persen<=100) ? str_replace('.',',',$persenx).' %' : '100 % + '.str_replace('.',',',($persenx-100)).' %';
            $obj->BG        = ($r['REALISASI']>0) ? 'green':'gray';
            $obj->TOP       = ($r['REALISASI']<=0) ? 'danger':(($persen<=80 || $persen>100)?'danger':'primary');
            array_push($result,$obj);
            array_push($kodekecada,$r['KODE']);
        }

        foreach ($query_kec as $k) {
            if(!in_array($k['KODE'],$kodekecada)){
                $obj = (object)[];
                $obj->KECAMATAN = $k['KECAMATAN'];
                $obj->TARGET    = 0;
                $obj->REALISASI = 0;
                $obj->PERSEN    = 0;
                $obj->BG        = 'orange';
                $obj->TOP       = 'danger';
                array_push($result,$obj);
            }
        }
        // print_r($kodekecada);exit;
        return $result ;
    }

    public function getKecamatan()
    {
        $link = $this->dbOpen('sw', true);
        $sql = "SELECT * FROM cppmod_tax_kecamatan ORDER BY CPC_TKC_KECAMATAN ASC";

        return $this->dbQuery($sql, $link)->fetchAll();
    }

    public function getKelurahan($kcid = null)
    {
        $link = $this->dbOpen('sw', true);

        $where = $kcid !== null ? 'CPC_TKL_KCID = "' . $this->dbEscape($kcid, $link) . '"' : '1=1';
        $sql = "SELECT * FROM cppmod_tax_kelurahan WHERE {$where} ORDER BY CPC_TKL_KELURAHAN ASC";

        return $this->dbQuery($sql, $link)->fetchAll();
    }

    public function request($key, $default = null)
    {
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
    }

    /**
     * @param $n
     * @return string
     * Use to convert large positive numbers in to short form like 1K+, 100K+, 199K+, 1M+, 10M+, 1B+ etc
     * source: https://gist.github.com/RadGH/84edff0cc81e6326029c#gistcomment-2643113
     */
    public function number_format_short($n)
    {
        if ($n >= 0 && $n < 1000) {
            // 1 - 999
            $n_format = floor($n);
            $suffix = '';
        } else if ($n >= 1000 && $n < 1000000) {
            // 1k-999k
            $n_format = floor($n / 1000);
            $suffix = 'RB+';
        } else if ($n >= 1000000 && $n < 1000000000) {
            // 1m-999m
            $n_format = floor($n / 1000000);
            $suffix = 'JT+';
        } else if ($n >= 1000000000 && $n < 1000000000000) {
            // 1b-999b
            $n_format = floor($n / 1000000000);
            $suffix = 'M+';
        } else if ($n >= 1000000000000) {
            // 1t+
            $n_format = floor($n / 1000000000000);
            $suffix = 'T+';
        }
    
        return !empty($n_format . $suffix) ? $n_format . $suffix : 0;
    }

    /** counter */
    public function getCounterTunggakan()
    {
        $sql = "SELECT 
                    SUM(IFNULL(A.SPPT_PBB_HARUS_DIBAYAR, 0)) AS SUM_KETETAPAN,
                    SUM(IFNULL(D.PBB_DENDA, 0)) AS SUM_DENDA
                FROM 
                    {$this->table} A
                    LEFT JOIN pbb_denda D ON D.NOP = A.NOP AND D.SPPT_TAHUN_PAJAK = A.SPPT_TAHUN_PAJAK
                WHERE (PAYMENT_FLAG <> 1 OR PAYMENT_FLAG IS NULL) AND A.SPPT_TAHUN_PAJAK <> '' AND A.SPPT_TAHUN_PAJAK IS NOT NULL";

        return $this->dbQuery($sql)->fetchRow();
    }

    public function getCounterRealisasi($period = 'all')
    {
        if ($period == 'today') {
            $this->setFilter("DATE(A.PAYMENT_PAID) =", $this->today);
        } else if ($period == 'this month') {
            $this->setFilter("YEAR(A.PAYMENT_PAID) =", $this->thisYear);
            $this->setFilter("MONTH(A.PAYMENT_PAID) =", $this->thisMonth);
        } else if ($period == 'this year') {
            $this->setFilter("YEAR(A.PAYMENT_PAID) =", $this->thisYear);
        }

        if ($period != 'all') {
            $this->setFilter("(A.PAYMENT_PAID IS NOT NULL OR A.PAYMENT_PAID <> '')");
        }

        $sql = "SELECT 
                    SUM(IFNULL(A.PBB_TOTAL_BAYAR, 0)) AS SUM_REALISASI
                FROM 
                    {$this->table} A
                WHERE ". $this->getFilter() ." AND A.PAYMENT_FLAG = 1 AND A.SPPT_TAHUN_PAJAK <> '' AND A.SPPT_TAHUN_PAJAK IS NOT NULL";

        return $this->dbQuery($sql)->fetchRow();
    }

    public function getCounterNOP($period = 'all', $status = 'sudah bayar')
    {
        $isSudahBayar = $status === 'sudah bayar';

        if ($isSudahBayar) {
            if ($period == 'today') {
                $this->setFilter("DATE(A.PAYMENT_PAID) =", $this->today);
            } else if ($period == 'this month') {
                $this->setFilter("YEAR(A.PAYMENT_PAID) =", $this->thisYear);
                $this->setFilter("MONTH(A.PAYMENT_PAID) =", $this->thisMonth);
            } else if ($period == 'this year') {
                $this->setFilter("YEAR(A.PAYMENT_PAID) =", $this->thisYear);
            }

            if ($period != 'all') {
                $this->setFilter("(A.PAYMENT_PAID IS NOT NULL OR A.PAYMENT_PAID <> '')");
            }

            $this->setFilter('A.PAYMENT_FLAG =', 1);
        } else {
            $this->setFilter('(PAYMENT_FLAG <> 1 OR PAYMENT_FLAG IS NULL)');
        }

        $sql = "SELECT 
                    COUNT(A.NOP) AS COUNT_NOP
                FROM 
                    {$this->table} A
                WHERE ". $this->getFilter() ." AND A.SPPT_TAHUN_PAJAK <> '' AND A.SPPT_TAHUN_PAJAK IS NOT NULL";

        return $this->dbQuery($sql)->fetchRow();
    }
}

/** ALDES */
