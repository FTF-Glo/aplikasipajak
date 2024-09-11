<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'Registrasi', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/registrasi/inc-registrasi.php");
$arConfig = $User->GetAreaConfig($area);
$UrlLog = $arConfig["urlLog"];
require_once "Mail.php";

$from = TAX_MAIL_NOTIFICATION_FROM;
$to = $email;
$subject = "[BPHTB] Notifikasi Pendaftaran";
$body = "Terima kasih\n\n User Account Anda telah diverifikasi dan disetujui oleh Admin.\n\n Username : " . $userId . "\n Password : " . $pwd . "\n\n Silahkan login ke : " . $UrlLog;
$host = TAX_MAIL_NOTIFICATION_HOST;
$port = TAX_MAIL_NOTIFICATION_PORT;
$username = TAX_MAIL_NOTIFICATION_USER;
$password = TAX_MAIL_NOTIFICATION_PASSWD;
$headers = array('From' => $from,
    'To' => $to,
    'Subject' => $subject);

$smtp = Mail::factory('smtp', array('host' => $host,
            'port' => $port,
            'auth' => TAX_MAIL_NOTIFICATION_USEAUTH,
            'username' => $username,
            'password' => $password));

$mail = $smtp->send($to, $headers, $body);

if (PEAR::isError($mail)) {

    echo("<p>" . $mail->getMessage() . "</p>");
} else {

    echo("<p>Email Notifikasi telah dikirimkan..</p>");
}
?>