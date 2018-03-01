<?php
session_start();
header("Content-type: text/html; charset=windows-1251");
include 'inc/functions.php';
//include 'inc/initialize.php';
$handle=anyconnect($_SESSION['fin_baza']);
$q = strtolower($_GET["q"]);
if (!$q) return;
 
$sql = "select concat(cod,' - ',opis) as celo from mesto_trosok where cod LIKE '%$q%' or opis LIKE '%$q%'";
$rsd = mysql_query($sql, $handle);
while($rs = mysql_fetch_array($rsd)) {
    $cname = $rs['celo'];
    echo "$cname\n";
}
?>