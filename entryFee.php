<?php
session_start();
include_once "GunnarCore.php";

$debug = 0;

$act=$_POST['myAction'];
// We must have logged in first
if (!session_is_registered("shotSession"))
	http_redirect("LogIn.php");
	
$shot = new Shot();
$shot = unserialize($_SESSION["shotSession"]);

if (($shot->userType != "ADMIN") && ($shot->userType != "OPER")) {
	http_redirect("notAllowed.php");
}

$eshot = new Shot();
$entry = new Entry();

$comp = new Competition();
$club = new Club();


$msg = "";

// Load up stuff?
if ($_POST["compId"] > 0) {
	$eshot->load($_POST["shotId"]);
	$comp->load($_POST["compId"]);
	$club->load($_POST["clubId"]);
}
else {
	// Check if the session comp is set
	if (session_is_registered("competitionId"))
		$comp->load($_SESSION["competitionId"]);
}

switch ($act) {
	case "pay":
		$entry->load($_POST["entryId"]);
		$ok = $entry->setEntryStatus('P',$shot->gunCard,1);
		// Did we suceed?
		if ($ok)
		{
			$msg = "Betalning gjord.";
		}
		else {
			$msg = "Kunde ej utföras. " . $ok . " " . $msg;
		}
		break;
	case "cancelPayment":
		$entry->load($_POST["entryId"]);
		$ok = $entry->setEntryStatus('U',$shot->gunCard,1);
		// Did we suceed?
		if ($ok)
		{
			$msg = "Betalning raderad.";
		}
		else {
			$msg = "Kunde ej utföras. " . $ok . " " . $msg;
		}		
		break;
	case "pick":
		if (!session_is_registered("competitionId"))
			$_SESSION["competitionId"] = $comp->id;
		break;
	default:
		break;
}

header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>EntryFee</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css">
<link rel="stylesheet" href="css/gunnar.css" type="text/css">

</head>
<script language="javascript">

  function setFocus()
  {
  		document.forms[0].elements["hits"].focus();
  }
  
  function pay(eid)
  {
		document.forms[0].elements["myAction"].value = "pay";
		document.forms[0].elements["entryId"].value = eid;
		document.forms[0].submit();
  }

  function cancelPayment(eid)
  {
		document.forms[0].elements["myAction"].value = "cancelPayment";
		document.forms[0].elements["entryId"].value = eid;
		document.forms[0].submit();
  }

  function pick()
  {
		document.forms[0].elements["myAction"].value = "pick";
		document.forms[0].submit();
  }

</script>

<body onLoad="javascript:setFocus();">
<div class="error"><?=$msg?></div>
<br>

<form method="POST"><input type="hidden" name="myAction">
<form method="POST"><input type="hidden" name="entryId">

<center>

<table border="0" width="90%">
<tr>
	<td align="right">Tävling:</td>
		<td>
		<?
			$cps = $comp->getList(" < 4");
			$selected = "";
			if ($comp->id == 0)
				$selected = " selected";
		?>
			
		<select name="compId" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>-- Välj Tävling --</option>
			<?
			foreach ($cps as $key => $value)
			{
				$selected = "";
				if ($key == $comp->id)
					$selected = " selected";
			?>
				<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
			<?
			}
		 	?>
		</select>
		</td>
</tr>

<? if ($comp->id != 0) { ?>
<tr>
	<td align="right">Klubb:</td>
		<td>
		<?
			$cps = $club->getClubsInComp($comp->id);
			$selected = "";
			if ($club->id == 0)
				$selected = " selected";
		?>
			
		<select name="clubId" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>-- Välj Klubb --</option>
			<?
			foreach ($cps as $key => $value)
			{
				$selected = "";
				if ($key == $club->id)
					$selected = " selected";
			?>
				<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
			<?
			}
		 	?>
		</select>
		</td>
</tr>
<? }  ?>


<? if ($club->id != 0) { ?>
	<tr>
		<td align="right">Skytt:</td>
		<td>
		<?
			$cps = $club->listEntries($comp->id);
			$selected = "";
		?>

		<select name="shotId" onChange="javascript:pick();">
		<option value="0">-- Välj skytt --</option>
			<?
			foreach ($cps as $key => $value)
			{
				$selected = "";
				if ($key == $eshot->id)
					$selected = " selected";
					
			?>
				<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
			<?
			}
		 	?>
		</select>
		</td>
	</tr>
<? } ?>
	<tr>
	</tr>
</table>

<br>

<? if ($eshot->id > 0) { 
	$cps = $entry->getList($eshot->id, $comp->id);
	?>
<table border="0" width="50%">
<tr>
<th></th>
<th>Starttid</th>
<th>Klass</th>
<th>Patrull no</th>
<th>Betalningstid</th>
<th>Transactions id med online betalning eller betalningen tagits emot av skytt me kortnr.</th>
<th>Status (1:Manuel betalnig, 2:Online betalning)</th>
</tr>
<? 
	$eid = 0;
	foreach ($cps as $row) {
		if ($row["Id"] == $eid) {
			continue;
		}
		$eid = $row["Id"];
		$chk = "";
		$fnk = "pay";
		if ($row["Status"] == "P") {
			$chk = "checked";
			$fnk = "cancelPayment";
		}
		$entryTr = new Entry();
		$transact = $entryTr->getTransactionsId($eid);
		$stcodes = $entryTr->getStatusCode($eid);
		$trstr = "";
		$sts = "";
		foreach($transact as $t) {
			$trstr = $t->TransactionId . " " . $trstr;
		}
		foreach($stcodes as $s) {
			$sts = $s->StatusCode . " " . $sts;
		}
	?>
	<tr>
		<td><input onClick="javascript:<?=$fnk?>(<?=$row["Id"]?>);" type="checkbox" <?=$chk?>></td> 
		<td><?=$row["FirstStart"]?></td>
		<td><?=$row["Gun"]?></td>
		<td><?=$row["PatrolNumber"]?></td>
		<td><?=$row["PayDate"]?></td>
		<td><?=$trstr?></td>
		<td><?=$sts?></td>
		
	</tr>
	<? } ?>
</table>
<? } // If shot ?>
</center>


<? if ($comp->id != 0 &&  $eshot->id != 0)  { 
	$payments = $comp->getDibsPayments($comp->id, $eshot->id);

?><br/>Skyttens online betalningar för aktuella tävlingen

	<? if(!empty($payments)) { ?>
	<table class="ShowPayments" cellspacing="0" cellpadding="0">
	<tr class="ShowPayments">
	<th class="ShowPayments">Datum</th>
	<th class="ShowPayments">Värde</th>
	<th class="ShowPayments">Transaktion Id</th>
	<th class="ShowPayments">Order Id</th>
	</tr>
	<?	
		foreach ($payments as $payment) { 
	?>
		<tr class="ShowPayments">
			<td class="ShowPayments"><?=$payment->PayDate?></td>
			<td class="ShowPayments"><?=$payment->Amount?></td>
			<td class="ShowPayments"><?=$payment->TransactionId?></td>
			<td class="ShowPayments"><?=$payment->OrderId?></td>
		</tr>
	<? } ?>
	</table>
	<?} else {
		?><br/>Inga betalningar än<?
	}

}
?>

</form>
</body>
</html>
