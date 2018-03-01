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
echo "<h1 align=center>Промет</h1>";


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
if (isset($_POST['org_e']) && !EMPTY($_POST['org_e'])){
	$org_e=$_POST['org_e'];
	$string_org_e=" and org_e=$org_e ";
}else {$string_org_e="";}
if (isset($_POST['tip_pla']) && !EMPTY($_POST['tip_pla'])){
	$tip_pla=$_POST['tip_pla'];
	$string_tip_pla=" and tip_pla=$tip_pla ";
}else {$string_tip_pla="";}

$aa="'".$god."-".$mesec."-".$den."'";
if ($oddat==$aa && $dodat==$aa){
	echo "<h2 align=center>за денешен ден</h2>";
}else{
	echo "<h2 align=center>во период од $oddat до $dodat</h2>";
}

if(isset($_POST['tot_datum']) && !EMPTY($_POST['tot_datum']) && EMPTY($_POST['tot_plakjanja'])){
	$sel="SELECT sum(iznos) as iznos, org_e.opis as org_opis, datum FROM org_pla
				LEFT JOIN org_e on org_e=org_e.cod
				WHERE datum between $oddat and $dodat $string_org_e $string_tip_pla  group by org_e,datum order by datum";
	$rez=mysqli_query($handle, $sel);
}
elseif(isset($_POST['tot_plakjanja']) && !EMPTY($_POST['tot_plakjanja']) && EMPTY($_POST['tot_datum'])){
	$sel="SELECT sum(iznos) as iznos, org_e.opis as org_opis, platip.dsc as platip_opis FROM org_pla
				LEFT JOIN org_e on org_e=org_e.cod
				LEFT JOIN platip on platip.cod=tip_pla
				WHERE datum between $oddat and $dodat $string_org_e $string_tip_pla  group by org_e,tip_pla";
	$rez=mysqli_query($handle, $sel);
}
elseif(isset($_POST['tot_plakjanja']) && !EMPTY($_POST['tot_plakjanja']) && isset($_POST['tot_datum']) && !EMPTY($_POST['tot_datum'])){
	$sel="SELECT sum(iznos) as iznos, org_e.opis as org_opis, platip.dsc as platip_opis, datum FROM org_pla
				LEFT JOIN org_e on org_e=org_e.cod
				LEFT JOIN platip on platip.cod=tip_pla
				WHERE datum between $oddat and $dodat $string_org_e $string_tip_pla  group by org_e,datum,tip_pla order by datum";
	$rez=mysqli_query($handle, $sel);
}
else{
	$sel="SELECT sum(iznos) as iznos, org_e.opis as org_opis FROM org_pla 
	 			LEFT JOIN org_e on org_e=org_e.cod
	 			WHERE datum between $oddat and $dodat $string_org_e $string_tip_pla  group by org_e";
	$rez=mysqli_query($handle, $sel);
}

//echo $sel;
$orged_sel=mysqli_query($handle, 'SELECT cod,opis from org_e');
$platip_sel=mysqli_query($handle, 'SELECT cod,dsc from platip');
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
			<td width='47%' align='center'><b>Орг.Ед.</b></br>
				<select style='font-size: 25px' name='org_e' id='org_e'>
					<option> </option>
					<?php while($org=mysqli_fetch_row($orged_sel)){
								echo "<option value='$org[0]'>$org[1]</option>";
					} ?>
				</select>
			</td>
			<td width='26%' align='right'><b>Тот. по датум:</b><input type='checkbox' value='tot_datum' name='tot_datum' <?php if (isset($_POST['tot_datum'])) echo "checked='checked'" ;?>>
		</tr>
		<tr>	
			<td width='27%'><b>До датум:</b></br>
				<input style='font-size: 25px' type="text" onClick="this.select();" name="DoDen"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['DoDen'])){ echo $_REQUEST['DoDen'];} else {echo $den;}?>'>
				<input style='font-size: 25px' type="text" onClick="this.select();" name="DoMesec"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['DoMesec'])){ echo $_REQUEST['DoMesec'];} else {echo $mesec;}?>'> 
				<input style='font-size: 25px' type="text" onClick="this.select();" name="DoGodina"  maxlength = "4" size="4" value='<?php if (isset($_REQUEST['DoGodina'])){ echo $_REQUEST['DoGodina'];} else {echo $god;}?>'>
			</td>
			<td width='47%' align='center'><b>Тип пла.</b></br>
				<select style='font-size: 25px' id='tip_pla' name='tip_pla'>
					<option> </option>
					<?php while($platip=mysqli_fetch_row($platip_sel)){
								echo "<option value='$platip[0]'>$platip[1]</option>";
					} ?>
				</select>
			</td>
			<td width='26%' align='right'><b>Тот. по плаќања:</b><input value='tot_plakjanja' name='tot_plakjanja' type='checkbox' <?php if (isset($_POST['tot_plakjanja'])) echo "checked='checked'" ;?>>
		</tr>
		<tr style='height:70px'>
			<td align='left'><input style='font-size: 25px;height:100%; width:100%;' type="submit" id='Baraj' name="Baraj" value="Барај"/></td>
		</tr>
	</table>
</form>

<?php
if(isset($_POST['tot_datum']) && !EMPTY($_POST['tot_datum']) && EMPTY($_POST['tot_plakjanja'])){
	$total=0;
	echo "<table border='1' width='60%' align = 'center' cellspacing='0' style='font-size: 25px'>
	<tr>
	<th>Орг. единица</th>
	<th>Датум</th>
	<th>Износ</th>
	</tr>";
	
	while ($row=mysqli_fetch_array($rez)){
		echo "<tr>";
		echo "<td  align = 'center' style='height: 70%; width: 40%'>" . $row['org_opis'] ."</td>";
		echo "<td  align = 'center' style='height: 70%; width: 30%'>" . $row['datum'] ."</td>";
		echo "<td  align = 'right' style='height: 70%; width: 40%'>" . number_format($row['iznos'],0) . "</td>";
		echo "</tr>";
	
		$total=$row['iznos'] + $total;
	}
	echo "<tr>";
	echo "<td  align = 'center' style='height: 70%; width: 40%'><b>ТОТАЛ</b></td>";
	echo "<td><td  align = 'right' style='height: 70%; width: 40%'><b>" . number_format($total,0) . "</b></td></td>";
	echo "</tr>";
	
	echo "</table>";
}
elseif(isset($_POST['tot_plakjanja']) && !EMPTY($_POST['tot_plakjanja']) && EMPTY($_POST['tot_datum'])){
	$total=0;
	echo "<table border='1' width='60%' align = 'center' cellspacing='0' style='font-size: 25px'>
	<tr>
	<th>Орг. единица</th>
	<th>Тип на плаќања</th>
	<th>Износ</th>
	</tr>";
	
	while ($row=mysqli_fetch_array($rez)){
		echo "<tr>";
		echo "<td  align = 'center' style='height: 70%; width: 40%'>" . $row['org_opis'] ."</td>";
		echo "<td  align = 'center' style='height: 70%; width: 30%'>" . $row['platip_opis'] ."</td>";
		echo "<td  align = 'right' style='height: 70%; width: 40%'>" . number_format($row['iznos'],0) . "</td>";
		echo "</tr>";
	
		$total=$row['iznos'] + $total;
	}
	echo "<tr>";
	echo "<td  align = 'center' style='height: 70%; width: 40%'><b>ТОТАЛ</b></td>";
	echo "<td><td  align = 'right' style='height: 70%; width: 40%'><b>" . number_format($total,0) . "</b></td></td>";
	echo "</tr>";
	
	echo "</table>";
}
elseif(isset($_POST['tot_plakjanja']) && !EMPTY($_POST['tot_plakjanja']) && isset($_POST['tot_datum']) && !EMPTY($_POST['tot_datum'])){
	$total=0;
	echo "<table border='1' width='70%' align = 'center' cellspacing='0' style='font-size: 25px'>
	<tr>
	<th>Орг. единица</th>
	<th>Тип на плаќања</th>
	<th>Датум</th>
	<th>Износ</th>
	</tr>";
	
	while ($row=mysqli_fetch_array($rez)){
		echo "<tr>";
		echo "<td  align = 'center' style='height: 70%; width: 30%'>" . $row['org_opis'] ."</td>";
		echo "<td  align = 'center' style='height: 70%; width: 20%'>" . $row['platip_opis'] ."</td>";
		echo "<td  align = 'center' style='height: 70%; width: 20%'>" . $row['datum'] ."</td>";
		echo "<td  align = 'right' style='height: 70%; width: 50%'>" . number_format($row['iznos'],0) . "</td>";
		echo "</tr>";
	
		$total=$row['iznos'] + $total;
	}
	echo "<tr>";
	echo "<td  align = 'center' style='height: 70%; width: 40%'><b>ТОТАЛ</b></td>";
	echo "<td><td><td  align = 'right' style='height: 70%; width: 50%'><b>" . number_format($total,0) . "</b></td></td></td>";
	echo "</tr>";
	
	echo "</table>";
}
else{
	$total=0;
	echo "<table border='1' width='60%' align = 'center' cellspacing='0' style='font-size: 25px'>
	<tr>
		<th>Орг. единица</th>
		<th>Износ</th>
	</tr>";
	
	while ($row=mysqli_fetch_array($rez)){
		echo "<tr>";
		echo "<td  align = 'center' style='height: 70%; width: 50%'>" . $row['org_opis'] ."</td>";
		echo "<td  align = 'right' style='height: 70%; width: 50%'>" . number_format($row['iznos'],0) . "</td>";
		echo "</tr>";
		
		$total=$row['iznos'] + $total;
	}
	echo "<tr>";
	echo "<td  align = 'center' style='height: 70%; width: 50%'><b>ТОТАЛ</b></td>";
	echo "<td  align = 'right' style='height: 70%; width: 50%'><b>" . number_format($total,0) . "</b></td>";
	echo "</tr>";
	
	echo "</table>";
}
?>
</html>