<?php class JPB8 {
    
    public static function get($nop, $thn, $data, $conn) {
        $s = [];
        $kdPropinsi = substr($nop, 0, 2);
        $dati2      = substr($nop, 2, 2);
        $klsBintang = "0";
        $nilaiKomponenUtama = 0;
        $nilaiAtap = 1;
        $nilaiDinding = 0;
        $nilaiLantai = 0;
        $nilaiLangit = 0;
        $typeKonstruksi = 0;
        $nilaiDayaDukung = 0;
        $nilaiMezzanine = 0;

        $tinggiKolom    = (float)$data->CPM_JPB8_TINGGI_KOLOM;
        $lebarBentang   = (float)$data->CPM_JPB8_LEBAR_BENTANG;
        $kelilingDinding= (float)$data->CPM_JPB8_KELILING_DINDING;
        $luasMezzanine  = (float)$data->CPM_JPB8_LUAS_MEZZANINE;
        $dayaDukung     = (float)$data->CPM_JPB8_DAYA_DUKUNG_LANTAI;

        $jnsAtap    = sprintf("%02d", (int)$data->CPM_OP_ATAP);
        $kdLantai   = sprintf("%02d", (int)$data->CPM_OP_LANTAI);
        $kdLangit   = sprintf("%02d", (int)$data->CPM_OP_LANGIT);
        
        $luasBng        = $data->CPM_OP_LUAS_BANGUNAN;
        $thnDibangun    = (int)$data->CPM_OP_THN_DIBANGUN;
        $thnDirenovasi  = (int)$data->CPM_OP_THN_RENOVASI;
        $kondisiBng     = $data->CPM_OP_KONDISI;
        $kdDinding      = (int)$data->CPM_OP_DINDING;
        $jmlLantai      = (int)$data->CPM_OP_JML_LANTAI;
        $jmlLantai      = ($jmlLantai == 0) ? 1 : $jmlLantai;

        if($kdDinding == 0) {
            $kdDinding = 10;
        }elseif($kdDinding == 4) {
            $kdDinding = 7;
        }elseif($kdDinding == 5) {
            $kdDinding = 8;
        }elseif($kdDinding == 6) {
            $kdDinding = false;
        }

        $sql = "SELECT CPM_TYPE_KONSTRUKSI 
                FROM cppmod_pbb_daya_dukung 
                WHERE 
                    CPM_DAYA_DUKUNG_LANTAI_MIN_DBKB <= '$dayaDukung'
                    AND CPM_DAYA_DUKUNG_LANTAI_MAX_DBKB >= '$dayaDukung'";
        $res = mysqli_query($conn, $sql);
        while ($obj = mysqli_fetch_object($res)) {
            $typeKonstruksi = (int)$obj->CPM_TYPE_KONSTRUKSI;
        }

        $sql = "SELECT CPM_NILAI_DBKB_JPB8 
                FROM cppmod_pbb_dbkb_jpb8 
                WHERE 
                    CPM_KD_PROPINSI = '$kdPropinsi'
                    AND CPM_KD_DATI2 = '$dati2' 
                    AND CPM_THN_DBKB_JPB8 = '$thn' 
                    AND CPM_LBR_BENT_MIN_DBKB_JPB8 <= = $lebarBentang 
                    AND CPM_LBR_BENT_MAX_DBKB_JPB8 >= $lebarBentang 
                    AND CPM_TINGGI_KOLOM_MIN_DBKB_JPB8 <= $tinggiKolom 
                    AND CPM_TINGGI_KOLOM_MAX_DBKB_JPB8 >= $tinggiKolom";
        $res = mysqli_query($conn, $sql);
        while ($obj = mysqli_fetch_object($res)) {
            $nilaiKomponenUtama = (float)$obj->CPM_NILAI_DBKB_JPB8;
        }

        if($kdDinding){
            $kdDinding = sprintf("%02d", $kdDinding);
    
            $s[] = "SELECT CPM_KD_PEKERJAAN, CPM_NILAI_DBKB_MATERIAL 
                    FROM cppmod_pbb_dbkb_material 
                    WHERE 
                        CPM_THN_DBKB_MATERIAL = '$thn' 
                        AND CPM_KD_PEKERJAAN = '21' 
                        AND CPM_KD_KEGIATAN = '$kdDinding' 
                        AND CPM_KD_DATI2 = '$dati2' 
                        AND CPM_KD_PROPINSI = '$kdPropinsi'";
        }

        $s[] = "SELECT CPM_KD_PEKERJAAN, CPM_NILAI_DBKB_MATERIAL 
                FROM cppmod_pbb_dbkb_material 
                WHERE 
                    CPM_KD_PROPINSI = '$kdPropinsi' 
                    AND CPM_KD_DATI2 = '$dati2' 
                    AND CPM_THN_DBKB_MATERIAL = '$thn' 
                    AND CPM_KD_PEKERJAAN = '22' 
                    AND CPM_KD_KEGIATAN = '$kdLantai'";

        $s[] = "SELECT CPM_KD_PEKERJAAN, CPM_NILAI_DBKB_MATERIAL 
                FROM cppmod_pbb_dbkb_material 
                WHERE 
                    CPM_KD_PROPINSI = '$kdPropinsi' 
                    AND CPM_KD_DATI2 = '$dati2' 
                    AND CPM_THN_DBKB_MATERIAL = '$thn' 
                    AND CPM_KD_PEKERJAAN = '23' 
                    AND CPM_KD_KEGIATAN = '$jnsAtap'";
        
        $s[] = "SELECT CPM_KD_PEKERJAAN, CPM_NILAI_DBKB_MATERIAL 
                FROM cppmod_pbb_dbkb_material 
                WHERE 
                    CPM_KD_PROPINSI = '$kdPropinsi' 
                    AND CPM_KD_DATI2 = '$dati2' 
                    AND CPM_THN_DBKB_MATERIAL = '$thn' 
                    AND CPM_KD_PEKERJAAN = '24' 
                    AND CPM_KD_KEGIATAN = '$kdLangit'";

        if(count($s)>0){
            $sql = implode(' UNION ALL ', $s);
            $sql = "SELECT * FROM ($sql) DBKB;";
            $res = mysqli_query($conn, $sql);
            while($obj = mysqli_fetch_object($res)){
                if($obj->CPM_KD_PEKERJAAN=='21'){
                    $nilaiDinding = (float)$obj->CPM_NILAI_DBKB_MATERIAL;

                }elseif($obj->CPM_KD_PEKERJAAN=='22'){
                    $nilaiLantai = (float)$obj->CPM_NILAI_DBKB_MATERIAL;

                }elseif($obj->CPM_KD_PEKERJAAN=='23'){
                    $nilaiAtap = (float)$obj->CPM_NILAI_DBKB_MATERIAL;

                }elseif($obj->CPM_KD_PEKERJAAN=='24'){
                    $nilaiLangit = (float)$obj->CPM_NILAI_DBKB_MATERIAL;
                }
            }
        }

        $totalNilaiDinding = $nilaiDinding * $kelilingDinding * $tinggiKolom * 1.666667;

        $nilaiAtap = $nilaiAtap/$jmlLantai;
        
        $nilaiMaterial = $nilaiAtap + $nilaiLantai + $nilaiLangit;

        $sql = "SELECT CPM_NILAI_DBKB_DAYA_DUKUNG 
                FROM cppmod_pbb_daya_dukung 
                WHERE 
                    CPM_KD_PROPINSI = '$kdPropinsi' 
                    AND CPM_KD_DATI2 = '$dati2' 
                    AND CPM_THN_DBKB_DAYA_DUKUNG = '$thn' 
                    AND CPM_TYPE_KONSTRUKSI = '$typeKonstruksi'";
        $res = mysqli_query($conn, $sql);
        while ($obj = mysqli_fetch_object($res)) {
            $nilaiDayaDukung = (float)$obj->CPM_NILAI_DBKB_DAYA_DUKUNG;
        }

        $nilaiFasilitas = FasilitasSusutLuas::get($nop, $thn, $data, $conn, $kdJpb="08", $klsBintang);
        
        $nilaiTemp = $nilaiKomponenUtama + $nilaiMaterial + $nilaiDayaDukung + $nilaiFasilitas;
        
        $nilaiTotalKaliLuas = $nilaiTemp * $luasBng;
        
        $sql = "SELECT CPM_NILAI_DBKB_MEZANIN 
                FROM cppmod_pbb_dbkb_mezanin 
                WHERE 
                    CPM_KD_PROPINSI = '$kdPropinsi' 
                    AND CPM_KD_DATI2 = '$dati2'
                    AND CPM_THN_DBKB_MEZANIN = '$thn'";
        $res = mysqli_query($conn, $sql);
        while ($obj = mysqli_fetch_object($res)) {
            $nilaiMezzanine = (float)$obj->CPM_NILAI_DBKB_MEZANIN;
        }
        
        $nilaiTotalMezzanine = $nilaiMezzanine * $luasMezzanine;

        $nilaiFasilitasSusut = FasilitasSusut::get($nop, $thn, $data, $jmlLantai, $conn);

        $nilaiSebelumSusut = $nilaiTotalKaliLuas + $totalNilaiDinding + $nilaiTotalMezzanine + $nilaiFasilitasSusut;

        $besarSusut = Susut::get($thn, $thnDibangun, $thnDirenovasi, $kondisiBng, $nilaiSebelumSusut, $luasBng, $flag="0", $conn);

        if($besarSusut>0) {
            $nilaiSetelahSusut = $nilaiSebelumSusut - ($nilaiSebelumSusut * ($besarSusut/100));
        }else{
            $nilaiSetelahSusut = $nilaiSebelumSusut;
        }
        $nilaiFasilitasTdkSusut = FasilitasTidakSusut::get($nop, $thn, $data, $conn);

        return $nilaiSetelahSusut + $nilaiFasilitasTdkSusut;
    }
}