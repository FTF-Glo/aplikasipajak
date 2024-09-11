<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'berkas', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");

echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery.ui.button.js\"></script>";

echo "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";
echo "<script src=\"function/BPHTB/berkas/jquery.validate.min.js\"></script>\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<link rel=\"stylesheet\" href=\"function/BPHTB/berkas/func-mod-pelayanan.css\" type=\"text/css\">\n";

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

function getConfigValue($id, $key) {
    global $DBLink;
    $qry = "select * from central_app_config where CTR_AC_AID = '" . $id . "' and CTR_AC_KEY = '$key'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['CTR_AC_VALUE'];
    }
}

function jenishak($js){
	global $DBLink;
	
	$texthtml= "<select name=\"jnsPerolehan\" style=\"width:250px;height: 30px;\"  id=\"jnsper\" onchange='cleancheckbox();javascript:showJnsPerolehan(this);'>";
	$qry = "select * from cppmod_ssb_jenis_hak ORDER BY CPM_KD_JENIS_HAK asc";
					//echo $qry;exit;
					$res = mysqli_query($DBLink, $qry);
					$texthtml .="<option value=\"0\" />Pilih Jenis Hak</option>";
						while($data = mysqli_fetch_assoc($res)){
							if(($js!=$data['CPM_KD_JENIS_HAK'])||($js=="")){
								$selected= "";
							}else{
								$selected= "selected";
							}
							$texthtml .= "<option value=\"".$data['CPM_KD_JENIS_HAK']."\" ".$selected." >".str_pad($data['CPM_KD_JENIS_HAK'],2,"0",STR_PAD_LEFT)." ".$data['CPM_JENIS_HAK']."</option>";
						}
$texthtml .="			      </select>";
return $texthtml;
	
}

function formPenerimaan($value) {
    global $a, $m, $appConfig, $arConfig, $DBLink;

    $today = date("d-m-Y");
    
    $value = null;
    $values = explode(",", "CPM_BERKAS_ID,CPM_BERKAS_NOP,CPM_BERKAS_TANGGAL,CPM_BERKAS_LAMPIRAN,CPM_BERKAS_PETUGAS,CPM_BERKAS_ALAMAT_OP,CPM_BERKAS_KELURAHAN_OP,CPM_BERKAS_KECAMATAN_OP,CPM_BERKAS_NPWP,CPM_BERKAS_NAMA_WP,CPM_BERKAS_ALAMAT_WP,CPM_BERKAS_STATUS,CPM_BERKAS_JNS_PEROLEHAN,CPM_BERKAS_NOPEL,CPM_BERKAS_NOTARIS,CPM_BERKAS_TELP_WP,CPM_BERKAS_HARGA_TRAN");
    
    for($x=0;$x<count($values);$x++){
        $value[$values[$x]] = null;
    }


    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
	$lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
	$lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";

    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
	$jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
	$jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
	$jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
	$jnsPerolehan[] = "";
	
	$jnsPerolehan =jenishak("");
	
    $strJnsPerolehan = "";
    $value['CPM_BERKAS_NOPEL'] = "";
	
    if (isset($_REQUEST['svcid'])) {
		//print_R($_REQUEST);
        $query = "select * from cppmod_ssb_berkas where CPM_BERKAS_ID = '{$_REQUEST['svcid']}'";
        $result = mysqli_query($DBLink, $query);
        $value = mysqli_fetch_array($result);
		
		$jnsPerolehan = jenishak($value['CPM_BERKAS_JNS_PEROLEHAN']);
		
		
        $strJnsPerolehan = $value['CPM_BERKAS_JNS_PEROLEHAN'];
        $lampiran[0] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "checked" : "";
        $lampiran[1] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "checked" : "";
        $lampiran[2] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "checked" : "";
        $lampiran[3] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "checked" : "";
        $lampiran[4] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "checked" : "";
        $lampiran[5] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "checked" : "";
        $lampiran[6] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "checked" : "";
        $lampiran[7] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "8") !== false) ? "checked" : "";
        $lampiran[8] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "9") !== false) ? "checked" : "";
        $lampiran[9] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "10") !== false) ? "checked" : "";
        $lampiran[10]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "11") !== false) ? "checked" : "";
        $lampiran[11]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "12") !== false) ? "checked" : "";
		$lampiran[12]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "13") !== false) ? "checked" : "";
        $lampiran[13]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "14") !== false) ? "checked" : "";
		$lampiran[14]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "15") !== false) ? "checked" : "";
        $lampiran[15]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "16") !== false) ? "checked" : "";
		$lampiran[16]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "17") !== false) ? "checked" : "";
        $lampiran[17]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "18") !== false) ? "checked" : "";
		$lampiran[18]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "19") !== false) ? "checked" : "";
        $lampiran[19]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "20") !== false) ? "checked" : "";
        $lampiran[20]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "21") !== false) ? "checked" : "";
        $lampiran[21]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "22") !== false) ? "checked" : "";
        $lampiran[22]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "23") !== false) ? "checked" : "";
        $lampiran[23]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "24") !== false) ? "checked" : "";
        $lampiran[24]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "25") !== false) ? "checked" : "";
        $lampiran[25]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "26") !== false) ? "checked" : "";
        $lampiran[26]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "27") !== false) ? "checked" : "";
        $lampiran[27]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "28") !== false) ? "checked" : "";
        $lampiran[28]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "29") !== false) ? "checked" : "";
        
        $lampiran[901]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "901") !== false) ? "checked" : "";
        $lampiran[902]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "902") !== false) ? "checked" : "";
        $lampiran[903]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "903") !== false) ? "checked" : "";
        $lampiran[904]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "904") !== false) ? "checked" : "";
        $lampiran[905]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "905") !== false) ? "checked" : "";
        $lampiran[906]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "906") !== false) ? "checked" : "";
        $lampiran[907]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "907") !== false) ? "checked" : "";
        $lampiran[908]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "908") !== false) ? "checked" : "";
        $lampiran[909]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "909") !== false) ? "checked" : "";
        $lampiran[910]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "910") !== false) ? "checked" : "";

        $lampiran[911]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "911") !== false) ? "checked" : "";
        $lampiran[912]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "912") !== false) ? "checked" : "";
        $lampiran[913]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "913") !== false) ? "checked" : "";
        $lampiran[914]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "914") !== false) ? "checked" : "";
        $lampiran[915]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "915") !== false) ? "checked" : "";
        $lampiran[916]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "916") !== false) ? "checked" : "";
        $lampiran[917]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "917") !== false) ? "checked" : "";
        $lampiran[918]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "918") !== false) ? "checked" : "";
        $lampiran[919]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "919") !== false) ? "checked" : "";
        $lampiran[920]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "920") !== false) ? "checked" : "";
        
        $lampiran[921]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "921") !== false) ? "checked" : "";
        $lampiran[922]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "922") !== false) ? "checked" : "";
        $lampiran[923]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "923") !== false) ? "checked" : "";
        $lampiran[924]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "924") !== false) ? "checked" : "";
        $lampiran[925]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "925") !== false) ? "checked" : "";
        $lampiran[926]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "926") !== false) ? "checked" : "";
        $lampiran[927]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "927") !== false) ? "checked" : "";
        $lampiran[928]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "928") !== false) ? "checked" : "";
        $lampiran[929]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "929") !== false) ? "checked" : "";
        $lampiran[930]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "930") !== false) ? "checked" : "";

        $lampiran[931]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "931") !== false) ? "checked" : "";
        $lampiran[932]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "932") !== false) ? "checked" : "";
        $lampiran[933]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "933") !== false) ? "checked" : "";
        $lampiran[934]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "934") !== false) ? "checked" : "";
        $lampiran[935]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "935") !== false) ? "checked" : "";
        $lampiran[936]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "936") !== false) ? "checked" : "";

    }

    //query ka upload file
    //fetch loop
    //if index+1 = kode lampiran
    //then $lampiran[0] .=  " readonly";
    //else skip 
     if (isset($_REQUEST['svcid'])) {
        $querys = "select * from cppmod_ssb_upload_file where CPM_BERKAS_ID = '{$value['CPM_BERKAS_NOPEL']}'";
		//echo $querys;
        $results = mysqli_query($DBLink, $querys);
		
		while($val = mysqli_fetch_array($results)){
			if($val['CPM_KODE_LAMPIRAN']=='1')
				$lampiran[0] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='2')
				$lampiran[1] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='3')
				$lampiran[2] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='4')
				$lampiran[3] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='5')
				$lampiran[4] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='6')
				$lampiran[5] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='7')
				$lampiran[6] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='8')
				$lampiran[7] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='9')
				$lampiran[8] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='10')
				$lampiran[9] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='11')
				$lampiran[10] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='12')
				$lampiran[11] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='13')
				$lampiran[12] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='14')
				$lampiran[13] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='15')
				$lampiran[14] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='16')
				$lampiran[15] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='17')
				$lampiran[16] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='18')
				$lampiran[17] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='19')
				$lampiran[18] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='20')
				$lampiran[19] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='21')
				$lampiran[20] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='22')
				$lampiran[21] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='23')
				$lampiran[22] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='24')
				$lampiran[23] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='25')
				$lampiran[24] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='26')
				$lampiran[25] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='27')
				$lampiran[26] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='28')
				$lampiran[27] .= " style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='29')
				$lampiran[28] .= " style=\"pointer-events: none; tabindex: -1;\" ";

			if($val['CPM_KODE_LAMPIRAN']=='901')
				$lampiran[901] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='902')
				$lampiran[902] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='903')
				$lampiran[903] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='904')
				$lampiran[904] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='905')
				$lampiran[905] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='906')
				$lampiran[906] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='907')
				$lampiran[907] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='908')
				$lampiran[908] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='909')
				$lampiran[909] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='910')
				$lampiran[910] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";

			if($val['CPM_KODE_LAMPIRAN']=='911')
				$lampiran[911] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='912')
				$lampiran[912] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='913')
				$lampiran[913] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='914')
				$lampiran[914] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='915')
				$lampiran[915] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='916')
				$lampiran[916] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='917')
				$lampiran[917] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='918')
				$lampiran[918] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='919')
				$lampiran[919] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='920')
				$lampiran[920] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";

			if($val['CPM_KODE_LAMPIRAN']=='921')
				$lampiran[921] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='922')
				$lampiran[922] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='923')
				$lampiran[923] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='924')
				$lampiran[924] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='925')
				$lampiran[925] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='926')
				$lampiran[926] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='927')
				$lampiran[927] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='928')
				$lampiran[928] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='929')
				$lampiran[929] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='930')
				$lampiran[930] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";

			if($val['CPM_KODE_LAMPIRAN']=='931')
				$lampiran[931] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='932')
				$lampiran[932] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='933')
				$lampiran[933] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='934')
				$lampiran[934] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='935')
				$lampiran[935] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
			if($val['CPM_KODE_LAMPIRAN']=='936')
				$lampiran[936] .= "checked style=\"pointer-events: none; tabindex: -1;\" ";
		}
		
     }

    $html = "
    <style>
    #main-content {
        width: 788px;
    }
    #form-penerimaan input.error {
        border-color: #ff0000;
    }
    #form-penerimaan textarea.error {
        border-color: #ff0000;
    }
	.fileContainer {
			overflow: hidden;
			position: relative;
		}

		.fileContainer [type=file] {
			cursor: inherit;
			display: block;
			font-size: 999px;
			filter: alpha(opacity=0);
			min-height: 100%;
			min-width: 100%;
			opacity: 0;
			position: absolute;
			right: 0;
			text-align: right;
			top: 0;
		}
    </style>
			
    <script language=\"javascript\">
        jQuery.validator.setDefaults({
            debug: true,
            success: \"valid\"
          });
         $(document).ready(function(){
             var form = $(\"#form-penerimaan\");
             form.validate({
                 rules : {
                         \"nop\" :{
                                    required : true,
                                    digits : true,
                                    },
                         \"alamatOp\" : \"required\",
                         \"kelurahanOp\" : \"required\",
                         \"kecamatanOp\" : \"required\",
                         \"npwp\" : \"required\",
                         \"namaWp\" : \"required\",
                         \"alamatWp\" : \"required\",
                         \"jnsPerolehan\" : \"required\",
                         \"noPel\" : \"required\",
                         \"telpWp\" : \"required\",
                         \"telppnjl\" : \"required\",
						 \"notaris\" : \"required\"
                         
                         },
                  messages : {
                         \"nop\" :{
                                    required : \"harus diisi\",
                                    digits : \"harus berupa angka\",
                                    },
                         \"alamatOp\" : \"harus diisi\",
                         \"kelurahanOp\" : \"harus diisi\",
                         \"kecamatanOp\" : \"harus diisi\",
                         \"npwp\" : \"harus diisi\",
                         \"namaWp\" : \"harus diisi\",
                         \"alamatWp\" : \"harus diisi\",
                         \"jnsPerolehan\":\"harus diisi\",
                         \"noPel\" : \"harus diisi\",
                         \"telpWp\" : \"harus diisi\",
                         \"telppnjl\" : \"harus diisi\",
                         \"notaris\" : \"harus diisi\"
                    }
             });
             
            $(\"#btn-simpan\").click(function(){
                $(\"#process\").val($(this).val());
                if(form.valid()){
					
						document.getElementById(\"form-penerimaan\").submit();
					
                }
            });
            
            $(\".jnsPerolehan\").hide();
            disabledJnsPerolehan();
            enabledJnsPerolehan('#jnsPerolehan" . $strJnsPerolehan . "')
                

            $( \"#nop\" ).focusout(function()
		{
                var val = $.trim($(\"#nop\").val());
                showDialog('Load', '<img src=\"image/icon/loading.gif\" width=\"32\" height=\"32\" style=\"margin-right:8px;\" align=\"absmiddle\"/>Tunggu', 'prompt', false, true);
                    $.ajax({
                        type: \"post\",
                        data: \"nop=\" + val,
                        url: \"./function/BPHTB/berkas/svc-check-nop.php\",
                        dataType: \"json\",
                        success: function(res) {

                            if (res.message)
                                alert(res.message);

                            $('#errnop').remove();
                            if (res.denied)
                                if (res.denied == 1) {


                                    var errnop = $(\"<span id='errnop'><br>\" + res.message + \"</span>\").css({'color': '#FF0000'});

                            }
                            

                            hideDialog();           
                        },
                        error: hideDialog() , //function(res){ hideMask();console.log(res) },
                        failure: hideDialog() //function(res){ hideMask();console.log(res) }
                    });

                                });
        });
        
        function iniAngka(evt,x){
            x.value=x.value.replace(/[^0-9]+/g, '');
        }
        
        function disabledJnsPerolehan(){            
            $(\".jnsPerolehan input[type='checkbox']\").each(function(){
                $(this).prop('disabled','disabled')
            });
        }

        function enabledJnsPerolehan(id){
            $(id).show();
            $(id+\" input[type='checkbox']\").each(function(){
                $(this).removeAttr('disabled');
            });
        }
        
        function setNoPel(id){
            $.ajax({
                type : 'post',
                data : 'type='+id,
                url: './function/BPHTB/berkas/svc-get-nopel.php',
                success : function(res){
                    $('#noPel').val(res);
                }
            });
        }
        
        function showJnsPerolehan(obj){
            var id = obj.value;
            $(\".jnsPerolehan\").hide();
            disabledJnsPerolehan();
            setNoPel(id);
            $(\"#jnsPerolehan\"+id).show();
            enabledJnsPerolehan(\"#jnsPerolehan\"+id);            
        }
		function cleancheckbox(){
					var b1=document.getElementById(\"lamp1\");				var b77=document.getElementById(\"lamp77\");
					var b2=document.getElementById(\"lamp2\");              var b78=document.getElementById(\"lamp78\");
					var b3=document.getElementById(\"lamp3\");              var b79=document.getElementById(\"lamp79\");
					var b4=document.getElementById(\"lamp4\");              var b80=document.getElementById(\"lamp80\");
					var b5=document.getElementById(\"lamp5\");              var b81=document.getElementById(\"lamp81\");
					var b6=document.getElementById(\"lamp6\");              var b82=document.getElementById(\"lamp82\");
					var b7=document.getElementById(\"lamp7\");              var b83=document.getElementById(\"lamp83\");
					//var b8=document.getElementById(\"lamp8\");              var b84=document.getElementById(\"lamp84\");
					var b9=document.getElementById(\"lamp9\");              var b85=document.getElementById(\"lamp85\");
					var b10=document.getElementById(\"lamp10\");            var b86=document.getElementById(\"lamp86\");
					var b11=document.getElementById(\"lamp11\");            var b87=document.getElementById(\"lamp87\");
					var b12=document.getElementById(\"lamp12\");            var b88=document.getElementById(\"lamp88\");
					var b13=document.getElementById(\"lamp13\");            var b89=document.getElementById(\"lamp89\");
					var b14=document.getElementById(\"lamp14\");            var b90=document.getElementById(\"lamp90\");
					var b15=document.getElementById(\"lamp15\");            var b91=document.getElementById(\"lamp91\");
                                                                            var b92=document.getElementById(\"lamp92\");
				                                                            var b93=document.getElementById(\"lamp93\");
					var b18=document.getElementById(\"lamp18\");            var b94=document.getElementById(\"lamp94\");
					var b19=document.getElementById(\"lamp19\");            var b95=document.getElementById(\"lamp95\");
					var b20=document.getElementById(\"lamp20\");            var b96=document.getElementById(\"lamp96\");
					var b21=document.getElementById(\"lamp21\");            var b97=document.getElementById(\"lamp97\");
					var b22=document.getElementById(\"lamp22\");            var b98=document.getElementById(\"lamp98\");
					var b23=document.getElementById(\"lamp23\");            var b99=document.getElementById(\"lamp99\");
					var b24=document.getElementById(\"lamp24\");            var b100=document.getElementById(\"lamp100\");
					var b25=document.getElementById(\"lamp25\");            var b101=document.getElementById(\"lamp101\");
					var b26=document.getElementById(\"lamp26\");            var b102=document.getElementById(\"lamp102\");
					var b27=document.getElementById(\"lamp27\");            var b103=document.getElementById(\"lamp103\");
					var b28=document.getElementById(\"lamp28\");            var b104=document.getElementById(\"lamp104\");
					var b29=document.getElementById(\"lamp29\");            var b105=document.getElementById(\"lamp105\");
					var b30=document.getElementById(\"lamp30\");            var b106=document.getElementById(\"lamp106\");
					var b31=document.getElementById(\"lamp31\");            var b107=document.getElementById(\"lamp107\");
					var b32=document.getElementById(\"lamp32\");            var b108=document.getElementById(\"lamp108\");
					var b33=document.getElementById(\"lamp33\");            var b109=document.getElementById(\"lamp109\");
					var b34=document.getElementById(\"lamp34\");            var b110=document.getElementById(\"lamp110\");
					var b35=document.getElementById(\"lamp35\");            var b111=document.getElementById(\"lamp111\");
					var b36=document.getElementById(\"lamp36\");            var b112=document.getElementById(\"lamp112\");
					var b37=document.getElementById(\"lamp37\");            var b113=document.getElementById(\"lamp113\");
					var b38=document.getElementById(\"lamp38\");            var b114=document.getElementById(\"lamp114\");
					var b39=document.getElementById(\"lamp39\");			var b115=document.getElementById(\"lamp115\");
					var b40=document.getElementById(\"lamp40\");		    var b116=document.getElementById(\"lamp116\");
					var b41=document.getElementById(\"lamp41\");            var b117=document.getElementById(\"lamp117\");
					var b42=document.getElementById(\"lamp42\");            var b118=document.getElementById(\"lamp118\");
					var b43=document.getElementById(\"lamp43\");            var b119=document.getElementById(\"lamp119\");
					var b44=document.getElementById(\"lamp44\");            var b120=document.getElementById(\"lamp120\");
					var b45=document.getElementById(\"lamp45\");            var b121=document.getElementById(\"lamp121\");
					var b46=document.getElementById(\"lamp46\");            var b122=document.getElementById(\"lamp122\");
					var b47=document.getElementById(\"lamp47\");            var b123=document.getElementById(\"lamp123\");
					var b48=document.getElementById(\"lamp48\");            var b124=document.getElementById(\"lamp124\");
					var b49=document.getElementById(\"lamp49\");            var b125=document.getElementById(\"lamp125\");
					var b50=document.getElementById(\"lamp50\");            var b126=document.getElementById(\"lamp126\");
					var b51=document.getElementById(\"lamp51\");            var b127=document.getElementById(\"lamp127\");
					var b52=document.getElementById(\"lamp52\");            var b128=document.getElementById(\"lamp128\");
					var b53=document.getElementById(\"lamp53\");            var b129=document.getElementById(\"lamp129\");
					var b54=document.getElementById(\"lamp54\");            var b130=document.getElementById(\"lamp130\");
					var b55=document.getElementById(\"lamp55\");            var b131=document.getElementById(\"lamp131\");
					var b56=document.getElementById(\"lamp56\");            var b132=document.getElementById(\"lamp132\");
					var b57=document.getElementById(\"lamp57\");            var b133=document.getElementById(\"lamp133\");
					var b58=document.getElementById(\"lamp58\");            var b134=document.getElementById(\"lamp134\");
					var b59=document.getElementById(\"lamp59\");            var b135=document.getElementById(\"lamp135\");
					var b60=document.getElementById(\"lamp60\");            var b136=document.getElementById(\"lamp136\");
					var b61=document.getElementById(\"lamp61\");            var b137=document.getElementById(\"lamp137\");
					var b62=document.getElementById(\"lamp62\");            var b138=document.getElementById(\"lamp138\");
					var b63=document.getElementById(\"lamp63\");            var b139=document.getElementById(\"lamp139\");
					var b64=document.getElementById(\"lamp64\");            var b140=document.getElementById(\"lamp140\");
					var b65=document.getElementById(\"lamp65\");            var b141=document.getElementById(\"lamp141\");
					var b66=document.getElementById(\"lamp66\");            var b142=document.getElementById(\"lamp142\");
					var b67=document.getElementById(\"lamp67\");            var b143=document.getElementById(\"lamp143\");
					var b68=document.getElementById(\"lamp68\");            var b144=document.getElementById(\"lamp144\");
					var b69=document.getElementById(\"lamp69\");            var b145=document.getElementById(\"lamp145\");
					var b70=document.getElementById(\"lamp70\");            var b146=document.getElementById(\"lamp146\");
					var b71=document.getElementById(\"lamp71\");            
					var b72=document.getElementById(\"lamp72\");            
					var b73=document.getElementById(\"lamp73\");            
					var b74=document.getElementById(\"lamp74\");            
					var b75=document.getElementById(\"lamp75\");            
					var b76=document.getElementById(\"lamp76\");
					
					b1.checked=false;			  b77.checked=false;
					b2.checked=false;             b78.checked=false;
					b3.checked=false;             b79.checked=false;
					b4.checked=false;             b80.checked=false;
					b5.checked=false;             b81.checked=false;
					b6.checked=false;             b82.checked=false;
					b7.checked=false;             b83.checked=false;
					//b8.checked=false;             b84.checked=false;
					b9.checked=false;             b85.checked=false;
					b10.checked=false;            b86.checked=false;
					b11.checked=false;            b87.checked=false;
					b12.checked=false;            b88.checked=false;
					b13.checked=false;            b89.checked=false;
					b14.checked=false;            b90.checked=false;
					b15.checked=false;            b91.checked=false;
                                                    b92.checked=false;
                                                    b93.checked=false;
					b18.checked=false;            b94.checked=false;
					b19.checked=false;            b95.checked=false;
					b20.checked=false;            b96.checked=false;
					b21.checked=false;            b97.checked=false;
					b22.checked=false;            b98.checked=false;
					b23.checked=false;            b99.checked=false;
					b24.checked=false;            b100.checked=false;
					b25.checked=false;            b101.checked=false;
					b26.checked=false;            b102.checked=false;
					b27.checked=false;            b103.checked=false;
					b28.checked=false;            b104.checked=false;
					b29.checked=false;            b105.checked=false;
					b30.checked=false;            b106.checked=false;
					b31.checked=false;            b107.checked=false;
					b32.checked=false;            b108.checked=false;
					b33.checked=false;            b109.checked=false;
					b34.checked=false;            b110.checked=false;
					b35.checked=false;            b111.checked=false;
					b36.checked=false;            b112.checked=false;
					b37.checked=false;            b113.checked=false;
					b38.checked=false;            b114.checked=false;
					b39.checked=false;			  b115.checked=false;
					b40.checked=false;		      b116.checked=false;
					b41.checked=false;            b117.checked=false;
					b42.checked=false;            b118.checked=false;
					b43.checked=false;            b119.checked=false;
					b44.checked=false;            b120.checked=false;
					b45.checked=false;            b121.checked=false;
					b46.checked=false;            b122.checked=false;
					b47.checked=false;            b123.checked=false;
					b48.checked=false;            b124.checked=false;
					b49.checked=false;            b125.checked=false;
					b50.checked=false;            b126.checked=false;
					b51.checked=false;            b127.checked=false;
					b52.checked=false;            b128.checked=false;
					b53.checked=false;            b129.checked=false;
					b54.checked=false;            b130.checked=false;
					b55.checked=false;            b131.checked=false;
					b56.checked=false;            b132.checked=false;
					b57.checked=false;            b133.checked=false;
					b58.checked=false;            b134.checked=false;
					b59.checked=false;            b135.checked=false;
					b60.checked=false;            b136.checked=false;
					b61.checked=false;            b137.checked=false;
					b62.checked=false;            b138.checked=false;
					b63.checked=false;            b139.checked=false;
					b64.checked=false;            b140.checked=false;
					b65.checked=false;            b141.checked=false;
					b66.checked=false;            b142.checked=false;
					b67.checked=false;            b143.checked=false;
					b68.checked=false;            b144.checked=false;
					b69.checked=false;            b145.checked=false;
					b70.checked=false;            b146.checked=false;
					b71.checked=false;            
					b72.checked=false;            
					b73.checked=false;            
					b74.checked=false;            
					b75.checked=false;            
					b76.checked=false;
					
					
					
		}           
		function validateCheckBoxes() 
				{
					// for(var i=1;i<=146;i++){
					// var b[i]=document.getElementById('lamp'+i);
					// eval( 'var b'+i+' = document.getElementById(\"lamp' + i +'\");' );
					// }
					var b1=document.getElementById(\"lamp1\");				var b77=document.getElementById(\"lamp77\");
					var b2=document.getElementById(\"lamp2\");              var b78=document.getElementById(\"lamp78\");
					var b3=document.getElementById(\"lamp3\");              var b79=document.getElementById(\"lamp79\");
					var b4=document.getElementById(\"lamp4\");              var b80=document.getElementById(\"lamp80\");
					var b5=document.getElementById(\"lamp5\");              var b81=document.getElementById(\"lamp81\");
					var b6=document.getElementById(\"lamp6\");              var b82=document.getElementById(\"lamp82\");
					var b7=document.getElementById(\"lamp7\");              var b83=document.getElementById(\"lamp83\");
					var b8=document.getElementById(\"lamp8\");              var b84=document.getElementById(\"lamp84\");
					var b9=document.getElementById(\"lamp9\");              var b85=document.getElementById(\"lamp85\");
					var b10=document.getElementById(\"lamp10\");            var b86=document.getElementById(\"lamp86\");
					var b11=document.getElementById(\"lamp11\");            var b87=document.getElementById(\"lamp87\");
					var b12=document.getElementById(\"lamp12\");            var b88=document.getElementById(\"lamp88\");
					var b13=document.getElementById(\"lamp13\");            var b89=document.getElementById(\"lamp89\");
					var b14=document.getElementById(\"lamp14\");            var b90=document.getElementById(\"lamp90\");
					var b15=document.getElementById(\"lamp15\");            var b91=document.getElementById(\"lamp91\");
                                                                            var b92=document.getElementById(\"lamp92\");
                                                                            var b93=document.getElementById(\"lamp93\");
					var b18=document.getElementById(\"lamp18\");            var b94=document.getElementById(\"lamp94\");
					var b19=document.getElementById(\"lamp19\");            var b95=document.getElementById(\"lamp95\");
					var b20=document.getElementById(\"lamp20\");            var b96=document.getElementById(\"lamp96\");
					var b21=document.getElementById(\"lamp21\");            var b97=document.getElementById(\"lamp97\");
					var b22=document.getElementById(\"lamp22\");            var b98=document.getElementById(\"lamp98\");
					var b23=document.getElementById(\"lamp23\");            var b99=document.getElementById(\"lamp99\");
					var b24=document.getElementById(\"lamp24\");            var b100=document.getElementById(\"lamp100\");
					var b25=document.getElementById(\"lamp25\");            var b101=document.getElementById(\"lamp101\");
					var b26=document.getElementById(\"lamp26\");            var b102=document.getElementById(\"lamp102\");
					var b27=document.getElementById(\"lamp27\");            var b103=document.getElementById(\"lamp103\");
					var b28=document.getElementById(\"lamp28\");            var b104=document.getElementById(\"lamp104\");
					var b29=document.getElementById(\"lamp29\");            var b105=document.getElementById(\"lamp105\");
					var b30=document.getElementById(\"lamp30\");            var b106=document.getElementById(\"lamp106\");
					var b31=document.getElementById(\"lamp31\");            var b107=document.getElementById(\"lamp107\");
					var b32=document.getElementById(\"lamp32\");            var b108=document.getElementById(\"lamp108\");
					var b33=document.getElementById(\"lamp33\");            var b109=document.getElementById(\"lamp109\");
					var b34=document.getElementById(\"lamp34\");            var b110=document.getElementById(\"lamp110\");
					var b35=document.getElementById(\"lamp35\");            var b111=document.getElementById(\"lamp111\");
					var b36=document.getElementById(\"lamp36\");            var b112=document.getElementById(\"lamp112\");
					var b37=document.getElementById(\"lamp37\");            var b113=document.getElementById(\"lamp113\");
					var b38=document.getElementById(\"lamp38\");            var b114=document.getElementById(\"lamp114\");
					var b39=document.getElementById(\"lamp39\");			var b115=document.getElementById(\"lamp115\");
					var b40=document.getElementById(\"lamp40\");		    var b116=document.getElementById(\"lamp116\");
					var b41=document.getElementById(\"lamp41\");            var b117=document.getElementById(\"lamp117\");
					var b42=document.getElementById(\"lamp42\");            var b118=document.getElementById(\"lamp118\");
					var b43=document.getElementById(\"lamp43\");            var b119=document.getElementById(\"lamp119\");
					var b44=document.getElementById(\"lamp44\");            var b120=document.getElementById(\"lamp120\");
					var b45=document.getElementById(\"lamp45\");            var b121=document.getElementById(\"lamp121\");
					var b46=document.getElementById(\"lamp46\");            var b122=document.getElementById(\"lamp122\");
					var b47=document.getElementById(\"lamp47\");            var b123=document.getElementById(\"lamp123\");
					var b48=document.getElementById(\"lamp48\");            var b124=document.getElementById(\"lamp124\");
					var b49=document.getElementById(\"lamp49\");            var b125=document.getElementById(\"lamp125\");
					var b50=document.getElementById(\"lamp50\");            var b126=document.getElementById(\"lamp126\");
					var b51=document.getElementById(\"lamp51\");            var b127=document.getElementById(\"lamp127\");
					var b52=document.getElementById(\"lamp52\");            var b128=document.getElementById(\"lamp128\");
					var b53=document.getElementById(\"lamp53\");            var b129=document.getElementById(\"lamp129\");
					var b54=document.getElementById(\"lamp54\");            var b130=document.getElementById(\"lamp130\");
					var b55=document.getElementById(\"lamp55\");            var b131=document.getElementById(\"lamp131\");
					var b56=document.getElementById(\"lamp56\");            var b132=document.getElementById(\"lamp132\");
					var b57=document.getElementById(\"lamp57\");            var b133=document.getElementById(\"lamp133\");
					var b58=document.getElementById(\"lamp58\");            var b134=document.getElementById(\"lamp134\");
					var b59=document.getElementById(\"lamp59\");            var b135=document.getElementById(\"lamp135\");
					var b60=document.getElementById(\"lamp60\");            var b136=document.getElementById(\"lamp136\");
					var b61=document.getElementById(\"lamp61\");            var b137=document.getElementById(\"lamp137\");
					var b62=document.getElementById(\"lamp62\");            var b138=document.getElementById(\"lamp138\");
					var b63=document.getElementById(\"lamp63\");            var b139=document.getElementById(\"lamp139\");
					var b64=document.getElementById(\"lamp64\");            var b140=document.getElementById(\"lamp140\");
					var b65=document.getElementById(\"lamp65\");            var b141=document.getElementById(\"lamp141\");
					var b66=document.getElementById(\"lamp66\");            var b142=document.getElementById(\"lamp142\");
					var b67=document.getElementById(\"lamp67\");            var b143=document.getElementById(\"lamp143\");
					var b68=document.getElementById(\"lamp68\");            var b144=document.getElementById(\"lamp144\");
					var b69=document.getElementById(\"lamp69\");            var b145=document.getElementById(\"lamp145\");
					var b70=document.getElementById(\"lamp70\");            var b146=document.getElementById(\"lamp146\");
					var b71=document.getElementById(\"lamp71\");            
					var b72=document.getElementById(\"lamp72\");            
					var b73=document.getElementById(\"lamp73\");            
					var b74=document.getElementById(\"lamp74\");            
					var b75=document.getElementById(\"lamp75\");            
					var b76=document.getElementById(\"lamp76\");            
					
					
					if(document.getElementById(\"jnsper\").value=='1'){
						if((b1.checked==true)&&(b2.checked==true)&&(b3.checked==true)&&(b4.checked==true)&&(b5.checked==true)&&(b6.checked==true)&&(b7.checked==true)&&(b9.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					
					}else if(document.getElementById(\"jnsper\").value=='2'){
						if((b10.checked==true)&&(b11.checked==true)&&(b12.checked==true)&&(b13.checked==true)&&(b14.checked==true)&&(b15.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='3'){
						if((b18.checked==true)&&(b19.checked==true)&&(b20.checked==true)&&(b21.checked==true)&&(b22.checked==true)&&(b23.checked==true)&&(b24.checked==true)&&(b25.checked==true)&&(b26.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='4'){
						if((b27.checked==true)&&(b28.checked==true)&&(b29.checked==true)&&(b30.checked==true)&&(b31.checked==true)&&(b32.checked==true)&&(b33.checked==true)&&(b34.checked==true)&&(b35.checked==true)&&(b36.checked==true)&&(b37.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='5'){
						if((b38.checked==true)&&(b39.checked==true)&&(b40.checked==true)&&(b41.checked==true)&&(b42.checked==true)&&(b43.checked==true)&&(b44.checked==true)&&(b45.checked==true)&&(b46.checked==true)&&(b47.checked==true)&&(b48.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='6'){
						if((b49.checked==true)&&(b50.checked==true)&&(b51.checked==true)&&(b52.checked==true)&&(b53.checked==true)&&(b54.checked==true)&&(b55.checked==true)&&(b56.checked==true)&&(b57.checked==true)&&(b58.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='7'){
						if((b59.checked==true)&&(b60.checked==true)&&(b61.checked==true)&&(b62.checked==true)&&(b63.checked==true)&&(b64.checked==true)&&(b65.checked==true)&&(b66.checked==true)&&(b67.checked==true)&&(b68.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='8'){
						if((b70.checked==true)&&(b71.checked==true)&&(b72.checked==true)&&(b73.checked==true)&&(b74.checked==true)&&(b64.checked==true)&&(b75.checked==true)&&(b76.checked==true)&&(b77.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='9'){
						if((b78.checked==true)&&(b79.checked==true)&&(b80.checked==true)&&(b81.checked==true)&&(b82.checked==true)&&(b83.checked==true)&&(b85.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='10'){
						if((b86.checked==true)&&(b87.checked==true)&&(b88.checked==true)&&(b89.checked==true)&&(b90.checked==true)&&(b91.checked==true)&&(b92.checked==true)&&(b93.checked==true)&&(b94.checked==true)&&(b95.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='11'){
						if((b96.checked==true)&&(b97.checked==true)&&(b98.checked==true)&&(b99.checked==true)&&(b100.checked==true)&&(b101.checked==true)&&(b102.checked==true)&&(b103.checked==true)&&(b104.checked==true)&&(b105.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='12'){
						if((b106.checked==true)&&(b107.checked==true)&&(b108.checked==true)&&(b109.checked==true)&&(b110.checked==true)&&(b111.checked==true)&&(b112.checked==true)&&(b113.checked==true)&&(b114.checked==true)&&(b115.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='13'){
						if((b116.checked==true)&&(b117.checked==true)&&(b118.checked==true)&&(b119.checked==true)&&(b120.checked==true)&&(b121.checked==true)&&(b122.checked==true)&&(b123.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='14'){
						if((b124.checked==true)&&(b125.checked==true)&&(b126.checked==true)&&(b127.checked==true)&&(b128.checked==true)&&(b129.checked==true)&&(b130.checked==true)&&(b131.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='21'){
						if((b132.checked==true)&&(b133.checked==true)&&(b134.checked==true)&&(b135.checked==true)&&(b136.checked==true)&&(b137.checked==true)&&(b138.checked==true)&&(b139.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='22'){
						if((b140.checked==true)&&(b141.checked==true)&&(b142.checked==true)&&(b143.checked==true)&&(b144.checked==true)&&(b145.checked==true)&&(b146.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}
					
					
				}
				// function check_extension(val){
					// var ext = val.toString().split('.').pop().toLowerCase();
					// alert(val.toString());
					// if($.inArray(ext, ['png','jpg','jpeg']) == -1){
						// alert('Format Gambar Salah !');
						// val.value='';
					// }	
				// }
				function check_extension(file) {
				  var extension = file.toString().substr((file.toString().lastIndexOf('.') +1)).toLowerCase();
				  if (!/(jpg|png|jpeg|pdf)$/ig.test(extension)) {
					alert(\"Invalid file type: \"+extension+\".  Please use JPEG, JPG, PDF or PNG.\");
					$(\"#file\").val(\"\");
				  }
				}
				function showInputfile(checkbox,file)
				{
					
					if (checkbox.checked)
					{
						$('#file_lamp'+file).removeAttr('hidden','hidden');
					}else{
						$('#file_lamp'+file).attr('hidden','hidden');
					}
				}
				function gethide(){
					for(var i = 1; i < 146; i++){
						if($('#file_lamp'+i).is(':visible')){
							if ($('#file_lamp'+i).get(0).files.length === 0) {
								alert(\"No files selected.\");
								return false;exit;
							}
						}
					}
				}
    </script>
    <div id=\"main-content\"><form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">
        <input type=\"hidden\" name=\"process\" id=\"process\">
        <input type=\"hidden\" name=\"idssb\" id=\"idssb\" value=\"{$value['CPM_BERKAS_ID']}\">
	<table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
              <tr>
                <td colspan=\"2\"><strong><font size=\"+2\">Penerimaan Berkas Pelayanan BPHTB</font></strong><br /><hr><br /></td>
              </tr>
                  <tr><td colspan=\"2\"><h3>A. DATA OBJEK PAJAK</h3></td></tr>
                      <tr>
                        <td width=\"39%\"><label for=\"noPel\">Nomor Pelaporan *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"noPel\" readonly id=\"noPel\" style=\"text-align:right\" value=\"{$value['CPM_BERKAS_NOPEL']}\" size=\"20\" maxlength=\"50\" placeholder=\"Nomor Pelayanan\"/>                                      
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"tglMasuk\">Tanggal Surat Masuk</label></td>
                        <td width=\"60%\">";
                        $date = $value['CPM_BERKAS_TANGGAL'] != '' ? date('Y-m-d', strtotime($value['CPM_BERKAS_TANGGAL'])) : date('Y-m-d', strtotime($today));
                        // var_dump($date);
                        $html .= "   <input type=\"date\" name=\"tglMasuk\" id=\"tglMasuk\" value=\"" . $date . "\" size=\"10\" maxlength=\"10\" placeholder=\"Tgl Masuk\"/>                                      
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"sptpd\">NOP *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"nop\" id=\"nop\" size=\"50\" " . (isset($_REQUEST['svcid']) ? "readonly" : "") . "  maxlength=\"22\" onblur=\"return iniAngka(event,this)\" onkeypress=\"return iniAngka(event,this)\" value=\"{$value['CPM_BERKAS_NOP']}\" placeholder=\"NOP\" />
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"alamatOp\">Alamat Objek Pajak *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"alamatOp\" id=\"alamatOp\" size=\"50\" maxlength=\"70\" value=\"{$value['CPM_BERKAS_ALAMAT_OP']}\" placeholder=\"Alamat Objek Pajak\" />
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"kelurahanOp\">Kelurahan *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"kelurahanOp\" id=\"kelurahanOp\" size=\"50\" maxlength=\"70\" value=\"{$value['CPM_BERKAS_KELURAHAN_OP']}\" placeholder=\"Kelurahan Objek Pajak\" />
                        </td>
                      </tr>  
                      <tr>
                        <td width=\"39%\"><label for=\"kecamatanOp\">Kecamatan *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"kecamatanOp\" id=\"kecamatanOp\" size=\"50\" maxlength=\"70\" value=\"{$value['CPM_BERKAS_KECAMATAN_OP']}\" placeholder=\"Kecamatan Objek Pajak\" />
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"koordinatOp\">Titik Koordinat *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"koordinatOp\" id=\"koordinatOp\" size=\"50\" value=\"{$value['CPM_KOORDINAT_OP']}\" placeholder=\"Titik Koordinat Objek Pajak\" />
                        </td>
                      </tr>
                      <tr><td colspan=\"2\"><h3>B. DATA WAJIB PAJAK</h3></td></tr>                                                                        
                      <tr>
                        <td width=\"39%\"><label for=\"npwp\">NPWP / KTP *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"npwp\" id=\"npwp\" size=\"50\" maxlength=\"50\" value=\"{$value['CPM_BERKAS_NPWP']}\" placeholder=\"NPWP / KTP\" />
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"namaWp\">Nama Wajib Pajak *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"namaWp\" id=\"namaWp\" size=\"50\" maxlength=\"50\" value=\"{$value['CPM_BERKAS_NAMA_WP']}\" placeholder=\"Nama Wajib Pajak\" />
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"telpWp\">Nomor Telp Wajib Pajak *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"telpWp\" id=\"telpWp\" size=\"50\" maxlength=\"20\" value=\"{$value['CPM_BERKAS_TELP_WP']}\" placeholder=\"Nomor Telp Wajib Pajak\" />
                        </td>
                      </tr>
                      <tr><td colspan=\"2\"><h3></h3></td></tr>
                      <tr>
                        <td width=\"39%\"><label for=\"telppnjl\">Nomor Telp Penjual *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"telppnjl\" id=\"telppnjl\" size=\"50\" maxlength=\"20\" value=\"{$value['CPM_BERKAS_TELP_OP']}\" placeholder=\"Nomor Telp Penjual\" />
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"hargaTran\">Harga Transaksi *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"hargaTran\" style=\"text-align:right\" id=\"hargaTran\" size=\"50\" maxlength=\"12\" value=\"{$value['CPM_BERKAS_HARGA_TRAN']}\" placeholder=\"Harga Transaksi\" />
                        </td>
                      </tr>
					  <tr>
                        <td width=\"39%\"><label for=\"notaris\">Notaris *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"notaris\" style=\"text-align:right\" id=\"notaris\" size=\"50\" maxlength=\"12\" value=\"{$value['CPM_BERKAS_NOTARIS']}\" placeholder=\"Nama Notaris\" />
                        </td>
                      </tr>
                      <tr valign=\"top\">
                        <td width=\"39%\"><label for=\"jnsPerolehan\">Jenis Perolehan *</label></td>
                        <td width=\"60%\">
                          <!-- <input type=\"radio\" name=\"jnsPerolehan\" value=\"1\" {$jnsPerolehan[1]} onclick=\"javascript:showJnsPerolehan(this)\"/> SK<br/>
                          <input type=\"radio\" name=\"jnsPerolehan\" value=\"2\" {$jnsPerolehan[2]} onclick=\"javascript:showJnsPerolehan(this)\"/> JUAL-BELI<br/>
                          <input type=\"radio\" name=\"jnsPerolehan\" value=\"3\" {$jnsPerolehan[3]} onclick=\"javascript:showJnsPerolehan(this)\"/> HIBAH<br/>
                          <input type=\"radio\" name=\"jnsPerolehan\" value=\"4\" {$jnsPerolehan[4]} onclick=\"javascript:showJnsPerolehan(this)\"/> WARIS<br/> -->
						  
							".$jnsPerolehan."
						</td>
                      </tr>
                      <tr>
                        <td width=\"39%\">Persyaratan Administrasi :</td>
                        <td width=\"60%\">
                        </td>
                      </tr>
                      <tr class=\"jnsPerolehan\" id=\"jnsPerolehan1\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp1\" value=\"1\" {$lampiran[0]}> 
                                    Bukti penerimaan/Lembar expedisi berkas BPHTB (dicetak oleh Loket BPPRD) 
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp1\"  value=\"2\" {$lampiran[1]}>
                                    SSPD-BPHTB Lunas
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp3\"  value=\"905\" {$lampiran[905]}> 
                                    Fotocopy Lunas PBB 5 tahun terakhir (Informasi data pembayaran) 
                                </li>
                                <li>
                            		<input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp4\"  value=\"906\" {$lampiran[906]}> 
                            		Fotocopy Surat keterangan jual beli tanah / Bukti transaksi dilegalisir
                                	<input onchange='check_extension(this.value)' type='file' name='file_lamp4' id='file_lamp4' hidden='hidden'>
                            	</li>

                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp5\"  value=\"908\" {$lampiran[908]}> 
                                    Fotocopy daftar harga (Pricelist) dalam hal pembelian dan pengembangan (perumahan/kavlingan) dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp6\"  value=\"910\" {$lampiran[910]}>
                                    Fotocopy Akta
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp7\"  value=\"913\" {$lampiran[913]}> 
                                    Fotocopy Dokumen Pendukung Lainnya 
                                </li>
                            </ol>
                        </td>
                      </tr>
                      <tr class=\"jnsPerolehan\" id=\"jnsPerolehan2\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp10\"   value=\"1\" {$lampiran[0]}> Bukti penerimaan/Lembar expedisi berkas BPHTB (dicetak oleh Loket BPPRD) </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp11\"   value=\"2\" {$lampiran[1]}> SSPD-BPHTB Lunas</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp12\"   value=\"905\" {$lampiran[905]}> Fotocopy Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran) </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp13\"   value=\"912\" {$lampiran[912]}> Fotocopy Surat Pernyataan Tukar Hak Atas Tanah dari yang mengalihkan hak dilegalisir </li>

                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp14\"   value=\"910\" {$lampiran[910]}> Fotocopy Akta</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp15\"   value=\"913\" {$lampiran[913]}> Fotocopy Dokumen Pendukung Lainnya </li>
                            </ol>
                        </td>
                      </tr>
                      <tr class=\"jnsPerolehan\" id=\"jnsPerolehan3\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp18\" value=\"1\" {$lampiran[0]}> 
                                    Bukti penerimaan/Lembar expedisi berkas BPHTB (dicetak oleh Loket BPPRD) 
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp19\" value=\"2\" {$lampiran[1]}> 
                                    SSPD-BPHTB Lunas
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp20\" value=\"905\" {$lampiran[905]}> 
                                    Fotocopy Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran)
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp21\" value=\"913\" {$lampiran[913]}> 
                                    Fotocopy KTP Pemberi dan Penerima Hibah yang masih berlaku dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp22\" value=\"914\" {$lampiran[914]}> 
                                    Fotocopy Pertanyaan Hibah/Surat keterangan Hibah dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp23\" value=\"910\" {$lampiran[910]}> 
                                    Fotocopy Akta 
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp24\" value=\"913\" {$lampiran[913]}> 
                                    Fotocopy Dokumen Pendukung Lainnya
                                </li>
                            </ol>
                        </td>
                      </tr>
                      <tr class=\"jnsPerolehan\" id=\"jnsPerolehan4\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp27\" value=\"1\" {$lampiran[0]}> 
                                    Bukti penerimaan/Lembar expedisi berkas BPHTB (dicetak oleh Loket BPPRD) 
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp28\" value=\"2\" {$lampiran[1]}> 
                                    SSPD-BPHTB Lunas 
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp29\" value=\"905\" {$lampiran[905]}> 
                                    Fotocopy Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran)
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp30\" value=\"916\" {$lampiran[916]}>
                                    Fotocopy KTP Para ahli Waris/penerima Hibah Wasiat dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp31\" value=\"917\" {$lampiran[917]}>
                                    Fotocopy Surat/Keterangan Kematian dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp32\" value=\"918\" {$lampiran[918]}> 
                                    Fotocopy Surat Pernyataan hibah dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp33\" value=\"910\" {$lampiran[910]}> 
                                    Fotocopy Akta
                                </li>
								<li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp34\" value=\"913\" {$lampiran[913]}> 
                                    Fotocopy Dokumen Pendukung Lainnya
                                </li>

                                <input type=\"hidden\" name=\"lampiranx[]\" id=\"lamp35\" value=\"14\" {$lampiran[13]}> 
                                <input type=\"hidden\" name=\"lampiranx[]\" id=\"lamp36\" value=\"15\" {$lampiran[14]}> 
                                <input type=\"hidden\" name=\"lampiranx[]\" id=\"lamp37\" value=\"16\" {$lampiran[15]}> 
                            </ol>
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan5\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                             <ol id=\"lampiran\" style=\"margin-left: -20px;\">
								<li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp38\" value=\"1\" {$lampiran[0]}> 
                                    Bukti penerimaan/Lembar expedisi berkas BPHTB (dicetak oleh Loket BPPRD)
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp39\" value=\"2\" {$lampiran[1]}> 
                                    SSPD-BPHTB Lunas  
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp40\" value=\"905\" {$lampiran[905]}> 
                                    Fotocopy Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran)
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp41\" value=\"916\" {$lampiran[916]}>
                                    Fotocopy KTP Para ahli Waris/penerima Hibah Wasiat dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp42\" value=\"917\" {$lampiran[917]}>
                                    Fotocopy Surat/Keterangan Kematian dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp43\" value=\"925\" {$lampiran[925]}> 
                                    Fotocopy Surat Pernyataan Waris dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp44\" value=\"910\" {$lampiran[910]}> 
                                    Fotocopy AKTA
                                </li>
								<li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp45\" value=\"913\" {$lampiran[913]}> 
                                    Fotocopy Dokumen Pendukung Lainnya
                                </li>

                                <input type=\"hidden\" name=\"lampiranx[]\" id=\"lamp46\" value=\"14\" {$lampiran[13]}>
                                <input type=\"hidden\" name=\"lampiranx[]\" id=\"lamp47\" value=\"15\" {$lampiran[14]}>
                                <input type=\"hidden\" name=\"lampiranx[]\" id=\"lamp48\" value=\"16\" {$lampiran[15]}>
                            </ol>
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan6\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp49\" value=\"1\" {$lampiran[0]}> 
                                    Bukti penerimaan/Lembar expedisi berkas BPHTB (dicetak oleh Loket BPPRD)
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp50\" value=\"2\" {$lampiran[1]}> 
                                    SSPD-BPHTB Lunas 
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp51\" value=\"905\" {$lampiran[905]}> 
                                    Fotocopy Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran)
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp52\" value=\"926\" {$lampiran[926]}> 
                                    Fotocopy Akta Pendirian Perusahaan yang terbaru dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp53\" value=\"928\" {$lampiran[928]}>
                                    Fotocopy Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp55\" value=\"910\" {$lampiran[910]}> 
                                    Fotocopy AKTA
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp56\" value=\"913\" {$lampiran[913]}> 
                                    Fotocopy Dokumen Pendukung Lainnya
                                </li>
                                <input type=\"hidden\" name=\"lampiran[]\" id=\"lamp54\" value=\"6\" {$lampiran[5]}> 
                                <input type=\"hidden\" name=\"lampiran[]\" id=\"lamp57\" value=\"18\" {$lampiran[17]}>
                                <input type=\"hidden\" name=\"lampiran[]\" id=\"lamp58\" value=\"19\" {$lampiran[18]}>
                            </ol>                                                 
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan7\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp59\" value=\"1\" {$lampiran[0]}> Bukti penerimaan/Lembar expedisi berkas BPHTB (dicetak oleh Loket BPPRD)
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp60\" value=\"2\" {$lampiran[1]}> SSPD-BPHTB Lunas
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp61\" value=\"905\" {$lampiran[905]}> 
                                    Fotocopy Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran)
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp62\" value=\"916\" {$lampiran[916]}> Fotocopy KTP para ahli waris dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp63\" value=\"917\" {$lampiran[917]}>
                                    Fotocopy Surat/Keterangan Kematian dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp64\" value=\"925\" {$lampiran[925]}> 
                                    Fotocopy Surat Pernyataan Waris dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp65\" value=\"910\" {$lampiran[910]}> 
                                    Fotocopy Akta
                                </li>
								<li>
                                    <input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp66\" value=\"913\" {$lampiran[913]}> 
                                    Fotocopy Dokumen Pendukung Lainnya
                                </li>
                                <input type=\"hidden\" name=\"lampiran[]\"  id=\"lamp67\" value=\"21\" {$lampiran[20]}>
                                <input type=\"hidden\" name=\"lampiran[]\"  id=\"lamp68\" value=\"22\" {$lampiran[21]}>
								<!-- <li><input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp69\" value=\"23\" {$lampiran[22]}> Fotocopy Surat Pernyataan waris</li> -->
                            </ol>
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan8\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp70\" vavalue=\"1\" {$lampiran[0]}> 
                                    Bukti penerimaan/Lembar expedisi berkas BPHTB (dicetak oleh Loket BPPRD)
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp71\" valalue=\"2\" {$lampiran[1]}> SSPD-BPHTB Lunas
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp72\" e=\"905\" {$lampiran[905]}> 
                                    Fotocopy Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran)
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp73\" value=\"929\" {$lampiran[929]}>
                                    Fotocopy Kwitansi lelang/Risalah Lelang dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp74\" valvalue=\"910\" {$lampiran[910]}> 
                                    Fotocopy Akta
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp75\" valuvalue=\"913\" {$lampiran[913]}> 
                                    Fotocopy Dokumen Pendukung Lainnya
                                </li>
                                <input type=\"hidden\" name=\"lampiran[]\" id=\"lamp76\" value=\"7\" {$lampiran[6]}>
								<input type=\"hidden\" name=\"lampiran[]\" id=\"lamp77\" value=\"24\" {$lampiran[23]}>
                            </ol>                                                 
                        </td>                                                     
                      </tr>                                                       
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan9\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp78\" alue=\"1\" {$lampiran[0]}> 
                                    Bukti penerimaan/Lembar expedisi berkas BPHTB (dicetak oleh Loket BPPRD)
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp79\" lue=\"2\" {$lampiran[1]}> 
                                    SSPD-BPHTB Lunas  
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp80\" value=\"905\" {$lampiran[905]}> 
                                    Fotocopy Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran)
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp81\" value=\"930\" {$lampiran[930]}> 
                                    Fotocopy Keputusan Hakim/Pengadilan dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp82\" value=\"910\" {$lampiran[910]}> 
                                    Fotocopy Akta
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp83\" value=\"913\" {$lampiran[913]}> 
                                    Fotocopy Dokumen Pendukung Lainnya
                                </li>
                                <input type=\"hidden\" name=\"lampiran[]\" id=\"lamp84\" value=\"7\" {$lampiran[6]}>
								<input type=\"hidden\" name=\"lampiran[]\" id=\"lamp85\" value=\"25\" {$lampiran[24]}>
                            </ol>                                                 
                        </td>                                                     
                      </tr>                                                       
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan10\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp86\" value=\"1\" {$lampiran[0]}> 
                                    Bukti penerimaan/Lembar expedisi berkas BPHTB (dicetak oleh Loket BPPRD)
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp87\" value=\"2\" {$lampiran[1]}> 
                                    SSPD-BPHTB Lunas  
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp88\" value=\"905\" {$lampiran[905]}> 
                                    Fotocopy Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran)
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp89\" value=\"930\" {$lampiran[930]}> 
                                    Fotocopy Keputusan Hakim/Pengadilan dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp90\" value=\"926\" {$lampiran[926]}> 
                                    Fotocopy Akta Pendirian Perusahaan yang terbaru dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp91\" value=\"928\" {$lampiran[928]}>
                                    Fotocopy Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp92\" value=\"910\" {$lampiran[910]}> 
                                    Fotocopy Akta
                                </li>
								<li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp93\" value=\"913\" {$lampiran[913]}> 
                                    Fotocopy Dokumen Pendukung Lainnya
                                </li>
                                <input type=\"hidden\" name=\"lampiran[]\" id=\"lamp94\" value=\"18\" {$lampiran[17]}>
                                <input type=\"hidden\" name=\"lampiran[]\" id=\"lamp95\" value=\"19\" {$lampiran[18]}>
                            </ol>                                                 
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan11\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp96\"  value=\"1\" {$lampiran[0]}> 
                                    Bukti penerimaan/Lembar expedisi berkas BPHTB (dicetak oleh Loket BPPRD)
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp97\"  value=\"2\" {$lampiran[1]}> 
                                    SSPD-BPHTB Lunas 
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp98\"  value=\"905\" {$lampiran[905]}> 
                                    Fotocopy Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran)
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp99\"  value=\"926\" {$lampiran[926]}> 
                                    Fotocopy Akta Pendirian Perusahaan yang terbaru dilegalisir
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp100\"  value=\"928\" {$lampiran[928]}>
                                    Fotocopy Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya dilegalisir
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp101\"  value=\"910\" {$lampiran[910]}> 
                                    Fotocopy Akta
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp102\"  value=\"913\" {$lampiran[913]}> 
                                    Fotocopy Dokumen Pendukung Lainnya
                                </li>
								<input type=\"hidden\" name=\"lampiran[]\" id=\"lamp103\"  value=\"17\" {$lampiran[16]}>
                                <input type=\"hidden\" name=\"lampiran[]\" id=\"lamp104\"  value=\"18\" {$lampiran[17]}>
                                <input type=\"hidden\" name=\"lampiran[]\" id=\"lamp105\"  value=\"19\" {$lampiran[18]}>
                            </ol>                                                
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan12\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp106\"  value=\"1\" {$lampiran[0]}> 
                                    Bukti penerimaan/Lembar expedisi berkas BPHTB (dicetak oleh Loket BPPRD) 
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp107\"  value=\"2\" {$lampiran[1]}>
                                    SSPD-BPHTB Lunas
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp108\"  value=\"905\" {$lampiran[905]}> 
                                    Fotocopy Lunas PBB 5 tahun terakhir (Informasi data pembayaran) 
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp109\"  value=\"926\" {$lampiran[926]}> 
                                    Fotocopy Akta Pendirian Perusahaan yang terbaru dilegalisir
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp110\"  value=\"928\" {$lampiran[928]}> 
                                    Fotocopy Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya dilegalisir 
                                </li>
                                <li>
                                    <input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp111\"  value=\"910\" {$lampiran[910]}> 
                                    Fotocopy Akta
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp112\"  value=\"913\" {$lampiran[913]}> 
                                    Fotocopy Dokumen Pendukung Lainnya
                                </li>
								<input type=\"hidden\" name=\"lampiran[]\" id=\"lamp113\"  value=\"17\" {$lampiran[16]}>
                                <input type=\"hidden\" name=\"lampiran[]\" id=\"lamp114\"  value=\"18\" {$lampiran[17]}>
                                <input type=\"hidden\" name=\"lampiran[]\" id=\"lamp115\"  value=\"19\" {$lampiran[18]}>
                            </ol>                                                 
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan13\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp116\"  value=\"1\" {$lampiran[0]}> 
                                    Bukti penerimaan/Lembar expedisi berkas BPHTB (dicetak oleh Loket BPPRD)
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp117\"  value=\"2\" {$lampiran[1]}> 
                                    SSPD-BPHTB Lunas 
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp118\"  value=\"905\" {$lampiran[905]}> 
                                    Fotocopy Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran)
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp119\"  value=\"931\" {$lampiran[931]}> 
                                    Fotocopy Surat Pernyataan Hadiah dari yang mengalihkan hak dilegalisir
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp120\"  value=\"910\" {$lampiran[910]}> 
                                    Fotocopy Akta
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp121\"  value=\"913\" {$lampiran[913]}> 
                                    Fotocopy Dokumen Pendukung Lainnya
                                </li>
                                <input type=\"hidden\" name=\"lampiran[]\" id=\"lamp122\"  value=\"7\" {$lampiran[6]}>
								<input type=\"hidden\" name=\"lampiran[]\" id=\"lamp123\"  value=\"26\" {$lampiran[25]}>
                            </ol>                                                 
                        </td>                                                     
                      </tr>                                                       
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan14\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp124\"  value=\"1\" {$lampiran[0]}> 
                                    Bukti penerimaan/Lembar expedisi berkas BPHTB (dicetak oleh Loket BPPRD)
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp125\"  value=\"2\" {$lampiran[1]}> 
                                    SSPD-BPHTB Lunas 
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp126\"  value=\"905\" {$lampiran[905]}> 
                                    Fotocopy Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran)
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp127\"  value=\"932\" {$lampiran[932]}> 
                                    Fotocopy Surat Penegasan Persetujuan Pemberian Kredit (SP3K) dari bank dilegalisir
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp128\"  value=\"910\" {$lampiran[910]}> 
                                    Fotocopy Akta
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp129\"  value=\"913\" {$lampiran[913]}> 
                                    Fotocopy Dokumen Pendukung Lainnya
                                </li>
                                <input type=\"hidden\" name=\"lampiran[]\" id=\"lamp130\"  value=\"7\" {$lampiran[6]}>
								<input type=\"hidden\" name=\"lampiran[]\" id=\"lamp131\"  value=\"27\" {$lampiran[26]}>
                            </ol>                                                 
                        </td>                                                     
                      </tr>                                                       
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan21\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp132\"  value=\"1\" {$lampiran[0]}> 
                                    Bukti penerimaan/Lembar expedisi berkas BPHTB (dicetak oleh Loket BPPRD)
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp133\"  value=\"2\" {$lampiran[1]}> 
                                    SSPD-BPHTB Lunas 
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp134\"  value=\"905\" {$lampiran[905]}> 
                                    Fotocopy Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran)
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp135\"  value=\"933\" {$lampiran[933]}> Fotocopy Surat Pelepasan Hak Atas Tanah atau sejenisnya dari BPN dilegalisir
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp136\"  value=\"910\" {$lampiran[910]}> 
                                    Fotocopy Akta
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp137\"  value=\"913\" {$lampiran[913]}> 
                                    Fotocopy Dokumen Pendukung Lainnya
                                </li>
                                <input type=\"hidden\" name=\"lampiran[]\" id=\"lamp138\"  value=\"7\" {$lampiran[6]}>
								<input type=\"hidden\" name=\"lampiran[]\" id=\"lamp139\"  value=\"28\" {$lampiran[27]}>
                            </ol>                                                 
                        </td>                                                     
                      </tr>                                                       
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan22\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp140\"  valuee=\"1\" {$lampiran[0]}> 
                                    Bukti penerimaan/Lembar expedisi berkas BPHTB (dicetak oleh Loket BPPRD)
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp141\"  value=\"2\" {$lampiran[1]}> 
                                    SSPD-BPHTB Lunas 
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp142\"  value=\"905\" {$lampiran[905]}> 
                                    Fotocopy Bukti Lunas PBB 5 tahun terakhir (Informasi data pembayaran)
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp143\"  value=\"933\" {$lampiran[933]}> Fotocopy Surat Pelepasan Hak Atas Tanah atau sejenisnya dari BPN dilegalisir
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp144\"  value=\"910\" {$lampiran[910]}> 
                                    Fotocopy Akta
                                </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp145\"  value=\"913\" {$lampiran[913]}> 
                                    Fotocopy Dokumen Pendukung Lainnya
                                </li>
                                <input type=\"hidden\" name=\"lampiran[]\" id=\"lamp146\"  value=\"7\" {$lampiran[6]}>
                            </ol>                                                 
                        </td>                                                     
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan30\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp1\" value=\"1\" {$lampiran[0]}> Formulir penyampaian SSPD BPHTB</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp2\"  value=\"2\" {$lampiran[1]}> SSPD-BPHTB</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp3\"  value=\"3\" {$lampiran[2]}> Fotocopy KTP WP yang masih berlaku/Keterangan domisili</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp4\"  value=\"4\" {$lampiran[3]}> Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp5\"  value=\"5\" {$lampiran[4]}> Fotocopy SPPT yang sedang berjalan</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp6\"  value=\"6\" {$lampiran[5]}> Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp7\"  value=\"7\" {$lampiran[6]}> Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</li>
                                <!-- <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp8\"  value=\"8\" {$lampiran[7]}> Daftar harga/Pricelist dalam hal pembelian dan pengembangan</li> -->
								<li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp9\"  value=\"9\" {$lampiran[8]}> Fotocopy Bukti transaksi/rincian pembayaran</li>
                            </ol>
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan31\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                             <ol id=\"lampiran\" style=\"margin-left: -20px;\">
								<li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp38\" value=\"1\" {$lampiran[0]}> Formulir penyampaian SSPD BPHTB </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp39\" value=\"2\" {$lampiran[1]}> SSPD-BPHTB</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp40\" value=\"3\" {$lampiran[2]}> Fotocopy KTP WP yang masih berlaku/Keterangan domisili</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp41\" value=\"4\" {$lampiran[3]}> Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp42\" value=\"5\" {$lampiran[4]}> Fotocopy SPPT yang sedang berjalan</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp43\" value=\"6\" {$lampiran[5]}> Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp44\" value=\"7\" {$lampiran[6]}> Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</li>
								<li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp45\" value=\"13\" {$lampiran[12]}> Fotocopy KTP Para ahli Waris/penerima Hibah Wasiat</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp46\" value=\"14\" {$lampiran[13]}> Fotocopy Surat/Keterangan Kematian</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp47\" value=\"15\" {$lampiran[14]}> Fotocopy Surat Pernyataan Waris</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp48\" value=\"16\" {$lampiran[15]}> Fotocopy Surat Kuasa Waris dalam hal Dikuasakan</li>
                            </ol>
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan32\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp18\" value=\"1\" {$lampiran[0]}> Formulir penyampaian SSPD BPHTB</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp19\" value=\"2\" {$lampiran[1]}> SSPD-BPHTB</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp20\" value=\"3\" {$lampiran[2]}> Fotocopy KTP WP yang masih berlaku/Keterangan domisili</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp21\" value=\"4\" {$lampiran[3]}> Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp22\" value=\"5\" {$lampiran[4]}> Fotocopy SPPT yang sedang berjalan</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp23\" value=\"6\" {$lampiran[5]}> Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp24\" value=\"7\" {$lampiran[6]}> Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp25\" value=\"11\" {$lampiran[10]}> Fotocopy KTP Pemberi dan Penerima Hibah yang masih berlaku</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" id=\"lamp26\" value=\"12\" {$lampiran[11]}> Pertanyaan Hibah/Surat keterangan Hibah yang diketahui oleh Kepala Desa </li>
								
                            </ol>
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan33\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li><input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp59\" value=\"1\" {$lampiran[0]}> Formulir penyampaian SSPD BPHTB</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp60\" value=\"2\" {$lampiran[1]}> SSPD-BPHTB </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp61\" value=\"3\" {$lampiran[2]}> Fotocopy KTP WP yang masih berlaku/Keterangan domisili</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp62\" value=\"4\" {$lampiran[3]}> Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp63\" value=\"5\" {$lampiran[4]}> Fotocopy SPPT yang sedang berjalan </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp64\" value=\"6\" {$lampiran[5]}> Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009 </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp65\" value=\"7\" {$lampiran[6]}> Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir </li>
								<li><input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp66\" value=\"20\" {$lampiran[19]}> Fotocopy KTP para ahli waris </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp67\" value=\"21\" {$lampiran[20]}> Fotocopy Surat/keterangan Kematian </li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp68\" value=\"22\" {$lampiran[21]}> Fotocopy Surat Pernyataan waris </li>
								<li><input type=\"checkbox\" name=\"lampiran[]\"  id=\"lamp69\" value=\"23\" {$lampiran[22]}> Fotocopy Surat Pernyataan waris</li>
                            </ol>
                        </td>
                      </tr>
                      <tr><td colspan=\"2\"><h3>&nbsp;</h3></td></tr>                                                                          
                      <tr>
                        <td width=\"100%\" colspan=\"2\" valign=\"top\" align=\"center\">";
    $html .= (isset($_REQUEST['svcid'])) ? "<input type=\"submit\" name=\"btn-save\" id=\"btn-simpan\" value=\"Update\" />&nbsp;" : "<input type=\"submit\" name=\"btn-save\" id=\"btn-simpan\" value=\"Simpan\" />&nbsp;";

    $html.= "<input type=\"button\" name = \"btn-cancel\" id = \"btn-cancel\" value=\"Batal\" onclick='window.location.href=\"main.php?param=" . base64_encode("a=" . $a . "&m=" . $m . "") . "\"' />
                </td>
                      </tr>
                </table>
                </td>
              </tr>
              <tr>
                <td colspan=\"2\">&nbsp;</td>
              </tr>                        
              <tr>
                <td colspan=\"2\" align=\"center\" valign=\"middle\"></td>
            </tr>
            <tr>
              <td colspan=\"2\" align=\"center\" valign=\"middle\">&nbsp;</td>
            </tr>
      </table>
    </form></div>";
    return $html;
}

function save($status="") {
    global $data, $DBLink, $uname;
    $lampiran = implode(";", $_POST['lampiran']);
    $jumSyarat = array(1 => 1, 2 => 1, 3 => 1, 4 => 1, 5=> 1, 6=> 1, 7=> 1, 8 => 1, 9 => 1, 10 => 1, 11 => 1, 12 => 1, 13 => 1, 14 => 1, 21 => 1, 22 => 1, 30 => 1, 31 => 1, 32 => 1, 33 => 1);
    $status = (count($_POST['lampiran']) >= $jumSyarat[$_POST['jnsPerolehan']]) ? 1 : 0;
	$jp= @isset($_REQUEST['jnsPerolehan']) ? $_REQUEST['jnsPerolehan'] : "";
	for($i=1;$i<=146;$i++){
		if (isset($_REQUEST['file_lamp'.$i]) && $_REQUEST['file_lamp'.$i]!=""){
			
			echo $_REQUEST['file_lamp'.$i];
		
		}
	}
    //echo $lampiran;exit;
    $qry = sprintf("INSERT INTO cppmod_ssb_berkas (
            CPM_BERKAS_NOP,CPM_BERKAS_TANGGAL,CPM_BERKAS_LAMPIRAN,
            CPM_BERKAS_PETUGAS,CPM_BERKAS_ALAMAT_OP,CPM_BERKAS_KELURAHAN_OP, 
             CPM_BERKAS_KECAMATAN_OP,CPM_BERKAS_NPWP,CPM_BERKAS_NAMA_WP,
            CPM_BERKAS_JNS_PEROLEHAN,CPM_BERKAS_NOPEL,CPM_BERKAS_STATUS, 
            CPM_BERKAS_HARGA_TRAN, CPM_BERKAS_TELP_WP, CPM_BERKAS_NOTARIS,CPM_BERKAS_TELP_OP
            ) VALUES ('%s','%s','%s',
                    '%s','%s','%s',                    
                    '%s','%s','%s',
                    '%s','%s',{$status},
                    '%s','%s','%s','%s')", mysqli_escape_string($DBLink, $_POST['nop']), mysqli_escape_string($DBLink, $_POST['tglMasuk']), $lampiran, mysqli_escape_string($DBLink, $_SESSION['username']), mysqli_escape_string($DBLink, $_POST['alamatOp']), mysqli_escape_string($DBLink, $_POST['kelurahanOp']), mysqli_escape_string($DBLink, $_POST['kecamatanOp']), mysqli_escape_string($DBLink, $_POST['npwp']), mysqli_escape_string($DBLink, $_POST['namaWp']), mysqli_escape_string($DBLink, $_POST['jnsPerolehan']), mysqli_escape_string($DBLink, $_POST['noPel']), mysqli_escape_string($DBLink, $_POST['hargaTran']), mysqli_escape_string($DBLink, $_POST['telpWp']), mysqli_escape_string($DBLink, $_POST['notaris']),mysqli_escape_string($DBLink, $_POST['telppnjl']));

    $res = mysqli_query($DBLink, $qry);
    if ($res) {
        echo 'Data berhasil disimpan...!';
        $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'];
        echo "<script language='javascript'>
                $(document).ready(function(){
                    window.location = \"./main.php?param=" . base64_encode($params) . "\"
                })
              </script>";
    } else {
        $err = mysqli_error($DBLink);
        echo strpos(strtolower($err), "duplicate") === false ? $err : "Ada kesalahan! dokumen sudah pernah diinput! data sudah tersedia dan silakan periksa pada tabel.";
    }
}

function update($status="") {
    global $data, $DBLink, $uname;
    // var_dump($_POST['tglMasuk']);die;
    $tgl_berkas = date('d-m-Y', strtotime($_POST['tglMasuk']));
    $lampiran = implode(";", $_POST['lampiran']);
    $jumSyarat = array(1 => 1, 2 => 1, 3 => 1, 4 => 1, 5=> 1, 6=> 1, 7=> 1, 8 => 1, 9 => 1, 10 => 1, 11 => 1, 12 => 1, 13 => 1, 14 => 1, 21 => 1, 22 => 1, 30 => 1, 31 => 1, 32 => 1, 33 => 1);
    $status = (count($_POST['lampiran']) >= $jumSyarat[$_POST['jnsPerolehan']]) ? 1 : 0; 
	
    $qry = sprintf("UPDATE cppmod_ssb_berkas SET        
            CPM_BERKAS_NOPEL = '" . mysqli_escape_string($DBLink, $_POST['noPel']) . "',
            CPM_BERKAS_JNS_PEROLEHAN = '{$_POST['jnsPerolehan']}',
            CPM_BERKAS_LAMPIRAN ='{$lampiran}',
            CPM_BERKAS_TANGGAL ='{$tgl_berkas}',
            CPM_BERKAS_PETUGAS = '" . mysqli_escape_string($DBLink, $_SESSION['username']) . "',
                
            CPM_BERKAS_NOP = '" . mysqli_escape_string($DBLink, $_POST['nop']) . "',
            CPM_BERKAS_ALAMAT_OP = '" . mysqli_escape_string($DBLink, $_POST['alamatOp']) . "',
            CPM_BERKAS_KELURAHAN_OP = '" . mysqli_escape_string($DBLink, $_POST['kelurahanOp']) . "', 
            CPM_BERKAS_KECAMATAN_OP = '" . mysqli_escape_string($DBLink, $_POST['kecamatanOp']) . "',
            CPM_BERKAS_TELP_OP = '" . mysqli_escape_string($DBLink, $_POST['telppnjl']) . "',
            CPM_KOORDINAT_OP = '" . mysqli_escape_string($DBLink, $_POST['koordinatOp']) . "',
            CPM_BERKAS_NPWP = '" . mysqli_escape_string($DBLink, $_POST['npwp']) . "',
            CPM_BERKAS_NAMA_WP = '" . mysqli_escape_string($DBLink, $_POST['namaWp']) . "',  
            CPM_BERKAS_NAMA_WP = '" . mysqli_escape_string($DBLink, $_POST['namaWp']) . "',  
            
            CPM_BERKAS_HARGA_TRAN = '" . mysqli_escape_string($DBLink, $_POST['hargaTran']) . "',
            CPM_BERKAS_TELP_WP = '" . mysqli_escape_string($DBLink, $_POST['telpWp']) . "',
			CPM_BERKAS_NOTARIS = '" . mysqli_escape_string($DBLink, $_POST['notaris']) . "',

            CPM_BERKAS_STATUS = '{$status}'
            WHERE CPM_BERKAS_ID = '" . mysqli_escape_string($DBLink, $_POST['idssb']) . "'");
	// echo $qry;exit;
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }

    if ($res) {
        echo 'Data berhasil diupdate...!';
        $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'];
        echo "<script language='javascript'>
                $(document).ready(function(){
                    window.location = \"./main.php?param=" . base64_encode($params) . "\"
                })
              </script>";
    } else {
        echo mysqli_error($DBLink);
    }
}

$appConfig = $User->GetAppConfig($application);
$arConfig = $User->GetModuleConfig($m);
$save = null;
if(isset($_REQUEST['process'])){
    $save = $_REQUEST['process'];
}

if ($save == 'Simpan') {
    save();
} elseif ($save == 'Update') {
    update();
} else {
    $svcid = @isset($_REQUEST['svcid']) ? $_REQUEST['svcid'] : "";

    echo "<script language=\"javascript\">var axx = '" . base64_encode($_REQUEST['a']) . "'</script> ";
    echo formPenerimaan($value=null);
}
?>

