<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'dbkb', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/central/user-central.php");


echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
//echo "<script language=\"javascript\" src=\"view/PBB/loket/mod-tax-service-print.js\" type=\"text/javascript\"></script>\n";

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

class DBKBJPB2
{
	function __construct($userGroup, $user)
	{
		$this->userGroup = $userGroup;
		$this->user = $user;
	}

	function getTotalRows($query)
	{
		global $DBLink;
		// echo $query;
		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			echo $query . "<br>";
			echo mysqli_error($DBLink);
		}

		$row = mysqli_fetch_array($res);
		return $row['TOTALROWS'];
	}

	function mysql2json($mysql_result, $name)
	{
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
			$json .= "{\n";
			for ($y = 0; $y < count($field_names); $y++) {
				$json .= "'$field_names[$y]' :	'" . addslashes($row[$y]) . "'";
				if ($y == count($field_names) - 1) {
					$json .= "\n";
				} else {
					$json .= ",\n";
				}
			}
			if ($x == $rows - 1) {
				$json .= "\n}\n";
			} else {
				$json .= "\n},\n";
			}
		}
		$json .= "]\n}";
		return ($json);
	}

	function getDocument(&$dat)
	{
		global $DBLink, $json, $a, $m, $f, $tab, $find, $tahun, $page, $perpage, $arConfig, $arrType, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcKelas;
		// echo "<pre>";
		// print_r($_REQUEST);

		$where = " WHERE dbkb.CPM_THN_DBKB_MATERIAL = '" . $tahun . "' ";
		if ($srcKelas != "") $where .= " AND dbkb.CPM_KD_PEKERJAAN LIKE '%" . $srcKelas . "%' ";

		$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
		$query = "";
		$query="SELECT dbkb.*, m.CPM_DESKRIPSI_ADJ AS NAMA_MATERIAL
				FROM cppmod_pbb_dbkb_material dbkb
				LEFT JOIN cppmod_pbb_adj_material m ON  m.CPM_KD_PEKERJAAN=dbkb.CPM_KD_PEKERJAAN AND m.CPM_KD_KEGIATAN=dbkb.CPM_KD_KEGIATAN
                $where 
				ORDER BY dbkb.CPM_THN_DBKB_MATERIAL
				LIMIT " . $hal . "," . $perpage;
		$qry   = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_pbb_dbkb_material dbkb $where "; //echo $query;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}

		$totalrows = $this->getTotalRows($qry);
		$d =  $json->decode($this->mysql2json($res, "data"));
		$HTML = $startLink = $endLink = "";
		$data = $d;
		$params = "a=" . $a . "&m=" . $m . "&f=" . $f;

		if (count($data->data) > 0) {
			for ($i = 0; $i < count($data->data); $i++) {
				$HTML .= "<tr>\n";
				// $HTML .= "<td class=\"".$class."\" align=\"center\"><input class=\"check\" name=\"check[]\" type=\"checkbox\" value=\"".$data->data[$i]->CPM_KLS_DBKB_JPB2."\" />";
				$HTML .= "<td align=center>" . $data->data[$i]->CPM_KD_PEKERJAAN . "</td> \n";
				$HTML .= "<td align=center>" . $data->data[$i]->CPM_KD_KEGIATAN . "</td> \n";
				$HTML .= "<td>" . $data->data[$i]->NAMA_MATERIAL . "</td> \n";
				$HTML .= "<td align=right>" . $data->data[$i]->CPM_NILAI_DBKB_MATERIAL . "</td> \n";
				$key = $data->data[$i]->CPM_KD_PEKERJAAN . "+" . $data->data[$i]->CPM_KD_KEGIATAN . "+" . $data->data[$i]->CPM_NILAI_DBKB_MATERIAL. "+" . $data->data[$i]->NAMA_MATERIAL;
				$HTML .= "<td align=center><a href=\"main.php?param=" . base64_encode($params . "&edit=1&kelas2=" . $key) . "\">Ubah</a> | <a href=\"#\" onclick=\"prosesDel('" . $key . "','main.php?param=" . base64_encode($params . "&hapus=1&kelas2=" . $key) . "')\">Hapus</a></td>\n";
				$HTML .= "</tr>";
			}
			$dat = array();
			$dat[0] = $HTML;
			$dat[1] = $totalrows;
			return true;
		} else {
			return false;
		}
	}

	public function view()
	{
		global $DBLink, $find, $tahun, $a, $m, $f, $tab, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcKelas, $modeInput, $kelas, $nilaiMax, $nilaiMin, $nilai, $kdPropinsi, $kdDati2;
		// print_r($_REQUEST);
		$val = null;
		if (isset($_REQUEST['kelas2'])) {
			$val = explode("+", $_REQUEST['kelas2']);
		}
		$params = "a=" . $a . "&m=" . $m . "&f=" . $f;
		if (!empty($modeInput) || (isset($_REQUEST['edit']) && $_REQUEST['edit'] == 1)) {
			$HTML = "<form id=\"form-input\" name=\"form-input\" method=\"post\" action=\"main.php?param=" . base64_encode($params) . "\">";
			$HTML .= "
			" . (!empty($modeInput) ? "<button name=\"\" type=\"button\" class=\"btn btn-primary btn-orange\" onclick=\"addRowsMulti()\">Tambah Baris</button<br><br>" : "") . "
			<div class=\"table-responsive\" style=\"margin-top:10px\">
			<table width=\"auto\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\" id=\"tableAdd\" class=\"table table-bordered table-striped table-hover\">
					<tr>
						<th colspan=\"4\">" . (!empty($modeInput) ? "Tambah Data" : "Ubah Data") . "</th>
					</tr>
					<tr>
						<td>Kode Pekerjaan : </td>
						<td>Kode Kegiatan : </td>
						<td>Nama Material : </td>
						<td>Nilai : </td>
					</tr>
					<tr>
						<td><input name=\"kodepekerjaan[]\" type=\"text\" id=\"kodepekerjaan[]\" value=\"" . ($val[0] != "" ? $val[0] : "") . "\" " . (!empty($modeInput) ? "" : "readonly") . "></td>
						<td><input name=\"kodekegiatan[]\" type=\"text\" id=\"kodekegiatan[0]\" value=\"" . ($val[1] != "" ? $val[1] : "") . "\" maxlength=\"9\" onkeypress=\"return iniAngka(event)\" " . (!empty($modeInput) ? "" : "readonly") . " /></td>
						<td><input type=\"text\" value=\"" . ($val[3] != "" ? $val[3] : "") . "\" maxlength=\"20\" " . (!empty($modeInput) ? "" : "readonly") . " /></td>
						<td><input name=\"nilai[]\" type=\"text\" id=\"nilai[0]\" value=\"" . ($val[2] != "" ? $val[2] : "") . "\" maxlength=\"9\" onkeypress=\"return iniAngka(event)\"/></td>
					</tr>
					<tr>
						<td colspan=\"4\"><div align=\"right\">
							" . (!empty($modeInput) ? "<input name=\"btn-back\" type=\"submit\" id=\"btn-back\" value=\"Kembali\" />&nbsp;<input name=\"btn-save\" type=\"submit\" id=\"btn-save\" value=\"Simpan\" />" : "<input name=\"btn-back\" type=\"submit\" id=\"btn-back\" value=\"Kembali\" />&nbsp;<input name=\"btn-save2\" type=\"submit\" id=\"btn-save2\" value=\"Simpan Perubahan\" />") . "
							
						</div></td>
					</tr>
				</table>";
		} else {
			//Update
			// echo "<pre>";
			// print_r($_REQUEST);
			if (!empty($_REQUEST['btn-save2'])) {
				$jArray = count($_REQUEST['kodepekerjaan']);
				for ($i = 0; $i < $jArray; $i++) {
					$kodepekerjaan 		= $_REQUEST['kodepekerjaan'][$i];
					$kodekegiatan = $_REQUEST['kodekegiatan'][$i];
					$nilai		= $_REQUEST['nilai'][$i];

					$sqlUpdate = "UPDATE cppmod_pbb_dbkb_material SET CPM_NILAI_DBKB_MATERIAL='$nilai' WHERE CPM_KD_PROPINSI='$kdPropinsi' AND CPM_KD_DATI2='$kdDati2' AND CPM_THN_DBKB_MATERIAL='$tahun' AND CPM_KD_PEKERJAAN='$kodepekerjaan' AND CPM_KD_KEGIATAN='$kodekegiatan' ";
					// echo $sqlUpdate;exit;
					$bOK = mysqli_query($DBLink, $sqlUpdate);
				}
				if ($bOK) {
					echo "<script language='javascript'>
					$(document).ready(function(){
						window.location = \"./main.php?param=" . base64_encode($params) . "\"
					})
						</script>";
				} else {
					echo mysqli_error($DBLink);
				}
				//Insert
			} else if (!empty($_REQUEST['btn-save'])) {
				// echo "masuk";
				$jArray = count($_REQUEST['kodepekerjaan']);
				// var_dump($jArray);
				// exit;
				for ($i = 0; $i < $jArray; $i++) {
					$kodepekerjaan 		= $_REQUEST['kodepekerjaan'][$i];
					$kodekegiatan = $_REQUEST['kodekegiatan'][$i];
					$nilai		= $_REQUEST['nilai'][$i];
					$sqlInsert = "INSERT INTO cppmod_pbb_dbkb_material 
					(
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					CPM_THN_DBKB_MATERIAL,
					CPM_KD_PEKERJAAN,
					CPM_KD_KEGIATAN,
					CPM_NILAI_DBKB_MATERIAL
					)
					 VALUES ('$kdPropinsi','$kdDati2','$tahun','$kodepekerjaan','$kodekegiatan', '$nilai');";
					if (!empty($kodepekerjaan) and !empty($kodekegiatan)) {
						$bOK = mysqli_query($DBLink, $sqlInsert);
						// echo $sqlInsert;
					}
				}
				if ($bOK) {
					$message = $bOK;
					echo "<script language='javascript'>
					$(document).ready(function(){
						window.location = \"./main.php?param=" . base64_encode($params . "&msg=" . $message) . "\"
					})
						</script>";
				} else {
					echo mysqli_error($DBLink);
				}
			} else if (!empty($_REQUEST['kelas2']) && $_REQUEST['hapus'] == 1) {
				$jArray = count($_REQUEST['kelas']);
				if ($jArray == 0) {
					$jArray = 1;
				}
				for ($i = 0; $i < $jArray; $i++) {
					$kelas = $_REQUEST['kelas'][$i];
					if (empty($kelas)) {
						$kelas = explode("+", $_REQUEST['kelas2']);
						// print_r($kelas);
					}
					if (!empty($kelas)) {
						$sqlTampil = "DELETE FROM cppmod_pbb_dbkb_material WHERE CPM_KD_PROPINSI='$kdPropinsi' AND CPM_KD_DATI2='$kdDati2' AND CPM_THN_DBKB_MATERIAL='$tahun' AND CPM_KD_PEKERJAAN='$kelas[0]' AND CPM_KD_KEGIATAN='$kelas[1]' ";
						// echo $sqlTampil; exit;
						$bOK += mysqli_query($DBLink, $sqlTampil);
					}
				}
				if ($bOK) {
					echo "<b>" . ($bOK) . " data dihapus!</b>";
				} else {
					echo mysqli_error($DBLink);
				}
			}

			$HTML = $this->headerContent();

			if (!empty($_REQUEST['msg'])) {
				echo "<b> Sebanyak " . $_REQUEST['msg'] . " data berhasil ditambahkan! </b>";
			}
			$this->getDocument($dt);
			// print_r($dt);
			if ($dt) {
				$HTML .= $dt[0];
			} else {
				$HTML .= "<tr><td colspan=\"4\" align=\"center\">Data Kosong !</td></tr> ";
			}
			$HTML .= "</table></div></div></div>";
		}
		return $HTML;
	}

	public function headerContent()
	{
		global $find, $a, $m, $f, $arConfig, $appConfig, $tab, $srcTglAwal, $srcTglAkhir, $srcNomor, $srcKelas;

		$params = "a=" . $a . "&m=" . $m . "&f=" . $f;
		$startLink = "<a style='text-decoration: none;' href=\"main.php?param=" . base64_encode($params) . "\">";
		$endLink = "</a>";

		$HTML = "<form id=\"form-laporan\" name=\"form-laporan\" method=\"post\" action=\"main.php?param=" . base64_encode($params) . "\">";
		$HTML .= "
			<div class=\"row\" style=\"margin-top: 15px; margin-bottom: 15px;\">
				<div class=\"col-md-2\">
					<button type=\"submit\" class=\"btn btn-primary btn-orange mb5\" value=\"Tambah\" id=\"btn-add\" name=\"btn-add\">Tambah</button>
					<!-- <input type=\"button\" value=\"Ubah\" id=\"btn-edit\" name=\"btn-edit\"/>
					<input type=\"button\" value=\"Hapus\" id=\"btn-delete\" name=\"btn-delete\" onclick=\"return delAll()\"/> -->
				</div>
				<div class=\"col-md-5\" style='float:right; text-align: right;'>
					<div class=\"row\">
				<div class=\"col-md-3\" style=\"margin-top: 5px;\">Pencarian </div>
						<div class=\"col-md-6\">
							<input type=\"text\" class=\"form-control\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs();\" id=\"srcKelas\" name=\"srcKelas\" size=\"30\" value=\"" . $srcKelas . "\" placeholder=\"Kelas\"/>
						</div>
						<div class=\"col-md-3\">
							<button class=\"btn btn-primary btn-blue mb5\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs()\">Cari</button>
						</div>
					</div>
				</div>
			</div>
        </form>";

		// $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"600px\">";
		$HTML .= "<div class=\"row\"><div class=\"col-md-12\"><div class=\"table-responsive\"><table class=\"table table-bordered table-striped table-hover\">";
		$HTML .= "<tr>";
		// $HTML .= "<td class=\"tdheader\"><div class=\"container\">
		// <div class=\"all\"><input name=\"checkAll\" id=\"checkAll\" type=\"checkbox\" value=\"\"/></div></div></td>\n";
		$HTML .= "<td class=tdheader>Kode Pekerjaan</td>";
		$HTML .= "<td class=tdheader>Kode Kegiatan</td>";
		$HTML .= "<td class=tdheader>Nama Material</td>";
		$HTML .= "<td class=tdheader>Nilai</td>";
		$HTML .= "<td class=tdheader>Proses</td>";
		$HTML .= "</tr>";

		return $HTML;
	}

	public function displayDataDBKB()
	{
		global $modeInput, $modeEdit;
		// print_r($_REQUEST);
		// echo "<div style=\"width : auto;\">"; //622px
		// echo "<div class=\"ui-widget consol-main-content\">\n";
		// echo "<div class=\"ui-widget-content consol-main-content-inner\">\n";
		// echo $this->view();
		// echo "</div>\n";
		// echo "<div class=\"ui-widget-header consol-main-content-footer\" align=\"right\">  \n";
		// if($modeInput!='Tambah' && $modeEdit!=1) echo $this->paging();
		// echo "</div>\n";
		// echo "</div>";
		echo "<div class=\"col-md-12\">";
		echo $this->view();
		echo "</div>";
		echo "<div style=\"float: right; margin-right: 15px;\">";
		if ($modeInput != 'Tambah' && $modeEdit != 1) echo $this->paging();
		echo "</div>";
	}

	function paging()
	{
		global $a, $m, $f, $page, $perpage;

		$this->getDocument($dt);

		$totalrows = $dt[1];

		$html = "<div>";
		$row = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
		$rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
		$html .= ($row + 1) . " - " . ($rowlast) . " dari " . $totalrows;

		if ($page != 1) {
			// $page--;
			$html .= "&nbsp;<a onclick=\"setPage('0')\"><span id=\"navigator-left\"></span></a>";
		}
		if ($rowlast < $totalrows) {
			// $page++;
			$html .= "&nbsp;<a onclick=\"setPage('1')\"><span id=\"navigator-right\"></span></a>";
		}
		$html .= "</div>";
		return $html;
	}
}
?>
<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery.ui.button.js"></script>

<script type="text/javascript">
	var url = "<?php echo "main.php?param=" . base64_encode("a=" . $a . "&m=" . $m . "&f=" . $f); ?>";
	var page = "<?php echo $page; ?>";

	function setPage(np) {
		if (np == 1) {
			if (page == '') {
				page = 2;
			} else {
				page++;
			}
		} else
			page--;

		window.open(url + "&page=" + page, "_parent");
	}

	function prosesDel(a, a_link) {
		// id = a.split("+",1);
		var b = confirm("Anda yakin akan menghapus data ini?");
		if (b == false) {
			return false;
		} else {
			window.open(a_link, "_parent");
			return true;
		}
	}

	function setTabs() {
		$('#form-laporan').submit();
	}

	$(document).ready(function() {
		$("#checkAll").click(function() {
			$('.check').each(function() {
				this.checked = $("#checkAll").is(':checked');
			});
		});

	});

	function delAll() {
		var b = confirm("Apakah anda yakin menghapus dengan mode All?");
		if (b == false) {
			return false;
		} else {
			return true;
		}
	}

	var n = 3;
	var id = 1;

	function addRows() {
		var row = document.getElementById("tableAdd").insertRow(n);
		row.insertCell(0).innerHTML = "<input name='kodepekerjaan[]' type='text' id='kodepekerjaan[" + id + "]' value=''/>";
		row.insertCell(1).innerHTML = "<input name='kodekegiatan[]' type='text' id='kodekegiatan[" + id + "]' maxlength='9' onkeypress='return iniAngka(event)'/>";
		row.insertCell(2).innerHTML = "<input name='nilai[]' type='text' id='nilai[" + id + "]' maxlength='9' onkeypress='return iniAngka(event)' />";
		n++;
		id++;
	}

	function addRowsMulti() {
		for (i = 0; i < 1; i++) {
			addRows();
		}
	}

	function iniAngka(evt) {
		var charCode = (evt.which) ? evt.which : event.keyCode
		if ((charCode >= 48 && charCode <= 57) || charCode == 46 || charCode == 8)
			return true;

		return false;
	}
</script>
<?php
$User	 	= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$page 		= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$modeInput	= isset($_REQUEST['btn-add']) ? $_REQUEST['btn-add'] : '';
$modeEdit	= isset($_REQUEST['edit']) ? $_REQUEST['edit'] : '';

//$arConfig = $User->GetModuleConfig($m);
$appConfig 	= $User->GetAppConfig($a);
$perpage 	= $appConfig['ITEM_PER_PAGE'];
$tahun 		= $appConfig['tahun_tagihan'];
$kdPropinsi	= substr($appConfig['KODE_KOTA'], 0, 2);
$kdDati2	= substr($appConfig['KODE_KOTA'], 2, 3);
$modDBKB 	= new  DBKBJPB2(1, $uname);
$modDBKB->displayDataDBKB();
?>