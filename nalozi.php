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
genmenu();
echo "<h4 align=center>Испрати барање</h4>";

if (isset($_GET['promeni'])){  //ako status ne e aktivno, ne smee da menuva
	$handle=connectwebnal();
	
	$promeni=$_GET['promeni'];
	$promeni=mysqli_real_escape_string($handle, $promeni);
	
	$check=mysqli_query($handle, "SELECT status from webnal where id=".$promeni);
	$checkstat=mysqli_fetch_row($check);
	
	if ($checkstat[0] != 1){
		echo ("<SCRIPT LANGUAGE='JavaScript'>
				window.alert('Не е дозволена промена на барањето!')
				window.location.href='mngprikaz_nalozi.php'
				</SCRIPT>");
	}
}


$den=date('d');
$mesec=date('m');
$god=date('Y');
$ch_mbr=$_SESSION['lgn'];

?>
<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />

<a href='mngprikaz_nalozi.php'>Назад</a><br/><br/>

<form method='POST'>
<table>
	<tr>
		<td align='right'><b>Дериват:</b></td>
		<td>
			<select id='derivat' name='derivat'>
				<?php 
					if (isset($_GET['promeni'])){
						$handle=connectwebnal();
						$promeni=$_GET['promeni'];
						$promeni=mysqli_real_escape_string($handle, $promeni);
						$rez=mysqli_query($handle, "select mat,kolicina,datum,zabeleska from webnal where id=".$promeni);
						$res=mysqli_fetch_row($rez);
						$rez1=mysqli_query($handle, "Select opis from materijali where cod='".$res[0]."'");
						$res1=mysqli_fetch_row($rez1);
						echo "<option value=$res[0]>$res1[0]</option>";
					}else echo "<option> </option>";
					
					$handle=connectwebnal();
					$sql = "select distinct cod,opis from materijali where tip='D'";
					$rsd = mysqli_query($handle, $sql);
					while($rs = mysqli_fetch_row($rsd)) {
    					echo "<option value=$rs[0]>$rs[1]</option>";
					}
				?>
			</select>
		</td></tr>
	<tr>
		<td align='right'><b>Количина:</b></td>
		<td><input type='text' onClick="this.select();" required name='kolicina' id='kolicina' value='<?php if (isset($_GET['promeni'])){ echo $res[1];}?>'/> литри</td>
	</tr>
	<tr>	
		<td align='right'><b>За датум:</b></td>
		<td>
			<input type="text" onClick="this.select();" required name="ZaDen"  maxlength = "2" size="2" value='<?php if (isset($_GET['promeni'])){ echo substr($res[2],8,2);} else {echo $den;}?>'>
			<input type="text" onClick="this.select();" required name="ZaMesec"  maxlength = "2" size="2" value='<?php if (isset($_GET['promeni'])){ echo substr($res[2],5,2);} else {echo $mesec;}?>'> 
			<input type="text" onClick="this.select();" required name="ZaGodina"  maxlength = "4" size="4" value='<?php if (isset($_GET['promeni'])){ echo substr($res[2],0,4);} else {echo $god;}?>'>
		</td>
	</tr>
	<tr>
		<td align='right'><b>Забелешка:</b></td>
		<td><textarea type='text' style="width: 255px; height: 70px" name='zabeleska' id='zabeleska'><?php if(isset($_GET['promeni'])){ echo $res[3]; } ?></textarea></td>
	</tr>
	<tr>
		<td><td align='right'><input type='submit' value='Испрати' name='Isprati' id='Isprati' onclick="return confirm('Дали го испраќате ова барање?');"/></td></td>
	</tr>
</table>
</form> 
</html>

<?php 

if (isset($_POST['derivat']) && !EMPTY($_POST['derivat']) && isset($_POST['kolicina']) && !EMPTY($_POST['kolicina']) && isset($_POST['ZaDen']) && isset($_POST['ZaMesec']) && isset($_POST['ZaGodina'])){
	
	$id_usr=getid();
	$firma=getidcmp_nal();
	
	$handle=connectwebnal();
	
	$datcre=date('Y-m-d H:i:s', time());
	$query=mysqli_query($handle, 'SELECT max(id) from webnal');
	$row=mysqli_fetch_row($query);
	$idid=$row[0]+1;
	
	$derivat=$_POST['derivat'];
	$derivat=mysqli_real_escape_string($handle,$derivat);
	
	$kolicina=$_POST['kolicina'];
	$kolicina=mysqli_real_escape_string($handle,$kolicina);
	
	if(isset($_POST['zabeleska']) && !EMPTY($_POST['zabeleska'])){
		$zabeleska=$_POST['zabeleska'];
		$zabeleska=mysqli_real_escape_string($handle,$zabeleska);
	}else{$zabeleska=' ';}
	
	if(!EMPTY($_POST['ZaDen']) && !EMPTY($_POST['ZaMesec']) && !EMPTY($_POST['ZaGodina']))
	{
		$ZaDen=$_POST['ZaDen'];
		$ZaDen=mysqli_real_escape_string($handle,$ZaDen);
		
		$ZaMesec=$_POST['ZaMesec'];
		$ZaMesec=mysqli_real_escape_string($handle,$ZaMesec);
		
		$ZaGodina=$_POST['ZaGodina'];
		$ZaGodina=mysqli_real_escape_string($handle,$ZaGodina);
		
		$datum="'".$ZaGodina."-".$ZaMesec."-".$ZaDen."'";
	}
	
	if (isset($_GET['promeni'])){
		$promeni=$_GET['promeni'];
		$promeni=mysqli_real_escape_string($handle, $promeni);
		$cmd=mysqli_query($handle, "UPDATE webnal set ch_mbr='$ch_mbr',datum=$datum,mat='$derivat',kolicina='$kolicina',status=1,datcre='$datcre',zabeleska='$zabeleska' where id=".$promeni);
		echo ("<SCRIPT LANGUAGE='JavaScript'>
				window.alert('Барањето е променето.')
				window.location.href='mngprikaz_nalozi.php'
				</SCRIPT>");
	}
	else
	{	
		$cmd=mysqli_query($handle,"INSERT INTO webnal(id,ch_mbr,firma,datum,mat,kolicina,status,datcre,zabeleska) VALUES($idid,'$ch_mbr','$firma',$datum,'$derivat','$kolicina',1,'$datcre','$zabeleska')");
		echo ("<SCRIPT LANGUAGE='JavaScript'>
				window.alert('Барањето е испратено.')
				window.location.href='mngprikaz_nalozi.php'
				</SCRIPT>");
	}
}

?>




