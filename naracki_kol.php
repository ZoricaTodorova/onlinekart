<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/jquery-2.0.3.min.js"></script>          
<script src="js/functions.js"></script>
<script type="text/javascript" src="js/jquery.js"></script>
<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />

<script type="text/javascript">
function FocusOnInput()
{
     document.getElementById("kol").focus();
}
</script>

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
$god=date("Y");
echo "<h4 align=center>Испрати нарачка</h4>";

$handle=connectwebnal();
if (isset($_GET['glava_id']) && !EMPTY($_GET['glava_id'])){
	$glava_id=$_GET['glava_id'];
	$glava_id=mysqli_real_escape_string($handle,$glava_id);
	
	if (isset($_GET['mat']) && !EMPTY($_GET['mat']) && isset($_GET['sifra_mat']) && EMPTY($_GET['sifra_mat'])){
		$mat=$_GET['mat'];
		$mat=mysqli_real_escape_string($handle,$mat);
		$_SESSION['mat']=$mat;
		$cmd2=mysqli_query($handle, "SELECT opis from materijali where cod='$mat'");
		$mat_opis=mysqli_fetch_row($cmd2);
		
		$datum=date('Y-m-d H:i:s');
		$datum=substr($datum,0,10);
		$firma=$_SESSION['firma'];
		
		$cmd1=mysqli_query($handle, "Select tip_cena from firmi where cod='".$firma."'");
		$tip_arr=mysqli_fetch_row($cmd1);
		if (EMPTY($tip_arr[0])){
			$tip=1;
		}else{$tip=$tip_arr[0];}
		
		$cmd="	select cena from cenovnik where mat = '$mat' and cenovnik.tip ='$tip'
				and concat(cast(oddat as char),odcas)<= concat(CAST(CAST('$datum' as date) as char),'00:00')
				and if(cenovnik.dodat<>'0000-00-00',concat(cast(dodat as char),docas)>=CONCAT(CAST(CAST('$datum' as date) as char),'00:00') ,cenovnik.dodat='0000-00-00') ";
		$result=mysqli_query($handle, $cmd);	
		$cena=mysqli_fetch_row($result);
		$cena_ddv=$cena[0]*18/100 + $cena[0];
		
		$query=mysqli_query($handle, "SELECT rbp from firmi where cod='$firma'");
		$rabat=mysqli_fetch_row($query);
		
		$select=mysqli_query($handle, " select sum(kol_v-kol_i) from 
										(SELECT SUM(Ymana.kolicina) as kol_v, 000000000.000 as kol_i
										FROM Ymana  LEFT JOIN tbrojce  ON Ymana.brojce_v = tbrojce.brojce 
										WHERE Ymana.godina = '2013' 
											AND tbrojce.godina = '2013'
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
										WHERE Ymana.godina = '2013' 
											AND tbrojce.godina = '2013' 
											AND Ymana.status='2'  
											AND Ymana.m_u= 'M'
											AND Ymana.fe_i = 'E' 
											AND Ymana.cod_i = '02-1'  
											AND tbrojce.fe  = 'E'  
											AND tbrojce.cod = '02-1'  
										    AND Ymana.mat='$mat'	
											AND IF(Ymana.codr='', 1, obrabotka <> 0) 
										GROUP BY   Ymana.mat) as sostojba");
		
		$zaliha=mysqli_fetch_row($select);
		
		$cmd_kol=mysqli_query($handle, "SELECT sum(kolicina) from nar_stavki where mat='$mat' and ISNULL(nalb) ");
		$kol=mysqli_fetch_row($cmd_kol);
			
		$vkupno=$zaliha[0] - $kol[0];
		
		echo "<br/><a style='font-size: 200%;' href='naracki_mat.php?glava_id=".$glava_id."'>Назад</a>";
		
		$cmd_vknar_nerealizirani=mysqli_query($handle, "select (select cod from firmi where tip='F' and cod='$firma') as firma,
				ifnull(sum(nar_stavki.vk_vr), 0000.000) as naracka_nerealizirana
				from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id
				where nar_glava.firma='$firma' and nar_glava.nalb='' and nar_glava.godina='$god'");		
		$nerealizirani=mysqli_fetch_row($cmd_vknar_nerealizirani);
		
		
		$cmd_check=mysqli_query($handle, " select if(f_saldo<0 and limit_f,0,1) as dozvola, f_saldo, fin_limit,
											finansii as finansii_sostojba, naracka_st as naracka_segasna ,
											naracka_vk as naracka_nerealizirana, limit_f
				from (select korisnik, fin_limit,finansii,naracka_st,naracka_vk, fin_limit+finansii-naracka_st-naracka_vk-0 as f_saldo, limit_f
				from (select (select cod from firmi where tip='F' and cod='$firma') as korisnik, ifnull(sum(sumap-sumad),0.0000) as 'finansii',
				(select limit_f from firmi where firmi.tip='F' and firmi.cod=korisnik) as limit_f, (select fin_limit from firmi where firmi.tip='F' and firmi.cod=korisnik) as fin_limit
				from yana join konta on yana.konto=konta.konto and yana.godina=konta.godina where yana.godina='$god' and help_k=1 and korisnik='$firma' ) as fin
				join (select  (select cod from firmi where tip='F' and cod='$firma') as firma, ifnull(sum(nar_stavki.vk_vr), 0000.000) as naracka_st
				from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id where nar_glava.firma='$firma' and nar_glava.id=$glava_id and nar_glava.godina='$god') as nar
				on fin.korisnik=nar.firma
				join (select (select cod from firmi where tip='F' and cod='$firma') as firma,
				ifnull(sum(nar_stavki.vk_vr), 0000.000) as naracka_vk
				from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id
				where nar_glava.firma='$firma' and nar_glava.id<>$glava_id and nar_glava.nalb=''
				and nar_glava.godina='$god') as nar_vk
				on fin.korisnik=nar_vk.firma) as site");
		
				$check=mysqli_fetch_row($cmd_check);
						
?>
<body bgcolor='<?php echo $_SESSION['boja']; ?>' onload="FocusOnInput()">
<table>
<tr>
	<td><b>За фирма: <?php echo $_SESSION['firma_opis']; ?></b></td> 
	<td align='right'><b> Лимит во финансии:</b> </td>
	<td align='right'> <?php 	if (isset($check[2]) && !empty($check[2])){
					echo "<font color='blue'><b>".number_format($check[2],2)."</b></font>";
		} ?>
	</td>
</tr>
<tr>
	<td><b>Продавница: <?php echo $_SESSION['prod_opis']; ?></b></td>
	<td align='right'><b>Салдо во финансии:</b> </td>
	<td align='right'><?php 	if (isset($_SESSION['saldo']) && !empty($_SESSION['saldo'])){
					$aa=$_SESSION['saldo'];
					echo "<font color='red'><b>".number_format($aa,2)."</b></font>";
		} ?>
	</td>
</tr>
<tr>
	<td><b>Датум: <?php echo $_SESSION['datum']; ?></b></td>
	<td align='right'><b> Нереализирани нарачки:</b> </td>
	<td align='right'><?php 	if (isset($nerealizirani[1]) && !empty($nerealizirani[1])){
					echo "<font color='red'><b>".number_format($nerealizirani[1]*(-1),2)."</b></font>";
		} ?>
	
	</td>
</tr>
<tr>
	<td><b><?php if (isset($check[6]) && !empty($check[6])){
					echo "<b>Има </b>";
			}else{echo "<b>Нема </b>";}?></b>забрана при негативно салдо</td>
	<td align='right'><b> Тековно салдо:</b> </td>
	<td align='right' ><?php 	if (isset($check[1]) && !empty($check[1])){
					echo "<font color='red'><b>".number_format($check[1],2)."</b></font>";
				} ?>
	
	</td>
</tr>
</table>

<?php 
if (EMPTY($zaliha[0]) || ($zaliha[0]==0)){
	echo "<p><b style='color:red;font-size:20'>Пропуштена продажба</b></p>";
}
?>

<form method='POST' action='naracki_presmetka.php'>
<table width='100%'>
	<tr>
		<th><font size="5"></font></th>
		<th><font size="5">Материјал</font></th>
		<th align='center'><font size="5">Залиха</font></th>
		<th align='center'><font size="5">Цена</font></th>
		<th align='center'><font size="5">Рабат</font></th>
		<th><font size="5">Количина</font></th>
		<th>&nbsp</th>
	</tr>
	<tr>
	    <td><input type='hidden' readonly required value='<?php echo $glava_id; ?>' style="height:60px; width:100%; text-align:center; font-size:22px; border:none;" onClick="this.select();" name='glava_id' id='glava_id'/></td>
		<td align='center' width='50%'><input type='text' readonly required value='<?php echo $mat." - ".$mat_opis[0]; ?>' style="height:60px; width:100%; text-align:center; background-color:<?php echo '#'. $_SESSION['boja']; ?> ;font-size:22px; border:none;<?php if (EMPTY($zaliha[0]) || ($zaliha[0]==0)){echo "color:red";} ?>" onClick="this.select();" name='mat' id='mat'/></td>
		<td align='center'><input type='text'  readonly value='<?php echo $vkupno; ?>' style="height:60px; text-align:center; background-color:<?php echo '#'. $_SESSION['boja']; ?> ;font-size:22px; border:none;<?php if (EMPTY($zaliha[0]) || ($zaliha[0]==0)){echo "color:red";} ?>" onClick="this.select();" name='zaliha' id='zaliha'/></td>
		<td align='center'><input type='text'  readonly value='<?php echo number_format($cena_ddv,0); ?>' style="height:60px; text-align:center; background-color:<?php echo '#'. $_SESSION['boja']; ?> ;font-size:22px; border:none;<?php if (EMPTY($zaliha[0]) || ($zaliha[0]==0)){echo "color:red";} ?>" onClick="this.select();" name='cena' id='cena'/></td>
		<td align='center' width='10%'><input type='text' readonly value='<?php echo number_format($rabat[0],2); ?>' style="height:60px; background-color:<?php echo '#'. $_SESSION['boja']; ?> ;text-align:center; width:80%; font-size:22px; border:none;<?php if (EMPTY($zaliha[0]) || ($zaliha[0]==0)){echo "color:red";} ?>" onClick="this.select();" name='rabat' id='rabat'/></td>
		<td align='center' width='15%'><input type='text' required style="height:60px; text-align:center; width:80%; font-size:22px;<?php if (EMPTY($zaliha[0]) || ($zaliha[0]==0)){echo "color:red";} ?>" onClick="this.select();" name='kol' id='kol'/></td>
		<td width='20%'><input type='submit' value='Зачувај' style="height:70px; width:100%" name='zacuvaj' id='zacuvaj'/></td>
	</tr>
</table>
</form>

<?php 
	}
	elseif (isset($_GET['sifra_mat']) && !EMPTY($_GET['sifra_mat'])){
		$mat_arr=explode(' - ',$_GET['sifra_mat']);
		$mat=$mat_arr[0];
		$mat=mysqli_real_escape_string($handle, $mat);
		$_SESSION['mat']=$mat;
		
		$cmd_mat=mysqli_query($handle, "SELECT cod from materijali where cod='$mat'");
		$ima_mat=mysqli_fetch_row($cmd_mat);
		if (EMPTY($ima_mat[0])){
			echo ("<SCRIPT LANGUAGE='JavaScript'>
					window.alert('Неправилен код')
					window.location.href='naracki_mat.php?glava_id=$glava_id'
					</SCRIPT>");
			exit();
		}
		
		$cmd2=mysqli_query($handle, "SELECT opis from materijali where cod='$mat'");
		$mat_opis=mysqli_fetch_row($cmd2);
		
		$datum=date('Y-m-d H:i:s');
		$datum=substr($datum,0,10);
		$firma=$_SESSION['firma'];
		$cmd3=mysqli_query($handle, "Select opis from firmi where cod='$firma'");
		$firma_opis=mysqli_fetch_row($cmd3);
		
		$cmd1=mysqli_query($handle, "Select tip_cena from firmi where cod='".$firma."'");
		$tip_arr=mysqli_fetch_row($cmd1);
		if (EMPTY($tip_arr[0])){
			$tip=1;
		}else{$tip=$tip_arr[0];}
		
		$cmd="	select cena from cenovnik where mat = '$mat' and cenovnik.tip ='$tip'
				and concat(cast(oddat as char),odcas)<= concat(CAST(CAST('$datum' as date) as char),'00:00')
				and if(cenovnik.dodat<>'0000-00-00',concat(cast(dodat as char),docas)>=CONCAT(CAST(CAST('$datum' as date) as char),'00:00') ,cenovnik.dodat='0000-00-00') ";
		$result=mysqli_query($handle, $cmd);	
		$cena=mysqli_fetch_row($result);
		$cena_ddv=$cena[0]*18/100 + $cena[0];
		
		$query=mysqli_query($handle, "SELECT rbp from firmi where cod='$firma'");
		$rabat=mysqli_fetch_row($query);
		
		$select=mysqli_query($handle, " select sum(kol_v-kol_i) from 
										(SELECT SUM(Ymana.kolicina) as kol_v, 000000000.000 as kol_i
										FROM Ymana  LEFT JOIN tbrojce  ON Ymana.brojce_v = tbrojce.brojce 
										WHERE Ymana.godina = '2013' 
											AND tbrojce.godina = '2013'
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
										WHERE Ymana.godina = '2013' 
											AND tbrojce.godina = '2013' 
											AND Ymana.status='2'  
											AND Ymana.m_u= 'M'
											AND Ymana.fe_i = 'E' 
											AND Ymana.cod_i = '02-1'  
											AND tbrojce.fe  = 'E'  
											AND tbrojce.cod = '02-1'  
										    AND Ymana.mat='$mat'	
											AND IF(Ymana.codr='', 1, obrabotka <> 0) 
										GROUP BY   Ymana.mat) as sostojba");
		
		$zaliha=mysqli_fetch_row($select);
		
		$cmd_kol=mysqli_query($handle, "SELECT sum(kolicina) from nar_stavki where mat='$mat' and ISNULL(nalb) ");
		$kol=mysqli_fetch_row($cmd_kol);
			
		$vkupno=$zaliha[0] - $kol[0];
		
		echo "<br/><a style='font-size: 200%;' href='naracki_mat.php?glava_id=".$glava_id."'>Назад</a>";
		
		$cmd_vknar_nerealizirani=mysqli_query($handle, "select (select cod from firmi where tip='F' and cod='$firma') as firma,
				ifnull(sum(nar_stavki.vk_vr), 0000.000) as naracka_nerealizirana
				from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id
				where nar_glava.firma='$firma' and nar_glava.nalb='' and nar_glava.godina='$god'");
		$nerealizirani=mysqli_fetch_row($cmd_vknar_nerealizirani);
		
		$cmd_check=mysqli_query($handle, " select if(f_saldo<0 and limit_f,0,1) as dozvola, f_saldo, fin_limit,
											finansii as finansii_sostojba, naracka_st as naracka_segasna ,
											naracka_vk as naracka_nerealizirana, limit_f
				from (select korisnik, fin_limit,finansii,naracka_st,naracka_vk, fin_limit+finansii-naracka_st-naracka_vk-0 as f_saldo, limit_f
				from (select (select cod from firmi where tip='F' and cod='$firma') as korisnik, ifnull(sum(sumap-sumad),0.0000) as 'finansii',
				(select limit_f from firmi where firmi.tip='F' and firmi.cod=korisnik) as limit_f, (select fin_limit from firmi where firmi.tip='F' and firmi.cod=korisnik) as fin_limit
				from yana join konta on yana.konto=konta.konto and yana.godina=konta.godina where yana.godina='$god' and help_k=1 and korisnik='$firma' ) as fin
				join (select  (select cod from firmi where tip='F' and cod='$firma') as firma, ifnull(sum(nar_stavki.vk_vr), 0000.000) as naracka_st
				from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id where nar_glava.firma='$firma' and nar_glava.id=$glava_id and nar_glava.godina='$god') as nar
				on fin.korisnik=nar.firma
				join (select (select cod from firmi where tip='F' and cod='$firma') as firma,
				ifnull(sum(nar_stavki.vk_vr), 0000.000) as naracka_vk
				from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id
				where nar_glava.firma='$firma' and nar_glava.id<>$glava_id and nar_glava.nalb=''
				and nar_glava.godina='$god') as nar_vk
				on fin.korisnik=nar_vk.firma) as site");
		
				$check=mysqli_fetch_row($cmd_check);
					
?>
<body bgcolor='<?php echo $_SESSION['boja']; ?>' onload="FocusOnInput()">
<table>
<tr>
	<td><b>За фирма: <?php echo $_SESSION['firma_opis']; ?></b></td> 
	<td align='right'><b> Лимит во финансии:</b> </td>
	<td align='right'> <?php 	if (isset($check[2]) && !empty($check[2])){
					echo "<font color='blue'><b>".number_format($check[2],2)."</b></font>";
		} ?>
	</td>
</tr>
<tr>
	<td><b>Продавница: <?php echo $_SESSION['prod_opis']; ?></b></td>
	<td align='right'><b>Салдо во финансии:</b> </td>
	<td align='right'><?php 	if (isset($_SESSION['saldo']) && !empty($_SESSION['saldo'])){
					$aa=$_SESSION['saldo'];
					echo "<font color='red'><b>".number_format($aa,2)."</b></font>";
		} ?>
	</td>
</tr>
<tr>
	<td><b>Датум: <?php echo $_SESSION['datum']; ?></b></td>
	<td align='right'><b> Нереализирани нарачки:</b> </td>
	<td align='right'><?php 	if (isset($nerealizirani[1]) && !empty($nerealizirani[1])){
					echo "<font color='red'><b>".number_format($nerealizirani[1]*(-1),2)."</b></font>";
		} ?>
	
	</td>
</tr>
<tr>
	<td><b><?php if (isset($check[6]) && !empty($check[6])){
				echo "<b>Има </b>";
		}else{echo "<b>Нема </b>";}?></b>забрана при негативно салдо</td>
	<td align='right'><b> Моментална состојба-салдо:</b> </td>
	<td align='right'><?php 	if (isset($check[1]) && !empty($check[1])){
					echo "<font color='red'><b>".number_format($check[1],2)."</b></font>";
				} ?>
	
	</td>
</tr>
</table>

<?php 
if (EMPTY($zaliha[0]) || ($zaliha[0]==0)){
	echo "<p><b style='color:red;font-size:20'>Пропуштена продажба</b></p>";
}
?>

<form method='POST' action='naracki_presmetka.php'>
<table width='100%'>
	<tr>
		<th><font size="5"></font></th>
		<th><font size="5">Материјал</font></th>
		<th align='center'><font size="5">Залиха</font></th>
		<th align='center'><font size="5">Цена</font></th>
		<th align='center'><font size="5">Рабат</font></th>
		<th><font size="5">Количина</font></th>
		<th>&nbsp</th>
	</tr>
	<tr>
	    <td><input type='hidden' readonly required value='<?php echo $glava_id; ?>' style="height:60px; width:100%; text-align:center; font-size:22px; border:none;" onClick="this.select();" name='glava_id' id='glava_id'/></td>
		<td align='center' width='50%'><input type='text' readonly required value='<?php echo $mat." - ".$mat_opis[0]; ?>' style="height:60px; width:100%; text-align:center; background-color:<?php echo '#'. $_SESSION['boja']; ?> ;font-size:22px; border:none;<?php if (EMPTY($zaliha[0]) || ($zaliha[0]==0)){echo "color:red";} ?>" onClick="this.select();" name='mat' id='mat'/></td>
		<td align='center'><input type='text'  readonly value='<?php echo $vkupno; ?>' style="height:60px; text-align:center; background-color:<?php echo '#'. $_SESSION['boja']; ?> ;font-size:22px; border:none;<?php if (EMPTY($zaliha[0]) || ($zaliha[0]==0)){echo "color:red";} ?>" onClick="this.select();" name='zaliha' id='zaliha'/></td>
		<td align='center'><input type='text'  readonly value='<?php echo number_format($cena_ddv,0); ?>' style="height:60px; background-color:<?php echo '#'. $_SESSION['boja']; ?> ;text-align:center; font-size:22px; border:none;<?php if (EMPTY($zaliha[0]) || ($zaliha[0]==0)){echo "color:red";} ?>" onClick="this.select();" name='cena' id='cena'/></td>
		<td align='center' width='10%'><input type='text' readonly value='<?php echo number_format($rabat[0],2); ?>' style="height:60px; background-color:<?php echo '#'. $_SESSION['boja']; ?> ;text-align:center; width:80%; font-size:22px; border:none;<?php if (EMPTY($zaliha[0]) || ($zaliha[0]==0)){echo "color:red";} ?>" onClick="this.select();" name='rabat' id='rabat'/></td>
		<td align='center' width='15%'><input type='text' required style="height:60px; text-align:center; width:80%; font-size:22px;<?php if (EMPTY($zaliha[0]) || ($zaliha[0]==0)){echo "color:red";} ?>" onClick="this.select();" name='kol' id='kol'/></td>
		<td width='20%'><input type='submit' value='Зачувај' style="height:70px; width:100%" name='zacuvaj' id='zacuvaj'/></td>
	</tr>
</table>
</form>

<?php 
	}
	
	echo "<table border='2' cellspacing='0' width='100%'>
	<tr>
	<th>Мат</th>
	<th>Опис</th>
	<th>Количина</th>
	<th>Цена</th>
	<th>Рабат</th>
	<th>Цена со рабат</th>
	<th>Износ со рабат</th>
	<th>Пропуштено</th>
	<th>&nbsp</th>
	</tr>";
	
	$res=mysqli_query($handle, "Select * from nar_stavki where nar_glava_id=".$glava_id." order by id desc");
	while($row = mysqli_fetch_array($res))
	{
	
		$con=mysqli_query($handle, "SELECT opis from materijali where cod='".$row['mat']."'");
		$opis_mat=mysqli_fetch_row($con);
	
		echo "<tr>";
		echo "<td align='center'>" . $row['mat']."</td>";
		echo "<td align='center'>". $opis_mat[0] . "</td>";
		echo "<td align='center'>" . number_format($row['kolicina'],1) . "</td>";
		echo "<td align='center'>" . number_format($row['cena'],0) . "</td>";
		echo "<td align='center'>" . number_format($row['rabat'],2) . "</td>";
		echo "<td align='center'>" . number_format($row['cena_r'],0) . "</td>";
		echo "<td align='center'>" . number_format($row['vk_vr'],0) . "</td>";
		echo "<td align='center' style='color:red'>" . number_format($row['propusteno'],1) . "</td>";
		echo "<td align='center'><a href=\"?glava_id=".$row['nar_glava_id']."&brisi_s=".$row['id']."\" onclick=\"return confirm('Дали ја бришете ставката?');\">бриши</a></td>";
	
		echo "</tr>";
	}
	echo "</table>";

}

if (isset($_GET['glava_id']) && isset($_GET['brisi_s'])){
	$handle=connectwebnal();
	$brisi_s=$_GET['brisi_s'];
	$brisi_s=mysqli_real_escape_string($handle, $brisi_s);
	$glava_id=$_GET['glava_id'];
	$glava_id=mysqli_real_escape_string($handle, $glava_id);
	mysqli_query($handle,"DELETE FROM nar_stavki where nar_glava_id=$glava_id AND id=$brisi_s" );
	echo ("<SCRIPT LANGUAGE='JavaScript'>
			window.location.href='naracki_kol.php?glava_id=$glava_id&sifra_mat=".$_SESSION['mat']."'
			</SCRIPT>");
}
?>


</body>
</html>







