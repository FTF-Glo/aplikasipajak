<?php class JPB16 {
    public static function get($nop, $thn, $data, $conn) {
        $kdPropinsi = substr($nop, 0, 2);
        $dati2 = substr($nop, 2, 2);
        $nilaiKomponenUtama = 0;
        $klsBintang = "0";

        $luasBng        = (float)$data->CPM_OP_LUAS_BANGUNAN;
        $thnDibangun    = (int)$data->CPM_OP_THN_DIBANGUN;
        $thnDirenovasi  = (int)$data->CPM_OP_THN_RENOVASI;
        $kondisiBng     = (int)$data->CPM_OP_KONDISI;
        $kondisiBng     = ($kondisiBng == 0) ? 4 : $kondisiBng;
        $jmlLantai      = $data->CPM_OP_JML_LANTAI;
        $jmlLantai      = ($jmlLantai == 0) ? 1 : $jmlLantai;
        $klsJpb16      = trim($data->CPM_JPB16_KELAS_BANGUNAN);

        $sql = "SELECT CPM_NILAI_DBKB_JPB16 
                FROM cppmod_pbb_dbkb_jpb16 
                WHERE 
                    CPM_KD_PROPINSI = '$kdPropinsi' 
                    AND CPM_KD_DATI2 = '$dati2' 
                    AND CPM_THN_DBKB_JPB16 = '$thn'
                    AND CPM_KLS_DBKB_JPB16 = '$klsJpb16'
                    AND CPM_LANTAI_MIN_JPB16 <= $jmlLantai 
                    AND CPM_LANTAI_MAX_JPB16 >= $jmlLantai";
        $res = mysqli_query($conn, $sql);
        while ($obj = mysqli_fetch_object($res)) {
            $nilaiKomponenUtama = (float)$obj->CPM_NILAI_DBKB_JPB16;
        }

        $nilaifasilitasLuas = FasilitasSusutLuas::get($nop, $thn, $data, $conn, $kdJpb="16", $klsBintang);

        $nilaiTemp = ($nilaiKomponenUtama + $nilaifasilitasLuas) * $luasBng;

        $nilaifasilitasSusut = FasilitasSusut::get($nop, $thn, $data, $jmlLantai, $conn);

        $nilaiSebelumSusut = $nilaiTemp + $nilaifasilitasSusut;

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