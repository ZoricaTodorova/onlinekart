<?php
session_start();
header("Content-type: text/html; charset=windows-1251");
include 'inc/functions.php';
//include 'inc/initialize.php';
$handle=connectwebnal();


if (isset($_SESSION['nar_firma']) && !empty($_SESSION['nar_firma'])){
	$firma_arr=explode(' - ',$_SESSION['nar_firma']);
	$firma=$firma_arr[0];
	$firma=mysqli_real_escape_string($handle, $firma);
	$korisnik=" AND korisnik='".$firma."'";
}else{$korisnik='';}

if (isset($_SESSION['nar_komerc']) && !empty($_SESSION['nar_komerc'])){
	$komerc_arr=explode(' - ',$_SESSION['nar_komerc']);
	$komerc=$komerc_arr[0];
	$komerc=mysqli_real_escape_string($handle, $komerc);
	$m_t=" AND m_t='".$komerc."'";
}else{$m_t='';}

$q = strtolower($_GET["q"]);
$q=mysqli_real_escape_string($handle,$q);
if (!$q) return;

$q1251 = iconv("UTF-8", "windows-1251", $q);
$sql = "select concat(cod,' - ',opis) as celo from org_e where (cod LIKE '%".$q."%' or opis LIKE '%".$q1251."%') $korisnik $m_t";
//echo $sql;
$rsd = mysqli_query($handle, $sql);
while($rs = mysqli_fetch_array($rsd)) {
    $cname = $rs['celo'];
    echo "$cname\n";
}
?>