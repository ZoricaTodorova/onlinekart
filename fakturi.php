<?php
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

	if (!logged()) redirect('index.php?rx=fin_karti');
 	if (bskart()==0){
  		echo ("<SCRIPT LANGUAGE='JavaScript'>
  				window.alert('Немате дозвола за пристап.')
  				window.location.href='index.php'
  				</SCRIPT>");
 	}

genmenu();
echo "<h4 align=center>Фактури</h4>";

$handle=connectkart();

if (isset($_GET['broj'])){
	
	$broj=mysqli_real_escape_string($handle, $_GET['broj']);
	
	if (isset($_GET['godina'])){
		$god=mysqli_real_escape_string($handle,$_GET['godina']);
	}else{
		$god='2014';
	}
	
	$firma=$_SESSION['idcmp'];
	
	$cmd_vid=mysqli_query($handle, "select vid from fin_exchange_mat where fbr_user='$broj'");
	$vid=mysqli_fetch_row($cmd_vid);
	
	$firma_vid=mysqli_query($handle, "select vid from firmi where cod='$firma'");
	$firma_vid=mysqli_fetch_row($firma_vid);
	
	//** tuka pocnuvaat fakturite **\\
	if (isset($vid[0]) && $vid[0]=='D'){
  		echo ("<SCRIPT LANGUAGE='JavaScript'>
  				window.open('fpdffakturi_D.php?godina=$god&broj=$broj','_blank');
  				window.location.href='fin_karti.php'
  				</SCRIPT>");

	}
	
	elseif (isset($vid[0]) && $vid[0]=='M'){
  		echo ("<SCRIPT LANGUAGE='JavaScript'>
  				window.open('fpdffakturi_M.php?godina=$god&broj=$broj','_blank');
  				window.location.href='fin_karti.php'
  				</SCRIPT>");
	
	}

	elseif (isset($vid[0]) && $vid[0]=='K' && isset($firma_vid[0]) && $firma_vid[0]<>'D'){
  		echo ("<SCRIPT LANGUAGE='JavaScript'>
  				window.open('fpdffakturi_K.php?godina=$god&broj=$broj','_blank');
  				window.location.href='fin_karti.php'
  				</SCRIPT>");
	}
	elseif(isset($vid[0]) && $vid[0]=='K' && isset($firma_vid[0]) && $firma_vid[0]=='D'){
		echo ("<SCRIPT LANGUAGE='JavaScript'>
				window.open('fpdffakturi_amb_K.php?godina=$god&broj=$broj','_blank');
				window.location.href='fin_karti.php'
				</SCRIPT>");
	}
	
	elseif (isset($vid[0]) && $vid[0]=='O'){
  		echo ("<SCRIPT LANGUAGE='JavaScript'>
  				window.open('fpdffakturi_O.php?godina=$god&broj=$broj','_blank');
  				window.location.href='fin_karti.php'
  				</SCRIPT>");
		}
		
		elseif (isset($vid[0]) && $vid[0]=='Y'){
  		echo ("<SCRIPT LANGUAGE='JavaScript'>
  				window.open('fpdffakturi_Y.php?godina=$god&broj=$broj','_blank');
  				window.location.href='fin_karti.php'
  				</SCRIPT>");
			}
			
		elseif (isset($vid[0]) && $vid[0]=='A'){
  		echo ("<SCRIPT LANGUAGE='JavaScript'>
  				window.open('fpdffakturi_A.php?godina=$god&broj=$broj','_blank');
  				window.location.href='fin_karti.php'
  				</SCRIPT>");
				}
	
	//** do tuka **\\
	
}
	
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



</html>