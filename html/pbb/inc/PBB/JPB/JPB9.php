<?php class JPB9 {

    public static function get($nop, $thn, $data, $conn) {
        $kdPropinsi = substr($nop, 0, 2);
        $dati2      = substr($nop, 2, 2);
        $klsBintang = "0";
        $nilaiKomponenUtama = 0;

        $luasBng        = $data->CPM_OP_LUAS_BANGUNAN;
        $thnDibangun    = (int)$data->CPM_OP_THN_DIBANGUN;
        $thnDirenovasi  = (int)$data->CPM_OP_THN_RENOVASI;
        $kondisiBng     = $data->CPM_OP_KONDISI;
        $klsJpb9        = $data->CPM_JPB9_KELAS_BANGUNAN;
        $jmlLantai      = $data->CPM_OP_JML_LANTAI;
        $jmlLantai      = ($jmlLantai == 0) ? 1 : $jmlLantai;
        
        $sql = "SELECT CPM_NILAI_DBKB_JPB9 
                FROM cppmod_pbb_dbkb_jpb9 
                WHERE 
                    CPM_KD_PROPINSI = '$kdPropinsi' 
                    AND CPM_KD_DATI2 = '$dati2' 
                    AND CPM_THN_DBKB_JPB9 = '$thn' 
                    AND CPM_KLS_DBKB_JPB9 = '$klsJpb9' 
                    AND CPM_LANTAI_MIN_JPB9 <= $jmlLantai 
                    AND CPM_LANTAI_MAX_JPB9 >= $jmlLantai";
        $res = mysqli_query($conn, $sql);
        while ($obj = mysqli_fetch_object($res)) {
            $nilaiKomponenUtama = (float)$obj->CPM_NILAI_DBKB_JPB9;
        }
        
        $nilaifasilitasLuas = FasilitasSusutLuas::get($nop, $thn, $data, $conn, $kdJpb="09", $klsBintang);

        $nilaiTemp = ($nilaiKomponenUtama + $nilaifasilitasLuas) * $luasBng;

        $nilaifasilitasLuasSusut = FasilitasSusut::get($nop, $thn, $data, $jmlLantai, $conn);

        $nilaiSebelumSusut = $nilaiTemp + $nilaifasilitasLuasSusut;

        $besarSusut = Susut::get($thn, $thnDibangun, $thnDirenovasi, $kondisiBng, $nilaiSebelumSusut, $luasBng, $flagStandard='0', $conn);

        if($besarSusut>0){
            $nilaiSetelahSusut = $nilaiSebelumSusut - ($nilaiSebelumSusut * ($besarSusut/100));
        }else{
            $nilaiSetelahSusut = $nilaiSebelumSusut;
        }

        $nilaiFasilitasTdkSusut = FasilitasTidakSusut::get($nop, $thn, $data, $conn);

        return $nilaiSetelahSusut + $nilaiFasilitasTdkSusut;
    }
}