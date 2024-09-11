<?php

// exit;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(9000);
ini_set('memory_limit', '-1');

echo "
<style>
    * {
        box-sizing: border-box;
    }
    
    .column {
        float: left;
        width: 50%;
        padding: 10px;
        height: 300px;
		text-align: left;
    }
    
    .row:after {
        content: '';
        display: table;
        clear: both;
    }
</style>
";
echo '<body style="background:#3c3c3c;color:#3dfd64;text-align:center">';

$host2 = 'localhost';
$user2 = 'root';
$pass2 = 'pesawaran2@24';
$db2   = 'gw_pbb';

$tahun = isset($_REQUEST['tahun']) ? $_REQUEST['tahun'] : false;

if(!$tahun) exit('tahun berapa ?');
$conn = mysqli_connect($host2, $user2, $pass2, $db2);


    $tablecetak = ($tahun==date('Y')) ? 'cppmod_pbb_sppt_current' : 'cppmod_pbb_sppt_cetak_'.$tahun;
    $query="SELECT
				IFNULL(f.CPM_WP_NO_KTP,NULL) AS NO_KTP,
				IFNULL(f.CPM_WP_PEKERJAAN,NULL) AS WP_PEKERJAAN,
                c.*
            FROM sw_pbb.{$tablecetak} c
			LEFT JOIN sw_pbb.cppmod_pbb_sppt_final f ON f.CPM_NOP=c.NOP 
            LEFT JOIN gw_pbb.pbb_sppt g ON g.NOP=c.NOP AND g.SPPT_TAHUN_PAJAK='$tahun'
            WHERE g.NOP IS NULL";

    $res = mysqli_query($conn, $query);

    $arr = [];
    $arrInsert = '';
    while($row = mysqli_fetch_assoc($res)) {
		$arr[] = "'".$row['NOP']."'";
		$colomnX = array(
						'NOP'						=> $row['NOP'], 
						'SPPT_TAHUN_PAJAK'			=> $tahun, 
						'SPPT_TANGGAL_JATUH_TEMPO'	=> $row['SPPT_TANGGAL_JATUH_TEMPO'], 
						'SPPT_PBB_HARUS_DIBAYAR'	=> $row['SPPT_PBB_HARUS_DIBAYAR'], 
						
						'WP_NAMA'		=> $row['WP_NAMA'], 
						'WP_TELEPON'	=> $row['WP_TELEPON'], 
						'WP_NO_HP'		=> $row['WP_NO_HP'], 
						'WP_ALAMAT'		=> $row['WP_ALAMAT'], 
						'WP_RT'			=> sprintf("%03d", (int)$row['WP_RT']), 
						'WP_RW'			=> sprintf("%02d", (int)$row['WP_RW']), 
						'WP_KELURAHAN'	=> $row['WP_KELURAHAN'], 
						'WP_KECAMATAN'	=> $row['WP_KECAMATAN'], 
						'WP_KOTAKAB'	=> $row['WP_KOTAKAB'], 
						'WP_KODEPOS'	=> $row['WP_KODEPOS'], 

						'SPPT_TANGGAL_TERBIT'	=> $row['SPPT_TANGGAL_TERBIT'], 
						'SPPT_TANGGAL_CETAK'	=> $row['SPPT_TANGGAL_CETAK'], 

						'OP_LUAS_BUMI'		=> $row['OP_LUAS_BUMI'], 
						'OP_LUAS_BANGUNAN'	=> $row['OP_LUAS_BANGUNAN'], 
						'OP_KELAS_BUMI'		=> $row['OP_KELAS_BUMI'], 
						'OP_KELAS_BANGUNAN'	=> $row['OP_KELAS_BANGUNAN'], 
						'OP_NJOP_BUMI'		=> $row['OP_NJOP_BUMI'], 
						'OP_NJOP_BANGUNAN'	=> $row['OP_NJOP_BANGUNAN'], 
						'OP_NJOP'			=> $row['OP_NJOP'], 
						'OP_NJOPTKP'		=> $row['OP_NJOPTKP'], 
						'OP_NJKP'			=> $row['OP_NJKP'], 

						'PAYMENT_FLAG'				=> '0', 
						'PAYMENT_PAID'				=> NULL, 
						'PAYMENT_REF_NUMBER'		=> NULL, 
						'PAYMENT_BANK_CODE'			=> NULL, 
						'PAYMENT_SW_REFNUM'			=> NULL, 
						'PAYMENT_GW_REFNUM'			=> NULL, 
						'PAYMENT_SW_ID'				=> NULL, 
						'PAYMENT_MERCHANT_CODE'		=> NULL, 
						'PAYMENT_SETTLEMENT_DATE'	=> NULL, 

						'PBB_COLLECTIBLE'	=> NULL, 
						'PBB_DENDA'			=> NULL, 
						'PBB_ADMIN_GW'		=> NULL, 
						'PBB_MISC_FEE'		=> NULL, 
						'PBB_TOTAL_BAYAR'	=> NULL, 
						
						'OP_ALAMAT'			=> $row['OP_ALAMAT'], 
						'OP_RT'				=> sprintf("%03d", (int)$row['OP_RT']), 
						'OP_RW'				=> sprintf("%02d", (int)$row['OP_RW']), 
						'OP_KELURAHAN'		=> $row['OP_KELURAHAN'], 
						'OP_KECAMATAN'		=> $row['OP_KECAMATAN'], 
						'OP_KOTAKAB'		=> $row['OP_KOTAKAB'], 
						'OP_KELURAHAN_KODE'	=> $row['OP_KELURAHAN_KODE'], 
						'OP_KECAMATAN_KODE'	=> $row['OP_KECAMATAN_KODE'], 
						'OP_KOTAKAB_KODE'	=> $row['OP_KOTAKAB_KODE'], 
						'OP_PROVINSI_KODE'	=> $row['OP_PROVINSI_KODE'], 

						'TGL_STPD'		=> NULL, 
						'TGL_SP1'		=> NULL, 
						'TGL_SP2'		=> NULL, 
						'TGL_SP3'		=> NULL, 
						'STATUS_SP'		=> NULL, 
						'STATUS_CETAK'	=> NULL, 

						'WP_PEKERJAAN'	=> $row['WP_PEKERJAAN'], 
						
						'PAYMENT_OFFLINE_USER_ID'	=> NULL, 
						'PAYMENT_OFFLINE_FLAG'		=> NULL, 
						'PAYMENT_OFFLINE_PAID'		=> NULL, 

						'ID_WP'=> $row['NO_KTP'], 

						'PAYMENT_CODE'		=> NULL, 
						'BOOKING_EXPIRED'	=> NULL, 
						'COLL_PAYMENT_CODE'	=> NULL
		);
		$colomn = $values = [];
		foreach ($colomnX as $k => $v) {
			$colomn[] = $k;
			$values[] = ($v==NULL) ? 'NULL' : "'".$v."'";
		}
		$colomn = implode(', ',$colomn);
		$values = implode(', ',$values);

		$arrInsert .= "INSERT INTO pbb_sppt <br>($colomn) <br>VALUES <br>($values);". '<br><br>';
	}
    $n1 = count($arr);
    $arr = implode(', ',$arr);


    $query="SELECT
                c.*
            FROM sw_pbb.{$tablecetak} c
            LEFT JOIN sw_pbb.cppmod_pbb_sppt_final f ON f.CPM_NOP=c.NOP 
            WHERE f.CPM_NOP IS NULL";

    $res = mysqli_query($conn, $query);

    $arr2 = [];
    while($row = mysqli_fetch_assoc($res)) $arr2[] = "'".$row['NOP']."'";
    $n2 = count($arr2);
    $arr2 = implode(', ',$arr2);


    // echo '<pre>';
    // print_r($arr);
    // exit;

    $html = '';
    for ($i=1992; $i <=date('Y') ; $i++) $html .= '<a href="http://36.92.151.83:2010/devtool/cari_sppt_hilang.php?tahun='.$i.'" style="color:#3dfd64">'.$i.'</a> ';
    $html .= '<br><br>';
    $html .= '<h3>TAHUN CETAK : '.$tahun.'</h3>';
    $html .= '<div class="row">';
    $html .= '<div class="column">';
    $html .= '<h4>SPPT ada, GW Tagihan Tidak ada = '.$n1.'</h4>';
    $html .= ($arr=='') ? '':"SELECT * FROM sw_pbb.pbb_sppt WHERE NOP IN ($arr)";
	$html .= '<br><br>';
    $html .= ($arr=='') ? '':$arrInsert;
    $html .= '</div>';
    $html .= '<div class="column">';
    $html .= '<h4>SPPT ada, di Final Tidak ada = '.$n2.'</h4>';
    $html .= ($arr2=='') ? '':"SELECT * FROM sw_pbb.cppmod_pbb_sppt_final WHERE CPM_NOP IN ($arr2)";
    $html .= '</div>';
    $html .= '</div>';
    print_r($html);