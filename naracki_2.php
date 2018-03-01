<html>
<link href="css/default.css" rel="stylesheet" type="text/css" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/jquery-2.0.3.min.js"></script>          
<script src="js/functions.js"></script>
<script type="text/javascript" src="js/jquery.js"></script>
<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />
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
$m_t=$_SESSION['lgn'];
genmenu();
$god=date("Y");
echo "<h4 align=center>Испрати нарачка</h4>";

$handle = connectwebnal();

if (isset($_GET['firma']) && !empty($_GET['firma']) && isset($_GET['sifra_firma']) && empty($_GET['sifra_firma'])){
	$firma=$_GET['firma'];
	$firma=mysqli_real_escape_string($handle,$firma);
	
	$cmd_zabrana=mysqli_query($handle, "select count(m_t) as zab from firmi_zabr_dozv where firma='$firma' and m_t='$m_t' and z_d='Z'");
	$zabrana=mysqli_fetch_row($cmd_zabrana);
	if ($zabrana[0]==0){
		$cmd_string=mysqli_query($handle , "select klasa from firmi where tip='F' and cod='$firma'");
		$string=mysqli_fetch_row($cmd_string);
		if (EMPTY($string[0])){
			
			$cmd_saldo=mysqli_query($handle , "select korisnik, sum(sumad) as sumad, sum(sumap) as sumap, sum(sumad-sumap) as saldo
												 from yana join konta on yana.konto=konta.konto and yana.godina=konta.godina
												 where yana.godina='$god'  and korisnik='$firma' and konta.help_k=1");
			$saldo=mysqli_fetch_row($cmd_saldo);
			
		}else{
			
			$aa=$string[0];
			$bb=explode(',' , $aa);
			$cc=count($bb);
			
			$res="(";
			
			if ($cc==1){
				$res="(yana.konto like '".$bb[0]."%')";
			}elseif ($cc>1){
				for ($i=0;$i<=$cc-1;$i++){
					$res.=" yana.konto like '".$bb[$i]."%'";
					if ($i<$cc-1){
						$res.=" or ";
					}
				}
			}
			
			$res=$res.")";
			
			$cmd_saldo=mysqli_query($handle, "select korisnik, sum(sumad) as sumad, sum(sumap) as sumap, sum(sumad-sumap) as saldo
						 						from yana where godina='$god' and korisnik='$firma' and $res");
			$saldo=mysqli_fetch_row($cmd_saldo);
		}
	}
	
	$cmd_vknar_nerealizirani=mysqli_query($handle, "select (select cod from firmi where tip='F' and cod='$firma') as firma, 
											ifnull(sum(nar_stavki.vk_vr), 0000.000) as naracka_nerealizirana 
											from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id 
											where nar_glava.firma='$firma' and nar_glava.nalb='' and nar_glava.godina='$god' and nar_glava.brisi=0 and nar_stavki.brisi=0");
	$nerealizirani=mysqli_fetch_row($cmd_vknar_nerealizirani);
	
	
	$cmd_vknar=mysqli_query($handle, "select (select cod from firmi where tip='F' and cod='$firma') as firma,
			ifnull(sum(nar_stavki.vk_vr), 0000.00) as naracka_vk
			from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id
			where nar_glava.firma='$firma' and nar_glava.godina='$god' and nar_glava.brisi=0  and nar_stavki.brisi=0 ");
	$vknar=mysqli_fetch_row($cmd_vknar);
	
	
	$cmd_check=mysqli_query($handle , "select if(f_saldo<0 and limit_f,0,1) as dozvola, f_saldo, fin_limit,
										finansii as finansii_sostojba, naracka_vk as naracka_nerealizirana, limit_f
										from (select korisnik, fin_limit,finansii,naracka_vk, fin_limit+finansii-naracka_vk as f_saldo, limit_f
										from (select (select cod from firmi where tip='F' and cod='$firma') as korisnik, ifnull(sum(sumap-sumad),0.0000) as 'finansii',
										(select limit_f from firmi where firmi.tip='F' and firmi.cod=korisnik) as limit_f, (select fin_limit from firmi where firmi.tip='F' and firmi.cod=korisnik) as fin_limit
										from yana join konta on yana.konto=konta.konto and yana.godina=konta.godina where yana.godina='$god' and help_k=1 and korisnik='$firma' ) as fin
										join (select (select cod from firmi where tip='F' and cod='$firma') as firma, ifnull(sum(nar_stavki.vk_vr), 0000.000) as naracka_vk
										from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id where nar_glava.firma='$firma'  and nar_glava.nalb='' and nar_glava.brisi=0  and nar_stavki.brisi=0 
										and nar_glava.godina='$god') as nar_vk
										on fin.korisnik=nar_vk.firma) as site");
	$check=mysqli_fetch_row($cmd_check);
	if ($check[0]==0){
		echo ("<SCRIPT LANGUAGE='JavaScript'>
				window.alert('Надминат лимит!')
				window.location.href='naracki.php'
				</SCRIPT>");
		exit();
	}
	
	$cmd=mysqli_query($handle, "SELECT opis from firmi where cod=$firma");
	$firma_opis=mysqli_fetch_row($cmd);
	$_SESSION['firma']=$firma;
	$_SESSION['firma_opis']=$firma_opis[0];

	if (isset($saldo[3]) && !empty($saldo[3])){
		$_SESSION['saldo']=$saldo[3]*(-1);
	}else {$_SESSION['saldo']='';}
	if (isset($nerealizirani[1]) && !empty($nerealizirani[1])){
		$_SESSION['nerealizirani']=$nerealizirani[1]*(-1);
	}else {$_SESSION['nerealizirani']='';}
	
	$res = mysqli_query($handle, "select cod,opis from org_e where korisnik=$firma AND m_t=$m_t");
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
	<td> </td>
	<td align='right'><b>Салдо во финансии:</b> </td>
	<td align='right'><?php 	if (isset($_SESSION['saldo']) && !empty($_SESSION['saldo'])){
					$aa=$_SESSION['saldo'];
					if ($aa<0){
						echo "<font color='red'><b>".number_format($aa,2)."</b></font>";
					}else{echo "<font color='green'><b>".number_format($aa,2)."</b></font>";}
				} ?>
	</td>
</tr>
<tr>
	<td> </td>
	<td align='right'><b> Нереализирани нарачки:</b> </td>
	<td align='right'><?php 	if (isset($nerealizirani[1]) && !empty($nerealizirani[1])){
									$nerealizirani[1]=$nerealizirani[1]*(-1);
									if ($nerealizirani[1]<0){
										echo "<font color='red'><b>".number_format($nerealizirani[1],2)."</b></font>";
									}else{echo "<font color='green'><b>".number_format($nerealizirani[1],2)."</b></font>";}
				} ?>
	
	</td>
</tr>
<tr>
	<td><b><?php if (isset($check[5]) && !empty($check[5])){
					echo "<b>Има </b>";
			}else{echo "<b>Нема </b>";}?></b>забрана при негативно салдо</td>
	<td align='right'><b> Тековно салдо:</b> </td>
	<td align='right' ><?php 	if (isset($check[1]) && !empty($check[1])){
									if($check[1]<0){
										echo "<font color='red'><b>".number_format($check[1],2)."</b></font>";
									}else{echo "<font color='red'><b>".number_format($check[1],2)."</b></font>";}
				} ?>
	
	</td>
</tr>
</table>

<form method='GET' action='naracki_save.php'>

<?php 
		echo "<input type='hidden' value='$firma' name='firma' id='firma'>";
	while($row = mysqli_fetch_row($res)) {
		echo "<br/><button type='submit' value='".$row[0]."' style='height: 10%; width: 30%' name='prod_m' id='prod_m'>$row[1]</button>";
		//echo "<br/><br/><a style='font-size: 250%;' href='naracki_save.php?prod_m=".$row[0]."&firma=".$firma."'>".$row[1]."</a>";
	}
echo "</form>";

}
elseif (isset($_GET['sifra_firma']) && !EMPTY($_GET['sifra_firma'])){
	$firma_arr=explode(' - ',$_GET['sifra_firma']);
	$firma=$firma_arr[0];
	$firma=mysqli_real_escape_string($handle, $firma);
	
	$cmd_firma=mysqli_query($handle, "SELECT cod from firmi where cod='$firma'");
	$ima_firma=mysqli_fetch_row($cmd_firma);
	if (EMPTY($ima_firma[0])){
		echo ("<SCRIPT LANGUAGE='JavaScript'>
				window.alert('Неправилен код')
				window.location.href='naracki_1.php?dejnost=".$_SESSION['dejnost']."'
				</SCRIPT>");
		exit();
	}
	
	$cmd_zabrana=mysqli_query($handle, "select count(m_t) as zab from firmi_zabr_dozv where firma='$firma' and m_t='$m_t' and z_d='Z'");
	$zabrana=mysqli_fetch_row($cmd_zabrana);
	
	if ($zabrana[0]==0){
		$cmd_string=mysqli_query($handle , "select klasa from firmi where tip='F' and cod='$firma'");
		$string=mysqli_fetch_row($cmd_string);
		if (EMPTY($string[0])){
				
			$cmd_saldo=mysqli_query($handle , "select korisnik, sum(sumad) as sumad, sum(sumap) as sumap, sum(sumad-sumap) as saldo
												from yana join konta on yana.konto=konta.konto and yana.godina=konta.godina
												where yana.godina='$god'  and korisnik='$firma' and konta.help_k=1");
			$saldo=mysqli_fetch_row($cmd_saldo);
				
		}else{
				
			$aa=$string[0];
			$bb=explode(',' , $aa);
			$cc=count($bb);
				
			$res="(";
				
			if ($cc==1){
				$res="(yana.konto like '".$bb[0]."%')";
			}elseif ($cc>1){
				for ($i=0;$i<=$cc-1;$i++){
					$res.=" yana.konto like '".$bb[$i]."%'";
					if ($i<$cc-1){
						$res.=" or ";
					}
				}
			}
				
			$res=$res.")";
				
			$cmd_saldo=mysqli_query($handle, "select korisnik, sum(sumad) as sumad, sum(sumap) as sumap, sum(sumad-sumap) as saldo
												from yana where godina='$god' and korisnik='$firma' and $res");
			$saldo=mysqli_fetch_row($cmd_saldo);

		}
	}
	
	$cmd_vknar_nerealizirani=mysqli_query($handle, "select (select cod from firmi where tip='F' and cod='$firma') as firma,
			ifnull(sum(nar_stavki.vk_vr), 0000.000) as naracka_nerealizirana
			from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id
			where nar_glava.firma='$firma' and nar_glava.nalb='' and nar_glava.godina='$god' and nar_glava.brisi=0 and nar_stavki.brisi=0");
	$nerealizirani=mysqli_fetch_row($cmd_vknar_nerealizirani);
	
	
	$cmd_vknar=mysqli_query($handle, "select (select cod from firmi where tip='F' and cod='$firma') as firma,
			ifnull(sum(nar_stavki.vk_vr), 0000.00) as naracka_vk
			from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id
			where nar_glava.firma='$firma' and nar_glava.godina='$god' and nar_glava.brisi=0 and nar_stavki.brisi=0");
	$vknar=mysqli_fetch_row($cmd_vknar);
	
	$cmd_check=mysqli_query($handle , "select if(f_saldo<0 and limit_f,0,1) as dozvola, f_saldo, fin_limit,
										finansii as finansii_sostojba, naracka_vk as naracka_nerealizirana, limit_f
										from (select korisnik, fin_limit,finansii,naracka_vk, fin_limit+finansii-naracka_vk as f_saldo, limit_f
										from (select (select cod from firmi where tip='F' and cod='$firma') as korisnik, ifnull(sum(sumap-sumad),0.0000) as 'finansii',
										(select limit_f from firmi where firmi.tip='F' and firmi.cod=korisnik) as limit_f, (select fin_limit from firmi where firmi.tip='F' and firmi.cod=korisnik) as fin_limit
										from yana join konta on yana.konto=konta.konto and yana.godina=konta.godina where yana.godina='$god' and help_k=1 and korisnik='$firma' ) as fin
										join (select (select cod from firmi where tip='F' and cod='$firma') as firma, ifnull(sum(nar_stavki.vk_vr), 0000.000) as naracka_vk
										from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id where nar_glava.firma='$firma'  and nar_glava.nalb='' and nar_glava.brisi=0  and nar_stavki.brisi=0 
										and nar_glava.godina='$god') as nar_vk
										on fin.korisnik=nar_vk.firma) as site");
	$check=mysqli_fetch_row($cmd_check);
	if ($check[0]==0){
		echo ("<SCRIPT LANGUAGE='JavaScript'>
				window.alert('Надминат лимит!')
				window.location.href='naracki.php'
				</SCRIPT>");
		exit();
	}
	
	$cmd=mysqli_query($handle, "SELECT opis from firmi where cod=$firma");
	$firma_opis=mysqli_fetch_row($cmd);
	$_SESSION['firma']=$firma;
	$_SESSION['firma_opis']=$firma_opis[0];

	if (isset($saldo[3]) && !empty($saldo[3])){
		$_SESSION['saldo']=$saldo[3]*(-1);
	}else {$_SESSION['saldo']='';}
	if (isset($nerealizirani[1]) && !empty($nerealizirani[1])){
		$_SESSION['nerealizirani']=$nerealizirani[1]*(-1);
	}else {$_SESSION['nerealizirani']='';}
	
	$res = mysqli_query($handle, "select cod,opis from org_e where korisnik=$firma AND m_t=$m_t");
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
	<td> </td>
	<td align='right'><b>Салдо во финансии:</b> </td>
	<td align='right'><?php 	if (isset($_SESSION['saldo']) && !empty($_SESSION['saldo'])){
					$aa=$_SESSION['saldo'];
					if ($aa<0){
						echo "<font color='red'><b>".number_format($aa,2)."</b></font>";
					}else{echo "<font color='green'><b>".number_format($aa,2)."</b></font>";}
				} ?>
	</td>
</tr>
<tr>
	<td> </td>
	<td align='right'><b> Нереализирани нарачки:</b> </td>
	<td align='right'><?php 	if (isset($nerealizirani[1]) && !empty($nerealizirani[1])){
										$nerealizirani[1]=$nerealizirani[1]*(-1);
										if ($nerealizirani[1]<0){
											echo "<font color='red'><b>".number_format($nerealizirani[1],2)."</b></font>";
										}else{echo "<font color='green'><b>".number_format($nerealizirani[1],2)."</b></font>";}
				} ?>
	
	</td>
</tr>
<tr>
	<td><b><?php if (isset($check[5]) && !empty($check[5])){
					echo "<b>Има </b>";
			}else{echo "<b>Нема </b>";}?></b>забрана при негативно салдо</td>
	<td align='right'><b> Тековно салдо:</b> </td>
	<td align='right' ><?php 	if (isset($check[1]) && !empty($check[1])){
									if ($check[1]<0){
										echo "<font color='red'><b>".number_format($check[1],2)."</b></font>";
									}else {echo "<font color='green'><b>".number_format($check[1],2)."</b></font>";}
				} ?>
	
	</td>
</tr>
</table>

<form method='GET' action='naracki_save.php'>
	
	<?php 
			echo "<input type='hidden' value='$firma' name='firma' id='firma'>";
		while($row = mysqli_fetch_row($res)) {
			echo "<br/><button type='submit' value='".$row[0]."' style='height: 10%; width: 30%' name='prod_m' id='prod_m'>$row[1]</button>";
			//echo "<br/><br/><a style='font-size: 250%;' href='naracki_save.php?prod_m=".$row[0]."&firma=".$firma."'>".$row[1]."</a>";
		}
}
?>
</form>
</body>
</html>
