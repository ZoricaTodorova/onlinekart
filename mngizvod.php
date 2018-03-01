<?php 

include 'inc/functions.php';
include 'inc/initialize.php';
include_once 'inc/menu.php';

if (!isadmin() || !logged()) {
	redirect('infomng.php');
}

genmenu();
echo "<h4 align=center>Извештај за продажба</h4>";

$handle=connectkart();

?>
<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/jquery-2.0.3.min.js"></script>          
<script src="js/functions.js"></script>
<script type="text/javascript" src="js/jquery.js"></script>
<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />

<script type="text/javascript">
$().ready(function() {
    $("#cmp").autocomplete("popuni_cmpfirma.php", {
        width: 260,
        matchContains: true,
        selectFirst: false
    });
});
</script>
<p>Одберете фирма:</p>
<form method="GET">
	<input onClick="this.select();" size='50' type='text' name='cmp' id='cmp'/> 
	<input type='submit' value='Одбери' id='kopce'/>
</form>
</html>

<?php 

if (isset($_GET['cmp']) && !EMPTY($_GET['cmp'])){
	
	$strip_cmp=mysqli_real_escape_string($handle,$_GET['cmp']);
	$cmp_array=explode(' - ',$strip_cmp);
	$cmp=$cmp_array[0];
	
	$cmd_firma=mysqli_query($handle, "SELECT cod from firmi where cod='$cmp'");
	$ima_firma=mysqli_fetch_row($cmd_firma);
	if (EMPTY($ima_firma[0])){
		echo ("<SCRIPT LANGUAGE='JavaScript'>
				window.alert('Неправилен код')
				window.location.href='mngizvod.php'
				</SCRIPT>");
		exit();
	}
	
	$_SESSION['idcmp']=$cmp;
	redirect('mngizvod_2.php');
}

?>







