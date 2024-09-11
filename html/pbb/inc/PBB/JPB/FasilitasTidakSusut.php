<?php class FasilitasTidakSusut {

    public static function get($nop, $thn, $data, $conn) {
        $kdPropinsi = substr($nop, 0, 2);
        $dati2 = substr($nop, 2, 2);
        $nilatFasilitas = 0;

        $resFasilitas = mysqli_query($conn, "SELECT * FROM cppmod_pbb_fasilitas WHERE STATUS_FASILITAS='5'");
        while($objFas = mysqli_fetch_object($resFasilitas)) {
            $nilaiSatuan = 0;
            $jmlSatuan = 0;

            if($objFas->KD_FASILITAS == "01"){
                $jmlSatuan = (float)$data->CPM_FOP_AC_SPLIT;
                
            }elseif($objFas->KD_FASILITAS == "02"){
                $jmlSatuan = (float)$data->CPM_FOP_AC_WINDOW;
                
            }elseif($objFas->KD_FASILITAS == "44"){
                $jmlSatuan = (float)$data->CPM_OP_DAYA;
            }
            
            $sql = "SELECT NILAI_NON_DEP
                    FROM CPPMOD_PBB_FAS_NON_DEP 
                    WHERE 
                        KD_PROPINSI = '$kdPropinsi' 
                        AND KD_DATI2 = '$dati2' 
                        AND THN_NON_DEP = '$thn' 
                        AND KD_FASILITAS = '".$objFas->KD_FASILITAS."'";
            $res = mysqli_query($conn, $sql);
            while ($obj = mysqli_fetch_object($res)) {
                $nilaiSatuan = (float)$obj->NILAI_NON_DEP;
            }

            if($objFas->KD_FASILITAS=="44") {
                $nilatFasilitas += (($jmlSatuan * $nilaiSatuan) / 1000);
            }else{
                $nilatFasilitas += ($jmlSatuan * $nilaiSatuan);
            }
        }
        return $nilatFasilitas;
    }
}