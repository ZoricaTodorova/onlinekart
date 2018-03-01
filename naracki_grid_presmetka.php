<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/jquery-2.0.3.min.js"></script>          
<script src="js/functions.js"></script>
<script type="text/javascript" src="js/jquery.js"></script>
<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />
<?php
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!logged()) redirect('index.php?rx=izbor_naracki');
if (nalog()==0){
	echo ("<SCRIPT LANGUAGE='JavaScript'>
			window.alert('Немате дозвола за пристап.')
			window.location.href='index.php'
			</SCRIPT>");
}
if (nalog()==1 && !isadmin_nalozi()){
	$komercijalist=true;
}
genmenu();
echo "<h4 align=center>Испрати нарачка</h4>";

$handle=connectwebnal();
$god=date("Y");
$boja=$_SESSION['boja'];
echo "<body bgcolor='$boja'>";

if (isset($_POST['glava_id']) && !EMPTY($_POST['glava_id'])){
	$glava_id=$_POST['glava_id'];
	$glava_id=mysqli_real_escape_string($handle,$glava_id);
	
	//echo "<pre>";
	//print_r($_POST); 
	//exit; 
	$broj_var=count($_POST);
	$var=($broj_var-2)/5;
	
	 for ($i=1;$i<=$var;$i++){
	 	
	 	$kol=$_POST['kol'.$i];
	 	$kol=mysqli_real_escape_string($handle,$kol);
	 	
	 	IF (EMPTY($kol)){
	 		continue;
	 	}
	
		$mat_arr=explode(' - ',$_POST['mat'.$i]);
		$mat=$mat_arr[0];
		$mat=mysqli_real_escape_string($handle, $mat);
		
		$select=mysqli_query($handle, " select sum(kol_v-kol_i) from
				(SELECT SUM(Ymana.kolicina) as kol_v, 000000000.000 as kol_i
				FROM Ymana  LEFT JOIN tbrojce  ON Ymana.brojce_v = tbrojce.brojce
				WHERE Ymana.godina = '$god'
				AND tbrojce.godina = '$god'
				AND Ymana.status='2'
				AND Ymana.m_u= 'M'
				AND Ymana.fe_v  = 'E'
				AND Ymana.cod_v = '02-1'
				AND tbrojce.fe  = 'E'
				AND tbrojce.cod = '02-1'
				AND Ymana.mat='$mat'
				AND IF(Ymana.codr='', 1, obrabotka <> 0)
				GROUP BY Ymana.mat
				UNION ALL
				SELECT 000000000.000 as kol_v,  SUM(Ymana.kolicina) as kol_i
				FROM Ymana  LEFT JOIN tbrojce  ON Ymana.brojce_i = tbrojce.brojce
				WHERE Ymana.godina = '$god'
				AND tbrojce.godina = '$god'
				AND Ymana.status='2'
				AND Ymana.m_u= 'M'
				AND Ymana.fe_i = 'E'
				AND Ymana.cod_i = '02-1'
				AND tbrojce.fe  = 'E'
				AND tbrojce.cod = '02-1'
				AND Ymana.mat='$mat'
				AND IF(Ymana.codr='', 1, obrabotka <> 0)
				GROUP BY   Ymana.mat) as sostojba");
		
				$zal=mysqli_fetch_row($select);
		
				$cmd_kol=mysqli_query($handle, "SELECT sum(kolicina) from nar_stavki where mat='$mat' and nalb='' and brisi=0 and nar_glava_id<>$glava_id");
				$vk_kol=mysqli_fetch_row($cmd_kol);
					
		$zaliha=$zal[0] - $vk_kol[0];
		
		$cena=$_POST['cena'.$i];
		$cena=mysqli_real_escape_string($handle,$cena);
		
		$rabat=$_POST['rabat'.$i];
		$rabat=mysqli_real_escape_string($handle,$rabat);
		
		$cmd=mysqli_query($handle, "SELECT max(id) from nar_stavki where nar_glava_id=$glava_id");
		$rowid=mysqli_fetch_row($cmd);
		$id=$rowid[0]+1;
		
		$cena_r=$cena - $cena*$rabat/100;
		
		$vk_vr=($kol * $cena_r);
		
		$firma=$_SESSION['firma'];
		
		$cmd_check=mysqli_query($handle, " select if(f_saldo<0 and limit_f,0,1) as dozvola, f_saldo, fin_limit,
											finansii as finansii_sostojba, naracka_st as naracka_segasna ,
											naracka_vk as naracka_nerealizirana, limit_f
				from (select korisnik, fin_limit,finansii,naracka_st,naracka_vk, fin_limit+finansii-naracka_st-naracka_vk-0 as f_saldo, limit_f
				from (select (select cod from firmi where tip='F' and cod='$firma') as korisnik, ifnull(sum(sumap-sumad),0.0000) as 'finansii',
				(select limit_f from firmi where firmi.tip='F' and firmi.cod=korisnik) as limit_f, (select fin_limit from firmi where firmi.tip='F' and firmi.cod=korisnik) as fin_limit
				from yana join konta on yana.konto=konta.konto and yana.godina=konta.godina where yana.godina='$god' and help_k=1 and korisnik='$firma' ) as fin
				join (select  (select cod from firmi where tip='F' and cod='$firma') as firma, ifnull(sum(nar_stavki.vk_vr), 0000.000) as naracka_st
				from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id where nar_glava.firma='$firma' and nar_glava.brisi=0 and nar_stavki.brisi=0 and nar_glava.id=$glava_id and nar_glava.godina='$god') as nar
				on fin.korisnik=nar.firma
				join (select (select cod from firmi where tip='F' and cod='$firma') as firma,
				ifnull(sum(nar_stavki.vk_vr), 0000.000) as naracka_vk
				from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id
				where nar_glava.firma='$firma' and nar_glava.id<>$glava_id and nar_glava.nalb='' and nar_glava.brisi=0 and nar_stavki.brisi=0 
				and nar_glava.godina='$god') as nar_vk
				on fin.korisnik=nar_vk.firma) as site");
		
		$check=mysqli_fetch_row($cmd_check);
		
		if ($check[0]==0){
			echo ("<SCRIPT LANGUAGE='JavaScript'>
					window.alert('Со оваа нарачка се надминува дозволениот лимит! Моменталната состојба е $check[1]' )
					</SCRIPT>");
			exit();
		}
		
		
		if ($zaliha>0 && ($zaliha-$kol) < 0){
			$ostalo=($zaliha-$kol)*(-1);
			$vk_vr=$zaliha*$cena_r;
			$sel1=mysqli_query($handle, "SELECT * from nar_stavki where nar_glava_id=$glava_id and mat='$mat' and kolicina>0 and brisi=0 ");
			$ima_mat=mysqli_fetch_row($sel1);
			
			if(EMPTY($ima_mat[3])){
				mysqli_query($handle, "INSERT INTO nar_stavki (id, nar_glava_id, mat, kolicina, cena, rabat, cena_r, vk_vr) VALUES($id, $glava_id, $mat, $zaliha, $cena, $rabat, $cena_r, $vk_vr)");
			}else{
				mysqli_query($handle, "UPDATE nar_stavki set kolicina=$zaliha, vk_vr=$vk_vr, sentd=0 where nar_glava_id=$glava_id and mat='$mat' and kolicina>0");
			}
			
			$id=$id+1;
			$sel2=mysqli_query($handle, "SELECT * from nar_stavki where nar_glava_id=$glava_id and brisi=0 and mat='$mat' and propusteno>0");
			$ima=mysqli_fetch_row($sel2);
			if(EMPTY($ima[3])){
				mysqli_query($handle, "INSERT INTO nar_stavki (id, nar_glava_id, mat, propusteno) VALUES($id, $glava_id, $mat, $ostalo)");
			}else{
				mysqli_query($handle, "UPDATE nar_stavki set propusteno=$ostalo, sentd=0 where nar_glava_id=$glava_id and mat='$mat' and propusteno>0");
			}
			echo ("<SCRIPT LANGUAGE='JavaScript'>
					window.alert('Нарачавте $zaliha од ".$_POST['mat'.$i]." , пропуштени се $ostalo')
					</SCRIPT>");
			continue;
		}
		elseif ($zaliha==0 || empty($zaliha))
		{
			$sel=mysqli_query($handle, "SELECT * from nar_stavki where nar_glava_id=$glava_id and brisi=0 and mat='$mat' and propusteno>0");
			$ima_mat=mysqli_fetch_row($sel);
			if(EMPTY($ima_mat[3])){
				mysqli_query($handle, "INSERT INTO nar_stavki (id, nar_glava_id, mat, propusteno) VALUES($id, $glava_id, $mat, $kol)");
				continue;
			}else{
				mysqli_query($handle, "UPDATE nar_stavki set propusteno=$kol, sentd=0 where nar_glava_id=$glava_id and mat='$mat' and propusteno>0");
				continue;
			}
		}
		elseif ((empty($cena) || $cena==0) && ($zaliha>0)){                               
			echo ("<SCRIPT LANGUAGE='JavaScript'>
					window.alert('Артиклот ".$_POST['mat'.$i]."нема цена')                      
					</SCRIPT>");
			continue;
		}
		elseif ($zaliha>0 && ($zaliha-$kol) >= 0){
			$sel=mysqli_query($handle, "SELECT COUNT(mat) FROM (SELECT * from nar_stavki where nar_glava_id=$glava_id and brisi=0 and mat='$mat') as aa");
			$ima_mat=mysqli_fetch_row($sel);		
			if($ima_mat[0]==2){
				mysqli_query($handle, "UPDATE nar_stavki set brisi=1, sentd=0 where nar_glava_id=$glava_id and mat='$mat' and propusteno>0");
				mysqli_query($handle, "UPDATE nar_stavki set kolicina=$kol, vk_vr=$vk_vr, sentd=0 where nar_glava_id=$glava_id and mat='$mat' and kolicina>0");
			}elseif($ima_mat[0]==1){
				mysqli_query($handle, "UPDATE nar_stavki set kolicina=$kol, vk_vr=$vk_vr, sentd=0 where nar_glava_id=$glava_id and mat='$mat' and kolicina>0");
			}elseif($ima_mat[0]==0){
				mysqli_query($handle, "INSERT INTO nar_stavki (id, nar_glava_id, mat, kolicina, cena, rabat, cena_r, vk_vr) VALUES($id, $glava_id, $mat, $kol, $cena, $rabat, $cena_r, $vk_vr)");
			}
		}
	}
	echo ("<SCRIPT LANGUAGE='JavaScript'>
			window.location.href='naracki_prikaz_stavki.php?glava_id=".$glava_id."'
			</SCRIPT>");
	exit();
}

?>








