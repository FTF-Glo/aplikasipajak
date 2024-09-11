<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
date_default_timezone_set("Asia/Jakarta");

require_once 'SimpleDB.php';
require_once $sRootPath . 'portlet-new/Portlet.php';

class HitungDendaMassal extends SimpleDB
{
    protected $table      = 'pbb_denda';
    protected $table_sppt = 'pbb_sppt';
    protected $time;
    protected $today;
    protected $tgldendalama;
    protected $tgldendabaru;

    public function __construct()
    {
        parent::__construct();
        $this->dbOpen('gw');

        $this->time  = date('Y-m-d H:i:s');
        $this->today = date('Y-m-d');
        $this->tgldendalama = '2023-12-31';
        $this->tgldendabaru = '2024-01-01';
    }

    public function execute()
    {

        $this->dbQuery("TRUNCATE TABLE {$this->table}");
        // $this->dbQuery("INSERT INTO 
        //     {$this->table} 
        // SELECT 
        //     NOP, 
        //     SPPT_TAHUN_PAJAK, 
        //     HITUNGDENDA('{$this->today}', DATE(SPPT_TANGGAL_JATUH_TEMPO), IFNULL(SPPT_PBB_HARUS_DIBAYAR, 0), ". Portlet::PBB_ONE_MONTH .", ". Portlet::PBB_MAXPENALTY_MONTH .", IF(SPPT_TAHUN_PAJAK>=2024, ". Portlet::PBB_PENALTY_PERCENT_1 .",". Portlet::PBB_PENALTY_PERCENT .")),
        //     '{$this->time}' AS SEKARANG 
        // FROM 
        //     {$this->table_sppt}
        // WHERE SPPT_TANGGAL_JATUH_TEMPO < '{$this->today}' AND (PAYMENT_FLAG <> '1' OR PAYMENT_FLAG IS NULL)");
        $this->dbQuery("INSERT INTO 
            {$this->table} 
        SELECT 
            NOP, 
            SPPT_TAHUN_PAJAK, 
            (
                HITUNGDENDA('{$this->tgldendalama}', DATE(SPPT_TANGGAL_JATUH_TEMPO), IFNULL(SPPT_PBB_HARUS_DIBAYAR, 0), ". Portlet::PBB_ONE_MONTH .", ". Portlet::PBB_MAXPENALTY_MONTH .", ". Portlet::PBB_PENALTY_PERCENT .") 
                + HITUNGDENDA('{$this->today}', DATE('{$this->tgldendabaru}'), IFNULL(SPPT_PBB_HARUS_DIBAYAR, 0), ". Portlet::PBB_ONE_MONTH .", ". Portlet::PBB_MAXPENALTY_MONTH .", ". Portlet::PBB_PENALTY_PERCENT_1 .")
            ),
            '{$this->time}' AS SEKARANG 
        FROM 
            {$this->table_sppt}
        WHERE SPPT_TANGGAL_JATUH_TEMPO < '{$this->today}' AND (PAYMENT_FLAG <> '1' OR PAYMENT_FLAG IS NULL)");
        
        return $this->getLastDenda();

        // 2021-10-03 01:09:15 - 1.7 Menit - Estimasi waktu yg diperlukan
    }

    public function executeAsync()
    {
        //$this->prep();

        $this->dbQuery("TRUNCATE TABLE {$this->table}");
        $sql = "INSERT INTO 
                    {$this->table} 
                SELECT 
                    NOP, 
                    SPPT_TAHUN_PAJAK, 
                    HITUNGDENDA('{$this->today}', DATE(SPPT_TANGGAL_JATUH_TEMPO), IFNULL(SPPT_PBB_HARUS_DIBAYAR, 0), ". Portlet::PBB_ONE_MONTH .", ". Portlet::PBB_MAXPENALTY_MONTH .", ". Portlet::PBB_PENALTY_PERCENT ."),
                    '{$this->time}' AS SEKARANG 
                FROM 
                    {$this->table_sppt}
                WHERE SPPT_TANGGAL_JATUH_TEMPO < '{$this->today}' AND (PAYMENT_FLAG <> '1' OR PAYMENT_FLAG IS NULL)";
        
        mysqli_query($this->link, $sql, MYSQLI_ASYNC);
    }

    public function getLastDenda()
    {
        $row = $this->dbQuery("SELECT TANGGAL_HITUNG_DENDA FROM {$this->table} ORDER BY TANGGAL_HITUNG_DENDA DESC LIMIT 1")->fetchRow();
        return isset($row['TANGGAL_HITUNG_DENDA']) ? $row['TANGGAL_HITUNG_DENDA'] : false;
    }

    private function prep()
    {
        $this->dbQuery("DROP FUNCTION IF EXISTS `getMonthsInterval`");
        $this->dbQuery("DROP FUNCTION IF EXISTS `hitungDenda`");
        $this->dbQuery("CREATE DEFINER = CURRENT_USER FUNCTION `getMonthsInterval`(`now` date,`toDate` date) RETURNS int(11)
        BEGIN
            DECLARE toDateYear INT;
            DECLARE toDateMonth INT;
            DECLARE toDateDay INT;
            DECLARE nowYear INT;
            DECLARE nowMonth INT;
            DECLARE nowDay INT;
            DECLARE addDay INT;
            DECLARE monthsInYear INT;
        
            SET toDateYear = YEAR(`toDate`);
            SET toDateMonth = MONTH(`toDate`);
            SET toDateDay = DAY(`toDate`);
            SET nowYear = YEAR(`now`);
            SET nowMonth = MONTH(`now`);
            SET nowDay = DAY(`now`);
            SET addDay = IF(nowDay > toDateDay, 1, 0);
            SET monthsInYear = 12;
        
            RETURN ((nowYear - toDateYear) * monthsInYear) + nowMonth - toDateMonth + addDay;
        END");
        $this->dbQuery("CREATE DEFINER = CURRENT_USER FUNCTION `hitungDenda`(`now` date,`dueDate` date,`bill` double,`daysInMonth` int,`maxPenaltyMonth` int,`penaltyPercentagePerMonth` double) RETURNS double
        BEGIN
            DECLARE monthInterval INT;
        
            SET penaltyPercentagePerMonth = penaltyPercentagePerMonth / 100;
            SET monthInterval = IF(daysInMonth = 0, getMonthsInterval(`now`, dueDate), FLOOR(DATEDIFF(`now`, dueDate) * daysInMonth));
            SET monthInterval = IF(monthInterval >= maxPenaltyMonth, maxPenaltyMonth, IF(monthInterval <= 0, 0, monthInterval));
        
            
            RETURN FLOOR(penaltyPercentagePerMonth * monthInterval * bill);
        END");
    }
}

/** ALDES */