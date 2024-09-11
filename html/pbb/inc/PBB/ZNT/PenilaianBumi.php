<?php class PenilaianBumi {

    public static function get($nop, $znt, $luas, $thn, $conn, $table) {
        $nilai = 0;
        $listLastCode = "78";

        if(is_numeric($znt)){
            $nilai = self::getNilaiDariKelasBumi($znt, $conn);
        }else{
            if(strpos($listLastCode, substr($nop, 17, 1)) !== false){
                $nilai = self::getNilaiDariKelasBumi($znt, $conn);
                if($nilai==0) $nilai = self::getnilaiDariZNT($nop, $znt, $thn, $conn); 
            }else{
                $nilai = self::getnilaiDariZNT($nop, $znt, $thn, $conn);
            } 
        }

        $nilai = $nilai * 1000;

        $arrTableAllow = array( 'cppmod_pbb_sppt',
                                'cppmod_pbb_sppt_final', 
                                'cppmod_pbb_sppt_susulan', 
                                'cppmod_pbb_sppt_mundur',
                                'cppmod_pbb_service_merge_sppt',
                                'cppmod_pbb_service_change');
        if(in_array($table, $arrTableAllow)){
            $sql = "SELECT * FROM $table WHERE CPM_NOP='$nop'";
            $res = mysqli_query($conn, $sql);
            $n = mysqli_num_rows($res);
            $njop = $luas * $nilai;
            if($n>0) mysqli_query($conn, "UPDATE $table SET CPM_OT_PENILAIAN_TANAH='sistem', CPM_OT_PAYMENT_SISTEM='$nilai', CPM_OT_PAYMENT_INDIVIDU=NULL, CPM_NJOP_TANAH='$njop' WHERE CPM_NOP = '$nop'");
        }        
        return $nilai;
    }

    private static function getNilaiDariKelasBumi($znt, $conn) {
        $njopm2 = 0;
        $znt = sprintf("%03d", (int)$znt);
        $sql = "SELECT CPM_NJOP_M2 FROM cppmod_pbb_kelas_bumi WHERE CPM_KELAS='$znt' AND CPM_THN_AWAL>='2011' AND CPM_THN_AKHIR<='9999'";
        $res = mysqli_query($conn, $sql);
        while ($obj = mysqli_fetch_object($res)) {
            $njopm2 = $obj->CPM_NJOP_M2;
        }
        return $njopm2;
    }
        
    private static function getnilaiDariZNT($nop, $znt, $thn, $conn) {
        $codeKel = substr($nop, 0, 10);
        $nir = 0;
        $sql = "SELECT CPM_NIR FROM cppmod_pbb_znt WHERE CPM_KODE_LOKASI='$codeKel' AND CPM_KODE_ZNT='$znt' AND CPM_TAHUN='$thn'";
        $res = mysqli_query($conn, $sql);
        while ($obj = mysqli_fetch_object($res)) {
            $nir = $obj->CPM_NIR;
        }
        return $nir;
    }
}