<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="container">
    <div class="content bg-light p-4">
        <h3><strong>Pajak <?php echo $title; ?></strong></h3>
        <h4>Status Pembayaran Pajak</h4>
        <div class="row my-2">
            <div class="col-md-6">
                <div class="p-3 bg-infotext">
                    <h4>Informasi</h4>
                    <?php echo $info; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border p-3">
                    <div class="form-group">
                        <label>NOP</label>
                        <input class="form-control" id="txNop" type="text">
                    </div>
                    <div class="form-group">
                        <label>Tahun</label>
                        <input class="form-control" id="txTahun" type="text">
                    </div>
                    <div>
                        <button class="btn btn-primary" id="btnCheckTagihan">Periksa</button>
                        <button class="btn btn-secondary" id="btnReset">Reset</button>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="border p-3 mt-1 d-none" id="txResult"></div>
            </div>
        </div>
        <a href="<?php echo base_url('jenis/' . $code); ?>" class="btn btn-secondary"><i class="icon-arrow-left mr-2"></i>Kembali</a>
    </div>
</div>

<script>
    $('#btnCheckTagihan').click(function() {
        var iValue = $('#txNop').val();
        var iValue2 = $('#txTahun').val();

        var iTax = <?php echo $tax; ?>;
        if (iValue == "" || iValue2 == "") {
            alert("Data yang diinput belum lengkap !")
        } else {
            $.ajax({
                type: "POST",
                beforeSend: function() {
                    $('#overlay').removeClass('d-none');
                },
                data: {
                    iValue: iValue,
                    iValue2: iValue2
                },
                url: "<?php echo base_url('api/checkTagihanPBB'); ?>",
                success: function(msg) {
                    var arr = JSON.parse(msg);
                    $("#txResult").removeClass('d-none').html(arr.result);
                    scrollToResult();
                },
                complete:function() {
                    $('#overlay').addClass('d-none');
                }
            });
        }
    });

    $('#btnReset').click(function() {
        $("#txNop").val("");
        $("#txTahun").val("");
        $("#txResult").addClass('d-none').html("");
    })
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>ext/custom/autoNumeric.js"></script>
<script>
    jQuery(function($) {
        $('.aUse').autoNumeric('init', {
            mDec: '0'
        });
    });
</script>