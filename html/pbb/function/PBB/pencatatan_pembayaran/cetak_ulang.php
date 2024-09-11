<div class="col-md-12">
    <b>PENCETAKAN ULANG</b>
    <?php
    require_once('view/PBB/pencatatan_pembayaran/pembayaran.php');
    ?>
</div>

<script>
    $('#mode').val('cetak_ulang');
    $('#payment').val('Cetak Ulang').html('Cetak Ulang');
    $('#catatan').closest('tr').hide();
</script>