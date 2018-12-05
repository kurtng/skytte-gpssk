-- DELIMITER $$

DROP PROCEDURE IF EXISTS `RegisterEntry` $$

CREATE PROCEDURE  `RegisterEntry`(in patrolId bigint,
	in pShotId bigint,
	in pGunClassId bigint,
	in pShotClassId bigint,
	in pBokadAvShotId bigint)
begin
	declare pStatus varchar(10) default 'FAILED';
	declare eId bigint;
	
	drop temporary table if exists PatrolMembers;
	
	create temporary table PatrolMembers (
		CompetitionDayId bigint,
		PatrolId bigint,
		Seats int
	);
	
	main: begin
	
	declare seatsBooked int;
	declare seatsLeft int;
  	declare currentSeatNo int;
	declare patrolSize int;
	declare minPatrolId bigint;
	declare compDayId bigint;
	declare compId bigint;
	
	
	set pStatus = 'FAILED';
	
	select d.Id, d.CompetitionId
	into compDayId, compId
	from tbl_Pistol_Patrol p
	join tbl_Pistol_CompetitionDay d on (d.Id = p.CompetitionDayId)
	where p.Id = patrolId;
	
	
	
	
	if exists (select e.Id 
		from tbl_Pistol_Entry e
		join tbl_Pistol_Patrol p on (p.Id = e.PatrolId)
		join tbl_Pistol_CompetitionDay d on (d.Id = p.CompetitionDayId
			and d.CompetitionId = compid)
		where e.ShotId = pShotId
		and e.GunClassificationId = pGunClassId
		)
	then
		
		set pStatus = 'EXISTS';
		leave main;
	end if;
	
	
	if not exists(select Id from tbl_Pistol_Competition where Id = compId)
	then
		set pStatus = 'NOT_FOUND';
		leave main;
	end if;
	
	
	if not exists(select Id from tbl_Pistol_GunClassification where Id = pGunClassId)
	then
		set pStatus = 'NOT_FOUND';
		leave main;
	end if;
	
	
	if not exists(select Id from tbl_Pistol_ShotClass where Id = pShotClassId)
	then
		set pStatus = 'NOT_FOUND';
		leave main;
	end if;
	
	
	
	select MaxPatrolSize
	into patrolSize
	from tbl_Pistol_Competition
	where Id = compId;
	
	
	insert into PatrolMembers (
		CompetitionDayId,
		PatrolId,
		Seats
	)
	select
		p.CompetitionDayId,
		p.Id,
		patrolSize - count(e.Id)
	from tbl_Pistol_Patrol p
	join tbl_Pistol_CompetitionDay d on (d.Id = p.CompetitionDayId and d.CompetitionId = compId)
	join tbl_Pistol_PatrolGun g on (g.GunClassId = pGunClassId and g.PatrolId = p.Id)
	left join tbl_Pistol_Entry e on (e.PatrolId = p.Id)
	group by p.CompetitionDayId, p.Id;
	
	
	delete m
	from PatrolMembers m
	join tbl_Pistol_Entry e on (e.PatrolId = m.PatrolId and e.ShotId = pShotId);
	
	
	select max(StaPlats) into currentSeatNo from tbl_Pistol_Entry e where e.PatrolId = patrolId;
	
  if currentSeatNo is null then
    set currentSeatNo = 0;
  end if;
	
	insert into tbl_Pistol_Entry (
		ShotId,
		GunClassificationId,
		PatrolId,
		ShotClassId,
		Status,
    	RegisterDate,
    	StaPlats,
    	BokadAvShotId
	)
	values (
		pShotId,
		pGunClassId,
		patrolId,
		pShotClassId,
		'U',
    	now(),
    	currentSeatNo + 1,
    	pBokadAvShotId
	);
	
	select last_insert_id()
	into eId;
	
	set pStatus = 'OK';
	
	
	end main;
	
	drop temporary table PatrolMembers;
	
	select pStatus as Status, eId as EntryId;
end $$
