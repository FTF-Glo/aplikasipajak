  <?php
	require_once($sRootPath."inc/registrasi/inc-registrasi.php");
    require_once "Mail.php";
	$from = TAX_MAIL_NOTIFICATION_FROM;
    $to = $email;
    $subject = "[BPHTB] Verifikasi Pendaftaran Notaris";
    $body = "Selamat!\n\n User Account Anda telah diverifikasi oleh Sistem.\n\n Username : ".$nameUser."\n Password : ".$pwdUser."\n\n Silahkan login ke :http://192.168.30.2:9800/payment/pc/svr/central/main.php \n\n Terima kasih";
    $host = TAX_MAIL_NOTIFICATION_HOST;
    $port = TAX_MAIL_NOTIFICATION_PORT;
    $username = TAX_MAIL_NOTIFICATION_USER;
    $password = TAX_MAIL_NOTIFICATION_PASSWD;
    $headers = array ('From' => $from,
    'To' => $to,
    'Subject' => $subject);

    $smtp = Mail::factory('smtp',
    array ('host' => $host,
    'port' => $port,
    'auth' => true,
    'username' => $username,
    'password' => $password));

    $mail = $smtp->send($to, $headers, $body);

    if (PEAR::isError($mail)) {

    echo("<p>" . $mail->getMessage() . "</p>");

    } else {

    echo("<p>Email Konfiramsi telah dikirimkan..</p>");

    }

    ?>