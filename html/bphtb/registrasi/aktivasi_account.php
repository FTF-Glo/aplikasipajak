<?php
				require_once("library.php");
				require_once("koneksi.php");
				$konek=new konek();
				if (! $koneksi) {
									echo "Mengalami kegagalan koneksi";
									mysqli_error($koneksi);
				}

				
				$id=$_GET['id'];
				$sqlUpdate = "UPDATE tbl_reg_user_notaris SET status='1',keterangan='aktif' WHERE uuid='$id'";
				$bOK=mysqli_query($koneksi, $sqlUpdate);
				if($bOK){
							# Jika sukses
							echo "<div align='center' style='margin-top:70px'>Account Sudah diaktifkan,<br>Silahkan Tunggu Email Verifikasi dari Sistem.<br>Terima Kasih</img></div>";
				}
				else{
							# Jika gagal
							echo "<script language='javascript'>alert('Validasi Account Gagal.')</script>";
					}
	
?>