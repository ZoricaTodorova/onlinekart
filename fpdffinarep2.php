<?php
session_start();
header("Content-type: text/html; charset=windows-1251");
include_once 'inc/functions.php';
define('FPDF_FONTPATH','font/');
require('fpdf17/fpdf.php');

if (!logged()) redirect('index.php?rx=finansii');

$ini = new Configini("flpt");

$cmd=$_SESSION['selekt'];
//echo $cmd;

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
			//$this->SetFont('arial','','B');
			$this->SetFontSize(8);
			//$this->SetLeftMargin(20);
			//Header
			if ($_SESSION['klik']=='Vkupno'){
				$w=array(25,36,25,25,25,25); //tuka se kazuva kolku i kolkavi koloni imame
				$this->SetLeftMargin(22);
			}
			elseif ($_SESSION['klik']=="Vkupno_saldo"){
				$w=array(17,34,23,25,23,23,23,23);
			}
			elseif($_SESSION['klik']=='Analitika'){
				if ($_SESSION['totfin']==1){
					$w=array(40,30,30,30,30);
					$this->SetLeftMargin(24);
				}else{
					$w=array(25,25,40,25,25,25,25);
				}
			}
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
			
			if ($_SESSION['klik']=='Vkupno'){
			foreach($data as $row)
				{ //tuka gi pravime kolonite
				$this->Cell($w[0],6,$row[1],'LR',0,'C',$fill);
				$this->Cell($w[1],6,$row[0],'LR',0,'C',$fill);
				$this->Cell($w[2],6,number_format($row[2]),'LR',0,'R',$fill);
				$this->Cell($w[3],6,number_format($row[3]),'LR',0,'R',$fill);
				$this->Cell($w[4],6,number_format($row[4]),'LR',0,'R',$fill);
				$this->Cell($w[5],6,number_format($row[5]),'LR',0,'R',$fill);
				$this->Ln();
				$fill=!$fill;
				}
			}
			elseif($_SESSION['klik']=="Vkupno_saldo"){
				foreach($data as $row)
				{ //tuka gi pravime kolonite
				$this->Cell($w[0],6,$row[1],'LR',0,'C',$fill);
				$this->Cell($w[1],6,$row[0],'LR',0,'C',$fill);
				$this->Cell($w[2],6,number_format($row[2]),'LR',0,'R',$fill);
				$this->Cell($w[3],6,number_format($row[3]),'LR',0,'R',$fill);
				$this->Cell($w[4],6,number_format($row[4]),'LR',0,'R',$fill);
				$this->Cell($w[5],6,number_format($row[5]),'LR',0,'R',$fill);
				$this->Cell($w[6],6,number_format($row[6]),'LR',0,'R',$fill);
				$this->Cell($w[7],6,number_format($row[7]),'LR',0,'R',$fill);
				$this->Ln();
				$fill=!$fill;
				}
			}
			elseif($_SESSION['klik']=='Analitika'){
				if ($_SESSION['totfin']==1){
					foreach($data as $row)
					{ //tuka gi pravime kolonite
					$this->Cell($w[0],6,$row[4],'LR',0,'C',$fill);
					$this->Cell($w[1],6,number_format($row[7]),'LR',0,'R',$fill);
					$this->Cell($w[2],6,number_format($row[8]),'LR',0,'R',$fill);
					$this->Cell($w[3],6,number_format($row[9]),'LR',0,'R',$fill);
					$this->Cell($w[4],6,number_format($row[10]),'LR',0,'R',$fill);
					$this->Ln();
					$fill=!$fill;
					}
				}
				else {
					foreach($data as $row)
					{ //tuka gi pravime kolonite
					$this->Cell($w[0],6,$row[3],'LR',0,'C',$fill);
					$this->Cell($w[1],6,$row[2],'LR',0,'C',$fill);
					$this->Cell($w[2],6,$row[4],'LR',0,'C',$fill);
					$this->Cell($w[3],6,number_format($row[7]),'LR',0,'R',$fill);
					$this->Cell($w[4],6,number_format($row[8]),'LR',0,'R',$fill);
					$this->Cell($w[5],6,number_format($row[9]),'LR',0,'R',$fill);
					$this->Cell($w[6],6,number_format($row[10]),'LR',0,'R',$fill);
					$this->Ln();
					$fill=!$fill;
					}
				}
			}
		$this->Cell(array_sum($w),0,'','T');
		}
		}
		$pdf=new PDF();
		//Column titles
		if ($_SESSION['klik']=='Vkupno'){
			$header=array('Конто','Корисник','Должи','Побарува','Салдо должи','Салдо побарува');
		}
		elseif($_SESSION['klik']=="Vkupno_saldo"){
			$header=array('Конто','Корисник','Пр.салдо должи','Пр.салдо побарува','Должи','Побарува','Салдо должи','Салдо побарува');
		}
		elseif ($_SESSION['klik']=='Analitika'){
			if ($_SESSION['totfin']==1){
				$header=array('Бр.на документ','Должи','Побарува','Салдо должи','Салдо побарува');
			}
			else{
				$header=array('Датум','Валута','Бр.на документ','Должи','Побарува','Салдо должи','Салдо побарува');
			}
		}
		//Data loading
		
		//*** Load MySQL Data ***//
		$objConnect = anyconnect($_SESSION['fin_baza'][0]);
		$aaa=$ini->getinival($_SESSION['fin_baza'][0], 'dbname', '');
		$objDB = mysqli_select_db($objConnect, $aaa);     
		$objQuery = mysqli_query($objConnect, $cmd);
		
		$resultData = array();
		if ($_SESSION['klik']=='Vkupno')
		{
			for ($i=0;$i<mysqli_num_rows($objQuery)-1;$i++) {
				$result = mysqli_fetch_array($objQuery);
				if ($_SESSION['totfin']==1){
					if (!is_null($result['KONTO'])){
						continue;
					}
				}
			$rez=mysqli_query($objConnect, "SELECT LEFT(opis,18) from firmi where cod=".$result['KORISNIK']." ");
			$korisnik=mysqli_fetch_row($rez);
			$result[0] = $korisnik[0];
			$result['KORISNIK'] = $korisnik[0];
			array_push($resultData,$result);
			}
		}
		elseif ($_SESSION['klik']=='Vkupno_saldo')
		{
			for ($i=0;$i<mysqli_num_rows($objQuery)-1;$i++) {
				$result = mysqli_fetch_array($objQuery);
				if ($_SESSION['totfin']==1){
					if (!is_null($result['KONTO'])){
						continue;
					}
				}
				$rez=mysqli_query($objConnect, "SELECT LEFT(opis,18) from firmi where cod=".$result['KORISNIK']." ");
				$korisnik=mysqli_fetch_row($rez);
				//$replace_arr=array(0=>$korisnik[0],'KORISNIK'=>$korisnik[0]);
				//$result=array_replace($result, $replace_arr);
				$result[0] = $korisnik[0];
				$result['KORISNIK'] = $korisnik[0];
				array_push($resultData,$result);
			}
		}
		elseif ($_SESSION['klik']=='Analitika')
		{
			for ($i=0;$i<mysqli_num_rows($objQuery)-1;$i++) {
				$result = mysqli_fetch_array($objQuery);
				if (is_null($result['DIZ'])){
					$result[2] = '';
					$result['DATUM'] = '';
				}
				if (is_null($result['BROJ']) && !is_null($result['KONTO'])){
					$result[4] = $result['KONTO'];
					$result['BROJ'] = $result['KONTO'];
				}
				if (is_null($result['BROJ']) && is_null($result['KONTO'])){
					$rez=mysqli_query($objConnect, "SELECT LEFT(opis,18) from firmi where cod=".$result['KORISNIK']." ");
					$korisnik=mysqli_fetch_row($rez);
					$result[4] = 'за '.$korisnik[0];
					$result['BROJ'] = 'за '.$korisnik[0];
				}
				//print_r($result);
				//echo "<br/><br/>";
				array_push($resultData,$result);
			}
		}
		//print_r($result);
		
			
			$pdf->AddFont('arial','','arial.php');
			$pdf->SetFont('arial','',20);
			$pdf->AddPage();
			$pdf->Image('img/logo.png',5,5,20);
			$pdf->Ln(20);
			if ($_SESSION['klik']=='Vkupno'){
				$pdf->Cell(0,0,'Состојба вкупно',0,0,'C');
				$pdf->SetFont('arial','',8);
				$pdf->Cell(-179);
				$pdf->Cell(0,20,'За корисник: '.$_SESSION['korisnik'].' ',0,0,'L');
				$pdf->Cell(-179);
				$pdf->Cell(0,30,'Конто: '.$_SESSION['konto'].' ',0,0,'L');
				$pdf->Cell(-179);
				$pdf->Cell(0,40,'Датум од '.$_SESSION['oddat'].' до '.$_SESSION['dodat'].'',0,0,'L');
				//$pdf->Cell(-200,20,'Za korisnik: '.cp_1251_zont($_SESSION['korisnik']).' ',0,0,'L');				
				//$pdf->Cell(-48,30,'Konto: '.$_SESSION['konto'].' ',0,0,'L');
				//$pdf->Cell(0,40,'Datum od '.$_SESSION['oddat'].' do '.$_SESSION['dodat'].'',0,0,'L');
			}
			elseif ($_SESSION['klik']=='Vkupno_saldo'){
				$pdf->Cell(0,0,'Состојба вкупно',0,0,'C');
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(-191);
				$pdf->Cell(0,20,'За корисник: '.$_SESSION['korisnik'].' ',0,0,'L');
				$pdf->Cell(-191);
				$pdf->Cell(0,30,'Конто: '.$_SESSION['konto'].' ',0,0,'L');
				$pdf->Cell(-191);
				$pdf->Cell(0,40,'Датум од '.$_SESSION['oddat'].' до '.$_SESSION['dodat'].'',0,0,'L');
			}
			elseif ($_SESSION['klik']=='Analitika'){
				$pdf->Cell(0,0,'Аналитичка картица',0,0,'C');
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(-191);
				$pdf->Cell(0,20,'За корисник: '.$_SESSION['korisnik'].' ',0,0,'L');
				$pdf->Cell(-191);
				$pdf->Cell(0,30,'Конто: '.$_SESSION['konto'].' ',0,0,'L');
				$pdf->Cell(-191);
				$pdf->Cell(0,40,'Датум од '.$_SESSION['oddat'].' до '.$_SESSION['dodat'].'',0,0,'L');
			}
			$pdf->Ln(25);
			 $pdf->FancyTable($header,$resultData);
		    //$pdf->PageNo();
			$pdf->Output();
?>