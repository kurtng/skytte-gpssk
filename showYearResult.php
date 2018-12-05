<?php
include_once "GunnarCore.php";
session_start();

	$debug = 0;
	$act=$_POST['myAction'];
	$msg = "";
	
	$compName=$_POST['compName'];
	$compYear=$_POST['compYear'];
		

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
	<td>År:</td><td><input name="compYear"/></td>
</tr>
<tr>
	<td>Namn som:</td><td><input name="compName"/></td>
</tr><tr>
	<td></td><td><input type="submit" value="Räkna"/></td>
</tr>

</table>

 <?
 if($compName != "" && intval($compYear) != 0)
	{
		$compIdList = $comp->loadByNameAndYear($compName, $compYear);
		foreach ($compIdList as $key => $value)
		{
			
			$cp = new Score();
			$compResultList[$key] = $cp->listResult($key, '');
			$comp = new Competition();
			$comp->load($key);
			
			$place = 0;
			$lc = 0;
			
			foreach ($compResultList[$key] as $row)
			{
				
				if ($lc != $row["ShotClassId"]) {
					$place = 1;	
				}
				else {
					//if ( $rt < $lTotal || $rp < $lPoints || $lTotal == 0 ) {
						$place++;
					//}
				}
				$lc = $row["ShotClassId"];
				
				$isExist = false;
				unset($newrow);
				$rt = 0 + $row["Total"];
				if(isPrecision($comp->scoreType)) {
					$dtt = $rt;
				} else {
					if ($comp->scoreType == "N")
						$dtt = $rt;
					else
						$dtt = $rt + $row["Targets"];
				}
				if(is_array($newlist)) {
					foreach($newlist as $keyNewList => $existingRow)
					{
						if($existingRow["ShotId"] == $row["ShotId"] && $existingRow["ShotClassId"] == $row["ShotClassId"]) {
							$isExist = true;
							
							$existingRow[$key] =  $place;
							$existingRow["Result".$key] = $dtt;
							
							$newlist[$keyNewList] = $existingRow;
							break;
						}
					}
				}
				if(!$isExist) {
					$newrow["ShotId"] = $row["ShotId"];
					$newrow["ShotName"] = $row["ShotName"];
					$newrow["ShotClassId"] = $row["ShotClassId"];
					$newrow["ShotClass"] = $row["ShotClass"];
					$newrow["GunClassName"] = $row["GunClassName"];
					$newrow["Result".$key] = $dtt;
					$newrow[$key] = $place;
					$newlist[] = $newrow;
				}
					
			}
		}
		
		
		$uniqueClasses = array();
		$uniqueGunClasses = array();
		
		foreach($newlist as $foundRow)
		{
			$i = 0;
			unset($resultsOfAll);
			unset($pointsOfAll);
			foreach ($compIdList as $key => $value)
			{
				if(array_key_exists($key, $foundRow)) {
					$resultsOfAll[$i] = $foundRow[$key];
					$pointsOfAll[$i] = $foundRow["Result".$key];
					$i++;
				}
			}
			
			if(!in_array($foundRow["ShotClass"], $uniqueClasses)) {
				$uniqueClasses[] = $foundRow["ShotClass"]; 
			}
			if(!in_array($foundRow["GunClassName"], $uniqueGunClasses)) {
				$uniqueGunClasses[] = $foundRow["GunClassName"]; 
			}
			
			if(count($resultsOfAll) < 4)
			{
				$newtotal[] = 0;
				$newpointstotal[] = 0;
			} else 
			{
				sort($resultsOfAll);
				$total = 0;
				for($j = 0 ; $j < 4 ; $j++)
				{
					$total += intval($resultsOfAll[$j]);
				}
				$newtotal[] = $total;
				
				rsort($pointsOfAll);
				$total = 0;
				for($j = 0 ; $j < 4 ; $j++)
				{
					$total += intval($pointsOfAll[$j]);
				}
				$newpointstotal[] = $total;
			}
			
			
		}
		
		
		$origlist = $newlist;
		array_multisort($newtotal, $newlist);
		
		?><table>
			<tr>
			<th></th><?
		foreach ($compIdList as $key => $value) {
			?><th><?=$value?></th><?
			
		}
		
		
		?><th>Total</th></tr><?
		foreach($uniqueClasses as $currentClass) {
			?><tr><th colspan="<?=count($compIdList)+2?>"><?=$currentClass?></th></tr><?
			$i=0;
			foreach($newlist as $foundRow)
			{
				if($currentClass == $foundRow["ShotClass"]) {
						
					if($newtotal[$i] != 0) {
						?><tr><th><?=$foundRow["ShotName"]?></th><?
						foreach ($compIdList as $key => $value)
						{
							?><td><?=$foundRow[$key]?></td><?
						}
						?><th><?=$newtotal[$i]?></th>
						</tr>
						
						<? 
					}
						
				}
				$i++;
			}
		}
			
		?></table><?
		
		
		$newlist = $origlist;
		array_multisort($newpointstotal, SORT_DESC, $newlist);
		?><br/><br/><?
		?><table>
			<tr>
			<th></th><?
		foreach ($compIdList as $key => $value) {
			?><th><?=$value?></th><?
			
		}
		
		
		?><th>Total</th></tr><?
		foreach($uniqueGunClasses as $currentClass) {
			?><tr><th colspan="<?=count($compIdList)+2?>"><?=$currentClass?></th></tr><?
			$i=0;
			foreach($newlist as $foundRow)
			{
				if($currentClass == $foundRow["GunClassName"]) {
					if($newpointstotal[$i] != 0) {
						?><tr><th><?=$foundRow["ShotName"]?></th><?
						foreach ($compIdList as $key => $value)
						{
							?><td><?=$foundRow["Result".$key]?></td><?
						}
						?><th><?=$newpointstotal[$i]?></th>
						</tr>
						
						<? 
					}
						
				}
				$i++;
			}
		}
			
		?></table><?
		
	}
 ?>
 
</form>
</body>
</html>

