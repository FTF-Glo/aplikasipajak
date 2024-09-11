<?php
require_once ('connectdb.php');

$latitude = $_REQUEST['lat'];
$longitude = $_REQUEST['lon'];

$result = pg_query($conn, "select gid, kode, nir from znt_palembang where ST_Contains(the_geom, GeomFromText('POINT($longitude $latitude)'))");

$row = pg_fetch_object($result);
if ($row->kode != null) {
    $kode = $row->kode;
    $nir = $row->nir;
    header('Content-type: application/javascript');
    //$item = array("KODE" => $kode, "NIR" => $nir);
    //echo json_encode($item);
    echo "{\"KODE\":\"$kode\",\"NIR\":\"$nir\"}";
}
?>