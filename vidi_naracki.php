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
echo "<h4 align=center>Листа на нарачки</h4>";

echo "<a style='font-size: 150%;' href='naracki_lager_mat.php'>Назад</a><br/><br/>";

$handle=connectwebnal();

$boja=$_SESSION['boja'];
$god=date('Y');
$datum = date('Y-m-d H:i:s');
$datum=substr($datum,0,10);

echo "<body bgcolor='$boja'>";

if (isset($_GET['mat']) && !EMPTY($_GET['mat'])){
	$mat=$_GET['mat'];
	$mat=mysqli_real_escape_string($handle,$mat);
	
	$selekt=mysqli_query($handle, "SELECT opis from materijali where cod='$mat' and godina='$god' ");
	$mat_opis=mysqli_fetch_row($selekt);
	
	$cmd1=mysqli_query($handle, "SELECT nar_glava_id,kolicina FROM nar_stavki where mat='$mat' and nar_glava_id in (select id from nar_glava where godina='$god') ");
	
	echo "<font size='5'>за <b>$mat_opis[0]</b></font></br></br>";
	
	echo "<table border='2' cellspacing='0' width='100%'>
	<tr>
	<th style='font-size: 25px;'>Фирма</th>
	<th style='font-size: 25px;'>Продавница</th>
	<th style='font-size: 25px;'>Комерцијалист</th>
	<th style='font-size: 25px;'>Количина</th>
	<th style='font-size: 25px;'>Датум</th>
	</tr>";
	
	while($glava_id=mysqli_fetch_row($cmd1)){
		$cmd2=mysqli_query($handle, "SELECT * FROM nar_glava where id=$glava_id[0]");
		while ($row=mysqli_fetch_array($cmd2)){
			
			$rez1=mysqli_query($handle, "select opis from firmi where cod=".$row['firma']);
			$firma_opis=mysqli_fetch_row($rez1);
			
			$rez2=mysqli_query($handle, "select opis from org_e where cod=".$row['prod_m']);
			$prodm_opis=mysqli_fetch_row($rez2);
			
			$rez3=mysqli_query($handle, "select opis from mesto_trosok where godina= '$god' and cod=".$row['m_t']);
			$komerc_opis=mysqli_fetch_row($rez3);
			
			echo "<tr>";
			echo "<td align='center' style='font-size: 25px;' >" . $row['firma']." - ".$firma_opis[0]."</td>";
			echo "<td align='center' style='font-size: 25px;' >". $row['prod_m']." - ".$prodm_opis[0]. "</td>";
			echo "<td align='center' style='font-size: 25px;' >" . $row['m_t']." - ".$komerc_opis[0]. "</td>";		
			echo "<td align='center' style='font-size: 25px;' >" . number_format($glava_id[1],1) . "</td>";      //$glava_id[1] e pole za kolicina
			echo "<td align='center' style='font-size: 25px;' >" . $row['datum']. "</td>";
			echo "</tr>";
		}
	}
	echo "</table>";
	
}
?>
</body>
</html>