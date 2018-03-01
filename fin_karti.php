<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<?php
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

	if (!logged()) redirect('index.php?rx=fin_karti');
 	if (bskart()==0){
  		echo ("<SCRIPT LANGUAGE='JavaScript'>
  				window.alert('Немате дозвола за пристап.')
  				window.location.href='index.php'
  				</SCRIPT>");
 	}

genmenu();
$handle=connectkart();
$web=connectweb();

$cmd=mysqli_query($handle, "SELECT opis from firmi where cod='".$_SESSION['idcmp']."'");
$firma=mysqli_fetch_row($cmd);
echo "<h4 align=center>Финансиска картица за ".$firma[0]. " - " .$_SESSION['idcmp']."</h4>";

//selectot za dropdown listata za konta
$cmd_vid=mysqli_query($web, "SELECT distinct(vid), opis from vid_konto");

?>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/jquery-2.0.3.min.js"></script>          
<script src="js/functions.js"></script>
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

<form method='POST'>
	<table align='left'>
		<tr>
			<td><b>Година:</b></td>
			<td><input onClick="this.select();" required type='text' name='godina' id='godina' value='<?php if (isset($_REQUEST['godina'])){ echo $_REQUEST['godina'];} else {echo '2014';} ?>'/></td>
		</tr>
		<tr>
			<td><b>Вид:</b></td>
			<td><select name='vid' id='vid'>
			<?php 
				while ($vid=mysqli_fetch_row($cmd_vid)){
					echo "<option value=$vid[0]>$vid[1]</option>";
				}
			?>
						</select></td>
			<td><input type="submit" id="kartica" name="kartica" value="Прикажи"></td>
		</tr>
	</table>
</form>


<?php
if (isset($_POST['kartica'])){

	$handle=connectkart();
	$cIdCmp = $_SESSION['idcmp'];
	$web=connectweb();
	
	if(isset($_POST['godina']) && !EMPTY($_POST['godina']))
	{
		$godina=mysqli_real_escape_string($handle, $_POST['godina']);
	}else {$godina='2014';}
	
	if(isset($_POST['vid']) && !EMPTY($_POST['vid'])){
		$vidot=$_POST['vid'];
		$cmd_vidkonto=mysqli_query($web, "select konto,opis from vid_konto where vid=".$_POST['vid']);
		$konto=mysqli_fetch_row($cmd_vidkonto);
	}
	
	$status_cmd=mysqli_query($web, "Select * from vid_status where vid=$vidot order by rbr");

	echo "<div style='position:absolute; right:5px;'>";
	echo "	<body>
			<fieldset style='border:black 1px solid; width:450'>
			<legend align='left' style='font:bold;border: 1px solid black'>Статус на клиентот</legend>";
	echo "<table>";
	while ($stat=mysqli_fetch_array($status_cmd)){
	
		$tabela=$stat['tabela'];
		$doct=$stat['doct'];
		$doct_arr=explode(',', $doct);
	
		if (EMPTY($stat['doct'])){
			$cmd = "select (round(sum(sumap-sumad), 2)) as limito from $tabela where korisnik = '$cIdCmp' and konto=$konto[0]";
			$res = mysqli_query($handle, $cmd);
			$row=mysqli_fetch_row($res);
		}
		elseif(substr($doct_arr[0],0,1)=='#'){
			$dd=substr($doct_arr[0],1);
			foreach($doct_arr as $doc){
				$dd=$dd.','.substr($doc,1);
			}
			$cmd = "select (round(sum(sumap-sumad), 2)) as limito from $tabela where naldoc not in ($dd) and korisnik = '$cIdCmp' and konto=$konto[0]";
			$res = mysqli_query($handle, $cmd);
			$row=mysqli_fetch_row($res);
		}
		else{
			$cmd = "select (round(sum(sumap-sumad), 2)) as limito from $tabela where naldoc in ($doct) and korisnik = '$cIdCmp' and konto=$konto[0]";
			$res = mysqli_query($handle, $cmd);
			$row=mysqli_fetch_row($res);
		}
		//echo $cmd;
	
		echo "<tr>";
		echo "<th align='left'>".$stat['opis']."</th>";
		if ($row[0]<0){
			echo "<td align='right' style='color:red'>".str_pad(number_format(abs($row[0]),0),20,'.',STR_PAD_LEFT);
		}
		else{
			echo "<td align='right'>".str_pad(number_format($row[0],0),20,'.',STR_PAD_LEFT);
		}
		echo "</td></tr>";
	
	}
	
	if (!empty($tabela)){
		$cmd = "select max(wdt) as limito from $tabela where korisnik = '$cIdCmp'";
		$res = mysqli_query($handle, $cmd);
		$row=mysqli_fetch_row($res);
		
		echo "<tr>";
		echo "<th align='left'>Датум и час на ажурирање:</th>";
		echo "<td>".$row[0]."</td>";
		echo "</tr>";
	}
		
	echo "</table>";
	echo "</fieldset></body>";
	echo "</div>";
	echo "<br><br><br><br><br>";
 	
	// vo zavisnost sto e odbrano da se pravi cmd i linkot za otvoranje na faktura 
	$nalog_cmd=mysqli_query($web, "Select nalog_f from vid_konto where vid=$vidot");
	$nalog=mysqli_fetch_row($nalog_cmd);
	$nalog_arr=explode(',',$nalog[0]);
	
	$nalog_str="naldoc=".$nalog_arr[0];
	for($i=1; $i<count($nalog_arr); $i++){
		$nalog_str=$nalog_str." or naldoc=".$nalog_arr[$i];
	}
		
	if ($vidot==3){
		$cmd=mysqli_query($handle, "select primary_c, 0 as faktura, opis, broj, nalmes, org_e, sumad, sumap, diz, datum
				from fin_exchange where korisnik =".$_SESSION['idcmp']." and godina=$godina and konto=$konto[0] order by diz, nalmes");
	}
	else{
		$cmd=mysqli_query($handle, "select primary_c, if($nalog_str,1,0) as faktura, opis, broj, nalmes, org_e, sumad, sumap, diz, datum 
										from fin_exchange where korisnik =".$_SESSION['idcmp']." and godina=$godina and konto=$konto[0] order by diz, nalmes");
	}
	

	echo "<b>ГОДИНА: $godina </b></br>";
	echo "<b>ВИД: $konto[1] </b><br></br></br></br>";
?>	

<table>
	<form method='POST' action='fpdffin_karti.php' target="_blank">
		<input type="hidden" name="godina_pdf" value="<?php echo $godina?>">
		<input type="hidden" name="vid_pdf" value="<?php echo $_POST['vid']?>">
		<input type="submit" value="PDF" target='_blank'>
	</form>
		
	<form method='POST' action="xls_finkarti.php" target="_blank">
		<input type="hidden" name="godina" value="<?php echo $godina;?>">
		<input type="hidden" name="vid" value="<?php echo $_POST['vid'];?>">
		<input type="submit" value="Excel" target='_blank'>
	</form>
</table>
<br>

<?php
	echo "<table class='tabelata'>
	<tr class='redot'>
	<th>Опис</th>
	<th>Документ</th>
	<th>Орг. ед.</th>
	<th align='right'>Должи</th>
	<th align='right'>Побарува</th>
	<th>Датум</th>
	<th>Валута</th>
	</tr>";

	$vk_dolzi=0;
	$vk_pobaruva=0;
	$ima_nalmes=0;
	$nalmes_dolzi=0;
	$nalmes_pobaruva=0;
	
	while ($row=mysqli_fetch_array($cmd)){
		
		if ($row['nalmes']=='00'){
			$ima_nalmes=1;
			$nalmes_dolzi=$nalmes_dolzi + $row['sumad'];
			$nalmes_pobaruva=$nalmes_pobaruva + $row['sumap'];
			$nalmes_opis=$row['opis'];
			continue;
		}
		
		if ($ima_nalmes==1){
			echo "<tr>";
			echo "<td align='center' style=font-size:15px>". $nalmes_opis ."</td>";
			echo "<td align='center' style=font-size:15px>Салдо од мината година</td>";
			echo "<td><td align='right' style=font-size:15px>" . number_format($nalmes_dolzi,0) . "</td></td>";
			echo "<td align='right' style=font-size:15px>" . number_format($nalmes_pobaruva,0) . "</td>";
			echo "<td align='center' style=font-size:15px>2014-01-01</td>";
			echo "<td align='center' style=font-size:15px>2014-01-01</td>";
			echo "</tr>";
			$ima_nalmes=0;
		}
		
			echo "<tr>";
			echo "<td align='center' style=font-size:15px>" . $row['opis'] . "</td>";
				
			if ($row['faktura']==1){
				echo "<td align='center' style=font-size:15px><a href=\"fakturi.php?broj=".$row['broj']."&godina=$godina\">" . $row['broj'] . "</td>";
			}else{
				echo "<td align='center' style=font-size:15px>". $row['broj'] ."</td>";
			}
			echo "<td align='center' style=font-size:15px>" . $row['org_e'] . "</td>";
			echo "<td align='right' style=font-size:15px>" . number_format($row['sumad'],0) . "</td>";
			echo "<td align='right' style=font-size:15px>" . number_format($row['sumap'],0) . "</td>";
			echo "<td align='center' style=font-size:15px>" . $row['diz'] . "</td>";
			echo "<td align='center' style=font-size:15px>" . $row['datum'] . "</td>";
			echo "</tr>";

			$vk_dolzi=$vk_dolzi + $row['sumad'];
			$vk_pobaruva=$vk_pobaruva + $row['sumap'];
	}
		
	$vk_dolzi= $vk_dolzi + $nalmes_dolzi;
	$vk_pobaruva=$vk_pobaruva + $nalmes_pobaruva;

	echo "<tr><td> </td></tr>";
	echo "<tr bgcolor='#D0D0D0'>";
	echo "<td align='center' style=font-size:15px><b>Вкупно ДОЛЖИ/ПОБАРУВА</b></td>";
	echo "<td><td><td align='right' style=font-size:15px><b>" . number_format($vk_dolzi,0) . "</b></td></td></td>";
	echo "<td align='right' style=font-size:15px><b>" . number_format($vk_pobaruva,0) . "</b></td>";
	echo "<td><td></td></td>";
	echo "</tr>";
		
		echo "<tr bgcolor='#D0D0D0'>";
		if ($vk_dolzi>$vk_pobaruva){
			
			$vk=$vk_dolzi- $vk_pobaruva;
			
			echo "<td align='center' style=font-size:15px><b>САЛДО</b></td>";
			echo "<td><td><td align='right' color='red' style=font-size:15px><font color='red'><b>". number_format($vk,0) ."</b></font></td></td></td>";
			echo "<td align='right' style=font-size:15px><b>0</b></td>";
		}
		elseif ($vk_dolzi<$vk_pobaruva){
			
			$vk=$vk_pobaruva-$vk_dolzi;
			
			echo "<td align='center' style=font-size:15px><b>САЛДО</b></td>";
			echo "<td><td><td align='right' style=font-size:15px><b>0</b></td></td></td>";
			echo "<td align='right' style=font-size:15px><b>" . number_format($vk,0) . "</b></td>";
		}
		else{
			echo "<td align='center' style=font-size:15px><b>САЛДО</b></td>";
			echo "<td><td><td align='right' style=font-size:15px><b>0</b></td></td></td>";
			echo "<td align='right' style=font-size:15px><b>0</b></td>";
		}
		echo "<td><td></td></td>";
		echo "</tr>";
	
		echo "</table>";

}
?>
</html>



