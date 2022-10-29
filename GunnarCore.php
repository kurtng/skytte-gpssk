<?php
error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED & ~E_NOTICE);
include_once 'classes/Shot.php';
include_once 'classes/Club.php';
include_once 'classes/Competition.php';
include_once 'classes/CompetitionDay.php';
include_once 'classes/Entry.php';
include_once 'classes/Patrol.php';
include_once 'classes/Score.php';
include_once 'LoginCore.php';

$debug = 0;
$dbh = null;

if (! function_exists('session_is_registered')) {
	function session_is_registered($sessionvar)
	{
		if(isset($_SESSION[$sessionvar]) && !empty($_SESSION[$sessionvar])) {
			return true;
		}
		return false;
	}
}

function getOpenedConnection() {
	//After php 7.0 update it is not possible to save dbh in session anymore
	global $dbh;
	if($dbh->sqlstate == null)  {
		$dbh = null;
		$dbh = openDB();
		
	} else{} 
	return $dbh;
}

function openDB()
{
	//if (session_is_registered("dbh"))
	//	$dbh = $_SESSION["dbh"];

	if (!is_resource($dbh)) {
		$dbh = mysqli_connect(getServerName(), getLoginName(), getPassword());
		mysqli_select_db($dbh, getLoginName()) ;

		mysqli_query($dbh, "SET NAMES 'utf8'");
	}

	/* check connection */
	if (!$dbh) {
		printf("Database connection failed: %s\n", mysqli_error($dbh));
		exit();
	}
	mysqli_select_db($dbh, getLoginName());

	// Save away the connection
	//$_SESSION["dbh"] = $dbh;
	return $dbh;
}

// Close persistant connection
function closeDB()
{
	if (session_is_registered("dbh"))
	$dbh = getOpenedConnection();

	if (is_resource($dbh))
	mysqli_close($dbh);
}

// Open and return an extra db handle (for sprocs)
function getDBHandle()
{
	$dbh = new mysqli(getServerName(), getLoginName(), getPassword(), getLoginName());

	$dbh->multi_query("SET NAMES 'utf8'");

	/* check connection */
	if (mysqli_connect_errno()) {
		printf("Core:getDBHandle => Connection failed: %s\n", mysqli_connect_error());
		exit();
	}

	// Return the connection
	return $dbh;
}

function login($gunCard, $passwd)
{
	$dbh = getOpenedConnection();

	if ($dbh == null)
	openDB();

	// Let's see if the user exists
	$shot = new Shot();
	$shot->gunCard = $gunCard;
	$found = $shot->findByGunCard($gunCard);

	if ($found != 0) {
		// Check the status
		if ($shot->status == 'LOCKED') {
			$found = 0;
			return $found;
		}

		// Check the password
		$hpasswd = sha1($passwd);
		if ($hpasswd != $shot->password)
		$found = 0;
		else {
			$_SESSION["shotSession"] = serialize($shot);
			$shot->recordLogon();
		}
	}
	if ($found == 0)
	$shot->recordFailedLogon();

	return $found;
}


function http_redirect($url)
{
	header("Location: " . $url);
}

function safeSQL($in)
{
	$dbh = openDB();
	if (session_is_registered("dbh"))
	$dbh = getOpenedConnection();

	if ($dbh == null) {
		$dbh = openDB();
		if ($dbh == null)
		return str_replace("'", "\'", $in); // At least get rid of quotes
	}
	
	return mysqli_real_escape_string($dbh, $in);
}

function isPrecision($scoreType)
{
	if($scoreType == 'P') {
		return true;
	} else if($scoreType == 'S') {
		return true;
	} else if($scoreType == 'C') {
		return true;
	} else {
		return false;
	}
}
function isSport($scoreType)
{
	if($scoreType == 'S') {
		return true;
	} else {
		return false;
	}
}
?>
