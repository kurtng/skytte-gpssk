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
$gunCard = $_REQUEST['gunCard'];
$approvalcode = $_REQUEST['approvalcode'];
$paytype = $_REQUEST['paytype'];

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
		$antalbetalda ++;
	} else if($transactCurrent == NULL) {
	
		$ok = $entry->setEntryStatus('P', $transact, $statuscode);
		$entry->setDibsPayment($transact, $orderid, $gunCard, $amount, $statuscode, $approvalcode, $paytype);
		
		if ($ok)
		{
			$msg .= "Betalning gjord.";
			$antalbetalda ++;
			
			$shot = new Shot();
			$lst = $shot->getGunClassList($comp->id);
					
			foreach ($lst as $key => $value)
			{
				if ($key == $gunClassificationId) {
					$gun .= $value . '<br/>';
				}
			}
			
			$lst = $shot->getClassList($gunClassificationId, $comp->masterskap);
			foreach ($lst as $key => $value)
			{
				if ($key == $shotClassId) {
					$klass .= $value  . '<br/>';
				}
			}
			
			$sortorder .= $patrul->sortOrder . '<br/>';
			
			$starttime .= $patrul->startTime . '<br/>';
			
						
		}
		else {
			$msg .= "Kunde ej utföras. " . $ok . " " . $msg;
		}
	}
	
	
	
	if($antalbetalda == $antalbokade) { 
		break;
	}
	
	
}



$shot = new Shot();
$shot->load($shotId);
$ok = $shot->sendMailOmBetalning($shot->email, $comp->name, $gun, $klass, $sortorder, $starttime, $amount.  " SEK", $orderid, $transact);


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
Vapen : <strong><?=$gun?></strong>
<br/>
Klass : <strong><?=$klass?></strong>
<br/>
Patrul : <strong><?=$sortorder?></strong>
<br/>
Start  : <strong><?=$starttime?></strong>
<br/>
Pris  : <strong><?=$amount?> SEK</strong>
<br/>
Du har följande orderid: <strong><?=$orderid?></strong>	
<br/>
Transactions id: <strong><?=$transact?></strong>

</body>
</html>
