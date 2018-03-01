<?php
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!isadmin() || !logged()){
	redirect('infomng.php');
}

genmenu();
echo "<h4 align=center>Додади соопштение</h4>";

//$handle = anyconnect($_SESSION['sektor']);
$handle = connectweb();
$sektor = $_SESSION['sektor'];
$query=mysqli_query($handle, 'SELECT max(id) from soop');
$row=mysqli_fetch_row($query);
$idid=$row[0]+1;
$idid=mysqli_real_escape_string($handle,$idid);

if (isset($_POST['id']) && isset($_POST['subject']) && !EMPTY($_POST['subject']) && isset($_POST['sodrzina']) && !EMPTY($_POST['sodrzina'])){
	$id=$_POST['id'];
	$id=mysqli_real_escape_string($handle,$id);
	$subject=$_POST['subject'];
	$subject=mysqli_real_escape_string($handle,$subject);
	$text=$_POST['sodrzina'];
	$text=mysqli_real_escape_string($handle,$text);
	$dt = date('Y-m-d H:i:s', time());
	$order = " INSERT INTO soop (id, id_cmp, subject, text, datum) VALUES('$id', '$sektor', '$subject','$text','$dt') ";
	mysqli_query($handle, $order);
	if (isset($_POST['resobj']))
	{
		dodadisoop($_SESSION['sektor'],$_POST['resobj'],$id);
	}
	echo ("<SCRIPT LANGUAGE='JavaScript'>
 		window.alert('Соопштението е додадено.')
 		window.location.href='infomng.php'
 		</SCRIPT>");
}


?>

<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script src="js/functions.js"></script>

<a href='infomng.php'>Назад</a><br/><br/>

<?php 
if (isset($_SESSION['sektor']) && !EMPTY($_SESSION['sektor'])){
	$handle=connectweb();
	$cmd=mysqli_query($handle, "SELECT id, dsc from cmp where id='".$_SESSION['sektor']."'");
	$sel=mysqli_fetch_row($cmd);
	if ($_SESSION['sektor']=='dbkart'){
		echo "<p><b>Фирми</b></p>";
	}else echo "<p><b>Корисници</b></p>";
}
?>

<fieldset style="border:black 1px solid; width:45%;">
<legend align="center" style="font:bold;border: 1px solid black">Соопштение</legend>
<form method='post' name='picksoop' id ='picksoop'>
<input type="hidden" name="id" value="<?php echo $idid;?>">
<table>
	<tr>
		<td>Наслов:</td>
		<td><input type='text' required name='subject' required></input></td>
	</tr>
	<tr>
		<td>Содржина:</td>
		<td><textarea type='text' required style="width: 450px; height: 150px" name='sodrzina'></textarea></td>
	</tr>
</table>
<br/>
<table>
	<tr>
		<td>Избор на корисници</td>
		<td></td>
		<td>Избрани корисници</td>
	</tr>
	<tr>
		<td>
			<select id="bsselector" name="bsselector" ondblclick="mvoptsel2sel('bsselector', 'bsselected')" size="10" multiple="1" style="width: 140px;">
			<?php echo populateusrid();?>
			</select>
		</td>
		<td>
			<table>
				<tr><input type="button" value="> " onclick="mvopts2sright('bsselector', 'bsselected');"></tr>
				<tr><br></tr>
				<tr><input type="button" value="< " onclick="mvopts2sleft('bsselected', 'bsselector');"></tr>
				<tr><br></tr>
				<tr><input type="button" value="<=" onclick="moveall('bsselected', 'bsselector');"></tr>
			</table>
		</td>
		<td>
			<select id="bsselected" name="bsselected" ondblclick="mvoptsel2sel('bsselected', 'bsselector')" size="10" multiple="1" style="width: 140px;">
				<option value="###">СИТЕ</option>
			</select>
		</td>
	</tr>
	<tr><td><td><td align="right"><input type="button" id="click" name="click" value="Зачувај" onclick="submitpicksoop()"></td></td></td></tr>
</table>
</form>
</fieldset>
</html>