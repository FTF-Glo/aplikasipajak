<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class HitungDenda
{
    const PBB_ONE_MONTH        = 0;
    const PBB_MAXPENALTY_MONTH = 24;
    const PBB_PENALTY_PERCENT  = 2;

    public $now;

    public function __construct($now)
    {
        $this->now = strtotime($now);
    }

    public function get($dueDate, $bill, $daysInMonth = null, $maxPenaltyMonth = null, $penaltyPercentagePerMonth = null)
    {
        $daysInMonth = $daysInMonth !== null ? $daysInMonth : self::PBB_ONE_MONTH;
        $maxPenaltyMonth = $maxPenaltyMonth !== null ? $maxPenaltyMonth : self::PBB_MAXPENALTY_MONTH;
        $penaltyPercentagePerMonth = $penaltyPercentagePerMonth !== null ? $penaltyPercentagePerMonth : self::PBB_PENALTY_PERCENT;

        $secondsInDay = 86400;
        $penaltyPercentagePerMonth = $penaltyPercentagePerMonth / 100;
        $dueDate = strtotime(date('Y-m-d', strtotime($dueDate)) . ' 23:59:59');
        $monthInterval = $daysInMonth === 0 ? $this->getMonthsInterval(date('Y-m-d', $dueDate)) : floor(($this->now - $dueDate) / ($secondsInDay * $daysInMonth));
        $monthInterval = $monthInterval >= $maxPenaltyMonth ? $maxPenaltyMonth : ($monthInterval <= 0 ? 0 : $monthInterval);
        
		return floor($penaltyPercentagePerMonth * $monthInterval * $bill);
    }

    public function getMonthsInterval($dueDate)
    {
        $monthsInYear = 12;
        
        $dueDate = strtotime($dueDate);
        $dueDateYear = date('Y', $dueDate);
        $nowYear = date('Y', $this->now);
        $dueDateMonth = date('m', $dueDate);
        $nowMonth = date('m', $this->now);
		$dueDateDay = date('d', $dueDate);
        $nowDay = date('d', $this->now);
		
		$addHari = $nowDay > $dueDateDay ? 1 : 0;

        return ((($nowYear - $dueDateYear) * $monthsInYear) + ($nowMonth - $dueDateMonth)) + $addHari;
    }
}
