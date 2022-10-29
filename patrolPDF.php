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

	//$list = $compDay->genScoreCards();
	
	define('FPDF_FONTPATH','classes/fonts/');
	require('classes/fpdf_new.php');

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
	private $boxHeight = 8;
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
		
		$maxNoCardPerPage = 10;
		$noBox = 4; //F�lt 		// Draw four rows of boxes
		if(isPrecision($scoreType)) {
			$maxNoCardPerPage  = 4;
			$noBox = 7; //5 skott a tavla
		}
		
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

		$shooterNo++;
		
		if ($this->cardNo > 0) {
			switch ($this->colNo) {
				case 0:
					$this->cx += $this->cardWidth + $this->cardSpacing;
					$this->colNo++;
					break;
				case 1:
					$this->cx = $this->leftMarg;
					$this->cy += ($this->boxHeight*($noBox+2)) + $this->cardSpacing;
					$this->colNo = 0;
					break;
			}
		}
		else {
			$this->cx = $this->leftMarg;
			$this->cy = $this->topmarg;
			$this->colNo = 0;
			
			// Header
			$this->SetXY(20, 5);
			$this->SetFont('Arial','B',14);
			$this->Cell(160, 10, "Dag: " . $compDay->dayNo . 
				"  *** " . iconv("UTF-8", "CP1252", $comp->name) . " ***  " . (isPrecision($scoreType)?"Start":"Patrull") . ": " . 
					iconv("UTF-8", "CP1252", $person["SortOrder"]) . " " . $person["StartTime"], 
					1, 0, "C", 0, "");
		}
		
		$this->Rect($this->cx, $this->cy, $this->cardWidth, $this->boxHeight*($noBox+2), '');
		$this->SetFont('Arial','B',10);
		//$this->SetFont('DejaVu','B',14);
		
		$sw = ($this->cardWidth - 15) / $stations;
		$sh = ($this->boxHeight);
		$this->SetXY($this->cx, $this->cy);
		
		
		//Draw boxes of rows	
		for ($rno = 0; $rno < $noBox; $rno++)
		{
			$this->SetY($this->cy + $rno * $sh);
			// Draw a box for each station
			for ($i=1; $i<=$stations; $i++) {
				$txt = '';
				if ($rno == 0)
					$txt = $i;
				$this->SetX($this->cx + $sw * ($i - 1));
				$this->Cell($sw, $sh, $txt, 1,0,'C',0);
			}
			
			// Draw total box
			if ($rno == 0)
				$txt = "Tot";
			else
				$txt = "";
				
			$this->SetFillColor(245, 245, 245);
			$this->Rect($this->cx + $this->cardWidth - 14, $this->cy + 1 + $rno * $sh, 13, $sh - 2, 'F');
			
			$this->SetX($this->cx + $this->cardWidth - 15);
			$this->Cell(15, $sh, $txt, 1,0,'C',0);
			
		}

		$this->SetTextColor(220,220,220);
		$this->SetFontSize(8);
		
		
		if(isPrecision($scoreType)) {
			for ($rno = 1; $rno < $noBox-1; $rno++)
			{
				$lastColTexts[$rno] = $rno . ":a skott";
			}
			$lastColTexts[$rno] = "Total";
		} else {
			$lastColTexts = array(1 => "Träff", 2 => "Figur", 3 => "Poäng");
		}
		
		for ($rno = 1; $rno < $noBox; $rno++)
		{
			$this->SetXY($this->cx + $this->cardWidth - 15, $this->cy + ($sh * $rno));
			$this->Cell(15, $sh, iconv("UTF-8", "CP1252", $lastColTexts[$rno]), 0,0,'C',0);
		}
		
		
		$this->SetTextColor(0,0,0);
		
		$this->SetFontSize(10);
		// Print the gun card no and name
		$this->SetY($this->cy + $noBox * $sh);
		$this->SetX($this->cx + 5);
		$this->Cell(20, $sh, $person["GunCard"], 0,0,'L',0);
		$this->Cell($this->cardWidth - 25, $sh, 
			iconv("UTF-8", "CP1252", "(${shooterNo}) " . $person["ShotName"]), 0,0,'L',0);

		// Print the class and home club
		$this->SetY($this->cy + ($noBox+1) * $sh);
		$this->SetX($this->cx + 5);
		$this->Cell(20, $sh - 5, $person["ShotClassName"] . " (" .
			$person["GunClassName"] . ")", 0,0,'L',0);
		$clubN = $person["ClubName"]; 
		if(strlen($clubN) > 20) {
			$clubN = substr($clubN, 0, $this->maxPatrolNameLen) . "...";
		} else {
			$clubN = substr($clubN, 0, $this->maxPatrolNameLen);
		}
		$this->Cell($this->cardWidth - 25, $sh - 5, 
			iconv("UTF-8", "CP1252", $clubN), 0,0,'L',0);

		if ($person["EntryStatus"] == "P")
		{
			$this->SetY($this->cy + $noBox+1 * $sh - 5);
			$this->SetX($this->cx + $this->cardWidth - 20);
			$this->Cell(10, $sh, 
				'BETALD', 0,0,'L',0);
		} 

		/*
		$this->SetXY($this->cx, $this->cy);
		$this->SetFont('Arial','B',20);
		$this->Cell(125,30,"Congrats! ",0,0,'C',0);
		$this->SetTextColor(255,0,0);
		*/
		
					$this->curPatrol = $person["PatrolId"];
		
		$this->cardNo++;	
	}
}

$pdf=new ScoreCard();
//$pdf->Open();
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
