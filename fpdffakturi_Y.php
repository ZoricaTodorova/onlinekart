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
         (select  firma as cod_v, datum, datum_f, rezervoar, rez, fbr_user, m_u,  mat, matime, opis_edm, akciza, matopis, diz_f, valuta_f, 
		 kolicina, cena, iznos,cena_vk, iznos_vk, akciza_v, ddv_v, ddv_proc, akciza_ed, org_e, docb, zabeleska_f,prilozi_f, iznos_v, cena_v 
		 from fin_exchange_mat where fbr_user='$broj' and godina=$godina) as s1 left outer join 
         (select firmi.cod, firmi.opis, ifnull(firmi.adresa,'') as adresa, firmi.grad_cod, 
         ifnull(grad.opis,'') as opis_grad, ifnull(grad.p_cod,'') as p_cod, ifnull(grad.drzava,'') as drzava
         from firmi left outer join grad on firmi.grad_cod=grad.zip_cod) as s2
         on s1.cod_v=s2.cod";

// echo $cmd;
// exit();

$faktura_cmd=mysqli_query($handle, $cmd);
$faktura=mysqli_fetch_array($faktura_cmd);

// $org_cmd=mysqli_query($handle, 'select opis from org_e where cod='.$faktura['org_e']);
// $org_ime=mysqli_fetch_array($org_cmd);

$m_u=$faktura['m_u'];

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
		function FancyTable($header,$data,$m_u)
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
			$w=array(25,60,20,20,20);
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
					if ($m_u=='M'){
						$this->Cell($w[1],6,$row['matime'],'LR',0,'C',$fill);
					}else{
						$this->Cell($w[1],6,$row['matopis'],'LR',0,'C',$fill);
					}
					$this->Cell($w[2],6,number_format($row['kolicina'],2),'LR',0,'R',$fill);
					$this->Cell($w[3],6,number_format($row['cena_v'],2),'LR',0,'R',$fill);
					$this->Cell($w[4],6,number_format($row['iznos_v'],2),'LR',0,'R',$fill);
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
			$this->Cell(0,20,'����������� ������ 18 , 1000 �����, ���������, ���. 02 3293033 , ���� 02 3293021   e-mail: lukoilmacedonia@lukoil.com.mk',0,0,'C');
		}
}
		$pdf=new PDF();
		//Column titles
		$header=array('����� �� ������','����� �� ��������','��������','����', '����� ���.');
		
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
			$pdf->Cell(0,0,'������ ��������� ����� �����',0,0,'R');
			$pdf->Ln(5);
			$pdf->SetFont('arial','',12);
			$pdf->Cell(0,0,'',0,0,'C');
			$pdf->SetFont('arial','',8);
			
			$pdf->Rect(10, 21, 75, 23);
			$pdf->Cell(-188);
			$pdf->Cell(0,20,'�������: '.$firma.' ',0,0,'L');
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
			$pdf->Cell(0,70,'��� � ����������� ������ ��',0,0,'L');
			$pdf->SetFont('arialbd','',13);
			$pdf->Cell(-191);
			$pdf->Cell(0,80,'������� ��. '.$faktura['fbr_user'].' ',0,0,'L');
			$pdf->AddFont('arial','','arial.php');
			$pdf->SetFont('Arial','',6);
			$pdf->Cell(-191);
			$pdf->Cell(0,90,'����� �� ��������� � � ������� ���',0,0,'L');

			$pdf->AddFont('arial','','arial.php');
			$pdf->SetFont('Arial','',7);
			$pdf->Rect(134, 21, 67, 23);
			$pdf->Cell(-65);
			$pdf->Cell(0,20,'������ ��������� ����� - �����',0,0,'L');
			$pdf->Cell(-65);
			$pdf->Cell(0,30,'���: ��4030005551138',0,0,'L');
			$pdf->Cell(-65);
			$pdf->Cell(0,40,'������: 300000002136558',0,0,'L');
			$pdf->Cell(-65);
			$pdf->Cell(0,50,'�����: ����������� �����',0,0,'L');

			$pdf->Cell(-50);
			$pdf->Cell(0,75,'����� �� �������:                '.$faktura['diz_f'].'',0,0,'L');
			$pdf->Cell(-50);
			$pdf->Cell(0,85,'����� �� ������:                   '.$faktura['valuta_f'].'',0,0,'L');
			$pdf->Ln(52);
			
			$pdf->FancyTable($header,$resultData,$m_u);
			
			$pdf->Ln(3);
				
			$pdf->SetFont('Arial','',7);
			if (!EMPTY($faktura['akciza_v']) && $faktura['akciza_v']<>0){
				$pdf->Cell(80);
				$pdf->Cell(25,5,'������:',1,0,'L');
				$pdf->Cell(20,5,number_format($faktura['akciza_ed'],0),1,0,'R');
				$pdf->Cell(20,5,number_format($faktura['akciza_v'],0),1,1,'R');
			}
			$pdf->Cell(80);
			$pdf->Cell(25,5,'�������� �� ���:',1,0,'L');
			$pdf->Cell(20,5,'   ',1,0,'R');
			$pdf->Cell(20,5,number_format(abs($faktura['akciza_v']+$faktura['iznos_v']),2),1,1,'R');
			$pdf->Cell(80);
			$pdf->Cell(25,5,'��� '.$faktura['ddv_proc'].'%:',1,0,'L');
			$pdf->Cell(20,5,'   ',1,0,'R');
			$pdf->Cell(20,5,number_format(abs($faktura['ddv_v']),2),1,1,'R');
			$pdf->SetFont('Arialbd','',7);
			$pdf->Cell(80);
			$pdf->Cell(25,5,'������:',1,0,'L');
			$pdf->Cell(20,5,' ',1,0,'R');
			$pdf->Cell(20,5,number_format(abs($faktura['iznos_v']+$faktura['ddv_v']+$faktura['akciza_v']),2),1,1,'R');
				
			if (!EMPTY($faktura['prilozi_f']) && $faktura['prilozi_f']<>0){
				$pdf->Ln(10);
				$pdf->SetFont('Arialbd','',7);
				$pdf->Cell(-15);
				$pdf->Cell(20,5,'�������',0,1,'L');
				$pdf->SetFont('Arial','',7);
				$pdf->Cell(-15);
				$pdf->Cell(20,5,$faktura['prilozi_f'],0,1,'L');
			}
			
			$pdf->Ln(10);
			$pdf->SetFont('Arialbd','',7);
			$pdf->SetDrawColor(0,80,180);
			$pdf->SetFillColor(230,230,0);
			$pdf->SetTextColor(220,50,50);
			$pdf->Cell(-15);
			$pdf->Cell(20,5,'��������',0,1,'L');
			$pdf->SetFont('Arial','',7);
			$pdf->SetTextColor(0,0,0);
			$pdf->Cell(-15);
			$pdf->Cell(0,5,'�� ������ ������� �� ���������� ����� �� ������� �� �� ��������� ������ ����. �� ����� �����������',0,1,'L');
			$pdf->Cell(-15);
			$pdf->Cell(0,5,'�� �� ���������� �������� ������� ������.',0,1,'L');
			$pdf->Cell(-15);
			//$pdf->Cell(0,50,$faktura['zabeleska_f'],0,0,'L');
			
				
			
			$pdf->Ln(40);
			
			$pdf->SetFont('Arialbd','',8);
			$pdf->Cell(-15);
			//$pdf->Ln(40);
			$pdf->Cell(20,5,'�� ������ ��������� ����� - �����',0,1,'L');
			$pdf->Cell(-15);
			$pdf->Cell(20,5,'��������� ����',0,1,'L');
			$pdf->Cell(-15);
			$pdf->Cell(20,20,'__________________________________',0,0,'L');
			
			$pdf->Output();
?>