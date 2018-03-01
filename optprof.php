<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="jscolor/jscolor.js"></script>
<?php

include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';
if (!logged()) redirect('index.php?rx=optprof');

genmenu();

fin_unset();

echo "<h4 align=center>Промена на лозинка</h4>";

?>
<body bgcolor='<?php echo $_SESSION['boja']; ?>'>
<form name='changepass' method='post'>
<table>
	<tr>
		<td>Лозинка: </td>
		<td><input type='password' name='passot' autocomplete='off'></input></td>
	</tr>
	<tr>
		<td>Нова лозинка:</td>
		<td><input type='password' name='pass1' autocomplete='off'></input></td>
	</tr>
	<tr>
		<td>Потврдете ја новата лозинка: </td>
		<td><input type='password' name='pass2' autocomplete='off'></input></td>
	</tr>
	<tr> <td><input type='submit' name='subbut' value='Потврди'></input> </td></tr>
</table>
</form>

<p style="font:14"><i>*Вашата лозинка мора да има најмалку 8 карактери и не смее да имате празно место или наводници!</i></p>

<?php 
// if (isset($_POST["passot"])){
// 	$xxx=$_POST["passot"];
// 	$xxx=md5($xxx);
// }

if (!empty($_POST["passot"])){

	$handle = connectweb();
	$cmd = "select pass from usr where usr.login = '" . getuser() . "' ";
	$res = mysqli_query($handle, $cmd) or die(mysqli_error());
	$row=mysqli_fetch_row($res);
	$passw = trim($row[0]);	
	$xxx = $_POST["passot"];
	$xxx=md5($xxx);
	$xxx=mysqli_real_escape_string($handle,$xxx);
	$pass1=$_POST["pass1"];
	$pass1=md5($pass1);
	$pass1=mysqli_real_escape_string($handle,$pass1);
	if ($xxx == $passw){
    if (!empty($_POST["pass1"]) && !preg_match ('/[ "\']/i', $_POST["pass1"]) && strlen($_POST["pass1"])>=8){
	 if ($_POST["pass1"] == $_POST["pass2"]) {
	 	//connect();
		$query = "UPDATE usr ".
				 "SET pass= '". $pass1 ." ' " .
				 "WHERE usr.login = '" . getuser() . "' ";

		$result = mysqli_query($handle, $query) or die(mysqli_error());

		echo "<p style='color:red'>Вашата лозинка е променета.</p>";
	  }
	 else{

		echo "<p style='color:red'>Лозинките не се совпаѓаат! Обидете се повторно.</p>";
	}
   }
   }
   else{
   
   	echo "<p style='color:red'>Внесовте погрешна лозинка! Обидете се повторно.</p>";
   }
}

?>
<form method='post'>
	Кликнете тука: <input id='boja' name='boja' class="color" value='<?php if (isset($_SESSION['boja'])) echo $_SESSION['boja'];?>'>
	<input type='submit' name='odberi_boja' id='odberi_boja' value='Одбери боја'></input>
</form>
<?php 
if (isset($_POST['odberi_boja'])){
	$handle=connectweb();
	$boja=$_POST['boja'];
	$_SESSION['boja']=$boja;
	mysqli_query($handle, "UPDATE usr set boja='$boja' where login='".getuser()."'");
	echo ("<SCRIPT LANGUAGE='JavaScript'>
			window.location.href='optprof.php'
			</SCRIPT>");
}
?>
</body>
</html>






