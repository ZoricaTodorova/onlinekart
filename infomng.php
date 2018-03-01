<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script src="js/functions.js"></script>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script type="text/javascript">
function PopulateTextbox(id1,id2){	
	document.getElementById(id1).value = document.getElementById(id2).value;
}
</script>

<script type="text/javascript">
function SetBaza(){
	document.getElementById('baza_id').value = document.getElementById('baza').value;
}
</script>
<?php
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!isadmin() || !logged()){
	if (isset($_GET['rx'])) getrx($_GET['rx']);
	redirect('login.php');
}

getrx();

genmenu();
echo "<h4 align=center>Соопштенија</h4>";

if (isset($_POST['baza_id']) && !EMPTY($_POST['baza_id'])){
	$_SESSION['sektor'] = $_POST['baza_id'];
}

$handle = connectweb();
$rez=mysqli_query($handle, 'SELECT id, dsc from cmp');
?>

<body onload='SetBaza();'>
<form method='POST' action="">
Одберете база:<select name='baza' id='baza' onchange="PopulateTextbox('baza_id','baza')">
<?php
if (isset($_SESSION['sektor']) && !EMPTY($_SESSION['sektor'])){
	$sektor=$_SESSION['sektor'];
	echo "<option value=$sektor> </option>";
}
if (bskart()==1){
	echo "<option value='dbkart'>Фирми</option>";
}else echo "<option> </option>";
while ($red=mysqli_fetch_row($rez)){
	echo "<option value='$red[0]'>Корисници</option>";
}
?>
</select>
<input type='hidden' id='baza_id' name='baza_id' required value='<?php echo $_SESSION['sektor']; ?>'></input>
<input type='submit' value='Прикажи'></input>
</form>

<?php
if (isset($_SESSION['sektor']) && !EMPTY($_SESSION['sektor'])){
	$handle=connectweb();
	$cmd=mysqli_query($handle, "SELECT id, dsc from cmp where id='".$_SESSION['sektor']."'");
	$sel=mysqli_fetch_row($cmd);
	if ($_SESSION['sektor']=='dbkart'){
		echo "<p><b>Фирми</b></p>";
	}else echo "<p><b>Корисници</b></p>";
}
	
if (isset($_POST['baza_id']) && !EMPTY($_POST['baza_id'])){
 	$_SESSION['sektor'] = $_POST['baza_id'];
 	//$sektor=$_SESSION['sektor'];
 	//$handle=anyconnect("$sektor");
 	$handle=connectweb();
}

if (isset($_SESSION['sektor']) && !EMPTY($_SESSION['sektor'])){
	//$handle=anyconnect($_SESSION['sektor']);
	$handle=connectweb();
	if (isset($_GET["page"])) { //za pagenumberingot
		$page  = $_GET["page"];
	} else { $page=1;
	};
	$start_from = ($page-1) * 10;
	$sektor = $_SESSION['sektor'];
	$res = mysqli_query($handle, "SELECT id,subject,text,datum from soop where id_cmp = '$sektor' ORDER BY datum ASC LIMIT $start_from, 10") or die(mysqli_error());
	
	echo "<table border='1' cellspacing='0' width='600px'>
	<tr>
	<th>Код</th>
	<th>Наслов</th>
	<th>Датум</th>
	</tr>";
	
	while($row = mysqli_fetch_array($res))
	{
		echo "<tr>";
		echo "<td>" . $row['id'] . "</td>";
		echo "<td width='63%'><a href=\"editsoop.php?id=".$row['id']."\">".$row['subject']."</a></td>";
		echo "<td align='center' width='25%'>" . $row['datum'] . "</td>";
		//echo "<td><a href=\"editsoop.php?id=".$row['id']."\">отвори</a></td>";
		echo "<td align='center'><a href=\"?id=".$row['id']."\" onclick=\"return confirm('Дали сте сигурни?');\">избриши</a></td>";
		echo "</tr>";
	}
	echo "</table>";
	
	IF (isset($_GET['id'])){ //za brisenje na soop
		//$handle=anyconnect($_SESSION['sektor']);
		$handle=connectweb();
		$idid=$_GET['id'];
		$idid=mysqli_real_escape_string($handle,$idid);
		mysqli_query($handle, "DELETE FROM soop where id=".$idid);
		mysqli_query($handle, "DELETE FROM usrsoop where id_soop=".$idid);
		echo ("<SCRIPT LANGUAGE='JavaScript'>
				window.alert('Соопштението е избришано.')
				window.location.href='infomng.php'
				</SCRIPT>");
	}
	
	$handle=connectweb();
	$sql = "SELECT COUNT(id) FROM soop"; //za pagenumberingot
	$rs_result = mysqli_query($handle, $sql);
	$row = mysqli_fetch_row($rs_result);
	$total_records = $row[0];
	$total_pages = ceil($total_records / 10);
	  
	for ($i=1; $i<=$total_pages; $i++) {
	            echo "<a href='infomng.php?page=".$i."'>".$i."</a> ";
	};
	echo "<br/><br/>
		  <a href='add_soop.php' id='addnew'>Додади соопштение</a>";

}
?>
</body>
</html>