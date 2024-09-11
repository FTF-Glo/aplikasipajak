<?php

require_once 'SimpleDB.php';

class FlaggingKolektif extends SimpleDB
{
    public function __construct()
    {
        parent::__construct();
        $this->dbOpen('gw');
    }

    public function execute()
    {
        $sql = "SELECT
                    COUNT(a.CPM_CGM_ID) AS COUNT_MEMBER,
                    COUNT(c.NOP)        AS COUNT_MEMBER_LUNAS,
                    a.CPM_CGM_ID        AS GROUP_ID,
                    b.CPM_CG_STATUS     AS GROUP_STATUS,
                    b.CPM_CG_NAME       AS GROUP_NAME
                FROM
                    `cppmod_cg_member` a
                    INNER JOIN cppmod_collective_group b ON a.CPM_CGM_ID = b.CPM_CG_ID 
                    LEFT JOIN pbb_sppt c ON a.CPM_CGM_NOP = c.NOP AND a.CPM_CGM_TAX_YEAR = c.SPPT_TAHUN_PAJAK AND c.PAYMENT_FLAG = 1
                WHERE
                    b.CPM_CG_STATUS IN (1, 2)
                GROUP BY
                    a.CPM_CGM_ID";
        
        $setGroupFinal = array();
        $setGroupLunas = array();
        $statusSetGroupFinal = true;
        $statusSetGroupLunas = true;

        $rows = $this->dbQuery($sql)->fetchAll();
        
        if (!empty($rows)) foreach ($rows as $row) {
            $_data = array(
                'ID'                 => $row['GROUP_ID'],
                'MEMBER'             => $row['GROUP_NAME'],
                'COUNT_MEMBER'       => $row['COUNT_MEMBER'],
                'COUNT_MEMBER_LUNAS' => $row['COUNT_MEMBER_LUNAS'],
                'SELISIH'            => $row['COUNT_MEMBER'] - $row['COUNT_MEMBER_LUNAS']
            );
            // jika group sudah terflag lunas, tetapi ada member yang belum lunas
            if ($row['COUNT_MEMBER'] != $row['COUNT_MEMBER_LUNAS'] && $row['GROUP_STATUS'] == 2) {
                $setGroupFinal[] = $_data;
            }
            // jika group belum terflag lunas, tetapi semua member sudah lunas
            if ($row['COUNT_MEMBER'] == $row['COUNT_MEMBER_LUNAS'] && $row['GROUP_STATUS'] == 1) {
                $setGroupLunas[] = $_data;
            }
        }

        if (!empty($setGroupFinal)) {
            $statusSetGroupFinal = $this->dbQuery("UPDATE cppmod_collective_group SET CPM_CG_STATUS = 1 WHERE CPM_CG_ID IN ('" . implode("', '", array_map(function($item) { return $item['ID']; }, $setGroupFinal)) . "')");
        }

        if (!empty($setGroupLunas)) {
            $statusSetGroupLunas = $this->dbQuery("UPDATE cppmod_collective_group SET CPM_CG_STATUS = 2 WHERE CPM_CG_ID IN ('" . implode("', '", array_map(function($item) { return $item['ID']; }, $setGroupLunas)) . "')");
        }

        return array(
            'status'        => true,
            'updated'       => count(array_merge($setGroupFinal, $setGroupLunas)),
            'setGroupFinal' => array('status'  => $statusSetGroupFinal,'updated' => count($setGroupFinal),'ids' => implode(', ', array_map(function($item) { return $item['ID']; }, $setGroupFinal)), 'detail' => $setGroupFinal),
            'setGroupLunas' => array('status'  => $statusSetGroupLunas,'updated' => count($setGroupLunas),'ids' => implode(', ', array_map(function($item) { return $item['ID']; }, $setGroupLunas)), 'detail' => $setGroupLunas),
        );
    }
}
