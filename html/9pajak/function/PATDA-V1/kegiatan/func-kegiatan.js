$(document).ready(function() {
    $('.datepicker').datepicker({
        dateFormat: 'dd/mm/yy',
        changeYear: true,
        changeMonth: true,
        maxDate:0
    });
    $('#CPM_KECAMATAN_WP').change(function(){
        $.ajax({
            type: "POST",
            url: "/function/PATDA-V1/airbawahtanah/lapor/svc-lapor.php",
            data: {'function' : 'get_list_kelurahan', 'CPM_KEC_ID' : $(this).val()},
            async:false,
            beforeSend: function() {
                $('#CPM_KELURAHAN_WP').html("---Loading...---");
            },
            success: function(html){
                $('#CPM_KELURAHAN_WP').html(html);
            },
            complete: function(){
                $('#btn-submit').removeAttr('disabled');
            }
        });
    });
    
    
    /* if($('#CPM_KECAMATAN_WP').val()!==''){
        $('#CPM_KECAMATAN_WP').trigger('change');
        var kel = $('#CPM_KECAMATAN_WP').data('kel');
        $('#CPM_KELURAHAN_WP').val(kel);
    } */

    //$('#CPM_KECAMATAN_WP').select2({placeholder: "KECAMATAN"});
    var kecamatan_select2 = $('#CPM_KECAMATAN_WP').select2({placeholder: "KECAMATAN"});
    var bla = $('#CPM_KECAMATAN_WP').val();
    $('#CPM_KELURAHAN_WP').select2({placeholder: "KELURAHAN"});
    if(bla !== ""){
		    //kecamatan_select2.trigger('change');
    }

    jQuery.validator.addMethod("alphanumeric", function(value, element) {
		return this.optional( element ) || /^[a-zA-Z0-9_.]+$/.test( value );
	}, 'Hanya boleh huruf dan angka, hilangkan space dan yang lainnya');
	
    var form = $("#form-wp");
    form.validate({
        rules: {
			"nama_kegiatan": "required",
            "WP[CAPTCHA]": "required"
        },
        messages: {
            "nama_kegiatan":"harus diisi",
            "WP[CAPTCHA]": "harus diisi"
        }
    });

    $(".btn-submit").click(function() {
        var action = $(this).attr('action');
        var res = false;

        $('#function').val(action);
        if (action == "save") {
            if (form.valid()) {
                res = confirm("Simpan Pelaksana Kegiatan ini?");
            }
        } else if (action == "update") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin merubah Pelaksana Kegiatan ini?");
            }
        } else if (action == "delete") {
            res = confirm("Apakah anda yakin untuk menghapus Pelaksana Kegiatan ini?");
        } else if (action == "aktivasi") {
            res = confirm("Apakah anda yakin untuk mengaktivasi wajib pajak ini?");
        } else if (action == "blok") {
            res = confirm("Apakah anda yakin untuk memblokir wajib pajak ini?");
        } else if (action == "daftar") {
            if (form.valid()) {
                if (cekCaptcha() == "1") {
                    res = confirm("Apakah anda yakin untuk mendaftar?");
                } else {
                    alert("Verification code tidak valid.");
                }
            }
        }

        if (res) {
            document.getElementById("form-wp").submit();
        }
    });
    
    $("input.btn-print").click(function() {
        var action = $(this).attr('action');
        $('#function').val(action);
        $("#form-wp").attr('target', '_blank');
        document.getElementById("form-wp").submit();
    });

    $('.CPM_LUAR_DAERAH').change(function(){
        var value_daerah = $(this).val();
        var username = $("#CPM_USER").val();
        if (value_daerah == 1) {
            $(".DK").hide();
            $(".LK").show();
            if(username=="") $("#CPM_KOTA_WP").val("");
        }else{
            $(".DK").show();
            $(".LK").hide();
            $("#CPM_KOTA_WP").val("Lampung Tengah");
        }
    });

    $('.CPM_JENIS_WP').change(function(){
        var value_jenis_wp = $(this).val();
        if (value_jenis_wp == 1) { //WP_PRIBADI
            $(".WB").hide();
            $(".WP").show();
        } else if (value_jenis_wp == 2) { //WP_BADAN
            $(".WB").show();
            $(".WP").hide();
        }
    });
    $('.CPM_JENIS_TANDABUKTI').change(function(){
        $("#CPM_NO_TANDABUKTI").attr('placeholder', 'Nomor '+$(this).val());
    });
    $('.CPM_JENIS_PEKERJAAN').change(function(){
        if($(this).val()=='Lainnya'){
            $('#CPM_JENIS_PEKERJAAN1').show();
        }else{
            $('#CPM_JENIS_PEKERJAAN1').hide();
        }
    })
});

function cekCaptcha() {
    var kode = $("#CAPTCHA").val();
    var res = $.ajax({
        type: "post",
        url: "../function/" + DIR + "/registrasi-wp/svc-wp.php",
        data: "function=captcha&WP[CAPTCHA]=" + kode,
        async: false,
    });
    return res.responseText;
}
