<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<!-- <link href="bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css" /> -->
<!-- <link href="bootstrap/css/bootstrap-theme.css" rel="stylesheet" type="text/css" /> -->
<!-- <script src="bootstrap/js/bootstrap.php"></script> -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/jquery-2.0.3.min.js"></script>          
<script src="js/functions.js"></script>
<script type="text/javascript" src="js/jquery.js"></script>
<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />

<script>
$(document).ready(function(){
	$("#opcii").click(function(){
	    $("#tabela_opcii").toggle();
	}); 
});
</script>

<?php
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!logged()) redirect('index.php?rx=ihr_analitika');
if (finarep()==0){
	echo ("<SCRIPT LANGUAGE='JavaScript'>
			window.alert('Немате дозвола за пристап.')
			window.location.href='index.php'
			</SCRIPT>");
}
genmenu();
echo "<h1 align=center>Преглед на искористеност</h1>";


$den=date('d');
$mesec=date('m');
$god=date('Y');

$handle = connectana();

if(!EMPTY($_POST['OdDen']) && !EMPTY($_POST['OdMesec']) && !EMPTY($_POST['OdGodina']))
{
	$strip_odgodina=mysqli_real_escape_string($handle,$_POST['OdGodina']);
	$strip_odmesec=mysqli_real_escape_string($handle,$_POST['OdMesec']);
	$strip_odden=mysqli_real_escape_string($handle,$_POST['OdDen']);
	$oddat="'".$strip_odgodina."-".$strip_odmesec."-".$strip_odden."'";
}else {$oddat="'$god-$mesec-$den'";}
if(!EMPTY($_POST['DoDen']) && !EMPTY($_POST['DoMesec']) && !EMPTY($_POST['DoGodina']))
{
	$strip_dogodina=mysqli_real_escape_string($handle,$_POST['DoGodina']);
	$strip_domesec=mysqli_real_escape_string($handle,$_POST['DoMesec']);
	$strip_doden=mysqli_real_escape_string($handle,$_POST['DoDen']);
	$dodat="'".$strip_dogodina."-".$strip_domesec."-".$strip_doden."'";
}else {$dodat="'$god-$mesec-$den'";}
if (isset($_POST['grupa_e']) && !EMPTY($_POST['grupa_e'])){
	$grupa_e=$_POST['grupa_e'];
	$string_grupa_e=" and grupa_e=$grupa_e ";
}else {$string_grupa_e="";}

$aa="'".$god."-".$mesec."-".$den."'";
if ($oddat==$aa && $dodat==$aa){
	echo "<h2 align=center>за денешен ден</h2>";
}else{
	echo "<h2 align=center>во период од $oddat до $dodat</h2>";
}

if(isset($_POST['totali']) && !EMPTY($_POST['totali'])){
	$sel="select sum(vk_sobi) as vk_sobi, sum(isk_sobi) as isk_sobi,
			grpsee.dsc from iskoristenost
			left join grpsee on grpsee.cod=iskoristenost.grupa_e
			WHERE datum between $oddat and $dodat $string_grupa_e
			group by grupa_e";
	$rez=mysqli_query($handle, $sel);
}
else{
	$sel="select grpsee.dsc, datum, vk_sobi, isk_sobi from iskoristenost
			left join grpsee on grpsee.cod=iskoristenost.grupa_e
			WHERE datum between $oddat and $dodat $string_grupa_e
			group by grupa_e,datum";
	$rez=mysqli_query($handle, $sel);
}


//echo $sel;

$grupa_sel=mysqli_query($handle, 'SELECT cod,dsc from grpsee');
?>
<button style='font-size: 25px' type="button" id="opcii">Филтер</button>
<form method='POST'>
	<table id="tabela_opcii" bgcolor="#DEEBF5" width='100%' align = 'center' cellspacing='0' style='font-size: 25px;display:none'>
		<tr>
			<td width='27%'><b>Од датум:</b></br>
				<input style='font-size: 25px' type="text" onClick="this.select();" name="OdDen"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['OdDen'])){ echo $_REQUEST['OdDen'];} else {echo $den;}?>'>
				<input style='font-size: 25px' type="text" onClick="this.select();" name="OdMesec"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['OdMesec'])){ echo $_REQUEST['OdMesec'];} else {echo $mesec;}?>'> 
				<input style='font-size: 25px' type="text" onClick="this.select();" name="OdGodina"  maxlength = "4" size="4" value='<?php if (isset($_REQUEST['OdGodina'])){ echo $_REQUEST['OdGodina'];} else {echo $god;}?>'>
			</td>
			<td width='47%' align='center'><b>Објект</b></br>
				<select style='font-size: 25px' name='grupa_e' id='grupa_e'>
					<option> </option>
					<?php while($grp=mysqli_fetch_row($grupa_sel)){
								echo "<option value='$grp[0]'>$grp[1]</option>";
					} ?>
				</select>
			</td>
			<td width='26%' align='center'><b>Тотали:</b><input value='totali' name='totali' type='checkbox' checked='checked' <?php if (isset($_POST['totali'])) echo "checked='checked'" ;?>>
		</tr>
		<tr>	
			<td width='27%'><b>До датум:</b></br>
				<input style='font-size: 25px' type="text" onClick="this.select();" name="DoDen"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['DoDen'])){ echo $_REQUEST['DoDen'];} else {echo $den;}?>'>
				<input style='font-size: 25px' type="text" onClick="this.select();" name="DoMesec"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['DoMesec'])){ echo $_REQUEST['DoMesec'];} else {echo $mesec;}?>'> 
				<input style='font-size: 25px' type="text" onClick="this.select();" name="DoGodina"  maxlength = "4" size="4" value='<?php if (isset($_REQUEST['DoGodina'])){ echo $_REQUEST['DoGodina'];} else {echo $god;}?>'>
			</td>
			
		</tr>
		<tr style='height:70px'>
			<td align='left'><input style='font-size: 25px;height:100%; width:100%;' type="submit" id='Baraj' name="Baraj" value="Барај"/></td>
		</tr>
	</table>
</form>

<?php 
if (isset($_POST['totali']) && !EMPTY($_POST['totali'])){
	echo "<table border='1' width='60%' align = 'center' cellspacing='0' style='font-size: 25px'>
	<tr>
	<th>Објект</th>
	<th>Вк.Соби</th>
	<th>Искористени</th>
	<th>Процент</th>
	</tr>";
}
else{
	echo "<table border='1' width='60%' align = 'center' cellspacing='0' style='font-size: 25px'>
	<tr>
	<th>Објект</th>
	<th>Датум</th>
	<th>Вк.Соби</th>
	<th>Искористени</th>
	<th>Процент</th>
	</tr>";
}

while ($row=mysqli_fetch_array($rez)){
	
	if (isset($_POST['totali']) && !EMPTY($_POST['totali'])){
		echo "<tr>";
		echo "<td  align = 'center' style='height: 70%; width: 20%'>" . $row['dsc'] ."</td>";
		echo "<td  align = 'center' style='height: 70%; width: 20%'>" . $row['vk_sobi'] ."</td>";
		echo "<td  align = 'center' style='height: 70%; width: 20%'>" . $row['isk_sobi'] . "</td>";
		echo "<td  align = 'center' style='height: 70%; width: 20%'>" . round($row['isk_sobi']/$row['vk_sobi']*100) . " %</td>";
		echo "</tr>";
	}
	else{
		echo "<tr>";
		echo "<td  align = 'center' style='height: 70%; width: 20%'>" . $row['dsc'] ."</td>";
		echo "<td  align = 'center' style='height: 70%; width: 20%'>" . $row['datum'] . "</td>";
		echo "<td  align = 'center' style='height: 70%; width: 20%'>" . $row['vk_sobi'] ."</td>";
		echo "<td  align = 'center' style='height: 70%; width: 20%'>" . $row['isk_sobi'] . "</td>";
		echo "<td  align = 'center' style='height: 70%; width: 20%'>" . round($row['isk_sobi']/$row['vk_sobi']*100) . " %</td>";
		echo "</tr>";
	}

}

echo "</table>";

?>
</html>