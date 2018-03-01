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
    $("#sifra_mat").autocomplete("popuni_mat_nar.php", {
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
}
$god=date("Y");
genmenu();
echo "<h4 align=center>Испрати нарачка</h4>";

$handle=connectwebnal();

if (isset($_GET['glava_id']) && !EMPTY($_GET['glava_id'])){
	$glava_id=$_GET['glava_id'];
	$glava_id=mysqli_real_escape_string($handle,$glava_id);
		//$res=mysqli_query($handle, "SELECT cod,opis FROM materijali where godina='2013' and grupa='$grupa' order by opis");	
		$res=mysqli_query($handle, "SELECT cod , opis ,tip, m_u, grupa, edm,edm2,  IFNULL((select text from grupi_mat where grupa=materijali.grupa ), '') as grupa_opis from materijali
 									where godina ='$god' and web_mat=1 order by grupa,opis");
		
	$firma=$_SESSION['firma'];
	
	$cmd_vknar_nerealizirani=mysqli_query($handle, "select (select cod from firmi where tip='F' and cod='$firma') as firma,
			ifnull(sum(nar_stavki.vk_vr), 0000.000) as naracka_nerealizirana
			from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id
			where nar_glava.firma='$firma' and nar_glava.nalb='' and nar_glava.godina='$god'");
	$nerealizirani=mysqli_fetch_row($cmd_vknar_nerealizirani);
	
	$cmd_check=mysqli_query($handle, " select if(f_saldo<0 and limit_f,0,1) as dozvola, f_saldo, fin_limit,
			finansii as finansii_sostojba, naracka_st as naracka_segasna ,
			naracka_vk as naracka_nerealizirana, limit_f
			from (select korisnik, fin_limit,finansii,naracka_st,naracka_vk, fin_limit+finansii-naracka_st-naracka_vk-0 as f_saldo, limit_f
			from (select (select cod from firmi where tip='F' and cod='$firma') as korisnik, ifnull(sum(sumap-sumad),0.0000) as 'finansii',
			(select limit_f from firmi where firmi.tip='F' and firmi.cod=korisnik) as limit_f, (select fin_limit from firmi where firmi.tip='F' and firmi.cod=korisnik) as fin_limit
			from yana join konta on yana.konto=konta.konto and yana.godina=konta.godina where yana.godina='$god' and help_k=1 and korisnik='$firma' ) as fin
			join (select  (select cod from firmi where tip='F' and cod='$firma') as firma, ifnull(sum(nar_stavki.vk_vr), 0000.000) as naracka_st
			from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id where nar_glava.firma='$firma' and nar_glava.id=$glava_id and nar_glava.godina='$god') as nar
			on fin.korisnik=nar.firma
			join (select (select cod from firmi where tip='F' and cod='$firma') as firma,
			ifnull(sum(nar_stavki.vk_vr), 0000.000) as naracka_vk
			from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id
			where nar_glava.firma='$firma' and nar_glava.id<>$glava_id and nar_glava.nalb=''
			and nar_glava.godina='$god') as nar_vk
			on fin.korisnik=nar_vk.firma) as site");
	
	$check=mysqli_fetch_row($cmd_check);

?>

<body bgcolor='<?php echo $_SESSION['boja']; ?>'>
<table>
<tr>
	<td><b>За фирма: <?php echo $_SESSION['firma_opis']; ?></b></td> 
	<td align='right'><b> Лимит во финансии:</b> </td>
	<td align='right'> <?php 	if (isset($check[2]) && !empty($check[2])){
					echo "<font color='blue'><b>".number_format($check[2],2)."</b></font>";
		} ?>
	</td>
</tr>
<tr>
	<td><b>Продавница: <?php echo $_SESSION['prod_opis']; ?></b></td>
	<td align='right'><b>Салдо во финансии:</b> </td>
	<td align='right'><?php 	if (isset($_SESSION['saldo']) && !empty($_SESSION['saldo'])){
					$aa=$_SESSION['saldo'];
					echo "<font color='red'><b>".number_format($aa,2)."</b></font>";
		} ?>
	</td>
</tr>
<tr>
	<td><b>Датум: <?php echo $_SESSION['datum']; ?></b></td>
	<td align='right'><b> Нереализирани нарачки:</b> </td>
	<td align='right'><?php 	if (isset($nerealizirani[1]) && !empty($nerealizirani[1])){
					echo "<font color='red'><b>".number_format($nerealizirani[1]*(-1),2)."</b></font>";
		} ?>
	
	</td>
</tr>
<tr>
	<td><b><?php if (isset($check[6]) && !empty($check[6])){
					echo "<b>Има </b>";
			}else{echo "<b>Нема </b>";}?></b>забрана при негативно салдо</td>
	<td align='right'><b> Тековно салдо:</b> </td>
	<td align='right' ><?php 	if (isset($check[1]) && !empty($check[1])){
					echo "<font color='red'><b>".number_format($check[1],2)."</b></font>";
				} ?>
	
	</td>
</tr>
</table>

<form method='GET' action='naracki_kol.php'>
<table width='30%'>
<tr>
	<td width='68%'><input type='text' style="height:45px; width:100%; text-align:center; font-size:17px;" onClick="this.select();" name='sifra_mat' id='sifra_mat'/></td>
	<td width='32%'><input type='submit' disabled id='kopce' value='Според шифра' style='height: 45px; width: 100%'/></td>
</tr>
</table>
<br/>
<?php
			echo "<input type='hidden' value='$glava_id' name='glava_id' id='glava_id'>";
		while($row = mysqli_fetch_row($res)) {
			
			echo "<br/><button type='submit' class='kopcinja' value='".$row[0]."' style='height: 10%; width: 30%' name='mat' id='mat'>$row[1]</button>";
			//echo "<br/><br/><a style='font-size: 250%;' href='naracki_kol.php?glava_id=$glava_id&mat=".$row[0]."'>".$row[1]."</a>";
			
		}
}
?>

</form>
</body>
</html>