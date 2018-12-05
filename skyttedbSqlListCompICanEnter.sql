-- DELIMITER $$



DROP PROCEDURE IF EXISTS `ListCompICanEnter` $$

CREATE PROCEDURE  `ListCompICanEnter`(in pShotId bigint, in pGunClassId bigint)
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
end $$
