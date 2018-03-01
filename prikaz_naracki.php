<?php
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!logged()) redirect('index.php?rx=izbor_naracki');
if (nalog()==0){ // treba adminsko pravo da ima, ova da se smeni
	echo ("<SCRIPT LANGUAGE='JavaScript'>
			window.alert('Немате дозвола за пристап.')
			window.location.href='index.php'
			</SCRIPT>");
}
if (nalog()==1 && !isadmin_nalozi()){
	$god=date('Y');
	$komercijalist=true;
	$handle=connectwebnal();
	$cmd=mysqli_query($handle, "SELECT cod, opis from mesto_trosok where godina='$god' AND cod='".$_SESSION['lgn']."'");
	$m_t=mysqli_fetch_row($cmd);
}else {$komercijalist=false;}
genmenu();

fin_unset();

echo "<h4 align=center>Нарачки</h4>";

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

<script type="text/javascript">
$().ready(function() {
    $("#firma").autocomplete("popuni_firma.php", {
        width: 260,
        matchContains: true,
        selectFirst: false
    });
});
</script>

<script type="text/javascript">
$().ready(function() {
    $("#prod_mesto").autocomplete("popuni_prodm.php", {
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

<script type="text/javascript">
$().ready(function() {
    $("#mat").autocomplete("popuni_mat.php", {
        width: 260,
        matchContains: true,
        selectFirst: false
    });
});
</script>

<body bgcolor='<?php echo $_SESSION['boja']; ?>'>
<form method='POST'>
	<table>
	  <tr>
	  	<td align='left'><font size='5'>Фирма:</font></td>
	  	<td><input type='text' style="width:100%; height:40px; font-size:20px" onClick="this.select();" size='30' name='firma' id='firma' value='<?php if (isset($_REQUEST['firma'])){ echo $_REQUEST['firma'];}?>' ></td>
	  	<td align='left'><font size='5'>Комерцијалист:</font></td>
	  	<td><input type='text' style="width:100%; height:40px; font-size:20px" size='30' onClick="this.select();" name='komerc' id='komerc' <?php if ($komercijalist) echo 'readonly';?> value='<?php if ($komercijalist){ echo $m_t[0]." - ".$m_t[1];}
	  																																				else{
	  																																				 if (isset($_REQUEST['komerc'])){ echo $_REQUEST['komerc'];}}
													  																									 		
													  																								?>' ></td>
		<td>
			сите активни:<input type='radio' name='radio_real' id='site_aktivni' value='site_aktivni'  <?php if(isset($_REQUEST['radio_real']) && $_REQUEST['radio_real']=='site_aktivni') echo 'checked="checked"'; ?>/>
			реализирани:<input type='radio' name='radio_real' id='real' value='real'  <?php if(isset($_REQUEST['radio_real']) && $_REQUEST['radio_real']=='real') echo 'checked="checked"'; ?>/>
			нереализирани:<input type='radio' name='radio_real' id='nereal' value='nereal'  <?php if(isset($_REQUEST['radio_real']) && $_REQUEST['radio_real']=='nereal') echo 'checked="checked"'; ?>/>
		</td>													  
	  </tr>
	  <tr>
	  	<td align='left'><font size='5'>Материјал:</font></td>
	  	<td><input type='text' style="width:100%; height:40px; font-size:20px" size='30' onClick="this.select();" name='mat' id='mat' value='<?php if (isset($_REQUEST['mat'])){ echo $_REQUEST['mat'];}?>' ></td>
	 	<td align='left'><font size='5'>Прод.место:</font></td>
	  	<td><input type='text' style="width:100%; height:40px; font-size:20px" size='30' onClick="this.select();" name='prod_mesto' id='prod_mesto' value='<?php if (isset($_REQUEST['prod_mesto'])){ echo $_REQUEST['prod_mesto'];}?>' ></td>	 
	  	
		<td>
			неактивни<input type='radio' name='radio_real' id='neaktivni' value='neaktivni'  <?php if(isset($_REQUEST['radio_real']) && $_REQUEST['radio_real']=='neaktivni') echo 'checked="checked"'; ?>/>
			пропуштени:<input type='radio' name='radio_real' id='propusteni' value='propusteni'  <?php if(isset($_REQUEST['radio_real']) && $_REQUEST['radio_real']=='propusteni') echo 'checked="checked"'; ?>/>
		</td>
	  <tr>
	  	<td align='left'><font size='5'>Од Датум:</font></td>
		<td>
			<input type="text" style="width:15%; height:40px; font-size:20px" onClick="this.select();" required name="OdDen"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['OdDen'])){ echo $_REQUEST['OdDen'];} else {echo $den;}?>'>
			<input type="text" style="width:15%; height:40px; font-size:20px" onClick="this.select();" required name="OdMesec"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['OdMesec'])){ echo $_REQUEST['OdMesec'];} else {echo $mesec;}?>'> 
			<input type="text" style="width:30%; height:40px; font-size:20px" onClick="this.select();" required name="OdGodina"  maxlength = "4" size="4" value='<?php if (isset($_REQUEST['OdGodina'])){ echo $_REQUEST['OdGodina'];} else {echo $god;}?>'>
		</td>
		<td align='left'><font size='5'>До Датум:</font></td>
		<td>
			<input type="text" style="width:15%; height:40px; font-size:20px" onClick="this.select();" required name="DoDen"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['DoDen'])){ echo $_REQUEST['DoDen'];} else {echo $den;}?>'>
			<input type="text" style="width:15%; height:40px; font-size:20px" onClick="this.select();" required name="DoMesec"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['DoMesec'])){ echo $_REQUEST['DoMesec'];} else {echo $mesec;}?>'> 
			<input type="text" style="width:30%; height:40px; font-size:20px" onClick="this.select();" required name="DoGodina"  maxlength = "4" size="4" value='<?php if (isset($_REQUEST['DoGodina'])){ echo $_REQUEST['DoGodina'];} else {echo $god;}?>'>
		</td>
		<tr>
	  		<td><input style='height: 70px; width: 100%' type='submit' name='baraj' id='baraj' value='Барај'></td>
	    </tr>
	</table>
</form>

<a style='font-size: 250%;' href='naracki.php'>Нова нарачка</a><br/><br/>

<?php

$handle=connectwebnal();

if (isset($_POST['baraj'])){

	if (isset($_POST['firma']) && !empty($_POST['firma'])){
		$firma_arr=explode(' - ',$_POST['firma']);
		$firma=$firma_arr[0];
		$firma=mysqli_real_escape_string($handle, $firma);
		
		$firma2=" and firma='$firma'";
	}else{$firma2='';}
	
	if (isset($_POST['komerc']) && !EMPTY($_POST['komerc'])){
		$komerc_arr=explode(' - ',$_POST['komerc']);
		$komerc=$komerc_arr[0];
		$komerc=mysqli_real_escape_string($handle, $komerc);
		
		$komerc2=" and m_t='$komerc'";
	}else{$komerc2='';}
	
	if (isset($_POST['mat']) && !EMPTY($_POST['mat'])){
		$mat_arr=explode(' - ',$_POST['mat']);
		$mat=$mat_arr[0];
		$mat=mysqli_real_escape_string($handle, $mat);
		
		$query=mysqli_query($handle, "SELECT nar_glava_id from nar_stavki where mat='$mat'");
		$glava=mysqli_fetch_row($query);
		$id=$glava[0];
		while($glava=mysqli_fetch_row($query)){
			$id=$id.",".$glava[0];
		}
		
		$mat2=" and id in ($id)";
	}else{$mat2='';}
	
	if (isset($_POST['prod_mesto']) && !EMPTY($_POST['prod_mesto'])){
		$prod_mesto_arr=explode(' - ',$_POST['prod_mesto']);
		$prod_mesto=$prod_mesto_arr[0];
		$prod_mesto=mysqli_real_escape_string($handle, $prod_mesto);
		
		$prod_mesto2=" and prod_m='$prod_mesto'";
	}else{$prod_mesto2='';}
	
	if(isset($_POST['OdDen']) && isset($_POST['OdMesec']) && isset($_POST['OdGodina'])){
		if(!EMPTY($_POST['OdDen']) && !EMPTY($_POST['OdMesec']) && !EMPTY($_POST['OdGodina']))
		{
			$OdDen=$_POST['OdDen'];
			$OdDen=mysqli_real_escape_string($handle,$OdDen);
			
			$OdMesec=$_POST['OdMesec'];
			$OdMesec=mysqli_real_escape_string($handle,$OdMesec);
			
			$OdGodina=$_POST['OdGodina'];
			$OdGodina=mysqli_real_escape_string($handle,$OdGodina);
			
			$oddat="'".$OdGodina."-".$OdMesec."-".$OdDen."'";
		}
	}
	else{$oddat="'".$god."-".$mesec."-".$den."'";}
	
	if(isset($_POST['DoDen']) && isset($_POST['DoMesec']) && isset($_POST['DoGodina'])){	
		if(!EMPTY($_POST['DoDen']) && !EMPTY($_POST['DoMesec']) && !EMPTY($_POST['DoGodina']))
		{
			$DoDen=$_POST['DoDen'];
			$DoDen=mysqli_real_escape_string($handle,$DoDen);
		
			$DoMesec=$_POST['DoMesec'];
			$DoMesec=mysqli_real_escape_string($handle,$DoMesec);
		
			$DoGodina=$_POST['DoGodina'];
			$DoGodina=mysqli_real_escape_string($handle,$DoGodina);
		
			$dodat="'".$DoGodina."-".$DoMesec."-".$DoDen." 23:59:59'";
		}
	}
	else{$dodat="'".$god."-".$mesec."-".$den." 23:59:59'";}
	
	$date='datum';

	if (isset($_POST['firma']) && !EMPTY($_POST['firma'])){
		if (isset($_POST['komerc']) && !EMPTY($_POST['komerc'])){
			if (isset($_POST['mat']) && !EMPTY($_POST['mat'])){
				if (isset($_POST['prod_mesto']) && !EMPTY($_POST['prod_mesto'])){
					$res=mysqli_query($handle, "SELECT * from nar_glava where firma='$firma' and m_t='$komerc' and brisi=0 and id in ($id) and prod_m='$prod_mesto' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where firma='$firma' and m_t='$komerc' and brisi=0 and id in ($id) and prod_m='$prod_mesto' and $date between $oddat and $dodat";				
				}
				else
				{
					$res=mysqli_query($handle, "SELECT * from nar_glava where firma='$firma' and m_t='$komerc' and brisi=0 and id in ($id) and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where firma='$firma' and m_t='$komerc' and brisi=0 and id in ($id) and $date between $oddat and $dodat";
				}
			}
			else
			{
				if (isset($_POST['prod_mesto']) && !EMPTY($_POST['prod_mesto'])){
					$res=mysqli_query($handle, "SELECT * from nar_glava where firma='$firma' and m_t='$komerc' and brisi=0 and prod_m='$prod_mesto' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where firma='$firma' and m_t='$komerc' and brisi=0 and prod_m='$prod_mesto' and $date between $oddat and $dodat";
				}
				else 
				{
					$res=mysqli_query($handle, "SELECT * from nar_glava where firma='$firma' and m_t='$komerc' and brisi=0 and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where firma='$firma' and m_t='$komerc' and brisi=0 and $date between $oddat and $dodat";
				}
			}
		}
		else
		{
			if (isset($_POST['mat']) && !EMPTY($_POST['mat'])){
				if (isset($_POST['prod_mesto']) && !EMPTY($_POST['prod_mesto'])){
					$res=mysqli_query($handle, "SELECT * from nar_glava where firma='$firma' and brisi=0 and id in ($id) and prod_m='$prod_mesto' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where firma='$firma' and brisi=0 and id in ($id) and prod_m='$prod_mesto' and $date between $oddat and $dodat";				
				}
				else
				{
					$res=mysqli_query($handle, "SELECT * from nar_glava where firma='$firma' and brisi=0 and id in ($id) and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where firma='$firma' and brisi=0 and id in ($id) and $date between $oddat and $dodat";				
				}
			}
			else 
			{
				if (isset($_POST['prod_mesto']) && !EMPTY($_POST['prod_mesto'])){
					$res=mysqli_query($handle, "SELECT * from nar_glava where firma='$firma' and brisi=0 and prod_m='$prod_mesto' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where firma='$firma' and brisi=0 and prod_m='$prod_mesto' and $date between $oddat and $dodat";				
				}
				else
				{
					$res=mysqli_query($handle, "SELECT * from nar_glava where firma='$firma' and brisi=0 and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where firma='$firma' and brisi=0 and $date between $oddat and $dodat";				
				}
			}
		}
	}
	else
	{
		if (isset($_POST['komerc']) && !EMPTY($_POST['komerc'])){
			if (isset($_POST['mat']) && !EMPTY($_POST['mat'])){
				if (isset($_POST['prod_mesto']) && !EMPTY($_POST['prod_mesto'])){
					$res=mysqli_query($handle, "SELECT * from nar_glava where m_t='$komerc' and brisi=0 and id in ($id) and prod_m='$prod_mesto' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where m_t='$komerc' and brisi=0 and id in ($id) and prod_m='$prod_mesto' and $date between $oddat and $dodat";				
				}
				else
				{
					$res=mysqli_query($handle, "SELECT * from nar_glava where m_t='$komerc' and brisi=0 and id in ($id) and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where m_t='$komerc' and brisi=0 and id in ($id) and $date between $oddat and $dodat";
				}
			}
			else
			{
				if (isset($_POST['prod_mesto']) && !EMPTY($_POST['prod_mesto'])){
					$res=mysqli_query($handle, "SELECT * from nar_glava where m_t='$komerc' and brisi=0 and prod_m='$prod_mesto' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where m_t='$komerc' and brisi=0 and prod_m='$prod_mesto' and $date between $oddat and $dodat";				
				}
				else 
				{
					$res=mysqli_query($handle, "SELECT * from nar_glava where m_t='$komerc' and brisi=0 and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where m_t='$komerc' and brisi=0 and $date between $oddat and $dodat";
				}
			}
		}
		else 
		{
			if (isset($_POST['mat']) && !EMPTY($_POST['mat'])){
				if (isset($_POST['prod_mesto']) && !EMPTY($_POST['prod_mesto'])){
					$res=mysqli_query($handle, "SELECT * from nar_glava where brisi=0 and id in ($id) and prod_m='$prod_mesto' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where brisi=0 and id in ($id) and prod_m='$prod_mesto' and $date between $oddat and $dodat";
				}
				else 
				{
					$res=mysqli_query($handle, "SELECT * from nar_glava where brisi=0 and id in ($id) and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where brisi=0 and id in ($id) and $date between $oddat and $dodat";				
				}
			}
			else 
			{
				if (isset($_POST['prod_mesto']) && !EMPTY($_POST['prod_mesto'])){
					$res=mysqli_query($handle, "SELECT * from nar_glava where prod_m='$prod_mesto' brisi=0 and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where prod_m='$prod_mesto' brisi=0 and $date between $oddat and $dodat";
				}
				else 
				{
					$res=mysqli_query($handle, "SELECT * from nar_glava where brisi=0 and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where brisi=0 and $date between $oddat and $dodat";
				}
			}
		}
	}
	
	if (isset($_POST['radio_real']) && $_POST['radio_real']=='site_aktivni'){
		$res=mysqli_query($handle, "select * from nar_glava where brisi=0 and nar_glava.id in ( select distinct nar_stavki.nar_glava_id from nar_stavki where brisi=0)
				and datum between $oddat and $dodat $firma2 $komerc2 $mat2 $prod_mesto2 ");
		$_SESSION['select_nal']="select * from nar_glava where brisi=0 and nar_glava.id in ( select distinct nar_stavki.nar_glava_id from nar_stavki where brisi=0)
				and datum between $oddat and $dodat $firma2 $komerc2 $mat2 $prod_mesto2 ";
	}
	
	if (isset($_POST['radio_real']) &&  $_POST['radio_real']=='real'){
		$res=mysqli_query($handle, "select * from nar_glava where nalb<>'' and brisi=0 and nar_glava.id in ( select distinct nar_stavki.nar_glava_id from nar_stavki where brisi=0)
				and datum between $oddat and $dodat $firma2 $komerc2 $mat2 $prod_mesto2 ");
		$_SESSION['select_nal']="select * from nar_glava where nalb<>'' and brisi=0 and nar_glava.id in ( select distinct nar_stavki.nar_glava_id from nar_stavki where brisi=0)
								and datum between $oddat and $dodat $firma2 $komerc2 $mat2 $prod_mesto2 ";
	}
	
	if (isset($_POST['radio_real']) &&  $_POST['radio_real']=='nereal'){
		$res=mysqli_query($handle, "select * from nar_glava where nalb='' and brisi=0 and nar_glava.id in ( select distinct nar_stavki.nar_glava_id from nar_stavki where brisi=0)
				and datum between $oddat and $dodat $firma2 $komerc2 $mat2 $prod_mesto2 ");
		$_SESSION['select_nal']="select * from nar_glava where nalb='' and brisi=0 and nar_glava.id in ( select distinct nar_stavki.nar_glava_id from nar_stavki where brisi=0)
								and datum between $oddat and $dodat $firma2 $komerc2 $mat2 $prod_mesto2 ";
	}
	
	if (isset($_POST['radio_real']) &&  $_POST['radio_real']=='neaktivni'){
		$res=mysqli_query($handle, "select * from nar_glava where brisi=0 and not nar_glava.id in ( select distinct nar_stavki.nar_glava_id from nar_stavki where brisi=0)
				and datum between $oddat and $dodat $firma2 $komerc2 $mat2 $prod_mesto2 ");
		$_SESSION['select_nal']="select * from nar_glava where brisi=0 and not nar_glava.id in ( select distinct nar_stavki.nar_glava_id from nar_stavki where brisi=0)
		and datum between $oddat and $dodat $firma2 $komerc2 $mat2 $prod_mesto2 ";
	}
	
	if (isset($_POST['radio_real']) &&  $_POST['radio_real']=='propusteni'){
		$res=mysqli_query($handle, "SELECT nar_glava.* FROM nar_glava
										 JOIN nar_stavki ON nar_glava.id=nar_stavki.nar_glava_id  
										 WHERE godina='$god' and nar_stavki.propusteno<>0 and nar_glava.brisi=0 and nar_stavki.brisi=0 
										 and datum between $oddat and $dodat $firma2 $komerc2 $mat2 $prod_mesto2 ");
		$_SESSION['select_nal']="SELECT nar_glava.* FROM nar_glava
										 JOIN nar_stavki ON nar_glava.id=nar_stavki.nar_glava_id  
										 WHERE godina='$god' and nar_stavki.propusteno<>0 and nar_glava.brisi=0 and nar_stavki.brisi=0 
										 and datum between $oddat and $dodat $firma2 $komerc2 $mat2 $prod_mesto2";
	}

//echo $_SESSION['select_nal'];
//echo "<a href='fpdfnalozi_s.php' target='_blank'>Печати PDF<a><br/>";
echo "<table border='2' cellspacing='0' width='100%'>
<tr>
	<th>Фирма</th>
	<th>Прод.место</th>
	<th>Датум на нарачка</th>
	<th>Коментар</th>
	<th>Комерцијалист</th>
	<th>&nbsp</th>
	<th>&nbsp</th>
</tr>";

while($row = mysqli_fetch_array($res))
{
	$con=mysqli_query($handle, "SELECT opis from firmi where cod='".$row['firma']."'");
	$row_firma=mysqli_fetch_row($con);
	
	$con1=mysqli_query($handle, "SELECT opis from org_e where cod='".$row['prod_m']."'");
	$row_prodm=mysqli_fetch_row($con1);
	
	$con1=mysqli_query($handle, "SELECT opis from mesto_trosok where cod='".$row['m_t']."'");
	$row_komerc=mysqli_fetch_row($con1);
	
	echo "<tr>";
	echo "<td align='center'>" . $row['firma']." - ".$row_firma[0] . "</td>";
	echo "<td align='center'>" . $row['prod_m']." - ".$row_prodm[0] . "</td>";
	echo "<td align='center'>" . $row['datum'] . "</td>";
	echo "<td width='30%' align='center'>" . $row['komentar'] . "&nbsp</td>";
	echo "<td align='center'>" . $row['m_t']." - ".$row_komerc[0] . "</td>";
	echo "<td align='center'><a href=\"prikaz_stari_naracki.php?glava_id=".$row['id']."\">прикажи</a></td>";
	
	$nalb1 = preg_replace('/\s+/', '', $row['nalb']);
	$nalb2 = str_replace(' ', '', $row['nalb']);
	
	if (EMPTY($row['nalb']) || $nalb1=='' || $nalb2==''){
		echo "<td align='center'><a href=\"?otkazi=".$row['id']."\" onclick=\"return confirm('Дали ја бришете нарачката за $row_firma[0]?');\">бриши</a></td>";
	}else{
		echo "<td>&nbsp</td>";
	}
	
	echo "</tr>";
}
echo "</table>";


$cmd_br_naracki=mysqli_query($handle, "SELECT count(id) from nar_glava where brisi=0 and datum between $oddat and $dodat $firma2 $komerc2 $mat2 $prod_mesto2 ");
$br_naracki=mysqli_fetch_row($cmd_br_naracki);

$cmd1=mysqli_query($handle, "SELECT id from nar_glava where brisi=0 and datum between $oddat and $dodat $firma2 $komerc2 $mat2 $prod_mesto2");
$count_neak=0;
$count_akt=0;
while ($glavi=mysqli_fetch_row($cmd1)){
	$cmd_all=mysqli_query($handle, "SELECT * from nar_stavki where brisi=0 and nar_glava_id=$glavi[0]");
	$all=mysqli_fetch_row($cmd_all);
	if (EMPTY($all[0])){
		$count_neak=$count_neak + 1;
	}else{$count_akt=$count_akt + 1;}
}

$cmd2=mysqli_query($handle, "SELECT * from nar_stavki LEFT JOIN nar_glava ON nar_glava.id=nar_stavki.nar_glava_id 
								where nar_glava.brisi=0 and nar_stavki.brisi=0 and nar_glava.datum between $oddat and $dodat $firma2 $komerc2 $mat2 $prod_mesto2");
$total_kol=0;
$total_iznos=0;
while($totali=mysqli_fetch_array($cmd2)){
	$total_kol=$total_kol+$totali['kolicina'];
	$total_iznos=$total_iznos+$totali['vk_vr'];
}

echo "<table border='1' cellspacing='0' width='33%'>
<tr>
	<th>Вк.нарачки</th>
	<th>Активни</th>
	<th>Неактивни</th>
	<th>Количина</th>
	<th>Износ</th>
</tr>";
echo "<tr>";
echo "<td align='center'>" . $br_naracki[0] . "</td>";
echo "<td align='center'>" . $count_akt . "</td>";
echo "<td align='center'>" . $count_neak. "</td>";
echo "<td align='center'>" . $total_kol. "</td>";
echo "<td align='center'>" . $total_iznos. "</td>";
echo "</tr>";

}

if (isset($_GET['otkazi'])){     
	$handle = connectwebnal();
	$otkazi=$_GET['otkazi'];
	$otkazi=mysqli_real_escape_string($handle, $otkazi);
	mysqli_query($handle, "UPDATE nar_glava set brisi=1, sentd=0 where id=".$otkazi);
	mysqli_query($handle, "UPDATE nar_stavki set brisi=1, sentd=0 where nar_glava_id=".$otkazi);
				echo ("<SCRIPT LANGUAGE='JavaScript'>
				window.location.href='prikaz_naracki.php'
				</SCRIPT>");

}
?>
</body>
</html>
