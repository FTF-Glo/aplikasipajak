<?php
if (!empty($_SESSION['_success'])){
	echo "
	<script>
		$(function(){
			var text = '{$_SESSION['_success']}';
			var notice = new PNotify({
				title :'Berhasil', 
				text, 
				type : 'success',
				nonblock: {
					nonblock: true
				}
			});
			notice.get().click(function() {
				notice.remove();
			});
		});
	</script>";
}

if (!empty($_SESSION['_error'])){
	echo "
	<script>
		$(function(){
			var text = '{$_SESSION['_error']}';
			var notice = new PNotify({
				title :'Gagal', 
				text, 
				type : 'warning',
				nonblock: {
					nonblock: true
				}
			});
			notice.get().click(function() {
				notice.remove();
			});
		});
	</script>";
}
	
$_SESSION['_success'] = '';
$_SESSION['_error'] = '';
?>
