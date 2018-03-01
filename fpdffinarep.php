<?php
session_start();
header("Content-type: text/html; charset=windows-1251");
include_once 'inc/functions.php';
define('FPDF_FONTPATH','font/');
require('fpdf17/fpdf.php');

if (!logged()) redirect('index.php?rx=finansii');

$ini = new Configini("flpt");

$bazite=$_SESSION['fin_baza'];
$x=0;
foreach($bazite as $bazi){
	$cmd[$x] = "SELECT '".$bazi."' as DB,".$_SESSION['query'];	
	$x++;
}
//echo $cmd[0];
class PDF extends FPDF
{
	var $widths;
	var $aligns;
	
	//******************************************************************
	
	
	
	//******************************************************************
	
	//Load data
	function LoadData($file)
	{
		//Read file lines
		$lines=file($file);
		$data=array();
		foreach($lines as $line)
			$data[]=explode(';',chop($line));
		return $data;
		}
		function FancyTable($header,$data)
		{
			//Colors, line width and bold font
			$this->SetFillColor(255,0,0);
			$this->SetTextColor(255);
			$this->SetDrawColor(128,0,0);
			$this->SetLineWidth(.1);
			//$this->SetFont('','B');
			$this->SetFontSize(8);
			//Header
			$w=array(31,31,32,32,32,32); //tuka se kazuva kolku i kolkavi koloni imame
			for($i=0;$i<count($header);$i++)
				$this->Cell($w[$i],6,$header[$i],1,0,'C',true);
			$this->Ln();
			//Color and font restoration
			$this->SetFillColor(224,235,255);
			$this->SetTextColor(0);
			//$this->SetFont('Arial');
			$this->SetFontSize(8);
			//Data
			$fill=false;
			foreach($data as $row)
				{ //tuka gi pravime kolonite
				$this->Cell($w[0],6,$row[0],'LR',0,'C',$fill);
				$this->Cell($w[1],6,$row[2],'LR',0,'C',$fill);
				$this->Cell($w[2],6,number_format((int)$row[3]),'LR',0,'R',$fill);
				$this->Cell($w[3],6,number_format((int)$row[4]),'LR',0,'R',$fill);
				$this->Cell($w[4],6,number_format((int)$row[5]),'LR',0,'R',$fill);
				$this->Cell($w[5],6,number_format((int)$row[6]),'LR',0,'R',$fill);
				$this->Ln();
				$fill=!$fill;
				}
		$this->Cell(array_sum($w),0,'','T');
		}
		
			function SetWidths($w)
			{
				//Set the array of column widths
				$this->widths=$w;
			}
		
			function SetAligns($a)
			{
				//Set the array of column alignments
				$this->aligns=$a;
			}
		
			function Row($data)
			{
				//Calculate the height of the row
				$nb=0;
				for($i=0;$i<count($data);$i++)
					$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
				$h=5*$nb;
				//Issue a page break first if needed
				$this->CheckPageBreak($h);
				//Draw the cells of the row
				for($i=0;$i<count($data);$i++)
					{
					$w=$this->widths[$i];
					$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
					//Save the current position
					$x=$this->GetX();
					$y=$this->GetY();
					//Draw the border
					$this->Rect($x,$y,$w,$h);
					//Print the text
					$this->MultiCell($w,5,$data[$i],0,$a);
					//Put the position to the right of the cell
					$this->SetXY($x+$w,$y);
			}
					//Go to the next line
					$this->Ln($h);
		}
		
		function CheckPageBreak($h)
						{
					//If the height h would cause an overflow, add a new page immediately
					if($this->GetY()+$h>$this->PageBreakTrigger)
						$this->AddPage($this->CurOrientation);
		}
		
			function NbLines($w,$txt)
			{
			//Computes the number of lines a MultiCell of width w will take
			$cw=&$this->CurrentFont['cw'];
			if($w==0)
				$w=$this->w-$this->rMargin-$this->x;
				$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
			$s=str_replace("\r",'',$txt);
			$nb=strlen($s);
			if($nb>0 and $s[$nb-1]=="\n")
				$nb--;
			$sep=-1;
			$i=0;
			$j=0;
			$l=0;
			$nl=1;
			while($i<$nb)
			{
				$c=$s[$i];
				if($c=="\n")
				{
				$i++;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
				continue;
			}
			if($c==' ')
				$sep=$i;
				$l+=$cw[$c];
				if($l>$wmax)
				{
					if($sep==-1)
					{
					if($i==$j)
						$i++;
			}
			else
				$i=$sep+1;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
			}
			else
				$i++;
		}
			return $nl;
		}
}
		
		
		$pdf=new PDF();
		//Column titles
		$header=array('Фирма','Конто','Должи','Побарува','Салдо должи','Салдо побарува');
		//Data loading
		
		//*** Load MySQL Data ***//
		$z=0;
		foreach($bazite as $baza){
			$objConnect[$z] = anyconnect($baza);
			$aaa[$z]=$ini->getinival($baza, 'dbname', '');
			$objDB = mysqli_select_db($objConnect[$z], $aaa[$z]);
			$objQuery[$z] = mysqli_query($objConnect[$z], $cmd[$z]);
			$z++;
		}
		
		$resultData = array();
		for ($a=0;$a<count($objQuery);$a++){
			for ($i=0;$i<mysqli_num_rows($objQuery[$a])-1;$i++) {
				$result = mysqli_fetch_array($objQuery[$a]);
				if ($_SESSION['totfin']==1){
					if (!is_null($result['KONTO'])){
						continue;
					}
				}
				$handle=connectweb();
				$rez=mysqli_query($handle, "SELECT dsc from cmp where id='".$result['DB']."' ");
				$dbid=mysqli_fetch_row($rez);
				$dbname=cp_1251_zont($dbid[0]);
				$replace_arr=array(0=>$dbname,'DB'=>$dbname);
				//$result=array_replace($result, $replace_arr);
				$result[0] = $dbname;
				$result['DB'] = $dbname;
				array_push($resultData,$result);
				}
		}		
		//print_r($result);
			$arr=Array ( 0 => 'Вкупно', 'DB' => 'Вкупно', 1 => $result['KORISNIK'], 'KORISNIK' => $result['KORISNIK'], 2 =>'', 'KONTO' =>'', 3 => $_SESSION['sumad'], 'DOLZI' => $_SESSION['sumad'], 4 => $_SESSION['sumap'], 'POBARUVA' => $_SESSION['sumap'], 5 => $_SESSION['sumasd'], 'SD' => $_SESSION['sumasd'], 6 => $_SESSION['sumasp'], 'SP' => $_SESSION['sumasp']);
			array_push($resultData,$arr);
			$pdf->AddFont('arial','','arial.php');
			$pdf->SetFont('arial','',14);
			$pdf->AddPage();
		    $pdf->Image('img/logo.png',5,5,20);
		    $pdf->Ln(20);
		    $sql=mysqli_query($objConnect[0],"SELECT LEFT(opis,18) from firmi where cod= ".$result['KORISNIK']);
		    $rez=mysqli_fetch_row($sql);
		    $pdf->Cell(0,0,'Комбиниран извештај за: '.$rez[0],0,0,'C');
		    $pdf->SetFont('arial','',8);
// 		    $pdf->Cell(-179);
// 		    $pdf->Cell(0,20,'Za korisnik: '.cp_1251_zont($_SESSION['korisnik']).' ',0,0,'L');
 		    $pdf->Cell(-191);
		    $pdf->Cell(0,20,'Конто: '.$_SESSION['konto'].' ',0,0,'L');
		    $pdf->Cell(-191);
		    $pdf->Cell(0,30,'Датум од '.$_SESSION['oddat'].' до '.$_SESSION['dodat'].'',0,0,'L');
		    $pdf->Ln(20);
		    $pdf->FancyTable($header,$resultData);
			
			$pdf->Output();
?>