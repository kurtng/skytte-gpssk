<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
include_once "GunnarCore.php";
require_once('payBamboraSecrets.php');
session_start();


$orderid = $_REQUEST['orderid'];

$orderidarr = explode("_", $orderid);
$compId = $orderidarr[0];
$shotId = $orderidarr[1];
$date = $orderidarr[2];




header("Content-Type: text/html; charset=UTF-8");
?>

<html>
<head>
<title>Betala för dina starter</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css" media="screen" />
<link rel="stylesheet" href="css/gunnar.css" type="text/css" media="screen" />

</head>
<body>

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
Pris: <strong><?=100*$antalstart?> SEK</strong> (0% moms)
<br/>
Du har följande orderid: <strong><?=$orderid?></strong>
<br/>
<?

	$bamboraorderid = $compId . "z" . $shotId . "z" . strtotime($date);

	$apiKey=getAuthHeader();

	$checkoutUrl = "https://api.v1.checkout.bambora.com/sessions";

  	$request = array();
  	$request["order"] = array();
  	$request["order"]["id"] = $bamboraorderid;
  	$request["order"]["amount"] = strval(10000*$antalstart);
  	$request["order"]["currency"] = "SEK";

  	$request["url"] = array();
  	$request["url"]["accept"] = "https://www.okrets.se/skytte/payCompleteCompetitionWithBambora.php";
  	$request["url"]["cancel"] = "https://www.okrets.se/skytte/payCancelCompetitionWithBambora.php";
	$request["url"]["immediateredirecttoaccept"] = 1;
  	$request["url"]["callbacks"] = array();
  	$request["url"]["callbacks"][] = array("url" => "https://www.okrets.se/skytte/payCallbackCompetitionWithBambora.php");

	$request["paymentwindow"] = array();
	$request["paymentwindow"]["language"] = "sv-SE";

	$request["paymentwindow"]["paymentmethods"] = array();
	$request["paymentwindow"]["paymentmethods"]["id"] = "swish";
	$request["paymentwindow"]["paymentmethods"]["action"] = "include";

  	$requestJson = json_encode($request);

  	$contentLength = isset($requestJson) ? strlen($requestJson) : 0;

  	$headers = array(
    	'Content-Type: application/json',
    	'Content-Length: ' . $contentLength,
    	'Accept: application/json',
    	'Authorization: Basic ' . $apiKey
  	);

  	$curl = curl_init();

  	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
  	curl_setopt($curl, CURLOPT_POSTFIELDS, $requestJson);
  	curl_setopt($curl, CURLOPT_URL, $checkoutUrl);
  	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  	curl_setopt($curl, CURLOPT_FAILONERROR, false);
  	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

  	$rawResponse = curl_exec($curl);
  	$response = json_decode($rawResponse);

?>
<?php if($response->meta->result) { ?>
      <script src="https://static.bambora.com/checkout-sdk-web/latest/checkout-sdk-web.min.js"></script>
      <script>
      </script>
    <?php } else { ?>
      <p>Error: <?php echo $response->meta->message->enduser; ?></p>
	  Bambura orderid: <strong><?=$bamboraorderid?></strong>

<?php } ?>

<?php if($response->meta->result) { ?>
<input type="button" value="Jag accepterar och vill betala" onclick="new Bambora.RedirectCheckout('<?=$response->token?>');"/>
<a href="/skytte">Avbryt betalning och gå till hemsidan</a>
<?php } else { ?>
      <p>Error: <?php echo $response->meta->message->merchant; ?></p>

	  <p>Error: <?php echo $rawResponse; ?></p>
<?php } ?>

<form method="" name="DIBSForm" action="payCompetition.php">
	<?while(list($key,$val) = each($_REQUEST)) {?>
	<input type="hidden" name="<?=$key?>" value="<?=$val?>">
	<? } ?>
	<a href="villkor.html" target="_blank">Villkor för online betalning</a>
</form>



<br/>
<div>Du kommer att betala online  med VISA eller MasterCard <img alt="visa" src="RTEmagicC_RTEmagicC_master_mellem.gif.gif"> <img alt="mastercard" src="RTEmagicC_visa_mellem.gif.gif"> genom att klicka på "Jag accepterar" knappen ovan </div>
<br/>
Betalningen görs till<br/>
<br/>
Organisationsnummer: 857209-3394<br/>
Göteborgs och Bohus läns Pistolskyttekrets<br/>
<br/>
Adress: Box 255<br/>
421 23 VÄSTRA FRÖLUNDA<br/>
<br/>
Besöksadress: Skjutfältsvägen 90<br/>
431 90 Mölndal<br/>
<br/>
Kontakt info: Alliansens sekretariatet.
<br/>


</html>