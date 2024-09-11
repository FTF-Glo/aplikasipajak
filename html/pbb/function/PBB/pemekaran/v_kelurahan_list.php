<script src="jtable/jquery-ui-1.10.4.min.js" type="text/javascript"></script>
<script src="jtable/jquery.jtable.js" type="text/javascript"></script>
<link href="jtable/themes/metro/green/jtable.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">
    $(document).ready(function () {
        $('#TableContainer').jtable({
            title: 'LIST KELURAHAN (KECAMATAN : <?php echo $nmKec?>)',
            paging: true, 
            pageSize: 15, 
            actions: {listAction: 'function/PBB/pemekaran/f_kelurahan.php?ID=<?php echo $sessID?>&action=0'},
            fields: {
				CPC_TKL_ID        : {title: 'KODE KELURAHAN', width: '30%', key: true},
				CPC_TKL_KELURAHAN : {title: 'NAMA KELURAHAN', width: '40%'},
				CPC_NM_SEKTOR     : {title: 'SEKTOR', width: '40%'},
				EDIT : {
				    title: '...',  textalign:'center',
					display: function (data) {
					    var id  = data.record.CPC_TKL_ID;
						var nm  = data.record.CPC_TKL_KELURAHAN;
						var tag = data.record.TAG;
						var sek = data.record.CPC_KD_SEKTOR;
						
						var ico =  "<a href='#'><img src='image/icon/list-items.gif' onclick='showForm(\""+id+"\",\""+nm+"\",\""+sek+"\",\""+tag+"\")'/></a>";
						
						if(tag < 1){
						    ico += "<a href='#'><img src='image/icon/delete.png' onclick='deleteData(\""+id+"\")'/></a>";
						}
						return ico;
				    }
				}
            }
        });
        $('#TableContainer').jtable('load');
		loadKecamatan();
    });
	
	function showForm(id, name, sektor, tag){
		if(id){
			window.location = 'main.php?param=<?php echo $param?>&action=form&id='+id+'&name='+name+'&tag='+tag+'&sektor='+sektor;
		}
	}
	
	function loadKecamatan(){
	    $('#kec').find('option').remove();
		if('<?php echo $kdKec?>'=='') $('#kec').append("<option value='0'>-- PILIH KECAMATAN --</option>");
		$.each(arrKec, function(key, value) {  
		     var selected = '';
			 if('<?php echo $kdKec?>'==value['kode'])selected='selected';
			 $('#kec') .append("<option value='"+value['kode']+"' "+selected+">"+value['nama']+"</option>");
		});
	}
	
	function loadDataList(){
		var kec = $.grep(arrKec, function(v){ return v.kode == $('#kec').val();});
		window.location = 'main.php?param=<?php echo $param?>&kdkec='+kec[0].kode+'&nmkec='+kec[0].nama;
	}
	
	function deleteData(id){
		var r=confirm("Anda yakin akan menghapus Kelurahan "+id+"?");
			if (r==true){
				$.ajax({
					type: "POST",   
					url: "function/PBB/pemekaran/f_kelurahan.php", 
					data:{
						ID: "<?php echo $sessID?>", 
						action: "dELETE",
						kdkel: id
					},
					success : function(json){
						window.location = 'main.php?param=<?php echo $param?>';
					},
					error: function (error){}
				});
			}
	}
</script>
<div class="col-md-12">
	<select id="kec" name="kec" onchange="loadDataList()"></select> <br> <br>
	<input type="button" value="Buat Kelurahan Baru" onclick="window.location = 'main.php?param=<?php echo $param?>&action=form'">
	<div id="TableContainer" style="width:98%; margin-top:5px"></div>
</div>