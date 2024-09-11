<?php class JPB4 {

    public static function get($nop, $thn, $data, $conn) {
        $kdPropinsi     = substr($nop, 0, 2);
        $dati2          = substr($nop, 2, 2);
        $klsBintang     = "0";
        $nilaiKomponenUtama = 0;
        $luasBng        = (float)$data->CPM_OP_LUAS_BANGUNAN;
        $thnDibangun    = (int)$data->CPM_OP_THN_DIBANGUN;
        $thnDirenovasi  = (int)$data->CPM_OP_THN_RENOVASI;
        $kondisiBng     = $data->CPM_OP_KONDISI;
        $klsJpb4        = $data->CPM_JPB4_KELAS_BANGUNAN;
        $jmlLantai      = $data->CPM_OP_JML_LANTAI;
        $jmlLantai      = ($jmlLantai == 0) ? 1 : $jmlLantai;

        $sql = "SELECT CPM_NILAI_DBKB_JPB4 
                FROM cppmod_pbb_dbkb_jpb4 
                WHERE 
                    CPM_KD_PROPINSI = '$kdPropinsi' 
                    AND CPM_KD_DATI2 = '$dati2' 
                    AND CPM_THN_DBKB_JPB4 = '$thn' 
                    AND CPM_KLS_DBKB_JPB4 = '$klsJpb4' 
                    AND CPM_LANTAI_MIN_DBKB_JPB4 <= $jmlLantai 
                    AND CPM_LANTAI_MAX_DBKB_JPB4 >= $jmlLantai";
        $res = mysqli_query($conn, $sql);
        while ($obj = mysqli_fetch_object($res)) {
            $nilaiKomponenUtama = (float)$obj->CPM_NILAI_DBKB_JPB4;
        }

        $nilaiFasilitas = FasilitasSusutLuas::get($nop, $thn, $data, $conn, $kdJpb="04", $klsBintang);

        $nilaiTemp = ($nilaiKomponenUtama + $nilaiFasilitas) * $luasBng;

        $nilaiFasilitasSusut = FasilitasSusut::get($nop, $thn, $data, $jmlLantai, $conn);

        $nilaiSebelumSusut = $nilaiTemp + $nilaiFasilitasSusut;

        $besarSusut = Susut::get($thn, $thnDibangun, $thnDirenovasi, $kondisiBng, $nilaiSebelumSusut, $luasBng, $flagStandard="1", $conn);

        if($besarSusut>0) {
            $nilaiSetelahSusut = $nilaiSebelumSusut - ($nilaiSebelumSusut * ($besarSusut/100));
        }else{
            $nilaiSetelahSusut = $nilaiSebelumSusut;
        }
        
        $nilaiFasilitasTdkSusut = FasilitasTidakSusut::get($nop, $thn, $data, $conn);

        return $nilaiSetelahSusut + $nilaiFasilitasTdkSusut;
    }
}