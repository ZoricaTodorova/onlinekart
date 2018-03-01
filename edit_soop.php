<?php
include_once 'inc/functions.php';
include_once 'inc/initialize.php';
include_once 'inc/menu.php';

if (!isadmin() || !logged()){
	redirect('infomng.php');
}

//$handle = anyconnect($_SESSION['sektor']);
$handle = connectweb();
$id=$_POST['id'];
$id=mysqli_real_escape_string($handle,$id);
if (isset($_POST['id']) && isset($_POST['subject']) && !EMPTY($_POST['subject']) && isset($_POST['sodrzina']) && !EMPTY($_POST['sodrzina'])){
$subject=$_POST['subject'];
$subject=mysqli_real_escape_string($handle,$subject);
$text=$_POST['sodrzina'];
$text=mysqli_real_escape_string($handle,$text);
$dt = date('Y-m-d H:i:s', time());
$order = "UPDATE soop
			SET subject='$subject',
				text='$text', datum='$dt'
			WHERE
				id='$id'";
mysqli_query($handle, $order);
//redirect('infomng.php?cod='.$id);
if (isset($_POST['resobj']))
{
	//echo $_POST['resobj'];
	editirajsoop($_SESSION['sektor'],$_POST['resobj'],$id);
}
echo ("<SCRIPT LANGUAGE='JavaScript'>
 	window.alert('Соопштението е променето.')
 	window.location.href='infomng.php'
 	</SCRIPT>");
}
else {
	echo ("<SCRIPT LANGUAGE='JavaScript'>
 	window.alert('Соопштението мора да има наслов и содржина!')
 	window.location.href='infomng.php'
 	</SCRIPT>");
}
?>