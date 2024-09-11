<?php class JPB6 {

    public static function get($nop, $thn, $data, $conn) {
        $kdPropinsi = substr($nop, 0, 2);
        $dati2      = substr($nop, 2, 2);
        $klsBintang = "0";
        $flagStandard = "0";
        $nilaiKomponenUtama = 0;

        $luasBng        = (float)$data->CPM_OP_LUAS_BANGUNAN;
        $thnDibangun    = (int)$data->CPM_OP_THN_DIBANGUN;
        $thnDirenovasi  = (int)$data->CPM_OP_THN_RENOVASI;
        $kondisiBng     = $data->CPM_OP_KONDISI;
        $klsJpb6        = $data->CPM_JPB6_KELAS_BANGUNAN;
        $jmlLantai      = $data->CPM_OP_JML_LANTAI;
        $jmlLantai      = ($jmlLantai == 0) ? 1 : $jmlLantai;

        $sql = "SELECT CPM_NILAI_DBKB_JPB6 
                FROM cppmod_pbb_dbkb_jpb6 
                WHERE 
                    CPM_KD_PROPINSI = '$kdPropinsi' 
                    AND CPM_KD_DATI2 = '$dati2' 
                    AND CPM_THN_DBKB_JPB6 = '$thn'
                    AND CPM_KLS_DBKB_JPB6 = '$klsJpb6'";
        $res = mysqli_query($conn, $sql);
        while ($obj = mysqli_fetch_object($res)) {
            $nilaiKomponenUtama = (float)$obj->CPM_NILAI_DBKB_JPB6;
        }

        $nilaifasilitasLuas = FasilitasSusutLuas::get($nop, $thn, $data, $conn, $kdJpb="06", $klsBintang);

        $nilaiTemp = $nilaiKomponenUtama + $nilaifasilitasLuas;
        
        $nilaiTotalKaliLuas = $nilaiTemp * $luasBng;

        $nilaifasilitasSusut = FasilitasSusut::get($nop, $thn, $data, $jmlLantai, $conn);

        $nilaiSebelumSusut = $nilaiTotalKaliLuas + $nilaifasilitasSusut;

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