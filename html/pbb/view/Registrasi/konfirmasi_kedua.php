  <?php
	$sRootPath = str_replace('\\', '/', str_replace('view'.DIRECTORY_SEPARATOR.'Registrasi', '', dirname(__FILE__))).'/';
	require_once($sRootPath."inc/registrasi/inc-registrasi.php");
    //require_once "Mail.php";
        require_once "SMTPClient.php";
    $from = TAX_MAIL_NOTIFICATION_FROM;
    $fromName = TAX_MAIL_NOTIFICATION_FROM_NAME;
    $to = $email1;
    $subject = "[BPHTB] Verifikasi Pendaftaran Notaris";
    $body = "Terima kasih\n\n User Account Anda telah diverifikasi dan disetujui oleh Admin.\n\n Username : ".$nameUser."\n Password : ".$pwdUser."\n\n Silahkan login ke : ".$arConfig["urlLog"];
    $host = TAX_MAIL_NOTIFICATION_HOST;
    $port = TAX_MAIL_NOTIFICATION_PORT;
    $username = TAX_MAIL_NOTIFICATION_USER;
    $password = TAX_MAIL_NOTIFICATION_PASSWD;
//    $headers = array ('From' => $from,
//    'To' => $to,
//    'Subject' => $subject);
//
//    $smtp = Mail::factory('smtp',
//    array ('host' => $host,
//    'port' => $port,
//    'auth' => TAX_MAIL_NOTIFICATION_USEAUTH,
//    'username' => $username,
//    'password' => $password));
//
//    $mail = $smtp->send($to, $headers, $body);
//
//    if (PEAR::isError($mail)) {
//
//    echo("<p>" . $mail->getMessage() . "</p>");
//
//    } else {
//
//    echo("<p>Email Konfiramsi telah dikirimkan..</p>");
//
//    }
    $SMTPMail = new SMTPClient ($host, $port, $username, $password, $from, $to, $subject, $body,$fromName);
    $SMTPChat = $SMTPMail->SendMail();

    ?>