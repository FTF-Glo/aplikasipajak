<?php class FindNJOPbgn {

    public static function penentuanNJOPBangunan($nop, $tahun, $conn, $table1, $table2) {
        $result 		= array("luas"=>0,  "kelas"=>"XXX", "njop"=>0, "ext"=>array("op_num"=>1, "luas"=>0, "njopm2"=>0, "njop"=>0));
        $njopBangunan 	= 0;
        $nilaiBngTotal 	= 0;
        $luasBngTotal 	= 0;
        $nilaiBngPerM2 	= 0;
        $kelasBng 		= "XXX";
        $NJOPM2 		= 0;
        $res = mysqli_query($conn, "SELECT b.*
                                    FROM $table1 a, $table2  b 
                                    WHERE 
                                        a.CPM_NOP = '$nop' 
                                        AND a.CPM_SPPT_DOC_ID = b.CPM_SPPT_DOC_ID");
        $nilaiBng 	= 0;
        $nilaiSistem= 0;
        $ext = [];
        while ($obj = mysqli_fetch_object($res)) {
            $nilaiBng      	= 0;
            $nilaiSistem   	= 0;
            $nilaiIndividu  = $obj->CPM_PAYMENT_INDIVIDU;
            $kodeJPB       	= sprintf("%02d", (int)$obj->CPM_OP_PENGGUNAAN);
            $noBng          = (int)$obj->CPM_OP_NUM;
            $luasBng       	= (float)$obj->CPM_OP_LUAS_BANGUNAN;
            $jmlLantai     	= (int)$obj->CPM_OP_JML_LANTAI;
            $kodeLantai    	= $obj->CPM_OP_LANTAI;
            $nilaiTempSistem= (float)$obj->CPM_PAYMENT_SISTEM;
            $lastCode     	= substr($nop, 17, 1);

			$obj->NOP 					= $nop;
			$obj->LASTCODE 				= $lastCode;
			$obj->CPM_OP_PENGGUNAAN 	= $kodeJPB;
			$obj->CPM_OP_NUM 			= $noBng;
			$obj->CPM_OP_LUAS_BANGUNAN 	= $luasBng;
			$obj->CPM_OP_JML_LANTAI 	= $jmlLantai;
			$data = $obj;

            if(trim($kodeLantai) == "" && ($lastCode == "7" || $lastCode == "8")){
                $nilaiSistem = $nilaiTempSistem;
            }else{
                $nilaiSistem = PenilaianBgn::get($nop, $tahun, $data, $conn, $table1, $table2);
            }

            if ($nilaiIndividu > 0) { 
                $nilaiAntaraAwal  = $nilaiSistem * 0.949999988079071;
                $nilaiAntaraAkhir = $nilaiSistem * 1.0499999523162842;
                $nilaiBng = (($nilaiIndividu >= $nilaiAntaraAwal && $nilaiIndividu <= $nilaiAntaraAkhir)) ? $nilaiSistem : $nilaiIndividu;
            } else {
                $nilaiBng = $nilaiSistem;
            }

            $extTempNilai = $nilaiBng * 1000;
            $ext[] = array("op_num"=>$noBng, "luas"=>$luasBng, "njopm2"=>($extTempNilai/$luasBng), "njop"=>$extTempNilai);

            $nilaiBngTotal += (float)$nilaiBng;
            $luasBngTotal += (float)$luasBng;
        }

        if ($luasBngTotal>0 && $nilaiBngTotal>0) {
            $nilaiBngPerM2 = ($nilaiBngTotal / $luasBngTotal) * 1000;
            $kelasNJOPM2 = PenentuanKelas::get('2', $tahun, $nilaiBngPerM2, $conn);
            $kelasBng = trim($kelasNJOPM2[0]);
            $NJOPM2 = (float)$kelasNJOPM2[1];
            $njopBangunan = $NJOPM2 * $luasBngTotal;
            $njopBangunan = round($njopBangunan, 0);
        } else {
            $njopBangunan = 0;
        } 
		$tQuery = " UPDATE $table1 
					SET 
						CPM_OP_LUAS_BANGUNAN='$luasBngTotal', 
						CPM_OP_KELAS_BANGUNAN='$kelasBng', 
						CPM_NJOP_BANGUNAN='$njopBangunan'
					WHERE CPM_NOP='$nop'";
		mysqli_query($conn, $tQuery);

        if(count($ext)==1){
            $ext[0] = array("op_num"=>"1", "luas"=>$luasBngTotal, "njopm2"=>($njopBangunan/$luasBngTotal), "njop"=>$njopBangunan);
        }

		$result['luas'] = $luasBngTotal;
		$result['kelas']= $kelasBng;
		$result['njop']	= $njopBangunan;
		$result['ext'] 	= $ext;
        return $result;
    }
}