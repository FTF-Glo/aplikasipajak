<?php
$DIR = "PATDA-V1";
$modul = "map";
$submodul = "";

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $DIR . DIRECTORY_SEPARATOR . $modul, '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");

$host = ONPAYS_DBHOST;
$pass = ONPAYS_DBPWD;
$db = ONPAYS_DBNAME;
$user = ONPAYS_DBUSER;
$conn = mysqli_connect($host, $user, $pass, $db) or die(mysqli_error());

$query = mysqli_query($conn, "SELECT * FROM patda_parkir_profil WHERE latitude != '0' AND longitude != 0");


?>

<style>
#map { 
height: 60vh; 
width : 100%;
z-index:0;
}

.card{
   margin-top:10px;
   margin-right:10px;
}
.card-header{
   background-color: #fff;
   font-size:22px;
}

</style>

<div class="content-body">
<div class="card">
         <div class="card-header">
            Posisi Pajak Parkir
         </div>

         <div class="card-body">
            <div id="map"></div>
         </div>
   </div>
</div>
</body>


<script>
var map = L.map('map').setView([-3.8985514,121.9997171],4.5);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);
var LeafIcon = L.Icon.extend({
        options: {
            iconSize:     [48, 48],
            shadowSize:   [50, 64],
            iconAnchor:   [18, 46],
            popupAnchor:  [0, -45]
        }
    });
    var mainIcon = new LeafIcon({iconUrl: 'http://103.76.172.162:8090/view/PATDA-V1/map/marker.png'});
<?php
while ($restoran_marker = mysqli_fetch_assoc($query)) {

echo "L.marker([{$restoran_marker['latitude']},{$restoran_marker['longitude']}], {icon: mainIcon}).addTo(map).bindPopup('<b>{$restoran_marker['CPM_NPWPD']}</b><br>{$restoran_marker['CPM_NAMA_OP']}<br>NOP : {$restoran_marker['CPM_NOP']}');";
}
?> 
</script>

