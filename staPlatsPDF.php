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

	//$list = $compDay->genScoreCards();
	
	define('FPDF_FONTPATH','classes/');
	require('classes/fpdf.php');

class ScoreCard extends FPDF
{
	private $cx = 0;
	private $cy = 0;
	private $leftMarg = 10;
	private $topmarg = 20;
	private $colNo = 0;
	private $cardNo = 0;
	private $cardWidth = 90;
	private $cardHeight = 30;
	private $cardSpacing = 4;
	private $curPatrol = 0;
	private $boxHeight = 2;
	private $maxPatrolNameLen = 30;
	
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
	
	function DrawCard($stations, $person, $scoreType)
	{
		global $comp;
		global $compDay;
		global $shooterNo;
		
		$this->SetLineWidth(0.5);
		$this->SetDrawColor(255,0,0);
		$this->SetTextColor(0,0,0);
		
		$maxNoCardPerPage = 15;
		$noBox = 20; //F�lt 		// Draw four rows of boxes
		
		
		if ($this->curPatrol == 0)
			$this->curPatrol = $person["PatrolId"];
			
		if ($person["PatrolId"] != $this->curPatrol) {
			// New patrol
			$shooterNo = 0;
			$this->AddPage();
			$this->cardNo = 0;
		}
		
		if ($this->cardNo >= $maxNoCardPerPage) {
			$this->AddPage();
			$this->cardNo = 0;
			
		}

		$shooterNo ++;
		
		if ($this->cardNo > 0) {
			
		}
		else {
			// Header
			$this->SetXY(20, 5);
			$this->SetFont('Arial','B',14);
			$this->Cell(160, 10, "Dag: " . $compDay->dayNo . 
				"  *** " . iconv("UTF-8", "CP1252", $comp->name) . " ***  " . (isPrecision($scoreType)?"Start":"Patrull") . ": " . 
					iconv("UTF-8", "CP1252", $person["SortOrder"]) . " " . $person["StartTime"], 
					1, 0, "C", 0, "");
			$this->cy = 10;
		}
		
		
		$this->SetTextColor(0,0,0);
		$this->SetFontSize(10);
		// Print the gun card no and name
		$this->SetY($this->cy + 10);
		$this->cy += 10;
		$this->Cell(20, 10, "Plats." . ${shooterNo},0,0,'L',0);
		$this->Cell(20, 10, $person["GunCard"], 0,0,'L',0);
		$this->Cell(40, 10, iconv("UTF-8", "CP1252", $person["ShotName"]), 0,0,'L',0);
		$this->Cell(120, 10, $person["ShotClassName"] . " (" . $person["GunClassName"] . ")", 0,0,'L',0);
		$this->SetY($this->cy + 4);
		$this->cy += 6;
		$this->Cell(40, 10, " ", 0,0,'L',0);
		$this->Cell(100, 10, iconv("UTF-8", "CP1252", $person["ClubName"]), 0,0,'L',0);
		if ($person["EntryStatus"] == "P")
		{
			$this->Cell(150, 10, 'BETALD', 0,0,'L',0);
		} 

		
		$this->curPatrol = $person["PatrolId"];
		
		$this->cardNo++;	
	}
}

$pdf=new ScoreCard();
$pdf->Open();
//$pdf->AddFont('DejaVu','B','dejavusans-bold.php');
$pdf->AddPage();

$pdf->SetDrawColor(200);

$shooterNo = 0;

$list = $compDay->genScoreCards();
foreach ($list as $shooter) {
	$pdf->DrawCard($compDay->maxStation, $shooter, $comp->scoreType);
}

$pdf->Output();
?>
