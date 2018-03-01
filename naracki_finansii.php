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
$().ready(function() {
    $("#firma").autocomplete("popuni_firma_nar.php", {
        width: 260,
        matchContains: true,
        selectFirst: false
    });
});
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
$god=date("Y");
$datum=date('Y-m-d H:i:s');
$datum=substr($datum,0,10);
genmenu();
echo "<h4 align=center>Финансии</h4>";

$handle=connectwebnal();

$boja=$_SESSION['boja'];
?>

<body bgcolor='<?php echo $_SESSION['boja']; ?>'>
<form method='POST'>
	<table>
	  <tr>
	  	<td align='left'><font size='5'>Фирма:</font></td>
	  	<td><input type='text' style="width:100%; height:40px; font-size:20px" onClick="this.select();" size='30' name='firma' id='firma' value='<?php if (isset($_REQUEST['firma'])){ echo $_REQUEST['firma'];}?>' ></td>
		<td align='right'><font size='4'>Отворени:</font></td>	
		<td><input type='checkbox' <?php if (isset($_POST['otvoreni'])) echo "checked='checked'" ;?>  value='otvoreni' name='otvoreni'/></td>												  
	  </tr>
	  <tr>
	  	<td align='left'><font size='5'>Конто:</font></td>
	  	<td><input type='text' style="width:100%; height:40px; font-size:20px" onClick="this.select();" size='30' name='konto' id='konto' value='<?php if (isset($_REQUEST['konto'])){ echo $_REQUEST['konto'];}?>' ></td>
	  </tr>
  	  <tr>
  		<td><td><input style='height: 70px; width: 100%' type='submit' name='baraj' id='baraj' value='Барај'></td></td>
      </tr>
	</table>
</form>

<?php 
if (isset($_POST['baraj'])){
	
	$firma_arr=explode(' - ',$_POST['firma']);
	$firma=$firma_arr[0];
	$firma=mysqli_real_escape_string($handle, $firma);
	
	if (isset($_POST['konto']) && !EMPTY($_POST['konto'])){
		$konto=$_POST['konto'];
		$konto=mysqli_real_escape_string($handle, $konto);
		
			$aa=$konto;
			$bb=explode(',' , $aa);
			$cc=count($bb);
			
			$po_konto=" and (";
			
			if ($cc==1){
				$po_konto=" and konto like '".$bb[0]."%'";
			}elseif ($cc>1){
				for ($i=0;$i<=$cc-1;$i++){
					$po_konto.=" konto like '".$bb[$i]."%'";
					if ($i<$cc-1){
						$po_konto.=" or ";
					}
				}
				$po_konto=$po_konto.")";
			}
			
	}else{$po_konto='';}
	
	if (isset($_POST['otvoreni']) && !EMPTY($_POST['otvoreni'])){
		$res=mysqli_query($handle, "select sum(sumad) as dolzi, sum(sumap) as pobaruva, sum(sumad-sumap) as za_placa, konto, korisnik , broj, min(diz) as datum, min(datum) as valuta from Yana where
										godina='$god' and korisnik='$firma'  $po_konto  and o_z=' ' group by konto, korisnik,broj");
		
		$tot=mysqli_query($handle,"SELECT SUM(dolzi) as tot_dolzi, sum(pobaruva) as tot_pobaruva, sum(za_placa) as tot_za_placa FROM
										(select sum(sumad) as dolzi, sum(sumap) as pobaruva, sum(sumad-sumap) as za_placa, konto, korisnik , broj, min(diz) as datum, min(datum) as valuta from Yana where
										godina='$god' and korisnik='$firma'  $po_konto  and o_z=' ' group by konto, korisnik,broj) as aa");
	}
	else 
	{
		$res=mysqli_query($handle, "select sum(sumad) as dolzi, sum(sumap) as pobaruva, sum(sumad-sumap) as za_placa, konto, korisnik , broj, min(diz) as datum, min(datum) as valuta from Yana where
										godina='$god' and korisnik='$firma' $po_konto group by konto, korisnik,broj");
		
		$tot=mysqli_query($handle,"SELECT SUM(dolzi) as tot_dolzi, sum(pobaruva) as tot_pobaruva, sum(za_placa) as tot_za_placa FROM
										(select sum(sumad) as dolzi, sum(sumap) as pobaruva, sum(sumad-sumap) as za_placa, konto, korisnik , broj, min(diz) as datum, min(datum) as valuta from Yana where
										godina='$god' and korisnik='$firma' $po_konto group by konto, korisnik,broj) as aa");
	}
	
	//echo $zz;
	
	echo "<table border='2' cellspacing='0' width='100%'>
	<tr>
	<th><font size='4'>Број</font></th>
	<th><font size='4'>Датум</font></th>
	<th><font size='4'>Валута</font></th>
	<th><font size='4'>Должи</font></th>
	<th><font size='4'>Побарува</font></th>
	<th><font size='4'>Остаток</font></th>
	</tr>";
	
	$totali=mysqli_fetch_array($tot);
		echo "<tr>";
		echo "<td><td><td align='center' bgcolor='#B8B8B8'><font size='4'><b>Тотали</b></font></td></td></td>";
		echo "<td align='right' style='color:red' bgcolor='#B8B8B8'><font size='4'>" . number_format($totali['tot_dolzi'],0). "</font></td>";
		echo "<td align='right' style='color:blue' bgcolor='#B8B8B8'><font size='4'>" . number_format($totali['tot_pobaruva'],0) . "</font></td>";
		echo "<td align='right' style='color:red' bgcolor='#B8B8B8'><font size='4'>" . number_format($totali['tot_za_placa'],0). "</font></td>";
		echo "</tr>";
	
	while($row = mysqli_fetch_array($res))
	{
		echo "<tr>";
		echo "<td align='center'><font size='4'>" . $row['broj']. "</font></td>";
		echo "<td align='center'><font size='4'>" . $row['datum'] . "</font></td>";
		echo "<td align='center'><font size='4'>" . $row['valuta'] . "</font></td>";
		echo "<td align='right' style='color:red'><font size='4'>" . number_format($row['dolzi'],0). "</font></td>";
		echo "<td align='right' style='color:blue'><font size='4'>" . number_format($row['pobaruva'],0) . "</font></td>";
		echo "<td align='right' style='color:red'><font size='4'>" . number_format($row['za_placa'],0). "</font></td>";
		echo "</tr>";
	}
	echo "</table>";
	
}
?>
</body>
</html>












