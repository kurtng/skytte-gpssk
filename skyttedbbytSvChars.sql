use gunnar;
SET SQL_SAFE_UPDATES=0;
update tbl_Pistol_Club set Name = replace(Name, 'Ã¥', '�');
update tbl_Pistol_Club set Name = replace(Name, 'Ã¶', '�');
update tbl_Pistol_Club set Name = replace(Name, 'Ã¤', '�');
update tbl_Pistol_Club set Name = replace(Name, 'Ã„', '�');
update tbl_Pistol_Club set Name = replace(Name, 'Ã…', '�');
update tbl_Pistol_Club set Name = replace(Name, 'Ã–', '�');


update tbl_Pistol_Shot set LastName = replace(LastName, 'Ã¥', '�');
update tbl_Pistol_Shot set LastName = replace(LastName, '¥', '�');
update tbl_Pistol_Shot set LastName = replace(LastName, 'Ã¶', '�');
update tbl_Pistol_Shot set LastName = replace(LastName, 'ö', '�');
update tbl_Pistol_Shot set LastName = replace(LastName, 'Ã¤', '�');
update tbl_Pistol_Shot set LastName = replace(LastName, 'ä', '�');

update tbl_Pistol_Shot set LastName = replace(LastName, 'Ã„', '�');
update tbl_Pistol_Shot set LastName = replace(LastName, 'Ã…', '�');
update tbl_Pistol_Shot set LastName = replace(LastName, 'Ã–', '�');

update tbl_Pistol_Shot set FirstName = replace(FirstName, 'Ã¥', '�');
update tbl_Pistol_Shot set FirstName = replace(FirstName, '¥', '�');
update tbl_Pistol_Shot set FirstName = replace(FirstName, 'Ã¶', '�');
update tbl_Pistol_Shot set FirstName = replace(FirstName, '¶', '�');
update tbl_Pistol_Shot set FirstName = replace(FirstName, 'Ã¤', '�');
update tbl_Pistol_Shot set FirstName = replace(FirstName, '¤', '�');
update tbl_Pistol_Shot set FirstName = replace(FirstName, 'Ã„', '�');
update tbl_Pistol_Shot set FirstName = replace(FirstName, 'Ã…', '�');
update tbl_Pistol_Shot set FirstName = replace(FirstName, 'Ã–', '�');