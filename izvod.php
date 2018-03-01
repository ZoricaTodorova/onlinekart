<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<?php
	include_once 'inc/functions.php';
	include_once 'inc/initialize.php';
	include_once 'inc/menu.php';
	
	genmenu();
	
	if (!logged()) redirect('index.php?rx=izvod');
 	if (bskart()==0){
  		echo ("<SCRIPT LANGUAGE='JavaScript'>
  				window.alert('Немате дозвола за пристап.')
  				window.location.href='index.php'
  				</SCRIPT>");
 	}
 	
 	fin_unset();
 	
 	$handle=connectkart();
 	
 	$cmd=mysqli_query($handle, "SELECT opis from firmi where cod='".$_SESSION['idcmp']."'");
 	$firma=mysqli_fetch_row($cmd);
 	
 	echo "<h4 align=center>Извештај за продажба за $firma[0]</h4>";
?>
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
		<input type="text" onClick="this.select();" id="OdMesec" name="OdMesec" value="01" maxlength = "2" size="2"> 
		<input type="text" onClick="this.select();" id="OdGodina" name="OdGodina" value="2014" maxlength = "4" size="4">
	<td>До датум:</td>
	<td><input type="text" onClick="this.select();" id="DoDen" name="DoDen" value="31" maxlength = "2" size="2">
		<input type="text" onClick="this.select();" id="DoMesec" name="DoMesec" value="12" maxlength = "2" size="2"> 
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