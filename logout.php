<?php
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
session_start();
//unset($_SESSION["lgn"]);
session_unset();
session_destroy();
session_write_close();
redirect('login.php');
die();
?>