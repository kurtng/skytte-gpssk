<?php

//encode 5 precision values into a single value to be saved into database
function encodePrecisionArr($values)
{
	if(sizeof($values) !=5)
		return 0;
	return ($values[1])+(11*$values[2])+(11*11*$values[3])+(11*11*11*$values[4])+(11*11*11*11*$values[5]);
}

function encodePrecision($value1,$value2,$value3,$value4,$value5)
{
	return encodePrecisionArr(array(1=>$value1, 2=>$value2, 3=>$value3, 4=>$value4, 5=>$value5));
}

//decode back from database a single precision value into 5 different precision values
function decodePrecision($value){
	$value1 = $value%11;
	$value2 = $value/11%11;
	$value3 = $value/11/11%11;
	$value4 = $value/11/11/11%11;
	$value5 = $value/11/11/11/11%11;
	return array(1=>$value1, 2=>$value2, 3=>$value3, 4=>$value4, 5=>$value5);
}

function decodePrecisionTotal($value) {
	$valueArr = decodePrecision($value);
	return $valueArr[1] + $valueArr[2] + $valueArr[3] + $valueArr[4] + $valueArr[5];
}
?>
