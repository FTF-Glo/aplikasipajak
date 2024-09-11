<?php class PenentuanKelas {

    public static function get($tanahOrBgn, $thn, $nilaiPerM2, $conn) {
        $nilaiPerM2 = $nilaiPerM2 / 1000;
        $kelasNJOPM2 = ["XXX", 0, ""];
        if ($nilaiPerM2 > 0) {
            if ($tanahOrBgn == '1') {
                $kelasNJOPM2 = self::getKelasNJOPM2Bumi($nilaiPerM2, $thn, $conn);
            } elseif ($tanahOrBgn == '2') {
                $kelasNJOPM2 = self::getKelasNJOPM2Bangunan($nilaiPerM2, $thn, $conn);
            } else {
                $kelasNJOPM2[0] = "XXX";
                $kelasNJOPM2[1] = $nilaiPerM2;
            }
            $kelasNJOPM2[1] = $kelasNJOPM2[1] * 1000;
        }
        return $kelasNJOPM2;
    }

    private static function getKelasNJOPM2Bumi($nilaiPerM2, $thn, $conn) {
        $result = ["XXX", 0, ""];
        $qry = "SELECT CPM_KELAS, CPM_NJOP_M2 
                FROM cppmod_pbb_kelas_bumi
                WHERE 
                    CPM_THN_AWAL<='$thn' 
                    AND CPM_THN_AKHIR>='$thn' 
                    AND CPM_NILAI_BAWAH<'$nilaiPerM2' 
                    AND CPM_NILAI_ATAS>='$nilaiPerM2' 
                    AND CPM_KELAS NOT IN ('XXX', '00')
                ORDER BY CPM_NJOP_M2
                LIMIT 0, 1";
        $res = mysqli_query($conn, $qry);
        while ($row = mysqli_fetch_object($res)) {
            $result[0] = $row->CPM_KELAS;
            $result[1] = $row->CPM_NJOP_M2;
        }
        return $result;
    }

    private static function getKelasNJOPM2Bangunan($nilaiPerM2, $thn, $conn) {
        $result = ["XXX", 0, ""];
        $qry = "SELECT CPM_KELAS, CPM_NJOP_M2 
                FROM cppmod_pbb_kelas_bangunan
                WHERE 
                    CPM_THN_AWAL<='$thn'
                    AND CPM_THN_AKHIR>='$thn' 
                    AND CPM_NILAI_BAWAH<'$nilaiPerM2' 
                    AND CPM_NILAI_ATAS>='$nilaiPerM2' 
                    AND CPM_KELAS NOT IN ('XXX', '00')
                ORDER BY CPM_NJOP_M2
                LIMIT 0, 1";
        $res = mysqli_query($conn, $qry);
        while ($row = mysqli_fetch_object($res)) {
            $result[0] = $row->CPM_KELAS;
            $result[1] = $row->CPM_NJOP_M2;
        }
        return $result;
    }
}