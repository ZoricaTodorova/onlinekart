<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/jquery-2.0.3.min.js"></script>          
<script src="js/functions.js"></script>
<script type="text/javascript" src="js/jquery.js"></script>
<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />
<?php
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!logged()) redirect('index.php?rx=izbor_naracki');
if (nalog()==0){
	echo ("<SCRIPT LANGUAGE='JavaScript'>
			window.alert('Немате дозвола за пристап.')
			window.location.href='index.php'
			</SCRIPT>");
}
if (nalog()==1 && !isadmin_nalozi()){
	$komercijalist=true;
}
genmenu();
echo "<h4 align=center>Испрати нарачка</h4>";

$handle=connectwebnal();
if (isset($_GET['glava_id']) && !EMPTY($_GET['glava_id'])){
	$glava_id=$_GET['glava_id'];
	$glava_id=mysqli_real_escape_string($handle,$glava_id);
	$res=mysqli_query($handle, "SELECT grupa,text FROM grupi_mat where grupa in ('010314','010315','010316','010317')");
?>

<form method='GET' action='naracki_mat.php'>

<?php
		echo "<input type='hidden' value='$glava_id' name='glava_id' id='glava_id'>";
	while($row = mysqli_fetch_row($res)) {
		echo "<br/><button type='submit' value='".$row[0]."' style='height: 6%; width: 20%' name='grupa' id='grupa'>$row[1]</button>";
		//echo "<br/><br/><a style='font-size: 250%;' href='naracki_mat.php?glava_id=$glava_id&grupa=".$row[0]."'>".$row[1]."</a>";
	}
}
?>
</form>


</html>