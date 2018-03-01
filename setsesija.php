<?php
session_start();
if (empty($_POST['name'])) 
	unset($_SESSION['xxk']);
else
	$_SESSION['xxk']=$_POST['name'];

if (empty($_POST['firma']) || $_POST['firma']=='')
	unset($_SESSION['nar_firma']);
else
	$_SESSION['nar_firma']=$_POST['firma'];

if (empty($_POST['komerc']) || $_POST['komerc']=='')
	unset($_SESSION['nar_komerc']);
else
	$_SESSION['nar_komerc']=$_POST['komerc'];
?>