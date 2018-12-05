<?php
$debug = 0;

class Club
{
	public $id = 0;
	public $name = '';
	public $createDate = '';
			
	
	// Clear out data in this object
	function clear()
	{
		$this->id = 0;
		$this->name = '';
		$this->createDate = '';
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
		
		if ($pid == 0)
			return;
			
		$sql = "
			select Id,
				Name,
				CreateDate
			from tbl_Pistol_Club
			where Id = $pid
		";
		
		$result = mysqli_query($dbh,$sql);
		if ($obj = mysqli_fetch_object($result))
		{
			$this->id = $obj->Id;
			$this->name = $obj->Name;
			$this->createDate = $obj->CreateDate;
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
			from tbl_Pistol_Club
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

	// List clubs
	function getClubList()
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
			select Id, Name
			from tbl_Pistol_Club
			order by Name;
		";
		
		$result = mysqli_query($dbh,$sql);
		
		while ($obj = mysqli_fetch_object($result))
		{
			$list[$obj->Id] = $obj->Name;
		}

		mysqli_free_result($result);

		return $list;
	}

	// List participating clubs
	function getClubsInComp($compId)
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
			from tbl_Pistol_Club c
			join tbl_Pistol_CompetitionDay d on (d.CompetitionId = 0$compId)
			join tbl_Pistol_Patrol p on (p.CompetitionDayId = d.Id)
			join tbl_Pistol_Entry e on (e.PatrolId = p.Id)
			join tbl_Pistol_Shot s on (s.Id = e.ShotId and s.ClubId = c.Id)
			order by c.Name;
		";
		
		$result = mysqli_query($dbh,$sql);
		
		if (mysqli_errno($dbh)!=0) {
			print_r(mysqli_error($dbh));
			return;
		}
		
		while ($obj = mysqli_fetch_object($result))
		{
			$list[$obj->Id] = $obj->Name;
		}

		mysqli_free_result($result);

		return $list;
	}

	// List competition entries from this club
	function listEntries($compId)
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
			select distinct s.Id, concat(s.FirstName, ' ', s.LastName, ' (', s.GunCard, ')') Name
			from tbl_Pistol_Entry e
			join tbl_Pistol_Shot s on (s.Id = e.ShotId and s.ClubId = $this->id)
			join tbl_Pistol_Patrol p on (e.PatrolId = p.Id)
			join tbl_Pistol_CompetitionDay d on (d.Id = p.CompetitionDayId and d.CompetitionId = 0$compId)
			order by s.LastName, s.FirstName;
		";
		
		$result = mysqli_query($dbh,$sql);
		
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
		$dbh = getOpenedConnection();
		$ret = 1;
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
		
		if ($this->id == 0)
		{
			// Create new object
			$sql = "
				insert into tbl_Pistol_Club(
					Name
				)
				values ('$this->name')
			";
		}
		else
		{
			// Update existing item
			$sql = "
				update tbl_Pistol_Club
				set 
					Name = '$this->name'
				where Id = $this->id
			";
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
			$this->id = mysqli_insert_id($dbh);
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
			delete from Club
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
}

?>
