<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<?php 

include_once 'inc/initialize.php';
include_once 'inc/functions.php';

if (!isadmin() || !logged()) {
	redirect('usrmng.php');
}

top_pic();
echo '<br/>';
genmenu();
echo "<br/>";

?>
<a href="usrmng.php">Назад</a>

<form method="post" action=''>
<fieldset style="border:black 1px solid; width:35%; bgcolor:red">
<legend align="center" style="font:bold;border: 1px solid black">Додади нов корисник</legend>
<table>
	<tr>
		<td align='right'>*Шифра на клиент:</td>
		<td><input type='text' name='sifra'></td>
	</tr>
	<tr>
		<td align='right'>*Име на фирма:</td>
		<td><input type='text' name='firmadsc'></td>
	</tr>
	<tr>
		<td align='right'>*Корисничко име:</td>
		<td><input type='text' name='usrname'></td>
	</tr>
	<tr>
		<td align='right'>*Лозинка:</td>
		<td><input type='text' name='pass' value="<?php echo mt_rand(10000000,100000000); ?>"></td>
	</tr>
	<tr>
		<td align='right'>E-mail:</td>
		<td><input type='text' name='e_mail'></td>
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
if (isset($_POST['sifra']))
if (!EMPTY($_POST['sifra']) && !EMPTY($_POST['firmadsc']) && !EMPTY($_POST['usrname']) && !EMPTY($_POST['pass'])){	
	$handle=connectweb();
	$sifra=$_POST['sifra'];
	$sifra=mysqli_real_escape_string($handle,$sifra);
	$firmadsc=$_POST['firmadsc'];
	$firmadsc=mysqli_real_escape_string($handle,$firmadsc);
	$usrname=$_POST['usrname'];
	$usrname=mysqli_real_escape_string($handle,$usrname);
	$pass=$_POST['pass'];
	$pass2=md5($pass);
	$pass2=mysqli_real_escape_string($handle,$pass2);
	$e_mail=$_POST['e_mail'];
	$e_mail=mysqli_real_escape_string($handle,$e_mail);
	$query=mysqli_query($handle, 'SELECT max(id) from usr');
	$row=mysqli_fetch_row($query);
	$idid=$row[0]+1;
	$idid=mysqli_real_escape_string($handle,$idid);
	
	$cmd1="INSERT INTO cmp (id, dsc) VALUES('$sifra','$firmadsc')";
	mysqli_query($handle, $cmd1);
	$cmd2="INSERT INTO usr (id,login,pass,id_cmp,resetpass,pass_resetiran,e_mail) VALUES('$idid','$usrname','$pass2','$sifra',1,'$pass','$e_mail')";
	mysqli_query($handle, $cmd2);

 	echo ("<SCRIPT LANGUAGE='JavaScript'>
 			window.alert('Корисникот е додаден.')
 			window.location.href='usrmng.php'
 			</SCRIPT>");
}else{
	echo ("<SCRIPT LANGUAGE='JavaScript'>
			window.alert('Пополнете ги потребните полиња!')
			</SCRIPT>");
}

?>
</html>