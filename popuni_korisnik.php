<?php
session_start();
include_once 'inc/functions.php';
header("Content-type: text/html; charset=windows-1251");

if (!isset($_SESSION['xxk'])) 
{
	echo 'Err1 Одберете база!1';
	return ;
}

if (strlen($_SESSION['xxk']) < 1)
{
	echo 'Err2 Одберете база!';
	return ;
}

$conn=explode(';',$_SESSION['xxk']);
$handle=anyconnect($conn[0]);                //momentalno ja zemam prvata; treba da se zema prioritetna od baza
$q = strtolower($_GET["q"]);
$q=mysqli_real_escape_string($handle,$q);
if (!$q) return; 
$q1251 = iconv("UTF-8", "windows-1251", $q);
$sql = "select concat(cod,' - ',opis) as celo from firmi where cod LIKE '%".$q."%' or opis LIKE '%".$q1251."%' or opis_a like '%".$q."%'";
//echo $sql;
$rsd = mysqli_query($handle, $sql);
while($rs = mysqli_fetch_array($rsd)) {
    $cname = $rs['celo'];
    echo "$cname\n";
}
disconnect($handle);
?>