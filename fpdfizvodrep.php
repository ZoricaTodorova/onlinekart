<?php
session_start();
//header("Content-type: text/html; charset=windows-1251");
define('FPDF_FONTPATH','font/');
include_once 'inc/functions.php';
require('fpdf17/fpdf.php');
// if (!logged()) redirect('index.php?rx=izvod');

$handle=connectkart();

$firma_cod = $_SESSION['idcmp'];
$firma_cmd=mysqli_query($handle, "Select opis from firmi where cod='$firma_cod'");
$firma_opis=mysqli_fetch_row($firma_cmd);
// echo $firma_opis[0];
// exit();

if (isset($_POST['bss']) && isset($_POST['karts']) && isset($_POST['oddat']) && isset($_POST['dodat'])){
	$oddat=$_POST['oddat'];
	$dodat=$_POST['dodat'];
	$bss=$_POST['bss'];
	$karts=$_POST['karts'];
}

$odgodina=substr($oddat,0,4);
$odmesec=substr($oddat,4,2);
$odden=substr($oddat,6,2);
$dogodina=substr($dodat,0,4);
$domesec=substr($dodat,4,2);
$doden=substr($dodat,6,2);

$cmd = "select 'aaa' as trclass,
			0 as bold, 
			firmi.opis as klient, 
			kartici.vozac_id as sap,
			kartici.vozac as korisnik, 
			lkk_broj as karta, 
			t_dt as datum_cas, 
			kartici.reg_br as avtomobil, 
			dokument as smetka, 
			materijali.opis as artikl,
			mat as artgrup,  
			format(bs_exchange.cena,2) as ed_cena, 
			format(kolicina,2) as kolicina, 
			format(iznos,2) as vrednost, 
			bs as stanica,
			kilometri as kilometraza,
			firmi.cod as klientcod from bs_exchange
	left join kartici on kart_br = lkk_broj and bs_exchange.firma=kartici.firma
	left join firmi on bs_exchange.firma = firmi.cod
	left join materijali on materijali.cod = bs_exchange.mat and materijali.godina = bs_exchange.godina 
	where t_dt between '$oddat' and '$dodat' and 
	lkk_broj in ($karts) and bs in ($bss) and mat <> ''
	order by klientcod, karta, datum_cas";
	$res=mysqli_query($handle, $cmd) or die(mysqli_error($handle));
	//echo $cmd;
	$cmd = "select 'bbb' as trclass,
			1 as bold, 
			concat('Вкупно за артикл:', mat) as klient, 
			lkk_broj as kardno, 
			mat as artgrup,  
			materijali.opis as artikl,
			format(sum(iznos),0) as vrednost from bs_exchange 
	left join kartici on kart_br = lkk_broj and bs_exchange.firma=kartici.firma
	left join firmi on bs_exchange.firma = firmi.cod
	left join materijali on materijali.cod = bs_exchange.mat and materijali.godina = bs_exchange.godina 
	where t_dt between '$oddat' and '$dodat' and 
	lkk_broj in ($karts) and bs in ($bss)
	group by klient, kardno, artgrup
	order by kardno, artikl;";
	$res1=mysqli_query($handle, $cmd) or die(mysqli_error());
	
	$cmd = "select 'ccc' as trclass,
			1 as bold, 
			materijali.opis as artikl,
			mat as artgrup,
			format(sum(iznos),0) as vrednost from bs_exchange
	left join kartici on kart_br = lkk_broj and bs_exchange.firma=kartici.firma
	left join firmi on bs_exchange.firma = firmi.cod
	left join materijali on materijali.cod = bs_exchange.mat and materijali.godina = bs_exchange.godina 
	where t_dt between '$oddat' and '$dodat' and
	lkk_broj in ($karts) and bs in ($bss)
	order by artikl;";
	$res2=mysqli_query($handle, $cmd) or die(mysqli_error());
	
	$cmd = "select 'ddd' as trclass,
			1 as bold, 
			concat('Вкупно за карта:', lkk_broj) as klient,
			lkk_broj as kardno,
			format(sum(iznos),0) as vrednost from bs_exchange
	left join kartici on kart_br = lkk_broj and bs_exchange.firma=kartici.firma
	left join firmi on bs_exchange.firma = firmi.cod
	where t_dt between '$oddat' and '$dodat' and
	lkk_broj in ($karts) and bs in ($bss)
	group by klient, kardno
	order by kardno";
	$res3=mysqli_query($handle, $cmd) or die(mysqli_error());
	//echo $cmd;
	$tmp = '';
	$tmp3 = '';
	$result = array();
	while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)){
		if ($tmp=='') $tmp=$row['karta'];
		else 
		{
			if ($tmp != $row['karta'])
			{
				if (isset($row1)) $result[] = $row1;
				do {
					$row1 = mysqli_fetch_array($res1, MYSQLI_ASSOC);
					if ($tmp == $row1['kardno']) $result[] = $row1;
				} while ($tmp == $row1['kardno']);
				$row3 = mysqli_fetch_array($res3, MYSQLI_ASSOC);
				$result[] = $row3;
				$tmp = $row['karta'];
			}
		}			
		//$result[] = $row;
		if (isset($row)) $result[] = $row;
	}
	//$result[] = $row1;
	if (isset($row1)) $result[] = $row1;
	while ($row1 = mysqli_fetch_array($res1, MYSQLI_ASSOC)) $result[] = $row1;
	$result[] = mysqli_fetch_array($res3, MYSQLI_ASSOC);
	
	
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
			$this->AddFont('arialbd','','arialbd.php');
			$this->SetFont('arialbd','',7);
			//Header
			$w=array(16,12,25,17,17,40,12,13,14,11,7,10); //tuka se kazuva kolku i kolkavi koloni imame
			$this->SetLeftMargin(10);
			for($i=0;$i<count($header);$i++)
				$this->Cell($w[$i],6,$header[$i],1,0,'C',true);
			$this->Ln();
			//Color and font restoration
			$this->SetFillColor(224,235,255);
			$this->SetTextColor(0);
			$this->AddFont('arial','','arial.php');
			$this->SetFont('arial','',6);
			//Data
			$fill=false;
			foreach($data as $row)
				{ //tuka gi pravime kolonite
					if (isset($row['korisnik'])){
						$this->Cell($w[0],6,$row['korisnik'],'LR',0,'C',$fill);
					}else {$this->Cell($w[0],6,' ','LR',0,'C',$fill);}
					if (isset($row['karta'])){
						$this->Cell($w[1],6,$row['karta'],'LR',0,'C',$fill);
					}else {$this->Cell($w[1],6,' ','LR',0,'C',$fill);}
					if (isset($row['datum_cas'])){
						$this->Cell($w[2],6,$row['datum_cas'],'LR',0,'C',$fill);
					}else {$this->Cell($w[2],6,' ','LR',0,'C',$fill);}
					if (isset($row['avtomobil'])){
						$this->Cell($w[3],6,$row['avtomobil'],'LR',0,'C',$fill);
					}else {$this->Cell($w[3],6,' ','LR',0,'C',$fill);}
					if (isset($row['smetka'])){
						$this->Cell($w[4],6,$row['smetka'],'LR',0,'C',$fill);
					}else {$this->Cell($w[4],6,' ','LR',0,'C',$fill);}
					if (isset($row['artikl'])){
						$this->Cell($w[5],6,$row['artikl'],'LR',0,'C',$fill);
					}else {$this->Cell($w[5],6,' ','LR',0,'C',$fill);}
					if (isset($row['ed_cena'])){
						$this->Cell($w[6],6,$row['ed_cena'],'LR',0,'R',$fill);
					}else {$this->Cell($w[6],6,' ','LR',0,'C',$fill);}
					if (isset($row['kolicina'])){
						$this->Cell($w[7],6,$row['kolicina'],'LR',0,'R',$fill);
					}else {$this->Cell($w[7],6,' ','LR',0,'C',$fill);}
					if (isset($row['vrednost'])){
						$this->Cell($w[8],6,$row['vrednost'],'LR',0,'R',$fill);
					}else {$this->Cell($w[8],6,' ','LR',0,'C',$fill);}
					if (isset($row['stanica'])){
						$this->Cell($w[9],6,$row['stanica'],'LR',0,'C',$fill);
					}else {$this->Cell($w[9],6,' ','LR',0,'C',$fill);}
					if (isset($row['kilometraza'])){
						$this->Cell($w[10],6,$row['kilometraza'],'LR',0,'C',$fill);
					}else {$this->Cell($w[10],6,' ','LR',0,'C',$fill);}
					if (isset($row['sap'])){
						$this->Cell($w[11],6,$row['sap'],'LR',0,'C',$fill);
					}else {$this->Cell($w[11],6,' ','LR',0,'C',$fill);}
				$this->Ln();
				$fill=!$fill;
				}
		$this->Cell(array_sum($w),0,'','T');
		}
		}
		$pdf=new PDF();
		//Column titles
		$header=array('Корисник','Карта','Датум/час','Регистрација','Сметка','Артикл','Ед.цена','Количина','Вредност','Станица','км','САП');
		//Data loading
		
		//*** Load MySQL Data ***//
// 		$objConnect = connectkart();
// 		$objDB = mysqli_select_db($objConnect, "isr");
// 		$objQuery = mysqli_query($objConnect, $cmd);
		
// 		$resultData = array();
// 		for ($i=0;$i<mysqli_num_rows($objQuery);$i++) {
// 			$result = mysqli_fetch_array($objQuery);
// 			array_push($resultData,$result);
// 			}
			//print_r($result);
			$pdf->SetFont('Arial','',6);
			$pdf->AddPage();
			$pdf->Image('img/logo.png',80,8,33);
			$pdf->Ln(15);
			
			$pdf->AddFont('arialbd','','arialbd.php');
			$pdf->SetFont('arialbd','',7);
			$pdf->Cell(1);
			$pdf->Cell(50,5,'За корисник: '.$firma_opis[0],0,1,'L');
			$pdf->Cell(1);
			$pdf->Cell(50,5,'За објекти: '.$bss.' ',0,1,'L');
			$pdf->Cell(1);
			$pdf->Cell(50,5,'За карти: '.$karts.' ',0,1,'L');
			$pdf->Cell(1);
			$pdf->Cell(50,5,'За датум од: '.$odden.'/'.$odmesec.'/'.$odgodina.'  до: '.$doden.'/'.$domesec.'/'.$dogodina,0,0,'L');
			$pdf->Ln(10);
			
		    $pdf->FancyTable($header,$result);
			
			$pdf->Output();
?>