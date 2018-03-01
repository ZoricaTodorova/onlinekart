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
echo "<h4 align=center>Лагер</h4>";

echo "<a style='font-size: 150%;' href='izbor_naracki.php'>Назад</a><br/>";

$handle=connectwebnal();
	$res=mysqli_query($handle, "SELECT grupa,text FROM grupi_mat where grupa in ('010314','010315','010316','010317')");
?>

<body bgcolor='<?php echo $_SESSION['boja']; ?>'>
<form method='GET' action='naracki_lager_mat.php'>

<?php 	
	while($row = mysqli_fetch_row($res)) {
		echo "<br/><button type='submit' value='".$row[0]."' style='height: 6%; width: 20%' name='grupa' id='grupa'>$row[1]</button>";
		//echo "<br/><br/><a style='font-size: 250%;' href='naracki_lager_mat.php?grupa=".$row[0]."'>".$row[1]."</a>";
	}
?>

</form>
</body>
</html>