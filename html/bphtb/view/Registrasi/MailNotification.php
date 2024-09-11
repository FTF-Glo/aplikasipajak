<?php
// Pear Mail Library
set_include_path("/usr/share/pear/");
//require_once ("Mail.php");

class MailNotification
{
	function MailNotification ($Host, $Port, $User, $Pass, $From, $To, $Subject, $Body, $FromName='')
	{
		$this->HOST = $Host;
		$this->USER = $User;
		$this->PASS = base64_decode($Pass);
		$this->FROM = $From;
		$this->FROMNAME = $FromName;
		$this->TO = $To;
		$this->SUBJECT = $Subject;
		$this->BODY = $Body;
		if ($Port == "")
		{
			$this->PORT = 25;
		}
		else
		{
			$this->PORT = $Port;
		}
	}

	function SendMail ()
	{
		#echo "SEND MAIL START <BR>";
		#echo $this->HOST."<br>";
                #echo $this->USER."<br>";
                #echo $this->PASS."<br>";
                #echo $this->FROM."<br>";
                #echo $this->FROMNAME."<br>";
                #echo $this->TO."<br>";
                #echo $this->SUBJECT."<br>";
                #echo $this->BODY."<br>";
                #echo $this->PORT."<br>";
  

		$from = $this->FROM;
		$to = $this->TO;
		$subject = $this->SUBJECT;
		$body = $this->BODY;

		$headers = array(
			'From' => $this->FROM,
			'To' => $this->TO,
			'Subject' => $this->SUBJECT
		);

		$smtp = Mail::factory('smtp', array(
				'host' => $this->HOST,
				'port' => $this->PORT,
				'auth' => true,
				'username' => $this->USER,
				'password' => $this->PASS
			));

		$mail = $smtp->send($to, $headers, $body);

		if (PEAR::isError($mail)) {
			echo('<p>' . $mail->getMessage() . '</p>');
		} else {
			echo('<center><p>Mail berhasil dikirim!</p></center>');
		}
	}
}
?>

