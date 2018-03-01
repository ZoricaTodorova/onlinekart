<?php
session_start();
//header("Content-type: text/html; charset=windows-1251");
define('FPDF_FONTPATH','font/');
include_once 'inc/functions.php';
require('fpdf17/fpdf.php');
// if (!logged()) redirect('index.php?rx=izvod');

$godina=$_GET['godina'];
//$vid=$_POST['vid'];
//$izvestaj=$_POST['faktura'];
$broj=$_GET['broj'];
$web=connectweb();
$handle=connectkart();


$firma=$_SESSION['idcmp'];
$firma_cmd=mysqli_query($handle, "SELECT opis from firmi where cod=$firma");
$firma_opis=mysqli_fetch_row($firma_cmd);

$cmd="select s1.*,s2.* from 
         (select  firma as cod_v,  fbr_user, m_u,  mat, matime, akciza, matopis, diz_f, valuta_f, 
		 kolicina, cena_v, iznos_v, akciza_v, ddv_v, ddv_proc
		 from fin_exchange_mat where fbr_user='$broj' and godina=$godina) as s1 left outer join 
         (select firmi.cod, firmi.opis, ifnull(firmi.adresa,'') as adresa, firmi.grad_cod, 
         ifnull(grad.opis,'') as opis_grad, ifnull(grad.p_cod,'') as p_cod, ifnull(grad.drzava,'') as drzava
         from firmi left outer join grad on firmi.grad_cod=grad.zip_cod) as s2
         on s1.cod_v=s2.cod";

// echo $cmd;
// exit();

$faktura_cmd=mysqli_query($handle, $cmd);
$faktura=mysqli_fetch_array($faktura_cmd);

$fbruser=substr($faktura['fbr_user'],0,3);

$ddv_cmd=mysqli_query($handle, $cmd);
$ddv_vk=0;
$iznos_vk=0;
$akciza_vk=0;
while ($ddv=mysqli_fetch_array($ddv_cmd)){
	$ddv_vk=$ddv_vk + $ddv['ddv_v'];
	$iznos_vk=$iznos_vk + $ddv['iznos_v'];
	$akciza_vk=$akciza_vk + $ddv['akciza_v'];
}

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
		function FancyTable($header,$data, $fbruser, $faktura)
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
			$w=array(25,60,25,20,20);
			$this->SetLeftMargin(25);
			
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
						$this->Cell($w[0],6,$row['mat'],'LR',0,'C',$fill);
						$this->Cell($w[1],6,$row['matime'],'LR',0,'C',$fill);
						
						if (EMPTY($faktura['matopis'])){
							$this->Cell($w[2],6,number_format($row['kolicina'],2),'LR',0,'R',$fill);
						}else{
							$this->Cell($w[2],6,substr($row['matopis'],0,15),'LR',0,'R',$fill);
						}
						
						if ($fbruser<>'099' && empty($faktura['matopis'])){
							$this->Cell($w[3],6,number_format(abs($row['cena_v']),2),'LR',0,'R',$fill);
						}
						elseif($fbruser=='099' && empty($faktura['matopis'])){
							$this->Cell($w[3],6,number_format(abs($faktura['iznos_v']/$faktura['kolicina']),2),'LR',0,'R',$fill);
						}
						elseif(!empty($faktura['matopis'])){
							$this->Cell($w[3],6,substr($row['matopis'],-9),'LR',0,'R',$fill);
						}
						
						$this->Cell($w[4],6,number_format(abs($row['iznos_v']),2),'LR',0,'R',$fill);
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
		if (EMPTY($faktura['matopis'])){
			$header=array('Шифра на артикл','Назив на артиклот','Количина','Цена','Износ ден');
		}else{
			$header=array('Шифра на артикл','Назив на артиклот','Промет','Процент','Износ ден');
		}
		
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
			$pdf->SetFont('arialbd','',13);
			$pdf->Cell(-191);
			if ($faktura['iznos_v']<0){
				$pdf->Cell(0,80,'КНИЖНО ОДОБРЕНИЕ бр. '.$faktura['fbr_user'].' ',0,0,'L');
			}elseif ($faktura['iznos_v']>0){
				$pdf->Cell(0,80,'КНИЖНО ЗАДОЛЖЕНИЕ бр. '.$faktura['fbr_user'].' ',0,0,'L');
			}
			$pdf->AddFont('arial','','arial.php');
			$pdf->SetFont('Arial','',6);
			$pdf->Cell(-191);
			$pdf->Cell(0,90,'бројот на фактурата-одобрението е и сериски број',0,0,'L');

			$pdf->AddFont('arial','','arial.php');
			$pdf->SetFont('Arial','',7);
			$pdf->Rect(134, 21, 67, 23);
			$pdf->Cell(-65);
			$pdf->Cell(0,20,'ЛУКОИЛ Македонија ДООЕЛ - Скопје',0,0,'L');
			$pdf->Cell(-65);
			$pdf->Cell(0,30,'ЕДБ: МК4030005551138',0,0,'L');
			$pdf->Cell(-65);
			$pdf->Cell(0,40,'Сметка: 300000002136558',0,0,'L');
			$pdf->Cell(-65);
			$pdf->Cell(0,50,'Банка: Комерцијална Банка',0,0,'L');

			$pdf->Cell(-40);
			$pdf->Cell(0,75,'Датум на фактура:  '.$faktura['diz_f'].'',0,0,'L');
			$pdf->Cell(-40);
			$pdf->Cell(0,85,'Датум на валута:    '.$faktura['valuta_f'].'',0,0,'L');
			$pdf->Ln(60);
			
			$pdf->FancyTable($header,$resultData, $fbruser, $faktura);
			
			$pdf->Ln(3);
				
			$pdf->SetFont('Arial','',7);
			if (!EMPTY($faktura['akciza_v']) && $faktura['akciza_v']<>0){
				$pdf->Cell(85);
				$pdf->Cell(20,5,'Акциза:',1,0,'L');
				$pdf->Cell(20,5,number_format($faktura['akciza'],0),1,0,'R');
				$pdf->Cell(20,5,number_format($faktura['akciza_v'],0),1,1,'R');
			}
			$pdf->Cell(85);
			$pdf->Cell(25,5,'Основица за ДДВ:',1,0,'L');
			$pdf->Cell(20,5,'   ',1,0,'R');
			$pdf->Cell(20,5,number_format(abs($akciza_vk+$iznos_vk),2),1,1,'R');
			$pdf->Cell(85);
			$pdf->Cell(25,5,'ДДВ '.$faktura['ddv_proc'].'%:',1,0,'L');
			$pdf->Cell(20,5,'   ',1,0,'R');
			$pdf->Cell(20,5,number_format(abs($ddv_vk),2),1,1,'R');
			$pdf->SetFont('Arialbd','',7);
			$pdf->Cell(85);
			$pdf->Cell(25,5,'ВКУПНО:',1,0,'L');
			$pdf->Cell(20,5,'   ',1,0,'R');
			$pdf->Cell(20,5,number_format(abs($ddv_vk+$akciza_vk+$iznos_vk),2),1,1,'R');
			
			
			$pdf->SetFont('Arialbd','',7);
			$pdf->Cell(-175);
			//$pdf->Ln(30);
			$pdf->Cell(0,0,'',0,0,'L');
			$pdf->SetFont('Arial','',7);
			$pdf->Cell(-175);
			$pdf->Cell(0,10,'',0,0,'L');
			
			$pdf->SetFont('Arialbd','',7);
			$pdf->SetDrawColor(0,80,180);
			$pdf->SetFillColor(230,230,0);
			$pdf->SetTextColor(220,50,50);
			$pdf->Cell(-190);
			$pdf->Cell(0,40,'Напомени',0,0,'L');
			$pdf->SetFont('Arial','',7);
			$pdf->SetTextColor(0,0,0);
			$pdf->Cell(-190);
			$pdf->Cell(0,50,'Испорачаното гориво е по договор бр. 1672/12 од 23.04.2014',0,0,'L');
			
			$pdf->Ln(50);
			
			$pdf->SetFont('Arialbd','',8);
			$pdf->Cell(-15);
			//$pdf->Ln(40);
			$pdf->Cell(20,5,'За ЛУКОИЛ Македонија ДООЕЛ - Скопје',0,1,'L');
			$pdf->Cell(-15);
			$pdf->Cell(20,5,'одговорно лице',0,1,'L');
			$pdf->Cell(-15);
			$pdf->Cell(20,20,'__________________________________',0,0,'L');
			
			$pdf->Output();
?>