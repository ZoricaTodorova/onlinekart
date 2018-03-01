<body>
<fieldset style="border:black 1px solid; width:450">
<legend align="left" style="font:bold;border: 1px solid black">Статус на клиентот</legend>
<?php 
	$cIdCmp = $_SESSION['idcmp'];
	$handle = connectkart();
	
	$cmd = "select abs(round(sum(sgn*iznos), 2)) as limito from bs_exchange where doct='F0' and firma = '$cIdCmp'";
	$res = mysqli_query($handle, $cmd);
	$row=mysqli_fetch_row($res);
	$cTable = '<table>
	<tr>
	  <th align="left">Почетно салдо:</th>
	  <td>'.str_pad(number_format($row[0],2),20,'.',STR_PAD_LEFT);

	$cmd = "select round(sum(sgn*iznos), 2) as limito from bs_exchange where doct='L' and firma = '$cIdCmp'";
	$res = mysqli_query($handle, $cmd);
	$row=mysqli_fetch_row($res);
	 
	$cTable = $cTable.'</td>
	</tr>
	<tr>
	<th align="left">Дозволен лимит:</th>
	<td>'.str_pad(number_format($row[0],2),20,'.',STR_PAD_LEFT);
	
	$cmd = "select round(sum(sgn*iznos), 2) as limito from bs_exchange where doct in ('F', 'I', 'R') and firma = '$cIdCmp'";
	$res = mysqli_query($handle, $cmd);
	$row=mysqli_fetch_row($res);
	  
	$cTable = $cTable.'</td>
	</tr>
	<tr>
	  <th align="left">Сопствени средства(уплати, одобренија):</th>
	  <td>'.str_pad(number_format($row[0],2),20,'.',STR_PAD_LEFT);
	
	$cmd = "select abs(round(sum(sgn*iznos), 2)) as limito from bs_exchange where (mat <> '' or doct='66') and firma = '$cIdCmp'";
	$res = mysqli_query($handle, $cmd);
	$row=mysqli_fetch_row($res);
	
	$cTable = $cTable.'</td>
	</tr>
	<tr>
	  <th align="left">Искористено:</th>
	  <td>'.str_pad(number_format($row[0],2),20,'.',STR_PAD_LEFT);
	
	$cmd = "select round(sum(sgn*iznos), 2) as limito from bs_exchange where firma = '$cIdCmp'";
	$res = mysqli_query($handle, $cmd);
	$row=mysqli_fetch_row($res); 
	
	$cTable = $cTable.'</td>
	</tr>
	<tr>
	  <th align="left">Расположливи средства:</th>
	  <td>'.str_pad(number_format($row[0],2),20,'.',STR_PAD_LEFT);
	
	$cmd = "select max(wdt) as limito from bs_exchange where firma = '$cIdCmp'";
	$res = mysqli_query($handle, $cmd);
	$row=mysqli_fetch_row($res);	  
	  
	$cTable = $cTable.'</td>
	</tr>
	<tr>
	  <th align="left">Датум и час на ажурирање:</th>
	  <td>'.$row[0].'</td>
	</tr>
	</table>';
	echo $cTable;
?>
</fieldset>
</body>