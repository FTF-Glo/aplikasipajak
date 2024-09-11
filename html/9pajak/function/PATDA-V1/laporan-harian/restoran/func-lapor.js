$(function(){
	
	$('#JumlahMeja').autoNumeric('init');
    $('#JumlahKursi').autoNumeric('init');
    $('#JumlahPengunjung').autoNumeric('init');
    
    $('#TransactionAmount').autoNumeric('init');
    $('#CPM_TARIF_PAJAK').autoNumeric('init');
    $('#TaxAmount').autoNumeric('init');
	$('#TransactionDate').datetimepicker({format: 'Y-m-d h:i:s'});
	
	var form = $("#form-lapor");
    form.validate({
        rules: {
            "PAJAK[CPM_NPWPD]": "required",
            "PAJAK[CPM_NAMA_WP]": "required",
            "PAJAK[CPM_ALAMAT_WP]": "required",
            "PAJAK[CPM_NOP]": "required",
            "PAJAK[CPM_NAMA_OP]": "required",
            
            
            "PAJAK[JenisKamar]": "required",
            "PAJAK[TarifKamar]": "required",
            "PAJAK[JumlahMeja]": "required",
            "PAJAK[JumlahKursi]": "required",
            "PAJAK[TransactionAmount]": "required",
            "PAJAK[TransactionDate]": "required",
            "PAJAK[TaxAmount]": "required",
            
        },
        messages: {
            "PAJAK[CPM_NPWPD]": "harus diisi",
            "PAJAK[CPM_NAMA_WP]": "harus diisi",
            "PAJAK[CPM_ALAMAT_WP]": "harus diisi",
            "PAJAK[CPM_NOP]": "harus diisi",
            "PAJAK[CPM_NAMA_OP]": "harus diisi",
            
            "PAJAK[JenisKamar]": "harus diisi",
            "PAJAK[TarifKamar]": "harus diisi",
            "PAJAK[JumlahMeja]": "harus diisi",
            "PAJAK[JumlahKursi]": "harus diisi",
            "PAJAK[TransactionAmount]": "harus diisi",
            "PAJAK[TransactionDate]": "harus diisi",
            "PAJAK[TaxAmount]": "harus diisi",
        }
    });
    
    $('input.SUM').keyup(function(){
		var omzet = $('#TransactionAmount').autoNumeric('get');
		
		var tarif = Number($('#CPM_TARIF_PAJAK').val());
		var pajak = eval(omzet) * eval(tarif) / 100;
		$('#TaxAmount').autoNumeric('set',pajak);
	});
	
});
