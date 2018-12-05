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

class StartList extends FPDF
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
	
	function DrawHeader()
	{
		global $comp;
		global $compDay;
		
		$this->SetFont('Arial','B',10);
		$this->SetXY(100 - strlen($comp->name) / 2, 10);
		$this->Cell(60, $this->cardHeight, iconv("UTF-8", "CP1252", $comp->name) . " dag: " . iconv("UTF-8", "CP1252", $compDay->dayNo), 0,0,'L',0);
		
		$this->SetXY(25, 15);
		$this->Cell(60, $this->cardHeight, "Skytt", 0,0,'L',0);
		$this->Cell(20, $this->cardHeight, "Klass", 0,0,'L',0);
		$this->Cell(50, $this->cardHeight, "Klubb", 0,0,'L',0);
		$this->Cell(20, $this->cardHeight, "Start", 0,0,'L',0);
		$this->Cell(20, $this->cardHeight, "Patrull", 0,0,'L',0);
		
		$this->Line(25, 21, 200, 21);
	}
		
	function DrawEntry($stations, $person)
	{
		global $comp;
		global $compDay;

		//$this->SetXY(20, 5);
		$this->SetFont('Arial','B',10);
		
		$this->SetLineWidth(0.5);
		$this->SetDrawColor(255,0,0);
		$this->SetTextColor(0,0,0);
		
		//print_r($person);
		//print("<br>");
		
		if ($this->cardNo >= $this->maxLinesPerPage) {
			$this->AddPage();
			$this->DrawHeader();
			$this->cardNo = 0;
			$this->curPatrol = $person["PatrolId"];
			$this->curClub = $person["ClubName"];
		}
		
		if ($this->curClub == "") {
			$this->curClub = $person["ClubName"];
			$this->DrawHeader();
		}
			
		if ($this->curPatrol == 0)
			$this->curPatrol = $person["PatrolId"];
			
		if ($person["PatrolId"] != $this->curPatrol) {
			// New patrol
			//$this->AddPage();
			//$this->cardNo = 0;
			$this->curPatrol = $person["PatrolId"];
		}

		if ($person["ClubName"] != $this->curClub) {
			// New club
			$this->AddPage();
			$this->cardNo = 0;
			$this->curClub = $person["ClubName"];
			$this->DrawHeader();
		}

		// Print the entry
		$this->SetY($this->cardNo * ($this->cardHeight + $this->cardSpacing) + $this->topmarg);
		$this->SetX(25);
		$this->Cell(60, $this->cardHeight, 
			iconv("UTF-8", "CP1252", $person["ShotName"]), 0,0,'L',0);
		$this->Cell(20, $this->cardHeight, 
			$person["ShotClassName"] . " (" .
			$person["GunClassName"] . ")", 0,0,'L',0);
		$clubN = $person["ClubName"]; 
		if(strlen($clubN) > 20) {
			$clubN = substr($clubN, 0, $this->maxPatrolNameLen) . "...";
		} else {
			$clubN = substr($clubN, 0, $this->maxPatrolNameLen);
		}
		$this->Cell(50, $this->cardHeight, 
			iconv("UTF-8", "CP1252", $clubN ), 0,0,'L',0);
		$this->Cell(20, $this->cardHeight, 
			$person["FirstStart"], 0,0,'L',0);
		$this->Cell(20, $this->cardHeight, 
			$person["SortOrder"], 0,0,'L',0);
			
			
		/*
		$this->SetXY($this->cx, $this->cy);
		$this->SetFont('Arial','B',20);
		$this->Cell(125,30,"Congrats! ",0,0,'C',0);
		$this->SetTextColor(255,0,0);
		*/
		
		$this->cardNo++;	
	}
}

$pdf=new StartList();
$pdf->Open();
$pdf->AddPage();

$pdf->SetDrawColor(200);

$list = $compDay->genStartList();
if (!is_array($list))
	exit();

foreach ($list as $shooter) {
	$pdf->DrawEntry($compDay->maxStation, $shooter);
}

$pdf->Output();
?>
