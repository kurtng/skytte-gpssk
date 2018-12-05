<?php
include_once "GokhanCore.php";

class Score {
	public $id = 0;
	public $entryId = 0;
	public $stationId = 0;
	public $compDayId = 0;
	public $hits = 0;
	public $targets = 0;
	public $points = 0;
	public $registerDate = '';
	public $competitionId = 0;
		
	// Clear out data in this object
	function clear()
	{
		$this->id = 0;
		$this->entryId = 0;
		$this->stationId = 0;
		$this->compDayId = 0;
		$this->hits = 0;
		$this->targets = 0;
		$this->points = 0;
		$this->registerDate = '';
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

		if ($pid <= 0) {
			$msg = "Unknown Score Id " . $pid;
			return;
		}
					
		$sql = "
			select
				Id,
				StationId,
				EntryId,
				CompetitionDayId,
				Hits,
				Targets,
				Points,
				RegisterDate
			from tbl_Pistol_Score
			where Id = $pid
		";
		
		$result = mysqli_query($dbh,$sql);
		
		if ($obj = mysqli_fetch_object($result))
		{
			$this->id = $obj->Id;
			$this->stationId = $obj->StationId;
			$this->entryId = $obj->EntryId;
			$this->compDayId = $obj->CompetitionDayId;
			$this->hits = $obj->Hits;
			$this->targets = $obj->Targets;
			$this->points = $obj->Points;
			$this->registerDate = $obj->RegisterDate;
		}

		mysqli_free_result($result);
	}

	// Load the specified section from the db
	function find()
	{
		global $debug;
		global $msg;
		
		$dbh = getOpenedConnection();
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}

		if ($this->entryId == 0)
			return;
			
		$sql = "
			select
				Id,
				StationId,
				EntryId,
				CompetitionDayId,
				Hits,
				Targets,
				Points,
				RegisterDate
			from tbl_Pistol_Score
			where EntryId = 0$this->entryId
			and CompetitionDayId = $this->compDayId
			and StationId = $this->stationId
		";
		
		$result = mysqli_query($dbh,$sql);
		
		if ($obj = mysqli_fetch_object($result))
		{
			$this->id = $obj->Id;
			$this->stationId = $obj->StationId;
			$this->compDayId = $obj->CompetitionDayId;
			$this->hits = $obj->Hits;
			$this->targets = $obj->Targets;
			$this->points = $obj->Points;
			$this->registerDate = $obj->RegisterDate;
		}

		mysqli_free_result($result);
	}

	// Save this object to the db
	function save()
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

		if ($this->id == 0)
		{
			// Register entry
			$sql = "call RegisterScore (
				$this->entryId,
				$this->compDayId,
				$this->stationId,
				0$this->hits,
				0$this->targets,
				0$this->points,
				0,
				false
				)
				";
		}
		else
		{
			$msg = "Kan ej registrera resultatet. " . $dbh->error;;
			return "EXISTS";
		}
		if ($debug)
			print_r("SQL: " . $sql);
		
		if ($dbh->multi_query($sql))
			do {
				if ($result = $dbh->store_result()) {
					while ($obj = $result->fetch_object())
					{
						$status = $obj->Status;
						$this->id = $obj->ScoreId;
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
		return $status;
	}
	
	function saveExtra()
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

		if ($this->id == 0)
		{
			// Register entry
			$sql = "call RegisterScore (
				$this->entryId,
				0,
				$this->stationId,
				0$this->hits,
				0$this->targets,
				0$this->points,
				$this->competitionId,
				true
				)
				";
		}
		else
		{
			$msg = "Kan ej registrera resultatet. " . $dbh->error;;
			return "EXISTS";
		}
		if ($debug)
			print_r("SQL: " . $sql);
		
		if ($dbh->multi_query($sql))
		do {
			if ($result = $dbh->store_result()) {
				while ($obj = $result->fetch_object())
				{
					$status = $obj->Status;
					$this->id = $obj->ScoreId;
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
		return $status;
	}

	// Delete the current object
	function delete()
	{
		global $debug;
		global $msg;
		
		$dbh = getDBHandle(); // Open a new connection. Sprocs mess up existing db conns.
		$status = "OK";
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}

		// Register entry
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
			$msg = $dbh->error;
		}
		
		if ($debug)
			print($dbh->error);

		$dbh->close();
		return $status;
				
		$this->clear(); // Clear current object
	}

	// List entries for specified shot
	function getList($shotId)
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


		$sql = "select e.Id, c.Name as Competition, '' as Shot
			from tbl_Pistol_Entry e
			join tbl_Pistol_Patrol p on (p.Id = e.PatrolId)
			join tbl_Pistol_CompetitionDay d on (d.Id = p.CompetitionDayId)
			join tbl_Pistol_Competition c on (c.Id = d.CompetitionId and c.Status = 1)
			where e.ShotId = 0$shotId
			order by c.Id
		";
			
		if ($shotId == 0) {
			$sql = "select e.Id, c.Name as Competition, concat(s.FirstName, ' ', s.LastName) as Shot  
				from tbl_Pistol_Entry e
				join tbl_Pistol_Patrol p on (p.Id = e.PatrolId)
				join tbl_Pistol_CompetitionDay d on (d.Id = p.CompetitionDayId)
				join tbl_Pistol_Competition c on (c.Id = d.CompetitionId and c.Status = 1)
				join tbl_Pistol_Shot s on (s.Id = e.ShotId)
				order by c.Id, s.LastName
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
		
		$dbh = getOpenedConnection();
		$pid = 0;
		
		if ($dbh == null) {
			$dbh = openDB();
			if ($dbh == null)
				return;
		}
			
		if ($compDayId == 0) {
			return;
		}
		
		$sql = "select e.PatrolId  
			from tbl_Pistol_EntryPatrol e
			join tbl_Pistol_Patrol p on (p.Id = e.PatrolId and p.CompetitionDayId = $compDayId)
			where e.EntryId = 0$this->id
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
			$pid = $obj->PatrolId;
		}

		mysqli_free_result($result);

		return $pid;
	}

	// List missing scores
	function listMissingScores($compId)
	{
		global $debug;
		// Get a separate connection
		$dbh = getDBHandle();
		$list = array();
		
		if ($dbh == null) {
			return;
		}
			
		$sql = "
			call ListMissingScores($compId)
		";
		
		$cnt = 0;
		
		if ($dbh->multi_query($sql))
		do {
			if ($result = $dbh->store_result()) {
				while ($obj = $result->fetch_object())
				{
					unset($row);
					$row["ShotId"] = $obj->ShotId;
					$row["CompDayId"] = $obj->CompetitionDayId;
					$row["DayNo"] = $obj->DayNo;
					$row["StationId"] = $obj->StationId;
					$row["EntryId"] = $obj->EntryId;
					$row["ShotName"] = $obj->ShotName;
					$row["PatrolNo"] = $obj->PatrolNo;
					
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
	
	function listAvailabalityAndAnmalningarPerClass($compId)
	{
		
		global $debug;
		// Get a separate connection
		$dbh = getDBHandle();
		$list = array();
		
		if ($dbh == null) {
			return;
		}
			
		
		$sql = "
				select 	count(distinct(p.Id)) AntalPatrol, 
						c.MaxPatrolSize PatrolSize, 
						Count(distinct(e.Id)) AntalAnmalningar, 
						g.Grade GunClassName
				from tbl_Pistol_Competition c
				join tbl_Pistol_CompetitionDay cd on c.Id = cd.CompetitionId
				join tbl_Pistol_Patrol p on p.CompetitionDayId = cd.Id
				left outer join tbl_Pistol_Entry e  on e.PatrolId = p.Id
				join tbl_Pistol_PatrolGun pg on pg.PatrolId = p.Id
				join tbl_Pistol_GunClassification g on pg.GunClassId = g.Id and g.Id = e.GunClassificationId
				where c.Id = 0$compId
				group By g.Id 
				order by g.Grade
				
			";
		
		if ($debug)	
			print "$sql<br/>";			
		
		$cnt = 0;
		
		if ($dbh->multi_query($sql))
		do {
			if ($result = $dbh->store_result()) {
				while ($obj = $result->fetch_object())
				{
					unset($row);
					$row["AntalPatrol"] = $obj->AntalPatrol;
					$row["PatrolSize"] = $obj->PatrolSize;
					$row["AntalAnmalningar"] = $obj->AntalAnmalningar;
					$row["GunClassName"] = $obj->GunClassName;
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
	
	function anmalningarCountPerShotClass($compId)
	{
		
		global $debug;
		// Get a separate connection
		$dbh = getDBHandle();
		$list = array();
		
		if ($dbh == null) {
			return;
		}
			
		$sql = 	"	select count(ShotId) as AntalAnmalningar, sc.Name as ShotClassName
					from tbl_Pistol_CompetitionDay cd 
					join tbl_Pistol_Competition c on c.Id = cd.CompetitionId 
					join tbl_Pistol_Patrol p on p.CompetitionDayId = cd.Id
					join tbl_Pistol_Entry e on p.Id = e.PatrolId
					join tbl_Pistol_ShotClass sc on sc.Id = ShotClassId
					where cd.CompetitionId = 0$compId 
					group By ShotClassId
					order By sc.Name 
				";
		
		if ($debug)	
			print "$sql<br/>";			
		
		$cnt = 0;
		
		if ($dbh->multi_query($sql))
		do {
			if ($result = $dbh->store_result()) {
				while ($obj = $result->fetch_object())
				{
					unset($row);
					$row["AntalAnmalningar"] = $obj->AntalAnmalningar;
					$row["ShotClassName"] = $obj->ShotClassName;
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

	function scoreCountPerShotClass($compId)
	{
		
		global $debug;
		// Get a separate connection
		$dbh = getDBHandle();
		$list = array();
		
		if ($dbh == null) {
			return;
		}
			
		$sql = 	"	select  count(distinct(ShotId)) as ScoreCount, 
        			sc.Name as ShotClassName 
					from tbl_Pistol_Score s 
					join tbl_Pistol_Entry e on e.Id = s.EntryId 
					join tbl_Pistol_ShotClass sc on sc.Id = e.ShotClassId 
					join tbl_Pistol_CompetitionDay cd on cd.CompetitionId = 0$compId and s.CompetitionDayId = cd.Id
					join tbl_Pistol_Competition c on c.Id = 0$compId and c.Id = cd.CompetitionId 
					group By e.ShotClassId , sc.Name
					order by sc.Name
					
				";
		
		if ($debug)	
			print "$sql<br/>";			
		
		$cnt = 0;
		
		if ($dbh->multi_query($sql))
		do {
			if ($result = $dbh->store_result()) {
				while ($obj = $result->fetch_object())
				{
					unset($row);
					$row["ScoreCount"] = $obj->ScoreCount;
					$row["ShotClassName"] = $obj->ShotClassName;
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
	
// List result
	function listResultStats($shotId)
	{
		global $debug;
		// Get a separate connection
		$dbh = getDBHandle();
		$list = array();
		
		if ($dbh == null) {
			return;
		}
			
		$sql = "
		
		SELECT shot.FirstName, shot.LastName, comp.Name as CompName, gunClass.Grade, Hits, Targets, Points, comp.Id as CompId, comp.StartDate
		FROM  	tbl_Pistol_Score score, 
      			tbl_Pistol_CompetitionDay compDay, 
      			tbl_Pistol_Competition comp, 
      			tbl_Pistol_Entry entry,
      			tbl_Pistol_Shot shot,
      			tbl_Pistol_GunClassification gunClass
		where 	score.CompetitionDayId = compDay.Id and compDay.CompetitionId = comp.Id and entry.Id = score.EntryId
				and shot.Id = entry.ShotId and shot.Id = $shotId and gunClass.Id = entry.GunClassificationId and
				comp.Name like '%lians%'
				order by StartDate, comp.Id, Grade;
			 
		";
		
		if ($debug)	
			print "$sql<br/>";			
		
		$cnt = 0;
		
		if ($dbh->multi_query($sql))
		do {
			if ($result = $dbh->store_result()) {
				while ($obj = $result->fetch_object())
				{
					unset($row);
					$row["FirstName"] = $obj->FirstName;
					$row["LastName"] = $obj->LastName;
					$row["CompName"] = $obj->CompName;
					$row["Grade"] = $obj->Grade;
					$row["Hits"] = $obj->Hits;
					$row["Targets"] = $obj->Targets;
					$row["Points"] = $obj->Points;
					$row["CompId"] = $obj->CompId;
					$row["StartDate"] = $obj->StartDate;
					
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
	
	
	// List result
	function listResult($compId, $shotClassId, $verbose = 'N')
	{
		global $debug;
		// Get a separate connection
		$dbh = getDBHandle();
		$list = array();
		
		if ($dbh == null) {
			return;
		}
			
		$sql = "
			call ListResult($compId, 0$shotClassId, '$verbose', 0, false) 
		";
		
		if ($debug)	
			print "$sql<br/>";			
		
		$cnt = 0;
		
		if ($dbh->multi_query($sql))
		do {
			if ($result = $dbh->store_result()) {
				while ($obj = $result->fetch_object())
				{
					unset($row);
					$row["ShotId"] = $obj->ShotId;
					$row["ShotClassId"] = $obj->ShotClassId;
					$row["ShotClass"] = $obj->ShotClass;
					$row["ShotName"] = $obj->ShotName;
					$row["Hits"] = $obj->Hits;
					$row["Targets"] = $obj->Targets;
					$row["Total"] = $obj->Total;
					$row["Points"] = $obj->Points;
					$row["Place"] = $obj->Place;
					$row["Medal"] = $obj->Medal;
					$row["ClubName"] = $obj->ClubName;
					$row["EntryId"] = $obj->EntryId;
					$row["SuperScore"] = $obj->SuperScore;
					$row["ExtraScore"] = $obj->ExtraScore;
					$row["GunClassName"] = $obj->GunClassName;
					
					if ($verbose == 'Y') {
						$row["StationId"] = $obj->StationId;
						$row["StationHits"] = $obj->StationHits;
						$row["StationTargets"] = $obj->StationTargets;
						$row["StationPoints"] = $obj->StationPoints;
					} 
					
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
	
	// List team results
	function listTeamResult($compId)
	{
		global $debug;
		// Get a separate connection
		$dbh = getDBHandle();
		$list = array();
		
		if ($dbh == null) {
			return;
		}
			
		$sql = "
			call ListTeamResult($compId) 
		";
		
		if ($debug)
			print "$sql<br/>";			
		
		$cnt = 0;
		
		if ($dbh->multi_query($sql))
		do {
			if ($result = $dbh->store_result()) {
				while ($obj = $result->fetch_object())
				{
					unset($row);
					
					$row["TeamId"] = $obj->TeamId;
					$row["GunClassId"] = $obj->GunClassId;
					$row["GunGrade"] = $obj->GunGrade;
					$row["GunGradeDesc"] = $obj->GunGradeDesc;
					$row["TeamName"] = $obj->TeamName;
					$row["ClubName"] = $obj->ClubName;
					$row["Hits"] = $obj->Hits;
					$row["Targets"] = $obj->Targets;
					$row["Total"] = $obj->Total;
					$row["Points"] = $obj->Points;
					$row["Place"] = $obj->Place;
					
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
	
	// List scores
	function listScores($patrolId, $shotId, $scoreType)
	{
		global $debug;
		// Get a separate connection
		$dbh = getDBHandle();
		$list = array();
		
		if ($dbh == null) {
			return;
		}
			
		$sql = "
			call ListScores($patrolId, $shotId, false, 0, 0)
		";
		
		if ($debug)
			print "$sql<br/>";		
		
		$cnt = 0;
		
		if ($dbh->multi_query($sql))
		do {
			if ($result = $dbh->store_result()) {
				while ($obj = $result->fetch_object())
				{
					unset($row);			
					$row["StationId"] = $obj->StationId;
					if(isPrecision($scoreType)) {
						$allpoints = decodePrecision($obj->Hits);
						$row[1] = $allpoints[1];
						$row[2] = $allpoints[2];
						$row[3] = $allpoints[3];
						$row[4] = $allpoints[4];
						$row[5] = $allpoints[5];
						$row["Totals"] = $allpoints[1]+$allpoints[2]+$allpoints[3]+$allpoints[4]+$allpoints[5];
						$row["Targets"] = $obj->Targets;
						$row["Hits"] = $obj->Hits;
					} else {
						$row["Hits"] = $obj->Hits;
						$row["Targets"] = $obj->Targets;
						$row["Points"] = $obj->Points;
					}
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
	
	function listExtraScores($compId, $shotId, $scoreType, $gunClassificationId, $numberOfExtraShots)
	{
		global $debug;
		// Get a separate connection
		$dbh = getDBHandle();
		$list = array();
		
		if ($dbh == null) {
			return;
		}
			
		$sql = "
			call ListScores(0, $shotId, true, $compId, $gunClassificationId)
		";
		
		if ($debug)
			print "$sql<br/>";		
		
		$cnt = 0;
		
		if ($dbh->multi_query($sql))
		do {
			if ($result = $dbh->store_result()) {
				while ($obj = $result->fetch_object())
				{
					unset($row);
					$row["StationId"] = $obj->StationId;
					if(isPrecision($scoreType)) {
						$allpoints = decodePrecision($obj->Hits);
						$row[1] = $allpoints[1];
						$row[2] = $allpoints[2];
						$row[3] = $allpoints[3];
						$row[4] = $allpoints[4];
						$row[5] = $allpoints[5];
						$row["Totals"] = $allpoints[1]+$allpoints[2]+$allpoints[3]+$allpoints[4]+$allpoints[5];
					} else {
						$row["Hits"] = $obj->Hits;
						$row["Targets"] = $obj->Targets;
						$row["Points"] = $obj->Points;
					}
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
	
}
?>