-- DELIMITER $$

DROP PROCEDURE IF EXISTS `GenerateSchedule` $$

CREATE PROCEDURE  `GenerateSchedule`(in pCompDayId bigint)
begin
	declare pStatus varchar(10) default 'FAILED';
	
	main: begin
	
	declare firstStart datetime;
  	declare timeBegin datetime;
	declare lastStart datetime;
	declare stationId int default 0;
	declare maxStation int default 0;
	declare intervalMinutes smallint;
	declare curItem varchar(20) default 'Station';
    declare scoreType varchar(1) default'N';
	declare noMoreStations tinyint default 0;
	declare noMorePatrols tinyint default 0;
	declare patrolId bigint;
	declare startTime datetime default null;
	declare vSortOrder smallint;
	
	set pStatus = 'OK';
	
	if exists (select e.Id 
		from tbl_Pistol_Patrol p
		join tbl_Pistol_Entry e on (e.PatrolId = p.Id)
		where p.CompetitionDayId = pCompDayId
		)
	then
		set pStatus = 'ILLEGAL';
		leave main;
	end if;
	
	
	if not exists(select Id from tbl_Pistol_CompetitionDay where Id = pCompDayId)
	then
		set pStatus = 'NOT_FOUND';
		leave main;
	end if;
	
	delete from tbl_Pistol_Schedule
	where CompetitionDayId = pCompDayId;
	
	
	select timestamp(c.StartDate + interval (d.DayNo - 1) day, time(d.FirstStart)),
		timestamp(c.StartDate + interval (d.DayNo - 1) day, time(d.LastStart)),
		d.MaxStation,
		d.PatrolSpace, c.scoreType
	into firstStart, lastStart, maxStation, intervalMinutes, scoreType
	from tbl_Pistol_CompetitionDay d
	join tbl_Pistol_Competition c on (c.Id = d.CompetitionId)
	where d.Id = pCompDayId;
	
	set stationId = 0;

    set timeBegin=firstStart;
	
	while stationId < maxStation do
	
		set startTime = firstStart;
   		if  scoreType='P' then -- G�khan 2010-10-20
        	set startTime=timeBegin;  
    	end if;
		set patrolId = 0;
		set stationId = stationId + 1; 
		set firstStart = firstStart + interval intervalMinutes minute;
		
		select min(SortOrder)
		into vSortOrder
		from tbl_Pistol_Patrol
		where CompetitionDayId = pCompDayId;
	
		select Id
		into patrolId
		from tbl_Pistol_Patrol
		where CompetitionDayId = pCompDayId
		and SortOrder = vSortOrder;
	
		while patrolId is not null do
	
			insert into tbl_Pistol_Schedule (
				CompetitionDayId,
				Station,
				StartTime,
				PatrolId
				)
			value (
				pCompDayId,
				stationId,
				startTime,
				patrolId
			);
	
            set startTime = startTime + interval intervalMinutes minute;
	
			select min(SortOrder)
			into vSortOrder
			from tbl_Pistol_Patrol
			where CompetitionDayId = pCompDayId
			and SortOrder > vSortOrder;
	
			if vSortOrder is null then
				set patrolId = null;
			else
				select Id
				into patrolId
				from tbl_Pistol_Patrol
				where CompetitionDayId = pCompDayId
				and SortOrder = vSortOrder;
			end if;
		end while;
	
	end while;
	
	drop temporary table if exists FirstStarts;
	
	create temporary table FirstStarts (
	        PatrolId bigint not null,
	        StartTime datetime null
	);
	
	insert into FirstStarts(PatrolId, StartTime)
	select distinct s.PatrolId, min(s.StartTime)
	from tbl_Pistol_Schedule s
	where s.CompetitionDayId = pCompDayId
	group by s.PatrolId;
	
	
	update tbl_Pistol_Patrol p
	join FirstStarts f on (f.PatrolId = p.Id)
	set p.StartTime = f.StartTime;
	
	
	set pStatus = 'OK';
	
	end main;
	select pStatus as Status;
end $$


