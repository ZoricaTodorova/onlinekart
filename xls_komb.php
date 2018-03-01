<?php
session_start();
include_once 'inc/functions.php';
require_once 'inc/PHPExcel.php';
require_once 'inc/PHPExcel/Writer/Excel5.php';

$ini = new Configini("flpt");
$bazite=$_SESSION['fin_baza'];
$x=0;
foreach($bazite as $bazi){
	$cmd[$x] = "SELECT '".$bazi."' as DB,".$_SESSION['query'];	
	$x++;
}

//$result=mysqli_query($handle, $cmd);

$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Firma');
$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Konto');
$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Dolzi');
$objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Pobaruva');
$objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Saldo dolzi');
$objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Saldo pobaruva');
	
$rowCount = 3;
for ($i=0;$i<count($_SESSION['fin_baza']);$i++){
	$handle=anyconnect($_SESSION['fin_baza'][$i]);
	$result=mysqli_query($handle, $cmd[$i]);
	for($a=0;$a<mysqli_num_rows($result)-1;$a++) {
		$row = mysqli_fetch_array($result);
		if ($_SESSION['totfin']==1){
			if (!is_null($row['KONTO'])){
				continue;
			}
		}
		$handle1=connectweb();
		$rez=mysqli_query($handle1, "SELECT dsc from cmp where id='".$row['DB']."' ");
		$dbid=mysqli_fetch_row($rez);
		$dbname=cp_1251_zont($dbid[0]);
    
    	$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $dbname);
    	$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $row['KONTO']);
    	$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $row['DOLZI']);
    	$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $row['POBARUVA']);
    	$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $row['SD']);
    	$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $row['SP']);
    	$rowCount++;
	}
}

$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Vkupno');
$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, ' ');
$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $_SESSION['sumad']);
$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $_SESSION['sumap']);
$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $_SESSION['sumasd']);
$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $_SESSION['sumasp']);


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="finansii_excel.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');

?>