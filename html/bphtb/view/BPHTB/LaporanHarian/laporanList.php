<?php
//ini_set('display_errors', '1');
//error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB'. DIRECTORY_SEPARATOR . 'LaporanHarian', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
$json = new Services_JSON();
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
$tgl = $_GET['hari'];
$qry1= "SELECT count(B.CPM_WP_NAMA) as jumlah
        FROM cppmod_ssb_tranmain A JOIN cppmod_ssb_doc B 
        ON (B.CPM_SSB_ID = A.CPM_TRAN_SSB_ID) 
        WHERE A.CPM_TRAN_STATUS='5' AND A.CPM_TRAN_DATE LIKE '%{$tgl}%'";       
$qry2 = "SELECT B.CPM_WP_NAMA,B.CPM_WP_ALAMAT,B.CPM_OP_LUAS_TANAH,B.CPM_OP_LUAS_BANGUN,
        B.CPM_OP_HARGA,(B.CPM_SSB_AKUMULASI-B.CPM_OP_HARGA) AS BAYAR,CPM_OP_JENIS_HAK
        FROM cppmod_ssb_tranmain A JOIN cppmod_ssb_doc B 
        ON (B.CPM_SSB_ID = A.CPM_TRAN_SSB_ID) 
        WHERE A.CPM_TRAN_STATUS='5' AND A.CPM_TRAN_DATE LIKE '%{$tgl}%'";
 $hasil = mysqli_query($DBLink, $qry1);
 $jumlah = mysqli_fetch_row($hasil);
 $hasil = mysqli_query($DBLink, $qry2);
 $tampil = array();
 $record = array();
 
 
 $no = 1;
 $jTableResult = array();
 $jTableResult['Result'] = 'OK';
 unset($tampil);
 unset($record);
 $hak = array();
 $hak[1]= 'Jual Beli';
 $hak[2]= 'Tukar Menukar';
 $hak[3]= 'Hibah';
 $hak[4]= 'Hibah Wasiat Sedarah Satu Derajat';
 $hak[5]= 'Hibah Wasiat Non Sedarah Satu Derajat';
 $hak[6]= 'Waris';
 $hak[7]= 'Pemasukan dalam perseroan/badan hukum lainnya';
 $hak[8]= 'Pemisahan hak yang mengakibatkan peralihan';
 $hak[9]= 'Penunjukan pembeli dalam lelang';
 $hak[10]= 'Pelaksanaan putusan hakim yang mempunyai kekuatan hukum tetap';
 $hak[12]= 'Penggabungan usaha';
 $hak[13]= 'Peleburan usaha';
 $hak[14]= 'Pemekaran usaha';
 $hak[15]= 'Hadiah';
 $hak[16]= 'Jual beli khusus perolehan hak Rumah Sederhana dan Rumah Susun Sederhana melalui KPR bersubsidi';
 $hak[17]= 'Pemberian hak baru sebagai kelanjutan pelepasan hak';
 $hak[18]= 'Pemberian hak baru diluar pelepasan hak';
 try{
   while($report = mysqli_fetch_array($hasil)){
     $tampil['No'] = $no;
     $tampil['Nama'] = $report['CPM_WP_NAMA'];
     $tampil['Alamat'] = $report['CPM_WP_ALAMAT'];
     $tampil['Luas'] = $report['CPM_OP_LUAS_TANAH'];
     $tampil['Bangunan'] = $report['CPM_OP_LUAS_BANGUN'];
     $tampil['Harga'] = number_format($report['CPM_OP_HARGA'], 2, ",", ".");
     $tampil['Bphtb'] = number_format($report['BAYAR'], 2, ",", ".");
     $tampil['Jenis'] = $hak[$report['CPM_OP_JENIS_HAK']];
     $record[]=  $tampil;
     $no++;
     
   }
  
        $jTableResult['TotalRecordCount'] = $jumlah;
        $jTableResult['Records'] = $record;
        print $json->encode($jTableResult);     
 }catch (Exception $ex) {
        //Return error message
        $jTableResult = array();
        $jTableResult['Result'] = "ERROR";
        $jTableResult['Message'] = $ex->getMessage();
        print $json->encode($jTableResult);
 } 

?>