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

$datum = date('d-m-Y H:i:s');
$handle=connectwebnal();

echo "<b>За фирма: ".$_SESSION['firma_opis']."<b><br/>";

$cmd2=mysqli_query($handle, "SELECT opis from org_e where cod=".$_GET['prod_m']."");
$prod_opis=mysqli_fetch_row($cmd2);
$_SESSION['prod_opis']=$prod_opis[0];
echo "<b>Продавница: ".$_SESSION['prod_opis']."<b><br/><br/>";

?>
<body bgcolor='<?php echo $_SESSION['boja']; ?>'>
<form method='post'>
<b style='font-size:20px;'>Коментар:</b></br>
<textarea type='text' rows="7" cols="40"  style='font-size:20px;' name='komentar' id='komentar'></textarea><b style='font-size:20px;'>  Во готово:</b><input type="checkbox" name="vo_gotovo" id='vo_gotovo' value="1">
</br></br><input type='submit' value='Зачувај' style='height: 7%; width: 13%' name='zacuvaj' id='zacuvaj'/></br>
</form>
<?php 
if (isset($_GET['firma']) && isset($_GET['prod_m'])){
	$prod_m=$_GET['prod_m'];
	$prod_m=mysqli_real_escape_string($handle,$prod_m);
	
	$firma=$_GET['firma'];
	$firma=mysqli_real_escape_string($handle,$firma);
	
	$m_t=$_SESSION['lgn'];
	
	$cmd3=mysqli_query($handle, "SELECT id,datum from nar_glava where firma=$firma and brisi=0 and prod_m=$prod_m and m_t=$m_t and datum=(select max(datum) from nar_glava where firma=$firma and brisi=0 and prod_m=$prod_m and m_t=$m_t)");
	$glava_latest=mysqli_fetch_row($cmd3);
	if (EMPTY($glava_latest[0])){
		$glava_latest[0]=0;
	}
	if (EMPTY($glava_latest[1])){
		$glava_latest[1]='';
	}
	$cmd4=mysqli_query($handle, "SELECT * from nar_stavki where brisi=0 and nar_glava_id=$glava_latest[0]");
	
	echo "<p align=center>Претходна нарачка од датум: $glava_latest[1]</p>";
	echo "<table border='2' cellspacing='0' width='100%'>
	<tr>
	<th>Мат</th>
	<th>Опис</th>
	<th>Количина</th>
	<th>Испорачано</th>
	<th>Цена</th>
	<th>Рабат</th>
	<th>Цена со рабат</th>
	<th>Износ со рабат</th>
	<th style='color:red'>Пропуштено</th>
	</tr>";
	
	$vk_kol=0;
	$vk_iznos=0;
	$vk_propusteno=0;
	$vk_isporacano=0;
	
	while($row = mysqli_fetch_array($cmd4))
	{
	
		$kolicina=$row['kolicina'];
		$vk_kol=$vk_kol+$kolicina;
	
		$iznos=$row['vk_vr'];
		$vk_iznos=$vk_iznos+$iznos;
	
		$propusteno=$row['propusteno'];
		$vk_propusteno=$vk_propusteno+$propusteno;
	
		$isporacano=$row['isporacano'];
		$vk_isporacano=$vk_isporacano+$isporacano;
	
		$con=mysqli_query($handle, "SELECT opis from materijali where cod='".$row['mat']."'");
		$opis_mat=mysqli_fetch_row($con);
	
		echo "<tr>";
		echo "<td align='center'>" . $row['mat']."</td>";
		echo "<td align='center'>". $opis_mat[0] . "</td>";
		echo "<td align='center'>" . number_format($row['kolicina'],1) . "</td>";
		echo "<td align='center'>" . number_format($row['isporacano'],1) . "</td>";
		echo "<td align='center'>" . number_format($row['cena'],0) . "</td>";
		echo "<td align='center'>" . number_format($row['rabat'],2) . "</td>";
		echo "<td align='center'>" . number_format($row['cena_r'],0) . "</td>";
		echo "<td align='center'>" . number_format($row['vk_vr'],0) . "</td>";
		echo "<td align='center' style='color:red'>" . number_format($row['propusteno'],1) . "</td>";
		echo "</tr>";
	}
	
	echo "<tr>";
	echo "<td><td align='center'><b>Тотали</b></td></td>";
	echo "<td align='center'><b>".number_format($vk_kol,1)."</b></td>";
	echo "<td align='center'><b>".number_format($vk_isporacano,1)."</b></td>";
	echo "<td><td><td><td align='center'><b>".number_format($vk_iznos,0)."</b></td></td></td></td>";
	echo "<td align='center' style='color:red'><b>".number_format($vk_propusteno,1)."</b></td>";
	echo "</tr>";
	
	echo "</table>";
}
?>

<?php 
if (isset($_POST['zacuvaj']) && isset($_GET['firma']) && isset($_GET['prod_m'])){
	$handle=connectwebnal();
	
	$god=date("Y");
	
	if (isset($_POST['komentar']) && !EMPTY($_POST['komentar'])){
		$komentar=$_POST['komentar'];
		$komentar=mysqli_real_escape_string($handle,$komentar);
	}else{$komentar='';}
	
	if (isset($_POST['vo_gotovo']) && !EMPTY($_POST['vo_gotovo'])){
		$vo_gotovo=$_POST['vo_gotovo'];
		$vo_gotovo=mysqli_real_escape_string($handle,$vo_gotovo);
	}else{$vo_gotovo='0';}
	
	$prod_m=$_GET['prod_m'];
	$prod_m=mysqli_real_escape_string($handle,$prod_m);
	
	$firma=$_GET['firma'];
	$firma=mysqli_real_escape_string($handle,$firma);
	$_SESSION['firma']=$firma;
	
	$datum = date('Y-m-d H:i:s');
	$_SESSION['datum']=$datum;
	
	$cod_i='02-1';
	
	$m_t=$_SESSION['lgn'];
	
	$query=mysqli_query($handle, 'SELECT max(id) from nar_glava');
	$row=mysqli_fetch_row($query);
	$id=$row[0]+1;
	$_SESSION['glava_id']=$id;
	mysqli_query($handle, "INSERT INTO nar_glava (id,cod_i,firma,datum,prod_m,m_t,komentar,pla,godina) VALUES($id,'$cod_i','$firma','$datum','$prod_m','$m_t','$komentar',$vo_gotovo,'$god')");
	echo ("<SCRIPT LANGUAGE='JavaScript'>
			window.location.href='naracki_grid_mat.php?glava_id=$id'
			</SCRIPT>");

}
?>
</body>
</html>














