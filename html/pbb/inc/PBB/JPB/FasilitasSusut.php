<?php class FasilitasSusut {

    public static function get($nop, $thn, $data, $jmlLantai, $conn) {
        $kdPropinsi = substr($nop, 0, 2);
        $dati2 = substr($nop, 2, 2);
        $nilatTotal = 0;

        $resFasilitas = mysqli_query($conn, "SELECT * FROM cppmod_pbb_fasilitas WHERE STATUS_FASILITAS='4'");

        while($objFas = mysqli_fetch_object($resFasilitas)) {
            $jmlSatuan = 0;
            $nilaiSatuan = 0;

            if($objFas->KD_FASILITAS == "12") {
                if($data->CPM_FOP_KOLAM_LAPISAN == "Diplester" && (float)$data->CPM_FOP_KOLAM_LUAS>0){
                    $jmlSatuan = (float)$data->CPM_FOP_KOLAM_LUAS;
                }
            }elseif($objFas->KD_FASILITAS == "13") {
                if($data->CPM_FOP_KOLAM_LAPISAN == "Dengan pelapis" && (float)$data->CPM_FOP_KOLAM_LUAS>0){
                    $jmlSatuan = (float)$data->CPM_FOP_KOLAM_LUAS;
                }
            }elseif($objFas->KD_FASILITAS == "14"){
                $jmlSatuan = (float)$data->CPM_FOP_PERKERASAN_RINGAN;

            }elseif($objFas->KD_FASILITAS == "15"){
                $jmlSatuan = (float)$data->CPM_FOP_PERKERASAN_SEDANG;

            }elseif($objFas->KD_FASILITAS == "16"){
                $jmlSatuan = (float)$data->CPM_FOP_PERKERASAN_BERAT;

            }elseif($objFas->KD_FASILITAS == "17"){
                $jmlSatuan = (float)$data->CPM_FOP_PERKERASAN_PENUTUP;
                
            }elseif($objFas->KD_FASILITAS == "18"){
                $jmlSatuan = ($data->CPM_FOP_TENIS_LAMPU_BETON != "1") ? 0 : (float)$data->CPM_FOP_TENIS_LAMPU_BETON;
                
            }elseif($objFas->KD_FASILITAS == "19"){
                $jmlSatuan = ($data->CPM_FOP_TENIS_LAMPU_ASPAL != "1") ? 0 : (float)$data->CPM_FOP_TENIS_LAMPU_ASPAL;
                
            }elseif($objFas->KD_FASILITAS == "20"){
                $jmlSatuan = ($data->CPM_FOP_TENIS_LAMPU_TANAH != "1") ? 0 : (float)$data->CPM_FOP_TENIS_LAMPU_TANAH;
                
            }elseif($objFas->KD_FASILITAS == "21"){
                $jmlSatuan = ($data->CPM_FOP_TENIS_TANPA_LAMPU_BETON != "1") ? 0 : (float)$data->CPM_FOP_TENIS_TANPA_LAMPU_BETON;
                
            }elseif($objFas->KD_FASILITAS == "22"){
                $jmlSatuan = ($data->CPM_FOP_TENIS_TANPA_LAMPU_ASPAL != "1") ? 0 : (float)$data->CPM_FOP_TENIS_TANPA_LAMPU_ASPAL;
                
            }elseif($objFas->KD_FASILITAS == "23"){
                $jmlSatuan = ($data->CPM_FOP_TENIS_TANPA_LAMPU_TANAH != "1") ? 0 : (float)$data->CPM_FOP_TENIS_TANPA_LAMPU_TANAH;
                
            }elseif($objFas->KD_FASILITAS == "24"){
                $jmlSatuan = ($data->CPM_FOP_TENIS_LAMPU_BETON == "1") ? 0 : (float)$data->CPM_FOP_TENIS_LAMPU_BETON;
                
            }elseif($objFas->KD_FASILITAS == "25"){
                $jmlSatuan = ($data->CPM_FOP_TENIS_LAMPU_ASPAL == "1") ? 0 : (float)$data->CPM_FOP_TENIS_LAMPU_ASPAL;
                
            }elseif($objFas->KD_FASILITAS == "26"){
                $jmlSatuan = ($data->CPM_FOP_TENIS_LAMPU_TANAH == "1") ? 0 : (float)$data->CPM_FOP_TENIS_LAMPU_TANAH;
                
            }elseif($objFas->KD_FASILITAS == "27"){
                $jmlSatuan = ($data->CPM_FOP_TENIS_TANPA_LAMPU_BETON == "1") ? 0 : (float)$data->CPM_FOP_TENIS_TANPA_LAMPU_BETON;
                
            }elseif($objFas->KD_FASILITAS == "28"){
                $jmlSatuan = ($data->CPM_FOP_TENIS_TANPA_LAMPU_ASPAL == "1") ? 0 : (float)$data->CPM_FOP_TENIS_TANPA_LAMPU_ASPAL;
                
            }elseif($objFas->KD_FASILITAS == "29"){
                $jmlSatuan = ($data->CPM_FOP_TENIS_TANPA_LAMPU_TANAH == "1") ? 0 : (float)$data->CPM_FOP_TENIS_TANPA_LAMPU_TANAH;
                
            }elseif($objFas->KD_FASILITAS == "30"){
                $jmlSatuan = (float)$data->CPM_FOP_LIFT_PENUMPANG;
                
            }elseif($objFas->KD_FASILITAS == "31"){
                $jmlSatuan = (float)$data->CPM_FOP_LIFT_KAPSUL;
                
            }elseif($objFas->KD_FASILITAS == "32"){
                $jmlSatuan = (float)$data->CPM_FOP_LIFT_BARANG;
                
            }elseif($objFas->KD_FASILITAS == "33"){
                $jmlSatuan = (float)$data->CPM_FOP_ESKALATOR_SEMPIT;

            }elseif($objFas->KD_FASILITAS == "34"){
                $jmlSatuan = (float)$data->CPM_FOP_ESKALATOR_LEBAR;
                
            }elseif($objFas->KD_FASILITAS == "35"){
                $jmlSatuan = (float)$data->CPM_PAGAR_BESI_PANJANG;
                
            }elseif($objFas->KD_FASILITAS == "36"){
                $jmlSatuan = (float)$data->CPM_PAGAR_BATA_PANJANG;
                
            }elseif($objFas->KD_FASILITAS == "41"){
                $jmlSatuan = (float)$data->CPM_FOP_SALURAN;
                
            }elseif($objFas->KD_FASILITAS == "42"){
                $jmlSatuan = (float)$data->CPM_FOP_SUMUR;
            }

            if($objFas->KETERGANTUNGAN == "0") {
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
                if($objFas->KD_FASILITAS == "30" || $objFas->KD_FASILITAS == "31" || $objFas->KD_FASILITAS == "32") {
                    $qNilaiSatuan ="SELECT NILAI_DEP_MIN_MAX 
                                    FROM cppmod_pbb_fas_dep_min_max 
                                    WHERE 
                                        KLS_DEP_MIN <= $jmlLantai 
                                        AND KLS_DEP_MAX >= $jmlLantai 
                                        AND KD_PROPINSI = '$kdPropinsi' 
                                        AND KD_DATI2 = '$dati2' 
                                        AND THN_DEP_MIN_MAX = '$thn' 
                                        AND KD_FASILITAS = '" . $objFas->KD_FASILITAS . "'";
                    $res = mysqli_query($conn, $qNilaiSatuan);
                    while ($obj = mysqli_fetch_object($res)) {
                        $nilaiSatuan = (float)$obj->NILAI_DEP_MIN_MAX;
                    }
                }else{
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
                }
            }else{
                $nilaiSatuan = 0;
            }
            $nilatTotal += ($jmlSatuan * $nilaiSatuan);
        }

        return $nilatTotal;
    }
}