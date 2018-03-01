<?php
session_start();
header("Content-type: text/html; charset=windows-1251");
include_once 'inc/functions.php';
define('FPDF_FONTPATH','font/');
require('fpdf17/fpdf.php');

if (!logged()) redirect('index.php?rx=mngprikaz_nalozi');

$ini = new Configini("flpt");

$cmd=$_SESSION['select_nal'];

class PDF extends FPDF
{
	var $widths;
	var $aligns;
		
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
			$this->AddFont('arial','','arial.php');
			//$this->SetFont('arial','B');
			$this->SetFontSize(8);
			//Header
			$w=array(35,45,26,24,28,17,16); //tuka se kazuva kolku i kolkavi koloni imame
			for($i=0;$i<count($header);$i++)
				$this->Cell($w[$i],6,$header[$i],1,0,'C',true);
			$this->Ln();
			//Color and font restoration
			$this->SetFillColor(224,235,255);
			$this->SetTextColor(0);
			//$this->AddFont('arial','','arial.php');
			//$this->SetFont('arial');
			$this->SetFontSize(8);
			//Data
			$fill=false;
			foreach($data as $row)
				{ //tuka gi pravime kolonite
				$this->Cell($w[0],6,$row[1],'LR',0,'C',$fill);
				$this->Cell($w[1],6,$row[3],'LR',0,'C',$fill);
				$this->Cell($w[2],6,$row[4],'LR',0,'C',$fill);
				$this->Cell($w[3],6,$row[5],'LR',0,'C',$fill);
				$this->Cell($w[4],6,$row[6],'LR',0,'C',$fill);
				$this->Cell($w[5],6,$row[2],'LR',0,'C',$fill);
				$this->Cell($w[6],6,$row[7],'LR',0,'C',$fill);
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
		$header=array('Фирма','Дериват','Количина во литри','Количина - налог','Количина - испорака','За датум','Статус');
		//Data loading
		
		//*** Load MySQL Data ***//		
		$objConnect = connectwebnal();
		$aaa=$ini->getinival('dbwebnal', 'dbname', '');
		$objDB = mysqli_select_db($objConnect, $aaa);
		$objQuery = mysqli_query($objConnect, $cmd);
		
		$resultData = array();
		for ($i=0;$i<mysqli_num_rows($objQuery);$i++) {
			$result = mysqli_fetch_array($objQuery);
			
			//$handle=connectwebnal();
			$rez1=mysqli_query($objConnect, "SELECT LEFT(opis,18) from firmi where cod='".$result['firma']."'");
			$firma=mysqli_fetch_row($rez1);
			$result[1]=$firma[0];
			$result['firma']=$firma[0];
			
			$rez2=mysqli_query($objConnect, "SELECT LEFT(opis,25) from materijali where cod='".$result['mat']."'");
			$mat=mysqli_fetch_row($rez2);
			$result[3]=$mat[0];
			$result['mat']=$mat[0];
			
			switch ($result['status']){
				case 1:
					$stat='активно';
					break;
				case 2:
					$stat='прифатено';
					break;
				case 3:
					$stat='завршено';
					break;
				case 4:
					$stat='откажано';
					break;
			}
			$result[7]=$stat;
			$result['status']=$stat;
			
			array_push($resultData,$result);
			}
			$pdf->AddFont('arial','','arial.php');
			$pdf->SetFont('arial','',7);
			$pdf->AddPage();			
			$pdf->Image('img/logo.png',80,8,33);
			$pdf->Ln(35);
		    $pdf->FancyTable($header,$resultData);
			
			$pdf->Output();
?>