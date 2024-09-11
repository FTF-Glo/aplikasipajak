function loadZNTSuccess(params) {
    document.getElementById("div-znt-wait").innerHTML = "";
    if (params.responseText) {
        var objResult = Ext.decode(params.responseText);
        document.getElementById("OT_ZONA_NILAI").value = objResult.KODE;
    } else {
        document.getElementById("OT_ZONA_NILAI").value = "";
        document.getElementById("div-znt-wait").innerHTML = "<span class='error'>ZNT tidak ditemukan pada latitude dan longitude tersebut</span>";
    }
}

function loadZNTFailure(params) {
    document.getElementById("div-znt-wait").innerHTML = "<span class='error'>Terjadi kesalahan sistem. Ulangi pengambilan data</span>";
}

function loadZNT() {
    var latitude = document.getElementById("OT_LATITUDE").value;
    var longitude = document.getElementById("OT_LONGITUDE").value;
    //alert("lat:"+latitude+" and lon:"+longitude);
    if ((latitude.length > 0) && (longitude.length > 0)) {
        document.getElementById("div-znt-wait").innerHTML = "<img src=\"image/icon/loadinfo.net.gif\"/>";
        /*Ext.util.JSONP.request({ 
         url: 'http://192.168.30.2:9800/payment/pc/svr/central/inc/peta/getZNT.php?lat='+latitude+'&lon='+longitude, 
         callbackKey: 'jsonp_callback', 
         callback: function(data) { 
         // alert(data);
         // console.log(data.results); 
         // var msg = data.results; 
         // var html = tpl.apply(msg); 
         // resultPanel.update(html); 
         // console.log('SUCCESS'); 
         } 
         });
         */
        Ext.Ajax.request({
            url: 'inc/PBB/svc-znt-map.php',
            success: loadZNTSuccess,
            failure: loadZNTFailure,
            params: {lat: latitude, lon: longitude}
        });
    } else {
        alert("Latitude dan Longitude belum terisi");
    }
}

function viewMap(map_url) {
    var latitude = document.getElementById("OT_LATITUDE").value;
    var longitude = document.getElementById("OT_LONGITUDE").value;
	var controller = map_url.match("controller");
	var tmp = map_url.split("&");
    //alert("lat:"+latitude+" and lon:"+longitude);
    if ((latitude.length > 0) && (longitude.length > 0)) {
		if (controller == 'controller'){
			// alert (tmp[0]+'&lat='+latitude+'&lon='+longitude);
			window.open(tmp[0]+'&lat='+latitude+'&lon='+longitude, '_blank');
		} else {
			window.open(map_url+'?lat='+latitude+'&lon='+longitude, '_blank');
		}
    } else {
        alert("Latitude dan Longitude belum terisi");
    }
}

function loadNTSuccess(params) {
    //console.log(params);
    document.getElementById("div-nt-wait").innerHTML = "";
    if (params.responseText) {
        var objResult = Ext.decode(params.responseText);
        document.getElementById("OT_PAYMENT_SISTEM").value = objResult.njop;
        document.getElementById("NJOP_TANAH").value = objResult.njop;
        document.getElementById("OP_KELAS_TANAH").value = objResult.kelas;
    } else {
        document.getElementById("OT_PAYMENT_SISTEM").value = "";
        document.getElementById("NJOP_TANAH").value = "";
        document.getElementById("div-nt-wait").innerHTML = "<span class='error'>Gagal mengambil nilai</span>";
    }
}

function loadNTFailure(params) {
    document.getElementById("div-nt-wait").innerHTML = "<span class='error'>Terjadi kesalahan sistem. Ulangi pengambilan data</span>";
}

function loadNT() {
    var nop = document.getElementById("NOP1").value +
            document.getElementById("NOP2").value +
            document.getElementById("NOP3").value +
            document.getElementById("NOP4").value +
            document.getElementById("NOP5").value +
            document.getElementById("NOP6").value +
            document.getElementById("NOP7").value;

    var znts = document.getElementById("OT_ZONA_NILAI").value.split(" - ");
    //console.log(znts);
    var luas = document.getElementById("OP_LUAS_TANAH").value;
    var znt = znts[0];
    var njop_m2 = 0;
    if(znts[1]) {
        njop_m2 = znts[1].replace(/\./g,'');
    }
    
    if ((nop.length > 0) && (znt.length > 0)) {
        document.getElementById("div-nt-wait").innerHTML = "<img src=\"image/icon/loadinfo.net.gif\"/>";
        var params = "{NOP:'" + nop + "', ZNT:'" + znt + "', LUAS:'" + luas + "', TAHUN:'" + tahunTagihan + "', TABEL:'cppmod_pbb_sppt'}";
        params = Base64.encode(params);
        Ext.Ajax.request({
            url: 'inc/PBB/svc-tanah.php',
            success: loadNTSuccess,
            failure: loadNTFailure,
            params: {req: params}
        });
    } else {
        alert("NOP dan ZNT belum terisi");
    }
}

function loadNBSuccess(params) {
    document.getElementById("div-nb-wait").innerHTML = "";

    if (params.responseText) {
        var objResult = Ext.decode(params.responseText);

        if (objResult.RC == "0000") {
            document.getElementById("PAYMENT_SISTEM").value = objResult.NJOP_M2 * objResult.TIPE;
            document.getElementById("NJOP_BANGUNAN").value = objResult.NJOP_M2;
            //document.getElementById("OP_KELAS").value = objResult.KELAS;
        } else {
            document.getElementById("div-nb-wait").innerHTML = "<span class='error'>Tidak ditemukan nilai yang sesuai dengan data yang dimasukkan</span>";
        }
    } else {
        document.getElementById("div-nb-wait").innerHTML = "<span class='error'>Gagal mengambil nilai. Terjadi kesalahan server.</span>";
    }
}

function loadnilaiBangunanProses(params) {
    document.getElementById("div-nb-wait").innerHTML = "";
    if (params.responseText) {
        var objResult = Ext.decode(params.responseText);
        document.getElementById("NJOP_BANGUNAN").value = objResult.njop;
        let opnum = document.getElementById("OP_NUM").value;
        for (let i = 0; i < objResult.ext.length; i++) {
            if(objResult.ext[i].op_num==opnum){
                document.getElementById("PAYMENT_SISTEM").value = objResult.ext[i].njop;
            }
        }
    } else {
        document.getElementById("div-nb-wait").innerHTML = "<span class='error'>Gagal mengambil nilai. Terjadi kesalahan server.</span>";
    }
}

function loadNBFailure(params) {
    document.getElementById("div-nb-wait").innerHTML = "<span class='error'>Terjadi kesalahan sistem. Ulangi pengambilan data</span>";
}

function loadNB(svr_param) {
    var d = new Date();
    var nop = document.getElementById("NOP").firstChild.nodeValue;
    var tipe = document.getElementById("OP_LUAS_BANGUNAN").value;
    var jpb = document.getElementById("OP_PENGGUNAAN").value;
    var lantai = document.getElementById("OP_JML_LANTAI").value;
    var tahun = d.getFullYear();

    if ((nop.length > 0) && (tipe.length > 0) && (jpb.length > 0) && (lantai.length > 0)) {
        document.getElementById("div-nb-wait").innerHTML = "<img src=\"image/icon/loadinfo.net.gif\"/>";
        var params = "{\"SVR_PRM\":\"" + svr_param + "\",\"LANTAI\":\"" + lantai + "\",\"JPB\":\"" + jpb + "\",\"TIPE\":\"" + tipe + "\",\"NOP\":\"" + nop + "\", \"TAHUN\":\"" + tahun + "\"}";
        params = Base64.encode(params);
        Ext.Ajax.request({
            url: 'inc/PBB/svc-bangunan.php',
            success: loadNBSuccess,
            failure: loadNBFailure,
            params: {req: params}
        });
    } else {
        alert("Perhatian: JPB, Luas Bangunan, atau Jumlah Lantai belum terisi");
    }
}

function nilaiBangunanProses() {
    var d = new Date();
    var nop = document.getElementById("NOP").firstChild.nodeValue;
    var tahun = d.getFullYear();

    if(nop.length > 0) {
        document.getElementById("div-nb-wait").innerHTML = "<img src=\"image/icon/loadinfo.net.gif\"/>";
        var params = '{NOP:"' + nop + '", TAHUN:"' + tahun + '", TABEL1:"cppmod_pbb_sppt", TABEL2:"cppmod_pbb_sppt_ext"}';
        params = Base64.encode(params);
        Ext.Ajax.request({
            url: 'inc/PBB/svc-bangunan.php',
            success: loadnilaiBangunanProses,
            failure: loadNBFailure,
            params: {req: params}
        });
    } else {
        alert("Perhatian: JPB, Luas Bangunan, atau Jumlah Lantai belum terisi");
    }
}

function printToPDF(json) {
    window.open('./function/PBB/svc-print-app.php?q=' + json, '_newtab');
}

function trim(str) {
    return str.replace(/^\s+|\s+$/g, '');
}
function valid_form2(type) {

    if (document.getElementById("OP_KECAMATAN").value == '') {
        alert("Kecamatan belum terisi");
        return;
    }
    if (document.getElementById("OP_KELURAHAN").value == '') {
        alert("Kelurahan belum terisi");
        return;
    }
    if (document.getElementById("NOP5").value.length < 3) {
        alert("Blok belum terisi");
        return;
    }
    if (document.getElementById("NOP6").value.length < 1) {
        alert("Nomor Urut belum terisi");
        return;
    }
    if (document.getElementById("NOP7").value.length < 1) {
        alert("Kode belum terisi");
        return;
    }
    

    var nop = document.getElementById("NOP1").value +
            document.getElementById("NOP2").value +
            document.getElementById("NOP3").value +
            document.getElementById("NOP4").value +
            document.getElementById("NOP5").value + 
            document.getElementById("NOP6").value + 
            document.getElementById("NOP7").value;
    var uname = document.getElementById("uname").value;

    //console.log(enop);
    //document.getElementById("div-generatenop-wait").innerHTML = "<img src=\"image/icon/loadinfo.net.gif\"/>";
    var params = "{'nop' : '" + nop + "','uname' : '" + uname + "','method' : 'check'}";

    params = Base64.encode(params);
    Ext.Ajax.request({
        url: 'inc/PBB/svc-nop.php',
        params: {req: params},
        success: function(res) {
            var json = Ext.decode(res.responseText);
			//alert(json.r);
            if (json.r == false) {
				alert('NOP SUDAH ADA');
            } else {
                valid_form(type);				
				//alert('NOP SUDAH tidak ADA');
            }
			// alert(json.r);
			// return json.r;
        }
    });
	
}
function valid_form(type) {
    var NOP1 = document.getElementById("NOP1").value;
    var NOP2 = document.getElementById("NOP2").value;
    var NOP3 = document.getElementById("NOP3").value;
    var NOP4 = document.getElementById("NOP4").value;
    var NOP5 = document.getElementById("NOP5").value;
    var NOP6 = document.getElementById("NOP6").value;
    var NOP7 = document.getElementById("NOP7").value;

    if (trim(NOP1) == '' || trim(NOP2) == '' || trim(NOP3) == '' || trim(NOP4) == '' || trim(NOP5) == '' || trim(NOP6) == '' || trim(NOP7) == '') {
        alert("Kolom NOP harus di isi");
        return false;
    }
	

    /*Jika ada textfield hidden NOP_INDUK, berarti dokumen pemecahan, harus dilakukan konfirmasi penghapusan NOP induk */
    if ($('#NOP_INDUK').length > 0) {
        var luasOPInduk = parseInt($('#OP_INDUK_LUAS').val());
        var luasOPAnak = parseInt($('#OP_LUAS_TANAH').val());
        if (luasOPAnak >= luasOPInduk) {
            if (!confirm('Luas bumi NOP induk kurang dari atau sama dengan luas bumi NOP anak. Apakah NOP induk akan dihapus ?'))
                return false;
        }
    }
    
		if(type=='final'){
			if ($("#OP_PENETAPAN_INDUK").val()=="1"){
                if (!confirm(" Yakin untuk menetapkan NOP INDUK pada tahun berjalan ? ")){
                    return false;
                }
            }

			return confirm('Halaman ini akan difinalkan. Finalkan?');
		}else{
			
			return confirm('Halaman ini akan disimpan dulu sebelum melanjutkan. Lanjut?');
		}
	
	
    
}

function generateNOPSuccess(params) {
    document.getElementById("div-generatenop-wait").innerHTML = "";
    if (params.responseText) {
        var objResult = Ext.decode(params.responseText);
        if (objResult.r) {
            document.getElementById("NOP6").value = objResult.d.nourut;
            $('#is_new_nop').val('generate');
			loadPeta();
        } else {
            document.getElementById("NOP6").value = "";
            document.getElementById("div-generatenop-wait").innerHTML = "<span class='error'>Gagal men-generate NOP. Terjadi kesalahan server</span>";
        }
    } else {
        document.getElementById("NOP6").value = "";
        document.getElementById("div-generatenop-wait").innerHTML = "<span class='error'>Gagal men-generate NOP. Terjadi kesalahan server</span>";
    }
}

function generateNOPFailure(params) {
    document.getElementById("div-generatenop-wait").innerHTML = "<span class='error'>Terjadi kesalahan sistem. Ulangi Generate NOP</span>";
}

function generateNOP() {

    if (document.getElementById("OP_KECAMATAN").value == '') {
        alert("Kecamatan belum terisi");
        return;
    }
    if (document.getElementById("OP_KELURAHAN").value == '') {
        alert("Kelurahan belum terisi");
        return;
    }
    if (document.getElementById("NOP5").value.length < 3) {
        alert("Blok belum terisi");
        return;
    }
    if (document.getElementById("NOP7").value.length < 1) {
        alert("Kode belum terisi");
        return;
    }

    var nop = document.getElementById("NOP1").value +
            document.getElementById("NOP2").value +
            document.getElementById("NOP3").value +
            document.getElementById("NOP4").value +
            document.getElementById("NOP5").value;
    var enop = document.getElementById("NOP7").value;
    var uname = document.getElementById("uname").value;

    //console.log(enop);
    document.getElementById("div-generatenop-wait").innerHTML = "<img src=\"image/icon/loadinfo.net.gif\"/>";
    var params = "{'nop' : '" + nop + "','enop' : '" + enop + "','uname' : '" + uname + "','method':'generate'}";
    params = Base64.encode(params);
    Ext.Ajax.request({
        url: 'inc/PBB/svc-nop.php',
        success: generateNOPSuccess,
        failure: generateNOPFailure,
        params: {req: params}
    });

}

function loadKabkotaSuccess(params) {
    document.getElementById("div-sKota-wait").innerHTML = "";
    if (params.responseText) {
        var objResult = Ext.decode(params.responseText);
        if (objResult.r) {
            document.getElementById('sKota').innerHTML = objResult.d.stringselect;
        } else {
            document.getElementById("sKota").value = "";
            document.getElementById("div-sKota-wait").innerHTML = "<span class='error'>Gagal menampilkan daftar kota. Terjadi kesalahan server</span>";
        }
    } else {
        document.getElementById("sKota").value = "";
        document.getElementById("div-sKota-wait").innerHTML = "<span class='error'>Gagal menampilkan daftar kota. Terjadi kesalahan server</span>";
    }
}

function loadKabkotaFailure(params) {
    document.getElementById("div-sKota-wait").innerHTML = "<span class='error'>Terjadi kesalahan sistem. Ulangi Pemilihan Propinsi</span>";
}

function loadKabkota(x) {
    var val = x.value;
    document.getElementById('sKel2').innerHTML = "<select id=\"WP_KELURAHAN\" name=\"WP_KELURAHAN\"><option value=\"\">Kelurahan</option></select>";
    document.getElementById('sKec').innerHTML = "<select onchange=\"loadKel(this);\" id=\"WP_KECAMATAN\" name=\"WP_KECAMATAN\"><option value=\"\">Kecamatan</option></select>";
    document.getElementById('sKota').innerHTML = "";
    document.getElementById("div-sKota-wait").innerHTML = "<img src=\"image/icon/loadinfo.net.gif\"/>";
    var params = "{'type' : 'kota', 'id' : '" + val + "'}";
    params = Base64.encode(params);
    Ext.Ajax.request({
        url: 'inc/PBB/svc-kabkota.php',
        success: loadKabkotaSuccess,
        failure: loadKabkotaFailure,
        params: {req: params}
    });
}

function loadKecSuccess(params) {
    document.getElementById("div-sKec-wait").innerHTML = "";
    if (params.responseText) {
        var objResult = Ext.decode(params.responseText);
        if (objResult.r) {
            document.getElementById('sKec').innerHTML = objResult.d.stringselect;
        } else {
            document.getElementById('sKec').innerHTML = "<select onchange=\"loadKel(this);\" id=\"WP_KECAMATAN\" name=\"WP_KECAMATAN\"><option value=\"\">Kecamatan</option></select>";
            document.getElementById("div-sKec-wait").innerHTML = "<span class='error'>Gagal menampilkan daftar kecamatan. Terjadi kesalahan server</span>";
        }
    } else {
        document.getElementById('sKec').innerHTML = "<select onchange=\"loadKel(this);\" id=\"WP_KECAMATAN\" name=\"WP_KECAMATAN\"><option value=\"\">Kecamatan</option></select>";
        document.getElementById("div-sKec-wait").innerHTML = "<span class='error'>Gagal menampilkan daftar kecamatan. Terjadi kesalahan server</span>";
    }
}

function loadKecFailure(params) {
    document.getElementById("div-sKec-wait").innerHTML = "<span class='error'>Terjadi kesalahan sistem. Ulangi pemilihan kab/kodya</span>";
}

function loadKec(x) {
    var val = x.value;
    document.getElementById('sKel2').innerHTML = "<select id=\"WP_KELURAHAN\" name=\"WP_KELURAHAN\"><option value=\"\">Kelurahan</option></select>";
    document.getElementById('sKec').innerHTML = "";
    document.getElementById("div-sKec-wait").innerHTML = "<img src=\"image/icon/loadinfo.net.gif\"/>";
    var params = "{'type' : 'kecamatan', 'id' : '" + val + "'}";
    params = Base64.encode(params);
    Ext.Ajax.request({
        url: 'inc/PBB/svc-kabkota.php',
        success: loadKecSuccess,
        failure: loadKecFailure,
        params: {req: params}
    });
}

function loadKelSuccess(params) {
    document.getElementById("div-sKel2-wait").innerHTML = "";
    if (params.responseText) {
        var objResult = Ext.decode(params.responseText);
        if (objResult.r) {
            document.getElementById('sKel2').innerHTML = objResult.d.stringselect;
        } else {
            document.getElementById('sKel2').innerHTML = "<select id=\"WP_KELURAHAN\" name=\"WP_KELURAHAN\"><option value=\"\">Kelurahan</option></select>";
            document.getElementById("div-sKel2-wait").innerHTML = "<span class='error'>Gagal menampilkan daftar kelurahan. Terjadi kesalahan server</span>";
        }
    } else {
        document.getElementById('sKel2').innerHTML = "<select id=\"WP_KELURAHAN\" name=\"WP_KELURAHAN\"><option value=\"\">Kelurahan</option></select>";
        document.getElementById("div-sKel2-wait").innerHTML = "<span class='error'>Gagal menampilkan daftar kelurahan. Terjadi kesalahan server</span>";
    }
}

function loadKelFailure(params) {
    document.getElementById("div-sKel2-wait").innerHTML = "<span class='error'>Terjadi kesalahan sistem. Ulangi pemilihan kecamatan</span>";
}

function loadKel(x) {
    var val = x.value;
    document.getElementById('sKel2').innerHTML = "";
    document.getElementById("div-sKel2-wait").innerHTML = "<img src=\"image/icon/loadinfo.net.gif\"/>";
    var params = "{'type' : 'kelurahan', 'id' : '" + val + "'}";
    params = Base64.encode(params);
    Ext.Ajax.request({
        url: 'inc/PBB/svc-kabkota.php',
        success: loadKelSuccess,
        failure: loadKelFailure,
        params: {req: params}
    });
}

function copyNOPBersama() {

    if (document.getElementById("OP_KECAMATAN").value == '') {
        alert("Kecamatan belum terisi");
        return;
    }
    if (document.getElementById("OP_KELURAHAN").value == '') {
        alert("Kelurahan belum terisi");
        return;
    }
    if (document.getElementById("NOP5").value.length < 3) {
        alert("Blok belum terisi");
        return;
    }
    if (document.getElementById("NOP7").value.length < 1) {
        alert("Kode belum terisi");
        return;
    }


    $('#NOPB1').val($('#NOP1').val());
    $('#NOPB2').val($('#NOP2').val());
    $('#NOPB3').val($('#NOP3').val());
    $('#NOPB4').val($('#NOP4').val());
    $('#NOPB5').val($('#NOP5').val());
    $('#NOPB7').val('9');

}

function resetNOPBersama() {
    $('#NOPB1').val('');
    $('#NOPB2').val('');
    $('#NOPB3').val('');
    $('#NOPB4').val('');
    $('#NOPB5').val('');
    $('#NOPB6').val('');
    $('#NOPB7').val('');
}

function actionPenetapanMundur() {
    var tahun1 = $("#thn1").val();
    var tahun2 = $("#thn2").val();
    var intThn = parseInt(tahun2)-parseInt(tahun1);
    var nop_penilaian = $('#nop_penetapan').val();

    if (nop_penilaian =='') {
        alert('Isi terlebih dahulu NOP untuk ditetapkan!');
        return;
    }

    if (confirm('Anda yakin akan menetapkan data ini? Data akan langsung disiapkan sebagai bakal SPPT')) {
        $("#load-mask").css("display", "block");
        $("#load-content").fadeIn();
        for (let index = 0; index < intThn+1; index++) {
            Ext.Ajax.request({
                url: 'inc/PBB/svc-penetapan.php',
                success: actionPenetapanSusulanSuccess,
                failure: actionPenetapanSusulanFailure,
                params: {NOP:nop_penilaian, TAHUN:tahun1, TIPE:3},
                timeout: 30000000
            });
            tahun1++;
        }
    } else
        return;

}
function actionPenetapanSusulanMundurSuccess(params) {
    $("#load-content").css("display", "none");
    $("#load-mask").css("display", "none");

    if (params.responseText) {
        var objResult = Ext.decode(params.responseText);

        if (objResult.RC == "0000") {
            alert('Sebanyak ' + objResult.JML + ' NOP tahun '+objResult.TAHUN+' berhasil ditetapkan.');
            //document.location.reload(true);
        }else if (objResult.RC == "0066") {
            alert('NOP '+objResult.NOP+' Tahun '+objResult.TAHUN+' sudah pernah ditetapkan');
            
        } else {
            alert('Gagal melakukan penetapan. Terjadi kesalahan server');
        }
    } else {
        alert('Gagal melakukan penetapan. Terjadi kesalahan server');
    }
}

function actionPenetapanSuccess(params) {
    $("#load-content").css("display", "none");
    $("#load-mask").css("display", "none");

    if (params.responseText) {
        var objResult = Ext.decode(params.responseText);

        if (objResult.RC == "0000") {
            alert('Sebanyak ' + objResult.JML + ' NOP berhasil ditetapkan.');
            document.location.reload(true);
        } else {
            alert('Gagal melakukan penetapan. Terjadi kesalahan server');
        }
    } else {
        alert('Gagal melakukan penetapan. Terjadi kesalahan server');
    }
}

function actionPenetapanFailure(params) {
    $("#load-content").css("display", "none");
    $("#load-mask").css("display", "none");
    alert('Gagal melakukan penetapan. Terjadi kesalahan server');
}

function actionPenetapan(svr_param, tahun, susulan, uname) {
    if ($('#tgl_penetapan').val() == '') {
        alert('Tanggal penetapan harus diisi!');
        return;
    }

    if (confirm('Anda yakin akan menetapkan data ini? Data akan langsung disiapkan sebagai bakal SPPT')) {
        $("#load-mask").css("display", "block");
        $("#load-content").fadeIn();

        var kelurahan = $('#kel').val();
        var tgl = $('#tgl_penetapan').val();

        var tipe = '1';
        var params = "{\"SVR_PRM\":\"" + svr_param + "\",\"NOP\":\"" + listNOP + "\",\"TAHUN\":\"" + tahun + "\", \"TIPE\":\"" + tipe + "\", \"SUSULAN\":\"" + susulan + "\", \"TANGGAL\":\"" + tgl + "\", \"USER\":\"" + uname + "\"}";
        // var params = "{\"SVR_PRM\":\"" + svr_param + "\",\"KELURAHAN\":\"" + kelurahan + "\", \"TAHUN\":\"" + tahun + "\", \"TIPE\":\"" + tipe + "\", \"SUSULAN\":\"" + susulan + "\", \"TANGGAL\":\"" + tgl + "\", \"USER\":\"" +uname+ "\"}";
        params = Base64.encode(params);
        Ext.Ajax.request({
            url: 'inc/PBB/svc-penetapan.php',
            success: actionPenetapanSuccess,
            failure: actionPenetapanFailure,
            params: {req: params},
            timeout: 30000000
        });

    } else
        return;

}

function actionPenetapanSusulanSuccess(params) {
    $("#load-content").css("display", "none");
    $("#load-mask").css("display", "none");

    if (params.responseText) {
        var objResult = Ext.decode(params.responseText);

        if (objResult.RC == "0000") {
            alert('Sebanyak ' + objResult.JML + ' NOP berhasil ditetapkan.');
            document.location.reload(true);
        } else {
            alert('Gagal melakukan penetapan. Terjadi kesalahan server');
        }
    } else {
        alert('Gagal melakukan penetapan. Terjadi kesalahan server');
    }
}

function actionPenetapanSusulanFailure(params) {
    $("#load-content").css("display", "none");
    $("#load-mask").css("display", "none");
    alert('Gagal melakukan penetapan. Terjadi kesalahan server');
}

function actionPenetapanSusulan(svr_param, tahun, susulan, uname) {
    var arrListNOP = new Array();
    var i = 0;
    $("input:checkbox[name='check-all\\[\\]']").each(function() {
        if ($(this).is(":checked")) {
            arrListNOP[i] = $(this).val();
            i++;
        }
    });

    if (i == 0) {
        alert('Pilih terlebih dahulu NOP untuk ditetapkan!');
        return;
    }

    if ($('#tgl_penetapan').val() == '') {
        alert('Tanggal penetapan harus diisi!');
        return;
    }

    var listNOP = arrListNOP.join();

    if (confirm('Anda yakin akan menetapkan data ini? Data akan langsung disiapkan sebagai bakal SPPT')) {
        $("#load-mask").css("display", "block");
        $("#load-content").fadeIn();

        var tipe = '2';
        var tgl = $('#tgl_penetapan').val();
        var params = "{\"SVR_PRM\":\"" + svr_param + "\",\"NOP\":\"" + listNOP + "\", \"TAHUN\":\"" + tahun + "\", \"TIPE\":\"" + tipe + "\", \"SUSULAN\":\"" + susulan + "\", \"TANGGAL\":\"" + tgl + "\", \"USER\":\"" +uname+ "\"}";
        params = Base64.encode(params);
        Ext.Ajax.request({
            url: 'inc/PBB/svc-penetapan.php',
            success: actionPenetapanSusulanSuccess,
            failure: actionPenetapanSusulanFailure,
            params: {req: params},
            //timeout: 30000000
        });

    } else
        return;

}

function actionPenetapanTerseleksi(svr_param, tahun, susulan, uname) {
    var arrListNOP = new Array();
    var i = 0;
    $("input:checkbox[name='check-all\\[\\]']").each(function() {
        if ($(this).is(":checked")) {
            arrListNOP[i] = $(this).val();
            i++;
        }
    });

    if (i == 0) {
        alert('Pilih terlebih dahulu NOP untuk ditetapkan!');
        return;
    }

    if ($('#tgl_penetapan').val() == '') {
        alert('Tanggal penetapan harus diisi!');
        return;
    }

    var listNOP = arrListNOP.join();

    if (confirm('Anda yakin akan menetapkan data ini? Data akan langsung disiapkan sebagai bakal SPPT')) {
        $("#load-mask").css("display", "block");
        $("#load-content").fadeIn();
        var tipe = '2';
        Ext.Ajax.request({
            url: 'inc/PBB/svc-penetapan.php',
            success: actionPenetapanSusulanSuccess,
            failure: actionPenetapanSusulanFailure,
            params: {NOP:listNOP, TAHUN:tahun, TIPE:tipe, SUSULAN:0},
            timeout: 30000000
        });

    } else
        return;

}

function backToVeri(nop) {
    if (confirm('yakin ingin mengembalikan data ini ke Verifikasi II ?')) {
        Ext.Ajax.request({
            url: 'view/PBB/svc-backto-veri.php',
            params: {nop:nop},
            success: actionAfterBackToVeri,
            timeout: 3000
        });
    } else
        return;
}

function actionAfterBackToVeri() {
    alert('Data NOP berhasil di kembalikan ke Verifikasi II');
    document.location.reload(true);
}

function actionPenilaianSuccess(params) {
    $("#load-content").css("display", "none");
    $("#load-mask").css("display", "none");

    if (params.responseText) {
        var objResult = Ext.decode(params.responseText);

        if (objResult.JUMLAH>0) {
            alert('Sebanyak ' + objResult.JUMLAH + ' NOP berhasil dinilai.');
            document.location.reload(true);
        } else {
            alert('Gagal melakukan penilaian. Terjadi kesalahan server');
        }
    } else {
        alert('Gagal melakukan penilaian. Terjadi kesalahan server');
    }
}

function actionPenilaianFailure(params) {
    $("#load-content").css("display", "none");
    $("#load-mask").css("display", "none");
    alert('Gagal melakukan penilaian. Terjadi kesalahan server');
}

function actionPenilaian(svr_param, tahun, tipe, susulan) {
    var nop = '';

    if (tipe == '2') {
        var arrListNOP = new Array();
        var i = 0;
        $("input:checkbox[name='check-all\\[\\]']").each(function() {
            if ($(this).is(":checked")) {
                arrListNOP[i] = $(this).val();
                i++;
            }
        });

        if (i == 0) {
            alert('Pilih terlebih dahulu NOP untuk ditetapkan!');
            return;
        }

        nop = arrListNOP.join();
    }

    if (confirm('Anda yakin akan melakukan penilaian terhadap data ini?')) {
        console.log(nop);

        var kelurahan = '';

        if (tipe == '1') {
            kelurahan = $('#kel').val();
        }

        $("#load-mask").css("display", "block!important");
        $("#load-content").fadeIn();

        if(susulan==1){
            var params = "{\"TIPE\":\"2\",\"KELURAHAN\":\"" + kelurahan + "\", \"NOP\":\"" + nop + "\", \"TAHUN\":\"" + tahun + "\"}";
        }else{
            var params = "{\"TIPE\":\"1\",\"KELURAHAN\":\"" + kelurahan + "\", \"NOP\":\"" + nop + "\", \"TAHUN\":\"" + tahun + "\"}";
        }

        params = Base64.encode(params);
        Ext.Ajax.request({
            url: 'inc/PBB/svc-penilaian.php',
            success: actionPenilaianSuccess,
            failure: actionPenilaianFailure,
            params: {req: params},
            //timeout: 30000000
        });

    } else
        return;
}

function actionPenilaianMundur(susulan) {
    var tahun1 = $("#thn1").val();
    var tahun2 = $("#thn2").val();

    if (parseInt(tahun1) > parseInt(tahun2)) {
        alert("Tahun ke-1 tidak boleh lebih besar dari Tahun ke-2");
        document.getElementById("thn1").value = parseInt(tahun2) - 1;
        return false;
    }
    
    
    if(confirm('Anda yakin akan melakukan penilaian terhadap data ini?')) {
        $("#load-mask").css("display", "block");
        $("#load-content").fadeIn();
        var nops = document.getElementById("nop_penetapan").value;
        var params = '{TIPE:5, TAHUN:' + tahun2 + ', KELURAHAN:"", NOP:"' + nops + '"}';
        params = Base64.encode(params);
        Ext.Ajax.request({
            url: 'inc/PBB/svc-penilaian.php',
            success: actionPenilaianMundurSuccess,
            failure: actionPenilaianFailure,
            params: {req: params}
            //timeout: 30000000
        });
    } else
        return;

}


function actionPenilaianMundurSuccess(params) {
    $("#load-content").css("display", "none");
    $("#load-mask").css("display", "none");
    var tahun2 = $("#thn2").val();

    if (params.responseText) {
        var objResult = Ext.decode(params.responseText);

        if (objResult.JUMLAH>0) {
            alert('Sebanyak ' + objResult.JUMLAH + ' NOP tahun '+ tahun2 +' berhasil dinilai.');
            //document.location.reload(true);
            
            $('#btn-penetapan-mundur').removeAttr('disabled', true);
            $('#nop_penetapan').attr('readonly', true);
            onSearchDataSPPTMundur();
            
        } else {
            alert('Gagal melakukan penilaian. Terjadi kesalahan server');
        }
    } else {
        alert('Gagal melakukan penilaian. Terjadi kesalahan serverssssss');
        onSearchDataSPPTMundur();
    }
}
function changeMethodNOP(obj) {
    if ($(obj).attr('method') == 1) {
        $('#generateMethodSpan').hide();
        $('#manualMethodSpan').show();
        $(obj).html('Generate Input');
        $(obj).attr('method', 0);
        $('#NOP6').removeAttr('readonly').val('');

    } else {
        $('#generateMethodSpan').show();
        $('#manualMethodSpan').hide();
        $(obj).html('Manual Input');
        $(obj).attr('method', 1);
        $('#NOP6').attr('readonly', 'readonly').val('');

    }
}



function checkNOP() {

    if (document.getElementById("OP_KECAMATAN").value == '') {
        alert("Kecamatan belum terisi");
        return;
    }
    if (document.getElementById("OP_KELURAHAN").value == '') {
        alert("Kelurahan belum terisi");
        return;
    }
    if (document.getElementById("NOP5").value.length < 3) {
        alert("Blok belum terisi");
        return;
    }
    if (document.getElementById("NOP6").value.length < 1) {
        alert("Nomor Urut belum terisi");
        return;
    }
    if (document.getElementById("NOP7").value.length < 1) {
        alert("Kode belum terisi");
        return;
    }
    

    var nop = document.getElementById("NOP1").value +
            document.getElementById("NOP2").value +
            document.getElementById("NOP3").value +
            document.getElementById("NOP4").value +
            document.getElementById("NOP5").value + 
            document.getElementById("NOP6").value + 
            document.getElementById("NOP7").value;
    var uname = document.getElementById("uname").value;

    //console.log(enop);
    document.getElementById("div-generatenop-wait").innerHTML = "<img src=\"image/icon/loadinfo.net.gif\"/>";
    var params = "{'nop' : '" + nop + "','uname' : '" + uname + "','method' : 'check'}";
    params = Base64.encode(params);
    Ext.Ajax.request({
        url: 'inc/PBB/svc-nop.php',
        params: {req: params},
        success: function(res) {
            var json = Ext.decode(res.responseText);
            if (json.r == false) {
                document.getElementById("NOP6").value = '';
                alert('NOP sudah ada sebelumnya!');
            } else {
                alert('NOP berhasil!');
                $('#is_new_nop').val('check');
            }
        }
    });

}
function checkNOP2() {
    var nop = document.getElementById("NOP1").value +
            document.getElementById("NOP2").value +
            document.getElementById("NOP3").value +
            document.getElementById("NOP4").value +
            document.getElementById("NOP5").value + 
            document.getElementById("NOP6").value + 
            document.getElementById("NOP7").value;
    var uname = document.getElementById("uname").value;

    //console.log(enop);
    //document.getElementById("div-generatenop-wait").innerHTML = "<img src=\"image/icon/loadinfo.net.gif\"/>";
    var params = "{'nop' : '" + nop + "','uname' : '" + uname + "','method' : 'check'}";
    params = Base64.encode(params);
    Ext.Ajax.request({
        url: 'inc/PBB/svc-nop.php',
        params: {req: params},
        success: function(res) {
            var json = Ext.decode(res.responseText);
            if (json.r == false) {
                document.getElementById("NOP6").value = '';
                alert('NOP sudah ada sebelumnya!');
            } else {
               
            }
        }
    });

}

function actionSendNotificationSuccess(params) {
    $("#load-content").css("display", "none");
    $("#load-mask").css("display", "none");

    if (params.responseText) {
        var objResult = Ext.decode(params.responseText);
        if (objResult.JML_SUCCESS == 0) {
			alert('Gagal mengirim notifikasi. Terjadi kesalahan server'); 
		} else if ((objResult.JML_SUCCESS > 0) && (objResult.JML_FAILURE > 0)) {
			alert('Notifikasi terkirim ke '+ objResult.JML_SUCCESS +' NOP. \nNotifikasi gagal kirim ke ' + objResult.JML_FAILURE + ' NOP, yaitu: '+ objResult.NOP_FAILURE +''); 
        } else {
			alert('Berhasil mengirim notifikasi.');
        } 
    } else {
        alert('Gagal mengirim notifikasi. Terjadi kesalahan server');
    }
}

function actionSendNotificationFailure(params) {
    $("#load-content").css("display", "none");
    $("#load-mask").css("display", "none");
    alert('Gagal mengirim notifikasi. Terjadi kesalahan server');
}

function actionSendNotification(sms_param, type) {
    var arrListID 	= new Array();
	var arrListDest = new Array();
    var i = 0;
    $("input:checkbox[name='check-all\\[\\]']").each(function() {
        if ($(this).is(":checked")) {
			var prm			= $(this).val();
			var prm			= prm.split("+");
			arrListID[i] 	= prm[0];
			arrListDest[i]	= prm[1];
            i++;
        }
    });

    if (i == 0) {
        alert('Pilih terlebih dahulu nomor berkas untuk dikirim notifikasi!');
        return;
    }

    // var listID 		= arrListID.join();
	// var listDest 	= arrListDest.join();

    if (confirm('Anda yakin akan mengirim notifikasi?')) {
        $("#load-mask").css("display", "block");
        $("#load-content").fadeIn();
		var len = arrListDest.length;
		for (x=0;x<len;x++) { 
			sendSMS(sms_param,type,arrListDest[x]);
		}
    } else
        return;
		
			
}

function sendSMS(sms_param,type,dest){
	// Decode the Param
	var decodedString 	= Base64.decode(sms_param);
	var param 			= decodedString.split("&");
	var user 			= param[1];
	var pass 			= param[2];
	var url_sms			= param[0];
	//Format Pesan harus <= 150 karakter
	var message = 'Testing%20message%20PBB';
	var url_with_param 	= url_sms+"?username="+user+"&password="+pass+"&hp="+dest+"&message="+message;
	// var url_with_param 	= url_sms+"?username="+user+"&password="+pass;
	
	// alert(param);
	$.ajax({
        type: "POST",
        url: url_with_param,
        success: function(msg){
			// alert(Notifikasi berhasil dikirim!);
			console.log(msg)
        }
    });
}

// function actionSendNotification(svr_param, type, appId) {
    // var arrListNOP = new Array();
    // var i = 0;
    // $("input:checkbox[name='check-all\\[\\]']").each(function() {
        // if ($(this).is(":checked")) {
            // arrListNOP[i] = $(this).val();
            // i++;
        // }
    // });

    // if (i == 0) {
        // alert('Pilih terlebih dahulu nomor berkas untuk dikirim notifikasi!');
        // return;
    // }

    // var listNOP = arrListNOP.join();

    // if (confirm('Anda yakin akan mengirim notifikasi?')) {
        // $("#load-mask").css("display", "block");
        // $("#load-content").fadeIn();

        // var params = "{\"SVR_PRM\":\"" + svr_param + "\",\"NOP\":\"" + listNOP + "\",\"type\":\"" + type + "\",\"appId\":\"" + appId + "\"}";
        // params = Base64.encode(params);
        // Ext.Ajax.request({
            // url: 'function/PBB/sms/svc-sms-masking.php',
            // success: actionSendNotificationSuccess,//smsRespon,
            // failure: actionSendNotificationFailure,//smsRespon,
            // params: {req: params},
            // timeout: 300000
        // });

    // } else
        // return;

// }

$(document).ready(function(){
	$("#modalDialog").dialog({
		autoOpen: false,
		modal: true,
		width: 900,
		resizable: false,
		draggable: false,
		height: 'auto',
		title: '',
		position: ['middle', 20]
	});
})


function displayFormWp(id){
	$("#modalDialog").dialog('open');
	$("#modalDialog").load("function/PBB/nop/wp/form-edit-dialog.php?id="+id+"&a="+a);
}

function padWithLeadingZeros(num, totalLength) {
    return String(num).padStart(totalLength, '0');
}

function cekWP(evt, x) {
    x.value = trim(x.value)
    if(x.value == '') {
        const d = new Date();
        let month = d.getMonth();
        var txtrand = '' + Math.random();
        let bx = txtrand.length;
        let ax = parseInt(bx) - 3;
        var prov = document.getElementById("NOP1").value;
        var nomorRandom = prov + '000000000' + padWithLeadingZeros(month, 2) + txtrand.substring(ax,bx);
        x.value = nomorRandom;
    }

    if(x.value.length <= 15) {
        alert("digit nomor KTP masih kurang");
        return false;
    }
    if(x.value.length >= 17) {
        alert("digit nomor KTP terlalu banyak");
        return false;
    }
    // $("#load-mask").css("display","block");
    // $("#load-content").fadeIn();
    var noktp = x.value.replace(/[^0-9.]/g, '');
    document.getElementById("div-loadwp-wait").innerHTML = "&nbsp;&nbsp;<img src=\"image/icon/loadinfo.net.gif\"/>&nbsp;&nbsp;<font color=\"red\">Proses pencarian data KTP</font>";
    var params = "{'noktp' : '" + noktp + "'}";
    params = Base64.encode(params);
    Ext.Ajax.request({
        url: 'inc/PBB/svc-noktp.php',
        params: {req: params},
        success: function(res) {
            document.getElementById("div-loadwp-wait").innerHTML = "";
            var json = Ext.decode(res.responseText);
            $("input[name=WP_PEKERJAAN]").attr('disabled', true).attr('checked', false);
            
            if (json.r == true) {
                $("input[name=WP_STATUS][value=" + json.CPM_WP_STATUS + "]").attr('checked', 'checked').attr('disabled', false);
                $("input[name=WP_PEKERJAAN][value=" + json.CPM_WP_PEKERJAAN + "]").attr('checked', 'checked').attr('disabled', false);
                
                document.getElementById("WP_NAMA").value = json.CPM_WP_NAMA;
                document.getElementById("WP_ALAMAT").value = json.CPM_WP_ALAMAT;
                if (document.getElementById('BLOK_KAV_NO_WP')){
                    document.getElementById("BLOK_KAV_NO_WP").value = json.CPM_WP_ALAMAT_NOMOR;
                }
                document.getElementById("WP_RT").value = json.CPM_WP_RT;
                document.getElementById("WP_RW").value = json.CPM_WP_RW;
                document.getElementById("WP_PROPINSI").value = json.CPM_WP_PROPINSI;
                document.getElementById("WP_KOTAKAB").value = json.CPM_WP_KOTAKAB;
                document.getElementById("WP_KECAMATAN").value = json.CPM_WP_KECAMATAN;
                document.getElementById("WP_KELURAHAN").value = json.CPM_WP_KELURAHAN;
                document.getElementById("WP_KODEPOS").value = json.CPM_WP_KODEPOS;
                document.getElementById("WP_NO_HP").value = json.CPM_WP_NO_HP;
                //$("#div-tmbahwp").html('');
                /*ARD+- menambah link edit saat ditemukan*/                    
                $("#div-tmbahwp").html("<a href=javascript:displayFormWp('"+noktp+"')>Edit WP?</a>");
                // alert('No KTP Ditemukan');
            } else {
                alert("Nomor KTP Tidak Ditemukan\n\nharus ada\nNo. KTP adalah komponen penting untuk\nPenetapan SPPT\n\n16 Digit");
                document.getElementById("WP_NAMA").value = '';
                document.getElementById("WP_ALAMAT").value = '';
                if (document.getElementById('BLOK_KAV_NO_WP')){
                    document.getElementById("BLOK_KAV_NO_WP").value = '';
                }
                document.getElementById("WP_RT").value = '';
                document.getElementById("WP_RW").value = '';
                document.getElementById("WP_PROPINSI").value = '';
                document.getElementById("WP_KOTAKAB").value = '';
                document.getElementById("WP_KECAMATAN").value = '';
                document.getElementById("WP_KELURAHAN").value = '';
                document.getElementById("WP_KODEPOS").value = '';
                document.getElementById("WP_NO_HP").value = '';
                $("#div-tmbahwp").html("<a href=javascript:displayFormWp('"+noktp+"')>No KTP tidak ditemukan, Input WP Baru?</a>");
            }
            $("#load-content").css("display","none");
            $("#load-mask").css("display","none");
        },
        failure: function(res) {
            $("#load-content").css("display","none");
            $("#load-mask").css("display","none");
            document.getElementById("div-loadwp-wait").innerHTML = "";
            alert('Pengecekan No KTP Gagal!');
        }
    });
    
}

function deleteLampiranSuccess(params){
        $("#load-content").css("display","none");
        $("#load-mask").css("display","none");
	
	if(params.responseText){
		if (params.responseText == "sukses") {
			alert('Menghapus lampiran sukses.');
                        document.location.reload(true);
		} else {
			alert('Gagal menghapus lampiran. Terjadi kesalahan server');
		}
	} else {
		alert('Gagal menghapus lampiran. Terjadi kesalahan server');
	}
}

function deleteLampiranFailure(params){
	$("#load-content").css("display","none");
        $("#load-mask").css("display","none");
        alert('Gagal menghapus lampiran. Terjadi kesalahan server');
}
function deleteLampiran(doc_id, op_num) {
        if(confirm('Anda yakin data lampiran akan dihapus?')){
            var params = "{\"DOC_ID\":\""+doc_id+"\", \"OP_NUM\":\""+op_num+"\"}";
            params = Base64.encode(params);
            Ext.Ajax.request({
                    url : 'function/PBB/consol/svc-deletelampiran.php',
                    success: deleteLampiranSuccess,
                    failure: deleteLampiranFailure,			
                    params :{req:params}
            }); 
        }
}
