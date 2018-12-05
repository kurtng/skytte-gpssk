-- DELIMITER $$
DROP PROCEDURE IF EXISTS `ListTeamResult` $$

CREATE  PROCEDURE  `ListTeamResult`(in pCompId bigint)
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
		-- sum( ifnull(s.Hits, 0)),
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

end $$
