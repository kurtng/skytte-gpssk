<?php

class Station {
	public $id = 0;
	public $competitionDayId = 0;
	public $sortOrder = 1;
	public $intervalMinutes = 10;
	
	
	// Clear out data in this object
	function clear()
	{
		$this->id = 0;
		$this->competitionDayId = 0;
		$this->sortOrder = 1;
		$this->intervalMinutes = 10;
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
				PatrolSpace
			from tbl_Pistol_Station
			where Id = $pid;
		";
		
		$result = mysqli_query($dbh,$sql);
		if ($obj = mysqli_fetch_object($result))
		{
			$this->id = $obj->Id;
			$this->competitionDayId = $obj->CompetitionDayId;
			$this->sortOrder = $obj->SortOrder;
			$this->intervalMinutes = $obj->PatrolSpace;
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
			
		$sql = "
			select Id, SortOrder as Name
			from tbl_Pistol_Station
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
			$sql = "insert into tbl_Pistol_Station(
				CompetitionDayId,
				SortOrder,
				PatrolSpace
			)
			values (
				$this->competitionDayId,
				$this->sortOrder,
				$this->intervalMinutes
			)
			";
			
			$msg = "Ny station skapad";
		}
		else
		{
			// Update existing item
		$sql = "update tbl_Pistol_Station
			set CompetitionDayId = $this->competitionDayId,
					SortOrder = $this->sortOrder,
					PatrolSpace = $this->intervalMinutes
			where Id = $pid;
		";
			
		$msg = "Station uppdaterad";
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
		$sql = "delete from tbl_Pistol_Station
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