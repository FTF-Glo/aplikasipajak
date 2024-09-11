<?php class JPB15 {

    public static function get($nop, $thn, $data, $conn) {
        $kdPropinsi = substr($nop, 0, 2);
        $dati2 = substr($nop, 2, 2);
        $nilaiKomponenUtama = 0;

        $thnDibangun    = (int)$data->CPM_OP_THN_DIBANGUN;
        $thnDirenovasi  = (int)$data->CPM_OP_THN_RENOVASI;
        $kondisiBng     = $data->CPM_OP_KONDISI;
        $letakTangki    = $data->CPM_JPB15_TANGKI_MINYAK_LETAK;
        $kapasitasTangki= $data->CPM_JPB15_TANGKI_MINYAK_KAPASITAS;

        $sql = "SELECT CPM_NILAI_DBKB_JPB15 
                FROM cppmod_pbb_dbkb_jpb15 
                WHERE 
                    CPM_KD_PROPINSI = '$kdPropinsi' 
                    AND CPM_KD_DATI2 = '$dati2' 
                    AND CPM_THN_DBKB_JPB15 = '$thn'
                    AND CPM_JNS_TANGKI_DBKB_JPB15 = '$letakTangki'
                    AND CPM_KAPASITAS_MIN_DBKB_JPB15 <= $kapasitasTangki 
                    AND CPM_KAPASITAS_MAX_DBKB_JPB15 >= $kapasitasTangki";
        $res = mysqli_query($conn, $sql);
        while ($obj = mysqli_fetch_object($res)) {
            $nilaiKomponenUtama = (float)$obj->CPM_NILAI_DBKB_JPB15;
        }

        $umurEfektif = 0;
        if($thnDibangun > 0){
            if($thnDirenovasi > 0){
                if($thn - $thnDirenovasi > 10){
                    $umurEfektif = round(($thn - $thnDibangun + 20) / 3);
                }else{
                    if($thn - $thnDirenovasi <= 10){
                        $umurEfektif = round(($thn - $thnDibangun + (2 * ($thn - $thnDirenovasi))) / 3);
                    }else{
                        $umurEfektif = 0;
                    }
                }
            }else{
                if($thn - $thnDibangun > 10){
                    $umurEfektif = round(($thn - $thnDibangun + 20) / 3);
                }else{
                    if(($thn - $thnDibangun <= 10)){
                        $umurEfektif = $thn - $thnDibangun;
                    }else{
                        $umurEfektif = 0;
                    }
                }
            }
        }
        
        $umurEfektif = ($umurEfektif > 30) ? 30 : $umurEfektif;

        $besarSusut = $umurEfektif * 5;
        if($besarSusut >= 0){
            $besarSusut = ($besarSusut > 50) ? 50 : $besarSusut;
            $njopJpb15 = $nilaiKomponenUtama - ($nilaiKomponenUtama($besarSusut/100));
        }else{
            $njopJpb15 = $nilaiKomponenUtama;
        }
        return $njopJpb15;
    }
}