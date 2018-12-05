<?php

class Competition {
	public $id = 0;
	public $name = '';
	public $startDate = '';
	public $endDate = '';
	public $location = '';
	public $hostClubId = 0;
	public $maxPatrolSize = 0;
	public $scoreType = '';
	public $masterskap = '';
	public $onlineBetalning = '';
	public $status = 0;
	
	//For extra sjuktningar sär
	public $numberOfExtraShots = 0;
	public $gunClassificationId = 0;

	
	// Clear out data in this object
	function clear()
	{
		$this->id = 0;
		$this->name = '';
		$this->startDate = '';
		$this->endDate = '';
		$this->location = '';
		$this->hostClubId = 0;
		$this->maxPatrolSize = 0;
		$this->scoreType = '';
		$this->masterskap = '';
		$this->onlineBetalning = '';
		$this->status = 0;
	}
	
	function loadByNameAndYear($compName, $compYear)
	{
		global $debug;
		$dbh = getOpenedConnection();
		$list = array();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
			
		if ($status != "") {
			if (is_numeric($status)) {
				$status = " = " . $status;
			}
		}
		$sql = "
			SELECT c.Id,
				c.Name
			FROM tbl_Pistol_Competition c
			WHERE c.Name like '%$compName%'
			AND c.StartDate like '%$compYear%'
			";

		$result = mysqli_query($dbh,$sql);
		
		while ($obj = mysqli_fetch_object($result))
		{
			$list[$obj->Id] = utf8_encode($obj->Name);		
		}

		mysqli_free_result($result);

		return $list;
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
					
		$sql = "
			select
			Id,
			Name,
			StartDate,
			EndDate,
			Location,
			HostClubId,
			MaxPatrolSize,
			ScoreType,
			Status,
			Masterskap,
			OnlineBetalning
			from tbl_Pistol_Competition
			where Id = $pid;
		";
					
		$result = mysqli_query($dbh,$sql);
		$rc = mysqli_affected_rows($dbh);
		
		if ($rc >= 0)
		{
			if ($obj = mysqli_fetch_object($result))
			{
				$this->id = $obj->Id;
				$this->name = $obj->Name;
				$this->startDate = $obj->StartDate;
				$this->endDate = $obj->EndDate;
				$this->location = $obj->Location;
				$this->hostClubId = $obj->HostClubId;
				$this->maxPatrolSize = $obj->MaxPatrolSize;
				$this->scoreType = $obj->ScoreType;
				$this->masterskap = $obj->Masterskap;
				$this->onlineBetalning = $obj->OnlineBetalning;
				$this->status = $obj->Status;
			}
	
			mysqli_free_result($result);
		}
	}
	
	function getDibsPayments($compid, $shotid) {
		global $debug;
		$dbh = getOpenedConnection();
		$list = array();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
			
		$sql = "
			SELECT p.TransactionId,
				p.StatusCode,
				p.PayDate,
				p.OrderId,
				p.GunCard,
				p.Amount,
				p.ApprovalCode,
				p.PayType,
				p.CompetitionId,
				p.ShotId
			FROM tbl_Pistol_Dibs_Payment p
			WHERE p.CompetitionId = $compid and p.ShotId = $shotid
			";
		
		if ($debug)
			print "$sql<br/>";	
		
		$result = mysqli_query($dbh,$sql);
		
		while ($obj = mysqli_fetch_object($result))
		{
			$list[] = $obj;
		}

		mysqli_free_result($result);

		return $list;
	}

	// Find the specified object in the db
	function findByName($name)
	{
		global $debug;
		$dbh = getOpenedConnection();
		
		if ($dbh == null)
			return;
			
		$sql = "
			select Id
			from tbl_Pistol_Competition
			where Name like '$this->Name'
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

	// List competitions by start date
	function listByStartDate()
	{
		global $debug;
		$dbh = getOpenedConnection();
		$list = array();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
			
		if ($status != "") {
			if (is_numeric($status)) {
				$status = " = " . $status;
			}
		}
		$sql = "
			SELECT c.Id,
				c.Name,
				c.StartDate,
				c.Location,
				c.Status,
				l.Name HostClub,
				CASE
					WHEN c.StartDate < cast(now() as date) THEN 'EXPIRED'
					ELSE 'OK'
				END Expiry
			FROM tbl_Pistol_Competition c
			JOIN tbl_Pistol_Club l ON (l.Id = c.HostClubId)
			WHERE c.Status < 5
			ORDER BY c.StartDate DESC
			";

		$result = mysqli_query($dbh,$sql);
		
		while ($obj = mysqli_fetch_object($result))
		{
			$list[] = $obj;
		}

		mysqli_free_result($result);

		return $list;
	}
	
	function listShotEmails()
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
			SELECT s.Email 
			FROM tbl_Pistol_Competition c, 
				 tbl_Pistol_Patrol p, 
				 tbl_Pistol_Entry e, 
				 tbl_Pistol_CompetitionDay cd, 
				 tbl_Pistol_Shot s 
			WHERE c.Id = $this->id and 
				  c.Id = cd.CompetitionId and 
				  cd.Id = p.CompetitionDayId and 
				  e.PatrolId = p.Id and 
				  e.ShotId = s.Id
			";

		$result = mysqli_query($dbh,$sql);
		
		while ($obj = mysqli_fetch_object($result))
		{
			$list[] = $obj;
		}

		mysqli_free_result($result);

		return $list;
	}
	
	// Find the specified object in the db
	function getList($status = "")
	{
		global $debug;
		//$dbh = getOpenedConnection();
		$list = array();
		$dbh = getDBHandle();
		if ($dbh == null) {
			//$dbh = openDB();
			if ($dbh == null)
				return;
		}
			
		if ($status != "") {
			if (is_numeric($status)) {
				$status = " = " . $status;
			}
		}
		$sql = "
			select Id, concat(Name, ' (', StartDate, ')') as Name
			from tbl_Pistol_Competition";
		if ($status != "") {
			$sql .= "
			where Status $status";
		}
		else {
			$sql .= "
			where Status < 5 ";
		}
		if ($status == "4") {
			$sql .= "
				order by StartDate desc, Name
			";
		}
		else {
			$sql .= "
				order by Name, StartDate
			";
		}		
		
		if ($debug)
			print "$sql<br/>";	
			
		
			
		//$result = mysqli_query($dbh,$sql);
		
		if ($debug)
			print($dbh->error);
		
		if ($dbh->multi_query($sql))
		do {
			if ($result = $dbh->store_result()) {
				while ($obj = $result->fetch_object())
				{
					$list[$obj->Id] = utf8_encode($obj->Name);
				}
				$result->close();
			}
		} while ($dbh->next_result());
			
			
		/*while ($obj = mysqli_fetch_object($result))
		{
			$list[$obj->Id] = utf8_encode($obj->Name);
		}*/

		//mysqli_free_result($result);

		return $list;
	}

	// List open competitions
	function listCompetitionsICanEnter($shotId, $gunClassId)
	{
		global $debug;
		// Get a separate connection
		$dbh = getDBHandle();
		$list = array();
		
		if ($dbh == null) {
			return;
		}
			
		$sql = "
			call ListCompICanEnter($shotId, $gunClassId) 
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

		if ($debug)
			print($dbh->error);

		$dbh->close();
		return $list;
	}

	// Find the first competitionday and make it the session one
	function selectFirstCompDay($compId)
	{
		global $debug;
		$dbh = getOpenedConnection();
		
		if ($dbh == null)
			return;
			
		$sql = "
			select Id, DayNo
			from tbl_Pistol_CompetitionDay
			where CompetitionId = 0$compId
			group by CompetitionId
			having DayNo = min(DayNo)
		";

		$result = mysqli_query($dbh,$sql);
		if ($obj = mysqli_fetch_object($result))
		{
			$_SESSION["compDayId"] = $obj->Id; 
			$_SESSION["competitionDayId"] = $obj->Id;
			$_SESSION["competitionDayNo"] = $obj->dayNo;
		}

		mysqli_free_result($result);
	}

	
	// Save this object to the db
	function save()
	{
		global $debug;
		global $msg;
		
		$ret = 1;
		
		$dbh = getOpenedConnection();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}

		if ($this->endDate == '')
			$this->endDate = $this->startDate;
			
		if ($this->id == 0)
		{
			// Create new object
			$sql = "insert into tbl_Pistol_Competition(
				Name,
				StartDate,
				EndDate,
				Location,
				HostClubId,
				MaxPatrolSize,
				ScoreType,
				Status,
				OnlineBetalning,
				Masterskap
				)
				values (
				'$this->name',
				'$this->startDate',
				'$this->endDate',
				'$this->location',
				$this->hostClubId,
				$this->maxPatrolSize,
				'$this->scoreType',
				$this->status,
				'$this->onlineBetalning',
				'$this->masterskap'
				)
			";
			$msg = "Ny tävling skapad";
		}
		else
		{
			// Update existing item
			$sql = "update tbl_Pistol_Competition
				set Name = '$this->name',
						StartDate = '$this->startDate',
						EndDate = '$this->endDate',
						Location = '$this->location',
						HostClubId = $this->hostClubId,
						MaxPatrolSize = $this->maxPatrolSize,
						ScoreType = '$this->scoreType',
						Status = $this->status,
						Masterskap = '$this->masterskap',
						OnlineBetalning = '$this->onlineBetalning'
			where Id = $this->id;
			";
			$msg = "Tävlingen uppdaterad";
		}
		if ($debug)
			print_r("SQL: " . $sql);
		
		$res = mysqli_query($dbh,$sql);

		$rc = mysqli_affected_rows($dbh);
		if ($debug)
			print_r("AFFECTED ROWS: " . $rc);
		
		if (($rc < 0) && ($this->id != 0)) {
			// Someone has removed the row from the database
			// We'll need to insert a new row.
			if ($debug) {
				print_r(mysqli_error($dbh));
				print_r("UPDATED ZERO ROWS");
			}
			
			$this->id = 0;
			$this->save();
		}

		if ($this->id == 0) {
			// Retrieve the auto-generated id
			if ($rc >= 0)
				$this->id = mysqli_insert_id($dbh);
				
			if ($this->id == 0 || $rc < 0)
			{
				// It failed - get the error
				$msg = mysqli_error($dbh);
				if ($debug)
					print $sql;
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
		$sql = "delete from tbl_Pistol_Competition
			where Id = $pid;
		";

		if ($debug)
			print_r("SQL: " . $sql);
			
		mysqli_query($dbh,$sql);
		if (mysqli_errno($dbh)!=0) {
			print_r(mysqli_error($dbh));
			return;
		}
		
		$this->clear(); // Clear current object

	}
	
	function genExtraScoreCards()
	{
		global $debug;
		global $msg;
		
		$dbh = getDBHandle();//getOpenedConnection();
		$list = array();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
		
		
		$sql = "
			call ListResult($this->id, 0, '$verbose', $this->gunClassificationId, true) 
		";
		/*	
		$sql = "
			select p.Id PatrolId,
				p.SortOrder,
				e.Status as EntryStatus,
				concat(s.FirstName, ' ', s.LastName) as ShotName,
				s.GunCard,
				c.Name as ClubName,
				g.Grade as GunClassName,
				sc.Name as ShotClassName,
				s.Id as ShotId,
				e.Id as EntryId
			from tbl_Pistol_Competition comp
			join tbl_Pistol_CompetitionDay compday on (comp.id = compday.CompetitionId)
			join tbl_Pistol_Patrol p on (compday.id = p.CompetitionDayId ) 
			join tbl_Pistol_Entry e on (e.PatrolId = p.Id)
			join tbl_Pistol_Shot s on (s.Id = e.ShotId)
			join tbl_Pistol_Club c on (c.Id = s.ClubId)
			join tbl_Pistol_GunClassification g on (g.Id = e.GunClassificationId)
			join tbl_Pistol_ShotClass sc on (sc.Id = e.ShotClassId)
			where comp.Id = $this->id and e.GunClassificationId = $this->gunClassificationId
			order by p.SortOrder, s.LastName, s.FirstName
		";*/		
		
		if ($debug)
			print_r($sql . "<br><br>");
			
			
	
		if ($dbh->multi_query($sql)) {
			$rc = mysqli_affected_rows($dbh);
			
			if ($rc < 0)
			{
				$msg = mysqli_error($dbh);
				return 0;
			}
			
			do {
				if ($result = $dbh->store_result()) {
					while ($obj = $result->fetch_object())
					{
						unset($row);
						$row["PatrolId"] = $obj->PatrolId;
						$row["SortOrder"] = $obj->SortOrder;
						$row["ShotName"] = $obj->ShotName;
						$row["GunCard"] = $obj->GunCard;
						$row["ClubName"] = $obj->ClubName;
						$row["ShotClassName"] = $obj->ShotClassName;
						$row["GunClassName"] = $obj->GunClassName;
						$row["ShotId"] = $obj->ShotId;
						$row["EntryId"] = $obj->EntryId;
						$row["EntryStatus"] = $obj->EntryStatus;
						$row["Total"] = $obj->Total;
						
						$list[] = $row;
					}
					$result->close();
				}
			} while ($dbh->next_result());
		}
		else {
			$msg = $dbh->error;
		}
		
		$result = mysqli_query($dbh,$sql);

		return $list;
	}
}
?>