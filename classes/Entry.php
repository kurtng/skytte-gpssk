<?php

include_once 'Team.php';
include_once 'Shot.php';

class Entry {
	public $id = 0;
	public $shotId = 0;
	public $bokadAvShotId = 0;
	public $gunClassificationId = 0;
	public $shotClassId = 0;
	public $patrolId = 0;
	public $payDate = '';
	public $status = 'U';
	public $teamId = 0;
	
	// Clear out data in this object
	function clear()
	{
		$this->id = 0;
		$this->shotId = 0;
		$this->gunClassificationId = 0;
		$this->shotClassId = 0;
		$this->status = 'U'; // Unpaid
		$this->patrolId = 0;
		$this->payDate = '';
		$this->teamId = 0;
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

		if ($pid == "")
			return;
			
		if ($pid <= 0) {
			return;
		}
					
		$sql = "select
				Id,
				ShotId,
				GunClassificationId,
				PatrolId,
				PayDate,
				ShotClassId,
				Status,
				TeamId
				from tbl_Pistol_Entry
				where Id = $pid
		";

		$result = mysqli_query($dbh,$sql);
		
		if ($obj = mysqli_fetch_object($result))
		{
			$this->id = $obj->Id;
			$this->shotId = $obj->ShotId;
			$this->gunClassificationId = $obj->GunClassificationId;
			$this->patrolId = $obj->PatrolId;
			$this->payDate = $obj->PayDate;
			$this->shotClassId = $obj->ShotClassId;
			$this->status = $obj->Status;
			$this->teamId = $obj->TeamId;
		}

		mysqli_free_result($result);
	}
	

function getTransactionsId($entryid)
	{
		global $debug;
		global $msg;
		$list = array();
		
		$dbh = getOpenedConnection();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}

		if ($entryid == "")
			return;
			
		if ($entryid <= 0) {
			return;
		}
					
		$sql = "select
				TransactionId
				from tbl_Pistol_Payment
				where EntryId = $entryid
		";

		
		$result = mysqli_query($dbh,$sql);
		
		while ($obj = mysqli_fetch_object($result))
		{
			$list[] = $obj;
		}

		mysqli_free_result($result);
		
		return $list;
	}
	
	function getStatusCode($entryid)
	{
		global $debug;
		global $msg;
		$list = array();
		
		$dbh = getOpenedConnection();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}

		if ($entryid == "")
			return;
			
		if ($entryid <= 0) {
			return;
		}
					
		$sql = "select
				StatusCode
				from tbl_Pistol_Payment
				where EntryId = $entryid
		";

		
		$result = mysqli_query($dbh,$sql);
		
		while ($obj = mysqli_fetch_object($result))
		{
			$list[] = $obj;
		}

		mysqli_free_result($result);
		
		return $list;
	}
	
function loadUnPayedStarts($shotId, $compid = 0) {
		global $debug;
		global $msg;
		$list = array();
		
		$dbh = getOpenedConnection();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}

		if ($shotId == "")
			return;
			
		if ($shotId <= 0) {
			return;
		}
					
		$sql = "select 	e.Id as EntryId, 
						g.Id as GunClassificationId, 
						s.Id as ShotClassId, 
						c.Name CompetitionName, 
						s.Name as ClassName,
						p.StartTime as PatrolStartTime, 
						p.SortOrder as PatrolNo,
						p.Id as PatrolId,
						c.Id CompId
				from  tbl_Pistol_Entry e, 
				      tbl_Pistol_Patrol p, 
				      tbl_Pistol_CompetitionDay d, 
				      tbl_Pistol_Competition c,
				      tbl_Pistol_GunClassification g, 
				      tbl_Pistol_ShotClass s
				where e.ShotId = $shotId and   
				      e.PatrolId = p.Id and 
				      e.Status != 'P' and 
				      d.Id = p.CompetitionDayId and 
				      d.CompetitionId = c.Id and
				      g.Id = e.GunClassificationId and 
				      e.ShotClassId = s.Id and
					  c.OnlineBetalning = 'Y' "; 
	
		if($compid != 0)
			$sql .= " and c.Id = $compid";

		$result = mysqli_query($dbh,$sql);
		
		while ($obj = mysqli_fetch_object($result))
		{
			$list[] = $obj;
		}

		mysqli_free_result($result);
		
		return $list;
		
	}
	
	function loadStartsForCompetition($shotId, $compid = 0) {
		global $debug;
		global $msg;
		$list = array();
		
		$dbh = getOpenedConnection();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}

		if ($shotId == "")
			return;
			
		if ($shotId <= 0) {
			return;
		}
					
		$sql = "select 	e.Id as EntryId, 
						g.Id as GunClassificationId, 
						s.Id as ShotClassId, 
						c.Name as CompetitionName, 
						s.Name as ClassName,
						p.StartTime as PatrolStartTime, 
						p.SortOrder as PatrolNo,
						p.Id as PatrolId,
						c.Id as CompId,
						e.Status as Status,
						pay.TransactionId as TransactionId
				from   
				      tbl_Pistol_Patrol p, 
				      tbl_Pistol_CompetitionDay d, 
				      tbl_Pistol_Competition c,
				      tbl_Pistol_GunClassification g, 
				      tbl_Pistol_ShotClass s,
				      tbl_Pistol_Entry e
				      left join tbl_Pistol_Payment pay on e.Id = pay.EntryId
				where e.ShotId = $shotId and   
				      e.PatrolId = p.Id and 
				      d.Id = p.CompetitionDayId and 
				      d.CompetitionId = c.Id and
				      g.Id = e.GunClassificationId and 
				      e.ShotClassId = s.Id  
				      "; 
		//				      e.Status != 'P' and 
		
	
		if($compid != 0)
			$sql .= " and c.Id = $compid";

		$result = mysqli_query($dbh,$sql);
		
		while ($obj = mysqli_fetch_object($result))
		{
			$list[] = $obj;
		}

		mysqli_free_result($result);
		
		return $list;
		
	}
	
	function setDibsPayment($transactId, $orderid, $gunCard, $amount, $statuscode, $approvalcode, $paytype ) {
		global $debug;
		global $msg;
		
		$ret = 1;
		
		$dbh = getOpenedConnection();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}

		$orderidarr = split("_", $orderid);
		$compId = $orderidarr[0];
		$shotId = $orderidarr[1];
		$date = $orderidarr[2];
		
		// Insert payment item
		$sql = "insert tbl_Pistol_Dibs_Payment (TransactionId, StatusCode, PayDate, OrderId, GunCard, Amount, ApprovalCode, PayType, CompetitionId, ShotId)
				values ('$transactId', $statuscode, now(), '$orderid', '$gunCard', $amount, '$approvalcode', '$paytype', $compId, $shotId)
		";

		if ($debug)
			print_r("<br>SQL: $sql<br>");
			
		$res = mysqli_query($dbh,$sql);

		$rc = mysqli_affected_rows($dbh);
		if ($debug)
			print_r("AFFECTED ROWS: " . $rc);
		
		if ($rc < 0) {
			// Something went wrong
			if ($debug) {
				print_r(mysqli_error($dbh));
				print_r("UPDATED ZERO ROWS");
			}
		}
		
		return $ret;
	}

	// Update entry status
	function setEntryStatus($newStatus, $transactId = '0', $statuscode = 0)
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

		$payDate = ", PayDate = null";
		if ($newStatus == 'P')
			$payDate = ", PayDate = now()";
		
		// Update existing item
		$sql = "update tbl_Pistol_Entry
			set Status = '$newStatus'
			$payDate
			where Id = $this->id
		";

		if ($debug)
			print_r("<br>SQL: $sql<br>");
			
		$msg = "Anmälan uppdaterad";

		$res = mysqli_query($dbh,$sql);

		$rc = mysqli_affected_rows($dbh);
		if ($debug)
			print_r("AFFECTED ROWS: " . $rc);
		
		if ($rc < 0) {
			// Something went wrong
			if ($debug) {
				print_r(mysqli_error($dbh));
				print_r("UPDATED ZERO ROWS");
			}
			$ret = 0;
		}
		else {
			$this->status = $newStatus;
		}
		
		// Insert payment item
		$sql2 = "insert tbl_Pistol_Payment (EntryId, TransactionId, StatusCode)
				values ($this->id, '$transactId', $statuscode)
		";

		if ($debug)
			print_r("<br>SQL: $sql<br>");
			
		$res2 = mysqli_query($dbh,$sql2, $dbh);

		$rc2 = mysqli_affected_rows($dbh);
		if ($debug)
			print_r("AFFECTED ROWS: " . $rc2);
		
		if ($rc2 < 0) {
			// Something went wrong
			if ($debug) {
				print_r(mysqli_error($dbh));
				print_r("UPDATED ZERO ROWS");
			}
		}
		
		return $ret;
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
			from tbl_Pistol_Entry
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

	// Find the specified object in the db
	function findByPatrol($patrolId, $shotId)
	{
		global $debug;
		$dbh = getOpenedConnection();
		
		if ($dbh == null)
			return;
			
		$sql = "
			select e.Id
			from tbl_Pistol_Entry e
			where e.ShotId = $shotId
			and e.PatrolId = $patrolId
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
	
	function findBySameGunClassificationId($patrolId, $otherShotId)
	{
		global $debug;
		$dbh = getOpenedConnection();
		
		if ($dbh == null)
			return;
			
		$sql = "
		select e.Id from tbl_Pistol_Entry e
			join tbl_Pistol_Patrol p on p.Id = e.PatrolId
			join tbl_Pistol_CompetitionDay cd on cd.Id = p.CompetitionDayId 
			join tbl_Pistol_Competition c on c.Id = cd.CompetitionId
			where 
			c.Id = (select c1.Id from tbl_Pistol_Patrol p1
					join tbl_Pistol_Entry e1 on p1.Id = e1.patrolId
					join tbl_Pistol_CompetitionDay cd1 on cd1.Id = p1.CompetitionDayId 
					join tbl_Pistol_Competition c1 on c1.Id = cd1.CompetitionId
					where e1.Id = $patrolId)
			and ShotId = $otherShotId
			and GunClassificationId = (select GunClassificationId
			from tbl_Pistol_Entry where Id = $patrolId)
		";
		
		if ($debug)
			print_r("SQL: " . $sql);
		
		$result = mysqli_query($dbh,$sql);
		if ($obj = mysqli_fetch_object($result))
		{
			$found = $obj->Id;
		}

		mysqli_free_result($result);
		if ($found != 0) {
			//$this->load($found);
		}
		
		return $found;
	}
	
	// Save this object to the db
	function save($otherEntryId = -1)
	{
		global $debug;
		global $msg;
		
		$dbh = getDBHandle(); // Open a new connection. Sprocs mess up existing db conns.
		$status = "OK";
		
		if (!$dbh) {
			if ($debug)
				print "Failed to open a new connection to database<br/>";
			return "FAILED to open connection.";
		}

		if ($this->id == 0)
		{
			// Register entry
			$sql = "call RegisterEntry (
				$this->patrolId, 
				$this->shotId,
				$this->gunClassificationId,
				$this->shotClassId,
				$this->bokadAvShotId
				)
				";
		}
		else if($otherEntryId == -1)
		{
			// Save changes to entry
			$sql = "update tbl_Pistol_Entry
				set Status = '$this->status',
					TeamId = $this->teamId
				where Id = $this->id
			";
		} else {
			$sql = "update tbl_Pistol_Entry
				set Status = '$this->status',
					TeamId = $this->teamId
				where Id = $otherEntryId
			";
		}
		
		if ($debug)
			print_r("SQL: " . $sql);
		
		if ($dbh->multi_query($sql))
		do {
			if ($result = $dbh->store_result()) {
				while ($obj = $result->fetch_object())
				{
					$status = $obj->Status;
					$this->id = $obj->EntryId;
					if ($debug)
						print_r("Setting status to: " . $obj->Status);
				}
				$result->close();
			}
		} while ($dbh->next_result());
		else {
			//$msg = $msg . $dbh->error;
			$status = $dbh->error;
		}
		
		if ($debug)
			print($dbh->error);

		if ($debug)
			print_r("Save entry returning: " . $status);
			
		$dbh->close(); // close extra connection required for this procedure
		return $status;
	}

	// Update shot class
	function changeClass($newClassId)
	{
		global $debug;
		global $msg;
		
		if ($newClassId < 1)
			return;
		
		$dbh = getOpenedConnection();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}

		// Register entry
		$sql = "update tbl_Pistol_Entry
			set ShotClassId = $newClassId 
			where Id = $this->id
			";

		if ($debug)
			print_r("SQL: " . $sql);
		
		mysqli_query($dbh,$sql);
		$this->shotClassId = $newClassId;
		
		if ($debug)
			print($dbh->error);
	}

	// Delete the current object
	function delete()
	{
		global $debug;
		global $msg;
		
		$status = "OK";
		
		$dbh = getOpenedConnection();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
		
		$sql = "select * from tbl_Pistol_Score where EntryId = 0$this->id";
		
		if ($debug) {
			print_r("SQL: " . $sql);
		}
		
		$result = mysqli_query($dbh,$sql);
		
		if ($result) {
			$rc = mysqli_num_rows(mysqli_query($dbh,$sql));
			if ($rc > 0){
				return "Det finns resultat för denna anmälan";
			}
		}
		
		$dbh = getDBHandle(); // Open a new connection. Sprocs mess up existing db conns.
		$status = "OK";
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
		
		// Cancel entry
		$sql = "call CancelEntry ($this->id)
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
			$msg = "Entry:delete => " . $dbh->error;
		}
		
		if ($debug)
			print($dbh->error);

		$this->clear(); // Clear current object
		return $status;
				
	}

	// List entires for specified shot
	function getList($shotId, $compId = 0)
	{
		global $debug;
		global $msg;
		
		$dbh = getOpenedConnection();
		$list = array();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}

		$compClause = "and c.Status between 1 and 3";
		if ($compId > 0)
			$compClause = " and c.Id = $compId";


		$sql = "select e.Id,
				c.Name as Competition, 
				'' as Shot,
				concat(g.Grade, ' - ', g.Description) as Gun,
				e.Status,
				time_format(pa.StartTime, '%H:%i') as FirstStart,
				d.DayNo,
				case 
					when t.Id is null then 0
					else t.Id
				end as TeamId,
				case
					when t.Name is null then 'Inget Lag'
					else t.Name
					end as TeamName,
				pa.SortOrder as PatrolNumber,
				e.ShotClassId,
				e.GunClassificationId,
				c.Status as CompStatus,
				e.PayDate as PayDate
			from tbl_Pistol_Entry e
			join tbl_Pistol_GunClassification g on (g.Id = e.GunClassificationId)
			join tbl_Pistol_Patrol pa on (pa.Id = e.PatrolId)
			join tbl_Pistol_CompetitionDay d on (d.Id = pa.CompetitionDayId)
			join tbl_Pistol_Competition c on (c.Id = d.CompetitionId $compClause)
			left join tbl_Pistol_Team t on (t.Id = e.TeamId)
			where e.ShotId = 0$shotId
			order by c.Id, d.DayNo, FirstStart, e.GunClassificationId
		";
			
		if ($shotId == 0) {
			$sql = "select e.Id, c.Name as Competition, concat(s.FirstName, ' ', s.LastName) as Shot,
					concat(g.Grade, ' - ', g.Description) as Gun,
					e.Status,  
					time_format(pa.StartTime, '%H:%i') as FirstStart,
					d.DayNo,
					pa.SortOrder as PatrolNumber,
					e.ShotClassId,
					e.GunClassificationId,
					c.Status as CompStatus,
					e.PayDate as PayDate
				from tbl_Pistol_Entry e
				join tbl_Pistol_Shot s on (s.Id = e.ShotId)
				join tbl_Pistol_GunClassification g on (g.Id = e.GunClassificationId)
				join tbl_Pistol_Patrol pa on (pa.Id = e.PatrolId)
				join tbl_Pistol_CompetitionDay d on (d.Id = pa.CompetitionDayId)
				join tbl_Pistol_Competition c on (c.Id = d.CompetitionId and c.Status between 1 and 3)
				order by c.Id, d.DayNo, s.LastName, FirstStart
			";
		}
		
		if ($debug)
			print $sql;

		$result = mysqli_query($dbh,$sql);
		
		if ($result == null) {
			$msg = mysqli_error($dbh);
			print $msg;
			return 0;
		}
			
		$rc = mysqli_affected_rows($dbh);
		if ($rc < 0)
		
		{
			$msg = mysqli_error($dbh);
			return 0;
		}
		
		while ($obj = mysqli_fetch_object($result))
		{
			unset($row);
			$row["Id"] = $obj->Id;
			$row["Competition"] = $obj->Competition;
			$row["Shot"] = $obj->Shot;
			$row["Gun"] = $obj->Gun;
			$row["Status"] = $obj->Status;
			$row["FirstStart"] = $obj->FirstStart;
			$row["DayNo"] = $obj->DayNo;
			$row["TeamId"] = $obj->TeamId;
			$row["TeamName"] = $obj->TeamName;
			$row["PatrolNumber"] = $obj->PatrolNumber;
			$row["ShotClassId"] = $obj->ShotClassId;
			$row["GunClassificationId"] = $obj->GunClassificationId;
			$row["CompStatus"] = $obj->CompStatus;
			$row["PayDate"] = $obj->PayDate;
			
			$list[] = $row;
		}

		mysqli_free_result($result);

		return $list;
	}

	// List entires for specified club
	function getClubEntries($clubId)
	{
		global $debug;
		global $msg;
		
		$dbh = getOpenedConnection();
		$list = array();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}


		$sql = "select e.Id, c.Name as Competition, 
			concat(s.FirstName, ' ', s.LastName) as Shot,
			concat(g.Grade, ' - ', g.Description) as Gun,
			e.Status,
			time_format(pa.StartTime, '%H:%i') as FirstStart,
			d.DayNo 
			from tbl_Pistol_Entry e
			join tbl_Pistol_GunClassification g on (g.Id = e.GunClassificationId)
			join tbl_Pistol_Patrol pa on (pa.Id = e.PatrolId)
			join tbl_Pistol_CompetitionDay d on (d.Id = pa.CompetitionDayId)
			join tbl_Pistol_Competition c on (c.Id = d.CompetitionId and c.Status between 1 and 3)
			join tbl_Pistol_Shot s on (s.Id = e.ShotId and s.ClubId = 0$clubId)
			order by c.Id, d.DayNo, s.LastName, FirstStart
		";
					
		if ($debug)
			print $sql;
		
		$result = mysqli_query($dbh,$sql);
		
		if ($result == null) {
			$msg = mysqli_error($dbh);
			print $msg;
			return 0;
		}
			
		$rc = mysqli_affected_rows($dbh);
		if ($rc < 0)
		
		{
			$msg = mysqli_error($dbh);
			return 0;
		}
		
		while ($obj = mysqli_fetch_object($result))
		{
			unset($row);
			$row["Id"] = $obj->Id;
			$row["Competition"] = $obj->Competition;
			$row["Shot"] = $obj->Shot;
			$row["Gun"] = $obj->Gun;
			$row["Status"] = $obj->Status;
			$row["FirstStart"] = $obj->FirstStart;
			$row["DayNo"] = $obj->DayNo;
			
			$list[] = $row;
		}

		mysqli_free_result($result);

		return $list;
	}

	// List entries that are unpaid
	function listUnpaid($compId)
	{
		global $debug;
		global $msg;
		
		$dbh = getOpenedConnection();
		$list = array();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}


		$sql = "select e.Id,
			s.GunCard,
			concat(s.FirstName, ' ', s.LastName) as Shot,
			concat(g.Grade, ' - ', g.Description) as Gun,
			e.Status,
			s.Id as ShotId,
			p.SortOrder as PatrolNumber,
			e.RegisterDate,
			concat(bokadAv.FirstName, ' ', bokadAv.LastName, ' ', bokadAv.GunCard) as BokadAvShot
			from tbl_Pistol_CompetitionDay d
			join tbl_Pistol_Patrol p on (p.CompetitionDayId = d.Id)
			join tbl_Pistol_Entry e on (e.PatrolId = p.Id and e.Status = 'U')
			join tbl_Pistol_Shot s on (s.Id = e.ShotId)
			join tbl_Pistol_GunClassification g on (g.Id = e.GunClassificationId)
			left join tbl_Pistol_Shot bokadAv on bokadAv.Id = e.BokadAvShotId
			where d.CompetitionId = 0$compId
			order by p.SortOrder, s.LastName, s.FirstName
		";
		
		if ($debug)
			print $sql;

		$result = mysqli_query($dbh,$sql);
		
		if ($result == null) {
			$msg = mysqli_error($dbh);
			print $msg;
			return 0;
		}
			
		$rc = mysqli_affected_rows($dbh);
		if ($rc < 0)
		{
			$msg = mysqli_error($dbh);
			print_r($msg);
			return 0;
		}
		
		while ($obj = mysqli_fetch_object($result))
		{
			unset($row);
			$row["Id"] = $obj->Id;
			$row["ShotId"] = $obj->ShotId;
			$row["Shot"] = $obj->Shot;
			$row["Gun"] = $obj->Gun;
			$row["Status"] = $obj->Status;
			$row["GunCard"] = $obj->GunCard;
			$row["PatrolNumber"] = $obj->PatrolNumber;
			$row["RegisterDate"] = $obj->RegisterDate;
			$row["BokadAvShot"] = $obj->BokadAvShot;
			$list[] = $row;
		}

		mysqli_free_result($result);

		return $list;
	}

	// List entires for specified shot
	function getPatrolId($compDayId)
	{
		global $debug;
		global $msg;
		
		return $this->patrolId;
	}

	// List the teams that belong to the same club as the shot owning this entry
	// and which have the same gun class as this entry. 
	function listClubTeams($compId = -1)
	{
		global $debug;
		global $msg;
		
		$dbh = getOpenedConnection();
		$list = array();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
		
		if($compId != -1){
				
			$sql = "select t.Id,
				t.Name
				from tbl_Pistol_Team t
				join tbl_Pistol_CompetitionDay cd on cd.Id = t.CompetitionDayId
				join tbl_Pistol_Competition c on cd.CompetitionId = c.Id
				where c.Id = $compId
				order by t.Name
			";
			
		} else {
	
			$sql = "select t.Id,
				t.Name
				from tbl_Pistol_Entry e
				join tbl_Pistol_Patrol p on (p.Id = e.PatrolId)
				join tbl_Pistol_Team t on (t.GunClassId = e.GunClassificationId
					and t.CompetitionDayId = p.CompetitionDayId)
				where e.Id = $this->id
				order by t.Name
			";
		}
		
		if ($debug)
			print $sql . "<br/>";

		$result = mysqli_query($dbh,$sql);
		
		if ($result == null) {
			$msg = mysqli_error($dbh);
			print $msg;
			return 0;
		}
			
		$rc = mysqli_affected_rows($dbh);
		if ($rc < 0)
		{
			$msg = mysqli_error($dbh);
			print_r($msg);
			return 0;
		}
		
		while ($obj = mysqli_fetch_object($result))
		{
			unset($row);
			$row["Id"] = $obj->Id;
			$row["Name"] = $obj->Name;
			
			$list[] = $row;
		}

		mysqli_free_result($result);

		return $list;
	}

	// List the members of a team.
	function listTeamMembers($tid)
	{
		global $debug;
		global $msg;
		
		$dbh = getOpenedConnection();
		$list = array();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}

		$sql = "select
				s.Id,
				s.FirstName,
				s.LastName,
				gc.Grade,
				gc.Description,
				sc.Name as ShotClassName
			from tbl_Pistol_Entry e
			join tbl_Pistol_Shot s on (s.Id = e.ShotId)
			join tbl_Pistol_GunClassification gc on (gc.Id = e.GunClassificationId)
			join tbl_Pistol_ShotClass sc on (sc.Id = e.ShotClassId)
			where e.TeamId = 0$tid
			order by s.LastName
		";
		
		if ($debug)
			print $sql . "<br/>";

		$result = mysqli_query($dbh,$sql);
		
		if ($result == null) {
			$msg = mysqli_error($dbh);
			print $msg;
			return 0;
		}
			
		$rc = mysqli_affected_rows($dbh);
		if ($rc < 0)
		{
			$msg = mysqli_error($dbh);
			print_r($msg);
			return 0;
		}
		
		while ($obj = mysqli_fetch_object($result))
		{
			unset($row);
			$row["Id"] = $obj->Id;
			$row["FirstName"] = $obj->FirstName;
			$row["LastName"] = $obj->LastName;
			$row["Grade"] = $obj->Grade;
			$row["Description"] = $obj->Description;
			$row["ShotClassName"] = $obj->ShotClassName;
			
			$list[] = $row;
		}

		mysqli_free_result($result);

		return $list;
	}
	function joinTeam($teamId)
	{
		$this->teamId = $teamId;
		return $this->save();
	}

	function leaveTeam()
	{
		$this->teamId = 0;
		return $this->save();
	}

	function startTeam($name)
	{
		global $shot;
		global $debug;
		global $msg;
		
		$dbh = getOpenedConnection();
		$list = array();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
				
		$sql = "insert into tbl_Pistol_Team (
				Name, 
				CompetitionDayId, 
				GunClassId, 
				ClubId
			)
			select
				'$name',
				p.CompetitionDayId,
				$this->gunClassificationId,
				$shot->clubId
			from tbl_Pistol_Patrol p
			where p.Id = $this->patrolId
		";
				
		if ($debug)
			print_r("SQL: " . $sql);
		
		$res = mysqli_query($dbh,$sql);

		$rc = mysqli_affected_rows($dbh);
		
		if ($debug)
			print_r("AFFECTED ROWS: " . $rc);
		
		if ($rc < 0) {
			// Something went wrong
			if ($debug) {
				print_r(mysqli_error($dbh));
				print_r("UPDATED ZERO ROWS");
			}
			$ret = 0;
		}
		else {
			$this->teamId = mysqli_insert_id($dbh);
			$this->save();
		}
		
		return $ret;
	}
	
	// List entries that are unpaid
	function getGunClassName()
	{
		global $debug;
		global $msg;
		
		$dbh = getOpenedConnection();
		$gn = "";
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}


		$sql = "select Grade,
				Description
			from tbl_Pistol_GunClassification
			where Id = $this->gunClassificationId
		";
		
		if ($debug)
			print $sql;

		$result = mysqli_query($dbh,$sql);
		
		if ($result == null) {
			$msg = mysqli_error($dbh);
			print $msg;
			return 0;
		}
			
		$rc = mysqli_affected_rows($dbh);
		if ($rc < 0)
		{
			$msg = mysqli_error($dbh);
			print_r($msg);
			return 0;
		}
		
		while ($obj = mysqli_fetch_object($result))
		{
			$gn = $obj->Grade . " - " . $obj->Description;
		}

		mysqli_free_result($result);

		return $gn;
	}
	
	// List entries that are unpaid
	function getShotClassName()
	{
		global $debug;
		global $msg;
		
		$dbh = getOpenedConnection();
		$gn = "";
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}


		$sql = "select Name
			from tbl_Pistol_ShotClass
			where Id = $this->shotClassId
		";
		
		if ($debug)
			print $sql;

		$result = mysqli_query($dbh,$sql);
		
		if ($result == null) {
			$msg = mysqli_error($dbh);
			print $msg;
			return 0;
		}
			
		$rc = mysqli_affected_rows($dbh);
		if ($rc < 0)
		{
			$msg = mysqli_error($dbh);
			print_r($msg);
			return 0;
		}
		
		while ($obj = mysqli_fetch_object($result))
		{
			$gn = $obj->Name;
		}

		mysqli_free_result($result);

		return $gn;
	}
	
}
?>