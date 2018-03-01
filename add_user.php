<?php
include 'inc/functions.php'; 
include 'inc/initialize.php';
include_once 'inc/menu.php';

if (!isadmin() || !logged()) {redirect('usrmng.php');}

genmenu();
echo "<h4 align=center>Додади корисник</h4>";

//$handle=connect();
//$sql=mysql_query("SELECT id, dsc FROM cmp" , $handle);
//$red=mysql_fetch_array($sql);
?>
<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery.js"></script>
<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />
 
<script type="text/javascript">
$().ready(function() {
    $("#course").autocomplete("popuni.php", {
        width: 260,
        matchContains: true,
        selectFirst: false
    });
});
</script>
<script type="text/javascript">
$().ready(function() {
    $("#cmp_nalozi").autocomplete("popuni_firma.php", {
        width: 260,
        matchContains: true,
        selectFirst: false
    });
});
</script>
<a href="usrmng.php">Назад</a> <br/><br/>

<fieldset id="addpole" style="border:black 1px solid; width:35%;">
<legend align="center" style="border: 1px solid black;">Додади нов корисник</legend>
<form method="post">
<table>
	<tr>
		<td align='right'>Шифра на клиент за картички:</td>
		<td>
			<input name="sifra" type="text" id="course" size="25"/>
		</td>
	</tr>
<!-- 	<tr> -->
<!-- 		<td align='right'>Шифра на клиент за барања/финансии:</td> -->
<!-- 		<td> -->
<!-- 			<input name="sifra_nalozi" type="text" id="cmp_nalozi" size="25"/> -->
<!-- 		</td> -->
<!-- 	</tr> -->
	<tr>
		<td align='right'>Корисничко име:</td>
		<td><input type='text' required name='usrname' size="25"></td>
	</tr>
	<tr>
		<td align='right'>Лозинка:</td>
		<td><input type='text' name='pass' size="25" required value="<?php echo mt_rand(10000000,100000000); ?>"></td>
	</tr>
	<tr>
		<td align='right'>E-mail:</td>
		<td><input type='text' name='e_mail' size="25"></td>
	</tr>
	<tr>
		<td align='right'>Профил:</td>
		<td>
			<select id='profil' name='profil'>
				<?php 
					$handle=connectweb();
					$sql = "select concat(id,' - ',dsc) as celo from profil where id<>1";
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
		<td align='right'><input type='submit' value="Зачувај"></td>
	<tr>
</table>
</form>
</fieldset>
</html>
<?php 
if (isset($_POST['usrname']) && isset($_POST['pass'])){
	if (!EMPTY($_POST['usrname']) && !EMPTY($_POST['pass'])){
		//$sifra_array=array();
		$handle=connectweb();
		$sifra_array=explode(' - ' , $_POST['sifra']);
		$sifra=$sifra_array[0];
		$sifra=mysqli_real_escape_string($handle,$sifra);
		
		$handle1=anyconnect('x00001');
		$cmd_firma=mysqli_query($handle1, "SELECT cod from firmi where cod='$sifra'");
		$ima_firma=mysqli_fetch_row($cmd_firma);
		if (EMPTY($ima_firma[0])){
			echo ("<SCRIPT LANGUAGE='JavaScript'>
					window.alert('Неправилен код')
					window.location.href='add_user.php'
					</SCRIPT>");
			exit();
		}
		
// 		$sifra_nalozi_array=explode(' - ' , $_POST['sifra_nalozi']);
// 		$sifra_nalozi=$sifra_nalozi_array[0];
// 		$sifra_nalozi=mysqli_real_escape_string($handle,$sifra_nalozi);
		
		$usrname=$_POST['usrname'];
		$usrname=mysqli_real_escape_string($handle,$usrname);
		
		$pass=$_POST['pass'];
		$pass2=md5($pass);
		$pass2=mysqli_real_escape_string($handle,$pass2);
		
		$e_mail=$_POST['e_mail'];
		$e_mail=mysqli_real_escape_string($handle,$e_mail);
		
		$profil_array=explode(' - ' , $_POST['profil']);
		$profil=$profil_array[0];
		$profil=mysqli_real_escape_string($handle,$profil);	
		
		$sql=mysqli_query($handle, "SELECT login from usr where login='$usrname'");
		$red=mysqli_fetch_row($sql);
		if (!EMPTY($red[0])){
	 			echo ("<SCRIPT LANGUAGE='JavaScript'>
	 				window.alert('Корисничкото име е зафатено!')
	 				window.location.href='add_user.php'
	 				</SCRIPT>");
		}else{
		$query=mysqli_query($handle, 'SELECT max(id) from usr');
		$row=mysqli_fetch_row($query);
		$idid=$row[0]+1;
		$idid=mysqli_real_escape_string($handle,$idid);
	
		$cmd2="INSERT INTO usr (id,login,pass,id_cmp,id_profil,resetpass,pass_resetiran,e_mail) VALUES('$idid','$usrname','$pass2','$sifra','$profil',1,'$pass','$e_mail')";
		mysqli_query($handle, $cmd2);
	
	 	echo ("<SCRIPT LANGUAGE='JavaScript'>
	 			window.alert('Корисникот е додаден.')
	 			window.location.href='usrmng.php'
	 			</SCRIPT>");
		}
	}
}
?>