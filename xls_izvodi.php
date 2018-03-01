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
}

$cmp=$_SESSION['idcmp'];
$cmp_select=mysqli_query($handle, "select opis from firmi where cod=".$cmp);
$cmp_opis=mysqli_fetch_row($cmp_select);


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
			bs_exchange.cena as ed_cena, 
			kolicina, 
			iznos as vrednost, 
			bs as stanica,
			kilometri as kilometraza,
			firmi.cod as klientcod from bs_exchange
	left join kartici on kart_br = lkk_broj and bs_exchange.firma=kartici.firma
	left join firmi on bs_exchange.firma = firmi.cod
	left join materijali on materijali.cod = bs_exchange.mat and materijali.godina = bs_exchange.godina 
	where t_dt between '$oddat' and '$dodat' and 
	lkk_broj in ($karts) and bs in ($bss) and mat <> ''
	order by klientcod, karta, datum_cas;";
	$res=mysqli_query($handle, $cmd) or die(mysqli_error($handle));
	//echo $cmd;
	$cmd = "select 'bbb' as trclass,
			1 as bold, 
			concat('Вкупно за артикл:', mat) as klient, 
			lkk_broj as kardno, 
			mat as artgrup,  
			materijali.opis as artikl,
			sum(iznos) as vrednost from bs_exchange 
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
			sum(iznos) as vrednost from bs_exchange
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
			sum(iznos) as vrednost from bs_exchange
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
	//print_r($result);

	$row = $result;
	
	$datumod=substr($oddat,0,4).'-'.substr($oddat,4,2).'-'.substr($oddat,6,2);
	$datumdo=substr($dodat,0,4).'-'.substr($dodat,4,2).'-'.substr($dodat,6,2);
	
	
//$result=mysqli_query($handle, $cmd);

$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);

	$objPHPExcel->getActiveSheet()->SetCellValue('A1', iconv('cp1251','utf-8','Клиент:  '.$cmp_opis[0]));
	$objPHPExcel->getActiveSheet()->SetCellValue('A2', iconv('cp1251','utf-8','Објекти: '.$bss));
	$objPHPExcel->getActiveSheet()->SetCellValue('A3', iconv('cp1251','utf-8','Картички: '.$karts));
	$objPHPExcel->getActiveSheet()->SetCellValue('A4', iconv('cp1251','utf-8','За датум од: '.$datumod.'  до: '.$datumdo));

	$objPHPExcel->getActiveSheet()->SetCellValue('A6', iconv('cp1251','utf-8','Клиент'));
	$objPHPExcel->getActiveSheet()->SetCellValue('B6', iconv('cp1251','utf-8','Корисник'));
	$objPHPExcel->getActiveSheet()->SetCellValue('C6', iconv('cp1251','utf-8','Карта'));
	$objPHPExcel->getActiveSheet()->SetCellValue('D6', iconv('cp1251','utf-8','Датум/час'));
	$objPHPExcel->getActiveSheet()->SetCellValue('E6', iconv('cp1251','utf-8','Регистрација'));
	$objPHPExcel->getActiveSheet()->SetCellValue('F6', iconv('cp1251','utf-8','Сметка'));
	$objPHPExcel->getActiveSheet()->SetCellValue('G6', iconv('cp1251','utf-8','Артикл'));
	$objPHPExcel->getActiveSheet()->SetCellValue('H6', iconv('cp1251','utf-8','Ед.цена'));
	$objPHPExcel->getActiveSheet()->SetCellValue('I6', iconv('cp1251','utf-8','Количина'));
	$objPHPExcel->getActiveSheet()->SetCellValue('J6', iconv('cp1251','utf-8','Вредност'));
	$objPHPExcel->getActiveSheet()->SetCellValue('K6', iconv('cp1251','utf-8','Станица'));
	$objPHPExcel->getActiveSheet()->SetCellValue('L6', iconv('cp1251','utf-8','км'));
	$objPHPExcel->getActiveSheet()->SetCellValue('M6', iconv('cp1251','utf-8','САП'));

// 	print_r($row);
// 	echo count($row);
// 	exit();
	
	$rowCount = 8;
	for ($i=0;$i<count($row);$i++) {
		
	if (isset($row[$i]['stanica'])){
		$stanica_selekt=mysqli_query($handle, "SELECT opis from org_e where cod=".$row[$i]['stanica']);
		$stanica=mysqli_fetch_row($stanica_selekt);	
	}
	
// 		$cmd_artikl=mysqli_query($handle, "Select opis from materijali where cod=".$row[$i]['artgrup']);
// 		$artikl=mysqli_fetch_row($cmd_artikl);
    if (isset($row[$i]['klient'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, iconv('cp1251','utf-8',$row[$i]['klient']));
    }	
    if (isset($row[$i]['korisnik'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $row[$i]['korisnik'])->getColumnDimension('B')->setAutoSize(true);
    }
	if (isset($row[$i]['karta'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $row[$i]['karta']);
	}

		$objPHPExcel->getActiveSheet()->getStyle('C'.$rowCount)->getNumberFormat()->setFormatCode('00000000');

	if (isset($row[$i]['datum_cas'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $row[$i]['datum_cas'])->getColumnDimension('D')->setAutoSize(true);
	}
	if (isset($row[$i]['avtomobil'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $row[$i]['avtomobil'])->getColumnDimension('E')->setAutoSize(true);
	}

		$objPHPExcel->getActiveSheet()->getStyle('F'.$rowCount)->getNumberFormat()->setFormatCode('0000000000');

	if (isset($row[$i]['smetka'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $row[$i]['smetka'])->getColumnDimension('F')->setAutoSize(true);
	}
	if (isset($row[$i]['artikl'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, iconv('CP1251', 'UTF-8', $row[$i]['artikl']))->getColumnDimension('G')->setAutoSize(true);
	}
	if (isset($row[$i]['ed_cena'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $row[$i]['ed_cena']);
	}
	if (isset($row[$i]['kolicina'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $row[$i]['kolicina']);
	}
	if (isset($row[$i]['vrednost'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $row[$i]['vrednost']);
	}
	if (isset($row[$i]['stanica'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, iconv('cp1251','utf-8',$stanica[0]))->getColumnDimension('K')->setAutoSize(true);
	}		
	if (isset($row[$i]['kilometraza'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, $row[$i]['kilometraza']);
	}
	if (isset($row[$i]['sap'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('M'.$rowCount, $row[$i]['sap']);
	}
	
		$rowCount++;
	}


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Izvestaj_za_prodazba.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');

?>