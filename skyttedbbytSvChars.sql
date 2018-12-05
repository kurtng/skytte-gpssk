use gunnar;
SET SQL_SAFE_UPDATES=0;
update tbl_Pistol_Club set Name = replace(Name, 'ÃƒÂ¥', 'å');
update tbl_Pistol_Club set Name = replace(Name, 'ÃƒÂ¶', 'ö');
update tbl_Pistol_Club set Name = replace(Name, 'ÃƒÂ¤', 'ä');
update tbl_Pistol_Club set Name = replace(Name, 'Ãƒâ€ž', 'Ä');
update tbl_Pistol_Club set Name = replace(Name, 'Ãƒâ€¦', 'Å');
update tbl_Pistol_Club set Name = replace(Name, 'Ãƒâ€“', 'Ö');


update tbl_Pistol_Shot set LastName = replace(LastName, 'ÃƒÂ¥', 'å');
update tbl_Pistol_Shot set LastName = replace(LastName, 'Â¥', 'å');
update tbl_Pistol_Shot set LastName = replace(LastName, 'ÃƒÂ¶', 'ö');
update tbl_Pistol_Shot set LastName = replace(LastName, 'Ã¶', 'ö');
update tbl_Pistol_Shot set LastName = replace(LastName, 'ÃƒÂ¤', 'ä');
update tbl_Pistol_Shot set LastName = replace(LastName, 'Ã¤', 'ä');

update tbl_Pistol_Shot set LastName = replace(LastName, 'Ãƒâ€ž', 'Ä');
update tbl_Pistol_Shot set LastName = replace(LastName, 'Ãƒâ€¦', 'Å');
update tbl_Pistol_Shot set LastName = replace(LastName, 'Ãƒâ€“', 'Ö');

update tbl_Pistol_Shot set FirstName = replace(FirstName, 'ÃƒÂ¥', 'å');
update tbl_Pistol_Shot set FirstName = replace(FirstName, 'Â¥', 'å');
update tbl_Pistol_Shot set FirstName = replace(FirstName, 'ÃƒÂ¶', 'ö');
update tbl_Pistol_Shot set FirstName = replace(FirstName, 'Â¶', 'ö');
update tbl_Pistol_Shot set FirstName = replace(FirstName, 'ÃƒÂ¤', 'ä');
update tbl_Pistol_Shot set FirstName = replace(FirstName, 'Â¤', 'ä');
update tbl_Pistol_Shot set FirstName = replace(FirstName, 'Ãƒâ€ž', 'Ä');
update tbl_Pistol_Shot set FirstName = replace(FirstName, 'Ãƒâ€¦', 'Å');
update tbl_Pistol_Shot set FirstName = replace(FirstName, 'Ãƒâ€“', 'Ö');