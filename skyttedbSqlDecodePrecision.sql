-- DELIMITER $$


DROP FUNCTION IF EXISTS `decodePrecision` $$

CREATE FUNCTION `decodePrecision`(value INT) RETURNS int(11)
BEGIN
	DECLARE v1,v2,v3,v4,v5 INT;
	set v1 = floor(value%11);
	set v2 = floor(value/11%11);
	set v3 = floor(value/11/11%11);
	set v4 = floor(value/11/11/11%11);
	set v5 = floor(value/11/11/11/11%11);
    
    RETURN v1+v2+v3+v4+v5; 
END$$

