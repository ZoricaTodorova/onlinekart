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
echo "<h4 align=center>Нарачки</h4>";

$handle=connectwebnal();

// echo "<br/><br/><a style='font-size: 250%;' href='naracki.php'>Нова нарачка</a>";
// echo "<br/><br/><a style='font-size: 250%;' href='prikaz_naracki.php'>Стари нарачки</a>";
// echo "<br/><br/><a style='font-size: 250%;' href='naracki_akcija.php'>Акција</a>";
// echo "<br/><br/><a style='font-size: 250%;' href='naracki_lager_matgrupa.php'>Лагер</a>";

?>
<body bgcolor='<?php echo $_SESSION['boja']; ?>'>

<form method='POST' action='naracki.php'>
	<input type='submit' value='Нова нарачка' style='height: 10%; width: 30%'/>
</form>
<form method='POST' action='prikaz_naracki.php'>
	<input type='submit' value='Стари нарачки' style='height: 10%; width: 30%'/>
</form>
<form method='POST' action='naracki_finansii.php'>
	<input type='submit' value='Финансии' style='height: 10%; width: 30%'/>
</form>
<form method='POST' action='naracki_lager_mat.php'>
	<input type='submit' value='Лагер' style='height: 10%; width: 30%'/>
</form>
</body>
</html>