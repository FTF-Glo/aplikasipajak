<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// echo "asdasda";exit;
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'notaris', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/uuid.php");

// ini_set('display_errors', 1);
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";
//echo "<script src=\"inc/js/jquery-1.3.2.min.js\"></script>\n";
echo "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";

echo "<link rel=\"stylesheet\" href=\"function/BPHTB/notaris/func-detail-notaris.css\" type=\"text/css\">\n";
echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/datepicker/datepickercontrol.css\">";
echo "<script src=\"inc/datepicker/datepickercontrol.js\"></script>\n";
echo "<script src=\"inc/js/json-new.js\"></script>\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\">var uname = '" . $data->uname . "';</script>";


function getConfigValue($id, $key) {
    global $DBLink;
    //$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
    $id = $_REQUEST['a'];
    $qry = "select * from central_app_config where CTR_AC_AID = 'aBPHTB' and CTR_AC_KEY = '$key'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
                echo 
                error();
    }
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['CTR_AC_VALUE'];
    }
}

function getConfigure($appID) {
    $config = array();
    $a = $appID;
    $config['TENGGAT_WAKTU'] = getConfigValue($a, 'TENGGAT_WAKTU');
    $config['NPOPTKP_STANDAR'] = getConfigValue($a, 'NPOPTKP_STANDAR');
    $config['NPOPTKP_WARIS'] = getConfigValue($a, 'NPOPTKP_WARIS');
    $config['TARIF_BPHTB'] = getConfigValue($a, 'TARIF_BPHTB');
    $config['PRINT_SSPD_BPHTB'] = getConfigValue($a, 'PRINT_SSPD_BPHTB');
    $config['NAMA_DINAS'] = getConfigValue($a, 'NAMA_DINAS');
    $config['ALAMAT'] = getConfigValue($a, 'ALAMAT');
    $config['NAMA_DAERAH'] = getConfigValue($a, 'NAMA_DAERAH');
    $config['KODE_POS'] = getConfigValue($a, 'KODE_POS');
    $config['NO_TELEPON'] = getConfigValue($a, 'NO_TELEPON');
    $config['NO_FAX'] = getConfigValue($a, 'NO_FAX');
    $config['EMAIL'] = getConfigValue($a, 'EMAIL');
    $config['WEBSITE'] = getConfigValue($a, 'WEBSITE');
    $config['KODE_DAERAH'] = getConfigValue($a, 'KODE_DAERAH');
    $config['KEPALA_DINAS'] = getConfigValue($a, 'KEPALA_DINAS');
    $config['NAMA_JABATAN'] = getConfigValue($a, 'NAMA_JABATAN');
    $config['NIP'] = getConfigValue($a, 'NIP');
    $config['NAMA_PJB_PENGESAH'] = getConfigValue($a, 'NAMA_PJB_PENGESAH');
    $config['JABATAN_PJB_PENGESAH'] = getConfigValue($a, 'JABATAN_PJB_PENGESAH');
    $config['NIP_PJB_PENGESAH'] = getConfigValue($a, 'NIP_PJB_PENGESAH');
    return $config;
}

function mysql2json($mysql_result, $name) {
    $json = "{\n'$name': [\n";
    $field_names = array();
    $fields = mysqli_num_fields($mysql_result);
    for ($x = 0; $x < $fields; $x++) {
        $field_name = mysqli_fetch_field($mysql_result);
        if ($field_name) {
            $field_names[$x] = $field_name->name;
        }
    }
    $rows = mysqli_num_rows($mysql_result);
    for ($x = 0; $x < $rows; $x++) {
        $row = mysqli_fetch_array($mysql_result);
        $json.="{\n";
        for ($y = 0; $y < count($field_names); $y++) {
            $json.="'$field_names[$y]' :    '" . addslashes($row[$y]) . "'";
            if ($y == count($field_names) - 1) {
                $json.="\n";
            } else {
                $json.=",\n";
            }
        }
        if ($x == $rows - 1) {
            $json.="\n}\n";
        } else {
            $json.="\n},\n";
        }
    }
    $json.="]\n}";
    return($json);
}

function getSelectedData($id, &$dt) {
    global $DBLink;
    $maxtime = '00:20:00';
//  $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=0 AND A.CPM_SSB_ID='".$id."'";

    $query = sprintf("SELECT *,IF((TIMEDIFF(NOW(), B.CPM_TRAN_CLAIM_DATETIME))<'%s','1','0') AS CLAIM 
                    FROM cppmod_ssb_doc A,cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
                    AND B.CPM_TRAN_STATUS=1 AND B.CPM_TRAN_FLAG=0 AND A.CPM_SSB_ID='%s'", $maxtime, mysqli_real_escape_string($DBLink, $id));

    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        return false;
    }

    //echo $query;

    $json = new Services_JSON();
    $dt = $json->decode(mysql2json($res, "data"));

    for ($i = 0; $i < count($dt->data); $i++) {
        if ($dt->data[$i]->CLAIM != '1') {
            $query = sprintf("UPDATE cppmod_ssb_tranmain SET CPM_TRAN_CLAIM='%s', CPM_TRAN_CLAIM_DATETIME='%s' 
                WHERE CPM_TRAN_SSB_ID='%s'", "1", date('Y-m-d H:i:s'), mysqli_real_escape_string($DBLink, $id));
            $result = mysqli_query($DBLink, $query);
            if ($res === false) {
                return false;
            }
        }
        $query = sprintf("UPDATE cppmod_ssb_tranmain SET CPM_TRAN_READ=1 
                    WHERE CPM_TRAN_SSB_ID='%s'", mysqli_real_escape_string($DBLink, $id));
        $result = mysqli_query($DBLink, $query);
        if ($res === false) {
            echo "3:" . $query;
        }
    }



    return true;
}

function getNOKTP($noktp) {
    global $DBLink;

    $N1 = getConfigValue('NPOPTKP_STANDAR');
    $N2 = getConfigValue('NPOPTKP_WARIS');
    $day = getConfigValue("BATAS_HARI_NPOPTKP");
    $dbLimit = getConfigValue('TENGGAT_WAKTU');
    
    $CHECK_NPOPTKP_KTP_PAYMENT = getConfigValue('CHECK_NPOPTKP_KTP_PAYMENT');
    
    $dbName = getConfigValue('BPHTBDBNAME');
    $dbHost = getConfigValue('BPHTBHOSTPORT');
    $dbPwd = getConfigValue('BPHTBPASSWORD');
    $dbTable = getConfigValue('BPHTBTABLE');
    $dbUser = getConfigValue('BPHTBUSERNAME');
    // Connect to lookup database
    SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
    //payment_flag, mysqli_real_escape_string($payment_flag),
    $tahun = date('Y');
    $qry = "select * 
            from cppmod_ssb_doc A LEFT JOIN cppmod_ssb_tranmain AS B ON A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID
            where A.CPM_WP_NOKTP = '{$noktp}' and CPM_OP_THN_PEROLEH= '{$tahun}' and (DATE_ADD(DATE(A.CPM_SSB_CREATED), INTERVAL {$day} DAY) > CURDATE()) 
            AND B.CPM_TRAN_STATUS <> 4 AND B.CPM_TRAN_FLAG <> 1 AND A.CPM_OP_JENIS_HAK <> 30 AND A.CPM_OP_JENIS_HAK <> 31 
            AND A.CPM_OP_JENIS_HAK <> 32 AND A.CPM_OP_JENIS_HAK <> 33";
    //print_r($qry); 
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        return false;
    }

    if($CHECK_NPOPTKP_KTP_PAYMENT==0){
        if (mysqli_num_rows ($res)) {
        //$num_rows = mysqli_num_rows($res);
        // while($row = mysqli_fetch_assoc($res)){
                // $query2 = "SELECT wp_noktp, op_nomor, if (date(expired_date) < CURDATE(),1,0) AS EXPRIRE 
                        // FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 0 ORDER BY saved_date DESC LIMIT 1";
                // //print_r($query2);
                // $r = mysqli_query($DBLinkLookUp, $query2);
                // if ( $r === false ){
                    // die("Error Insertxx: ".mysqli_error($DBLinkLookUp));
                // }
                // if(mysqli_num_rows ($r)){
                    
                    // while($rowx = mysqli_fetch_assoc($r)){
                        // if ($rowx['EXPRIRE']) {
                            // return false;
                        // }else{
                            // $query3 = "SELECT wp_noktp, op_nomor FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 1 ORDER BY saved_date DESC LIMIT 1";
                            // $r2 = mysqli_query($DBLinkLookUp, $query3);
                            // if ( $r2 === false ){
                                // die("Error Insertxx: ".mysqli_error($DBLinkLookUp));
                            // }
                            // if (mysqli_num_rows($r2)) {
                                // return true;
                            // }
                        // }
                    // }
                    // return true;
                // }else return false;
        // }
        return true;


        }else return false;
    }else{
        if (mysqli_num_rows ($res)) {
            $num_rows = mysqli_num_rows($res);
            while($row = mysqli_fetch_assoc($res)){
                    $query2 = "SELECT wp_noktp, op_nomor, if (date(expired_date) < CURDATE(),1,0) AS EXPRIRE 
                            FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 0 ORDER BY saved_date DESC LIMIT 1";
                    //print_r($query2);
                    $r = mysqli_query($DBLinkLookUp, $query2);
                    if ( $r === false ){
                        die("Error Insertxx: ".mysqli_error($DBLinkLookUp));
                    }
                    if(mysqli_num_rows ($r)){
                        
                        while($rowx = mysqli_fetch_assoc($r)){
                            if ($rowx['EXPRIRE']) {
                                return false;
                            }else{
                                $query3 = "SELECT wp_noktp, op_nomor FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 1 ORDER BY saved_date DESC LIMIT 1";
                                $r2 = mysqli_query($DBLinkLookUp, $query3);
                                if ( $r2 === false ){
                                    die("Error Insertxx: ".mysqli_error($DBLinkLookUp));
                                }
                                if (mysqli_num_rows($r2)) {
                                    return true;
                                }
                            }
                        }
                        return true;
                    }else return false;
            }
        }
        else return false;
    }
}
function jenishak($js){
    global $DBLink;
    
    $texthtml= "<select name=\"right-land-build\" id=\"right-land-build\" style=\"height: 30px\" onchange=\"checkTransLast();hidepasar();cekAPHB();\">";
    $qry = "select * from cppmod_ssb_jenis_hak ORDER BY CPM_KD_JENIS_HAK asc";
                    //echo $qry;exit;
                    $res = mysqli_query($DBLink, $qry);
                    
                        while($data = mysqli_fetch_assoc($res)){
                            if($js==$data['CPM_KD_JENIS_HAK']){
                                $selected= "selected"; 
                            }else{
                                $selected= "";
                            }
                            $texthtml .= "<option value=\"".$data['CPM_KD_JENIS_HAK']."\" ".$selected." >".str_pad($data['CPM_KD_JENIS_HAK'],2,"0",STR_PAD_LEFT)." ".$data['CPM_JENIS_HAK']."</option>";
                        }
    $texthtml .="</select>";
    return $texthtml;
    
}

function aphb($aphb){
    global $DBLink;
    
    $texthtml= " Hamparan <select name=\"pengurangan-aphb\" id=\"pengurangan-aphb\" onchange=\"checkTransLast();\">
                    <option value=\"\" disabled>Pilih</option>
                    ";
    $qry = "select * from cppmod_ssb_aphb ORDER BY CPM_APHB_KODE asc";
                    //echo $qry;exit;
                    $res = mysqli_query($DBLink, $qry);
                        if(($aphb!=$data['CPM_APHB'])||($aphb=="")){
                                 $selected= "";
                            }else{
                                $selected= "selected";
                            }
                        while($data = mysqli_fetch_assoc($res)){
                            
                            $texthtml .= "<option value=\"".$data['CPM_APHB']."\" ".$selected." >".str_pad($data['CPM_APHB_KODE'],2,"0",STR_PAD_LEFT).":".$data['CPM_APHB']."</option>";
                        }
    $texthtml .="</select>";
    return $texthtml;
    
}
function formSSB($dat, $edit) {
    global $data, $DBLink;
    echo "<script src=\"inc/js/jquery.formatCurrency-1.4.0.min.js\" type=\"text/javascript\"></script>\n";
    echo "<script src=\"function/BPHTB/notaris/func-detail-ssb.js?ver=0\"></script>\n";
    echo "<link rel=\"stylesheet\" href=\"//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css\">";
    echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/bphtb/css/table/table.css\">";
    echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/bphtb/css/button/buttons.css\">";
    echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/bphtb/css/button/buttons-core.css\">";
    $a = strval($dat->CPM_OP_LUAS_BANGUN) * strval($dat->CPM_OP_NJOP_BANGUN) + strval($dat->CPM_OP_LUAS_TANAH) * strval($dat->CPM_OP_NJOP_TANAH);
    $b = strval($dat->CPM_OP_HARGA);
    $npop = 0;
    $type = $dat->CPM_PAYMENT_TIPE;
    $sel = $dat->CPM_PAYMENT_TIPE_SURAT;
    $sel_min = $dat->CPM_PAYMENT_TIPE_PENGURANGAN;
    $info = $dat->CPM_PAYMENT_TIPE_OTHER;
    $typeR = $dat->CPM_OP_JENIS_HAK;
    $APHB = $dat->CPM_APHB;
    $tAPHB = 0;
    if(($typeR==33)||($typeR==7)){
        $tAPHB = $dat->CPM_APHB;
    }
    if($sel_min!=0){
        $option_pengurangan="<option value=\"".$dat->CPM_PAYMENT_TYPE_KODE_PENGURANGAN.".".$dat->CPM_PAYMENT_TIPE_PENGURANGAN."\">Kode ".$dat->CPM_PAYMENT_TYPE_KODE_PENGURANGAN." : ".$dat->CPM_PAYMENT_TIPE_PENGURANGAN."%</option>";
    }else{
        $option_pengurangan="<option value=\"0\">0</option>";
    }
    $NPOPTKP = getConfigValue("1", 'NPOPTKP_STANDAR');

    //echo $typeR."<br>".$NPOPTKP;

    if ($typeR == 5) {
        $NPOPTKP = getConfigValue("1", 'NPOPTKP_WARIS');
    } else {
        
    }
    // if (getNOKTP($dat->CPM_WP_NOKTP, $dat->CPM_OP_NOMOR)) {
        // $NPOPTKP = 0;
    // }

    $sel1 = "";
    $sel2 = "";
    $sel3 = "";
    $sel4 = "";
    $sel5 = "";
    $c1 = "";
    $c2 = "";
    $c3 = "";
    $c4 = "";
    $r1 = "disabled=\"disabled\"";
    $r2 = "disabled=\"disabled\"";
    $r3 = "disabled=\"disabled\"";
    $r4 = "disabled=\"disabled\"";
    $r5 = "disabled=\"disabled\"";
    $r6 = "disabled=\"disabled\"";
    if ($sel_min == '1')
        $sel4 = "selected=\"selected\"";
    if ($sel_min == '2')
        $sel5 = "selected=\"selected\"";

    if ($sel == '1')
        $sel1 = "selected=\"selected\"";
    if ($sel == '2')
        $sel2 = "selected=\"selected\"";
    if ($sel == '3')
        $sel3 = "selected=\"selected\"";
    $kb = "false";
    if ($type == '1') {
        $c1 = "checked=\"checked\"";
        $r1 = "";
        $kb = "true";
    }
    if ($type == '2') {
        $c2 = "checked=\"checked\"";
        $r2 = "";
    }
    if ($type == '3') {
        $c3 = "checked=\"checked\"";
        $r3 = "";
    }
    if ($type == '4') {
        $c4 = "checked=\"checked\"";
        $r4 = "";
    }
    if ($type == '5') {
        $c5 = "checked=\"checked\"";
        $r5 = "";
        $r6 = "";
    }

    if ($b < $a)
        $npop = $a;
    else
        $npop = $b;
    
    if(getConfigValue("1", 'DENDA')=='1'){
        $c_denda="$(\"#denda-value\").val(0);
                $(\"#denda-percent\").val(0);
                $(\"#denda-percent\").focus(function() {
                    if($(\"#denda-percent\").val()==0){
                        $(\"#denda-percent\").val(\"\");
                    }
                  
                });
                $(\"#denda-value\").blur(function() {
                        if($(\"#denda-value\").val()==0){
                        $(\"#denda-value\").val(0);
                    }
                      
                    });
                    
                $(\"#denda-percent\").blur(function() {
                        if($(\"#denda-percent\").val()==0){
                        $(\"#denda-percent\").val(0);
                    }
                      
                    });
                    ";
        $kena_denda="<tr>
                    <td>Denda &nbsp;&nbsp;&nbsp;&nbsp;<input type=\"text\" name=\"denda-percent\" id=\"denda-percent\" value=\"".$dat->CPM_PERSEN_DENDA."\" onKeyPress=\"return numbersonly(this, event);checkTransaction();\" onkeyup=\"checkTransaction()\" title=\"Denda\" size=\"2\"  /> %</td>
                    <td id=\"tdenda\" align=\"right\"><input type=\"text\" name=\"denda-value\" id=\"denda-value\" value=\"".$dat->CPM_DENDA."\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"checkTransaction()\" title=\"Denda\" readonly=\"readonly\"/></td>
                  </tr>";
        $kena_denda2="";
    }else{
        $c_denda="$(\"#denda-value\").val(0);
                    $(\"#denda-percent\").val(0);";
        $kena_denda="";
        $kena_denda2="<input type=\"hidden\" name=\"denda-percent\" id=\"denda-percent\" />
                      <input type=\"hidden\" name=\"denda-value\" id=\"denda-value\"/>";
    }
    
    $readonly = "";
    $btnSave = "<input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan\" class=\"button-success pure-button\"/>";
    $btnSaveFinal = "<input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan dan Finalkan\" class=\"button-success pure-button\"/></td>";
    $msgClaim = "";
    if (($dat->CLAIM != '0') && ($dat->CPM_TRAN_OPR_NOTARIS != $data->uname)) {
        $readonly = "readonly=\"readonly\"";
        $btnSave = "";
        $btnSaveFinal = "";
        $msgClaim = "<div id=\"msg-claim\">Data ini sedang di akses oleh user lain, mohon tunggu sebentar !</div><br>";
    }
    $vedit = "false";
    if ($edit)
        $vedit = "true";
    $param = "{\'id\':\'" . $dat->CPM_SSB_ID . "\',\'draf\':1,\'uname\':\'" . $data->uname . "\',\'axx\':\'" . base64_encode($_REQUEST['a']) . "\'}";
    $ppdf = "<div align=\"right\">Print to PDF
            <img src=\"./image/icon/adobeacrobat.png\" width=\"16px\" height=\"16px\" 
            title=\"Dokumen PDF\" onclick=\"printToPDF('$param');\" ></div>";
   
    $dat->CPM_OP_LUAS_TANAH = number_format($dat->CPM_OP_LUAS_TANAH, 0,'','');
    $dat->CPM_OP_NJOP_TANAH = number_format($dat->CPM_OP_NJOP_TANAH, 0,'','');
    $dat->CPM_OP_LUAS_BANGUN = number_format($dat->CPM_OP_LUAS_BANGUN, 0,'','');
    $dat->CPM_OP_NJOP_BANGUN = number_format($dat->CPM_OP_NJOP_BANGUN, 0,'','');
    
    $typePecahan = explode('/', $dat->CPM_PAYMENT_TIPE_PECAHAN);
    $pengenaan = getConfigValue("1",'PENGENAAN_HIBAH_WARIS');
    $hitungAPHB = getConfigValue("aBPHTB",'HITUNG_APHB');
    $configAPHB = getConfigValue("aBPHTB",'CONFIG_APHB');

    $configPengenaan = getConfigValue('CONFIG_PENGENAAN');
    
    ($configAPHB=="1") ? $display_aphb= "" : $display_aphb="style=\"display:none\"";
    ($configPengenaan=="1") ? $display_pengenaan= "" : $display_pengenaan="style=\"display:none\"";
    $html = "
    <link rel=\"stylesheet\" href=\"https://www.w3schools.com/w3css/4/w3.css\">
    <script language=\"javascript\">
    var kb = " . $kb . ";
    var edit = " . $vedit . ";
    var hitungaphb = ".$hitungAPHB.";
    var configaphb = ".$configAPHB.";
    var configpengenaan = ".$configPengenaan.";
    $(function(){
        $('#loaderCek').hide();
        $('#pengurangan-aphb').attr(\"disabled\", \"disabled\");
        var jh=$(\"select#right-land-build option:selected\").val();
        if(jh==33){
            $('#pengurangan-aphb').removeAttr(\"disabled\", \"disabled\");
        }
        
        //$(\"#name2\").mask(\"" . getConfigValue('PREFIX_NOP') . "?99999999999999\");
        $(\"#noktp\").focus(function() {
          $(\"#noktp\").val(\"" . getConfigValue('PREFIX') . "\");
        });
        ".$c_denda."
        
        $(\"#noktp\").keyup(function() {
            var input = $(this),
            text = input.val().replace(/[^./0-9-_\s]/g, \"\");
            if(/_|\s/.test(text)) {
                text = text.replace(/_|\s/g, \"\");
                // logic to notify user of replacement
            }
            input.val(text);
        });
        var isFocus = true;
        $(\"#nama-wp-lama\").focus(function(eve){
            eve.preventDefault();            
            if(isFocus){
                $(\"#load-pbb\").html(\"<img src='image/large-loading.gif' style='width:20px;'>\");
                var nop = $(\"#name2\").val();
                $.ajax({
                    type: \"POST\",
                    url: \"".$sRootPath."function/BPHTB/notaris/func-cekNOPPBB.php\",
                    data: \"nop=\"+nop,
                    success: function(data){
                       /* checkNOP();*/
                        if(data.length != 0){
                            setElementsVal(data);                      
                        }
                        $(\"#load-pbb\").html(\"\");
                    }
                });   
                isFocus = false;
            }
        });
        
       
        function setElementsVal(data){
            var elVal = data.split('*');
            
            //current
            
            $(\"#nama-wp-lama\").val(elVal[0]);
            $(\"#nama-wp-cert\").val(elVal[0]);
            /*$(\"#name\").val(elVal[0]);
            $(\"#npwp\").val('0');
            $(\"#noktp\").val('0000000000000000');
            $(\"#address\").val(elVal[1]);
            $(\"#kelurahan\").val(elVal[2]);
            $(\"#rt\").val(elVal[3]);
            $(\"#rw\").val(elVal[4]);
            $(\"#kecamatan\").val(elVal[5]);
            $(\"#kabupaten\").val(elVal[6]);
            $(\"#zip-code\").val(elVal[7]);*/            
            $(\"#address2\").val(elVal[8]);
            $(\"#kelurahan2\").val(elVal[9]);
            $(\"#rt2\").val(elVal[10]);
            $(\"#rw2\").val(elVal[11]);
            $(\"#kecamatan2\").val(elVal[12]);
            $(\"#kabupaten2\").val(elVal[13]);            
            $(\"#zip-code2\").val('0');
            $(\"#zip-code\").val('0');
            $(\"#land-area\").val(elVal[14]);
            $(\"#land-njop\").val(elVal[15]);
            $(\"#building-area\").val(elVal[16]);
            $(\"#building-njop\").val(elVal[17]);
            $(\"#right-year\").val(elVal[18]);
            $(\"#op-znt\").val(elVal[19]);
            
            //old
            $(\"#nmWPOld\").val(elVal[0]);
            $(\"#alamatWPOld\").val(elVal[1]);
            $(\"#kelurahanWPOld\").val(elVal[2]);
            $(\"#rtWPOld\").val(elVal[3]);
            $(\"#rwWPOld\").val(elVal[4]);
            $(\"#kecamatanWPOld\").val(elVal[5]);
            $(\"#kabupatenWPOld\").val(elVal[6]);
            $(\"#alamatOPOld\").val(elVal[8]);
            $(\"#kelurahanOPOld\").val(elVal[9]);
            $(\"#rtOPOld\").val(elVal[10]);
            $(\"#rwOPOld\").val(elVal[11]);
            $(\"#kecamatanOPOld\").val(elVal[12]);
            $(\"#kabupatenOPOld\").val(elVal[13]);            
            $(\"#luasBumiOld\").val(elVal[14]);
            $(\"#njopBumiOld\").val(elVal[15]);
            $(\"#luasBangunanOld\").val(elVal[16]);
            $(\"#njopBangunanOld\").val(elVal[17]);
            $(\"#tahunSPPTOld\").val(elVal[18]);
            
            addSN();
            addET();
            checkTransaction();            
            setDisabledVal(true);
            $(\"#cekUpdateData\").css('display','');
            changeColor('#eeeeee');
        }
        
        $(\"#chkDisElements\").click(function(){
            if ($(\"#chkDisElements\").is(\":checked\")){
                setDisabledVal(false);
                changeColor('#ffffff');
                $(\"#isPBB\").val(1);
            }else{
                setDisabledVal(true);
                changeColor('#eeeeee');
                $(\"#isPBB\").val(0);
            }        
        });
            
        function setDisabledVal(valDis){
            
            $(\"#nama-wp-lama\").attr('readonly',valDis);
            $(\"#nama-wp-cert\").attr('readonly',valDis);
            /*$(\"#name\").attr('readonly',valDis);
            $(\"#npwp\").attr('readonly',valDis);
            $(\"#noktp\").attr('readonly',valDis);
            $(\"#address\").attr('readonly',valDis);
            $(\"#kelurahan\").attr('readonly',valDis);
            $(\"#rt\").attr('readonly',valDis);
            $(\"#rw\").attr('readonly',valDis);
            $(\"#kecamatan\").attr('readonly',valDis);
            $(\"#kabupaten\").attr('readonly',valDis);
            $(\"#zip-code\").attr('readonly',valDis);
            $(\"#address2\").attr('readonly',valDis);
            $(\"#kelurahan2\").attr('readonly',valDis);
            $(\"#rt2\").attr('readonly',valDis);
            $(\"#rw2\").attr('readonly',valDis);
            $(\"#kecamatan2\").attr('readonly',valDis);
            $(\"#kabupaten2\").attr('readonly',valDis);            
            $(\"#zip-code2\").attr('readonly',valDis);*/
            //$(\"#land-area\").attr('readonly',valDis);
            $(\"#land-njop\").attr('readonly',valDis);
               if ($(\"#building-area\").value =='0' || $(\"#building-area\").value ==''){
            $(\"#building-area\").attr('readonly',valDis);
            }
            $(\"#building-njop\").attr('readonly',valDis);
            $(\"#right-year\").attr('readonly',valDis);
        }

        function changeColor(vColor){
            
            $(\"#nama-wp-lama\").css('background-color',vColor);
            $(\"#nama-wp-cert\").css('background-color',vColor);
            /*$(\"#name\").css('background-color',vColor);
            $(\"#npwp\").css('background-color',vColor);
            $(\"#noktp\").css('background-color',vColor);
            $(\"#address\").css('background-color',vColor);
            $(\"#kelurahan\").css('background-color',vColor);
            $(\"#rt\").css('background-color',vColor);
            $(\"#rw\").css('background-color',vColor);
            $(\"#kecamatan\").css('background-color',vColor);
            $(\"#kabupaten\").css('background-color',vColor);
            $(\"#zip-code\").css('background-color',vColor);*/
            $(\"#address2\").css('background-color',vColor);
            $(\"#kelurahan2\").css('background-color',vColor);
            $(\"#rt2\").css('background-color',vColor);
            $(\"#rw2\").css('background-color',vColor);
            $(\"#kecamatan2\").css('background-color',vColor);
            $(\"#kabupaten2\").css('background-color',vColor);            
            $(\"#zip-code2\").css('background-color',vColor);
            //$(\"#land-area\").css('background-color',vColor);
            $(\"#land-njop\").css('background-color',vColor);
            if ($(\"#building-area\").value =='0' || $(\"#building-area\").value ==''){
                    $(\"#building-area\").css('background-color',vColor);
            }
            
            $(\"#building-njop\").css('background-color',vColor);
            $(\"#right-year\").css('background-color',vColor);
        }
        });
    /*$(\"#certificate-number\").keyup(function() {
        var input = $(this),
        text = input.val().replace(/[^./0-9-_\s]/g, \"\");
        if(/_|\s/.test(text)) {
            text = text.replace(/_|\s/g, \"\");
            // logic to notify user of replacement
        }
        input.val(text);
    });*/
    
    
    function setForm(d){
        $(\"#name\").val(d.CPM_WP_NAMA);
        $(\"#address\").val(d.CPM_WP_ALAMAT);
        $(\"#rt\").val(d.CPM_WP_RT);
        $(\"#rw\").val(d.CPM_WP_RW);
        //$(\"#WP_PROPINSI\").val(PROV);
        $(\"#kabupaten\").val(\"CIANJUR\");
        $(\"#kecamatan\").val(d.CPM_WP_KECAMATAN);
        $(\"#kelurahan\").val(d.CPM_WP_KELURAHAN);
    }

    function checkDukcapil(){
            var appID   = '".$_REQUEST['a']."'; 
            var noKTP   = $('#noktp').val();
            
            $('#loaderCek').show();
            $.ajax({
                type: 'POST',
                data: '&noKTP='+noKTP+'&appID='+appID,
                url: './function/BPHTB/notaris/svcCheckDukcapil.php',
                success: function(res){  
                    d=jQuery.parseJSON(res);
                    if(d.res==1){
                        $('#loaderCek').hide();
                        $(\"<div>\"+d.msg+\"</div>\").dialog({
                            modal: true,
                            buttons: {
                                Ya: function() {
                                    $(this).dialog( \"close\" );
                                    setForm(d.dat);
                                },
                                Tidak: function() {
                                    $(this).dialog( \"close\" );
                                }
                            }
                        });
                    } else if(d.res==0){
                        $('#loaderCek').hide();
                        $(\"<div>\"+d.msg+\"</div>\").dialog({
                            modal: true,
                            buttons: {
                                OK: function() {
                                    $(this).dialog( \"close\" );
                                }
                            }
                        });
                    }
                }   
            });
        }

    </script>
    <style>
        .myButton {
        -moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
        -webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
        box-shadow:inset 0px 1px 0px 0px #ffffff;
        background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #ffffff), color-stop(1, #f6f6f6));
        background:-moz-linear-gradient(top, #ffffff 5%, #f6f6f6 100%);
        background:-webkit-linear-gradient(top, #ffffff 5%, #f6f6f6 100%);
        background:-o-linear-gradient(top, #ffffff 5%, #f6f6f6 100%);
        background:-ms-linear-gradient(top, #ffffff 5%, #f6f6f6 100%);
        background:linear-gradient(to bottom, #ffffff 5%, #f6f6f6 100%);
        filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#f6f6f6',GradientType=0);
        background-color:#ffffff;
        -moz-border-radius:6px;
        -webkit-border-radius:6px;
        border-radius:6px;
        border:2px solid #dcdcdc;
        display:inline-block;
        cursor:pointer;
        color:#666666;
        font-family:Arial;
        font-size:11px;
        font-weight:bold;
        padding:6px 6px;
        text-decoration:none;
        text-shadow:0px 1px 0px #ffffff;
    }
    .myButton:hover {
        background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #f6f6f6), color-stop(1, #ffffff));
        background:-moz-linear-gradient(top, #f6f6f6 5%, #ffffff 100%);
        background:-webkit-linear-gradient(top, #f6f6f6 5%, #ffffff 100%);
        background:-o-linear-gradient(top, #f6f6f6 5%, #ffffff 100%);
        background:-ms-linear-gradient(top, #f6f6f6 5%, #ffffff 100%);
        background:linear-gradient(to bottom, #f6f6f6 5%, #ffffff 100%);
        filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#f6f6f6', endColorstr='#ffffff',GradientType=0);
        background-color:#f6f6f6;
    }
    .myButton:active {
        position:relative;
        top:1px;
    }


    </style>
    <style scoped>

            .button-success,
            .button-error,
            .button-warning,
            .button-secondary {
                color: white;
                border-radius: 4px;
                text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
            }

            .button-success {
                background: rgb(28, 184, 65); /* this is a green */
            }

            .button-error {
                background: rgb(202, 60, 60); /* this is a maroon */
            }

            .button-warning {
                background: rgb(223, 117, 20); /* this is an orange */
            }

            .button-secondary {
                background: rgb(66, 184, 221); /* this is a light blue */
            }

        </style>
        <div id=\"main-content\"><form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" onsubmit=\"return checkform();\">
              <table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
                <tr>
                  <td colspan=\"2\" align='center' style=\"border-radius: 10px 10px 0px 0px;\"><strong><font size=\"+2\">Form Surat Setoran Pajak Daerah <br>Bea Perolehan Hak Atas Tanah dan Bangunan <br>(SSPD-BPHTB)</font></strong>$ppdf</td>
                </tr>
                
                <tr>
                  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>A. </b></font></div></td>
                  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                    <tr>
                      <td width=\"10\"><div align=\"right\">1.</div></td>
                      <td width=\"200\">NOP PBB</td>
                      <td width=\"220\"><input readonly type=\"text\" name=\"name2\" id=\"name2\" value=\"" . $dat->CPM_OP_NOMOR . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"25\"  " . $readonly . " title=\"NOP PBB\"/></td>
                      <td width=\"100\">Nama WP Lama : </td>
                      <td><input type=\"text\" name=\"nama-wp-lama\" id=\"nama-wp-lama\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" value=\"" . $dat->CPM_WP_NAMA_LAMA . "\" size=\"30\" maxlength=\"30\" title=\"Nama WP Lama\"/>
                      </td>
                      </tr>
                    <tr valign=\"top\">
                      <td><div align=\"right\">2.</div></td>
                      <td>Lokasi Objek Pajak</td>
                      <td>
                        <textarea readonly name=\"address2\" id=\"address2\" cols=\"35\" rows=\"4\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" title=\"Lokasi Objek Pajak\" 
                        " . $readonly . ">" . str_replace("<br />", "\n", $dat->CPM_OP_LETAK) . "</textarea>
                        </td>
                                      <td width=\"100\">Nama WP Sesuai Sertifikat: </td>
                      <td><input type=\"text\" name=\"nama-wp-cert\" readonly id=\"nama-wp-cert\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" value=\"" . $dat->CPM_WP_NAMA_CERT . "\" size=\"30\" maxlength=\"30\" title=\"Nama WP Sesuai Sertifikat\"/>
                      
                    </tr>
                    <tr>
                      <td><div align=\"right\">3.</div></td>
                      <td>Kelurahan/Desa</td>
                      <td><input type=\"text\" readonly name=\"kelurahan2\" id=\"kelurahan2\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" value=\"" . $dat->CPM_OP_KELURAHAN . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" " . $readonly . " title=\"Kelurahan/Desa\"/></td>
                      <td>&nbsp;</td>
                      <td><input type=\"text\" name=\"op-znt\" id=\"op-znt\"  value=\"" . $dat->CPM_OP_ZNT . "\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" hidden /></td>
                    </tr>
                    <tr>
                      <td><div align=\"right\">4.</div></td>
                      <td>RT/RW</td>
                      <td colspan=\"3\"><input type=\"text\" readonly name=\"rt2\" id=\"rt2\" maxlength=\"3\" size=\"3\" value=\"" . $dat->CPM_OP_RT . "\" onKeyPress=\"return nextFocus(this, event)\" " . $readonly . " title=\"RT\"/>
                        /
                        <input type=\"text\" name=\"rw2\" readonly id=\"rw2\" maxlength=\"3\" size=\"3\" value=\"" . $dat->CPM_OP_RW . "\" onKeyPress=\"return nextFocus(this, event)\" " . $readonly . " title=\"RW\"/></td>
                    </tr>
                    <tr>
                      <td><div align=\"right\">5.</div></td>
                      <td>Kecamatan</td>
                      <td colspan=\"3\"><input type=\"text\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" readonly name=\"kecamatan2\" id=\"kecamatan2\" value=\"" . $dat->CPM_OP_KECAMATAN . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" " . $readonly . " title=\"Kecamatan\"/></td>
                    </tr>
                    <tr>
                      <td><div align=\"right\">6.</div></td>
                      <td>Kabupaten/Kota</td>
                      <td colspan=\"3\"><input type=\"text\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" readonly name=\"kabupaten2\" id=\"kabupaten2\" value=\"" . $dat->CPM_OP_KABUPATEN . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" " . $readonly . " title=\"Kabupaten/Kota\"/></td>
                    </tr>
                    <tr>
                      <td><div align=\"right\">7.</div></td>
                      <td>Kode Pos</td>
                      <td colspan=\"3\"><input type=\"text\" name=\"zip-code2\" id=\"zip-code2\" readonly value=\"" . $dat->CPM_OP_KODEPOS . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"5\" maxlength=\"5\" " . $readonly . " title=\"Kode POS\"/></td>
                    </tr>
                    <tr>
                      <td><div align=\"right\">8.</div></td>
                      <td>Nomor Sertifikat</td>
                      <td><input type=\"text\" name=\"certificate-number\" id=\"certificate-number\" value=\"" . $dat->CPM_OP_NMR_SERTIFIKAT . "\" onKeyPress=\"return numbersonly(this, event)\"  size=\"50\" maxlength=\"50\" title=\"Nomor Sertifikat Tanah\"/></td>
                    </tr>
                  </table>
                  
                  </td>
                </tr>
                <tr>
                  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>B. </b></font></div></td>
                  <td width=\"97%\"><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                    <tr>
                      <td width=\"10\"><div align=\"right\">1.</div></td>
                      <td>Nomor KTP</td>
                      <td><input type=\"text\" name=\"noktp\" id=\"noktp\" value=\"" . $dat->CPM_WP_NOKTP . "\" onkeypress=\"return nextFocus(this,event)\" size=\"24\" maxlength=\"24\" title=\"No KTP wajib di isi\" onblur=\"checkTransLast();\"/>&nbsp;&nbsp;<input type=\"hidden\" type=\"button\" name=\"checkKTP\" id=\"checkKTP\" value=\"Ambil Data Dukcapil\" class=\"myButton\" onclick=\"checkDukcapil();checkTransLast();\"><div id=\"newl\"></div></td>
                    </tr>
                    <tr>
                      <td><div align=\"right\">2.</div></td>
                      <td>NPWP</td>
                      <td><input type=\"text\" name=\"npwp\" id=\"npwp\" value=\"" . $dat->CPM_WP_NPWP . "\" onkeypress=\"return nextFocus(this,event)\" size=\"15\" maxlength=\"15\"  " . $readonly . " title=\"NPWP\"/></td>
                    </tr>
                    <tr>
                      <td><div align=\"right\">3.</div></td>
                      
                      <td width=\"200\">Nama Wajib Pajak</td>
                      <td width=\"\"><input type=\"text\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" name=\"name\" id=\"name\" value=\"" . $dat->CPM_WP_NAMA . "\" onkeypress=\"return nextFocus(this,event)\" size=\"60\" maxlength=\"60\" " . $readonly . " title=\"Nama Wajib Pajak\"/></td>
                    </tr>
                    <tr valign=\"top\">
                      <td><div align=\"right\">4.</div></td>
                      <td>Alamat Wajib Pajak</td>
                      <td><textarea  name=\"address\" id=\"address\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" cols=\"35\" rows=\"4\" title=\"Alamat Wajib pajak\" " . $readonly . ">" . str_replace("<br />", "\n", $dat->CPM_WP_ALAMAT) . "</textarea></td>                  
                    </tr>
                    <tr>
                      <td><div align=\"right\">5.</div></td>
                      <td>Kelurahan/Desa</td>
                      <td><input type=\"text\" name=\"kelurahan\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" id=\"kelurahan\" value=\"" . $dat->CPM_WP_KELURAHAN . "\" onkeypress=\"return nextFocus(this,event)\" size=\"20\" maxlength=\"20\"  " . $readonly . "  title=\"Kelurahan\"/></td>                 
                    </tr>
                    <tr>
                      <td><div align=\"right\">6.</div></td>
                      <td>RT/RW</td>
                      <td><input type=\"text\" name=\"rt\" id=\"rt\" maxlength=\"3\" size=\"3\"  value=\"" . $dat->CPM_WP_RT . "\" onkeypress=\"return nextFocus(this,event)\"  " . $readonly . "  title=\"RT\"/>/<input type=\"text\" name=\"rw\" id=\"rw\" maxlength=\"3\" size=\"3\"  value=\"" . $dat->CPM_WP_RW . "\" onkeypress=\"return nextFocus(this,event)\"  " . $readonly . " title=\"RW\"/></td>
                    </tr>
                    <tr>
                      <td><div align=\"right\">7.</div></td>
                      <td>Kecamatan</td>
                      <td><input type=\"text\" name=\"kecamatan\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" id=\"kecamatan\"  value=\"" . $dat->CPM_WP_KECAMATAN . "\" onkeypress=\"return nextFocus(this,event)\" size=\"20\" maxlength=\"20\"  " . $readonly . "  title=\"Kecamatan\"/></td>
                    </tr>
                    <tr>
                      <td><div align=\"right\">8.</div></td>
                      <td>Kabupaten/Kota</td>
                      <td><input type=\"text\" name=\"kabupaten\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" id=\"kabupaten\"  value=\"" . $dat->CPM_WP_KABUPATEN . "\" onkeypress=\"return nextFocus(this,event)\"  size=\"20\" maxlength=\"20\"  " . $readonly . " title=\"Kabupatan/Kota\"/></td>
                    </tr>
                    <tr>
                      <td><div align=\"right\">9.</div></td>
                      <td>Kode Pos</td>
                      <td><input type=\"text\" name=\"zip-code\" id=\"zip-code\"  value=\"" . $dat->CPM_WP_KODEPOS . "\" onKeyPress=\"return numbersonly(this, event)\"  size=\"5\" maxlength=\"5\" " . $readonly . " title=\"Kode POS\"/></td>
                    </tr>
                  </table>
                  <table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">              

                    <tr>
                      <td>10.</td>
                      <td width=\"22%\">Titik Koordinat</td>
                      <td><input type=\"text\" name=\"Koordinat\" id=\"Koordinat\" value=\"" . $dat->KOORDINAT . "\" onkeyup=\"javascript:{this.value = this.value.toUpperCase(); }\" value=\"\" onkeypress=\"return nextFocus(this,event)\" size=\"20\" title=\"Titik Koordinat Wajib diisi\" isdatepicker=\"true\"></td>
                    <tr>
                    <tr>
                      <td width=\"14\"><div align=\"right\">11.</div></td>
                      <td colspan=\"2\">Jenis perolehan hak atas tanah atau bangunan</td>
                    </tr>
                    <tr>
                      <td><div align=\"right\"></div></td>
                      <td colspan=\"2\">".jenishak($typeR)."</td>
                    <tr>
                    <tr id=\"aphb\" ".$display_aphb.">
                      <td><div align=\"right\"></div></td>
                      <td colspan=\"2\">".aphb($APHB)."</td>
                    <tr>
                  </table>
                  <table width=\"900\" border=\"1\" cellspacing=\"1\" cellpadding=\"4\" style=\"border: 1px solid black;border-collapse: collapse;\" class=\"pure-table\"><thead>
              <tr>
                <td colspan=\"5\"><strong>Penghitungan NJOP PBB:</strong></th>
                </tr>
              <tr>
                <th width=\"77\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Objek pajak</th>
                <th width=\"176\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Diisi luas tanah atau bangunan yang haknya diperoleh</th>
                <th width=\"197\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Diisi
                berdasarkan SPPT PBB terakhir sebelum terjadinya peralihan hak  
                  <input type=\"text\" name=\"right-year\" id=\"right-year\" maxlength=\"4\" size=\"4\" value=\"" . $dat->CPM_OP_THN_PEROLEH . "\" onKeyPress=\"return numbersonly(this, event)\" " . $readonly . " title=\"Tahun SPPT PBB\"/></th>
                <th colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Luas x NJOP PBB /m²</th>
                </tr>
                </thead>
              <tr>
                <td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Tanah / Bumi</td>
                <td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">7. Luas Tanah (Bumi)</td>
                <td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">9. NJOP Tanah (Bumi) /m²</td>
                <td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (7x9)</td>
                </tr>
              <tr>
                <td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input type=\"text\" name=\"land-area\" readonly id=\"land-area\" value=\"" . number_format(strval($dat->CPM_OP_LUAS_TANAH ), 0, '', ''). "\" onKeyPress=\"return numbersonly(this, event)\"  onkeyup=\"addSN();addET();checkTransaction();\" " . $readonly . "  title=\"Luas Tanah\"/>
                  m²</td>
                <td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input type=\"text\" name=\"land-njop\" readonly id=\"land-njop\" value=\"" . $dat->CPM_OP_NJOP_TANAH . "\" onKeyPress=\"return numbersonly(this, event)\"  onkeyup=\"addSN();addET();checkTransaction();\" " . $readonly . " title=\"NJOP Tanah\"/></td>
                <td width=\"28\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">11.</td>
                <td width=\"126\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\"  id=\"t1\">" . number_format(strval($dat->CPM_OP_LUAS_TANAH) * strval($dat->CPM_OP_NJOP_TANAH), 0, '.', ',') . "</td>
              </tr>
              <tr>
                <td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Bangunan</td>
                <td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">8. Luas Bangunan</td>
                <td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">10. NJOP Bangunan / m²</td>
                <td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (8x10)</td>
                </tr>
              <tr>
                <td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input type=\"text\" readonly name=\"building-area\" id=\"building-area\" value=\"" .number_format(strval($dat->CPM_OP_LUAS_BANGUN), 0, '', '') . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" " . $readonly . " title=\"Luas Bangunan\"/>
                m²</td>
                <td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input type=\"text\" readonly name=\"building-njop\" id=\"building-njop\" value=\"" . $dat->CPM_OP_NJOP_BANGUN . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" " . $readonly . " title=\"NJOP Bangunan\"/></td>
                <td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">12.</td>
                <td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t2\">" . number_format(strval($dat->CPM_OP_LUAS_BANGUN) * strval($dat->CPM_OP_NJOP_BANGUN), 0, '.', ',') . "</td>
              </tr>
              <tr>
                <td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">NJOP PBB </td>
                <td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">13.</td>
                <td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t3\">" . number_format(strval($dat->CPM_OP_LUAS_BANGUN) * strval($dat->CPM_OP_NJOP_BANGUN) + strval($dat->CPM_OP_LUAS_TANAH) * strval($dat->CPM_OP_NJOP_TANAH), 0, '.', ',') . "</td>
              </tr>
              
            </table>
                <div id=\"nilai-pasar\">
                    </div>
                    <br>
                        12. Harga Transaksi Rp. <input type=\"text\" name=\"trans-value\" id=\"trans-value\" value=\"" . $dat->CPM_OP_HARGA . "\" onKeyPress=\"return numbersonly(this, event)\"  onchange=\"checkTransaction()\" title=\"Harga Transaksi\"/ onblur=\"loadLaikPasar();\">
                  </td>
                </tr>
                <tr style=\"display:none\">
                  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>C. </b></font></div></td>
                  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                    <tr>
                      <td width=\"443\"><strong>AKUMULASI NILAI PEROLEHAN HAK SEBELUMNYA</strong></td>
                      <td width=\"188\" id=\"akumulasi\" >" . number_format(strval($dat->CPM_SSB_AKUMULASI), 0, '.', ',') . "</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>C. </b></font></div></td>
                  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                      <tr>
                        <td width=\"443\"><strong>Penghitungan BPHTB</strong></td>
                        <td width=\"188\" align=\"center\"><em><strong>Dalam rupiah</strong></em></td>
                      </tr>
                      <tr>
                        <td>Nilai Perolehan Objek Pajak (NPOP)</td>
                        <td id=\"tNJOP\" align=\"right\">" . number_format(getBPHTBPayment_all(0), 0, '.', ',') . "</td>
                      </tr>
                      <tr>
                        <td>Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)</td>
                        <td id=\"NPOPTKP\" align=\"right\">" . number_format($dat->CPM_OP_NPOPTKP, 0, '.', ',') . "</td>
                      </tr>
                      <tr>
                        <td>Nilai Perolehan Objek Pajak Kena Pajak(NPOPKP)</td>
                        <td id=\"tNPOPKP\"  align=\"right\">" . number_format(getBPHTBPayment_all(1), 0, '.', ',') . "</td>
                      </tr>
                      <tr>
                        <td>Bea Perolehan atas Hak Tanah dan Bangunan yang terhutang</td>
                        <td id=\"tBPHTBTS\" align=\"right\">" . number_format(getBPHTBPayment_all(2), 0, '.', ',') . "</td>
                      </tr>
                      </tr>
                       <tr ".$display_pengenaan.">
                        <td>Pengenaan Karena Waris/Hibah Wasiat/Pemberian Hak Pengelola &nbsp;&nbsp;<input style=\"border:none\" type=\"text\" id=\"Phibahwaris\" size=\"1\" name=\"Phibahwaris\" value=\"".$pengenaan."\" readonly=\"readonly\"/>%</td>
                        <td id=\"tPengenaan\" align=\"right\">" . number_format(getBPHTBPayment_all(3), 0, '.', ',') . "</td>
                      </tr>
                      <tr ".$display_aphb.">
                        <td>APHB &nbsp;&nbsp;</td>
                        <td id=\"tAPHB\" align=\"right\">" . number_format(getBPHTBPayment_all(4), 0, '.', ',') . "</td>
                      </tr>
                      ".$kena_denda."".$kena_denda2."
                      <tr>
                        <td><strong>Bea Perolehan atas Hak Tanah dan Bangunan yang harus dibayar</strong></td>
                        <td id=\"tBPHTBT\" align=\"right\">" . number_format(getBPHTBPayment_all(5), 0, '.', ',') . "</td>
                      </tr>
                      <tr id=\"ketlaporan\">
                      </tr>
                      <tr id=\"ketlaporan2\">
                      </tr>
                  </table></td>
                </tr>
                <tr>
                  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>D. </b></font></div></td>
                  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                    <tr>
                      <td colspan=\"3\"><strong>Jumlah Setoran Berdasarkan : </strong><em>(tekan kotak yang sesuai)</em></td>
                      </tr>
                    <tr>
                      <td width=\"24\" align=\"center\" valign=\"top\"><p>
                        <label>
                          <input type=\"radio\" name=\"RadioGroup1\" value=\"1\" id=\"RadioGroup1_0\"  onclick=\"enableE(this,0);\" " . $c1 . " />
                        </label>
                        <br />
                        <br />
                      </p></td>
                      <td width=\"15\" align=\"right\" valign=\"top\">a.</td>
                      <td width=\"583\" valign=\"top\">Penghitungan Wajib Pajak</td>
                    </tr>
                                    <tr>
                      <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"2\" id=\"RadioGroup1_1\" disabled onclick=\"enableE(this,1);\" ".$c2." /></td>
                      <td align=\"right\" valign=\"top\">b.</td>
                      <td valign=\"top\"><select name=\"jsb-choose\" id=\"jsb-choose\" ".$r2.">
                        <option value=\"1\" " . $sel1 . " >STPD BPHTB</option>
                        <option value=\"2\" " . $sel2 . " >SKPD Kurang Bayar</option>
                        <option value=\"3\" " . $sel3 . " >SKPD Kurang Bayar Tambahan</option>
                      </select><font size=\"2\" color=\"red\">*hanya bisa dilakukan di menu kurang bayar</font></td>
                    </tr>
                    <tr>
                      <td align=\"center\" valign=\"top\">&nbsp;</td>
                      <td align=\"right\" valign=\"top\">&nbsp;</td>
                      <td valign=\"top\">Nomor : 
                        <input type=\"text\" name=\"jsb-choose-number\" id=\"jsb-choose-number\" size=\"30\" maxlength=\"30\" value=\"".$dat->CPM_PAYMENT_TIPE_SURAT_NOMOR."\" ".$readonly."\" ".$r2." title=\"Nomor Surat Pengurangan\"/></td>
                    </tr>
                    <tr>
                      <td align=\"center\" valign=\"top\">&nbsp;</td>
                      <td align=\"right\" valign=\"top\">&nbsp;</td>
                      <td valign=\"top\">Tanggal : 
                        <input type=\"text\" name=\"jsb-choose-date\" id=\"jsb-choose-date\" datepicker=\"true\" datepicker_format=\"DD/MM/YYYY\" readonly=\"readonly\" size=\"10\" maxlength=\"10\" value=\"".$dat->CPM_PAYMENT_TIPE_SURAT_TANGGAL."\" ".$readonly."\" ".$r2." title=\"Tanggal Surat Pengurangan\"/></td>
                    </tr>
                    
                    <tr>
                      <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"3\" id=\"RadioGroup1_4\"  onclick=\"enableE(this,2);\" ".$c3."/></td>
                      <td align=\"right\" valign=\"top\">c.</td>
                      <td valign=\"top\">Pengurangan dihitung sendiri menjadi <select name=\"jsb-choose-percent\" id=\"jsb-choose-percent\" onchange=\"checkTransLast();\">".$option_pengurangan."
                        ";
                        $qry = "select * from cppmod_ssb_pengurangan ORDER BY CPM_KODE_PENGURANGAN asc";
                        //echo $qry;exit;
                        $res = mysqli_query($DBLink, $qry);
                        
                            while($data = mysqli_fetch_assoc($res)){
                                $html .= "<option value=\"".$data['CPM_KODE_PENGURANGAN'].".".$data['CPM_PENGURANGAN']."\">Kode ".$data['CPM_KODE_PENGURANGAN']." : ".$data['CPM_PENGURANGAN']."%</option>";
                            }
                    $html .="                 </select></td>
                    </tr>
                    <tr>
                      <td align=\"center\" valign=\"top\">&nbsp;</td>
                      <td align=\"right\" valign=\"top\">&nbsp;</td>
                      <td valign=\"top\"><!-- Berdasakan peraturan KDH No : --> 
                        <input type=\"text\" name=\"jsb-choose-role-number\" id=\"jsb-choose-role-number\" size=\"30\" maxlength=\"30\" value=\"-\" title=\"Peraturan KHD No\" hidden=\"hidden\" /></td>
                    </tr>
                    <tr>
                      <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"4\" id=\"RadioGroup1_6\" onclick=\"enableE(this,3);\" ".$c4." /></td>
                      <td align=\"right\" valign=\"top\">d.</td>
                      <td valign=\"top\"><textarea name=\"jsb-etc\" id=\"jsb-etc\" cols=\"35\" rows=\"5\" ".$readonly." ".$r4." title=\"Lain-lain\">".$info."</textarea>
                      <input type=\"hidden\" id=\"ver-doc\" value=\"".$dat->CPM_TRAN_SSB_VERSION."\" name=\"ver-doc\">
                      <input type=\"hidden\" id=\"trsid\" value=\"".$dat->CPM_TRAN_ID."\" name=\"trsid\">
                      </td>
                    </tr>
                    <tr>
                      <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"5\" id=\"RadioGroup1_8\"  onclick=\"enableE(this,4);\" ".$c5." hidden=\"hidden\" /></td>
                      <td align=\"right\" valign=\"top\"></td>
                      <td valign=\"top\"><!--Khusus untuk waris dan Hibah Pengurangan dihitung sendiri menjadi --> <input type=\"text\" name=\"jsb-choose-fraction1\" id=\"jsb-choose-fraction1\" size=\"1\" maxlength=\"2\" value=\"".$typePecahan[0]."\" ".$readonly." ".$r5." title=\"pecahan 1\" onkeyup=\"checkTransaction()\" onKeyPress=\"return numbersonly(this, event)\" hidden=\"hidden\"/><input type=\"text\" name=\"jsb-choose-fraction2\" id=\"jsb-choose-fraction2\" size=\"1\" maxlength=\"2\" value=\"".$typePecahan[1]."\" ".$readonly." ".$r6." title=\"pecahan 2\" onkeyup=\"checkTransaction()\" onKeyPress=\"return numbersonly(this, event)\" hidden=\"hidden\"/></td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                <td colspan=\"2\" align=\"center\" valign=\"middle\" id=\"jmlBayar\">Jumlah yang dibayarkan : " . number_format(getBPHTBPayment_all(6), 0, '.', ',') . "</td>
                </tr>
                <tr><input type=\"hidden\" id=\"jsb-total-before\" name=\"jsb-total-before\"><input type=\"hidden\" id=\"hd-npoptkp\" name=\"hd-npoptkp\" value=\"" . $dat->CPM_OP_NPOPTKP . "\">
                <td colspan=\"2\" align=\"center\" valign=\"middle\">" . $btnSave . "
                  &nbsp;&nbsp;&nbsp;" . $btnSaveFinal . "
                </tr>
                <tr>
                  <td colspan=\"2\" align=\"center\" valign=\"middle\" style=\"border-radius: 0px 0px 10px 10px;\"><input type=\"hidden\" name=\"role\" id=\"role\" name=\"role\" value=\"".getRole()."\"></td>
                </tr>
              </table>
            </form></div>
            
             <div id=\"id01\" class=\"w3-modal\">
        <div class=\"w3-modal-content\">
          <div id=\"w3-container\">
            
            
          </div>
        </div>
    </div>";
    return $html;
}
function getRole(){
    global $DBLink, $data;
    $id = $_REQUEST['a'];
    $qry = "select * from central_user_to_app where CTR_APP_ID = '" . $id . "' and CTR_USER_ID = '" . $data->uid . "'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['CTR_RM_ID'];
    }
}
function getNoPel($type) {
    global $DBLink;
    $nomor = "1";
    $arrJnsPerolehan = array(1=>"01",2=>"02",3=>"03",4=>"04",5=>"05",6=>"06",7=>"07",8=>"08",9=>"09",10=>"10",11=>"11",12=>"12",13=>"13",14=>"14",21=>"21",22=>"22", 30=>"30", 31=>"31", 32=>"32", 33=>"33");
    $jnsPerolehan = $arrJnsPerolehan[$type];
    $tahun = date("Y");
    
    $qry = "select * from cppmod_ssb_berkas WHERE CPM_BERKAS_JNS_PEROLEHAN = '{$type}'
            and DATE_FORMAT(STR_TO_DATE(CPM_BERKAS_TANGGAL,'%d-%m-%Y'),'%Y') ='{$tahun}' 
                order by CPM_BERKAS_ID DESC limit 0,1";
    $res = mysqli_query($DBLink, $qry);
    
    if ($row = mysqli_fetch_array($res)) {
        $nomor_exp = explode(".",$row['CPM_BERKAS_NOPEL']);
        $nomor = (int) $nomor_exp[2];
        $nomor ++;
        
    }    
    $nomor = str_pad($nomor, 5,"0",STR_PAD_LEFT);
    if($type!="0"){
            $noPel = "{$tahun}.{$jnsPerolehan}.{$nomor}";
    }else{
            $noPel = "";
        
    }
    return $noPel;    
}
function save_berkas($idssb){
    global $DBLink,$data;
    $nop = @isset($_REQUEST['name2']) ? $_REQUEST['name2'] : "";
    $jp = @isset($_REQUEST['right-land-build']) ? $_REQUEST['right-land-build'] : "";
    $nopel = getNoPel($jp);
    $alamat_op = @isset($_REQUEST['address2']) ? $_REQUEST['address2'] : "";
    $kec_op = @isset($_REQUEST['kecamatan2']) ? $_REQUEST['kecamatan2'] : "";
    $kel_op = @isset($_REQUEST['kelurahan2']) ? $_REQUEST['kelurahan2'] : "";
    
    $noktp = @isset($_REQUEST['noktp']) ? $_REQUEST['noktp'] : "";
    $npwp = @isset($_REQUEST['npwp']) ? $_REQUEST['npwp'] : "";
    $npwp_as=$npwp;
    if($npwp==""){
        $npwp_as=$noktp;
    }
    $nama_wp = @isset($_REQUEST['name']) ? $_REQUEST['name'] : "";
    $harga = @isset($_REQUEST['trans-value']) ? $_REQUEST['trans-value'] : "";
    $opr = $data->uname;
    $iddoc = $idssb;
    $qry = sprintf("INSERT INTO cppmod_ssb_berkas (
            CPM_BERKAS_NOP,CPM_BERKAS_TANGGAL,CPM_BERKAS_ALAMAT_OP,CPM_BERKAS_KELURAHAN_OP, 
            CPM_BERKAS_KECAMATAN_OP,CPM_BERKAS_NPWP,CPM_BERKAS_NAMA_WP,
            CPM_BERKAS_JNS_PEROLEHAN,CPM_BERKAS_NOPEL, 
            CPM_BERKAS_HARGA_TRAN,CPM_SSB_DOC_ID           
            ) VALUES ('%s','%s','%s',
                    '%s','%s','%s',                    
                    '%s','%s','%s',
                    '%s','%s')", mysqli_escape_string($DBLink, $nop), date('d-m-Y'), 
                                      mysqli_escape_string($DBLink, $alamat_op), mysqli_escape_string($DBLink, $kel_op), mysqli_escape_string($DBLink, $kec_op), 
                                      mysqli_escape_string($DBLink, $npwp_as), mysqli_escape_string($DBLink, $nama_wp), mysqli_escape_string($DBLink, $jp), 
                                      mysqli_escape_string($DBLink, $nopel), mysqli_escape_string($DBLink, $harga), mysqli_escape_string($DBLink, $iddoc));

    
    $result = mysqli_query($DBLink, $qry);
        if ($result === false) {
            //handle the error here
            print_r(mysqli_error($DBLink) . $qry);
        }
    
}
function formSSBKB($data) {

    /* $value = array();
      $errMsg = array();
      $j = count($val);
      $err="";
      for ($i=0; $i<$j ; $i++) {
      if ((substr($val[$i],0,5)=='Error') || ($val[$i]=="") ) {
      $errMsg[$i] = $val[$i];
      $value[$i] = "";
      }  else {
      $errMsg[$i] = "";
      $value[$i] = $val[$i];
      }
      } */
    echo "<script src=\"function/BPHTB/notaris/func-kurang-bayar.js?ver=0\"></script>\n";
    $idssb = @isset($_REQUEST["idssb"]) ? $_REQUEST["idssb"] : "";
    $idt = @isset($_REQUEST["idtid"]) ? $_REQUEST["idtid"] : "";
    //$data = "";
    $sel1 = "";
    $sel2 = "";
    $sel3 = "";
    $rej = "";
    $r2 = "";
    $txtReject = "";
    if ($idssb) {
        //$dat =  getDataReject($idssb,$idt);
        //$data = $dat->data[0];
        $typeR = $data->CPM_OP_JENIS_HAK;
        $sel = $data->CPM_PAYMENT_TIPE_SURAT;
        $rej = "1";
        if ($sel == '1')
            $sel1 = "selected=\"selected\"";
        if ($sel == '2')
            $sel2 = "selected=\"selected\"";
        if ($sel == '3')
            $sel3 = "selected=\"selected\"";
        //$txtReject = "Alasan penolakan : ".$data->CPM_TRAN_INFO;
        if ($data->CPM_TRAN_STATUS == '4') {
            if ($data->CPM_TRAN_SSB_NEW_VERSION)
                $msgNewVer = "<br><i>Dokumen ini telah dibuat versi barunya yaitu : " . $data->CPM_TRAN_SSB_NEW_VERSION . "</i>";
            $txtReject = "\n<br><br><div id=\"info-reject\" class=\"msg-info\"> <strong>Ditolak karena : </strong><br/>" . str_replace("\n", "<br>", $data->CPM_TRAN_INFO) . $msgNewVer . "</div>\n";
        }
        //print_r($data);
    }
    if(($typeR==33)||($typeR==7)){
        $APHB = $data->CPM_APHB;
    }
    $nop=$data->CPM_OP_NOMOR ? $data->CPM_OP_NOMOR : getConfigValue('PREFIX');
    $bphtb_before =$data->CPM_KURANG_BAYAR_SEBELUM;
    $pengenaan = getConfigValue("1",'PENGENAAN_HIBAH_WARIS');
    $type = $data->CPM_PAYMENT_TIPE;
    if ($type == '2') {
        $c2 = "checked=\"checked\"";
        $r2 = "";
    }
    if(getConfigValue("1", 'DENDA')=='1'){
        $c_denda="$(\"#denda-value\").val(0);
                $(\"#denda-percent\").val(0);
                $(\"#denda-percent\").focus(function() {
                    if($(\"#denda-percent\").val()==0){
                        $(\"#denda-percent\").val(\"\");
                    }
                  
                });
                $(\"#denda-value\").blur(function() {
                        if($(\"#denda-value\").val()==0){
                        $(\"#denda-value\").val(0);
                    }
                      
                    });
                    
                $(\"#denda-percent\").blur(function() {
                        if($(\"#denda-percent\").val()==0){
                        $(\"#denda-percent\").val(0);
                    }
                      
                    });
                    ";
        $kena_denda="<tr>
                    <td>Denda &nbsp;&nbsp;&nbsp;&nbsp;<input type=\"text\" name=\"denda-percent\" id=\"denda-percent\" value=\"".$data->CPM_PERSEN_DENDA."\" onKeyPress=\"return numbersonly(this, event);checkTransaction();\" onkeyup=\"checkTransaction()\" title=\"Denda\" size=\"2\"  /> %</td>
                    <td id=\"tdenda\" align=\"right\"><input type=\"text\" name=\"denda-value\" id=\"denda-value\" value=\"".$data->CPM_DENDA."\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"checkTransaction()\" title=\"Denda\" readonly=\"readonly\"/></td>
                  </tr>";
        $kena_denda2="";
    }else{
        $c_denda="$(\"#denda-value\").val(0);
                    $(\"#denda-percent\").val(0);";
        $kena_denda="";
        $kena_denda2="<input type=\"hidden\" name=\"denda-percent\" id=\"denda-percent\" />
                      <input type=\"hidden\" name=\"denda-value\" id=\"denda-value\"/>";
    }
    $hitungAPHB = getConfigValue("aBPHTB",'HITUNG_APHB');
    
    $configAPHB = getConfigValue('CONFIG_APHB');
    $configPengenaan = getConfigValue('CONFIG_PENGENAAN');
    
    ($configAPHB=="1") ? $display_aphb= "" : $display_aphb="style=\"display:none\"";
    ($configPengenaan=="1") ? $display_pengenaan= "" : $display_pengenaan="style=\"display:none\"";
    $html = "
    <script language=\"javascript\">
    var edit = false;
    var hitungaphb = ".$hitungAPHB.";
    var configaphb = ".$configAPHB.";
    var configpengenaan = ".$configPengenaan.";
    $(function(){
            $(\"#name2\").mask(\"" . str_pad(getConfigValue('PREFIX_NOP') . "?", 19, "9", STR_PAD_RIGHT) . "\");
        $(\"#noktp\").focus(function() {
          $(\"#noktp\").val(\"" . getConfigValue('PREFIX') . "\");
        });
        ".$c_denda."
        if($('#right-land-build').val()==7 || $('#right-land-build').val()==33){
            $('#pengurangan-aphb').removeAttr('disabled');
        }else{
            $('#pengurangan-aphb').attr(\"disabled\", \"disabled\");
        }
        $(\"#noktp\").keyup(function() {
            var input = $(this),
            text = input.val().replace(/[^./0-9-_\s]/g, \"\");
            if(/_|\s/.test(text)) {
                text = text.replace(/_|\s/g, \"\");
                // logic to notify user of replacement
            }
            input.val(text);
        });
        /*$(\"#certificate-number\").keyup(function() {
            var input = $(this),
            text = input.val().replace(/[^./0-9-_\s]/g, \"\");
            if(/_|\s/.test(text)) {
                text = text.replace(/_|\s/g, \"\");
                // logic to notify user of replacement
            }
            input.val(text);
        });*/
    });
    function setForm(d){
            $(\"#name\").val(d.CPM_WP_NAMA);
            $(\"#address\").val(d.CPM_WP_ALAMAT);
            $(\"#rt\").val(d.CPM_WP_RT);
            $(\"#rw\").val(d.CPM_WP_RW);
            //$(\"#WP_PROPINSI\").val(PROV);
            $(\"#kabupaten\").val(\"CIANJUR\");
            $(\"#kecamatan\").val(d.CPM_WP_KECAMATAN);
            $(\"#kelurahan\").val(d.CPM_WP_KELURAHAN);
    }

    function checkDukcapil(){
            var appID   = '".$_REQUEST['a']."'; 
            var noKTP   = $('#noktp').val();
            $('#pengurangan-aphb').attr(\"disabled\", \"disabled\");
            $('#loaderCek').show();
            $.ajax({
                type: 'POST',
                data: '&noKTP='+noKTP+'&appID='+appID,
                url: './function/BPHTB/notaris/svcCheckDukcapil.php',
                success: function(res){  
                    d=jQuery.parseJSON(res);
                    if(d.res==1){
                        $('#loaderCek').hide();
                        $(\"<div>\"+d.msg+\"</div>\").dialog({
                            modal: true,
                            buttons: {
                                Ya: function() {
                                    $(this).dialog( \"close\" );
                                    setForm(d.dat);
                                },
                                Tidak: function() {
                                    $(this).dialog( \"close\" );
                                }
                            }
                        });
                    } else if(d.res==0){
                        $('#loaderCek').hide();
                        $(\"<div>\"+d.msg+\"</div>\").dialog({
                            modal: true,
                            buttons: {
                                OK: function() {
                                    $(this).dialog( \"close\" );
                                }
                            }
                        });
                    }
                }   
            });
        }
    </script>
    <style scoped>

        .button-success,
        .button-error,
        .button-warning,
        .button-secondary {
            color: white;
            border-radius: 4px;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
        }

        .button-success {
            background: rgb(28, 184, 65); /* this is a green */
        }

        .button-error {
            background: rgb(202, 60, 60); /* this is a maroon */
        }

        .button-warning {
            background: rgb(223, 117, 20); /* this is an orange */
        }

        .button-secondary {
            background: rgb(66, 184, 221); /* this is a light blue */
        }

    </style>
        <div id=\"main-content\"><form name=\"form-notaris\" id=\"form-notaris\" method=\"post\" action=\"\" onsubmit=\"return checkform();\">
          <table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
            <tr>
              <td colspan=\"2\" align=\"center\" style=\"border-radius: 10px 10px 0px 0px;\"><strong><font size=\"+2\">Form Surat Setoran Pajak Daerah <br>Bea Perolehan Hak Atas Tanah dan Bangunan <br>(SSPD-BPHTB)</font></strong>" . $txtReject . "
            </tr>
            
            <tr>
              <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>A. </b></font></div></td>
              <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                <tr>
                  <td width=\"2%\"><div align=\"right\">1.</div></td>
                  <td width=\"18%\">NOP PBB</td>
                  <td width=\"30%\"><input readonly type=\"text\" name=\"name2\" id=\"name2\" value=\"" . $nop . "\" onBlur=\"checkNOP(this);autoNOP(this);\" onKeyPress=\"return nextFocus(this, event)\" size=\"25\" title=\"NOP PBB\" \"/></td>
                  <td>Nama WP Lama : </td>
                  <td><input type=\"text\" name=\"nama-wp-lama\" id=\"nama-wp-lama\" value=\"" . $data->CPM_WP_NAMA_LAMA . "\" size=\"35\" maxlength=\"30\" title=\"Nama WP Lama\"/>
                  </td>           
                  </tr>
                <tr>
                  <td valign=\"top\"><div align=\"right\">2.</div></td>
                  <td valign=\"top\">Lokasi Objek Pajak</td>
                  <td><textarea  name=\"address2\" id=\"address2\" cols=\"35\" rows=\"4\" title=\"Lain-lain\">" . str_replace("<br />", "\n", $data->CPM_OP_LETAK) . "</textarea>
                  <td valign=\"top\">Nama WP Sesuai Sertifikat : </td>
                  <td valign=\"top\"><input type=\"text\" name=\"nama-wp-cert\" id=\"nama-wp-cert\" value=\"" . $data->CPM_WP_NAMA_CERT . "\" size=\"35\" maxlength=\"30\" title=\"Nama WP Sesuai Sertifikat\"/></td>                 
                </tr>
                <tr>
                  <td><div align=\"right\">3.</div></td>
                  <td>Kelurahan/Desa</td>
                  <td><input type=\"text\" name=\"kelurahan2\" id=\"kelurahan2\" value=\"" . $data->CPM_OP_KELURAHAN . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" title=\"Kelurahan Objek Pajak\"/></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><div align=\"right\">4.</div></td>
                  <td>RT/RW</td>
                  <td><input type=\"text\" name=\"rt2\" id=\"rt2\" maxlength=\"3\" size=\"3\" value=\"" . $data->CPM_OP_RT . "\" onKeyPress=\"return nextFocus(this, event)\" title=\"RT Objek Pajak\"/>
                    /
                    <input type=\"text\" name=\"rw2\" id=\"rw2\" maxlength=\"3\" size=\"3\" value=\"" . $data->CPM_OP_RW . "\" onKeyPress=\"return nextFocus(this, event)\" title=\"RW Wajib Pajak\"/></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><div align=\"right\">5.</div></td>
                  <td>Kecamatan</td>
                  <td><input type=\"text\" name=\"kecamatan2\" id=\"kecamatan2\" value=\"" . $data->CPM_OP_KECAMATAN . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" title=\"Kecamatan Objek Pajak\"/></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><div align=\"right\">6.</div></td>
                  <td>Kabupaten/Kota</td>
                  <td><input type=\"text\" name=\"kabupaten2\" id=\"kabupaten2\" value=\"" . $data->CPM_OP_KABUPATEN . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" title=\"Kabupaten/Kota Objek Pajak\"/></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><div align=\"right\">7.</div></td>
                  <td>Kode Pos</td>
                  <td><input type=\"text\" name=\"zip-code2\" id=\"zip-code2\" value=\"" . $data->CPM_OP_KODEPOS . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"5\" maxlength=\"5\" title=\"Kode Pos Objek Pajak\"/></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
              </table>
              
              </td>
            </tr>
            <tr>
              <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>B. </b></font></div></td>
              <td width=\"97%\"><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                <tr>
                  <td width=\"2%\"><div align=\"right\">1.</div></td>
                  <td>Nomor KTP</td>
                  <td><input type=\"text\" name=\"noktp\" id=\"noktp\" value=\"" . $data->CPM_WP_NOKTP . "\" onkeypress=\"return nextFocus(this,event)\" size=\"16\" maxlength=\"24\" title=\"No KTP wajib di isi\" onblur=\"checkTransLast();checkTransaksi();\"/></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><div align=\"right\">2.</div></td>
                  <td>NPWP</td>
                  <td><input type=\"text\" name=\"npwp\" id=\"npwp\" value=\"" . $data->CPM_WP_NPWP . "\" onkeypress=\"return nextFocus(this,event)\" size=\"15\" maxlength=\"15\" title=\"NPWP\"/></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><div align=\"right\">3.</div></td>
                  <td width=\"18%\">Nama Wajib Pajak</td>
                  <td width=\"40%\"><input type=\"text\" name=\"name\" id=\"name\" value=\"" . $data->CPM_WP_NAMA . "\" onkeypress=\"return nextFocus(this,event)\" size=\"60\" maxlength=\"60\" title=\"Nama Wajib Pajak\"/></td>
                   <td colspan=\"2\"><input type=\"hidden\" id=\"idssb-lama\" name =\"idssb-lama\" value=\"" . $idssb . "\"></td>
                </tr>
                <tr>
                  <td valign=\"top\"><div align=\"right\">4.</div></td>
                  <td valign=\"top\">Alamat Wajib Pajak</td>
                  <td><textarea  name=\"address\" id=\"address\" cols=\"35\" rows=\"4\" title=\"Alamat\">" . str_replace("<br />", "\n", $data->CPM_WP_ALAMAT) . "</textarea>
                  <td></td>
                  <td></td>
                </tr>
                <tr>
                  <td><div align=\"right\">5.</div></td>
                  <td>Kelurahan/Desa</td>
                  <td><input type=\"text\" name=\"kelurahan\" id=\"kelurahan\" value=\"" . $data->CPM_WP_KELURAHAN . "\" onkeypress=\"return nextFocus(this,event)\" size=\"20\" maxlength=\"20\" title=\"Kelurahan/Desa Wajib Pajak\"/></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><div align=\"right\">6.</div></td>
                  <td>RT/RW</td>
                  <td><input type=\"text\" name=\"rt\" id=\"rt\" maxlength=\"3\" size=\"3\"  value=\"" . $data->CPM_WP_RT . "\" onkeypress=\"return nextFocus(this,event)\" title=\"RT Wajib Pajak\"/>/<input type=\"text\" name=\"rw\" id=\"rw\" maxlength=\"3\" size=\"3\"  value=\"" . $data->CPM_WP_RW . "\" onkeypress=\"return nextFocus(this,event)\" title=\"RW Wajib Pajak\"/></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><div align=\"right\">7.</div></td>
                  <td>Kecamatan</td>
                  <td><input type=\"text\" name=\"kecamatan\" id=\"kecamatan\"  value=\"" . $data->CPM_WP_KECAMATAN . "\" onkeypress=\"return nextFocus(this,event)\" size=\"20\" maxlength=\"20\" title=\"Kecamatan Wajib Pajak\"/></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><div align=\"right\">8.</div></td>
                  <td>Kabupaten/Kota</td>
                  <td><input type=\"text\" name=\"kabupaten\" id=\"kabupaten\"  value=\"" . $data->CPM_WP_KABUPATEN . "\" onkeypress=\"return nextFocus(this,event)\"  size=\"20\" maxlength=\"20\" title=\"Kabupaten/Kota Wajib Pajak\"/></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><div align=\"right\">9.</div></td>
                  <td>Kode Pos</td>
                  <td><input type=\"text\" name=\"zip-code\" id=\"zip-code\"  value=\"" . $data->CPM_WP_KODEPOS . "\" onKeyPress=\"return numbersonly(this, event)\"  size=\"5\" maxlength=\"5\" title=\"Kode Pos Wajib Pajak\"/></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
              </table>
              <table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
              
                <tr>
                  <td width=\"14\"><div align=\"right\">15.</div></td>
                  <td colspan=\"2\">Jenis perolehan hak atas tanah atau bangunan</td>
                </tr>
                <tr>
                  <td><div align=\"right\"></div></td>
                  <td colspan=\"2\">".jenishak($typeR)."</td>
                <tr>
                <tr id=\"aphb\" ".$display_aphb.">
                  <td><div align=\"right\"></div></td>
                  <td colspan=\"2\">".aphb($APHB)."</td>
                <tr>
                    
                <tr>
                  <td><div align=\"right\">16.</div></td>
                  <td>Nomor Sertifikat</td>
                  <td><input type=\"text\" name=\"certificate-number\" id=\"certificate-number\" value=\"" . $data->CPM_OP_NMR_SERTIFIKAT . "\" size=\"30\" maxlength=\"30\" title=\"Nomor Sertifikat Tanah\"/></td>
                </tr>
              </table>
              <table width=\"900\" border=\"1\" cellspacing=\"1\" cellpadding=\"4\" style=\"border: 1px solid black;border-collapse: collapse;\" class=\"pure-table\"><thead>
          <tr>
            <td colspan=\"5\"><strong>Penghitungan NJOP PBB:</strong></th>
            </tr>
          <tr>
            <th width=\"77\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Objek pajak</th>
            <th width=\"176\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Diisi luas tanah atau bangunan yang haknya diperoleh</th>
            <th width=\"197\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Diisi
        berdasarkan SPPT PBB terakhir sebelum terjadinya peralihan hak 
              <input type=\"text\" name=\"right-year\" id=\"right-year\" maxlength=\"4\" size=\"4\" value=\"" . $data->CPM_OP_THN_PEROLEH . "\" onKeyPress=\"return numbersonly(this, event)\" title=\"Tahun Pajak\"/></th>
            <th colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Luas x NJOP PBB /m²</th>
            </tr>
            </thead>
          <tr>
            <td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Tanah / Bumi</td>
            <td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">7. Luas Tanah (Bumi)</td>
            <td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">9. NJOP Tanah (Bumi) /m²</td>
            <td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (7x9)</td>
            </tr>
          <tr>
            <td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input type=\"text\" name=\"land-area\" readonly id=\"land-area\" value=\"" . number_format(strval($data->CPM_OP_LUAS_TANAH), 0, '', '')  . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addSN();addET();checkTransaction();\" title=\"Luas Tanah\"/>
              m²</td>
            <td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input type=\"text\" name=\"land-njop\" readonly id=\"land-njop\" value=\"" . $data->CPM_OP_NJOP_TANAH . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addSN();addET();checkTransaction();\" title=\"NJOP Tanah\"/></td>
            <td width=\"28\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">11.</td>
            <td width=\"126\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\"  id=\"t1\">" . number_format(strval($data->CPM_OP_LUAS_TANAH) * strval($data->CPM_OP_NJOP_TANAH), 0, '.', ',') . "</td>
          </tr>
          <tr>
            <td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Bangunan</td>
            <td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">8. Luas Bangunan</td>
            <td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">10. NJOP Bangunan / m²</td>
            <td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (8x10)</td>
            </tr>
          <tr>
            <td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input type=\"text\" name=\"building-area\" readonly id=\"building-area\" value=\"" . number_format(strval($data->CPM_OP_LUAS_BANGUN ), 0, '', '') . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" title=\"Luas Bangunan\"/>
        m²</td>
            <td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input type=\"text\" name=\"building-njop\" readonly id=\"building-njop\" value=\"" . $data->CPM_OP_NJOP_BANGUN . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" title=\"NJOP Bangunan\"/></td>
            <td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">12.</td>
            <td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t2\">" . number_format(strval($data->CPM_OP_LUAS_BANGUN) * strval($data->CPM_OP_NJOP_BANGUN), 0, '.', ',') . "</td>
          </tr>
          <tr>
            <td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">NJOP PBB </td>
            <td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">13.</td>
            <td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t3\">" . number_format(strval($data->CPM_OP_LUAS_BANGUN) * strval($data->CPM_OP_NJOP_BANGUN) + strval($data->CPM_OP_LUAS_TANAH) * strval($data->CPM_OP_NJOP_TANAH), 0, '.', ',') . "</td>
          </tr>
           <tr>
            <td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">Harga Transaksi</td>
            <td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">14.</td>
            <td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t3\">
            <input type=\"text\" name=\"trans-value\" id=\"trans-value\" value=\"" . $data->CPM_OP_HARGA . "\" onKeyPress=\"return numbersonly(this, event)\"    onkeyup=\"checkTransaction()\" title=\"Harga Transaksi\"/></td>
          </tr>
              </table>
              </td>
            </tr>
            <tr style=\"display:none\">
              <td align=\"center\" valign=\"middle\"><strong><font size=\"+2\">C</font></strong></td>
              <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                <tr>
                  <td width=\"443\"><strong>AKUMULASI NILAI PEROLEHAN HAK SEBELUMNYA</strong></td>
                  <td width=\"188\" id=\"akumulasi\">" . number_format(strval($data->CPM_OP_NPOP), 0, '.', ',') . "</td></td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>C. </b></font></div></td>
              <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                  <tr>
                    <td width=\"443\"><strong>Penghitungan BPHTB</strong></td>
                    <td width=\"188\"><em><strong>Dalam rupiah</strong></em></td>
                  </tr>
                  <tr>
                    <td>Nilai Perolehan Objek Pajak (NPOP)</td>
                    <td id=\"xNJOP\" align=\"right\"><input type=\"text\" name=\"tNPOP\" id=\"tNPOP\"  value=\"" . number_format(getBPHTBPayment_all(0), 0, '.', ',') . "\"onKeyPress=\"return numbersonly(this, event)\"  onkeyup=\"checkTransaction();\" title=\"Nilai Perolehan Objek Pajak (NPOP)\"/></td>
                  </tr>
                  <tr>
                    <td>Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP) X</td>
                    <td id=\"xNPOPTKP\" align=\"right\"><input type=\"text\" name=\"tNPOPTKP\" id=\"tNPOPTKP\"  value=\"" . $data->CPM_OP_NPOPTKP . "\" onKeyPress=\"return numbersonly(this, event)\"  \" title=\"Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)\"/></td>
                  </tr>
                  <tr>
                    <td>Nilai Perolehan Objek Pajak Kena Pajak(NPOPKP)</td>
                    <td id=\"tNPOPKP\" align=\"right\">" . number_format(getBPHTBPayment_all(1), 0, '.', ',') . "</td>
                  </tr>
                  <tr>
                    <td>Bea Perolehan atas Hak Tanah dan Bangunan yang terhutang</td>
                    <td id=\"tBPHTBTS\" align=\"right\">" . number_format(getBPHTBPayment_all(2), 0, '.', ',') . "</td>
                  </tr>
                  <tr ".$display_aphb.">
                    <td>APHB &nbsp;&nbsp;
                    <td id=\"tAPHB\" align=\"right\">" . number_format(getBPHTBPayment_all(4), 0, '.', ',') . "</td>
                  </tr>
                  <tr ".$display_pengenaan.">
                    <td>Pengenaan Karena Waris/Hibah Wasiat/Pemberian Hak Pengelola &nbsp;&nbsp;<input style=\"border:none\" type=\"text\" id=\"Phibahwaris\" size=\"1\" name=\"Phibahwaris\" value=\"".$pengenaan."\" readonly=\"readonly\"/>%</td>
                    <td id=\"tPengenaan\" align=\"right\">" . number_format(getBPHTBPayment_all(3), 0, '.', ',') . "</td>
                  </tr>
                  ".$kena_denda."".$kena_denda2."
                  <tr>
                    <td>Bea Perolehan atas Hak Tanah dan Bangunan yang harusnya dibayar</td>
                    <td id=\"harusbayar\" align=\"right\">" . number_format(getBPHTBPayment_all(5), 0, '.', ',') . "</td>
                  </tr>
                  <tr>
                    <td>Nilai Pajak yang Dibayar sebelumnya</td>
                    <td id=\"xBPHTB_BAYAR\" align=\"right\"><input type=\"text\" name=\"tBPHTB_BAYAR\" id=\"tBPHTB_BAYAR\"  value=\"" . $bphtb_before . "\" onKeyPress=\"return numbersonly(this, event);\" onkeyup=\"checkTransaction();\" title=\"BPHTB sebelumnya\" /></td>
                  </tr>
                  <tr>
                    <td>Bea Perolehan atas Hak Tanah dan Bangunan Kurang Bayar</td>
                    <td id=\"xBPHTBT\" align=\"right\"><input type=\"text\" name=\"bphtbtu\" id=\"tBPHTBTU\" value=\"" . $data->CPM_KURANG_BAYAR . "\" onKeyPress=\"return numbersonly(this, event)\"  onkeyup=\"\" title=\"Bea Perolehan atas Hak Tanah dan Bangunan yang terutang\" readonly=\"readonly\"/></td>
                  </tr>
              </table></td>
            </tr>
            <tr>
              <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>D. </b></font></div></td>
              <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                <tr>
                  <td colspan=\"3\"><strong>Jumlah Setoran Berdasarkan : </strong></td>
                  </tr>
                <tr>
                  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"2\" id=\"RadioGroup1_1\" onclick=\"enableE(this,1);\" ".$c2." /></td>
                  <td align=\"right\" valign=\"top\"></td>
                  <td valign=\"top\"><select name=\"jsb-choose\" id=\"jsb-choose\" " . $r2 . ">
                    <!-- <option " . $sel1 . " >STPD BPHTB</option> -->
                    <option value=\"".$sel2."\" >SKPD Kurang Bayar</option>
                    <option value=\"".$sel3."\" >SKPD Kurang Bayar Tambahan</option>
                  </select></td>
                </tr>
                <tr>
                  <td align=\"center\" valign=\"top\">&nbsp;</td>
                  <td align=\"right\" valign=\"top\">&nbsp;</td>
                  <td valign=\"top\">Nomor : 
                    <input type=\"text\" name=\"jsb-choose-number\" id=\"jsb-choose-number\" size=\"30\" maxlength=\"30\" value=\"" . $data->CPM_NO_KURANG_BAYAR . "\" title=\"Nomor Surat Pengurangan\" readonly=\"readonly\"/></td>
                </tr>
                <tr>
                  <td align=\"center\" valign=\"top\">&nbsp;</td>
                  <td align=\"right\" valign=\"top\">&nbsp;</td>
                  <td valign=\"top\">Tanggal : 
                    <input type=\"text\" name=\"jsb-choose-date\" id=\"jsb-choose-date\" datepicker=\"true\" value=\"" . $data->CPM_PAYMENT_TIPE_SURAT_TANGGAL . "\" datepicker_format=\"DD/MM/YYYY\" readonly=\"readonly\" size=\"10\" maxlength=\"10\" value=\"" . date("d/m/Y") . "\" title=\"Tanggal Surat Pengurangan\"/></td>
                </tr>
                
              </table></td>
            </tr>
            <tr>
            <td colspan=\"2\" align=\"center\" valign=\"middle\" id=\"jmlBayar\">Jumlah yang dibayarkan : </td>
            </tr>
            <tr><input type=\"hidden\" id=\"jsb-total-before\" name=\"jsb-total-before\"><input type=\"hidden\" id=\"hd-npoptkp\" name=\"hd-npoptkp\">
            <input type=\"hidden\" id=\"reject-data\" name =\"reject-data\" value=\"" . $rej . "\">
            <input type=\"hidden\" id=\"ver-doc\" value=\"" . $data->CPM_TRAN_SSB_VERSION . "\" name=\"ver-doc\">
            <input type=\"hidden\" id=\"trsid\" value=\"" . $data->CPM_TRAN_ID . "\" name=\"trsid\">
            <input type=\"hidden\" value=\"" . $data->CPM_OP_ZNT . "\" id=\"znt\" name=\"op-znt\">
            <input type=\"hidden\" value=\"" . $data->CPM_OP_BPHTB_TU . "\" id=\"bphtbtu\" name=\"tBPHTBTU\">
            <td colspan=\"2\" align=\"center\" valign=\"middle\"><input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan\" class=\"button-success pure-button\"/>
              &nbsp;&nbsp;&nbsp;
              <input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan dan Finalkan\" class=\"button-success pure-button\"/></td>
            </tr>
            <tr>
              <td colspan=\"2\" align=\"center\" valign=\"middle\">&nbsp;</td>
            </tr>
          </table>
        </form></div>";
    return $html;
}

function validation($str, &$err) {
    $OK = true;
    $j = count($str);
    $err = "";
    for ($i = 0; $i < $j; $i++) {
        if (($i != 31) && ($i != 32) && ($i != 33) && ($i != 34) && ($i != 35) && ($i != 37) && ($i != 39)) {
            if ((substr($str[$i], 0, 5) == 'Error') || ($str[$i] == "")) {
                $err .= $str[$i] . "<br>\n";
                $OK = false; //print_r("1 $i");
            }
        }
        if ($str[30] == 2) {
            if (($i == 31) || ($i == 32) || ($i == 33)) {
                if ((substr($str[$i], 0, 5) == 'Error') || ($str[$i] == "")) {
                    $err .= $str[$i] . "<br>\n";
                    $OK = false; //print_r("2");
                }
            }
        }
        if ($str[30] == 3) {
            if (($i == 39) || ($i == 37)) {
                if ((substr($str[$i], 0, 5) == 'Error') || ($str[$i] == "")) {
                    $err .= $str[$i] . "<br>\n";
                    $OK = false; //print_r("3");
                }
            }
        }
        if ($str[30] == 4) {
            if ($i == 35) {
                if ((substr($str[$i], 0, 5) == 'Error') || ($str[$i] == "")) {
                    $err .= $str[$i] . "<br>\n";
                    $OK = false;
                    //print_r("4");
                }
            }
        }
    }

    return $OK;
}

function getBPHTBPayment($lb,$nb,$lt,$nt,$h,$p,$jh,$NPOPTKP,$phw,$aphbt,$denda) {
        //$a = $_REQUEST['a'];
        /*$NPOPTKP =  $this->getConfigValue($a,'NPOPTKP_STANDAR');
        
        $typeR = $jh;
        
        if (($typeR==4) || ($typeR==6)){
            $NPOPTKP =  $this->getConfigValue($a,'NPOPTKP_WARIS');
        } else {
            
        }*/
        
        /*if($this->getNOKTP($noktp,$nop,$tgl)) {   
            $NPOPTKP = 0;
        }*/
        $configAPHB = getConfigValue("aBPHTB",'CONFIG_APHB');
        $hitungaphb = getConfigValue("aBPHTB",'HITUNG_APHB');
        $configPengenaan = getConfigValue("aBPHTB",'CONFIG_PENGENAAN');
        $a = strval($lb)*strval($nb)+strval($lt)*strval($nt);
        $b = strval($h);
        $npop = 0;
        if($jh=='15'){
            $npop=$b;
        }else{
            if ($b <= $a) $npop = $a; else $npop = $b;
        }
        $npkp = $npop-strval($NPOPTKP);
        $jmlByr = ($npop-strval($NPOPTKP))*0.05;
        $hbphtb = ($npop-strval($NPOPTKP))*0.05;
        $aphb=0;
        $hbphtb_pengenaan = 0;
        $hbphtb_aphb = 0;
        if(($jh==4)||($jh==5)||($jh==31)){
            if($configPengenaan=='1'){
                $hbphtb_pengenaan = ($hbphtb * $phw * 0.01);
                $jmlByr= $hbphtb-($hbphtb_pengenaan);
            }else{
                $hbphtb_pengenaan = 0;
                $jmlByr= $hbphtb;
            }
            
        }else if($jh==7){
            if($configAPHB=='1'){
                $p=explode("/",$aphbt);
                $aphb=$p[0]/$p[1];
                $hbphtb_pengenaan = 0;
                if($hitungaphb=='1'){
                    $hbphtb_aphb = ($npop-strval($NPOPTKP))*0.05 * $aphb;
                }else if($hitungaphb=='2'){
                    $hbphtb_aphb = (($npop-strval($NPOPTKP))*0.05)-(($npop-strval($NPOPTKP))*0.05 * $aphb);
                }else if($hitungaphb=='3'){
                    $hbphtb_aphb = ($npop*$aphb)-(strval($NPOPTKP)* 0.05);
                }else if($hitungaphb=='0'){
                    $hbphtb_aphb = ($npop-strval($NPOPTKP))*0.05;
                }
            }else{
                $hbphtb_aphb = ($npop-strval($NPOPTKP))*0.05;
            }
                $jmlByr= $hbphtb_aphb;
        }
        $total_temp = $jmlByr;
        $tp = strval($p);
        if ($tp!=0) $jmlByr = $jmlByr-($jmlByr*($tp*0.01));
        
        if($denda>0){
            $jmlByr = $jmlByr+$denda;
        }else{
            $jmlByr = $jmlByr;
        }
        if ($jmlByr < 0) $jmlByr = 0;
        return $jmlByr;
    }
function getBPHTBPayment_all($no) {
        global $data;
        $hitungaphb = getConfigValue("aBPHTB",'HITUNG_APHB');
        $lb = $data->CPM_OP_LUAS_BANGUN;
        $nb = $data->CPM_OP_NJOP_BANGUN;
        $lt = $data->CPM_OP_LUAS_TANAH;
        $nt = $data->CPM_OP_NJOP_TANAH;
        $h  = $data->CPM_OP_HARGA;
        $p  = $data->CPM_PAYMENT_TIPE_PENGURANGAN;
        $jh = $data->CPM_OP_JENIS_HAK;
        $NPOPTKP = $data->CPM_OP_NPOPTKP;
        $phw = $data->CPM_PENGENAAN;
        $denda = $data->CPM_DENDA;
        $aphbt = $data->CPM_APHB;
        
        $a = strval($lb)*strval($nb)+strval($lt)*strval($nt);
        $b = strval($h);
        $npop = 0;
        if($jh=='15'){
            $npop=$b;
        }else{
            if ($b <= $a) $npop = $a; else $npop = $b;
        }
        $npkp = $npop-strval($NPOPTKP);
        if($npkp<=0){
            $npkp = 0;
        }
        $jmlByr = ($npop-strval($NPOPTKP))*0.05;
        $hbphtb = ($npop-strval($NPOPTKP))*0.05;
        $aphb=0;
        $hbphtb_pengenaan = 0;
        $hbphtb_aphb = 0;
        if(($jh==4)||($jh==5)||($jh==31)){
            $hbphtb_pengenaan = ($hbphtb * $phw * 0.01);
            $jmlByr= $hbphtb-($hbphtb_pengenaan);
            
        }else if($jh==7){
            $p=explode("/",$aphbt);
            $aphb=$p[0]/$p[1];
            $hbphtb_pengenaan = 0;
            if($hitungaphb=='1'){
                $hbphtb_aphb = ($npop-strval($NPOPTKP))*0.05 * $aphb;
            }else if($hitungaphb=='2'){
                $hbphtb_aphb = (($npop-strval($NPOPTKP))*0.05)-(($npop-strval($NPOPTKP))*0.05 * $aphb);
            }else if($hitungaphb=='3'){
                $hbphtb = $npop*$aphb;
                $hbphtb_aphb = ($hbphtb-strval($NPOPTKP))* 0.05;
            }else if($hitungaphb=='0'){
                $hbphtb_aphb = ($npop-strval($NPOPTKP))*0.05;
            }
            $jmlByr= $hbphtb_aphb;
        }else if($jh==16){
            $p=explode("/",$aphbt);
            $aphb=$p[0]/$p[1];
            $hbphtb_pengenaan = ($hbphtb * $phw * 0.01);
            $hbphtb_aphb = ($npop-strval($NPOPTKP))*0.05 * $aphb;
            $jmlByr= $hbphtb-$hbphtb_aphb-$hbphtb_pengenaan;
        }
        
        $tp = strval($p);
        if ($tp!=0) $jmlByr = $jmlByr-($jmlByr*($tp*0.01));
        
        if($denda>0){
            $jmlByr = $jmlByr+$denda;
        }else{
            $jmlByr = $jmlByr;
        }
        
        if ($jmlByr < 0){
            $jmlByr = 0;
            $hbphtb = 0;
        }
        $total_temp = $jmlByr;
        $hasil = $npop.",".$npkp.",".$hbphtb.",".$hbphtb_pengenaan.",".$hbphtb_aphb.",".$total_temp.",".$jmlByr;
        $pilihhitung=explode(",",$hasil);
        
        //echo $hasil;exit;
        return $pilihhitung[$no];
    }
function save($final, $x) {

    global $data, $DBLink;
    $dat = $data;
    //print_r($_REQUEST);
    $data = array();
    $data[0] = "-";
    $data[1] = "-";
    $cpm_wp_nama = $data[2] = @isset($_REQUEST['name']) ? $_REQUEST['name'] : "Error: Nama Wajib Pajak tidak boleh dikosongkan!";
    $data[3] = @isset($_REQUEST['npwp']) ? $_REQUEST['npwp'] : "Error: NPWP tidak boleh dikosongkan!";
    $data[4] = @isset($_REQUEST['address']) ? $_REQUEST['address'] : "Error: Alamat tidak boleh dikosongkan!";
    $data[5] = "-";
    $data[6] = @isset($_REQUEST['kelurahan']) ? $_REQUEST['kelurahan'] : "Error: Kelurahan tidak boleh dikosongkan!";
    $data[7] = @isset($_REQUEST['rt']) ? $_REQUEST['rt'] : "Error: RT tidak boleh dikosongkan!";
    $data[8] = @isset($_REQUEST['rw']) ? $_REQUEST['rw'] : "Error: RW tidak boleh dikosongkan!";
    $data[9] = @isset($_REQUEST['kecamatan']) ? $_REQUEST['kecamatan'] : "Error: Kecamatan tidak boleh dikosongkan!";
    $data[10] = @isset($_REQUEST['kabupaten']) ? $_REQUEST['kabupaten'] : "Error: Kabupaten tidak boleh dikosongkan!";
    $data[11] = @isset($_REQUEST['zip-code']) ? $_REQUEST['zip-code'] : "Error: Kode POS tidak boleh dikosongkan!";
    $cpm_op_nomor = $data[12] = @isset($_REQUEST['name2']) ? $_REQUEST['name2'] : "Error: NOP PBB tidak boleh dikosongkan!";
    $data[13] = @isset($_REQUEST['address2']) ? $_REQUEST['address2'] : "Error: Alamat Objek Pajak tidak boleh dikosongkan!";
    $data[14] = "-";
    $data[15] = @isset($_REQUEST['kelurahan2']) ? $_REQUEST['kelurahan2'] : "Error: Kelurahan Objek Pajak tidak boleh dikosongkan!";
    $data[16] = @isset($_REQUEST['rt2']) ? $_REQUEST['rt2'] : "Error: RT Objek Pajak tidak boleh dikosongkan!";
    $data[17] = @isset($_REQUEST['rw2']) ? $_REQUEST['rw2'] : "Error: RW Objek Pajak tidak boleh dikosongkan!";
    $data[18] = @isset($_REQUEST['kecamatan2']) ? $_REQUEST['kecamatan2'] : "Error: Kecamatan Objek Pajak tidak boleh dikosongkan!";
    $data[19] = @isset($_REQUEST['kabupaten2']) ? $_REQUEST['kabupaten2'] : "Error: Kabupaten Objek Pajak tidak boleh dikosongkan!";
    $data[20] = @isset($_REQUEST['zip-code2']) ? $_REQUEST['zip-code2'] : "Error: Kode POS Objek Pajak tidak boleh dikosongkan!";
    $data[21] = @isset($_REQUEST['right-year']) ? $_REQUEST['right-year'] : "Error: Tahun SPPT PBB tidak boleh dikosongkan!";
    $data[22] = @isset($_REQUEST['land-area']) ? $_REQUEST['land-area'] : "Error: Luas Tanah tidak boleh dikosongkan!";
    $data[23] = @isset($_REQUEST['land-njop']) ? $_REQUEST['land-njop'] : "Error: NJOP Tanah tidak boleh dikosongkan!";
    $data[24] = @isset($_REQUEST['building-area']) ? $_REQUEST['building-area'] : "Error: Luas Bangunan tidak boleh dikosongkan!";
    $data[25] = @isset($_REQUEST['building-njop']) ? $_REQUEST['building-njop'] : "Error: NJOP Bangunan tidak boleh dikosongkan!";
    $data[26] = @isset($_REQUEST['right-land-build']) ? $_REQUEST['right-land-build'] : "";
    $data[27] = @isset($_REQUEST['trans-value']) ? $_REQUEST['trans-value'] : "Error: Harga transasksi tidak boleh dikosongkan!";
    $data[28] = @isset($_REQUEST['certificate-number']) ? $_REQUEST['certificate-number'] : "Error: Nomor sertifikat tidak boleh dikosongkan!";
    $vNPOPTKP = @isset($_REQUEST['tNPOPTKP']) ? $_REQUEST['tNPOPTKP'] : "";
    $data[29] = @isset($_REQUEST['hd-npoptkp']) ? ($vNPOPTKP ? $vNPOPTKP : $_REQUEST['hd-npoptkp']) : "";
    $data[30] = @isset($_REQUEST['RadioGroup1']) ? $_REQUEST['RadioGroup1'] : "Error: Pilihan Jumlah Setoran tidak dipilih!";
    $data[31] = @isset($_REQUEST['jsb-choose']) ? $_REQUEST['jsb-choose'] : "Error: Pilihan jenis tidak dipilih!";
    $data[32] = @isset($_REQUEST['jsb-choose-number']) ? $_REQUEST['jsb-choose-number'] : "Error: Nomor surat tidak boleh dikosongkan!";
    $data[33] = @isset($_REQUEST['jsb-choose-date']) ? $_REQUEST['jsb-choose-date'] : "Error: Tanggal surat tidak boleh dikosongkan!";
    $data[34] = "-"; //$_REQUEST['pdsk-choose']? $_REQUEST['pdsk-choose']:"Error: Pengurangan tidak dipilih!";
    $data[35] = @isset($_REQUEST['jsb-etc']) ? $_REQUEST['jsb-etc'] : "Error: Keterangan lain-lain tidak boleh dikosongkan!";
    $data[36] = @isset($_REQUEST['jsb-total-before']) ? $_REQUEST['jsb-total-before'] : "Error: Akumulasi nilai perolehan hak sebelumnya tidak boleh di kosongkan!";
    $data[37] = @isset($_REQUEST['jsb-choose-role-number']) ? $_REQUEST['jsb-choose-role-number'] : "Error: No Aturan KHD tidak boleh di kosongkan!";
    $data[38] = @isset($_REQUEST['noktp']) ? $_REQUEST['noktp'] : "Error: Nomor KTP tidak boleh dikosongkan!";
    $data[39] = @isset($_REQUEST['jsb-choose-percent']) ? $_REQUEST['jsb-choose-percent'] : "0";
    $data[40] = @isset($_REQUEST['tBPHTBTU']) ? $_REQUEST['tBPHTBTU'] : "0";
    $data[41] = @isset($_REQUEST['nama-wp-lama']) ? $_REQUEST['nama-wp-lama'] : "Error: Nama WP lama tidak boleh di kosongkan!";
    $data[42] = @isset($_REQUEST['nama-wp-cert']) ? $_REQUEST['nama-wp-cert'] : "Error: Nama WP Sesuai Sertifikat tidak boleh di kosongkan!";
    
    $data[43] = @isset($_REQUEST['jsb-choose-fraction1']) ? $_REQUEST['jsb-choose-fraction1'] : "1";
    $data[44] = @isset($_REQUEST['jsb-choose-fraction2']) ? $_REQUEST['jsb-choose-fraction2'] : "1";
    $data[45] = @isset($_REQUEST['op-znt']) ? $_REQUEST['op-znt'] : "";
    $data[46] = @isset($_REQUEST['pengurangan-aphb']) ? $_REQUEST['pengurangan-aphb'] : "1";
    $data[47] = @isset($_REQUEST['Koordinat']) ? $_REQUEST['Koordinat'] : "1";
    $denda = @isset($_REQUEST['denda-value'])? $_REQUEST['denda-value']:"0";
    $pdenda = @isset($_REQUEST['denda-percent'])? $_REQUEST['denda-percent']:"0";
    
    if (($data[29] == "0") || ($data[29] == 0)) {
                if (!getNOKTP($_REQUEST['noktp'])) {
                    //print_r($_REQUEST['right-land-build']);
                    if ($_REQUEST['right-land-build'] == 5) {
                        $data[29] = getConfigValue('NPOPTKP_WARIS');
                    } else if (($_REQUEST['right-land-build'] == 30) || ($_REQUEST['right-land-build'] == 31)|| ($_REQUEST['right-land-build'] == 32)|| ($_REQUEST['right-land-build'] == 33)) {
                        $data[29] = 0;
                    }else{
                        $data[29] = getConfigValue('NPOPTKP_STANDAR');
                    }
                }

                if (intval($_REQUEST['right-land-build']) == 5) {
                    print_r($_REQUEST['right-land-build']);
                    $data[29] = getConfigValue('NPOPTKP_WARIS');
                }
            }
    
    $pAPHB="";
    if(($_REQUEST['right-land-build']==33) || ($_REQUEST['right-land-build']==7)){
        $pAPHB=$data[46];
    }else{
        $pAPHB="";
    }
    
    $typeSurat = '';
    $typeSuratNomor = '';
    $typeSuratTanggal = '';
    $typePengurangan = '';
    $typeLainnya = '';
    $trdate = date("Y-m-d H:i:s");
    $opr = $dat->uname;
    $version = $_REQUEST['ver-doc'];
    $nokhd = "";
    $pengurangansplit=explode(".",$data[39]);
    $pengurangan=$pengurangansplit[1];
    $kdpengurangan=$pengurangansplit[0];
    if ($data[30] == 2) {
        $typeSurat = $data[31];
        $typeSuratNomor = $data[32];
        $typeSuratTanggal = $data[33];
    } else if ($data[30] == 3) {
        $typePengurangan = $data[34];
        $nokhd = $data[37];
    } else if ($data[30] == 4) {
        $typeLainnya = $data[35];
    }else if ($data[30] == 5) {
        $typePecahan  = $data[43]."/".$data[44];
    }
    if($data[40]<0){
        $data[40]=0;
    }
        $pengenaan =0;
        if (($_REQUEST['right-land-build'] == 5)||($_REQUEST['right-land-build'] == 3)||($_REQUEST['right-land-build'] == 4)||($_REQUEST['right-land-build'] == 31)) {
            $pengenaan = getConfigValue("1",'PENGENAAN_HIBAH_WARIS');
        }
    $bphtb_sebelum=@isset($_REQUEST['tBPHTB_BAYAR']) ? $_REQUEST['tBPHTB_BAYAR'] : 0;
    $kurang_bayar = @isset($_REQUEST['bphtbtu']) ? $_REQUEST['bphtbtu'] : "0";
    if($data[30] == 2){
        $ccc = $kurang_bayar;
    }else{
        $ccc = getBPHTBPayment($data[24],$data[25],$data[22],$data[23],$data[27],$pengurangan,$data[26],$data[29], $pengenaan, $aphbt, $denda);
    }
        //  if (validation($data,$err)) {
    /*
      $data[22] = $_REQUEST['land-area']? $_REQUEST['land-area']:"Error: Luas Tanah tidak boleh dikosongkan!";
      $data[23] = $_REQUEST['land-njop']? $_REQUEST['land-njop']:"Error: NJOP Tanah tidak boleh dikosongkan!";
      $data[24] = $_REQUEST['building-area']? $_REQUEST['building-area']:"Error: Luas Bangunan tidak boleh dikosongkan!";
      $data[25] = $_REQUEST['building-njop']? $_REQUEST['building-njop']:"Error: NJOP Bangunan tidak boleh dikosongkan!";
     */
    $query = sprintf("UPDATE cppmod_ssb_doc SET CPM_KPP ='%s',CPM_KPP_ID ='%s',CPM_WP_NAMA ='%s',CPM_WP_NPWP ='%s',CPM_WP_ALAMAT ='%s',
        CPM_WP_RT='%s',CPM_WP_RW='%s',CPM_WP_KELURAHAN='%s',CPM_WP_KECAMATAN='%s',CPM_WP_KABUPATEN='%s',CPM_WP_KODEPOS='%s',
        CPM_OP_NOMOR='%s',CPM_OP_LETAK='%s',CPM_OP_RT='%s',CPM_OP_RW='%s',CPM_OP_KELURAHAN='%s',CPM_OP_KECAMATAN='%s',CPM_OP_KABUPATEN='%s',
        CPM_OP_KODEPOS='%s',CPM_OP_THN_PEROLEH='%s',CPM_OP_LUAS_TANAH='%s',CPM_OP_LUAS_BANGUN='%s',CPM_OP_NJOP_TANAH='%s',CPM_OP_NJOP_BANGUN='%s',
        CPM_OP_JENIS_HAK='%s',CPM_OP_HARGA='%s',CPM_OP_NMR_SERTIFIKAT='%s',CPM_OP_NPOPTKP='%s',CPM_PAYMENT_TIPE='%s',       
        CPM_PAYMENT_TIPE_SURAT='%s',CPM_PAYMENT_TIPE_SURAT_NOMOR='%s',CPM_PAYMENT_TIPE_SURAT_TANGGAL='%s',CPM_PAYMENT_TIPE_PENGURANGAN='%s',
        CPM_PAYMENT_TIPE_OTHER='%s',CPM_SSB_CREATED='%s',CPM_SSB_AUTHOR='%s',CPM_SSB_VERSION='%s',CPM_SSB_AKUMULASI='%s',CPM_PAYMENT_TIPE_KHD_NOMOR='%s',CPM_WP_NOKTP='%s',
        CPM_OP_BPHTB_TU='%s',CPM_WP_NAMA_LAMA='%s',CPM_WP_NAMA_CERT='%s', CPM_PAYMENT_TIPE_PECAHAN='%s', CPM_PAYMENT_TYPE_KODE_PENGURANGAN='%s' , CPM_OP_ZNT='%s', CPM_PENGENAAN='%s', CPM_APHB='%s',CPM_KURANG_BAYAR_SEBELUM='%s',CPM_KURANG_BAYAR='%s', CPM_DENDA='%s',CPM_PERSEN_DENDA='%s',CPM_BPHTB_BAYAR='%s',KOORDINAT='%s'
        WHERE CPM_SSB_ID ='%s'", '', '', mysqli_real_escape_string($DBLink, $data[2]), mysqli_real_escape_string($DBLink, $data[3]), mysqli_real_escape_string($DBLink, nl2br($data[4])), mysqli_real_escape_string($DBLink, $data[7]), mysqli_real_escape_string($DBLink, $data[8]), mysqli_real_escape_string($DBLink, $data[6]), mysqli_real_escape_string($DBLink, $data[9]), mysqli_real_escape_string($DBLink, $data[10]), mysqli_real_escape_string($DBLink, $data[11]), mysqli_real_escape_string($DBLink, $data[12]), mysqli_real_escape_string($DBLink, nl2br($data[13])), mysqli_real_escape_string($DBLink, $data[16]), mysqli_real_escape_string($DBLink, $data[17]), mysqli_real_escape_string($DBLink, $data[15]), mysqli_real_escape_string($DBLink, $data[18]), mysqli_real_escape_string($DBLink, $data[19]), mysqli_real_escape_string($DBLink, $data[20]), mysqli_real_escape_string($DBLink, $data[21]), mysqli_real_escape_string($DBLink, $data[22]), mysqli_real_escape_string($DBLink, $data[24]), mysqli_real_escape_string($DBLink, $data[23]), mysqli_real_escape_string($DBLink, $data[25]), mysqli_real_escape_string($DBLink, $data[26]), mysqli_real_escape_string($DBLink, $data[27]), mysqli_real_escape_string($DBLink, $data[28]), mysqli_real_escape_string($DBLink, $data[29]), mysqli_real_escape_string($DBLink, $data[30]), mysqli_real_escape_string($DBLink, $typeSurat), mysqli_real_escape_string($DBLink, $typeSuratNomor), mysqli_real_escape_string($DBLink, $typeSuratTanggal), mysqli_real_escape_string($DBLink, $pengurangan), mysqli_real_escape_string($DBLink, $typeLainnya), mysqli_real_escape_string($DBLink, $trdate), mysqli_real_escape_string($DBLink, $opr), mysqli_real_escape_string($DBLink, $version), mysqli_real_escape_string($DBLink, $data[36]), mysqli_real_escape_string($DBLink, $nokhd), mysqli_real_escape_string($DBLink, $data[38]), mysqli_real_escape_string($DBLink, $data[40]), mysqli_real_escape_string($DBLink, $data[41]), mysqli_real_escape_string($DBLink, $data[42]),mysqli_real_escape_string($DBLink, $typePecahan), mysqli_real_escape_string($DBLink, $kdpengurangan), mysqli_real_escape_string($DBLink, $data[45]),$pengenaan,$pAPHB,$bphtb_sebelum,$kurang_bayar, $denda, $pdenda, $ccc, mysqli_real_escape_string($DBLink, $data[47]),mysqli_real_escape_string($DBLink, $x));
     //echo $query;exit;
    $result = mysqli_query($DBLink, $query);

    /* echo $query;
      print_r($data);
      print_r($_REQUEST); */

    if ($result === false) {
        //handle the error here
        echo "Error 1" . mysqli_error($DBLink);
    }
    
    if($final==2){
            save_berkas($x);
        }
    
    $query = sprintf("UPDATE cppmod_ssb_tranmain SET CPM_TRAN_STATUS='%s', CPM_TRAN_FLAG='%s', CPM_TRAN_DATE='%s', 
        CPM_TRAN_OPR_NOTARIS='%s' WHERE CPM_TRAN_ID='%s'", "1", "1", mysqli_real_escape_string($DBLink, $trdate), mysqli_real_escape_string($DBLink, $opr), mysqli_real_escape_string($DBLink, $_REQUEST['trsid']));
    $result = mysqli_query($DBLink, $query);

    $idtran = c_uuid();
    $refnum = c_uuid();

    $query = sprintf("INSERT INTO cppmod_ssb_tranmain (CPM_TRAN_ID,CPM_TRAN_REFNUM,CPM_TRAN_SSB_ID,CPM_TRAN_SSB_VERSION,CPM_TRAN_STATUS,CPM_TRAN_FLAG,
        CPM_TRAN_DATE,CPM_TRAN_CLAIM,CPM_TRAN_OPR_NOTARIS,CPM_TRAN_OPR_DISPENDA_1,CPM_TRAN_OPR_DISPENDA_2,CPM_TRAN_INFO) 
        VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')", $idtran, $refnum, $x, $version, $final, '0', mysqli_real_escape_string($DBLink, $trdate), '', mysqli_real_escape_string($DBLink, $opr), '', '', '');
    $result = mysqli_query($DBLink, $query);

    if ($result === false) {
        //handle the error here
        echo "Error 2" . mysqli_error($DBLink);
    } else {
        
        $action = ($final==1)? 3 : 4; #simpan draf : finalkan draf
        $log_update = "insert into cppmod_ssb_log(
                                    CPM_SSB_ID,
                                    CPM_SSB_LOG_ACTOR,
                                    CPM_SSB_LOG_ACTION,
                                    CPM_OP_NOMOR,
                                    CPM_WP_NAMA,
                                    CPM_SSB_AUTHOR) 
                            values ('" . mysqli_real_escape_string($DBLink, $x) . "',
                                    '" . mysqli_real_escape_string($DBLink, $opr) . "',                                   
                                    '" . mysqli_real_escape_string($DBLink, $action) . "',
                                    '" . mysqli_real_escape_string($DBLink, $cpm_op_nomor) . "',
                                    '" . mysqli_real_escape_string($DBLink, $cpm_wp_nama) . "',
                                    '" . mysqli_real_escape_string($DBLink, $opr)."')"; 
       
        mysqli_query($DBLink, $log_update);
             
        $getdt = "select * FROM cppmod_ssb_berkas WHERE CPM_SSB_DOC_ID='".$x."'";
        // die(mysqli_real_escape_string($DBLink, $iddoc));

        $resultw = mysqli_query($DBLink, $getdt);
        if ($resultw === false)
            echo mysqli_error('error 1');
        $rows = mysqli_fetch_array($resultw);
        echo "Data Berhasil disimpan ...! ";
        if ($action==3) {
            $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&n=4";
        }
        else{
            $params = "a=" . $_REQUEST['a'] . "&m=mUploadBerkas&f=fUploadBerkas&tab=0&svcid=".$rows['CPM_BERKAS_ID'];
        }

        $address = $_SERVER['HTTP_HOST'] . "payment/pc/svr/central/main.php?param=" . base64_encode($params);
        echo "\n<script language=\"javascript\">\n";
        echo "  function delayer(){\n";
        echo "      window.location = \"./main.php?param=" . base64_encode($params) . "\"\n";
        echo "  }\n";
        echo "  Ext.onReady(function(){\n";
        echo "      setTimeout('delayer()', 2000);\n";
        echo "  });\n";
        echo "</script>\n";
    }

//  } else {
    //  echo "Kesalahan ".$err;
    //echo formSSB($data,true);
//  }
}

getSelectedData($_REQUEST['idssb'], $xdata);

$save = @isset($_REQUEST['btn-save']) ? $_REQUEST['btn-save'] : "";
//print_r($_REQUEST);
if ($save == 'Simpan') {
    save(1, $xdata->data[0]->CPM_SSB_ID);
} else if ($save == 'Simpan dan Finalkan') {
    if(getConfigValue("1", 'VERIFIKASI')!='1'){
       save(3, $xdata->data[0]->CPM_SSB_ID);
    }else{
        save(2, $xdata->data[0]->CPM_SSB_ID);
    }
} else {

    for ($i = 0; $i < count($xdata->data); $i++) {
        if (base64_encode($xdata->data[$i]->CPM_SSB_ID) == base64_encode($_REQUEST['idssb'])) {
            echo "<script language=\"javascript\">var axx = '" . base64_encode($_REQUEST['a']) . "'</script> ";
            if ($xdata->data[$i]->CPM_PAYMENT_TIPE != "2")
                // echo formSSB($xdata->data[$i], true);
            else
                echo formSSBKB($xdata->data[$i]);
        }
    }
}
// echo "asdasdas";
?>