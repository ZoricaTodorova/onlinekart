<?php
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!isadmin() || !logged()) redirect('infomng.php?rx=usrmng');

genmenu();
echo "<h4 align=center>Профили</h4>";
echo "<br/>";

$handle=connectweb();
$res=mysqli_query($handle, "Select * from profil ");   //nemam funkcija za zemanje na id od profil

echo "<table border='1' width='50%' cellspacing='0'>
<tr>
<th>Код</th>
<th>Опис</th>
<th>Пристап до картички</th>
<th>Пристап до финансии</th>
<th>Пристап до барања</th>
<th>&nbsp;</th>
<th>&nbsp;</th>
</tr>";

while($row = mysqli_fetch_array($res))
{
	if($row['bskart']==1){
		$kart='Има';
	}else{$kart="Нема";}
	
	if($row['finarep']==1){
		$fin='Има';
	}else{$fin="Нема";}
	
	if($row['nalozi']==1){
		$nal='Има';
	}else{$nal="Нема";
	}
	
	echo "<tr>";
	echo "<td align='center' width='20px'>" . $row['id'] . "</td>";
	echo "<td align='center'>" . $row['dsc'] . "</td>";
	echo "<td align='center' width='130px'>" . $kart . "&nbsp;</td>";
	echo "<td align='center' width='130px'>" . $fin . "</td>";
	echo "<td align='center' width='130px'>" . $nal . "</td>";
	echo "<td align='center' width='50px'><a href=\"changeprofili.php?id=".$row['id']."\">Промени</a></td>";
	echo "<td align='center' width='50px'><a href=\"?id_delete=".$row['id']."\" onclick=\"return confirm('Дали сте сигурни?');\">Избриши</a></td>";
	echo "</tr>";
}
echo "</table>";

if (isset($_GET['id_delete'])){
	$handle=connectweb();
	
	$rez=mysqli_query($handle, "SELECT * from usr where id_profil=".$_GET['id_delete']);
	$ima=mysqli_fetch_row($rez);
	if (!EMPTY($ima)){
		echo ("<SCRIPT LANGUAGE='JavaScript'>
				window.alert('Бришењето е забрането!')
				window.location.href='profili.php'
				</SCRIPT>");
	}
	else{
	mysqli_query($handle, "DELETE from profilcmp where id_profil=".$_GET['id_delete']);
	mysqli_query($handle, "DELETE from profil where id=".$_GET['id_delete']);
	mysqli_query($handle, "DELETE from menu where id_profil=".$_GET['id_delete']);
	redirect('profili.php');
	}
}

?>
<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script src="js/functions.js"></script>
<br/>
<a href='add_profil.php' id='addusr'>Додади профил</a>
<br/><br/>
</html>
<?php 
if (isset($_POST['run']) && $_POST['run']==genmen){
	Generatemenuphp();
}
?>





