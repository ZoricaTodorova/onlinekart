<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script src="js/functions.js"></script>
<?php

include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!logged()) redirect('index.php?rx=optprof');
echo "<h4 align=center>Ресетирање лозинка</h4>";

fin_unset();

?>

<form name='changepass' method='post'>
<table>
	<tr>
		<td>Нова лозинка:</td>
		<td><input type='password' name='pass1'></input></td>
	</tr>
	<tr>
		<td>Потврдете ја новата лозинка: </td>
		<td><input type='password' name='pass2'></input></td>
	</tr>
	<tr> <td><input type='submit' name='subbut' value='Потврди'></input> </td></tr>
</table>
</form>

<p style="font:14"><i>*Вашата лозинка мора да има најмалку 8 карактери и не смее да имате празно место или наводници!</i></p>

<?php 
if (isset($_POST["pass1"])){
	$pass1=$_POST["pass1"];
	$pass1=md5($pass1);
	$handle=connectweb();
	$pass1=mysqli_real_escape_string($handle,$pass1);
}
	//$pass2=md5($_POST["pass2"]);
    if (!empty($_POST["pass1"]) && !preg_match ('/[ "\']/i', $_POST["pass1"]) && strlen($_POST["pass1"])>=8){
	 if ($_POST["pass1"] == $_POST["pass2"]) {
	 	
		$query = "UPDATE usr ".
				 "SET pass= '". $pass1 ."', resetpass=0, pass_resetiran='' ".
				 "WHERE usr.login = '" . getuser() . "' ";

		$result = mysqli_query($handle, $query) or die(mysqli_error());

		//echo "<p style='color:red'>Вашата лозинка е променета.</p>";
		echo ("<SCRIPT LANGUAGE='JavaScript'>
        window.alert('Вашата лозинка е променета.')
        window.location.href='index.php'
        </SCRIPT>");  
		//redirect("index.php");
	  }
	 else{

		echo "<p style='color:red'>Лозинките не се совпаѓаат! Обидете се повторно.</p>";
	 }
    }

?>
</html>