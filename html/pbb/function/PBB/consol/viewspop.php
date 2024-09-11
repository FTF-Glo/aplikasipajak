<?php
if (!isset($data)) {
	die("Forbidden direct access");
}

if (!$User) {
	die("Access not permitted");
}

$bOK = $User->IsFunctionGranted($uid, $area, $module, $function);

if (!$bOK) {
	die("Function access not permitted");
}

require_once("inc/payment/uuid.php");
require_once("inc/PBB/dbSppt.php");
require_once("inc/PBB/dbSpptTran.php");
require_once("inc/PBB/dbSpptExt.php");
require_once("function/PBB/gwlink.php");
require_once("inc/PBB/dbSpptHistory.php");

$arConfig = $User->GetModuleConfig($module);
$dbSppt = new DbSppt($dbSpec);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbSpptExt = new DbSpptExt($dbSpec);
$dbSpptHistory = new DbSpptHistory($dbSpec);

//////////////////////////////
// Process approved by staff dispenda
/////////////////////////////
if (isset($_REQUEST['btn-process']) && ($arConfig['usertype'] == "dispenda" || $arConfig['usertype'] == "pejabatdispenda")) {
	if (isset($rekomendasi)) {
		$aVal['CPM_TRAN_FLAG'] = 1;
		$vals = $dbSpptTran->get($idt);
		$dbSpptTran->edit($idt, $aVal);

		unset($vals[0]['CPM_TRAN_ID']);
		unset($vals[0]['CPM_TRAN_DATE']);

		if ($rekomendasi == "y") {
			if ($vals[0]['CPM_TRAN_OPR_DISPENDA_1'] == "") {
				$vals[0]['CPM_TRAN_STATUS'] = 3;
			} else {
				$vals[0]['CPM_TRAN_STATUS'] = 4;
			}
		} else if ($rekomendasi == "n") {
			$vals[0]['CPM_TRAN_STATUS'] = 6;
			$vals[0]['CPM_TRAN_INFO'] = $TRAN_INFO;
		}

		if ($vals[0]['CPM_TRAN_OPR_DISPENDA_1'] == "") {
			$vals[0]['CPM_TRAN_OPR_DISPENDA_1'] = $uname;
		} else {
			$vals[0]['CPM_TRAN_OPR_DISPENDA_2'] = $uname;
		}

		$vals[0]['CPM_TRAN_DATE'] = date("Y-m-d H:i:s");
		$lastID = c_uuid();
		$bOK = $dbSpptTran->add($lastID, $vals[0]);

		if ($bOK && $vals[0]['CPM_TRAN_STATUS'] == 4) {
			$tVal['CPM_PJB_TGL_PENELITIAN'] = $PJB_TGL_PENELITIAN;
			$tVal['CPM_PJB_NAMA'] = $PJB_NAMA;
			$tVal['CPM_PJB_NIP'] = $PJB_NIP;
			$dbSppt->edit($vals[0]['CPM_TRAN_SPPT_DOC_ID'], $vals[0]['CPM_SPPT_DOC_VERSION'], $tVal);

			//proses penyimpanan ke tabel gateway
			$finalVal = $dbSppt->get($vals[0]['CPM_TRAN_SPPT_DOC_ID'], $vals[0]['CPM_SPPT_DOC_VERSION']);
			$finalVal[0]['CPM_TRAN_DATE'] = $vals[0]['CPM_TRAN_DATE'];

			$finalExt = $dbSpptExt->get($vals[0]['CPM_TRAN_SPPT_DOC_ID'], $vals[0]['CPM_SPPT_DOC_VERSION']);

			$bOK = saveGatewayCurrent($finalVal[0], $finalExt);
			if ($bOK) {
				$bOK = $dbSpptHistory->goFinal($lastID);
			}
		}
	}
	if ($bOK) {
		header("Location: main.php?param=" . base64_encode("a=$a&m=$m"));
	} else {
		echo "<div class='error'>Kesalahan saat finalisasi ke database</div>";
	}
}

//Preparing Parameters
if (isset($idt)) {
	$tran = $dbSpptTran->get($idt);
	$idd = $tran[0]['CPM_TRAN_SPPT_DOC_ID'];
	$v = $tran[0]['CPM_SPPT_DOC_VERSION'];
	$dispenda = $tran[0]['CPM_TRAN_OPR_DISPENDA_1'];
}

if (isset($idd) || isset($v)) {
	$docVal = $dbSppt->get($idd, $v);
	foreach ($docVal[0] as $key => $value) {
		$tKey = substr($key, 4);
		$$tKey = $value;
	}
	$aDocExt = $dbSpptExt->get($idd, $v);

	if (isset($aDocExt)) {
		$HtmlExt = "";
		foreach ($aDocExt as $docExt) {
			$param = "a=$a&m=$m&f=" . $arConfig['id_view_lampiran'] . "&idd=$idd&v=$v&num=" . $docExt['CPM_OP_NUM'];
			$HtmlExt .= "<li><a href='main.php?param=" . base64_encode($param) . "'>Lampiran Bangunan " . $docExt['CPM_OP_NUM'] . "</a></li>";
		}
	}
}

echo "<link rel=\"stylesheet\" href=\"function/PBB/viewspop.css\" type=\"text/css\">";
?>

<script type="text/javascript" src="inc/datepicker/datepickercontrol.js"></script>
<link type="text/css" rel="stylesheet" href="inc/datepicker/datepickercontrol.css">

<input type="hidden" id="DPC_TODAY_TEXT" value="today">
<input type="hidden" id="DPC_BUTTON_TITLE" value="Open calendar...">
<input type="hidden" id="DPC_MONTH_NAMES" value="['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']">
<input type="hidden" id="DPC_DAY_NAMES" value="['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']">

<?php
include("viewtmp.php");
if (($arConfig['usertype'] == "dispenda" && $tran[0]['CPM_TRAN_STATUS'] == 2) || ($arConfig['usertype'] == "pejabatdispenda" && $tran[0]['CPM_TRAN_STATUS'] == 3)) {
?>
	<br>
	<form method="post">
		<table border=0 cellpadding=5>
			<tr>
				<td colspan=2 class="tbl-rekomen"><b>Masukkan rekomendasi anda</b></td>
			</tr>
			<tr>
				<td class="tbl-rekomen" valign="top"><label><input type="radio" name="rekomendasi" value="y"> Setuju</label></td>
				<td class="tbl-rekomen">
					<?php if ($dispenda != "") { ?>
						<table>
							<tr>
								<td colspan=5 class="tbl-rekomen"><b>Pejabat yang berwenang</b></td>
							</tr>
							<tr>
								<td class="tbl-rekomen">Tanggal penelitian</td>
								<td colspan=4 class="tbl-rekomen"><input type="text" name="PJB_TGL_PENELITIAN" id="PJB_TGL_PENELITIAN" datepicker="true" datepicker_format="DD/MM/YYYY"></td>
							</tr>
							<tr>
								<td class="tbl-rekomen">Nama Jelas</td>
								<td colspan=4 class="tbl-rekomen"><input type="text" name="PJB_NAMA" size="34"></td>
							</tr>
							<tr>
								<td class="tbl-rekomen">NIP</td>
								<td colspan=4 class="tbl-rekomen"><input type="text" name="PJB_NIP" size="17"></td>
							</tr>
						</table>
					<?php				} ?>
				</td>
			</tr>
			<tr>
				<td valign="top" class="tbl-rekomen"><label><input type="radio" name="rekomendasi" value="n"> Tolak</label></td>
				<td class="tbl-rekomen">Alasan<br><textarea name="TRAN_INFO" cols=70 rows=7></textarea></td>
			</tr>
			<tr>
				<td colspan=2 align="right" class="tbl-rekomen"><input type="submit" name="btn-process" value="Submit"></td>
			</tr>
		</table>
	</form>
<?php
}
?>