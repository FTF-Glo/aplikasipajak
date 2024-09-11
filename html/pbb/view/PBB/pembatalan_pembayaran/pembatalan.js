function inquiryPembatalan() {
    var mode = $("#mode").val();
    var kode_bayar = $("#kode_bayar").val();
    var nop = $("#nop").val();
    var tahun = $("#year").val();
    var valid = false;
    var data = {
        func: "inquiry",
        mode: mode,
        nop: nop,
        tahun: tahun,
        kode_bayar: kode_bayar
    };
    //validation
    if (mode == "kode_bayar") {
        if (kode_bayar == "") {
            alert("Silahkan isi kode bayar terlebih dahulu!!")
        } else {
            valid = true;
        }
    } else {
        if (nop == "") {
            alert("Silahkan isi NOP terlebih dahulu!!")
        } else {
            valid = true;
        }
    }

    if (valid) {
        $.ajax({
            type: 'POST',
            url: './view/PBB/pembatalan_pembayaran/svc/dataPembatalan.php',
            data: data,
            success: function (res) {
                d = jQuery.parseJSON(res);
                if (d.msg == "00") {
                    if (d.data.STATUS == "") {
                        alert("Data tersebut belum melakukan pembayaran!!");
                    } else if (d.data.STATUS == "2") {
                        alert("Data tersebut sudah dibatalkan !!");
                    } else {
                        setDataInquiry(d.data);
                        document.getElementById("cancel").disabled = false;
                    }
                } else {
                    alert("Data tidak ditemukan!!");
                }
            },
            error: function () {
                alert("Connection Error!!");
            }
        });
    }
}




function pembatalan() {
    var mode = $("#mode").val();
    var kode_bayar = $("#kode_bayar").val();
    var nop = $("#nop").val();
    var tahun = $("#year").val();
    var tgl = $("#tgl-batal").val();
    var ket = $("#keterangan").val();
    var uname = $("#uname").val();
    var data = {
        func: "execute",
        mode: mode,
        nop: nop,
        tahun: tahun,
        kode_bayar: kode_bayar,
        tgl_pembatalan: tgl,
        ket: ket,
        uname: uname
    };

    if (confirm("Yakin akan membatalkan ?")) {
        $.ajax({
            type: 'POST',
            url: './view/PBB/pembatalan_pembayaran/svc/dataPembatalan.php',
            data: data,
            success: function (res) {
                d = jQuery.parseJSON(res);
                if (d.msg == "00") {
                    alert("Data berhasil dibatalkan");
                    clearInput();
                } else if (d.msg == "11") {
                    alert("Pembayaran dari bank tidak bisa di batalkan!!");
                } else {
                    alert("Data tidak ditemukan!!");
                }
            },
            error: function () {
                alert("Connection Error!!");
            }
        });
    }
}

function setDataInquiry(data) {
    $('#wp-name').html(data.NAMA);
    $('#wp-duedate').html(data.TGL_PEMBAYARAN);
    $('#wp-address').html(data.ALAMAT);
    $('#wp-kelurahan').html(data.KELURAHAN);
    $('#wp-rtRw').html(data.RT_RW);
    $('#wp-kecamatan').html(data.KECAMATAN);
    $('#wp-kabupaten').html(data.KOTA);
    $('#wp-kdPos').html(data.KODE_POS);
    $('#wp-amount').html(formatRupiah(String(data.TAGIHAN)));
    $('#wp-penalty').html(formatRupiah(String(data.DENDA)));
    $('#wp-admin').html(formatRupiah("0"));
    $('#wp-totalamount').html(formatRupiah(String(data.TOTAL)));
}

function clearInput() {
    $('#wp-name').html("-");
    $('#wp-duedate').html("0000-00-00");
    $('#wp-address').html("-");
    $('#wp-kelurahan').html("-");
    $('#wp-rtRw').html("-");
    $('#wp-kecamatan').html("-");
    $('#wp-kabupaten').html("-");
    $('#wp-kdPos').html("-");
    $('#wp-amount').html("Rp.");
    $('#wp-penalty').html("Rp.");
    $('#wp-admin').html("Rp.");
    $('#wp-totalamount').html("Rp.");
    $("#keterangan").val("");
    // $("#nm_pajak").val("");
}

function formatRupiah(angka) {
    var prefix = "Rp. ";
    var number_string = angka.replace(/[^,\d]/g, '').toString(),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    // tambahkan titik jika yang di input sudah menjadi angka ribuan
    if (ribuan) {
        separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
}