<?php
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!logged()) redirect('index.php?rx=mngprikaz_nalozi');
if (nalog()==0){ // treba adminsko pravo da ima, ova da se smeni
	echo ("<SCRIPT LANGUAGE='JavaScript'>
			window.alert('Немате дозвола за пристап.')
			window.location.href='index.php'
			</SCRIPT>");
}
genmenu();
echo "<h4 align=center>Барања</h4>";

$den=date('d');
$mesec=date('m');
$god=date('Y');
if (isset($_SESSION['cmpnalozi'])){ $cmp=$_SESSION['cmpnalozi'];}
?>
<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery.js"></script>
<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />

<?php
if (isadmin_nalozi()){
?>
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
}
?>

<script type="text/javascript">
$().ready(function() {
    $("#derivat").autocomplete("popuni_derivat.php", {
        width: 260,
        matchContains: true,
        selectFirst: false
    });
});
</script>

<form method='POST'>
	<table>
	  <tr>
	  	<td align='left'>Фирма:</td>
	  	<td><input type='text' onClick="this.select();" size='40' <?php if (!isadmin_nalozi()) echo 'readonly';?> name='firma' id='firma' value='<?php 
	  																if (isadmin_nalozi()){	  																	
	  																	if (isset($_REQUEST['firma'])){ echo $_REQUEST['firma'];}
	  																}else{
	  																	$handle=connectwebnal();
	  																	$conn=mysqli_query($handle, "SELECT concat(cod,' - ',opis) from firmi where cod='".$_SESSION['cmpnalozi']."'");
	  																	$cmp_nal=mysqli_fetch_row($conn);
	  																	echo $cmp_nal[0];
	  																}
	  														  	?>' >
	  	</td>
	  	<td align='right'>Статус:</td>
	  	<td><select id='status' name='status'>
	  			<option></option>  			
				<option value=1>активни</option>
				<option value=2>прифатени</option>
				<option value=3>завршени</option>
				<option value=4>откажани</option>
	  		</select>
	  	</td>
	  <tr>
	  	<td align='left'>Дериват:</td>
	  	<td><input type='text' onClick="this.select();" name='derivat' id='derivat' value='<?php if (isset($_REQUEST['derivat'])){ echo $_REQUEST['derivat'];}?>' ></td>
	  </tr>
	  <tr>
	  	<td align='right'>Од датум:</td>
		<td>
			<input type="text" onClick="this.select();" required name="OdDen"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['OdDen'])){ echo $_REQUEST['OdDen'];} else {echo $den;}?>'>
			<input type="text" onClick="this.select();" required name="OdMesec"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['OdMesec'])){ echo $_REQUEST['OdMesec'];} else {echo $mesec;}?>'> 
			<input type="text" onClick="this.select();" required name="OdGodina"  maxlength = "4" size="4" value='<?php if (isset($_REQUEST['OdGodina'])){ echo $_REQUEST['OdGodina'];} else {echo $god;}?>'>
		</td>
		<td align='right'>До датум:</td>
		<td>
			<input type="text" onClick="this.select();" required name="DoDen"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['DoDen'])){ echo $_REQUEST['DoDen'];} else {echo $den;}?>'>
			<input type="text" onClick="this.select();" required name="DoMesec"  maxlength = "2" size="2" value='<?php if (isset($_REQUEST['DoMesec'])){ echo $_REQUEST['DoMesec'];} else {echo $mesec;}?>'> 
			<input type="text" onClick="this.select();" required name="DoGodina"  maxlength = "4" size="4" value='<?php if (isset($_REQUEST['DoGodina'])){ echo $_REQUEST['DoGodina'];} else {echo $god;}?>'>
		</td>
	  </tr>
	  <tr>
	  	<td><input type='submit' value='Барај'></td>
	  </tr>
	</table>
</form>

<?php
$handle=connectwebnal();

IF (isadmin_nalozi())
{  //ako e logiran admin za nalozi
	if (isset($_POST['firma']) && !empty($_POST['firma'])){
		$firma_arr=explode(' - ',$_POST['firma']);
		$firma=$firma_arr[0];
		$firma=mysqli_real_escape_string($handle, $firma);
	}
	
	if (isset($_POST['derivat']) && !EMPTY($_POST['derivat'])){
		$derivat_arr=explode(' - ',$_POST['derivat']);
		$derivat=$derivat_arr[0];
		$derivat=mysqli_real_escape_string($handle, $derivat);
	}
	
	if (isset($_POST['status']) && !EMPTY($_POST['status'])){
		$status=$_POST['status'];
		$status=mysqli_real_escape_string($handle, $status);
		//$_SESSION['nalog_status']=$status;
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
	
	
	
	if (isset($_POST['firma']) && !EMPTY($_POST['firma'])){
		if (isset($_POST['status']) && !EMPTY($_POST['status'])){
			if (isset($_POST['derivat']) && !EMPTY($_POST['derivat'])){
				$res=mysqli_query($handle, "SELECT * from webnal where firma='$firma' and status=$status and mat='$derivat' and datum between $oddat and $dodat order by datcre desc");
				$_SESSION['select_nal']="SELECT * from webnal where firma='$firma' and status=$status and mat='$derivat' and datum between $oddat and $dodat order by datcre desc";
			}
			else
			{
				$res=mysqli_query($handle, "SELECT * from webnal where firma='$firma' and status=$status and datum between $oddat and $dodat order by datcre desc");
				$_SESSION['select_nal']="SELECT * from webnal where firma='$firma' and status=$status and datum between $oddat and $dodat order by datcre desc";
			}
		}
		else
		{
			$res=mysqli_query($handle, "SELECT * from webnal where firma='$firma' and datum between $oddat and $dodat order by datcre desc");
			$_SESSION['select_nal']="SELECT * from webnal where firma='$firma' and datum between $oddat and $dodat order by datcre desc";
		}	
		
	}
	else
	{
		if (isset($_POST['status']) && !EMPTY($_POST['status'])){
			if (isset($_POST['derivat']) && !EMPTY($_POST['derivat'])){
				$res=mysqli_query($handle, "SELECT * from webnal where status=$status and mat='$derivat' and datum between $oddat and $dodat order by datcre desc");
				$_SESSION['select_nal']="SELECT * from webnal where status=$status and mat='$derivat' and datum between $oddat and $dodat order by datcre desc";
			}
			else{
				$res=mysqli_query($handle, "SELECT * from webnal where status=$status and datum between $oddat and $dodat order by datcre desc");
				$_SESSION['select_nal'] = "SELECT * from webnal where status=$status and datum between $oddat and $dodat order by datcre desc";
			}
		}
		else
		{
			if (isset($_POST['derivat']) && !EMPTY($_POST['derivat'])){
				$res=mysqli_query($handle, "SELECT * from webnal where mat='$derivat' and datum between $oddat and $dodat order by datcre desc");
				$_SESSION['select_nal']="SELECT * from webnal where mat='$derivat' and datum between $oddat and $dodat order by datcre desc";
			}
			else
			{
				$res=mysqli_query($handle, "SELECT * from webnal where datum between $oddat and $dodat order by datcre desc");
				$_SESSION['select_nal']="SELECT * from webnal where datum between $oddat and $dodat order by datcre desc";
			}
		}
	}
}
else   //ako e logiran korisnik
{
	if (isset($_POST['derivat']) && !EMPTY($_POST['derivat'])){
		$derivat_arr=explode(' - ',$_POST['derivat']);
		$derivat=$derivat_arr[0];
		$derivat=mysqli_real_escape_string($handle, $derivat);
	}
	
	if (isset($_POST['status']) && !EMPTY($_POST['status'])){
		$status=$_POST['status'];
		$status=mysqli_real_escape_string($handle, $status);
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
	else {$oddat="'".$god."-".$mesec."-".$den."'";
	}
	
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
	else{$dodat="'".$god."-".$mesec."-".$den."'";
	}
	
	if (isset($_POST['status']) && !EMPTY($_POST['status'])){
		if (isset($_POST['derivat']) && !EMPTY($_POST['derivat'])){
			$res=mysqli_query($handle, "SELECT * from webnal where firma='$cmp' and status=$status and mat='$derivat' and datum between $oddat and $dodat and status<>4 order by datcre desc");
			$_SESSION['select_nal']="SELECT * from webnal where firma='$cmp' and status=$status and mat='$derivat' and datum between $oddat and $dodat and status<>4 order by datcre desc";
		}
		else{
			$res=mysqli_query($handle, "SELECT * from webnal where firma='$cmp' and status=$status and datum between $oddat and $dodat and status<>4 order by datcre desc");
			$_SESSION['select_nal']="SELECT * from webnal where firma='$cmp' and status=$status and datum between $oddat and $dodat and status<>4 order by datcre desc";
		}
	}
	else 
	{
		if (isset($_POST['derivat']) && !EMPTY($_POST['derivat'])){
			$res=mysqli_query($handle, "SELECT * from webnal where firma='$cmp' and mat='$derivat' and datum between $oddat and $dodat and status<>4 order by datcre desc");
			$_SESSION['select_nal']="SELECT * from webnal where firma='$cmp' and mat='$derivat' and datum between $oddat and $dodat and status<>4 order by datcre desc";
		}
		else{
			$res=mysqli_query($handle, "SELECT * from webnal where firma='$cmp' and datum between $oddat and $dodat and status<>4 order by datcre desc");
			$_SESSION['select_nal']="SELECT * from webnal where firma='$cmp' and datum between $oddat and $dodat and status<>4 order by datcre desc";
		}
	}
}

echo "<a href='fpdfnalozi.php' target='_blank'>Печати PDF<a><br/>";
echo "<table border='2' cellspacing='0' width='100%'>
<tr>
	<th>Фирма</th>
	<th>Дериват</th>
	<th>Количина во литри</th>
	<th>Количина - налог</th>
	<th>Количина - испорака</th>
	<th>За датум</th>
	<th>Забелешка</th>
	<th>Статус</th>";
if (!isadmin_nalozi()){
	echo "<th>&nbsp</th>
		  <th>&nbsp</th>";
}
"</tr>";

while($row = mysqli_fetch_array($res))
{
	$con=mysqli_query($handle, "SELECT opis from firmi where cod='".$row['firma']."'");
	$row_firma=mysqli_fetch_row($con);
	
	$con1=mysqli_query($handle, "SELECT opis from materijali where cod='".$row['mat']."'");
	$row_mat=mysqli_fetch_row($con1);
	
	switch ($row['status']){
		case 1:
			$stat='активно';
			break;
		case 2:
			$stat='прифатено';
			break;
		case 3:
			$stat='завршено';
			break;
		case 4:
			$stat='откажано';
			break;
	}
	
	echo "<tr>";
	echo "<td align='center'>" . $row['firma']." - ".$row_firma[0] . "</td>";
	echo "<td align='center'>" . $row_mat[0] . "</td>";
	echo "<td align='center'>" . $row['kolicina'] . "</td>";
	echo "<td align='center'>" . $row['kolicina_nalog'] . "&nbsp</td>";
	echo "<td align='center'>" . $row['kolicina_isporaka'] . "&nbsp</td>";
	echo "<td align='center'>" . $row['datum'] . "</td>";
	echo "<td align='center'>" . $row['zabeleska'] . "&nbsp</td>";
	echo "<td align='center'>" . $stat . "</td>";
	if (!isadmin_nalozi()){
		echo "<td align='center'><a href=\"nalozi.php?promeni=".$row['id']."\">промени</a></td>";
		echo "<td align='center'><a href=\"?otkazi=".$row['id']."\" onclick=\"return confirm('Дали го бришете/откажувате барањето?');\">откажи</a></td>";
	}
	echo "</tr>";
}
echo "</table>";

if (!isadmin_nalozi()){
	echo "<br/><a href='nalozi.php'>Испрати барање</a>";
}
if (isset($_GET['otkazi'])){
	$handle = connectwebnal();
	$otkazi=$_GET['otkazi'];
	$otkazi=mysqli_real_escape_string($handle, $otkazi);
	$con2=mysqli_query($handle, "SELECT status from webnal where id=".$otkazi);
	$row_stat=mysqli_fetch_row($con2);
	if ($row_stat[0] != 1){
		echo ("<SCRIPT LANGUAGE='JavaScript'>
				window.alert('Не е дозволено откажување на барањето!')
				window.location.href='mngprikaz_nalozi.php'
				</SCRIPT>");
	}else{
		mysqli_query($handle, "UPDATE webnal set status=4 where id=".$otkazi);
		header("location:mngprikaz_nalozi.php");
	}
}
?>