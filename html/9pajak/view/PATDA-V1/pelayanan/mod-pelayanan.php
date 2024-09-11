<?php
//session_start();
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;
$_SESSION['role'];

function displayMenu() {
    global $DIR, $a, $m, $data;

    if($_SESSION['role'] == 'rmPatdaWp'){
		echo "\t<ul>\n";
	echo "\t\t<li><a class='tab' href=\"view/{$DIR}/pelayanan/svc-list-berkas.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'1','f':'fPatdaBerkas','mod':'ply'}") . "\">Berkas Masuk <b class='notif masuk'></b></a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/pelayanan/svc-list-berkas.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'3','s':'5','f':'fPatdaBerkas','mod':'ply'}") . "\">Cetak SSPD <b class='notif disetujui'></b></a></li>\n";
	echo "\t\t<li><a class='tab' href=\"view/{$DIR}/pelayanan/svc-list-berkas.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'6','s':'5','f':'fPatdaBerkas','mod':'ply'}") . "\">SSPD Sudah Bayar  <b class='notif sbayar'></b></a></li>\n";
    echo "\t</ul>\n";
	}else{
	
		echo "\t<ul>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/pelayanan/svc-list-berkas.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'1','f':'fPatdaBerkas','mod':'ply'}") . "\">Berkas Masuk <b class='notif masuk'></b></a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/pelayanan/svc-list-berkas.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'2','s':'2','f':'fPatdaBerkas','mod':'ply'}") . "\">Berkas Diterima <b class='notif diterima'></b></a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/pelayanan/svc-list-berkas.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'3','s':'5','f':'fPatdaBerkas','mod':'ply'}") . "\">Cetak SSPD <b class='notif disetujui'></b></a></li>\n";

	echo "\t\t<li><a class='tab' href=\"view/{$DIR}/pelayanan/svc-list-berkas.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'5','s':'5','f':'fPatdaBerkas','mod':'ply'}") . "\">Berkas Expired <b class='notif disetujui'></b></a></li>\n";
	echo "\t\t<li><a class='tab' href=\"view/{$DIR}/pelayanan/svc-list-berkas.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'6','s':'5','f':'fPatdaBerkas','mod':'ply'}") . "\">SSPD Sudah Bayar  <b class='notif sbayar'></b></a></li>\n";

    echo "\t</ul>\n";
	
	}
}
?>


<script type="text/javascript">
    var repeat = 0;
    
    $(document).ready(function() {
        $("#tabsContent").tabs({
            beforeLoad: function(event, ui) {
                ui.panel.html('<img src="image/large-loading.gif" />');
            }
        });
        
        $('#tabsContent').tabs({ active: <?php echo isset($_SESSION['_tab_berkas'])?$_SESSION['_tab_berkas'] : 0?> });
        <?php unset($_SESSION['_tab_berkas'])?>
    });
    $.ajax({
        type: "post",
        data: "function=read_dokumen_notif&tab=masuk;diterima;disetujui;sbayar",
        url: "function/<?php echo $DIR ?>/pelayanan/svc-berkas.php",
        dataType: "json",
        success: function(res) {
            $('.masuk').html(res.masuk + " new");
            $('.diterima').html(res.diterima + " new");
            $('.disetujui').html(res.disetujui + " new");
			$('.sbayar').html(res.sbayar + " new");

            if (res.masuk == 0)
                $('.masuk').hide();
            if (res.diterima == 0)
                $('.diterima').hide();
            if (res.disetujui == 0)
                $('.disetujui').hide();
			if (res.sbayar == 0)
				$('.sbayar').hide();
        }
    });

    function download_excel(id, url, type) {
        var form = document.createElement("form");
        form.setAttribute("method", 'post');
        form.setAttribute("target", 'excel');
        form.setAttribute("action", url);

        var h = $("#hidden-" + id);
        var mod = h.attr('mod');
        var id_pajak = h.attr('id_pajak');
        var s = h.attr('s');

        var tipe = document.createElement("input");
        tipe.setAttribute("type", "hidden");
        tipe.setAttribute("name", 'tipe');
        tipe.setAttribute("value", type);
        form.appendChild(tipe);
        
        var modul = document.createElement("input");
        modul.setAttribute("type", "hidden");
        modul.setAttribute("name", 'mod');
        modul.setAttribute("value", mod);
        form.appendChild(modul);

        var jenis_pajak = document.createElement("input");
        jenis_pajak.setAttribute("type", "hidden");
        jenis_pajak.setAttribute("name", 'CPM_JENIS_PAJAK');
        jenis_pajak.setAttribute("value", $('#CPM_JENIS_PAJAK-' + id).val());
        form.appendChild(jenis_pajak);

        var idTab = document.createElement("input");
        idTab.setAttribute("type", "hidden");
        idTab.setAttribute("name", 'i');
        idTab.setAttribute("value", id);
        form.appendChild(idTab);

        var status = document.createElement("input");
        status.setAttribute("type", "hidden");
        status.setAttribute("name", 's');
        status.setAttribute("value", s);
        form.appendChild(status);

        var app = document.createElement("input");
        app.setAttribute("type", "hidden");
        app.setAttribute("name", 'a');
        app.setAttribute("value", '<?php echo $a ?>');
        form.appendChild(app);

        var npwpd = document.createElement("input");
        npwpd.setAttribute("type", "hidden");
        npwpd.setAttribute("name", 'CPM_NPWPD');
        npwpd.setAttribute("value", $('#CPM_NPWPD-' + id).val());
        form.appendChild(npwpd);
        
        var sptpd = document.createElement("input");
        sptpd.setAttribute("type", "hidden");
        sptpd.setAttribute("name", 'CPM_NO_SPTPD');
        sptpd.setAttribute("value", $('#CPM_NO_SPTPD-' + id).val());
        form.appendChild(sptpd);

        var tahun = document.createElement("input");
        tahun.setAttribute("type", "hidden");
        tahun.setAttribute("name", 'CPM_TAHUN_PAJAK');
        tahun.setAttribute("value", $('#CPM_TAHUN_PAJAK-' + id).val());
        form.appendChild(tahun);

        var bulan = document.createElement("input");
        bulan.setAttribute("type", "hidden");
        bulan.setAttribute("name", 'CPM_MASA_PAJAK');
        bulan.setAttribute("value", $('#CPM_MASA_PAJAK-' + id).val());
        form.appendChild(bulan);

        var tran_date1 = document.createElement("input");
        tran_date1.setAttribute("type", "hidden");
        tran_date1.setAttribute("name", 'CPM_TGL_LAPOR1');
        tran_date1.setAttribute("value", $('#CPM_TGL_LAPOR1-' + id).val());
        form.appendChild(tran_date1);

        var tran_date2 = document.createElement("input");
        tran_date2.setAttribute("type", "hidden");
        tran_date2.setAttribute("name", 'CPM_TGL_LAPOR2');
        tran_date2.setAttribute("value", $('#CPM_TGL_LAPOR2-' + id).val());
        form.appendChild(tran_date2);
		
		var nilai_pajak = document.createElement("input");
        nilai_pajak.setAttribute("type", "hidden");
        nilai_pajak.setAttribute("name", 'CPM_NILAI_PAJAK');
        nilai_pajak.setAttribute("value", $('#CPM_NILAI_PAJAK-' + id).val());
        form.appendChild(nilai_pajak);

		var total_pajak = document.createElement("input");
        total_pajak.setAttribute("type", "hidden");
        total_pajak.setAttribute("name", 'TOTAL_PAJAK');
        total_pajak.setAttribute("value", $('#TOTAL_PAJAK-' + id).val());
        form.appendChild(total_pajak);
        // console.log(total_pajak);
        document.body.appendChild(form);
        form.submit();

       
    }

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    function getQRCode(id,sha1) {
        if(repeat==0){
            if(confirm("Proses ini akan men-generate QRIS code \n\nApakah mau lanjut ?\n")){
                repeat = 1;
                document.getElementById("idico"+id).src = "./image/large-loading.gif";
                hitit(id,sha1);
            }else{
                document.getElementById("idico"+id).src = "./image/icon/qr_disable.png";
                repeat = 0;
            }
        }
    }

    function hitit(id,sha1) {
        if(repeat!=0) {
            Ext.Ajax.request({
                url: "function/PATDA-V1/func-getQRIS-GET.php",
                method: "POST",
                params: {
                    idswitching:id,
                    sha1:sha1
                },
                success: function(result, request) {
                    var respon = JSON.parse(result.responseText);
                    if(respon.status) {
                        repeat=0;
                        alert("\nB E R H A S I L");
                        document.getElementById("idico"+id).src = "./image/icon/qr.png";
                        let elem = document.getElementById("divico"+id).firstElementChild;
                            elem.removeAttribute("onclick");
                            elem.removeAttribute("href");
                    }else if(respon.msg=="repeat"){
                        repeat++;
                        if(repeat>=4) {
                            repeat=0;
                            document.getElementById("idico"+id).src = "./image/icon/qr_disable.png";
                            alert("Gagal koneksi ke Server REST API");
                        }
                        sleep(2000).then(() => { hitit(token,id,code,exp,nop,tipe); });
                    }else{
                        repeat=0;
                        alert(respon.msg);
                        document.getElementById("idico"+id).src = "./image/icon/qr_disable.png";
                        let elem = document.getElementById("divico"+id).firstElementChild;
                            elem.removeAttribute("onclick");
                            elem.removeAttribute("href");
                    }
                },
                failure: function(result, request) {
                    repeat=0;
                    alert("Gagal mengirim data via Ajax Javascript,\nSilakan periksa dan test koneksi internet Anda !\n\n\nN O T E :\nJika akses Aplikasi ke Local IP\nSilakan daftarkan nomor Local IP nya terlebih dahulu");
                    document.getElementById("idico"+id).src = "./image/icon/qr_disable.png";
                }
            });
        }
    }
</script>
<div id="tabsContent">
    <?php echo displayMenu() ?>
</div>

