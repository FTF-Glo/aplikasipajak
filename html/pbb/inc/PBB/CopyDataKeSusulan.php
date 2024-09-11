<?php class CopyDataKeSusulan {

    public static function isPeriodeSusulan($tipe, $nops, $kelurahan, $conn, $tabel1, $tabel2) {
        $return = (object)[];
        $return->isSusulan = false;
        $return->tabel1 = $tabel1;
        $return->tabel2 = $tabel2;
        $bln_susulan_awal = 13;
        $bln_susulan_akhir = 0;

        $res = mysqli_query($conn, "SELECT * FROM central_app_config WHERE CTR_AC_KEY='susulan_start' OR CTR_AC_KEY='susulan_end'");
        while ($obj = mysqli_fetch_object($res)) {
            $bln_susulan_awal = (isset($obj->CTR_AC_KEY) && $obj->CTR_AC_KEY=='susulan_start') ? (int)$obj->CTR_AC_VALUE : $bln_susulan_awal;
            $bln_susulan_akhir = (isset($obj->CTR_AC_KEY) && $obj->CTR_AC_KEY=='susulan_end') ? (int)$obj->CTR_AC_VALUE : $bln_susulan_akhir;;
        }

        $bln_now = (int)date('m');
        $return->isSusulan = ($bln_now>=$bln_susulan_awal && $bln_now<=$bln_susulan_akhir) ? true : false;

        if($return->isSusulan){
            if($tipe==1){
                // ini utk pemilaian masal atau single yang biasa,
                // tapi memakai tabel susulan karena masuk bulan Susulan
                $return->tabel1 = 'cppmod_pbb_sppt_susulan';
                $return->tabel2 = 'cppmod_pbb_sppt_ext_susulan';
            }else{
                // ini utk pemilaian Mundur,
                // memakai tabel cppmod_pbb_sppt_mundur
                // tapi data harus di copy dahulu dari tabel susulan
                if($kelurahan){
                    $result = mysqli_query($conn, "REPLACE INTO cppmod_pbb_sppt_mundur SELECT * FROM cppmod_pbb_sppt_susulan WHERE LEFT(CPM_NOP,10)='$kelurahan'");
                    if($result){
                        $result = mysqli_query($conn, "REPLACE INTO cppmod_pbb_sppt_ext_mundur SELECT b.* FROM cppmod_pbb_sppt_susulan a JOIN cppmod_pbb_sppt_ext_susulan b ON a.CPM_SPPT_DOC_ID=b.CPM_SPPT_DOC_ID AND a.CPM_SPPT_DOC_VERSION=b.CPM_SPPT_DOC_VERSION AND LEFT(a.CPM_NOP,10)='$kelurahan'");
                        if($result){
                            $return->tabel1 = 'cppmod_pbb_sppt_mundur';
                            $return->tabel2 = 'cppmod_pbb_sppt_ext_mundur';
                        }
                    }
                }else{
                    $nopIN = [];
                    foreach ($nops as $nop) {
                        $nop = (int)$nop;
                        if(strlen($nop)==18){
                            $nopIN[] = "'".$nop."'";
                        }
                    }
                    $nopIN = implode(',',$nopIN);

                    $result = mysqli_query($conn, "REPLACE INTO cppmod_pbb_sppt_mundur SELECT * FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP IN ($nopIN)");
                    if($result){
                        $result = mysqli_query($conn, "REPLACE INTO cppmod_pbb_sppt_ext_mundur SELECT b.* FROM cppmod_pbb_sppt_susulan a JOIN cppmod_pbb_sppt_ext_susulan b ON a.CPM_SPPT_DOC_ID=b.CPM_SPPT_DOC_ID AND a.CPM_SPPT_DOC_VERSION=b.CPM_SPPT_DOC_VERSION AND a.CPM_NOP IN ($nopIN)");
                        if($result){
                            $return->tabel1 = 'cppmod_pbb_sppt_mundur';
                            $return->tabel2 = 'cppmod_pbb_sppt_ext_mundur';
                        }
                    }
                }
            }
        }else{
            if($tipe==1){
                // ini utk pemilaian masal atau single yang biasa,
                // tapi memakai tabel susulan karena masuk bulan Susulan
                $return->tabel1 = 'cppmod_pbb_sppt_final';
                $return->tabel2 = 'cppmod_pbb_sppt_ext_final';
            }else{
                // ini utk pemilaian Mundur,
                // memakai tabel cppmod_pbb_sppt_mundur
                // tapi data harus di copy dahulu dari tabel final
                if($kelurahan){
                    $result = mysqli_query($conn, "REPLACE INTO cppmod_pbb_sppt_mundur SELECT * FROM cppmod_pbb_sppt_final WHERE LEFT(CPM_NOP,10)='$kelurahan'");
                    if($result){
                        $result = mysqli_query($conn, "REPLACE INTO cppmod_pbb_sppt_ext_mundur SELECT b.* FROM cppmod_pbb_sppt_final a JOIN cppmod_pbb_sppt_ext_final b ON a.CPM_SPPT_DOC_ID=b.CPM_SPPT_DOC_ID AND a.CPM_SPPT_DOC_VERSION=b.CPM_SPPT_DOC_VERSION AND LEFT(a.CPM_NOP,10)='$kelurahan'");
                        if($result){
                            $return->tabel1 = 'cppmod_pbb_sppt_mundur';
                            $return->tabel2 = 'cppmod_pbb_sppt_ext_mundur';
                        }
                    }
                }else{
                    $nopIN = [];
                    foreach ($nops as $nop) {
                        $nop = (int)$nop;
                        if(strlen($nop)==18){
                            $nopIN[] = "'".$nop."'";
                        }
                    }
                    $nopIN = implode(',',$nopIN);

                    $result = mysqli_query($conn, "REPLACE INTO cppmod_pbb_sppt_mundur SELECT * FROM cppmod_pbb_sppt_final WHERE CPM_NOP IN ($nopIN)");
                    if($result){
                        $result = mysqli_query($conn, "REPLACE INTO cppmod_pbb_sppt_ext_mundur SELECT b.* FROM cppmod_pbb_sppt_final a JOIN cppmod_pbb_sppt_ext_final b ON a.CPM_SPPT_DOC_ID=b.CPM_SPPT_DOC_ID AND a.CPM_SPPT_DOC_VERSION=b.CPM_SPPT_DOC_VERSION AND a.CPM_NOP IN ($nopIN)");
                        if($result){
                            $return->tabel1 = 'cppmod_pbb_sppt_mundur';
                            $return->tabel2 = 'cppmod_pbb_sppt_ext_mundur';
                        }
                    }
                }
            }
        }
        return $return;
    }
}