<?php
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;
global $DBLink;

?>

<head>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css"
   integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
   crossorigin=""/>
   <script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js"
   integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew=="
   crossorigin=""></script>
</head>
<script type="text/javascript">
function download(d) {
        if (d == 'Pilih MAP') return;
        $("#div1").load("/view/PATDA-V1/map/" + d);
}
</script>
<br>
<select name="download" onChange="download(this.value)">
<option value="map_kosong.php">Pilih MAP</option>
<option value="map_all.php">Semua Pajak</option>
<option value="map_air.php">Air Bawah Tanah</option>
<option value="map_hiburan.php">Hiburan</option>
<option value="map_hotel.php">Hotel</option>
<option value="map_mineral.php">Mineral Non Logam dan Batuan</option>
<option value="map_parkir.php">Parkir</option>
<option value="map_jalan.php">Penerangan Jalan</option>
<option value="map_reklame.php">Reklame</option>
<option value="map_restoran.php">Restoran</option>
</select>
<br>
<div id="div1">
