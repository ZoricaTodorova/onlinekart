<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
</html>
<?php
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!logged()) redirect('index.php?rx=nalozi');
if (nalog()==0){
	echo ("<SCRIPT LANGUAGE='JavaScript'>
			window.alert('Немате дозвола за пристап.')
			window.location.href='index.php'
			</SCRIPT>");
}
genmenu();
echo "<h4 align=center>Барања</h4>";

$handle=connectwebnal();
$res=mysqli_query($handle, "Select * from webnal where firma='".getidcmp_nal()."'");
echo "<br/>";
echo "<table border='1' cellspacing='0' width='50%'>
<tr>
<th>Дериват</th>
<th>Количина во литри</th>
<th>За датум</th>
<th>&nbsp</th>
<th>&nbsp</th>
</tr>";

while($row = mysqli_fetch_array($res))
{
	echo "<tr>";
	echo "<td align='center'>" . $row['mat'] . "</td>";
	echo "<td align='center'>" . $row['kolicina'] . "</td>";
	echo "<td align='center'>" . $row['datum'] . "</td>";
	echo "<td align='center'><a href=\"nalozi.php?promeni=".$row['id']."\">промени</a></td>";
	echo "<td align='center'><a href=\"?otkazi=".$row['id']."\" onclick=\"return confirm('Дали сте сигурни?');\">откажи</a></td>";
	echo "</tr>";
}
echo "</table>";
echo "<br/><a href='nalozi.php'>Испрати барање</a>";

if (isset($_GET['otkazi'])){
	$handle = connectwebnal();
	mysqli_query($handle, "UPDATE webnal set status=4 where id=".$_GET['otkazi']);
}
?>