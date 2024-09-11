    <?php
    require_once "Mail.php";
	
    $from = "budi@mba.web.id";
	//$from = "taxsystem@vsi.co.id";
    //$to = "budi@mba.web.id";
	$to = "budi.yuliaziz@vsi.co.id";
    $to = "ardi@vsi.co.id";
	$subject = "Tes";
    $body = "Hi,\n\n coba cek source code saya di testemail2.php";
    $host = "10.24.110.62";
    $port = "25";
    //$username = "taxsystem@mba.web.id";
    //$password = "T@xSystemP@ssw0rd";
    //$username = "taxsystem@vsi.co.id";
    //$password = "hallotaxsystem";
	
	$username = "budi@mba.web.id";
	$password = "rahasia123";
    $headers = array ('From' => $from,
    'To' => $to,
    'Subject' => $subject);

    $smtp = Mail::factory('smtp',
    array ('host' => $host,
    'port' => $port,
    'auth' => false,
    'username' => $username,
    'password' => $password));

    $mail = $smtp->send($to, $headers, $body);
echo "kirim email";
    if (PEAR::isError($mail)) {

    echo("<p>" . $mail->getMessage() . "</p>");

    } else {

    echo("<p>Pesan Terkirim :) </p>");

    }

    ?>