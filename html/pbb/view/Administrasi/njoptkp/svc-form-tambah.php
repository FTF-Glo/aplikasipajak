<?php 
session_start();

error_reporting(E_ERROR);
ini_set('display_errors', 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'Administrasi'.DIRECTORY_SEPARATOR.'njoptkp', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/json.php");

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

$rowsData = "
		  <form>
			<fieldset>
				<label for=\"nilai_bawah\">Nilai Bawah</label>
				<input type=\"text\" name=\"nilai_bawah\" id=\"nilai_bawah\" onkeypress=\"return iniAngka(event, this)\" class=\"text ui-widget-content ui-corner-all\">
				<label for=\"nilai_atas\">Nilai Atas</label>
				<input type=\"text\" name=\"nilai_atas\" id=\"nilai_atas\" onkeypress=\"return iniAngka(event, this)\" class=\"text ui-widget-content ui-corner-all\">
				<label for=\"njoptkp\">NJOPTKP</label>
				<input type=\"text\" name=\"njoptkp\" id=\"njoptkp\" onkeypress=\"return iniAngka(event, this)\" class=\"text ui-widget-content ui-corner-all\">
			</fieldset>
		  </form>";
$response['id'] 	= "";
$response['table'] 	= $rowsData;

exit($json->encode($response));