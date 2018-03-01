<?php
session_start();
header("Content-type: text/html; charset=windows-1251");
include 'inc/functions.php';
//include 'inc/initialize.php';
$handle=connectwebnal();
$q = strtolower($_GET["q"]);
$q=mysqli_real_escape_string($handle,$q);
if (!$q) return;
 
$q1251 = iconv("UTF-8", "windows-1251", $q);
//$sql = "select concat(cod,' - ',opis) as celo from firmi where id LIKE '%$q%' or dsc LIKE '%$q%'";
$sql = "select concat(cod,' - ',opis) as celo from firmi where cod LIKE '%".$q."%' or opis LIKE '%".$q1251."%' or opis_a like '%".$q."%'";
$rsd = mysqli_query($handle, $sql);
while($rs = mysqli_fetch_array($rsd)) {
    $cname = $rs['celo'];
    echo "$cname\n";
}
?>