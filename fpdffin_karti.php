<?php
session_start();
//header("Content-type: text/html; charset=windows-1251");
define('FPDF_FONTPATH','font/');
include_once 'inc/functions.php';
require('fpdf17/fpdf.php');
// if (!logged()) redirect('index.php?rx=izvod');

$godina=$_POST['godina_pdf'];
$cIdCmp = $_SESSION['idcmp'];
$vid=$_POST['vid_pdf'];
$web=connectweb();
$cmd_vidkonto=mysqli_query($web, "select konto,opis from vid_konto where vid=$vid");
$konto=mysqli_fetch_row($cmd_vidkonto);

$handle=connectkart();

$firma=$_SESSION['idcmp'];
$firma_cmd=mysqli_query($handle, "SELECT opis from firmi where cod=$firma");
$firma_opis=mysqli_fetch_row($firma_cmd);

$cmd=mysqli_query($handle, "select primary_c, 0 as faktura, opis, broj, nalmes, org_e, sumad, sumap, diz, datum 
									from fin_exchange where korisnik =".$_SESSION['idcmp']." and godina=$godina and konto=$konto[0] order by diz, nalmes");


$status_cmd=mysqli_query($web, "Select * from vid_status where vid=$vid order by rbr");

$vk_dolzi=0;
$vk_pobaruva=0;
$ima_nalmes=0;
$nalmes_dolzi=0;
$nalmes_pobaruva=0;


class PDF extends FPDF
{
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
			$this->SetFont('arial','',8);
			$this->SetFontSize(8);
			//Header
			$w=array(60,40,15,20,20,19,19); //tuka se kazuva kolku i kolkavi koloni imame
			$this->SetLeftMargin(10);
			for($i=0;$i<count($header);$i++)
				$this->Cell($w[$i],6,$header[$i],1,0,'C',true);
			$this->Ln();
			//Color and font restoration
			$this->SetFillColor(224,235,255);
			$this->SetTextColor(0);
			$this->AddFont('arial','','arial.php');
			$this->SetFont('Arial','',8);
			//Data
			$fill=false;
			foreach($data as $row)
				{ //tuka gi pravime kolonite
				//$this->Cell($w[0],6,$row[2],'LR',0,'C',$fill);
				$this->Cell($w[0],6,$row['opis'],'LR',0,'C',$fill);
				if (isset($row['org_e']) && isset($row['broj'])){
				$this->Cell($w[1],6,$row['broj'],'LR',0,'C',$fill);
				$this->Cell($w[2],6,$row['org_e'],'LR',0,'C',$fill);
				}
				$this->Cell($w[3],6,$row['sumad'],'LR',0,'R',$fill);
				$this->Cell($w[4],6,$row['sumap'],'LR',0,'R',$fill);
				$this->Cell($w[5],6,$row['diz'],'LR',0,'C',$fill);
				$this->Cell($w[6],6,$row['datum'],'LR',0,'C',$fill);
				$this->Ln();
				$fill=!$fill;
				}
		$this->Cell(array_sum($w),0,'','T');
		}
}
		$pdf=new PDF();
		//Column titles
		$header=array('Опис','Ф-ра број','Орг. ед.','Должи','Побарува','Датум','Валута');
		//Data loading
		
		//*** Load MySQL Data ***//
		$objConnect = connectkart();
		$objDB = mysqli_select_db($objConnect, "isr");
		$objQuery = $cmd;
		
		$resultData = array();
		for ($i=0;$i<mysqli_num_rows($objQuery);$i++) {
				$result = mysqli_fetch_array($objQuery);
				
// 				print_r($result);
// 				exit();
				
				if ($result['nalmes']=='00'){
					$ima_nalmes=1;
					$nalmes_dolzi=$nalmes_dolzi + $result['sumad'];
					$nalmes_pobaruva=$nalmes_pobaruva + $result['sumap'];
					$nalmes_opis=$result['opis'];
					continue;
				}
				
				if ($ima_nalmes==1){
					$arr=Array ( 2 => $nalmes_opis, 'opis' => $nalmes_opis, 3 => 'Салдо од мината година', 'broj' => 'Салдо од мината година', 5=>'','org_e'=>'', 6 => $nalmes_dolzi, 'sumad' => $nalmes_dolzi, 7 => $nalmes_pobaruva, 'sumap' =>$nalmes_pobaruva, 8 => '2014-01-01', 'diz' => '2014-01-01', 9=>'2014-01-01', 'datum' => '2014-01-01') ;
					array_push($resultData,$arr);
					$ima_nalmes=0;
				}
				
				$vk_dolzi=$vk_dolzi + $result['sumad'];
				$vk_pobaruva=$vk_pobaruva + $result['sumap'];
				
				array_push($resultData,$result);
			}
			
			$vk_dolzi= $vk_dolzi + $nalmes_dolzi;
			$vk_pobaruva=$vk_pobaruva + $nalmes_pobaruva;
			$arr=Array ( 2 => 'Вкупно ДОЛЖИ/ПОБАРУВА', 'opis' => 'Вкупно ДОЛЖИ/ПОБАРУВА', 3 => '', 'broj' => '', 5=>'','org_e'=>'', 6 => number_format($vk_dolzi,0), 'sumad' => number_format($vk_dolzi,0), 7 => number_format($vk_pobaruva,0), 'sumap' =>number_format($vk_pobaruva,0), 8 => '', 'diz' => '', 9=>'', 'datum' => '') ;
			array_push($resultData,$arr);
			
			if ($vk_dolzi>$vk_pobaruva){
					
				$vk=$vk_dolzi- $vk_pobaruva;
				$arr=Array ( 2 => 'САЛДО', 'opis' => 'САЛДО', 3 => '', 'broj' => '', 5=>'','org_e'=>'', 6 => number_format($vk,0), 'sumad' => number_format($vk,0), 7 => '0.00', 'sumap' =>'0.00', 8 => '', 'diz' => '', 9=>'', 'datum' => '') ;
				array_push($resultData,$arr);
					
			}
			elseif ($vk_dolzi<$vk_pobaruva){
					
				$vk=$vk_pobaruva-$vk_dolzi;
				$arr=Array ( 2 => 'САЛДО', 'opis' => 'САЛДО', 3 => '', 'broj' => '', 5=>'','org_e'=>'', 6 => '0.00', 'sumad' => '0.00', 7 => number_format($vk,0), 'sumap' =>number_format($vk,0), 8 => '', 'diz' => '', 9=>'', 'datum' => '') ;
				array_push($resultData,$arr);
			}
			else{
				$arr=Array ( 2 => 'САЛДО', 'opis' => 'САЛДО', 3 => '', 'broj' => '', 5=>'','org_e'=>'', 6 => '0.00', 'sumad' => '0.00', 7 => '0.00', 'sumap' =>'0.00', 8 => '', 'diz' => '', 9=>'', 'datum' => '') ;
				array_push($resultData,$arr);
			}
			
// 			print_r($resultData);
// 			exit();
			$pdf->AddFont('arial','','arial.php');
			$pdf->SetFont('Arial','',7);
			$pdf->AddPage();
			$pdf->Image('img/logo.png',85,5,'C');
			$pdf->Ln(17);
			$pdf->SetFont('arial','',12);
			$pdf->Cell(0,0,'Финансиска картица',0,0,'C');
			$pdf->SetFont('arial','',8);
			
			//*** za status ***//
			
			$pdf->Cell(-55);
			$pdf->Cell(0,13,'Статус на клиентот',0,0,'C');
			$k=23;
			while ($stat=mysqli_fetch_array($status_cmd)){
					
				$tabela=$stat['tabela'];
				$doct=$stat['doct'];
				$doct_arr=explode(',', $doct);
					
				if (EMPTY($stat['doct'])){
					$cmd = "select (round(sum(sumap-sumad), 2)) as limito from $tabela where korisnik = '$cIdCmp' and konto=$konto[0]";
					$res = mysqli_query($handle, $cmd);
					$row=mysqli_fetch_row($res);
				}
				elseif(substr($doct_arr[0],0,1)=='#'){
					$dd=substr($doct_arr[0],1);
					foreach($doct_arr as $doc){
						$dd=$dd.','.substr($doc,1);
					}
					$cmd = "select (round(sum(sumap-sumad), 2)) as limito from $tabela where naldoc not in ($dd) and korisnik = '$cIdCmp' and konto=$konto[0]";
					$res = mysqli_query($handle, $cmd);
					$row=mysqli_fetch_row($res);
				}
				else{
					$cmd = "select (round(sum(sumap-sumad), 2)) as limito from $tabela where naldoc in ($doct) and korisnik = '$cIdCmp' and konto=$konto[0]";
					$res = mysqli_query($handle, $cmd);
					$row=mysqli_fetch_row($res);
				}
				
				$pdf->Cell(-55);
				$pdf->Cell(0,$k,$stat['opis'].' :      '.number_format($row[0],0).' ',0,0,'R');
				$k=$k+8;
			}
			
			$cmd = "select max(wdt) as limito from $tabela where korisnik = '$cIdCmp'";
			$res = mysqli_query($handle, $cmd);
			$row=mysqli_fetch_row($res);
			
			$pdf->Cell(-55);
			$pdf->Cell(0,$k,'Датум и час на ажурирање:    '.$row[0].' ',0,0,'R');
				
			//****************//
			
			$pdf->Cell(-191);
			$pdf->Cell(0,20,'За корисник: '.$firma_opis[0].' ',0,0,'L');
			$pdf->Cell(-191);
			$pdf->Cell(0,30,'Година: '.$godina.' ',0,0,'L');
			$pdf->Cell(-191);
			$pdf->Cell(0,40,'Вид: '.$konto[1].' ',0,0,'L');
			$pdf->Ln(45);
			
		    $pdf->FancyTable($header,$resultData);
			
			$pdf->Output();
?>