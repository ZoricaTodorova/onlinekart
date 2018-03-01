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
$datum=date('Y-m-d H:i:s');
$datum=substr($datum,0,10);
genmenu();
echo "<h4 align=center>Испрати нарачка</h4>";

$handle=connectwebnal();

$boja=$_SESSION['boja'];
echo "<body bgcolor='$boja'>";

if (isset($_GET['glava_id']) && !EMPTY($_GET['glava_id'])){
	$glava_id=$_GET['glava_id'];
	$glava_id=mysqli_real_escape_string($handle,$glava_id);
	
	$firma=$_SESSION['firma'];
	
	$cmd_vknar_nerealizirani=mysqli_query($handle, "select (select cod from firmi where tip='F' and cod='$firma') as firma,
			ifnull(sum(nar_stavki.vk_vr), 0000.000) as naracka_nerealizirana
			from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id
			where nar_glava.firma='$firma' and nar_glava.nalb='' and nar_glava.godina='$god' and nar_glava.brisi=0 and nar_stavki.brisi=0 ");
	$nerealizirani=mysqli_fetch_row($cmd_vknar_nerealizirani);
	
	$cmd_check=mysqli_query($handle, " select if(f_saldo<0 and limit_f,0,1) as dozvola, f_saldo, fin_limit,
			finansii as finansii_sostojba, naracka_st as naracka_segasna ,
			naracka_vk as naracka_nerealizirana, limit_f
			from (select korisnik, fin_limit,finansii,naracka_st,naracka_vk, fin_limit+finansii-naracka_st-naracka_vk-0 as f_saldo, limit_f
			from (select (select cod from firmi where tip='F' and cod='$firma') as korisnik, ifnull(sum(sumap-sumad),0.0000) as 'finansii',
			(select limit_f from firmi where firmi.tip='F' and firmi.cod=korisnik) as limit_f, (select fin_limit from firmi where firmi.tip='F' and firmi.cod=korisnik) as fin_limit
			from yana join konta on yana.konto=konta.konto and yana.godina=konta.godina where yana.godina='$god' and help_k=1 and korisnik='$firma' ) as fin
			join (select  (select cod from firmi where tip='F' and cod='$firma') as firma, ifnull(sum(nar_stavki.vk_vr), 0000.000) as naracka_st
			from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id where nar_glava.firma='$firma' and nar_glava.id=$glava_id and nar_glava.godina='$god' and nar_glava.brisi=0  and nar_stavki.brisi=0 ) as nar
			on fin.korisnik=nar.firma
			join (select (select cod from firmi where tip='F' and cod='$firma') as firma,
			ifnull(sum(nar_stavki.vk_vr), 0000.000) as naracka_vk
			from nar_glava join nar_stavki on nar_glava.id=nar_stavki.nar_glava_id
			where nar_glava.firma='$firma' and nar_glava.id<>$glava_id and nar_glava.nalb=''  and nar_glava.brisi=0  and nar_stavki.brisi=0 
			and nar_glava.godina='$god') as nar_vk
			on fin.korisnik=nar_vk.firma) as site");
	
			$check=mysqli_fetch_row($cmd_check);

?>

</br>

<?php
		
		$query=mysqli_query($handle, "SELECT rbp from firmi where cod='$firma'");
		$rabat=mysqli_fetch_row($query);
		
		$cmd1=mysqli_query($handle, "Select tip_cena from firmi where cod='".$firma."'");
		$tip_arr=mysqli_fetch_row($cmd1);
		if (EMPTY($tip_arr[0])){
			$tip=1;
		}else{$tip=$tip_arr[0];}
	
		
		$res=mysqli_query($handle, "select mat, opis,sum(kol_v-kol_i) as zaliha, cena, nar_kolicina, ddv_proc from(
										SELECT nar_st.nar_kolicina, ceno.cena,sostojba.mat,maus.opis, sostojba.kol_v, sostojba.kol_i,sostojba.ddv_proc
										FROM  (SELECT Ymana.mat,Ymana.ddv_proc,  SUM(Ymana.kolicina) as kol_v,   000000000.000 as kol_i  
										FROM Ymana  
										WHERE Ymana.godina = '$god' 
										AND Ymana.status='2'  
										AND Ymana.m_u= 'M' 
										AND Ymana.fe_v  = 'E'
										AND Ymana.cod_v = '02-1'    AND IF(Ymana.codr='', 1, obrabotka <> 0)  
										GROUP BY Ymana.mat
										UNION ALL    
										SELECT Ymana.mat,  Ymana.ddv_proc, 000000000.000 as kol_v,   SUM(Ymana.kolicina) as kol_i  
										FROM Ymana 
										WHERE Ymana.godina = '$god' 
										AND Ymana.status='2'  
										AND Ymana.m_u= 'M'  
										AND Ymana.fe_i = 'E'
										AND Ymana.cod_i = '02-1'    AND IF(Ymana.codr='', 1, obrabotka <> 0)  
										GROUP BY   Ymana.mat ) as sostojba
										inner join (SELECT cod , opis  from materijali  where godina ='$god' and web_mat=1 and m_u='M')  as maus 
										ON sostojba.mat = maus.cod 
										left join (select cena,mat from cenovnik where cenovnik.tip ='1'
										and concat(cast(oddat as char),odcas)<= concat(CAST(CAST('$datum' as date) as char),'00:00')
										and if(cenovnik.dodat<>'0000-00-00',concat(cast(dodat as char),docas)>=CONCAT(CAST(CAST('$datum' as
										date) as char),'00:00') ,cenovnik.dodat='0000-00-00')) as ceno on sostojba.mat=ceno.mat
										left join (select sum(kolicina) as nar_kolicina, mat from nar_stavki where nalb='' and brisi=0 and nar_glava_id<>$glava_id 
										and nar_glava_id in (select distinct id from nar_glava where datum like '$god%') group by mat)
											 as nar_st on sostojba.mat=nar_st.mat				
										) as final group by mat order by opis");
		
		
		echo "<form method='POST' action='naracki_grid_presmetka.php'>
				<table width='100%' border='1'>
				<tr>
					<th><font size='5'></font></th>
					<th><font size='5'>Материјал</font></th>
					<th align='center'><font size='5'>Залиха</font></th>
					<th align='center'><font size='5'>Цена</font></th>
					<th><font size='5'>Рабат</font></th>
					<th><font size='5'>Количина</font></th>
					<th>&nbsp</th>
				</tr>";
		
		
	$i=1;
	while ($row=mysqli_fetch_row($res)){
		$mat=$row[0];
		
		$mat_opis=$row[1];
			
		$akc_dat1=date('Y-m-d', strtotime($datum. ' + 1 days'));
		$akc_dat2=date('Y-m-d', strtotime($datum. ' - 1 days'));
		$cmd_akciza=mysqli_query($handle, "SELECT akciza.vrednost as akciza FROM akciza_p 
			 INNER JOIN akciza ON akciza.id=akciza_p.id 
			 WHERE akciza_p.oddat<'$akc_dat1' and if(akciza_p.dodat<>'0000-00-00',akciza_p.dodat>'$akc_dat2',akciza_p.dodat='0000-00-00')
			 and akciza.cod in (select akciza_cod from materijali where godina='$god' and cod='$mat')");
		
		$akciza=mysqli_fetch_row($cmd_akciza);
		
		$cena=$row[3];
		$cena_ddv=($cena+$akciza[0])*(1+$row[5]/100);
		
		$zaliha=$row[2];
		
		$kol=$row[4];
			
		$vkupno=$zaliha - $kol;
		
		if ($i % 2 == 0){
			$paren=true;
		}else{$paren=false;}
		
		$cmd4=mysqli_query($handle, "SELECT COUNT(kolicina) FROM (SELECT kolicina,propusteno from nar_stavki where nar_glava_id=$glava_id and brisi=0 and mat='$mat') as aa");
		$count=mysqli_fetch_row($cmd4);
		if ($count[0]>1){
			$cmd3=mysqli_query($handle, "SELECT SUM(kolicina)+SUM(propusteno) from nar_stavki where nar_glava_id=$glava_id and mat='$mat' and brisi=0 ");
			$mat_kol=mysqli_fetch_row($cmd3);
			$cmd5=mysqli_query($handle, "SELECT rabat from nar_stavki where nar_glava_id=$glava_id and mat='$mat' and brisi=0 ");
			$mat_rab=mysqli_fetch_row($cmd5);
		}else{
			$cmd3=mysqli_query($handle, "SELECT kolicina,propusteno from nar_stavki where nar_glava_id=$glava_id and brisi=0 and mat='$mat'");
			$mat_kol=mysqli_fetch_row($cmd3);
			$cmd5=mysqli_query($handle, "SELECT rabat from nar_stavki where nar_glava_id=$glava_id and mat='$mat' and brisi=0 ");
			$mat_rab=mysqli_fetch_row($cmd5);
		}
				
?>
	<tr>
	    <td <?php if ($paren) echo "bgcolor='#B8B8B8'" ?> ><input type='hidden' readonly required value='<?php echo $glava_id; ?>' style="height:60px; width:100%; text-align:center; font-size:22px; border:none;" onClick="this.select();" name='glava_id' id='glava_id'/></td>
		<td align='left' width='60%' <?php if ($paren) echo "bgcolor='#B8B8B8'" ?> ><input type='text' readonly required value='<?php echo $mat." - ".$mat_opis; ?>' style="height:60px; width:100%; text-align:left; background-color:<?php if($paren) {echo '#B8B8B8';} else echo '#'. $_SESSION['boja']; ?> ;font-size:22px; border:none;<?php if (EMPTY($vkupno) || ($vkupno==0)){echo "color:red";} ?>" onClick="this.select();" name='mat<?php echo $i;?>' id='mat<?php echo $i;?>'/></td>
		<td align='center' width='5%' <?php if ($paren) echo "bgcolor='#B8B8B8'" ?>><input type='text'  readonly value='<?php echo $vkupno; ?>' style="height:60px; width:100%; text-align:center; background-color:<?php if($paren) {echo '#B8B8B8';} else echo '#'. $_SESSION['boja']; ?> ;font-size:22px; border:none;<?php if (EMPTY($vkupno) || ($vkupno==0)){echo "color:red";} ?>" onClick="this.select();" name='zaliha<?php echo $i;?>' id='zaliha<?php echo $i;?>'/></td>
		<td align='center' width='5%' <?php if ($paren) echo "bgcolor='#B8B8B8'" ?>><input type='text'  readonly value='<?php echo number_format($cena_ddv,0); ?>' style="height:60px; text-align:center; background-color:<?php if($paren) {echo '#B8B8B8';} else echo '#'. $_SESSION['boja']; ?> ;font-size:22px; border:none;<?php if (EMPTY($vkupno) || ($vkupno==0)){echo "color:red";} ?>" onClick="this.select();" name='cena<?php echo $i;?>' id='cena<?php echo $i;?>'/></td>
		<td align='center' width='10%' <?php if ($paren) echo "bgcolor='#B8B8B8'" ?>><input type='text' style="height:60px; text-align:center; width:70%; font-size:22px;<?php if (EMPTY($vkupno) || ($vkupno==0)){echo "color:red";} ?>" onClick="this.select();" value='<?php if(isset($mat_rab[0]) && !EMPTY($mat_rab[0]) && $mat_rab[0]<>0){ echo number_format($mat_rab[0],0);}
																																																								  												elseif(isset($mat_rab[1]) && !EMPTY($mat_rab[1]) && $mat_rab[1]<>0){ echo number_format($mat_rab[1],0);} ?>' name='rabat<?php echo $i;?>' id='rabat<?php echo $i;?>'/></td>
				
		<td align='center' width='10%' <?php if ($paren) echo "bgcolor='#B8B8B8'" ?>><input type='text' style="height:60px; text-align:center; width:70%; font-size:22px;<?php if (EMPTY($vkupno) || ($vkupno==0)){echo "color:red";} ?>" onClick="this.select();" value='<?php if(isset($mat_kol[0]) && !EMPTY($mat_kol[0]) && $mat_kol[0]<>0){ echo number_format($mat_kol[0],0);}
																																																								  												elseif(isset($mat_kol[1]) && !EMPTY($mat_kol[1]) && $mat_kol[1]<>0){ echo number_format($mat_kol[1],0);} ?>' name='kol<?php echo $i;?>' id='kol<?php echo $i;?>'/></td>
																																																								  												
	</tr>

<?php 
		$i++;			
	}
	echo "<td><td><td><td><td><td width='15%' align='center'><input type='submit' value='Зачувај' style='height:60px; width:100%' name='zacuvaj' id='zacuvaj'/></td></td></td></td></td></td>";

}
?>
</table>
</form>
</body>
</html>