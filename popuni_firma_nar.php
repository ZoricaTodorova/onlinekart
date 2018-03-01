<?php
session_start();
header("Content-type: text/html; charset=windows-1251");
include 'inc/functions.php';
$handle=connectwebnal();
$q = strtolower($_GET["q"]);
$q=mysqli_real_escape_string($handle,$q);
if (!$q) return;
 
$q1251 = iconv("UTF-8", "windows-1251", $q);

$sql="select  distinct concat(firmi.cod,' - ',firmi.opis) as celo from firmi inner join org_e on firmi.cod=org_e.korisnik where org_E.korisnik<>'' and org_e.m_t='".getuser()."' and (firmi.cod LIKE '%".$q."%' or firmi.opis LIKE '%".$q1251."%' or firmi.opis_a like '%".$q."%')";
$rsd = mysqli_query($handle, $sql);
while($rs = mysqli_fetch_array($rsd)) {
    $cname = $rs['celo'];
    echo "$cname\n";
}
?>