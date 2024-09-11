function bayar(nopp,tahun,sts,uid){
	$.ajax({
		url: 'view/PBB/updatePBB/svc-update-status.php',
		type: 'post',
		data: 'uid='+uid+'&nopp='+nopp+'&tahun='+tahun+'&sts='+sts+"&GW_DBHOST="+GW_DBHOST+"&GW_DBNAME="+GW_DBNAME+"&GW_DBUSER="+GW_DBUSER+"&GW_DBPWD="+GW_DBPWD+"&GW_DBPORT="+GW_DBPORT,
		success: function(msg){
			if($.trim(msg)=='1'){
				if(sts=='0'){
					alert('Pembayaran berhasil!');
				}
				else{
					alert('Pembatalan bayar berhasil!');
				}
				onSearch (4);
			}else{
				alert('Gagal')
			}
		}
	});
}
function tgl_pembayaran(nopp,tahun,sts,uid,tgl){
	$.ajax({
		url: 'view/PBB/updatePBB/svc-update-status.php',
		type: 'post',
		data: 'uid='+uid+'&nopp='+nopp+'&tahun='+tahun+'&sts='+sts+'&tgl='+tgl+"&GW_DBHOST="+GW_DBHOST+"&GW_DBNAME="+GW_DBNAME+"&GW_DBUSER="+GW_DBUSER+"&GW_DBPWD="+GW_DBPWD+"&GW_DBPORT="+GW_DBPORT,
		success: function(msg){
			if($.trim(msg)=='1'){
				if(sts=='0'){
					alert('Pembayaran berhasil!');
				}
				else{
					alert('Pembatalan bayar berhasil!');
				}
				onSearch (4);
			}else{
				alert('Gagal')
			}
		}
	});
}
function prosespendataan(nop,tahun){
	$.ajax({
		url: 'view/PBB/updatePBB/svc-proses-pendataan.php',
		type: 'post',
		data: 'nop='+nop+'&tahun='+tahun+"&GW_DBHOST="+GW_DBHOST+"&GW_DBNAME="+GW_DBNAME+"&GW_DBUSER="+GW_DBUSER+"&GW_DBPWD="+GW_DBPWD+"&GW_DBPORT="+GW_DBPORT,
		success: function(msg){
		//console.log(msg)
		//alert(msg);
			if($.trim(msg)=='1'){
                            alert('Proses pengembalian ke pendataan berhasil!');
                            onSearchDataSPPTFinal(5);
			}else{
				alert('Proses pengembalian ke pendataan gagal')
			}
		}
	});
}
