<?php 
	require_once('queryOpen.php');
	require_once('../../../inc/payment/inc-payment-db-c.php');
	require_once('../../../inc/payment/db-payment.php');
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
	
	
	$data  = $_REQUEST['jsonData'];
	$data  = json_decode($data);
        
        if($data[0]->SPPT_DOC_ID != ''){
            $sql  = "INSERT INTO `cppmod_pbb_sppt_ext` (
                                            `CPM_SPPT_DOC_ID`,
                                            `CPM_SPPT_DOC_VERSION`,
                                            `CPM_OP_NUM`,
                                            `cpm_op_penggunaan`,
                                            `CPM_OP_LUAS_BANGUNAN`,
                                            `CPM_OP_JML_LANTAI`,
                                            `CPM_OP_THN_DIBANGUN`,
                                            `CPM_OP_THN_RENOVASI`,
                                            `CPM_OP_DAYA`,
                                            `CPM_OP_KONDISI`,
                                            `CPM_OP_KONSTRUKSI`,
                                            `CPM_OP_ATAP`,
                                            `CPM_OP_DINDING`,
                                            `CPM_OP_LANTAI`,
                                            `CPM_OP_LANGIT`,
                                            `CPM_FOP_AC_SPLIT`,
                                            `CPM_FOP_AC_WINDOW`,
                                            `CPM_FOP_AC_CENTRAL`,
                                            `CPM_FOP_KOLAM_LUAS`,
                                            `CPM_FOP_KOLAM_LAPISAN`,
                                            `CPM_FOP_PERKERASAN_RINGAN`,
                                            `CPM_FOP_PERKERASAN_SEDANG`,
                                            `CPM_FOP_PERKERASAN_BERAT`,
                                            `CPM_FOP_PERKERASAN_PENUTUP`,
                                            `CPM_FOP_TENIS_LAMPU_BETON`,
                                            `CPM_FOP_TENIS_LAMPU_ASPAL`,
                                            `CPM_FOP_TENIS_LAMPU_TANAH`,
                                            `CPM_FOP_TENIS_TANPA_LAMPU_BETON`,
                                            `CPM_FOP_TENIS_TANPA_LAMPU_ASPAL`,
                                            `CPM_FOP_TENIS_TANPA_LAMPU_TANAH`,
                                            `CPM_FOP_LIFT_PENUMPANG`,
                                            `CPM_FOP_LIFT_KAPSUL`,
                                            `CPM_FOP_LIFT_BARANG`,
                                            `CPM_FOP_ESKALATOR_SEMPIT`,
                                            `CPM_FOP_ESKALATOR_LEBAR`,
                                            `CPM_FOP_SALURAN`,
                                            `CPM_FOP_SUMUR`,
                                            `CPM_PAYMENT_PENILAIAN_BGN`,
                                            `CPM_PAYMENT_SISTEM`,
                                            `CPM_PAYMENT_INDIVIDU`,
                                            `CPM_NJOP_BANGUNAN`,
                                            `CPM_PAGAR_BESI_PANJANG`,
                                            `CPM_PAGAR_BATA_PANJANG`,
                                            `CPM_PEMADAM_HYDRANT`,
                                            `CPM_PEMADAM_SPRINKLER`,
                                            `CPM_PEMADAM_FIRE_ALARM`,
                                            `CPM_JPB2_KELAS_BANGUNAN`,
                                            `CPM_JPB3_TINGGI_KOLOM`,
                                            `CPM_JPB3_DAYA_DUKUNG_LANTAI`,
                                            `CPM_JPB3_LEBAR_BENTANG`,
                                            `CPM_JPB3_KELILING_DINDING`,
                                            `CPM_JPB3_LUAS_MEZZANINE`,
                                            `CPM_JPB4_KELAS_BANGUNAN`,
                                            `CPM_JPB5_KELAS_BANGUNAN`,
                                            `CPM_JPB5_LUAS_KMR_AC_CENTRAL`,
                                            `CPM_JPB5_LUAS_RUANG_AC_CENTRAL`,
                                            `CPM_JPB6_KELAS_BANGUNAN`,
                                            `CPM_JPB7_JENIS_HOTEL`,
                                            `CPM_JPB7_JUMLAH_BINTANG`,
                                            `CPM_JPB7_JUMLAH_KAMAR`,
                                            `CPM_JPB7_LUAS_KMR_AC_CENTRAL`,
                                            `CPM_JPB7_LUAS_RUANG_AC_CENTRAL`,
                                            `CPM_JPB8_TINGGI_KOLOM`,
                                            `CPM_JPB8_DAYA_DUKUNG_LANTAI`,
                                            `CPM_JPB8_LEBAR_BENTANG`,
                                            `CPM_JPB8_KELILING_DINDING`,
                                            `CPM_JPB8_LUAS_MEZZANINE`,
                                            `CPM_JPB9_KELAS_BANGUNAN`,
                                            `CPM_JPB12_TIPE_BANGUNAN`,
                                            `CPM_JPB13_JUMLAH_APARTEMEN`,
                                            `CPM_JPB13_KELAS_BANGUNAN`,
                                            `CPM_JPB13_LUAS_APARTEMEN_AC_CENTRAL`,
                                            `CPM_JPB13_LUAS_RUANG_AC_CENTRAL`,
                                            `CPM_JPB15_TANGKI_MINYAK_KAPASITAS`,
                                            `CPM_JPB15_TANGKI_MINYAK_LETAK`,
                                            `CPM_JPB16_KELAS_BANGUNAN`) 
                                    VALUES (
                                            '".$data[0]->SPPT_DOC_ID."',
                                            '".$data[0]->SPPT_DOC_VERSION."',
                                            '".$data[0]->OP_NUM."',
                                            '".$data[0]->OP_PENGGUNAAN."',
                                            '".$data[0]->OP_LUAS_BANGUNAN."',
                                            '".$data[0]->OP_JML_LANTAI."',
                                            '".$data[0]->OP_THN_DIBANGUN."',
                                            '".$data[0]->OP_THN_RENOVASI."',
                                            '".$data[0]->OP_DAYA."',
                                            '".$data[0]->OP_KONDISI."',
                                            '".$data[0]->OP_KONSTRUKSI."',
                                            '".$data[0]->OP_ATAP."',
                                            '".$data[0]->OP_DINDING."',
                                            '".$data[0]->OP_LANTAI."',
                                            '".$data[0]->OP_LANGIT."',
                                            '".$data[0]->FOP_AC_SPLIT."',
                                            '".$data[0]->FOP_AC_WINDOW."',
                                            '".$data[0]->FOP_AC_CENTRAL."',
                                            '".$data[0]->FOP_KOLAM_LUAS."',
                                            '".$data[0]->FOP_KOLAM_LAPISAN."',
                                            '".$data[0]->FOP_PERKERASAN_RINGAN."',
                                            '".$data[0]->FOP_PERKERASAN_SEDANG."',
                                            '".$data[0]->FOP_PERKERASAN_BERAT."',
                                            '".$data[0]->FOP_PERKERASAN_PENUTUP."',
                                            '".$data[0]->FOP_TENIS_LAMPU_BETON."',
                                            '".$data[0]->FOP_TENIS_LAMPU_ASPAL."',
                                            '".$data[0]->FOP_TENIS_LAMPU_TANAH."',
                                            '".$data[0]->FOP_TENIS_TANPA_LAMPU_BETON."',
                                            '".$data[0]->FOP_TENIS_TANPA_LAMPU_ASPAL."',
                                            '".$data[0]->FOP_TENIS_TANPA_LAMPU_TANAH."',
                                            '".$data[0]->FOP_LIFT_PENUMPANG."',
                                            '".$data[0]->FOP_LIFT_KAPSUL."',
                                            '".$data[0]->FOP_LIFT_BARANG."',
                                            '".$data[0]->FOP_ESKALATOR_SEMPIT."',
                                            '".$data[0]->FOP_ESKALATOR_LEBAR."',
                                            '".$data[0]->FOP_SALURAN."',
                                            '".$data[0]->FOP_SUMUR."',
                                            '".$data[0]->PAYMENT_PENILAIAN_BGN."',
                                            '".$data[0]->PAYMENT_SISTEM."',
                                            '".$data[0]->PAYMENT_INDIVIDU."',
                                            '".$data[0]->NJOP_BANGUNAN."',
                                            '".$data[0]->PAGAR_BESI_PANJANG."',
                                            '".$data[0]->PAGAR_BATA_PANJANG."',
                                            '".$data[0]->PEMADAM_HYDRANT."',
                                            '".$data[0]->PEMADAM_SPRINKLER."',
                                            '".$data[0]->PEMADAM_FIRE_ALARM."',
                                            '".$data[0]->JPB2_KELAS_BANGUNAN."',
                                            '".$data[0]->JPB3_TINGGI_KOLOM."',
                                            '".$data[0]->JPB3_DAYA_DUKUNG_LANTAI."',
                                            '".$data[0]->JPB3_LEBAR_BENTANG."',
                                            '".$data[0]->JPB3_KELILING_DINDING."',
                                            '".$data[0]->JPB3_LUAS_MEZZANINE."',
                                            '".$data[0]->JPB4_KELAS_BANGUNAN."',
                                            '".$data[0]->JPB5_KELAS_BANGUNAN."',
                                            '".$data[0]->JPB5_LUAS_KMR_AC_CENTRAL."',
                                            '".$data[0]->JPB5_LUAS_RUANG_AC_CENTRAL."',
                                            '".$data[0]->JPB6_KELAS_BANGUNAN."',
                                            '".$data[0]->JPB7_JENIS_HOTEL."',
                                            '".$data[0]->JPB7_JUMLAH_BINTANG."',
                                            '".$data[0]->JPB7_JUMLAH_KAMAR."',
                                            '".$data[0]->JPB7_LUAS_KMR_AC_CENTRAL."',
                                            '".$data[0]->JPB7_LUAS_RUANG_AC_CENTRAL."',
                                            '".$data[0]->JPB8_TINGGI_KOLOM."',
                                            '".$data[0]->JPB8_DAYA_DUKUNG_LANTAI."',
                                            '".$data[0]->JPB8_LEBAR_BENTANG."',
                                            '".$data[0]->JPB8_KELILING_DINDING."',
                                            '".$data[0]->JPB8_LUAS_MEZZANINE."',
                                            '".$data[0]->JPB9_KELAS_BANGUNAN."',
                                            '".$data[0]->JPB12_TIPE_BANGUNAN."',
                                            '".$data[0]->JPB13_JUMLAH_APARTEMEN."',
                                            '".$data[0]->JPB13_KELAS_BANGUNAN."',
                                            '".$data[0]->JPB13_LUAS_APARTEMEN_AC_CENTRAL."',
                                            '".$data[0]->JPB13_LUAS_RUANG_AC_CENTRAL."',
                                            '".$data[0]->JPB15_TANGKI_MINYAK_KAPASITAS."',
                                            '".$data[0]->JPB15_TANGKI_MINYAK_LETAK."',
                                            '".$data[0]->JPB16_KELAS_BANGUNAN."') ";

            echo mysqli_query($DBLink, $sql);
        }
?>
