<?php
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
	
	define('FPDF_FONTPATH','classes/');
	require('classes/fpdf.php');

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
	private $maxLinesPerPage = 38;
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
		// $this->Cell(10, $this->cardHeight, "Vapenklass: " . $ShotClass, 0,0,'L',0);
		$this->Cell(10, $this->cardHeight, "Resultat Lag", 0,0,'L',0);
		/*
		$this->SetXY(25, 15);
		$this->Cell(10, $this->cardHeight, "Plc", 0,0,'L',0);
		$this->Cell(60, $this->cardHeight, "Skytt", 0,0,'L',0);
		$this->Cell(20, $this->cardHeight, "Resultat", 0,0,'L',0);
		*/
		
		$this->Line(25, 21, 200, 21);
	}
		
	function DrawEntry($stations, $person, $teamMembers)
	{
		global $comp;
		global $compDay;
		global $debug;
		
		$NewPage = 0;
		$this->SetDrawColor(255,0,0);
		
		//$this->SetXY(20, 5);
		
		//print_r($person);
		//print("<br>");
		
		if ($this->curClass == "")
			$NewPage = 1;
			
		/* Prefer compact report - no page break here
		if ($person["GunClassId"] != $this->curClass)
			$NewPage = 1;
		*/
		
		if ($this->cardNo >= $this->maxLinesPerPage)
			$NewPage = 1;

		if ($NewPage)
		{
			if ($this->curClass != "")
				$this->AddPage();
			$this->DrawHeader($person["GunGrade"]);
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

		// Print the entry
		$this->SetY($this->cardNo * ($this->cardHeight + $this->cardSpacing) + $this->topmarg);

		$this->SetX(5);
		$this->SetTextColor(0,100,0);
		
		$this->Cell(10, $this->cardHeight, 
				$person["GunGrade"], 0,0,'R',0);
		
		$this->SetTextColor(0,0,0);
		$this->Cell(10, $this->cardHeight, 
				$person["Place"], 0,0,'R',0);

		$this->Cell(60, $this->cardHeight, 
			iconv("UTF-8", "CP1252", $person["TeamName"]), 0,0,'L',0);
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
		$this->Cell(50, $this->cardHeight, 
			iconv("UTF-8", "CP1252", $clubN), 0,0,'L',0);
			
		// Points
		if($comp->scoreType != "P")
			$this->Cell(10, $this->cardHeight, $person["Points"] . "p", 0,0,'R',0);
		
		if ($comp->scoreType == "N")
			$total = $person["Total"];
		else if($comp->scoreType == "P")
		 	$total = $person["Total"];
		else
			$total = $person["Hits"] . "/" . $person["Targets"];
		
		$this->Cell(10, $this->cardHeight, 
			$total, 0,0,'R',0);

		//$total2 = $person["Hits"] + $person["Total"]; 
		//$this->Cell(10, $this->cardHeight, 
		//	$total2, 0,0,'R',0);

		/*
		$this->SetXY($this->cx, $this->cy);
		$this->SetFont('Arial','B',20);
		$this->Cell(125,30,"Congrats! ",0,0,'C',0);
		$this->SetTextColor(255,0,0);
		*/
		$this->cardNo++;
		
		foreach ($teamMembers as $teamMember) {
			
			$this->SetXY(100,$this->cardNo * ($this->cardHeight + $this->cardSpacing) + $this->topmarg);
		
			$this->Cell(10, $this->cardHeight, 
				iconv("UTF-8", "CP1252", $teamMember["FirstName"] . " " . $teamMember["LastName"]), 0,0,'L',0);
			$this->cardNo++;
		} 
		
		
		$this->curClass = $person["GunClassId"];	
	}
}

$pdf=new ResultList();
$pdf->Open();
$pdf->AddPage();

$pdf->SetDrawColor(200);

$cp = new Score();
$list = $cp->listTeamResult($comp->id);

$lastEntryId = 0;
$hits = array();

foreach ($list as $res) {
	$entry = new Entry();
	$mms = $entry->listTeamMembers($res["TeamId"]);
	$pdf->DrawEntry($compDay->maxStation, $res, $mms);
}

$pdf->Output();
?>
