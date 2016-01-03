<!--
	 vstupar.php
	 Version: 0.9
   
   Copyright 2015 Petr Studený <dosmanak@centrum.cz>
   
   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.
   
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
   
   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
   MA 02110-1301, USA.
   
   
-->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>Vstupařův průvodce večerem</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<meta content='width=device-width, initial-scale=0.7, maximum-scale=0.9, user-scalable=0' name='viewport' />
	<link rel="stylesheet" href="style.css" type="text/css">
	<script src="concertsApp.js" type="text/javascript"></script>
</head>

<body onload="Evening('concerts')">
<div id="concerts">
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
//dos	require 'class.phpmailer.php';
	$datajson = ($_POST["data"]);
	$data = json_decode($_POST["data"],true);
	//var_dump($data);
	$body = '<pre>';
	setlocale(LC_TIME, "cs_CZ.UTF-8");
	$body .= "Vstupné do JazzDocku za ".strftime("%A %d. %m. %G %R\n");
	$total = 0;
	foreach ($data as $key => $value) {
		$body .= urldecode($value['title']);
		$body .= ":\n";
		$body .= "\tCena:\tPočet:\tCelkem\n";
		$sum = 0;
		for ($i = 0; ($i < count($value['prices'])); $i++)
		{
			if ($value['counts'][$i] != 0)
			{
				$body .= "\t".$value['prices'][$i];
				$body .= "\t".$value['counts'][$i];
				$body .= "\t".$value['counts'][$i]*$value['prices'][$i];
				$sum += $value['counts'][$i]*$value['prices'][$i];
				$body .= "\n";
			}
		}
		$body .= "\t\tSoučet:\t$sum\n";
		$total += $sum;
	}
	$body .= "Součet za celý večer:\t$total";
	$body .= '</pre>';
	echo $body;
	$to = ($_POST["mailto"]);
	if (filter_var($to, FILTER_VALIDATE_EMAIL)) 
	{
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$headers .= 'From: =?utf-8?Q?Vstupa=C5=99=20JZD?= <vstupar@jazztecetera.cz>'. "\r\n";
		if ( mail($to, 'JZD Vstupné '.strftime('%d.%m.%G'), $body, $headers) ) 
		{
				echo 'E-mail byl odeslán.';
		} else {
  	  echo 'E-mail nebyl odeslán';
  	}
	} else {
  	  echo 'E-mailová adresa není ve správném formátu.';
  }
	//$to = "Petr Studeny <dosmanak@centrum.cz>";
//dos 	$mail = new PHPMailer;
//dos 	$mail->isMail();
//dos 	$mail->setFrom('vstupar@jazztecetera.cz', 'Vstupar JZD');
//dos 	$mail->addAddress($to);
//dos 	$mail->Subject = 'JZD Vstupné '.strftime('%d.%m.%G');
//dos 	$mail->msgHTML($body);
//dos 	if (!$mail->send()) {
//dos 		    echo "Chyba při odesílání: " . $mail->ErrorInfo;
//dos 	} else {
//dos 		    echo "E-mail odeslán na ".$to."!\n".$mail->ErrorInfo;
//dos 	}
}
else
{
}
?>
</div>
<div class="footer">
<div id="eveningTotal"></div>
<div id="sendMail"></div>
<div id="resetCookies"></div>
</div>
</body>

</html>
