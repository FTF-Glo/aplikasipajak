//9pajak
function check_kodebayar(){
    var kodebayar = $("#kodebayar").val();
    $("#B_KODEBAYAR").val('');
    $("#B_NAMA").val('');
    $("#B_TOTAL").val('');
    $("#B_ALAMAT_WP").val('');
    $("#B_ALAMAT_OP").val('');
    $("#B_JP").val('');

    if(kodebayar === ''){
        swal({
            title: "Gagal!",
            text: "Kode bayar harus diisi",
            icon: "warning",
            button: "Tutup",
        });
    }else{
        $.ajax({
            url:'/view/PATDA-V1/payment_pembatalan/function-check.php',
            method:"POST",
            dataType:'json',
            data:{function:'check_kodebayar',kodebayar:kodebayar},
                beforeSend: function() {
                    setTimeout(function(){
                        $("#overlay").fadeOut(300);
                    },500);
                },
                success:function(data)
                {
                    if(data.RC ==  '01'){
                        swal({
                        title: "Gagal!",
                        text: "Data Tidak Tersedia!",
                        icon: "warning",
                        button: "Tutup",
                        });
                    }else if(data.RC ==  '02'){
                        swal({
                        title: "Gagal!",
                        text: "Tagihan Dari Bank",
                        icon: "warning",
                        button: "Tutup",
                        });
                    }else if(data.RC ==  '03'){
                        swal({
                        title: "Gagal!",
                        text: "Tagihan Belum Lunas",
                        icon: "warning",
                        button: "Tutup",
                        });
                    }else{
                        $("#B_KODEBAYAR").val(data.kodebayar);
                        $("#B_NAMA").val(data.nama);
                        $("#B_TOTAL").val(data.total);
                        $("#B_ALAMAT_WP").val(data.alamat_wp);
                        $("#B_ALAMAT_OP").val(data.alamat_op);
                        $("#B_JP").val(data.jp);
                    }
                    
                }
        });
    }
    
}

function batalkan_pembayaran(){
    var kodebayar = $("#B_KODEBAYAR").val();

        swal({
            title: "Pembatalan Pembayaran",
            text: "Batalkan Tagihan ?",
            icon: "warning",
            buttons: [
              'Tidak',
              'Ya, Batalkan Tagihan'
            ],
            dangerMode: true,
          }).then(function(isConfirm) {
            if (isConfirm) {

                $.ajax({
                    url:'/view/PATDA-V1/payment_pembatalan/function-check.php',
                    method:"POST",
                    dataType:'json',
                    data:{function:'batalkan_pembayaran',kodebayar:kodebayar},
                        beforeSend: function() {
                            setTimeout(function(){
                                $("#overlay").fadeOut(300);
                            },500);
                        },
                        success:function(data)
                        {
                            if(data.RC ==  '00'){
                                $("#B_KODEBAYAR").val('');
                                $("#B_NAMA").val('');
                                $("#B_TOTAL").val('');
                                $("#B_ALAMAT_WP").val('');
                                $("#B_ALAMAT_OP").val('');
                                $("#B_JP").val('');
        
                                swal({
                                title: "Sukses",
                                text: "Pembayaran Berhasil Dibatalkan",
                                icon: "success",
                                button: "Tutup",
                                });
                            }else{
                                swal({
                                title: "Gagal!",
                                text: "Gagal Membatalkan Pembayaran",
                                icon: "warning",
                                button: "Tutup",
                                });
                            }
                            
                        }
                });

            }
          });
    
}

