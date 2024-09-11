<script src="jtable/jquery-ui-1.10.4.min.js" type="text/javascript"></script>
<script src="jtable/jquery.jtable.js" type="text/javascript"></script>
<script type="text/javascript" src="jtable/localization/jquery.jtable.id.js"></script>
<link href="jtable/themes/metro/green/jtable.css" rel="stylesheet" type="text/css" />
<?php //echo "test"; exit; ?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#TableContainer').jtable({
            title: 'LIST KECAMATAN',
            paging: true, 
            pageSize: 15, 
            actions: {
				listAction: 'function/PBB/pemekaran/f_kecamatan.php?ID=<?php echo $sessID?>&action=0',
				editAction: 'function/PBB/pemekaran/f_kecamatan.php?ID=<?php echo $sessID?>&action=0'
			},
            fields: {
				CPC_TKC_ID :        {title: 'KODE KECAMATAN', width: '30%', key: true},
				CPC_TKC_KECAMATAN : {title: 'NAMA KECAMATAN', width: '50%'},
				EDIT : {
				    title: '...',  textalign:'center',
					display: function (data) {
					    var id = data.record.CPC_TKC_ID;
						var nm = data.record.CPC_TKC_KECAMATAN;
						var tag = data.record.TAG;
						var ico =  "<a href='#'><img src='image/icon/list-items.gif' onclick='showForm(\""+id+"\",\""+nm+"\",\""+tag+"\")'/></a>";
						
						if(tag < 1){
						    ico += "<a href='#'><img src='image/icon/delete.png' onclick='deleteData(\""+id+"\")'/></a>";
						}
						return ico;
				    }
				}
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
</script>

<input type="button" value="Buat Kecamatan Baru" onclick="window.location = 'main.php?param=<?php echo $param?>&action=form'">
<div id="TableContainer" style="width:98%; margin-top:5px;overflow:auto"></div>