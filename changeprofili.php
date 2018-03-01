<link href="css/default.css" rel="stylesheet" type="text/css" />
<?php
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!logged() || !isadmin()) redirect('profili.php');
if (!isset($_GET['id'])) redirect('profili.php');

genmenu();
echo "<h4 align=center>Промени профил</h4>";

if (isset($_GET['id'])){	
	$handle=connectweb();
	$res = mysqli_query($handle, "Select * from profil where id=".$_GET['id']);
	$row=mysqli_fetch_array($res);
	
	if ($row['isadmin']==1){
		echo ("<SCRIPT LANGUAGE='JavaScript'>
				window.alert('Не е дозволена промена на профилот!')
				window.location.href='profili.php'
				</SCRIPT>");
	}
}
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
    } else {
        $("#ddlfirmi").hide();
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
    } else {
        $("#ddlfirmi").hide();
    } 
});
</script>

<br/>
<a href="profili.php">Назад</a>

<fieldset id="addpole" style="border:black 1px solid; width:25%; bgcolor:red;">
<legend align="center" style="font:bold;border: 1px solid black">Промени профил</legend>
<form method="post">
<table>
	<tr>
		<td><input type='text' name='id' readonly hidden value="<?php echo $row['id']; ?>"></td>
	</tr>
	<tr>
		<td align='left'>Опис:</td>
	</tr>
	<tr>
		<td>
			<input name="dsc" required type="text" id="dsc" size="25" value='<?php echo $row['dsc']?>'/>
		</td>
	</tr>
	<tr>
		<td><input type='checkbox' name='bskart' id='bskart' <?php if($row['bskart']==1) echo "checked='checked'"; ?>>Картички</td>
	</tr>
	<tr>
		<td><input type='checkbox' name='nalozi' id='nalozi' <?php if($row['nalozi']==1) echo "checked='checked'"; ?>>Барања</td>
	</tr>
	<tr>
		<td><input type='checkbox' name='admin_nalozi' id='admin_nalozi' <?php if($row['admin_nalozi']==1) echo "checked='checked'"; ?>>Админ за барања</td>
	</tr>
	<tr>
		<td><input type='checkbox' name='finarep' id='finarep' <?php if($row['finarep']==1) echo "checked='checked'"; ?>>Финансии</td>
	</tr>
	<tr>
		<td><input type='checkbox' name='admin_fina' id='admin_fina' <?php if($row['admin_fina']==1) echo "checked='checked'"; ?>>Админ за финансии</td>
	</tr>
	<tr>
		<td>
			<div id='ddlfirmi' name='ddlfirmi' style="display:none">
				<select id='firma' name='firma[]' multiple size='3'>
					<?php 
						$handle=connectweb();
						
						$sql = "select concat(id,' - ',dsc) as celo,id from cmp";
						$rsd = mysqli_query($handle,$sql);
						while($rs = mysqli_fetch_row($rsd)) {
					?>
    					<option <?php $rez=mysqli_query($handle,"Select id_cmp from profilcmp where id_profil=".$_GET['id']);
    									while($cmpid=mysqli_fetch_row($rez)){
    									if ($rs[1]==$cmpid[0]) echo "selected='selected'";}?>>
    					<?php echo $rs[0];?>
    					</option>
						<?php }?>
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

if (isset($_POST['dsc']) && !EMPTY($_POST['dsc'])){
	$handle=connectweb();
	$id=$_POST['id'];
	$id=mysqli_real_escape_string($handle,$id);
	$dsc=$_POST['dsc'];
	$dsc=mysqli_real_escape_string($handle,$dsc);
	
	mysqli_query($handle,"DELETE from menu where id_profil=".$id);
	if (!EMPTY($_POST['bskart'])){
		$bskart=1;
		mysqli_query($handle,"INSERT INTO menu(id_profil, ind, href, dsc, class)
				VALUES ($id, 2, 'izvod.php', 'Извештај за продажба', 'menulink')");
	} else {$bskart=0;}
	
	if (!EMPTY($_POST['nalozi'])){
		$nalozi=1;
		mysqli_query($handle,"INSERT INTO menu(id_profil, ind, href, dsc, class)
				VALUES ($id, 4, 'mngprikaz_nalozi.php', 'Барања', 'menulink')");
	} else {$nalozi=0;}
	
	if (!EMPTY($_POST['finarep'])){
		$finarep=1;
		mysqli_query($handle,"INSERT INTO menu(id_profil, ind, href, dsc, class)
				VALUES ($id, 3, 'finansii.php', 'Финансиски извештаи', 'menulink')");
	} else {$finarep=0;}
	
	if(!EMPTY($_POST['admin_nalozi'])){
		$nalozi=1;
		$admin_nalozi=1;
		mysqli_query($handle,"INSERT INTO menu(id_profil, ind, href, dsc, class)
				VALUES ($id, 4, 'mngprikaz_nalozi.php', 'Барања', 'menulink')");
	}else{$admin_nalozi=0;}
	
	if(!EMPTY($_POST['admin_fina'])){
		$finarep=1;
		$admin_fina=1;
		mysqli_query($handle,"INSERT INTO menu(id_profil, ind, href, dsc, class)
				VALUES ($id, 3, 'finansii.php', 'Финансиски извештаи', 'menulink')");
	}else{$admin_fina=0;}
	
	mysqli_query($handle,"INSERT INTO menu(id_profil, ind, href, dsc, class)
			VALUES ($id, 1, 'index.php', 'Почетна', 'menulink')");
	mysqli_query($handle,"INSERT INTO menu(id_profil, ind, href, dsc, class)
			VALUES ($id, 5, 'info.php', 'Соопштенија', 'menulink')");
	mysqli_query($handle,"INSERT INTO menu(id_profil, ind, href, dsc, class)
			VALUES ($id, 6, 'optprof.php', 'Промена на лозинка', 'menulink')");
	mysqli_query($handle,"INSERT INTO menu(id_profil, ind, href, dsc, class)
			VALUES ($id, 7, 'help.php', 'Помош', 'menulink')");
	
	mysqli_query($handle, "UPDATE profil SET dsc='$dsc',bskart=$bskart,finarep=$finarep,nalozi=$nalozi,admin_nalozi=$admin_nalozi,admin_fina=$admin_fina WHERE id='$id'");
	
	mysqli_query($handle,"Delete from profilcmp where id_profil=".$id);
	if (isset($_POST['firma'])){
		foreach ($_POST['firma'] as $firmi)
			$firma[]=explode(' - ' , $firmi);
		foreach ($firma as $fir){
			$fir[0]=mysqli_real_escape_string($handle, $fir[0]);
			mysqli_query($handle,"INSERT INTO profilcmp(id_profil,id_cmp) VALUES('$id','$fir[0]')");
		}
	}
		echo ("<SCRIPT LANGUAGE='JavaScript'>
			window.alert('Профилот е променет.')
			window.location.href='profili.php'
			</SCRIPT>");
}
?>