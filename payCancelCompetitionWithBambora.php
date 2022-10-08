<?php
include_once "GunnarCore.php";
session_start();

/* $orderid = $_REQUEST['orderid'];

$orderidarr = split("z", $orderid);
$patrulid = $orderidarr[0];
$gunClassificationId = $orderidarr[1];
$shotClassId = $orderidarr[2];
$shotId = $orderidarr[3];
$entryId = $orderidarr[4];

$entry = new Entry();
$entry->id = $entryId;
$msg = $entry->delete();
*/
header("Content-Type: text/html; charset=UTF-8");
?>

<html>

<head>
<title>Betalningen avbröts</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css" media="screen" />
<link rel="stylesheet" href="css/gunnar.css" type="text/css" media="screen" />

</head>
<body onLoad="javascript:setFocus();">
<?//if($msg == "OK") {?>
	Betalningen misslyckades och du har inte genomfört betalningen <?=$entryId?> <?=$orderid?>
<?//} else { ?>
<? //}?>
<a href="/skytte">Gå tillbaka till hemsidan</a>
</body>
</html>
