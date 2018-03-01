<?php
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!logged()) redirect('index.php?rx=mngprikaz_nalozi_s');
if (nalog()==0){ // treba adminsko pravo da ima, ova da se smeni
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

<form method='POST'>
	<table>
	  <tr>
	  	<td align='left'>Од Магацин:</td>
	  	<td><input type='text' readonly size='30' name='magacin' id='magacin' value='02-1' ></td>
	  </tr>
	  <tr>
	  	<td align='left'>Фирма:</td>
	  	<td><input type='text' onClick="this.select();" size='30' name='firma' id='firma' value='<?php if (isset($_REQUEST['firma'])){ echo $_REQUEST['firma'];}?>' ></td>
	  	<td align='left'>Комерцијалист:</td>
	  	<td><input type='text' size='30' onClick="this.select();" name='komerc' id='komerc' <?php if ($komercijalist) echo 'readonly';?> value='<?php if ($komercijalist){ echo $m_t[0]." - ".$m_t[1];}
	  																																				else{
	  																																				 if (isset($_REQUEST['komerc'])){ echo $_REQUEST['komerc'];}}
													  																									 		
													  																								?>' ></td>
	  </tr>
	  <tr>
	  	<td align='left'>Материјал:</td>
	  	<td><input type='text' size='30' onClick="this.select();" name='mat' id='mat' value='<?php if (isset($_REQUEST['mat'])){ echo $_REQUEST['mat'];}?>' ></td>
	 	<td align='left'>Прод.место:</td>
	  	<td><input type='text' size='30' onClick="this.select();" name='prod_mesto' id='prod_mesto' value='<?php if (isset($_REQUEST['prod_mesto'])){ echo $_REQUEST['prod_mesto'];}?>' ></td>	 
	  </tr>
	  <tr>
	  	<td align='left'>Од Датум:</td>
		<td>
			<input type="text" onClick="this.select();" required name="OdDen"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['OdDen'])){ echo $_REQUEST['OdDen'];} else {echo $den;}?>'>
			<input type="text" onClick="this.select();" required name="OdMesec"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['OdMesec'])){ echo $_REQUEST['OdMesec'];} else {echo $mesec;}?>'> 
			<input type="text" onClick="this.select();" required name="OdGodina"  maxlength = "4" size="4" value='<?php if (isset($_REQUEST['OdGodina'])){ echo $_REQUEST['OdGodina'];} else {echo $god;}?>'>
		</td>
		<td align='left'>До Датум:</td>
		<td>
			<input type="text" onClick="this.select();" required name="DoDen"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['DoDen'])){ echo $_REQUEST['DoDen'];} else {echo $den;}?>'>
			<input type="text" onClick="this.select();" required name="DoMesec"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['DoMesec'])){ echo $_REQUEST['DoMesec'];} else {echo $mesec;}?>'> 
			<input type="text" onClick="this.select();" required name="DoGodina"  maxlength = "4" size="4" value='<?php if (isset($_REQUEST['DoGodina'])){ echo $_REQUEST['DoGodina'];} else {echo $god;}?>'>
		</td>
		<td>Датум на нарачка:<input type='radio'  checked='checked' name='radiokopce' value='dat_nar' <?php if(isset($_REQUEST['radiokopce']) && $_REQUEST['radiokopce']=='dat_nar') echo 'checked="checked"'; ?>/></td>
		<td>Датум на фактура:<input type='radio' 					name='radiokopce' value='dat_fak' <?php if(isset($_REQUEST['radiokopce']) && $_REQUEST['radiokopce']=='dat_fak') echo 'checked="checked"'; ?>/></td>		
	  	<tr>
	  		<td><input type='submit' name='baraj' id='baraj' value='Барај'></td>
	    </tr>
	</table>
</form>

<?php

$handle=connectwebnal();

if (isset($_POST['baraj'])){

	if (isset($_POST['firma']) && !empty($_POST['firma'])){
		$firma_arr=explode(' - ',$_POST['firma']);
		$firma=$firma_arr[0];
		$firma=mysqli_real_escape_string($handle, $firma);
	}
	
	if (isset($_POST['komerc']) && !EMPTY($_POST['komerc'])){
		$komerc_arr=explode(' - ',$_POST['komerc']);
		$komerc=$komerc_arr[0];
		$komerc=mysqli_real_escape_string($handle, $komerc);
	}
	
	if (isset($_POST['mat']) && !EMPTY($_POST['mat'])){
		$mat_arr=explode(' - ',$_POST['mat']);
		$mat=$mat_arr[0];
		$mat=mysqli_real_escape_string($handle, $mat);
	}
	
	if (isset($_POST['prod_mesto']) && !EMPTY($_POST['prod_mesto'])){
		$prod_mesto_arr=explode(' - ',$_POST['prod_mesto']);
		$prod_mesto=$prod_mesto_arr[0];
		$prod_mesto=mysqli_real_escape_string($handle, $prod_mesto);
	}
	
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
		
			$dodat="'".$DoGodina."-".$DoMesec."-".$DoDen."'";
		}
	}
	else{$dodat="'".$god."-".$mesec."-".$den."'";}
	
	$date='datum';
	if ($_POST['radiokopce'] == 'dat_nar'){
		$date='datum';
	}elseif ($_POST['radiokopce'] == 'dat_fak') {
		$date='datfak';
	}
	

	if (isset($_POST['firma']) && !EMPTY($_POST['firma'])){
		if (isset($_POST['komerc']) && !EMPTY($_POST['komerc'])){
			if (isset($_POST['mat']) && !EMPTY($_POST['mat'])){
				if (isset($_POST['prod_mesto']) && !EMPTY($_POST['prod_mesto'])){
					$res=mysqli_query($handle, "SELECT * from nar_glava where firma='$firma' and m_t='$komerc' and mat='$mat' and prod_m='$prod_mesto' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where firma='$firma' and m_t='$komerc' and mat='$mat' and prod_m='$prod_mesto' and $date between $oddat and $dodat";				
				}
				else
				{
					$res=mysqli_query($handle, "SELECT * from nar_glava where firma='$firma' and m_t='$komerc' and mat='$mat' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where firma='$firma' and m_t='$komerc' and mat='$mat' and $date between $oddat and $dodat";
				}
			}
			else
			{
				if (isset($_POST['prod_mesto']) && !EMPTY($_POST['prod_mesto'])){
					$res=mysqli_query($handle, "SELECT * from nar_glava where firma='$firma' and m_t='$komerc' and prod_m='$prod_mesto' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where firma='$firma' and m_t='$komerc' and prod_m='$prod_mesto' and $date between $oddat and $dodat";
				}
				else 
				{
					$res=mysqli_query($handle, "SELECT * from nar_glava where firma='$firma' and m_t='$komerc' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where firma='$firma' and m_t='$komerc' and $date between $oddat and $dodat";
				}
			}
		}
		else
		{
			if (isset($_POST['mat']) && !EMPTY($_POST['mat'])){
				if (isset($_POST['prod_mesto']) && !EMPTY($_POST['prod_mesto'])){
					$res=mysqli_query($handle, "SELECT * from nar_glava where firma='$firma' and mat='$mat' and prod_m='$prod_mesto' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where firma='$firma' and mat='$mat' and prod_m='$prod_mesto' and $date between $oddat and $dodat";				
				}
				else
				{
					$res=mysqli_query($handle, "SELECT * from nar_glava where firma='$firma' and mat='$mat' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where firma='$firma' and mat='$mat' and $date between $oddat and $dodat";				
				}
			}
			else 
			{
				if (isset($_POST['prod_mesto']) && !EMPTY($_POST['prod_mesto'])){
					$res=mysqli_query($handle, "SELECT * from nar_glava where firma='$firma' and prod_m='$prod_mesto' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where firma='$firma' and prod_m='$prod_mesto' and $date between $oddat and $dodat";				
				}
				else
				{
					$res=mysqli_query($handle, "SELECT * from nar_glava where firma='$firma' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where firma='$firma' and $date between $oddat and $dodat";				
				}
			}
		}
	}
	else
	{
		if (isset($_POST['komerc']) && !EMPTY($_POST['komerc'])){
			if (isset($_POST['mat']) && !EMPTY($_POST['mat'])){
				if (isset($_POST['prod_mesto']) && !EMPTY($_POST['prod_mesto'])){
					$res=mysqli_query($handle, "SELECT * from nar_glava where m_t='$komerc' and mat='$mat' and prod_m='$prod_mesto' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where m_t='$komerc' and mat='$mat' and prod_m='$prod_mesto' and $date between $oddat and $dodat";				
				}
				else
				{
					$res=mysqli_query($handle, "SELECT * from nar_glava where m_t='$komerc' and mat='$mat' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where m_t='$komerc' and mat='$mat' and $date between $oddat and $dodat";
				}
			}
			else
			{
				if (isset($_POST['prod_mesto']) && !EMPTY($_POST['prod_mesto'])){
					$res=mysqli_query($handle, "SELECT * from nar_glava where m_t='$komerc' and prod_m='$prod_mesto' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where m_t='$komerc' and prod_m='$prod_mesto' and $date between $oddat and $dodat";				
				}
				else 
				{
					$res=mysqli_query($handle, "SELECT * from nar_glava where m_t='$komerc' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where m_t='$komerc' and $date between $oddat and $dodat";
				}
			}
		}
		else 
		{
			if (isset($_POST['mat']) && !EMPTY($_POST['mat'])){
				if (isset($_POST['prod_mesto']) && !EMPTY($_POST['prod_mesto'])){
					$res=mysqli_query($handle, "SELECT * from nar_glava where mat='$mat' and prod_m='$prod_mesto' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where mat='$mat' and prod_m='$prod_mesto' and $date between $oddat and $dodat";
				}
				else 
				{
					$res=mysqli_query($handle, "SELECT * from nar_glava where mat='$mat' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where mat='$mat' and $date between $oddat and $dodat";				
				}
			}
			else 
			{
				if (isset($_POST['prod_mesto']) && !EMPTY($_POST['prod_mesto'])){
					$res=mysqli_query($handle, "SELECT * from nar_glava where prod_m='$prod_mesto' and $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where prod_m='$prod_mesto' and $date between $oddat and $dodat";
				}
				else 
				{
					$res=mysqli_query($handle, "SELECT * from nar_glava where $date between $oddat and $dodat");
					$_SESSION['select_nal']="SELECT * from nar_glava where $date between $oddat and $dodat";
				}
			}
		}
	}

//echo $_SESSION['select_nal'];
//echo "<a href='fpdfnalozi_s.php' target='_blank'>Печати PDF<a><br/>";
echo "<table border='2' cellspacing='0' width='100%'>
<tr>
	<th>Фирма</th>
	<th>Прод.место</th>
	<th>Датум на нарачка</th>
	<th>Датум на фактура</th>
	<th>Денови</th>
	<th>Валута на доспевање</th>	
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
	
	$datediff=strtotime($row['datval']) - strtotime($row['datfak']);
	$sekundi=abs($datediff);
	$denovi=floor($sekundi/(60*60*24));
	
	echo "<tr>";
	echo "<td align='center'>" . $row['firma']." - ".$row_firma[0] . "</td>";
	echo "<td align='center'>" . $row['prod_m']." - ".$row_prodm[0] . "</td>";
	echo "<td align='center'>" . $row['datum'] . "</td>";
	echo "<td align='center'>" . $row['datfak'] . "</td>";
	echo "<td align='center'>" . $denovi . "&nbsp</td>";
	echo "<td align='center'>" . $row['datval'] . "&nbsp</td>";
	echo "<td width='30%' align='center'>" . $row['komentar'] . "&nbsp</td>";
	echo "<td align='center'>" . $row['m_t']." - ".$row_komerc[0] . "</td>";

	echo "<td align='center'><a href=\"nalozi_s.php?promeni=".$row['id']."\">промени</a></td>";
	echo "<td align='center'><a href=\"?otkazi=".$row['id']."\" onclick=\"return confirm('Дали ја бришете/откажувате нарачката?');\">откажи</a></td>";

	echo "</tr>";
}
echo "</table>";
}

	echo "<br/><a href='nalozi_s.php'>Испрати барање</a>";
	

if (isset($_GET['otkazi'])){     
	$handle = connectwebnal();
	$otkazi=$_GET['otkazi'];
	$otkazi=mysqli_real_escape_string($handle, $otkazi);
	mysqli_query($handle, "DELETE FROM nar_glava where id=".$otkazi);
				echo ("<SCRIPT LANGUAGE='JavaScript'>
				window.location.href='mngprikaz_nalozi_s.php'
				</SCRIPT>");

}
?>