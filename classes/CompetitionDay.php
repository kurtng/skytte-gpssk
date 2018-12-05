<?php


class CompetitionDay {
	public $id = 0;
	public $competitionId = 0;
	public $dayNo = 0;
	public $firstStart = '';
	public $lastStart = '';
	public $patrolSpace = 0;
	public $maxStation = 0;

	
	// Clear out data in this object
	function clear()
	{
		$this->id = 0;
		$this->competitionId = 0;
		$this->dayNo = 0;
		$this->firstStart = '';
		$this->lastStart = '';
		$this->patrolSpace = 0;
		$this->maxStation = 0;
	}
	
	// Load the specified section from the db
	function load($pid)
	{
		global $debug;
		$dbh = getOpenedConnection();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}

		if ($pid == "")
			return;
					
		$sql = "
			select
		Id,
		CompetitionId,
		DayNo,
		FirstStart,
		LastStart,
		MaxStation,
		PatrolSpace
		from tbl_Pistol_CompetitionDay
		where Id = $pid;
		";

		$result = mysqli_query($dbh,$sql);
		if ($obj = mysqli_fetch_object($result))
		{
			$this->id = $obj->Id;
			$this->competitionId = $obj->CompetitionId;
			$this->dayNo = $obj->DayNo;
			$this->firstStart = $obj->FirstStart;
			$this->lastStart = $obj->LastStart;
			$this->maxStation = $obj->MaxStation;
			$this->patrolSpace = $obj->PatrolSpace;
			
		}

		mysqli_free_result($result);
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
			from tbl_Pistol_CompetitionDay
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
	function getList($compId)
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
			
		if ($compId == 0) {
			return;
		}
		
		$sql = "select Id, DayNo as Name
			from tbl_Pistol_CompetitionDay
			where CompetitionId = 0$compId
			order by DayNo
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
			$list[$obj->Id] = $obj->Name;
		}

		mysqli_free_result($result);

		return $list;
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
					
		if ($this->id == 0)
		{
			// Create new object
			$sql = "insert into tbl_Pistol_CompetitionDay(
					CompetitionId,
					DayNo,
					FirstStart,
					LastStart,
					MaxStation,
					PatrolSpace
				)
				values (
					$this->competitionId,
					$this->dayNo,
					'$this->firstStart',
					'$this->lastStart',
					$this->maxStation,
					$this->patrolSpace
				)
			";
			
			$msg = "Ny tävlingsdag skapad";
		}
		else
		{
			// Update existing item
		$sql = "update tbl_Pistol_CompetitionDay
			set CompetitionId = $this->competitionId,
					DayNo = $this->dayNo,
					FirstStart = '$this->firstStart',
					LastStart = '$this->lastStart',
					MaxStation = $this->maxStation,
					PatrolSpace = $this->patrolSpace
			where Id = $this->id;
		";
		
		$msg = "Tävlingsdag uppdaterad";
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

	// Generate a schedule for this day
	function genSchedule()
	{
		global $debug;
		global $msg;
		
		$ret = "FAILED";
		
		$dbh = getOpenedConnection();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
					
		// Create new object
		$sql = "call GenerateStarttider (0$this->id)
		";
		
		$msg = "Tidsschema skapat";
		if ($debug)
			print_r("SQL: " . $sql);
		
		$res = mysqli_query($dbh,$sql);
		if ($res != null) {
			while ($obj = mysqli_fetch_object($res))
			{
				$ret = $obj->Status;
			}
		}
		else {
			$msg .= mysqli_error($dbh);
			
		}
		if ($res != null)
			mysqli_free_result($res);
		
		return $ret;
	}
	
	// Return the schedule for this day
	function listSchedule($scoreType)
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
			
		$sql = "
			select c.Id, 
				c.Station,
				time_format(time(c.StartTime), '%H:%i') as StartTime,
				p.SortOrder as Patrol
			from tbl_Pistol_Schedule c
			join tbl_Pistol_Patrol p on (p.Id = c.PatrolId)
			where c.CompetitionDayId = 0$this->id
		";
		if(isPrecision($scoreType)){
			$sql.="	order by Patrol, c.Id;";
		}else{
			$sql.="	order by c.Id;";	
		}
		
		if ($debug)
			print_r($sql . "<br><br>");
		
		
		$result = mysqli_query($dbh,$sql);
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
			$row["Station"] = $obj->Station;
			$row["Patrol"] = $obj->Patrol;
			$row["StartTime"] = $obj->StartTime;
			
			$list[] = $row;
		}

		mysqli_free_result($result);

		return $list;
	}
	
	// Return score cards for this day
	function getGunClasses()
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

		
		//$sql = "select Id, Grade, Description from tbl_Pistol_GunClassification";
		$sql = "select Id, Grade, Description from tbl_Pistol_GunClassification 
				where ForScoreType LIKE
						CONCAT('%', 
							(select ScoreType from tbl_Pistol_Competition comp, tbl_Pistol_CompetitionDay cDay 
							where cDay.Id = $this->id and cDay.CompetitionId = comp.Id)
							, '%') ";
		
		if ($debug)
			print_r($sql . "<br><br>");
			
		$result = mysqli_query($dbh,$sql);
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
			$row["Grade"] = $obj->Grade;
			$row["Description"] = $obj->Description;
			
			$list[] = $row;
		}

		mysqli_free_result($result);

		return $list;
	}
	
// Return score cards for this day
	function getPatrols()
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

		$sql = "
			SELECT Id, SortOrder as Name, StartTime, Hidden
		 	FROM tbl_Pistol_Patrol 
		 	where CompetitionDayId = $this->id 
		 	order by SortOrder";
		
		if ($debug)
			print_r($sql . "<br><br>");
			
		$result = mysqli_query($dbh,$sql);
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
			$row["SortOrder"] = $obj->Name;
			$row["StartTime"] = $obj->StartTime;
			$row["Hidden"] = $obj->Hidden;
			$list[] = $row;
		}

		mysqli_free_result($result);

		return $list;
	}
	
	// Return score cards for this day
	function genPatrolChoices()
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
	
			
		$sql = "
			select p.Id, p.SortOrder as Name, pg.GunClassId
			from tbl_Pistol_Patrol p , tbl_Pistol_PatrolGun pg
			where p.CompetitionDayId = $this->id and pg.PatrolId = p.Id
			order by SortOrder, GunClassId
		";
		
		if ($debug)
			print_r($sql . "<br><br>");
			
		$result = mysqli_query($dbh,$sql);
		$rc = mysqli_affected_rows($dbh);
		
		if ($rc < 0)
		{
			$msg = mysqli_error($dbh);
			return 0;
		}
		
		while ($obj = mysqli_fetch_object($result))
		{
			unset($row);
			$row["PatrolId"] = $obj->Id;
			$row["SortOrder"] = $obj->Name;
			$row["GunClassId"] = $obj->GunClassId;
			
			$list[] = $row;
		}

		mysqli_free_result($result);

		return $list;
	}

	// Return score cards for this day
	function genScoreCards($patrolId = 0)
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
		
		$pat = "";
		if ($patrolId > 0)
			$pat = " and p.Id = $patrolId";
			
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
				e.Id as EntryId,
				time_format(time(p.StartTime), '%H:%i') as StartTime
			from tbl_Pistol_Patrol p
			join tbl_Pistol_Entry e on (e.PatrolId = p.Id)
			join tbl_Pistol_Shot s on (s.Id = e.ShotId)
			join tbl_Pistol_Club c on (c.Id = s.ClubId)
			join tbl_Pistol_GunClassification g on (g.Id = e.GunClassificationId)
			join tbl_Pistol_ShotClass sc on (sc.Id = e.ShotClassId)
			where p.CompetitionDayId = $this->id
			$pat
			order by p.SortOrder, e.StaPlats, s.LastName, s.FirstName
		";
		
		if ($debug)
			print_r($sql . "<br><br>");
			
		$result = mysqli_query($dbh,$sql);
		$rc = mysqli_affected_rows($dbh);
		
		if ($rc < 0)
		{
			$msg = mysqli_error($dbh);
			return 0;
		}
		
		while ($obj = mysqli_fetch_object($result))
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
			$row["StartTime"] = $obj->StartTime;
			$list[] = $row;
		}

		mysqli_free_result($result);

		return $list;
	}

	// Return start list for this day
	function genStartList()
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
		
		$pat = "";
		$paid = "and e.Status = 'P'";
		$paid = "";
			
		$sql = "
			select p.Id PatrolId,
				p.SortOrder,
				concat(s.FirstName, ' ', s.LastName) as ShotName,
				s.GunCard,
				c.Name as ClubName,
				e.Id as EntryId,
				g.Grade as GunClassName,
				sc.Name as ShotClassName,
				s.Id as ShotId,
				time_format( p.StartTime, '%H:%i') as FirstStart
			from tbl_Pistol_Patrol p
			join tbl_Pistol_Entry e on (e.PatrolId = p.Id $paid)
			join tbl_Pistol_Shot s on (s.Id = e.ShotId)
			join tbl_Pistol_Club c on (c.Id = s.ClubId)
			join tbl_Pistol_GunClassification g on (g.Id = e.GunClassificationId)
			join tbl_Pistol_ShotClass sc on (sc.Id = e.ShotClassId)
			where p.CompetitionDayId = $this->id
			$pat
			order by c.Name, p.SortOrder, s.LastName, s.FirstName
		";
		
		if ($debug)
			print_r($sql);
			
		$result = mysqli_query($dbh,$sql);
		$rc = mysqli_affected_rows($dbh);
		
		if ($rc < 0)
		{
			$msg = mysqli_error($dbh);
			return 0;
		}
		
		while ($obj = mysqli_fetch_object($result))
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
			$row["FirstStart"] = $obj->FirstStart;
			
			$list[] = $row;
		}

		mysqli_free_result($result);

		return $list;
	}

	// List available patrols
	function listAvailablePatrols($entryId)
	{
		global $debug;
		global $msg;
		
		// Get a separate connection
		$dbh = getDBHandle();
		$list = array();
		
		if ($dbh == null) {
			return;
		}
			
		$sql = "
			call ListAvailablePatrols(0$entryId, 0$this->id) 
		";
		
		if ($debug)
			print($sql);
			
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

	// List available patrols
	function listAvailability($compDayId, $gunClassId)
	{
		global $debug;
		global $msg;
		
		// Get a separate connection
		$dbh = getDBHandle();
		$list = array();
		
		if ($dbh == null) {
			return;
		}
			
		$sql = $this->ListAvailibltySQL($compDayId, $gunClassId);
		
		if ($debug)
			print($sql);
			
		if ($dbh->multi_query($sql))
		do {
			if ($result = $dbh->store_result()) {
				while ($obj = $result->fetch_object())
				{
					unset($row);
					$row["PatrolId"] = $obj->Id;
					$row["SortOrder"] = $obj->SortOrder;
					$row["FirstStart"] = $obj->FirstStart;
					//$row["LastStart"] = $obj->LastStart;
					$row["PatrolSize"] = $obj->PatrolSize;
					$row["Availability"] = $obj->Availability;

					$list[] = $row;
				}
				$result->close();
			}
		} while ($dbh->next_result());
			
		if ($debug)
			print($dbh->error);

		$dbh->close();
		return $list;
	}
	
	private function ListAvailibltySQL ($compDayId, $gunClassId) { 
		// Bättre göra med sql än stored procedures eftersom våran webbhotell accepterar inte sps
		$patrols =
		"(
			select 	distinct p.Id as PatrolId, 
					d.CompetitionId as CompetitionId,
				 	count(distinct e.Id) as Members, 
				 	p.StartTime as FirstStartTime,
				 	'' as LastStartTime
			from tbl_Pistol_CompetitionDay d
			join tbl_Pistol_Patrol p on (p.CompetitionDayId = d.Id)
			join tbl_Pistol_PatrolGun g on (g.PatrolId = p.Id and g.GunClassId = $gunClassId)
			left join tbl_Pistol_Entry e on (e.PatrolId = p.Id)
			where d.Id = $compDayId
			group by p.Id, d.CompetitionId
		)";
		
		return	
		"
			select t.PatrolId as Id,
				p.SortOrder,
				time_format(time(t.FirstStartTime), '%H:%i') as FirstStart,
				time_format(time(t.LastStartTime), '%H:%i') as LastStart,
				c.MaxPatrolSize as PatrolSize,
				c.MaxPatrolSize - t.Members as Availability
			from $patrols t
			join tbl_Pistol_Patrol p on (p.Id = t.PatrolId)
			join tbl_Pistol_Competition c on (c.Id = t.CompetitionId)
			order by t.FirstStartTime
		";
	}

	// List days a shot hasn't booked yet
	function listDaysToBook($compId, $shotId)
	{
		global $debug;
		// Get a separate connection
		$dbh = getDBHandle();
		$list = array();
		
		if ($dbh == null) {
			return;
		}
			
		$sql = "
			call ListDaysToBook(0$compId, 0$shotId) 
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

	
	// Delete the current object
	function delete()
	{
		global $debug;
		$dbh = getOpenedConnection();

		// Delete current item
		$sql = "delete from tbl_Pistol_CompetitionDay
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
}
?>