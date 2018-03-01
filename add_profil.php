<?php 

include 'inc/functions.php';
include 'inc/initialize.php';
include_once 'inc/menu.php';

if (!isadmin() || !logged()) {
	redirect('infomng.php');
}

genmenu();
echo "<h4 align=center>Додади профил</h4>";

?>
<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script>
$(document).ready(function(){
    $('#finarep').change(function(){
        if(this.checked)
            $('#ddlfirmi').show();
        else
            $('#ddlfirmi').hide();

    });

    if ($('#finarep').is(':checked')) {
        $("#ddlfirmi").show();
    }
});
</script>
<script>
$(document).ready(function(){
    $('#admin_fina').change(function(){
        if(this.checked)
            $('#ddlfirmi').show();
        else
            $('#ddlfirmi').hide();

    });

    if ($('#admin_fina').is(':checked')) {
        $("#ddlfirmi").show();
    }
});
</script>

<a href="profili.php">Назад</a> <br/><br/>

<fieldset id="addpole" style="border:black 1px solid; width:25%; bgcolor:red;">
<legend align="center" style="font:bold;border: 1px solid black">Додади нов профил</legend>
<form method="post">
<table>
	<tr>
		<td align='left'>Опис:</td>
	</tr>
	<tr>
		<td>
			<input name="dsc" required type="text" id="dsc" size="25"/>
		</td>
	</tr>
	<tr>
		<td><input type='checkbox' name='bskart' id='bskart'>Картички</td>
	</tr>
	<tr>
		<td><input type='checkbox' name='nalozi' id='nalozi'>Барања</td>
	</tr>
	<tr>
		<td><input type='checkbox' name='admin_nalozi' id='admin_nalozi'>Админ за барања</td>
	</tr>
	<tr>
		<td><input type='checkbox' name='finarep' id='finarep'>Финансии</td>
	</tr>
	<tr>
		<td><input type='checkbox' name='admin_fina' id='admin_fina'>Админ за финансии</td>
	</tr>
	<tr>
		<td>
			<div id='ddlfirmi' name='ddlfirmi' style="display:none">
				<select id='firma' name='firma[]' multiple size='3'>
					<?php 
						$handle=connectweb();
						$sql = "select concat(id,' - ',dsc) as celo from cmp";
						$rsd = mysqli_query($handle,$sql);
						while($rs = mysqli_fetch_row($rsd)) {
    						echo "<option>$rs[0]</option>";
						}
					?>
				</select>
			</div>
		</td>
	</tr>
	<tr>
		<td></td>
		<td align='right'><input type='submit' value="Зачувај"></td>
	<tr>
</table>
</form>
</fieldset>
</html>
<?php 
$handle=connectweb();
$query=mysqli_query($handle, 'SELECT max(id) from profil');
$row=mysqli_fetch_row($query);
$idid=$row[0]+1;
$idid=mysqli_real_escape_string($handle,$idid);
if (isset($_POST['dsc']) && !EMPTY($_POST['dsc'])){
	$dsc=$_POST['dsc'];
	
	if (!EMPTY($_POST['bskart'])){
		$bskart=1;
		mysqli_query($handle,"INSERT INTO menu(id_profil, ind, href, dsc, class)
					VALUES ($idid, 2, 'izvod.php', 'Извештај за продажба', 'menulink')");
	} else {$bskart=0;}
	
	if (!EMPTY($_POST['nalozi'])){
		$nalozi=1;
		mysqli_query($handle,"INSERT INTO menu(id_profil, ind, href, dsc, class)
				VALUES ($idid, 4, 'mngprikaz_nalozi.php', 'Барања', 'menulink')");
	} else {$nalozi=0;}
	
	if (!EMPTY($_POST['finarep'])){
		$finarep=1;
		mysqli_query($handle,"INSERT INTO menu(id_profil, ind, href, dsc, class)
					VALUES ($idid, 3, 'finansii.php', 'Финансиски извештаи', 'menulink')");
	} else {$finarep=0;}
	
	if (!EMPTY($_POST['admin_nalozi'])){
		$nalozi=1;
		$admin_nalozi=1;
		mysqli_query($handle,"INSERT INTO menu(id_profil, ind, href, dsc, class)
				VALUES ($idid, 4, 'mngprikaz_nalozi.php', 'Барања', 'menulink')");
	}else {$admin_nalozi=0;}
	
	if (!EMPTY($_POST['admin_fina'])){
		$finarep=1;
		$admin_fina=1;
		mysqli_query($handle,"INSERT INTO menu(id_profil, ind, href, dsc, class)
				VALUES ($idid, 3, 'finansii.php', 'Финансиски извештаи', 'menulink')");
	}else {$admin_fina=0;}
	
	mysqli_query($handle,"INSERT INTO menu(id_profil, ind, href, dsc, class) 
								VALUES ($idid, 1, 'index.php', 'Почетна', 'menulink')");	
	mysqli_query($handle,"INSERT INTO menu(id_profil, ind, href, dsc, class)
								VALUES ($idid, 5, 'info.php', 'Соопштенија', 'menulink')");
	mysqli_query($handle,"INSERT INTO menu(id_profil, ind, href, dsc, class)
								VALUES ($idid, 6, 'optprof.php', 'Промена на лозинка', 'menulink')");
	mysqli_query($handle,"INSERT INTO menu(id_profil, ind, href, dsc, class)
								VALUES ($idid, 7, 'help.php', 'Помош', 'menulink')");
	
	if (isset($_POST['firma'])){
		foreach ($_POST['firma'] as $firmi)
			$firma[]=explode(' - ' , $firmi);
		foreach ($firma as $fir){				
			$fir[0]=mysqli_real_escape_string($handle, $fir[0]);
			mysqli_query($handle,"INSERT INTO profilcmp(id_profil,id_cmp) VALUES('$idid','$fir[0]')");
		}
	}
	$dsc=mysqli_real_escape_string($handle,$dsc);
	mysqli_query($handle, "INSERT INTO profil(id,dsc,bskart,finarep,nalozi,admin_nalozi,admin_fina) VALUES($idid,'$dsc',$bskart,$finarep,$nalozi,$admin_nalozi,$admin_fina)");
	mysqli_query($handle, "INSERT INTO profilcmp(id_profil,id_cmp) VALUES('$idid','x00001')"); //zakodirano e za id_cmp
	echo ("<SCRIPT LANGUAGE='JavaScript'>
			window.alert('Профилот е додаден.')
			window.location.href='profili.php'
			</SCRIPT>");
}
	

?>