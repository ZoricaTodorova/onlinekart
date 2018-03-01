<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script src="js/functions.js"></script>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>

<?php 

include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';
if (!isadmin() || !logged()){
	redirect('infomng.php');
}

genmenu();
echo "<h4 align=center>Промени соопштение</h4>";

if (isset($_GET['id']))
	$soopid=$_GET['id'];
	//$handle = anyconnect($_SESSION['sektor']);
	$handle = connectweb();
	$soopid=mysqli_real_escape_string($handle,$soopid);
	$res = mysqli_query($handle, "SELECT id,subject,text,datum from soop where id=".$soopid."") or die(mysqli_error());
	$row = mysqli_fetch_array($res);

?>
<br/>
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

<fieldset style="border:black 1px solid; width:450">
<legend align="center" style="font:bold;border: 1px solid black">Соопштение</legend>
<form method='post' name="picksoop" id="picksoop" action='edit_soop.php'>
<input type="hidden" name="id" value="<?php echo "$row[id]"?>">
<table>
	<tr>
		<td>Наслов:</td>
		<td><input type='text' name='subject' required value='<?php echo "$row[subject]";?>'></input></td>
	</tr>
	<tr>
		<td>Содржина:</td>
		<td><textarea type='text' required style="width: 300px; height: 150px" name='sodrzina' value='<?php echo "$row[text]";?>'><?php echo "$row[text]";?></textarea></td>
	</tr>
</table>
</form>

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
	</tr><td><td><td align='right'><input type="button" id="click" name="click" value="Зачувај" onclick="submitpicksoop()"></td></td></td></tr>
</table>
</form>
</fieldset>
</html>