<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script src="js/functions.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/jquery-2.0.3.min.js"></script>          
<script type="text/javascript" src="js/jquery.js"></script>
<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />
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
	font-size:15px;
	font-weight:bold;
}
</style>
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
	
	
	if (isset($_POST['bss']) && isset($_POST['karts']) && isset($_POST['oddat']) && isset($_POST['dodat'])){
		$oddat=$_POST['oddat'];
		$dodat=$_POST['dodat'];
		$bss=$_POST['bss'];
		$karts=$_POST['karts'];
		
		$datumod=substr($oddat,0,4).'-'.substr($oddat,4,2).'-'.substr($oddat,6,2);
		$datumdo=substr($dodat,0,4).'-'.substr($dodat,4,2).'-'.substr($dodat,6,2);
		
		echo "<p><b>За објекти:</b> ".$bss."</p>";
		echo "<p><b>За карти:</b> ".$karts."</p>";
		echo "<p><b>За датум од:</b> ".$datumod." <b>до:</b> ".$datumdo."</p>";
	
		
	$cmd="SELECT minkilo, maxkilo, lkk_broj, avtomobil, artikl, (maxkilo-minkilo) as izminati, (kolicina-kol) as finalkol, ((maxkilo-minkilo)/(kolicina-kol)) as prosek, oddat, dodat FROM 

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
			on aa.dodat=dd.t_dt and aa.artgrup=dd.grupa and aa.avtomobil=dd.car) as FF";
	
	
	$rezult=mysqli_query($handle, $cmd);
	
	
?>
<form method='POST' action="xls_izvodsrvr.php" target="_blank">
<input type="hidden" name="oddat" value="<?php echo $_POST['oddat']?>">
<input type="hidden" name="dodat" value="<?php echo $_POST['dodat']?>">
<input type="hidden" name="bss" value="<?php echo $_POST['bss']?>">
<input type="hidden" name="karts" value="<?php echo $_POST['karts']?>">
	<input type="submit" value="Excel" target='_blank'>
</form>
	
<?php	
	echo "<table class='tabelata'>
			<tr class='redot'>
			<th>Карта</th>
			<th>Регистрација</th>
			<th>Вид гориво</th>
			<th>Датум на прво точење</th>
			<th align='right'>Километри</th>
			<th>Датум на последно точење</th>
			<th align='right'>Километри</th>
			<th align='right'>Изминати километри</th>
			<th align='right'>Количество</th>
			<th align='right'>Просечна потрошувачка</th>
			</tr>";
	
	while ($row=mysqli_fetch_array($rezult)){
		
		echo "<tr>";
		echo "<td align='center' style=font-size:15px>" . $row['lkk_broj'] . "</td>";
		echo "<td align='center' style=font-size:15px>" . $row['avtomobil'] . "</td>";
		echo "<td align='center' style=font-size:15px>" . $row['artikl'] . "</td>";
		echo "<td align='center' style=font-size:15px>" . $row['oddat'] . "</td>";
		echo "<td align='right' style=font-size:15px>" . number_format($row['minkilo'],0) . "</td>";
		echo "<td align='center' style=font-size:15px>" . $row['dodat'] . "</td>";
		echo "<td align='right' style=font-size:15px>" . number_format($row['maxkilo'],0) . "</td>";
		echo "<td align='right' style=font-size:15px>" . number_format($row['izminati'],0) . "</td>";
		echo "<td align='right' style=font-size:15px>" . number_format($row['finalkol'],2) . "</td>";
		echo "<td align='right' style=font-size:15px>" . number_format($row['prosek'],2) . "</td>";
		echo "</tr>";
		
	}
	echo "</table>";
}
	
?>

</html>