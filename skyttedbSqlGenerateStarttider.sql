-- DELIMITER $$
DROP PROCEDURE IF EXISTS `GenerateStarttider` $$
CREATE PROCEDURE `GenerateStarttider`(in pCompDayId bigint)
begin
	
	main: begin
	
	declare firstStart datetime;
  declare timeBegin datetime;
	declare lastStart datetime;
	declare maxStation int default 0;
	declare intervalMinutes smallint;
	declare scoreType varchar(1) default'N';
	declare patrolId bigint;
	declare startTime datetime default null;
	declare vSortOrder smallint;
	
	select timestamp(c.StartDate + interval (d.DayNo) day, time(d.FirstStart)),
		timestamp(c.StartDate + interval (d.DayNo) day, time(d.LastStart)),
		d.MaxStation,
		d.PatrolSpace, c.scoreType
	into firstStart, lastStart, maxStation, intervalMinutes, scoreType
	from tbl_Pistol_CompetitionDay d
	join tbl_Pistol_Competition c on (c.Id = d.CompetitionId)
	where d.Id = pCompDayId;

    select min(SortOrder)
		into vSortOrder
		from tbl_Pistol_Patrol
		where CompetitionDayId = pCompDayId;

    set timeBegin=firstStart;
	
	sloop: while true do
	
		set startTime = firstStart;
		
    set firstStart = firstStart + interval intervalMinutes minute;
		
    set patrolId = -1;
		
		select Id
		into patrolId
		from tbl_Pistol_Patrol
		where CompetitionDayId = pCompDayId
		and SortOrder = vSortOrder;
		
    if patrolId = -1 then
      
        leave sloop;
     end if;

    
		
    update tbl_Pistol_Patrol p
		set p.StartTime = startTime
    where patrolId = p.Id;

    set vSortOrder = vSortOrder + 1;
	
	end while;
	select 'OK';
	end main;
end