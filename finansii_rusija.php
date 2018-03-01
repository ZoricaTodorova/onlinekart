<?php
//ob_start();
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!logged()) redirect('index.php?rx=finansii_rusija');
if (finarep()==0){
	echo ("<SCRIPT LANGUAGE='JavaScript'>
			window.alert('Немате дозвола за пристап.')
			window.location.href='index.php'
			</SCRIPT>");
}
genmenu();
echo "<h2 align=center>Финансиски извештаи за обврски и побарувања</h2>";

//promenlivi za polinjata za datum + vadenje na firmi/bazi od bazata onlinekart
$den=date('d');
$mesec=date('m');
$god=date('Y');

$VK_DOLZI=0;
$VK_POBARUVA=0;
$VK_SD=0;
$VK_SP=0;

$handle = connectweb();
$rez=mysqli_query($handle, 'SELECT id_cmp,cmp.dsc from profilcmp LEFT JOIN cmp on cmp.id=profilcmp.id_cmp where id_profil='.getprofil());

if(isset($_POST['check_list']) && !empty($_POST['check_list'])) {   		 //polnenje na sesija i zemanje na odbranite checkboxi
	$l=count($_POST['check_list']);
	$_SESSION['fin_baza']=$_POST['check_list'];
	$_SESSION['xxk']=$_POST['check_list'];
	for($i=0; $i<$l; $i++){
		${'handle'.$i}=anyconnect($_SESSION['fin_baza'][$i]);
	}
}else{
	//echo "<p style='color:red'>Одберете фирма!</p>";
}
$kolku_bazi=count($_SESSION['fin_baza']);	
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
<?php //if (isadmin_fina()){?>
	<script type="text/javascript">
	$().ready(function() {
	    $("#korisnik").autocomplete("popuni_korisnik.php", {
	        width: 260,
	        matchContains: true,
	        selectFirst: false
	    });
	});
	</script>
<?php //}?>

<script>
$().ready(function() {
$('.chks').change(function(){
	$('#Vkupno').attr('disabled', $('.chks:checked').length < 1);
    $('#Analitika').attr('disabled', $('.chks:checked').length != 1);
    $('#saldo').attr('disabled', $('.chks:checked').length != 1);
    $('#analitika_ios').attr('disabled', $('.chks:checked').length != 1);
    $('#analitika_se').attr('disabled', $('.chks:checked').length != 1);
});
});
</script>

<script>
$(document).ready(function(){
	$("#opcii").click(function(){
	    $("#tabela_opcii").toggle();
	}); 
});
</script>

<script>
$(function(){
	$('.chks').change(function(){

		  var values = new Array();
		  var values = $('#checkboxes input:checked').map(function(i,el){return el.value;}).get().join(';');
	      $.ajax({
	    	    type: 'POST',
	    	    url: 'setsesija.php',
	    	    data: {name: values}
	    	});
	});
});
</script>

<script>
$(function(){

	if ($('.chks').is(':checked')) {

		  var values = new Array();
		  var values = $('#checkboxes input:checked').map(function(i,el){return el.value;}).get().join(';');
	      $.ajax({
	    	    type: 'POST',
	    	    url: 'setsesija.php',
	    	    data: {name: values}
	    	});
	}
});
</script>
<form method='POST' name='filter' id='filter'>
<table width='100%' style="float:left; margin-bottom:10;font-size: 25px" align='center' bgcolor="#DEEBF5">
	<tr>
		<td style="display: none;" width='18%'><b>Фирма:</b></td>
		<td style="display: none;"><div id="checkboxes">
		<?php      
			$a=0;                                                                    //checkboxite za odbiranje baza
			while ($red=mysqli_fetch_row($rez)){
		?>
			
			<input class='chks' type='checkbox' <?php while($a<1) {echo "checked='checked'"; $a++;} ?> value=<?php echo $red[0];?> name='check_list[]' 
																				<?php
																				//da zapamti ako checkbox e selektiran
																				if (!EMPTY($_POST['check_list']) && $kolku_bazi==1){ 
																						if ($_POST['check_list'][0]==$red[0]) echo "checked='checked'";
																				 }elseif (!EMPTY($_POST['check_list']) && $kolku_bazi>1){
																				 		for($i=0; $i<$kolku_bazi; $i++){
																				 			if ($_POST['check_list'][$i]==$red[0]) echo "checked='checked'";}
																				 }?>><?php echo $red[1]; ?></option>
		<?php }?>
		</div></td>
	</tr>
	<tr>
		<td width='30%' ><b>Година:</b><input size='4' style='font-size: 25px' onClick="this.select();" type='text' name='godina' id='godina' value='<?php if (isset($_REQUEST['godina'])){ echo $_REQUEST['godina'];} else {echo '2015';} ?>'/></td>
		<td width='25%' ><b>Корисник:</b><input style='font-size: 20px' onClick="this.select();" <?php //if (!isadmin_fina()) echo 'readonly'; ?> size='25' type='text' name='korisnik' id='korisnik' value='<?php if (isset($_REQUEST['korisnik'])){ echo $_REQUEST['korisnik'];} ?>'/></td>
		<td width='30%' align='center'><b>Обврски:</b><input type='checkbox' <?php if (isset($_POST['obvrski'])) echo "checked='checked'" ;?>  value='obvrski' name='obvrski'/></td>
	</tr>
	<tr>
		<td height="50" width="30%">
			<input type="submit" id='Vkupno' name="Vkupno" value="Вкупно" style="height:100%; width:46%;font-size: 20px"/>
			<input type="submit" name="Analitika" style="height:100%; width:46%;font-size: 20px" id='Analitika' value="Аналитика" <?php if ($kolku_bazi > 1){echo 'disabled';}?>/>
		</td>
		<td align='center'>
			се:<input type='radio' name='radioanalitika' checked='checked' <?php if ($kolku_bazi>1){echo 'disabled';}?> id='analitika_se' value='analitika_se'  <?php if(isset($_REQUEST['radioanalitika']) && $_REQUEST['radioanalitika']=='analitika_se') echo 'checked="checked"'; ?>/>
			ios:<input type='radio' name='radioanalitika' id='analitika_ios' <?php if ($kolku_bazi>1){echo 'disabled';}?> value='analitika_ios'  <?php if(isset($_REQUEST['radioanalitika']) && $_REQUEST['radioanalitika']=='analitika_ios') echo 'checked="checked"'; ?>/>
			датум:<input type='radio' name='radioanalitika' id='analitika_datum' <?php if ($kolku_bazi>1){echo 'disabled';}?> value='analitika_datum'  <?php if(isset($_REQUEST['radioanalitika']) && $_REQUEST['radioanalitika']=='analitika_datum') echo 'checked="checked"'; ?>/>
		</td>
		<td width='30%' align='center'>
			<b>Побарувања:</b><input type='checkbox' checked='checked' <?php if (isset($_POST['pobaruvanja'])) echo "checked='checked'";?>  value='pobaruvanja' name='pobaruvanja'/>
		</td>
	</tr>
	<tr>
		<td><td><td><button style="height:100%; width:100%;font-size: 25px" type="button" id="opcii">филтер +</button></td></td></td>
	</tr>
</table>
<table id="tabela_opcii" cellspacing='0' style="display:none;float:left; margin-left: 100%; margin-top:-100;font-size: 25px" width='100%' border=1>
	<tr>
		<td width='25%'><b>Датум од/до:</b><input type='radio'  checked='checked' name='radiokopce' value='od_do' <?php if(isset($_REQUEST['radiokopce']) && $_REQUEST['radiokopce']=='od_do') echo 'checked="checked"'; ?>/></td>
		<td width='25%'><b>Валута од/до:</b><input type='radio' name='radiokopce' value='valuta' <?php if(isset($_REQUEST['radiokopce']) && $_REQUEST['radiokopce']=='valuta') echo 'checked="checked"'; ?>/></td>
		<td width='11%'><b>прет. Салдо:</b><input type='checkbox' <?php if (isset($_POST['saldo'])) echo "checked='checked'";?> value='saldo' name='saldo' id='saldo <?php if ($kolku_bazi>1){echo 'disabled';}?>'/></td>				
	</tr>
	<tr>		
		<td width='25%'><b>Од датум:</b>
			<input type="text" onClick="this.select();" style='font-size: 25px' name="OdDen"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['OdDen'])){ echo $_REQUEST['OdDen'];} else {echo '01';}?>'>
			<input type="text" onClick="this.select();" style='font-size: 25px' name="OdMesec"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['OdMesec'])){ echo $_REQUEST['OdMesec'];} else {echo '01';}?>'> 
			<input type="text" onClick="this.select();" style='font-size: 25px' name="OdGodina"  maxlength = "4" size="4" value='<?php if (isset($_REQUEST['OdGodina'])){ echo $_REQUEST['OdGodina'];} else {echo '2000';}?>'>
		</td>
		<td width='25%'><b>До датум:</b>
			<input type="text" onClick="this.select();" style='font-size: 25px' name="DoDen"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['DoDen'])){ echo $_REQUEST['DoDen'];} else {echo $den;}?>'>
			<input type="text" onClick="this.select();" style='font-size: 25px' name="DoMesec"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['DoMesec'])){ echo $_REQUEST['DoMesec'];} else {echo $mesec;}?>'> 
			<input type="text" onClick="this.select();" style='font-size: 25px' name="DoGodina"  maxlength = "4" size="4" value='<?php if (isset($_REQUEST['DoGodina'])){ echo $_REQUEST['DoGodina'];} else {echo $god;}?>'>
		</td>
		<td width='11%'><b>Тотали:</b><input checked='checked' type='checkbox' <?php if (isset($_POST['totali'])) echo "checked='checked'" ;?>  value='totali' name='totali'/></td>
	</tr>
</table>
</form>
</body>
</html>

<?php                                                 //selektite i tabelite za prikaz																								
																										
	if (isset($_POST['korisnik']) && !empty($_POST['korisnik'])){
		$strip_kor=mysqli_real_escape_string($handle0,$_POST['korisnik']);
		$kor_array=explode(' - ',$strip_kor);
		$korisnik=$kor_array[0];
		$korisnik=trim($korisnik);
		//if(isadmin_fina()){
			$str_korisnik=" AND korisnik='".$korisnik."' ";
			$_SESSION['korisnik']=$strip_kor;
// 		}
// 		else {
// 			$str_korisnik=" AND korisnik='".getidcmp_nal()."'";
// 			$_SESSION['korisnik']=$strip_kor;
// 		}
	}else {$str_korisnik=""; $_SESSION['korisnik']='';}
	
	if (isset($_POST['ekedinica']) && !empty($_POST['ekedinica'])){
		$eked_array=explode(' - ',$_POST['ekedinica']);
		$eked=$eked_array[0];
		$eked=trim($eked);
		$str_eked=" AND ORG_E='$eked' ";
	}else {$str_eked='';}
	
	if (isset($_POST['pozicija']) && !EMPTY($_POST['pozicija'])){
		$poz_array=explode(' - ',$_POST['pozicija']);
		$pozicija=$poz_array[0];
		$pozicija=trim($pozicija);
		$str_poz = " AND yana.mesto_trosok='$pozicija' ";
	}else {$str_poz="";}
	
	if (isset($_POST['obvrski']) && !EMPTY($_POST['obvrski']) && !isset($_POST['pobaruvanja']) && EMPTY($_POST['pobaruvanja']) ){
		$str_obvrski=" AND konta.d_p='P' and konta.help_k=1 and konta.f_l='F' ";
	}else{$str_obvrski=" ";}
	
	if (isset($_POST['pobaruvanja']) && !EMPTY($_POST['pobaruvanja']) && !isset($_POST['obvrski']) && EMPTY($_POST['obvrski'])){
		$str_pobaruvanja=" AND konta.d_p='D' and konta.help_k=1 and konta.f_l='F' ";
	}else{$str_pobaruvanja=" ";}
	
	if (isset($_POST['obvrski']) && !EMPTY($_POST['obvrski']) && isset($_POST['pobaruvanja']) && !EMPTY($_POST['pobaruvanja']) ){
		$str_obvrski_pob=" AND (konta.d_p='P' OR konta.d_p='D') and konta.help_k=1 and konta.f_l='F' ";
	}else{$str_obvrski_pob=" ";
	}
	
	if(!EMPTY($_POST['OdDen']) && !EMPTY($_POST['OdMesec']) && !EMPTY($_POST['OdGodina']))
	{
		$strip_odgodina=mysqli_real_escape_string($handle0,$_POST['OdGodina']);
		$strip_odmesec=mysqli_real_escape_string($handle0,$_POST['OdMesec']);
		$strip_odden=mysqli_real_escape_string($handle0,$_POST['OdDen']);
		$oddat="'".$strip_odgodina."-".$strip_odmesec."-".$strip_odden."'";
	}else {$oddat="'2000-01-01'";}
	
	if(!EMPTY($_POST['DoDen']) && !EMPTY($_POST['DoMesec']) && !EMPTY($_POST['DoGodina']))
	{
		$strip_dogodina=mysqli_real_escape_string($handle0,$_POST['DoGodina']);
		$strip_domesec=mysqli_real_escape_string($handle0,$_POST['DoMesec']);
		$strip_doden=mysqli_real_escape_string($handle0,$_POST['DoDen']);
		$dodat="'".$strip_dogodina."-".$strip_domesec."-".$strip_doden."'";
	}else {$dodat="'2020-01-01'";}
	
	if(isset($_POST['godina']) && !EMPTY($_POST['godina']))
	{
		$godina=mysqli_real_escape_string($handle0, $_POST['godina']);
	}else {$godina='2015';}
	
	if(isset($_POST['totali']) && !EMPTY($_POST['totali']))
	{
		$_SESSION['totfin']=1;
	}else {$_SESSION['totfin']=0;
	}
		
	//*****************************************************************************koga ke klikne za prikaz
	
IF ($kolku_bazi==1){ 																//ako ima odbrano edna firma(edinecen izvestaj)
	
if(isset($_POST['Vkupno'])){
	
	//echo "<a href='fpdffinarep2.php' target='_blank'>Печати PDF<a><br/><br\>";
	//echo "<a href='xls_ed.php' target='_blank'>Печати Excel<a><br/>";
	
	$_SESSION['klik']='Vkupno';
	
	if (EMPTY($_POST['saldo']))
	{  //ne e izbrano prethodno saldo
		
		//$_SESSION['klik']='Vkupno';
		
	if ($_POST['radiokopce'] == 'od_do')
	{      //izbran e period od\do

	if(!EMPTY($_POST['korisnik']))
	{
		
		$selekt = "	SELECT firmi.opis,KORISNIK, yana.KONTO , SUM(SUMAD) AS DOLZI, SUM(SUMAP) AS POBARUVA,
		if(SUM(SUMAD)>SUM(SUMAP),SUM(SUMAD)-SUM(SUMAP),0) AS SD,
		if(SUM(SUMAD) < SUM(SUMAP),SUM(SUMAP)-SUM(SUMAD),0) AS SP
		FROM yana  JOIN konta ON konta.KONTO = yana.KONTO join firmi on firmi.cod=yana.korisnik
		WHERE yana.godina=$godina  AND konta.godina=$godina AND konta.GRUPA_2 = '1' and konta.p_r='' AND DIZ BETWEEN $oddat AND $dodat $str_korisnik $str_obvrski  $str_pobaruvanja $str_obvrski_pob
		GROUP BY opis, KONTO WITH ROLLUP";
	}
	else //ako ne e popolneto poleto za korisnik treba da gi dade site - onevozmozeno!
	{
		$selekt = "	SELECT firmi.opis,KORISNIK, yana.KONTO , SUM(SUMAD) AS DOLZI, SUM(SUMAP) AS POBARUVA,
		IF(SUM(SUMAD)>SUM(SUMAP),SUM(SUMAD)-SUM(SUMAP),0) AS SD,IF(SUM(SUMAD) < SUM(SUMAP),SUM(SUMAP)-SUM(SUMAD),0) AS SP
		FROM yana JOIN konta ON konta.KONTO = yana.KONTO join firmi on firmi.cod=yana.korisnik
		WHERE yana.godina=$godina AND konta.godina=$godina AND KORISNIK <>'000000'  AND konta.GRUPA_2 = '1' and konta.p_r='' AND DIZ BETWEEN $oddat AND $dodat  $str_obvrski  $str_pobaruvanja $str_obvrski_pob
		GROUP BY opis, KONTO WITH ROLLUP";

	}
	
	
	}
	else //izbrana od do valuta
	{
	if(!EMPTY($_POST['korisnik']))
	{
			$selekt = "	SELECT firmi.opis,KORISNIK, yana.KONTO , SUM(SUMAD) AS DOLZI, SUM(SUMAP) AS POBARUVA,
			IF(SUM(SUMAD)>SUM(SUMAP),SUM(SUMAD)-SUM(SUMAP),0) AS SD,IF(SUM(SUMAD)<SUM(SUMAP),SUM(SUMAP)-SUM(SUMAD),0) AS SP
			FROM yana  JOIN konta ON konta.KONTO = yana.KONTO join firmi on firmi.cod=yana.korisnik
			WHERE yana.godina=$godina  AND konta.godina=$godina $str_korisnik  AND konta.GRUPA_2 = '1' and konta.p_r='' AND DATUM BETWEEN $oddat AND $dodat $str_obvrski  $str_pobaruvanja $str_obvrski_pob
			GROUP BY opis, KONTO WITH ROLLUP";
	}
		else //ako ne e popolneto poleto za korisnik treba da gi dade site - onevozmozeno!
			{
			$selekt = "	SELECT firmi.opis,KORISNIK, yana.KONTO , SUM(SUMAD) AS DOLZI, SUM(SUMAP) AS POBARUVA,
			IF(SUM(SUMAD)>SUM(SUMAP),SUM(SUMAD)-SUM(SUMAP),0) AS SD,IF(SUM(SUMAD)<SUM(SUMAP),SUM(SUMAP)-SUM(SUMAD),0) AS SP
			FROM yana  JOIN konta ON konta.KONTO = yana.KONTO join firmi on firmi.cod=yana.korisnik
			WHERE yana.godina=$godina AND konta.godina=$godina AND KORISNIK <>'000000'  AND konta.GRUPA_2 = '1' and konta.p_r='' AND DATUM BETWEEN $oddat AND $dodat  $str_obvrski  $str_pobaruvanja $str_obvrski_pob
			GROUP BY opis, KONTO WITH ROLLUP";
			}
	}
	
	
	$rezultat=mysqli_query($handle0, $selekt) or die("Error in query:" . $selekt . " <hr> error test:" . mysqli_error());
	//echo $selekt;
	echo '<table border=1 cellspacing="0" cellpadding="0" width ="100%" style="font-size: 25px">';
	if (isset($_POST['totali'])){
		//echo "<td width='5%'><b>" . "&nbsp;" . "</td>" ;
	}else{
	echo "<td width='5%' align = 'center'><b>" . "Конто" . "</td>" ;}
	echo "<td align = 'center' width='29%'><b>" . "Корисник" . "</td>";
	echo "<td width='11%' align = 'center'><b>" . "Должи" . "</td>";
	echo "<td width='11%' align = 'center'><b>" . "Побарува" . "</td>";
	echo "<td width='11%' align = 'center'><b>" . "Салдо должи" . "</td>";
	echo "<td width='11%' align = 'center'><b>" . "Салдо побарува" . "</td>";
		
	WHILE ($recs = mysqli_fetch_array($rezultat))
	{
	$KORISNIK=$recs['opis'];

	$KONTO = $recs['KONTO'];
	$DOLZI=$recs['DOLZI'];
	$POBARUVA=$recs['POBARUVA'];
	$SD=$recs['SD'];
	$SP=$recs['SP'];
	
	//$VK_DOLZI=$VK_DOLZI+$recs['DOLZI'];
	//$VK_POBARUVA=$VK_POBARUVA+$recs['POBARUVA'];
	//$VK_SD=$VK_SD+$recs['SD'];
	//$VK_SP=$VK_SP+$recs['SP'];
	
	
	echo "<tr>";
	IF(!is_null($KORISNIK))
	{
		//$reskor = mysqli_query($handle0, "select opis from firmi where cod = '$KORISNIK'");
		$korrow = $KORISNIK;		 		
	if(isset($_POST['totali']))
	{
	IF(is_null($KONTO))
	{
		//echo "<td width='5%'>" . "&nbsp;" ."Вкупно за:". "</td>" ;
		echo "<td align = 'center' width='29%'>" . $korrow ."</td>";
		echo "<td style='color:red' width='11%' align = 'right' >" . number_format($DOLZI,0) ."</td>";
		echo "<td style='color:blue' width='11%' align = 'right'>" . number_format($POBARUVA,0) ."</td>";
		echo "<td style='color:red' width='11%' align = 'right'>" .number_format($SD,0) . "</td>";
		echo "<td style='color:blue' width='11%' align = 'right'>" .number_format($SP,0) . "</td>";
	
	}
	}
	else
	{
	echo "<td width='5%' align = 'center'>" . $KONTO."</td>" ;
	IF(is_null($KONTO))
	{
	echo "<td align = 'center' width='29%'>" . "<b>".$korrow ."</td>";
	}
	ELSE
	{
	echo "<td width='29%'>" . "&nbsp;" . "</td>" ;
	}
		IF(is_null($KONTO))
		{
		echo "<td style='color:red' width='11%' align = 'right' >" . "<b>".number_format($DOLZI,0) ."</td>";
		echo "<td style='color:blue' width='11%' align = 'right'>" ."<b>". number_format($POBARUVA,0) ."</td>";
		echo "<td style='color:red' width='11%' align = 'right'>" ."<b>".number_format($SD,0) . "</td>";
		echo "<td style='color:blue' width='11%' align = 'right'>" ."<b>".number_format($SP,0) . "</td>";
		}
		else
		{
		echo "<td style='color:red' width='11%' align = 'right' >" . number_format($DOLZI,0) ."</td>";
		echo "<td style='color:blue' width='11%' align = 'right'>" . number_format($POBARUVA,0) ."</td>";
		echo "<td style='color:red' width='11%' align = 'right'>" .number_format($SD,0) . "</td>";
		echo "<td style='color:blue' width='11%' align = 'right'>" .number_format($SP,0) . "</td>";
		}
		}
		}
	
		echo "</tr>";
		//totalot
			IF (is_null($KONTO) && is_null($KORISNIK)){
				if (isset($_POST['totali'])){
					echo "<tr>";
					echo "<td width='11%' align = 'center' >" . "<b>ВКУПНО</b>" ."</td>";
					echo "<td style='color:red' width='11%' align = 'right' ><b>" . number_format($DOLZI,0) ."</b></td>";
					echo "<td style='color:blue' width='11%' align = 'right'><b>" . number_format($POBARUVA,0) ."</b></td>";
					echo "<td style='color:red' width='11%' align = 'right'><b>" .number_format($SD,0) . "</b></td>";
					echo "<td style='color:blue' width='11%' align = 'right'><b>" .number_format($SP,0) . "</b></td>";
					echo "</tr>";
				}else{
					echo "<tr>";
					echo "<td width='11%' align = 'center' >" . "<b></b>" ."</td>";
					echo "<td width='11%' align = 'center' >" . "<b>ВКУПНО</b>" ."</td>";
					echo "<td style='color:red' width='11%' align = 'right' ><b>" . number_format($DOLZI,0) ."</b></td>";
					echo "<td style='color:blue' width='11%' align = 'right'><b>" . number_format($POBARUVA,0) ."</b></td>";
					echo "<td style='color:red' width='11%' align = 'right'><b>" .number_format($SD,0) . "</b></td>";
					echo "<td style='color:blue' width='11%' align = 'right'><b>" .number_format($SP,0) . "</b></td>";
					echo "</tr>";
				}
			}
		}
		
		echo "</table>";
	
		
	
}else {//so prethodno saldo
	
	$_SESSION['klik']='Vkupno_saldo';
	
			if ($_POST['radiokopce'] == 'od_do'){
				
				if(!EMPTY($_POST['korisnik'])){
					
					$selekt = "SELECT firmi.opis, KORISNIK, yana.KONTO ,
					SUM(IF(DIZ<$oddat,SUMAD,0)) AS PREDD,
					SUM(IF(DIZ<$oddat,SUMAP,0)) AS PREDP,
					SUM(IF (DIZ>=$oddat AND DIZ < $dodat ,SUMAD,0)) AS POSLED,
					SUM(IF(DIZ>=$oddat AND DIZ < $dodat ,SUMAP,0)) AS POSLEP,
					IF(SUM(IF(DIZ<$dodat,SUMAD,0))>SUM(IF(DIZ<$dodat,SUMAP,0)),SUM(SUMAD)-SUM(SUMAP),0) AS PREDSD,
					IF(SUM(IF(DIZ<$dodat,SUMAP,0))>SUM(IF(DIZ<$dodat,SUMAD,0)),SUM(SUMAP)-SUM(SUMAD),0) AS PREDSP,
					IF(SUM(IF (DIZ>=$oddat AND DIZ < $dodat ,SUMAD,0))>SUM(IF (DIZ>=$oddat AND DIZ < $dodat ,SUMAP,0)),SUM(IF (DIZ>=$oddat AND DIZ < $dodat ,SUMAD,0))-SUM(IF (DIZ>=$oddat AND DIZ < $dodat ,SUMAP,0)),0) AS DOLZI,
					IF(SUM(IF (DIZ>=$oddat AND DIZ < $dodat ,SUMAP,0))>SUM(IF (DIZ>=$oddat AND DIZ < $dodat ,sumad,0)),SUM(IF (DIZ>=$oddat AND DIZ < $dodat ,SUMAP,0))-SUM(IF (DIZ>=$oddat AND DIZ < $dodat ,SUMAD,0)),0) AS POBARUVA
					FROM yana JOIN konta ON konta.konto = yana.konto join firmi on firmi.cod=yana.korisnik
					WHERE yana.godina=$godina AND konta.godina=$godina $str_korisnik  AND konta.GRUPA_2 = '1' and konta.p_r=''   $str_obvrski  $str_pobaruvanja $str_obvrski_pob
					GROUP BY opis, KONTO WITH ROLLUP";			
				}
				else{
					$selekt = "SELECT firmi.opis, KORISNIK, yana.KONTO ,
					SUM(IF(DIZ<$oddat,SUMAD,0)) AS PREDD,
					SUM(IF(DIZ<$oddat,SUMAP,0)) AS PREDP,
					SUM(IF (DIZ>=$oddat AND DIZ < $dodat ,SUMAD,0)) AS POSLED,
					SUM(IF(DIZ>=$oddat AND DIZ < $dodat ,SUMAP,0)) AS POSLEP,
					IF(SUM(IF(DIZ<$dodat,SUMAD,0))>SUM(IF(DIZ<$dodat,SUMAP,0)),SUM(SUMAD)-SUM(SUMAP),0) AS PREDSD,
					IF(SUM(IF(DIZ<$dodat,SUMAP,0))>SUM(IF(DIZ<$dodat,SUMAD,0)),SUM(SUMAP)-SUM(SUMAD),0) AS PREDSP,
					IF(SUM(IF (DIZ>=$oddat AND DIZ < $dodat ,SUMAD,0))>SUM(IF (DIZ>=$oddat AND DIZ < $dodat ,SUMAP,0)),SUM(IF (DIZ>=$oddat AND DIZ < $dodat ,SUMAD,0))-SUM(IF (DIZ>=$oddat AND DIZ < $dodat ,SUMAP,0)),0) AS D,
					IF(SUM(IF (DIZ>=$oddat AND DIZ < $dodat ,SUMAP,0))>SUM(IF (DIZ>=$oddat AND DIZ < $dodat ,sumad,0)),SUM(IF (DIZ>=$oddat AND DIZ < $dodat ,SUMAP,0))-SUM(IF (DIZ>=$oddat AND DIZ < $dodat ,SUMAD,0)),0) AS P
					FROM yana JOIN konta ON konta.konto = yana.konto join firmi on firmi.cod=yana.korisnik
					WHERE yana.godina=$godina AND konta.godina=$godina AND KORISNIK <>'000000'  AND konta.GRUPA_2 = '1' and konta.p_r=''   $str_obvrski  $str_pobaruvanja $str_obvrski_pob
					GROUP BY opis, KONTO WITH ROLLUP ";
					}
		
			}
			else { //so valuta
				if(!EMPTY($_POST['korisnik']))
				{	
					$selekt = "SELECT firmi.opis,KORISNIK, yana.KONTO ,
					SUM(IF(DATUM<$oddat,SUMAD,0)) AS PREDD,
					SUM(IF(DATUM<$oddat,SUMAP,0)) AS PREDP,
					SUM(IF (DATUM>=$oddat AND DATUM <= $dodat ,SUMAD,0)) AS POSLED,
					SUM(IF(DATUM>=$oddat AND DATUM <= $dodat ,SUMAP,0)) AS POSLEP,
					IF(SUM(IF(DATUM<$dodat,SUMAD,0))>SUM(IF(DATUM<$dodat,SUMAP,0)),SUM(SUMAD)-SUM(SUMAP),0) AS PREDSD,
					IF(SUM(IF(DATUM<$dodat,SUMAP,0))>SUM(IF(DATUM<$dodat,SUMAD,0)),SUM(SUMAP)-SUM(SUMAD),0) AS PREDSP,
					IF(SUM(IF (DATUM>=$oddat AND DATUM <= $dodat ,SUMAD,0))>SUM(IF (DATUM>=$oddat AND DATUM <= $dodat ,SUMAP,0)),SUM(IF (DATUM>=$oddat AND DATUM <= $dodat ,SUMAD,0))-SUM(IF (DATUM>=$oddat AND DATUM <= $dodat ,SUMAP,0)),0) AS DOLZI,
					IF(SUM(IF (DATUM>=$oddat AND DATUM <= $dodat ,SUMAP,0))>SUM(IF (DATUM>=$oddat AND DATUM <= $dodat ,SUMAD,0)),SUM(IF (DATUM>=$oddat AND DATUM <= $dodat ,SUMAP,0))-SUM(IF (DATUM>=$oddat AND DATUM <= $dodat ,SUMAD,0)),0) AS POBARUVA
					FROM yana JOIN konta ON konta.konto = yana.konto join firmi on firmi.cod=yana.korisnik
					WHERE yana.godina=$godina AND konta.godina=$godina AND konta.GRUPA_2 = '1' and konta.p_r=''  $str_obvrski  $str_pobaruvanja $str_korisnik $str_obvrski_pob
					GROUP BY opis, KONTO WITH ROLLUP";
				}
				else
				{
					$selekt = "SELECT firmi.opis,KORISNIK, yana.KONTO ,
					SUM(IF(DATUM<$oddat,SUMAD,0)) AS PREDD,
					SUM(IF(DATUM<$oddat,SUMAP,0)) AS PREDP,
					SUM(IF (DATUM>=$oddat AND DATUM <= $dodat ,SUMAD,0)) AS POSLED,
					SUM(IF(DATUM>=$oddat AND DATUM <= $dodat ,SUMAP,0)) AS POSLEP,
					IF(SUM(IF(DATUM<$dodat,SUMAD,0))>SUM(IF(DATUM<$dodat,SUMAP,0)),SUM(SUMAD)-SUM(SUMAP),0) AS PREDSD,
					IF(SUM(IF(DATUM<$dodat,SUMAP,0))>SUM(IF(DATUM<$dodat,SUMAD,0)),SUM(SUMAP)-SUM(SUMAD),0) AS PREDSP,
					IF(SUM(IF (DATUM>=$oddat AND DATUM <= $dodat ,SUMAD,0))>SUM(IF (DATUM>=$oddat AND DATUM <= $dodat ,SUMAP,0)),SUM(IF (DATUM>=$oddat AND DATUM <= $dodat ,SUMAD,0))-SUM(IF (DATUM>=$oddat AND DATUM <= $dodat ,SUMAP,0)),0) AS DOLZI,
					IF(SUM(IF (DATUM>=$oddat AND DATUM <= $dodat ,SUMAP,0))>SUM(IF (DATUM>=$oddat AND DATUM <= $dodat ,sumad,0)),SUM(IF (DATUM>=$oddat AND DATUM <= $dodat ,SUMAP,0))-SUM(IF (DATUM>=$oddat AND DATUM <= $dodat ,SUMAD,0)),0) AS POBARUVA
					FROM yana JOIN konta ON konta.konto = yana.konto join firmi on firmi.cod=yana.korisnik
					WHERE yana.godina=$godina AND konta.godina=$godina AND KORISNIK <>'000000' AND konta.GRUPA_2 = '1' and konta.p_r=''  $str_obvrski  $str_pobaruvanja $str_obvrski_pob
					GROUP BY opis, KONTO WITH ROLLUP ";
				}
				
			}
			
			//$handle=anyconnect($_SESSION['fin_baza']);
			$rezultat=mysqli_query($handle0, $selekt) or die("Error in query:" . $selekt . " <hr> error test:" . mysqli_error());
			//echo $selekt;
			
			echo '<table border=1 cellspacing="0" cellpadding="0" width ="100%" style="font-size: 25px">';	
			if (isset($_POST['totali'])){
				//echo "<td width='7%'><b>" . "&nbsp;" . "</td>" ;
			}else{		
			echo "<td width='7%' align = 'center'><b>" . "Конто" . "</td>" ;}
			echo "<td align = 'center' width='29%'><b>" . "Корисник" . "</td>";
			echo "<td width='11%' align = 'center'><b>" . "Пр.салдо должи" . "</td>";
			echo "<td width='11%' align = 'center'><b>" . "Пр.салдо побарува" . "</td>";
			echo "<td width='11%' align = 'center'><b>" . "Должи" . "</td>";
			echo "<td width='11%' align = 'center'><b>" . "Побарува" . "</td>";
			echo "<td width='11%' align = 'center'><b>" . "Салдо должи" . "</td>";
			echo "<td width='11%' align = 'center'><b>" . "Салдо побарува" . "</td>";
	 		
				
		WHILE ($recs = mysqli_fetch_array($rezultat))
		{
		
				$KORISNIK=$recs['opis'];
				$KONTO = $recs['KONTO'];
				$PREDD=$recs['PREDD'];
				$PREDP=$recs['PREDP'];
				$POSLED=$recs['POSLED'];
				$POSLEP=$recs['POSLEP'];
				$D = $recs['PREDSD'];
				$P = $recs['PREDSP'];
				
			echo "<tr>";
			IF(!is_null($KORISNIK))
			{
				//$reskor = mysqli_query($handle0, "select opis from firmi where cod = '$KORISNIK'");
				$korrow = $KORISNIK;
				if(isset($_POST['totali']))
				{
					IF(is_null($KONTO))
					{	
						//echo "<td width='7%'>" . "&nbsp;" ."Вкупно за:". "</td>" ;	
						echo "<td align = 'center' width='29%'>" . $korrow ."</td>";
						echo "<td style='color:red' width='11%' align = 'right' >" . number_format($PREDD,0) ."</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>" . number_format($PREDP,0) ."</td>";
						echo "<td style='color:red' width='11%' align = 'right'>" .number_format($POSLED,0) . "</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>" .number_format($POSLEP,0) . "</td>";
						echo "<td style='color:red' width='11%' align = 'right'>" .number_format($D,0) . "</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>" .number_format($P,0) . "</td>";
					}
				}
				else 
				{
						echo "<td width='7%' align = 'center'>" . $KONTO. "</td>" ;	
						IF(is_null($KONTO))
						{
							echo "<td align = 'center' width='29%'>" . "<b>".$korrow ."</td>";
						}	
						ELSE
						{
							echo "<td width='29%'>" . "&nbsp;" . "</td>" ;
						}
						IF(is_null($KONTO))
						{
						echo "<td style='color:red'' width='11%' align = 'right' >" ."<b>". number_format($PREDD,0) ."</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>" . "<b>".number_format($PREDP,0) ."</td>";
						echo "<td style='color:red' width='11%' align = 'right'>" ."<b>".number_format($POSLED,0) . "</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>" ."<b>".number_format($POSLEP,0) . "</td>";
						echo "<td style='color:red' width='11%' align = 'right'>" ."<b>".number_format($D,0) . "</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>" ."<b>".number_format($P,0) . "</td>";
						}
						else 
						{
						echo "<td style='color:red' width='11%' align = 'right' >" . number_format($PREDD,0) ."</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>" . number_format($PREDP,0) ."</td>";
						echo "<td style='color:red' width='11%' align = 'right'>" .number_format($POSLED,0) . "</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>" .number_format($POSLEP,0) . "</td>";
						echo "<td style='color:red' width='11%' align = 'right'>" .number_format($D,0) . "</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>" .number_format($P,0) . "</td>";	
						}
				}
			}		
								
			echo "</tr>";
			
				//totalot
			IF (is_null($KONTO) && is_null($KORISNIK)){
				if (isset($_POST['totali'])){
					echo "<tr>";
					echo "<td width='11%' align = 'center' >" . "<b>ВКУПНО</b>" ."</td>";
					echo "<td style='color:red' width='11%' align = 'right' >" . number_format($PREDD,0) ."</td>";
					echo "<td style='color:blue' width='11%' align = 'right'>" . number_format($PREDP,0) ."</td>";
					echo "<td style='color:red' width='11%' align = 'right'>" .number_format($POSLED,0) . "</td>";
					echo "<td style='color:blue' width='11%' align = 'right'>" .number_format($POSLEP,0) . "</td>";
					echo "<td style='color:red' width='11%' align = 'right'>" .number_format($D,0) . "</td>";
					echo "<td style='color:blue' width='11%' align = 'right'>" .number_format($P,0) . "</td>";	
					echo "</tr>";
				}else{
					echo "<tr>";
					echo "<td width='11%' align = 'center' >" . "<b></b>" ."</td>";
					echo "<td width='11%' align = 'center' >" . "<b>ВКУПНО</b>" ."</td>";
					echo "<td style='color:red' width='11%' align = 'right' >" . number_format($PREDD,0) ."</td>";
					echo "<td style='color:blue' width='11%' align = 'right'>" . number_format($PREDP,0) ."</td>";
					echo "<td style='color:red' width='11%' align = 'right'>" .number_format($POSLED,0) . "</td>";
					echo "<td style='color:blue' width='11%' align = 'right'>" .number_format($POSLEP,0) . "</td>";
					echo "<td style='color:red' width='11%' align = 'right'>" .number_format($D,0) . "</td>";
					echo "<td style='color:blue' width='11%' align = 'right'>" .number_format($P,0) . "</td>";	
					echo "</tr>";
				}
			}
		}
			echo "</table>";
		
}

}//vkupno


if(isset($_POST['Analitika']) && ($_POST['radioanalitika'] == 'analitika_se'))
{
	$_SESSION['klik']='Analitika';

	//echo "<a href='fpdffinarep2.php' target='_blank'>Печати PDF<a><br/><br\>";
	//echo "<a href='xls_ed.php' target='_blank'>Печати Excel<a><br/>";
	
	if(isset($_POST['totali'])){

		if ($_POST['radiokopce'] == 'od_do'){
			
			if(empty($_POST['korisnik'])){
				$selekt = "select firmi.opis, yana.KORISNIK, yana.KONTO,yana.DATUM,yana.DIZ,yana.BROJ,yana.ORG_E,yana.MESTO_TROSOK,sum(round(SUMAD,2)) as D,sum(round(SUMAP,2)) as P,
				IF(sum(SUMAD)>sum(SUMAP),SUM(ROUND(SUMAD-SUMAP,2)),0) AS SD, IF(sum(SUMAP)>sum(SUMAD),SUM(ROUND(SUMAP-SUMAD,2)),0) AS SP
				from yana left join konta on konta.KONTO = yana.KONTO join firmi on firmi.cod=yana.korisnik
				where yana.godina=$godina AND konta.godina=$godina AND DIZ between $oddat and $dodat AND konta.GRUPA_2='1' and konta.p_r=''   $str_obvrski  $str_pobaruvanja $str_obvrski_pob
				group by opis, KONTO,BROJ  with rollup";
			}
			else
			{
				$selekt = "select firmi.opis,yana.KORISNIK, yana.KONTO,yana.DATUM,yana.DIZ,yana.BROJ,yana.ORG_E,yana.MESTO_TROSOK,sum(round(SUMAD,2)) as D,sum(round(SUMAP,2)) as P,
				IF(sum(SUMAD)>sum(SUMAP),SUM(ROUND(SUMAD-SUMAP,2)),0) AS SD, IF(sum(SUMAP)>sum(SUMAD),SUM(ROUND(SUMAP-SUMAD,2)),0) AS SP
				from yana left join konta on konta.KONTO = yana.KONTO join firmi on firmi.cod=yana.korisnik
				where yana.godina=$godina AND konta.godina=$godina AND DIZ between $oddat and $dodat AND konta.GRUPA_2='1' and konta.p_r='' $str_korisnik  $str_obvrski  $str_pobaruvanja $str_obvrski_pob
				group by opis, KONTO,BROJ  with rollup";
			}
			
		}
		else //totali od_do valuta
		{
			
			$selekt = "select firmi.opis,yana.KORISNIK,yana.KONTO,yana.DATUM,yana.DIZ,yana.BROJ,yana.ORG_E,yana.MESTO_TROSOK,sum(round(SUMAD,2)) as D,sum(round(SUMAP,2)) as P,
			IF(sum(SUMAD)>sum(SUMAP),SUM(ROUND(SUMAD-SUMAP,2)),0) AS SD, IF(sum(SUMAP)>sum(SUMAD),SUM(ROUND(SUMAP-SUMAD,2)),0) AS SP
			from yana left join konta on konta.KONTO = yana.KONTO join firmi on firmi.cod=yana.korisnik
			where yana.godina=$godina AND konta.godina=$godina AND DIZ between $oddat and $dodat AND konta.GRUPA_2='1' and konta.p_r='' $str_korisnik  $str_obvrski  $str_pobaruvanja $str_obvrski_pob
			group by opis, KONTO,BROJ with rollup";
			
		}
		//$_SESSION['query'] = 'tot';
	}
	else //nema totali
	{
		
		if ($_POST['radiokopce'] == 'od_do'){
			
			if(empty($_POST['korisnik'])){
				$selekt = "select firmi.opis, yana.KORISNIK, yana.KONTO,DATUM,DIZ,BROJ,yana.ORG_E,yana.MESTO_TROSOK,sum(round(SUMAD,2)) as D,sum(round(sumap,2)) as P,
				IF(sum(SUMAD)>sum(SUMAP),SUM(ROUND(SUMAD-SUMAP,2)),0) AS SD, IF(sum(SUMAP)>sum(SUMAD),SUM(ROUND(SUMAP-SUMAD,2)),0) AS SP
				from yana left join konta on konta.KONTO = yana.KONTO join firmi on firmi.cod=yana.korisnik
				where yana.godina=$godina AND konta.godina=$godina AND DIZ between $oddat and $dodat AND konta.GRUPA_2='1' and konta.p_r=''   $str_obvrski  $str_pobaruvanja $str_obvrski_pob
				group by opis, KONTO,BROJ,DIZ with rollup";
			}
			else{
				$selekt = "select firmi.opis,yana.KORISNIK, yana.KONTO,DATUM,DIZ,BROJ,yana.ORG_E,yana.MESTO_TROSOK,sum(round(SUMAD,2)) as D,sum(round(sumap,2)) as P,
				IF(sum(SUMAD)>sum(SUMAP),SUM(ROUND(SUMAD-SUMAP,2)),0) AS SD, IF(sum(SUMAP)>sum(SUMAD),SUM(ROUND(SUMAP-SUMAD,2)),0) AS SP
				from yana left join konta on konta.KONTO = yana.KONTO join firmi on firmi.cod=yana.korisnik
				where yana.godina=$godina AND konta.godina=$godina AND DIZ between $oddat and $dodat AND konta.GRUPA_2='1' and konta.p_r='' $str_korisnik  $str_obvrski  $str_pobaruvanja $str_obvrski_pob
				group by opis,KONTO,BROJ,DIZ with rollup";
			}
			
		}
		else{
			if (empty($_POST['korisnik'])){
				$selekt = "select firmi.opis,yana.KORISNIK, yana.KONTO,DATUM,DIZ,BROJ,yana.ORG_E,yana.MESTO_TROSOK,sum(round(SUMAD,2)) as D,sum(round(sumap,2)) as P,
				IF(sum(SUMAD)>sum(SUMAP),SUM(ROUND(SUMAD-SUMAP,2)),0) AS SD, IF(sum(SUMAP)>sum(SUMAD),SUM(ROUND(SUMAP-SUMAD,2)),0) AS SP
				from yana left join konta on konta.KONTO = yana.KONTO join firmi on firmi.cod=yana.korisnik
				where yana.godina=$godina AND konta.godina=$godina AND DATUM between $oddat and $dodat AND konta.GRUPA_2='1' and konta.p_r=''  $str_obvrski  $str_pobaruvanja $str_obvrski_pob
				group by opis, KONTO,BROJ,DIZ with rollup";
			}
			else{
				$selekt = "select firmi.opis,yana.KORISNIK, yana.KONTO,DATUM,DIZ,BROJ,yana.ORG_E,yana.MESTO_TROSOK,sum(round(SUMAD,2)) as D,sum(round(sumap,2)) as P,
				IF(sum(SUMAD)>sum(SUMAP),SUM(ROUND(SUMAD-SUMAP,2)),0) AS SD, IF(sum(SUMAP)>sum(SUMAD),SUM(ROUND(SUMAP-SUMAD,2)),0) AS SP
				from yana left join konta on konta.KONTO = yana.KONTO join firmi on firmi.cod=yana.korisnik
				where yana.godina=$godina AND konta.godina=$godina AND DATUM between $oddat and $dodat AND konta.GRUPA_2='1' and konta.p_r='' $str_korisnik $str_obvrski  $str_pobaruvanja
				group by opis, KONTO,BROJ,DIZ with rollup";
			}
		}
		
	}
	//$handle=anyconnect($_SESSION['fin_baza']);
	$rezultat=mysqli_query($handle0, $selekt) or die("Error in query:" . $selekt . " <hr> error test:" . mysqli_error());
	//echo $selekt;
	echo '<table border=1 cellspacing="0" cellpadding="0" width ="100%" style="font-size: 25px">';
	echo "<tr>" ;
	if(isset($_POST['totali']))
	{
	echo "<th width='50%'>" . "Број на документ" . "</th>";
	}
	else
	{
	echo "<th  width ='10%'>Датум</th>";
	echo "<th  width='10%' > Валута </th>";
	echo "<th  width='30%'>" . "Број на документ" . "</th>";
	}
	
	echo "<th   width='15%'>" . "Должи" . "</th>";
	echo "<th width='15%'>" . "Побарува" . "</th>";
	echo "<th width='10%'>" . "Салдо Д" . "</th>";
	echo "<th width='10%'>" . "Салдо П" . "</th>";
	echo "</tr>";
	
	while($row=mysqli_fetch_array($rezultat)){
		
		$KORISNIK=$row['opis'];
		$KONTO = $row['KONTO'];
		$BROJ=$row['BROJ'];
		$DATUM=$row['DIZ'];
		$VALUTA=$row['DATUM'];
		$D=$row['D'];
		$P=$row['P'];
		$SD=$row['SD'];
		$SP=$row['SP'];
		
		//$reskor = mysqli_query($handle0, "select opis from firmi where cod = '$KORISNIK'");
		$korrow = $KORISNIK;
		
		if(!isset($_POST['totali']))
		{
			if($D!=0 )
			{
				$D=number_format($D,0);
			}
			elseif(!is_null($DATUM))
			{
				$D='&nbsp';
			}
			if($P!=0 )
			{
				$P=number_format($P,0);
			}
			elseif(!is_null($DATUM))
			{
				$P='&nbsp';
			}
		}
		else
		{
			$D=number_format($D,0);
			$P=number_format($P,0);
		}
		if(is_null($DATUM) and !is_null($VALUTA))
		{
			$VALUTA='&nbsp';
		}
		
		if ((!is_null($VALUTA) and !is_null($BROJ))) {
		
			echo "<tr>";
				
			if(isset($_POST['totali']))
			{
				echo "<td align = 'center' >" . $BROJ."</td>";
			}
			else
			{
				if(!is_null($DATUM))
				{
					echo "<td align = 'center'>" . $DATUM . "</td>";
					echo "<td align = 'center'>" . $VALUTA . "</td>";
				}
				else
				{
					echo "<td align = 'center'>" . $DATUM . "</td>";
					echo "<td align = 'center'>" . $VALUTA . "</td>";
				}
				if(!is_null($DATUM))
				{
					echo "<td align = 'center'>" . $BROJ ."</td>";
				}
				else
				{
					echo "<td align = 'center'>" . $BROJ ."</td>";
				}
			}
			if(!is_null($DATUM))
			{
					
				echo "<td style='color:red' align = 'right' >" .$D ."</td>";
				echo "<td style='color:blue' align = 'right'>" . $P ."</td>";
			}
			else
			{
		
				echo "<td style='color:red' align = 'right' >" .$D ."</td>";
				echo "<td style='color:blue' align = 'right'>" .$P ."</td>";
			}
			if(!is_null($DATUM) and !isset($_POST['totali']))
			{
				
			}
			elseif(isset($_POST['totali']))
			{
				echo "<td  style='color:red' align = 'right'>" .number_format($SD,0) . "</td>";
				echo "<td  style='color:blue' align = 'right'>" .number_format($SP,0) . "</td>";
			}
			else{
				echo "<td style='color:red' align = 'right'>" .number_format($SD,0) . "</td>";
				echo "<td style='color:blue' align = 'right'>" .number_format($SP,0) . "</td>";
			}
				
			echo "</tr>";
		}
		else
		{
			if((is_null($DATUM) and is_null($BROJ))) {
				echo "<tr>";
				if(isset($_POST['totali']))
				{
					echo "<td align = 'center'><b>" . $KONTO . "</td>";
					echo "<td align = 'center'>" ."&nbsp;".$BROJ."</td>";
				}
				else
				{
					if(is_null($KONTO) and !is_null($KORISNIK))
					{
						echo "<td>" . "&nbsp;" . "</td>";
						echo "<td align = 'center'><b>" .  "</td>";
						echo "<td align = 'center'><b>" ."&nbsp;".'Вкупно зa '.$korrow."</td>";
					}
					else
					{
						echo "<td>" . "&nbsp;" . "</td>";
						echo "<td align = 'center'><b>" . $KONTO . "</td>";
						echo "<td align = 'center'><font size='2'><b>" ."&nbsp;". $BROJ ."</td>";
					}
				}
				echo "<td style='color:red' align = 'right'><b>" .$D ."</b></td>";
				echo "<td style='color:blue' align = 'right'><b>" . $P ."</b></td>";
				echo "<td style='color:red' align = 'right'><b>" . number_format($SD,0) ."</b></td>";
				echo "<td style='color:blue' align = 'right'><b>" . number_format($SP,0) ."</b></td>";
				echo "</tr>";
		
			}elseif(!is_null($DATUM and is_null($BROJ))) {
				echo "<tr>";
				if(isset($_POST['totali']))
				{
					if(is_null($KONTO) and !is_null($row['KORISNIK']))
					{
						echo "<td align = 'right'><b>" . $KONTO."&nbsp;"." Вкупно за"."&nbsp;"."&nbsp;".$row['opis']." ".$BROJ ."</td>";
					}
					else
					{
						echo "<td align = 'center'><b>" . $KONTO." ".$BROJ ."</td>";
					}
				}
				else
				{
					echo "<td>" . "&nbsp;" . "</td>";
					echo "<td>" ."&nbsp;". $VALUTA. "</td>";
					echo "<td align = 'center'>". $BROJ ."</td>";
				}
		
				if(!is_null($row['KORISNIK']))
				{
		
					echo "<td style='color:red'  align = 'right' <b>" . $D ."</b></td>";
					echo "<td style='color:blue' align = 'right' <b>" .$P ."</b></td>";
					echo "<td style='color:red' align = 'right' <b>" .number_format($SD,0) . "</b></td>";
					echo "<td style='color:blue' align = 'right' <b>" .number_format($SP,0) . "</b></td>";
				}
				
		
			}
		}
		}
		echo "</table>";
		}


if(isset($_POST['Analitika']) && ($_POST['radioanalitika'] == 'analitika_ios'))
{
	$_SESSION['klik']='Analitika';
	
	//echo "<a href='fpdffinarep2.php' target='_blank'>Печати PDF<a><br/><br\>";
	//echo "<a href='xls_ed.php' target='_blank'>Печати Excel<a><br/>";
	
	if (empty($_POST['korisnik'])){
		echo "ВНЕСЕТЕ КОРИСНИК!";
		return;
	}
	
	if (isset($_POST['totali']))
	{
		
		if($_POST['radiokopce'] == 'od_do')
		{
			
			$selekt = "select firmi.opis,yana.KORISNIK,yana.KONTO,DATUM,DIZ,BROJ,yana.ORG_E,yana.MESTO_TROSOK,sum(round(SUMAD,2)) as D,sum(round(sumap,2)) as P,
			IF(sum(SUMAD)>sum(SUMAP),SUM(ROUND(SUMAD-SUMAP,2)),0) AS SD, IF(sum(SUMAP)>sum(SUMAD),SUM(ROUND(SUMAP-SUMAD,2)),0) AS SP
			from yana left join konta on konta.KONTO = yana.KONTO join firmi on firmi.cod=yana.korisnik
			where yana.godina=$godina AND konta.godina=$godina AND diz between $oddat and $dodat AND konta.GRUPA_2='1' and konta.p_r='' AND O_Z=''  $str_korisnik  $str_obvrski  $str_pobaruvanja $str_obvrski_pob
			group by opis,KONTO,BROJ with rollup";
			
		}
		else
		{
			$selekt = "select firmi.opis,yana.KORISNIK,yana.KONTO,DATUM,DIZ,BROJ,yana.ORG_E,yana.MESTO_TROSOK,sum(round(SUMAD,2)) as D,sum(round(sumap,2)) as P,
			IF(sum(SUMAD)>sum(SUMAP),SUM(ROUND(SUMAD-SUMAP,2)),0) AS SD, IF(sum(SUMAP)>sum(SUMAD),SUM(ROUND(SUMAP-SUMAD,2)),0) AS SP
			from yana left join konta on konta.KONTO = yana.KONTO join firmi on firmi.cod=yana.korisnik
			where yana.godina=$godina AND konta.godina=$godina AND datum between $oddat and $dodat AND konta.GRUPA_2='1' and konta.p_r='' AND O_Z='' $str_korisnik $str_obvrski  $str_pobaruvanja $str_obvrski_pob
			group by opis,KONTO,BROJ with rollup";
		}
		
	}
	else //bez totali
	{
		
		if($_POST['radiokopce'] == 'od_do')
		{
			
			$selekt = "select firmi.opis,yana.KORISNIK,yana.KONTO,DATUM,DIZ,BROJ,yana.ORG_E,yana.MESTO_TROSOK ,sum(round(SUMAD,2)) as D,sum(round(sumap,2)) as P,
			IF(sum(SUMAD)>sum(SUMAP),SUM(ROUND(SUMAD-SUMAP,2)),0) AS SD, IF(sum(SUMAP)>sum(SUMAD),SUM(ROUND(SUMAP-SUMAD,2)),0) AS SP
			from yana left join konta on konta.KONTO = yana.KONTO join firmi on firmi.cod=yana.korisnik
			where yana.godina=$godina AND konta.godina=$godina AND diz between $oddat and $dodat AND konta.GRUPA_2='1' and konta.p_r='' AND O_Z='' $str_korisnik $str_obvrski  $str_pobaruvanja $str_obvrski_pob
			group by opis,KONTO,BROJ,DIZ with rollup";
			
		}
		else
		{
			$selekt = "select firmi.opis,yana.KORISNIK,yana.KONTO,DATUM,DIZ,BROJ,yana.ORG_E,yana.MESTO_TROSOK ,sum(round(SUMAD,2)) as D,sum(round(sumap,2)) as P,
			IF(SUMAD>SUMAP,SUM(ROUND(SUMAD-SUMAP,2)),0) AS SD, IF(SUMAP>SUMAD,SUM(ROUND(SUMAP-SUMAD,2)),0) AS SP
			from yana left join konta on konta.KONTO = yana.KONTO join firmi on firmi.cod=yana.korisnik
			where yana.godina=$godina AND konta.godina=$godina AND datum between $oddat and $dodat AND konta.GRUPA_2='1' and konta.p_r='' AND O_Z='' $str_korisnik $str_obvrski  $str_pobaruvanja $str_obvrski_pob
			group by opis,KONTO,BROJ,DIZ with rollup";
		}
		
	}
	
	//$handle=anyconnect($_SESSION['fin_baza']);
	$rezultat=mysqli_query($handle0, $selekt) or die("Error in query:" . $selekt . " <hr> error test:" . mysqli_error());
	
	echo '<table border=1 cellspacing="0" cellpadding="0" width ="100%" style="font-size: 25px">';
	
	if(isset($_POST['totali']))
	{
		echo "<th width='50%'>" . "Број на документ" . "</th>";
	}
	else
	{
		echo "<th  width ='10%'>Датум</th>";
		echo "<th  width='10%' > Валута </th>";
		echo "<th  width='30%'>" . "Број на документ" . "</th>";
	}

	echo "<th   width='15%'>" . "Должи" . "</th>";
	echo "<th width='15%'>" . "Побарува" . "</th>";
	echo "<th width='10%'>" . "Салдо Д" . "</th>";
	echo "<th width='10%'>" . "Салдо П" . "</th>";

	WHILE ($recs = mysqli_fetch_array($rezultat)){

		$KORISNIK=$recs['opis'];
		//$reskor = mysqli_query($handle0, "select opis from firmi where cod = '$KORISNIK'");
		$korrow = $KORISNIK;
		$KONTO = $recs['KONTO'];
		$BROJ=$recs['BROJ'];
		$DATUM=$recs['DIZ'];
		$VALUTA=$recs['DATUM'];
		$D=$recs['D'];
		$P=$recs['P'];
		$SD=$recs['SD'];
		$SP=$recs['SP'];
		if(!isset($_POST['totali']))
		{
			if($D!=0 )
			{
			$D=number_format($D,0);
			}
			elseif(!is_null($DATUM) )
			{
				$D='&nbsp';
			}
			if($P!=0 )
			{
			$P=number_format($P,0);
			}
			elseif(!is_null($DATUM))
			{
				$P='&nbsp';
			}
		}
		else
		{
			$D=number_format($D,0);
			$P=number_format($P,0);
		}
		
		if(is_null($DATUM) and !is_null($VALUTA))
		{
			$VALUTA='&nbsp';
		}
		
			if ((!is_null($VALUTA) and !is_null($BROJ))) {

			echo "<tr>";
			if(isset($_POST['totali']))
			{
				echo "<td align = 'center' >" . $BROJ."</td>";
			}
			else {
				if(!is_null($DATUM))
				{
					echo "<td align = 'center'>" . $DATUM . "</td>";
					echo "<td align = 'center'>" . $VALUTA . "</td>";
				}
				else 
				{
					echo "<td align = 'center'>" . $DATUM . "</td>";
					echo "<td align = 'center'>" . $VALUTA . "</td>";
				}	
				if(!is_null($DATUM))
				{
				echo "<td align = 'center'>" . $BROJ ."</td>";
				}
				else
				{
					echo "<td align = 'center'>" . $BROJ ."</td>";
				}
			}
			if(!is_null($DATUM))
			{
			echo "<td style='color:red' align = 'right' >" .$D ."</td>";
			echo "<td style='color:blue' align = 'right'>" . $P ."</td>";
			}
			else
			{
			echo "<td style='color:red' align = 'right' >" .$D ."</td>";
			echo "<td style='color:blue' align = 'right'>" .$P ."</td>";
			}
			if(!is_null($DATUM) and !isset($_POST['totali']))
			{
			echo "<td style='color:red' align = 'right'>".' '. "</td>";
			echo "<td style='color:blue' align = 'right'>".' '. "</td>";
			}
			elseif(isset($_POST['totali']))
			{
			echo "<td  style='color:red' align = 'right'>" .number_format($SD,0) . "</td>";
			echo "<td  style='color:blue' align = 'right'>" .number_format($SP,0) . "</td>";
			}
			else
			{
			echo "<td style='color:red' align = 'right'>" .number_format($SD,0) . "</td>";
			echo "<td style='color:blue' align = 'right'>" .number_format($SP,0) . "</td>";
			}
			echo "</tr>";
		}else
		{
			if((is_null($DATUM) and is_null($BROJ))) {
				echo "<tr>";
				if(isset($_POST['totali']))
				{
					echo "<td align = 'center'><b>" . $KONTO . "</td>";
					echo "<td align = 'center'>" ."&nbsp;". $BROJ ."</td>";
				}
				else {
					echo "<td >" . "&nbsp;" . "</td>";
					echo "<td align = 'center'><b>" . $KONTO . "</td>";
					echo "<td align = 'center'><b>" ."&nbsp;". $BROJ ."</td>";
				}
					echo "<td style='color:red' align = 'right'><b>" .$D ."</b></td>";
					echo "<td style='color:blue' align = 'right'><b>" . $P ."</b></td>";
					echo "<td style='color:red' align = 'right'><b>" . number_format($SD,0) ."</b></td>";
					echo "<td style='color:blue' align = 'right'><b>" . number_format($SP,0) ."</b></td>";
					echo "</tr>";

			}elseif(!is_null($VALUTA and is_null($BROJ))) {
				echo "<tr>";
				if(isset($_POST['totali']))
				{
					echo "<td align = 'center'><b>" . $KONTO." ".$BROJ ."</td>";
				}
				else
				{ 
					echo "<td>" . "&nbsp;" . "</td>";
					echo "<td>" ."&nbsp;". $VALUTA . "</td>";
					echo "<td align = 'center'>". $BROJ ."</td>";
				}
				echo "<td style='color:red' align = 'right' <b>" . $D ."</b></td>";
				echo "<td style='color:blue' align = 'right' <b>" .$P ."</b></td>";
				echo "<td style='color:red' align = 'right' <b>" .number_format($SD,0) . "</b></td>";
				echo "<td style='color:blue' align = 'right' <b>" .number_format($SP,0) . "</b></td>";
				echo "</tr>";

			}
		}
	}
	echo "</table>";
}

if(isset($_POST['Analitika']) && ($_POST['radioanalitika'] == 'analitika_datum'))
{
	$_SESSION['klik']='Analitika';

	//echo "<a href='fpdffinarep2.php' target='_blank'>Печати PDF<a><br/><br\>";
	//echo "<a href='xls_ed.php' target='_blank'>Печати Excel<a><br/>";

	if (empty($_POST['korisnik'])){
		echo "ВНЕСЕТЕ КОРИСНИК!";
		return;
	}


		if($_POST['radiokopce'] == 'od_do')
		{
				
			$selekt = "select firmi.opis,yana.KORISNIK,yana.KONTO,DIZ,sum(round(SUMAD,2)) as D,sum(round(sumap,2)) as P,
			IF(sum(SUMAD)>sum(SUMAP),SUM(ROUND(SUMAD-SUMAP,2)),0) AS SD, IF(sum(SUMAP)>sum(SUMAD),SUM(ROUND(SUMAP-SUMAD,2)),0) AS SP
			from yana left join konta on konta.KONTO = yana.KONTO join firmi on firmi.cod=yana.korisnik
			where yana.godina=$godina AND konta.godina=$godina AND diz between $oddat and $dodat AND konta.GRUPA_2='1' and konta.p_r='' AND O_Z='' $str_korisnik $str_obvrski  $str_pobaruvanja $str_obvrski_pob
			group by opis,KONTO,DIZ with rollup";
				
		}
		else
		{
			$selekt = "select firmi.opis,yana.KORISNIK,yana.KONTO,DATUM,sum(round(SUMAD,2)) as D,sum(round(sumap,2)) as P,
			IF(sum(SUMAD)>sum(SUMAP),SUM(ROUND(SUMAD-SUMAP,2)),0) AS SD, IF(sum(SUMAP)>sum(SUMAD),SUM(ROUND(SUMAP-SUMAD,2)),0) AS SP
			from yana left join konta on konta.KONTO = yana.KONTO join firmi on firmi.cod=yana.korisnik
			where yana.godina=$godina AND konta.godina=$godina AND datum between $oddat and $dodat AND konta.GRUPA_2='1' and konta.p_r='' AND O_Z='' $str_korisnik $str_obvrski  $str_pobaruvanja $str_obvrski_pob
			group by opis,KONTO,DATUM with rollup";
			//echo $selekt;
		}


	//$handle=anyconnect($_SESSION['fin_baza']);
	$rezultat=mysqli_query($handle0, $selekt) or die("Error in query:" . $selekt . " <hr> error test:" . mysqli_error());

	echo '<table border=1 cellspacing="0" cellpadding="0" width ="100%" style="font-size: 25px">';

	if($_POST['radiokopce'] == 'od_do')
	{
		echo "<th  width ='10%'>Датум</th>";
	}
	else
	{
		echo "<th  width='10%' > Валута </th>";
	}

	echo "<th width='15%'>" . "Должи" . "</th>";
	echo "<th width='15%'>" . "Побарува" . "</th>";
	echo "<th width='10%'>" . "Салдо Д" . "</th>";
	echo "<th width='10%'>" . "Салдо П" . "</th>";

	WHILE ($recs = mysqli_fetch_array($rezultat)){

		$KORISNIK=$recs['opis'];
		//$reskor = mysqli_query($handle0, "select opis from firmi where cod = '$KORISNIK'");
		$korrow = $KORISNIK;
		$KONTO = $recs['KONTO'];
		if($_POST['radiokopce'] == 'od_do'){
			$DATUM=$recs['DIZ'];
		}else{
			$DATUM=$recs['DATUM'];
		}
		$D=$recs['D'];
		$P=$recs['P'];
		$SD=$recs['SD'];
		$SP=$recs['SP'];
		$D=number_format($D,0);
		$P=number_format($P,0);
		
		echo "<tr>";
		if (is_null($DATUM) && !is_null($KONTO)){
			echo "<td align = 'center'><b>" . $KONTO ."&nbsp</b></td>";
			echo "<td style='color:red' align = 'right'><b>" .$D ."</b></td>";
			echo "<td style='color:blue' align = 'right'><b>" . $P ."</b></td>";
			echo "<td style='color:red' align = 'right'><b>" . number_format($SD,0) ."</b></td>";
			echo "<td style='color:blue' align = 'right'><b>" . number_format($SP,0) ."</b></td>";
			echo "</tr>";
		}
		elseif (is_null($DATUM) && is_null($KONTO) && !is_null($KORISNIK)){
			echo "<td align = 'center'><b>" . "Вкупно за ". $korrow ."&nbsp</b></td>";
			echo "<td style='color:red' align = 'right'><b>" .$D ."</b></td>";
			echo "<td style='color:blue' align = 'right'><b>" . $P ."</b></td>";
			echo "<td style='color:red' align = 'right'><b>" . number_format($SD,0) ."</b></td>";
			echo "<td style='color:blue' align = 'right'><b>" . number_format($SP,0) ."</b></td>";
			echo "</tr>";
		}
		else
		{
			echo "<td align = 'center'>" .$DATUM ."&nbsp</td>";
			echo "<td style='color:red' align = 'right'>" .$D ."</td>";
			echo "<td style='color:blue' align = 'right'>" . $P ."</td>";
			echo "<td style='color:red' align = 'right'>" . number_format($SD,0) ."</td>";
			echo "<td style='color:blue' align = 'right'>" . number_format($SP,0) ."</td>";
			echo "</tr>";
		}

	}
	echo "</table>";
}



if (isset($selekt) && !EMPTY($selekt)){
	$_SESSION['selekt']=$selekt;
}
$_SESSION['oddat']=$oddat;
$_SESSION['dodat']=$dodat;

}

else           //ako ima odbrano povekje firmi(kombiniran izvestaj)
{
	
	if (isset($_POST['korisnik']) && !empty($_POST['korisnik']))
	{
		$Tabelakor = 'firmi';
		$danbr = reports_look_danbr($korisnik,$Tabelakor) ;
	}
	
	if(isset($_POST['Vkupno']))
	{
			//echo "<a href='fpdffinarep.php' target='_blank'>Печати PDF<a><br/><br\>";
			//echo "<a href='xls_komb.php' target='_blank'>Печати Excel<a><br/>";
		
		if (EMPTY($_POST['korisnik'])){
			echo "Внесете корисник!";
			return;
		}
		
		if(is_null($danbr) or $danbr=='')
		{
			if(is_null($korisnik))
			{
				echo "VNESETE KORISNIK";
			}
			else
			{
				echo "Фирмата: "."<big>".$korrow."</big>"."  "."нема внесено даночен број";
			}
			return ;
		}
		
		if ($_POST['radiokopce'] == 'od_do')       //izbran e period od\do
		{  
			if(isset($_POST['korisnik']) && !EMPTY($_POST['korisnik']))
			{
				
				echo '<table border=1 cellspacing="2" cellpadding="0" width ="100%" style="font-size: 25px">';
				echo "<td align = 'center' width='22%'><b>" . "Фирма" . "</td>";
				echo "<td align = 'center' width='5%'><b>" . "Конто" . "</td>" ;
				echo "<td width='11%' align = 'center'><b>" . "Должи" . "</td>";
				echo "<td width='11%' align = 'center'><b>" . "Побарува" . "</td>";
				echo "<td width='11%' align = 'center'><b>" . "Салдо должи" . "</td>";
				echo "<td width='11%' align = 'center'><b>" . "Салдо побарува" . "</td>";
				
				$sumad = null;
				$sumap = null;
				$sumasd = null;
				$sumasp = null;
				$i=0;
				
				foreach($_SESSION['fin_baza'] as $bazi){		

					$handle=anyconnect($bazi);
					$godina=mysqli_real_escape_string($handle, $godina);
					//$oddat=mysqli_real_escape_string($handle, $oddat);
					//$dodat=mysqli_real_escape_string($handle, $dodat);
					
				$query = "	SELECT '".$bazi."' AS 'DB',KORISNIK, yana.KONTO ,
				SUM(SUMAD) AS DOLZI, SUM(SUMAP) AS POBARUVA,
				if(sum(SUMAD)>sum(SUMAP),sum(SUMAD)-sum(SUMAP),0) as SD,
				if(sum(SUMAP)>sum(SUMAD),sum(SUMAP)-sum(SUMAD),0) as SP
				FROM yana  JOIN konta ON konta.KONTO = yana.KONTO JOIN firmi ON yana.KORISNIK=firmi.COD
				WHERE yana.godina=$godina AND konta.godina=$godina AND firmi.DANBR = '$danbr'   AND konta.GRUPA_2 = '1' and konta.p_r='' AND DIZ BETWEEN $oddat AND $dodat $str_obvrski  $str_pobaruvanja $str_obvrski_pob
				GROUP BY KORISNIK, KONTO WITH ROLLUP  ";
				
				$_SESSION['query']=" KORISNIK, yana.KONTO ,
				SUM(SUMAD) AS DOLZI, SUM(SUMAP) AS POBARUVA,
				if(sum(SUMAD)>sum(SUMAP),sum(SUMAD)-sum(SUMAP),0) as SD,
				if(sum(SUMAP)>sum(SUMAD),sum(SUMAP)-sum(SUMAD),0) as SP
				FROM yana  JOIN konta ON konta.KONTO = yana.KONTO JOIN firmi ON yana.KORISNIK=firmi.COD
				WHERE yana.godina=$godina AND konta.godina=$godina AND firmi.DANBR = '$danbr'   AND konta.GRUPA_2 = '1' and konta.p_r='' AND DIZ BETWEEN $oddat AND $dodat $str_obvrski  $str_pobaruvanja $str_obvrski_pob
				GROUP BY KORISNIK, KONTO WITH ROLLUP  ";
				//$_SESSION['query']="$query";
				//echo $_SESSION['query'];
				
					$rezultat = mysqli_query($handle, $query) or die("Error in query:" . $query . " <hr> error test:" . mysqli_error());
					$i++;
					WHILE ($recs = mysqli_fetch_array($rezultat))
					{
// 						if (is_null($recs['KORISNIK']) && is_null($recs['KONTO']) && is_null($recs['DB'])){  //dali e ok? Ako ja nema taa firma vo taa baza
// 							echo "nema danbr";
// 							return;
// 						}
						$KORISNIK=$recs['opis'];
						//$reskor = mysqli_query($handle0, "select opis from firmi where cod = '$KORISNIK'");
						$korrow = $KORISNIK;
						$KONTO = $recs['KONTO'];
						if(!is_null($recs['DB']) and is_null($recs['opis']))
						{
						$sumad = $sumad+$recs['DOLZI'];
						$sumap = $sumap+$recs['POBARUVA'];
						$sumasd = $sumasd+$recs['SD'];
						$sumasp = $sumasp+$recs['SP'];
						}
						$DOLZI=$recs['DOLZI'];
						$POBARUVA=$recs['POBARUVA'];
						$SD=$recs['SD'];
						$SP=$recs['SP'];
						$aa = $recs['DB'];
						$hand=connectweb();
						$rez=mysqli_query($hand, "SELECT dsc from cmp where id='$aa'");
						$bb=mysqli_fetch_row($rez);
						$DB=$bb[0];
						
						echo "<tr>";
						IF(!is_null($KORISNIK) and !is_null($DB))
						{
						if(isset($_POST['totali']))//so totali
						{
							
							$_SESSION['totfin']=1;
							
						IF(is_null($KONTO))
						{
						echo "<td style='color:red' align = 'center' width='22%'>" ."<big>". strtoupper($DB). "</td>";
						echo "<td align = 'center' width='5%'>" . "&nbsp;" ."Вкупно". "</td>" ;
						echo "<td style='color:red' width='11%' align = 'right' >" . number_format($DOLZI,0) ."</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>" . number_format($POBARUVA,0) ."</td>";
						echo "<td style='color:red' width='11%' align = 'right'>" .number_format($SD,0) . "</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>" .number_format($SP,0) . "</td>";
						}
						}
						else
						{
							$_SESSION['totfin']=0;
							
						IF(is_null($KONTO))
						{
						echo "<td style='color:red' align = 'center' width='22%'>" . "<big>".strtoupper($DB). "</td>";
						}
						ELSE
						{
						echo "<td align = 'center' width='22%'>" . strtoupper($DB)  . "</td>" ;
						}
						echo "<td align = 'center' width='5%'>" . $KONTO."</td>" ;
						IF(is_null($KONTO))
						{
								echo "<td style='color:red' width='11%' align = 'right' >" . "<b>".number_format($DOLZI,0) ."</td>";
								echo "<td style='color:blue' width='11%' align = 'right'>" ."<b>". number_format($POBARUVA,0) ."</td>";
								echo "<td style='color:red' width='11%' align = 'right'>" ."<b>".number_format($SD,0) . "</td>";
								echo "<td style='color:blue' width='11%' align = 'right'>" ."<b>".number_format($SP,0) . "</td>";
						}
						else
						{
						echo "<td style='color:red' width='11%' align = 'right' >" . number_format($DOLZI,0) ."</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>" . number_format($POBARUVA,0) ."</td>";
						echo "<td style='color:red' width='11%' align = 'right'>" .number_format($SD,0) . "</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>" .number_format($SP,0) . "</td>";
						}
						}
						}
						}
						}
						echo "</tr>";
						echo "<td width='22%'>" . "&nbsp;" . "</td>" ;
						echo "<td align = 'center' width='5%'>" . "&nbsp;" ."Вкупно". "</td>" ;
						echo "<td style='color:red' width='11%' align = 'right'>"."<big>" .number_format($sumad,0) . "</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>"."<big>" .number_format($sumap,0) . "</td>";
						echo "<td style='color:red' width='11%' align = 'right'>"."<big>" .number_format($sumasd,0) . "</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>"."<big>" .number_format($sumasp,0) . "</td>";
						echo "</table>";
				
			
			}
		
		}
		else    //izbrano e valuta
		{
			
			if(isset($_POST['korisnik']) && !EMPTY($_POST['korisnik']))
			{
				echo '<table border=1 cellspacing="2" cellpadding="0" width ="100%" style="font-size: 25px">';
				echo "<td align = 'center' width='22%'><b>" . "Фирма" . "</td>";
				echo "<td align = 'center' width='5%'><b>" . "Конто" . "</td>" ;
				echo "<td width='11%' align = 'center'><b>" . "Должи" . "</td>";
				echo "<td width='11%' align = 'center'><b>" . "Побарува" . "</td>";
				echo "<td width='11%' align = 'center'><b>" . "Салдо должи" . "</td>";
				echo "<td width='11%' align = 'center'><b>" . "Салдо побарува" . "</td>";
				
				$sumad = null;
				$sumap = null;
				$sumasd = null;
				$sumasp = null;
				$i=0;
				foreach($_SESSION['fin_baza'] as $bazi){	

					$handle=anyconnect($bazi);
					$godina=mysqli_real_escape_string($handle, $godina);
					//$oddat=mysqli_real_escape_string($handle, $oddat);
					//$dodat=mysqli_real_escape_string($handle, $dodat);
					
				$query = "	SELECT '".$bazi."' AS 'DB',KORISNIK, yana.KONTO ,
				SUM(SUMAD) AS DOLZI, SUM(SUMAP) AS POBARUVA,
				if(sum(SUMAD)>sum(SUMAP),sum(SUMAD)-sum(SUMAP),0) as SD,
				if(sum(SUMAP)>sum(SUMAD),sum(SUMAP)-sum(SUMAD),0) as SP
				FROM yana  JOIN konta ON konta.KONTO = yana.KONTO JOIN firmi ON yana.KORISNIK=firmi.COD
				WHERE yana.godina=$godina AND konta.godina=$godina AND firmi.DANBR = '$danbr'   AND konta.GRUPA_2 = '1' and konta.p_r='' AND DATUM BETWEEN $oddat AND $dodat $str_obvrski  $str_pobaruvanja $str_obvrski_pob
				GROUP BY KORISNIK, KONTO WITH ROLLUP";				
				
				$_SESSION['query']=" KORISNIK, yana.KONTO ,
				SUM(SUMAD) AS DOLZI, SUM(SUMAP) AS POBARUVA,
				if(sum(SUMAD)>sum(SUMAP),sum(SUMAD)-sum(SUMAP),0) as SD,
				if(sum(SUMAP)>sum(SUMAD),sum(SUMAP)-sum(SUMAD),0) as SP
				FROM yana  JOIN konta ON konta.KONTO = yana.KONTO JOIN firmi ON yana.KORISNIK=firmi.COD
				WHERE yana.godina=$godina AND konta.godina=$godina AND firmi.DANBR = '$danbr'   AND konta.GRUPA_2 = '1' and konta.p_r='' AND DATUM BETWEEN $oddat AND $dodat $str_obvrski  $str_pobaruvanja $str_obvrski_pob
				GROUP BY KORISNIK, KONTO WITH ROLLUP";
				

					$rezultat = mysqli_query($handle, $query) or die("Error in query:" . $query . " <hr> error test:" . mysqli_error());
					$i++;										
					WHILE ($recs = mysqli_fetch_array($rezultat))
					{
						$a=$recs;						
						$KORISNIK=$recs['opis'];
						//$reskor = mysqli_query($handle0, "select opis from firmi where cod = '$KORISNIK'");
						$korrow = $KORISNIK;
						$KONTO = $recs['KONTO'];
// 						if(is_null($KORISNIK))
// 						{
// 						$KORISNIK_IME = NULL;
// 					}
// 						ELSE
// 						{
// 						$Tabela = 'firmi';
// 						$KORISNIK_IME = reports_look($recs[KORISNIK],$Tabela);
// 						}
						if(!is_null($recs['DB']) and is_null($recs['opis']))
						{
						$sumad = $sumad+$recs['DOLZI'];
						$sumap = $sumap+$recs['POBARUVA'];
						$sumasd = $sumasd+$recs['SD'];
						$sumasp = $sumasp+$recs['SP'];
						}
						$DOLZI=$recs['DOLZI'];
						$POBARUVA=$recs['POBARUVA'];
						$SD=$recs['SD'];
						$SP=$recs['SP'];
						$aa = $recs['DB'];
						$hand=connectweb();
						$rez=mysqli_query($hand, "SELECT dsc from cmp where id='$aa'");
						$bb=mysqli_fetch_row($rez);
						$DB=$bb[0];
						
						echo "<tr>";
						IF(!is_null($KORISNIK) and !is_null($DB))
						{
						if(isset($_POST['totali']))//ako e stiklirano totali da ne gi prikazuva kontata
						{
							
							$_SESSION['totfin']=1;
							
						IF(is_null($KONTO))
						{
						echo "<td style='color:red' align = 'center' width='22%'>" ."<big>". strtoupper($DB). "</td>";
						echo "<td align = 'center' width='5%'>" . "&nbsp;" ."Вкупно". "</td>" ;
						echo "<td style='color:red' width='11%' align = 'right' >" . number_format($DOLZI,0) ."</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>" . number_format($POBARUVA,0) ."</td>";
						echo "<td style='color:red' width='11%' align = 'right'>" .number_format($SD,0) . "</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>" .number_format($SP,0) . "</td>";
						}
						}
						else
						{
							$_SESSION['totfin']=0;
							
						IF(is_null($KONTO))
						{
						echo "<td style='color:red' align = 'center' width='22%'>" . "<big>".strtoupper($DB). "</td>";
						}
						ELSE
						{
						echo "<td align = 'center' width='22%'>" . strtoupper($DB)  . "</td>" ;
						}
						echo "<td align = 'center' width='5%'>" . $KONTO."</td>" ;
						IF(is_null($KONTO))
						{
								echo "<td style='color:red' width='11%' align = 'right' >" . "<b>".number_format($DOLZI,0) ."</td>";
								echo "<td style='color:blue' width='11%' align = 'right'>" ."<b>". number_format($POBARUVA,0) ."</td>";
								echo "<td style='color:red' width='11%' align = 'right'>" ."<b>".number_format($SD,0) . "</td>";
								echo "<td style='color:blue' width='11%' align = 'right'>" ."<b>".number_format($SP,0) . "</td>";
						}
						else
						{
						echo "<td style='color:red' width='11%' align = 'right' >" . number_format($DOLZI,0) ."</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>" . number_format($POBARUVA,0) ."</td>";
						echo "<td style='color:red' width='11%' align = 'right'>" .number_format($SD,0) . "</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>" .number_format($SP,0) . "</td>";
						}
						}
						}
						}
						}
						echo "</tr>";
						echo "<td width='22%'>" . "&nbsp;" . "</td>" ;
						echo "<td align = 'center' width='5%'>" . "&nbsp;" ."Вкупно". "</td>" ;
						echo "<td style='color:red' width='11%' align = 'right'>"."<big>" .number_format($sumad,0) . "</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>"."<big>" .number_format($sumap,0) . "</td>";
						echo "<td style='color:red' width='11%' align = 'right'>"."<big>" .number_format($sumasd,0) . "</td>";
						echo "<td style='color:blue' width='11%' align = 'right'>"."<big>" .number_format($sumasp,0) . "</td>";
						echo "</table>";
			}
			
		}
		
		$_SESSION['sumad']=$sumad;
		$_SESSION['sumap']=$sumap;
		$_SESSION['sumasd']=$sumasd;
		$_SESSION['sumasp']=$sumasp;
	}
	$_SESSION['oddat']=$oddat;
	$_SESSION['dodat']=$dodat;
}
?>




