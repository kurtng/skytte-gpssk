<?php
include_once "GunnarCore.php";
session_start();

	$debug = 0;
	$act=$_POST['myAction'];
	$msg = "";
	// We must be logged in
	if (!session_is_registered("shotSession"))
		http_redirect("LogIn.php");

	$shot = unserialize($_SESSION["shotSession"]);
	
	$gunClassList = $shot->getGunClassList();
	
	switch ($act) {
		case "pick":
			$gunClassId = $_POST["gunClassId"];
			break;
		default:
			break;
	}
	
	
header("Content-Type: text/html; charset=UTF-8");
?>
<html>

<head>
<title>Stats</title>
<link rel="stylesheet" href="gunnar1.css" type="text/css" media="screen" />


<?

	$cp = new Score();
	$list = $cp->listResultStats($shot->id);
	$results = array(array());
	$grades = array();
	foreach ($list as $row)
	{
		if (array_key_exists($row["CompId"], $results) && array_key_exists($row["Grade"], $results[$row["CompId"]])){
			$results[$row["CompId"]][$row["Grade"]] ["Result"] += $row["Hits"] + $row["Targets"];
			$results[$row["CompId"]][$row["Grade"]] ["Points"] += $row["Points"];
		} else {
			$results[$row["CompId"]][$row["Grade"]] ["Result"] = $row["Hits"] + $row["Targets"];
			$results[$row["CompId"]][$row["Grade"]] ["Points"] = $row["Points"];
			$results[$row["CompId"]][$row["Grade"]] ["StartDate"] = $row["StartDate"];
			$results[$row["CompId"]][$row["Grade"]] ["CompName"] = $row["CompName"];
		}
		
		if(!in_array($row["Grade"], $grades)) {
			array_push($grades, $row["Grade"]);
		}
	}
	
	
	
?>


<head>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});

      <?
      foreach($grades as $grade) 
  	{ ?>
  	google.setOnLoadCallback(drawChart<?=$grade?>);
    function drawChart<?=$grade?>() {
    	var data = new google.visualization.DataTable();
    	data.addColumn('string', 'Tävling'); // Implicit domain label col.
    	data.addColumn('number', 'Resultat');
    	data.addColumn({type:'string', role:'annotation'});
    	data.addRows([
        
        <?
        foreach ($results as $compId => $comp)
  		{
  			foreach ($comp as $gradeHere => $result)
  			{
  				if($grade == $gradeHere) {
  					print "[" . "'" . $result["CompName"] . "(" . $result["StartDate"] . ")', " . $result["Result"] . ",'" . $result["Result"] . "/" . $result["Points"] . "'],";
  					//print $result["Points"];
  					
  				}
  				
  			}
  		}
        ?>
      ]);

      var options = {
        title: 'Klass <?=$grade?>',
        hAxis: {title: 'Resultat'},
        vAxis: {title: 'Tävling'},
        legend: 'none'
      };

      var chart = new google.visualization.AreaChart(document.getElementById('chart_div_<?=$grade?>'));
      chart.draw(data, options);
    }
  	
  		
  			<?
  		}
  	?>
      
      
    </script>
  </head>

</head>

<body >
<div class="error"><?=$msg?></div>
<br>
<?
  foreach($grades as $grade) 
  	{
  		?>
  	
  	    <div id="chart_div_<?=$grade?>" style="width: 900px; height: 500px;"></div>
  	
  		<?}?>

</body>
</html>