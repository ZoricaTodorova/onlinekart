<?php
ob_start();
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!isadmin() || !logged()) redirect('infomng.php?rx=usrmng');

genmenu();
echo "<h4 align=center>Корисници</h4>";

// $handle = connect();
// $res = mysql_query("SELECT usr.id,login,pass,resetpass,isadmin,pass_resetiran,cmp.dsc from usr left join cmp on cmp.id=usr.id_cmp where login <> '".getuser()."'", $handle) or die(mysql_error());

echo "<form method='POST' action=''>
	  <table>
	  <tr>
	  	<td align='left'>Корисничко име:</td>
	  	<td><input type='text' name='login'></td>
	  <tr>
	  	<td align='left'>Фирма:</td>
	  	<td><input type='text' name='firma'></td>
	  	<td><input type='submit' value='Барај'></td>
	  </tr>
	  </table>";

echo "<br/>";

$handle=connectweb();
if (isset($_GET["page"])) { //za pagenumberingot
	$page  = $_GET["page"];
} else { $page=1;
};
$start_from = ($page-1) * 10;


if (isset($_POST['login']) && !EMPTY($_POST['login'])){
	$start_from=0;
	$handle = connectweb();
	$log=$_POST['login'];
	$log=mysqli_real_escape_string($handle,$log);
	$res = mysqli_query($handle, "SELECT usr.id,login,pass,resetpass,pass_resetiran,e_mail,id_cmp,profil.dsc as profil from usr left join profil on usr.id_profil=profil.id where login like '%".$log."%' LIMIT $start_from, 10") or die(mysqli_error());
}elseif (isset($_POST['firma']) && !EMPTY($_POST['firma'])) {
	$start_from=0;
	$fir=$_POST['firma'];
	$fir=mysqli_real_escape_string($handle,$fir);
	$handle = connectweb();
	$res = mysqli_query($handle, "SELECT usr.id,login,pass,resetpass,pass_resetiran,e_mail,id_cmp,profil.dsc as profil FROM usr left join profil on usr.id_profil=profil.id WHERE id_cmp like '%".$fir."%' LIMIT $start_from, 10") or die(mysqli_error());
}else{
	$handle = connectweb();
	$res = mysqli_query($handle, "SELECT usr.id,login,pass,resetpass,pass_resetiran,e_mail,id_cmp,profil.dsc as profil from usr left join profil on usr.id_profil=profil.id LIMIT $start_from, 10") or die(mysqli_error());
}

echo "<table border='1' width='100%' cellspacing='0'>
<tr>
<th>Код</th>
<th>Корисничко име</th>
<th>Лозинка</th>
<th>Фирма</th>
<th>е_mail</th>
<th>Профил</th>
<th>&nbsp;</th>
<th>&nbsp;</th>
<th>&nbsp;</th>
<th>&nbsp;</th>
</tr>";

while($row = mysqli_fetch_array($res))
{
	
	$hand=connectkart();
	$aa=mysqli_query($hand, "SELECT opis from firmi where cod='".$row['id_cmp']."'");
	$firma_opis=mysqli_fetch_row($aa);
	
	echo "<tr>";
	echo "<td width='20px' align='center'>" . $row['id'] . "</td>";
	echo "<td align='center'>" . $row['login'] . "</td>";
	echo "<td align='center' width='70px'>" . $row['pass_resetiran'] . "&nbsp;</td>";
	//echo "<td align='center'>" . $row['id_cmp'] . "&nbsp</td>";
	echo "<td align='center'>" . $firma_opis[0] . "&nbsp</td>";
	echo "<td width='50px'>" . $row['e_mail'] . "&nbsp</td>";
	echo "<td align='center'>" . $row['profil'] . "</td>";
	echo "<td align='center' width='50px'><a href=\"changeusr.php?id=".$row['id']."\">Промени</a></td>";
	echo "<td align='center' width='170px'><a href=\"?id=".$row['id']."\" onclick=\"return confirm('Дали сте сигурни?');\">Ресетирање на лозинка</a></td>";
	echo "<td align='center' width='65px'><a href=\"?cmpid=".$row['id_cmp']."\">Извештај</a></td>";
	echo "<td align='center' width='65px'><a href=\"?id_delete=".$row['id']."\" onclick=\"return confirm('Дали сте сигурни?');\">Избриши</a></td>";
	echo "</tr>";
}
echo "</table>";

if (isset($_GET['cmpid'])){
	$_SESSION['idcmp']=$_GET['cmpid'];
	redirect('izvod.php');
}

if (isset($_GET['id_delete'])){
	$handle=connectweb();
	mysqli_query($handle,"DELETE from usr where id=".$_GET['id_delete']);
	redirect('usrmng.php');
}

if (isset($_GET['id'])){
	$id=$_GET['id'];
	$id=mysqli_real_escape_string($handle,$id);
	$resetiran=mysqli_query($handle, "select resetpass from usr where id='$id'");
	$reset=mysqli_fetch_row($resetiran);
	if ($reset[0]==1){
		exit();
	}
	$newpass = mt_rand(10000000,100000000);
	$newpass1 = md5($newpass);
	$newpass1=mysqli_real_escape_string($handle,$newpass1);
	mysqli_query($handle, "UPDATE usr SET pass='".$newpass1."', resetpass=1, pass_resetiran='".$newpass."' where id=".$id) or die(mysqli_error());
	$rez=mysqli_query($handle, "SELECT login from usr where id=".$id);
	$row = mysqli_fetch_row($rez);
	echo "<br/><p style='color:red'>Новата лозинка на корисникот ".$row[0]." е ".$newpass;
	echo '<br/>';
}
$handle=connectweb();
$sql = "SELECT COUNT(id) FROM usr"; //za pagenumberingot
$rs_result = mysqli_query($handle, $sql);
$row = mysqli_fetch_row($rs_result);
$total_records = $row[0];
$total_pages = ceil($total_records / 10);

for ($i=1; $i<=$total_pages; $i++) {
	echo "<a href='usrmng.php?page=".$i."'>".$i."</a> ";
}
?>
<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script src="js/functions.js"></script>
<br/><br/>
<a href='add_user.php' id='addusr'>Додади корисник</a>
<br/><br/>
<a href='mngizvod.php' id='addusr'>Извештај за продажба</a>
</html>
