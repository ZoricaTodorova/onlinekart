<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script src="js/functions.js"></script>
<?php 

include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!logged()) redirect('index.php?rx=info');

genmenu();
echo "<h4 align=center>Соопштенија</h4>";

$handle=connectweb();
$id_usr=getid();

//$handle = anyconnect($_SESSION['sektor']);
if (isset($_GET['id'])){
	$idid=$_GET['id'];
	$idid=mysqli_real_escape_string($handle,$idid);
	mysqli_query($handle, "UPDATE usrsoop SET procitano=1 where id_soop=".$idid." and id_usr=".$id_usr);
	$res = mysqli_query($handle, "SELECT id,subject,text,datum from soop where id=".($_GET['id'])."") or die(mysqli_error());
	$row = mysqli_fetch_array($res);
}

if (isset($_SESSION['sektor']) && !EMPTY($_SESSION['sektor'])){
	$handle=connectweb();
	$cmd=mysqli_query($handle, "SELECT id, dsc from cmp where id='".$_SESSION['sektor']."'");
	$sel=mysqli_fetch_row($cmd);
	if ($_SESSION['sektor']=='dbkart'){
		echo "<p><b>картички</b></p>";
	}else echo "<p><b>$sel[1]</b></p>";
}
?>
<br/>
<table>
	<tr>
		<td><strong><span>Наслов:</span></strong></td>
		<td><span><?php echo "$row[subject]";?></span></td>
	</tr>
	<tr><td><br/></td></tr>
	<tr>
		<td><strong><span>Датум:</span></strong></td>
		<td><span><?php echo "$row[datum]";?></span></td>
	<tr>
	<tr><td><br/></td></tr>
		<td><strong><span>Содржина:</span></strong></td>
		<td><textarea type='text' style="width: 300px; height: 150px" name='sodrzina' value='<?php echo "$row[text]";?>' readonly><?php echo "$row[text]";?></textarea></td>
	</tr>
	<tr><td><br/></td></tr>
	<tr><td><td align='right'><form method='POST' action='info.php'><input type='submit' value='назад' ></form></td></td></tr>
</table>

</html>