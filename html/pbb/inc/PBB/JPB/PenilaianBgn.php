<?php class PenilaianBgn {

    public static function get($nop, $thn, $data, $conn, $table1, $table2) {
        $noBng      = $data->CPM_OP_NUM;
        $kdJpb      = $data->CPM_OP_PENGGUNAAN;
        $luasBng    = $data->CPM_OP_LUAS_BANGUNAN;
        $jmlLantai  = $data->CPM_OP_JML_LANTAI;
        $lastCode   = substr($nop, 17, 1);

        

        $arrJpb = ["02", "04", "05", "07", "09"];
        $result = 0;
        if (in_array($kdJpb, $arrJpb) && ($luasBng >= 1000 || $jmlLantai > 4)) {
            switch ($kdJpb) {
                case "09":
                    $result = JPB9::get($nop, $thn, $data, $conn);
                    break;
                case "07":
                    $result = JPB7::get($nop, $thn, $data, $conn);
                    break;
                case "05":
                    $result = JPB5::get($nop, $thn, $data, $conn);
                    break;
                case "04":
                    $result = JPB4::get($nop, $thn, $data, $conn);
                    break;
                default:
                    $result = JPB2::get($nop, $thn, $data, $conn);
            }
        }else{
            switch ($kdJpb) {
                case "03":
                    $result = JPB3::get($nop, $thn, $data, $conn);
                    break;
                case "06":
                    $result = JPB6::get($nop, $thn, $data, $conn);
                    break;
                case "08":
                    $result = JPB8::get($nop, $thn, $data, $conn);
                    break;
                case "11":
                    $result = 0;
                    break;
                case "12":
                    $result = JPB12::get($nop, $thn, $data, $conn);
                    break;
                case "13":
                    $result = JPB13::get($nop, $thn, $data, $conn);
                    break;
                case "14":
                    $result = JPB14::get($nop, $thn, $data, $conn);
                    break;
                case "15":
                    $result = JPB15::get($nop, $thn, $data, $conn);
                    break;
                case "16":
                    $result = JPB16::get($nop, $thn, $data, $conn);
                    break;
                default:
                    $result = PenilaianStandard::get($nop, $thn, $data, $conn, $table1, $table2);
            }
        }

        // echo '<pre>'; print_r($result); exit;

        $result = round($result * 1000, 2);
        $result = (float)$result;
        $query="UPDATE $table1 a, $table2 b
                SET b.CPM_PAYMENT_SISTEM='$result' 
                WHERE 
                    a.CPM_SPPT_DOC_ID=b.CPM_SPPT_DOC_ID 
                    AND a.CPM_NOP='$nop' 
                    AND b.CPM_OP_NUM='$noBng'";
        mysqli_query($conn, $query);
        return $result;
    }
}