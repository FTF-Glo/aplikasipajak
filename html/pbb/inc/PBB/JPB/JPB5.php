<?php class JPB5 {

    public static function get($nop, $thn, $data, $conn) {
        $selainAcCentralLain = ["02", "04", "05", "07", "13"];
        $kdPropinsi = substr($nop, 0, 2);
        $dati2      = substr($nop, 2, 2);
        $nilaiKomponenUtama = 0;
        $nilaiFasilitas = 0;
        $nilaiJpb5 = 0;
        $kdJpb = "05";

        $thnDibangun    = (int)$data->CPM_OP_THN_DIBANGUN;
        $thnDirenovasi  = (int)$data->CPM_OP_THN_RENOVASI;
        $luasBng        = (float)$data->CPM_OP_LUAS_BANGUNAN;
        $kondisiBng     = $data->CPM_OP_KONDISI;
        $klsJpb5        = $data->CPM_JPB5_KELAS_BANGUNAN;
        $luasKmrJpb5    = (float)$data->CPM_JPB5_LUAS_KMR_AC_CENTRAL;
        $luasRngLain    = (float)$data->CPM_JPB5_LUAS_RUANG_AC_CENTRAL;
        $jmlLantai      = $data->CPM_OP_JML_LANTAI;
        $jmlLantai      = ($jmlLantai == 0) ? 1 : $jmlLantai;
        
        $sql = "SELECT CPM_NILAI_DBKB_JPB5 
                FROM cppmod_pbb_dbkb_jpb5 
                WHERE 
                    CPM_KD_PROPINSI = '$kdPropinsi' 
                    AND CPM_KD_DATI2 = '$dati2' 
                    AND CPM_THN_DBKB_JPB5 = '$thn'
                    AND CPM_KLS_DBKB_JPB5 = '$klsJpb5' 
                    AND CPM_LANTAI_MIN_JPB5 <= $jmlLantai 
                    AND CPM_LANTAI_MAX_JPB5 >= $jmlLantai";
        $res = mysqli_query($conn, $sql);
        while ($obj = mysqli_fetch_object($res)) {
            $nilaiKomponenUtama = (float)$obj->CPM_NILAI_DBKB_JPB5;
        }

        $nilaiKomponenUtama = $nilaiKomponenUtama * $luasBng;

        $sql = "SELECT KD_FASILITAS, STATUS_FASILITAS, KETERGANTUNGAN 
                FROM cppmod_pbb_fasilitas 
                WHERE STATUS_FASILITAS IN ('0','2','3') 
                ORDER BY STATUS_FASILITAS,KD_FASILITAS,KETERGANTUNGAN ";
        $resFasilitas = mysqli_query($conn, $sql);

        while($objFas = mysqli_fetch_object($resFasilitas)) {
            $jmlSatuan = 0;
            $nilaiSatuan = 0;

            if($objFas->KD_FASILITAS == "07" && $kdJpb == "05"){
                $jmlSatuan = (float)$data->CPM_FOP_AC_CENTRAL;
                $jmlSatuan = ($jmlSatuan>1) ? 0 : $jmlSatuan;
                
            }elseif($objFas->KD_FASILITAS == "11" && !in_array($kdJpb, $selainAcCentralLain)){
                $jmlSatuan = (float)$data->CPM_FOP_AC_CENTRAL;
                $jmlSatuan = ($jmlSatuan>1) ? 0 : $jmlSatuan;

            }elseif($objFas->KD_FASILITAS == "37"){
                $jmlSatuan = (float)$data->CPM_PEMADAM_HYDRANT;
                
            }elseif($objFas->KD_FASILITAS == "38"){
                $jmlSatuan = (float)$data->CPM_PEMADAM_SPRINKLER;
                
            }elseif($objFas->KD_FASILITAS == "39"){
                $jmlSatuan = (float)$data->CPM_PEMADAM_FIRE_ALARM;
            }

            if($objFas->KETERGANTUNGAN == "0") {
                $qry = "SELECT NILAI_NON_DEP 
                        FROM cppmod_pbb_fas_non_dep 
                        WHERE 
                            KD_PROPINSI = '$kdPropinsi' 
                            AND KD_DATI2 = '$dati2' 
                            AND THN_NON_DEP = '$thn' 
                            AND KD_FASILITAS = '".$objFas->KD_FASILITAS."'";
                $res = mysqli_query($conn, $qry);
                while ($obj = mysqli_fetch_object($res)) {
                    $nilaiSatuan = (float)$obj->NILAI_NON_DEP;
                }
                
            }elseif($objFas->KETERGANTUNGAN == "1") {
                $qry = "SELECT NILAI_DEP_MIN_MAX 
                        FROM cppmod_pbb_fas_dep_min_max 
                        WHERE 
                            KD_PROPINSI = '$kdPropinsi' 
                            AND KD_DATI2 = '$dati2' 
                            AND THN_DEP_MIN_MAX = '$thn' 
                            AND KLS_DEP_MIN <= $jmlSatuan 
                            AND KLS_DEP_MAX >= $jmlSatuan 
                            AND KD_FASILITAS = '".$objFas->KD_FASILITAS."'";
                $res = mysqli_query($conn, $qry);
                while ($obj = mysqli_fetch_object($res)) {
                    $nilaiSatuan = (float)$obj->NILAI_DEP_MIN_MAX;
                }
                
            } elseif ($objFas->KETERGANTUNGAN == "2") {
                $qry = "SELECT NILAI_FASILITAS_KLS_BINTANG 
                        FROM cppmod_pbb_fas_dep_jpb_kls_bintang 
                        WHERE 
                            KD_PROPINSI = '$kdPropinsi' 
                            AND KD_DATI2 = '$dati2' 
                            AND THN_DEP_JPB_KLS_BINTANG = '$thn' 
                            AND KD_JPB = '05' 
                            AND KLS_BINTANG = '$klsJpb5' 
                            AND KD_FASILITAS = '".$objFas->KD_FASILITAS."'";
                $res = mysqli_query($conn, $qry);
                while ($obj = mysqli_fetch_object($res)) {
                    $nilaiSatuan = (float)$obj->NILAI_FASILITAS_KLS_BINTANG;
                }
            }

            if ($objFas->STATUS_FASILITAS == "0") {
                $nilaiFasilitas += ($nilaiSatuan * $jmlSatuan * $luasBng);
                continue;
            }
            if ($objFas->STATUS_FASILITAS == "2") {
                $nilaiFasilitas += ($nilaiSatuan * $luasKmrJpb5);
                continue;
            }
            if ($objFas->STATUS_FASILITAS != "3") {
                continue;
            }
            $nilaiFasilitas += ($nilaiSatuan*$luasRngLain);
        }

        $nilaiFasilitasSusut = FasilitasSusut::get($nop, $thn, $data, $jmlLantai, $conn);

        $nilaiJpb5 = $nilaiKomponenUtama + $nilaiFasilitas + $nilaiFasilitasSusut;

        $besarSusut = Susut::get($thn, $thnDibangun, $thnDirenovasi, $kondisiBng, $nilaiJpb5, $luasBng, $flagStandard="0", $conn);
        if($besarSusut>0) {
            $nilaiSetelahSusut = $nilaiJpb5 - ($nilaiJpb5 * ($besarSusut/100));
        }else{
            $nilaiSetelahSusut = $nilaiJpb5;
        }
        
        $nilaiFasilitasTdkSusut = FasilitasTidakSusut::get($nop, $thn, $data, $conn);

        return $nilaiSetelahSusut + $nilaiFasilitasTdkSusut;
    }
}