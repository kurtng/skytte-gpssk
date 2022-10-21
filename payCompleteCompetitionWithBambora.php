<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
include_once "GunnarCore.php";
require_once('payBamboraSecrets.php');
session_start();

$orderidbambora = $_GET['orderid'];

$orderidarr = explode("z", $orderidbambora);
$compId = $orderidarr[0];
$shotId = $orderidarr[1];
$date = date ( 'c', intval($orderidarr[2]));
$orderid = $compId . "_" . $shotId . "_" . $date;

//https://www.okrets.se/skytte/payCompleteCompetitionWithBambora.php?
//ui=fullscreen&language=en-US&txnid=273749032051562496&
//orderid=107z61z1662648213&reference=444775633217&amount=20000&currency=SEK&date=20220908
//&time=1443&feeid=778628&txnfee=0&paymenttype=5&cardno=415421XXXXXX0001&eci=5
//&issuercountry=DNK&hash=c65f72d6f203c8a2652351f54b64b425

$getconcat = "";
foreach ($_GET as $key => $value) { 
	if($key != "hash") {
		$getconcat = $getconcat . $value;
	}
}
$hash = $_GET['hash'];
$md5 = md5($getconcat . getMD5Key());

$transact = $_GET['txnid'];

$statuscode = 2; //$_REQUEST['statuscode'];
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
	} else if($transactCurrent == NULL) {
		$ok = $entry->setEntryStatus('P', $transact, $statuscode);

		//ui=fullscreen&language=en-US&txnid=273749032051562496&
//orderid=107z61z1662648213&reference=444775633217&amount=20000&currency=SEK&date=20220908
//&time=1443&feeid=778628&txnfee=0&paymenttype=5&cardno=415421XXXXXX0001&eci=5
//&issuercountry=DNK&hash=c65f72d6f203c8a2652351f54b64b425
		$approvalcode = "paymenttype:" . $_GET['paymenttype'] . " " . "cardno:" . $_GET['cardno'] . " " . "eci:" . $_GET['eci']. " " . "txnid:" . $_GET['txnid'];
		$paytype = "reference:" . $_GET['reference'] . " " . "date:" . $_GET['date'] . " " . "time:" . $_GET['time']. " " . "feeid:" . $_GET['feeid'];
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
			$msg .= "Kunde ej utföras. " . $ok . " <br/> " . $msg;
		}
	} else {
		$msg .= "Finns redan transaktion med annan id : " . $transactCurrent . " ny id " . $transact . "<br/>";
	}
}

$shot = new Shot();
$shot->load($shotId);

//Det här händer 2 gånger en från Callback en från Accept från betalningsleverantör.
//Man får 2 mejl i så fall.
//$ok = $shot->sendMailOmBetalning($shot->email, $comp->name, $gun, $klass, $sortorder, $starttime, $amount.  " SEK", $orderid, $transact, $msg);

header("Content-Type: text/html; charset=UTF-8");
?>

<html>

<head>
<title>Betalning lyckades</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css" media="screen" />
<link rel="stylesheet" href="css/gunnar.css" type="text/css" media="screen" />

</head>
<body onLoad="javascript:setFocus();">

<?if($md5 != $hash) {?>
	Ogiltigt anrop. Betalning misslyckades
<?} else if($statuscode == "2" || $statuscode == "5" || $statuscode == "12") {?>
	Du har betalat <?=$amount/100?> SEK.<br/>Notera din order id: <strong><?=$orderid?></strong> och transactions id: <strong><?=$transact?></strong>.
	<div class="error"><?=$msg?></div>
<?} else { ?>
	Ett problem har uppstått.<br/> Notera din order id: <strong><?=$orderid?></strong> och transactions id: <strong><?=$transact?></strong>.
	med status kod: <?=$statuscode?>
<? }?>
<br/>
Tävling : <strong><?=$comp->name?></strong>
<br/>
<br/>
<?

	$gun = explode("<br/>", $gun);
	$klass = explode("<br/>", $klass);
	$sortorder = explode("<br/>", $sortorder);
	$starttime = explode("<br/>", $starttime);
			
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
Pris  : <strong><?=$amount/100?> SEK</strong>
<br/>
Du har följande orderid: <strong><?=$orderid?></strong>	
<br/>
Transactions id: <strong><?=$transact?></strong><br/>

Vi har skickat mejl till din epost adress. Det kan komma flera mejl eller ingen alls om betalningen.
<br/><a href="/skytte">Gå till hemsidan</a>
</body>
</html>
