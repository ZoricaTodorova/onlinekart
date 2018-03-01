<html>
<body>
<fieldset style="border:black 1px solid; width:450">
<legend align="left" style="font:bold;border: 1px solid black">Статус на клиентот</legend>
<?php 
$cIdCmp = $_SESSION['idcmp'];
$handle=connectkart();
$web=connectweb();
$status_cmd=mysqli_query($web, "Select * from vid_status where vid=11 order by rbr");

echo "<table>";
while ($stat=mysqli_fetch_array($status_cmd)){
	
	$tabela=$stat['tabela'];
	$doct=$stat['doct'];
	$doct_arr=explode(',', $doct);
	
	if (EMPTY($stat['doct'])){
		$cmd = "select (round(sum(sgn*iznos), 2)) as limito from $tabela where firma = '$cIdCmp'";
		$res = mysqli_query($handle, $cmd);
		$row=mysqli_fetch_row($res);
	}
	elseif(substr($doct_arr[0],0,1)=='#'){
		$dd=substr($doct_arr[0],1);
		foreach($doct_arr as $doc){
			$dd=$dd.','.substr($doc,1);
		}
		$cmd = "select (round(sum(sgn*iznos), 2)) as limito from $tabela where doct not in ($dd) and firma = '$cIdCmp'";
		$res = mysqli_query($handle, $cmd);
		$row=mysqli_fetch_row($res);
	}
	else{
		$cmd = "select (round(sum(sgn*iznos), 2)) as limito from $tabela where doct in ($doct) and firma = '$cIdCmp'";
		$res = mysqli_query($handle, $cmd);
		$row=mysqli_fetch_row($res);
	}
	//echo $cmd;
	
	echo "<tr>";
	echo "<th align='left'>".$stat['opis']."</th>";
	if ($row[0]<0){
		echo "<td align='right' style='color:red'>".str_pad(number_format(abs($row[0]),0),20,'.',STR_PAD_LEFT);
	}
	else{
		echo "<td align='right'>".str_pad(number_format($row[0],0),20,'.',STR_PAD_LEFT);
	}
	echo "</td></tr>";
	
}

$cmd = "select max(wdt) as limito from $tabela where firma = '$cIdCmp'";
$res = mysqli_query($handle, $cmd);
$row=mysqli_fetch_row($res);

echo "<tr>";
echo "<th align='left'>Датум и час на ажурирање:</th>";
echo "<td>".$row[0]."</td>";
echo "</tr>";

echo "</table>";

?>
</fieldset>
</body>
</html>