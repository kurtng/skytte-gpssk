<?php
session_start();
include_once "GunnarCore.php";

$islogin=$_GET['login'];

$act=$_POST['myAction'];
if (session_is_registered("shotSession"))
	$shot = unserialize($_SESSION["shotSession"]);
else
	http_redirect("LogIn.php");


$comp = new Competition();


// Load up stuff?
if ($_POST["compId"] > 0) {
	
	$comp->load($_POST["compId"]);
	$_SESSION["competitionId"] = $comp->id;
}

switch ($act) {
	
	case "pick":
			if ($_POST["compId"] > 0) {
				$compId = $_POST["compId"];
				$comp->load($compId);
				$_SESSION["competitionId"] = $comp->id;
			}
		break;
	default:
			// Try to load in the selected competition
			if (session_is_registered("competitionId"))
			{
				$compId = $_SESSION["competitionId"]; 
				$comp->load($compId);
				$_SESSION["competitionId"] = $comp->id;
				
			}
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
	function pick(isTavling)
  	{
		document.forms[0].elements["myAction"].value = "pick";
		document.forms[0].action="lagesrapport.php";
		document.forms[0].submit();
  	}
</script>

<body onLoad="javascript:startMenu();">
<div class="error"><?=$msg?></div>
<br>

<form method="POST"><input type="hidden" name="myAction" value="login" />
<input type="hidden" name="compId" />

<center>

	<table border="0" width="90%">
		<tr>
			<td align="right">Tävling:</td>
				<td>
				<?
					$cps = $comp->getList("between 2 and 4"); // Get competitions I can enter scores for
					$num = sizeof($cps);
					$selected = "";
					if ($comp->id == 0)
						$selected = " selected";
				?>
					
				<select name="compId" onChange="javascript:pick(true);">
				<option value="0"<?=$selected?>>-- Välj Tävling --</option>
					<?
					foreach ($cps as $key => $value)
					{
						$selected = "";
						if ($comp->id == 0 && $num==1) {
							$comp->load($key);
							$_SESSION["competitionId"] = $comp->id;
						}
							
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
	</table>

<? 	if($compId != 0) {
		$cmp = new Competition();
		$cList = $cmp->listByStartDate($compId);
	
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
				$statusText = "Anmälan öppen";
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
			$statusText = "Tävling pågår";
			$onClick = "onClick=\"javascript:goBook(" . $cp->Id . ");\"";
			break;
		case 4:
			$class = "CompClosed";
			$statusText = "Tävling avslutad";
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

<tr class="<?=$class?>" >
	<td class="PickCompetition"><?=$cp->StartDate?></td>
	<td class="PickCompetition"><?=$cp->Name?></td>
	<td class="PickCompetition"><?=$cp->Location?></td>
	<td class="PickCompetition"><?=$cp->HostClub?></td>
	<td class="PickCompetition"><?=$statusText?></td>
</tr>
<tr>
	<td colspan=2 class="PickCompetition infocls">
		<div>
			<table>
			<tr><td colspan=10><strong>Lediga platser och anmälningar</strong></td></tr>
			<tr>
				<td>Klass</td>
				<td>Antal patruller</td>
				<td>Platser</td>
				<td>Anmälningar</td>
				<td>Lediga platser kvar</td>
			</tr>
			<?
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
			
				?>
				<tr>
					<td><?=$gunClassName?></td>
					<td><?=$antalPatrol?></td>
					<td><?=$totalAvaliableForKlass?></td>
					<td><?=$totalOccupedForKlass?></td>
					<td><?=$totalAvaliableForKlass - $totalOccupedForKlass?></td>
				</tr><?
					
			}
			?>
			
		
			<tr><td>Totalt</td><td></td><td></td><td><?=$totalOccupied?> anmälningar</td></tr>
		</table>
		</div>
	</td>
	<td colspan=4 class="PickCompetition infocls">
		

		<table>
			<tr><td><strong>Klass</strong></td><td>Anmälningar</td><td>Resultat</td></tr>
			<?
			$totalAntalResultat = 0;
			$totalAntalAnmalningar = 0;
			$score = new Score();
			$lstShKl = $score->scoreCountPerShotClass($cp->Id);
			$lstAnCn = $score->anmalningarCountPerShotClass($cp->Id);
			foreach ($lstAnCn as $row) {
				$scoreCount = $row["AntalAnmalningar"];
				$shotClassName = $row["ShotClassName"];
				$totalAntalAnmalningar += $scoreCount;
				?>
				<tr>
				<td><?=$shotClassName?></td>
				<td><?=$scoreCount?></td>
				<?foreach($lstShKl as $rowResult) {
					if($rowResult["ShotClassName"] == $shotClassName) {
						$resultCount = $rowResult["ScoreCount"];
						$totalAntalResultat += $resultCount;
						?><td><?=$resultCount?></td><?
						break;
					}
				}?>
				</tr>
			<?}?>
			
			<tr>
				<td>Total</td>
				<td><?=$totalAntalAnmalningar?></td>
				<td><?=$totalAntalResultat?></td>
			</tr>
		</table>
	</td>
</tr>

<? } // foreach competition ?>
</table>
			<?}?>
</center>
</form>
</body>
</html>
