-- DELIMITER $$
DROP PROCEDURE IF EXISTS `CancelEntry` $$ 

CREATE PROCEDURE  `CancelEntry`(in eId bigint)
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
end $$
