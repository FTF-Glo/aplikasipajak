<script src="jtable/jquery-ui-1.10.4.min.js" type="text/javascript"></script>
<script src="jtable/jquery.jtable.js" type="text/javascript"></script>
<script type="text/javascript" src="jtable/localization/jquery.jtable.id.js"></script>
<link href="jtable/themes/metro/green/jtable.css" rel="stylesheet" type="text/css" />
<?php //echo "test"; exit; ?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#TableContainer').jtable({
            title: 'LIST PERUBAHAN NOP',
            paging: true, 
            pageSize: 10, 
            actions: {
				listAction: 'function/PBB/pemekaran/f_pengecekanNOP.php?ID=<?php echo $sessID?>&action=0'
			},
            fields: {
				JENIS 			: {title: 'JENIS', width: '15%', key: true},
				NOP_LAMA 		: {title: 'NOP LAMA', width: '10%'},
				KECAMATAN_LAMA 	: {title: 'KECAMATAN LAMA', width: '10%'},
				KELURAHAN_LAMA 	: {title: 'KELURAHAN LAMA', width: '10%'},
				NOP_BARU 		: {title: 'NOP BARU', width: '10%'},
				KECAMATAN_BARU 	: {title: 'KECAMATAN BARU', width: '10%'},
				KELURAHAN_BARU 	: {title: 'KELURAHAN BARU', width: '10%'},
				TGL_UPDATE 		: {title: 'TANGGAL PERUBAHAN', width: '5%'},
				//CPC_TKC_URUTAN :    {title: 'NO URUT', width: '15%'}
            }
        });

        $('#TableContainer').jtable('load');
    });
	
	function showForm(id, name, tag){
		if(id){
			window.location = 'main.php?param=<?php echo $param?>&action=form&id='+id+'&name='+name+'&tag='+tag;
		}
	}
	
	function deleteData(id){
		var r=confirm("Anda yakin akan menghapus Kecamatan "+id+"?");
			if (r==true){
				$.ajax({
					type: "POST",   
					url: "function/PBB/pemekaran/f_kecamatan.php", 
					data:{
						ID: "<?php echo $sessID?>", 
						action: "dELETE",
						kdkec: id
					},
					success : function(json){
						window.location = 'main.php?param=<?php echo $param?>';
					},
					error: function (error){}
				});
			}
	}

	function excelModelE2(){
            window.open("function/PBB/pemekaran/svc-toexcel.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'3','uid':'$uid','srch':'$srch'}"); ?>");
}


function loadData(){
		var nop = $('#nop').val();
		window.location = 'main.php?param=<?php echo $param?>&nop='+nop;
	}


</script>
<div class="filtering" style="width:1200px; margin:10px 10px">
    <!-- <form>
        Pencarian : <input type="text" name="nop" id="nop" placeholder="NOP"/>
        <input type="button" id="Load" onclick="loadData()" value="Cari"> -->
		<input type="button" name="buttonToExcel" id="buttonToExcel" value="Export to xls" onClick="excelModelE2()"/>        
    <!-- </form> -->
</div>

<div id="TableContainer" style="width:1300px; margin:10px 10px; display:block;" ></div>