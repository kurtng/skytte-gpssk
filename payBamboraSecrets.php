<?php
$debug = 0;
$bamboraserverid = 1;
//1 prodution
//2 test

function getEncodedApiKey() {
	global $bamboraserverid;
	switch ($bamboraserverid) {
			case 1:
				return "***"; //PROD
				break;
			case 2:
				return "aGVCRnMxOUY4cENFR3RRMHlvVlU="; //TEST
				break;			
		}
}

function getSecretKey() {
	global $bamboraserverid;
	switch ($bamboraserverid) {
			case 1:
				return "**"; //PROD
				break;
			case 2:
				return "FjGqhjiEKVs4UIC1QfH56wPShDU4OUmpJmjWxu8j"; //TEST
				break;
		}
}
function getAccessKey() {
	global $bamboraserverid;
	switch ($bamboraserverid) {
			case 1:
				return "**"; //PROD
				break;
			case 2:
				return "heBFs19F8pCEGtQ0yoVU"; //TEST
				break;
		}
}

function getMerchantNumber() {
	global $bamboraserverid;
	switch ($bamboraserverid) {
			case 1:
				return "**"; //PROD
				break;
			case 2:
				return "T115840101"; //TEST
				break;
		}
}

function getMD5Key() {
	global $bamboraserverid;
	switch ($bamboraserverid) {
			case 1:
				return "**"; //PROD
				break;
			case 2:
				return "l8tIgR8tEC"; //TEST
				break;
		}
}

function getPublicKey() {
	global $bamboraserverid;
	switch ($bamboraserverid) {
			case 1:
				return "***"; //PROD
				break;
			case 2:
				return "heBFs19F8pCEGtQ0yoVU"; //TEST
				break;
		}
}

function getAuthHeader() {
	return base64_encode( getAccessKey() . "@" . getMerchantNumber() . ":" . getSecretKey() );
}

?>
