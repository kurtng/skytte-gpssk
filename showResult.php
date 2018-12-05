<?php
include_once "GunnarCore.php";
session_start();

	$debug = 0;
	$act=$_POST['myAction'];
	$msg = "";
	// We must be logged in
	if (!session_is_registered("shotSession"))
		http_redirect("LogIn.php");

	$shot = unserialize($_SESSION["shotSession"]);
	$comp = new Competition();
	$shotClassId = 0;
		
	if (session_is_registered("compId")) {
		$compId = $_SESSION["compId"]; 
		$comp->load($compId);
	}
	
	switch ($act) {
		case "pick":
			$compId = $_POST["compId"];
			$shotClassId = $_POST["shotClassId"];
			$comp->load($compId);
			$selectedClubName = $_POST["selectedClubName"];
			break;
		default:
			break;
	}
	
	
header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>ShowResult</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css" media="screen" />

</head>
<script language="javascript">

  function setFocus()
  {
  		document.forms[0].elements["<?=$focusPoint?>"].focus();
  }
  
  function pick()
  {
		document.forms[0].elements["myAction"].value = "pick";
		document.forms[0].submit();
  }

  function rullapa() {
	    //window.scrollBy(0,20); // horizontal and vertical scroll increments
	    
	    
	    if(document.forms[0].elements["compId"].selectedIndex > 0){
	    	var old = document.body.scrollTop;
		    document.body.scrollTop += 1;
		    
		    if (document.body.scrollTop > old) {
		        // we still have some scrolling to do...
		        scrolldelay = setTimeout('rullapa()',25); // scrolls every 100 milliseconds
		    } else {
		    	document.forms[0].elements["myAction"].value = "pick";
		    	document.forms[0].submit();
		    	
		    }
	    }
  }
</script>

<body onload="<? if( $_GET['projektor']) {?>scroll(0,0);setTimeout('rullapa()',2000);<?}?>">
<div class="error"><?=$msg?></div>
<br>

<form method="POST">
<input type="hidden" name="myAction" value="nop">

<table border="0" width="100%">
<tr>
	<td align="right">Tävling:</td>
		<td>
		<?
			$cp = new Competition();
			$cps = $cp->getList("between 2 and 4");
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

<tr style="display:none;">
	<td align="right">Tävlingsklass:</td>
		<td>
		<?
			// Get a list of Gun classes
			$lst = $shot->getCompetitionClassList($comp->id);
			$selected = "";
			if ($shotClassId == 0)
				$selected = " selected";
		?>
			
		<select disabled name="shotClassId" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>-- Välj klass --</option>
			<?
			foreach ($lst as $key => $value)
			{
				$selected = "";
				if ($key == $shotClassId)
					$selected = " selected";
			?>
				<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
			<?
			}
		 	?>
		</select>
		</td>
</tr>

<?
	$cp = new Score();
	$list = $cp->listResult($comp->id, $shotClassId);
	
	$klubbar = array();
	foreach ($list as $row)
	{
		$rt = $row["ClubName"];
		if(!in_array($rt, $klubbar)) {
			array_push($klubbar, $rt);
		}
	}
			
	if(!empty($selectedClubName)) {
		$i = 0;
		foreach ($list as $row)
		{
			$rt = $row["ClubName"];
			if($selectedClubName != $rt) {
				unset($list[$i]);
			}
			$i++;
		}
	}
?>

<tr>
	<td align="right">Klubb:</td>
		<td>
		
		<select name="selectedClubName" onChange="javascript:pick();">
		<option value="0"<?=$selected?>>-- Välj Klubb --</option>
				
		<?
			
			
			foreach ($klubbar as $key => $value)
			{
				$selected = "";
				if ($value == $selectedClubName)
					$selected = " selected";
			?>
				<option value="<?=$value?>"<?=$selected?>><?=$value?></option>
			<?
			}
		 	?>
		</select>
		</td>
</tr>
</table>

<table border="0" width="100%">
<th>Placering</th>
<th>Klass</th>
<th>Skytt</th>
<th>Klubb</th>
<th>Total</th>
<?if(!isPrecision($comp->scoreType)) {?>
<th>Poäng</th>
<?}?>
<?if(isSport($comp->scoreType)) {?>
<th>Inner</th>
<?}else{?>
<th>Medalj</th>
<?}?>



		<?
			$col = "grey";
			$place = 0;
			$lTotal = 0;
			$lPoints = 0;
			
			
			$selected = "";
			if ($comp->id == 0)
				$selected = " selected";
		?>
		
		<?
			foreach ($list as $row)
			{
				$selected = "";
				$rt = 0 + $row["Total"];
				$rp = 0 + $row["Points"];
				
				if(isPrecision($comp->scoreType)) {
					$dtt = $rt;
					if(isSport($comp->scoreType)) {
						$rp = 0 + $row["Targets"];
					}
				} else {
					if ($comp->scoreType == "N")
						$dtt = $rt;
					else
						$dtt = $rt . "/" . $row["Targets"];
				}
				
				if ($lc != $row["ShotClassId"]) {
					$place = 1;	
				}
				else {
					if ( $rt < $lTotal || $rp < $lPoints || $lTotal == 0 ) {
						$place++;
					}
				}
				
				// Highlight the user's own results
				if ($row["ShotId"] == $shot->id)
					$col = "red";
					
				if ($row["ShotClass"] != $lastShotClass) {
					if ($lastShotClass != '') {
		?>
					<tr><td>&nbsp;</td></tr>
<th>Placering</th>
<th>Klass</th>
<th>Skytt</th>
<th>Klubb</th>
<th>Total</th>
<?if(!isPrecision($comp->scoreType)) {?>
<th>Poäng</th>
<?}?>
<?if(isSport($comp->scoreType)) {?>
<th>Inner</th>
<?}else{?>
<th>Medalj</th>
<?}?>
		<?
					}
					$lastShotClass = $row["ShotClass"];
					
				}
		?>
			<tr class="<?=$col?>">
				<td><?=$row["Place"]?></td>
				<td><?=$row["ShotClass"]?></td>
				<td><?=$row["ShotName"]?></td>
				<td><?=$row["ClubName"]?></td>
				<td><?=$dtt?></td>
				<?if(!isPrecision($comp->scoreType)) {?>
				<td><?=$rp?></td>
				<?}?>
				
				<?if(isSport($comp->scoreType)) {?>
				<td><?=$rp?></td>
				<?} else {?>
				<td><?=$row["Medal"]?></td>
				<?}?>
			</tr>
		<?
				if ($col == "grey")
					$col = "pink";
				else
					$col = "grey";

				$lTotal = $rt;
				$lPoints = $rp;
				$lc = $row["ShotClassId"];
					
			}
		?>
</table>

<br>

<table border="0" width="100%">

<tr>
	<td>
		<button onClick="javascript:pick();">Uppdatera</button>
	</td>
</tr>
<tr>
	<td>
		<a style="font-size:6pt;" href="showResult.php?projektor=true" target="_blank">Projektor anpassad visning</a>
	</td>
</tr>
</table>

</form>
</body>
</html>

