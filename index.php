<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<?php
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';
if (!logged())
{
	if (isset($_GET['rx'])) getrx($_GET['rx']);
	redirect('login.php');
}
getrx();

genmenu();

fin_unset();

echo "<br/>";
echo "<div>";
include_once 'inc/sodrzina.php';
echo "</div>";
echo "<br/>";
echo "<div>";
if (bskart() == 1){
	include_once 'inc/klient_stat.php';
}
echo "</div>";
?>
<body bgcolor='<?php echo $_SESSION['boja']; ?>'>
</body>
</html>