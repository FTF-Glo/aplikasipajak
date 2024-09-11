<?php class KomponenUtamaSTD {

    public static function get($nop, $thn, $kdJpb, $luasBng, $jmlLantai, $jnsKonstruksi, $conn) {
        $sttsKayuUlin   = 0;
        $tipeBng        = 0;
        $kdBngLantai    = 0;
        $nilaiDbkb      = 0;
        $cKdJpb         = ["04", "09"]; // Ruko atau gedung Pertemuan
        $kdJpb          = in_array($kdJpb, $cKdJpb) ? "02" : $kdJpb;
        $kdPropinsi     = substr($nop, 0, 2);
        $dati2          = substr($nop, 2, 2);

        $res = mysqli_query($conn, "SELECT STATUS_KAYU_ULIN FROM cppmod_pbb_kayu_ulin WHERE THN_STATUS_KAYU_ULIN = '$thn'");
        while ($obj = mysqli_fetch_object($res)) {
            $sttsKayuUlin = $obj->STATUS_KAYU_ULIN;
        }
        
        $sql = "SELECT a.CPM_TIPE_BNG, a.CPM_KD_BNG_LANTAI 
                FROM cppmod_pbb_bangunan_lantai a, cppmod_pbb_tipe_bangunan b
                WHERE 
                    a.CPM_KD_JPB = '$kdJpb' 
                    AND a.CPM_LANTAI_MIN_BNG_LANTAI <= '$jmlLantai' 
                    AND a.CPM_LANTAI_MAX_BNG_LANTAI >= '$jmlLantai' 
                    AND b.CPM_LUAS_MIN_TIPE_BNG <= '$luasBng' 
                    AND b.CPM_LUAS_MAX_TIPE_BNG >= '$luasBng' 
                    AND b.CPM_TIPE_BNG = a.CPM_TIPE_BNG";
        $res = mysqli_query($conn, $sql);
        while ($obj = mysqli_fetch_object($res)) {
            $tipeBng    = $obj->CPM_TIPE_BNG;
            $kdBngLantai= $obj->CPM_KD_BNG_LANTAI;
        }
        
        $sql = "SELECT CPM_NILAI_DBKB_STANDARD 
                FROM cppmod_pbb_dbkb_standard 
                WHERE 
                    CPM_THN_DBKB_STANDARD = '$thn' 
                    AND CPM_KD_JPB = '$kdJpb' 
                    AND CPM_TIPE_BNG = '$tipeBng' 
                    AND CPM_KD_BNG_LANTAI = '$kdBngLantai' 
                    AND CPM_KD_DATI2 = '$dati2' 
                    AND CPM_KD_PROPINSI = '$kdPropinsi'";
        $res = mysqli_query($conn, $sql);
        while ($obj = mysqli_fetch_object($res)) {
            $nilaiDbkb = (float)$obj->CPM_NILAI_DBKB_STANDARD;
        }
        return ($jnsKonstruksi == "4" && $sttsKayuUlin == "0") ? ($nilaiDbkb * 0.7) : $nilaiDbkb;
    }
}