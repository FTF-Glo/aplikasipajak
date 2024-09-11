<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// echo "<pre>";	
// 	print_r($_REQUEST);
// echo "</pre>";
// exit;
if (!isset($data)) {
    die("Forbidden direct access");
}

if (!$User) {
    die("Access not permitted");
}

$bOK = $User->IsFunctionGranted($uid, $area, $module, $function);

if (!$bOK) {
    die("Function access not permitted");
}



// ini_set('display_errors', 1);

// ini_set('display_startup_errors', 1);

// error_reporting(E_ALL);

#print_r($_REQUEST);exit;
require_once("inc/payment/uuid.php");
require_once("inc/PBB/dbSppt.php");
require_once("inc/PBB/dbSpptPerubahan.php");
require_once("inc/PBB/dbSpptTran.php");
require_once("inc/PBB/dbSpptExt.php");
require_once("inc/PBB/dbUtils.php");
require_once("inc/PBB/dbServices.php");
require_once("inc/PBB/dbFinalSppt.php");
require_once("inc/PBB/dbOPAccount.php");
require_once("inc/PBB/dbGwCurrent.php");
require_once("inc/PBB/dbWajibPajak.php");
require_once("inc/payment/comm-central.php");
require_once("inc/PBB/dbSpptHistory.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
$arConfig = $User->GetModuleConfig($module);
$appConfig = $User->GetAppConfig($application);
$dbSppt = new DbSppt($dbSpec);
$dbSpptPerubahan = new DbSpptPerubahan($dbSpec);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbSpptExt = new DbSpptExt($dbSpec);
$dbUtils = new DbUtils($dbSpec);
$dbOPAccount = new dbOPAccount($dbSpec);
$dbServices = new DbServices($dbSpec);
$dbFinalSppt = new DbFinalSppt($dbSpec);
$dbGwCurrent = new DbGwCurrent($dbSpec);
$dbWajibPajak = new DbWajibPajak($dbSpec);
$userDetail = $dbUtils->getUserDetailPbb($uid);
$dbSpptHistory = new DbSpptHistory($dbSpec);

$NBParam_before = '{"ServerAddress":"' . $appConfig['TPB_ADDRESS'] . '","ServerPort":"' . $appConfig['TPB_PORT'] . '","ServerTimeOut":"' . $appConfig['TPB_TIMEOUT'] . '"}';
$NBParam = base64_encode($NBParam_before);

//User Login
$nm_lengkap = $userDetail[0]['nm_lengkap'];
$nip = $userDetail[0]['nip'];

//Preparing Parameters
/* Jika ada parameter idt maka proses yg dilakukan adalah proses edit SPOP */
if (isset($idt)) {

    $tran = $dbSpptTran->gets($idt);
    $idd = $tran[0]['CPM_TRAN_SPPT_DOC_ID'];
    $v = $tran[0]['CPM_SPPT_DOC_VERSION'];
    $mode = 'edit';
    $uname = $userDetail[0]['userId'];
    $bSlash = "\'";
    $ktip    = "'";

    // echo "<pre>";
    // print_r($idt);
    // die();
}

$aKabKota = $dbUtils->getKabKota($appConfig['KODE_KOTA']);
$bKecamatanOP = $dbUtils->getKecamatan(null, array("CPC_TKC_KKID" => $appConfig['KODE_KOTA']));
$bKelurahanOP = $dbUtils->getKelOnKota($appConfig['KODE_KOTA']);

$bZNT = array();

function clearString($str){
    $str = str_replace('   ', ' ', $str);
    $str = str_replace('  ', ' ', $str);
    $str = str_replace("'", ' ', $str);
    $str = str_replace('"', ' ', $str);
    $str = str_replace("\\", '', $str);
    $str = str_replace(" & ", ' dan ', $str);
    $str = str_replace("&", 'dan', $str);
    $str = str_replace("%", 'persen', $str);
    return trim($str);
}

//////////////////////////////
// Process Saving Form 1, create new SPOP
/////////////////////////////
// die(var_dump($newForm1));
if (isset($newForm1)) {


    $content = array();
    $idDoc = (isset($idd) ? $idd : c_uuid());
    $vers = (isset($v) ? $v : "1");

    //get sppt data even if exist or not
    $sppt = $dbSppt->gets($idDoc, $vers);
    $content = null;

    if (isset($sppt[0]) && $sppt[0] != null) {
        foreach ($sppt[0] as $key => $value) {
            if (is_numeric($key)) {
                unset($sppt[0][$key]);
            }
        }

        $content = $sppt[0];
    }


    unset($content['CPM_SPPT_DOC_ID']);
    unset($content['CPM_SPPT_DOC_VERSION']);
    $content['CPM_NOP'] = implode($NOP);
    $nopAwal = $content['CPM_NOP'];
    if (isset($is_new_nop)) {
        if ($is_new_nop == 'generate') {
            $content['CPM_NOP'] = $dbUtils->getNoUrut($content['CPM_NOP'], $uname);
        } else {
            $dbUtils->insertNOP($content['CPM_NOP'], $uname);
        }
    }

    // BY 35UTECH 07 MARET 2018
    $saveOPAccount = false;
    $CPM_OP_ACCOUNT = $dbOPAccount->getMaxOPAccount();
    $CPM_OP_ACCOUNT = $CPM_OP_ACCOUNT[0]['MAX'];

    $param = array();
    $param['CPM_NOP'] = $content['CPM_NOP'];
    $param['CPM_OP_ACCOUNT'] = $CPM_OP_ACCOUNT;
    $param['CPM_LAST_INSERT'] = date("Y-m-d h:i:s");
    $param['CPM_USER'] =  $uname;
    $bOK = $dbOPAccount->save($param);
    if ($bOK) {
        $saveOPAccount = true;
    } else {
        $saveOPAccount = false;
    }
    $content['CPM_OP_ACCOUNT'] = $CPM_OP_ACCOUNT; // TAMBAHKAn UNTUK ADD OP ACCOUNT    

    // END 35UTECH

    $content['CPM_SPPT_DOC_AUTHOR'] = $uname;
    $content['CPM_SPPT_DOC_CREATED']= date("Y-m-d H:i:s");
    $content['CPM_NOP_BERSAMA']     = implode($NOP_BERSAMA);
    $content['CPM_OP_ALAMAT']       = strtoupper(clearString($OP_ALAMAT));
    $content['CPM_OP_NOMOR']        = strtoupper(clearString($OP_NOMOR));
    $content['CPM_OP_KELURAHAN']    = (int)$OP_KELURAHAN;
    $content['CPM_OP_KECAMATAN']    = (int)$OP_KECAMATAN;
    $content['CPM_OP_KOTAKAB']      = (int)$OP_KOTAKAB;
    $content['CPM_OP_RT']           = sprintf("%03d", (int)$OP_RT);
    $content['CPM_OP_RW']           = sprintf("%02d", (int)$OP_RW);
    $content['CPM_WP_STATUS']       = clearString($WP_STATUS);
    $content['CPM_WP_PEKERJAAN']    = isset($WP_PEKERJAAN) ? clearString($WP_PEKERJAAN) : '';
    $content['CPM_WP_NAMA']         = strtoupper(clearString($WP_NAMA));
    $content['CPM_WP_ALAMAT']       = strtoupper(clearString($WP_ALAMAT));
    $content['CPM_WP_KELURAHAN']    = strtoupper(clearString($WP_KELURAHAN));
    $content['CPM_WP_RT']           = sprintf("%03d", (int)$WP_RT);
    $content['CPM_WP_RW']           = sprintf("%02d", (int)$WP_RW);
    $content['CPM_WP_PROPINSI']     = strtoupper(clearString($WP_PROPINSI));
    $content['CPM_WP_KOTAKAB']      = strtoupper(clearString($WP_KOTAKAB));
    $content['CPM_WP_KECAMATAN']    = strtoupper(clearString($WP_KECAMATAN));
    $content['CPM_WP_KODEPOS']      = clearString($WP_KODEPOS);
    $content['CPM_WP_ID']           = ($WP_NO_KTP!='') ? clearString($WP_NO_KTP) : $content['CPM_NOP'];
    $content['CPM_WP_NO_KTP']       = ($WP_NO_KTP!='') ? clearString($WP_NO_KTP) : $content['CPM_NOP'];
    $content['CPM_WP_NO_HP']        = addslashes($WP_NO_HP);
    $content['CPM_OP_LUAS_TANAH']   = (float)$OP_LUAS_TANAH;
    $content['CPM_OT_LATITUDE']     = clearString($OT_LATITUDE);
    $content['CPM_OT_LONGITUDE']    = clearString($OT_LONGITUDE);
    if (isset($OT_ZONA_NILAI_INDUK)) {
        $otzon = explode(" - ", $OT_ZONA_NILAI_INDUK);
        $content['CPM_OT_ZONA_NILAI'] = clearString($OT_ZONA_NILAI_INDUK);
    } else {
        $content['CPM_OT_ZONA_NILAI'] = clearString($OT_ZONA_NILAI);
    }

    $content['CPM_OT_JENIS']           = (int)$OT_JENIS;
    $content['CPM_OT_PENILAIAN_TANAH'] = (float)$OT_PENILAIAN_TANAH;

    $content['CPM_OT_PAYMENT_INDIVIDU'] = isset($OT_PAYMENT_INDIVIDU) && is_numeric($OT_PAYMENT_INDIVIDU) ? (float)$OT_PAYMENT_INDIVIDU : '0';
    $content['CPM_OP_JML_BANGUNAN'] = isset($OP_JML_BANGUNAN) ? (int)$OP_JML_BANGUNAN : 0;
    $content['CPM_PP_TIPE']         = clearString($PP_TIPE);
    $content['CPM_PP_NAMA']         = strtoupper(clearString($PP_NAMA));
    $content['CPM_PP_DATE']         = clearString($PP_DATE);
    $content['CPM_OPR_TGL_PENDATAAN'] = clearString($OPR_TGL_PENDATAAN);
    $content['CPM_OPR_NAMA']        = strtoupper(clearString($OPR_NAMA));
    $content['CPM_OPR_NIP']         = clearString($OPR_NIP);

    // Nilai saat simpan
    $directTo   = BASE_URL."inc/PBB/svc-tanah.php";
    $param      = '{NOP:"' .$content['CPM_NOP']. '", ZNT:"' .$content['CPM_OT_ZONA_NILAI']. '", LUAS:"' .$content['CPM_OP_LUAS_TANAH']. '", TAHUN:"' .$appConfig['tahun_tagihan']. '", TABEL:"cppmod_pbb_sppt"}';
    $response   = phpPenilaian($param, $directTo, $isReturn=true);
    $response   = json_decode($response);

    $OT_ZONA_NILAI = isset($response->znt) ? $response->znt : $OT_ZONA_NILAI;
    $NJOPM2 = isset($response->njopm2) ? $response->njopm2 : 0;
    $NJOP_TANAH = isset($response->njop) ? $response->njop : $NJOP_TANAH;
    $OP_KELAS_TANAH = isset($response->kelas) ? $response->kelas : $OP_KELAS_TANAH;
    

    $content['CPM_OT_ZONA_NILAI']       = $OT_ZONA_NILAI;
    $content['CPM_OT_PAYMENT_SISTEM']   = (float)$NJOPM2;
    $content['CPM_NJOP_TANAH']          = (float)$NJOP_TANAH;
    $content['CPM_OP_KELAS_TANAH']      = clearString($OP_KELAS_TANAH);

    $contentWP['CPM_WP_STATUS']         = $WP_STATUS;
    $contentWP['CPM_WP_PEKERJAAN']      = isset($WP_PEKERJAAN) ? $WP_PEKERJAAN : '';
    $contentWP['CPM_WP_NAMA']           = strtoupper($WP_NAMA);
    $contentWP['CPM_WP_ALAMAT']         = strtoupper($WP_ALAMAT);
    $contentWP['CPM_WP_KELURAHAN']      = strtoupper($WP_KELURAHAN);
    $contentWP['CPM_WP_RT']             = strtoupper($WP_RT);
    $contentWP['CPM_WP_RW']             = strtoupper($WP_RW);
    $contentWP['CPM_WP_PROPINSI']       = strtoupper($WP_PROPINSI);
    $contentWP['CPM_WP_KOTAKAB']        = strtoupper($WP_KOTAKAB);
    $contentWP['CPM_WP_KECAMATAN']      = strtoupper($WP_KECAMATAN);
    $contentWP['CPM_WP_KODEPOS']        = strtoupper($WP_KODEPOS);
    $contentWP['CPM_WP_NO_HP']          = strtoupper($WP_NO_HP);

    if (isset($tran[0]['CPM_TRAN_STATUS']) && ($tran[0]['CPM_TRAN_STATUS'] == 6 || $tran[0]['CPM_TRAN_STATUS'] == 7 || $tran[0]['CPM_TRAN_STATUS'] == 8)) {
        $oldver = $vers;
        $vers = $vers + 1;
        $aVal['CPM_TRAN_FLAG'] = 1;
        $dbSpptTran->edit($idt, $aVal);
    }

    // echo "masuk";
    //var_dump($dbSppt->isExist($idDoc, $vers));exit();
    //exit;
    //check availability

    // SERTIFIKAT
    $serti['CPM_NOMOR_SERTIFIKAT'] = addslashes($NOMOR_SERTIFIKAT);
    $serti['CPM_TANGGAL'] = addslashes($TANGGAL_SERTIFIKAT);
    $serti['CPM_NAMA_SERTIFIKAT'] = addslashes($NAMA_SERTIFIKAT);
    $serti['CPM_JENIS_HAK'] = addslashes($JENIS_HAK);
    $serti['CPM_NAMA_PEMEGANG'] = addslashes($NAMA_SERTIFIKAT);

    if ($dbSppt->isExist($idDoc, $vers)) {
        $bOK = $dbSppt->edit($idDoc, $vers, $content);

        $dbSppt->update_sertifikat($np, $serti);
        //$bOK = $dbWajibPajak->save($content['CPM_WP_NO_KTP'],$contentWP);
        if ($newForm1 == 'Simpan dan Finalkan') { #simpan dan finalkan
            // var_dump($newForm1);
            $lastID = c_uuid();
            $vals = $dbSpptTran->gets($idt);
            $dbSpptTran->edit($idt, array('CPM_TRAN_FLAG' => '1'));
            if ($appConfig['jumlah_verifikasi'] == 0) {
                $vals[0]['CPM_TRAN_STATUS'] = 4;
            } else {
                $vals[0]['CPM_TRAN_STATUS'] = 1;
            }

            $idd         = $vals[0]['CPM_TRAN_SPPT_DOC_ID'];
            $v             = $vals[0]['CPM_SPPT_DOC_VERSION'];
            $docSPPT     = $dbSppt->gets($idd, $v);
            unset($vals[0]['CPM_TRAN_ID']);
            unset($vals[0]['CPM_TRAN_DATE']);

            foreach ($vals[0] as $key => $value) {
                if (is_numeric($key)) {
                    unset($vals[0][$key]);
                }
            }

            $addsppt = $dbSpptTran->add($lastID, $vals[0]);

            if ($appConfig['jumlah_verifikasi'] == 0) {

                $contentWP = array();
                $contentWP['CPM_WP_STATUS']     = $docSPPT[0]['CPM_WP_STATUS'];
                $contentWP['CPM_WP_PEKERJAAN']  = $docSPPT[0]['CPM_WP_PEKERJAAN'];
                $contentWP['CPM_WP_NAMA']       = strtoupper($docSPPT[0]['CPM_WP_NAMA']);
                $contentWP['CPM_WP_ALAMAT']     = strtoupper($docSPPT[0]['CPM_WP_ALAMAT']);
                $contentWP['CPM_WP_KELURAHAN']  = strtoupper($docSPPT[0]['CPM_WP_KELURAHAN']);
                $contentWP['CPM_WP_RT']         = sprintf("%03d", (int)$docSPPT[0]['CPM_WP_RT']);
                $contentWP['CPM_WP_RW']         = sprintf("%02d", (int)$docSPPT[0]['CPM_WP_RW']);
                $contentWP['CPM_WP_PROPINSI']   = strtoupper($docSPPT[0]['CPM_WP_PROPINSI']);
                $contentWP['CPM_WP_KOTAKAB']    = strtoupper($docSPPT[0]['CPM_WP_KOTAKAB']);
                $contentWP['CPM_WP_KECAMATAN']  = strtoupper($docSPPT[0]['CPM_WP_KECAMATAN']);
                $contentWP['CPM_WP_KODEPOS']    = (int)$docSPPT[0]['CPM_WP_KODEPOS'];
                $contentWP['CPM_WP_NO_HP']      = addslashes($docSPPT[0]['CPM_WP_NO_HP']);

                $bOK = $dbWajibPajak->save($docSPPT[0]['CPM_WP_NO_KTP'], $contentWP);

                if($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end']) {
                    $bOK = $dbSpptHistory->goSusulan($lastID);
                }else{
                    $bOK = $dbSpptHistory->goFinal($lastID);
                }
            }
        }

    } else {
		
        // if not exist on cppmod_pbb_sppt
        //$bOK = $dbWajibPajak->save($content['CPM_WP_NO_KTP'],$contentWP);
        $dbSppt->update_sertifikat($np, $serti);
        $bOK = $dbSppt->add($idDoc, $vers, $content);
        if ($bOK) {
			
            if ($tran[0]['CPM_TRAN_STATUS'] == 6 || $tran[0]['CPM_TRAN_STATUS'] == 7 || $tran[0]['CPM_TRAN_STATUS'] == 8) {
                //doc version sudah di increment, sekarang mengincrement ext version
                $dbSpptExt->incVers($idDoc, $vers, $oldver);
            }

            $idt = c_uuid();
            if ($tran[0]['CPM_TRAN_STATUS'] == 6 || $tran[0]['CPM_TRAN_STATUS'] == 7 || $tran[0]['CPM_TRAN_STATUS'] == 8) {
                $tranValue['CPM_TRAN_REFNUM'] = $tran[0]['CPM_TRAN_REFNUM'];
                $bOK = $dbSppt->del($idDoc, $tran[0]['CPM_SPPT_DOC_VERSION']);
                $bOK = $dbSpptExt->del($idDoc, $tran[0]['CPM_SPPT_DOC_VERSION']);
            } else {
                $tranValue['CPM_TRAN_REFNUM'] = c_uuid();
            }

            if ($newForm1 == 'Simpan dan Finalkan') { #simpan dan finalkan
                $tranValue['CPM_TRAN_FLAG'] = "1";
            }

            $tranValue['CPM_TRAN_STATUS'] = "0";
            $tranValue['CPM_TRAN_SPPT_DOC_ID'] = $idDoc;
            $tranValue['CPM_SPPT_DOC_VERSION'] = $vers;
            $tranValue['CPM_TRAN_DATE'] = date("Y-m-d H:i:s");
            $tranValue['CPM_TRAN_OPR_KONSOL'] = $uname;
            $bOK = $dbSpptTran->add($idt, $tranValue);

            if ($newForm1 == 'Simpan dan Finalkan') { #simpan dan finalkan
                $idt = c_uuid();
                unset($tranValue['CPM_TRAN_FLAG']);
                $tranValue['CPM_TRAN_STATUS'] = "1";
                $bOK = $dbSpptTran->add($idt, $tranValue);
            }

            if (!$bOK) {
                //failed add transaction. Maybe ID transaction already exist. Second try
                $idt = c_uuid();
                $bOK = $dbSpptTran->add($idt, $tranValue);
                if (!$bOK) {
                    //something failed. Delete Sppt document
                    $dbSppt->del($idDoc);
                    $errorMsg = "<div class='error'>Data SPOP gagal dimasukkan. Mohon ulangi.</div>";
                } else {
                    if (isset($idServices)) {
                        $dbServices->insertTransactionFromPendataan(c_uuid(), $idServices, $content['CPM_NOP'], $uname);
                    }

                    $isSpttFinal = false;
                    $isSpttSusulan = false;
                    $isSpttFinal = $dbFinalSppt->isNopExist($NOP_INDUK);
                    if (!$isSpttFinal)
                        $isSpttSusulan = $dbFinalSppt->isNopExistInSusulan($NOP_INDUK);

                    if (isset($NOP_INDUK) && ($isSpttFinal || $isSpttSusulan)) {

                        // jika melakukan pemecahan && ada pada final dan susulan
                        // echo "masuk";

                        $datNOPInduk = array();
                        if ($isSpttFinal)
                            $datNOPInduk = $dbFinalSppt->get_where(array("CPM_NOP" => $NOP_INDUK));
                        // mengambil data FINAL
                        else
                            $datNOPInduk = $dbFinalSppt->get_susulan(array("CPM_NOP" => $NOP_INDUK));
                        // mengambil data susulan

                        $OP_INDUK_LUAS = $datNOPInduk[0]['CPM_OP_LUAS_TANAH'];
                        $iddoc = $datNOPInduk[0]['CPM_SPPT_DOC_ID'];
                        $vers = $datNOPInduk[0]['CPM_SPPT_DOC_VERSION'];

                        //Jika luas bumi NOP induk habis dipecah, maka NOP induk dihapus
                        if (($OP_INDUK_LUAS - $content['CPM_OP_LUAS_TANAH']) > 0) { // jika sisa luas induk d atas 0 

							

                            $dtEditSPPTFinal = array('CPM_OP_LUAS_TANAH' => $OP_INDUK_LUAS - $content['CPM_OP_LUAS_TANAH']);
                            if ($isSpttFinal)
                                $bOK = $dbFinalSppt->edit($iddoc, $vers, $dtEditSPPTFinal);
                            else
                                $bOK = $dbFinalSppt->editSusulan($iddoc, $vers, $dtEditSPPTFinal);



                            // 18 APRIL 2018 BY 35U TECH START
                            $GWDBLink = mysqli_connect($appConfig['GW_DBHOST'], $appConfig['GW_DBUSER'], $appConfig['GW_DBPWD'], $appConfig['GW_DBNAME'], $appConfig['GW_DBPORT']);
                            if (!$GWDBLink) {
                                $gw = false;
                                echo mysqli_error($GWDBLink);
                                die();
                            }
                            //mysql_select_db($appConfig['GW_DBNAME'], $GWDBLink) ;
                            $sppt  = $dbGwCurrent->getDataTagihanSPPT($NOP_INDUK, $appConfig['tahun_tagihan'], $GWDBLink);
                            $curVal = array();
                            $curVal['OP_LUAS_BUMI'] = $OP_INDUK_LUAS - $content['CPM_OP_LUAS_TANAH'];
                            if ($sppt['PAYMENT_FLAG'] != 1  && $_REQUEST['OP_PENETAPAN_INDUK'] == "1") { // JIKA BELUM BAYAR MAKA UPDATE SPPT DAN CURRENT
                                $updatePBBInduk = $dbGwCurrent->updateTagihanSPPT($NOP_INDUK, $appConfig['tahun_tagihan'], $curVal, $GWDBLink);
                                if ($updatePBBInduk) {
                                    $updateCurrentInduk = $dbServices->editSpptCurrent($NOP_INDUK, $curVal);
                                }
                            }
                            mysqli_select_db($GWDBLink, $appConfig['ADMIN_SW_DBNAME']);
                            $initData = $dbSpptPerubahan->getInitData($idServices);
                            $NOP = $initData['CPM_NOP'];



                            // 18 APRIL 2018 BY 35U TECH END

                            // 08 MEI 2018 BY 35U TECH START
                            // if ($_REQUEST['OP_PENETAPAN_INDUK']=="1"){ // jika NOP Induk di tetapkan tahun config skrg maka

                            // }
                            // 08 MEI 2018 BY 35U TECH END
                            //ARD- menonaktifkan update ke current
                            //$dbGwCurrent->edit($NOP_INDUK, array('OP_LUAS_BUMI' => $OP_INDUK_LUAS - $content['CPM_OP_LUAS_TANAH']),$appConfig);

                        } else { // jika sisa luas induk tidak d atas 0


                            //hapus dari sppt final
                            if ($isSpttFinal) {
                                $bOK = $dbFinalSppt->moveFinalToHistory($iddoc, $vers);
                                $bOK = $dbFinalSppt->doPurge($iddoc, $vers);
                            } else {
                                $bOK = $dbFinalSppt->moveSusulanToHistory($iddoc, $vers);
                                $bOK = $dbFinalSppt->doPurgeSusulan($iddoc, $vers);
                            }
                            $dbGwCurrent->del($NOP_INDUK);
                        }
                    }
                    /* Jika NOP memiliki NOP Bersama, maka insert ke tabel cppmod_pbb_sppt_anggota */
                    if (trim($content['CPM_NOP_BERSAMA']) != '') {
                        $bOK = $dbSppt->addAnggota($content['CPM_NOP_BERSAMA'], $content['CPM_NOP']);
                    }
                }
            } else {
                // berhasil input table train main

                if (isset($idServices)) {
                    // NEW BY 35UTECH 9 MEI 2018 START
                    if ($_REQUEST['OP_PENETAPAN_INDUK'] == "0" || $_REQUEST['OP_PENETAPAN_INDUK'] == "") {
                        $dbServices->insertTransactionFromPendataan(c_uuid(), $idServices, $content['CPM_NOP'], $uname);
                    } else if ($_REQUEST['OP_PENETAPAN_INDUK'] == "1") {
                        $dbServices->insertTransactionSplit(c_uuid(), $idServices, $content['CPM_NOP'], $uname, $_REQUEST['OP_PENETAPAN_INDUK']);
                    }
                    // NEW BY 35UTECH 9 MEI 2018 END
                }



                $isSpttFinal = false;
                $isSpttSusulan = false;
                $isSpttFinal = $dbFinalSppt->isNopExist($NOP_INDUK);
                if (!$isSpttFinal)
                    $isSpttSusulan = $dbFinalSppt->isNopExistInSusulan($NOP_INDUK);

                // var_dump($NOP_INDUK);
                // exit;
                if (isset($NOP_INDUK) && ($isSpttFinal || $isSpttSusulan)) {
                    $datNOPInduk = array();
                    if ($isSpttFinal)
                        $datNOPInduk = $dbFinalSppt->get_where(array("CPM_NOP" => $NOP_INDUK));
                    else
                        $datNOPInduk = $dbFinalSppt->get_susulan(array("CPM_NOP" => $NOP_INDUK));
                    $OP_INDUK_LUAS = $datNOPInduk[0]['CPM_OP_LUAS_TANAH'];
                    $iddoc = $datNOPInduk[0]['CPM_SPPT_DOC_ID'];
                    $vers = $datNOPInduk[0]['CPM_SPPT_DOC_VERSION'];


                    //Jika luas bumi NOP induk habis dipecah, maka NOP induk dihapus
                    if (($OP_INDUK_LUAS - $content['CPM_OP_LUAS_TANAH']) > 0) {

                        //die('masuk');

						//var_dump('masuk', $bOK, $isSpttFinal, $isSpttSusulan, $NOP_INDUK);exit;
                        
						
						$dtEditSPPTFinal = array('CPM_OP_LUAS_TANAH' => $OP_INDUK_LUAS - $content['CPM_OP_LUAS_TANAH']);
						
						//var_dump('masuk', $bOK, $isSpttFinal, $isSpttSusulan, $NOP_INDUK, $dtEditSPPTFinal);exit;
						
                        if ($isSpttFinal)
                            $bOK = $dbFinalSppt->edit($iddoc, $vers, $dtEditSPPTFinal);
                        else
                            $bOK = $dbFinalSppt->editSusulan($iddoc, $vers, $dtEditSPPTFinal);

                        // die(var_dump($isSpttFinal,$bOK));


                        // 18 APRIL 2018 BY 35U TECH START
                        $GWDBLink = mysqli_connect($appConfig['GW_DBHOST'], $appConfig['GW_DBUSER'], $appConfig['GW_DBPWD'], $appConfig['GW_DBNAME'], $appConfig['GW_DBPORT']);
                        if (!$GWDBLink) {
                            $gw = false;
                            echo mysqli_error($GWDBLink);
                        }
                        //mysql_select_db($appConfig['GW_DBNAME'], $GWDBLink) ;
                        $sppt  = $dbGwCurrent->getDataTagihanSPPT($NOP_INDUK, $appConfig['tahun_tagihan'], $GWDBLink);
                        $curVal = array();
                        $curVal['OP_LUAS_BUMI'] = $OP_INDUK_LUAS - $content['CPM_OP_LUAS_TANAH'];
                        if ($sppt['PAYMENT_FLAG'] != 1 && $_REQUEST['OP_PENETAPAN_INDUK'] == "1") { // JIKA BELUM BAYAR MAKA UPDATE SPPT DAN CURRENT
                            $updatePBBInduk = $dbGwCurrent->updateTagihanSPPT($NOP_INDUK, $appConfig['tahun_tagihan'], $curVal, $GWDBLink);
                            if ($updatePBBInduk) {
                                mysqli_select_db($GWDBLink, $appConfig['ADMIN_SW_DBNAME']);
                                $updateCurrentInduk = $dbServices->editSpptCurrent($NOP_INDUK, $curVal);
                                // YANG BIASANYA KEPAKE


                                // jika mau di tetapkan tahun ketetapan saat ini
                                // 08 MEI 2018 BY 35U TECH START
                                // if ($_REQUEST['OP_PENETAPAN_INDUK']=="1"){ // jika NOP Induk di tetapkan tahun config skrg maka

                                // }
                                // 08 MEI 2018 BY 35U TECH END


                            }
                        }

                        mysqli_select_db($GWDBLink, $appConfig['ADMIN_SW_DBNAME']);
                        $initData = $dbSpptPerubahan->getInitData($idServices);





                        //ARD- menonaktifkan update ke current
                        //$dbGwCurrent->edit($NOP_INDUK, array('OP_LUAS_BUMI' => $OP_INDUK_LUAS - $content['CPM_OP_LUAS_TANAH']),$appConfig);

                    } else {
                        //hapus dari sppt final
                        if ($isSpttFinal) {
                            $bOK = $dbFinalSppt->moveFinalToHistory($iddoc, $vers);
                            $bOK = $dbFinalSppt->doPurge($iddoc, $vers);
                        } else {
                            $bOK = $dbFinalSppt->moveSusulanToHistory($iddoc, $vers);
                            $bOK = $dbFinalSppt->doPurgeSusulan($iddoc, $vers);
                        }
                        $dbGwCurrent->del($NOP_INDUK);
                    }
                } else {
                    // echo "false";
                    // exit;
                }

                /* Jika NOP memiliki NOP Bersama, maka insert ke tabel cppmod_pbb_sppt_anggota */
                if (trim($content['CPM_NOP_BERSAMA']) != '') {
                    $bOK = $dbSppt->addAnggota($content['CPM_NOP_BERSAMA'], $content['CPM_NOP']);
                }
            }
        } else {
            $errorMsg = "<div class='error'>Data SPOP gagal dimasukkan. Ada kesalahan pada server database. Mohon ulangi.</div>";
            $aVal['CPM_TRAN_FLAG'] = 0;
            $dbSpptTran->edit($idt, $aVal);
        }
    }

    //saving uploaded file
    if ($bOK && $_FILES['OP_SKET']['error'] != 4) {
        $pos = strpos($_FILES['OP_SKET']['name'], '.');
        if ($pos != "")
            $ext = substr($_FILES['OP_SKET']['name'], $pos);
        else
            $ext = ".jpg";

        $filename = $content['CPM_NOP'] . "-" . date("Ymd") . "001" . $ext;
        $target = $appConfig['target_path'] . $filename;

        $bOK = move_uploaded_file($_FILES['OP_SKET']['tmp_name'], $target);
        if ($bOK) {
            $aValue['CPM_OP_SKET'] = $target;
            $aSppt = $dbSppt->gets($idDoc, $vers);
            $oldPath = $aSppt[0]['CPM_OP_SKET'];

            $bOK = $dbSppt->edit($idDoc, $vers, $aValue);

            if (!$bOK) {
                unlink($target);
                $errorMsg = "<div class='error'>Error while uploading. Please try again</div>";
            } else {
                if ($oldPath != "") {
                    unlink($oldPath);
                }
            }
        } else {
            $errorMsg = "<div class='error'>Permission error or file not uploaded. Please try again later</div>";
        }
    }

    //saving uploaded Foto
    if ($bOK && $_FILES['OP_FOTO']['error'] != 4) {
        $pos = strpos($_FILES['OP_FOTO']['name'], '.');
        if ($pos != "")
            $ext = substr($_FILES['OP_FOTO']['name'], $pos);
        else
            $ext = ".jpg";

        $filename = $content['CPM_NOP'] . "-" . date("Ymd") . "002" . $ext;
        $target = $appConfig['target_path'] . $filename;

        $bOK = move_uploaded_file($_FILES['OP_FOTO']['tmp_name'], $target);
        if ($bOK) {
            $aValue['CPM_OP_FOTO'] = $target;
            $aSppt = $dbSppt->gets($idDoc, $vers);
            $oldPath = $aSppt[0]['CPM_OP_FOTO'];

            $bOK = $dbSppt->edit($idDoc, $vers, $aValue);

            if (!$bOK) {
                unlink($target);
                $errorMsg = "<div class='error'>Error while uploading. Please try again</div>";
            } else {
                if ($oldPath != "") {
                    unlink($oldPath);
                }
            }
        } else {
            $errorMsg = "<div class='error'>Permission error or file not uploaded. Please try again later</div>";
        }
    }

    if ($bOK && $appConfig['jumlah_verifikasi'] == 0 && $newForm1 == 'Simpan dan Finalkan') {        // jika tanpa verifikasi dan langsung d menjadi NOP
        $vals     = $dbSpptTran->gets($idt);

        $dbSpptTran->edit($idt, array('CPM_TRAN_FLAG' => '1'));
        if ($appConfig['jumlah_verifikasi'] == 0) {
            $vals[0]['CPM_TRAN_STATUS'] = 4;
        } else {
            $vals[0]['CPM_TRAN_STATUS'] = 1;
        }

        $idd         = $vals[0]['CPM_TRAN_SPPT_DOC_ID'];
        $v             = $vals[0]['CPM_SPPT_DOC_VERSION'];
        $docSPPT     = $dbSppt->gets($idd, $v);
        // echo "<pre>";
        // print_r($docSPPT); exit;
        unset($vals[0]['CPM_TRAN_ID']);
        unset($vals[0]['CPM_TRAN_DATE']);

        foreach ($vals[0] as $key => $value) {
            if (is_numeric($key)) {
                unset($vals[0][$key]);
            }
        }

        $dbSpptTran->add($idt, $vals[0]);

        $contentWP = array();
        $contentWP['CPM_WP_STATUS'] = $docSPPT[0]['CPM_WP_STATUS'];
        $contentWP['CPM_WP_PEKERJAAN'] = $docSPPT[0]['CPM_WP_PEKERJAAN'];
        $contentWP['CPM_WP_NAMA'] = strtoupper($docSPPT[0]['CPM_WP_NAMA']);
        $contentWP['CPM_WP_ALAMAT'] = strtoupper($docSPPT[0]['CPM_WP_ALAMAT']);
        $contentWP['CPM_WP_KELURAHAN'] = strtoupper($docSPPT[0]['CPM_WP_KELURAHAN']);
        $contentWP['CPM_WP_RT'] = strtoupper($docSPPT[0]['CPM_WP_RT']);
        $contentWP['CPM_WP_RW'] = strtoupper($docSPPT[0]['CPM_WP_RW']);
        $contentWP['CPM_WP_PROPINSI'] = strtoupper($docSPPT[0]['CPM_WP_PROPINSI']);
        $contentWP['CPM_WP_KOTAKAB'] = strtoupper($docSPPT[0]['CPM_WP_KOTAKAB']);
        $contentWP['CPM_WP_KECAMATAN'] = strtoupper($docSPPT[0]['CPM_WP_KECAMATAN']);
        $contentWP['CPM_WP_KODEPOS'] = strtoupper($docSPPT[0]['CPM_WP_KODEPOS']);
        $contentWP['CPM_WP_NO_HP'] = strtoupper($docSPPT[0]['CPM_WP_NO_HP']);

        $bOK = $dbWajibPajak->save($docSPPT[0]['CPM_WP_NO_KTP'], $contentWP);

        /*kirim data peta*/
        // $url = $appConfig['MAP_URL']."service/migrate/movetopersil";

        // $query = sprintf("SELECT CPC_TKL_KELURAHAN FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID = '%s'", $docSPPT[0]['CPM_OP_KELURAHAN']);
        // $dtKel = mysqli_fetch_assoc(mysqli_query($DBLink, $query));
        // $nmKel = $dtKel['CPC_TKL_KELURAHAN'];

        // $query = sprintf("SELECT CPC_TKC_KECAMATAN FROM cppmod_tax_kecamatan WHERE CPC_TKC_ID = '%s'",$docSPPT[0]['CPM_OP_KECAMATAN']);
        // $dtKec = mysqli_fetch_assoc(mysqli_query($DBLink, $query));
        // $nmKec = $dtKec['CPC_TKC_KECAMATAN'];

        // $jenisTanah = array(
        // 	1=>'TANAH+BANGUNAN',
        // 	2=>'KAVLING SIAP BANGUN',
        // 	3=>'TANAH KOSONG',
        // 	4=>'FASILITAS UMUM'
        // );
        // $vars = array(
        // 		'jns_tanah'=>$jenisTanah[$docSPPT[0]['CPM_OT_JENIS']],
        // 		'kelas_znt'=>$docSPPT[0]['CPM_OT_ZONA_NILAI'],
        // 		'nir'=>'',
        // 		'cpm_sppt_id'=>$docSPPT[0]['CPM_SPPT_DOC_ID'],
        // 		'jns_trnks'=>'',
        // 		'nop'=>$docSPPT[0]['CPM_NOP'],
        // 		'nop_baru'=>$docSPPT[0]['CPM_NOP'],
        // 		'nop_brsm'=>$docSPPT[0]['CPM_NOP'],
        // 		'nop_asal'=>$docSPPT[0]['CPM_NOP'],
        // 		'op_kel'=>$nmKel,
        // 		'op_kec'=>$nmKec,
        // 		'status'=>strtoupper($docSPPT[0]['CPM_WP_STATUS']),
        // 		'pekerjaan'=>$docSPPT[0]['CPM_WP_PEKERJAAN'],
        // 		'nama_sp'=>$docSPPT[0]['CPM_WP_NAMA'],
        // 		'latitude'=>$docSPPT[0]['CPM_OT_LATITUDE'],
        // 		'longitude'=>$docSPPT[0]['CPM_OT_LATITUDE'],
        // 		'geom'=> ''
        // );

        // $postData = http_build_query($vars);
        // $ch = curl_init( $url );
        // curl_setopt( $ch, CURLOPT_POST, 1);
        // curl_setopt( $ch, CURLOPT_POSTFIELDS, $postData);
        // curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        // curl_setopt( $ch, CURLOPT_HEADER, 0);
        // curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
        // $response = curl_exec( $ch );
        /* end kirim*/

        if ($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end']) {
            $bOK = $dbSpptHistory->goSusulan($idt);
        } else {
            $bOK = $dbSpptHistory->goFinal($idt);
        }
    }
    // echo "<pre>";
    // print_r($_REQUEST);
    // echo "</pre>";
    // go to lampiran
    // var_dump($bOK);
    // exit;
    // die(print_r("a=$a&m=$m&f=" . $arConfig['id_lampiran'] . "&idt=$idt&num=1"));
    if ($bOK) {
        if ($OP_JML_BANGUNAN > 0) {

            if (isset($newForm1) && $newForm1 == 'Simpan dan Finalkan') {
                header("Location: main.php?param=" . base64_encode("a=$a&m=$m"));
                exit();
            } else {
                header("Location: main.php?param=" . base64_encode("a=$a&m=$m&f=" . $arConfig['id_lampiran'] . "&idt=$idt&num=1"));
                exit();
            }
        } else {
            header("Location: main.php?param=" . base64_encode("a=$a&m=$m"));
            exit();
        }
    } else {
        echo "input data gagal";
        exit();
    }
}

//////////////////////////////
// Process approved by kelurahan
/////////////////////////////

if (isset($_REQUEST['btn-process']) && $arConfig['usertype'] == "kelurahan") {
    // print_r($arConfig);
    // print_r($_REQUEST);exit;
    #print_r($appConfig);exit;
    if (isset($rekomendasi)) {
        $lastID = c_uuid();
        $aVal['CPM_TRAN_FLAG'] = 1;
        $vals = $dbSpptTran->gets($idt);

        //=========================================================================== 
        $docSPPT = $dbSppt->gets($idd, $v);
        $xx = getNOPIndukFromSplit($docSPPT[0]['CPM_NOP']);

        /*var_dump($idt);
        var_dump($aVal);
        exit();*/

        if (count($xx) > 0) {
            if ($appConfig['jumlah_verifikasi'] == 1 && $rekomendasi == "y" && isset($xx['NOP_INDUK'])) { // jika jumlah verifikasi 1 dan pemecahan

                // JIKA VERIFIKASI 1 START BY END OF MEI 2018
                // 09 MEI 2018 BY 35U TECH START
                // GET NOP INDUK BY NO SERVICE

                // var_dump($xx);
                // exit;
                setChangeInduk($xx);
                // 09 MEI 2018 BY 35U TECH END
                // JIKA VERIFIKASI 1 END BY END OF MEI 2018
            }
        }
        //===========================================================================

        $dbSpptTran->edit($idt, $aVal);

        unset($vals[0]['CPM_TRAN_ID']);
        unset($vals[0]['CPM_TRAN_DATE']);

        $vals[0]['CPM_TRAN_OPR_KELURAHAN'] = $uname;
        $vals[0]['CPM_TRAN_DATE'] = date("Y-m-d H:i:s");

        if ($rekomendasi == "y") {

            if ($appConfig['jumlah_verifikasi'] == 1) {
                $vals[0]['CPM_TRAN_STATUS'] = 4;
            } else {
                $vals[0]['CPM_TRAN_STATUS'] = 2;
            }

            $idd = $vals[0]['CPM_TRAN_SPPT_DOC_ID'];
            $v = $vals[0]['CPM_SPPT_DOC_VERSION'];
            $docSPPT = $dbSppt->gets($idd, $v);

            $updateService = array();
            $updateService['CPM_STATUS'] = '4';
            $updateService['CPM_APPROVER'] = $uname;
            $updateService['CPM_DATE_APPROVER'] = date("Y-m-d");


            foreach ($vals[0] as $key => $value) {
                if (is_numeric($key)) {
                    unset($vals[0][$key]);
                }
            }

            $dbServices->updateTransactionFromPendataan($docSPPT[0]['CPM_NOP'], $updateService);
            //insert to TRANMAIN last status
            $dbSpptTran->add($lastID, $vals[0]);

            if ($appConfig['jumlah_verifikasi'] == 1) { // jika jumlah verifikasi 1


                $contentWP = array();
                $contentWP['CPM_WP_STATUS'] = $docSPPT[0]['CPM_WP_STATUS'];
                $contentWP['CPM_WP_PEKERJAAN'] = $docSPPT[0]['CPM_WP_PEKERJAAN'];
                $contentWP['CPM_WP_NAMA'] = strtoupper($docSPPT[0]['CPM_WP_NAMA']);
                $contentWP['CPM_WP_ALAMAT'] = strtoupper($docSPPT[0]['CPM_WP_ALAMAT']);
                $contentWP['CPM_WP_KELURAHAN'] = strtoupper($docSPPT[0]['CPM_WP_KELURAHAN']);
                $contentWP['CPM_WP_RT'] = strtoupper($docSPPT[0]['CPM_WP_RT']);
                $contentWP['CPM_WP_RW'] = strtoupper($docSPPT[0]['CPM_WP_RW']);
                $contentWP['CPM_WP_PROPINSI'] = strtoupper($docSPPT[0]['CPM_WP_PROPINSI']);
                $contentWP['CPM_WP_KOTAKAB'] = strtoupper($docSPPT[0]['CPM_WP_KOTAKAB']);
                $contentWP['CPM_WP_KECAMATAN'] = strtoupper($docSPPT[0]['CPM_WP_KECAMATAN']);
                $contentWP['CPM_WP_KODEPOS'] = strtoupper($docSPPT[0]['CPM_WP_KODEPOS']);
                $contentWP['CPM_WP_NO_HP'] = strtoupper($docSPPT[0]['CPM_WP_NO_HP']);

                $bOK = $dbWajibPajak->save($docSPPT[0]['CPM_WP_NO_KTP'], $contentWP);


                /*kirim data peta*/
                // $url = $appConfig['MAP_URL']."service/migrate/movetopersil";

                // $query = sprintf("SELECT CPC_TKL_KELURAHAN FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID = '%s'", $docSPPT[0]['CPM_OP_KELURAHAN']);
                // $dtKel = mysqli_fetch_assoc(mysqli_query($DBLink, $query));
                // $nmKel = $dtKel['CPC_TKL_KELURAHAN'];

                // $query = sprintf("SELECT CPC_TKC_KECAMATAN FROM cppmod_tax_kecamatan WHERE CPC_TKC_ID = '%s'",$docSPPT[0]['CPM_OP_KECAMATAN']);
                // $dtKec = mysqli_fetch_assoc(mysqli_query($DBLink, $query));
                // $nmKec = $dtKec['CPC_TKC_KECAMATAN'];

                // $jenisTanah = array(
                // 	1=>'TANAH+BANGUNAN',
                // 	2=>'KAVLING SIAP BANGUN',
                // 	3=>'TANAH KOSONG',
                // 	4=>'FASILITAS UMUM'
                // );
                // $vars = array(
                // 	'jns_tanah'=>$jenisTanah[$docSPPT[0]['CPM_OT_JENIS']],
                // 	'kelas_znt'=>$docSPPT[0]['CPM_OT_ZONA_NILAI'],
                // 	'nir'=>'',
                // 	'cpm_sppt_id'=>$docSPPT[0]['CPM_SPPT_DOC_ID'],
                // 	'jns_trnks'=>'',
                // 	'nop'=>$docSPPT[0]['CPM_NOP'],
                // 	'nop_baru'=>$docSPPT[0]['CPM_NOP'],
                // 	'nop_brsm'=>$docSPPT[0]['CPM_NOP'],
                // 	'nop_asal'=>$docSPPT[0]['CPM_NOP'],
                // 	'op_kel'=>$nmKel,
                // 	'op_kec'=>$nmKec,
                // 	'status'=>strtoupper($docSPPT[0]['CPM_WP_STATUS']),
                // 	'pekerjaan'=>$docSPPT[0]['CPM_WP_PEKERJAAN'],
                // 	'nama_sp'=>$docSPPT[0]['CPM_WP_NAMA'],
                // 	'latitude'=>$docSPPT[0]['CPM_OT_LATITUDE'],
                // 	'longitude'=>$docSPPT[0]['CPM_OT_LATITUDE'],
                // 	'geom'=> ''
                // );

                // $postData = http_build_query($vars);
                // $ch = curl_init( $url );
                // curl_setopt( $ch, CURLOPT_POST, 1);
                // curl_setopt( $ch, CURLOPT_POSTFIELDS, $postData);
                // curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
                // curl_setopt( $ch, CURLOPT_HEADER, 0);
                // curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
                // $response = curl_exec( $ch );
                /* end kirim*/

                if ($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end']) {
                    // die('nyusul');
                    $bOK = $dbSpptHistory->goSusulan($lastID);
                } else {
                    // die('final');
                    $bOK = $dbSpptHistory->goFinal($lastID);
                }
            } // end jika jumlah verifikasi = 1
        } else if ($rekomendasi == "n") {
            $vals[0]['CPM_TRAN_STATUS'] = 6;
            $vals[0]['CPM_TRAN_INFO'] = $TRAN_INFO;

            foreach ($vals[0] as $key => $value) {
                if (is_numeric($key)) {
                    unset($vals[0][$key]);
                }
            }

            $dbSpptTran->add($lastID, $vals[0]);
        }

        //$lastID = c_uuid();



    }
    header("Location: main.php?param=" . base64_encode("a=$a&m=$m"));
}
?>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>

<script src="function/PBB/consol/jquery.validate.min.js"></script>
<link rel="stylesheet" href="function/PBB/consol/newspop.css?v.0.0.1" type="text/css">
<script type="text/javascript" src="inc/datepicker/datepickercontrol.js"></script>
<script type="text/javascript" src="function/PBB/consol/script.js?v.0.0.0.4"></script>
<link type="text/css" rel="stylesheet" href="inc/datepicker/datepickercontrol.css?v0001">

<input type="hidden" id="DPC_TODAY_TEXT" value="today">
<input type="hidden" id="DPC_BUTTON_TITLE" value="Open calendar...">
<input type="hidden" id="DPC_MONTH_NAMES" value="['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']">
<input type="hidden" id="DPC_DAY_NAMES" value="['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']">

<?php
/* Pengecekan dilakukan terhadap data pendataan yang sudah disimpan sebelumnya */
if (isset($idd) || isset($v)) {
    $docVal = $dbSppt->gets($idd, $v);
    foreach ($docVal[0] as $key => $value) {
        $tKey = substr($key, 4);
        $$tKey = $value;
    }

    // added by d3Di
    if(substr($NOP, -1)==3) {
        $OP_TYPE = 12;
        unset($NOP_INDUK);
    }
    //---------------------

    $aDocExt = $dbSpptExt->gets($idd, $v);

    if (isset($aDocExt)) {
        $HtmlExt = "";
        foreach ($aDocExt as $docExt) {
            $param = "a=$a&m=$m&f=" . $arConfig['id_lampiran'] . "&idt=$idt&num=" . $docExt['CPM_OP_NUM'];
            $HtmlExt .= "<li><a href='main.php?param=" . base64_encode($param) . "'>Lampiran Bangunan " . $docExt['CPM_OP_NUM'] . "</a>";

            if (!isset($readonly) || !$readonly) $HtmlExt .= "<a href='#' onClick=\"deleteLampiran('" . $idd . "', '" . $docExt['CPM_OP_NUM'] . "');\"><img border=\"0\" alt=\"Hapus Lampiran\" title=\"Hapus Lampiran\" src=\"image/icon/delete.png\"></a>";
            $HtmlExt .= "</li>";
        }
    }

    //bagian utk menambahkan lampiran baru
    if (($OP_JML_BANGUNAN > 0) && ($aDocExt == "" || count($aDocExt) < $OP_JML_BANGUNAN)) {
        $btnExt = "<input type='button' value='Tambah Baru' onclick=\"javascript:window.location='main.php?param=" . base64_encode("a=$a&m=$m&f=" . $arConfig['id_lampiran'] . "&idt=$idt") . "'\">";
    }
    
    $CPM_KODE_LOKASI = substr($NOP, 0,10);
    $bZNT = $dbUtils->getZNT_with_kelas(array("CPM_KODE_LOKASI" => $CPM_KODE_LOKASI));

    $bKecamatanOP = $dbUtils->getKecamatan($OP_KECAMATAN);
    $bKelurahanOP = $dbUtils->getKelurahan($OP_KELURAHAN);

    

    $OP_KELURAHAN_NAMA = "";
    $OP_KECAMATAN_NAMA = "";
    $NOMOR_SERTIFIKAT = "";
    $TANGGAL_SERTIFIKAT = "";
    $NAMA_SERTIFIKAT = "";
    $JENIS_HAK = "";

    if (count($bKecamatanOP) > 0)
        $OP_KECAMATAN_NAMA = $bKecamatanOP[0]['CPC_TKC_KECAMATAN'];
    if (count($bKelurahanOP) > 0)
        $OP_KELURAHAN_NAMA = $bKelurahanOP[0]['CPC_TKL_KELURAHAN'];

    $sert = $dbSppt->get_sertifikat($NOP)[0];
    $NOMOR_SERTIFIKAT = isset($sert['CPM_NOMOR_SERTIFIKAT']) ? $sert['CPM_NOMOR_SERTIFIKAT'] : '';
    $TANGGAL_SERTIFIKAT = isset($sert['CPM_TANGGAL']) ? $sert['CPM_TANGGAL'] : '';
    $NAMA_SERTIFIKAT = isset($sert['CPM_NAMA_SERTIFIKAT']) ? $sert['CPM_NAMA_SERTIFIKAT'] : '';
    $JENIS_HAK = isset($sert['CPM_JENIS_HAK']) ? $sert['CPM_JENIS_HAK'] : '';
} else if (isset($NOP_INDUK) && $NOP_INDUK) {
    // var_dump($NOP_INDUK);exit;
    /* Pengecekan dilakukan terhadap data pemecahan, default dalam form SPOP disesuaikan dengan data induk objek pajak */
    /* Jika ada parameter NOPInduk maka proses yang dilakukan adalah input data pemecahan NOP
     *  ambil data luas NOP induk
     *  jika luas bumi NOP induk habis dipecah, maka ketika di simpan NOP induk langsung dihapus
     *  jika luas bumi NOP induk tidak habis dipecah, maka ketika di simpan luas bumi NOP induk oromatis berkurang sesuai dengan pemecahan dari NOP yang baru dibuat
     */
    if ($dbFinalSppt->isNopExist($NOP_INDUK)) {
        // echo "1";
        $datNOPInduk = $dbFinalSppt->get_where(array("CPM_NOP" => $NOP_INDUK));
    } else {
        // echo "2";
        $datNOPInduk = $dbFinalSppt->get_susulan(array("CPM_NOP" => $NOP_INDUK));
    }
    // exit;
    // var_dump($datNOPInduk);exit;
    $OP_INDUK_LUAS = $datNOPInduk[0]['CPM_OP_LUAS_TANAH'];
    $OT_ZONA_NILAI = $datNOPInduk[0]['CPM_OT_ZONA_NILAI'];

    $NOP = substr($NOP_INDUK, 0, 13);
    $OP_KECAMATAN = $datNOPInduk[0]['CPM_OP_KECAMATAN'];
    $OP_KELURAHAN = $datNOPInduk[0]['CPM_OP_KELURAHAN'];
    $OP_RW = $datNOPInduk[0]['CPM_OP_RW'];
    $OP_RT = $datNOPInduk[0]['CPM_OP_RT'];
    $OP_ALAMAT = $datNOPInduk[0]['CPM_OP_ALAMAT'];
    $OP_NOMOR = $datNOPInduk[0]['CPM_OP_NOMOR'];

    $CPM_KODE_LOKASI = substr($NOP, 0,10);
    $bZNT = $dbUtils->getZNT_with_kelas(array("CPM_KODE_LOKASI" => $CPM_KODE_LOKASI));

    $bKecamatanOP = $dbUtils->getKecamatan($OP_KECAMATAN);
    $bKelurahanOP = $dbUtils->getKelurahan($OP_KELURAHAN);

    $OP_KELURAHAN_NAMA = "";
    $OP_KECAMATAN_NAMA = "";

    if (count($bKecamatanOP) > 0)
        $OP_KECAMATAN_NAMA = $bKecamatanOP[0]['CPC_TKC_KECAMATAN'];
    if (count($bKelurahanOP) > 0)
        $OP_KELURAHAN_NAMA = $bKelurahanOP[0]['CPC_TKL_KELURAHAN'];

    /*GET WP*/
    $filterService = array('CPM_ID' => $idServices);
    // var_dump($filterService);
    // $dtService = $dbServices->gets($filterService);
    $dtService = $dbServices->get($filterService);


    // var_dump($dtService);exit;

    $filterWajibPajak = array('CPM_WP_ID' => $dtService[0]['CPM_WP_NO_KTP']);
    // $dtWp = $dbWajibPajak->gets($filterWajibPajak);
    $dtWp = $dbWajibPajak->get_id($filterWajibPajak);

    #echo '<pre>'.print_r($dtWp,true).'</pre>';

    // $NOP = $dtService[0]['CPM_OP_KELURAHAN'];
    $WP_NAMA = $dtWp[0]['CPM_WP_NAMA'];
    $WP_ALAMAT = $dtWp[0]['CPM_WP_ALAMAT'];
    $WP_RT = $dtWp[0]['CPM_WP_RT'];
    $WP_RW = $dtWp[0]['CPM_WP_RW'];
    $WP_KELURAHAN = $dtWp[0]['CPM_WP_KELURAHAN'];
    $WP_KECAMATAN = $dtWp[0]['CPM_WP_KECAMATAN'];
    $WP_PROPINSI = $dtWp[0]['CPM_WP_PROPINSI'];
    $WP_KOTAKAB = $dtWp[0]['CPM_WP_KOTAKAB'];
    $WP_NO_HP = $dtWp[0]['CPM_WP_NO_HP'];
    $WP_NO_KTP = $dtWp[0]['CPM_WP_ID'];
    $WP_KODEPOS = $dtWp[0]['CPM_WP_KODEPOS'];
    // $OP_INDUK_LUAS = 2000;
    // echo $OP_INDUK_LUAS;exit;

} else if (isset($idServices)) {
    /* Pengecekan dilakukan terhadap data penerimaan pelayanan, sehingga data-data dari penerimaan pelayanan dijadikan nilai default dalam form SPOP */
    $filterService = array('CPM_ID' => $idServices);
    //$dtService = $dbServices->get($filterService);
    $dtService = $dbServices->get_id($filterService);
    // var_dump($dtService);exit();

    $filterWajibPajak = array('CPM_WP_ID' => $dtService[0]['CPM_WP_NO_KTP']);
    $dtWp = $dbWajibPajak->get_id($filterWajibPajak);

    // echo '<pre>'.print_r($dtWp,true).'</pre>';

    $NOP = $dtService[0]['CPM_OP_KELURAHAN'];
    $WP_NAMA = $dtWp[0]['CPM_WP_NAMA'];
    $WP_ALAMAT = $dtWp[0]['CPM_WP_ALAMAT'];
    $WP_RT = $dtWp[0]['CPM_WP_RT'];
    $WP_RW = $dtWp[0]['CPM_WP_RW'];
    $WP_KELURAHAN = $dtWp[0]['CPM_WP_KELURAHAN'];
    $WP_KECAMATAN = $dtWp[0]['CPM_WP_KECAMATAN'];
    $WP_PROPINSI = $dtWp[0]['CPM_WP_PROPINSI'];
    $WP_KOTAKAB = $dtWp[0]['CPM_WP_KOTAKAB'];
    $WP_NO_HP = $dtWp[0]['CPM_WP_NO_HP'];
    $WP_NO_KTP = $dtWp[0]['CPM_WP_ID'];
    $WP_KODEPOS = $dtWp[0]['CPM_WP_KODEPOS'];


    $WP_PEKERJAAN = $dtWp[0]['CPM_WP_PEKERJAAN'];
    // $WP_STATUS = $dtWp[0]['CPM_WP_PEKERJAAN'];

    $OP_KECAMATAN = $dtService[0]['CPM_OP_KECAMATAN'];
    $OP_KELURAHAN = $dtService[0]['CPM_OP_KELURAHAN'];
    $OP_RW = $dtService[0]['CPM_OP_RW'];
    $OP_RT = $dtService[0]['CPM_OP_RT'];
    $OP_ALAMAT = $dtService[0]['CPM_OP_ADDRESS'];
    $OP_NOMOR = $dtService[0]['CPM_OP_ADDRESS_NO'];
    $OP_TYPE = $dtService[0]['CPM_TYPE'];
    if($OP_TYPE!=2) unset($NOP_INDUK);

    // $bZNT = $dbUtils->getZNT(null, array("CPM_KODE_LOKASI" => $OP_KELURAHAN));
    $bZNT = $dbUtils->getZNT_with_kelas(array("A.CPM_KODE_LOKASI" => $OP_KELURAHAN));

    //var_dump($bZNT);exit();

    $bKecamatanOP = $dbUtils->getKecamatan($OP_KECAMATAN);
    $bKelurahanOP = $dbUtils->getKelurahan($OP_KELURAHAN);

    $OP_KELURAHAN_NAMA = "";
    $OP_KECAMATAN_NAMA = "";

    if (count($bKecamatanOP) > 0)
        $OP_KECAMATAN_NAMA = $bKecamatanOP[0]['CPC_TKC_KECAMATAN'];
    if (count($bKelurahanOP) > 0)
        $OP_KELURAHAN_NAMA = $bKelurahanOP[0]['CPC_TKL_KELURAHAN'];

    #echo '<pre>'.print_r($dtService[0],true).'</pre>';
}

//var_dump($OT_PAYMENT_SISTEM);exit();
echo '<div class="col-md-12">';
include("page1.php");
if (($arConfig['usertype'] == "kelurahan" && $tran[0]['CPM_TRAN_STATUS'] == 1)) {
?>
    <br>
    <div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-11" style="max-width:840px;background:#FFF;padding:25px;box-shadow:0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);border:solid 1px #54936f">
            <form method="post">
                <table border="0" cellpadding="5" class="table table-bordered">
                    <tr>
                        <td colspan=2 class="tbl-rekomen"><b>Masukkan rekomendasi anda</b></td>
                    </tr>
                    <tr>
                        <td class="tbl-rekomen"><label><input checked="" type="radio" name="rekomendasi" value="y"> Setuju</label></td>
                        <td class="tbl-rekomen">&nbsp;</td>
                    </tr>
                    <tr>
                        <td valign="top" class="tbl-rekomen"><label><input type="radio" name="rekomendasi" value="n"> Tolak</label></td>
                        <td class="tbl-rekomen">Alasan<br><textarea name="TRAN_INFO" cols=70 rows=7></textarea></td>
                    </tr>
                    <tr>
                        <td colspan=2 align="right" class="tbl-rekomen"><input type="submit" name="btn-process" value="Submit"></td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
<?php
}
echo '</div>';
?>
<style type="text/css">
    #load-mask,
    #load-content {
        display: none;
        position: fixed;
        height: 100%;
        width: 100%;
        top: 0;
        left: 0;
    }

    #load-mask {
        background-color: #000000;
        filter: alpha(opacity=70);
        opacity: 0.7;
        z-index: 1;
    }

    #load-content {
        z-index: 2;
    }

    #loader {
        margin-right: auto;
        margin-left: auto;
        background-color: #ffffff;
        width: 100px;
        height: 100px;
        margin-top: 200px;
    }
</style>
<div id="load-content">
    <div id="loader">
        <img src="image/icon/loading-big.gif" style="margin-right: auto;margin-left: auto;" />
    </div>
</div>
<div id="load-mask"></div>






<?php

// 31 MEI 2018 BY TRY SETYO UTOMO

function getNOPIndukFromSplit($nop_anak)
{
    global $appConfig;
    $dbLink = mysqli_connect($appConfig['GW_DBHOST'], $appConfig['ADMIN_DBUSER'], $appConfig['ADMIN_DBPWD'], $appConfig['ADMIN_SW_DBNAME'], $appConfig['GW_DBPORT']); // koneksi ke gw
    //mysql_select_db($appConfig['ADMIN_SW_DBNAME'], $dbLink) or die(mysqli_error($DBLink));
    $query = "SELECT
            A.*, B.CPM_OP_NUMBER AS NOP_INDUK
            FROM
            cppmod_pbb_service_split A
            INNER JOIN cppmod_pbb_services B ON A.CPM_SP_SID = B.CPM_ID
            WHERE A.CPM_SP_NOP = '$nop_anak'
             ";
    $data = mysqli_query($dbLink, $query) or die(mysqli_error($dbLink));
    $numrows = mysqli_num_rows($data);
    $array = array();
    if ($numrows > 0) {
        while ($row = mysqli_fetch_array($data)) {
            $array['CPM_SP_ID'] = $row['CPM_SP_ID'];
            $array['CPM_SP_SID'] = $row['CPM_SP_SID'];
            $array['CPM_SP_NOP'] = $row['CPM_SP_NOP'];
            $array['CPM_SP_PENETAPAN_INDUK'] = $row['CPM_SP_PENETAPAN_INDUK'];
            $array['NOP_INDUK'] = $row['NOP_INDUK'];
        }
    }

    return $array;
}

function hitungTagihan($njop, $njoptkp)
{
    global $appConfig, $dbUtils;
    $njoptkp = ($njoptkp != 0 || $njoptkp != null || $njoptkp != "" ? $njoptkp : 0);
    $minTagihan = ($appConfig['minimum_sppt_pbb_terhutang'] != 0 ? $appConfig['minimum_sppt_pbb_terhutang'] : 0);
    if ($njop > $njoptkp) {
        $njkp = $njop - $njoptkp;
    } else {
        $njkp = 0;
    }

    // echo $njkp;
    // exit;

    $tarif = $dbUtils->getTarif($njkp);
    $tagihan = $njkp * ($tarif / 100);
    if ($tagihan < $minTagihan) $tagihan = $minTagihan;
    return $tagihan;
}

function getNJKP($njop, $njoptkp)
{
    global $appConfig, $dbUtils;
    $njoptkp = ($njoptkp != 0 || $njoptkp != null || $njoptkp != "" ? $njoptkp : 0);
    $minTagihan = ($appConfig['minimum_sppt_pbb_terhutang'] != 0 ? $appConfig['minimum_sppt_pbb_terhutang'] : 0);

    // var_dump("123");
    // var_dump("NJOP ".$njop);
    // var_dump("NJOPTKP ".$njoptkp);
    // exit;

    if ($njop > $njoptkp) {
        $njkp = $njop - $njoptkp;
    } else {
        $njkp = 0;
    }

    return $njkp;
}

function setChangeInduk($xx)
{

    global $dbGwCurrent, $dbUtils, $dbSpptPerubahan, $dbSppt, $dbWajibPajak, $appConfig, $dbServices, $NBParam, $NBParam_before;
    $svc_id = $xx['CPM_SP_SID'];
    $NOP_INDUK = $xx['NOP_INDUK']; // NOP INDUK DI GUNAKAN UNTUK PENILAIAN DAN PENETAPAN
    $dataPerubahan = $dbServices->getDataChangeBySID($svc_id); // mendapatkan data perubahan dari service change
    $is_penetapanan_thn_ini = $xx['CPM_SP_PENETAPAN_INDUK'];

    // JIKA NOP INDUK D TETAPKAN TAHUN INI MAKA
    $GWDBLink = mysqli_connect($appConfig['GW_DBHOST'], $appConfig['GW_DBUSER'], $appConfig['GW_DBPWD'], $appConfig['GW_DBNAME'], $appConfig['GW_DBPORT']); // koneksi ke gw

    if ($is_penetapanan_thn_ini == "1") {
        //mysql_select_db($appConfig['GW_DBNAME'], $GWDBLink) or die(mysqli_error($DBLink));
        $sppt = $dbGwCurrent->getDataTagihanSPPT($NOP_INDUK, $appConfig['tahun_tagihan'], $GWDBLink);

        // jika belum bayar [START]

        if ($sppt['PAYMENT_FLAG'] != 1 || $sppt['PAYMENT_FLAG'] === NULL) {

            // MELAKUKAN PENILAIAN MENGGUNAKAN SERVICE JAVA  [START]

            // $url = "10.24.200.5:8080/inc/PBB/svc-penilaian.php";
            //$url = "127.0.0.1:8080/inc/PBB/svc-penilaian.php";
            $url = "inc/PBB/svc-penilaian.php";

            $url = sprintf(
                "%s://%s",
                isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
                '36.92.151.83:2010'
              ) . '/';
            $url .= "inc/PBB/svc-penilaian.php";

            // $url = "http://okuselatankab.-.id/pbb/inc/PBB/svc-penilaian.php";
            $param = array(
                "SVR_PRM" => $NBParam,
                'NOP' => $NOP_INDUK,
                "TAHUN" => $appConfig['tahun_tagihan'],
                "TIPE" => 2,
                "SUSULAN" => "0"
            );
            // echo "<pre>";
            // print_r($param);
            // echo "</pre>";
            // exit;
            $param = json_encode($param);
            $param = base64_encode($param);
            $vars = array(
                "req" => $param
            );
            $postData = http_build_query($vars);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout in seconds
            $response = curl_exec($ch);
            // echo $response;
            
            $array = json_decode($response);

            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                
                echo "<br /><br />ERROR NIH<br />Tidak ada response dari $url<br />Hubungi IT nya<br /><br />";
                var_dump($error_msg, $array, $response);
                curl_close($ch);
                die;
            }


            // MELAKUKAN PENILAIAN MENGGUNAKAN SERVICE JAVA  [END]
            // jika penilaian sukses maka
// var_dump($array->RC);die;
            if ($array->RC == "0000") {
                mysqli_select_db($GWDBLink, $appConfig['ADMIN_SW_DBNAME']) or die(mysqli_error($GWDBLink));

                // MULAI HITUNG KEMBALI [START]
                // ambil data current untuk mendapatkan nilai NJOPTKP dan Tagihan sebelumnya

                $dataCurrent = $dbGwCurrent->getDataCurrent($NOP_INDUK);
                $njoptkp = $dataCurrent['OP_NJOPTKP'];
                $tagihanLama = $dataCurrent['SPPT_PBB_HARUS_DIBAYAR'];
                $totalNJOPBaru = $dataPerubahan['CPM_NJOP_TANAH'] + $dataPerubahan['CPM_NJOP_BANGUNAN'];
                $tagihanBaru = hitungTagihan($totalNJOPBaru, $njoptkp); // mendapatkan nilai tagihan
                $njkp_baru = getNJKP($totalNJOPBaru, $njoptkp); // mendapatkan NJKP

                // MULAI HITUNG KEMBALI [END]

                mysqli_select_db($GWDBLink, $appConfig['GW_DBNAME']) or die(mysqli_error($GWDBLink));

                // data dibawah ini untuk data perubahan

                $valTagihanSPPT = array();
                $valTagihanSPPT['SPPT_PBB_HARUS_DIBAYAR'] = $tagihanBaru;
                $valTagihanSPPT['WP_PEKERJAAN'] = $dataPerubahan['CPM_WP_PEKERJAAN'];
                $valTagihanSPPT['WP_NAMA'] = $dataPerubahan['CPM_WP_NAMA'];
                $valTagihanSPPT['WP_ALAMAT'] = $dataPerubahan['CPM_WP_ALAMAT'];
                $valTagihanSPPT['WP_KELURAHAN'] = $dataPerubahan['CPM_WP_KELURAHAN'];
                $valTagihanSPPT['WP_RT'] = $dataPerubahan['CPM_WP_RT'];
                $valTagihanSPPT['WP_RW'] = $dataPerubahan['CPM_WP_RW'];
                $valTagihanSPPT['WP_KOTAKAB'] = $dataPerubahan['CPM_WP_KOTAKAB'];
                $valTagihanSPPT['WP_KECAMATAN'] = $dataPerubahan['CPM_WP_KECAMATAN'];
                $valTagihanSPPT['WP_KODEPOS'] = $dataPerubahan['CPM_WP_KODEPOS'];
                $valTagihanSPPT['WP_NO_HP'] = $dataPerubahan['CPM_WP_NO_HP'];
                $valTagihanSPPT['OP_LUAS_BUMI'] = $dataPerubahan['CPM_OP_LUAS_TANAH'];
                $valTagihanSPPT['OP_LUAS_BANGUNAN'] = $dataPerubahan['CPM_OP_LUAS_BANGUNAN'];
                $valTagihanSPPT['OP_KELAS_BUMI'] = $dataPerubahan['CPM_OP_KELAS_TANAH'];
                $valTagihanSPPT['OP_KELAS_BANGUNAN'] = $dataPerubahan['CPM_OP_KELAS_BANGUNAN'];
                $valTagihanSPPT['OP_NJOP_BUMI'] = $dataPerubahan['CPM_NJOP_TANAH'];
                $valTagihanSPPT['OP_NJOP_BANGUNAN'] = $dataPerubahan['CPM_NJOP_BANGUNAN'];
                $valTagihanSPPT['OP_NJOP'] = $totalNJOPBaru;
                $valTagihanSPPT['OP_NJKP'] = $njkp_baru;
                $valTagihanSPPT['OP_ALAMAT'] = $dataPerubahan['CPM_OP_ALAMAT'];
                $valTagihanSPPT['OP_RT'] = $dataPerubahan['CPM_OP_RT'];
                $valTagihanSPPT['OP_RW'] = $dataPerubahan['CPM_OP_RW'];

                // UNTUK TABLE CURRENT

                //          echo "masuk";
                // exit;
                $valCurrentSPPT = array();
                $valCurrentSPPT['SPPT_PBB_HARUS_DIBAYAR'] = $tagihanBaru;
                $ubahSPPT = $dbGwCurrent->updateTagihanSPPT($NOP_INDUK, $appConfig['tahun_tagihan'], $valTagihanSPPT, $GWDBLink);
                if ($ubahSPPT) { // jika berhasil Ubah SPPT [START]
                    mysqli_select_db($GWDBLink, $appConfig['ADMIN_SW_DBNAME']);
                    $ubahCurrent1 = $dbSpptPerubahan->updateToCurrent($svc_id, $appConfig);
                    if ($ubahCurrent1) {
                        $ubahCurrent2 = $dbGwCurrent->updateCurrentSPPT($NOP_INDUK, $appConfig['tahun_tagihan'], $valCurrentSPPT, $appConfig);
                    }
                }
                mysqli_select_db($GWDBLink, $appConfig['ADMIN_SW_DBNAME']);
                lastAction($dataPerubahan, $svc_id); // update to Final

                // jika berhasil Ubah SPPT [END]

            } else {

                echo '<div class="col-md-12" style="background:#FFF;padding:25px;box-shadow:0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);border:solid 1px #54936f">';
                print_r($response);
                echo "<br><h2>Penilaian NOP Induk Gagal</h2>";
                echo "<br>NOP Induk = $NOP_INDUK THN ".($appConfig['tahun_tagihan']);
                echo "<br>Tidak Ada di Tabel Final";
                echo '<br>array = ';print_r($array);
                echo '<br>param = ';print_r($param);
                echo '</div>';
                // var_dump(expression)
                exit;
            }
        } // jika belum bayar [END]
        else { // jika udah bayar dan set tahun ini
            mysqli_select_db($GWDBLink, $appConfig['ADMIN_SW_DBNAME']);
            lastAction($dataPerubahan, $svc_id);
        }
    } // end  jika tahun ini d tetapkan
    else { // jika tahun depan maka
        mysqli_select_db($GWDBLink, $appConfig['ADMIN_SW_DBNAME']);
        lastAction($dataPerubahan, $svc_id);
    }
}

function lastAction($dataPerubahan, $svc_id)
{
    global $dbSpptPerubahan, $dbWajibPajak, $appConfig, $GWDBLink;

    // mau tahun depan atau sekarang tetap update final
    // GET DATA UNTUK SIMPEN KE WAJIB PAJAK [START]
    // mysql_select_db($appConfig['ADMIN_SW_DBNAME'], $GWDBLink);

    $contentWP = array();
    $contentWP['CPM_WP_STATUS'] = $dataPerubahan['CPM_WP_STATUS'];
    $contentWP['CPM_WP_PEKERJAAN'] = $dataPerubahan['CPM_WP_PEKERJAAN'];
    $contentWP['CPM_WP_NAMA'] = $dataPerubahan['CPM_WP_NAMA'];
    $contentWP['CPM_WP_ALAMAT'] = $dataPerubahan['CPM_WP_ALAMAT'];
    $contentWP['CPM_WP_KELURAHAN'] = $dataPerubahan['CPM_WP_KELURAHAN'];
    $contentWP['CPM_WP_RT'] = $dataPerubahan['CPM_WP_RT'];
    $contentWP['CPM_WP_RW'] = $dataPerubahan['CPM_WP_RW'];
    $contentWP['CPM_WP_PROPINSI'] = $dataPerubahan['CPM_WP_PROPINSI'];
    $contentWP['CPM_WP_KOTAKAB'] = $dataPerubahan['CPM_WP_KOTAKAB'];
    $contentWP['CPM_WP_KECAMATAN'] = $dataPerubahan['CPM_WP_KECAMATAN'];
    $contentWP['CPM_WP_KODEPOS'] = $dataPerubahan['CPM_WP_KODEPOS'];
    $contentWP['CPM_WP_NO_HP'] = $dataPerubahan['CPM_WP_NO_HP'];

    // GET DATA UNTUK SIMPEN KE WAJIB PAJAK [END]
    // UPDATE KE TABLE FINAL [START]
    // update data wajib pajak

    $res = $dbWajibPajak->save($dataPerubahan['CPM_WP_NO_KTP'], $contentWP);
    $res3 = $dbSpptPerubahan->updateToFinal($dataPerubahan['CPM_SPPT_DOC_ID'], $svc_id);
    if ($res3) {

        $res3 = $dbSpptPerubahan->deleteDataPerubahan($dataPerubahan['CPM_SPPT_DOC_ID']);
    } else {
        echo "Gagal melakukan penghapusan data perubahan";
    }

    // UPDATE KE TABLE FINAL [END]

}

// END 31 MEI 2018 BY TRY SETYO UTOMO

?>