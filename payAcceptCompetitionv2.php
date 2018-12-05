<?php
include_once "GunnarCore.php";
session_start();


$orderid = $_REQUEST['orderid'];

$orderidarr = split("_", $orderid);
$compId = $orderidarr[0];
$shotId = $orderidarr[1];
$date = $orderidarr[2];




header("Content-Type: text/html; charset=UTF-8");
?>

<html>
<head>
<title>AcceptCompetition</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css" media="screen" />
<link rel="stylesheet" href="css/gunnar.css" type="text/css" media="screen" />

</head>
<body onLoad="javascript:setFocus();">

<?
$entryComp = new Entry();
$obetald = $entryComp->loadUnPayedStarts($shotId, $compId);
$antalstart = 0;
foreach ($obetald as $rowobetald) {
	$antalstart ++;
	//$rowobetald->CompetitionName
	//$rowobetald->ClassName
	//$rowobetald->PatrolStartTime
	//$rowobetald->PatrolNo
	$patrulid = $rowobetald->PatrolId;
	
	$patrul = new Patrol();
	$patrul->load($patrulid);

	$compday = new CompetitionDay();
	$compday->load($patrul->competitionDayId);
	
	$comp = new Competition();
	$comp->load($compday->competitionId);
	
	$gunClassificationId = $rowobetald->GunClassificationId;
	
	$shot = new Shot();
	$lst = $shot->getGunClassList($comp->id);
	$gun = "";		
	foreach ($lst as $key => $value)
	{
		if ($key == $gunClassificationId) {
			$gun = $value;
		}
	}
	
	$shotClassId = $rowobetald->ShotClassId;
	$klass = "";
	$lst = $shot->getClassList($gunClassificationId, $comp->masterskap);
	foreach ($lst as $key => $value)
	{
		if ($key == $shotClassId) {
			$klass = $value;
		}
	}
	
?>

<?if($antalstart == 1) {//Bara första iteration?>
Vi har bokat följande start(er) åt dig. Du kommer att skickas vidare till betalningssidan.
Om du inte betalar online garanteras du inte din plats.
<br/>
Tävling : <strong><?=$comp->name?></strong>
<br/>
<?}?>
<br/>
Vapen : <strong><?=$gun?></strong>
<br/>
Klass : <strong><?=$klass?></strong>
<br/>
Patrul : <strong><?=$patrul->sortOrder?></strong>
<br/>
Start  : <strong><?=$patrul->startTime?></strong>
<br/>
<?}?>
<br/>
Pris: <strong><?=100*$antalstart?> SEK</strong>
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