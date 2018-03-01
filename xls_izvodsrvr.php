<?php
session_start();
include_once 'inc/functions.php';
require_once 'inc/PHPExcel.php';
require_once 'inc/PHPExcel/Writer/Excel5.php';

$handle = connectkart();

if (isset($_POST['bss']) && isset($_POST['karts']) && isset($_POST['oddat']) && isset($_POST['dodat'])){
	$oddat=$_POST['oddat'];
	$dodat=$_POST['dodat'];
	$bss=$_POST['bss'];
	$karts=$_POST['karts'];
	
	$datumod=substr($oddat,0,4).'-'.substr($oddat,4,2).'-'.substr($oddat,6,2);
	$datumdo=substr($dodat,0,4).'-'.substr($dodat,4,2).'-'.substr($dodat,6,2);
}

$cmp=$_SESSION['idcmp'];
$cmp_select=mysqli_query($handle, "select opis from firmi where cod=".$cmp);
$cmp_opis=mysqli_fetch_row($cmp_select);

	$cmd=mysqli_query($handle,"SELECT minkilo, maxkilo, lkk_broj, avtomobil, artikl, (maxkilo-minkilo) as izminati, (kolicina-kol) as finalkol, ((maxkilo-minkilo)/(kolicina-kol)) as prosek, oddat, dodat FROM 

			(select kol, minkilo, maxkilo, lkk_broj, avtomobil, artikl, artgrup, kolicina, oddat, dodat
			 from (SELECT lkk_broj, kartici.reg_br as avtomobil, materijali.opis as artikl, mat as artgrup, sum(kolicina) as kolicina, 
			 min(t_dt) as oddat, max(t_dt) as dodat
			from bs_exchange 
			left join kartici on kart_br = lkk_broj and bs_exchange.firma=kartici.firma 
			left join materijali on materijali.cod = bs_exchange.mat and materijali.godina = bs_exchange.godina 
			where t_dt between '$oddat' and '$dodat' and materijali.tip='D' and 
			lkk_broj in ($karts) and 
			bs in ($bss) 
			group by avtomobil, artgrup order by t_dt) as aa 
			
			LEFT join
			
			(SELECT t_dt,kilometri as minkilo, kartici.reg_br as car, mat as grupa from bs_exchange 
			left join kartici on kart_br = lkk_broj and bs_exchange.firma=kartici.firma 
			left join materijali on materijali.cod = bs_exchange.mat and materijali.godina = bs_exchange.godina 
			where t_dt between '$oddat' and '$dodat' and materijali.tip='D' and 
			lkk_broj in ($karts) and 
			bs in ($bss) ) as bb
			on aa.oddat=bb.t_dt and aa.artgrup=bb.grupa and aa.avtomobil=bb.car
			
			LEFT join
			
			(SELECT t_dt,kilometri as maxkilo, kartici.reg_br as car, mat as grupa from bs_exchange 
			left join kartici on kart_br = lkk_broj and bs_exchange.firma=kartici.firma 
			left join materijali on materijali.cod = bs_exchange.mat and materijali.godina = bs_exchange.godina 
			where t_dt between '$oddat' and '$dodat' and materijali.tip='D' and 
			lkk_broj in ($karts) and 
			bs in ($bss) ) as cc
			on aa.dodat=cc.t_dt and aa.artgrup=cc.grupa and aa.avtomobil=cc.car
			
			LEFT JOIN
			
			(SELECT t_dt,kolicina as kol, kartici.reg_br as car, mat as grupa from bs_exchange 
			left join kartici on kart_br = lkk_broj and bs_exchange.firma=kartici.firma 
			left join materijali on materijali.cod = bs_exchange.mat and materijali.godina = bs_exchange.godina 
			where t_dt between '$oddat' and '$dodat' and materijali.tip='D' and 
			lkk_broj in ($karts) and 
			bs in ($bss) ) as dd
			on aa.dodat=dd.t_dt and aa.artgrup=dd.grupa and aa.avtomobil=dd.car) as FF");


$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->SetCellValue('A1', iconv('cp1251','utf-8','Клиент:  '.$cmp_opis[0]));
$objPHPExcel->getActiveSheet()->SetCellValue('A2', iconv('cp1251','utf-8','Објекти: '.$bss));
$objPHPExcel->getActiveSheet()->SetCellValue('A3', iconv('cp1251','utf-8','Картички: '.$karts));
$objPHPExcel->getActiveSheet()->SetCellValue('A4', iconv('cp1251','utf-8','За датум од: '.$datumod.'  до: '.$datumdo));

$objPHPExcel->getActiveSheet()->SetCellValue('A6', iconv('cp1251','utf-8','Карта'));
$objPHPExcel->getActiveSheet()->SetCellValue('B6', iconv('cp1251','utf-8','Регистрација'));
$objPHPExcel->getActiveSheet()->SetCellValue('C6', iconv('cp1251','utf-8','Вид гориво'));
$objPHPExcel->getActiveSheet()->SetCellValue('D6', iconv('cp1251','utf-8','Датум на прво точење'));
$objPHPExcel->getActiveSheet()->SetCellValue('E6', iconv('cp1251','utf-8','Километри'));
$objPHPExcel->getActiveSheet()->SetCellValue('F6', iconv('cp1251','utf-8','Датум на последно точење'));
$objPHPExcel->getActiveSheet()->SetCellValue('G6', iconv('cp1251','utf-8','Километри'));
$objPHPExcel->getActiveSheet()->SetCellValue('H6', iconv('cp1251','utf-8','Изминати километри'));
$objPHPExcel->getActiveSheet()->SetCellValue('I6', iconv('cp1251','utf-8','Количество'));
$objPHPExcel->getActiveSheet()->SetCellValue('J6', iconv('cp1251','utf-8','Просечна потрошувачка'));


$rowCount = 8;
while ($row = mysqli_fetch_array($cmd)) {

	if (isset($row['lkk_broj'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $row['lkk_broj']);
	}
	
	$objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getNumberFormat()->setFormatCode('00000000');
	
	if (isset($row['avtomobil'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $row['avtomobil'])->getColumnDimension('B')->setAutoSize(true);
	}
	
	if (isset($row['artikl'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, iconv('cp1251','utf-8',$row['artikl']))->getColumnDimension('C')->setAutoSize(true);
	}
	
	if (isset($row['oddat'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $row['oddat'])->getColumnDimension('D')->setAutoSize(true);
	}

	if (isset($row['minkilo'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $row['minkilo'])->getColumnDimension('E')->setAutoSize(true);
	}

	if (isset($row['dodat'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $row['dodat'])->getColumnDimension('F')->setAutoSize(true);
	}
	
	if (isset($row['maxkilo'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $row['maxkilo'])->getColumnDimension('G')->setAutoSize(true);
	}
	
	if (isset($row['izminati'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $row['izminati'])->getColumnDimension('H')->setAutoSize(true);
	}
	
	if (isset($row['finalkol'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $row['finalkol'])->getColumnDimension('I')->setAutoSize(true);
	}
	
	if (isset($row['prosek'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $row['prosek'])->getColumnDimension('J')->setAutoSize(true);
	}

	$rowCount++;

}


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Izvestaj_za_prodazba_srednavrednost.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');

?>