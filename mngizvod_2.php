<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<?php
	include_once 'inc/functions.php';
	include_once 'inc/initialize.php';
	include_once 'inc/menu.php';
	
	genmenu();
	
	if (!logged()) redirect('mngizvod.php');
 	if (bskart()==0){
  		echo ("<SCRIPT LANGUAGE='JavaScript'>
  				window.alert('Немате дозвола за пристап.')
  				window.location.href='index.php'
  				</SCRIPT>");
 	}
 	
 	
 	$handle=connectkart();
 	$cmd=mysqli_query($handle, "SELECT opis from firmi where cod='".$_SESSION['idcmp']."'");
 	$firma=mysqli_fetch_row($cmd);
 	
 	echo "<h4 align=center>Извештај за продажба за $firma[0]</h4>";
 	
 	//include 'inc/stat_klient.php';
 	
 	$mesec = date("m");
 	$den=date("d");
 	$godina=date("Y");
 	$cIdCmp = $_SESSION['idcmp'];
 	
 	$web=connectweb();
 	$status_cmd=mysqli_query($web, "Select * from vid_status where vid=11 order by rbr");
 	
?>
<body>
<fieldset style="border:black 1px solid; width:450">
<legend align="left" style="font:bold;border: 1px solid black">Статус на клиентот</legend>
<?php 

echo "<table>";
while ($stat=mysqli_fetch_array($status_cmd)){
	
	$tabela=$stat['tabela'];
	$doct=$stat['doct'];
	$doct_arr=explode(',', $doct);
	
	if (EMPTY($stat['doct'])){
		$cmd = "select (round(sum(sgn*iznos), 2)) as limito from $tabela where firma = '$cIdCmp'";
		$res = mysqli_query($handle, $cmd);
		$row=mysqli_fetch_row($res);
	}
	elseif(substr($doct_arr[0],0,1)=='#'){
		$dd=substr($doct_arr[0],1);
		foreach($doct_arr as $doc){
			$dd=$dd.','.substr($doc,1);
		}
		$cmd = "select (round(sum(sgn*iznos), 2)) as limito from $tabela where doct not in ($dd) and firma = '$cIdCmp'";
		$res = mysqli_query($handle, $cmd);
		$row=mysqli_fetch_row($res);
	}
	else{
		$cmd = "select (round(sum(sgn*iznos), 2)) as limito from $tabela where doct in ($doct) and firma = '$cIdCmp'";
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

$cmd = "select max(wdt) as limito from $tabela where firma = '$cIdCmp'";
$res = mysqli_query($handle, $cmd);
$row=mysqli_fetch_row($res);

echo "<tr>";
echo "<th align='left'>Датум и час на ажурирање:</th>";
echo "<td>".$row[0]."</td>";
echo "</tr>";

echo "</table>";

?>
</fieldset>
</body>


<script src="js/functions.js"></script>
<div>
</br>
Ве молиме одберете објект и карти</div>
<form name="pickvod" method="POST" action='izvodrep.php'>
<table>
<tr>
<td>
	<table>
	<tr>
		<td>Избор на објекти</td>
		<td></td>
		<td>Избрани објекти</td>
	</tr>
	<tr>
		<td>
			<select id="bsselector" name="bsselector" ondblclick="mvoptsel2sel('bsselector', 'bsselected')" size="10" multiple="1" style="width: 140px;">
			<?php echo populatebsobjopt();?>
			</select>
		</td>
		<td>
			<table>
				<tr><input type="button" value="> " onclick="mvopts2sright('bsselector', 'bsselected');"></tr>
				<tr><br></tr>
				<tr><input type="button" value="< " onclick="mvopts2sleft('bsselected', 'bsselector');"></tr>
				<tr><br></tr>
				<tr><input type="button" value="<=" onclick="moveall('bsselected', 'bsselector');"></tr>
			</table>
		</td>
		<td>
			<select id="bsselected" name="bsselected" ondblclick="mvoptsel2sel('bsselected', 'bsselector')" size="10" multiple="1" style="width: 140px;">
				<option value="###">СИТЕ</option>
			</select>
		</td>
	</tr>
	</table>
</td>
<td>
	<table>
	<tr>
		<tr>
			<td>Избор на карти</td>
			<td></td>
			<td>Избрани карти</td>
		</tr>
		<td>
			<select id="kartselector" name="kartselector" ondblclick="mvoptsel2sel('kartselector', 'kartselected')" size="10" multiple="1" style="width: 140px;">
			<?php echo populatekartopt();?>
			</select>
		</td>
		<td>
			<table>
				<tr><input type="button" value="> " onclick="mvopts2sright('kartselector', 'kartselected');"></tr>
				<tr></br></tr>
				<tr><input type="button" value="< " onclick="mvopts2sleft('kartselected', 'kartselector');"></tr>
				<tr></br></tr>				
				<tr><input type="button" value="<=" onclick="moveall('kartselected', 'kartselector');"></tr>
			</table>
		</td>
		<td>
			<select id="kartselected" name="kartselected" ondblclick="mvoptsel2sel('kartselected', 'kartselector')" size="10" multiple="1" style="width: 140px;">
				<option value="###">СИТЕ</option>
			</select>
		</td>
	</tr>
	<tr><input type="hidden" name="testo"></tr>
	<tr><input type="hidden" name="testo2"></tr>
	</table>
</td>
</tr>
</table>
<div>
<br></br>



Ве молиме одберете период. Воведените податоци треба да бидат во формат DD-MM-YYYY.

<table>
	<tr>
	<td>Од датум:</td>
	<td><input type="text" onClick="this.select();" id="OdDen" name="OdDen" value="01" maxlength = "2" size="2">
		<input type="text" onClick="this.select();" id="OdMesec" name="OdMesec" value="<?php echo $mesec;?>" maxlength = "2" size="2"> 
		<input type="text" onClick="this.select();" id="OdGodina" name="OdGodina" value="2014" maxlength = "4" size="4">
	<td>До датум:</td>
	<td><input type="text" onClick="this.select();" id="DoDen" name="DoDen" value="<?php echo $den;?>" maxlength = "2" size="2">
		<input type="text" onClick="this.select();" id="DoMesec" name="DoMesec" value="<?php echo $mesec;?>" maxlength = "2" size="2"> 
		<input type="text" onClick="this.select();" id="DoGodina" name="DoGodina" value="<?php echo date("Y");?>" maxlength = "4" size="4"></td>
	<td><input type="button" id="click" name="click" value="Прикажи" onclick="submitpickvod()"></td>
	</tr>
	<tr>
		<td>SAP број:</td><td><input type="text" id="SAP" name="SAP"></td>
	</tr>
</table>
</form>
</div>

<?php
	if (isset($_POST['resobj']) && isset($_POST['reskart']) && isset($_POST['thaoddat']) && isset($_POST['thadodat']))
	{
		echo genreport($_POST['thaoddat'], $_POST['thadodat'], $_POST['resobj'], $_POST['reskart']);
	}
?>
</html>




