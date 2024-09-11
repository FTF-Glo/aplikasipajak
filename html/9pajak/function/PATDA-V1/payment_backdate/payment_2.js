$(document).ready(function(){
	
	/*init autonumeric*/
	$('#simpatda_dibayar, #patda_denda, #patda_admin_gw, #patda_total_bayar').autoNumeric('init',{aSign:'Rp.',mDec: '0'});
	$('#jml-bayar, #jml-uang, #jml-kembali').autoNumeric('init',{mDec: '0'});
	$('#payment_paid').datepicker({
        dateFormat: 'yy-mm-dd',
        showOn: "button",
        buttonImageOnly: false,
        buttonText: "..."});
	
	var form_inquiry = $("#form-inquiry");
	form_inquiry.validate({
        rules: {
			payment_code:{
				required:true, 
				minlength: 8,
				number:false,
			}
        },
        messages: {
            payment_code:{
				required:"harus diisi",
				minlength:"minimum 8 karakter",
				number : "harus berisi angka"
			}
        },
        submitHandler: function(form) {
            var postData = {};
			postData.function = 'inquiry';
			postData.app = APP;
			postData.payment_code = $('#payment_code').val();
			postData.tanggal = $('#payment_paid').val();
			
			$('#jml-bayar, #jml-uang, #jml-kembali').autoNumeric('set',0);
			
			$.ajax({
				data:postData,
				url: "function/PATDA-V1/payment_backdate/svc-payment.php",
				type:'post',
				dataType:'json',
				success:function(msg){
					
					$('#expired_date').html('');
					$('#simpatda_dibayar, #patda_denda, #patda_admin_gw, #patda_total_bayar').autoNumeric('set',0);
						
					if(msg.RC == '00'){
						$('#expired_date').html(msg.expired_date);
						$('#simpatda_dibayar').autoNumeric('set',msg.simpatda_dibayar);
						$('#patda_denda').autoNumeric('set',msg.patda_denda);
						$('#patda_admin_gw').autoNumeric('set',msg.patda_admin_gw);
						$('#patda_total_bayar').autoNumeric('set',msg.patda_total_bayar);
						$('#jml-bayar').autoNumeric('set',msg.patda_total_bayar);
					}else{
						alert(msg.MSG);
					}
				}
			});
            return false;
        }
    });
    $('#btnInquiry').click(function(){
		form_inquiry.submit();
	})
    
    var form_bayar = $("#form-bayar");
	form_bayar.validate({
        rules: {
			'jml-bayar':"required",
			'jml-uang':"required"
        },
        messages: {
            'jml-bayar':"harus diisi",
			'jml-uang':"harus diisi"
        },
        submitHandler: function(form) {
			var bayar = $('#jml-bayar').autoNumeric('get');
			var kembali = $('#jml-kembali').autoNumeric('get');
			var uang = $('#jml-uang').autoNumeric('get');
			
			$('#jml-bayar').removeClass('error');
			$('#jml-uang').removeClass('error');
			$('#jml-kembali').removeClass('error');
			
			if(bayar <= 0){$('#jml-bayar').addClass('error');return false;}
			if(uang <= 0){$('#jml-uang').addClass('error');return false;}
			if(kembali < 0){$('#jml-kembali').addClass('error');return false;}
			
			if(confirm('Apakah anda yakin untuk membayar transaksi ini?') == false) return false;
            var postData = {};
			postData.function = 'bayar';
			postData.app = APP;
			postData.uid = UID;
			postData.payment_code = $('#payment_code').val();
			postData.payment_paid = $('#payment_paid').val();
			
			$.ajax({
				data:postData,
				url: "function/PATDA-V1/payment_backdate/svc-payment.php",
				type:'post',
				dataType:'json',
				success:function(msg){
					alert(msg.MSG);
					$('#expired_date').html('');
					$('#simpatda_dibayar, #patda_denda, #patda_admin_gw, #patda_total_bayar').autoNumeric('set',0);
					$('#jml-bayar, #jml-uang, #jml-kembali').autoNumeric('set',0);
					
					if(msg.RC == '00'){
						cetakKwitansiPenbayaran(msg);
					}
				}
			});
            return false;
        }
    });
    $('#btnBayar').click(function(){
		form_bayar.submit();
	})
	
	var cetakKwitansiPenbayaran = function(param){
		param.function = 'printKwitansi';
		param.app = APP;
		
		var param = Base64.encode(JSON.stringify(param));
		window.open("function/PATDA-V1/payment_backdate/svc-payment.php?param="+param,"_print");
	}
	
	var form_cetakulang = $("#form-cetakulang");
	form_cetakulang.validate({
        rules: {
			payment_code:{
				required:true, 
				minlength: 8,
				number:false,
			}
        },
        messages: {
            payment_code:{
				required:"harus diisi",
				minlength:"minimum 8 karakter",
				number : "harus berisi angka"
			}
        },
        submitHandler: function(form) {
            var postData = {};
			postData.payment_code = $('#payment_code').val();
			cetakKwitansiPenbayaran(postData);
            return false;
        }
    });
	$('#btnCetak').click(function(){
		form_cetakulang.submit();
	});
	
	$('#jml-uang').keyup(function(){
		var bayar = $('#jml-bayar').autoNumeric('get');
		var uang = $('#jml-uang').autoNumeric('get');
		
		var kembali = uang - bayar;
		$('#jml-kembali').autoNumeric('set',kembali);
		
	});
});
