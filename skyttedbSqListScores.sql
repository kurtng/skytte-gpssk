-- DELIMITER $$


DROP PROCEDURE IF EXISTS `ListScores` $$

CREATE  PROCEDURE `ListScores`(in pPatrolId bigint, in pShotId bigint, in isExtra boolean, in competitionId bigint, in gunClassId bigint)
begin

    if isExtra then /*here competitionid saved in CompetitionId and station ids are negative*/
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

end $$