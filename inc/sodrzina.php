<?php

// echo "���������� �������, ������������ �� ����� �������� �� ������ ���������.<br/>"
// 		."���� ������ �� �� ��������� ������ ������ �� ������ �� ��������. ���������<br/>"
// 		."���������� �� ������������ ������ ������ �� ������ �� �������� <a class=\"menulink\" href=\"form/about.php\" >�����</a><br/>";

// echo '<img style="position:absolute;top:110;right:10" src="http://upload.wikimedia.org/wikipedia/commons/d/db/Russian_gas_station_in_Macedonia.jpg" width="650px" height="450px" title="Lukoil" alt="Lukoil" />';

include_once 'inc/functions.php';
$ini = new Configini("flpt");

//treba da se smeeeeeeeeniiiiiiiiii

if ($ini->getinival('def', 'onlyfina', '0') == '0')
	echo "<div>���������� �������, ������������ �� ����� �������� �� ".$ini->getinival('def', 'cmpname', '').".<br/>";

$handle = connectweb();
$query = "SELECT usr.id, usr.lastlogin, cmp.dsc ".
		"FROM usr LEFT JOIN cmp ".
		"ON cmp.id = usr.id_cmp WHERE login = '".getuser()."'";

$result = mysqli_query($handle, $query) or die(mysqli_error());

while($row = mysqli_fetch_array($result))
{
	echo "<br>";
	//echo "<B>" . "��������: " . $row['dsc'] . "<br>"; 
	echo "<B>" . "��������: " . $_SESSION['lgn'] . "<br>";
	echo "���������������� ���: " . $row['id'] . "<br>";
	echo "�������� ���� �� ��������: " . $row['lastlogin'] . "<br>";
}

?>