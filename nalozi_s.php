<?php
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!logged()) redirect('index.php?rx=mngprikaz_nalozi');
if (nalog()==0){
	echo ("<SCRIPT LANGUAGE='JavaScript'>
			window.alert('Немате дозвола за пристап.')
			window.location.href='index.php'
			</SCRIPT>");
}
if (nalog()==1 && !isadmin_nalozi()){
	$komercijalist=true;
	$handle=connectwebnal();
	$cmd=mysqli_query($handle, "SELECT cod, opis from mesto_trosok where godina='2013' AND cod='".$_SESSION['lgn']."'");
	$m_t=mysqli_fetch_row($cmd);
}
genmenu();
echo "<h4 align=center>Испрати нарачка</h4>";

if (isset($_GET['promeni'])){ 
	$handle=connectwebnal();
	
	$promeni=$_GET['promeni'];
	$promeni=mysqli_real_escape_string($handle, $promeni);
	$rez1=mysqli_query($handle, "select * from nar_glava where id=".$promeni);
	$res1=mysqli_fetch_array($rez1);
	
	$rez2=mysqli_query($handle, "select opis from firmi where cod='".$res1['firma']."'");
	$firma_opis=mysqli_fetch_row($rez2);
	
	$rez3=mysqli_query($handle, "select opis from mesto_trosok where cod='".$res1['m_t']."' AND godina='2013'");
	$komerc_opis=mysqli_fetch_row($rez3);
	
	$rez4=mysqli_query($handle, "select opis from org_e where cod='".$res1['prod_m']."'");
	$prod_opis=mysqli_fetch_row($rez4);
}


$den=date('d');
$mesec=date('m');
$god=date('Y');

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

<!-- <script>
$(function(){
	$("#mat").blur(function(){
		  var val1 =  $("#mat").val(); 
		  var val2 =  $("#kol").val(); 
		  var datden =  $("#FakDen").val(); 
		  var datmesec =  $("#FakMesec").val(); 
		  var datgodina =  $("#FakGodina").val();
		  var fir =  $("#firma").val();
		  var rab = $("#rabat").val();
	      $.ajax({
	    	    type: 'POST',
	    	    url: 'calc_cena.php',
	    	    data: {materijal: val1, kolicina: val2, den: datden, mesec: datmesec, godina: datgodina, korisnik: fir, rabat: rab}
	    	});
	});
});
</script> -->

<script>
$(function(){
	$("#denovi").blur(function(){
		  var denovi = $("#denovi").val();
		  var fakden =  $("#FakDen").val(); 
		  var fakmesec =  $("#FakMesec").val(); 
		  var fakgodina =  $("#FakGodina").val();
		  var valden =  $("#ValDen").val(); 
		  var valmesec =  $("#ValMesec").val(); 
		  var valgodina =  $("#ValGodina").val();
	
		  var datefak="'"+fakgodina+"/"+fakmesec+"/"+fakden+"'";
		  var dateval="'"+valgodina+"/"+valmesec+"/"+valden+"'";
		  var fakdate = new DATE(dateval);
	
		  alert(datefak);
	
	});
});
</script>

<!-- <script>
$(function(){
	$("#kol").blur(function(){
		  var cena1 = $("#cena").val();
		  var kol =  $("#kol").val(); 
	      $.ajax({
	    	    type: 'POST',
	    	    url: 'calc_vrednost.php',
	    	    data: {cena: cena1, kolicina: kol}
	    	});
	});
});
</script> -->

<script type="text/javascript">
$().ready(function() {
    $("#prod_m").autocomplete("popuni_prodm.php", {
        width: 260,
        matchContains: true,
        selectFirst: false
    });
});
</script>

<script type="text/javascript">
$().ready(function() {
    $("#mat").autocomplete("popuni_mat.php", {
        width: 260,
        matchContains: true,
        selectFirst: false
    });
});
</script>

<script type="text/javascript">
$().ready(function() {
    $("#firma").autocomplete("popuni_firma.php", {
        width: 260,
        matchContains: true,
        selectFirst: false
    });
});
</script>

<?php
if (isadmin_nalozi()){
?>
	<script type="text/javascript">
	$().ready(function() {
	    $("#komerc").autocomplete("popuni_komerc.php", {
	        width: 260,
	        matchContains: true,
	        selectFirst: false
	    });
	});
	</script>
<?php
}
?>

<script>
$(function(){
	$('#prod_m').focus(function(){
		  var val1 =  $("#firma").val(); 
		  var val2 =  $("#komerc").val(); 
	      $.ajax({
	    	    type: 'POST',
	    	    url: 'setsesija.php',
	    	    data: {firma: val1 , komerc: val2}
	    	});
	});
});
</script>

<script>
$(function(){
	$('#firma').blur(function(){
		  var val1 =  $("#firma").val(); 
		  var val2 =  $("#komerc").val(); 
	      $.ajax({
	    	    type: 'POST',
	    	    url: 'setsesija.php',
	    	    data: {firma: val1 , komerc: val2}
	    	});
	});
});
</script>

<script>
$(function(){
	$('#komerc').blur(function(){
		  var val1 =  $("#firma").val(); 
		  var val2 =  $("#komerc").val(); 
	      $.ajax({
	    	    type: 'POST',
	    	    url: 'setsesija.php',
	    	    data: {firma: val1 , komerc: val2}
	    	});
	});
});
</script>

<a href='mngprikaz_nalozi_s.php'>Назад</a><br/>

<form method='POST'>
<table>
	<tr>
		<td><input type='hidden' name='kod' id='kod' value='<?php if (isset($_GET['promeni'])){ echo $res1['id'];}?>'/></td>
	</tr>
	<tr>
		<td align='left'><b>Од Магацин:</b></td>
		<td><input type='text' readonly onClick="this.select();" required name='magacin' id='magacin' value='02-1'/></td>
		<td align='left'><b>Прод.место:</b></td>
	  	<td><input type='text' onClick="this.select();" size='30' name='prod_m' id='prod_m' value='<?php if (isset($_GET['promeni'])){ echo $res1['prod_m']." - ".$prod_opis[0];}?>' ></td>
		<td bgcolor="DarkGray" align='left'>Материјал:</td></td>
		<td bgcolor="DarkGray"><input type='text' onClick="this.select();" value='<?php if (isset($_SESSION['mat'])){ echo $_SESSION['mat'];}?>' name='mat' id='mat'/></td>
		<td bgcolor="DarkGray" align='right'><input type='submit' value='Пресметај' name='presmetaj' id='presmetaj'/></td>
	</tr>
	<tr>	
		<td align='left'><b>Фирма:</b></td>
	  	<td><input type='text' onClick="this.select();" size='30' name='firma' id='firma' value='<?php if (isset($_GET['promeni'])){ echo $res1['firma']." - ".$firma_opis[0];}?>' ></td>
	  	<td align='left'><b>Комерц.:</b></td>
	  	<td><input type='text' onClick="this.select();" size='30' <?php if ($komercijalist) echo 'readonly';?> name='komerc' id='komerc' value='<?php if ($komercijalist){ echo $m_t[0]." - ".$m_t[1];}
	  																																				else{
	  																																				 if (isset($_GET['promeni'])){ echo $res1['m_t']." - ".$komerc_opis[0];}}
	  																																				 
	  																																			?>' ></td>
	  	<td bgcolor="DarkGray" align='left'>Количина:</td></td>
		<td bgcolor="DarkGray"><input type='text' onClick="this.select();" value='<?php if (isset($_SESSION['kol'])){ echo $_SESSION['kol'];}?>' name='kol' id='kol'/></td>
		<td bgcolor="DarkGray" align='left'>Цена со рабат:</td>
		<td bgcolor="DarkGray"><input type='text' readonly value='<?php if (isset($_SESSION['cena_r'])){ echo $_SESSION['cena_r'];}?>' name='cena_r' id='cena_r'/></td>
	</tr>
	 <tr>
	  	<td align='left'><b>Датум:</b></td>
		<td>
			<input type="text" onClick="this.select();" required name="DatDen"  maxlength = "2" size="2" value='<?php if (isset($_GET['promeni'])){ echo substr($res1['datum'],8,2);} else {echo $den;}?>'>
			<input type="text" onClick="this.select();" required name="DatMesec"  maxlength = "2" size="2" value='<?php if (isset($_GET['promeni'])){ echo substr($res1['datum'],5,2);} else {echo $mesec;}?>'> 
			<input type="text" onClick="this.select();" required name="DatGodina"  maxlength = "4" size="4" value='<?php if (isset($_GET['promeni'])){ echo substr($res1['datum'],0,4);} else {echo $god;}?>'>
		</td>
		<td align='right'><b>Датум на фактура:</b></td>
		<td>
			<input type="text" onClick="this.select();" required name="FakDen"  id="FakDen" maxlength = "2" size="2" value='<?php if (isset($_GET['promeni'])){ echo substr($res1['datfak'],8,2);} else {echo $den;}?>'>
			<input type="text" onClick="this.select();" required name="FakMesec"  id="FakMesec" maxlength = "2" size="2" value='<?php if (isset($_GET['promeni'])){ echo substr($res1['datfak'],5,2);} else {echo $mesec;}?>'> 
			<input type="text" onClick="this.select();" required name="FakGodina"  id="FakGodina" maxlength = "4" size="4" value='<?php if (isset($_GET['promeni'])){ echo substr($res1['datfak'],0,4);} else {echo $god;}?>'>
		</td>
		<td bgcolor="DarkGray" align='left'>Цена со ддв:</td>
		<td bgcolor="DarkGray"><input type='text' readonly name='cena' id='cena' value='<?php if (isset($_SESSION['cena'])){echo $_SESSION['cena'];} ?>'/></td>
		<td bgcolor="DarkGray" align='left'>Вредност:</td></td></td>
		<td bgcolor="DarkGray"><input type='text' readonly name='vk_vr' id='vk_vr' value='<?php if (isset($_SESSION['vk_vr'])){echo $_SESSION['vk_vr'];} ?>'/></td>
	  </tr>
	  <tr>
	  	<td align='left'><b>Денови:</b></td>
		<td><input type='text' size='5' onClick="this.select();" name='denovi' id='denovi' value='<?php if (isset($_SESSION['denovi'])){echo $_SESSION['denovi'];} ?>'></td>
		<td align='right'><b>Валута:</b></td>
		<td>
			<input type="text" onClick="this.select();" required name="ValDen"  maxlength = "2" size="2" value='<?php if (isset($_GET['promeni'])){ echo substr($res1['datval'],8,2);} else {echo $den;}?>'>
			<input type="text" onClick="this.select();" required name="ValMesec"  maxlength = "2" size="2" value='<?php if (isset($_GET['promeni'])){ echo substr($res1['datval'],5,2);} else {echo $mesec;}?>'> 
			<input type="text" onClick="this.select();" required name="ValGodina"  maxlength = "4" size="4" value='<?php if (isset($_GET['promeni'])){ echo substr($res1['datval'],0,4);} else {echo $god;}?>'>
		</td>
		<td bgcolor="DarkGray" align='left'>Рабат:</td>
		<td bgcolor="DarkGray"><input type='text' size='5' onClick="this.select();" value='<?php if (isset($_SESSION['rabat'])){ echo $_SESSION['rabat'];}else{echo "0";}?>' name='rabat' id='rabat'/> %</td>
		<td><td align='right'><input type='submit' value='Додај' name='dodaj' id='dodaj'/></td></td>
	  </tr>
	  <tr>
		<td align='left'><b>Коментар:</b></td>
		<td><textarea type='text' rows="1" cols="22" name='komentar' id='komentar'><?php if(isset($_GET['promeni'])){ echo $res1['komentar']; } ?></textarea></td>
		<td align='right'><input type='submit' value='Зачувај' name='zacuvaj' id='zacuvaj'/></td>
	  </tr>

</table>
</form> 
</html>

<?php 
$handle=connectwebnal();
echo "<table border='2' cellspacing='0' width='100%'>
<tr>
<th>Мат</th>
<th>Опис</th>
<th>Количина</th>
<th>Цена</th>
<th>Рабат</th>
<th>Цена со рабат</th>
<th>Износ со рабат</th>
<th>&nbsp</th>
</tr>";

if (isset($_GET['promeni'])){
	$promeni=$_GET['promeni'];
	$promeni=mysqli_real_escape_string($handle, $promeni);
	$res=mysqli_query($handle, "Select * from nar_stavki where nar_glava_id=".$promeni);
	while($row = mysqli_fetch_array($res))
	{	
		
		$con=mysqli_query($handle, "SELECT opis from materijali where cod='".$row['mat']."'");
		$opis_mat=mysqli_fetch_row($con);
		
		echo "<tr>";
		echo "<td align='center'>" . $row['mat']."</td>";
		echo "<td align='center'>". $opis_mat[0] . "</td>";
		echo "<td align='center'>" . $row['kolicina'] . "</td>";
		echo "<td align='center'>" . $row['cena'] . "</td>";
		echo "<td align='center'>" . $row['rabat'] . "</td>";
		echo "<td align='center'>" . $row['cena_r'] . "</td>";
		echo "<td align='center'>" . $row['vk_vr'] . "</td>";
		echo "<td align='center'><a href=\"?promeni=".$row['nar_glava_id']."&brisi_s=".$row['id']."\" onclick=\"return confirm('Дали ја бришете нарачката?');\">бриши</a></td>";
	
		echo "</tr>";
	}
echo "</table>";
}

if (isset($_GET['promeni']) && isset($_GET['brisi_s'])){
	$handle=connectwebnal();
	$brisi_s=$_GET['brisi_s'];
	$brisi_s=mysqli_real_escape_string($handle, $brisi_s);
	$promeni=$_GET['promeni'];
	$promeni=mysqli_real_escape_string($handle, $promeni);
	mysqli_query($handle,"DELETE FROM nar_stavki where nar_glava_id=$promeni AND id=$brisi_s" );
			echo ("<SCRIPT LANGUAGE='JavaScript'>
				window.location.href='nalozi_s.php?promeni=$promeni'
				</SCRIPT>");
}

if(isset($_POST['dodaj'])){
	if (isset($_POST['mat']) && !EMPTY($_POST['mat']) && isset($_POST['kol']) && !EMPTY($_POST['kol']) && isset($_POST['kod']) && !EMPTY($_POST['kod'])){
		
		$handle=connectwebnal();
		$nar_glava_id=$_POST['kod']; //ovoj e kod e hidden od formata
		
		$mat_arr=explode(' - ',$_POST['mat']);
		$mat=$mat_arr[0];
		$mat=mysqli_real_escape_string($handle, $mat);
		
		$kolicina=$_POST['kol'];
		$kolicina=mysqli_real_escape_string($handle, $kolicina);
		
		$cena=$_POST['cena'];
		$cena=mysqli_real_escape_string($handle, $cena);
		
		$vk_vr=$_POST['vk_vr'];
		$vk_vr=mysqli_real_escape_string($handle, $vk_vr);
		
		if (isset($_POST['cena_r']) && !EMPTY($_POST['cena_r'])){
			$cena_r=$_POST['cena_r'];
			$cena_r=mysqli_real_escape_string($handle, $cena_r);
		}else {$cena_r=0;}
		
		if (isset($_POST['rabat']) && !EMPTY($_POST['rabat'])){
			$rabat=$_POST['rabat'];
			$rabat=mysqli_real_escape_string($handle, $rabat);
		}else {$rabat=0;}
		
		$query=mysqli_query($handle, 'SELECT max(id) from nar_stavki where nar_glava_id='.$nar_glava_id);
		$row=mysqli_fetch_row($query);
		$id_stavka=$row[0]+1;
		mysqli_query($handle, "INSERT INTO nar_stavki (id,nar_glava_id,mat,kolicina,cena,rabat,cena_r,vk_vr) VALUES($id_stavka,$nar_glava_id,'$mat',$kolicina,$cena,$rabat,$cena_r,$vk_vr)");
		echo ("<SCRIPT LANGUAGE='JavaScript'>
				window.location.href='nalozi_s.php?promeni=$promeni'
				</SCRIPT>");
	}
}

if (isset($_POST['zacuvaj'])){
	
	if (isset($_GET['promeni'])){ 
		
		if (isset($_POST['firma']) && !EMPTY($_POST['firma']) && isset($_POST['komerc']) && !EMPTY($_POST['komerc']) && isset($_POST['prod_m']) && !EMPTY($_POST['prod_m']) && isset($_POST['kod']) && !EMPTY($_POST['kod'])){
		
			$handle=connectwebnal();
			
			$glava_id=$_GET['promeni'];
			$glava_id=mysqli_real_escape_string($handle, $glava_id);
				
			$firma_arr=explode(' - ',$_POST['firma']);
			$firma=$firma_arr[0];
			$firma=mysqli_real_escape_string($handle, $firma);
				
			$prodm_arr=explode(' - ',$_POST['prod_m']);
			$prod_m=$prodm_arr[0];
			$prod_m=mysqli_real_escape_string($handle, $prod_m);
				
			$komerc_arr=explode(' - ',$_POST['komerc']);
			$komerc=$komerc_arr[0];
			$komerc=mysqli_real_escape_string($handle, $komerc);
				
			$magacin=$_POST['magacin'];
			$magacin=mysqli_real_escape_string($handle, $magacin);
				
			$komentar=$_POST['komentar'];
			$komentar=mysqli_real_escape_string($handle, $komentar);
				
			if(!EMPTY($_POST['DatDen']) && !EMPTY($_POST['DatMesec']) && !EMPTY($_POST['DatGodina']))
			{
				$DatDen=$_POST['DatDen'];
				$DatDen=mysqli_real_escape_string($handle,$DatDen);
					
				$DatMesec=$_POST['DatMesec'];
				$DatMesec=mysqli_real_escape_string($handle,$DatMesec);
					
				$DatGodina=$_POST['DatGodina'];
				$DatGodina=mysqli_real_escape_string($handle,$DatGodina);
					
				$datum="'".$DatGodina."-".$DatMesec."-".$DatDen."'";
			}
				
			if(!EMPTY($_POST['FakDen']) && !EMPTY($_POST['FakMesec']) && !EMPTY($_POST['FakGodina']))
			{
				$FakDen=$_POST['FakDen'];
				$FakDen=mysqli_real_escape_string($handle,$FakDen);
					
				$FakMesec=$_POST['FakMesec'];
				$FakMesec=mysqli_real_escape_string($handle,$FakMesec);
					
				$FakGodina=$_POST['FakGodina'];
				$FakGodina=mysqli_real_escape_string($handle,$FakGodina);
					
				$datfak="'".$FakGodina."-".$FakMesec."-".$FakDen."'";
			}
				
			if(!EMPTY($_POST['ValDen']) && !EMPTY($_POST['ValMesec']) && !EMPTY($_POST['ValGodina']))
			{
				$ValDen=$_POST['ValDen'];
				$ValDen=mysqli_real_escape_string($handle,$ValDen);
					
				$ValMesec=$_POST['ValMesec'];
				$ValMesec=mysqli_real_escape_string($handle,$ValMesec);
					
				$ValGodina=$_POST['ValGodina'];
				$ValGodina=mysqli_real_escape_string($handle,$ValGodina);
					
				$datval="'".$ValGodina."-".$ValMesec."-".$ValDen."'";
			}
				
			mysqli_query($handle, "UPDATE nar_glava SET cod_i='$magacin', firma='$firma', datum=$datum, datfak=$datfak, datval=$datval, prod_m='$prod_m', m_t='$komerc', komentar='$komentar' where id=".$glava_id);
			echo ("<SCRIPT LANGUAGE='JavaScript'>
					window.location.href='nalozi_s.php?promeni=$glava_id'
					</SCRIPT>");
		}
		
	}
	else 
	{
		if (isset($_POST['firma']) && !EMPTY($_POST['firma']) && isset($_POST['komerc']) && !EMPTY($_POST['komerc']) && isset($_POST['prod_m']) && !EMPTY($_POST['prod_m'])){
		
			$handle=connectwebnal();
			
			$firma_arr=explode(' - ',$_POST['firma']);
			$firma=$firma_arr[0];
			$firma=mysqli_real_escape_string($handle, $firma);
			
			$prodm_arr=explode(' - ',$_POST['prod_m']);
			$prod_m=$prodm_arr[0];
			$prod_m=mysqli_real_escape_string($handle, $prod_m);
			
			$komerc_arr=explode(' - ',$_POST['komerc']);
			$komerc=$komerc_arr[0];
			$komerc=mysqli_real_escape_string($handle, $komerc);
			
			$magacin=$_POST['magacin'];
			$magacin=mysqli_real_escape_string($handle, $magacin);
			
			$komentar=$_POST['komentar'];
			$komentar=mysqli_real_escape_string($handle, $komentar);
			
			if(!EMPTY($_POST['DatDen']) && !EMPTY($_POST['DatMesec']) && !EMPTY($_POST['DatGodina']))
			{
				$DatDen=$_POST['DatDen'];
				$DatDen=mysqli_real_escape_string($handle,$DatDen);
			
				$DatMesec=$_POST['DatMesec'];
				$DatMesec=mysqli_real_escape_string($handle,$DatMesec);
			
				$DatGodina=$_POST['DatGodina'];
				$DatGodina=mysqli_real_escape_string($handle,$DatGodina);
			
				$datum="'".$DatGodina."-".$DatMesec."-".$DatDen."'";
			}
			
			if(!EMPTY($_POST['FakDen']) && !EMPTY($_POST['FakMesec']) && !EMPTY($_POST['FakGodina']))
			{
				$FakDen=$_POST['FakDen'];
				$FakDen=mysqli_real_escape_string($handle,$FakDen);
			
				$FakMesec=$_POST['FakMesec'];
				$FakMesec=mysqli_real_escape_string($handle,$FakMesec);
			
				$FakGodina=$_POST['FakGodina'];
				$FakGodina=mysqli_real_escape_string($handle,$FakGodina);
			
				$datfak="'".$FakGodina."-".$FakMesec."-".$FakDen."'";
			}
			
			if(!EMPTY($_POST['ValDen']) && !EMPTY($_POST['ValMesec']) && !EMPTY($_POST['ValGodina']))
			{
				$ValDen=$_POST['ValDen'];
				$ValDen=mysqli_real_escape_string($handle,$ValDen);
			
				$ValMesec=$_POST['ValMesec'];
				$ValMesec=mysqli_real_escape_string($handle,$ValMesec);
			
				$ValGodina=$_POST['ValGodina'];
				$ValGodina=mysqli_real_escape_string($handle,$ValGodina);
			
				$datval="'".$ValGodina."-".$ValMesec."-".$ValDen."'";
			}
			
			$query=mysqli_query($handle, 'SELECT max(id) from nar_glava');
			$row=mysqli_fetch_row($query);
			$id=$row[0]+1;
			mysqli_query($handle, "INSERT INTO nar_glava (id,cod_i,firma,datum,datfak,datval,prod_m,m_t,komentar) VALUES($id,'$magacin','$firma',$datum,$datfak,$datval,'$prod_m','$komerc','$komentar')");
			echo ("<SCRIPT LANGUAGE='JavaScript'>
					window.location.href='nalozi_s.php?promeni=$id'
					</SCRIPT>");
		}
	}
	
	
}

if (isset($_POST['presmetaj'])){
	
	if (!empty($_POST['mat']) && !empty($_POST['FakDen']) && !empty($_POST['FakMesec']) && !empty($_POST['FakGodina']) && !empty($_POST['firma']) && !empty($_POST['kol']) && isset($_POST['kod']) && !EMPTY($_POST['kod'])){
	
		$handle=connectwebnal();
		$kod_glava=$_POST['kod'];
	
		//$_SESSION['mat']=$_POST['materijal'];
		$mat_arr=explode(' - ',$_POST['mat']);
		$mat=$mat_arr[0];
		$mat=mysqli_real_escape_string($handle, $mat);
		$_SESSION['mat']=$mat;
	
	
		$kol=$_POST['kol'];
		$kol=mysqli_real_escape_string($handle, $kol);
		$_SESSION['kol']=$kol;
	
		$datum="'".$_POST['FakGodina']."-".$_POST['FakMesec']."-".$_POST['FakDen']."'";
	
		$firma_arr=explode(' - ',$_POST['firma']);
		$firma=$firma_arr[0];
		$firma=mysqli_real_escape_string($handle, $firma);
	
		$cmd1=mysqli_query($handle, "Select tip_cena from firmi where cod='".$firma."'");
		$tip_arr=mysqli_fetch_row($cmd1);
		if (EMPTY($tip_arr[0])){
			$tip=1;
		}else{$tip=$tip_arr[0];}
	
		$cmd="	select cena from cenovnik  where mat = '$mat' and cenovnik.tip ='$tip'
				and concat(cast(oddat as char),odcas)<= concat(CAST(CAST($datum as date) as char),'00:00')
				and if(cenovnik.dodat<>'0000-00-00',concat(cast(dodat as char),docas)>=CONCAT(CAST(CAST($datum as date) as char),'00:00') ,cenovnik.dodat='0000-00-00') ";
		$rez=mysqli_query($handle,$cmd);
		$cena=mysqli_fetch_row($rez);
		$cena_ddv=$cena[0]*18/100 + $cena[0];
		$_SESSION['cena']=$cena_ddv;
		
		if (!isset($_POST['rabat']) || EMPTY($_POST['rabat'])){
			$rabat=0;		
			$_SESSION['rabat']=$rabat;
			$cena_r=$cena_ddv - $cena_ddv*$rabat/100;
			$_SESSION['cena_r']=$cena_r;
		}else{
			$rabat=$_POST['rabat'];
			$rabat=mysqli_real_escape_string($handle, $rabat);
			$_SESSION['rabat']=$rabat;
				
			$cena_r=$cena_ddv - $cena_ddv*$rabat/100;
			$_SESSION['cena_r']=$cena_r;
		}
		
		$vk_vr=($kol * $cena_r);
		$_SESSION['vk_vr']=$vk_vr;
						echo ("<SCRIPT LANGUAGE='JavaScript'>
				window.location.href='nalozi_s.php?promeni=$kod_glava'
				</SCRIPT>");
	}
	
}

//skriptite za presmetka na denovi

?>




