-- DELIMITER $$

DROP PROCEDURE IF EXISTS `ListAvailablePatrols` $$

CREATE  PROCEDURE  `ListAvailablePatrols`(in pShotId bigint, in pGunClassId bigint, in pCompId bigint)
begin

	declare pPatrolId bigint;
	declare	vMaxPatrolSize int;
	
	main: begin
	
	drop temporary table if exists patrols;
	
	create temporary table patrols (
		PatrolId	bigint,
		Members		int,
		StartTime	datetime,
		Status		varchar(10) null,
		EntryId		bigint null
	);
	
	select MaxPatrolSize
	into vMaxPatrolSize
	from tbl_Pistol_Competition
	where Id = pCompId;
	
	
	
	insert into patrols (PatrolId, Members, StartTime, Status, EntryId)
	select distinct p.Id, count(distinct e.Id), p.StartTime, 'AVAILABLE', null
	from tbl_Pistol_CompetitionDay d
	join tbl_Pistol_Patrol p on (p.CompetitionDayId = d.Id)
	join tbl_Pistol_PatrolGun g on (g.PatrolId = p.Id and g.GunClassId = pGunClassId)
	left join tbl_Pistol_Entry e on (e.PatrolId = p.Id)
	where d.CompetitionId = pCompId
	group by p.Id;
	
	
	update patrols p
	join tbl_Pistol_Entry e on (e.PatrolId = p.PatrolId and e.ShotId = pShotId)
	set p.Status = 'BOOKED', p.EntryId = e.Id;
	
	
	select t.PatrolId,
		p.SortOrder,
		date_format(t.StartTime, '%d/%m %H:%i') as StartTime,
		vMaxPatrolSize - t.Members as SeatsLeft,
		t.Status,
		t.EntryId,
		p.Hidden
	from patrols t
	join tbl_Pistol_Patrol p on (p.Id = t.PatrolId)
	where (t.Members < vMaxPatrolSize)
	order by t.StartTime;
	
	drop temporary table patrols;
	
	end main;
end $$

