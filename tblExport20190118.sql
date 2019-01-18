-- phpMyAdmin SQL Dump
-- version 3.5.8.1
-- http://www.phpmyadmin.net
--
-- Host: okrets.se.mysql:3306
-- Generation Time: Jan 18, 2019 at 08:16 AM
-- Server version: 10.3.10-MariaDB-1:10.3.10+maria~bionic
-- PHP Version: 7.2.10-0ubuntu0.18.04.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `okrets_se`
--

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `CancelEntry`$$
CREATE DEFINER=`okrets_se`@`%` PROCEDURE `CancelEntry`(in eId bigint)
begin	
	declare pStatus varchar(10) default 'FAILED';

	main: begin

	set pStatus = 'FAILED';


	if not exists (select Id from tbl_Pistol_Entry where Id = eId)
	then
	  set pStatus = 'NOT_FOUND';
	  leave main;
	end if;
	
	delete from tbl_Pistol_Entry
	where Id = eId;
	
	set pStatus = 'OK';
	
	end main;
	
	select pStatus as Status;
end$$

DROP PROCEDURE IF EXISTS `GenerateSchedule`$$
CREATE DEFINER=`okrets_se`@`%` PROCEDURE `GenerateSchedule`(in pCompDayId bigint)
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
   		if  scoreType='P' then 
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
end$$

DROP PROCEDURE IF EXISTS `GenerateStarttider`$$
CREATE DEFINER=`okrets_se`@`%` PROCEDURE `GenerateStarttider`(in pCompDayId bigint)
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
end$$

DROP PROCEDURE IF EXISTS `ListAvailability`$$
CREATE DEFINER=`okrets_se`@`%` PROCEDURE `ListAvailability`(in pCompDayId bigint, in pGunClassId bigint)
begin
	
	main: begin
	
	drop temporary table if exists patrols;
	
	create temporary table patrols (
		PatrolId bigint,
		CompetitionId int,
		Members int,
		FirstStartTime datetime,
		LastStartTime datetime
	);
	
	
	insert into patrols (PatrolId, CompetitionId, Members, FirstStartTime)
	select distinct p.Id, d.CompetitionId, count(distinct e.Id), p.StartTime
	from tbl_Pistol_CompetitionDay d
	join tbl_Pistol_Patrol p on (p.CompetitionDayId = d.Id)
	join tbl_Pistol_PatrolGun g on (g.PatrolId = p.Id and g.GunClassId = pGunClassId)
	left join tbl_Pistol_Entry e on (e.PatrolId = p.Id)
	where d.Id = pCompDayId
	group by p.Id, d.CompetitionId;
	
	
	select t.PatrolId as Id,
		p.SortOrder,
		time_format(time(t.FirstStartTime), '%H:%i') as FirstStart,
		
		time_format(time(t.LastStartTime), '%H:%i') as LastStart,
		
		c.MaxPatrolSize as PatrolSize,
		c.MaxPatrolSize - t.Members as Availability
	from patrols t
	join tbl_Pistol_Patrol p on (p.Id = t.PatrolId)
	join tbl_Pistol_Competition c on (c.Id = t.CompetitionId)
	order by t.FirstStartTime;
	
	drop temporary table patrols;
	
	end main;
end$$

DROP PROCEDURE IF EXISTS `ListAvailablePatrols`$$
CREATE DEFINER=`okrets_se`@`%` PROCEDURE `ListAvailablePatrols`(in pShotId bigint, in pGunClassId bigint, in pCompId bigint)
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
end$$

DROP PROCEDURE IF EXISTS `ListCompICanEnter`$$
CREATE DEFINER=`okrets_se`@`%` PROCEDURE `ListCompICanEnter`(in pShotId bigint, in pGunClassId bigint)
begin
	declare pStatus varchar(10) default 'FAILED';
	
	main: begin
	
	declare firstStart datetime;
	declare lastStart datetime;
	declare stationId int default 0;
	declare maxStation int default 0;
	declare intervalMinutes smallint;
	declare curItem varchar(20) default 'Station';
	declare noMoreStations tinyint default 0;
	declare noMorePatrols tinyint default 0;
	declare patrolId bigint;
	declare startTime datetime default null;
	
	
	set pStatus = 'OK';
	
	drop temporary table if exists comps;
	
	create temporary table comps (
		CompId bigint
	);
	
	
	
	insert into comps (CompId)
	select c.Id
	from tbl_Pistol_Competition c
	where c.Status between 1 and 3;
	
	
	delete t
	from comps t
	join tbl_Pistol_Entry e on (e.CompetitionId = t.CompId and e.ShotId = pShotId and e.GunClassificationId = pGunClassId);
	
	
	select distinct t.CompId as Id, c.Name
	from comps t
	join tbl_Pistol_Competition c on (c.Id = t.CompId)
	order by c.Name;
	
	drop temporary table comps;
	
	end main;
end$$

DROP PROCEDURE IF EXISTS `ListDaysToBook`$$
CREATE DEFINER=`okrets_se`@`%` PROCEDURE `ListDaysToBook`(in compId bigint, in shotId bigint)
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
end$$

DROP PROCEDURE IF EXISTS `ListMissingScores`$$
CREATE DEFINER=`okrets_se`@`%` PROCEDURE `ListMissingScores`(in pCompId bigint)
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

end$$

DROP PROCEDURE IF EXISTS `ListResult`$$
CREATE DEFINER=`okrets_se`@`%` PROCEDURE `ListResult`(in pCompId bigint, in pShotClassId bigint, in pVerbose char(1), in gunClassId bigint , in isOnlyBorderResult boolean )
begin
	
	
	
	
	
	declare cur_super cursor for
	  select r.EntryId, s.StationId, s.Hits, s.Targets
	  from Result r
	  join tbl_Pistol_Score s on (s.EntryId = r.EntryId)
      where s.StationId>0
	  order by r.EntryId, s.StationId desc;
      
  declare cur_extra cursor for
	  select r.EntryId, s.StationId, s.Hits, s.Targets
	  from Result r
	  join tbl_Pistol_Score s on (s.EntryId = r.EntryId)
      where s.StationId<0
	  order by r.EntryId, abs(s.StationId) desc;
	
	
	declare cur_nor cursor for
	  select s.EntryId,
		s.ShotClassId,
		s.Hits,
		s.Targets,
		s.Total,
		s.Points,
		s.SuperScore,
    s.ExtraScore
	  from Result s
	  join tbl_Pistol_Entry e on (e.Id = s.EntryId)
	  join tbl_Pistol_Shot p on (p.Id = e.ShotId)
	  join tbl_Pistol_ShotClass c on (c.Id = s.ShotClassId)
	  order by s.ShotClassId, s.Total desc, s.Points desc, s.SuperScore desc, s.ExtraScore desc;
	
	
	declare cur_nor_grp cursor for
	  select s.EntryId,
		s.MedalGroupId,
		s.Hits,
		s.Targets,
		s.Total,
		s.Points,
		s.SuperScore,
    s.ExtraScore
	  from Result s
	  join tbl_Pistol_Entry e on (e.Id = s.EntryId)
	  join tbl_Pistol_Shot p on (p.Id = e.ShotId)
	  join tbl_Pistol_ShotClass c on (c.Id = s.ShotClassId)
	  order by s.MedalGroupId, s.Total desc, s.Points desc, s.SuperScore desc, s.ExtraScore desc;
	
	
	declare cur_cls cursor for
	  select s.EntryId,
		s.ShotClassId,
		s.Hits,
		s.Targets,
		s.Total,
		s.Points,
		s.SuperScore,
    s.ExtraScore
	  from Result s
	  join tbl_Pistol_Entry e on (e.Id = s.EntryId)
	  join tbl_Pistol_Shot p on (p.Id = e.ShotId)
	  join tbl_Pistol_ShotClass c on (c.Id = s.ShotClassId)
	  order by s.ShotClassId, s.Hits desc, s.Targets desc, s.Points desc, s.SuperScore desc, s.ExtraScore desc;
      
     
	
	
	declare cur_cls_grp cursor for
	  select s.EntryId,
		s.MedalGroupId,
		s.Hits,
		s.Targets,
		s.Total,
		s.Points,
		s.SuperScore,
    s.ExtraScore
	  from Result s
	  join tbl_Pistol_Entry e on (e.Id = s.EntryId)
	  join tbl_Pistol_Shot p on (p.Id = e.ShotId)
	  join tbl_Pistol_ShotClass c on (c.Id = s.ShotClassId)
	  order by s.MedalGroupId, s.Hits desc, s.Targets desc, s.Points desc, s.SuperScore desc, s.ExtraScore desc;
	
    
    
declare cur_cls_prec cursor for
	  select s.EntryId,
		s.ShotClassId,
		s.Hits,
		s.Targets,
		s.Total,
		s.Points,
		s.SuperScore,
    s.ExtraScore
	  from Result s
	  join tbl_Pistol_Entry e on (e.Id = s.EntryId)
	  join tbl_Pistol_Shot p on (p.Id = e.ShotId)
	  join tbl_Pistol_ShotClass c on (c.Id = s.ShotClassId)
	  order by s.ShotClassId,  s.Total desc, s.Points desc, s.ExtraScore desc, s.SuperScore desc;
      
declare cur_cls_grp_prec cursor for
	  select s.EntryId,
		s.MedalGroupId,
		s.Hits,
		s.Targets,
		s.Total,
		s.Points,
		s.SuperScore,
    s.ExtraScore
	  from Result s
	  join tbl_Pistol_Entry e on (e.Id = s.EntryId)
	  join tbl_Pistol_Shot p on (p.Id = e.ShotId)
	  join tbl_Pistol_ShotClass c on (c.Id = s.ShotClassId)
	  order by s.MedalGroupId, s.Total desc, s.Points desc, s.ExtraScore desc, s.SuperScore desc; 
      
	
	
	if (pShotClassId = 0)
	then
		set pShotClassId = null;
	end if;
    
if (gunClassId = 0)
	then
		set gunClassId = null;
	end if;
	
	
	
	
	drop temporary table if exists Result;
	
	create temporary table Result (
		EntryId bigint not null,
		ShotClassId bigint not null,
		MedalGroupId bigint null,
		Hits int null,
		Targets int null,
		Total int null,
		SuperScore varchar(200) null, 
		Points int null,
		Place int null,
		GroupPlace int null,
		Medal char(1) null,
    ExtraScore varchar(200) null
	);
	
	drop temporary table if exists MedalGroups;
	
	create temporary table MedalGroups (
		MedalGroupId bigint not null,
		People int not null,
		LastBronzeTotal int null,
		LastBronze int null,
		LastSilverTotal int null,
		LastSilver int null
	);
	
	
	
	main: begin
	
	
	
	declare no_more_rows int default 0;
	declare vCompDayId bigint;
	declare vStationId smallint;
	declare vMaxStation int;
	declare vScoreType char(1);
	
	declare vEntryId bigint;
	declare vShotClassId bigint;
	declare vGroupId bigint;
	declare vShotClass varchar(50);
	declare vShotName varchar(150);
	declare vHits int;
	declare vTargets int;
	declare vTotal int;
	declare vPoints int;
	declare vPlace int default 0;
	declare vPrevClassId bigint;
	declare vPrevGroupId bigint;
	declare vLastBronze int;
	declare vLastSilver int;
	declare vLastBronzeTotal int;
	declare vLastSilverTotal int;
	declare vCurEntryId bigint;
	declare vSuperScore varchar(200);
  declare vExtraScore varchar(200);
  declare vExtraTotal int;
	
	
	
	declare continue handler for not found set no_more_rows = 1;
	
	
	if not exists (select Id from tbl_Pistol_Competition
		where Id = pCompId
		and Status > 2)
	then
		
		leave main;
	end if;
	
	
	select ScoreType
	into vScoreType
	from tbl_Pistol_Competition
	where Id = pCompId;
	
	
	insert into Result (
		EntryId,
		ShotClassId,
		Hits,
		Targets,
		Total,
		Points
	)
	select s.EntryId,
		e.ShotClassId,
		
    case vScoreType
			when 'P' or 'S' then sum(decodePrecision(ifnull(s.Hits,0)))
			else sum( ifnull(s.Hits,0) )
			end,
		sum( ifnull(s.Targets, 0)),
		case vScoreType
			when 'N' then sum( ifnull(s.Hits,0) + ifnull(s.Targets,0) )
      when 'P' or 'S' then sum(decodePrecision(ifnull(s.Hits,0)))
			else sum( ifnull(s.Hits,0) )
			end,
		sum( ifnull(s.Points, 0) )
	from tbl_Pistol_Score s
	join tbl_Pistol_CompetitionDay d on (d.Id = s.CompetitionDayId and d.CompetitionId = pCompId)
	
	join tbl_Pistol_Entry e on (e.Id = s.EntryId
		and e.ShotClassId = ifnull(pShotClassId, e.ShotClassId)
    and e.GunClassificationId = ifnull(gunClassId, e.GunClassificationId))
	group by s.EntryId, e.ShotClassId;
	
	
	
    
	open cur_super;
	
	set vCurEntryId = 0;
	set no_more_rows = 0;
	set vSuperScore = '';
	
	cur_loop0: loop
    fetch cur_super
		into vEntryId, vStationId, vHits, vTargets;
	
    if vScoreType = 'P' or vScoreType = 'S' then
			set vHits = decodePrecision(ifnull(vHits,0));
			end if ;
            
    if no_more_rows then
			
			if (vCurEntryId > 0)
			then
				update Result
				set SuperScore = vSuperScore
				where EntryId = vCurEntryId;
			end if;
	
			close cur_super;
			leave cur_loop0;
		end if;
	
		if (vEntryId != vCurEntryId)
		then
			if (vCurEntryId > 0)
			then
				update Result
				set SuperScore = vSuperScore
				where EntryId = vCurEntryId;
			end if;
	
			set vCurEntryId = vEntryId;
			set vSuperScore = '';
		end if;
	
		set vSuperScore = concat(vSuperScore, '-',
			right(concat('000', cast((vHits + vTargets) as char)), 3)
			);
            
	
	end loop cur_loop0;
    
    
    
    open cur_extra;
	
  set vCurEntryId = 0;
	set no_more_rows = 0;
	set vExtraScore = '';
  set vExtraTotal = 0;
	
	cur_loope: loop
		fetch cur_extra
		into vEntryId, vStationId, vHits, vTargets;
	
		if vScoreType = 'P' or vScoreType ='S' then
			set vHits = decodePrecision(ifnull(vHits,0));
			end if ;
            
		if no_more_rows then
			
			if (vCurEntryId > 0)
			then
        set vExtraScore = concat(
            vExtraScore,
            '-',
            right(concat('000', cast((vExtraTotal) as char)), 3)
        );
				update Result
				set ExtraScore = vExtraScore, Total = Total + vExtraTotal
				where EntryId = vCurEntryId;
			end if;
	
			close cur_extra;
			leave cur_loope;
		end if;
	
		if (vEntryId != vCurEntryId)
		then
			if (vCurEntryId > 0)
			then
        set vExtraScore = concat(
            vExtraScore,
            '-',
            right(concat('000', cast((vExtraTotal) as char)), 3)
        );
				update Result
				set ExtraScore = vExtraScore, Total = Total + vExtraTotal
				where EntryId = vCurEntryId;
			end if;
	
			set vCurEntryId = vEntryId;
			set vExtraScore = '';
      set vExtraTotal = 0;
		end if;
	
    set vExtraScore = concat(vExtraScore, '-',
			right(concat('000', cast((vHits + vTargets) as char)), 3)
			);
            
    set vExtraTotal = vExtraTotal + vHits + vTargets;
            
	
	end loop cur_loope;
	
	
	
	
	
	update Result r
	join tbl_Pistol_MedalGroupMember m on (m.ShotClassId = r.ShotClassId)
	set r.MedalGroupId = m.MedalGroupId
	;
	
	
	insert into MedalGroups (
		MedalGroupId,
		People
	)
	select MedalGroupId,
		count(*)
	from Result r
	group by MedalGroupId;
	
	
	update MedalGroups
	set	LastBronze = case when round(People/3, 0) = 0 then 1
			else round(People/3, 0)
			end,
		LastSilver = case 
			when isOnlyBorderResult and People > 9 and People < 60 then 10
			when isOnlyBorderResult and People < 10 then People
			when round(People/9, 0) = 0 then 1
			when isOnlyBorderResult then round(People/6, 0)
			else round(People/9, 0)
			end;
	
	
	
	
	
	
	if (vScoreType = 'N')
	then
		open cur_nor;
	else
    if (vScoreType = 'P' or vScoreType = 'S')
    then
        open cur_cls_prec;
    else
        open cur_cls;
    end if;
	end if;
	
	set vPrevClassId = null;
	set no_more_rows = 0;
	
	cur_loop1: loop
	  if (vScoreType = 'N') then
		fetch cur_nor
	  	into vEntryId, vShotClassId,
		vHits, vTargets,
		vTotal, vPoints, vSuperScore, vExtraScore;
	  else
      if (vScoreType = 'P' or vScoreType = 'S') then
      fetch cur_cls_prec
	  	into vEntryId, vShotClassId,
		vHits, vTargets,
		vTotal, vPoints, vSuperScore, vExtraScore;
      else
		fetch cur_cls
	  	into vEntryId, vShotClassId,
		vHits, vTargets,
		vTotal, vPoints, vSuperScore, vExtraScore;
        end if;
	  end if;
	
	  if no_more_rows then
	  	if (vScoreType = 'N') then
			close cur_nor;
		else
        if (vScoreType = 'P' or vScoreType = 'S') then
        close cur_cls_prec;
        else
			close cur_cls;
		end if;
        end if;
		leave cur_loop1;
	  end if;
	
	  if vPrevClassId is null
	  then
		set vPrevClassId = vShotClassId;
		set vPlace = 1;
	  else
		if (vPrevClassId != vShotClassId)
		then
			set vPlace = 1;
			set vPrevClassId = vShotClassId;
		else
			set vPlace = vPlace + 1;
		end if;
	  end if;
	
	  update Result
	  set Place = vPlace
	  where EntryId = vEntryId;
	
	end loop cur_loop1;
	
	
	
	
	if (vScoreType = 'N')
	then
		open cur_nor_grp;
	else
    	if (vScoreType = 'P' or vScoreType = 'S')
	then
    open cur_cls_grp_prec;
else
		open cur_cls_grp;
        end if;
	end if;
	
	set vPrevGroupId = null;
	set vPlace = 0;
	set no_more_rows = 0;
	
	cur_loop2: loop
	  if (vScoreType = 'N') then
		fetch cur_nor_grp
	  	into vEntryId, vGroupId,
		vHits, vTargets,
		vTotal, vPoints, vSuperScore, vExtraScore;
	  else
      if (vScoreType = 'P' or vScoreType = 'S') then
      	fetch cur_cls_grp_prec
	  	into vEntryId, vGroupId,
		vHits, vTargets,
		vTotal, vPoints, vSuperScore, vExtraScore;
      else
		fetch cur_cls_grp
	  	into vEntryId, vGroupId,
		vHits, vTargets,
		vTotal, vPoints, vSuperScore, vExtraScore;
        end if;
	  end if;
	
	  if no_more_rows then
	  	if (vScoreType = 'N') then
			close cur_nor_grp;
		else
        if (vScoreType = 'P' or vScoreType = 'S') then
        close cur_cls_grp_prec;
        else
			close cur_cls_grp;
            end if;
		end if;
		leave cur_loop2;
	  end if;
	
	  if vPrevGroupId is null
	  then
		set vPrevGroupId = vGroupId;
		set vPlace = 1;
	  else
		if (vPrevGroupId != vGroupId)
		then
			set vPlace = 1;
			set vPrevGroupId = vGroupId;
		else
			set vPlace = vPlace + 1;
		end if;
	  end if;
	
	  update Result
	  set GroupPlace = vPlace
	  where EntryId = vEntryId;
	
	end loop cur_loop2;
	
	
	
	
	
	update MedalGroups g
	join Result s on (s.MedalGroupId = g.MedalGroupId and s.GroupPlace = g.LastBronze)
	set	LastBronzeTotal = s.Total;
	
	
	update MedalGroups g
	join Result s on (s.MedalGroupId = g.MedalGroupId and s.GroupPlace = g.LastSilver)
	set	LastSilverTotal = s.Total;
	
	update Result r
	join MedalGroups g on (g.MedalGroupId = r.MedalGroupId)
	set Medal = case
			when r.Total >= g.LastSilverTotal and LastSilverTotal is not null then 'S'
			when r.Total >= g.LastBronzeTotal and r.Total < g.LastSilverTotal then 'B'
			else ''
			end
	;
	

    if isOnlyBorderResult then
       
       select 
            p.Id PatrolId, 
            p.SortOrder, 
            e.Status as EntryStatus, 
            concat(s.FirstName, ' ', s.LastName) as ShotName, 
            s.GunCard, 
            c.Name as ClubName, 
            g.Grade as GunClassName, 
            sc.Name as ShotClassName, 
            s.Id as ShotId, 
            e.Id as EntryId,
            r.Total
        from tbl_Pistol_Competition comp 
            join tbl_Pistol_CompetitionDay compday on (comp.id = compday.CompetitionId) 
            join tbl_Pistol_Patrol p on (compday.id = p.CompetitionDayId ) 
            join tbl_Pistol_Entry e on (e.PatrolId = p.Id) 
            join tbl_Pistol_Shot s on (s.Id = e.ShotId) 
            join tbl_Pistol_Club c on (c.Id = s.ClubId) 
            join tbl_Pistol_GunClassification g on (g.Id = e.GunClassificationId) 
            join tbl_Pistol_ShotClass sc on (sc.Id = e.ShotClassId)    
            join Result r on (r.EntryId = e.Id)
        where comp.Id = pCompId 
            and e.GunClassificationId = gunClassId 
            and r.Total >= (select max(LastSilverTotal) from MedalGroups)
        order by r.Total desc,p.SortOrder, s.LastName, s.FirstName ;
        
    else
        if (pVerbose = 'Y') then
            select p.Id as ShotId,
                s.EntryId,
                s.ShotClassId,
                c.Name as ShotClass,
                b.Name as ClubName,
                concat(p.FirstName, ' ', p.LastName) ShotName,
                s.Hits,
                s.Targets,
                s.Total,
                s.Points,
                s.Place,
                s.Medal,
                s.GroupPlace,
                r.StationId,
                r.Hits StationHits,
                r.Targets StationTargets,
                r.Points StationPoints,
                s.SuperScore,
                s.ExtraScore,
                g.Grade as GunClassName
            from Result s
            join tbl_Pistol_Entry e on (e.Id = s.EntryId)
            join tbl_Pistol_Shot p on (p.Id = e.ShotId)
            join tbl_Pistol_ShotClass c on (c.Id = s.ShotClassId)
            join tbl_Pistol_GunClassification g on (g.Id = e.GunClassificationId)
            left join tbl_Pistol_Score r on (r.EntryId = s.EntryId)
            left join tbl_Pistol_Club b on (b.Id = p.ClubId)
            where r.StationId > 0
            order by s.ShotClassId, s.Place, r.StationId;
        else
            select p.Id as ShotId,
                s.EntryId,
                s.ShotClassId,
                c.Name as ShotClass,
                b.Name as ClubName,
                concat(p.FirstName, ' ', p.LastName) ShotName,
                s.Hits,
                s.Targets,
                s.Total,
                s.Points,
                s.Place,
                s.Medal,
                s.GroupPlace,
                s.SuperScore,
                s.ExtraScore,
                g.Grade as GunClassName
            from Result s
            join tbl_Pistol_Entry e on (e.Id = s.EntryId)
            join tbl_Pistol_Shot p on (p.Id = e.ShotId)
            join tbl_Pistol_ShotClass c on (c.Id = s.ShotClassId)
            join tbl_Pistol_GunClassification g on (g.Id = e.GunClassificationId)
            left join tbl_Pistol_Club b on (b.Id = p.ClubId)
            order by s.ShotClassId, s.Place;
        end if;
    end if;
	
	
	end main;
	
	drop temporary table if exists Result;
	drop temporary table if exists MedalGroups;

end$$

DROP PROCEDURE IF EXISTS `ListScores`$$
CREATE DEFINER=`okrets_se`@`%` PROCEDURE `ListScores`(in pPatrolId bigint, in pShotId bigint, in isExtra boolean, in competitionId bigint, in gunClassId bigint)
begin

    if isExtra then 
        select ABS(s.StationId) as StationId, s.Hits, s.Targets, s.Points
        from tbl_Pistol_Score s
        join tbl_Pistol_Entry e on (s.EntryId = e.Id and e.ShotId = pShotId)
        where s.CompetitionId = competitionId and e.GunClassificationId = gunClassId
        and s.StationId<0
        order by ABS(s.StationId);
    else
        select s.StationId, s.Hits, s.Targets, s.Points
            from tbl_Pistol_Patrol p
            join tbl_Pistol_Entry e on (e.PatrolId = p.Id and e.ShotId = pShotId)
            join tbl_Pistol_Score s on (s.CompetitionDayId = p.CompetitionDayId and s.EntryId = e.Id)
            where p.Id = pPatrolId
            and s.StationId>0
            order by s.StationId;
    end if;

end$$

DROP PROCEDURE IF EXISTS `ListTeamResult`$$
CREATE DEFINER=`okrets_se`@`%` PROCEDURE `ListTeamResult`(in pCompId bigint)
begin
	
	declare cur_super cursor for
	  select r.TeamId, s.StationId, s.Hits, s.Targets
	  from Result r
	  join tbl_Pistol_Score s on (s.EntryId = r.EntryId)
	  order by r.EntryId, s.StationId desc;
	
	
	
	declare cur_nor cursor for
	  select s.TeamId,
	  	t.GunClassId,
		s.Hits,
		s.Targets,
		s.Total,
		s.Points
	  from Result s
	  join tbl_Pistol_Team t on (t.Id = s.TeamId)  
	  order by t.GunClassId, s.Total desc, s.Points desc;
	
	
	declare cur_cls cursor for
	  select s.TeamId,
	  	t.GunClassId,
		s.Hits,
		s.Targets,
		s.Total,
		s.Points
	  from Result s
	  join tbl_Pistol_Team t on (t.Id = s.TeamId)
	  order by t.GunClassId, s.Hits desc, s.Targets desc, s.Points desc;
	
	
	
	
	
	drop temporary table if exists Result;
	
	create temporary table Result (
		TeamId bigint not null,
		Hits int null,
		Targets int null,
		Total int null,
		Points int null,
		SuperScore varchar(100) null, 
		Place int null
	);
	
	
	
	
	main: begin
	
	
	
	declare no_more_rows int default 0;
	declare vCompDayId bigint;
	declare vStationId smallint;
	declare vMaxStation int;
	declare vScoreType char(1);
	declare vSuperScore varchar(100);
	declare vEntryId bigint;
	declare vTeamId bigint;
	declare vGunClassId bigint;
	declare vGroupId bigint;
	declare vHits int;
	declare vTargets int;
	declare vTotal int;
	declare vPoints int;
	declare vPlace int default 0;
	declare vPrevClassId bigint;
	declare vPrevGroupId bigint;
	
	
	
	declare continue handler for not found set no_more_rows = 1;
	
	
	select ScoreType
	into vScoreType
	from tbl_Pistol_Competition
	where Id = pCompId;
	
	
	insert into Result (
		TeamId,
		Hits,
		Targets,
		Total,
		Points
	)
	select e.TeamId,
		    	case vScoreType
			when 'P' then sum(decodePrecision(ifnull(s.Hits,0)))
			else sum( ifnull(s.Hits,0) )
			end,
		sum( ifnull(s.Targets, 0)),
		case vScoreType
			when 'N' then sum( ifnull(s.Hits,0) + ifnull(s.Targets,0) )
      		when 'P' then sum(decodePrecision(ifnull(s.Hits,0)))
			else sum( ifnull(s.Hits,0) )
			end,
		sum( ifnull(s.Points, 0) )
	from tbl_Pistol_Score s
	join tbl_Pistol_CompetitionDay d on (d.Id = s.CompetitionDayId and d.CompetitionId = pCompId)
	join tbl_Pistol_Entry e on (e.Id = s.EntryId and e.TeamId is not null)
	group by e.TeamId;
	
	
	
	if (vScoreType = 'N')
	then
		open cur_nor;
	else
		open cur_cls;
	end if;
	
	set vPrevClassId = null;
	
	cur_loop1: loop
	  if (vScoreType = 'N') then
		fetch cur_nor
	  	into vTeamId, vGunClassId,
		vHits, vTargets,
		vTotal, vPoints;
	  else
		fetch cur_cls
	  	into vTeamId, vGunClassId,
		vHits, vTargets,
		vTotal, vPoints;
	  end if;
	
	  if no_more_rows then
	  	if (vScoreType = 'N') then
			close cur_nor;
		else
			close cur_cls;
		end if;
		leave cur_loop1;
	  end if;
	
	  if vPrevClassId is null
	  then
		set vPrevClassId = vGunClassId;
		set vPlace = 1;
	  else
		if (vPrevClassId != vGunClassId)
		then
			set vPlace = 1;
			set vPrevClassId = vGunClassId;
		else
			set vPlace = vPlace + 1;
		end if;
	  end if;
	
	  update Result
	  set Place = vPlace
	  where TeamId = vTeamId;
	
	end loop cur_loop1;
	
	
	
	
	select	s.TeamId,
		t.GunClassId,
		t.Name TeamName,
		b.Name ClubName,
		g.Grade GunGrade,
		g.Description GunGradeDesc,
		s.Hits,
		s.Targets,
		s.Total,
		s.Points,
		s.Place
	from Result s
	join tbl_Pistol_Team t on (t.Id = s.TeamId)
	join tbl_Pistol_GunClassification g on (g.Id = t.GunClassId)
	left join tbl_Pistol_Club b on (b.Id = t.ClubId)
	order by t.GunClassId, s.Place;
	
	end main;
	
	drop temporary table if exists Result;

end$$

DROP PROCEDURE IF EXISTS `RegisterEntry`$$
CREATE DEFINER=`okrets_se`@`%` PROCEDURE `RegisterEntry`(in patrolId bigint,
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
end$$

DROP PROCEDURE IF EXISTS `RegisterEntryPatrol`$$
CREATE DEFINER=`okrets_se`@`%` PROCEDURE `RegisterEntryPatrol`(in pEntryId bigint,
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
end$$

DROP PROCEDURE IF EXISTS `RegisterScore`$$
CREATE DEFINER=`okrets_se`@`%` PROCEDURE `RegisterScore`(in pEntryId bigint,
	in pCompDayId bigint,
	in pStationId smallint,
	in pHits int,
	in pTargets int,
	in pPoints int,
  in pCompId int,
  in isExtra boolean
)
begin
	declare pStatus varchar(10) default 'FAILED';
	declare eId bigint;
	declare lastStation int;
	
	main: begin
	
    if isExtra then
        select 20
        into lastStation;
    else
        select MaxStation
        into lastStation
        from tbl_Pistol_CompetitionDay
        where Id = pCompDayId;
    end if;

	
	
	
	if (pStationId > lastStation)
	then
		set pStatus = "STATIONERR";
		leave main;
	end if;
	
	if not isExtra then
        if not exists (select Id from tbl_Pistol_CompetitionDay where Id = pCompDayId)
        then
            set pStatus = "NOSUCHDAY";
            leave main;
        end if;
    end if;
	
	
	if not exists (select Id from tbl_Pistol_Entry where Id = pEntryId)
	then
		set pStatus = "NTRY_UNKN";
		leave main;
	end if;
	
	
	
    if isExtra then
        delete from tbl_Pistol_Score
        where EntryId = pEntryId
        and CompetitionId = pCompId
        and StationId = pStationId;

    else
	
        delete from tbl_Pistol_Score
        where EntryId = pEntryId
        and CompetitionDayId = pCompDayId
        and StationId = pStationId;
    end if;
	
	 if isExtra then
        insert into tbl_Pistol_Score (
            EntryId,
            CompetitionId,
            StationId,
            Hits,
            Targets,
            Points,
            RegisterDate
        )
        values (
            pEntryId,
            pCompId,
            pStationId,
            pHits,
            pTargets,
            pPoints,
            now()
        );
     else
        insert into tbl_Pistol_Score (
            EntryId,
            CompetitionDayId,
            StationId,
            Hits,
            Targets,
            Points,
            RegisterDate
        )
        values (
            pEntryId,
            pCompDayId,
            pStationId,
            pHits,
            pTargets,
            pPoints,
            now()
        );
    end if;
	
	select last_insert_id()
	into eId;
	
	set pStatus = 'OK';
	
	
	end main;
	
	select pStatus as Status, eId as ScoreId;
end$$

DROP PROCEDURE IF EXISTS `ResetPassword`$$
CREATE DEFINER=`okrets_se`@`%` PROCEDURE `ResetPassword`(
	in pEmail varchar(60),
	in pGunCard varchar(10),
	in pNewPassword varchar(100)
)
begin
	
	declare pStatus varchar(10) default 'FAILED';
	
	main: begin
	
	declare pId bigint;
	
	
	set pStatus = 'FAILED';
	
	
	select Id
	into pId
	from tbl_Pistol_Shot
	where GunCard = pGunCard
	and Email = pEmail;
	
	if pId is null
	then
		set pStatus = "NOT_FOUND";
		leave main;
	end if;
	
	
	update tbl_Pistol_Shot
	set Password = pNewPassword
	where Id = pId;
	
	if row_count() = 1
	then
		set pStatus = 'OK';
	end if;
	
	end main;
	
	select pStatus as Status;
end$$

--
-- Functions
--
DROP FUNCTION IF EXISTS `decodePrecision`$$
CREATE DEFINER=`okrets_se`@`%` FUNCTION `decodePrecision`(value INT) RETURNS int(11)
BEGIN
	DECLARE v1,v2,v3,v4,v5 INT;
	set v1 = floor(value%11);
	set v2 = floor(value/11%11);
	set v3 = floor(value/11/11%11);
	set v4 = floor(value/11/11/11%11);
	set v5 = floor(value/11/11/11/11%11);
    
    RETURN v1+v2+v3+v4+v5; 
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_Club`
--

DROP TABLE IF EXISTS `tbl_Pistol_Club`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_Club` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `CreateDate` datetime NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `i1` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=131 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_Competition`
--

DROP TABLE IF EXISTS `tbl_Pistol_Competition`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_Competition` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) DEFAULT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  `Location` varchar(100) DEFAULT NULL,
  `HostClubId` bigint(20) DEFAULT NULL,
  `MaxPatrolSize` int(11) NOT NULL,
  `Status` int(11) NOT NULL,
  `ScoreType` char(1) DEFAULT NULL,
  `OnlineBetalning` char(1) DEFAULT NULL,
  `Masterskap` char(1) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `i2` (`Name`,`StartDate`),
  KEY `i1` (`StartDate`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=88 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_CompetitionDay`
--

DROP TABLE IF EXISTS `tbl_Pistol_CompetitionDay`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_CompetitionDay` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `CompetitionId` bigint(20) DEFAULT NULL,
  `DayNo` smallint(6) NOT NULL,
  `FirstStart` varchar(5) DEFAULT NULL,
  `LastStart` varchar(5) DEFAULT NULL,
  `MaxStation` int(11) DEFAULT NULL,
  `PatrolSpace` int(11) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `i1` (`CompetitionId`,`DayNo`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=92 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_Dibs_Payment`
--

DROP TABLE IF EXISTS `tbl_Pistol_Dibs_Payment`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_Dibs_Payment` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `TransactionId` varchar(40) NOT NULL,
  `StatusCode` int(11) NOT NULL,
  `PayDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `OrderId` varchar(100) NOT NULL,
  `GunCard` varchar(20) NOT NULL,
  `Amount` int(11) NOT NULL,
  `ApprovalCode` varchar(20) NOT NULL,
  `PayType` varchar(20) NOT NULL,
  `CompetitionId` int(11) NOT NULL,
  `ShotId` int(11) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `OrderId` (`OrderId`),
  UNIQUE KEY `TransactionId` (`TransactionId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=768 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_Entry`
--

DROP TABLE IF EXISTS `tbl_Pistol_Entry`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_Entry` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ShotId` bigint(20) DEFAULT NULL,
  `GunClassificationId` bigint(20) DEFAULT NULL,
  `ShotClassId` bigint(20) DEFAULT NULL,
  `RegisterDate` datetime NOT NULL,
  `Status` char(1) DEFAULT NULL,
  `PatrolId` bigint(20) DEFAULT NULL,
  `PayDate` datetime DEFAULT NULL,
  `TeamId` bigint(20) DEFAULT NULL,
  `StaPlats` int(11) DEFAULT 0,
  `BokadAvShotId` int(11) DEFAULT 0,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `i1` (`PatrolId`,`ShotId`),
  UNIQUE KEY `i2` (`ShotId`,`PatrolId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21699 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_EntryPatrol`
--

DROP TABLE IF EXISTS `tbl_Pistol_EntryPatrol`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_EntryPatrol` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `EntryId` bigint(20) DEFAULT NULL,
  `PatrolId` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `i1` (`EntryId`,`PatrolId`),
  UNIQUE KEY `i2` (`PatrolId`,`EntryId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=279 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_FailedLogons`
--

DROP TABLE IF EXISTS `tbl_Pistol_FailedLogons`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_FailedLogons` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `GunCard` varchar(10) NOT NULL,
  `LogonDate` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Id`),
  KEY `i1` (`GunCard`,`LogonDate`),
  KEY `i2` (`LogonDate`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13917 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_GunClassification`
--

DROP TABLE IF EXISTS `tbl_Pistol_GunClassification`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_GunClassification` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Grade` varchar(30) NOT NULL,
  `Description` varchar(100) DEFAULT NULL,
  `ForScoreType` char(10) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `i1` (`Grade`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_Logons`
--

DROP TABLE IF EXISTS `tbl_Pistol_Logons`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_Logons` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ShotId` bigint(20) NOT NULL,
  `LogonDate` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Id`),
  KEY `i1` (`ShotId`,`LogonDate`),
  KEY `i2` (`LogonDate`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=72115 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_MedalGroup`
--

DROP TABLE IF EXISTS `tbl_Pistol_MedalGroup`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_MedalGroup` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `i1` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_MedalGroupMember`
--

DROP TABLE IF EXISTS `tbl_Pistol_MedalGroupMember`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_MedalGroupMember` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `MedalGroupId` bigint(20) DEFAULT NULL,
  `ShotClassId` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `i1` (`ShotClassId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_Patrol`
--

DROP TABLE IF EXISTS `tbl_Pistol_Patrol`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_Patrol` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `CompetitionDayId` bigint(20) DEFAULT NULL,
  `SortOrder` smallint(6) NOT NULL,
  `Description` varchar(100) DEFAULT NULL,
  `StartTime` datetime DEFAULT NULL,
  `Hidden` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `i1` (`CompetitionDayId`,`SortOrder`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3297 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_PatrolGun`
--

DROP TABLE IF EXISTS `tbl_Pistol_PatrolGun`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_PatrolGun` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `PatrolId` bigint(20) DEFAULT NULL,
  `GunClassId` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `i1` (`PatrolId`,`GunClassId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26966 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_Payment`
--

DROP TABLE IF EXISTS `tbl_Pistol_Payment`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_Payment` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `EntryId` bigint(20) NOT NULL,
  `TransactionId` varchar(40) NOT NULL,
  `StatusCode` int(11) DEFAULT 0,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11352 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_Schedule`
--

DROP TABLE IF EXISTS `tbl_Pistol_Schedule`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_Schedule` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `CompetitionDayId` bigint(20) DEFAULT NULL,
  `StartTime` datetime NOT NULL,
  `PatrolId` bigint(20) DEFAULT NULL,
  `Station` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `i1` (`CompetitionDayId`,`PatrolId`,`Station`),
  KEY `i2` (`PatrolId`,`StartTime`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7023 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_Score`
--

DROP TABLE IF EXISTS `tbl_Pistol_Score`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_Score` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `CompetitionDayId` bigint(20) DEFAULT NULL,
  `StationId` smallint(6) NOT NULL,
  `Hits` int(11) NOT NULL,
  `Targets` int(11) NOT NULL,
  `Points` int(11) NOT NULL,
  `RegisterDate` datetime NOT NULL,
  `EntryId` bigint(20) DEFAULT NULL,
  `CompetitionId` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `i1` (`EntryId`,`CompetitionDayId`,`StationId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=125317 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_Shot`
--

DROP TABLE IF EXISTS `tbl_Pistol_Shot`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_Shot` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(20) DEFAULT NULL,
  `LastName` varchar(40) DEFAULT NULL,
  `ClubId` bigint(20) DEFAULT NULL,
  `GunCard` varchar(10) DEFAULT NULL,
  `Email` varchar(60) DEFAULT NULL,
  `Password` varchar(100) DEFAULT NULL,
  `UserType` varchar(10) DEFAULT NULL,
  `CreateDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `Status` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `i3` (`GunCard`),
  KEY `i1` (`LastName`,`FirstName`),
  KEY `i2` (`ClubId`,`LastName`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1028 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_ShotClass`
--

DROP TABLE IF EXISTS `tbl_Pistol_ShotClass`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_ShotClass` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Name` varchar(20) DEFAULT NULL,
  `Description` varchar(20) DEFAULT NULL,
  `GunClassificationId` bigint(20) DEFAULT NULL,
  `Masterskap` char(1) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `i1` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=42 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_Station`
--

DROP TABLE IF EXISTS `tbl_Pistol_Station`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_Station` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `CompetitionDayId` bigint(20) DEFAULT NULL,
  `SortOrder` smallint(6) NOT NULL,
  `PatrolSpace` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `i1` (`CompetitionDayId`,`SortOrder`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_Team`
--

DROP TABLE IF EXISTS `tbl_Pistol_Team`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_Team` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) NOT NULL,
  `CompetitionDayId` bigint(20) DEFAULT NULL,
  `GunClassId` bigint(20) DEFAULT NULL,
  `ClubId` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=102 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_TeamMember`
--

DROP TABLE IF EXISTS `tbl_Pistol_TeamMember`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_TeamMember` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `TeamId` bigint(20) DEFAULT NULL,
  `EntryId` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_Pistol_Traning`
--

DROP TABLE IF EXISTS `tbl_Pistol_Traning`;
CREATE TABLE IF NOT EXISTS `tbl_Pistol_Traning` (
  `Id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ShotId` bigint(20) NOT NULL,
  `ShotClassId` bigint(20) NOT NULL,
  `Date` date DEFAULT NULL,
  `Score` int(11) NOT NULL,
  `GodkanShotId` bigint(20) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2614 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
