<html>
<script src="js/functions.js"></script>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<?php

include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!logged()) redirect('index.php?rx=info');

genmenu();

fin_unset();

echo "<h4 align=center>Соопштенија</h4>";

if (isset($_POST['baza_id']) && !EMPTY($_POST['baza_id'])){
	$_SESSION['sektor'] = $_POST['baza_id'];
}

$handle = connectweb();
$zzid=getid();
$zzid=mysqli_real_escape_string($handle,$zzid);
$rez=mysqli_query($handle,'SELECT id_cmp, cmp.dsc from profilcmp LEFT JOIN cmp ON cmp.id=profilcmp.id_cmp where id_profil='.getprofil());

?>
<body onload='SetBaza();'>
<!-- <form method='POST' action=""> -->
<!-- 	Одберете база:<select name='baza' id='baza' onchange="PopulateTextbox('baza_id','baza')"> -->
	<?php 
// 	if (isset($_SESSION['sektor']) && !EMPTY($_SESSION['sektor'])){
// 		$sektor=$_SESSION['sektor'];
// 		echo "<option value=$sektor> </option>";
// 	}
// 	if (bskart()==1){
// 		echo "<option value='dbkart'>картички</option>";
// 	}
// 	while ($red=mysqli_fetch_row($rez)){
// 		echo "<option value='$red[0]'>$red[1]</option>";
// 	}
// 	?>
<!-- 	</select> -->
	<input readonly type='hidden' id='baza_id' name='baza_id' value='<?php if (isset($_SESSION['sektor']) && !EMPTY($_SESSION['sektor'])){echo $_SESSION['sektor'];} ?>'></input>
<!-- 	<input type='submit' value='Прикажи'></input> -->
<!-- </form> -->

<?php
$_SESSION['sektor']='x00001';
// if (isset($_SESSION['sektor']) && !EMPTY($_SESSION['sektor'])){
// 	$handle=connectweb();
// 	$cmd=mysqli_query($handle, "SELECT id, dsc from cmp where id='".$_SESSION['sektor']."'");
// 	$sel=mysqli_fetch_row($cmd);
// 	if ($_SESSION['sektor']=='dbkart'){
// 		echo "<p><b>картички</b></p>";
// 	}else echo "<p><b>$sel[1]</b></p>";
// }

if (isset($_POST['baza_id']) && !EMPTY($_POST['baza_id'])){
	$_SESSION['sektor'] = $_POST['baza_id'];
	//$sektor=$_SESSION['sektor'];
	//$handle = anyconnect($_SESSION['sektor']);
	$handle = connectweb();
}

if (isset($_SESSION['sektor']) && !EMPTY($_SESSION['sektor'])){
	//$handle=anyconnect($_SESSION['sektor']);
	$sek = $_SESSION['sektor'];
	$handle=connectweb();
	$res = mysqli_query($handle, "SELECT * from soop left join usrsoop on usrsoop.id_soop=soop.id where usrsoop.id_usr=".$zzid." and soop.id_cmp in ('x00001', 'dbkart')") or die(mysqli_error());
	echo "<br/>";
	echo "<table border='1' cellspacing='0' width='50%'>
	<tr>
	<th>Предмет</th>
	<th>Датум</th>
	</tr>";
	
	while($row = mysqli_fetch_array($res))
	{
		echo "<tr>";
		if ($row['procitano']==0){
			echo "<td width='50%'><a class=\"linkot\" href=\"usropensoop.php?id=".$row['id']."\">".$row['subject']."</a></td>";
		}else{ echo "<td width='50%'><a href=\"usropensoop.php?id=".$row['id']."\">".$row['subject']."</a></td>"; }
		echo "<td align='center'>" . $row['datum'] . "</td>";
		//echo "<td><a href=\"usropensoop.php?id=".$row['id']."\">отвори</a></td>";
		echo "<td align='center'><a href=\"?id=".$row['id']."\" onclick=\"return confirm('Дали сте сигурни?');\">избриши</a></td>";
		echo "</tr>";
	}
	echo "</table>";
	
	IF (isset($_GET['id'])){
		$idid=$_GET['id'];
		$idid=mysqli_real_escape_string($handle,$idid);
		//$handle = anyconnect($_SESSION['sektor']);
		$handle = connectweb();
		mysqli_query($handle, "UPDATE usrsoop SET id_usr = $zzid where id_soop=".$idid." and id_usr=".$zzid);
		mysqli_query($handle, "DELETE from usrsoop where id_soop=".$idid." and id_usr=".$zzid);
		//header("location:info.php");
		redirect('info.php');
	}
}
?>
</body>
</html>