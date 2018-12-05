<?php
include_once "GunnarCore.php";
header("Content-Type: text/html; charset=UTF-8");

$eshot = new Shot();
$cps = $eshot->getShooters();
$i = 0;
$len = count($cps);
?>{"shoots" : [<?
foreach ($cps as $key => $value)
{
	?>
	{"name":"<?=$value["ShotName"]?>","club":"<?=$value["ClubName"]?>"}
	<?if ($i != $len - 1) {
		?>,<?
	}
	$i++;
}
?>] }
		