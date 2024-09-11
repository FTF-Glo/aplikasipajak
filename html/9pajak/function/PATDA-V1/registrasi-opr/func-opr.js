$(document).ready(function() {

    var form = $("#form-opr");
    form.validate({
        rules: {
            "OPR[CPM_USER]": "required",
            "OPR[CPM_NAMA]": "required",
            "OPR[CPM_ROLE]": "required",
            "OPR[CPM_NIP]": "required"
        },
        messages: {
            "OPR[CPM_USER]": "harus diisi",
            "OPR[CPM_NAMA]": "harus diisi",
            "OPR[CPM_ROLE]": "harus diisi",
            "OPR[CPM_NIP]": "harus diisi"
        }
    });

    $(".btn-submit").click(function() {
        var action = $(this).attr('action');
        var res = false;

        $('#function').val(action);
        if (action == "save") {
            if (form.valid()) {
                res = confirm("Apakah anda yakin untuk menyimpan wajib pajak ini?");
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
            document.getElementById("form-opr").submit();
        }
    });

    $("#btn-delete").click(function() {
        if (confirm("Apakah anda yakin untuk membatalkan perubahan?")) {
            $('#function').val("rollback");
            document.getElementById("form-opr").submit();
        }
    })
});

function cekCaptcha() {
    var kode = $("#CAPTCHA").val();
    var res = $.ajax({
        type: "post",
        url: "../function/" + DIR + "/registrasi-opr/svc-opr.php",
        data: "function=captcha&OPR[CAPTCHA]=" + kode,
        async: false,
    });
    return res.responseText;
}