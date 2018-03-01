<?php
session_start();
include_once 'inc/functions.php';
require_once 'inc/PHPExcel.php';
require_once 'inc/PHPExcel/Writer/Excel5.php';

$handle = connectkart();
$web=connectweb();

if(isset($_POST['godina']) && !EMPTY($_POST['godina']))
{
	$godina=mysqli_real_escape_string($handle, $_POST['godina']);
}else {$godina='2014';}

if(isset($_POST['vid']) && !EMPTY($_POST['vid'])){
	$cmd_vidkonto=mysqli_query($web, "select konto,opis from vid_konto where vid=".$_POST['vid']);
	$konto=mysqli_fetch_row($cmd_vidkonto);
}

$cmp=$_SESSION['idcmp'];
$cmp_select=mysqli_query($handle, "select opis from firmi where cod=".$cmp);
$cmp_opis=mysqli_fetch_row($cmp_select);

$cmd =mysqli_query($handle, "select primary_c, if(naldoc=89 or naldoc=71,1,0) as faktura, opis, broj, nalmes, org_e, sumad, sumap, diz, datum
		from fin_exchange where korisnik =".$_SESSION['idcmp']." and godina=$godina and konto=$konto[0] order by diz, nalmes");

$vk_dolzi=0;
$vk_pobaruva=0;
$ima_nalmes=0;
$nalmes_dolzi=0;
$nalmes_pobaruva=0;

	$objPHPExcel = new PHPExcel();
	$objPHPExcel->setActiveSheetIndex(0);

	$objPHPExcel->getActiveSheet()->SetCellValue('A1', iconv('cp1251','utf-8','Клиент:  '.$cmp_opis[0]));
	$objPHPExcel->getActiveSheet()->SetCellValue('A2', iconv('cp1251','utf-8','Година: '.$godina));
	$objPHPExcel->getActiveSheet()->SetCellValue('A3', iconv('cp1251','utf-8','Вид: '.$konto[1]));

	$objPHPExcel->getActiveSheet()->SetCellValue('A5', iconv('cp1251','utf-8','Опис'));
	$objPHPExcel->getActiveSheet()->SetCellValue('B5', iconv('cp1251','utf-8','Документ'));
	$objPHPExcel->getActiveSheet()->SetCellValue('C5', iconv('cp1251','utf-8','Орг. ед'));
	$objPHPExcel->getActiveSheet()->SetCellValue('D5', iconv('cp1251','utf-8','Должи'));
	$objPHPExcel->getActiveSheet()->SetCellValue('E5', iconv('cp1251','utf-8','Побарува'));
	$objPHPExcel->getActiveSheet()->SetCellValue('F5', iconv('cp1251','utf-8','Датум'));
	$objPHPExcel->getActiveSheet()->SetCellValue('G5', iconv('cp1251','utf-8','Валута'));

	$rowCount = 7;
	while ($row = mysqli_fetch_array($cmd)) {

		if ($row['nalmes']=='00'){
			$ima_nalmes=1;
			$nalmes_dolzi=$nalmes_dolzi + $row['sumad'];
			$nalmes_pobaruva=$nalmes_pobaruva + $row['sumap'];
			$nalmes_opis=$row['opis'];
			continue;
		}
		
		if ($ima_nalmes==1){
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, iconv('cp1251','utf-8',"Почетна состојба"))->getColumnDimension('A')->setAutoSize(true);
			
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, iconv('cp1251','utf-8',"Салдо од мината година"))->getColumnDimension('B')->setAutoSize(true);
			
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, '');
	
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $nalmes_dolzi)->getColumnDimension('D')->setAutoSize(true);
			
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $nalmes_pobaruva)->getColumnDimension('E')->setAutoSize(true);
			
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, '2014-01-01')->getColumnDimension('F')->setAutoSize(true);
	
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, '2014-01-01')->getColumnDimension('G')->setAutoSize(true);
			
			$rowCount++;
			$ima_nalmes=0;
		}

    if (isset($row['opis'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, iconv('cp1251','utf-8',$row['opis']))->getColumnDimension('A')->setAutoSize(true);
    }
    if (isset($row['broj'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $row['broj'])->getColumnDimension('B')->setAutoSize(true);
    }
	if (isset($row['org_e'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $row['org_e']);
	}
	if (isset($row['sumad'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $row['sumad'])->getColumnDimension('D')->setAutoSize(true);
	}

	if (isset($row['sumap'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $row['sumap'])->getColumnDimension('E')->setAutoSize(true);
	}

	if (isset($row['diz'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $row['diz'])->getColumnDimension('F')->setAutoSize(true);
	}
	if (isset($row['datum'])){
		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $row['datum'])->getColumnDimension('G')->setAutoSize(true);
	}

		$rowCount++;

		$vk_dolzi=$vk_dolzi + $row['sumad'];
		$vk_pobaruva=$vk_pobaruva + $row['sumap'];
	}
	
	$vk_dolzi= $vk_dolzi + $nalmes_dolzi;
	$vk_pobaruva=$vk_pobaruva + $nalmes_pobaruva;
	
	$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, iconv('cp1251','utf-8',"Вкупно ДОЛЖИ/ПОБАРУВА"))->getColumnDimension('A')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, iconv('cp1251','utf-8'," "))->getColumnDimension('B')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, '');
	$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $vk_dolzi)->getColumnDimension('D')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $vk_pobaruva)->getColumnDimension('E')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, ' ')->getColumnDimension('F')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, ' ')->getColumnDimension('G')->setAutoSize(true);
	$rowCount++;

	if ($vk_dolzi>$vk_pobaruva){
	
		$vk=$vk_dolzi- $vk_pobaruva;
	
		$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, iconv('cp1251','utf-8',"САЛДО"))->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, iconv('cp1251','utf-8'," "))->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, '');
		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $vk)->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, ' ')->getColumnDimension('E')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, ' ')->getColumnDimension('F')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, ' ')->getColumnDimension('G')->setAutoSize(true);
	
	}
	elseif ($vk_dolzi<$vk_pobaruva){
	
		$vk=$vk_pobaruva-$vk_dolzi;
	
		$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, iconv('cp1251','utf-8',"САЛДО"))->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, iconv('cp1251','utf-8'," "))->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, '');
		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, ' ' )->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $vk)->getColumnDimension('E')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, ' ')->getColumnDimension('F')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, ' ')->getColumnDimension('G')->setAutoSize(true);

	}
	else{
		$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, iconv('cp1251','utf-8',"САЛДО"))->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, iconv('cp1251','utf-8'," "))->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, '');
		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, '0.00' )->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, '0.00')->getColumnDimension('E')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, ' ')->getColumnDimension('F')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, ' ')->getColumnDimension('G')->setAutoSize(true);
	}
	
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Finansiska_kartica.xls"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
?>


















<?php
// session_start();
// include_once 'inc/functions.php';
// require_once 'inc/PHPExcel.php';
// require_once 'inc/PHPExcel/Writer/Excel5.php';

// $handle = connectkart();
// $web=connectweb();

// if(isset($_POST['godina']) && !EMPTY($_POST['godina']))
// {
// 	$godina=mysqli_real_escape_string($handle, $_POST['godina']);
// }else {$godina='2014';}

// if(isset($_POST['vid']) && !EMPTY($_POST['vid'])){
// 	$cmd_vidkonto=mysqli_query($web, "select konto,opis from vid_konto where vid=".$_POST['vid']);
// 	$konto=mysqli_fetch_row($cmd_vidkonto);
// }

// $cmp=$_SESSION['idcmp'];
// $cmp_select=mysqli_query($handle, "select opis from firmi where cod=".$cmp);
// $cmp_opis=mysqli_fetch_row($cmp_select);

// $cmd1=mysqli_query($handle, "select primary_c, if(naldoc=89 or naldoc=71,1,0) as faktura, opis, broj, org_e, sumad, sumap, diz, datum, s_k
// 		from fin_exchange where s_k=0 and korisnik =".$_SESSION['idcmp']." and godina=$godina and konto=$konto[0] order by diz desc");


// $cmd2=mysqli_query($handle, "select primary_c, if(naldoc=89 or naldoc=71,1,0) as faktura, nalmes, opis, broj, org_e, sumad, sumap, diz, datum, s_k
// 		from fin_exchange where s_k=1 and korisnik =".$_SESSION['idcmp']." and godina=$godina and konto=$konto[0] order by diz desc");


// $cmd3=mysqli_query($handle, "select primary_c, if(naldoc=89 or naldoc=71,1,0) as faktura, nalmes, opis, broj, org_e, sumad, sumap, diz, datum, s_k
// 		from fin_exchange where s_k=1 and nalmes='00' and korisnik =".$_SESSION['idcmp']." and godina=$godina and konto=$konto[0] order by diz desc");


// $vk_dolzi=0;
// $vk_pobaruva=0;
// $vk_dolzi1=0;
// $vk_pobaruva1=0;
// $vk_dolzi2=0;
// $vk_pobaruva2=0;
// $nalmes_dolzi=0;
// $nalmes_pobaruva=0;


// $objPHPExcel = new PHPExcel();
// $objPHPExcel->setActiveSheetIndex(0);

// 	$objPHPExcel->getActiveSheet()->SetCellValue('A1', iconv('cp1251','utf-8','Клиент:  '.$cmp_opis[0]));
// 	$objPHPExcel->getActiveSheet()->SetCellValue('A2', iconv('cp1251','utf-8','Година: '.$godina));
// 	$objPHPExcel->getActiveSheet()->SetCellValue('A3', iconv('cp1251','utf-8','Вид: '.$konto[1]));

// 	$objPHPExcel->getActiveSheet()->SetCellValue('A6', iconv('cp1251','utf-8','Опис'));
// 	$objPHPExcel->getActiveSheet()->SetCellValue('B6', iconv('cp1251','utf-8','Документ'));
// 	$objPHPExcel->getActiveSheet()->SetCellValue('C6', iconv('cp1251','utf-8','Орг. ед'));
// 	$objPHPExcel->getActiveSheet()->SetCellValue('D6', iconv('cp1251','utf-8','Должи'));
// 	$objPHPExcel->getActiveSheet()->SetCellValue('E6', iconv('cp1251','utf-8','Побарува'));
// 	$objPHPExcel->getActiveSheet()->SetCellValue('F6', iconv('cp1251','utf-8','Датум'));
// 	$objPHPExcel->getActiveSheet()->SetCellValue('G6', iconv('cp1251','utf-8','Валута'));

	
// 	$rowCount = 8;
// 	while ($row = mysqli_fetch_array($cmd1)) {
	
// 		//$row = mysqli_fetch_array($cmd1);
		
//     if (isset($row['opis'])){
// 		$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, iconv('cp1251','utf-8',$row['opis']))->getColumnDimension('A')->setAutoSize(true);
//     }	
//     if (isset($row['broj'])){
// 		$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $row['broj'])->getColumnDimension('B')->setAutoSize(true);
//     }
// 	if (isset($row['org_e'])){
// 		$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $row['org_e']);
// 	}
// 	if (isset($row['sumad'])){
// 		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $row['sumad'])->getColumnDimension('D')->setAutoSize(true);
// 	}

// 	if (isset($row['sumap'])){
// 		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $row['sumap'])->getColumnDimension('E')->setAutoSize(true);
// 	}
	
// 	if (isset($row['diz'])){
// 		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $row['diz'])->getColumnDimension('F')->setAutoSize(true);
// 	}
// 	if (isset($row['datum'])){
// 		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $row['datum'])->getColumnDimension('G')->setAutoSize(true);
// 	}
	
// 		$rowCount++;
		
// 		$vk_dolzi1=$vk_dolzi1 + $row['sumad'];
// 		$vk_pobaruva1=$vk_pobaruva1 + $row['sumap'];
// 	}
	
	
	
// 	while ($row=mysqli_fetch_array($cmd3)){
// 		$ima3=$row['primary_c'];
// 		$nalmes_dolzi=$nalmes_dolzi + $row['sumad'];
// 		$nalmes_pobaruva=$nalmes_pobaruva + $row['sumap'];
// 		$nalmes_opis=$row['opis'];
// 	}
	
// 	if (!EMPTY($ima3)){

// 		$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, iconv('cp1251','utf-8',"Почетна состојба"))->getColumnDimension('A')->setAutoSize(true);
		
// 		$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, iconv('cp1251','utf-8',"Салдо од мината година"))->getColumnDimension('B')->setAutoSize(true);
		
// 		$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, '');

// 		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $nalmes_dolzi)->getColumnDimension('D')->setAutoSize(true);
		
// 		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $nalmes_pobaruva)->getColumnDimension('E')->setAutoSize(true);
		
// 		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, '2014-01-01')->getColumnDimension('F')->setAutoSize(true);

// 		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, '2014-01-01')->getColumnDimension('G')->setAutoSize(true);
		
// 		$rowCount++;
// 	}
	
	
// 	while ($row = mysqli_fetch_array($cmd2)) {
		
// 		if ($row['nalmes']=='00'){
// 			continue;
// 		}
	
// 		//$row = mysqli_fetch_array($cmd2);
	
// 		if (isset($row['opis'])){
// 			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, iconv('cp1251','utf-8',$row['opis']))->getColumnDimension('A')->setAutoSize(true);
// 		}
// 		if (isset($row['broj'])){
// 			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $row['broj'])->getColumnDimension('B')->setAutoSize(true);
// 		}
// 		if (isset($row['org_e'])){
// 			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $row['org_e']);
// 		}
	
// 		if (isset($row['sumad'])){
// 			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $row['sumad'])->getColumnDimension('D')->setAutoSize(true);
// 		}
	
// 		if (isset($row['sumap'])){
// 			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $row['sumap'])->getColumnDimension('E')->setAutoSize(true);
// 		}
	
// 		if (isset($row['diz'])){
// 			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $row['diz'])->getColumnDimension('F')->setAutoSize(true);
// 		}
// 		if (isset($row['datum'])){
// 			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $row['datum'])->getColumnDimension('G')->setAutoSize(true);
// 		}
	
// 		$vk_dolzi2=$vk_dolzi2 + $row['sumad'];
// 		$vk_pobaruva2=$vk_pobaruva2 + $row['sumap'];
		
// 		$rowCount++;
// 	}
	
// 	$vk_dolzi= $vk_dolzi1 + $vk_dolzi2 + $nalmes_dolzi;
// 	$vk_pobaruva=$vk_pobaruva1 + $vk_pobaruva2 + $nalmes_pobaruva;
	
	
// 	$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, iconv('cp1251','utf-8',"Вкупно ДОЛЖИ/ПОБАРУВА"))->getColumnDimension('A')->setAutoSize(true);		
// 	$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, iconv('cp1251','utf-8'," "))->getColumnDimension('B')->setAutoSize(true);		
// 	$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, '');
// 	$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $vk_dolzi)->getColumnDimension('D')->setAutoSize(true);		
// 	$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $vk_pobaruva)->getColumnDimension('E')->setAutoSize(true);		
// 	$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, ' ')->getColumnDimension('F')->setAutoSize(true);
// 	$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, ' ')->getColumnDimension('G')->setAutoSize(true);
// 	$rowCount++;
	
// 	if ($vk_dolzi>$vk_pobaruva){
			
// 		$vk=$vk_dolzi- $vk_pobaruva;
			
// 		$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, iconv('cp1251','utf-8',"САЛДО"))->getColumnDimension('A')->setAutoSize(true);		
// 		$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, iconv('cp1251','utf-8'," "))->getColumnDimension('B')->setAutoSize(true);		
// 		$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, '');
// 		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $vk)->getColumnDimension('D')->setAutoSize(true);		
// 		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, ' ')->getColumnDimension('E')->setAutoSize(true);		
// 		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, ' ')->getColumnDimension('F')->setAutoSize(true);
// 		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, ' ')->getColumnDimension('G')->setAutoSize(true);
			
// 	}
// 	elseif ($vk_dolzi<$vk_pobaruva){
			
// 		$vk=$vk_pobaruva-$vk_dolzi;
			
// 		$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, iconv('cp1251','utf-8',"САЛДО"))->getColumnDimension('A')->setAutoSize(true);		
// 		$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, iconv('cp1251','utf-8'," "))->getColumnDimension('B')->setAutoSize(true);		
// 		$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, '');
// 		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, ' ' )->getColumnDimension('D')->setAutoSize(true);		
// 		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $vk)->getColumnDimension('E')->setAutoSize(true);		
// 		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, ' ')->getColumnDimension('F')->setAutoSize(true);
// 		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, ' ')->getColumnDimension('G')->setAutoSize(true);
		
// 	}
// 	else{
// 		$objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, iconv('cp1251','utf-8',"САЛДО"))->getColumnDimension('A')->setAutoSize(true);		
// 		$objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, iconv('cp1251','utf-8'," "))->getColumnDimension('B')->setAutoSize(true);		
// 		$objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, '');
// 		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, '0.00' )->getColumnDimension('D')->setAutoSize(true);		
// 		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, '0.00')->getColumnDimension('E')->setAutoSize(true);		
// 		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, ' ')->getColumnDimension('F')->setAutoSize(true);
// 		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, ' ')->getColumnDimension('G')->setAutoSize(true);
// 	}
	


// header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
// header('Content-Disposition: attachment;filename="Finansiska_kartica.xls"');
// header('Cache-Control: max-age=0');

// $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
// $objWriter->save('php://output');

// ?>