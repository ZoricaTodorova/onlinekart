<?php
require('fpdf17/fpdf.php');
if (isset($_POST['bss']) && isset($_POST['karts']) && isset($_POST['oddat']) && isset($_POST['dodat'])){
	$oddat=$_POST['oddat'];
	$dodat=$_POST['dodat'];
	$bss=$_POST['bss'];
	$karts=$_POST['karts'];
}
$cmd = "select cmp.dsc as klient,
' ' as korisnik,
kardno as karta,
dttrans as datum_cas,
reg as avtomobil,
nosmet as smetka,
art as artikl,
edcen as ed_cena,
kol as kolicina,
vkcen as vrednost,
id_obj as stanica,
km as kilometraza from olkitm
left join kart on id_kart = kart.id
left join cmp on id_cmp = cmp.id
where dttrans between '$oddat' and '$dodat' and
id_kart in ($karts) and id_obj in ($bss);";

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
			$this->SetFont('','B');
			$this->SetFontSize(8);
			//Header
			$w=array(25,12,17,28,17,13,13,13,13,13,13,13); //tuka se kazuva kolku i kolkavi koloni imame
			for($i=0;$i<count($header);$i++)
				$this->Cell($w[$i],6,$header[$i],1,0,'C',true);
			$this->Ln();
			//Color and font restoration
			$this->SetFillColor(224,235,255);
			$this->SetTextColor(0);
			$this->SetFont('Arial');
			$this->SetFontSize(8);
			//Data
			$fill=false;
			foreach($data as $row)
				{ //tuka gi pravime kolonite
				$this->Cell($w[0],6,$row[0],'LR',0,'C',$fill);
				$this->Cell($w[1],6,$row[1],'LR',0,'C',$fill);
				$this->Cell($w[2],6,$row[2],'LR',0,'C',$fill);
				$this->Cell($w[3],6,$row[3],'LR',0,'C',$fill);
				$this->Cell($w[4],6,$row[4],'LR',0,'C',$fill);
				$this->Cell($w[5],6,$row[5],'LR',0,'C',$fill);
				$this->Cell($w[6],6,$row[6],'LR',0,'C',$fill);
				$this->Cell($w[7],6,$row[7],'LR',0,'C',$fill);
				$this->Cell($w[8],6,$row[8],'LR',0,'C',$fill);
				$this->Cell($w[9],6,$row[9],'LR',0,'C',$fill);
				$this->Cell($w[10],6,$row[10],'LR',0,'C',$fill);
				$this->Cell($w[11],6,$row[11],'LR',0,'C',$fill);
				$this->Ln();
				$fill=!$fill;
				}
		$this->Cell(array_sum($w),0,'','T');
	}
}
		$pdf=new PDF();
		//Column titles
		$header=array('Klient','Korisnik','Karta','Datum/cas','Registracija','Smetka','Artikl','Ed.cena','Kolicina','Vrednost','Stanica','km');
		//Data loading
		
		//*** Load MySQL Data ***//
		$objConnect = mysql_connect("10.0.0.131:13306","root","evreca") or die("Error Connect to Database");
		$objDB = mysql_select_db("onlinekart");
		//$strSQL = "SELECT id_kart,dttrans,reg,nosmet,art,edcen,kol,vkcen,id_obj,km FROM olkitm";
		$objQuery = mysql_query($cmd);
		$resultData = array();
		for ($i=0;$i<mysql_num_rows($objQuery);$i++) {
			$result = mysql_fetch_array($objQuery);
			array_push($resultData,$result);
			}
			$pdf->SetFont('Arial','',8);
			$pdf->AddPage();
			$pdf->Image('img/logo.png',80,8,33);
			$pdf->Ln(35);
		    $pdf->FancyTable($header,$resultData);
			
			$pdf->Output();
?>