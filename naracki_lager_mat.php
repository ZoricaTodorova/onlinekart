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
genmenu();
echo "<h4 align=center>Лагер листа</h4>";

echo "<a style='font-size: 150%;' href='izbor_naracki.php'>Назад</a><br/><br/>";

$god=date('Y');
$datum = date('Y-m-d H:i:s');
$datum=substr($datum,0,10);

$handle=connectwebnal();
		$res=mysqli_query($handle, " select mat, opis,sum(kol_v-kol_i) as zaliha, cena, nar_kolicina, ddv_proc from(
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
										INNER JOIN  ( SELECT cod , opis  from materijali  where godina ='$god' and web_mat=1 and m_u='M')  as maus 
										ON sostojba.mat = maus.cod 
										left join (select cena,mat from cenovnik where cenovnik.tip ='1'
										and concat(cast(oddat as char),odcas)<= concat(CAST(CAST('$datum' as date) as char),'00:00')
										and if(cenovnik.dodat<>'0000-00-00',concat(cast(dodat as char),docas)>=CONCAT(CAST(CAST('$datum' as
										date) as char),'00:00') ,cenovnik.dodat='0000-00-00')) as ceno on sostojba.mat=ceno.mat
										left join (select sum(kolicina) as nar_kolicina, mat from nar_stavki where nalb='' 
										and nar_glava_id in (select id from nar_glava where godina ='$god') group by mat)
											 as nar_st on sostojba.mat=nar_st.mat				
										) as final group by mat order by mat");	
		
		$boja=$_SESSION['boja'];
		echo "<body bgcolor='$boja'>";
		echo "<table border='2' cellspacing='0' width='100%'>
		<tr>
		<th style='font-size: 25px;'>Код</th>
		<th style='font-size: 25px;'>Опис</th>
		<th style='font-size: 25px;'>Цена</th>
		<th style='font-size: 25px;'>Залиха</th>
		<th style='font-size: 25px;'>Нарачки</th>
		<th style='font-size: 25px;'>Вкупно</th>
		</tr>";
		
		while($row = mysqli_fetch_row($res)) {
			
			$mat=$row[0];
		
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
				
			echo "<tr>";
			echo "<td align='center' style='font-size: 25px;' >" . $mat."</td>";
			echo "<td align='center' style='font-size: 25px;' >". $row[1] . "</td>";
			echo "<td align='center' style='font-size: 25px;' >". number_format($cena_ddv,0) . "</td>";
			echo "<td align='center' style='font-size: 25px;' >" . number_format($row[2],0) . "</td>";
			echo "<td align='center' style='font-size: 25px;' ><a href='vidi_naracki.php?mat=".$row[0]."'>".number_format($kol,0)."</a></td>";
			echo "<td align='center' style='font-size: 25px;' >" . number_format($vkupno,0) . "</td>";
			echo "</tr>";
			
		}
		echo "</table>";
?>


</body>
</html>