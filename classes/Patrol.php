<?php

class Patrol {
	public $id = 0;
	public $competitionDayId = 0;
	public $sortOrder = 1;
	public $description = '';
	public $startTime = '';
	public $hidden = false;

	public $gunList = array();
	
	// Clear out data in this object
	function clear()
	{
		$this->id = 0;
		$this->competitionDayId = 0;
		$this->sortOrder = 1;
		$this->description = '';
		$this->startTime = '';
		$this->hidden = false;
		
		$this->gunList = array();
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
			
					
		$sql = "
			select
				Id,
				CompetitionDayId,
				SortOrder,
				Description,
				StartTime,
				Hidden
			from tbl_Pistol_Patrol
			where Id = $pid;
		";
		
		$result = mysqli_query($dbh,$sql);
		if ($obj = mysqli_fetch_object($result))
		{
			$this->id = $obj->Id;
			$this->competitionDayId = $obj->CompetitionDayId;
			$this->sortOrder = $obj->SortOrder;
			$this->description = $obj->Description;
			$this->startTime = $obj->StartTime;
			$this->hidden = $obj->Hidden;
		}

		mysqli_free_result($result);
		
		// Also load the list of allowed guns
		$this->getAllowedGuns();
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
	function getList($compDayId)
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
			
		if ($compDayId == 0) {
			return;
		}
		
		$sql = "
			select Id, SortOrder as Name
			from tbl_Pistol_Patrol
			where CompetitionDayId = 0$compDayId
			order by SortOrder;
		";
		
		$result = mysqli_query($dbh,$sql);
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

	// List the members of this patrol
	function listMembers($pid = "")
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
		
		if ($pid == "")
			$pid = $this->id;
			
		$sql = "
			select s.Id, 
				e.Id as EntryId,
				s.FirstName, 
				s.LastName,
				s.ClubId, 
				c.Name as ShotClass, 
				g.Grade as GunClass, 
				g.Description as GunClassName,
				cl.Name as ClubName
			from tbl_Pistol_Entry e
			join tbl_Pistol_Shot s on (s.Id = e.ShotId)
			join tbl_Pistol_Club cl on (cl.Id = s.ClubId)
			join tbl_Pistol_ShotClass c on (c.Id = e.ShotClassId)
			join tbl_Pistol_GunClassification g on (g.Id = e.GunClassificationId) 
			where e.PatrolId = 0$pid
			order by e.StaPlats, s.LastName, s.FirstName
		";

		$result = mysqli_query($dbh,$sql);
		
		if ($debug)
			print "SQL Error: " . mysqli_error($dbh) . "<br/>";		
		
		$rc = mysqli_affected_rows($dbh);
				
		if ($rc < 0)
		{
			$msg = "NO ROWS: " . mysqli_error($dbh);
			return 0;
		}
		
		while ($obj = mysqli_fetch_object($result))
		{
			unset($row);
			$row["Id"] = $obj->Id;
			$row["EntryId"] = $obj->EntryId;
			$row["FirstName"] = $obj->FirstName;
			$row["LastName"] = $obj->LastName;
			$row["ShotClass"] = $obj->ShotClass;
			$row["GunClass"] = $obj->GunClass;
			$row["GunClassName"] = $obj->GunClassName;
			$row["ClubName"] = $obj->ClubName;
			$row["ClubId"] = $obj->ClubId;
			
			$list[] = $row;
		}

		mysqli_free_result($result);

		return $list;
	}

	// Find the guns that are allowed in this patrol
	function getAllowedGuns()
	{
		global $debug;
		global $msg;
		
		// Reset the list of allowed guns
		$this->gunList = array();
		
		if ($this->id == 0)
			return;
			
		$dbh = getOpenedConnection();
		$list = array();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
			
		$sql = "
			select p.GunClassId as Id, c.Grade as Name 
			from tbl_Pistol_PatrolGun p
			join tbl_Pistol_GunClassification c on (c.Id = p.GunClassId)
			where p.PatrolId = 0$this->id
		";
		
		if ($debug)
			print_r("<br>$sql");
			
		$result = mysqli_query($dbh,$sql);
		$rc = mysqli_affected_rows($dbh);
		
		if ($rc < 0)
		{
			$msg = mysqli_error($dbh);
			return 0;
		}

		// Now read in the current list of allowed guns
		while ($obj = mysqli_fetch_object($result))
		{
			$list[$obj->Id] = $obj->Name;
			$this->gunList[$obj->Id] = $obj->Name;
		}

		mysqli_free_result($result);

		return $list;
	}
	
	function deletePatrulGuns($compdayid){
		global $debug;
		global $msg;
		
		$dbh = getOpenedConnection();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}

		// First get rid of existing rows
		$sql = "
			delete from tbl_Pistol_PatrolGun
			where PatrolId in 
				(select Id from tbl_Pistol_Patrol where CompetitionDayId = 0$compdayid)
		";

		if ($debug)
			print_r($sql);
			
		mysqli_query($dbh,$sql);
		$rc = mysqli_affected_rows($dbh);
	}

	function saveHidden()
	{
		global $debug;
		global $msg;
		
		$dbh = getOpenedConnection();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
		
		$sql = "update tbl_Pistol_Patrol
			set Hidden = $this->hidden
			where Id = $this->id;
		";
		
			
		//print_r("SQL: " . $sql);
		
		$res = mysqli_query($dbh,$sql);

		$rc = mysqli_affected_rows($dbh);
		if ($debug)
			print_r("AFFECTED ROWS: " . $rc);
		
		if (($rc < 0) && ($this->id != 0)) {
			// Someone has removed the row from the database
			// We'll need to insert a new row.
				print_r(mysqli_error($dbh));
				print_r("UPDATED ZERO ROWS");
			
			
			$this->id = 0;
			$this->save();
		}
		
	}
	
	// Find the guns that are allowed in this patrol
	function saveAllowedGuns($list)
	{
		global $debug;
		global $msg;
		
		$dbh = getOpenedConnection();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}

		// Reset list
		$this->gunList = array();
		
		// First get rid of existing rows
		//$sql = "
		//	delete from tbl_Pistol_PatrolGun
		//	where PatrolId = 0$this->id
		//";

		//if ($debug)
		//	print_r($sql);
			
		//mysqli_query($dbh,$sql);
		//$rc = mysqli_affected_rows($dbh);

		if (!is_array($list))
			return 0;
			
		// Insert each allowed gun
		foreach ($list as $key => $value) {
			$sql = "insert into tbl_Pistol_PatrolGun(PatrolId, GunClassId)
				values ($this->id, $key)
			";
			
			$this->gunList[] = $key;
			
			if ($debug)
				print_r("<br>$sql");
				
			mysqli_query($dbh,$sql);
			$rc = mysqli_affected_rows($dbh);
			if ($rc < 0) {	
				$msg = mysqli_error($dbh);
				if ($debug)
					print $sql;
				$ret = 0;
			}
		}
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
			if ($this->sortOrder == 0) {
				// Get the next available sort order
				$sql = "select ifnull(max(SortOrder), 0) + 1 as NextSortOrder
					from tbl_Pistol_Patrol
					where CompetitionDayId = 0$this->competitionDayId
				";
					
				$result = mysqli_query($dbh,$sql);
				if ($obj = mysqli_fetch_object($result))
				{
					$this->sortOrder = $obj->NextSortOrder;
				}
		
				mysqli_free_result($result);
			}
			
			// Create new object
			$sql = "insert into tbl_Pistol_Patrol(
				CompetitionDayId,
				SortOrder,
				Description,
				StartTime
			)
			values (
				$this->competitionDayId,
				$this->sortOrder,
				'$this->description',
				'$this->startTime'
			)
			";
			
			$msg = "Ny patrull skapad";
		}
		else
		{
			// Update existing item
		$sql = "update tbl_Pistol_Patrol
			set CompetitionDayId = $this->competitionDayId,
					SortOrder = $this->sortOrder,
					Description = '$this->description',
					StartTime = '$this->startTime'
			where Id = $this->id;
		";

		$msg = "Patrull uppdaterad";
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

	// Find suitable patrols with room to spare
	function listAvailable($compId, $gunClassId, $shotId)
	{
		global $debug;
		global $msg;
		
		$dbh = getDBHandle(); // Open a new connection. Sprocs mess up existing db conns.
		$list = array();

		if (!$dbh) {
				if ($debug)
					print "Patrol:listAvailable => Failed to open a new connection to database<br/>";
				return "FAILED to open connection.";
		}
		
		$sql = "
			call ListAvailablePatrols ($shotId, $gunClassId, $compId)
		";

		if ($debug)				
			print("SQL: " . $sql . "<br/>");

		if ($dbh->multi_query($sql))
		do {
			if ($result = $dbh->store_result()) {
				while ($obj = $result->fetch_object())
				{
					unset($row);
		
					$row["EntryId"] = $obj->EntryId;
					$row["PatrolId"] = $obj->PatrolId;
					$row["SortOrder"] = $obj->SortOrder;
					$row["StartTime"] = $obj->StartTime;
					$row["SeatsLeft"] = $obj->SeatsLeft;
					$row["Status"] = $obj->Status;
					$row["Hidden"] = $obj->Hidden;
					
					$list[] = $row;
					
					if ($debug)
						print_r("Setting status to: " . $obj->Status . "<br/>");
				}
				$result->close();
			}
		} while ($dbh->next_result());
		else {
			//$msg = $msg . $dbh->error;
			$status = $dbh->error;
		}

		$dbh->close(); // close extra connection required for this procedure
		return $list;
	}
	
	// Delete the current object
	function delete()
	{
		global $debug;
		global $msg;
		
		$dbh = getOpenedConnection();

		// Delete current item
		$sql = "delete from tbl_Pistol_Patrol
			where Id = $this->id;
		";

		if ($debug)
			print_r("SQL: " . $sql);
			
		mysqli_query($dbh,$sql);
		if (mysqli_errno($dbh)!=0) {
			$msg = mysqli_error($dbh);
			return 0;
		}
		
		$this->clear(); // Clear current object
		return 1;
	}
}
?>
