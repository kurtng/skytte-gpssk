-- DELIMITER $$

DROP PROCEDURE IF EXISTS `RegisterScore` $$

CREATE  PROCEDURE `RegisterScore`(in pEntryId bigint,
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
end $$

