<script src="jtable/jquery.min.js" type="text/javascript"></script>
<script src="jtable/jquery-ui-1.10.4.min.js" type="text/javascript"></script>
<script src="jtable/jquery.jtable.js" type="text/javascript"></script>
<link href="jtable/themes/metro/blue/jtable.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">
    $(document).ready(function () {
        $('#StudentTableContainer').jtable({
            title: 'PEMEKARAN WILAYAH',
            paging: true, 
            pageSize: 15, 
            actions: {listAction: 'function/PBB/pemekaran/show_table_change.php?idexec=<?php echo $idexec?>'},
            fields: {
                NOP_LAMA: {title: 'NOP_LAMA', width: '200'},
                NOP_BARU: {title: 'NOP_BARU', width: '200'}
            }
        });
        $('#StudentTableContainer').jtable('load');
    });
	
	
	
	
	function sendProcessOK(){
		var r=confirm("Anda yakin akan melanjutkan proses?");
		if (r==true){
			$("#load-mask").css("display","block");
                        $("#load-content").fadeIn();
                        Ext.Ajax.request({
				url    : 'function/PBB/pemekaran/pemekaran.php?idexec=<?php echo $idexec.$paramDB.$paramUname; ?>',
				success: function(res){
					var json = Ext.decode(res.responseText);
                                        if (json.r == true) {
                                            alert('Proses Pemekaran Wilayah Berhasil');
                                            window.location = 'main.php?param=<?php echo $param ?>';
                                        } else {
                                                alert(json.m);
                                        }
                                        $("#load-content").css("display","none");
                                        $("#load-mask").css("display","none");
				},
				failure: function(){
                                    alert('Proses Pemekaran Wilayah Gagal. Eror Koneksi !!');
                                    $("#load-content").css("display","none");
                                    $("#load-mask").css("display","none");
                                }
			});
		}
	}
</script>
<br>
<input type='button' value='PROSES PERUBAHAN NOP' onclick='sendProcessOK()'> 
<input type='button' value='BATAL' onclick="window.location = 'main.php?param=<?php echo $param?>'">
<div id="StudentTableContainer" style="width:600px; margin-top:5px"></div>