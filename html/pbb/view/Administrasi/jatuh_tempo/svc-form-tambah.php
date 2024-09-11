<?php
session_start();

error_reporting(E_ERROR);
ini_set('display_errors', 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'Administrasi' . DIRECTORY_SEPARATOR . 'jatuh_tempo', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/json.php");

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

$rowsData = "
		<form>
			<fieldset>
				<label for=\"tgl_awal\">Tanggal Penetapan Awal</label>
				<input type=\"text\" name=\"tgl_awal_penetapan\" id=\"tgl_awal_penetapan\" maxlength=\"5\" class=\"text ui-widget-content ui-corner-all\">
				<label for=\"tgl_akhir\">Tanggal Penetapan Akhir</label>
				<input type=\"text\" name=\"tgl_akhir_penetapan\" id=\"tgl_akhir_penetapan\" maxlength=\"5\" class=\"text ui-widget-content ui-corner-all\">
				<label for=\"tgl_jatuh_tempo\">Tanggal Jatuh Tempo</label>
				<input type=\"text\" name=\"tgl_jatuh_tempo\" id=\"tgl_jatuh_tempo\" maxlength=\"5\" class=\"text ui-widget-content ui-corner-all\">
			</fieldset>
		</form>
		<script>
			$(document).ready(function(){
				$( \"#tgl_awal_penetapan\" ).datepicker({dateFormat:'mm-dd'});
				$( \"#tgl_akhir_penetapan\" ).datepicker({dateFormat:'mm-dd'});
				$( \"#tgl_jatuh_tempo\" ).datepicker({dateFormat:'mm-dd'});
			})";
$response['id'] 	= "";
$response['table'] 	= $rowsData;

exit($json->encode($response));
