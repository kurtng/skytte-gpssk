-- DELIMITER $$


DROP PROCEDURE IF EXISTS `ListMissingScores` $$

CREATE PROCEDURE `ListMissingScores`(in pCompId bigint)
begin
	
	drop temporary table if exists MustHaveScores;
	
	create temporary table MustHaveScores (
		ShotId bigint not null,
		CompetitionDayId bigint not null,
		EntryId bigint not null,
		StationId smallint not null
	);
	
	main: begin
	
	declare vCompDayId bigint;
	declare vStationId smallint;
	declare vMaxStation int;
	
	
	
	
	
	select min(Id)
	into vCompDayId
	from tbl_Pistol_CompetitionDay
	where CompetitionId = pCompId;
	
	while vCompDayId is not null
	do
		select MaxStation
		into vMaxStation
		from tbl_Pistol_CompetitionDay
		where Id = vCompDayId;
	
		set vStationId = 0;
	
		while vStationId < vMaxStation
		do
			set vStationId = vStationId + 1;
	
			insert into MustHaveScores (
				ShotId,
				CompetitionDayId,
				StationId,
				EntryId
			)
			select	e.ShotId,
				vCompDayId,
				vStationId,
				e.Id
			from	tbl_Pistol_Entry e
			where	e.CompetitionId = pCompId;
		end while;
	
		select min(Id)
		into vCompDayId
		from tbl_Pistol_CompetitionDay
		where CompetitionId = pCompId
		and Id > vCompDayId;
	end while;
	
	
	delete p
	from MustHaveScores p
	join tbl_Pistol_Score s on (s.ShotId = p.ShotId
		and s.CompetitionDayId = p.CompetitionDayId
		and s.StationId = p.StationId);
	
	select distinct m.ShotId, m.CompetitionDayId,
		d.DayNo,
		m.StationId,
		e.Id as EntryId,
		p.SortOrder as PatrolNo,
		concat(s.FirstName, ' ', s.LastName) as ShotName
	from MustHaveScores m
	join tbl_Pistol_Shot s on (s.Id = m.ShotId)
	join tbl_Pistol_CompetitionDay d on (d.Id = m.CompetitionDayId)
	join tbl_Pistol_Entry e on (e.Id = m.EntryId)
	join tbl_Pistol_Patrol p on (p.Id = e.PatrolId)
	order by d.DayNo, m.StationId, p.SortOrder, s.LastName, s.FirstName;
	
	end main;
	
	drop temporary table if exists MustHaveScores;

end $$





