<?php
	include_once 'inc/functions.php';
	include_once 'inc/initialize.php';
	include_once 'inc/menu.php';
	
	if (!logged()) redirect('index.php?rx=izvod');

	genmenu();
	
	$handle=connectkart();
	$cmd=mysqli_query($handle, "SELECT opis from firmi where cod='".$_SESSION['idcmp']."'");
	$firma=mysqli_fetch_row($cmd);
	
	echo "<h4 align=center>Извештај за продажба за $firma[0]</h4>";
	
	
	if (isset($_POST['resobj']) && isset($_POST['reskart']) && isset($_POST['thaoddat']) && isset($_POST['thadodat'])){
		
		$oddat=$_POST['thaoddat'];
		$dodat=$_POST['thadodat'];
		$bss=$_POST['resobj'];
		$karts=$_POST['reskart'];
		
		$datumod=substr($oddat,0,4).'-'.substr($oddat,4,2).'-'.substr($oddat,6,2);
		$datumdo=substr($dodat,0,4).'-'.substr($dodat,4,2).'-'.substr($dodat,6,2);
		
		
		$cmd = "select lkk_broj as karta, materijali.opis as artikl, mat as artgrup, 
					format(sum(kolicina),2) as kolicina, format(sum(iznos),0) as vrednost,
					firmi.cod as klientcod, firmi.opis from bs_exchange 
					left join kartici on kart_br = lkk_broj and bs_exchange.firma=kartici.firma 
					left join firmi on bs_exchange.firma = firmi.cod 
					left join materijali on materijali.cod = bs_exchange.mat and materijali.godina = bs_exchange.godina 
					where t_dt between '$oddat' and '$dodat' and lkk_broj in ($karts) 
					and bs in ($bss) and 
					mat <> '' group by klientcod, artikl with rollup";
		
		$res=mysqli_query($handle, $cmd);
		
		echo "<p><b>За објекти:</b> ".$bss."</p>";
		echo "<p><b>За карти:</b> ".$karts."</p>";
		echo "<p><b>За датум од:</b> ".$datumod." <b>до:</b> ".$datumdo."</p>";
	}
	
?>
<link href="css/default.css" rel="stylesheet" type="text/css" />

<script src="js/functions.js"></script>

<style>
.tabelata
{
	cellspacing="0";
	cellpadding="4";
	align="center";
	rules="cols";
	border="1";
	color:#333333;
	border-color:#666666;
	border-width:1px;
	border-style:solid;
	font-size:20px;
	width:100%;
	border-collapse:collapse;
}
.redot
{
	align="center";
	color:#666666;
	background-color:GhostWhite;
	border-color:White;
	font-size:10px;
	font-weight:bold;
}
</style>

<table>
	<form>
		<input type="checkbox" name="vk_kart" checked id="vk_kart" onclick='hideall1();'>Вкупно карти 
		<input type="checkbox" name="vk_param">Вкупно по дополнителни параметри
		<input type="checkbox" name="vk_prod" checked id="vk_prod" onclick='hideall3();'>Вкупно продажби
	</form>
	
	<form method='POST' action="izvod_srvr.php">
	<input type="hidden" name="oddat" value="<?php echo $_POST['thaoddat']?>">
	<input type="hidden" name="dodat" value="<?php echo $_POST['thadodat']?>">
	<input type="hidden" name="bss" value="<?php echo $_POST['resobj']?>">
	<input type="hidden" name="karts" value="<?php echo $_POST['reskart']?>">
		<input type="submit" value="Средна вредност">
	</form>
	
	<br><br>

	<form method='POST' action="fpdfizvodrep.php" target="_blank">
	<input type="hidden" name="oddat" value="<?php echo $_POST['thaoddat']?>">
	<input type="hidden" name="dodat" value="<?php echo $_POST['thadodat']?>">
	<input type="hidden" name="bss" value="<?php echo $_POST['resobj']?>">
	<input type="hidden" name="karts" value="<?php echo $_POST['reskart']?>">
		<input type="submit" value="PDF" target='_blank'>
	</form>
		
	<form method='POST' action="xls_izvodi.php" target="_blank">
	<input type="hidden" name="oddat" value="<?php echo $_POST['thaoddat']?>">
	<input type="hidden" name="dodat" value="<?php echo $_POST['thadodat']?>">
	<input type="hidden" name="bss" value="<?php echo $_POST['resobj']?>">
	<input type="hidden" name="karts" value="<?php echo $_POST['reskart']?>">
		<input type="submit" value="Excel" target='_blank'>
	</form>
</table>
		
</br>

	<form>
<?php
	
	if (isset($_POST['resobj']) && isset($_POST['reskart']) && isset($_POST['thaoddat']) && isset($_POST['thadodat']))
	{
		echo genreport($_POST['thaoddat'], $_POST['thadodat'], $_POST['resobj'], $_POST['reskart']);
		
		while ($row=mysqli_fetch_array($res)){
			
			if (!EMPTY($row['artikl'])){
				echo "<tr class='redot'>";
				echo "<td align='left' style=font-size:10px>Вкупно за артикл:".$row['artikl']."</td>";
				echo "<td><td><td><td><td><td><td></td></td></td></td></td></td></td>";
				echo "<td align='left' style=font-size:10px>".$row['kolicina']."</td>";
				echo "<td align='left' style=font-size:10px>".$row['vrednost']."</td>";
				echo "<td><td><td><td></td></td></td></td>";
				echo "</tr>";
			}
			
			if (EMPTY($row['klientcod']) && EMPTY($row['artikl'])){
				echo "<tr class='redot'>";
				echo "<td align='left' style=font-size:10px>Вкупно за клиент:".$row['opis']."</td>";
				echo "<td><td><td><td><td><td><td></td></td></td></td></td></td></td>";
				echo "<td align='left' style=font-size:10px>".$row['kolicina']."</td>";
				echo "<td align='left' style=font-size:10px>".$row['vrednost']."</td>";
				echo "<td><td><td><td></td></td></td></td>";
				echo "</tr>";
			}
		}
		
		echo "</table>"; 
	}
?>
	</form>
