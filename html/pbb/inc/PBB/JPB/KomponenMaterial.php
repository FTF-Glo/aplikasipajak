<?php class KomponenMaterial {

    public static function get($nop, $thn, $data, $conn) {
        $s = [];
        $kdPropinsi = substr($nop, 0, 2);
        $dati2 = substr($nop, 2, 2);
        $atap = 1;
        $nilaiDinding = 0;
        $nilaiLantai = 0;
        $nilaiLangit = 0;

        $jmlLantai  = (int)$data->CPM_OP_JML_LANTAI;
        $jmlLantai  = ($jmlLantai == 0) ? 1 : $jmlLantai;

        $kdDinding  = (int)$data->CPM_OP_DINDING;
        $jnsAtap    = sprintf("%02d", (int)$data->CPM_OP_ATAP);
        $kdLantai   = sprintf("%02d", (int)$data->CPM_OP_LANTAI);
        $kdLangit   = sprintf("%02d", (int)$data->CPM_OP_LANGIT);
        
        if($kdDinding == 0) {
            $kdDinding = 10;
        }elseif($kdDinding == 4) {
            $kdDinding = 7;
        }elseif($kdDinding == 5) {
            $kdDinding = 8;
        }elseif($kdDinding == 6) {
            $kdDinding = false;
        }

        if($kdDinding){
            $kdDinding = sprintf("%02d", $kdDinding);
    
            $s[] = "SELECT CPM_KD_PEKERJAAN, CPM_NILAI_DBKB_MATERIAL 
                    FROM cppmod_pbb_dbkb_material 
                    WHERE 
                        CPM_THN_DBKB_MATERIAL = '$thn' 
                        AND CPM_KD_PEKERJAAN = '21' 
                        AND CPM_KD_KEGIATAN = '$kdDinding' 
                        AND CPM_KD_DATI2 = '$dati2' 
                        AND CPM_KD_PROPINSI = '$kdPropinsi'";
        }

        if ($kdLantai!="00") {
            $s[] = "SELECT CPM_KD_PEKERJAAN, CPM_NILAI_DBKB_MATERIAL 
                    FROM cppmod_pbb_dbkb_material 
                    WHERE 
                        CPM_THN_DBKB_MATERIAL = '$thn' 
                        AND CPM_KD_PEKERJAAN = '22' 
                        AND CPM_KD_KEGIATAN = '$kdLantai' 
                        AND CPM_KD_DATI2 = '$dati2' 
                        AND CPM_KD_PROPINSI = '$kdPropinsi'";
        }

        if ($jnsAtap!="00") {
            $s[] = "SELECT CPM_KD_PEKERJAAN, CPM_NILAI_DBKB_MATERIAL 
                    FROM cppmod_pbb_dbkb_material 
                    WHERE 
                        CPM_THN_DBKB_MATERIAL = '$thn' 
                        AND CPM_KD_PEKERJAAN = '23' 
                        AND CPM_KD_KEGIATAN = '$jnsAtap' 
                        AND CPM_KD_DATI2 = '$dati2' 
                        AND CPM_KD_PROPINSI = '$kdPropinsi'";
        }


        if ($kdLangit!="00") {
            $s[] = "SELECT CPM_KD_PEKERJAAN, CPM_NILAI_DBKB_MATERIAL 
                    FROM cppmod_pbb_dbkb_material 
                    WHERE 
                        CPM_THN_DBKB_MATERIAL = '$thn' 
                        AND CPM_KD_PEKERJAAN = '24' 
                        AND CPM_KD_KEGIATAN = '$kdLangit' 
                        AND CPM_KD_DATI2 = '$dati2' 
                        AND CPM_KD_PROPINSI = '$kdPropinsi'";
        }

        if(count($s)>0){
            $sql = implode(' UNION ALL ', $s);
            $sql = "SELECT * FROM ($sql) DBKB;";
            $res = mysqli_query($conn, $sql);
            while($obj = mysqli_fetch_object($res)){
                if($obj->CPM_KD_PEKERJAAN=='21'){
                    $nilaiDinding = (float)$obj->CPM_NILAI_DBKB_MATERIAL;

                }elseif($obj->CPM_KD_PEKERJAAN=='22'){
                    $nilaiLantai = (float)$obj->CPM_NILAI_DBKB_MATERIAL;

                }elseif($obj->CPM_KD_PEKERJAAN=='23'){
                    $atap = (float)$obj->CPM_NILAI_DBKB_MATERIAL;

                }elseif($obj->CPM_KD_PEKERJAAN=='24'){
                    $nilaiLangit = (float)$obj->CPM_NILAI_DBKB_MATERIAL;
                }
            }
        }

        $nilaiAtap = ($atap>0) ? ($atap/$jmlLantai) : 0;

        return $nilaiDinding + $nilaiAtap + $nilaiLantai + $nilaiLangit;
    }
}