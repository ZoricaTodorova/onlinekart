<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<?php
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!logged() || !isadmin()) redirect('usrmng.php');
if (!isset($_GET['id'])) redirect('usrmng.php');

genmenu();
echo "<h4 align=center>Промени корисник</h4>";

if (isset($_GET['id'])){	
	$handle=connectweb();
	$res = mysqli_query($handle, "Select usr.id,login,id_cmp,e_mail,profil.dsc as profil_dsc FROM usr LEFT JOIN profil ON usr.id_profil=profil.id where usr.id=".$_GET['id']);
	$row=mysqli_fetch_array($res);
	
	$rez=mysqli_query($handle, "SELECT id_profil from usr where id=".$_GET['id']);
	$id_profil=mysqli_fetch_row($rez);
	
	IF ($id_profil[0]==1){
		echo ("<SCRIPT LANGUAGE='JavaScript'>
				window.alert('Не е дозволена промена на корисникот!')
				window.location.href='usrmng.php'
				</SCRIPT>");
	}
}
?>

<br/>
<a href="usrmng.php">Назад</a>

<form method='POST' action=''>
<fieldset style="border:black 1px solid; width:35%; bgcolor:red">
<legend align="center" style="font:bold;border: 1px solid black">Промени корисник</legend>
<table>
	<tr>
		<td align='right'>Идентификационен број:</td>
		<td><input type='text' name='id' readonly value="<?php echo $row['id']; ?>"></td>
	</tr>
	<tr>
		<td align='right'>Корисничко име:</td>
		<td><input type='text' name='login' required value="<?php echo $row['login']; ?>"></td>
	</tr>
	<tr>
		<td align='right'>Име на фирма:</td>
		<td><input type='text' readonly name='dsc' value="<?php echo $row['id_cmp']; ?>"></td>
	</tr>
	<tr>
		<td align='right'>E-mail:</td>
		<td><input type='text' name='e_mail' value="<?php echo $row['e_mail']; ?>"></td>
	</tr>
	<tr>
		<td align='right'>Профил:</td>
		<td>
			<select id='profil' name='profil'>
			<option>
				<?php 					
					$handle=connectweb();
					$res=mysqli_query($handle, "SELECT concat(id,' - ',dsc),id from profil where id=$id_profil[0]");
					$profil_id=mysqli_fetch_row($res);
					echo $profil_id[0];				
				?>
			</option>
				<?php 
					$handle=connectweb();
					$sql = "select concat(id,' - ',dsc) as celo from profil where id<>1 and id<>$profil_id[1]";
					$rsd = mysqli_query($handle, $sql);
					while($rs = mysqli_fetch_row($rsd)) {
    					echo "<option>$rs[0]</option>";
					}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td></td>
		<td align='right'><input type='submit' value="Зачувај" onclick="return confirm('Дали сте сигурни?');"></td>
	<tr>
</table>
</fieldset>
</form>
</html>

<?php 

if (isset($_POST['login']) && !EMPTY($_POST['login'])){
	$handle=connectweb();
	$id=$_POST['id'];
	$id=mysqli_real_escape_string($handle,$id);
	$usrname=$_POST['login'];
	$usrname=mysqli_real_escape_string($handle,$usrname);
	$mail=$_POST['e_mail'];
	$mail=mysqli_real_escape_string($handle,$mail);
	$profil_array=explode(' - ' , $_POST['profil']);
	$profil=$profil_array[0];
	$profil=mysqli_real_escape_string($handle,$profil);	
	mysqli_query($handle, "UPDATE usr SET login='$usrname',id_profil='$profil',e_mail='$mail' WHERE id='$id'");
	redirect('usrmng.php');
}
?>