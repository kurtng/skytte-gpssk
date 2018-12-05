<?php
session_start();
include_once "GunnarCore.php";

$islogin=$_GET['login'];

$act=$_POST['myAction'];
if (session_is_registered("shotSession"))
	$shot = unserialize($_SESSION["shotSession"]);
else
	http_redirect("LogIn.php");

switch ($act) {
	case "GoBook":
		$_SESSION["compId"] = $_POST["compId"];
		$_SESSION["competitionId"] = $_POST["compId"];
		$comp = new Competition();
		$comp->load($_POST["compId"]);
		$comp->selectFirstCompDay($_POST["compId"]);
		http_redirect("enterCompetition.php");
		break;
	case "cancel":
		$entry = new Entry();
		$cid = $_POST["entryId"];
		$entry->id = $cid;
		$msg = $entry->delete();
		break;
	case "GoResults":
		$_SESSION["compId"] = $_POST["compId"];
		$_SESSION["competitionId"] = $_POST["compId"];
		$comp = new Competition();
		$comp->load($_POST["compId"]);
		$comp->selectFirstCompDay($_POST["compId"]);
		http_redirect("showResult.php");
		break;
}
	
$msg = "";

header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>Welcome</title>
<STYLE>
@import url(gunnar2.css);
@import url(css/gunnar.css);
</STYLE>
<style>
.infocls {font-size:10px;}
.infocls td{font-size:10px;}
</style>
</head>
<script language="javascript">
	function startMenu()
	{
		<? if ($islogin) {?>
  			window.top.frames["menuFrame"].location = "Menu.php";
  		<?}?>

  	}
	
	function goBook(pCompId)
	{
		document.forms[0].compId.value = pCompId;
		document.forms[0].myAction.value = "GoBook";
		document.forms[0].submit();
	}

	function goResults(pCompId)
	{
		document.forms[0].compId.value = pCompId;
		document.forms[0].myAction.value = "GoResults";
		document.forms[0].submit();
	}

	function cancelBooking(entry)
	{
		document.forms[0].elements["myAction"].value = "cancel";
		document.forms[0].elements["entryId"].value = entry;
		document.forms[0].submit();
	}
</script>

<body onLoad="javascript:startMenu();">
<div class="error"><?=$msg?></div>
<br>

<form method="POST">

<input type="hidden" name="myAction" value="login" />
<input type="hidden" name="entryId">

<input type="hidden" name="compId" />
</form>
<center>
<h2>V&auml;lkommen <?=$shot->firstName?></h2>

<?
	$entryComp = new Entry();
	$obetald = $entryComp->loadUnPayedStarts($shot->id);
	
	if(!empty($obetald)) {
		?><div>Du har obetalda starter i din kundkorg. Var god betala dem. Annars avbokas de efter 24 timmar.</div><?
		?><table class="PickCompetition" >
		<th>Tävling</th>
		<th>Klass</th>
		<th>Starttid</th>
		<th>Patrol No</th>
		<th></th><?
		$compid = 0;
		$antalstart = 0;
		$obetald[] = null;//Add one to the end to make the last button print down in foreach
		foreach ($obetald as $rowobetald) {
			if($compid != 0 && $rowobetald->CompId != $compid) {
				?><tr><?
					?><td></td><?
					?><td></td><?
					
					?><td colspan="2">
						<form method="POST" action="payAcceptCompetitionv2.php">
						
						<input type="hidden" name="amount" value="<?=10000*$antalstart?>">
						<input type="hidden" name="merchant" value="90150489">
						<!-- <input type="hidden" name="orderid" value="<?=$rowobetald->PatrolId?>_<?=$rowobetald->GunClassificationId?>_<?=$rowobetald->ShotClassId?>_<?=$shot->id?>_<?=$rowobetald->EntryId?>"/> -->
						<input type="hidden" name="orderid" value="<?=$compid?>_<?=$shot->id?>_<?=date(DATE_ATOM)?>"/>
						
						<input type="hidden" name="currency" value="SEK"/>
						<input type="hidden" name="test" value="foo"/>
						<input type="hidden" name="lang" value="sv"/>
						<input type="hidden" name="capturenow" value="true"/>
						<?$hostname="www.okrets.se";?>
						<input type="hidden" name="popaction" value="PopUp">
						<input type="hidden" name="accepturl" value="http://<?=$hostname?>/skytte/payCompleteCompetitionv2.php?popresult=accept&popaction=PopDown">
						<input type="hidden" name="cancelurl" value="http://<?=$hostname?>/skytte/payCancelCompetitionv2.php?popresult=cancel&popaction=PopDown">
						<input type="hidden" name="callbackurl" value="http://<?=$hostname?>/skytte/payCallbackCompetitionv2.php?popresult=accept&popaction=PopDown">
						<input type="submit" value="Betala">
						<span><?=100*$antalstart?> SEK för <?=$antalstart?> start(er)</span>
						</form>
					
					</td><? 
				?></tr><?
				$antalstart = 0;
			}
			if($rowobetald != NULL) {
				$compid = $rowobetald->CompId;
				$antalstart ++;
				?><tr><?
					?><td><?=$rowobetald->CompetitionName?></td><?
					?><td><?=$rowobetald->ClassName?></td><?
					?><td><?=$rowobetald->PatrolStartTime?></td><?
					?><td><?=$rowobetald->PatrolNo?></td><?
					?><td><button onClick="javascript:cancelBooking(<?=$rowobetald->EntryId?>);">Avboka</button></td><?
				?></tr><?
			}
		} 
		?></table><?
	}
?>

<? $cmp = new Competition();
	$cList = $cmp->listByStartDate();
	$rno = 0;
	$disp = 0;
?>
 
<table class="PickCompetition" cellspacing="0" cellpadding="0">
<tr>
<th class="PickCompetition">Tävlingsdatum</th>
<th class="PickCompetition">Tävling</th>
<th class="PickCompetition">Plats</th>
<th class="PickCompetition">Arrangör</th>
<th class="PickCompetition">Status</th>

</tr>

<? foreach ($cList as $cp) { 

	$class = "CompOpen";
	$statusText = "";
	$onClick = "";
	$ledigaPlatserMsg = "";
	
	
	switch ($cp->Status) {
		case 0:
			$class = "CompUnknown";
			$statusText = "Tävlingen förbereds";
			if ($shot->userType != "ADMIN")
				continue(2);
			break;
		case 1:
			$class = "CompOpen";
			if ($cp->Expiry == "OK") {
				$statusText = "Anmälan öppen gå till bokning";
				$onClick = "onClick=\"javascript:goBook(" . $cp->Id . ");\"";
			}
			else {
				$class = "CompClosed";
				$statusText = "Start-datum passerat";
			}
			break;
		case 2:
			$class = "CompRunning";
			$statusText = "Anmälan stängd";
			break;
		case 3:
			$class = "CompRunning";
			$statusText = "Tävling pågår gå till bokning";
			$onClick = "onClick=\"javascript:goBook(" . $cp->Id . ");\"";
			break;
		case 4:
			$class = "CompClosed";
			$statusText = "Tävling avslutad gå till resultat";
			$onClick = "onClick=\"javascript:goResults(" . $cp->Id . ");\"";
			break;
		default:
			$class = "CompArchived";
			$statusText = "Tävling arkiverad";
			if ($shot->userType != "ADMIN")
				continue;
			break; 
	}
?>

<tr class="<?=$class?>" <?=$onClick?>>
	<td class="PickCompetition"><?=$cp->StartDate?></td>
	<td class="PickCompetition"><?=$cp->Name?></td>
	<td class="PickCompetition"><?=$cp->Location?></td>
	<td class="PickCompetition"><?=$cp->HostClub?></td>
	<td class="PickCompetition"><?=$statusText?>
	<?
		if ($cp->Expiry == "OK" && ($cp->Status == 1 || $cp->Status == 3)) {
			$totalAvailable = 0; // notera att vissa platser räknas flera gånger om dem är möjliga i flera klasser
			$totalOccupied = 0;
				
			$score = new Score();
			$lstSc = $score->listAvailabalityAndAnmalningarPerClass($cp->Id);
			foreach($lstSc as $row)
			{
				$antalPatrol = $row["AntalPatrol"];
				$patrolSize = $row["PatrolSize"];
				$antalAnmalningar = $row["AntalAnmalningar"];
				$gunClassName = $row["GunClassName"];
				
				settype($antalPatrol, "integer");
				settype($patrolSize, "integer");
				settype($antalAnmalningar, "integer");
				$totalAvaliableForKlass = $antalPatrol*$patrolSize;
				$totalOccupedForKlass = $antalAnmalningar;
				$totalAvailable += $totalAvaliableForKlass;
				$totalOccupied += $totalOccupedForKlass;
			}
			
			?><br/>(<?=$totalOccupied?> anmälningar)<?
		}

		?>
	</td>
</tr>

<? } // foreach competition ?>
</table>

</center>

</body>
</html>
