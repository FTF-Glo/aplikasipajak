<?php
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pencatatan_pembayaran', '', dirname(__FILE__))) . '/';

require_once $sRootPath . 'inc/PBB/SimpleDB.php';
require_once $sRootPath . 'inc/PBB/dbUtils.php';
require_once $sRootPath . 'portlet-new/Portlet.php';

class PencatatanPembayaran extends SimpleDB
{
    protected $table = 'pbb_sppt';

    private $centraldata = null;
    private $dbUtils;
    private $portlet;

    private $uname;

    const CODE_SUCCESS = '0000';
    const CODE_ERROR   = '0001';
    const CODE_LUNAS   = '0099';
    const MODE_CETAK_ULANG = 'cetak_ulang';

    private $requests = array();

    public function __construct()
    {
        parent::__construct();
        $this->dbOpen('gw');

        $this->dbUtils = new DbUtils(null);
        $this->portlet = new Portlet($this->dbUtils);

        $centraldata       = isset($_COOKIE['centraldata']) ? base64_decode($_COOKIE['centraldata']) : null;
        $this->centraldata = $centraldata ? json_decode($centraldata, true) : null;
        $this->uname       = $this->getUser();
    }

    public function getUser()
    {
        if (!isset($this->centraldata['uid'])) {
            return null;
        }

        $link = $this->dbOpen('sw', true);
        $sql  = "SELECT * FROM central_user WHERE CTR_U_ID = '". $this->dbEscape($this->centraldata['uid'], $link) ."'";
        $row  = $this->dbQuery($sql, $link)->fetchRow();

        return !empty($row) ? $row['CTR_U_UID'] : '';
    }

    public function inquiry($nop, $tahun, $returnRow = false)
    {
        $dataTagihan = $this->portlet
                        ->set('nop', $nop)
                        ->set('tahun1', $tahun)
                        ->set('tahun2', $tahun)
                        ->getData();

        if (empty($dataTagihan)) {
            return array();
        }

        $rows = $dataTagihan['rows'];

        $result = array();
        foreach ($rows as $row) {
            if ($row['IS_LUNAS']) {
                $row['PBB_DENDA'] = $row['PBB_TOTAL_BAYAR'] - $row['SPPT_PBB_HARUS_DIBAYAR'];
                $row['TAGIHAN_PLUS_DENDA'] = $row['PBB_TOTAL_BAYAR'];
            }

            $_result = array(
                'PAYMENT_FLAG'             => $row['PAYMENT_FLAG'],
                'PAYMENT_PAID'             => $row['PAYMENT_PAID'],
                'SPPT_DENDA'               => number_format($row['PBB_DENDA'], 0, ',', '.'),
                'SPPT_PBB_HARUS_DIBAYAR'   => number_format($row['SPPT_PBB_HARUS_DIBAYAR'], 0, ',', '.'),
                'SPPT_TANGGAL_JATUH_TEMPO' => $row['SPPT_TANGGAL_JATUH_TEMPO'],
                'TOTAL_TAGIHAN'            => $row['TAGIHAN_PLUS_DENDA'],
                'TOTAL_TAGIHAN_VIEW'       => number_format($row['TAGIHAN_PLUS_DENDA'], 0, ',', '.'),
                'WP_ALAMAT'                => $row['WP_ALAMAT'],
                'WP_KECAMATAN'             => $row['WP_KECAMATAN'],
                'WP_KELURAHAN'             => $row['WP_KELURAHAN'],
                'WP_KODEPOS'               => $row['WP_KODEPOS'],
                'WP_KOTAKAB'               => $row['WP_KOTAKAB'],
                'WP_NAMA'                  => $row['WP_NAMA'],
                'WP_RT'                    => $row['WP_RT'],
                'WP_RW'                    => $row['WP_RW'],
            );

            if ($returnRow) {
                $_result['row'] = $row;
            }

            $result[] = $_result;
        }

        return $result;
    }

    public function pay($nop, $tahun, $tglBayar = null, $mode = null)
    {
        $inquiry = $this->inquiry($nop, $tahun, true);
        
        if (empty($inquiry)) {
            return self::CODE_ERROR;
        }

        $data = $inquiry[0];

        if ($data['row']['IS_LUNAS']) {
            return self::CODE_LUNAS;
        }

        $sets = $this->dbSet(array(
            'PAYMENT_FLAG'            => 1,
            'PAYMENT_PAID'            => $tglBayar ? $tglBayar : date('Y-m-d H:i:s'),
            'PBB_DENDA'               => $data['row']['PBB_DENDA'],
            'PBB_TOTAL_BAYAR'         => $data['row']['TAGIHAN_PLUS_DENDA'],
            'PAYMENT_OFFLINE_USER_ID' => $this->uname,
        ));

        $sqlUpdate = "UPDATE {$this->table} SET {$sets} WHERE NOP = '". $this->dbEscape($nop) ."' AND SPPT_TAHUN_PAJAK = '". $this->dbEscape($tahun) ."'";
        if ($mode != self::MODE_CETAK_ULANG) {
            $this->dbQuery($sqlUpdate);
            $this->insertToLog(array(
                'NOP'           => $nop,
                'TAHUN'         => $tahun,
                'TAGIHAN'       => $data['row']['SPPT_PBB_HARUS_DIBAYAR'],
                'DENDA'         => $data['row']['PBB_DENDA'],
                'TOTAL_BAYAR'   => $data['row']['TAGIHAN_PLUS_DENDA'],
                'TANGGAL_BAYAR' => $tglBayar ? $tglBayar : date('Y-m-d H:i:s')
            ));
        }

        return self::CODE_SUCCESS;
    }

    public function insertToLog($data)
    {
        $insert = array(
            'NOP'           => $data['NOP'],
            'TAHUN'         => $data['TAHUN'],
            'TAGIHAN'       => $data['TAGIHAN'],
            'DENDA'         => $data['DENDA'],
            'TOTAL_BAYAR'   => $data['TOTAL_BAYAR'],
            'TANGGAL_BAYAR' => $data['TANGGAL_BAYAR'],
            'CATATAN'       => $this->requests['catatan'],
            'CREATED_BY'    => $this->uname,
        );

        return $this->dbQuery("INSERT INTO pencatatan_pembayaran SET " . $this->dbSet($insert));
    }

    public function request($request, $key)
    {
        if (!isset($request[$key])) {
            return null;
        }

        $this->requests[$key] = $request[$key];

        return $this->requests[$key];
    }
}
