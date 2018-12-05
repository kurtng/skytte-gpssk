<?php
$debug = 0;

class Traning
{
	public $id = 0;
	public $shotClassId = 0;
	public $shotId = 0;
	public $result = 0;
	public $date = '';
	public $shotIdGodkan = 0;
			
	
	// Clear out data in this object
	function clear()
	{
		$this->id = 0;
		$this->$shotClassId = 0;
		$this->$shotId = 0;
		$this->$result = 0;
		$this->$date = '';
		$this->$shotIdGodkan = 0;
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
	
	function getDates()
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
			select distinct s.Date, count(s.Id) as Count
			from tbl_Pistol_Traning s
			group by s.Date
		";
		
		$result = mysqli_query($dbh,$sql);
		
		while ($obj = mysqli_fetch_object($result))
		{
			unset($row);
			$row["Count"] = $obj->Count;
			$row["Date"] = $obj->Date;
			$list[] = $row;
		}

		mysqli_free_result($result);

		return $list;
	}
	
	function getClassForDate($selectedDate)
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
			select distinct s.ShotClassId, c.Name, count(s.Id) as Count
			from tbl_Pistol_Traning s
			join tbl_Pistol_ShotClass c on (s.ShotClassId = c.Id)
			where s.Date = '$selectedDate'
			group by s.ShotClassId
			order by c.Name desc;
		";
		
		$result = mysqli_query($dbh,$sql);
		
		while ($obj = mysqli_fetch_object($result))
		{
			unset($row);
			$row["Count"] = $obj->Count;
			$row["Name"] = $obj->Name;
			$row["ShotClassId"] = $obj->ShotClassId;
			$list[] = $row;
		}

		mysqli_free_result($result);

		return $list;
	}
	
function getScores($selectedDate, $selectedKlass)
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
			select s.Id, s.Date, c.Name, s.Score, sh.FirstName, sh.LastName, sh2.FirstName as GFirstName, sh2.LastName as GLastName
			from tbl_Pistol_Traning s
			join tbl_Pistol_ShotClass c on (s.ShotClassId = c.Id)
			join tbl_Pistol_Shot sh on s.ShotId = sh.Id
			left outer join tbl_Pistol_Shot sh2 on s.GodkanShotId = sh2.Id
			where s.Date = '$selectedDate' and s.ShotClassId = $selectedKlass
			order by s.Score desc;
		";
		
		$result = mysqli_query($dbh,$sql);
		
		while ($obj = mysqli_fetch_object($result))
		{
			
			unset($row);
			$row["Id"] = $obj->Id;
			$row["Date"] = $obj->Date;
			$row["ShotClassName"] = $obj->Name;
			$row["Score"] = $obj->Score;
			$row["ShotFirstName"] = $obj->FirstName;
			$row["ShotLastName"] = $obj->LastName;
			$row["GShotName"] = $obj->GFirstName . " " . $obj->GLastName;
			$list[] = $row;
		}

		mysqli_free_result($result);

		return $list;
	}
	
	function getScoresReg($shotIdSelected)
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
			select s.Id, s.Date, c.Name, s.Score, sh.FirstName, sh.LastName, sh2.FirstName as GFirstName, sh2.LastName as GLastName
			from tbl_Pistol_Traning s
			join tbl_Pistol_ShotClass c on (s.ShotClassId = c.Id)
			join tbl_Pistol_Shot sh on s.ShotId = sh.Id
			left outer join tbl_Pistol_Shot sh2 on s.GodkanShotId = sh2.Id
			where s.GodkanShotId = $shotIdSelected
			order by s.Score desc;
		";
		
		$result = mysqli_query($dbh,$sql);
		
		while ($obj = mysqli_fetch_object($result))
		{
			
			unset($row);
			$row["Id"] = $obj->Id;
			$row["Date"] = $obj->Date;
			$row["ShotClassName"] = $obj->Name;
			$row["Score"] = $obj->Score;
			$row["ShotFirstName"] = $obj->FirstName;
			$row["ShotLastName"] = $obj->LastName;
			$row["GShotName"] = $obj->GFirstName . " " . $obj->GLastName;
			$list[] = $row;
		}

		mysqli_free_result($result);

		return $list;
	}
	
	

	function listEntries($shotId)
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
			select s.Id, s.Date, c.Name, s.Score, sh.FirstName as GFirstName, sh.LastName as GLastName
			from tbl_Pistol_Traning s
			join tbl_Pistol_ShotClass c on (s.ShotClassId = c.Id)
			left outer join tbl_Pistol_Shot sh on (s.GodkanShotId = sh.Id)  
			where s.ShotId = 0$shotId
			order by s.Date desc;
		";
		
		if ($debug)
			print_r("SQL: " . $sql);
		
		$result = mysqli_query($dbh,$sql);
		
		while ($obj = mysqli_fetch_object($result))
		{
			
			unset($row);
			$row["Id"] = $obj->Id;
			$row["Date"] = $obj->Date;
			$row["ShotClassName"] = $obj->Name;
			$row["Score"] = $obj->Score;
			$row["GShotName"] = $obj->GFirstName . " " . $obj->GLastName;
			$list[] = $row;
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
				insert into tbl_Pistol_Traning(
					ShotId,
					ShotClassId,
					Date,
					Score,
					GodkanShotId
				)
				values (
					$this->shotId,
					$this->shotClassId,
					'$this->date',
					$this->result,
					$this->shotIdGodkan
				)
			";
		}
		else
		{
			// Update existing item
			$sql = "
				update tbl_Pistol_Traning
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
	function delete($traningId)
	{
		global $debug;
		$dbh = getOpenedConnection();
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}

		// Delete current item
		$sql = "
			delete from tbl_Pistol_Traning
			where Id = " . $traningId;
		
		if ($debug)
			print_r("SQL: " . $sql);
			
		mysqli_query($dbh,$sql);
		if (mysqli_errno($dbh)!=0) {
			print_r(mysqli_error($dbh));
			return;
		}
		
		//$this->clear(); // Clear current object

	}
}

?>
