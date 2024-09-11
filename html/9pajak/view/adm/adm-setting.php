<?php

// Prevent direct access to file
if ($User == null) {
	die();
}

echo "<div class='subTitle'>Change Setting</div>";
echo "<div class='spacer10'></div>";

$url64 = base64_encode("setting=1&m=s");
echo "<form action='main.php?param=$url64' method='POST'>";
echo "<table class='transparent'>";
echo "<tr>";
echo "<td>Title</td>";
echo "<td>:</td>";
echo "<td><input type='text' id='setTitle' name='setTitle' size='20' maxlength='100' autocomplete='off' value='$MAINtitle'></input></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Footer</td>";
echo "<td>:</td>";
echo "<td>";
echo "<textarea id='setFooter' name='setFooter' rows='3' cols='50'>$MAINfooterText</textarea>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td colspan='2'></td>";
echo "<td>";
echo "<input type='submit' value='Save'></input>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</form>";

?>
