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
$handle=connectwebnal();
if (nalog()==1 && !isadmin_nalozi()){
	$komercijalist=true;
	$god=date("Y");
	$cmd=mysqli_query($handle, "SELECT cod, opis from mesto_trosok where godina='$god' AND cod='".$_SESSION['lgn']."'");
	$m_t=mysqli_fetch_row($cmd);

}
genmenu();
echo "<h4 align=center>Испрати нарачка</h4>";

echo "<a style='font-size: 150%;' href='izbor_naracki.php'>Назад</a><br/><br/>";

$handle=connectwebnal();
//$res=mysqli_query($handle, "select * from dejnosti where length(cod)=3 and web_dejnost=1");
$stmt_dej="select distinct dejnosti.cod, dejnosti.opis from dejnosti inner join firmi_m_dejnosti
	on dejnosti.cod=firmi_m_dejnosti.dejnost
	where length(dejnosti.cod)=3 and dejnosti.web_dejnost=1
	and firmi_m_dejnosti.firma in (select distinct korisnik from org_e where  org_e.m_t='".getuser()."')";

$res = mysqli_query($handle, $stmt_dej);

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

<body bgcolor='<?php echo $_SESSION['boja']; ?>'>
<form method='GET' action='naracki_2.php'>
<table style="width:30%">
<tr>
	<td width='68%'><input type='text' style="height:45px; width:100%; text-align:center; font-size:17px;" onClick="this.select();" name='sifra_firma' id='sifra_firma'/></td>
	<td width='32%'><input type='submit' disabled value='Шифра на фирма' id='kopce' style='height: 45px; width: 100%'/></td>
</tr>
</table>
</form>

<form method='GET' action='naracki_1.php'>
<?php 
while($row = mysqli_fetch_row($res)) {
	echo "<br/><button type='submit' value='".$row[0]."' style='height: 5%; width: 30%' name='dejnost' id='dejnost' class='kopcinja'>$row[1]</button>";
	//echo "<br/><br/><a style='font-size: 250%;' href='naracki_1.php?dejnost=".$row[0]."'>".$row[1]."</a>";
}
?>
</form>
</body>
</html>









