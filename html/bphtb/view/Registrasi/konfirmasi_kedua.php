  <?php
	$sRootPath = str_replace('\\', '/', str_replace('view'.DIRECTORY_SEPARATOR.'Registrasi', '', dirname(__FILE__))).'/';
	require_once($sRootPath."inc/registrasi/inc-registrasi.php");
    require_once "MailNotification.php";

    $from = TAX_MAIL_NOTIFICATION_FROM;
    $to = $email1;
    $subject = "[BPHTB] Verifikasi Pendaftaran Notaris";
    $body = "Terima kasih\n\n User Account Anda telah diverifikasi dan disetujui oleh Admin.\n\n Username : ".$nameUser."\n Password : ".$pwdUser."\n\n Silahkan login ke : ".$arConfig["urlLog"];
    $host = TAX_MAIL_NOTIFICATION_HOST;
    $port = TAX_MAIL_NOTIFICATION_PORT;
    $username = TAX_MAIL_NOTIFICATION_USER;
    $password = TAX_MAIL_NOTIFICATION_PASSWD;
    // $headers = array ('From' => $from,
    // 'To' => $to,
    // 'Subject' => $subject);

    // $smtp = Mail::factory('smtp',
    // array ('host' => $host,
    // 'port' => $port,
    // 'auth' => TAX_MAIL_NOTIFICATION_USEAUTH,
    // 'username' => $username,
    // 'password' => $password));

    // $mail = $smtp->send($to, $headers, $body);

    // if (PEAR::isError($mail)) {

    // echo("<p>" . $mail->getMessage() . "</p>");

    // } else {

    // echo("<p>Email Konfiramsi telah dikirimkan..</p>");

    // }
	
	$MailNotif = new MailNotification ($host, $port, $username, $password, $from, $to, $subject, $body);
	$NotifRes = $MailNotif->SendMail();


    ?>