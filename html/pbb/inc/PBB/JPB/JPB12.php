<?php class JPB12 {

    public static function get($nop, $thn, $data, $conn) {
        $kdPropinsi = substr($nop, 0, 2);
        $dati2      = substr($nop, 2, 2);
        $klsBintang = "0";
        $flagStandard = "1";
        $nilaiKomponenUtama = 0;

        $luasBng        = (float)$data->CPM_OP_LUAS_BANGUNAN;
        $thnDibangun    = (int)$data->CPM_OP_THN_DIBANGUN;
        $thnDirenovasi  = (int)$data->CPM_OP_THN_RENOVASI;
        $kondisiBng     = $data->CPM_OP_KONDISI;
        $tipeJpb12      = trim($data->CPM_JPB12_TIPE_BANGUNAN);
        $jmlLantai      = $data->CPM_OP_JML_LANTAI;
        $jmlLantai      = ($jmlLantai == 0) ? 1 : $jmlLantai;
        
        $sql = "SELECT CPM_NILAI_DBKB_JPB12 
                FROM cppmod_pbb_dbkb_jpb12 
                WHERE 
                    CPM_KD_PROPINSI = '$kdPropinsi' 
                    AND CPM_KD_DATI2 = '$dati2' 
                    AND CPM_THN_DBKB_JPB12 = '$thn'
                    AND CPM_TYPE_DBKB_JPB12 = '$tipeJpb12'";
        $res = mysqli_query($conn, $sql);
        while ($obj = mysqli_fetch_object($res)) {
            $nilaiKomponenUtama = (float)$obj->CPM_NILAI_DBKB_JPB12;
        }
        
        $nilaifasilitasLuas = FasilitasSusutLuas::get($nop, $thn, $data, $conn, $kdJpb="12", $tipeJpb12);
        
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