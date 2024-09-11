function bayar(nopp,tahun,sts){
	//alert('Test');
	$.ajax({
		url: '/view/PBB/svc-update-status.php',
		type: 'post',
		data: 'nopp='+nopp+'&tahun='+tahun+'&sts='+sts,
		success: function(msg){
		//console.log(msg)
		//alert(msg);
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