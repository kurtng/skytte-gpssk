<?php

class Shot
{
	public $id = 0;
	public $firstName = '';
	public $lastName = '';
	public $clubId = 0;
	public $gunCard = '';
	public $email = '';
	public $password = '';
	public $createDate = '';
	public $userType = 'USER';
	public $status = 'ACTIVE';

	// Clear out data in this object
	function clear()
	{
		$this->id = 0;
		$this->firstName = '';
		$this->lastName = '';
		$this->clubId = 0;
		$this->gunCard = '';
		$this->email = '';
		$this->password = '';
		$this->createDate = '';
		$this->userType = 'USER';
		$this->status = 'ACTIVE';
	}
	
	// Load the specified section from the db
	function load($pid)
	{
		global $debug;
		global $msg;
		
		$dbh = getOpenedConnection();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
		
		if ($pid == 0)
			return;
					
		$sql = "
			select Id,
				FirstName,
				LastName,
				ClubId,
				GunCard,
				Email,
				Password,
				UserType,
				Status,
				CreateDate
			from tbl_Pistol_Shot
			where Id = $pid
		";
		
		$result = mysqli_query($dbh,$sql);
		if ($obj = mysqli_fetch_object($result))
		{
			$this->id = $obj->Id;
			$this->firstName = $obj->FirstName;
			$this->lastName = $obj->LastName;
			$this->clubId = $obj->ClubId;
			$this->gunCard = $obj->GunCard;
			$this->email = $obj->Email;
			$this->userType = $obj->UserType;
			$this->status = $obj->Status;
			$this->password = $obj->Password;
			$this->createDate = $obj->CreateDate;
		}

		mysqli_free_result($result);
	}

	// Find the specified object in the db
	function findByGunCard($gunCard)
	{
		global $debug;
		$dbh = getOpenedConnection();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
					
					
		$sql = "
			select Id
			from tbl_Pistol_Shot
			where GunCard = '$gunCard'
		";
		
		$result = mysqli_query($dbh,$sql);
		if ($obj = mysqli_fetch_object($result))
		{
			$found = $obj->Id;
		}

		mysqli_free_result($result);
		if ($found != 0) {
			$this->load($found);
		}
		
		return $found;
	}

	// Save this object to the db
	function save()
	{
		global $debug;
		global $msg;
		
		if ($this->gunCard == "") {
			$ret = 0;
			$msg = "Pistolkortsnummer kan inte vara tomt.";
			return $ret;
		}
		if ($this->firstName == "" || $this->lastName == "") {
			$ret = 0;
			$msg = "F&ouml;r- och efternamn m&aring;ste fyllas i.";
			return $ret;
		}
		if ($this->email == "") {
			$ret = 0;
			$msg = "Vi kan inte kontakta dig utan e-post adress.";
			return $ret;
		}
		$pwdlen = strlen($this->password); 
		if ($pwdlen < 6) {
			if ($this->id != 0) {
				if ($pwdlen > 0) {
					$ret = 0;
					$msg = "Nytt lösenord måste vara minst 6 tecken.";
					return $ret;
				}
			}
			else {
				$ret = 0;
				$msg = "Lösenord måste vara minst 6 tecken.";
				return $ret;
			}
		}
		
		$dbh = getOpenedConnection();
		$ret = 1;
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
					
		if ($this->id == 0)
		{
			$pwd = sha1($this->password);
			
			// Create new object
			$sql = "
				insert into tbl_Pistol_Shot(
					FirstName,
					LastName,
					ClubId,
					GunCard,
					Email,
					Password,
					UserType,
					Status
				)
				values ('$this->firstName',
				'$this->lastName',
				$this->clubId,
				'$this->gunCard',
				'$this->email',
				'$pwd',
				'$this->userType',
				'$this->status'
				)
			";
		}
		else
		{
			// Update existing item
			$setPwd = "";
			if ($this->password != "") {
				$pwd = sha1($this->password);
				$setPwd = ", Password = '$pwd'";
			}
			
			$sql = "
				update tbl_Pistol_Shot
				set 
					FirstName = '$this->firstName',
					LastName = '$this->lastName',
					ClubId = $this->clubId,
					GunCard = '$this->gunCard',
					Email = '$this->email',
					UserType = '$this->userType',
					Status = '$this->status'
					$setPwd
				where Id = $this->id
			";
		}
		
		if ($debug)
			print_r("SQL: " . $sql);
		
		$res = mysqli_query($dbh,$sql);

		$rc = mysqli_affected_rows($dbh);
		$err = mysqli_error($dbh);
		
		if (($rc < 0) && ($this->id == 0)) {
			// Something went wrong when saving new shot
			$msg = $err;
			$ret = 0;
		}
		
		if ($debug)
			print_r("AFFECTED ROWS: " . $rc);
		
		if (($rc < 0) && ($this->id != 0)) {
			// Someone has removed the row from the database
			if ($debug) {
				print_r(mysqli_error($dbh));
				print_r("UPDATED ZERO ROWS");
			}
			$msg = mysqli_error($dbh);
			$ret = 0;
		}

		if ($this->id == 0) {
			// Retrieve the auto-generated id
			$this->id = mysqli_insert_id($dbh);
			if ($this->id == 0)
			{
				// It failed - get the error
				$msg = mysqli_error($dbh);
				$ret = 0;
			}
		}
		return $ret;
	}

	// Delete the current object
	function delete()
	{
		global $debug;
		$dbh = getOpenedConnection();

		// Delete current item
		$sql = "
			delete from tbl_Pistol_Shot
			where Id = " . $this->id;
		
		if ($debug)
			print_r("SQL: " . $sql);
			
		mysqli_query($dbh,$sql);
		if (mysqli_errno($dbh)!=0) {
			print_r(mysqli_error($dbh));
			return;
		}
		
		$this->clear(); // Clear current object

	}

	// Get a list of shot classes
	function getClassList($gunClassId = 0, $masterskap = 'N')
	{
		global $debug;
		$dbh = getOpenedConnection();
		$list = array();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
		
		if (!is_numeric($gunClassId))
			$gunClassId = 0;
		
		$gcw = "";
		if ($gunClassId > 0) {
			$gcw = " where GunClassificationId = $gunClassId";
		}
		
		if ($masterskap == 'Y') {
			$gcw = $gcw . " and Masterskap = 'Y'";
		}
		
		$sql = "
			select Id, Name
			from tbl_Pistol_ShotClass
			$gcw
			order by Id;
		";
		
		$result = mysqli_query($dbh,$sql);
		
		while ($obj = mysqli_fetch_object($result))
		{
			$list[$obj->Id] = $obj->Name;
		}

		mysqli_free_result($result);

		return $list;
	}

	function getCompetitionClassList($compId)
	{
		global $debug;
		$dbh = getOpenedConnection();
		$list = array();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
			
		$sql = "
			select distinct c.Id, c.Name
			from tbl_Pistol_ShotClass c
			join tbl_Pistol_CompetitionDay d on (d.CompetitionId = 0$compId)
			join tbl_Pistol_Patrol p on (p.CompetitionDayId = d.Id) 
			join tbl_Pistol_Entry e on (e.PatrolId = p.Id and e.ShotClassId = c.Id)
			order by c.Id;
		";
		
		$result = mysqli_query($dbh,$sql);
		
		print mysqli_error($dbh);
		
		while ($obj = mysqli_fetch_object($result))
		{
			$list[$obj->Id] = $obj->Name;
		}

		mysqli_free_result($result);

		return $list;
	}

	// Get a list of gun classifications
	function getGunClassList($compId = 0, $compDayId = 0)
	{
		global $debug;
		$dbh = getDBHandle();// getOpenedConnection();
		$list = array();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}	
		
		if($compId == 0) {
			if($compDayId == 0) {
				$sql = "
					select Id, concat(Grade, ' - ', Description) as Name
					from tbl_Pistol_GunClassification 
					order by Id;
				";	
			} else {
				$sql = "
					select Id, concat(Grade, ' - ', Description) as Name
					from tbl_Pistol_GunClassification 
					where ForScoreType LIKE
								CONCAT('%', 
									(select ScoreType from tbl_Pistol_Competition comp, tbl_Pistol_CompetitionDay cDay 
									where cDay.Id = $compDayId and cDay.CompetitionId = comp.Id)
									, '%') 
					order by Id;
				";	
			}
		} else {
			$sql = "
				select Id, concat(Grade, ' - ', Description) as Name
				from tbl_Pistol_GunClassification 
				where ForScoreType LIKE
							CONCAT('%', 
								(select ScoreType from tbl_Pistol_Competition comp 
								where comp.Id = $compId)
								, '%') 
				order by Id;
			";
		}
		
		if ($dbh->multi_query($sql))
		do {
			if ($result = $dbh->store_result()) {
				while ($obj = $result->fetch_object())
				{
					$list[$obj->Id] = $obj->Name;
				}
				$result->close();
			}
		} while ($dbh->next_result());

		return $list;
	}
	
	// Get a list of gun classifications
	function getGunClassShortList()
	{
		global $debug;
		$dbh = getDBHandle();// getOpenedConnection();
		$list = array();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
			
		$sql = "
			select Id, Grade as Name
			from tbl_Pistol_GunClassification
			order by Id;
		";
		
		if ($dbh->multi_query($sql))
		do {
			if ($result = $dbh->store_result()) {
				while ($obj = $result->fetch_object())
				{
					$list[$obj->Id] = $obj->Name;
				}
				$result->close();
			}
		} while ($dbh->next_result());

		return $list;
	}

	// Get a list of gun cards
function getGunCardList($forClubId = -1)
	{
		global $debug;
		$dbh = getOpenedConnection();
		$list = array();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}

		$sel  = "";
		if($forClubId != -1) {
			$sel = " where ClubId = $forClubId ";
		} 
		
		$sql = "
			select Id, concat(GunCard ,' (', FirstName, ' ', LastName, ')') Name 
			from tbl_Pistol_Shot
			$sel
			order by GunCard;
		";

		
		$result = mysqli_query($dbh,$sql);
		
		while ($obj = mysqli_fetch_object($result))
		{
			$list[$obj->Id] = $obj->Name;
		}

		mysqli_free_result($result);

		return $list;
	}
	
	function getShooters()
	{
		global $debug;
		$dbh = getOpenedConnection();
		$list = array();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}

		$sql = "
			select shot.Id, concat(shot.FirstName, ' ', shot.LastName) Name, club.Name ClubName
			from tbl_Pistol_Shot shot, tbl_Pistol_Club club
			where shot.ClubId = club.Id
			order by shot.FirstName, shot.LastName
			;
		";
		
		$result = mysqli_query($dbh,$sql);
		
		while ($obj = mysqli_fetch_object($result))
		{
			unset($row);
			$row["Id"] = $obj->Id;
			$row["ShotName"] = $obj->Name;			
			$row["ClubName"] = $obj->ClubName;			
			$list[] = $row;
		}

		mysqli_free_result($result);

		return $list;
	}

	// Log a successful login event
	function recordLogon()
	{
		global $debug;
		$dbh = getOpenedConnection();

		// Delete current item
		$sql = "
			insert into tbl_Pistol_Logons (
				ShotId
			)
			values (
				$this->id
			)
			";
		
		if ($debug)
			print_r("SQL: " . $sql);
			
		mysqli_query($dbh,$sql);
		if (mysqli_errno($dbh)!=0) {
			print_r(mysqli_error($dbh));
			return;
		}
	}

	// Log a failed login attempt
	function recordFailedLogon()
	{
		global $debug;
		$dbh = getOpenedConnection();

		// Delete current item
		$sql = "
			insert into tbl_Pistol_FailedLogons (
				GunCard
			)
			values (
				'$this->gunCard'
			)
			";
		
		if ($debug)
			print_r("SQL: " . $sql);
			
		mysqli_query($dbh,$sql);
		if (mysqli_errno($dbh)!=0) {
			print_r(mysqli_error($dbh));
			return;
		}
	}
	
	// Reset password
	function resetPassword()
	{
		global $debug;
		global $msg;
		
		$dbh = getDBHandle(); // Open a new connection. Sprocs mess up existing db conns.
		$status = "FAILED";
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}

		/*
		 * Generate a new password
		 */
		$newPassword = "";
		
		// 8 characters
		for ($i=0; $i<8; $i++) {
			$c = chr(rand(65,90));
			$newPassword .= $c;
		}
		
		// Now hash the password
		$shaPassword = sha1($newPassword);
		
		// Reset password
		$sql = "call ResetPassword (
			'$this->email',
			'$this->gunCard',
			'$shaPassword'
			)
			";
		
		if ($debug)
			print_r("SQL: " . $sql);
		
		if ($dbh->multi_query($sql))
		do {
			if ($result = $dbh->store_result()) {
				while ($obj = $result->fetch_object())
				{
					$status = $obj->Status;
				}
				$result->close();
			}
		} while ($dbh->next_result());
		else {
			$msg = $dbh->error;
		}
		
		if ($debug)
			print($dbh->error);

		$dbh->close();
		
		if ($status == "OK") {
			// Send a notification mail
			$body = "Ditt nya lösenord är: " . $newPassword;
			$headers = "Content-type: text/html; charset=UTF-8\r\n" .
				"X-Priority: 1\r\n" . "From: skytteprogram@okrets.se\r\n";
			$ok = mail($this->email, "Skytte - Ditt nya lösenord", $body, $headers);
			if (!$ok)
				$status = "MAILFAIL";
		}
		return $status;
	}	
	
	function sendMailOmBetalning($email, $tavlingNamn, $vapen, $klass, $patrulno, $startTime, $varde, $orderid, $transactionsid, $extra = '') {
		$status = "OK";
			// Send a notification mail
			$body = "Du har betalat följande start(er) <br/>";
			$body = $body . "$tavlingNamn  <br/><br/>";
			
			$vapen = explode("<br/>", $vapen);
			$klass = explode("<br/>", $klass);
			$patrulno = explode("<br/>", $patrulno);
			$startTime = explode("<br/>", $startTime);
			
			$antalstart = 0;
			for($i=0 ; $i<sizeof($vapen) ; $i++ ) {
				if(trim($vapen[$i]) != "") {
					$antalstart++;
					$body = $body . "Vapen: $vapen[$i] <br/>";
					$body = $body . "Klass: $klass[$i] <br/>";
					$body = $body . "Patrul: $patrulno[$i] <br/>";
					$body = $body . "Start: $startTime[$i] <br/><br/>";
				}
			}
			
			$body = $body . "Pris: $varde <br/>";
			$body = $body . "Orderid: $orderid <br/>";
			$body = $body . "Transactionsid: $transactionsid <br/>";
			$body = $body . $extra;
			$headers = "Content-type: text/html; charset=UTF-8\r\n" . "From: skytteprogram@okrets.se\r\n";
			$ok = mail($email, "Skytte - Du har betalat $antalstart start(er)", $body, $headers);
			if (!$ok)
				$status = "MAILFAIL";
		return $status;
	}
	
	function sendMailOmBetalningPaminnelse($email, $tavlingNamn, $vapen, $patrulno, $betalningsTid ) {
		$status = "OK";
			// Send a notification mail
			$body = "Du har en obetald start <br/>";
			$body = $body . "$tavlingNamn  <br/>";
			$body = $body . "Vapen: $vapen <br/>";
			$body = $body . "Patrul: $patrulno <br/>";
			$body = $body . "Bokningstid: $betalningsTid <br/><br/>";
			$body = $body . "Du kan betala genom att trycka på Betala knappen på Översikts sidan efter att du har loggat in på http://www.okrets.se/skytte <br/>";
			$body = $body . "Du garanteras inte den bokade platsen om du inte betalar innan tävlingsdagen<br/><br/>";
			$body = $body . "Som alla andra datasystem kan det vara fel på våran betalningsfunktion. ";
			$body = $body . "Om du har betalat för denna start vad god kontakta sekreteriatet. Referera till betalningsmejl du fick i samband med betalningen.<br/>";
			$headers = "Content-type: text/html; charset=UTF-8\r\n" .
				"X-Priority: 1\r\n" . "From: skytteprogram@okrets.se\r\n";
			$ok = mail($email, "Skytte - Du har en obetald start", $body, $headers);
			if (!$ok)
				$status = "MAILFAIL";
		return $status;
	}

	// Save this object to the db
	function setPassword($newPassword)
	{
		global $debug;
		global $msg;
		
		$pwdlen = strlen($newPassword); 
		if ($pwdlen < 6) {
				$ret = 0;
				$msg = "Nytt lösenord måste vara minst 6 tecken.";
				return $ret;
		}
		
		$dbh = getOpenedConnection();
		$ret = 1;
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
					
		if ($this->id == 0)
		{
			$ret = 0;
			$msg = "NOT_FOUND.";
			return $ret;
		}
					
		$pwd = sha1($newPassword);
			
		$sql = "
			update tbl_Pistol_Shot
			set Password = '$pwd'
			where Id = $this->id
			";
		
		if ($debug)
			print_r("SQL: " . $sql);
		
		$res = mysqli_query($dbh,$sql);

		$rc = mysqli_affected_rows($dbh);
		$err = mysqli_error($dbh);
		
		if (($rc < 0) && ($this->id == 0)) {
			// Something went wrong
			$msg = $err;
			$ret = 0;
		}
		
		if ($debug)
			print_r("AFFECTED ROWS: " . $rc);
		
		if (($rc < 0)) {
			// Someone has removed the row from the database
			// We'll need to insert a new row.
			if ($debug) {
				print_r(mysqli_error($dbh));
				print_r("UPDATED ZERO ROWS");
			}
			$msg = "FAILED";
			$ret = 0;
		}

		return $ret;
	}

}

?>
