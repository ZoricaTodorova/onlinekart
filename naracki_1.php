<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/jquery-2.0.3.min.js"></script>          
<script src="js/functions.js"></script>
<script type="text/javascript" src="js/jquery.js"></script>
<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />

<script>
$(function() {
  $(":text").keyup(check_submit).each(function() {
    check_submit();
  });
});

function check_submit() {
  if ($(this).val().length == 0) {
    $("#kopce").attr("disabled", true);
    $(".kopcinja").removeAttr("disabled");
  } else {
    $("#kopce").removeAttr("disabled");
    $(".kopcinja").attr("disabled", true);
  }
}
</script>

<script type="text/javascript">
$().ready(function() {
    $("#sifra_firma").autocomplete("popuni_firma_nar.php", {
        width: 260,
        matchContains: true,
        selectFirst: false
    });
});
</script>

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
// 	$handle=connectwebnal();
// 	$cmd=mysqli_query($handle, "SELECT cod, opis from mesto_trosok where godina='2013' AND cod='".$_SESSION['lgn']."'");
// 	$m_t=mysqli_fetch_row($cmd);
}
genmenu();
echo "<h4 align=center>Испрати нарачка</h4>";

echo "<a style='font-size: 150%;' href='naracki.php'>Назад</a><br/><br/>";

$handle=connectwebnal();
if (isset($_GET['dejnost']) && !EMPTY($_GET['dejnost'])){
	$dejnost=$_GET['dejnost'];
	$dejnost=mysqli_real_escape_string($handle,$dejnost);
	$_SESSION['dejnost']=$dejnost;
// 	$res=mysqli_query($handle, "SELECT firma,firmi.opis from firmi_m_dejnosti left join firmi on firmi.cod=firmi_m_dejnosti.firma 
// 									where firmi_m_dejnosti.dejnost like '".$dejnost."%' order by firmi.opis");

	$res=mysqli_query($handle, "select distinct firmi.cod,firmi.opis from firmi 
			inner join org_e on firmi.cod=org_e.korisnik
			inner join firmi_m_dejnosti on firmi.cod=firmi_m_dejnosti.firma 
			where org_E.korisnik<>'' and org_e.m_t='".getuser()."' and firmi_m_dejnosti.dejnost like '".$dejnost."%' order by firmi.opis;");
?>
<body bgcolor='<?php echo $_SESSION['boja']; ?>'>
<form method='GET' action='naracki_2.php'>
<table width='30%'>
<tr>
	<td width='68%'><input type='text' style="height:45px; width:100%; text-align:center; font-size:17px;" onClick="this.select();" name='sifra_firma' id='sifra_firma'/></td>
	<td width='32%'><input type='submit' value='Според шифра' id='kopce' style='height: 45px; width: 100%'/></td>
</tr>
</table>
<br/>
<?php
	while($row = mysqli_fetch_row($res)) {
		$firma_opis=$row[1];
		if (EMPTY($firma_opis)){
			continue;	
		}	
		echo "<br/><button type='submit' value='".$row[0]."' style='height: 10%; width: 30%' name='firma' id='firma' class='kopcinja'>$firma_opis</button>";
		//echo "<br/><br/><a style='font-size: 250%;' href='naracki_2.php?firma=".$row[0]."'>".$firma_opis[0]."</a>";
	}
}
?>
</form>
</body>
</html>

