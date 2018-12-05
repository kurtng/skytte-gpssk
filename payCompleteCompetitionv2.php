<?php
include_once "GunnarCore.php";
session_start();

$orderid = $_REQUEST['orderid'];

$orderidarr = split("_", $orderid);
$compId = $orderidarr[0];
$shotId = $orderidarr[1];
$date = $orderidarr[2];

$transact = $_REQUEST['transact'];
$statuscode = $_REQUEST['statuscode'];
$amount = $_REQUEST['amount'];

$entryComp = new Entry();
$starts = $entryComp->loadStartsForCompetition($shotId, $compId);

$antalbetalda = 0;
$amount = $amount/100;
$antalbokade = $amount/100;
foreach ($starts as $start) {
	$patrulid = $start->PatrolId;
	
	$patrul = new Patrol();
	$patrul->load($patrulid);

	$compday = new CompetitionDay();
	$compday->load($patrul->competitionDayId);
	
	$comp = new Competition();
	$comp->load($compday->competitionId);
	
	$gunClassificationId = $start->GunClassificationId;
	$shotClassId = $start->ShotClassId;
	
	$entryId = $start->EntryId;
	
	$entry = new Entry();
	$entry->load($entryId);
	$entry->id = $entryId;
	
	$transactCurrent = $start->TransactionId;
	
	if($transactCurrent == $transact) {
	
		//$ok = $entry->setEntryStatus('P', $transact, $statuscode);
			
			$shot = new Shot();
			$lst = $shot->getGunClassList($comp->id);
					
			foreach ($lst as $key => $value)
			{
				if ($key == $gunClassificationId) {
					$gun .= $value . "<br/>";
				}
			}
			
			$lst = $shot->getClassList($gunClassificationId, $comp->masterskap);
			foreach ($lst as $key => $value)
			{
				if ($key == $shotClassId) {
					$klass .= $value . "<br/>";
				}
			}
			
			$sortorder .= $patrul->sortOrder . "<br/>";
			
			$starttime .= $patrul->startTime . "<br/>";
						
		
	}
	
	
}



$shot = new Shot();
$shot->load($shotId);
//$ok = $shot->sendMailOmBetalning($shot->email, $comp->name, $gun, $klass, $sortorder, $starttime, $amount.  " SEK", $orderid, $transact);


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
	Du har betalat för <?=$amount?> SEK. Notera din order id: <strong><?=$orderid?></strong> och transactions id: <strong><?=$transact?></strong> i fall ett problem uppstår.
	<div class="error"><?=$msg?></div>
<?} else { ?>
	Ett problem har uppstått. Notera din order id: <strong><?=$orderid?></strong> och transactions id: <strong><?=$transact?></strong>.
	med status kod: <?=$statuscode?>
<? }?>
<br/>
Tävling : <strong><?=$comp->name?></strong>
<br/>
<br/>
<?
	$gun = split("<br/>", $gun);
	$klass = split("<br/>", $klass);
	$sortorder = split("<br/>", $sortorder);
	$starttime = split("<br/>", $starttime);
			
	$antalstart = 0;
	for($i=0 ; $i<sizeof($gun) ; $i++ ) {
		if(trim($gun[$i]) != "") {
			$antalstart++;
			?>Vapen: <?=$gun[$i]?> <br/><?
			?>Klass: <?=$klass[$i]?> <br/><?
			?>Patrul: <?=$sortorder[$i]?> <br/><?
			?>Start: <?=$starttime[$i]?> <br/><br/><?
		}
	}
?>

<br/>
Pris  : <strong><?=$amount?> SEK</strong>
<br/>
Du har följande orderid: <strong><?=$orderid?></strong>	
<br/>
Transactions id: <strong><?=$transact?></strong>

</body>
</html>
