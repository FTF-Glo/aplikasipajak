<?php
if (!isset($_GET['old'])) {
  header('Location: /portlet-new');
}

date_default_timezone_set('Asia/Jakarta');
// echo "123"; 
// exit;
include_once("inc-config.php");
include_once("image-2018/securimage.php");
// securimage_show.php
$client  = "";
$area  = "";

$nop  = isset($_POST["nop"]) ? $_POST["nop"] : "";
$idwp  = isset($_POST["idwp"]) ? $_POST["idwp"] : "";
$cImage_1  = isset($_POST["cImage_1"]) ? $_POST["cImage_1"] : "";
$cImage_2 = isset($_POST["cImage_2"]) ? $_POST["cImage_2"] : "";
$thn    = getConfig('tahun_tagihan');

$thn1  = isset($_POST["thn1"]) ? $_POST["thn1"] : "";
$thn2 = isset($_POST["thn2"]) ? $_POST["thn2"] : "";
// print_r($_REQUEST);
?>

<!DOCTYPE html>
<html>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" href="http://36.92.151.83:2010/style/default/favicon.ico" />
<link rel=" stylesheet" href="bootstrap/css/bootstrap.min.css">
<script src="bootstrap/js/jquery-3.1.1.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>

<title>Portlet</title>

<script src='../ext-core.js'></script>
<script src='../c-tools.js'></script>
<script src='../dialog_box.js'></script>
<script src='../func.js'></script>
<script src='../base64.js'></script>
<script src='../scrollmessage.js'></script>
<script src='../disableSelection.js'></script>


<script type="text/javascript">
  $(function() {
    var hash = window.location.hash;
    hash && $('ul.nav a[href="' + hash + '"]').tab('show');

    $('.nav-tabs a').click(function(e) {
      $(this).tab('show');
      var scrollmem = $('body').scrollTop();
      window.location.hash = this.hash;
      $('html,body').scrollTop(scrollmem);
    });

  });
</script>
</head>
<body>
	<br>
<div class="container-fluid">
  <div style="margin:0 auto">
    <img class="img-responsive" src="../style/style-pemda-kab-bangkabarat/little_logo.png" style="height: 8rem;margin:1em" alt="Logo">
  </div>

  <ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#tagihan">Cetak Tagihan</a></li>
    <!-- <li><a data-toggle="tab" href="#tunggakan">Cetak Tunggakan</a></li> -->
    <!-- <li><a data-toggle="tab" href="#stts">Cetak STTS </a></li> -->
  </ul>

  <div class="tab-content">
    <div id="tagihan" class="tab-pane fade in active ">
      <?php include("cek-tagihan.php") ?>
    </div> <!-- end of cek tagihan -->
    <div id="tunggakan" class="tab-pane fade " >
    <?php include("cetak-tunggakan.php"); ?>
    </div>
    <div id="stts" class="tab-pane fade">
      <?php include("cetak-stts.php") ?>
    </div>
  </div>


</div>
</body>
</html>