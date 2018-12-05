<?php
$debug = 0;

class Team
{
	public $id = 0;
	public $name = '';
	public $competitionDayId = 0;
	public $gunClassId = 0;
	public $clubId = 0;
	
	
	// Clear out data in this object
	function clear()
	{
		$this->id = 0;
		$this->name = '';
		$this->competitionDayId = 0;
		$this->gunClassId = 0;
		$this->clubId = 0;
	}
	
	// Load the specified section from the db
	function load($pid)
	{
		global $debug;
		$dbh = getOpenedConnection();
		
		if ($dbh == null)
			return;
		
		if ($pid == 0)
			return;
			
		$sql = "
			select Id,
				Name,
				CompetitionDayId,
				GunClassId,
				ClubId
			from tbl_Pistol_Team
			where Id = $pid
		";
		
		$result = mysqli_query($dbh,$sql);
		if ($obj = mysqli_fetch_object($result))
		{
			$this->id = $obj->Id;
			$this->name = $obj->Name;
			$this->competitionDayId = $obj->CompetitionDayId;
			$this->gunClassId = $obj->GunClassId;
			$this->clubId = $obj->ClubId;
		}

		mysqli_free_result($result);
	}

	// List available teams
	function getTeamList($shotId, $compDayId)
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
			select Id,
				Name,
				CompetitionDayId,
				GunClassId,
				ClubId
			from tbl_Pistol_Team t
			join tbl_Pistol_Shot s on (s.Id = $shotId and s.ClubId = t.ClubId)
			where t.CompetitionDayId = $compDayId
			order by t.Name
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
		
		if (!$dbh)
			return;
			
		if ($this->id == 0)
		{
			// Create new object
			$sql = "
				insert into tbl_Pistol_Team(
					Name,
					CompetitionDayId,
					GunClassId,
					ClubId
				)
				values ('$this->Name',
					$this->competitionDayId,
					$this->gunClassId,
					$this->clubId
				)
			";
		}
		else
		{
			// Update existing item
			$sql = "
				update tbl_Pistol_Team
				set 
					Name = '$this->Name',
					CompetitionDayId = $this->competitionDayId,
					GunClassId = $this->gunClassId,
					ClubId = $this->clubId
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
		
	}

	function joinTeam($shotId, $teamId)
	{
		global $debug;
		$dbh = getOpenedConnection();
		
		if (!$dbh)
			return;
		
		// First make sure we leave a team if we're already a member
		// of a team for the same gun class.
		$sql = "
			delete m
			from tbl_Pistol_TeamMember m
			join tbl_Pistol_Team t on (t.Id = $teamId)
			join tbl_Pistol_Team t2 on (t2.CompetitionDayId = t.CompetitionDayId
				and t2.GunClassId = t.GunClassId
				and t2.ClubId = t.ClubId
				and t2.Id = m.TeamId)
			where m.ShotId = @shotId
		";
			
		if ($debug)
			print_r("SQL: " . $sql);
		
		$res = mysqli_query($dbh,$sql);

		$rc = mysqli_affected_rows($dbh);
		if ($debug)
			print_r("AFFECTED ROWS: " . $rc);
		
		if ($this->id == 0) {
			// Retrieve the auto-generated id
			$this->id = mysqli_insert_id($dbh);
		}
		
	}
	
	// Delete the current object
	function delete()
	{
		global $debug;
		$dbh = getOpenedConnection();

		// Delete current item
		$sql = "
			delete from tbl_Pistol_Team
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
