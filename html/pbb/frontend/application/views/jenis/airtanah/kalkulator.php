<?php
defined('BASEPATH') or exit('No direct script access allowed');
$dev = @isset($_REQUEST['dev']) ? $_REQUEST['dev'] : false;
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
                        <label>Nomor Kode Bayar</label>
                        <input class="form-control" id="txKodebayar" type="text">
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
        <button class="btn btn-primary qris-btn" data-toggle="modal" data-target="#qrisModal" style="display:none">Bayar dengan QRIS</button>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="qrisModal" tabindex="-1" role="dialog" aria-labelledby="qrisModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <table>
                        <tr><td>NOP</td><td width=3>:</td><td><b class="text-danger modal_nop"></b></td></tr>
                        <tr><td>NAMA&nbsp;OP</td><td width=3>:</td><td><b class="text-danger modal_nama_op"></b></td></tr>
                        <tr><td>ALAMAT&nbsp;OP</td><td width=3>:</td><td><b class="text-danger modal_alamat"></b></td></tr>
                        <tr><td>TAGIHAN</td><td width=3>:</td><td><b class="text-danger modal_tagihan"></b></td></tr>
                    </table>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="25mm" height="auto" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd;" viewBox="0 0 21000 7750" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <defs> <style type="text/css"> <![CDATA[ .fil0 {fill:black;fill-rule:nonzero} ]]> </style> </defs>
                        <g id="__x0023_Layer_x0020_1">
                            <metadata id="CorelCorpID_0Corel-Layer"/>
                            <path class="fil0" d="M20140 4750l0 -667 0 -1333 -2000 0 -1333 0 0 -667 3333 0 0 -1333 -3333 0 -2000 0 0 1333 0 667 0 1333 2000 0 1333 0 0 667 -3333 0 0 1333 3333 0 2000 0 0 -1333zm527 -417l0 2167c0,44 -18,87 -49,118 -31,31 -74,49 -118,49l-2167 0 0 333 2500 0c44,0 87,-18 118,-49 31,-31 49,-74 49,-118l0 -2500 -333 0zm-18000 -4333l-2500 0c-44,0 -87,18 -118,49 -31,31 -49,74 -49,118l0 2500 333 0 0 -2167c0,-44 18,-87 49,-118 31,-31 74,-49 118,-49l2167 0 0 -333zm2140 7750l1333 0 0 -3000 -1333 0 0 3000zm1167 -7000l-3167 0 0 1333 2000 0 0 2000 1333 0 0 -3167c0,-44 -18,-87 -49,-118 -31,-31 -74,-49 -118,-49zm-3833 0l-1167 0c-44,0 -87,18 -118,49 -31,31 -49,74 -49,118l0 5000c0,44 18,87 49,118 31,31 74,49 118,49l3167 0 0 -1333 -2000 0 0 -4000zm667 3333l1333 0 0 -1333 -1333 0 0 1333zm333 -1000l0 0 667 0 0 667 -667 0 0 -667zm3667 -2333l0 1333 4000 0 0 667 -2667 0 -1333 0 0 1333 0 2000 1333 0 0 -1980 2000 1980 2000 0 -2087 -2000 753 0 1333 0 0 -1333 0 -667 0 -1333 -1333 0 -4000 0zm6000 5333l1333 0 0 -5333 -1333 0 0 5333z"/>
                        </g>
                    </svg>
                </div>
                <div id="modal_qris" class="text-center"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<script>
    var token = false;
    var batas_bayar = false;
    $("#btnCheckTagihan").click(function() {
        token = false;
        var iValue = $("#txKodebayar").val();
        if (iValue == "" || iValue.substr(-2)!=='10') {
            alert("Data yang diinput belum lengkap !");
            return;
        }
        $.ajax({
            type: "POST",
            beforeSend: function() {
                $('#overlay').removeClass('d-none');
            },
            data: {
                iValue: iValue
            },
            url: "<?php echo base_url('api/checkTagihanKodeBayar'); ?>",
            success: function(msg) {
                var arr = JSON.parse(msg);
                $("#txResult").removeClass('d-none').html(arr.result);
                if(arr.modal.length!=0){
                    var q = arr.modal;
                    if(q.status==0) {
                        <?php if($dev) { ?>
                            $('.qris-btn').show();
                        <?php } ?>
                        $('.modal_nop').html(q.nop);
                        $('.modal_nama_op').html(q.nama_op);
                        $('.modal_alamat').html(q.alamat);
                        $('.modal_tagihan').html(q.tagihan);
                        if(q.qr){
                            $('#modal_qris').html('<img src="'+q.qr+'"/>');
                        }else{
                            token = q.token;
                            batas_bayar = q.expired;
                        }
                    }
                }
                scrollToResult();
            },
            complete:function() {
                $('#overlay').addClass('d-none');
            }
        });
    });

    $('.qris-btn').click(function() {
        if(token){
            $('#modal_qris').html('<i class="fa fa-refresh fa-spin fa-3x fa-fw mt-5 mb-5"></i>');
            var code = $('#txKodebayar').val();
            $.ajax({
                type: "POST",
                data: {
                    token: token,
                    paymentcode: code,
                    exp: batas_bayar,
                    ref: '2020102900000000000001'
                },
                url: "<?=getConfig('api_pajak_curl_qr')?>",
                success: function(msg) {
                    if(msg.status){
                        $('#modal_qris').html('<i id="spin" class="fa fa-refresh fa-spin fa-3x fa-fw mt-5 mb-5"></i><img id="imgqr" class="d-none" src="'+msg.qr+'"/>');
                        sleep(1000).then(() => { showQRReal(); });
                    }else{
                        $('#modal_qris').html('<div class="mt-5 mb-5"><i class="text-danger fa fa-times-circle fa-4x mb-2"></i><br><b style="font-size:16px">'+msg.msg+'</b></div>');
                    }
                }
            });
        }
    });

    $('#btnReset').click(function() {
        $("#txKodebayar").val("");
        $("#txResult").addClass('d-none').html("");
        $('.qris-btn').hide();
        token = false;
    });

    function showQRReal() {
        document.getElementById("spin").removeAttribute("class");
        document.getElementById("imgqr").removeAttribute("class");
    }

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>ext/custom/autoNumeric.js"></script>
<script>
    jQuery(function($) {
        $('.aUse').autoNumeric('init', {
            mDec: '0'
        });
    });
</script>