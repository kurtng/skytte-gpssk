-- DELIMITER $$

DROP PROCEDURE IF EXISTS `ListDaysToBook` $$

CREATE PROCEDURE  `ListDaysToBook`(in compId bigint, in shotId bigint)
begin
	
	main: begin
	
	
	
	create temporary table compDays (
		Id bigint,
		DayNo smallint
	);
	
	
	
	insert into compDays (Id, DayNo)
	select d.Id, d.DayNo
	from tbl_Pistol_CompetitionDay d
	where d.CompetitionId = compId;
	
	
	delete t
	from compDays t
	join tbl_Pistol_Patrol p on (p.CompetitionDayId = t.Id)
	join tbl_Pistol_Entry e on (e.PatrolId = p.Id and e.ShotId = shotId);
	
	
	select distinct Id, DayNo as Name
	from compDays
	order by DayNo;
	
	drop temporary table compDays;
	
	end main;
end $$



