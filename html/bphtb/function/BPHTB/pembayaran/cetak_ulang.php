<b>PENCETAKAN ULANG</b>
<?php 
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'pembayaran', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/json.php");

require_once('view/BPHTB/pembayaran/pembayaran.php');
?>

<script>
 $('#mode').val('cetak_ulang');
 $('#payment').val('Cetak Ulang');
 
</script>