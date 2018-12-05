<?php
include_once "GunnarCore.php";
session_start();

	$debug = 0;
	$act=$_POST['myAction'];
	$msg = "";
	$done = 0;
	$shotIdSelected=$_POST['shotIdSelected'];

	// We must have selected a competition first
	if (!session_is_registered("shotSession"))
		http_redirect("LogIn.php");
	
	$shot = unserialize($_SESSION["shotSession"]);
	$entry = new Entry();
	
	$comp = new Competition();
	// Try to load in the selected competition
	if (session_is_registered("compId")) {
		$comp->load($_SESSION['compId']);
		//print $_SESSION['compId'];
		//print $comp->name;
		
		$payments = $comp->getDibsPayments($comp->id, $shot->id);
	}
	else {
		// Make the user choose a competition
		http_redirect("welcome.php");
	}

	$focusPoint = "gunClassId";

	switch ($act) {
		case "GoBook":
			// We've flown in from welcome.php
			break;
			
		case "cancelEntry":
			$cid = $_POST["entryId"];
			$entry->id = $cid;
			$msg = $entry->delete();
			
			$entry->gunClassificationId = $_POST["gunClassId"];
			$entry->shotClassId = $_POST['shotClassId'];
			break;
			
		case "bookPatrol":
			$entry->gunClassificationId = $_POST['gunClassId'];
			$entry->shotClassId = $_POST['shotClassId'];
			
			if (($shot->userType == "ADMIN") || ($shot->userType == "OPER")) {
				$entry->shotId = $shotIdSelected;
				$entry->bokadAvShotId = $shot->id;
			} else {
				$entry->shotId = $shot->id;
			}
			
			$entry->patrolId = $_POST['patrolId'];
			$ok = $entry->save();
			
			if ($ok != "OK") {
				$msg = "Misslyckades med anmälan. $ok. " . $msg;
				if (eregi("duplic.*", $msg))
					$msg = "Du är redan anmäld.";
				if (eregi("EXISTS.*", $msg))
					$msg = "Anmäl dig med annat vapen.";
			}
			else {
				$msg = "Anmälan mottagen.";
				// Now let the user change patrol if desired
				$_SESSION["entryId"] = $entry->id;
				$done = 0;
			}
			
			break;
			
		case "pickGunClass":
			$entry->shotClassId = 0;
			$entry->gunClassificationId = $_POST["gunClassId"];
			break;
			
		case "pick":
			$entry->shotClassId = $_POST['shotClassId'];
			$entry->gunClassificationId = $_POST["gunClassId"];
			break;
			
		default:
			if (session_is_registered("entryId")) {
				$entry->load($_SESSION["entryId"]);
				$patrol = new Patrol();
				$patrol->load($entry->patrolId);
				$compDay = new CompetitionDay();
				$compDay->load($patrol->competitionDayId);
				
				$selectedCompId = $comp->id;
				
				$comp = new Competition();
				$comp->load($compDay->competitionId);
				
				if( $compDay->competitionId != $selectedCompId) {
					$comp->load($selectedCompId);
					session_unregister("entryId");
					$patrol = null;
					$compDay = null;
					$entry = null;
				}
			}
			break;		
	}
header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>EnterCompetition</title>
<STYLE>
@import url(gunnar2.css);
@import url(css/gunnar.css);

table.ShowPayments
{
	background-color: silver;
	border-style: groove;
	border-color: #444444;
	width: 100%;
}

th.ShowPayments
{
	background-color: #000000;
	font-size: 16px;
	color: #ffffff;
	font-style: italic;
	text-align: left;
	padding-top: 5px;
	padding-bottom: 5px;
	padding-left: 5px;
}

tr.ShowPayments
{
	background-color: #cccccc;
	color: #000000;
}

tr.ShowPayments:Hover
{
	background-color: #ffff00;
	color: #000000;
}
</STYLE>
</head>
<script language="javascript">

  function setFocus()
  {
  		document.forms['bookform'].elements["<?=$focusPoint?>"].focus();
  }


  function doPayAll(patrulId, entryId, gunClassificationId, shotClassId, shotId) {
	  //window.name = "enterCompetition";
	  document.forms[0].action="payAcceptCompetition.php";
	  var orderid =  patrulId + "_" + 
		gunClassificationId + "_" + 
		shotClassId + "_" + 
		shotId + "_" + 
		entryId;
		<?$hostname = "www.okrets.se";?>
	  document.forms[0].elements["orderid"].value = orderid;
	  var accepturl = "http://<?=$hostname?>/skytte/payCompleteCompetition.php?popresult=accept&popaction=PopDown";
	  document.forms[0].elements["accepturl"].value = accepturl;// + "&orderid=" + orderid;
	  var cancelurl = "http://<?=$hostname?>/skytte/payCancelCompetition.php?popresult=cancel&popaction=PopDown";
	  document.forms[0].elements["cancelurl"].value = cancelurl;// + "&orderid=" + orderid;
	  var callbackurl = "http://<?=$hostname?>/skytte/payCallbackCompetition.php?popresult=accept&popaction=PopDown";
	  document.forms[0].elements["callbackurl"].value = callbackurl + "&orderid=" + orderid;
	  document.forms[0].submit();
  }
  
  function doPay(patrulId, entryId) {
	  // doPayAll(patrulId, entryId, <?=$entry->gunClassificationId?>, <?=$entry->shotClassId?>, <?=$entry->shotId?>) ;
  }
</script>
<script language="javascript">
  function doBook(pId) {
	  	document.forms['bookform'].elements["myAction"].value = "bookPatrol";
		document.forms['bookform'].elements["patrolId"].value = pId;
		document.forms['bookform'].submit();
  }
</script>
 
 <script language="javascript">
  function book(pId)
  {
 	doBook(pId);
  }


</script>

<script language="javascript">
  /*function doPayAsk(pId, entryId)
  {
	  if(confirm('Vill du betala')) {
		  doPay(pId, entryId);
	  } else {
	  }
 	
  }*/
</script>


 <script language="javascript">
  
  function cancelEntry(eId)
  {
	  
		document.forms['bookform'].elements["myAction"].value = "cancelEntry";
		document.forms['bookform'].elements["entryId"].value = eId;
		document.forms['bookform'].submit();
	  
  }

  function pickGunClass()
  {
		document.forms['bookform'].elements["myAction"].value = "pickGunClass";
		document.forms['bookform'].submit();
  }

  function pick()
  {
		document.forms['bookform'].elements["myAction"].value = "pick";
		document.forms['bookform'].submit();
  }
</script>

<? if($act == "bookPatrol") { ?>
<? if ($ok != "OK") { ?>
	<body onLoad="javascript:alert('Ett fel har inträffat');">
<? } else { ?>

	<? if ($comp->onlineBetalning == "Y") { ?>
		<? if (($shot->userType == "ADMIN") || ($shot->userType == "OPER")) { ?>
			<body onLoad="javascript:doPayAsk(<?=$_POST['patrolId']?>, <?= $entry->id?>);">
		<? } else { ?>
			<body onLoad="javascript:doPay(<?=$_POST['patrolId']?>, <?= $entry->id?>);">
		<? } ?>
	<? } else {?>
		<body onLoad="javascript:setFocus();">
	<? } ?>
<? } ?>
<? } else {?>
	<body onLoad="javascript:setFocus();">
<? } ?>
<div class="error"><?=$msg?></div>
<br>
<? if ($done == 0) { ?>



<? if ($comp->onlineBetalning == "Y") { ?>
<?
	$entryComp = new Entry();
	$obetald = $entryComp->loadUnPayedStarts($shot->id, $comp->id);
	
	if(!empty($obetald)) {
		?><div>Du har följande starter i din kundkorg som du inte har betalat. Antingen fortsätter du boka eller kan du betala dem. Obetalda starter avbokas efter 24 timmar.</div><?
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
						<!-- <input type="hidden" name="test" value="foo"/> -->
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
					?><td><button onClick="javascript:cancelEntry(<?=$rowobetald->EntryId?>);">Avboka</button></td><?
				?></tr><?
			}
		} 
		?></table><?
	}
?>
	
<? } ?>

<form method="POST" id="bookform">
<input type="hidden" name="myAction" value="nop">
<input type="hidden" name="patrolId">
<input type="hidden" name="entryId">

<input type="hidden" name="amount" value="10000">
<input type="hidden" name="merchant" value="90150489">
<input type="hidden" name="orderid" value="">
<input type="hidden" name="currency" value="SEK">
<!-- <input type="hidden" name="test" value="foo"/> -->
<input type="hidden" name="lang" value="sv"/>

<input type="hidden" name="capturenow" value="true"/>


<input type="hidden" name="popaction" value="PopUp">
<input type="hidden" name="accepturl" value="">
<input type="hidden" name="cancelurl" value="">
<input type="hidden" name="callbackurl" value="">

<table border="0" width="100%">
<tr>
	<td width="100%">

<table border="0" width="50%">

<tr>
	<td width="25%" align="right">Tävling:</td>
		<td><?=$comp->name?></td>
</tr>

<tr>
	<td align="right">Vapen:</td>
		<td>
		<?
			// Get a list of Gun classes
			$lst = $shot->getGunClassList($comp->id);
			$selected = "";
			if ($entry->gunClassificationId == 0)
				$selected = " selected";
		?>
			
		<select name="gunClassId" onChange="javascript:pickGunClass();">
		<option value="0"<?=$selected?>>-- Välj vapen --</option>
			<?
			foreach ($lst as $key => $value)
			{
				$selected = "";
				if ($key == $entry->gunClassificationId)
					$selected = " selected";
			?>
				<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
			<?
			}
		 	?>
		</select>
		</td>
</tr>


<? if ($entry->gunClassificationId > 0) { ?>
<tr>
	<td align="right">Tävlingsklass:</td>
		<td>
		<?
			// Get a list of Gun classes
			$lst = $shot->getClassList($entry->gunClassificationId, $comp->masterskap);
			$selected = "";
			if ($entry->shotClassId == 0)
				$selected = " selected";
		?>
			
		<select name="shotClassId" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>-- Välj klass --</option>
			<?
			foreach ($lst as $key => $value)
			{
				$selected = "";
				if ($key == $entry->shotClassId)
					$selected = " selected";
			?>
				<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
			<?
			}
		 	?>
		</select>
		</td>
</tr>
<? } // if we have a gun class ?>

<?if (($shot->userType == "ADMIN") || ($shot->userType == "OPER")) {?>
<tr>
		<td align="right">Anmäl denna skytt:</td>
		<td>
		<?
			$eshot = new Shot();
			$cps = $eshot->getGunCardList();
			$selected = "";
		?>

		<select name="shotIdSelected">
		<option selected value="<?=$shot->id?>">Anmäl mig</option>
			<?
			foreach ($cps as $key => $value)
			{
				$selected = "";
				if ($key == $shotId)
					$selected = " selected";
			?>
				<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
			<?
			}
		 	?>
		</select>
		</td>
		
	</tr>
<? }?>
</table>
</td>
</tr>
</table>

<div class="shooters" id="shooters">
</div>


<br>

<?
if ($entry->shotClassId > 0) {
	
	$patrol = new Patrol();
	$shotGroup = array();
	$list  = $patrol->listAvailable($comp->id, $entry->gunClassificationId, $shot->id);
		
	// Now get the shooters in each patrol
	foreach ($list as $pt) {
		$col = "Grey"; // First row in list is grey
		$mms = $patrol->listMembers($pt["PatrolId"]);
		$ss = "";
		
		foreach ($mms as $mm) {
			$colClass = "ShooterList" . $col;
			$clubClass = "";
			if ($mm["ClubId"] == $shot->clubId)
				$clubClass = " class=\"MyClub\"";
				
			
			$ss .= "<tr class=\"" . $colClass . "\"><td>" . $mm["FirstName"] . " " . $mm["LastName"] . "</td>" .
				"<td" . $clubClass . ">" . $mm["ClubName"] . "</td>" .
				"<td>" . $mm["GunClass"] . "</td>" . 
				"<td>" . $mm["ShotClass"] . "</td>" .
			"</tr>";
			
			// Flip row colour
			if ($col == "Grey")
				$col = "Pink";
			else
				$col = "Grey";
		} 
		$shotGroup[$pt["PatrolId"]] = $ss;
	}
	
?>
<div style="overflow: auto; height: 300px; width: 48%;">
<table class="PickPatrol" onmouseout="javascript:showShooters(0);">
<tr>
	<th class="PickPatrol">
		<?
			if (isPrecision($comp->scoreType)) {
		?>
			Start
		<?	
			}else{
		?>
			Patrull
		<? 
			}
		?>
	</th>
	<th class="PickPatrol">Start</th>
	<th class="PickPatrol">Platser kvar</th>
	<th></th>
	<th></th>
</tr>
<?
	foreach ($list as $p) {
		$class = "PickPatrol";
		$onClick = "<button name=bookBtn onClick=\"javascript:book(" . $p["PatrolId"] . ");\">Boka</button>";
		$cancelBtn = "";
		
		if ($p["Status"] == "BOOKED") {
			$class = "BookedPatrol";
			// $onClick = "";
			$cancelBtn = "&nbsp;&nbsp;<button name=cancelBtn onClick=\"javascript:cancelEntry(" . $p["EntryId"] . ");\">Avboka</button>";
		}
		
		if(	((($shot->userType == "ADMIN") || ($shot->userType == "OPER")) && $p["Hidden"] == 1) 
		 || $p["Hidden"] == 0) {
		
		?>
			<tr class="<?=$class?>" onmouseover="javascript:showShooters(<?=$p["PatrolId"]?>);">
				<td class="PickPatrol"><?=$p["StartTime"]?> <?=$p["Hidden"] == 1 ? "Gömd" : ""?></td>
				<td class="PickPatrol"><?=$p["SortOrder"]?></td>
				<td class="PickPatrol"><?=$p["SeatsLeft"]?></td>
				<td><?=$cancelBtn?></td>
				<?if ($p["Status"] == "BOOKED" && ! (($shot->userType == "ADMIN") || ($shot->userType == "OPER")) ) {?>
				<td></td>
				<? } else { ?>
				<td><?=$onClick?></td>
				<?}?>
			</tr>
		<? } ?>
<? } ?>
</table>
</div>

<br/>
<i>Anmäl dig genom att klicka på en rad.<br/></i>
<script language="javascript">

var shootersHdr = "<table width=\"100%\">" +
	"<tr>" +
		"<th class=\"ShooterList\">Skytt</th>" +
		"<th class=\"ShooterList\">Klubb</th>" +
		"<th class=\"ShooterList\">Vapen</th>" +
		"<th class=\"ShooterList\">Klass</th>" +
	"</tr>";
	
var shootersFtr = "</table>";
var obj = document.getElementById("shooters");

function showShooters(pid)
{
	switch (pid) {
<? 
		foreach ($shotGroup as $key=>$val) { ?>
		case <?=$key?>:
			obj.innerHTML = shootersHdr + '<?=$val?>' + shootersFtr;
			obj.style.visibility = "visible";
			break;
<?		} // foreach patrol ?>
		default: obj.innerHTML = "";
			obj.style.visibility = "hidden";
			break;
	} // End switch
}


</script>

<? } // if we have chosen shot class ?>

</form>
<? } // unless done ?>

<br/>
Dina betalningar för aktuella tävlingen
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
?>

</body>
</html>