-- DELIMITER $$


DROP PROCEDURE IF EXISTS `ResetPassword` $$

CREATE PROCEDURE  `ResetPassword`(
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
end $$


