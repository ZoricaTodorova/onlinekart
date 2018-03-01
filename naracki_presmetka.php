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

if (isset($_GET['glava_id'])){
	$handle=connectwebnal();
	$glava_id=$_GET['glava_id'];
	$glava_id=mysqli_real_escape_string($handle,$glava_id);
	
	$cmd2=mysqli_query($handle, "SELECT firma from nar_glava where id=$glava_id");
	$zz=mysqli_fetch_row($cmd2);
	
	$firma=$zz[0];
	
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
	echo "</br></br><table border='2' cellspacing='0' width='100%'>
	<tr>
	<th>Мат</th>
	<th>Опис</th>
	<th>Количина</th>
	<th>Цена</th>
	<th>Рабат</th>
	<th>Цена со рабат</th>
	<th>Износ со рабат</th>
	<th style='color:red'>Пропуштено</th>
	<th>&nbsp</th>
	</tr>";
	
	$res=mysqli_query($handle, "Select * from nar_stavki where nar_glava_id=".$glava_id." order by id desc");
	
	$vk_kol=0;
	$vk_iznos=0;
	$vk_propusteno=0;
	
	while($row = mysqli_fetch_array($res))
	{
	
		$kolicina=$row['kolicina'];
		$vk_kol=$vk_kol+$kolicina;
		
		$iznos=$row['vk_vr'];
		$vk_iznos=$vk_iznos+$iznos;
		
		$propusteno=$row['propusteno'];
		$vk_propusteno=$vk_propusteno+$propusteno;
		
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
	
		echo "<tr>";
		echo "<td><td align='center'><b>Тотали</b></td></td>";
		echo "<td align='center'><b>".number_format($vk_kol,1)."</b></td>";
		echo "<td><td><td><td align='center'><b>".number_format($vk_iznos,0)."</b></td></td></td></td>";
		echo "<td align='center' style='color:red'><b>".number_format($vk_propusteno,1)."</b></td>";
		echo "</tr>";
	
	echo "</table>";
?>
</br>
<table width='100%'>
<tr>
	<td width='50%'>
		<form method='POST' action='naracki_grid_mat.php?glava_id=<?php echo $glava_id; ?>'>
			<input type='submit' value='Додади ставка за истата нарачка' style='height: 70px; width: 50%' name='next' id='next'/>
		</form>
	</td>
	<td width='50%' align='right'>
		<form method='POST' action='izbor_naracki.php'>
			<input type='submit' value='Затвори' style='height: 70px; width: 50%' name='close' id='close'/>
		</form>
	</td>
</tr>
</table>
</body>
</html>

<?php 
}

if (isset($_GET['glava_id']) && isset($_GET['brisi_s'])){
	$handle=connectwebnal();
	$brisi_s=$_GET['brisi_s'];
	$brisi_s=mysqli_real_escape_string($handle, $brisi_s);
	$glava_id=$_GET['glava_id'];
	$glava_id=mysqli_real_escape_string($handle, $glava_id);
	mysqli_query($handle,"DELETE FROM nar_stavki where nar_glava_id=$glava_id AND id=$brisi_s" );
	echo ("<SCRIPT LANGUAGE='JavaScript'>
			window.location.href='naracki_presmetka.php?glava_id=".$glava_id."'
			</SCRIPT>");
}
?>







