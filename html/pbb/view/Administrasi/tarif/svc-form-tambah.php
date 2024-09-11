<?php
session_start();

error_reporting(E_ERROR);
ini_set('display_errors', 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'Administrasi' . DIRECTORY_SEPARATOR . 'tarif', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/json.php");

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

$rowsData = "
		  <form>
			<fieldset>
				<label for=\"trf_nilai_bawah\">Nilai Bawah</label>
				<input type=\"text\" name=\"trf_nilai_bawah\" id=\"trf_nilai_bawah\" onkeypress=\"return iniAngka(event, this)\" class=\"text ui-widget-content ui-corner-all\">
				<label for=\"trf_nilai_atas\">Nilai Atas</label>
				<input type=\"text\" name=\"trf_nilai_atas\" id=\"trf_nilai_atas\" onkeypress=\"return iniAngka(event, this)\" class=\"text ui-widget-content ui-corner-all\">
				<label for=\"tarif\">Tarif</label>
				<input type=\"text\" name=\"tarif\" id=\"tarif\" class=\"text ui-widget-content ui-corner-all\">
			</fieldset>
		  </form>";
$response['id'] 	= "";
$response['table'] 	= $rowsData;

exit($json->encode($response));
