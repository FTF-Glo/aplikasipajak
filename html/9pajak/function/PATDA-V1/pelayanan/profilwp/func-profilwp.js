$(document).ready(function() {

    var form = $("#form-wp");
    form.validate({
        rules: {
            "WP[CPM_USER]": "required",
            "WP[CPM_NPWPD]": "required",
            "WP[CPM_JENIS_PAJAK][]": "required",
            "WP[CAPTCHA]": "required"
        },
        messages: {
            "WP[CPM_USER]": "harus diisi",
            "WP[CPM_NPWPD]": "harus diisi",
            "WP[CPM_JENIS_PAJAK][]": "harus diisi",
            "WP[CAPTCHA]": "harus diisi"
        }
    });

    $(".btn-submit").click(function() {
        var action = $(this).attr('action');
        var res = false;

        $('#function').val(action);
        if (action == "save") {
            if (form.valid()) {
                if($.trim($('#NPASSWORD').val()) == ""){
                    alert("Password harus diisi!");
                }else if ($.trim($('#NPASSWORD').val()) != $.trim($('#CNPASSWORD').val())) {
                    alert("Password tidak cocok, silakan perbaiki!");
                    res = false;
                } else {
                    res = confirm("Apakah anda yakin untuk menyimpan wajib pajak ini?");
                }
            }
        } else if (action == "update") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk menyimpan perubahan ini?");
            }
        } else if (action == "delete") {
            res = confirm("Apakah anda yakin untuk menghapus wajib pajak ini?");
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