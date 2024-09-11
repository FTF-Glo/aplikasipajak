<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/json.php"); 
require_once("ctrQS.php");  
$json = new Services_JSON();  


$ServerAddress = '127.0.0.1';//$svr_param->ServerAddress;
$ServerPort = '23020';//$svr_param->ServerPort;
$ServerTimeOut = '120';//$svr_param->ServerTimeOut;
$LoadedKey = 'oracle-pidiejaya';


/*
{"PAN":"11000","f":"pbbv21.updatepaymentstatus",
        "IS_VALIDATE":0,
        "RC":"0000
","UID":"000000000015","o":"[{\"EFFECTED_ROWS\":\"1\"}]",
 * "i":{"WHERE":"KD_PROPINSI = '19' AND KD_DATI2= '71' AND KD_KECAMATAN ='020' AND KD_KELURAHAN= '002' AND KD_BLOK
= '004' AND NO_URUT= '0056' AND KD_JNS_OP= '0' AND THN_PAJAK_SPPT = '1997' AND (STATUS_PEMBAYARAN_SPPT IS NULL OR STATUS_PEMBAYARAN_SPPT!=1) AND ROWNUM = 1","STATUS_PEM
BAYARAN_SPPT":"'1'"}}*/

/*$req = array();
$req['PAN'] = '11000';
$req['f'] = 'pendataan.updateDatObjekPajak';
$req['i'] = array('KD_PROPINSI'=>'11', 
                    'KD_DATI2'=>'19',
                    'KD_KECAMATAN'=>'060',
                    'KD_KELURAHAN'=>'036',
                    'KD_BLOK'=>'001',
                    'NO_URUT'=>'0216',
                    'KD_JNS_OP'=>'0',
                    'JALAN_OP'=>'JL. MANDIRI 5',
                    'BLOK_KAV_NO_OP'=>'no 50',
                    'KD_STATUS_WP'=>'1',
                    'RT_OP'=>'21',
                    'RW_OP'=>'22',
                    'SUBJEK_PAJAK_ID'=>'111906003600102160',
                    'NIP_PENDATA'=>'060000000',
                    'TGL_PEREKAMAN_OP'=>'2008/06/06 00:00:00',
                    'NIP_PEREKAM_OP'=>'060000000',
                    'TOTAL_LUAS_BUMI'=>'200',
                    'TOTAL_LUAS_BNG'=>'300'
    );
$req['LOADEDKEY']= 'oracle-pidiejaya';
$req['UID']= '000000000175';*/

/*$req = array();
$req['PAN'] = '11000';
$req['f'] = 'pendataan.insertDatObjekPajak';
$req['i'] = array('KD_PROPINSI'=>'11', 
                    'KD_DATI2'=>'19',
                    'KD_KECAMATAN'=>'060',
                    'KD_KELURAHAN'=>'036',
                    'KD_BLOK'=>'001',
                    'NO_URUT'=>'9999',
                    'KD_JNS_OP'=>'0',
                    'JALAN_OP'=>'JL. MANDIRI',
                    'BLOK_KAV_NO_OP'=>'no 10',
                    'KD_STATUS_WP'=>'1',
                    'RT_OP'=>'21',
                    'RW_OP'=>'22',
                    'SUBJEK_PAJAK_ID'=>'111906003600102160',
                    'NIP_PENDATA'=>'060000000',
                    'NIP_PEREKAM_OP'=>'060000000',
                    'NIP_PEMERIKSA_OP'=>'060000000',
                    'TGL_PENDATAAN_OP'=>'2008/06/06 00:00:00',
                    'TGL_PEREKAMAN_OP'=>'2008/06/06 00:00:00',
                    'TGL_PEMERIKSAAN_OP'=>'2008/06/06 00:00:00',
                    'TOTAL_LUAS_BUMI'=>'200',
                    'TOTAL_LUAS_BNG'=>'300',
                    'NO_FORMULIR_SPOP'=>'-'
    );
$req['LOADEDKEY']= 'oracle-pidiejaya';
$req['UID']= '000000000175';*/

/*$req = array();
$req['PAN'] = '11000';
$req['f'] = 'pendataan.updateDatSubjekPajak';
$req['i'] = array('STATUS_PEKERJAAN_WP'=>'4', 
                    'NM_WP'=>'AHMAD GOZALI',
                    'JALAN_WP'=>'JL. MANDIRI 5',
                    'BLOK_KAV_NO_WP'=>'No 50',
                    'KELURAHAN_WP'=>'CIBEUNYING KALER',
                    'RT_WP'=>'21',
                    'RW_WP'=>'22',
                    'KOTA_WP'=>'BANDUNG',
                    'KD_POS_WP'=>'44112',
                    'TELP_WP'=>'085659611122',
                    'SUBJEK_PAJAK_ID'=>'111906003600102160'
    );
$req['LOADEDKEY']= 'oracle-pidiejaya';
$req['UID']= '000000000175';*/

/*$req = array();
$req['PAN'] = '11000';
$req['f'] = 'pendataan.insertDatSubjekPajak';
$req['i'] = array('STATUS_PEKERJAAN_WP'=>'4', 
                    'NM_WP'=>'AHMAD GOZALI',
                    'JALAN_WP'=>'JL. MANDIRI 5',
                    'BLOK_KAV_NO_WP'=>'No 50',
                    'KELURAHAN_WP'=>'CIBEUNYING KALER',
                    'RT_WP'=>'21',
                    'RW_WP'=>'22',
                    'KOTA_WP'=>'BANDUNG',
                    'KD_POS_WP'=>'44112',
                    'TELP_WP'=>'085659611122',
                    'SUBJEK_PAJAK_ID'=>'111906003600199990'
    );
$req['LOADEDKEY']= 'oracle-pidiejaya';
$req['UID']= '000000000175';*/

$fName = 'pendataan.selectDatSubjekPajak';
$req = array('SUBJEK_PAJAK_ID'=>'111906003600199990');
//{"f":"pendataan.selectDatSubjekPajak","PAN":"11000","LOADEDKEY":"oracle-pidiejaya","RC":"0000","UID":"000000000175","o":"[{\"RT_WP\":\"21 \",\"BLOK_KAV_NO_WP\":\"No 50\",\"NM_WP\":\"AHMAD GOZALI\",\"NPWP\":null,\"RW_WP\":\"22\",\"SUBJEK_PAJAK_ID\":\"111906003600199990 \",\"STATUS_PEKERJAAN_WP\":\"4\",\"TELP_WP\":\"085659611122\",\"KOTA_WP\":\"BANDUNG\",\"JALAN_WP\":\"JL. MANDIRI 5\",\"KD_POS_WP\":\"44112\",\"KELURAHAN_WP\":\"CIBEUNYING KALER\"}]","i":{"SUBJEK_PAJAK_ID":"111906003600199990"}}
//{"f":"pendataan.selectDatSubjekPajak","PAN":"11000","LOADEDKEY":"oracle-pidiejaya","RC":"0014","UID":"000000000175","o":null,"i":{"SUBJEK_PAJAK_ID":"111906003600188880"}}

/*$req = array();
$req['PAN'] = '11000';
$req['f'] = 'pendataan.updateDatOpBumi';
$req['i'] = array('KD_PROPINSI'=>'11', 
                    'KD_DATI2'=>'19',
                    'KD_KECAMATAN'=>'060',
                    'KD_KELURAHAN'=>'036',
                    'KD_BLOK'=>'001',
                    'NO_URUT'=>'0216',
                    'KD_JNS_OP'=>'0',
                    'KD_ZNT'=>'AC',
                    'LUAS_BUMI'=>'300',
                    'JNS_BUMI'=>'2'
    );
$req['LOADEDKEY']= 'oracle-pidiejaya';
$req['UID']= '000000000175';*/

/*$req = array();
$req['PAN'] = '11000';
$req['f'] = 'pendataan.insertDatOpBumi';
$req['i'] = array('KD_PROPINSI'=>'11', 
                    'KD_DATI2'=>'19',
                    'KD_KECAMATAN'=>'060',
                    'KD_KELURAHAN'=>'036',
                    'KD_BLOK'=>'001',
                    'NO_URUT'=>'9999',
                    'KD_JNS_OP'=>'0',
                    'KD_ZNT'=>'AC',
                    'LUAS_BUMI'=>'300',
                    'JNS_BUMI'=>'2'
    );
$req['LOADEDKEY']= 'oracle-pidiejaya';
$req['UID']= '000000000175';*/

/*$req = array();
$req['PAN'] = '11000';
$req['f'] = 'pendataan.updateDatOpBangunan';
$req['i'] = array('KD_PROPINSI'=>'11', 
                    'KD_DATI2'=>'19',
                    'KD_KECAMATAN'=>'060',
                    'KD_KELURAHAN'=>'036',
                    'KD_BLOK'=>'001',
                    'NO_URUT'=>'0199',
                    'KD_JNS_OP'=>'0',
                    'NO_BNG'=>'1',
                    'KD_JPB'=>'01',
                    'THN_DIBANGUN_BNG'=>'1998',
                    'THN_RENOVASI_BNG'=>'2012',
                    'LUAS_BNG'=>'50',
                    'JML_LANTAI_BNG'=>'2',
                    'KONDISI_BNG'=>'1',
                    'JNS_KONSTRUKSI_BNG'=>'1',
                    'JNS_ATAP_BNG'=>'1',
                    'KD_DINDING'=>'1',
                    'KD_LANTAI'=>'1',
                    'KD_LANGIT_LANGIT'=>'1'
    );
$req['LOADEDKEY']= 'oracle-pidiejaya';
$req['UID']= '000000000175';*/

/*$req = array();
$req['PAN'] = '11000';
$req['f'] = 'pendataan.insertDatOpBangunan';
$req['i'] = array('KD_PROPINSI'=>'11', 
                    'KD_DATI2'=>'19',
                    'KD_KECAMATAN'=>'060',
                    'KD_KELURAHAN'=>'036',
                    'KD_BLOK'=>'001',
                    'NO_URUT'=>'9999',
                    'KD_JNS_OP'=>'0',
                    'NO_BNG'=>'1',
                    'KD_JPB'=>'01',
                    'THN_DIBANGUN_BNG'=>'1998',
                    'THN_RENOVASI_BNG'=>'2012',
                    'LUAS_BNG'=>'50',
                    'JML_LANTAI_BNG'=>'2',
                    'KONDISI_BNG'=>'1',
                    'JNS_KONSTRUKSI_BNG'=>'1',
                    'JNS_ATAP_BNG'=>'1',
                    'KD_DINDING'=>'1',
                    'KD_LANTAI'=>'1',
                    'KD_LANGIT_LANGIT'=>'1',
                    'NO_FORMULIR_LSPOP'=>'-',
                    'NILAI_SISTEM_BNG'=>'0',
                    'TGL_PENDATAAN_BNG'=>'2008/06/06 00:00:00',
                    'NIP_PENDATA_BNG'=>'060000000',
                    'TGL_PEMERIKSAAN_BNG'=>'2008/06/06 00:00:00',
                    'NIP_PEMERIKSA_BNG'=>'060000000',
                    'NIP_PEREKAM_BNG'=>'060000000'
    );
$req['LOADEDKEY']= 'oracle-pidiejaya';
$req['UID']= '000000000175';*/

/*$req = array();
$req['PAN'] = '11000';
$req['f'] = 'pendataan.selectDatOpBangunan';
$req['i'] = array('KD_PROPINSI'=>'11', 
                    'KD_DATI2'=>'19',
                    'KD_KECAMATAN'=>'060',
                    'KD_KELURAHAN'=>'036',
                    'KD_BLOK'=>'001',
                    'NO_URUT'=>'9999',
                    'KD_JNS_OP'=>'0',
                    'NO_BNG'=>'1'
    );
$req['LOADEDKEY']= 'oracle-pidiejaya';
$req['UID']= '000000000175';*/

/*$req = array();
$req['PAN'] = '11000';
$req['f'] = 'pendataan.insertDatFasilitasBangunan';
$req['i'] = array('KD_PROPINSI'=>'11', 
                    'KD_DATI2'=>'19',
                    'KD_KECAMATAN'=>'060',
                    'KD_KELURAHAN'=>'036',
                    'KD_BLOK'=>'001',
                    'NO_URUT'=>'0199',
                    'KD_JNS_OP'=>'0',
                    'NO_BNG'=>'1',
                    'KD_FASILITAS'=>'44',
                    'JML_SATUAN'=>'700'
    );
$req['LOADEDKEY']= 'oracle-pidiejaya';
$req['UID']= '000000000175';*/


/*$req = array();
$req['PAN'] = '11000';
$req['f'] = 'pendataan.deleteDatFasilitasBangunan';
$req['i'] = array('KD_PROPINSI'=>'11', 
                    'KD_DATI2'=>'19',
                    'KD_KECAMATAN'=>'060',
                    'KD_KELURAHAN'=>'036',
                    'KD_BLOK'=>'001',
                    'NO_URUT'=>'0199',
                    'KD_JNS_OP'=>'0',
                    'NO_BNG'=>'1',
                    'KD_FASILITAS'=>'44'
    );
$req['LOADEDKEY']= 'oracle-pidiejaya';
$req['UID']= '000000000175*/

//$sRequestStream = $json->encode($req);

//echo $sRequestStream; exit();

$centralQS = new centralQS($ServerAddress, $ServerPort, $ServerTimeOut, $LoadedKey, $json); 
$res = array();
$bOK = $centralQS->SqlExec($req, $fName, $res);
echo '<pre>';
print_r($res);
echo '</pre>';
//$bOK = GetRemoteResponse($ServerAddress, $ServerPort, $ServerTimeOut, $sRequestStream, $sResp);
//
//if ($bOK == 0) {
//    $sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
//    echo $sResp;
//}
?>


<!--
if(rsSelectFasilitas.getObject(1).toString().equals("44")){
map.put("CPM_OP_DAYA", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("12")){
map.put("CPM_FOP_KOLAM_LUAS", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("14")){
map.put("CPM_FOP_PERKERASAN_RINGAN", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("16")){
map.put("CPM_FOP_PERKERASAN_BERAT", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("18")){
map.put("CPM_FOP_TENIS_LAMPU_BETON", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("19")){
map.put("CPM_FOP_TENIS_LAMPU_ASPAL", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("20")){
map.put("CPM_FOP_TENIS_LAMPU_TANAH", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("21")){
map.put("CPM_FOP_TENIS_TANPA_LAMPU_BETON", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("22")){
map.put("CPM_FOP_TENIS_TANPA_LAMPU_ASPAL", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("23")){
map.put("CPM_FOP_TENIS_TANPA_LAMPU_TANAH", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("30")){
map.put("CPM_FOP_LIFT_PENUMPANG", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("31")){
map.put("CPM_FOP_LIFT_KAPSUL", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("32")){
map.put("CPM_FOP_LIFT_BARANG", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("33")){
map.put("CPM_FOP_ESKALATOR_SEMPIT", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("34")){
map.put("CPM_FOP_ESKALATOR_LEBAR", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("35")){
map.put("CPM_PAGAR_BESI_PANJANG", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("36")){
map.put("CPM_PAGAR_BATA_PANJANG", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("41")){
map.put("CPM_FOP_SALURAN", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("42")){
map.put("CPM_FOP_SUMUR", Common.truncate(rsSelectFasilitas.getObject(2).toString(),4));
}else if(rsSelectFasilitas.getObject(1).toString().equals("11")){
map.put("CPM_FOP_AC_CENTRAL", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("37")){
map.put("CPM_PEMADAM_HYDRANT", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("38")){
map.put("CPM_PEMADAM_SPRINKLER", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("39")){
map.put("CPM_PEMADAM_FIRE_ALARM", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("01")){
map.put("CPM_FOP_AC_SPLIT", rsSelectFasilitas.getObject(2).toString());
}else if(rsSelectFasilitas.getObject(1).toString().equals("02")){
map.put("CPM_FOP_AC_WINDOW", rsSelectFasilitas.getObject(2).toString());
}
-->