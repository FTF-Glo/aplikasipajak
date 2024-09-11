<?php class JPB2 {

    public static function get($nop, $thn, $data, $conn) {
        $kdPropinsi = substr($nop, 0, 2);
        $dati2 = substr($nop, 2, 2);
        $klsBintang = "0";
        $nilaiKomponenUtama = 0;

        $kdJpb          = '02';
        $luasBng        = $data->CPM_OP_LUAS_BANGUNAN;
        $thnDibangun    = (int)$data->CPM_OP_THN_DIBANGUN;
        $thnDirenovasi  = (int)$data->CPM_OP_THN_RENOVASI;
        $kondisiBng     = $data->CPM_OP_KONDISI;
        $klsJpb2        = (int)$data->CPM_JPB2_KELAS_BANGUNAN;
        $noBng          = $data->CPM_OP_NUM;
        $jmlLantai      = $data->CPM_OP_JML_LANTAI;
        $jmlLantai      = ($jmlLantai == 0) ? 1 : $jmlLantai;

        $sql = "SELECT CPM_NILAI_DBKB_JPB2 
                FROM cppmod_pbb_dbkb_jpb2 
                WHERE 
                    CPM_KD_PROPINSI = '$kdPropinsi' 
                    AND CPM_KD_DATI2 = '$dati2' 
                    AND CPM_THN_DBKB_JPB2 = '$thn' 
                    AND CPM_KLS_DBKB_JPB2 = '$klsJpb2' 
                    AND CPM_LANTAI_MIN_JPB2 <= $jmlLantai 
                    AND CPM_LANTAI_MAX_JPB2 >= $jmlLantai";
        $res = mysqli_query($conn, $sql);
        while ($obj = mysqli_fetch_object($res)) {
            $nilaiKomponenUtama = (float)$obj->CPM_NILAI_DBKB_JPB2;
        }
        
        $fasilitasLuas = FasilitasSusutLuas::get($nop, $thn, $data, $conn, $kdJpb, $klsBintang);

        $fasilitasSusut = FasilitasSusut::get($nop, $thn, $data, $jmlLantai, $conn);
        
        $nilaiTemp = $nilaiKomponenUtama + $fasilitasLuas;
        
        $nilaiSebelumSusut = ($nilaiTemp * $luasBng) + $fasilitasSusut;

        $besarSusut = Susut::get($thn, $thnDibangun, $thnDirenovasi, $kondisiBng, $nilaiSebelumSusut, $luasBng, $flag='0', $conn);

        if($besarSusut>0){
            $nilaiSetelahSusut = $nilaiSebelumSusut - ($nilaiSebelumSusut * ($besarSusut/100));
        }else{
            $nilaiSetelahSusut = $nilaiSebelumSusut;
        }
        $fasilitasTdkSusut = FasilitasTidakSusut::get($nop, $thn, $data, $conn);

        return $nilaiSetelahSusut + $fasilitasTdkSusut;
    }
}