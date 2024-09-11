<?php class FasilitasSusutLuas {

    public static function get($nop, $thn, $data, $conn, $kdJpb, $klsBintang) {
        $selainAcCentral= ["02", "04", "05", "07", "13"];
        $kdPropinsi     = substr($nop, 0, 2);
        $dati2          = substr($nop, 2, 2);
        $nilatTotal     = 0;

        $fop_ac_central     = $data->CPM_FOP_AC_CENTRAL;
        $pemadamHydrant     = (float)$data->CPM_PEMADAM_HYDRANT;
        $pemadamSprinkler   = (float)$data->CPM_PEMADAM_SPRINKLER;
        $pemadamFireAlarm   = (float)$data->CPM_PEMADAM_FIRE_ALARM;
        
        $resFasilitas = mysqli_query($conn, "SELECT * FROM cppmod_pbb_fasilitas WHERE STATUS_FASILITAS='0'");

        while ($objFas = mysqli_fetch_object($resFasilitas)) {
            $jmlSatuan = 0;
            $nilaiSatuan = 0;
            
            if($objFas->KD_FASILITAS == "03" && $kdJpb == "02"){
                $jmlSatuanACCentral = (int)$fop_ac_central;
                $jmlSatuan = ($jmlSatuanACCentral == 2) ? 0 : (float)$fop_ac_central;

            }else{
                if($objFas->KD_FASILITAS == "06" && $kdJpb == "04"){
                    $jmlSatuanACCentral = (int)$fop_ac_central;
                    $jmlSatuan = ($jmlSatuanACCentral == 2) ? 0 : (float)$fop_ac_central;

                }else{
                    if($objFas->KD_FASILITAS == "11" && in_array($kdJpb, $selainAcCentral)){
                        $jmlSatuanACCentral = (int)$fop_ac_central;
                        $jmlSatuan = ($jmlSatuanACCentral == 2) ? 0 : (float)$fop_ac_central;

                    }else{
                        if($objFas->KD_FASILITAS == "37"){
                            $jmlSatuan = $pemadamHydrant;
                        }else{
                            if($objFas->KD_FASILITAS == "38"){
                                $jmlSatuan = $pemadamSprinkler;
                            }else{
                                if($objFas->KD_FASILITAS == "39"){
                                    $jmlSatuan = $pemadamFireAlarm;
                                }else{
                                    $jmlSatuan = 0;
                                }
                            }
                        }
                    }
                }
            }


            if($objFas->KETERGANTUNGAN == "0"){
                $qNilaiSatuan ="SELECT NILAI_NON_DEP 
                                FROM cppmod_pbb_fas_non_dep 
                                WHERE 
                                    KD_PROPINSI = '$kdPropinsi' 
                                    AND KD_DATI2 = '$dati2' 
                                    AND THN_NON_DEP = '$thn' 
                                    AND KD_FASILITAS = '" . $objFas->KD_FASILITAS . "'";
                $res = mysqli_query($conn, $qNilaiSatuan);
                while ($obj = mysqli_fetch_object($res)) {
                    $nilaiSatuan = (float)$obj->NILAI_NON_DEP;
                }

            }elseif($objFas->KETERGANTUNGAN == "1") {
                $qNilaiSatuan ="SELECT NILAI_DEP_MIN_MAX 
                                FROM cppmod_pbb_fas_dep_min_max 
                                WHERE 
                                    KLS_DEP_MIN <= $jmlSatuan 
                                    AND KLS_DEP_MAX >= $jmlSatuan 
                                    AND KD_PROPINSI = '$kdPropinsi' 
                                    AND KD_DATI2 = '$dati2' 
                                    AND THN_DEP_MIN_MAX = '$thn' 
                                    AND KD_FASILITAS = '" . $objFas->KD_FASILITAS . "'";
                $res = mysqli_query($conn, $qNilaiSatuan);
                while ($obj = mysqli_fetch_object($res)) {
                    $nilaiSatuan = (float)$obj->NILAI_DEP_MIN_MAX;
                }

            }elseif($objFas->KETERGANTUNGAN == "2") {
                $qNilaiSatuan ="SELECT NILAI_FASILITAS_KLS_BINTANG 
                                FROM cppmod_pbb_fas_dep_jpb_kls_bintang 
                                WHERE 
                                    KD_JPB = '$kdJpb' 
                                    AND KLS_BINTANG = '$klsBintang' 
                                    AND KD_PROPINSI = '$kdPropinsi' 
                                    AND KD_DATI2 = '$dati2' 
                                    AND THN_DEP_JPB_KLS_BINTANG = '$thn' 
                                    AND KD_FASILITAS = '" . $objFas->KD_FASILITAS . "'";
                $res = mysqli_query($conn, $qNilaiSatuan);
                while ($obj = mysqli_fetch_object($res)) {
                    $nilaiSatuan = (float)$obj->NILAI_FASILITAS_KLS_BINTANG;
                }
                
            }else{
                $nilaiSatuan = 0;
            }
            $nilatTotal += ($jmlSatuan * $nilaiSatuan);
        }

        return $nilatTotal;
    }
}