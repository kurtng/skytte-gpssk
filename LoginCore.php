<?php


$debug = 0;

$serverid = 2;
//1 skyskol
//2 localhost
//3 okrets
//4 pistolsm2011

function getServerName() {
	global $serverid;
	switch ($serverid) {
			case 1:
				return "skyskol.com.mysql";
				break;
			case 2:
				return "127.0.0.1";
				break;
			case 3:
				return "okrets.se.mysql";
				break;
			case 4:
				return "pistolsm2011.se.mysql";
				break;
		}
}

function getLoginName() {
	global $serverid;
	switch ($serverid) {
			case 1:
				return "skyskol_com";
				break;
			case 2:
				return "gokhan";
				break;
			case 3:
				return "okrets_se";
				break;
			case 4:
				return "pistolsm2011_se";
				break;
		}
}
function getPassword() {
	global $serverid;
	switch ($serverid) {
			case 1:
				return "X";
				break;
			case 2:
				return "X";
				break;
			case 3:
				return "X";
				break;
			case 4:
				return "X";
				break;
		}
}

?>
