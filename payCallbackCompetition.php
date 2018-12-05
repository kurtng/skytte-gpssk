<?php
include_once "GunnarCore.php";
session_start();


$orderid = $_REQUEST['orderid'];

$orderidarr = split("_", $orderid);
$patrulid = $orderidarr[0];
$gunClassificationId = $orderidarr[1];
$shotClassId = $orderidarr[2];
$shotId = $orderidarr[3];
$entryId = $orderidarr[4];

$transact = $_REQUEST['transact'];
$statuscode = $_REQUEST['statuscode'];

$entry = new Entry();
$entry->load($entryId);
$entry->id = $entryId;
$ok = $entry->setEntryStatus('P', $transact, $statuscode);
if ($ok)
{
	$msg = "Betalning gjord.";
}
else {
	$msg = "Kunde ej utföras. " . $ok . " " . $msg;
}


$patrul = new Patrol();
$patrul->load($patrulid);

$compday = new CompetitionDay();
$compday->load($patrul->competitionDayId);

$comp = new Competition();
$comp->load($compday->competitionId);

$shot = new Shot();
$lst = $shot->getGunClassList($comp->id);
$gun = "";		
foreach ($lst as $key => $value)
{
	if ($key == $gunClassificationId) {
		$gun = $value;
	}
}

$klass = "";
$lst = $shot->getClassList($gunClassificationId, $comp->masterskap);
foreach ($lst as $key => $value)
{
	if ($key == $shotClassId) {
		$klass = $value;
	}
}


$shot = new Shot();
$shot->load($shotId);
$ok = $shot->sendMailOmBetalning($shot->email, $comp->name, $gun, $klass, $patrul->sortOrder, $patrul->startTime, "100 SEK", $orderid, $transact);


header("Content-Type: text/html; charset=UTF-8");
?>

<html>

<head>
<title>CompleteCompetition</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css" media="screen" />
<link rel="stylesheet" href="css/gunnar.css" type="text/css" media="screen" />

</head>
<body onLoad="javascript:setFocus();">
<?if($statuscode == "2" || $statuscode == "5" || $statuscode == "12") {?>
	Du har betalat för en start. Notera din order id: <strong><?=$orderid?></strong> och transactions id: <strong><?=$transact?></strong> i fall ett problem uppstår.
	<div class="error"><?=$msg?></div>
<?} else { ?>
	Ett problem har uppstått. Notera din order id: <strong><?=$orderid?></strong> och transactions id: <strong><?=$transact?></strong>.
	med status kod: <?=$statuscode?>
<? }?>
<br/>
Tävling : <strong><?=$comp->name?></strong>
<br/>
Vapen : <strong><?=$gun?></strong>
<br/>
Klass : <strong><?=$klass?></strong>
<br/>
Patrul : <strong><?=$patrul->sortOrder?></strong>
<br/>
Start  : <strong><?=$patrul->startTime?></strong>
<br/>
Pris  : <strong>100 SEK</strong>
<br/>
Du har följande orderid: <strong><?=$orderid?></strong>	
<br/>
Transactions id: <strong><?=$transact?></strong>

</body>
</html>
