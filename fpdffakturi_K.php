<?php
session_start();
//header("Content-type: text/html; charset=windows-1251");
define('FPDF_FONTPATH','font/');
include_once 'inc/functions.php';
require('fpdf17/fpdf.php');
// if (!logged()) redirect('index.php?rx=izvod');

$godina=$_GET['godina'];
//$izvestaj=$_POST['faktura'];

$broj=$_GET['broj'];

$web=connectweb();
$handle=connectkart();

$firma=$_SESSION['idcmp'];
$firma_cmd=mysqli_query($handle, "SELECT opis from firmi where cod=$firma");
$firma_opis=mysqli_fetch_row($firma_cmd);

	$cmd=" select godina,fbr_user, firma, pom as rbr, pom, 
			 if ( left(pom,1) <>'6' , mat, if(pom='60','400000','400001') ) as mat, 
			 if (left(pom,1)<>'6',matime, if(pom='60','Доп.асортиман 18%','Доп.асортиман 5% ' )) as matime, 
			 datum_f, valuta_f, diz_f, koef, 
			 org_opis, ddv_proc, kolicina ,	cena_v,
			 iznos_v ,	ddv_v, akciza_i, cena_vk as cena_vvk, iznos_vk as iznos_vvk, iznos,
			 if (edm2='l', cena_v-akciza*koef, cena) as cena, 
			 if (edm2='l', akciza*koef, akciza) as akciza, 
			 if (edm2='l', edm2, edm) as edm, edm2, 
			 cod,opis, adresa, grad_cod, opis_grad, drzava 
			 from 
		( select godina,fbr_user, firma, rbr, 
			 if(left(mat,2)='01' and left(rbr,1)=2, if(ddv_proc='5','61','60') , rbr) as pom,
		 	 mat, matime, datum_f, valuta_f, diz_f, 
			 org_opis, ddv_proc, edm,	kolicina ,	cena_v, koef, 
			 iznos_v ,	ddv_v, akciza_i, akciza, cena_vk as cena_vk, iznos_vk as iznos_vk,
			 cena,	iznos, edm2, s2.*
			 FROM
			 (select * from fin_exchange_mat where fbr_user='$broj' and godina=$godina) as s1
	  			 left outer join
	  			 (select firmi.cod, if(firmi.ime_celo='',firmi.opis,firmi.ime_celo) as opis, ifnull(firmi.adresa,'') as adresa,
	  			 ifnull(grad.p_cod,'') as grad_cod, ifnull(grad.opis,'') as opis_grad, ifnull(grad.drzava,'') as drzava 
				 from firmi left outer join grad on firmi.grad_cod=grad.zip_cod) as s2 on s1.firma=s2.cod ) as s3 ";

// echo $cmd;
// exit();

$faktura_cmd=mysqli_query($handle, $cmd);
$faktura=mysqli_fetch_array($faktura_cmd);

$datum = $faktura['datum_f'];
$datum = date("d/m/Y", strtotime($datum));
$valuta = $faktura['valuta_f'];
$valuta = date("d/m/Y", strtotime($valuta));

$netoD=0;
$ddvD=0;
$neto18=0;
$ddv18=0;
$neto5=0;
$ddv5=0;
$neto0=0;
$ddv0=0;
$akc=0;
$netD=0;

$totali_cmd=mysqli_query($handle, $cmd);
while ($totali=mysqli_fetch_array($totali_cmd)){
	$korisnik=$totali['firma'];
	
	$mat=substr($totali['mat'],0,1);
	
	if ($mat=='0'){
		$netoD=$netoD+$totali['iznos_v'];
		$ddvD=$ddvD+$totali['ddv_v'];
		$akc=$akc+$totali['akciza_i'];
		$netD=$netD+$totali['iznos'];
	}
	elseif(($totali['rbr']=='60' || $totali['rbr']=='TU' || $totali['rbr']=='TP') && $totali['ddv_proc']==18){
		$neto18=$neto18+$totali['iznos_v'];
		$ddv18=$ddv18+$totali['ddv_v'];
	}
	elseif($totali['rbr']=='61' && $totali['ddv_proc']==5){
		$neto5=$neto5+$totali['iznos_v'];
		$ddv5=$ddv5+$totali['ddv_v'];
	}
	else{
		$neto0=$neto0+$totali['iznos_v'];
		$ddv0=$ddv0+$totali['ddv_v'];
	}
}

$vkupno=$netoD+$ddvD+$neto18+$ddv18+$neto5+$ddv5;
//$vkupno=number_format($vkupno,2);

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
				$w=array(50,30,15,20,20,19,19,19); //tuka se kazuva kolku i kolkavi koloni imame
			for($i=0;$i<count($header);$i++)
				$this->Cell($w[$i],5,$header[$i],1,0,'C',true);
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
						$this->Cell($w[0],6,$row['mat'].' - '.$row['matime'],'LR',0,'C',$fill);
						$this->Cell($w[1],6,$row['edm'],'LR',0,'C',$fill);
						$this->Cell($w[2],6,number_format($row['kolicina'],2),'LR',0,'R',$fill);
						if ($row['cena_vvk']==0){
							$this->Cell($w[3],6,' ','LR',0,'R',$fill);
						}else{
							$this->Cell($w[3],6,number_format($row['cena_vvk'],2),'LR',0,'R',$fill);
						}
						$this->Cell($w[4],6,number_format($row['iznos_vvk'],2),'LR',0,'R',$fill);
						$this->Cell($w[5],6,number_format($row['iznos_v'],2),'LR',0,'R',$fill);
						$this->Cell($w[6],6,number_format($row['ddv_proc'],0),'LR',0,'C',$fill);
						$this->Cell($w[7],6,number_format($row['ddv_v'],2),'LR',0,'R',$fill);
						$this->Ln();
						$fill=!$fill;
				}
		$this->Cell(array_sum($w),0,'','T');
		}
		
		function Footer()
		{
			$this->SetFont('Arialbd','',7);
			$this->SetY(-15);
			$this->Cell(0,12,'__________________________________________________________________________________________________________________________',0,0,'C');
			$this->SetY(-15);
			$this->Cell(0,20,'Партизански одреди 18 , 1000 Скопје, Македонија, тел. 02 3293033 , Факс 02 3293021   e-mail: lukoilmacedonia@lukoil.com.mk',0,0,'C');	
		}
		
}
		$pdf=new PDF();
		//Column titles
			$header=array('Опис на продажба','едм.','Количина','Цена со ддв','Износ со ддв','Износ без ддв','ДДВ%','ДДВ');
		//Data loading
		
		//*** Load MySQL Data ***//
		$objConnect = connectkart();
		$objDB = mysqli_select_db($objConnect, "isr");
		
		$objQuery = mysqli_query($handle, $cmd);
		
		$resultData = array();
		for ($i=0;$i<mysqli_num_rows($objQuery);$i++) {
				$result = mysqli_fetch_array($objQuery);
				
				array_push($resultData,$result);
		}
// 			print_r($resultData);
// 			exit();
			$pdf->AddFont('arial','','arial.php');
			$pdf->SetFont('Arial','',7);
			$pdf->AddPage();
			$pdf->Image('img/logo.png',10,3,'L');
			$pdf->AddFont('arialbd','','arialbd.php');
			$pdf->SetFont('arialbd','',14);
			$pdf->Cell(0,0,'ЛУКОИЛ Македонија ДООЕЛ Скопје',0,0,'R');
			$pdf->Ln(5);
			$pdf->SetFont('arial','',12);
			$pdf->Cell(0,0,'',0,0,'C');
			$pdf->SetFont('arial','',8);
			
			$pdf->Rect(10, 21, 75, 23);
			$pdf->Cell(-188);
			$pdf->Cell(0,20,'Купувач: '.$firma.' ',0,0,'L');
			$pdf->AddFont('arialbd','','arialbd.php');
			$pdf->SetFont('arialbd','',7);
			$pdf->Cell(-188);
			$pdf->Cell(0,30,''.$faktura['opis'].' ',0,0,'L');
			$pdf->Cell(-188);
			$pdf->Cell(0,40,''.$faktura['adresa'].' ',0,0,'L');
			$pdf->Cell(-188);
			$pdf->Cell(0,50,''.$faktura['grad_cod'].' '.$faktura['opis_grad'].' '.$faktura['drzava'].' ',0,0,'L');
			$pdf->SetFont('arial','',8);
			$pdf->Cell(-191);
			$pdf->Cell(0,70,'Ова е електронска верзија на',0,0,'L');
			$pdf->SetFont('arialbd','',13);
			$pdf->Cell(-191);
			$pdf->Cell(0,80,'ФАКТУРА бр. '.$faktura['fbr_user'].' ',0,0,'L');
			$pdf->AddFont('arial','','arial.php');
			$pdf->SetFont('Arial','',6);
			$pdf->Cell(-191);
			$pdf->Cell(0,90,'бројот на фактурата е и сериски број',0,0,'L');
			$pdf->AddFont('arial','','arial.php');
			$pdf->SetFont('Arial','',7);
			$pdf->Cell(-191);
			$pdf->Cell(0,100,'Економска единица: 099 - '.$faktura['org_opis'].' ',0,0,'L');

			$pdf->Rect(134, 21, 67, 23);
			$pdf->AddFont('arial','','arial.php');
			$pdf->SetFont('Arial','',7);
			$pdf->Cell(-65);
			$pdf->Cell(0,20,'ЛУКОИЛ Македонија ДООЕЛ - Скопје',0,0,'L');
			$pdf->Cell(-65);
			$pdf->Cell(0,30,'ЕДБ: МК4030005551138',0,0,'L');
			$pdf->Cell(-65);
			$pdf->Cell(0,40,'Сметка: 300000002136558',0,0,'L');
			$pdf->Cell(-65);
			$pdf->Cell(0,50,'Банка: Комерцијална Банка',0,0,'L');
			
			$pdf->Cell(-40);
			$pdf->Cell(0,75,'Датум на фактура:   '.$datum.'',0,0,'L');
			$pdf->Cell(-40);
			$pdf->Cell(0,85,'Валута на фактура:  '.$valuta.'',0,0,'L');
			$pdf->Ln(60);
			
			$pdf->FancyTable($header,$resultData);
			
			$pdf->Ln(3);
			
			$pdf->SetFont('Arial','',7);
			
			if ($netoD<>0){
				$pdf->Cell(127);
				$pdf->Cell(40,5,'Основица за деривати:',1,0,'L');
				$pdf->Cell(25,5,number_format($netoD,2),1,1,'R');
			}
			if ($ddvD<>0){
				$pdf->Cell(127);
				$pdf->Cell(40,5,'ДДВ деривати 18%:',1,0,'L');
				$pdf->Cell(25,5,number_format($ddvD,2),1,1,'R');
			}
			if ($neto18<>0){
				$pdf->Cell(127);
				$pdf->Cell(40,5,'Основица за ДАС1:',1,0,'L');
				$pdf->Cell(25,5,number_format($neto18,2),1,1,'R');
			}
			if ($ddv18<>0){
				$pdf->Cell(127);
				$pdf->Cell(40,5,'ДДВ ДАС 18%:',1,0,'L');
				$pdf->Cell(25,5,number_format($ddv18,2),1,1,'R');
			}
			if ($neto5<>0){
				$pdf->Cell(127);
				$pdf->Cell(40,5,'Основица за ДАС2:',1,0,'L');
				$pdf->Cell(25,5,number_format($neto5,2),1,1,'R');
			}
			if ($ddv5<>0){
				$pdf->Cell(127);
				$pdf->Cell(40,5,'ДДВ ДАС 5%:',1,0,'L');
				$pdf->Cell(25,5,number_format($ddv5,2),1,1,'R');
			}
			$pdf->SetFont('Arialbd','',8);
			$pdf->Cell(127);
			$pdf->Cell(40,5,'ВКУПНО:',1,0,'L');
			$pdf->Cell(25,5,number_format($vkupno,2),1,1,'R');

			
			$pdf->SetFont('Arialbd','',7);
			$pdf->SetDrawColor(0,80,180);
			$pdf->SetFillColor(230,230,0);
			$pdf->SetTextColor(220,50,50);
			$pdf->Cell(-180);
			$pdf->Ln(15);
			$pdf->Cell(0,4,'Напомена',0,0,'L');
			$pdf->SetFont('Arial','',7);
			$pdf->SetTextColor(0,0,0);
			$pdf->Cell(-190);
			$pdf->Cell(0,14,'Ве молиме најдоцна до наведениот датум за плаќање да го подмирите Вашиот долг. За секое задоцнување',0,0,'L');
			$pdf->Cell(-190);
			$pdf->Cell(0,21,'Ви се пресметува законска казнена камата.',0,0,'L');
			
			$pdf->SetFont('Arialbd','',8);
			$pdf->Cell(-190);
			$pdf->Ln(30);
			$pdf->Cell(0,0,'За ЛУКОИЛ Македонија ДООЕЛ - Скопје',0,0,'L');
			$pdf->Cell(-190);
			$pdf->Cell(0,10,'одговорно лице',0,0,'L');
			$pdf->Cell(-190);
			$pdf->Cell(0,35,'__________________________________',0,0,'L');
			
			//$pdf->Footer();
			
			$pdf->Output();
?>