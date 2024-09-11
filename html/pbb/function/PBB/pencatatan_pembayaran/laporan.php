<?php

$uid     = $data->uid;
$uname     = $data->uname;

$sql     = "SELECT * FROM `cppmod_pbb_user_printer` WHERE CPM_UID = '$uid' AND CPM_MODULE = '$m'";
$result  = mysqli_query($DBLink, $sql);
$row     = mysqli_fetch_array($result);

$printername = str_replace("\\", "\\\\", $row['CPM_PRINTERNAME']);
$driver  = $row['CPM_DRIVER'];
if (!$printername) $printername = "Epson Lx-300+";

$uid = $data->uname;
$filtertgl = isset($_REQUEST["filtertgl"]) ? $_REQUEST["filtertgl"] : date("Y-m-d");
$filterbl = date("m");
$filterth = date("Y");
?>
<script>
	function printReport(content, copy) {
		if (!copy) {
			copy = 1;
		}
		if (content) {
			if (content != "") {
				var applet = document.jZebra;
				if (applet != null) {
					if (content.substr(0, 3) == '1~~' || content.substr(0, 3) == '0~~') {
						strContent = "";
						for (var i = 0; i < copy; i++)
							strContent += content + "~p/";
						applet.appendRawOnpays(strContent);
						applet.printOnpays();
					} else {
						for (var i = 0; i < copy; i++)
							applet.append64(content);
						applet.print();
					}
					monitorPrinting();
				} else {
					alert("Applet not loaded!");
				}
			}
		}
	}

	function sendReportSuccess(params) {
		//hideMask();
		if (params.responseText) {
			var objResultPayment = Ext.decode(Base64.decode(params.responseText));
			///console.log(objResultPayment);
			if (objResultPayment.result) {
				var strBtn = "<input name='btnSend' type='button' value='Cetak Ke Printer' onclick='printReport(\"" + objResultPayment.printCode + "\",1);'><br>";
				document.getElementById('pbb-report-result').innerHTML = strBtn + objResultPayment.printHTML;
			} else {
				document.getElementById('pbb-report-result').innerHTML = '<label style="font-weight:bold;color:red">' + objResultPayment.message + '</label>';
			}
		}
	}

	function sendReportException(param) {
		//hideMask();
	}


	function sendReport(uid) {
		var dateTrs = document.getElementById('filtertgl').value;

		if (dateTrs != '') {
			var params = '{"dateTrs" : "' + dateTrs + '","uid":"' + uid + '","driver":"<?php echo $driver; ?>"}';
			params = Base64.encode(params);
			Ext.Ajax.request({
				url: 'function/PBB/pencatatan_pembayaran/svc-pbb-daily-report.php',
				success: sendReportSuccess,
				failure: sendReportException,
				params: {
					q: params
				}
			});
			//showMask();
		} else {
			alert("Pencetakan laporan tidak bisa dilakukan !");
		}
	}

	function downloadFile(uid) {
		ft = document.getElementById('filtertgl').value;
		var params = "{'dateTrs' : '" + ft + "','uid':'" + uid + "'}";

		if (ft.value != "") {
			urlparam = Base64.encode(params);
			url = 'http://' + window.location.host + window.location.pathname.replace("main.php", "") + 'function/PBB/pencatatan_pembayaran/svc-pbb-download-detail-daily.php?t=' + (new Date()).getSeconds() + '&q=' + urlparam;
			console.log(url);
			document.getElementById('download-file').src = url;
		}
	}
</script>
<link href='inc/datepicker/datepickercontrol.css' rel='stylesheet' type='text/css' />
<SCRIPT LANGUAGE='JavaScript' src='inc/datepicker/datepickercontrol.js'></SCRIPT>
<?php

echo "\n<link href='inc/datepicker/datepickercontrol.css' rel='stylesheet' type='text/css'/>\n";
echo "<SCRIPT LANGUAGE='JavaScript' src='inc/datepicker/datepickercontrol.js'></SCRIPT> \n";
echo "<script language=\"javascript\"> var pcopy = '" . $printercopy . "';</script>\n";
echo "<div class=\"col-md-12\">";
echo "<input type='hidden' id='DPC_TODAY_TEXT' value='Hari Ini'> \n";
echo "<input type='hidden' id='DPC_BUTTON_TITLE' value='Buka Tanggal'> \n";
echo "<input type='hidden' id='DPC_MONTH_NAMES' value=\"['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']\"> \n";
echo "<input type='hidden' id='DPC_DAY_NAMES' value=\"['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']\"> \n";
echo "<table border='0' cellpadding='4' cellspacing='0'  class='transparent'> \n";
echo "<tr> \n<td class='transparent'>Tanggal </td> \n<td class='transparent'> \n";
echo "<span id='input_harian'><input readonly type='text' id='filtertgl' name='filtertgl' value='" . $filtertgl . "' datepicker='true' datepicker_format='YYYY-MM-DD' disableSelection='false' /><a href='#'><img src='image/icon/textfield_delete.png' width='24' height='24' alt='Kosongkan' onclick='document.getElementById(\"filtertgl\").value=\"\"'></a>";
echo "</span>\n";
echo "<span id='input_bulanan' style='display:none;'>\n";

echo '<select id="filterbl" name="filterbl">';
echo '<option value="01" ' . ($filterbl == "01" ? "selected" : "") . '>Januari</option>';
echo '<option value="02" ' . ($filterbl == "02" ? "selected" : "") . '>Februari</option>';
echo '<option value="03" ' . ($filterbl == "03" ? "selected" : "") . '>Maret</option>';
echo '<option value="04" ' . ($filterbl == "04" ? "selected" : "") . '>April</option>';
echo '<option value="05" ' . ($filterbl == "05" ? "selected" : "") . '>Mei</option>';
echo '<option value="06" ' . ($filterbl == "06" ? "selected" : "") . '>Juni</option>';
echo '<option value="07" ' . ($filterbl == "07" ? "selected" : "") . '>Juli</option>';
echo '<option value="08" ' . ($filterbl == "08" ? "selected" : "") . '>Agustus</option>';
echo '<option value="09" ' . ($filterbl == "09" ? "selected" : "") . '>September</option>';
echo '<option value="10" ' . ($filterbl == "10" ? "selected" : "") . '>Oktober</option>';
echo '<option value="11" ' . ($filterbl == "11" ? "selected" : "") . '>November</option>';
echo '<option value="12" ' . ($filterbl == "12" ? "selected" : "") . '>Desember</option>';
echo '</select><input type="text" id="filterth"  name="filterth" value="' . $filterth . '" size="4" />';

echo "</span>\n</td>\n</tr>";
echo "<tr>\n<td class='transparent'>&nbsp;</td>\n<td class='transparent'><input name='btnSend' type='button' value='Submit' onclick='sendReport(\"" . $uid . "\");'><input type=\"button\" id=\"btnUnduh\" value=\"Unduh Detail\" onclick=\"downloadFile('" . $uid . "');\"><iframe id=\"download-file\" src=\"_blank\" style=\"width:0;height:0;display:block\"></iframe></td>\n";
echo "</tr>\n";
echo "<tr><td colspan='3'><div id='main-report-result'></div></td></tr>";
echo "</table>\n";
echo "<div id='pbb-report-result'></div>";
echo "</div>";
echo '<applet name="jZebra" code="jzebra.RawPrintApplet.class" archive="inc/jzebra/jzebra.jar" width="0" height="0"><param name="printer" value="' . $printername . '"><param name="sleep" value="200"></applet>';

?>