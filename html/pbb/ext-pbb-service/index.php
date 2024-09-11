<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<script src="js/jquery.js"></script>
</head>

<body>
	<form action="index.php" method="post">
		<h3>Function :
			<select name="type" onchange="submit()">
				<?php
				$pajak = array("getDataOPPBB", "updateOPPBBTematik_PG");
				echo "<option value=''>Pilih</option>";
				foreach ($pajak as $pjk) {
					echo "<option value='{$pjk}' " . ($pjk == $_POST['type'] ? "selected" : "") . ">" . $pjk . "</option>";
				}
				?>
			</select>
		</h3>
	</form>
	<?php
	$type = isset($_POST['type']) ? $_POST['type'] : "";
	if ($type == "getDataOPPBB") {
		echo "<input type=\"hidden\" name=\"fn\" id=\"fn\" value=\"getDataOPPBB\">
    <table>
        <tr>
            <td align=\"left\">NOP</td>
            <td><input type=\"text\" name=\"nop\" id=\"nop\" value=\"320410001100600530\"></td>
        </tr>
        <tr>
            <td align=\"right\" colspan=\"2\"><input type=\"button\" id=\"btnGetDataOP\" value=\"GET Json\"></td>
        </tr>
    </table>";
	} else if ("updateOPPBBTematik_PG") {
		echo "<input type=\"hidden\" name=\"fn\" id=\"fn\" value=\"updateOPPBBTematik_PG\">
    <table>
        <tr>
            <td align=\"left\">NOP</td>
            <td><input type=\"text\" name=\"nop\" id=\"nop\" value=\"320410001100600530\"></td>
        </tr>
        <tr>
            <td align=\"right\" colspan=\"2\"><input type=\"button\" id=\"btnUpdateOPPBBTematik_PG\" value=\"GET Json\"></td>
        </tr>
    </table>";
	}
	?>
	<div id="result"></div>
</body>
<script>
	$("#btnGetDataOP").click(function() {
		var tmpParams = new Object();
		tmpParams.nop = $('#nop').val();

		var tmpObj = new Object();
		tmpObj.user = 'Alfa System';
		tmpObj.fn = $('#fn').val();
		tmpObj.params = tmpParams;

		$.ajax({
			type: 'post',
			url: 'service.php',
			data: JSON.stringify(tmpObj),
			success: function(res) {
				//alert(res);
				$("#result").html(res);
			}
		})
	})

	$("#btnUpdateOPPBBTematik_PG").click(function() {
		var tmpParams = new Object();
		tmpParams.nop = $('#nop').val();

		var tmpObj = new Object();
		tmpObj.user = 'Alfa System';
		tmpObj.fn = $('#fn').val();
		tmpObj.params = tmpParams;

		$.ajax({
			type: 'post',
			url: 'service.php',
			data: JSON.stringify(tmpObj),
			success: function(res) {
				//alert(res);
				$("#result").html(res);
			}
		})
	})
</script>

</html>