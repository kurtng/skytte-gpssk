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

header("Content-Type: text/html; charset=UTF-8");
?>

<html>
<head>
<title>AcceptCompetition</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css" media="screen" />
<link rel="stylesheet" href="css/gunnar.css" type="text/css" media="screen" />

</head>
<body onLoad="javascript:setFocus();">

Vi har bokat följande start åt dig. Du kommer att skickas vidare till betalningssidan.
Om du inte betalar online garanteras du inte din plats.
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
<form method="post" name="DIBSForm" action="payCompetition.php">
	<?while(list($key,$val) = each($_REQUEST)) {?>
	<input type="hidden" name="<?=$key?>" value="<?=$val?>">
	<? } ?>
  <input type="submit" value="Jag accepterar"/>
</form>

<br/>
Kontakt info: Alliansens sekretariatet.
<br/>
Tel: 
<br/>
E-post:
<br/>
</html>