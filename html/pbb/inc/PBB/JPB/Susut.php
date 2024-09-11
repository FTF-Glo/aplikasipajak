<?php class Susut {

    public static function get($thn, $thnDibangun, $thnRenovasi, $kondisiBng, $nilaiSebelumSusut, $luasBng, $flagStandard, $conn) {
        $biayaPenggantiBaru = 0;
        $flag = (!$flagStandard || $flagStandard == null || $flagStandard == "") ? 0 : (int)$flagStandard;
        $thnDibangun = ($thnDibangun==$thn) ? 0 : $thnDibangun;
        $thnRenovasi = ($thnRenovasi==$thn) ? 0 : $thnRenovasi;

        $kdRangePenyusutan = 1;
        $persenSusut = 0;

        if($flag == 0) {
            if($thnDibangun > 0) {
                if($thnRenovasi > 0) {
                    if($thn - $thnRenovasi < 10) {
                        $umurEfektif = ($thn - $thnDibangun + (2 * ($thn - $thnRenovasi))) / 3;
                    }else{
                        $umurEfektif = ($thn - $thnDibangun + (2*10)) / 3;
                    }
                }elseif($thn - $thnDibangun >= 10) {
                    $umurEfektif = ($thn - $thnDibangun + (2*10)) / 3;
                }else{
                    $umurEfektif = $thn - $thnDibangun;
                }
            }else{
                $umurEfektif = 0;
            }
        }else{
            $umurEfektif = ($thnRenovasi > 0) ? ($thn - $thnRenovasi) : ($thn - $thnDibangun);
        }
        
        if($umurEfektif >= 30) {
            $umurEfektif = 30;
        }

        $luasBng = ($luasBng == 0) ? 1 : $luasBng;
        $biayaPenggantiBaru = ($nilaiSebelumSusut * 1000) / $luasBng;

        $sql = "SELECT KD_RANGE_PENYUSUTAN 
                FROM cppmod_pbb_range_penyusutan 
                WHERE 
                    NILAI_MIN_PENYUSUTAN < $biayaPenggantiBaru 
                    AND NILAI_MAX_PENYUSUTAN >= $biayaPenggantiBaru";
        $res = mysqli_query($conn, $sql);
        while ($obj = mysqli_fetch_object($res)) {
            $kdRangePenyusutan = (int)$obj->KD_RANGE_PENYUSUTAN;
        }
        
        
        $umurEfektif = (int)round($umurEfektif,0);
        $sql = "SELECT NILAI_PENYUSUTAN 
                FROM cppmod_pbb_penyusutan 
                WHERE 
                    UMUR_EFEKTIF = '$umurEfektif' 
                    AND KD_RANGE_PENYUSUTAN = '$kdRangePenyusutan' 
                    AND KONDISI_BNG_SUSUT = '$kondisiBng'";
        $res = mysqli_query($conn, $sql);
        while ($obj = mysqli_fetch_object($res)) {
            $persenSusut = (float)$obj->NILAI_PENYUSUTAN;
        }
        return $persenSusut;
    }
}