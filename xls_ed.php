<?php
session_start();
include_once 'inc/functions.php';
require_once 'inc/PHPExcel.php';
require_once 'inc/PHPExcel/Writer/Excel5.php';

$handle = anyconnect($_SESSION['fin_baza'][0]);

$ini = new Configini("flpt");
$cmd=$_SESSION['selekt'];

$result=mysqli_query($handle, $cmd);

$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);

if ($_SESSION['klik']=='Vkupno'){
	$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Konto');
	$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Korisnik');
	$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Dolzi');
	$objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Pobaruva');
	$objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Saldo dolzi');
	$objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Saldo pobaruva');

	$rowCount = 3;
	for ($i=0;$i<mysqli_num_rows($result)-1;$i++) {
		$row = mysqli_fetch_array($result);
		print_r($row);
		exit();
		if ($_SESSION['totfin']==1){
			if (!is_null($row['KONTO'])){
				continue;
			}
		}
    	$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $row['KONTO']);
    
    	$rez=mysqli_query($handle, "SELECT LEFT(opis_a,18) from firmi where cod='".$row['KORISNIK']."' ");
    	$korisnik=mysqli_fetch_row($rez);
    
    	$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $korisnik[0]);
    	$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $row['DOLZI']);
    	$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $row['POBARUVA']);
    	$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $row['SD']);
    	$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $row['SP']);
    	$rowCount++;
	}
}
elseif ($_SESSION['klik']=='Vkupno_saldo'){
	$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Konto');
	$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Korisnik');
	$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Pr. Saldo dolzi');
	$objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Pr. Saldo pobaruva');
	$objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Dolzi');
	$objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Pobaruva');
	$objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Saldo dolzi');
	$objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Saldo pobaruva');
	
	$rowCount = 3;
	for ($i=0;$i<mysqli_num_rows($result)-1;$i++) {
		$row = mysqli_fetch_array($result);
		if ($_SESSION['totfin']==1){
			if (!is_null($row['KONTO'])){
				continue;
			}
		}
		$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $row['KONTO']);
	
		$rez=mysqli_query($handle, "SELECT LEFT(opis_a,18) from firmi where cod='".$row['KORISNIK']."' ");
		$korisnik=mysqli_fetch_row($rez);
	
		$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $korisnik[0]);
		$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $row['PREDD']);
		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $row['PREDP']);
		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $row['POSLED']);
		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $row['POSLEP']);
		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $row['PREDSD']);
		$objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $row['PREDSP']);
		$rowCount++;
	}
}
elseif ($_SESSION['klik']=='Analitika'){
	if ($_SESSION['totfin']==1){
		$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Br. na dokument');
		$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Dolzi');
		$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Pobaruva');
		$objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Saldo dolzi');
		$objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Saldo pobaruva');
	
		$rowCount = 3;
		for ($i=0;$i<mysqli_num_rows($result)-1;$i++) {
			$row = mysqli_fetch_array($result);
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $row['BROJ']);
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $row['D']);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $row['P']);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $row['SD']);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $row['SP']);
			$rowCount++;
		}
	}
	else{
		$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Datum');
		$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Valuta');
		$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Br. na dokument');
		$objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Dolzi');
		$objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Pobaruva');
		$objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Saldo dolzi');
		$objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Saldo pobaruva');
		
		$rowCount = 3;
		for ($i=0;$i<mysqli_num_rows($result)-1;$i++) {
			$row = mysqli_fetch_array($result);
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $row['DIZ']);
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $row['DATUM']);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $row['BROJ']);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $row['D']);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $row['P']);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $row['SD']);
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $row['SP']);
			$rowCount++;
		}
	}
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="finansii_excel.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');

?>