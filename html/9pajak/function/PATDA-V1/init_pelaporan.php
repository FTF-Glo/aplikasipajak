<?php
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$lapor = new LaporPajak();
$lapor->read_dokumen();

//npwpd & nop akan diisi sesuai param url (defualt empty)
$npwpd = isset($npwpd)? $npwpd : '';
$nop = isset($nop)? $nop : '';

//jika npwpd diisi oleh session jika session terisi
if(isset($_SESSION['npwpd']) && !empty($_SESSION['npwpd'])) 
	$npwpd = $_SESSION['npwpd'];

$npwpd = preg_replace("/[^A-Za-z0-9 ]/", '', $npwpd);

//get data form by npwpd & nop
$DATA = $lapor->get_pajak($npwpd, $nop);

//npwpd & nop diisi dari database jika tersedia (tidak lagi dari param url)
if(!empty($DATA['profil']['CPM_NPWPD'])) 
	$npwpd = $DATA['profil']['CPM_NPWPD'];
	
if(!empty($DATA['profil']['CPM_NOP'])) 
	$nop = $DATA['profil']['CPM_NOP'];

//init data author
$DATA['pajak']['CPM_AUTHOR'] = ($DATA['pajak']['CPM_AUTHOR'] == "")? $data->uname : $DATA['pajak']['CPM_AUTHOR'];

//init variable global
$edit = ($lapor->_id != "") ? true : false;
$readonly = ($edit) ? "readonly" : "";

$config_terlambat_lap = $lapor->get_config_terlambat_lap($lapor->id_pajak);
$editable_terlambat_lap = $config_terlambat_lap->editable;
$editable_terlambat_lap = ($lapor->_s == 1 || $lapor->_s == "" || ($lapor->_s == "4" && $lapor->_mod == "pel")) ? $editable_terlambat_lap : 1;
$persen_terlambat_lap = $config_terlambat_lap->persen;

?>
