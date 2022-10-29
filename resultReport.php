<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

include_once "GunnarCore.php";

session_start();

	$debug = 0;
	$act=$_POST['myAction'];
	$msg = "";
	
	// We must be logged in
	if (!session_is_registered("shotSession"))
		http_redirect("LogIn.php");

	// We must have chosen a competition
	if (!session_is_registered("competitionId"))
		http_redirect("competition.php");

	// We must have chosen a competition day
	if (!session_is_registered("competitionDayId"))
		http_redirect("competitionDay.php");

	$compDayId = $_SESSION['competitionDayId'];
	$compId = $_SESSION['competitionId'];
	
	$compDay = new CompetitionDay();
	$compDay->load($compDayId);
	
	$comp = new Competition();
	$comp->load($compId);

	$list = $compDay->genScoreCards();
	
	define('FPDF_FONTPATH','classes/fonts/');
	require('classes/fpdf_new.php');

class ResultList extends FPDF
{
	private $cx = 0;
	private $cy = 0;
	private $leftMarg = 10;
	private $topmarg = 25;
	private $colNo = 0;
	private $cardNo = 0;
	private $cardWidth = 90;
	private $cardHeight = 4;
	private $cardSpacing = 2;
	private $curPatrol = 0;
	private $curClub = "";
	private $curClass = "";
	private $maxLinesPerPage = 40;
	private $maxPatrolNameLen = 20;
	
	function DashedRect($x1,$y1,$x2,$y2,$width=1,$nb=15)
	{
		$this->SetLineWidth($width);
		$longueur=abs($x1-$x2);
		$hauteur=abs($y1-$y2);
		if($longueur>$hauteur) {
			$Pointilles=($longueur/$nb)/2; // length of dashes
		}
		else {
			$Pointilles=($hauteur/$nb)/2;
		}
		
		for($i=$x1;$i<=$x2;$i+=$Pointilles+$Pointilles) {
			for($j=$i;$j<=($i+$Pointilles);$j++) {
				if($j<=($x2-1)) {
					$this->Line($j,$y1,$j+1,$y1); // upper dashes
					$this->Line($j,$y2,$j+1,$y2); // lower dashes
				}
			}
		}
		for($i=$y1;$i<=$y2;$i+=$Pointilles+$Pointilles) {
			for($j=$i;$j<=($i+$Pointilles);$j++) {
				if($j<=($y2-1)) {
					$this->Line($x1,$j,$x1,$j+1); // left dashes
					$this->Line($x2,$j,$x2,$j+1); // right dashes
				}				
			}
		}
	}
	
	function DrawHeader($ShotClass)
	{
		global $comp;
		
		$this->SetFont('Arial','B',12);
		$this->SetXY(100 - strlen($comp->name) / 2, 10);
		$this->Cell(60, $this->cardHeight, iconv("UTF-8", "CP1252", $comp->name), 0,0,'L',0);
		
		$this->SetXY(-20, 15);
		$this->Cell(10, $this->cardHeight, $comp->startDate, 0,0,'R',0);

		$this->SetXY(25, 15);
		// $this->Cell(10, $this->cardHeight, "Klass: " . $ShotClass, 0,0,'L',0);
		$this->Cell(10, $this->cardHeight, "Resultat individuell", 0,0,'L',0);
		
		/*
		$this->SetXY(25, 15);
		$this->Cell(10, $this->cardHeight, "Plc", 0,0,'L',0);
		$this->Cell(60, $this->cardHeight, "Skytt", 0,0,'L',0);
		$this->Cell(20, $this->cardHeight, "Resultat", 0,0,'L',0);
		*/
		
		$this->Line(25, 21, 200, 21);
	}
		
	function DrawEntry($stations, $person, $hits)
	{
		global $comp;
		global $compDay;
		global $debug;
		
		$NewPage = 0;
		$this->SetDrawColor(255,0,0);

		if ($debug) {
			var_dump($person);
			print "<br/>";
		}
		//$this->SetXY(20, 5);
		
		//print_r($person);
		//print("<br>");
		
		
		if ($this->curClass == "")
			$NewPage = 1;
		else {
			if($this->curClass != $person["ShotClass"])
				$NewPage = 1;
		}
			
		/* Prefer compact listing
		if ($person["ShotClass"] != $this->curClass)
			$NewPage = 1;
		*/
		
		if ($this->cardNo >= $this->maxLinesPerPage)
			$NewPage = 1;

		
		
		if ($NewPage)
		{
			if ($this->curClass != "")
				$this->AddPage();
			$this->DrawHeader($person["ShotClass"]);
			$this->cardNo = 0;
			$this->curPatrol = $person["PatrolId"];
			$this->curClub = $person["ClubName"];
		}

		$this->SetFont('Arial','B',8);
		$this->SetLineWidth(0.5);
		$this->SetTextColor(0,0,0);
		
		
		/*
		if ($this->curClub == "") {
			$this->curClub = $person["ClubName"];
			$this->DrawHeader();
		}
		*/
			
		if ($this->curPatrol == 0)
			$this->curPatrol = $person["PatrolId"];
			
		if ($person["PatrolId"] != $this->curPatrol) {
			// New patrol
			//$this->AddPage();
			//$this->cardNo = 0;
			$this->curPatrol = $person["PatrolId"];
		}

		/*
		if ($person["ClubName"] != $this->curClub) {
			// New club
			$this->AddPage();
			$this->cardNo = 0;
			$this->curClub = $person["ClubName"];
			$this->DrawHeader();
		}
		*/

		// Print the entry
		$this->SetY($this->cardNo * ($this->cardHeight + $this->cardSpacing) + $this->topmarg);
		$this->SetX(5);
		$this->SetTextColor(0,100,0);
		
		$this->Cell(10, $this->cardHeight, 
				$person["ShotClass"], 0,0,'R',0);
		
		$this->SetTextColor(0,0,0);
		// $this->SetX(15);

		$this->Cell(10, $this->cardHeight, 
				$person["Place"], 0,0,'R',0);

		$this->Cell(60, $this->cardHeight, 
			iconv("UTF-8", "CP1252", $person["ShotName"]), 0,0,'L',0);
		/*
		$this->Cell(20, $this->cardHeight, 
			$person["ShotClassName"] . " (" .
			$person["GunClassName"] . ")", 0,0,'L',0);
		*/
			
		$clubN = $person["ClubName"]; 
		if(strlen($clubN) > 20) {
			$clubN = substr($clubN, 0, $this->maxPatrolNameLen) . "...";
		} else {
			$clubN = substr($clubN, 0, $this->maxPatrolNameLen);
		}

		$this->Cell(40, $this->cardHeight, 
			iconv("UTF-8", "CP1252", $clubN), 0,0,'L',0);
			
		foreach ($hits as $hit) {
			if ($debug) {
				var_dump($hit);
				print "<br/>";
			}
			$this->Cell(5, $this->cardHeight, 
				$hit, 0,0,'L',0);
		}
		
		if(!isPrecision($comp->scoreType)) {
			// Points
			$this->Cell(8, $this->cardHeight, 
				$person["Points"] . "p", 0,0,'R',0);
		}

	
		if(isPrecision($comp->scoreType)) {
			$total = $person["Total"];
		} else {
			if ($comp->scoreType == "N")
				$total = $person["Total"];
			else 
				$total = $person["Hits"] . "/" . $person["Targets"];
		}
		
		if($person["ExtraScore"] != "") {
			$extraScore =  split("-",$person["ExtraScore"]);
			array_shift($extraScore); // remove först null element
			$extraScoresTotal = array_pop($extraScore);// get the total element
			
			$total = $total - $extraScoresTotal;
		}

		$this->Cell(10, $this->cardHeight, 
			$total, 0,0,'R',0);
			
		if(isSport($comp->scoreType)) {
			$this->Cell(10, $this->cardHeight, 
			$person["Targets"] . "*", 0,0,'R',0);	
		}

		//$total2 = $person["Hits"] + $person["Total"]; 
		//$this->Cell(8, $this->cardHeight, 
		//	$total2, 0,0,'R',0);

		$this->Cell(4, $this->cardHeight, 
		$person["Medal"], 0,0,'L',0);
		
		//$this->Cell(4, $this->cardHeight, 
		//$person["ExtraScore"], 0,0,'L',0);
		
		if($person["ExtraScore"] != "") {
			$this->cardNo++;
			
			$this->SetY($this->cardNo * ($this->cardHeight + $this->cardSpacing) + $this->topmarg);
			
			
			$eScoresCount = count($extraScore);
			$this->SetX(165 - ($eScoresCount*5));
			
			$extraScore = array_reverse($extraScore);
			
			foreach ($extraScore as $eScore) {
				$this->Cell(5, $this->cardHeight, 
					(int)$eScore, 0,0,'L',0);
			}
			
			$this->Cell(10, $this->cardHeight, 
					(int)$extraScoresTotal, 0,0,'R',0);
			
			$this->Cell(20, $this->cardHeight, 
					(int)($extraScoresTotal + $total), 0,0,'R',0);
		}
			
		//$this->Cell(4, $this->cardHeight, 
		//	$person["SortOrder"], 0,0,'L',0);
			
			
		/*
		$this->SetXY($this->cx, $this->cy);
		$this->SetFont('Arial','B',20);
		$this->Cell(125,30,"Congrats! ",0,0,'C',0);
		$this->SetTextColor(255,0,0);
		*/
		
		$this->cardNo++;
		$this->curClass = $person["ShotClass"];	
	}
}

$pdf=new ResultList();
//$pdf->Open();
$pdf->AddPage();

$pdf->SetDrawColor(200);

$cp = new Score();
$list = $cp->listResult($comp->id, $shotClassId, 'Y');

$lastEntryId = 0;
$lastRes = array();
$hits = array();

foreach ($list as $res) {
	$entryId = $res["EntryId"];
	if ($debug) {
		print "Entry = " . $res["EntryId"] . "<br/>";
	}
	if ($lastEntryId == 0) {
		$lastEntryId = $entryId;
		$lastRes = $res;
	}
	if ($entryId != $lastEntryId)
	{
		if ($debug) {
			var_dump($lastRes);
			print "<br/>";
		}
		$pdf->DrawEntry($compDay->maxStation, $lastRes, $hits);
		$lastEntryId = $entryId;
		$lastRes = $res;
		$hits = array();
	}
	if(isPrecision($comp->scoreType)){
		$hits[] = decodePrecisionTotal($res["StationHits"]);
	}else{
		$hits[] = $res["StationHits"] . "/" . $res["StationTargets"];
	}
}

// Print last one?
if (sizeof($hits) > 0)
	$pdf->DrawEntry($compDay->maxStation, $lastRes, $hits);

$pdf->Output();
?>
