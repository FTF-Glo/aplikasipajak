<?php class PenilaianStandard {
    
    public static function get($nop, $thn, $data, $conn, $table1, $table2) {
        $flag       = "1";
        $klsBintang = "0";
        $arrJpb1    = ["01", "10", "11"]; // Kelompok (1) Perumahan atau Undefinied
        $arrJpb2    = ["02", "04", "07", "09"]; // Kelompok (2) Pabrik, ruko, hotel, gedung Pertemuan

        $noBng          = $data->CPM_OP_NUM;
        $kdJpb          = $data->CPM_OP_PENGGUNAAN;
        $luasBng        = $data->CPM_OP_LUAS_BANGUNAN;
        $jmlLantai      = $data->CPM_OP_JML_LANTAI;
        $thnDibangun    = (int)$data->CPM_OP_THN_DIBANGUN;
        $thnDirenovasi  = (int)$data->CPM_OP_THN_RENOVASI;
        $kondisiBng     = (int)$data->CPM_OP_KONDISI;
        $jnsKonstruksi  = (int)$data->CPM_OP_KONSTRUKSI;

        if(in_array($kdJpb, $arrJpb1)) {
            $kdJpb = "01";
        }elseif(in_array($kdJpb, $arrJpb2)) {
            $kdJpb = "02";
        }
        
        $komponenUtama = KomponenUtamaSTD::get($nop, $thn, $kdJpb, $luasBng, $jmlLantai, $jnsKonstruksi, $conn);

        $komponenMaterial = KomponenMaterial::get($nop, $thn, $data, $conn);

        $fasilitasLuas = FasilitasSusutLuas::get($nop, $thn, $data, $conn, $kdJpb, $klsBintang);

        $fasilitasSusut = FasilitasSusut::get($nop, $thn, $data, $jmlLantai, $conn);

        $nilaiTemp =  $komponenUtama + $komponenMaterial + $fasilitasLuas;

        $nilaiSebelumSusut = ($nilaiTemp * $luasBng) + $fasilitasSusut;

        $besarSusut = Susut::get($thn, $thnDibangun, $thnDirenovasi, $kondisiBng, $nilaiSebelumSusut, $luasBng, $flag, $conn);
        
        if($besarSusut>0){
            $nilaiSetelahSusut = $nilaiSebelumSusut - ($nilaiSebelumSusut * ($besarSusut/100));
        }else{
            $nilaiSetelahSusut = $nilaiSebelumSusut;
        }
        $fasilitasTdkSusut = FasilitasTidakSusut::get($nop, $thn, $data, $conn);

        return $nilaiSetelahSusut + $fasilitasTdkSusut;
    }
}