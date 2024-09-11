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

		$where = " WHERE CPM_THN_DBKB_JPB3 = '" . $tahun . "' ";
		if ($srcKelas != "") $where .= " AND CPM_KLS_DBKB_JPB3 LIKE '%" . $srcKelas . "%' ";

		$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
		$query = "";
		$query = "SELECT * FROM cppmod_pbb_dbkb_jpb3
                  $where ORDER BY CPM_KD_PROPINSI, CPM_KD_DATI2, CPM_THN_DBKB_JPB3 LIMIT " . $hal . "," . $perpage;
		$qry   = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_pbb_dbkb_jpb3 $where "; //echo $query;

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
				$class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
				$HTML .= "\t<div class=\"container\"><tr>\n";
				// $HTML .= "\t\t<td class=\"".$class."\" align=\"center\"><input class=\"check\" name=\"check[]\" type=\"checkbox\" value=\"".$data->data[$i]->CPM_KLS_DBKB_JPB2."\" />";
				$HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_LBR_BENT_MIN_DBKB_JPB3 . "</td> \n";
				$HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_LBR_BENT_MAX_DBKB_JPB3 . "</td> \n";
				$HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_TINGGI_KOLOM_MIN_DBKB_JPB3 . "</td> \n";
				$HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_TINGGI_KOLOM_MAX_DBKB_JPB3 . "</td> \n";
				$HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_NILAI_DBKB_JPB3 . "</td> \n";
				$key = $data->data[$i]->CPM_LBR_BENT_MIN_DBKB_JPB3 . "+" . $data->data[$i]->CPM_LBR_BENT_MAX_DBKB_JPB3 . "+" . $data->data[$i]->CPM_TINGGI_KOLOM_MIN_DBKB_JPB3 . "+" . $data->data[$i]->CPM_TINGGI_KOLOM_MAX_DBKB_JPB3 . "+" . $data->data[$i]->CPM_NILAI_DBKB_JPB3;
				$HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\"><a href=\"main.php?param=" . base64_encode($params . "&edit=1&kelas2=" . $key . "+" . $data->data[$i]->CPM_NILAI_DBKB_JPB3) . "\">Ubah</a> | <a href=\"#\" onclick=\"prosesDel('" . $key . "','main.php?param=" . base64_encode($params . "&hapus=1&kelas2=" . $key) . "')\">Hapus</a></td>\n";
				$HTML .= "\t</tr></div>\n";
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
				" . (!empty($modeInput) ? "<button value=\"Tambah Baris\" class=\"btn btn-primary btn-orange\" onclick=\"addRowsMulti()\">Tambah Baris</button>" : "") . "
				<div class=\"table-responsive\" style=\"margin-top: 15px \">
					<table class=\"table table-bordered\" id=\"tableAdd\">
						<tr>
							<th colspan=\"5\">" . (!empty($modeInput) ? "Tambah Data" : "Ubah Data") . "</th>
						</tr>
						<tr>
							<td width=\"144\">Lebar Bent. Min : </td>
							<td width=\"144\">Lebar Bent. Max : </td>
							<td width=\"144\">Tinggi Kolom Min : </td>
							<td width=\"144\">Tinggi Kolom Max : </td>
							<td width=\"144\">Nilai : </td>
						</tr>
						<tr>
							<td><input name=\"lbMin[]\" class=\"form-control\" type=\"text\" id=\"lbMin[]\" value=\"" . (isset($val[0]) && $val[0] != "" ? $val[0] : "") . "\" " . (!empty($modeInput) ? "" : "readonly") . "></td>
							<td><input name=\"lbMax[]\" class=\"form-control\" type=\"text\" id=\"lbMax[0]\" value=\"" . (isset($val[1]) && $val[1] != "" ? $val[1] : "") . "\" maxlength=\"9\" onkeypress=\"return iniAngka(event)\" " . (!empty($modeInput) ? "" : "readonly") . " /></td>
							<td><input name=\"tkMin[]\" class=\"form-control\" type=\"text\" id=\"tkMin[0]\" value=\"" . (isset($val[2]) && $val[2] != "" ? $val[2] : "") . "\" maxlength=\"9\" onkeypress=\"return iniAngka(event)\" " . (!empty($modeInput) ? "" : "readonly") . " /></td>
							<td><input name=\"tkMax[]\" class=\"form-control\" type=\"text\" id=\"tkMax[0]\" value=\"" . (isset($val[3]) && $val[3] != "" ? $val[3] : "") . "\" maxlength=\"9\" onkeypress=\"return iniAngka(event)\"/></td>
							<td><input name=\"nilai[]\" class=\"form-control\" type=\"text\" id=\"nilai[0]\" value=\"" . (isset($val[4]) && $val[4] != "" ? $val[4] : "") . "\" maxlength=\"9\" onkeypress=\"return iniAngka(event)\"/></td>
						</tr>
						<tr>
							<td colspan=\"4\">&nbsp;</td>
							<td><div align=\"right\">
								" . (!empty($modeInput) ? "<button name=\"btn-save\" class=\"btn btn-primary bg-maka\" type=\"submit\" id=\"btn-save\" value=\"Simpan\">Simpan</button>" : "<button name=\"btn-save2\" class=\"btn btn-primary bg-maka\" type=\"submit\" id=\"btn-save2\" value=\"Simpan Perubahan\">Simpan Perubahan</button>") . "
								
							</div></td>
						</tr>
					</table>
				</div>";
		} else {
			//Update
			// echo "<pre>";
			// print_r($_REQUEST);
			if (!empty($_REQUEST['btn-save2'])) {
				$jArray = count($_REQUEST['lbMin']);
				for ($i = 0; $i < $jArray; $i++) {
					$lbMin 		= $_REQUEST['lbMin'][$i];
					$lbMax = $_REQUEST['lbMax'][$i];
					$tkMin 	= $_REQUEST['tkMin'][$i];
					$tkMax 	= $_REQUEST['tkMax'][$i];
					$nilai		= $_REQUEST['nilai'][$i];

					$sqlUpdate = "UPDATE cppmod_pbb_dbkb_jpb3 SET CPM_NILAI_DBKB_JPB3='$nilai' WHERE 
					CPM_KD_PROPINSI='$kdPropinsi' 
					AND CPM_KD_DATI2='$kdDati2' 
					AND CPM_THN_DBKB_JPB3='$tahun' 
					AND CPM_LBR_BENT_MIN_DBKB_JPB3='$lbMin' 
					AND CPM_LBR_BENT_MAX_DBKB_JPB3='$lbMax' 
					AND CPM_TINGGI_KOLOM_MIN_DBKB_JPB3='$tkMin' 
					AND CPM_TINGGI_KOLOM_MAX_DBKB_JPB3='$tkMax'
					";
					// echo $sqlUpdate;exit;
					$bOK = mysqli_query($DBLink, $sqlUpdate);
				}
				// echo $sqlUpdate;
				// exit;
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
				$jArray = count($_REQUEST['lbMin']);
				// var_dump($jArray);
				// exit;
				for ($i = 0; $i < $jArray; $i++) {
					$lbMin 		= $_REQUEST['lbMin'][$i];
					$lbMax = $_REQUEST['lbMax'][$i];
					$tkMin 	= $_REQUEST['tkMin'][$i];
					$tkMax 	= $_REQUEST['tkMax'][$i];
					$nilai		= $_REQUEST['nilai'][$i];
					if (!empty($lbMax) and !empty($lbMin)) {
						$sqlInsert = "INSERT INTO cppmod_pbb_dbkb_jpb3 
						(
							CPM_KD_PROPINSI,
							CPM_KD_DATI2,
							CPM_THN_DBKB_JPB3,
							CPM_LBR_BENT_MIN_DBKB_JPB3,
							CPM_LBR_BENT_MAX_DBKB_JPB3,
							CPM_TINGGI_KOLOM_MIN_DBKB_JPB3,
							CPM_TINGGI_KOLOM_MAX_DBKB_JPB3,
							CPM_NILAI_DBKB_JPB3
						)

						 VALUES ('$kdPropinsi','$kdDati2','$tahun','$lbMin', '$lbMax', '$tkMin', '$tkMax','$nilai');";
						// echo $sqlInsert;
						// exit;

						$bOK = mysqli_query($DBLink, $sqlInsert);
					}
				}
				// var
				// echo "exit";
				// exit;
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
						$sqlTampil = "DELETE FROM cppmod_pbb_dbkb_jpb3 WHERE CPM_KD_PROPINSI='$kdPropinsi' AND CPM_KD_DATI2='$kdDati2' AND CPM_THN_DBKB_JPB2='$tahun' AND CPM_KLS_DBKB_JPB2='$kelas[0]' AND CPM_LANTAI_MIN_JPB2='$kelas[1]' AND CPM_LANTAI_MAX_JPB2='$kelas[2]' ";
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
				$HTML .= "<tr><td colspan=\"6\" align=\"center\">Data Kosong !</td></tr> ";
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
								<button value=\"Cari\" class=\"btn btn-primary btn-blue\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs()\">Cari</button>
							</div>
						</div>
					</div>
            </div>
         </form>";

		$HTML .= "<div class=\"row\"><div class=\"col-md-12\"><div class=\"table-responsive\"><table class=\"table table-bordered\">";
		$HTML .= "<tr>";
		// $HTML .= "\t\t<td class=\"tdheader\"><div class=\"container\">
		// <div class=\"all\"><input name=\"checkAll\" id=\"checkAll\" type=\"checkbox\" value=\"\"/></div></div></td>\n";
		$HTML .= "\t\t<td class=\"tdheader\"> Lebar Bent Min </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Lebar Bent Max </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Tinggi Kolom Min </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Tinggi Kolom Max </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"100\"> Nilai </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"100\"> Proses </td> \n";
		$HTML .= "\t</tr>\n";

		return $HTML;
	}

	public function displayDataDBKB()
	{
		global $modeInput, $modeEdit;
		// print_r($_REQUEST);
		$width 		= '';
		if (($modeEdit == 1) || ($modeInput == 1)) {
			$width = '';
		} else {
			$width = '';
		}

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

		$totalrows = isset($dt[1]) ? $dt[1] : '1';

		$html = "<div style=\"font-weight: bold;\">";
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
		row.insertCell(0).innerHTML = "<input name='lbMin[]' type='text' id='lbMin[]' value=''/>";
		row.insertCell(1).innerHTML = "<input name='lbMax[]' type='text' id='lbMax[" + id + "]' maxlength='9' onkeypress='return iniAngka(event)'/>";
		row.insertCell(2).innerHTML = "<input name='tkMin[]' type='text' id='tkMin[" + id + "]' maxlength='9' onkeypress='return iniAngka(event)'/>";
		row.insertCell(3).innerHTML = "<input name='tkMax[]' type='text' id='tkMax[" + id + "]' maxlength='9' onkeypress='return iniAngka(event)' />";
		row.insertCell(4).innerHTML = "<input name='nilai[]' type='text' id='nilai[" + id + "]' maxlength='9' onkeypress='return iniAngka(event)' />";
		n++;
		id++;
	}
	// <td><input name=\"lbMin[]\" type=\"text\" id=\"lbMin[]\" value=\"".($val[0]!="" ? $val[0] : "")."\" ".(!empty($modeInput) ? "" : "readonly")."></td>
	// 		<td><input name=\"lbMax[]\" type=\"text\" id=\"lbMax[0]\" value=\"".($val[1]!="" ? $val[1] : "")."\" maxlength=\"9\" onkeypress=\"return iniAngka(event)\" ".(!empty($modeInput) ? "" : "readonly")." /></td>
	// 		<td><input name=\"tkMin[]\" type=\"text\" id=\"tkMin[0]\" value=\"".($val[2]!="" ? $val[2] : "")."\" maxlength=\"9\" onkeypress=\"return iniAngka(event)\" ".(!empty($modeInput) ? "" : "readonly")." /></td>
	// 		<td><input name=\"tkMax[]\" type=\"text\" id=\"tkMax[0]\" value=\"".($val[3]!="" ? $val[3] : "")."\" maxlength=\"9\" onkeypress=\"return iniAngka(event)\"/></td>
	// 		<td><input name=\"nilai[]\" type=\"text\" id=\"nilai[0]\" value=\"".($val[4]!="" ? $val[4] : "")."\" maxlength=\"9\" onkeypress=\"return iniAngka(event)\"/></td>
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