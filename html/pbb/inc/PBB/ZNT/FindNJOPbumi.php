<?php class FindNJOPbumi {

    public static function penentuanNJOP($nop, $znt, $luas, $thn, $conn, $table) {
        $kelasNJOP_M2 = array("XXX", 0, 0);
        $nilaiBumi = PenilaianBumi::get($nop, $znt, $luas, $thn, $conn, $table);
        if($luas>0 && $nilaiBumi>0){
            $kelasNJOP_M2 = PenentuanKelas::get('1', $thn, $nilaiBumi, $conn);
            $kelasNJOP_M2[1] = round($kelasNJOP_M2[1] * $luas, 0);
        }
        $result['luas'] = $luas;
        $result['kelas']= $kelasNJOP_M2[0];
        $result['njopm2']= $nilaiBumi;
        $result['njop']	= $kelasNJOP_M2[1];
        $result['znt'] 	= $znt;
        return $result;
    }
}