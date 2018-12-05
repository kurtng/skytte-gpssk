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

//if (($shot->userType != "ADMIN") && ($shot->userType != "OPER")) {
//	http_redirect("notAllowed.php");
//}

$comp = new Competition();

$msg = "";

// Load up stuff?
if ($_POST["compId"] > 0) {
	
	$comp->load($_POST["compId"]);
	$_SESSION["competitionId"] = $comp->id;
}

switch ($act) {
	
	case "pick":
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

header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>Välj tävling</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css">

</head>
<script language="javascript">



  function pick()
  {
		document.forms[0].elements["myAction"].value = "pick";
		document.forms[0].action="showEmails.php";
		document.forms[0].submit();
  }
  
 


</script>

<body >
<div class="error"><?=$msg?></div>
<br>

<form method="POST"><input type="hidden" name="myAction">

<center>

	<table border="0" width="90%">
		<tr>
			<td align="right">Tävling:</td>
				<td>
				<?
					$cps = $comp->getList("between 3 and 4"); // Get competitions I can enter scores for
					$num = sizeof($cps);
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

	<br>

	
</center>

</form>
<textarea rows="100" cols="100">
<? 
if ($comp->id != 0) {
	$cList = $comp->listShotEmails();
						
	foreach ($cList as $cp) {
		?><?=$cp->Email?>;<? 
	}
}
?>
</textarea>
</body>
</html>
