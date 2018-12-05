-- DELIMITER $$
DROP PROCEDURE IF EXISTS `ListResult` $$

CREATE PROCEDURE  `ListResult`(in pCompId bigint, in pShotClassId bigint, in pVerbose char(1), in gunClassId bigint , in isOnlyBorderResult boolean )
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
      -- borde göra så här för mästerskap bara annars SuperScore först kanske
	
	
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
		-- sum( ifnull(s.Hits, 0)),
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
	
	
	
    -- super score create
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
    
    
    -- extra score create
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

end $$


