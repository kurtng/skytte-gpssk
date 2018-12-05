-- DELIMITER $$




DROP PROCEDURE IF EXISTS `RegisterEntryPatrol` $$

CREATE PROCEDURE  `RegisterEntryPatrol`(in pEntryId bigint,
	in pPatrolId bigint)
begin
	declare pStatus varchar(10) default 'FAILED';
	
	main: begin
	
	declare compDayId bigint;
	declare eId bigint;
	
	
	set pStatus = 'FAILED';
	
	
	select CompetitionDayId
	into compDayId
	from tbl_Pistol_Patrol
	where Id = pPatrolId;
	
	start transaction;
	
	
	delete e
	from tbl_Pistol_EntryPatrol e
	join tbl_Pistol_Patrol p on (p.Id = e.PatrolId and p.CompetitionDayId = compDayId)
	where e.EntryId = pEntryId;
	
	
	insert into tbl_Pistol_EntryPatrol (EntryId, PatrolId)
	values (pEntryId, pPatrolId);
	
	commit;
	set pStatus = 'OK';
	
	end main;
	
	select pStatus as Status;
end $$



