<?php
	define("TAX_MAIL_BASE_URL", "http://36.92.151.83:7083/");
	define("TAX_MAIL_NOTIFICATION_HOST","ssl://smtp.gmail.com");
	define("TAX_MAIL_NOTIFICATION_PORT","465");
	define("TAX_MAIL_NOTIFICATION_USEAUTH",true);
	define("TAX_MAIL_NOTIFICATION_FROM","lampungselatan.bpprd@gmail.com");
	define("TAX_MAIL_NOTIFICATION_USER","lampungselatan.bpprd@gmail.com");
	define("TAX_MAIL_NOTIFICATION_PASSWD","bGFtc2VsMjAyMQ==");
	define("TAX_MAIL_NOTIFICATION_NOTARIS_SUBJECT","[BPHTB] Validasi Pendaftaran Notaris");
	define("TAX_MAIL_NOTIFICATION_NOTARIS_CONTENT","Anda telah berhasil mendaftar. \n Silahkan aktifkan account Anda, dengan mengklik link berikut : \n ". TAX_MAIL_BASE_URL ."registrasi/aktivasi_account.php?id=<id>\n\nTerima kasih");
	define("TAX_BPHTB_REGISTER_NOTARIS_ACCEPT_FUNCTION","fAdmNotAct");
	define("TAX_BPHTB_REGISTER_NOTARIS_REJECT_FUNCTION","fAdmNotAct");
	define("TAX_MAIL_NOTIFICATION_FROM_NAME", "BPPRD Lampung Selatan");
?>
